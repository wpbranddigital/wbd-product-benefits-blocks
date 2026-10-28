/**
 * Shared helpers used by both edit() and save() so the editor preview and the
 * saved frontend markup always produce identical classes and style variables.
 */
import { __ } from '@wordpress/i18n';

export const ALIGNMENTS = [ 'left', 'center', 'right' ];
export const TITLE_TAGS = [ 'h2', 'h3', 'h4', 'h5', 'h6', 'p' ];
export const FONT_WEIGHTS = [ '300', '400', '500', '600', '700', '800' ];
export const BORDER_STYLES = [ 'none', 'solid', 'dashed', 'dotted', 'double' ];
export const DESKTOP_COLUMNS = [ 1, 2, 3, 4, 6 ];
export const TABLET_COLUMNS = [ 1, 2, 3, 4 ];
export const MOBILE_COLUMNS = [ 1, 2 ];

/**
 * Default rendering values used when an attribute is unset.
 * Must stay in sync with the CSS fallbacks in style.scss.
 */
export const DEFAULT_ICON_SIZE = {
	grid: 40,
	horizontal: 40,
	checklist: 24,
	badges: 24,
};

/**
 * Creates a reasonably unique item id (editor-only, used as React key).
 *
 * @return {string} Id.
 */
export function createItemId() {
	return 'cb-' + Math.random().toString( 36 ).slice( 2, 10 );
}

/**
 * Returns a fresh benefit item.
 *
 * @param {Object} overrides Field overrides.
 * @return {Object} Item.
 */
export function createItem( overrides = {} ) {
	return {
		id: createItemId(),
		icon: 'check-circle',
		title: '',
		description: '',
		url: '',
		opensInNewTab: false,
		mediaId: 0,
		mediaUrl: '',
		mediaAlt: '',
		iconAlign: '',
		textAlign: '',
		...overrides,
	};
}

/**
 * Translated default items inserted with a new block.
 *
 * @return {Object[]} Items.
 */
export function getDefaultItems() {
	return [
		createItem( {
			icon: 'truck',
			title: __( 'Free Shipping', 'wbd-product-benefits-blocks' ),
			description: __(
				'Fast and reliable delivery on eligible orders.',
				'wbd-product-benefits-blocks'
			),
		} ),
		createItem( {
			icon: 'lock',
			title: __( 'Secure Payment', 'wbd-product-benefits-blocks' ),
			description: __(
				'Your payment information is protected.',
				'wbd-product-benefits-blocks'
			),
		} ),
		createItem( {
			icon: 'refresh',
			title: __( 'Easy Returns', 'wbd-product-benefits-blocks' ),
			description: __(
				'Simple and hassle-free return policy.',
				'wbd-product-benefits-blocks'
			),
		} ),
		createItem( {
			icon: 'headset',
			title: __( 'Expert Support', 'wbd-product-benefits-blocks' ),
			description: __(
				'Get help before and after your purchase.',
				'wbd-product-benefits-blocks'
			),
		} ),
	];
}

const isNum = ( value ) =>
	typeof value === 'number' && Number.isFinite( value ) && value >= 0;

