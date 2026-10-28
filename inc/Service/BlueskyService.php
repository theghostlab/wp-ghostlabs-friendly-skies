<?php

namespace GHOSTLABS\FRIENDLY_SKIES\Service;

if ( ! defined( 'ABSPATH' ) ) exit;

class BlueskyService
{
    public const BSKY_PUBLIC_API = 'https://public.api.bsky.app/xrpc/';

    public function __construct(
        private ApiClient $apiClient,
        private Cache $cache,
    ) {}

    private const BSKY_URL_TYPES = [
        'feed' => 'app.bsky.feed.generator',
        'lists' => 'app.bsky.graph.list',
        'post' => 'app.bsky.feed.post',
    ];

    private const PAUSED = 'bluesky_paused';

    /**
     * Ask Bluesky once, unless an outage paused asking.
     *
     * ⚠️ Once: every caller is a block render, a public page waiting on the answer. The retrying
     *   `ApiClient::get()` sleeps out a 429's `Retry-After`, up to 3 × 60 s, inside the render
     *   (security audit 2026-10-23, finding 5). A failure here is served from the last good copy
     *   or as the block's error state (`fallback()`), and the pause keeps the next blocks from
     *   each waiting out their own timeout while Bluesky is down.
     *
     * ⚠️ The pause is scoped to what failed. Unreachable, or rate-limited (a limit is per site),
     *   pauses everything; a 5xx is Bluesky answering for one resource — a broken custom feed's
     *   generator — and pausing every block for it would take the whole site down with it.
     *
     * @return array<string, mixed>|\WP_Error
     */
    private function request(string $url, string $cacheKey): array|\WP_Error
    {
        if ($this->cache->requestsPaused($cacheKey)) {
            return new \WP_Error(self::PAUSED, 'Bluesky failed a moment ago; not asking again yet.');
        }

        $response = $this->apiClient->getOnce($url, []);

        if ($response instanceof \WP_Error && self::isOutage($response)) {
            $code = (string) $response->get_error_code();
            $siteWide = $code === '429' || !ctype_digit($code) && $code !== 'unknown';

            $siteWide ? $this->cache->pauseRequests() : $this->cache->pauseRequests($cacheKey);
        }

        return $response;
    }

    /**
     * The last good copy when Bluesky can't be reached, else the error (owner, 2026-10-19;
     * register § Product behaviour).
     *
     * ⚠️ Only an outage falls back. A 4xx is Bluesky's answer — a deleted post is "not found" —
     *   and serving an old copy over it would keep showing a post its author removed.
     */
    private function fallback(string $cacheKey, \WP_Error $error): mixed
    {
        if (!self::isOutage($error)) {
            return $error;
        }

        $lastGood = $this->cache->lastGood($cacheKey);

        return $lastGood !== false ? $lastGood : $error;
    }

    /** A transport failure, a 429 or a 5xx — or retries exhausted, or our own pause. */
    private static function isOutage(\WP_Error $error): bool
    {
        $code = (string) $error->get_error_code();

        if (!ctype_digit($code)) {
            return true;
        }

        return (int) $code === 429 || (int) $code >= 500;
    }

    /**
     * @return array{handle: string, rkey: string}|null
     */
    public function parsePostUrl(string $url): ?array
    {
        $parsed = $this->parseBskyUrl($url);

        if ($parsed === null || $parsed['type'] !== 'post') {
            return null;
        }

        return [
            'handle' => $parsed['handle'],
            'rkey' => $parsed['rkey'],
        ];
    }

    /**
     * @return array{handle: string, type: 'feed'|'list'|'post', rkey: string}|null
     */
    public function parseBskyUrl(string $url): ?array
    {
        $path = wp_parse_url($url, PHP_URL_PATH);

        if (!is_string($path)) {
            return null;
        }

        $path = rtrim($path, '/');

        if (!preg_match('#^/profile/([^/]+)/(feed|lists|post)/([a-zA-Z0-9_-]+)$#', $path, $matches)) {
            return null;
        }

        $urlSegment = $matches[2];
        $type = $urlSegment === 'lists' ? 'list' : $urlSegment;

        return [
            'handle' => $matches[1],
            'type' => $type,
            'rkey' => $matches[3],
        ];
    }

