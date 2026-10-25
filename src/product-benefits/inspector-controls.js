/**
 * Sidebar (Inspector) controls for the Product Benefits block.
 */
import { createElement, Fragment } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import {
	InspectorControls,
	PanelColorSettings,
	ContrastChecker,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	RangeControl,
	ToggleControl,
	TextControl,
	Button,
	BaseControl,
	Notice,
} from '@wordpress/components';
import { prependHTTP } from '@wordpress/url';

import { ICONS, SvgIcon } from './icons';
import {
	BORDER_STYLES,
	DESKTOP_COLUMNS,
	FONT_WEIGHTS,
	MOBILE_COLUMNS,
	TABLET_COLUMNS,
} from './utils';

const columnOptions = ( list ) =>
	list.map( ( value ) => ( {
		label: String( value ),
		value: String( value ),
	} ) );

const ALIGN_OPTIONS = [
	{ label: __( 'Left', 'commerceboost-blocks' ), value: 'left' },
	{ label: __( 'Center', 'commerceboost-blocks' ), value: 'center' },
	{ label: __( 'Right', 'commerceboost-blocks' ), value: 'right' },
];

const WEIGHT_OPTIONS = [
	{ label: __( 'Default', 'commerceboost-blocks' ), value: '' },
	...FONT_WEIGHTS.map( ( weight ) => ( { label: weight, value: weight } ) ),
];

const BORDER_STYLE_LABELS = {
	none: __( 'None', 'commerceboost-blocks' ),
	solid: __( 'Solid', 'commerceboost-blocks' ),
	dashed: __( 'Dashed', 'commerceboost-blocks' ),
	dotted: __( 'Dotted', 'commerceboost-blocks' ),
	double: __( 'Double', 'commerceboost-blocks' ),
};

const stripTags = ( html ) =>
	( html || '' )
		.replace( /<[^>]*>/g, '' )
		.replace( /&nbsp;/g, ' ' )
		.trim();

/**
 * Accessible grid of built-in icons.
 *
 * @param {Object}   props          Props.
 * @param {string}   props.value    Selected key.
 * @param {Function} props.onChange Change handler.
 * @param {string}   props.id       Control id.
 * @return {Element} Icon picker.
 */
function IconPicker( { value, onChange, id } ) {
	return (
		<BaseControl
			id={ id }
			label={ __( 'Icon', 'commerceboost-blocks' ) }
			__nextHasNoMarginBottom
		>
			<div
				id={ id }
				className="cb-icon-picker"
				role="group"
				aria-label={ __( 'Choose an icon', 'commerceboost-blocks' ) }
			>
				{ Object.keys( ICONS ).map( ( key ) => (
					<Button
						key={ key }
						className="cb-icon-picker-button"
						label={ ICONS[ key ].label }
						showTooltip
						isPressed={ value === key }
						onClick={ () => onChange( key ) }
					>
						<SvgIcon name={ key } />
					</Button>
				) ) }
			</div>
		</BaseControl>
	);
}

/**
 * Settings panel for one benefit item.
 *
 * @param {Object}   props               Props.
 * @param {Object}   props.item          Benefit item.
 * @param {number}   props.index         Item index.
 * @param {number}   props.total         Number of items.
 * @param {boolean}  props.opened        Whether the panel is open.
 * @param {Function} props.onToggle      Toggle handler.
 * @param {string}   props.layout        Block layout.
 * @param {Function} props.updateItem    Update handler.
 * @param {Function} props.removeItem    Remove handler.
 * @param {Function} props.duplicateItem Duplicate handler.
 * @param {Function} props.moveItem      Move handler.
 * @return {Element} Panel.
 */
