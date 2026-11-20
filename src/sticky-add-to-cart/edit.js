/**
 * Editor component for the Sticky Add to Cart block.
 *
 * The canvas shows a server-rendered, static preview of the bar (the same PHP
 * markup used on the frontend). Shared behaviour lives in
 * WooCommerce → Sticky Add to Cart; the block can override the look per page.
 */
import { __ } from '@wordpress/i18n';
import {
	createElement,
	Fragment,
	useEffect,
	useState,
} from '@wordpress/element';
import {
	useBlockProps,
	InspectorControls,
	PanelColorSettings,
} from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	TextControl,
	ComboboxControl,
	ExternalLink,
	Spinner,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { decodeEntities } from '@wordpress/html-entities';

const config = window.wbdPbbSticky || {};

/**
 * Searchable product picker backed by the public WooCommerce Store API.
 *
 * @param {Object}   props          Props.
 * @param {number}   props.value    Selected product ID (0 = current product).
 * @param {Function} props.onChange Change handler.
 * @return {Element} Element.
 */
function ProductPicker( { value, onChange } ) {
	const [ search, setSearch ] = useState( '' );
	const [ options, setOptions ] = useState( [] );
	const [ selected, setSelected ] = useState( null );

	useEffect( () => {
		if ( ! value ) {
			setSelected( null );
			return;
		}
		let active = true;
		apiFetch( { path: `/wc/store/v1/products/${ value }` } )
			.then( ( product ) => {
				if ( active ) {
					setSelected( {
						value: product.id,
						label: decodeEntities( product.name ),
					} );
				}
			} )
			.catch( () => {
				if ( active ) {
					setSelected( { value, label: `#${ value }` } );
				}
			} );
		return () => {
			active = false;
		};
	}, [ value ] );

	useEffect( () => {
		let active = true;
		const timer = setTimeout( () => {
			apiFetch( {
				path: addQueryArgs( '/wc/store/v1/products', {
					search,
					per_page: 20,
				} ),
			} )
				.then( ( products ) => {
					if ( active && Array.isArray( products ) ) {
						setOptions(
							products.map( ( product ) => ( {
								value: product.id,
								label: decodeEntities( product.name ),
							} ) )
						);
					}
				} )
				.catch( () => active && setOptions( [] ) );
		}, 300 );
		return () => {
			active = false;
			clearTimeout( timer );
		};
	}, [ search ] );

	const list =
		selected && ! options.some( ( o ) => o.value === selected.value )
			? [ selected, ...options ]
			: options;

	return (
		<ComboboxControl
			label={ __( 'Product', 'wbd-product-benefits-blocks' ) }
			value={ value || null }
			options={ list }
			onChange={ ( next ) => onChange( next ? parseInt( next, 10 ) : 0 ) }
			onFilterValueChange={ setSearch }
			help={ __(
				'Leave empty to use the current product (product pages and the Single Product template). Choose a product to use the bar on landing pages.',
				'wbd-product-benefits-blocks'
			) }
			__nextHasNoMarginBottom
			__next40pxDefaultSize
		/>
	);
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		productId,
		template,
		position,
		buttonText,
		barBackground,
		barText,
		buttonBackground,
		buttonTextColor,
	} = attributes;

	const blockProps = useBlockProps( {
		className: 'wbd-satc-editor',
	} );

	return (
		<Fragment>
			<InspectorControls>
				<PanelBody
					title={ __( 'Product', 'wbd-product-benefits-blocks' ) }
				>
					<ProductPicker
						value={ productId }
						onChange={ ( next ) =>
							setAttributes( { productId: next } )
						}
					/>
				</PanelBody>
				<PanelBody
					title={ __( 'Template', 'wbd-product-benefits-blocks' ) }
				>
					<SelectControl
						label={ __(
							'Template',
							'wbd-product-benefits-blocks'
						) }
						value={ template }
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
									'Classic',
									'wbd-product-benefits-blocks'
								),
								value: 'classic',
							},
							{
								label: __(
									'Minimal',
									'wbd-product-benefits-blocks'
								),
								value: 'minimal',
							},
							{
								label: __(
									'Floating',
									'wbd-product-benefits-blocks'
								),
								value: 'floating',
							},
							{
								label: __(
									'Dark',
									'wbd-product-benefits-blocks'
								),
								value: 'dark',
							},
						] }
						onChange={ ( next ) =>
							setAttributes( { template: next } )
						}
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
					<SelectControl
						label={ __(
							'Position',
							'wbd-product-benefits-blocks'
						) }
						value={ position }
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
									'Bottom of the screen',
									'wbd-product-benefits-blocks'
								),
								value: 'bottom',
							},
							{
								label: __(
									'Top of the screen',
									'wbd-product-benefits-blocks'
								),
								value: 'top',
							},
						] }
						onChange={ ( next ) =>
							setAttributes( { position: next } )
						}
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
					<TextControl
						label={ __(
							'Button text',
							'wbd-product-benefits-blocks'
						) }
						value={ buttonText }
						maxLength={ 60 }
						placeholder={ __(
							'Use dashboard setting',
							'wbd-product-benefits-blocks'
						) }
						onChange={ ( next ) =>
							setAttributes( { buttonText: next } )
						}
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
				</PanelBody>
				<PanelColorSettings
					title={ __( 'Colors', 'wbd-product-benefits-blocks' ) }
					initialOpen={ false }
					colorSettings={ [
						{
							label: __(
								'Bar background',
								'wbd-product-benefits-blocks'
							),
							value: barBackground || undefined,
							onChange: ( next ) =>
								setAttributes( { barBackground: next || '' } ),
						},
						{
							label: __(
								'Bar text',
								'wbd-product-benefits-blocks'
							),
							value: barText || undefined,
							onChange: ( next ) =>
								setAttributes( { barText: next || '' } ),
						},
						{
							label: __(
								'Button background',
								'wbd-product-benefits-blocks'
							),
							value: buttonBackground || undefined,
							onChange: ( next ) =>
								setAttributes( {
									buttonBackground: next || '',
								} ),
						},
						{
							label: __(
								'Button text',
								'wbd-product-benefits-blocks'
							),
							value: buttonTextColor || undefined,
							onChange: ( next ) =>
								setAttributes( {
									buttonTextColor: next || '',
								} ),
						},
					] }
				/>
				<PanelBody
					title={ __(
						'Behaviour & visibility',
						'wbd-product-benefits-blocks'
					) }
					initialOpen={ false }
				>
					<p>
						{ __(
							'Shown elements, devices, AJAX, what happens after adding and the out-of-stock rule are shared by every sticky bar.',
							'wbd-product-benefits-blocks'
						) }
					</p>
					{ config.canManage && config.settingsUrl && (
						<ExternalLink href={ config.settingsUrl }>
							{ __(
								'Open Sticky Add to Cart settings',
								'wbd-product-benefits-blocks'
							) }
						</ExternalLink>
					) }
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<p className="wbd-satc-editor__label">
					{ __(
						'Sticky Add to Cart — on the frontend this bar sticks to the screen edge after shoppers scroll past the main Add to cart button.',
						'wbd-product-benefits-blocks'
					) }
				</p>
				<ServerSideRender
					block="wbd/sticky-add-to-cart"
					attributes={ attributes }
					urlQueryArgs={ { wbdSatcPreview: 1 } }
					LoadingResponsePlaceholder={ Spinner }
				/>
			</div>
		</Fragment>
	);
}