    /**
     * @return string|\WP_Error
     */
    public function resolveToAtUri(string $input): string|\WP_Error
    {
        $input = trim($input);

        if ($input === '') {
            return new \WP_Error('invalid_url', 'URL or AT URI is required.');
        }

        if (str_starts_with($input, 'at://')) {
            return $input;
        }

        $parsed = $this->parseBskyUrl($input);

        if ($parsed === null) {
            return new \WP_Error('invalid_url', 'Invalid Bluesky URL: ' . $input);
        }

        $did = $this->resolveHandle($parsed['handle']);

        if (is_wp_error($did)) {
            return $did;
        }

        $urlSegment = $parsed['type'] === 'list' ? 'lists' : $parsed['type'];
        $nsid = self::BSKY_URL_TYPES[$urlSegment];

        return 'at://' . $did . '/' . $nsid . '/' . $parsed['rkey'];
    }

    /**
     * @return string|\WP_Error
     */
    public function resolveHandle(string $handle): string|\WP_Error
    {
        $cacheKey = 'handle_' . md5($handle);
        $cached = $this->cache->get($cacheKey);

        if ($cached !== false) {
            return $cached;
        }

        $response = $this->request(
            self::BSKY_PUBLIC_API . 'com.atproto.identity.resolveHandle?handle=' . rawurlencode($handle),
            $cacheKey,
        );

        if (is_wp_error($response)) {
            return $this->fallback($cacheKey, $response);
        }

        $did = $response['did'] ?? '';

        if (empty($did)) {
            return new \WP_Error('resolve_failed', 'Could not resolve handle: ' . $handle);
        }

        $this->cache->set($cacheKey, $did, 'handle');

        return $did;
    }

    /**
     * @return array<string, mixed>|\WP_Error
     */
    public function getPost(string $atUri): array|\WP_Error
    {
        $cacheKey = 'post_' . md5($atUri);
        $cached = $this->cache->get($cacheKey);

        if ($cached !== false) {
            /** @var array<string, mixed> $cached */
            return $cached;
        }

        $response = $this->request(
            self::BSKY_PUBLIC_API . 'app.bsky.feed.getPostThread?uri=' . urlencode($atUri) . '&depth=0',
            $cacheKey,
        );

        if (is_wp_error($response)) {
            return $this->fallback($cacheKey, $response);
        }

        /** @var array<string, mixed> $postData */
        $postData = $response['thread']['post'] ?? [];

        if (empty($postData)) {
            return new \WP_Error('post_not_found', 'Post not found: ' . $atUri);
        }

        $this->cache->set($cacheKey, $postData, 'post');

        return $postData;
    }

    /**
     * @return array<string, mixed>|\WP_Error
     */
    public function getPostFromUrl(string $url): array|\WP_Error
    {
        $parsed = $this->parsePostUrl($url);

        if ($parsed === null) {
            return new \WP_Error('invalid_url', 'Invalid Bluesky post URL: ' . $url);
        }

        $did = $this->resolveHandle($parsed['handle']);

        if (is_wp_error($did)) {
            return $did;
        }

        $atUri = 'at://' . $did . '/app.bsky.feed.post/' . $parsed['rkey'];

        return $this->getPost($atUri);
    }

    /**
     * @return array<string, mixed>|\WP_Error
     */
    public function getProfile(string $actor): array|\WP_Error
    {
        $actor = ltrim(strtolower(trim($actor)), '@');

        if ($actor === '') {
            return new \WP_Error('invalid_actor', 'Actor handle or DID is required.');
        }

        $cacheKey = 'profile_' . md5($actor);
        $cached = $this->cache->get($cacheKey);

        if ($cached !== false) {
            /** @var array<string, mixed> $cached */
            return $cached;
        }

        $response = $this->request(
            self::BSKY_PUBLIC_API . 'app.bsky.actor.getProfile?actor=' . urlencode($actor),
            $cacheKey,
        );

        if (is_wp_error($response)) {
            return $this->fallback($cacheKey, $response);
        }

        if (empty($response['did'])) {
            return new \WP_Error('profile_not_found', 'Profile not found: ' . $actor);
        }

        $this->cache->set($cacheKey, $response, 'profile');

        return $response;
    }

