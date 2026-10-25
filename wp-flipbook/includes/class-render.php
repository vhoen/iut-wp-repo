<?php
/**
 * Shared flipbook HTML renderer.
 *
 * @package WP_Flipbook
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the Turn.js markup from a flipbook post.
 */
class WP_Flipbook_Render {

	/**
	 * Render from shortcode / PHP with a post ID.
	 *
	 * Role:
	 * Loads CPT settings and returns the flipbook HTML wrapper.
	 *
	 * Inputs:
	 * - $post_id: Flipbook CPT ID.
	 *
	 * Outputs:
	 * string HTML (empty when invalid)
	 */
	public static function render( $post_id ) {
		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return self::admin_notice( __( 'Flipbook: missing id.', 'wp-flipbook' ) );
		}

		$post = get_post( $post_id );
		if ( ! $post || WP_Flipbook_CPT::POST_TYPE !== $post->post_type || 'publish' !== $post->post_status ) {
			return self::admin_notice( __( 'Flipbook: invalid or unpublished book.', 'wp-flipbook' ) );
		}

		WP_Flipbook_Assets::require_front();

		$settings = WP_Flipbook_Meta::get_settings( $post_id );
		$uid      = 'flipbook-' . $post_id;

		$cover_front  = self::attachment_url( $settings['cover_front'] );
		$cover_inside = self::attachment_url( $settings['cover_inside'] );
		$cover_back   = self::attachment_url( $settings['cover_back'] );

		ob_start();
		?>
		<div class="wp-flipbook"
			data-flipbook-id="<?php echo esc_attr( (string) $post_id ); ?>">
			<div id="<?php echo esc_attr( $uid ); ?>"
				class="flipbook"
				data-width="<?php echo esc_attr( (string) $settings['width'] ); ?>"
				data-height="<?php echo esc_attr( (string) $settings['height'] ); ?>">
				<div class="hard cover-front"<?php echo self::bg_style( $cover_front ); ?>></div>
				<div class="hard cover-inside"<?php echo self::bg_style( $cover_inside ); ?>></div>

				<?php foreach ( $settings['pages'] as $index => $page ) : ?>
					<?php
					$image_url = self::attachment_url( isset( $page['image_id'] ) ? (int) $page['image_id'] : 0 );
					$folio     = (int) $index + 1;
					?>
					<div class="sheet">
						<div class="content">
							<?php if ( $image_url ) : ?>
								<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $page['title'] ); ?>" />
							<?php endif; ?>
							<?php if ( ! empty( $page['title'] ) ) : ?>
								<h2><?php echo esc_html( $page['title'] ); ?></h2>
							<?php endif; ?>
							<?php if ( ! empty( $page['text'] ) ) : ?>
								<p><?php echo esc_html( $page['text'] ); ?></p>
							<?php endif; ?>
							<span class="folio"><?php echo esc_html( (string) $folio ); ?></span>
						</div>
					</div>
				<?php endforeach; ?>

				<div class="hard cover-back-inside"<?php echo self::bg_style( $cover_inside ); ?>></div>
				<div class="hard cover-back"<?php echo self::bg_style( $cover_back ); ?>></div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Gutenberg dynamic block render callback.
	 *
	 * Role:
	 * Reads flipbookId attribute and delegates to the shared renderer.
	 *
	 * Inputs:
	 * - $attributes: Block attributes.
	 * - $content: Inner content (unused).
	 *
	 * Outputs:
	 * string HTML
	 */
	public static function render_from_block( $attributes, $content = '' ) {
		unset( $content );
		$id = isset( $attributes['flipbookId'] ) ? absint( $attributes['flipbookId'] ) : 0;
		return self::render( $id );
	}

	/**
	 * Resolve attachment URL.
	 *
	 * Role:
	 * Returns full URL for an attachment ID or empty string.
	 *
	 * Inputs:
	 * - $attachment_id: Media library ID.
	 *
	 * Outputs:
	 * string
	 */
	private static function attachment_url( $attachment_id ) {
		$attachment_id = absint( $attachment_id );
		if ( ! $attachment_id ) {
			return '';
		}
		$url = wp_get_attachment_url( $attachment_id );
		return $url ? $url : '';
	}

	/**
	 * Build an inline background-image style attribute.
	 *
	 * Role:
	 * Escapes URL for use on cover hard pages.
	 *
	 * Inputs:
	 * - $url: Image URL.
	 *
	 * Outputs:
	 * string Attribute fragment including leading space, or empty
	 */
	private static function bg_style( $url ) {
		if ( ! $url ) {
			return '';
		}
		$style = 'background-image:url(' . esc_url( $url ) . ')';
		return ' style="' . esc_attr( $style ) . '"';
	}

	/**
	 * Optional notice visible only to editors when render fails.
	 *
	 * Role:
	 * Helps admins debug missing IDs without exposing messages to visitors.
	 *
	 * Inputs:
	 * - $message: Notice text.
	 *
	 * Outputs:
	 * string HTML or empty
	 */
	private static function admin_notice( $message ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return '';
		}
		return '<p class="wp-flipbook-admin-notice">' . esc_html( $message ) . '</p>';
	}
}
