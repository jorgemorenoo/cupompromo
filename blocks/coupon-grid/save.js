/**
 * WordPress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * Save component for the coupon grid block
 */
export default function save({ attributes }) {
    const {
        columns = 3,
        limit = 12,
        store_id = 0,
        category_id = 0,
        coupon_type = '',
        orderby = 'created_at',
        order = 'DESC',
        show_filters = true,
        show_pagination = true,
        featured_only = false,
        card_style = 'default',
        align = '',
        backgroundColor,
        textColor,
        gradient,
        style
    } = attributes;

    const blockProps = useBlockProps.save({
        className: `cupompromo-coupon-grid-block align${align}`,
        style: {
            backgroundColor,
            color: textColor,
            ...style
        }
    });

    // Build shortcode attributes
    const shortcodeAttrs = [
        `columns="${columns}"`,
        `limit="${limit}"`,
        `card_style="${card_style}"`
    ];

    if (store_id > 0) {
        shortcodeAttrs.push(`store_id="${store_id}"`);
    }

    if (category_id > 0) {
        shortcodeAttrs.push(`category_id="${category_id}"`);
    }

    if (coupon_type) {
        shortcodeAttrs.push(`coupon_type="${coupon_type}"`);
    }

    if (orderby !== 'created_at') {
        shortcodeAttrs.push(`orderby="${orderby}"`);
    }

    if (order !== 'DESC') {
        shortcodeAttrs.push(`order="${order}"`);
    }

    if (!show_filters) {
        shortcodeAttrs.push('show_filters="false"');
    }

    if (!show_pagination) {
        shortcodeAttrs.push('show_pagination="false"');
    }

    if (featured_only) {
        shortcodeAttrs.push('featured_only="true"');
    }

    const shortcode = `[cupompromo_coupon_grid ${shortcodeAttrs.join(' ')}]`;

    return (
        <div {...blockProps}>
            <div 
                className="cupompromo-coupon-grid"
                style={{ '--columns': columns }}
            >
                {shortcode}
            </div>
        </div>
    );
} 