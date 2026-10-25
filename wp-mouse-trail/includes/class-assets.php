<?php
/**
 * Front-end asset loading.
 *
 * @package WP_Mouse_Trail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues mouse trail CSS/JS when needed.
 */
class WP_Mouse_Trail_Assets {

	/** @var bool */
	private static $front_enqueued = false;

	/**
	 * Hook asset loaders.
	 *
	 * Role:
	 * Registers front assets on demand when shortcode or block is present.
	 *
	 * Inputs:
	 * None.
	 *
	 * Outputs:
	 * void
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_front' ) );
	}

	/**
	 * Mark that front assets are required and enqueue them.
	 *
	 * Role:
	 * Called by shortcode/render so CSS/JS load only when a trail zone is present.
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
	 * Enqueue front assets if the page contains the block or shortcode.
	 *
	 * Role:
	 * Loads trail CSS/JS when has_block or has_shortcode detects usage.
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

		if ( has_block( 'wp-mouse-trail/zone', $post ) || has_shortcode( $post->post_content, 'mouse_trail' ) ) {
			self::enqueue_front();
		}
	}

	/**
	 * Register and enqueue front CSS/JS.
	 *
	 * Role:
	 * Loads scoped trail CSS and multi-zone init script once.
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
			'wp-mouse-trail',
			WP_MOUSE_TRAIL_URL . 'assets/css/trail.css',
			array(),
			WP_MOUSE_TRAIL_VERSION
		);

		wp_enqueue_script(
			'wp-mouse-trail',
			WP_MOUSE_TRAIL_URL . 'assets/js/trail.js',
			array(),
			WP_MOUSE_TRAIL_VERSION,
			true
		);
	}
}
