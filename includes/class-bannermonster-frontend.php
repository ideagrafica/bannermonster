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

		wp_enqueue_style( 'bannermonster-front', BANNERMONSTER_URL . 'public/css/frontend.css', array(), BANNERMONSTER_VERSION );

		$js_data = array();
		foreach ( $banners as $b ) {
			$m = BannerMonster_CPT::get_meta( $b->ID );
			$js_data[] = array(
				'id'             => absint( $b->ID ),
				'type'           => sanitize_text_field( $m['bannermonster_type'] ),
				'trigger'        => sanitize_text_field( $m['bannermonster_trigger'] ),
				'seconds'        => absint( $m['bannermonster_trigger_seconds'] ),
				'scroll'         => absint( $m['bannermonster_trigger_scroll'] ),
				'backdrop_close' => absint( $m['bannermonster_close_on_click'] ),
				'reappear'       => absint( $m['bannermonster_reappear'] ),
			);
		}

		wp_register_script( 'bannermonster-front', BANNERMONSTER_URL . 'public/js/frontend.js', array(), BANNERMONSTER_VERSION, true );
		wp_localize_script( 'bannermonster-front', 'bannermonsterData', array(
			'banners' => $js_data,
			'debug'   => isset( $_GET['bannermonster_debug'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['bannermonster_debug'] ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		) );
		wp_enqueue_script( 'bannermonster-front' );
	}

	public function render() {
		$banners = $this->get_active_banners();
		if ( empty( $banners ) ) {
			return;
		}

		foreach ( $banners as $b ) {
			$m     = BannerMonster_CPT::get_meta( $b->ID );
			$type  = sanitize_text_field( $m['bannermonster_type'] );
			$content = apply_filters( 'the_content', $b->post_content ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			$id    = 'bannermonster-' . $b->ID;

			$box_cls  = 'bannermonster-box';
			if ( ! empty( $m['bannermonster_css_class'] ) ) {
				$box_cls .= ' ' . esc_attr( $m['bannermonster_css_class'] );
			}

			$style = $this->build_styles( $m );
			$is_popup = strpos( $type, 'popup' ) !== false;

			// Accessible name: post title or fallback
			$dialog_title = get_the_title( $b->ID );
			$aria_label   = ! empty( $dialog_title ) ? $dialog_title : __( 'Banner', 'bannermonster' );

			// Dialog wrapper
			printf(
				'<dialog id="%s" class="bannermonster-dialog bannermonster-%s" aria-label="%s"%s data-id="%d" data-trigger="%s" data-sec="%d" data-scr="%d" data-reappear="%d">',
				esc_attr( $id ),
				esc_attr( $type ),
				esc_attr( $aria_label ),
				$is_popup ? '' : ' open',
				absint( $b->ID ),
				esc_attr( $m['bannermonster_trigger'] ),
				absint( $m['bannermonster_trigger_seconds'] ),
				absint( $m['bannermonster_trigger_scroll'] ),
				absint( $m['bannermonster_reappear'] )
			);

			// box
			printf( '<div class="%s" style="%s">', esc_attr( $box_cls ), esc_attr( $style ) );

			// close button — always rendered for WCAG 2.2 compliance
			printf(
				'<button class="bannermonster-x" data-id="%d" aria-label="%s">&times;</button>',
				absint( $b->ID ),
				esc_attr__( 'Chiudi', 'bannermonster' )
			);

			// content
			echo '<div class="bannermonster-inner">' . wp_kses_post( $content ) . '</div>';

			// close box and dialog
			echo '</div></dialog>';
		}
	}

	private function get_active_banners() {
		$all = BannerMonster_CPT::get_all_banners();
		$out = array();

		foreach ( $all as $b ) {
			$m = BannerMonster_CPT::get_meta( $b->ID );
			if ( ! $m['bannermonster_enabled'] ) {
				continue;
			}
			if ( $this->should_show( $m ) ) {
				$out[] = $b;
			}
		}

		return $out;
	}

	private function should_show( $m ) {
		switch ( $m['bannermonster_display_where'] ) {
			case 'all':
				return true;
			case 'posts':
				return is_single();
			case 'cpts':
				return is_singular() && ! in_array( get_post_type(), array( 'post', 'page' ), true );
			case 'specific_posts':
				return is_single() && in_array( get_the_ID(), array_map( 'absint', $m['bannermonster_show_on_posts'] ), true );
			case 'specific_pages':
				return is_page() && in_array( get_the_ID(), array_map( 'absint', $m['bannermonster_show_on_pages'] ), true );
			case 'specific_cpts':
				return is_singular() && in_array( get_the_ID(), array_map( 'absint', $m['bannermonster_show_on_cpts'] ), true );
			case 'urls':
				return $this->match_urls( $m['bannermonster_show_on_urls'] );
			case 'taxonomies':
				return $this->match_tax( $m['bannermonster_show_on_taxonomies'] );
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
		if ( ! empty( $m['bannermonster_bg_color'] ) ) {
			$s[] = 'background-color:' . sanitize_hex_color( $m['bannermonster_bg_color'] );
		}
		if ( ! empty( $m['bannermonster_text_color'] ) ) {
			$s[] = 'color:' . sanitize_hex_color( $m['bannermonster_text_color'] );
		}
		if ( ! empty( $m['bannermonster_border_color'] ) && $m['bannermonster_border_width'] > 0 ) {
			$s[] = 'border:' . absint( $m['bannermonster_border_width'] ) . 'px solid ' . sanitize_hex_color( $m['bannermonster_border_color'] );
		}
		if ( $m['bannermonster_padding'] > 0 ) {
			$s[] = 'padding:' . absint( $m['bannermonster_padding'] ) . 'px';
		}
		if ( $m['bannermonster_font_size'] > 0 ) {
			$s[] = 'font-size:' . absint( $m['bannermonster_font_size'] ) . 'px';
		}
		$is_popup = strpos( $m['bannermonster_type'], 'popup' ) !== false;
		if ( $is_popup && $m['bannermonster_width'] < 100 ) {
			$s[] = 'width:' . absint( $m['bannermonster_width'] ) . '%';
		}
		if ( $is_popup ) {
			$s[] = 'max-width:' . absint( $m['bannermonster_max_width'] ) . 'px';
		}
		return implode( ';', $s );
	}
}