function ItemPanel( {
	item,
	index,
	total,
	opened,
	onToggle,
	layout,
	updateItem,
	removeItem,
	duplicateItem,
	moveItem,
} ) {
	const position = index + 1;
	const label =
		stripTags( item.title ) || __( '(Untitled)', 'commerceboost-blocks' );
	const update = ( changes ) => updateItem( index, changes );
	const isChecklist = 'checklist' === layout;

	return (
		<PanelBody
			title={ sprintf(
				/* translators: 1: benefit position, 2: benefit title. */
				__( '%1$d. %2$s', 'commerceboost-blocks' ),
				position,
				label
			) }
			opened={ opened }
			onToggle={ onToggle }
			className="cb-item-panel"
		>
			{ isChecklist ? (
				<Notice status="info" isDismissible={ false }>
					{ __(
						'The checklist layout always uses a checkmark icon. Switch layout to use a custom icon or image.',
						'commerceboost-blocks'
					) }
				</Notice>
			) : (
				<>
					{ ! item.mediaUrl && (
						<IconPicker
							id={ `cb-icon-picker-${ item.id || index }` }
							value={ item.icon }
							onChange={ ( icon ) => update( { icon } ) }
						/>
					) }

					<MediaUploadCheck>
						<BaseControl
							id={ `cb-media-${ item.id || index }` }
							label={ __(
								'Custom icon or image',
								'commerceboost-blocks'
							) }
							help={
								item.mediaUrl
									? undefined
									: __(
											'Optional. Replaces the built-in icon. Square SVG or PNG images work best.',
											'commerceboost-blocks'
									  )
							}
							__nextHasNoMarginBottom
						>
							{ item.mediaUrl && (
								<img
									className="cb-media-preview"
									src={ item.mediaUrl }
									alt=""
									width="48"
									height="48"
								/>
							) }
							<div className="cb-button-row">
								<MediaUpload
									allowedTypes={ [ 'image' ] }
									value={ item.mediaId }
									onSelect={ ( media ) =>
										update( {
											mediaId: media.id || 0,
											mediaUrl: media.url || '',
											mediaAlt: media.alt || '',
										} )
									}
									render={ ( { open } ) => (
										<Button
											variant="secondary"
											onClick={ open }
										>
											{ item.mediaUrl
												? __(
														'Replace image',
														'commerceboost-blocks'
												  )
												: __(
														'Upload / select image',
														'commerceboost-blocks'
												  ) }
										</Button>
									) }
								/>
								{ item.mediaUrl && (
									<Button
										variant="tertiary"
										isDestructive
										onClick={ () =>
											update( {
												mediaId: 0,
												mediaUrl: '',
												mediaAlt: '',
											} )
										}
									>
										{ __(
											'Remove image',
											'commerceboost-blocks'
										) }
									</Button>
								) }
							</div>
						</BaseControl>
					</MediaUploadCheck>

					{ item.mediaUrl && (
						<TextControl
							label={ __(
								'Image alternative text',
								'commerceboost-blocks'
							) }
							help={ __(
								'Describe the image if it adds meaning. Leave empty if it is purely decorative.',
								'commerceboost-blocks'
							) }
							value={ item.mediaAlt || '' }
							onChange={ ( mediaAlt ) => update( { mediaAlt } ) }
							__nextHasNoMarginBottom
							__next40pxDefaultSize
						/>
					) }
				</>
			) }

			<TextControl
				label={ __( 'Link URL (optional)', 'commerceboost-blocks' ) }
				type="url"
				value={ item.url || '' }
				placeholder="https://"
				onChange={ ( url ) => update( { url } ) }
				onBlur={ () => {
					if ( item.url && item.url.trim() ) {
						update( { url: prependHTTP( item.url.trim() ) } );
					}
				} }
				help={ __(
					'The benefit title becomes a link.',
					'commerceboost-blocks'
				) }
				__nextHasNoMarginBottom
				__next40pxDefaultSize
			/>

			{ !! item.url && (
				<ToggleControl
					label={ __(
						'Open link in a new tab',
						'commerceboost-blocks'
					) }
					checked={ !! item.opensInNewTab }
					onChange={ ( opensInNewTab ) =>
						update( { opensInNewTab } )
					}
					__nextHasNoMarginBottom
				/>
			) }

			<SelectControl
				label={ __( 'Icon alignment', 'commerceboost-blocks' ) }
				value={ item.iconAlign || '' }
				options={ [
					{
						label: __( 'Block default', 'commerceboost-blocks' ),
						value: '',
					},
					...ALIGN_OPTIONS,
				] }
				onChange={ ( iconAlign ) => update( { iconAlign } ) }
				__nextHasNoMarginBottom
				__next40pxDefaultSize
			/>

			<SelectControl
				label={ __( 'Text alignment', 'commerceboost-blocks' ) }
				value={ item.textAlign || '' }
				options={ [
					{
						label: __( 'Block default', 'commerceboost-blocks' ),
						value: '',
					},
					...ALIGN_OPTIONS,
				] }
				onChange={ ( textAlign ) => update( { textAlign } ) }
				__nextHasNoMarginBottom
				__next40pxDefaultSize
			/>

			<div
				className="cb-button-row cb-item-panel-actions"
				role="group"
				aria-label={ sprintf(
					/* translators: %d: benefit position. */
					__( 'Benefit %d actions', 'commerceboost-blocks' ),
					position
				) }
			>
				<Button
					variant="secondary"
					size="compact"
					onClick={ () => moveItem( index, -1 ) }
					aria-disabled={ index === 0 }
				>
					{ __( 'Move up', 'commerceboost-blocks' ) }
				</Button>
				<Button
					variant="secondary"
					size="compact"
					onClick={ () => moveItem( index, 1 ) }
					aria-disabled={ index === total - 1 }
				>
					{ __( 'Move down', 'commerceboost-blocks' ) }
				</Button>
				<Button
					variant="secondary"
					size="compact"
					onClick={ () => duplicateItem( index ) }
				>
					{ __( 'Duplicate', 'commerceboost-blocks' ) }
				</Button>
				<Button
					variant="secondary"
					size="compact"
					isDestructive
					onClick={ () => removeItem( index ) }
				>
					{ __( 'Remove', 'commerceboost-blocks' ) }
				</Button>
			</div>
		</PanelBody>
	);
}