    /**
     * @return array<string, mixed>|\WP_Error
     */
    public function getPostThread(string $atUri, int $depth = 5): array|\WP_Error
    {
        $cacheKey = 'thread_' . md5($atUri . $depth);
        $cached = $this->cache->get($cacheKey);

        if ($cached !== false) {
            /** @var array<string, mixed> $cached */
            return $cached;
        }

        $response = $this->request(
            self::BSKY_PUBLIC_API . 'app.bsky.feed.getPostThread?uri=' . urlencode($atUri)
                . '&depth=' . $depth . '&parentHeight=0',
            $cacheKey,
        );

        if (is_wp_error($response)) {
            return $this->fallback($cacheKey, $response);
        }

        $thread = $response['thread'] ?? [];

        if (empty($thread)) {
            return new \WP_Error('thread_not_found', 'Thread not found: ' . $atUri);
        }

        $this->cache->set($cacheKey, $response, 'thread');

        return $response;
    }

    /**
     * @return array<string, mixed>|\WP_Error
     */
    public function getPostThreadFromUrl(string $url, int $depth = 5): array|\WP_Error
    {
        $parsed = $this->parsePostUrl($url);

        if ($parsed === null) {
            return new \WP_Error('invalid_url', 'Invalid Bluesky post URL: ' . $url);
        }

        $did = $this->resolveHandle($parsed['handle']);

        if (is_wp_error($did)) {
            return $did;
        }

        $atUri = 'at://' . $did . '/app.bsky.feed.post/' . $parsed['rkey'];

        return $this->getPostThread($atUri, $depth);
    }

    /**
     * @return array{posts: list<array<string, mixed>>, cursor: string|null}|\WP_Error
     */
    public function getAuthorFeed(string $actor, int $limit = 10, string $filter = 'posts_no_replies'): array|\WP_Error
    {
        return $this->postsFrom(
            self::BSKY_PUBLIC_API . 'app.bsky.feed.getAuthorFeed?actor=' . urlencode($actor)
                . '&limit=' . $limit . '&filter=' . $filter,
            'feed_author_' . md5($actor . $limit . $filter),
            'feed',
            'feed_empty',
            'No posts found for: ' . $actor,
        );
    }

    /**
     * One page of posts from a feed endpoint: from the cache when it has them, else asked once,
     * cached, and served from the last good copy on an outage — the read every feed source makes.
     *
     * @param string $url       The whole XRPC URL, query string included.
     * @param string $itemsKey  Where the answer keeps its posts: `feed` (each item wraps one under
     *                          `post`) or `posts` (bare).
     * @param string $emptyCode The error an answer with no posts becomes.
     * @return array{posts: list<array<string, mixed>>, cursor: string|null}|\WP_Error
     */
    public function postsFrom(string $url, string $cacheKey, string $itemsKey, string $emptyCode, string $emptyMessage): array|\WP_Error
    {
        $cached = $this->cache->get($cacheKey);

        if ($cached !== false) {
            /** @var array{posts: list<array<string, mixed>>, cursor: string|null} $cached */
            return $cached;
        }

        $response = $this->request($url, $cacheKey);

        if (is_wp_error($response)) {
            return $this->fallback($cacheKey, $response);
        }

        /** @var list<array<string, mixed>> $items */
        $items = $response[$itemsKey] ?? [];

        if (empty($items)) {
            return new \WP_Error($emptyCode, $emptyMessage);
        }

        $posts = array_map(fn(array $item): array => $item['post'] ?? $item, $items);

        $result = [
            'posts' => $posts,
            'cursor' => $response['cursor'] ?? null,
        ];

        $this->cache->set($cacheKey, $result, 'feed');

        return $result;
    }
}