const isColor = ( value ) =>
	typeof value === 'string' &&
	value.length > 0 &&
	value.length < 64 &&
	/^[#a-zA-Z0-9(),.%\s-]+$/.test( value );

const oneOf = ( value, list ) => list.includes( value );

const pick = ( value, list, fallback ) =>
	oneOf( value, list ) ? value : fallback;

/**
 * Block wrapper class names.
 *
 * @param {Object} attributes Block attributes.
 * @return {string} Class names.
 */
export function getBlockClassName( attributes ) {
	const {
		layout,
		columnsDesktop,
		columnsTablet,
		columnsMobile,
		contentAlign,
		hasShadow,
		shadowIntensity,
		showIcon,
		hasIconBackground,
		hideOnDesktop,
		hideOnTablet,
		hideOnMobile,
	} = attributes;

	const safeLayout = pick(
		layout,
		[ 'grid', 'horizontal', 'checklist', 'badges' ],
		'grid'
	);

	const classes = [
		'cb-product-benefits',
		`cb-layout-${ safeLayout }`,
		`cb-cols-d-${ pick( columnsDesktop, DESKTOP_COLUMNS, 4 ) }`,
		`cb-cols-t-${ pick( columnsTablet, TABLET_COLUMNS, 2 ) }`,
		`cb-cols-m-${ pick( columnsMobile, MOBILE_COLUMNS, 1 ) }`,
	];

	if ( 'grid' !== safeLayout ) {
		classes.push( 'cb-is-row' );
	}
	if ( oneOf( contentAlign, ALIGNMENTS ) ) {
		classes.push( `cb-align-${ contentAlign }` );
	}
	if ( hasShadow ) {
		classes.push(
			`cb-shadow-${ pick(
				shadowIntensity,
				[ 'small', 'medium', 'large' ],
				'small'
			) }`
		);
	}
	if ( false === showIcon ) {
		classes.push( 'cb-no-icon' );
	}
	if ( hasIconBackground ) {
		classes.push( 'cb-has-icon-bg' );
	}
	if ( hideOnDesktop ) {
		classes.push( 'cb-hide-desktop' );
	}
	if ( hideOnTablet ) {
		classes.push( 'cb-hide-tablet' );
	}
	if ( hideOnMobile ) {
		classes.push( 'cb-hide-mobile' );
	}

	return classes.join( ' ' );
}

/**
 * CSS custom properties for user-customised values only.
 * Unset values are omitted so the stylesheet defaults apply and no inline
 * style is saved for an unstyled block.
 *
 * @param {Object} attributes Block attributes.
 * @return {Object} Style object.
 */
export function getStyleVars( attributes ) {
	const style = {};
	const px = ( value ) => `${ value }px`;

	const numeric = {
		gap: [ '--cb-gap', px ],
		itemPadding: [ '--cb-item-padding', px ],
		titleFontSize: [ '--cb-title-size', px ],
		descriptionFontSize: [ '--cb-desc-size', px ],
		titleLineHeight: [ '--cb-title-lh', String ],
		descriptionLineHeight: [ '--cb-desc-lh', String ],
		borderWidth: [ '--cb-border-width', px ],
		borderRadius: [ '--cb-border-radius', px ],
		iconSize: [ '--cb-icon-size', px ],
		iconBackgroundRadius: [ '--cb-icon-radius', ( v ) => `${ v }%` ],
	};

	Object.keys( numeric ).forEach( ( key ) => {
		if ( isNum( attributes[ key ] ) ) {
			const [ prop, format ] = numeric[ key ];
			style[ prop ] = format( attributes[ key ] );
		}
	} );

	const colors = {
		itemBackgroundColor: '--cb-item-bg',
		itemTextColor: '--cb-item-color',
		iconColor: '--cb-icon-color',
		borderColor: '--cb-border-color',
		linkColor: '--cb-link-color',
		iconBackgroundColor: '--cb-icon-bg',
	};

	Object.keys( colors ).forEach( ( key ) => {
		if ( isColor( attributes[ key ] ) ) {
			style[ colors[ key ] ] = attributes[ key ];
		}
	} );

	if ( oneOf( attributes.titleFontWeight, FONT_WEIGHTS ) ) {
		style[ '--cb-title-weight' ] = attributes.titleFontWeight;
	}
	if ( oneOf( attributes.descriptionFontWeight, FONT_WEIGHTS ) ) {
		style[ '--cb-desc-weight' ] = attributes.descriptionFontWeight;
	}
	if ( oneOf( attributes.borderStyle, BORDER_STYLES ) ) {
		style[ '--cb-border-style' ] = attributes.borderStyle;
	}

	return style;
}

/**
 * Item class names.
 *
 * @param {Object} item Benefit item.
 * @return {string} Class names.
 */
export function getItemClassName( item ) {
	const classes = [ 'cb-benefit-item' ];

	if ( oneOf( item.iconAlign, ALIGNMENTS ) ) {
		classes.push( `cb-icon-align-${ item.iconAlign }` );
	}
	if ( oneOf( item.textAlign, ALIGNMENTS ) ) {
		classes.push( `cb-text-align-${ item.textAlign }` );
	}

	return classes.join( ' ' );
}

/**
 * Pixel size used for the width/height attributes of custom images
 * (prevents layout shift while the image loads).
 *
 * @param {Object} attributes Block attributes.
 * @return {number} Size in px.
 */
export function getIconPixelSize( attributes ) {
	if ( isNum( attributes.iconSize ) && attributes.iconSize > 0 ) {
		return Math.round( attributes.iconSize );
	}
	return DEFAULT_ICON_SIZE[ attributes.layout ] || 40;
}

/**
 * Returns a safe http(s)/relative URL or an empty string.
 *
 * @param {string} url Raw URL.
 * @return {string} URL.
 */
export function getSafeUrl( url ) {
	if ( typeof url !== 'string' ) {
		return '';
	}
	const trimmed = url.trim();
	if ( ! trimmed ) {
		return '';
	}
	if ( /^(https?:|mailto:|tel:|\/|#|\?)/i.test( trimmed ) ) {
		return trimmed;
	}
	if ( /^[a-z][a-z0-9+.-]*:/i.test( trimmed ) ) {
		return '';
	}
	return trimmed;
}

/**
 * Whether a RichText value contains content.
 *
 * @param {string} value RichText HTML string.
 * @return {boolean} Has content.
 */
export function hasText( value ) {
	return typeof value === 'string' && value.trim().length > 0;
}