export default function BenefitsInspectorControls( {
	attributes,
	setAttributes,
	activeIndex,
	setActiveIndex,
	updateItem,
	addItem,
	removeItem,
	duplicateItem,
	moveItem,
} ) {
	const {
		items,
		layout,
		columnsDesktop,
		columnsTablet,
		columnsMobile,
		gap,
		itemPadding,
		contentAlign,
		titleTag,
		itemBackgroundColor,
		itemTextColor,
		iconColor,
		borderColor,
		linkColor,
		titleFontSize,
		descriptionFontSize,
		titleFontWeight,
		descriptionFontWeight,
		titleLineHeight,
		descriptionLineHeight,
		borderWidth,
		borderRadius,
		borderStyle,
		hasShadow,
		shadowIntensity,
		showIcon,
		iconSize,
		hasIconBackground,
		iconBackgroundColor,
		iconBackgroundRadius,
		hideOnDesktop,
		hideOnTablet,
		hideOnMobile,
	} = attributes;

	const list = Array.isArray( items ) ? items : [];
	const set = ( key ) => ( value ) => setAttributes( { [ key ]: value } );
	const setNumber = ( key ) => ( value ) =>
		setAttributes( {
			[ key ]:
				value === undefined || value === ''
					? undefined
					: Number( value ),
		} );
	const setOptional = ( key ) => ( value ) =>
		setAttributes( { [ key ]: value ? value : undefined } );

	const colorSettings = [
		{
			value: itemBackgroundColor,
			onChange: set( 'itemBackgroundColor' ),
			label: __( 'Item background', 'commerceboost-blocks' ),
		},
		{
			value: itemTextColor,
			onChange: set( 'itemTextColor' ),
			label: __( 'Item text', 'commerceboost-blocks' ),
		},
		{
			value: iconColor,
			onChange: set( 'iconColor' ),
			label: __( 'Icon', 'commerceboost-blocks' ),
		},
		{
			value: borderColor,
			onChange: set( 'borderColor' ),
			label: __( 'Border', 'commerceboost-blocks' ),
		},
		{
			value: linkColor,
			onChange: set( 'linkColor' ),
			label: __( 'Link', 'commerceboost-blocks' ),
		},
	];

	if ( hasIconBackground ) {
		colorSettings.push( {
			value: iconBackgroundColor,
			onChange: set( 'iconBackgroundColor' ),
			label: __( 'Icon background', 'commerceboost-blocks' ),
		} );
	}

	return (
		<InspectorControls>
			<PanelBody
				title={ __( 'Benefits', 'commerceboost-blocks' ) }
				initialOpen
			>
				<p className="cb-panel-help">
					{ __(
						'Edit titles and descriptions directly in the block. Use the panels below for icons, links and order.',
						'commerceboost-blocks'
					) }
				</p>
				<Button variant="primary" icon="plus-alt2" onClick={ addItem }>
					{ __( 'Add benefit', 'commerceboost-blocks' ) }
				</Button>
			</PanelBody>

			{ list.map( ( item, index ) => (
				<ItemPanel
					key={ item.id || index }
					item={ item }
					index={ index }
					total={ list.length }
					layout={ layout }
					opened={ index === activeIndex }
					onToggle={ () =>
						setActiveIndex( index === activeIndex ? -1 : index )
					}
					updateItem={ updateItem }
					removeItem={ removeItem }
					duplicateItem={ duplicateItem }
					moveItem={ moveItem }
				/>
			) ) }

			<PanelBody
				title={ __( 'Layout', 'commerceboost-blocks' ) }
				initialOpen={ false }
			>
				<SelectControl
					label={ __( 'Layout type', 'commerceboost-blocks' ) }
					value={ layout }
					options={ [
						{
							label: __( 'Grid', 'commerceboost-blocks' ),
							value: 'grid',
						},
						{
							label: __( 'Horizontal', 'commerceboost-blocks' ),
							value: 'horizontal',
						},
						{
							label: __( 'Checklist', 'commerceboost-blocks' ),
							value: 'checklist',
						},
						{
							label: __( 'Trust badges', 'commerceboost-blocks' ),
							value: 'badges',
						},
					] }
					onChange={ set( 'layout' ) }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				<SelectControl
					label={ __( 'Desktop columns', 'commerceboost-blocks' ) }
					value={ String( columnsDesktop ) }
					options={ columnOptions( DESKTOP_COLUMNS ) }
					onChange={ setNumber( 'columnsDesktop' ) }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				<SelectControl
					label={ __( 'Tablet columns', 'commerceboost-blocks' ) }
					value={ String( columnsTablet ) }
					options={ columnOptions( TABLET_COLUMNS ) }
					onChange={ setNumber( 'columnsTablet' ) }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				<SelectControl
					label={ __( 'Mobile columns', 'commerceboost-blocks' ) }
					value={ String( columnsMobile ) }
					options={ columnOptions( MOBILE_COLUMNS ) }
					onChange={ setNumber( 'columnsMobile' ) }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				<RangeControl
					label={ __(
						'Gap between items (px)',
						'commerceboost-blocks'
					) }
					value={ gap }
					onChange={ setNumber( 'gap' ) }
					min={ 0 }
					max={ 80 }
					allowReset
					initialPosition={ 24 }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				<RangeControl
					label={ __( 'Item padding (px)', 'commerceboost-blocks' ) }
					value={ itemPadding }
					onChange={ setNumber( 'itemPadding' ) }
					min={ 0 }
					max={ 80 }
					allowReset
					initialPosition={ 24 }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				<SelectControl
					label={ __( 'Content alignment', 'commerceboost-blocks' ) }
					value={ contentAlign }
					options={ [
						{
							label: __(
								'Layout default',
								'commerceboost-blocks'
							),
							value: '',
						},
						...ALIGN_OPTIONS,
					] }
					onChange={ set( 'contentAlign' ) }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				<SelectControl
					label={ __( 'Title HTML tag', 'commerceboost-blocks' ) }
					value={ titleTag }
					options={ [
						{ label: 'H2', value: 'h2' },
						{ label: 'H3', value: 'h3' },
						{ label: 'H4', value: 'h4' },
						{ label: 'H5', value: 'h5' },
						{ label: 'H6', value: 'h6' },
						{
							label: __(
								'Paragraph (no heading)',
								'commerceboost-blocks'
							),
							value: 'p',
						},
					] }
					onChange={ set( 'titleTag' ) }
					help={ __(
						'Pick a heading one level below the section heading above this block, or "Paragraph" for short trust badges, to keep the page outline logical.',
						'commerceboost-blocks'
					) }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
			</PanelBody>

			<PanelColorSettings
				title={ __( 'Item colors', 'commerceboost-blocks' ) }
				initialOpen={ false }
				colorSettings={ colorSettings }
			>
				<ContrastChecker
					backgroundColor={ itemBackgroundColor }
					textColor={ itemTextColor }
				/>
			</PanelColorSettings>

			<PanelBody
				title={ __( 'Item typography', 'commerceboost-blocks' ) }
				initialOpen={ false }
			>
				<RangeControl
					label={ __(
						'Title font size (px)',
						'commerceboost-blocks'
					) }
					value={ titleFontSize }
					onChange={ setNumber( 'titleFontSize' ) }
					min={ 10 }
					max={ 48 }
					allowReset
					initialPosition={ 18 }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				<SelectControl
					label={ __( 'Title font weight', 'commerceboost-blocks' ) }
					value={ titleFontWeight || '' }
					options={ WEIGHT_OPTIONS }
					onChange={ setOptional( 'titleFontWeight' ) }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				<RangeControl
					label={ __( 'Title line height', 'commerceboost-blocks' ) }
					value={ titleLineHeight }
					onChange={ setNumber( 'titleLineHeight' ) }
					min={ 0.8 }
					max={ 3 }
					step={ 0.1 }
					allowReset
					initialPosition={ 1.3 }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				<RangeControl
					label={ __(
						'Description font size (px)',
						'commerceboost-blocks'
					) }
					value={ descriptionFontSize }
					onChange={ setNumber( 'descriptionFontSize' ) }
					min={ 10 }
					max={ 36 }
					allowReset
					initialPosition={ 15 }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				<SelectControl
					label={ __(
						'Description font weight',
						'commerceboost-blocks'
					) }
					value={ descriptionFontWeight || '' }
					options={ WEIGHT_OPTIONS }
					onChange={ setOptional( 'descriptionFontWeight' ) }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				<RangeControl
					label={ __(
						'Description line height',
						'commerceboost-blocks'
					) }
					value={ descriptionLineHeight }
					onChange={ setNumber( 'descriptionLineHeight' ) }
					min={ 0.8 }
					max={ 3 }
					step={ 0.1 }
					allowReset
					initialPosition={ 1.6 }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
			</PanelBody>

			<PanelBody
				title={ __( 'Border & shape', 'commerceboost-blocks' ) }
				initialOpen={ false }
			>
				<RangeControl
					label={ __( 'Border width (px)', 'commerceboost-blocks' ) }
					value={ borderWidth }
					onChange={ setNumber( 'borderWidth' ) }
					min={ 0 }
					max={ 10 }
					allowReset
					initialPosition={ 1 }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				<SelectControl
					label={ __( 'Border style', 'commerceboost-blocks' ) }
					value={ borderStyle || '' }
					options={ [
						{
							label: __( 'Default', 'commerceboost-blocks' ),
							value: '',
						},
						...BORDER_STYLES.map( ( value ) => ( {
							label: BORDER_STYLE_LABELS[ value ],
							value,
						} ) ),
					] }
					onChange={ setOptional( 'borderStyle' ) }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				<RangeControl
					label={ __( 'Border radius (px)', 'commerceboost-blocks' ) }
					value={ borderRadius }
					onChange={ setNumber( 'borderRadius' ) }
					min={ 0 }
					max={ 60 }
					allowReset
					initialPosition={ 8 }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				<ToggleControl
					label={ __( 'Box shadow', 'commerceboost-blocks' ) }
					checked={ !! hasShadow }
					onChange={ set( 'hasShadow' ) }
					__nextHasNoMarginBottom
				/>
				{ hasShadow && (
					<SelectControl
						label={ __(
							'Shadow intensity',
							'commerceboost-blocks'
						) }
						value={ shadowIntensity }
						options={ [
							{
								label: __( 'Small', 'commerceboost-blocks' ),
								value: 'small',
							},
							{
								label: __( 'Medium', 'commerceboost-blocks' ),
								value: 'medium',
							},
							{
								label: __( 'Large', 'commerceboost-blocks' ),
								value: 'large',
							},
						] }
						onChange={ set( 'shadowIntensity' ) }
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
				) }
			</PanelBody>

			<PanelBody
				title={ __( 'Icon', 'commerceboost-blocks' ) }
				initialOpen={ false }
			>
				<ToggleControl
					label={ __( 'Show icons', 'commerceboost-blocks' ) }
					checked={ false !== showIcon }
					onChange={ set( 'showIcon' ) }
					__nextHasNoMarginBottom
				/>
				{ false !== showIcon && (
					<>
						<RangeControl
							label={ __(
								'Icon size (px)',
								'commerceboost-blocks'
							) }
							value={ iconSize }
							onChange={ setNumber( 'iconSize' ) }
							min={ 12 }
							max={ 120 }
							allowReset
							initialPosition={
								'checklist' === layout || 'badges' === layout
									? 24
									: 40
							}
							__nextHasNoMarginBottom
							__next40pxDefaultSize
						/>
						<ToggleControl
							label={ __(
								'Icon background',
								'commerceboost-blocks'
							) }
							checked={ !! hasIconBackground }
							onChange={ set( 'hasIconBackground' ) }
							help={ __(
								'Set its color in "Item colors".',
								'commerceboost-blocks'
							) }
							__nextHasNoMarginBottom
						/>
						{ hasIconBackground && (
							<RangeControl
								label={ __(
									'Icon background radius (%)',
									'commerceboost-blocks'
								) }
								value={ iconBackgroundRadius }
								onChange={ setNumber( 'iconBackgroundRadius' ) }
								min={ 0 }
								max={ 50 }
								allowReset
								initialPosition={ 50 }
								__nextHasNoMarginBottom
								__next40pxDefaultSize
							/>
						) }
					</>
				) }
			</PanelBody>

			<PanelBody
				title={ __( 'Visibility', 'commerceboost-blocks' ) }
				initialOpen={ false }
			>
				<ToggleControl
					label={ __( 'Hide on desktop', 'commerceboost-blocks' ) }
					help={ __(
						'Screens 782px and wider.',
						'commerceboost-blocks'
					) }
					checked={ !! hideOnDesktop }
					onChange={ set( 'hideOnDesktop' ) }
					__nextHasNoMarginBottom
				/>
				<ToggleControl
					label={ __( 'Hide on tablet', 'commerceboost-blocks' ) }
					help={ __(
						'Screens from 600px to 781px.',
						'commerceboost-blocks'
					) }
					checked={ !! hideOnTablet }
					onChange={ set( 'hideOnTablet' ) }
					__nextHasNoMarginBottom
				/>
				<ToggleControl
					label={ __( 'Hide on mobile', 'commerceboost-blocks' ) }
					help={ __(
						'Screens narrower than 600px.',
						'commerceboost-blocks'
					) }
					checked={ !! hideOnMobile }
					onChange={ set( 'hideOnMobile' ) }
					__nextHasNoMarginBottom
				/>
				{ ( hideOnDesktop || hideOnTablet || hideOnMobile ) && (
					<Notice status="info" isDismissible={ false }>
						{ __(
							'Hidden blocks stay visible but faded in the editor so you can keep editing them.',
							'commerceboost-blocks'
						) }
					</Notice>
				) }
			</PanelBody>
		</InspectorControls>
	);
}
