<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BannerMonster_Frontend {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_footer', array( $this, 'render' ) );
	}

	public function enqueue() {
		$banners = $this->get_active_banners();
		if ( empty( $banners ) ) {
			return;
		}

		wp_enqueue_style( 'bm-front', BM_URL . 'public/css/frontend.css', array(), BM_VERSION );

		$js_data = array();
		foreach ( $banners as $b ) {
			$m = BannerMonster_CPT::get_meta( $b->ID );
			$js_data[] = array(
				'id'            => absint( $b->ID ),
				'type'          => sanitize_text_field( $m['bm_type'] ),
				'trigger'       => sanitize_text_field( $m['bm_trigger'] ),
				'seconds'       => absint( $m['bm_trigger_seconds'] ),
				'scroll'        => absint( $m['bm_trigger_scroll'] ),
				'close_on_click'=> absint( $m['bm_close_on_click'] ),
				'overlay'       => absint( $m['bm_overlay'] ),
				'reappear'      => absint( $m['bm_reappear'] ),
			);
		}

		wp_register_script( 'bm-front', BM_URL . 'public/js/frontend.js', array(), BM_VERSION, true );
		wp_localize_script( 'bm-front', 'bmData', array(
			'banners' => $js_data,
			'debug'   => isset( $_GET['bm_debug'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['bm_debug'] ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		) );
		wp_enqueue_script( 'bm-front' );
	}

	public function render() {
		$banners = $this->get_active_banners();
		if ( empty( $banners ) ) {
			return;
		}

		foreach ( $banners as $b ) {
			$m     = BannerMonster_CPT::get_meta( $b->ID );
			$type  = sanitize_text_field( $m['bm_type'] );
			$content = apply_filters( 'the_content', $b->post_content ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			$id    = 'bm-' . $b->ID;

			$wrap_cls = 'bm-wrap bm-' . $type;
			$box_cls  = 'bm-box';
			if ( ! empty( $m['bm_css_class'] ) ) {
				$box_cls .= ' ' . esc_attr( $m['bm_css_class'] );
			}

			$style = $this->build_styles( $m );

			// wrapper
			printf(
				'<div id="%s" class="%s" data-id="%d" data-trigger="%s" data-sec="%d" data-scr="%d" data-close="%d" data-overlay="%d" data-reappear="%d">',
				esc_attr( $id ),
				esc_attr( $wrap_cls ),
				absint( $b->ID ),
				esc_attr( $m['bm_trigger'] ),
				absint( $m['bm_trigger_seconds'] ),
				absint( $m['bm_trigger_scroll'] ),
				absint( $m['bm_close_on_click'] ),
				absint( $m['bm_overlay'] ),
				absint( $m['bm_reappear'] )
			);

			// overlay
			if ( $m['bm_overlay'] && strpos( $type, 'popup' ) !== false ) {
				printf( '<div class="bm-overlay" data-id="%d"></div>', absint( $b->ID ) );
			}

			// box
			printf( '<div class="%s" style="%s">', esc_attr( $box_cls ), esc_attr( $style ) );

			// close btn
			if ( $m['bm_close_enabled'] ) {
				printf( '<button class="bm-x" data-id="%d" aria-label="%s">&times;</button>', absint( $b->ID ), esc_attr__( 'Chiudi', 'bannermonster' ) );
			}

			echo '<div class="bm-inner">' . wp_kses_post( $content ) . '</div></div></div>';

			// custom CSS
			if ( ! empty( $m['bm_custom_css'] ) ) {
				printf(
					'<style>#%s .bm-box{%s}</style>',
					esc_attr( $id ),
					esc_attr( wp_strip_all_tags( $m['bm_custom_css'] ) )
				);
			}
		}
	}

	private function get_active_banners() {
		$all = BannerMonster_CPT::get_all_banners();
		$out = array();

		foreach ( $all as $b ) {
			$m = BannerMonster_CPT::get_meta( $b->ID );
			if ( ! $m['bm_enabled'] ) {
				continue;
			}
			if ( $this->should_show( $m ) ) {
				$out[] = $b;
			}
		}

		return $out;
	}

	private function should_show( $m ) {
		switch ( $m['bm_display_where'] ) {
			case 'all':
				return true;
			case 'posts':
				return is_single();
			case 'cpts':
				return is_singular() && ! in_array( get_post_type(), array( 'post', 'page' ), true );
			case 'specific_posts':
				return is_single() && in_array( get_the_ID(), array_map( 'absint', $m['bm_show_on_posts'] ), true );
			case 'specific_pages':
				return is_page() && in_array( get_the_ID(), array_map( 'absint', $m['bm_show_on_pages'] ), true );
			case 'specific_cpts':
				return is_singular() && in_array( get_the_ID(), array_map( 'absint', $m['bm_show_on_cpts'] ), true );
			case 'urls':
				return $this->match_urls( $m['bm_show_on_urls'] );
			case 'taxonomies':
				return $this->match_tax( $m['bm_show_on_taxonomies'] );
			default:
				return false;
		}
	}

	private function match_urls( $raw ) {
		if ( empty( $raw ) ) {
			return false;
		}
		$current_path = wp_parse_url( home_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) ), PHP_URL_PATH ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$current_full = esc_url_raw( home_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$current_norm = rtrim( strtolower( $current_full ), '/' );
		$current_path_norm = rtrim( strtolower( $current_path ), '/' );

		$lines = array_filter( array_map( 'trim', explode( "\n", $raw ) ) );
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( empty( $line ) ) {
				continue;
			}
			$line_norm = rtrim( strtolower( $line ), '/' );
			// Full URL match
			if ( strpos( $current_norm, $line_norm ) !== false ) {
				return true;
			}
			// Path-only match (user might enter full URL or just path)
			$line_path = wp_parse_url( $line, PHP_URL_PATH );
			if ( $line_path ) {
				$line_path_norm = rtrim( strtolower( $line_path ), '/' );
				if ( $current_path_norm === $line_path_norm ) {
					return true;
				}
				if ( strpos( $current_path_norm, $line_path_norm ) !== false ) {
					return true;
				}
			}
		}
		return false;
	}

	private function match_tax( $rules ) {
		if ( empty( $rules ) ) {
			return false;
		}
		$id = get_the_ID();
		if ( ! $id ) {
			return false;
		}
		foreach ( $rules as $r ) {
			if ( ! is_array( $r ) || empty( $r['tax'] ) || empty( $r['term_id'] ) ) {
				continue;
			}
			$terms = get_the_terms( $id, $r['tax'] );
			if ( $terms && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $t ) {
					if ( $t->term_id === (int) $r['term_id'] ) {
						return true;
					}
				}
			}
		}
		return false;
	}

	private function build_styles( $m ) {
		$s = array();
		if ( ! empty( $m['bm_bg_color'] ) ) {
			$s[] = 'background-color:' . sanitize_hex_color( $m['bm_bg_color'] );
		}
		if ( ! empty( $m['bm_text_color'] ) ) {
			$s[] = 'color:' . sanitize_hex_color( $m['bm_text_color'] );
		}
		if ( ! empty( $m['bm_border_color'] ) && $m['bm_border_width'] > 0 ) {
			$s[] = 'border:' . absint( $m['bm_border_width'] ) . 'px solid ' . sanitize_hex_color( $m['bm_border_color'] );
		}
		if ( $m['bm_padding'] > 0 ) {
			$s[] = 'padding:' . absint( $m['bm_padding'] ) . 'px';
		}
		if ( $m['bm_font_size'] > 0 ) {
			$s[] = 'font-size:' . absint( $m['bm_font_size'] ) . 'px';
		}
		$is_popup = strpos( $m['bm_type'], 'popup' ) !== false;
		if ( $is_popup && $m['bm_width'] < 100 ) {
			$s[] = 'width:' . absint( $m['bm_width'] ) . '%';
		}
		if ( $is_popup ) {
			$s[] = 'max-width:' . absint( $m['bm_max_width'] ) . 'px';
		}
		return implode( ';', $s );
	}
}
