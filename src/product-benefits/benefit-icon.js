/**
 * Icon renderer shared by edit() and save().
 */
import { createElement } from '@wordpress/element';
import { SvgIcon } from './icons';
import { getIconPixelSize } from './utils';

/**
 * Renders the visual for a single benefit item.
 *
 * - Checklist layout always uses a checkmark.
 * - A custom image replaces the SVG icon when set; its alt text is kept.
 * - SVG icons are decorative and hidden from assistive technology.
 *
 * @param {Object} props            Component props.
 * @param {Object} props.item       Benefit item.
 * @param {Object} props.attributes Block attributes.
 * @return {Element|null} Icon element.
 */
export default function BenefitIcon( { item, attributes } ) {
	if ( false === attributes.showIcon ) {
		return null;
	}

	if ( 'checklist' === attributes.layout ) {
		return (
			<span className="cb-benefit-icon" aria-hidden="true">
				<SvgIcon name="check" />
			</span>
		);
	}

	if ( item.mediaUrl ) {
		const size = getIconPixelSize( attributes );

		return (
			<span className="cb-benefit-icon cb-benefit-icon--image">
				<img
					src={ item.mediaUrl }
					alt={ item.mediaAlt || '' }
					width={ size }
					height={ size }
					loading="lazy"
					decoding="async"
				/>
			</span>
		);
	}

	return (
		<span className="cb-benefit-icon" aria-hidden="true">
			<SvgIcon name={ item.icon } />
		</span>
	);
}
