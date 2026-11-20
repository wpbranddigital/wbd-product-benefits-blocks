<?php
/**
 * Base class for WBD Woo Kit modules.
 *
 * A module declares its settings as a field schema; the hub renders the form,
 * sanitizes input by field type and stores one option per module.
 *
 * @package WBD_Product_Benefits_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Module base.
 */
abstract class WBD_PBB_Kit_Module {

	/**
	 * Request cache of settings.
	 *
	 * @var array|null
	 */
	private $settings_cache = null;

	/**
	 * Unique slug (lowercase, underscores).
	 *
	 * @return string
	 */
	abstract public function slug();

	/**
	 * Human title.
	 *
	 * @return string
	 */
	abstract public function title();

	/**
	 * One-line description for the Modules screen.
	 *
	 * @return string
	 */
	abstract public function description();

	/**
	 * Boots the module (only called when enabled and WooCommerce is active).
	 */
	abstract public function init();

	/**
	 * Whether the module is on before the user changes anything.
	 *
	 * @return bool
	 */
	public function default_enabled() {
		return true;
	}

	/**
	 * Settings schema: key => array( type, label, default, options?, help?, min?, max? ).
	 * Types: checkbox, select, text, number, color, textarea.
	 *
	 * @return array
	 */
	public function fields() {
		return array();
	}

	/**
	 * "How to use" steps shown next to the settings.
	 *
	 * @return string[]
	 */
	public function help() {
		return array();
	}

	/**
	 * Option name.
	 *
	 * @return string
	 */
	public function option_name() {
		return 'wbd_pbb_' . $this->slug();
	}

	/**
	 * Settings group.
	 *
	 * @return string
	 */
	public function group() {
		return 'wbd_pbb_' . $this->slug() . '_group';
	}

	/**
	 * Defaults from the schema.
	 *
	 * @return array
	 */
	public function defaults() {
		$defaults = array();
		foreach ( $this->fields() as $key => $field ) {
			$defaults[ $key ] = isset( $field['default'] ) ? $field['default'] : '';
		}
		return $defaults;
	}

	/**
	 * Saved settings merged with defaults.
	 *
	 * @return array
	 */
	public function get_settings() {
		if ( null === $this->settings_cache ) {
			$saved                = get_option( $this->option_name(), null );
			$this->settings_cache = is_array( $saved ) ? $this->sanitize( array_merge( $this->defaults(), $saved ) ) : $this->defaults();
		}
		return $this->settings_cache;
	}

	/**
	 * One setting.
	 *
	 * @param string $key Key.
	 * @return mixed
	 */
	public function get( $key ) {
		$settings = $this->get_settings();
		return isset( $settings[ $key ] ) ? $settings[ $key ] : null;
	}

	/**
	 * Clears the request cache.
	 */
	public function flush() {
		$this->settings_cache = null;
	}

	/**
	 * Sanitizes input against the schema. Unknown keys are dropped.
	 *
	 * @param mixed $input Raw input.
	 * @return array
	 */
	public function sanitize( $input ) {
		$input  = is_array( $input ) ? $input : array();
		$output = array();

		foreach ( $this->fields() as $key => $field ) {
			$default = isset( $field['default'] ) ? $field['default'] : '';
			$value   = isset( $input[ $key ] ) ? $input[ $key ] : null;

			switch ( $field['type'] ) {
				case 'checkbox':
					$output[ $key ] = empty( $value ) ? 0 : 1;
					break;
				case 'select':
					$value          = is_scalar( $value ) ? (string) $value : '';
					$output[ $key ] = array_key_exists( $value, $field['options'] ) ? $value : $default;
					break;
				case 'number':
					if ( null === $value || '' === $value || ! is_numeric( $value ) ) {
						$output[ $key ] = $default;
						break;
					}
					$number = (int) $value;
					if ( isset( $field['min'] ) ) {
						$number = max( (int) $field['min'], $number );
					}
					if ( isset( $field['max'] ) ) {
						$number = min( (int) $field['max'], $number );
					}
					$output[ $key ] = $number;
					break;
				case 'color':
					$color          = is_string( $value ) ? sanitize_hex_color( $value ) : '';
					$output[ $key ] = $color ? $color : '';
					break;
				case 'textarea':
					$output[ $key ] = is_string( $value ) ? sanitize_textarea_field( $value ) : $default;
					break;
				default:
					$text           = is_scalar( $value ) ? sanitize_text_field( (string) $value ) : $default;
					$output[ $key ] = function_exists( 'mb_substr' ) ? mb_substr( $text, 0, 200 ) : substr( $text, 0, 200 );
			}
		}

		return $output;
	}
}
