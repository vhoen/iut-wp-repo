<?php
/**
 * Plugin Name:       WP Flipbook
 * Description:       Configurable Turn.js flipbook with shortcode and Gutenberg block.
 * Version:           1.0.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            vhoen
 * Text Domain:       wp-flipbook
 * License:           GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WP_FLIPBOOK_VERSION', '1.0.1' );
define( 'WP_FLIPBOOK_FILE', __FILE__ );
define( 'WP_FLIPBOOK_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_FLIPBOOK_URL', plugin_dir_url( __FILE__ ) );

require_once WP_FLIPBOOK_DIR . 'includes/class-cpt.php';
require_once WP_FLIPBOOK_DIR . 'includes/class-meta.php';
require_once WP_FLIPBOOK_DIR . 'includes/class-assets.php';
require_once WP_FLIPBOOK_DIR . 'includes/class-render.php';
require_once WP_FLIPBOOK_DIR . 'includes/class-shortcode.php';

/**
 * Bootstrap plugin services.
 *
 * Role:
 * Registers CPT, meta, assets, shortcode and Gutenberg block.
 *
 * Inputs:
 * None (hooks on plugins_loaded).
 *
 * Outputs:
 * void
 */
function wp_flipbook_bootstrap() {
	WP_Flipbook_CPT::init();
	WP_Flipbook_Meta::init();
	WP_Flipbook_Assets::init();
	WP_Flipbook_Shortcode::init();

	add_action( 'init', 'wp_flipbook_register_block' );
}
add_action( 'plugins_loaded', 'wp_flipbook_bootstrap' );

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
function wp_flipbook_register_block() {
	$block_dir = WP_FLIPBOOK_DIR . 'block';

	wp_register_script(
		'wp-flipbook-block-editor',
		WP_FLIPBOOK_URL . 'block/index.js',
		array(
			'wp-blocks',
			'wp-element',
			'wp-block-editor',
			'wp-components',
			'wp-data',
			'wp-i18n',
			'wp-server-side-render',
		),
		WP_FLIPBOOK_VERSION,
		true
	);

	register_block_type(
		$block_dir,
		array(
			'editor_script'   => 'wp-flipbook-block-editor',
			'render_callback' => array( 'WP_Flipbook_Render', 'render_from_block' ),
		)
	);
}
