<?php
/**
 * Uninstall handler.
 *
 * Removes the plugin's settings options. Content the store owner entered
 * (block content, product FAQs/videos/bundle contents, global FAQ posts) is
 * intentionally kept so site content is never destroyed.
 *
 * @package WBD_Product_Benefits_Blocks
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Deletes the plugin options on the current site.
 */
function wbd_pbb_delete_options() {
	$options = array(
		'wbd_pbb_sticky_atc',
		'wbd_pbb_modules',
		'wbd_pbb_product_faqs',
		'wbd_pbb_variation_images',
		'wbd_pbb_product_videos',
		'wbd_pbb_bundles',
		'wbd_pbb_composite',
		'wbd_pbb_quick_view',
	);
	foreach ( $options as $option ) {
		delete_option( $option );
	}
}

if ( is_multisite() ) {
	$wbd_pbb_site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);
	foreach ( $wbd_pbb_site_ids as $wbd_pbb_site_id ) {
		switch_to_blog( $wbd_pbb_site_id );
		wbd_pbb_delete_options();
		restore_current_blog();
	}
} else {
	wbd_pbb_delete_options();
}
