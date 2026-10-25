<?php
/**
 * Flipbook shortcode.
 *
 * @package WP_Flipbook
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers [flipbook id="123"].
 */
class WP_Flipbook_Shortcode {

	/**
	 * Hook shortcode registration.
	 *
	 * Role:
	 * Registers the flipbook shortcode tag.
	 *
	 * Inputs:
	 * None.
	 *
	 * Outputs:
	 * void
	 */
	public static function init() {
		add_shortcode( 'flipbook', array( __CLASS__, 'render' ) );
	}

	/**
	 * Shortcode callback.
	 *
	 * Role:
	 * Parses id attribute and returns shared flipbook HTML.
	 *
	 * Inputs:
	 * - $atts: Shortcode attributes.
	 *
	 * Outputs:
	 * string HTML
	 */
	public static function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'flipbook'
		);

		return WP_Flipbook_Render::render( (int) $atts['id'] );
	}
}
