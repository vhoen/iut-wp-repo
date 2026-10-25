<?php
/**
 * Plugin Name:       WP Mouse Trail
 * Description:       Mouse trail canvas effect applied to a content zone via shortcode or Gutenberg block.
 * Version:           1.0.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            vhoen
 * Text Domain:       wp-mouse-trail
 * License:           GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WP_MOUSE_TRAIL_VERSION', '1.0.1' );
define( 'WP_MOUSE_TRAIL_FILE', __FILE__ );
define( 'WP_MOUSE_TRAIL_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_MOUSE_TRAIL_URL', plugin_dir_url( __FILE__ ) );

require_once WP_MOUSE_TRAIL_DIR . 'includes/class-assets.php';
require_once WP_MOUSE_TRAIL_DIR . 'includes/class-render.php';
require_once WP_MOUSE_TRAIL_DIR . 'includes/class-shortcode.php';

/**
 * Bootstrap plugin services.
 *
 * Role:
 * Registers assets, shortcode and Gutenberg block.
 *
 * Inputs:
 * None (hooks on plugins_loaded).
 *
 * Outputs:
 * void
 */
function wp_mouse_trail_bootstrap() {
	WP_Mouse_Trail_Assets::init();
	WP_Mouse_Trail_Shortcode::init();

	add_action( 'init', 'wp_mouse_trail_register_block' );
}
add_action( 'plugins_loaded', 'wp_mouse_trail_bootstrap' );

/**
 * Register the dynamic Gutenberg block.
 *
 * Role:
 * Loads block.json and binds the shared PHP render callback.
 *
 * Inputs:
 * None.
 *
 * Outputs:
 * void
 */
function wp_mouse_trail_register_block() {
	$block_dir = WP_MOUSE_TRAIL_DIR . 'block';

	wp_register_script(
		'wp-mouse-trail-block-editor',
		WP_MOUSE_TRAIL_URL . 'block/index.js',
		array(
			'wp-blocks',
			'wp-element',
			'wp-block-editor',
			'wp-components',
			'wp-i18n',
		),
		WP_MOUSE_TRAIL_VERSION,
		true
	);

	register_block_type(
		$block_dir,
		array(
			'editor_script'   => 'wp-mouse-trail-block-editor',
			'render_callback' => array( 'WP_Mouse_Trail_Render', 'render_from_block' ),
		)
	);
}
