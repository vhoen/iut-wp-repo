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

	/**
	 * Render a trail zone wrapping arbitrary HTML content.
	 *
	 * Role:
	 * Enqueues front assets and returns the zone wrapper with canvas.
	 *
	 * Inputs:
	 * - $content: Inner HTML for the zone.
	 * - $args: Optional duration and width (defaults match the static demo).
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
			)
		);

		$duration = absint( $args['duration'] );
		$width    = absint( $args['width'] );

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
	 * - $attributes: Block attributes (duration, width).
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
			)
		);
	}
}
