<?php
/**
 * Uninstall handler.
 *
 * CommerceBoost Blocks stores no options, transients, custom tables or user
 * meta. Block content lives inside post content and intentionally remains
 * after uninstall so site content is never destroyed.
 *
 * @package CommerceBoost_Blocks
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
