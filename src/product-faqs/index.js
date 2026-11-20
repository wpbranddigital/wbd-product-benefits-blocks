/**
 * Product FAQs block (dynamic, rendered in PHP).
 */
import { __ } from '@wordpress/i18n';
import { createElement, Fragment } from '@wordpress/element';
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	TextControl,
	ToggleControl,
	Spinner,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import './style.scss';
import './editor.scss';

import metadata from './block.json';
import ProductPicker from '../shared/product-picker';

const icon = (
	<svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
		<path
			fill="none"
			stroke="currentColor"
			strokeWidth="1.5"
			d="M4 5.75h16M4 12h16M4 18.25h10"
		/>
		<circle cx="19" cy="18.25" r="2.25" fill="currentColor" />
	</svg>
);

function Edit( { attributes, setAttributes } ) {
	const { productId, style, heading, showHeading } = attributes;
	const blockProps = useBlockProps();

	return (
		<Fragment>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'wbd-product-benefits-blocks' ) }>
					<ProductPicker
						value={ productId }
						onChange={ ( next ) =>
							setAttributes( { productId: next } )
						}
						help={ __(
							'Leave empty on product pages and the Single Product template.',
							'wbd-product-benefits-blocks'
						) }
					/>
					<SelectControl
						label={ __( 'Style', 'wbd-product-benefits-blocks' ) }
						value={ style }
						options={ [
							{
								label: __(
									'Use dashboard setting',
									'wbd-product-benefits-blocks'
								),
								value: '',
							},
							{
								label: __(
									'Accordion',
									'wbd-product-benefits-blocks'
								),
								value: 'accordion',
							},
							{
								label: __(
									'Boxed cards',
									'wbd-product-benefits-blocks'
								),
								value: 'boxed',
							},
							{
								label: __(
									'Minimal lines',
									'wbd-product-benefits-blocks'
								),
								value: 'minimal',
							},
						] }
						onChange={ ( next ) => setAttributes( { style: next } ) }
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
					<ToggleControl
						label={ __( 'Show heading', 'wbd-product-benefits-blocks' ) }
						checked={ showHeading }
						onChange={ ( next ) =>
							setAttributes( { showHeading: next } )
						}
						__nextHasNoMarginBottom
					/>
					{ showHeading && (
						<TextControl
							label={ __(
								'Heading',
								'wbd-product-benefits-blocks'
							) }
							value={ heading }
							placeholder={ __(
								'Use dashboard setting',
								'wbd-product-benefits-blocks'
							) }
							onChange={ ( next ) =>
								setAttributes( { heading: next } )
							}
							__nextHasNoMarginBottom
							__next40pxDefaultSize
						/>
					) }
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<ServerSideRender
					block={ metadata.name }
					attributes={ attributes }
					urlQueryArgs={ { wbdFaqsPreview: 1 } }
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
