module.exports = {
	root: true,
	extends: [ 'plugin:@wordpress/eslint-plugin/recommended' ],
	settings: {
		react: {
			pragma: 'createElement',
			fragment: 'Fragment',
			version: '18',
		},
	},
	rules: {
		// JSX is compiled with the classic runtime (see babel.config.js).
		'react/jsx-uses-react': 'error',
	},
};
