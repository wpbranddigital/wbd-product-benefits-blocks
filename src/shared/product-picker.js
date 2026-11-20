/**
 * Searchable product picker backed by the public WooCommerce Store API.
 * Shared by the WBD Woo Kit blocks.
 */
import { __ } from '@wordpress/i18n';
import { createElement, useEffect, useState } from '@wordpress/element';
import { ComboboxControl } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { decodeEntities } from '@wordpress/html-entities';

/**
 * @param {Object}   props          Props.
 * @param {number}   props.value    Selected product ID (0 = current product).
 * @param {Function} props.onChange Change handler.
 * @param {string}   props.help     Help text.
 * @return {Element} Element.
 */
export default function ProductPicker( { value, onChange, help } ) {
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
			help={
				help ||
				__(
					'Leave empty to use the current product.',
					'wbd-product-benefits-blocks'
				)
			}
			__nextHasNoMarginBottom
			__next40pxDefaultSize
		/>
	);
}
