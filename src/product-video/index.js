/**
 * Product Video block (dynamic, rendered in PHP).
 */
import { __ } from '@wordpress/i18n';
import { createElement, Fragment } from '@wordpress/element';
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, Spinner } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import './style.scss';
import './editor.scss';

import metadata from './block.json';
import ProductPicker from '../shared/product-picker';

const icon = (
	<svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
		<rect
			x="3"
			y="5"
			width="18"
			height="14"
			rx="2"
			fill="none"
			stroke="currentColor"
			strokeWidth="1.5"
		/>
		<path d="M10 9.2v5.6l4.6-2.8z" fill="currentColor" />
	</svg>
);

function Edit( { attributes, setAttributes } ) {
	const { productId, index } = attributes;
	return (
		<Fragment>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'wbd-product-benefits-blocks' ) }>
					<ProductPicker
						value={ productId }
						onChange={ ( next ) =>
							setAttributes( { productId: next } )
						}
					/>
					<RangeControl
						label={ __(
							'Which video (0 = all)',
							'wbd-product-benefits-blocks'
						) }
						value={ index }
						min={ 0 }
						max={ 10 }
						onChange={ ( next ) =>
							setAttributes( { index: next || 0 } )
						}
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
				<ServerSideRender
					block={ metadata.name }
					attributes={ attributes }
					urlQueryArgs={ { wbdVideoPreview: 1 } }
					LoadingResponsePlaceholder={ Spinner }
				/>
			</div>
		</Fragment>
	);
}

registerBlockType( metadata.name, {
	icon,
	edit: Edit,
	save: () => null,
} );
