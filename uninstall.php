<?php
/**
 * Uninstall handler.
 *
 * WBD Product Benefits Blocks stores no options, transients, custom tables or user
 * meta. Block content lives inside post content and intentionally remains
 * after uninstall so site content is never destroyed.
 *
 * @package WBD_Product_Benefits_Blocks
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
