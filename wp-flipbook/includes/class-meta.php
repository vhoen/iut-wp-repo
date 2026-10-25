<?php
/**
 * Flipbook post meta registration and admin UI.
 *
 * @package WP_Flipbook
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers meta keys and the CPT meta box.
 */
class WP_Flipbook_Meta {

	const META_WIDTH         = '_flipbook_width';
	const META_HEIGHT        = '_flipbook_height';
	const META_COVER_FRONT   = '_flipbook_cover_front';
	const META_COVER_INSIDE  = '_flipbook_cover_inside';
	const META_COVER_BACK    = '_flipbook_cover_back';
	const META_PAGES         = '_flipbook_pages';

	const DEFAULT_WIDTH  = 400;
	const DEFAULT_HEIGHT = 300;

	/**
	 * Hook meta registration and admin UI.
	 *
	 * Role:
	 * Registers REST-visible meta and the edit-screen meta box.
	 *
	 * Inputs:
	 * None.
	 *
	 * Outputs:
	 * void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_meta' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
		add_action( 'save_post_' . WP_Flipbook_CPT::POST_TYPE, array( __CLASS__, 'save' ), 10, 2 );
	}

	/**
	 * Register post meta for the flipbook CPT.
	 *
	 * Role:
	 * Exposes dimensions, cover attachment IDs and pages JSON to REST.
	 *
	 * Inputs:
	 * None.
	 *
	 * Outputs:
	 * void
	 */
	public static function register_meta() {
		$int_args = array(
			'type'              => 'integer',
			'single'            => true,
			'show_in_rest'      => true,
			'auth_callback'     => array( __CLASS__, 'can_edit' ),
			'sanitize_callback' => 'absint',
		);

		register_post_meta( WP_Flipbook_CPT::POST_TYPE, self::META_WIDTH, $int_args );
		register_post_meta( WP_Flipbook_CPT::POST_TYPE, self::META_HEIGHT, $int_args );
		register_post_meta( WP_Flipbook_CPT::POST_TYPE, self::META_COVER_FRONT, $int_args );
		register_post_meta( WP_Flipbook_CPT::POST_TYPE, self::META_COVER_INSIDE, $int_args );
		register_post_meta( WP_Flipbook_CPT::POST_TYPE, self::META_COVER_BACK, $int_args );

		register_post_meta(
			WP_Flipbook_CPT::POST_TYPE,
			self::META_PAGES,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => array( __CLASS__, 'can_edit' ),
				'sanitize_callback' => array( __CLASS__, 'sanitize_pages_json' ),
			)
		);
	}

	/**
	 * Auth callback for meta edits.
	 *
	 * Role:
	 * Allows meta updates only for users who can edit the post.
	 *
	 * Inputs:
	 * - $allowed: Unused default.
	 * - $meta_key: Meta key.
	 * - $post_id: Post ID.
	 *
	 * Outputs:
	 * bool
	 */
	public static function can_edit( $allowed, $meta_key, $post_id ) {
		return current_user_can( 'edit_post', $post_id );
	}

	/**
	 * Sanitize pages payload stored as JSON string.
	 *
	 * Role:
	 * Normalizes title/text/image_id entries and drops invalid rows.
	 *
	 * Inputs:
	 * - $value: Raw JSON string or array.
	 *
	 * Outputs:
	 * string JSON
	 */
	public static function sanitize_pages_json( $value ) {
		$pages = self::normalize_pages( $value );
		return wp_json_encode( $pages );
	}

	/**
	 * Normalize pages into a clean array.
	 *
	 * Role:
	 * Accepts JSON string or array and returns sanitized page rows.
	 *
	 * Inputs:
	 * - $value: Raw pages data.
	 *
	 * Outputs:
	 * array
	 */
	public static function normalize_pages( $value ) {
		if ( is_string( $value ) ) {
			$decoded = json_decode( $value, true );
			$value   = is_array( $decoded ) ? $decoded : array();
		}

		if ( ! is_array( $value ) ) {
			return array();
		}

		$pages = array();
		foreach ( $value as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$pages[] = array(
				'title'    => isset( $row['title'] ) ? sanitize_text_field( $row['title'] ) : '',
				'text'     => isset( $row['text'] ) ? sanitize_textarea_field( $row['text'] ) : '',
				'image_id' => isset( $row['image_id'] ) ? absint( $row['image_id'] ) : 0,
			);
		}

		return $pages;
	}

	/**
	 * Read all flipbook settings for a post.
	 *
	 * Role:
	 * Returns typed settings used by the shared renderer.
	 *
	 * Inputs:
	 * - $post_id: Flipbook post ID.
	 *
	 * Outputs:
	 * array{width:int,height:int,cover_front:int,cover_inside:int,cover_back:int,pages:array}
	 */
	public static function get_settings( $post_id ) {
		$post_id = absint( $post_id );
		$width   = (int) get_post_meta( $post_id, self::META_WIDTH, true );
		$height  = (int) get_post_meta( $post_id, self::META_HEIGHT, true );

		return array(
			'width'        => $width > 0 ? $width : self::DEFAULT_WIDTH,
			'height'       => $height > 0 ? $height : self::DEFAULT_HEIGHT,
			'cover_front'  => (int) get_post_meta( $post_id, self::META_COVER_FRONT, true ),
			'cover_inside' => (int) get_post_meta( $post_id, self::META_COVER_INSIDE, true ),
			'cover_back'   => (int) get_post_meta( $post_id, self::META_COVER_BACK, true ),
			'pages'        => self::normalize_pages( get_post_meta( $post_id, self::META_PAGES, true ) ),
		);
	}

	/**
	 * Register the configuration meta box.
	 *
	 * Role:
	 * Adds the covers / pages / size editor on the CPT screen.
	 *
	 * Inputs:
	 * None.
	 *
	 * Outputs:
	 * void
	 */
	public static function add_meta_box() {
		add_meta_box(
			'wp_flipbook_settings',
			__( 'Flipbook settings', 'wp-flipbook' ),
			array( __CLASS__, 'render_meta_box' ),
			WP_Flipbook_CPT::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Render the admin meta box markup.
	 *
	 * Role:
	 * Outputs size fields, cover pickers and the sortable pages repeater.
	 *
	 * Inputs:
	 * - $post: Current post object.
	 *
	 * Outputs:
	 * void (echo)
	 */
	public static function render_meta_box( $post ) {
		wp_nonce_field( 'wp_flipbook_save_meta', 'wp_flipbook_meta_nonce' );

		$settings = self::get_settings( $post->ID );
		?>
		<div class="wp-flipbook-admin">
			<p class="description">
				<?php esc_html_e( 'Configure covers, inner pages and Turn.js size. Use the shortcode or the Flipbook block with this post ID.', 'wp-flipbook' ); ?>
			</p>
			<p>
				<code>[flipbook id="<?php echo esc_attr( (string) $post->ID ); ?>"]</code>
			</p>

			<h3><?php esc_html_e( 'Dimensions', 'wp-flipbook' ); ?></h3>
			<p>
				<label>
					<?php esc_html_e( 'Width (px)', 'wp-flipbook' ); ?>
					<input type="number" min="100" max="2000" name="flipbook_width" value="<?php echo esc_attr( (string) $settings['width'] ); ?>" />
				</label>
				<label>
					<?php esc_html_e( 'Height (px)', 'wp-flipbook' ); ?>
					<input type="number" min="100" max="2000" name="flipbook_height" value="<?php echo esc_attr( (string) $settings['height'] ); ?>" />
				</label>
			</p>

			<h3><?php esc_html_e( 'Covers', 'wp-flipbook' ); ?></h3>
			<div class="wp-flipbook-covers">
				<?php
				self::render_image_field( 'flipbook_cover_front', $settings['cover_front'], __( 'Front cover', 'wp-flipbook' ) );
				self::render_image_field( 'flipbook_cover_inside', $settings['cover_inside'], __( 'Inside cover', 'wp-flipbook' ) );
				self::render_image_field( 'flipbook_cover_back', $settings['cover_back'], __( 'Back cover', 'wp-flipbook' ) );
				?>
			</div>

			<h3><?php esc_html_e( 'Pages', 'wp-flipbook' ); ?></h3>
			<div id="wp-flipbook-pages" class="wp-flipbook-pages" data-next-index="<?php echo esc_attr( (string) count( $settings['pages'] ) ); ?>">
				<?php
				if ( empty( $settings['pages'] ) ) {
					self::render_page_row( 0, array( 'title' => '', 'text' => '', 'image_id' => 0 ) );
				} else {
					foreach ( $settings['pages'] as $index => $page ) {
						self::render_page_row( $index, $page );
					}
				}
				?>
			</div>
			<p>
				<button type="button" class="button" id="wp-flipbook-add-page"><?php esc_html_e( 'Add page', 'wp-flipbook' ); ?></button>
			</p>

			<template id="wp-flipbook-page-template">
				<?php self::render_page_row( '__INDEX__', array( 'title' => '', 'text' => '', 'image_id' => 0 ), true ); ?>
			</template>
		</div>
		<?php
	}

	/**
	 * Render one cover/image picker field.
	 *
	 * Role:
	 * Shows thumbnail + select/remove buttons bound to an attachment ID input.
	 *
	 * Inputs:
	 * - $name: Input name.
	 * - $attachment_id: Current attachment ID.
	 * - $label: Field label.
	 *
	 * Outputs:
	 * void (echo)
	 */
	private static function render_image_field( $name, $attachment_id, $label ) {
		$url = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'medium' ) : '';
		?>
		<div class="wp-flipbook-image-field" data-field="<?php echo esc_attr( $name ); ?>">
			<strong><?php echo esc_html( $label ); ?></strong>
			<div class="wp-flipbook-image-preview">
				<?php if ( $url ) : ?>
					<img src="<?php echo esc_url( $url ); ?>" alt="" />
				<?php endif; ?>
			</div>
			<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $attachment_id ); ?>" class="wp-flipbook-image-id" />
			<p>
				<button type="button" class="button wp-flipbook-select-image"><?php esc_html_e( 'Select image', 'wp-flipbook' ); ?></button>
				<button type="button" class="button wp-flipbook-remove-image"><?php esc_html_e( 'Remove', 'wp-flipbook' ); ?></button>
			</p>
		</div>
		<?php
	}

	/**
	 * Render one sortable page row.
	 *
	 * Role:
	 * Outputs title, text, image picker and remove control for a page.
	 *
	 * Inputs:
	 * - $index: Row index or template placeholder.
	 * - $page: Page data.
	 * - $is_template: When true, keep __INDEX__ placeholders in names.
	 *
	 * Outputs:
	 * void (echo)
	 */
	private static function render_page_row( $index, $page, $is_template = false ) {
		$image_id = isset( $page['image_id'] ) ? (int) $page['image_id'] : 0;
		$url      = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
		$title    = isset( $page['title'] ) ? $page['title'] : '';
		$text     = isset( $page['text'] ) ? $page['text'] : '';
		$prefix   = $is_template ? 'flipbook_pages[__INDEX__]' : 'flipbook_pages[' . (int) $index . ']';
		?>
		<div class="wp-flipbook-page-row">
			<span class="wp-flipbook-page-handle dashicons dashicons-menu" aria-hidden="true"></span>
			<div class="wp-flipbook-page-fields">
				<p>
					<label>
						<?php esc_html_e( 'Title', 'wp-flipbook' ); ?>
						<input type="text" name="<?php echo esc_attr( $prefix ); ?>[title]" value="<?php echo esc_attr( $title ); ?>" class="widefat" />
					</label>
				</p>
				<p>
					<label>
						<?php esc_html_e( 'Text', 'wp-flipbook' ); ?>
						<textarea name="<?php echo esc_attr( $prefix ); ?>[text]" rows="3" class="widefat"><?php echo esc_textarea( $text ); ?></textarea>
					</label>
				</p>
				<div class="wp-flipbook-image-field wp-flipbook-page-image">
					<strong><?php esc_html_e( 'Illustration', 'wp-flipbook' ); ?></strong>
					<div class="wp-flipbook-image-preview">
						<?php if ( $url ) : ?>
							<img src="<?php echo esc_url( $url ); ?>" alt="" />
						<?php endif; ?>
					</div>
					<input type="hidden" name="<?php echo esc_attr( $prefix ); ?>[image_id]" value="<?php echo esc_attr( (string) $image_id ); ?>" class="wp-flipbook-image-id" />
					<p>
						<button type="button" class="button wp-flipbook-select-image"><?php esc_html_e( 'Select image', 'wp-flipbook' ); ?></button>
						<button type="button" class="button wp-flipbook-remove-image"><?php esc_html_e( 'Remove', 'wp-flipbook' ); ?></button>
					</p>
				</div>
			</div>
			<p>
				<button type="button" class="button-link-delete wp-flipbook-remove-page"><?php esc_html_e( 'Remove page', 'wp-flipbook' ); ?></button>
			</p>
		</div>
		<?php
	}

	/**
	 * Persist meta box fields on save.
	 *
	 * Role:
	 * Validates nonce/caps and stores dimensions, covers and pages.
	 *
	 * Inputs:
	 * - $post_id: Post being saved.
	 * - $post: Post object.
	 *
	 * Outputs:
	 * void
	 */
	public static function save( $post_id, $post ) {
		if ( ! isset( $_POST['wp_flipbook_meta_nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['wp_flipbook_meta_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'wp_flipbook_save_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$width  = isset( $_POST['flipbook_width'] ) ? absint( $_POST['flipbook_width'] ) : self::DEFAULT_WIDTH;
		$height = isset( $_POST['flipbook_height'] ) ? absint( $_POST['flipbook_height'] ) : self::DEFAULT_HEIGHT;

		update_post_meta( $post_id, self::META_WIDTH, $width > 0 ? $width : self::DEFAULT_WIDTH );
		update_post_meta( $post_id, self::META_HEIGHT, $height > 0 ? $height : self::DEFAULT_HEIGHT );
		update_post_meta( $post_id, self::META_COVER_FRONT, isset( $_POST['flipbook_cover_front'] ) ? absint( $_POST['flipbook_cover_front'] ) : 0 );
		update_post_meta( $post_id, self::META_COVER_INSIDE, isset( $_POST['flipbook_cover_inside'] ) ? absint( $_POST['flipbook_cover_inside'] ) : 0 );
		update_post_meta( $post_id, self::META_COVER_BACK, isset( $_POST['flipbook_cover_back'] ) ? absint( $_POST['flipbook_cover_back'] ) : 0 );

		$raw_pages = isset( $_POST['flipbook_pages'] ) ? wp_unslash( $_POST['flipbook_pages'] ) : array();
		if ( ! is_array( $raw_pages ) ) {
			$raw_pages = array();
		}

		$pages = array();
		foreach ( $raw_pages as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$pages[] = array(
				'title'    => isset( $row['title'] ) ? sanitize_text_field( $row['title'] ) : '',
				'text'     => isset( $row['text'] ) ? sanitize_textarea_field( $row['text'] ) : '',
				'image_id' => isset( $row['image_id'] ) ? absint( $row['image_id'] ) : 0,
			);
		}

		update_post_meta( $post_id, self::META_PAGES, wp_json_encode( $pages ) );
	}
}
