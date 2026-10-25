<?php
/**
 * Shared mouse trail HTML renderer.
 *
 * @package WP_Mouse_Trail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the trail zone markup from content and options.
 */
class WP_Mouse_Trail_Render {

	const DEFAULT_DURATION = 850;
	const DEFAULT_WIDTH    = 36;
	const DEFAULT_COLOR    = '#ffffff';

	/**
	 * Sanitize a trail stroke color to a safe hex value.
	 *
	 * Role:
	 * Accepts #RGB or #RRGGBB only; otherwise returns the default.
	 *
	 * Inputs:
	 * - $color: Raw color string from block or shortcode.
	 *
	 * Outputs:
	 * string Hex color including leading #.
	 */
	public static function sanitize_color( $color ) {
		$color = is_string( $color ) ? trim( $color ) : '';

		if ( preg_match( '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $color ) ) {
			return strtolower( $color );
		}

		return self::DEFAULT_COLOR;
	}

	/**
	 * Render a trail zone wrapping arbitrary HTML content.
	 *
	 * Role:
	 * Enqueues front assets and returns the zone wrapper with canvas.
	 *
	 * Inputs:
	 * - $content: Inner HTML for the zone.
	 * - $args: Optional duration, width, and color (defaults match the static demo).
	 *
	 * Outputs:
	 * string HTML
	 */
	public static function render( $content, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'duration' => self::DEFAULT_DURATION,
				'width'    => self::DEFAULT_WIDTH,
				'color'    => self::DEFAULT_COLOR,
			)
		);

		$duration = absint( $args['duration'] );
		$width    = absint( $args['width'] );
		$color    = self::sanitize_color( $args['color'] );

		if ( $duration < 1 ) {
			$duration = self::DEFAULT_DURATION;
		}
		if ( $width < 1 ) {
			$width = self::DEFAULT_WIDTH;
		}

		WP_Mouse_Trail_Assets::require_front();

		ob_start();
		?>
		<div
			class="wp-mouse-trail"
			data-duration="<?php echo esc_attr( (string) $duration ); ?>"
			data-width="<?php echo esc_attr( (string) $width ); ?>"
			data-color="<?php echo esc_attr( $color ); ?>"
		>
			<canvas class="wp-mouse-trail__canvas" aria-hidden="true"></canvas>
			<div class="wp-mouse-trail__content">
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted shortcode/block HTML. ?>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render callback for the Gutenberg block.
	 *
	 * Role:
	 * Maps block attributes and InnerBlocks content to the shared renderer.
	 *
	 * Inputs:
	 * - $attributes: Block attributes (duration, width, color).
	 * - $content: Saved InnerBlocks HTML.
	 *
	 * Outputs:
	 * string HTML
	 */
	public static function render_from_block( $attributes, $content ) {
		$attributes = is_array( $attributes ) ? $attributes : array();

		return self::render(
			$content,
			array(
				'duration' => isset( $attributes['duration'] ) ? (int) $attributes['duration'] : self::DEFAULT_DURATION,
				'width'    => isset( $attributes['width'] ) ? (int) $attributes['width'] : self::DEFAULT_WIDTH,
				'color'    => isset( $attributes['color'] ) ? $attributes['color'] : self::DEFAULT_COLOR,
			)
		);
	}
}
