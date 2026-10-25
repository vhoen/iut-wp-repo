<?php
/**
 * Mouse trail shortcode.
 *
 * @package WP_Mouse_Trail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers [mouse_trail]…[/mouse_trail].
 */
class WP_Mouse_Trail_Shortcode {

	/**
	 * Hook shortcode registration.
	 *
	 * Role:
	 * Registers the mouse_trail shortcode tag.
	 *
	 * Inputs:
	 * None.
	 *
	 * Outputs:
	 * void
	 */
	public static function init() {
		add_shortcode( 'mouse_trail', array( __CLASS__, 'render' ) );
	}

	/**
	 * Shortcode callback.
	 *
	 * Role:
	 * Parses duration/width/color attributes and wraps enclosed content in a trail zone.
	 *
	 * Inputs:
	 * - $atts: Shortcode attributes.
	 * - $content: Enclosed shortcode content.
	 *
	 * Outputs:
	 * string HTML
	 */
	public static function render( $atts, $content = null ) {
		$atts = shortcode_atts(
			array(
				'duration' => WP_Mouse_Trail_Render::DEFAULT_DURATION,
				'width'    => WP_Mouse_Trail_Render::DEFAULT_WIDTH,
				'color'    => WP_Mouse_Trail_Render::DEFAULT_COLOR,
			),
			$atts,
			'mouse_trail'
		);

		$inner = do_shortcode( shortcode_unautop( $content ?? '' ) );

		return WP_Mouse_Trail_Render::render(
			$inner,
			array(
				'duration' => (int) $atts['duration'],
				'width'    => (int) $atts['width'],
				'color'    => $atts['color'],
			)
		);
	}
}
