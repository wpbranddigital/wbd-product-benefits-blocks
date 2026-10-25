/**
 * Saved (frontend) markup for the Product Benefits block.
 *
 * Static, semantic and JavaScript-free: a role="list" <ul> of <li> items.
 * Only user-customised values are written as CSS custom properties.
 */
import { createElement } from '@wordpress/element';
import { useBlockProps, RichText } from '@wordpress/block-editor';

import BenefitIcon from './benefit-icon';
import {
	getBlockClassName,
	getStyleVars,
	getItemClassName,
	getSafeUrl,
	hasText,
	TITLE_TAGS,
} from './utils';

export default function save( { attributes } ) {
	const items = Array.isArray( attributes.items ) ? attributes.items : [];
	const TitleTag = TITLE_TAGS.includes( attributes.titleTag )
		? attributes.titleTag
		: 'h3';

	const blockProps = useBlockProps.save( {
		className: getBlockClassName( attributes ),
		style: getStyleVars( attributes ),
	} );

	return (
		// Explicit role keeps list semantics in Safari/VoiceOver when list-style is none.
		// eslint-disable-next-line jsx-a11y/no-redundant-roles
		<ul { ...blockProps } role="list">
			{ items.map( ( item, index ) => {
				const url = getSafeUrl( item.url );
				const hasTitle = hasText( item.title );
				const hasDescription = hasText( item.description );

				return (
					<li
						key={ item.id || index }
						className={ getItemClassName( item ) }
					>
						<BenefitIcon item={ item } attributes={ attributes } />
						{ ( hasTitle || hasDescription ) && (
							<div className="cb-benefit-content">
								{ hasTitle && (
									<TitleTag className="cb-benefit-title">
										{ url ? (
											<a
												href={ url }
												target={
													item.opensInNewTab
														? '_blank'
														: undefined
												}
												rel={
													item.opensInNewTab
														? 'noopener noreferrer'
														: undefined
												}
											>
												<RichText.Content
													value={ item.title }
												/>
											</a>
										) : (
											<RichText.Content
												value={ item.title }
											/>
										) }
									</TitleTag>
								) }
								{ hasDescription && (
									<RichText.Content
										tagName="p"
										className="cb-benefit-description"
										value={ item.description }
									/>
								) }
							</div>
						) }
					</li>
				);
			} ) }
		</ul>
	);
}
