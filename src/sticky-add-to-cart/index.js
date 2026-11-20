/**
 * Sticky Add to Cart block registration (dynamic block, rendered in PHP).
 */
import { createElement } from '@wordpress/element';
import { registerBlockType } from '@wordpress/blocks';

// Compiled to style-index.css, registered in PHP as `wbd-sticky-add-to-cart`.
import './style.scss';
import './editor.scss';

import metadata from './block.json';
import Edit from './edit';

const blockIcon = (
	<svg
		xmlns="http://www.w3.org/2000/svg"
		viewBox="0 0 24 24"
		width="24"
		height="24"
		aria-hidden="true"
		focusable="false"
	>
		<rect
			x="2.75"
			y="14.75"
			width="18.5"
			height="6.5"
			rx="1.5"
			fill="none"
			stroke="currentColor"
			strokeWidth="1.5"
		/>
		<rect
			x="14"
			y="16.5"
			width="5.5"
			height="3"
			rx="1"
			fill="currentColor"
		/>
		<path
			fill="none"
			stroke="currentColor"
			strokeWidth="1.5"
			strokeLinecap="round"
			strokeLinejoin="round"
			d="M5 3.5h1.6l1.5 6.5h7.4l1.5-4.75H7.4"
		/>
		<circle cx="9" cy="12" r="0.9" fill="currentColor" />
		<circle cx="14.5" cy="12" r="0.9" fill="currentColor" />
	</svg>
);

registerBlockType( metadata.name, {
	icon: blockIcon,
	edit: Edit,
	save: () => null,
} );
