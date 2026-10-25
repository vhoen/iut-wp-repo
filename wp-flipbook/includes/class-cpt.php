<?php
/**
 * Flipbook custom post type.
 *
 * @package WP_Flipbook
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the flipbook CPT.
 */
class WP_Flipbook_CPT {

	const POST_TYPE = 'flipbook';

	/**
	 * Hook registration.
	 *
	 * Role:
	 * Attaches CPT registration to init.
	 *
	 * Inputs:
	 * None.
	 *
	 * Outputs:
	 * void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Register the flipbook post type.
	 *
	 * Role:
	 * Exposes a public CPT usable by shortcode and Gutenberg block.
	 *
	 * Inputs:
	 * None.
	 *
	 * Outputs:
	 * void
	 */
	public static function register() {
		$labels = array(
			'name'               => __( 'Flipbooks', 'wp-flipbook' ),
			'singular_name'      => __( 'Flipbook', 'wp-flipbook' ),
			'add_new'            => __( 'Add New', 'wp-flipbook' ),
			'add_new_item'       => __( 'Add New Flipbook', 'wp-flipbook' ),
			'edit_item'          => __( 'Edit Flipbook', 'wp-flipbook' ),
			'new_item'           => __( 'New Flipbook', 'wp-flipbook' ),
			'view_item'          => __( 'View Flipbook', 'wp-flipbook' ),
			'search_items'       => __( 'Search Flipbooks', 'wp-flipbook' ),
			'not_found'          => __( 'No flipbooks found.', 'wp-flipbook' ),
			'not_found_in_trash' => __( 'No flipbooks found in Trash.', 'wp-flipbook' ),
			'menu_name'          => __( 'Flipbooks', 'wp-flipbook' ),
		);

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => $labels,
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => true,
				'menu_icon'           => 'dashicons-book-alt',
				'menu_position'       => 25,
				'capability_type'     => 'post',
				'hierarchical'        => false,
				'supports'            => array( 'title' ),
				'has_archive'         => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'rewrite'             => false,
			)
		);
	}
}
