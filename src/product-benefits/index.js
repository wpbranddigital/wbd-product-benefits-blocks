/**
 * Product Benefits block registration.
 */
import { createElement } from '@wordpress/element';
import { registerBlockType } from '@wordpress/blocks';

import './style.scss';
import './editor.scss';

import metadata from './block.json';
import Edit from './edit';
import save from './save';

const blockIcon = (
	<svg
		xmlns="http://www.w3.org/2000/svg"
		viewBox="0 0 24 24"
		width="24"
		height="24"
		aria-hidden="true"
		focusable="false"
	>
		<path
			fill="none"
			stroke="currentColor"
			strokeWidth="1.75"
			strokeLinejoin="round"
			d="M12 2.75 19.25 5.5v6c0 4.55-3.06 8.18-7.25 9.75-4.19-1.57-7.25-5.2-7.25-9.75v-6L12 2.75Z"
		/>
		<path
			fill="none"
			stroke="currentColor"
			strokeWidth="1.75"
			strokeLinecap="round"
			strokeLinejoin="round"
			d="m8.75 12 2.25 2.25 4.25-4.5"
		/>
	</svg>
);

registerBlockType( metadata.name, {
	icon: blockIcon,
	edit: Edit,
	save,
} );
