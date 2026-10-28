/**
 * Editor component for the Product Benefits block.
 */
import { __, sprintf } from '@wordpress/i18n';
import {
	createElement,
	Fragment,
	useEffect,
	useState,
} from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import {
	useBlockProps,
	RichText,
	BlockControls,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton, Button } from '@wordpress/components';

import BenefitIcon from './benefit-icon';
import BenefitsInspectorControls from './inspector-controls';
import {
	createItem,
	createItemId,
	getBlockClassName,
	getDefaultItems,
	getItemClassName,
	getStyleVars,
	TITLE_TAGS,
} from './utils';

export default function Edit( { attributes, setAttributes, isSelected } ) {
	const { items, titleTag } = attributes;
	const [ activeIndex, setActiveIndex ] = useState( 0 );
	const { __unstableMarkNextChangeAsNotPersistent: markNotPersistent } =
		useDispatch( blockEditorStore );

	// Insert translated default items once, when the block is first added.
	useEffect( () => {
		if ( undefined === items ) {
			if ( typeof markNotPersistent === 'function' ) {
				markNotPersistent();
			}
			setAttributes( { items: getDefaultItems() } );
		}
	}, [ items, markNotPersistent, setAttributes ] );

	const list = Array.isArray( items ) ? items : [];
	const TitleTag = TITLE_TAGS.includes( titleTag ) ? titleTag : 'h3';

	const setItems = ( next ) => setAttributes( { items: next } );

	const updateItem = ( index, changes ) => {
		setItems(
			list.map( ( item, i ) =>
				i === index ? { ...item, ...changes } : item
			)
		);
	};

	const addItem = () => {
		const next = [
			...list,
			createItem( {
				title: __( 'New benefit', 'wbd-product-benefits-blocks' ),
			} ),
		];
		setItems( next );
		setActiveIndex( next.length - 1 );
	};

	const removeItem = ( index ) => {
		const next = list.filter( ( item, i ) => i !== index );
		setItems( next );
		setActiveIndex( Math.max( 0, Math.min( index, next.length - 1 ) ) );
	};

	const duplicateItem = ( index ) => {
		const next = [ ...list ];
		next.splice( index + 1, 0, { ...list[ index ], id: createItemId() } );
		setItems( next );
		setActiveIndex( index + 1 );
	};

	const moveItem = ( index, direction ) => {
		const target = index + direction;
		if ( target < 0 || target >= list.length ) {
			return;
		}
		const next = [ ...list ];
		[ next[ index ], next[ target ] ] = [ next[ target ], next[ index ] ];
		setItems( next );
		setActiveIndex( target );
	};

	const itemActions = {
		updateItem,
		addItem,
		removeItem,
		duplicateItem,
		moveItem,
	};

	const blockProps = useBlockProps( {
		className: getBlockClassName( attributes ),
		style: getStyleVars( attributes ),
	} );

	return (
		<>
			<BlockControls group="block">
				<ToolbarGroup>
					<ToolbarButton
						icon="plus-alt2"
						label={ __( 'Add benefit', 'wbd-product-benefits-blocks' ) }
						onClick={ addItem }
					/>
				</ToolbarGroup>
			</BlockControls>

			<BenefitsInspectorControls
				attributes={ attributes }
				setAttributes={ setAttributes }
				activeIndex={ activeIndex }
				setActiveIndex={ setActiveIndex }
				{ ...itemActions }
			/>

			{ /* Explicit role keeps list semantics in Safari/VoiceOver when list-style is none. */ }
			{ /* eslint-disable-next-line jsx-a11y/no-redundant-roles */ }
			<ul { ...blockProps } role="list">
				{ list.length === 0 && (
					<li className="cb-benefit-item cb-benefit-empty">
						<p>
							{ __(
								'This block has no benefits yet.',
								'wbd-product-benefits-blocks'
							) }
						</p>
						<Button
							variant="secondary"
							icon="plus-alt2"
							onClick={ addItem }
						>
							{ __( 'Add benefit', 'wbd-product-benefits-blocks' ) }
						</Button>
					</li>
				) }

				{ list.map( ( item, index ) => {
					const isActive = isSelected && index === activeIndex;
					const position = index + 1;

					return (
						<li
							key={ item.id || index }
							className={
								getItemClassName( item ) +
								( isActive ? ' is-cb-active' : '' )
							}
							onFocus={ () => setActiveIndex( index ) }
						>
							{ isActive && (
								<div
									className="cb-item-actions"
									role="toolbar"
									aria-label={ sprintf(
										/* translators: %d: benefit position. */
										__(
											'Benefit %d actions',
											'wbd-product-benefits-blocks'
										),
										position
									) }
								>
									<Button
										size="small"
										icon="arrow-up-alt2"
										label={ __(
											'Move up',
											'wbd-product-benefits-blocks'
										) }
										onClick={ () => moveItem( index, -1 ) }
										aria-disabled={ index === 0 }
									/>
									<Button
										size="small"
										icon="arrow-down-alt2"
										label={ __(
											'Move down',
											'wbd-product-benefits-blocks'
										) }
										onClick={ () => moveItem( index, 1 ) }
										aria-disabled={
											index === list.length - 1
										}
									/>
									<Button
										size="small"
										icon="admin-page"
										label={ __(
											'Duplicate',
											'wbd-product-benefits-blocks'
										) }
										onClick={ () => duplicateItem( index ) }
									/>
									<Button
										size="small"
										icon="trash"
										label={ __(
											'Remove',
											'wbd-product-benefits-blocks'
										) }
										onClick={ () => removeItem( index ) }
										isDestructive
									/>
								</div>
							) }

							<BenefitIcon
								item={ item }
								attributes={ attributes }
							/>

							<div className="cb-benefit-content">
								<RichText
									tagName={ TitleTag }
									className={
										'cb-benefit-title' +
										( item.url ? ' cb-is-linked' : '' )
									}
									value={ item.title || '' }
									onChange={ ( value ) =>
										updateItem( index, { title: value } )
									}
									placeholder={ __(
										'Benefit title…',
										'wbd-product-benefits-blocks'
									) }
									allowedFormats={ [
										'core/bold',
										'core/italic',
									] }
									aria-label={ sprintf(
										/* translators: %d: benefit position. */
										__(
											'Benefit %d title',
											'wbd-product-benefits-blocks'
										),
										position
									) }
								/>
								<RichText
									tagName="p"
									className="cb-benefit-description"
									value={ item.description || '' }
									onChange={ ( value ) =>
										updateItem( index, {
											description: value,
										} )
									}
									placeholder={ __(
										'Benefit description…',
										'wbd-product-benefits-blocks'
									) }
									allowedFormats={ [
										'core/bold',
										'core/italic',
										'core/link',
									] }
									aria-label={ sprintf(
										/* translators: %d: benefit position. */
										__(
											'Benefit %d description',
											'wbd-product-benefits-blocks'
										),
										position
									) }
								/>
							</div>
						</li>
					);
				} ) }
			</ul>
		</>
	);
}
