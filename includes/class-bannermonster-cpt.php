<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BannerMonster_CPT {

	const POST_TYPE = 'bm_banner';

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
		$cache_key = 'bm_all_banners';
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
			'bm_type'               => get_post_meta( $post_id, 'bm_type', true ) ?: 'banner_top',
			'bm_enabled'            => (int) get_post_meta( $post_id, 'bm_enabled', true ),
			'bm_bg_color'           => get_post_meta( $post_id, 'bm_bg_color', true ) ?: '#0073aa',
			'bm_text_color'         => get_post_meta( $post_id, 'bm_text_color', true ) ?: '#ffffff',
			'bm_border_color'       => get_post_meta( $post_id, 'bm_border_color', true ),
			'bm_border_width'       => (int) get_post_meta( $post_id, 'bm_border_width', true ),
			'bm_padding'            => (int) ( get_post_meta( $post_id, 'bm_padding', true ) ?: 30 ),
			'bm_font_size'          => (int) ( get_post_meta( $post_id, 'bm_font_size', true ) ?: 16 ),
			'bm_css_class'          => get_post_meta( $post_id, 'bm_css_class', true ),
			'bm_close_enabled'      => (int) get_post_meta( $post_id, 'bm_close_enabled', true ),
			'bm_trigger'            => get_post_meta( $post_id, 'bm_trigger', true ) ?: 'immediate',
			'bm_trigger_seconds'    => (int) ( get_post_meta( $post_id, 'bm_trigger_seconds', true ) ?: 5 ),
			'bm_trigger_scroll'     => (int) ( get_post_meta( $post_id, 'bm_trigger_scroll', true ) ?: 50 ),
			'bm_display_where'      => get_post_meta( $post_id, 'bm_display_where', true ) ?: 'all',
			'bm_show_on_pages'      => (array) get_post_meta( $post_id, 'bm_show_on_pages', true ),
			'bm_show_on_posts'      => (array) get_post_meta( $post_id, 'bm_show_on_posts', true ),
			'bm_show_on_cpts'       => (array) get_post_meta( $post_id, 'bm_show_on_cpts', true ),
			'bm_show_on_urls'       => get_post_meta( $post_id, 'bm_show_on_urls', true ),
			'bm_show_on_taxonomies' => (array) get_post_meta( $post_id, 'bm_show_on_taxonomies', true ),
			'bm_close_on_click'     => (int) get_post_meta( $post_id, 'bm_close_on_click', true ),
			'bm_overlay'            => (int) get_post_meta( $post_id, 'bm_overlay', true ),
			'bm_width'              => (int) ( get_post_meta( $post_id, 'bm_width', true ) ?: 100 ),
			'bm_max_width'          => (int) ( get_post_meta( $post_id, 'bm_max_width', true ) ?: 600 ),
			'bm_reappear'           => (int) get_post_meta( $post_id, 'bm_reappear', true ),
			'bm_custom_css'         => get_post_meta( $post_id, 'bm_custom_css', true ),
		);

		self::$meta_cache[ $post_id ] = $meta;
		return $meta;
	}

	public static function clear_cache() {
		wp_cache_delete( 'bm_all_banners', 'bannermonster' );
		self::$meta_cache = array();
	}
}
