<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BannerMonster_CPT {

	const POST_TYPE = 'bannermonster_banner';

	private static $meta_cache = array();

	public static function register_post_type() {
		register_post_type( self::POST_TYPE, array(
			'labels'             => array(
				'name'                  => __( 'Banner & Popup', 'bannermonster' ),
				'singular_name'         => __( 'Banner / Popup', 'bannermonster' ),
				'menu_name'             => __( 'BannerMonster', 'bannermonster' ),
				'add_new'               => __( 'Aggiungi Nuovo', 'bannermonster' ),
				'add_new_item'          => __( 'Aggiungi Nuovo Banner/Popup', 'bannermonster' ),
				'edit_item'             => __( 'Modifica Banner/Popup', 'bannermonster' ),
				'new_item'              => __( 'Nuovo Banner/Popup', 'bannermonster' ),
				'view_item'             => __( 'Visualizza Banner/Popup', 'bannermonster' ),
				'search_items'          => __( 'Cerca Banner/Popup', 'bannermonster' ),
				'not_found'             => __( 'Nessun banner/popup trovato', 'bannermonster' ),
				'not_found_in_trash'    => __( 'Nessun banner/popup trovato nel cestino', 'bannermonster' ),
				'all_items'             => __( 'Tutti i Banner/Popup', 'bannermonster' ),
				'featured_image'        => __( 'Immagine del Banner', 'bannermonster' ),
				'set_featured_image'    => __( 'Imposta immagine del banner', 'bannermonster' ),
				'remove_featured_image' => __( 'Rimuovi immagine del banner', 'bannermonster' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_rest'       => false,
			'query_var'          => false,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 30,
			'menu_icon'          => 'dashicons-images-alt2',
			'supports'           => array( 'title', 'editor' ),
		) );
	}

	public static function get_all_banners() {
		$cache_key = 'bannermonster_all_banners';
		$cached = wp_cache_get( $cache_key, 'bannermonster' );
		if ( false !== $cached ) {
			return $cached;
		}

		$banners = get_posts( array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		) );

		wp_cache_set( $cache_key, $banners, 'bannermonster', HOUR_IN_SECONDS );
		return $banners;
	}

	public static function get_meta( $post_id ) {
		if ( isset( self::$meta_cache[ $post_id ] ) ) {
			return self::$meta_cache[ $post_id ];
		}

		$meta = array(
			'bannermonster_type'               => get_post_meta( $post_id, 'bannermonster_type', true ) ?: 'banner_top',
			'bannermonster_enabled'            => (int) get_post_meta( $post_id, 'bannermonster_enabled', true ),
			'bannermonster_bg_color'           => get_post_meta( $post_id, 'bannermonster_bg_color', true ) ?: '#0073aa',
			'bannermonster_text_color'         => get_post_meta( $post_id, 'bannermonster_text_color', true ) ?: '#ffffff',
			'bannermonster_border_color'       => get_post_meta( $post_id, 'bannermonster_border_color', true ),
			'bannermonster_border_width'       => (int) get_post_meta( $post_id, 'bannermonster_border_width', true ),
			'bannermonster_padding'            => (int) ( get_post_meta( $post_id, 'bannermonster_padding', true ) ?: 30 ),
			'bannermonster_font_size'          => (int) ( get_post_meta( $post_id, 'bannermonster_font_size', true ) ?: 16 ),
			'bannermonster_css_class'          => get_post_meta( $post_id, 'bannermonster_css_class', true ),
			'bannermonster_close_enabled'      => (int) get_post_meta( $post_id, 'bannermonster_close_enabled', true ),
			'bannermonster_trigger'            => get_post_meta( $post_id, 'bannermonster_trigger', true ) ?: 'immediate',
			'bannermonster_trigger_seconds'    => (int) ( get_post_meta( $post_id, 'bannermonster_trigger_seconds', true ) ?: 5 ),
			'bannermonster_trigger_scroll'     => (int) ( get_post_meta( $post_id, 'bannermonster_trigger_scroll', true ) ?: 50 ),
			'bannermonster_display_where'      => get_post_meta( $post_id, 'bannermonster_display_where', true ) ?: 'all',
			'bannermonster_show_on_pages'      => (array) get_post_meta( $post_id, 'bannermonster_show_on_pages', true ),
			'bannermonster_show_on_posts'      => (array) get_post_meta( $post_id, 'bannermonster_show_on_posts', true ),
			'bannermonster_show_on_cpts'       => (array) get_post_meta( $post_id, 'bannermonster_show_on_cpts', true ),
			'bannermonster_show_on_urls'       => get_post_meta( $post_id, 'bannermonster_show_on_urls', true ),
			'bannermonster_show_on_taxonomies' => (array) get_post_meta( $post_id, 'bannermonster_show_on_taxonomies', true ),
			'bannermonster_close_on_click'     => (int) get_post_meta( $post_id, 'bannermonster_close_on_click', true ),
			'bannermonster_overlay'            => (int) get_post_meta( $post_id, 'bannermonster_overlay', true ),
			'bannermonster_width'              => (int) ( get_post_meta( $post_id, 'bannermonster_width', true ) ?: 100 ),
			'bannermonster_max_width'          => (int) ( get_post_meta( $post_id, 'bannermonster_max_width', true ) ?: 600 ),
			'bannermonster_reappear'           => (int) get_post_meta( $post_id, 'bannermonster_reappear', true ),
		);

		self::$meta_cache[ $post_id ] = $meta;
		return $meta;
	}

	public static function clear_cache() {
		wp_cache_delete( 'bannermonster_all_banners', 'bannermonster' );
		self::$meta_cache = array();
	}
}
