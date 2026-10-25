<?php
/**
 * Front-end and admin asset loading.
 *
 * @package WP_Flipbook
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues flipbook CSS/JS when needed.
 */
class WP_Flipbook_Assets {

	/** @var bool */
	private static $front_enqueued = false;

	/**
	 * Hook asset loaders.
	 *
	 * Role:
	 * Registers front assets on demand and admin meta UI scripts.
	 *
	 * Inputs:
	 * None.
	 *
	 * Outputs:
	 * void
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_front' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_block_editor_data' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin' ) );
	}

	/**
	 * Mark that front assets are required and enqueue them.
	 *
	 * Role:
	 * Called by shortcode/render so CSS/JS load only when a flipbook is present.
	 *
	 * Inputs:
	 * None.
	 *
	 * Outputs:
	 * void
	 */
	public static function require_front() {
		self::enqueue_front();
	}

	/**
	 * Enqueue front assets if the page contains the block.
	 *
	 * Role:
	 * Loads Turn.js stack when has_block detects the Gutenberg block.
	 *
	 * Inputs:
	 * None.
	 *
	 * Outputs:
	 * void
	 */
	public static function maybe_enqueue_front() {
		if ( ! is_singular() ) {
			return;
		}

		$post = get_post();
		if ( ! $post ) {
			return;
		}

		if ( has_block( 'wp-flipbook/book', $post ) || has_shortcode( $post->post_content, 'flipbook' ) ) {
			self::enqueue_front();
		}
	}

	/**
	 * Register and enqueue front CSS/JS.
	 *
	 * Role:
	 * Loads scoped book CSS, turn.js (jQuery dep) and init script once.
	 *
	 * Inputs:
	 * None.
	 *
	 * Outputs:
	 * void
	 */
	public static function enqueue_front() {
		if ( self::$front_enqueued ) {
			return;
		}

		self::$front_enqueued = true;

		wp_enqueue_style(
			'wp-flipbook',
			WP_FLIPBOOK_URL . 'assets/css/book.css',
			array(),
			WP_FLIPBOOK_VERSION
		);

		wp_enqueue_script( 'jquery' );

		wp_enqueue_script(
			'wp-flipbook-turn',
			WP_FLIPBOOK_URL . 'assets/js/turn.min.js',
			array( 'jquery' ),
			WP_FLIPBOOK_VERSION,
			true
		);

		wp_enqueue_script(
			'wp-flipbook-init',
			WP_FLIPBOOK_URL . 'assets/js/flipbook-init.js',
			array( 'jquery', 'wp-flipbook-turn' ),
			WP_FLIPBOOK_VERSION,
			true
		);
	}

	/**
	 * Pass flipbook list to the block editor script.
	 *
	 * Role:
	 * Provides CPT options for the Gutenberg book selector.
	 *
	 * Inputs:
	 * None.
	 *
	 * Outputs:
	 * void
	 */
	public static function enqueue_block_editor_data() {
		// Preview in the editor needs the same Turn.js stack as the front.
		self::enqueue_front();

		$books = get_posts(
			array(
				'post_type'      => WP_Flipbook_CPT::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$options = array(
			array(
				'label' => __( '— Select a flipbook —', 'wp-flipbook' ),
				'value' => 0,
			),
		);

		foreach ( $books as $book ) {
			$options[] = array(
				'label' => $book->post_title ? $book->post_title : sprintf( __( 'Flipbook #%d', 'wp-flipbook' ), $book->ID ),
				'value' => (int) $book->ID,
			);
		}

		wp_add_inline_script(
			'wp-flipbook-block-editor',
			'window.wpFlipbookBlock = ' . wp_json_encode( array( 'options' => $options ) ) . ';',
			'before'
		);
	}

	/**
	 * Enqueue admin meta box scripts on flipbook edit screens.
	 *
	 * Role:
	 * Loads media modal helpers and page repeater UI for the CPT.
	 *
	 * Inputs:
	 * - $hook: Current admin page hook.
	 *
	 * Outputs:
	 * void
	 */
	public static function enqueue_admin( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || WP_Flipbook_CPT::POST_TYPE !== $screen->post_type ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_style(
			'wp-flipbook-admin',
			WP_FLIPBOOK_URL . 'assets/css/admin.css',
			array(),
			WP_FLIPBOOK_VERSION
		);

		wp_enqueue_script(
			'wp-flipbook-admin',
			WP_FLIPBOOK_URL . 'assets/js/admin.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			WP_FLIPBOOK_VERSION,
			true
		);

		wp_localize_script(
			'wp-flipbook-admin',
			'wpFlipbookAdmin',
			array(
				'i18n' => array(
					'selectImage' => __( 'Select image', 'wp-flipbook' ),
					'useImage'    => __( 'Use image', 'wp-flipbook' ),
					'remove'      => __( 'Remove', 'wp-flipbook' ),
					'page'        => __( 'Page', 'wp-flipbook' ),
				),
			)
		);
	}
}
