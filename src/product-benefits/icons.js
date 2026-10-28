/**
 * Built-in inline SVG icon set for the Product Benefits block.
 *
 * Every icon is drawn on a 24×24 grid with a 2px round stroke so the set
 * looks consistent at any size and inherits `currentColor`.
 * Shapes are adapted from Feather Icons (MIT License, https://feathericons.com).
 */
import { createElement } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Icon definitions: key => { label, shapes }.
 * Each shape is [ tagName, attributes ].
 */
export const ICONS = {
	truck: {
		label: __( 'Truck / shipping', 'wbd-product-benefits-blocks' ),
		shapes: [
			[ 'rect', { x: '1', y: '3', width: '15', height: '13' } ],
			[ 'polygon', { points: '16 8 20 8 23 11 23 16 16 16 16 8' } ],
			[ 'circle', { cx: '5.5', cy: '18.5', r: '2.5' } ],
			[ 'circle', { cx: '18.5', cy: '18.5', r: '2.5' } ],
		],
	},
	package: {
		label: __( 'Package', 'wbd-product-benefits-blocks' ),
		shapes: [
			[
				'path',
				{
					d: 'M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z',
				},
			],
			[ 'polyline', { points: '3.27 6.96 12 12.01 20.73 6.96' } ],
			[ 'line', { x1: '12', y1: '22.08', x2: '12', y2: '12' } ],
		],
	},
	shield: {
		label: __( 'Shield', 'wbd-product-benefits-blocks' ),
		shapes: [
			[ 'path', { d: 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z' } ],
			[ 'polyline', { points: '9 12 11 14 15 10' } ],
		],
	},
	lock: {
		label: __( 'Lock / secure', 'wbd-product-benefits-blocks' ),
		shapes: [
			[
				'rect',
				{
					x: '3',
					y: '11',
					width: '18',
					height: '11',
					rx: '2',
					ry: '2',
				},
			],
			[ 'path', { d: 'M7 11V7a5 5 0 0 1 10 0v4' } ],
		],
	},
	refresh: {
		label: __( 'Return / refresh', 'wbd-product-benefits-blocks' ),
		shapes: [
			[ 'polyline', { points: '1 4 1 10 7 10' } ],
			[ 'path', { d: 'M3.51 15a9 9 0 1 0 2.13-9.36L1 10' } ],
		],
	},
	headset: {
		label: __( 'Headset / support', 'wbd-product-benefits-blocks' ),
		shapes: [
			[ 'path', { d: 'M3 18v-6a9 9 0 0 1 18 0v6' } ],
			[
				'path',
				{
					d: 'M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z',
				},
			],
		],
	},
	'credit-card': {
		label: __( 'Credit card', 'wbd-product-benefits-blocks' ),
		shapes: [
			[
				'rect',
				{ x: '1', y: '4', width: '22', height: '16', rx: '2', ry: '2' },
			],
			[ 'line', { x1: '1', y1: '10', x2: '23', y2: '10' } ],
		],
	},
	'check-circle': {
		label: __( 'Check circle', 'wbd-product-benefits-blocks' ),
		shapes: [
			[ 'path', { d: 'M22 11.08V12a10 10 0 1 1-5.93-9.14' } ],
			[ 'polyline', { points: '22 4 12 14.01 9 11.01' } ],
		],
	},
	check: {
		label: __( 'Checkmark', 'wbd-product-benefits-blocks' ),
		shapes: [ [ 'polyline', { points: '20 6 9 17 4 12' } ] ],
	},
	award: {
		label: __( 'Award / quality', 'wbd-product-benefits-blocks' ),
		shapes: [
			[ 'circle', { cx: '12', cy: '8', r: '7' } ],
			[
				'polyline',
				{ points: '8.21 13.89 7 23 12 20 17 23 15.79 13.88' },
			],
		],
	},
	star: {
		label: __( 'Star', 'wbd-product-benefits-blocks' ),
		shapes: [
			[
				'polygon',
				{
					points: '12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2',
				},
			],
		],
	},
	'thumbs-up': {
		label: __( 'Thumbs up', 'wbd-product-benefits-blocks' ),
		shapes: [
			[
				'path',
				{
					d: 'M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3',
				},
			],
		],
	},
	gift: {
		label: __( 'Gift', 'wbd-product-benefits-blocks' ),
		shapes: [
			[ 'polyline', { points: '20 12 20 22 4 22 4 12' } ],
			[ 'rect', { x: '2', y: '7', width: '20', height: '5' } ],
			[ 'line', { x1: '12', y1: '22', x2: '12', y2: '7' } ],
			[ 'path', { d: 'M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z' } ],
			[ 'path', { d: 'M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z' } ],
		],
	},
	tag: {
		label: __( 'Price tag', 'wbd-product-benefits-blocks' ),
		shapes: [
			[
				'path',
				{
					d: 'M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z',
				},
			],
			[ 'line', { x1: '7', y1: '7', x2: '7.01', y2: '7' } ],
		],
	},
	clock: {
		label: __( 'Clock', 'wbd-product-benefits-blocks' ),
		shapes: [
			[ 'circle', { cx: '12', cy: '12', r: '10' } ],
			[ 'polyline', { points: '12 6 12 12 16 14' } ],
		],
	},
	zap: {
		label: __( 'Lightning / fast', 'wbd-product-benefits-blocks' ),
		shapes: [
			[ 'polygon', { points: '13 2 3 14 12 14 11 22 21 10 12 10 13 2' } ],
		],
	},
	heart: {
		label: __( 'Heart', 'wbd-product-benefits-blocks' ),
		shapes: [
			[
				'path',
				{
					d: 'M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z',
				},
			],
		],
	},
	globe: {
		label: __( 'Globe / worldwide', 'wbd-product-benefits-blocks' ),
		shapes: [
			[ 'circle', { cx: '12', cy: '12', r: '10' } ],
			[ 'line', { x1: '2', y1: '12', x2: '22', y2: '12' } ],
			[
				'path',
				{
					d: 'M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z',
				},
			],
		],
	},
};

export const DEFAULT_ICON = 'check-circle';

/**
 * Renders an icon as an inline, decorative SVG element.
 * Output is deterministic so it is safe to use in save().
 *
 * @param {Object} props      Component props.
 * @param {string} props.name Icon key.
 * @return {Element} SVG element.
 */
export function SvgIcon( { name } ) {
	const icon = ICONS[ name ] || ICONS[ DEFAULT_ICON ];

	return (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 24 24"
			fill="none"
			stroke="currentColor"
			strokeWidth="2"
			strokeLinecap="round"
			strokeLinejoin="round"
			aria-hidden="true"
			focusable="false"
		>
			{ icon.shapes.map( ( [ Tag, attrs ], index ) => (
				<Tag key={ index } { ...attrs } />
			) ) }
		</svg>
	);
}
