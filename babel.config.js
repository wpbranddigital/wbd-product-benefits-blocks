/**
 * Babel configuration.
 *
 * Uses the classic JSX runtime with `createElement` from `@wordpress/element`
 * so the build does not depend on the `react-jsx-runtime` script handle,
 * which only exists in WordPress 6.6+. This keeps WordPress 6.5 support.
 */
module.exports = ( api ) => {
	api.cache( true );

	return {
		presets: [ '@wordpress/babel-preset-default' ],
		plugins: [
			[
				'@babel/plugin-transform-react-jsx',
				{
					runtime: 'classic',
					pragma: 'createElement',
					pragmaFrag: 'Fragment',
					useSpread: true,
				},
			],
		],
	};
};
