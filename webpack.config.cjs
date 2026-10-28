const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

/**
 * Block compilation for both editions.
 *
 * This config lives next to the sources it compiles, and that is the whole trick. It needs
 * no "is there a sibling shared/?" fallback because `__dirname` already answers correctly in
 * both layouts that matter:
 *
 *   monorepo          shared/webpack.config.cjs  ->  shared/src, shared/build
 *   source distribution  <plugin>/webpack.config.cjs  ->  <plugin>/src, <plugin>/build
 *
 * That second layout is not a convenience. A published source tree has to be able to
 * reproduce the assets shipped beside it, and webpack derives module ids from each module's
 * request path — a block is `../shared/src/...` when compiled from `pro-only/` and `./src/...`
 * when compiled from the assembly. Same code, different ids, different bytes, and the
 * asset.php content hash moves with them. So the assets are compiled in the layout that
 * ships, and this config works unchanged in it.
 *
 * Only `@wordpress/*` is imported by the block sources, and wp-scripts externalises all of
 * it to the `wp-*` script handles WordPress already enqueues — which is why this package
 * needs no runtime dependencies at all, and in particular none from a private registry. That
 * is what makes a free source distribution publishable.
 */
const srcRoot = path.resolve( __dirname, 'src' );

/**
 * Readable unless asked otherwise. WordPress.org refused Undercard on 2026-10-08 over a minified
 * bundle, and the command center's submission preflight (*Readable code*) refuses a free tree whose
 * JavaScript or CSS is minified, so the free tier's zip has to be readable. `GHOSTLABS_MINIFY=1`
 * opts in, for any build that wants it. Ported from Cycle Block's config, 2026-10-27.
 *
 * ⚠️ `optimization.minimize` alone is not enough: sass-loader compresses on its own in production
 * mode, so its output style is set as well.
 */
const MINIFY = process.env.GHOSTLABS_MINIFY === '1';

const withSassOutputStyle = ( rules ) =>
	rules.map( ( rule ) => {
		if ( Array.isArray( rule.oneOf ) ) {
			return { ...rule, oneOf: withSassOutputStyle( rule.oneOf ) };
		}

		if ( ! Array.isArray( rule.use ) ) {
			return rule;
		}

		return {
			...rule,
			use: rule.use.map( ( entry ) => {
				const loader = typeof entry === 'string' ? entry : entry?.loader;

				// wp-scripts runs cssnano as a postcss-loader plugin, not as a webpack minimizer,
				// so `minimize: false` never reaches it. Drop it from the plugin list instead.
				// (Cycle Block does this with its own postcss.config.js; here the plugins do not
				// resolve from shared/ under pnpm, and the lockfile stays as it is.)
				if ( ! MINIFY && loader && loader.includes( 'postcss-loader' ) ) {
					const plugins = entry.options?.postcssOptions?.plugins;

					if ( ! Array.isArray( plugins ) ) {
						return entry;
					}

					return {
						...entry,
						options: {
							...entry.options,
							postcssOptions: {
								...entry.options.postcssOptions,
								// ⚠️ cssnano is not a plugin object with a name: it returns a whole postcss
								// Processor (its preset, nested), and it is the only one in wp-scripts' list.
								plugins: plugins.filter(
									( plugin ) => plugin?.constructor?.name !== 'Processor'
								),
							},
						},
					};
				}

				if ( ! loader || ! loader.includes( 'sass-loader' ) ) {
					return entry;
				}

				const base = typeof entry === 'string' ? { loader: entry } : entry;
				const options = base.options ?? {};

				return {
					...base,
					options: {
						...options,
						sassOptions: {
							...options.sassOptions,
							// `style` for sass-loader 16's modern API, which this repo runs; `outputStyle`
							// for the legacy one, which Cycle Block's sass-loader 12 reads.
							style: MINIFY ? 'compressed' : 'expanded',
							outputStyle: MINIFY ? 'compressed' : 'expanded',
						},
					},
				};
			} ),
		};
	} );

module.exports = {
	...defaultConfig,
	entry: {
		...( typeof defaultConfig.entry === 'function'
			? defaultConfig.entry()
			: defaultConfig.entry ),
		'blocks/_shared/global': path.join( srcRoot, 'blocks/_shared/global.js' ),
	},
	optimization: {
		...defaultConfig.optimization,
		minimize: MINIFY,
	},
	module: {
		...defaultConfig.module,
		rules: withSassOutputStyle( defaultConfig.module.rules ),
	},
};
