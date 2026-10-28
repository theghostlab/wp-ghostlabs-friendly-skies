<?php

namespace GHOSTLABS\FRIENDLY_SKIES\Service;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * HTTP to Bluesky, in two shapes.
 *
 * - `get()` / `post()` retry an outage with back-off, and honour a 429's `Retry-After` up to
 *   60 s, three times. For the import, which runs in the background.
 * - `getOnce()` asks once and hands any failure straight back. For anything a page render
 *   waits on.
 *
 * ⚠️ A front-end render must never use the retrying shape. Bluesky sets `Retry-After`, so a
 *   rate limit held a public page for up to 3 × 60 s and died at `max_execution_time`
 *   (security audit 2026-10-23, finding 5). `BlueskyService` asks once and serves its last good
 *   copy instead.
 */
class ApiClient
{
    private const MAX_RETRIES = 3;
    private const BACKOFF_BASE = 1;
    private const BACKOFF_MULTIPLIER = 3;
    private const MAX_RETRY_AFTER = 60;

    private Logger $logger;

    /** @var callable(int): void */
    private $sleeper;

    public function __construct(Logger $logger, ?callable $sleeper = null)
    {
        $this->logger = $logger;
        $this->sleeper = $sleeper ?? 'sleep';
    }

    /**
     * @param array<string, string> $headers
     * @return array<string, mixed>|\WP_Error
     */
    public function get(string $url, array $headers): array|\WP_Error
    {
        return $this->request('GET', $url, $headers, null, self::MAX_RETRIES);
    }

    /**
     * One request: no retry and no sleep, whatever the failure. See the class docblock.
     *
     * @param array<string, string> $headers
     * @return array<string, mixed>|\WP_Error
     */
    public function getOnce(string $url, array $headers = []): array|\WP_Error
    {
        return $this->request('GET', $url, $headers, null, 0);
    }

    /**
     * @param array<string, string> $headers
     * @param mixed $body
     * @return array<string, mixed>|\WP_Error
     */
    public function post(string $url, array $headers, mixed $body = null): array|\WP_Error
    {
        return $this->request('POST', $url, $headers, $body, self::MAX_RETRIES);
    }

    /**
     * @param array<string, string> $headers
     * @param mixed $body
     * @return array<string, mixed>|\WP_Error
     */
    private function request(string $method, string $url, array $headers, mixed $body, int $maxRetries): array|\WP_Error
    {
        $lastError = null;

        for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
            if ($attempt > 0 && $lastError !== null) {
                ($this->sleeper)($lastError['delay']);
            }

            $args = ['headers' => $headers];
            if ($body !== null) {
                $args['body'] = is_array($body) ? wp_json_encode($body) : $body;
            }

            $response = $method === 'GET'
                ? wp_remote_get($url, $args)
                : wp_remote_post($url, $args);

            if (is_wp_error($response)) {
                $lastError = ['delay' => $this->backoffDelay($attempt), 'response' => $response];
                continue;
            }

            $code = (int) wp_remote_retrieve_response_code($response);
            $responseBody = wp_remote_retrieve_body($response);

            if ($code >= 200 && $code < 300) {
                /** @var array<string, mixed> */
                return json_decode($responseBody, true) ?? [];
            }

            if ($code === 429 || $code >= 500) {
                $retryHeaders = wp_remote_retrieve_headers($response);
                $delay = $this->resolveDelay($attempt, $code, $retryHeaders);
                $lastError = ['delay' => $delay, 'code' => $code, 'body' => $responseBody];
                continue;
            }

            return new \WP_Error((string) $code, $responseBody);
        }

        $this->logger->error($maxRetries > 0
            ? "Persistent failure for {$method} {$url}"
            : "Failed, not retried: {$method} {$url}");

        if (isset($lastError['response'])) {
            return $lastError['response'];
        }

        return new \WP_Error(
            (string) ($lastError['code'] ?? 'unknown'),
            $lastError['body'] ?? 'Request failed after retries'
        );
    }

    private function backoffDelay(int $attempt): int
    {
        return (int) (self::BACKOFF_BASE * (self::BACKOFF_MULTIPLIER ** $attempt));
    }

    /**
     * @param \ArrayAccess<string, string>|array<string, string> $headers
     */
    private function resolveDelay(int $attempt, int $code, \ArrayAccess|array $headers): int
    {
        if ($code === 429 && isset($headers['retry-after'])) {
            return min((int) $headers['retry-after'], self::MAX_RETRY_AFTER);
        }

        return $this->backoffDelay($attempt);
    }
}
