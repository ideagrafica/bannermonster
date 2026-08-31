<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BannerMonster_Admin {

	const NONCE = 'bm_save_metabox';

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register_metaboxes' ) );
		add_action( 'save_post_' . BannerMonster_CPT::POST_TYPE, array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'updated_post_meta', array( $this, 'on_meta_updated' ), 10, 4 );
		add_action( 'deleted_post_meta', array( $this, 'on_meta_deleted' ), 10, 4 );
	}

	public function enqueue( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== BannerMonster_CPT::POST_TYPE ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		wp_enqueue_style( 'bm-admin', BM_URL . 'admin/css/admin.css', array(), BM_VERSION );
		wp_enqueue_script( 'bm-admin', BM_URL . 'admin/js/admin.js', array( 'jquery' ), BM_VERSION, true );
	}

	public function register_metaboxes() {
		add_meta_box( 'bm_type', __( 'Tipo & Configurazione', 'bannermonster' ), array( $this, 'box_type' ), BannerMonster_CPT::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'bm_display', __( 'Regole di Visualizzazione', 'bannermonster' ), array( $this, 'box_display' ), BannerMonster_CPT::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'bm_trigger', __( 'Trigger', 'bannermonster' ), array( $this, 'box_trigger' ), BannerMonster_CPT::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'bm_style', __( 'Stile & Personalizzazione', 'bannermonster' ), array( $this, 'box_style' ), BannerMonster_CPT::POST_TYPE, 'normal', 'high' );
	}

	/* ---- Metabox: Tipo ---- */

	public function box_type( $post ) {
		wp_nonce_field( self::NONCE, 'bm_nonce' );
		$m = BannerMonster_CPT::get_meta( $post->ID );
		?>
		<table class="form-table bm-table">
			<tr>
				<th><label for="bm_type"><?php esc_html_e( 'Tipo di elemento', 'bannermonster' ); ?></label></th>
				<td>
					<select name="bm_type" id="bm_type">
						<optgroup label="<?php esc_attr_e( 'Banner', 'bannermonster' ); ?>">
							<option value="banner_top" <?php selected( $m['bm_type'], 'banner_top' ); ?>><?php esc_html_e( 'Barra in alto (fissa)', 'bannermonster' ); ?></option>
							<option value="banner_bottom" <?php selected( $m['bm_type'], 'banner_bottom' ); ?>><?php esc_html_e( 'Barra in basso (fissa)', 'bannermonster' ); ?></option>
						</optgroup>
						<optgroup label="<?php esc_attr_e( 'Popup', 'bannermonster' ); ?>">
							<option value="popup_center" <?php selected( $m['bm_type'], 'popup_center' ); ?>><?php esc_html_e( 'Centrale', 'bannermonster' ); ?></option>
							<option value="popup_bottom_right" <?php selected( $m['bm_type'], 'popup_bottom_right' ); ?>><?php esc_html_e( 'Basso a destra', 'bannermonster' ); ?></option>
							<option value="popup_bottom_left" <?php selected( $m['bm_type'], 'popup_bottom_left' ); ?>><?php esc_html_e( 'Basso a sinistra', 'bannermonster' ); ?></option>
						</optgroup>
					</select>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Stato', 'bannermonster' ); ?></th>
				<td><label><input type="checkbox" name="bm_enabled" value="1" <?php checked( $m['bm_enabled'], 1 ); ?>> <?php esc_html_e( 'Attivo', 'bannermonster' ); ?></label></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Overlay (popup)', 'bannermonster' ); ?></th>
				<td><label><input type="checkbox" name="bm_overlay" value="1" <?php checked( $m['bm_overlay'], 1 ); ?>> <?php esc_html_e( 'Mostra overlay scuro', 'bannermonster' ); ?></label></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Chiudi su overlay', 'bannermonster' ); ?></th>
				<td><label><input type="checkbox" name="bm_close_on_click" value="1" <?php checked( $m['bm_close_on_click'], 1 ); ?>> <?php esc_html_e( 'Chiudi cliccando fuori', 'bannermonster' ); ?></label></td>
			</tr>
			<tr>
				<th><label for="bm_width"><?php esc_html_e( 'Larghezza (%)', 'bannermonster' ); ?></label></th>
				<td><input type="number" name="bm_width" id="bm_width" value="<?php echo esc_attr( $m['bm_width'] ); ?>" min="10" max="100" class="small-text"></td>
			</tr>
			<tr>
				<th><label for="bm_max_width"><?php esc_html_e( 'Max width (px)', 'bannermonster' ); ?></label></th>
				<td><input type="number" name="bm_max_width" id="bm_max_width" value="<?php echo esc_attr( $m['bm_max_width'] ); ?>" min="200" max="1200" class="small-text"></td>
			</tr>
		</table>
		<?php
	}

	/* ---- Metabox: Display Rules ---- */

	public function box_display( $post ) {
		$m = BannerMonster_CPT::get_meta( $post->ID );
		$where = $m['bm_display_where'];
		?>
		<table class="form-table bm-table">
			<tr>
				<th><label for="bm_display_where"><?php esc_html_e( 'Dove visualizzare', 'bannermonster' ); ?></label></th>
				<td>
					<select name="bm_display_where" id="bm_display_where">
						<option value="all" <?php selected( $where, 'all' ); ?>><?php esc_html_e( 'Tutte le pagine', 'bannermonster' ); ?></option>
						<option value="posts" <?php selected( $where, 'posts' ); ?>><?php esc_html_e( 'Tutti i post', 'bannermonster' ); ?></option>
						<option value="cpts" <?php selected( $where, 'cpts' ); ?>><?php esc_html_e( 'Tutti i Custom Post Type', 'bannermonster' ); ?></option>
						<option value="specific_posts" <?php selected( $where, 'specific_posts' ); ?>><?php esc_html_e( 'Post specifici', 'bannermonster' ); ?></option>
						<option value="specific_pages" <?php selected( $where, 'specific_pages' ); ?>><?php esc_html_e( 'Pagine specifiche', 'bannermonster' ); ?></option>
						<option value="specific_cpts" <?php selected( $where, 'specific_cpts' ); ?>><?php esc_html_e( 'CPT specifici', 'bannermonster' ); ?></option>
						<option value="urls" <?php selected( $where, 'urls' ); ?>><?php esc_html_e( 'URL specifiche', 'bannermonster' ); ?></option>
						<option value="taxonomies" <?php selected( $where, 'taxonomies' ); ?>><?php esc_html_e( 'Tassonomie', 'bannermonster' ); ?></option>
					</select>
				</td>
			</tr>
		</table>

		<?php /* Pagine */ ?>
		<div class="bm-display-section" data-show="specific_pages" <?php echo esc_attr( $where === 'specific_pages' ? '' : 'style="display:none"' ); ?>>
			<h4><?php esc_html_e( 'Seleziona pagine', 'bannermonster' ); ?></h4>
			<?php $this->picker_pages( $m['bm_show_on_pages'] ); ?>
		</div>

		<?php /* Post */ ?>
		<div class="bm-display-section" data-show="specific_posts" <?php echo esc_attr( $where === 'specific_posts' ? '' : 'style="display:none"' ); ?>>
			<h4><?php esc_html_e( 'Seleziona post', 'bannermonster' ); ?></h4>
			<?php $this->picker_posts( $m['bm_show_on_posts'] ); ?>
		</div>

		<?php /* CPT */ ?>
		<div class="bm-display-section" data-show="specific_cpts" <?php echo esc_attr( $where === 'specific_cpts' ? '' : 'style="display:none"' ); ?>>
			<h4><?php esc_html_e( 'Seleziona CPT', 'bannermonster' ); ?></h4>
			<?php $this->picker_cpts( $m['bm_show_on_cpts'] ); ?>
		</div>

		<?php /* URL */ ?>
		<div class="bm-display-section" data-show="urls" <?php echo esc_attr( $where === 'urls' ? '' : 'style="display:none"' ); ?>>
			<h4><?php esc_html_e( 'URL (una per riga)', 'bannermonster' ); ?></h4>
			<textarea name="bm_show_on_urls" class="large-text" rows="4" placeholder="https://example.com/pagina"><?php echo esc_textarea( $m['bm_show_on_urls'] ); ?></textarea>
			<p class="description"><?php esc_html_e('Confronto parziale: basta che l\'URL contenga la stringa inserita.', 'bannermonster'); ?></p>
		</div>

		<?php /* Tassonomie */ ?>
		<div class="bm-display-section" data-show="taxonomies" <?php echo esc_attr( $where === 'taxonomies' ? '' : 'style="display:none"' ); ?>>
			<h4><?php esc_html_e( 'Seleziona tassonomie', 'bannermonster' ); ?></h4>
			<?php $this->picker_taxonomies( $m['bm_show_on_taxonomies'] ); ?>
		</div>
		<?php
	}

	/* ---- Metabox: Trigger ---- */

	public function box_trigger( $post ) {
		$m = BannerMonster_CPT::get_meta( $post->ID );
		$trigger = $m['bm_trigger'];
		$debug_active = isset( $_GET['bm_debug'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['bm_debug'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		?>
		<?php if ( $debug_active ) : ?>
			<div class="notice notice-warning inline" style="margin:10px 0"><p><strong>BannerMonster Debug:</strong> <?php esc_html_e( 'Modalità debug attiva - il cookie di chiusura viene ignorato.', 'bannermonster' ); ?></p></div>
		<?php endif; ?>
		<table class="form-table bm-table">
			<tr>
				<th><label for="bm_trigger"><?php esc_html_e( 'Tipo di trigger', 'bannermonster' ); ?></label></th>
				<td>
					<select name="bm_trigger" id="bm_trigger">
						<option value="immediate" <?php selected( $trigger, 'immediate' ); ?>><?php esc_html_e( 'Immediato', 'bannermonster' ); ?></option>
						<option value="timer" <?php selected( $trigger, 'timer' ); ?>><?php esc_html_e( 'Dopo X secondi', 'bannermonster' ); ?></option>
						<option value="exit_intent" <?php selected( $trigger, 'exit_intent' ); ?>><?php esc_html_e( 'Intento di chiusura scheda', 'bannermonster' ); ?></option>
						<option value="scroll" <?php selected( $trigger, 'scroll' ); ?>><?php esc_html_e( 'A X% di scroll', 'bannermonster' ); ?></option>
					</select>
				</td>
			</tr>
			<tr class="bm-trigger-opt" data-trigger="timer" <?php echo esc_attr( $trigger === 'timer' ? '' : 'style="display:none"' ); ?>>
				<th><label for="bm_trigger_seconds"><?php esc_html_e( 'Secondi', 'bannermonster' ); ?></label></th>
				<td><input type="number" name="bm_trigger_seconds" id="bm_trigger_seconds" value="<?php echo esc_attr( $m['bm_trigger_seconds'] ); ?>" min="1" max="300" class="small-text"></td>
			</tr>
			<tr class="bm-trigger-opt" data-trigger="scroll" <?php echo esc_attr( $trigger === 'scroll' ? '' : 'style="display:none"' ); ?>>
				<th><label for="bm_trigger_scroll"><?php esc_html_e( 'Percentuale scroll', 'bannermonster' ); ?></label></th>
				<td><input type="number" name="bm_trigger_scroll" id="bm_trigger_scroll" value="<?php echo esc_attr( $m['bm_trigger_scroll'] ); ?>" min="1" max="100" class="small-text"> %</td>
			</tr>
			<tr>
				<th><label for="bm_reappear"><?php esc_html_e( 'Ricompari dopo (min)', 'bannermonster' ); ?></label></th>
				<td>
					<input type="number" name="bm_reappear" id="bm_reappear" value="<?php echo esc_attr( $m['bm_reappear'] ); ?>" min="0" max="525600" class="small-text">
					<p class="description"><?php esc_html_e( 'Minuti prima che il banner ricompaia dopo la chiusura. 0 = non ricomparire mai più.', 'bannermonster' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/* ---- Metabox: Stile ---- */

	public function box_style( $post ) {
		$m = BannerMonster_CPT::get_meta( $post->ID );
		?>
		<table class="form-table bm-table">
			<tr>
				<th><label for="bm_bg_color"><?php esc_html_e( 'Sfondo', 'bannermonster' ); ?></label></th>
				<td><input type="text" name="bm_bg_color" id="bm_bg_color" value="<?php echo esc_attr( $m['bm_bg_color'] ); ?>" class="bm-color"></td>
			</tr>
			<tr>
				<th><label for="bm_text_color"><?php esc_html_e( 'Testo', 'bannermonster' ); ?></label></th>
				<td><input type="text" name="bm_text_color" id="bm_text_color" value="<?php echo esc_attr( $m['bm_text_color'] ); ?>" class="bm-color"></td>
			</tr>
			<tr>
				<th><label for="bm_border_color"><?php esc_html_e( 'Bordo', 'bannermonster' ); ?></label></th>
				<td><input type="text" name="bm_border_color" id="bm_border_color" value="<?php echo esc_attr( $m['bm_border_color'] ); ?>" class="bm-color"></td>
			</tr>
			<tr>
				<th><label for="bm_border_width"><?php esc_html_e( 'Spessore bordo (px)', 'bannermonster' ); ?></label></th>
				<td><input type="number" name="bm_border_width" id="bm_border_width" value="<?php echo esc_attr( $m['bm_border_width'] ); ?>" min="0" max="20" class="small-text"></td>
			</tr>
			<tr>
				<th><label for="bm_padding"><?php esc_html_e( 'Padding (px)', 'bannermonster' ); ?></label></th>
				<td><input type="number" name="bm_padding" id="bm_padding" value="<?php echo esc_attr( $m['bm_padding'] ); ?>" min="0" max="100" class="small-text"></td>
			</tr>
			<tr>
				<th><label for="bm_font_size"><?php esc_html_e( 'Font size (px)', 'bannermonster' ); ?></label></th>
				<td><input type="number" name="bm_font_size" id="bm_font_size" value="<?php echo esc_attr( $m['bm_font_size'] ); ?>" min="10" max="48" class="small-text"></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Pulsante X', 'bannermonster' ); ?></th>
				<td><label><input type="checkbox" name="bm_close_enabled" value="1" <?php checked( $m['bm_close_enabled'], 1 ); ?>> <?php esc_html_e( 'Mostra pulsante di chiusura', 'bannermonster' ); ?></label></td>
			</tr>
			<tr>
				<th><label for="bm_css_class"><?php esc_html_e( 'Classi CSS', 'bannermonster' ); ?></label></th>
				<td><input type="text" name="bm_css_class" id="bm_css_class" value="<?php echo esc_attr( $m['bm_css_class'] ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th><label for="bm_custom_css"><?php esc_html_e( 'CSS personalizzato', 'bannermonster' ); ?></label></th>
				<td><textarea name="bm_custom_css" id="bm_custom_css" class="large-text code" rows="4"><?php echo esc_textarea( $m['bm_custom_css'] ); ?></textarea></td>
			</tr>
		</table>
		<?php
	}

	/* ---- Save ---- */

	public function save( $post_id, $post ) {
		$raw_nonce = isset( $_POST['bm_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['bm_nonce'] ) ) : '';
		if ( ! $raw_nonce || ! wp_verify_nonce( $raw_nonce, self::NONCE ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$sanitize = function ( $key, $filter = 'sanitize_text_field' ) {
			return isset( $_POST[ $key ] ) ? call_user_func( $filter, wp_unslash( $_POST[ $key ] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		};

		$checkbox = function ( $key ) {
			return isset( $_POST[ $key ] ) ? 1 : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		};

		$int = function ( $key ) {
			return isset( $_POST[ $key ] ) ? absint( $_POST[ $key ] ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		};

		// Tipo
		$type = $sanitize( 'bm_type' );
		$allowed = array( 'banner_top', 'banner_bottom', 'popup_center', 'popup_bottom_right', 'popup_bottom_left' );
		if ( $type && in_array( $type, $allowed, true ) ) {
			update_post_meta( $post_id, 'bm_type', $type );
		}

		update_post_meta( $post_id, 'bm_enabled', $checkbox( 'bm_enabled' ) );
		update_post_meta( $post_id, 'bm_overlay', $checkbox( 'bm_overlay' ) );
		update_post_meta( $post_id, 'bm_close_on_click', $checkbox( 'bm_close_on_click' ) );
		update_post_meta( $post_id, 'bm_close_enabled', $checkbox( 'bm_close_enabled' ) );

		$width = $int( 'bm_width' );
		update_post_meta( $post_id, 'bm_width', $width ? min( $width, 100 ) : 100 );

		$max_w = $int( 'bm_max_width' );
		update_post_meta( $post_id, 'bm_max_width', $max_w ? min( $max_w, 1200 ) : 600 );

		// Display
		$display = $sanitize( 'bm_display_where' );
		$display_allowed = array( 'all', 'posts', 'cpts', 'specific_posts', 'specific_pages', 'specific_cpts', 'urls', 'taxonomies' );
		if ( $display && in_array( $display, $display_allowed, true ) ) {
			update_post_meta( $post_id, 'bm_display_where', $display );
		}

		$pages = isset( $_POST['bm_show_on_pages'] ) && is_array( $_POST['bm_show_on_pages'] ) ? array_map( 'absint', wp_unslash( $_POST['bm_show_on_pages'] ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_post_meta( $post_id, 'bm_show_on_pages', $pages );

		$posts = isset( $_POST['bm_show_on_posts'] ) && is_array( $_POST['bm_show_on_posts'] ) ? array_map( 'absint', wp_unslash( $_POST['bm_show_on_posts'] ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_post_meta( $post_id, 'bm_show_on_posts', $posts );

		$cpts = isset( $_POST['bm_show_on_cpts'] ) && is_array( $_POST['bm_show_on_cpts'] ) ? array_map( 'absint', wp_unslash( $_POST['bm_show_on_cpts'] ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_post_meta( $post_id, 'bm_show_on_cpts', $cpts );

		update_post_meta( $post_id, 'bm_show_on_urls', $sanitize( 'bm_show_on_urls', 'sanitize_textarea_field' ) );

		$taxonomies = array();
		$raw_taxonomies = isset( $_POST['bm_show_on_taxonomies'] ) ? wp_unslash( $_POST['bm_show_on_taxonomies'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( is_array( $raw_taxonomies ) ) {
			foreach ( $raw_taxonomies as $tax => $terms ) {
				if ( ! is_array( $terms ) ) {
					continue;
				}
				$tax = sanitize_text_field( $tax );
				if ( ! taxonomy_exists( $tax ) ) {
					continue;
				}
				foreach ( $terms as $tid ) {
					$taxonomies[] = array( 'tax' => $tax, 'term_id' => absint( $tid ) );
				}
			}
		}
		update_post_meta( $post_id, 'bm_show_on_taxonomies', $taxonomies );

		// Trigger
		$trigger = $sanitize( 'bm_trigger' );
		$trigger_allowed = array( 'immediate', 'timer', 'exit_intent', 'scroll' );
		if ( $trigger && in_array( $trigger, $trigger_allowed, true ) ) {
			update_post_meta( $post_id, 'bm_trigger', $trigger );
		}

		$seconds = $int( 'bm_trigger_seconds' );
		update_post_meta( $post_id, 'bm_trigger_seconds', $seconds ? min( $seconds, 300 ) : 5 );

		$scroll = $int( 'bm_trigger_scroll' );
		update_post_meta( $post_id, 'bm_trigger_scroll', $scroll ? min( $scroll, 100 ) : 50 );

		$reappear = $int( 'bm_reappear' );
		update_post_meta( $post_id, 'bm_reappear', $reappear ? min( $reappear, 525600 ) : 0 );

		// Stile
		update_post_meta( $post_id, 'bm_bg_color', sanitize_hex_color( wp_unslash( $_POST['bm_bg_color'] ?? '' ) ) ?: '#0073aa' );
		update_post_meta( $post_id, 'bm_text_color', sanitize_hex_color( wp_unslash( $_POST['bm_text_color'] ?? '' ) ) ?: '#ffffff' );
		update_post_meta( $post_id, 'bm_border_color', sanitize_hex_color( wp_unslash( $_POST['bm_border_color'] ?? '' ) ) );

		$bw = $int( 'bm_border_width' );
		update_post_meta( $post_id, 'bm_border_width', $bw ? min( $bw, 20 ) : 0 );

		$pad = $int( 'bm_padding' );
		update_post_meta( $post_id, 'bm_padding', $pad ? min( $pad, 100 ) : 15 );

		$fs = $int( 'bm_font_size' );
		update_post_meta( $post_id, 'bm_font_size', $fs ? min( $fs, 48 ) : 16 );

		update_post_meta( $post_id, 'bm_css_class', $sanitize( 'bm_css_class' ) );
		update_post_meta( $post_id, 'bm_custom_css', $this->sanitize_css( wp_unslash( $_POST['bm_custom_css'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		BannerMonster_CPT::clear_cache();
	}

	/* ---- Meta updated/deleted callbacks ---- */

	public function on_meta_updated( $meta_id, $post_id, $meta_key ) {
		if ( strpos( $meta_key, 'bm_' ) === 0 ) {
			BannerMonster_CPT::clear_cache();
		}
	}

	public function on_meta_deleted( $meta_id, $post_id, $meta_key ) {
		if ( strpos( $meta_key, 'bm_' ) === 0 ) {
			BannerMonster_CPT::clear_cache();
		}
	}

	/* ---- CSS Sanitizer ---- */

	private function sanitize_css( $css ) {
		$css = wp_strip_all_tags( $css );
		$css = preg_replace( '/\/\*.*?\*\//s', '', $css );
		$css = preg_replace( '/expression\s*\(/i', '', $css );
		$css = preg_replace( '/@import/i', '', $css );
		$css = preg_replace( '/url\s*\(/i', '', $css );
		$css = preg_replace( '/behavior\s*:/i', '', $css );
		$css = preg_replace( '/-moz-binding\s*:/i', '', $css );
		return trim( $css );
	}

	/* ---- Pickers (lazy-loaded) ---- */

	private function picker_pages( $selected ) {
		$selected = array_map( 'absint', (array) $selected );
		$pages = get_pages( array( 'post_status' => 'publish', 'number' => 0, 'fields' => 'all' ) );
		if ( empty( $pages ) ) {
			echo '<p>' . esc_html__( 'Nessuna pagina trovata.', 'bannermonster' ) . '</p>';
			return;
		}
		echo '<select name="bm_show_on_pages[]" multiple class="bm-multi">';
		foreach ( $pages as $p ) {
			printf(
				'<option value="%d" %s>%s</option>',
				absint( $p->ID ),
				in_array( $p->ID, $selected, true ) ? 'selected' : '',
				esc_html( $p->post_title )
			);
		}
		echo '</select>';
	}

	private function picker_posts( $selected ) {
		$selected = array_map( 'absint', (array) $selected );
		$posts = get_posts( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 100,
			'fields'         => 'all',
		) );
		if ( empty( $posts ) ) {
			echo '<p>' . esc_html__( 'Nessun post trovato.', 'bannermonster' ) . '</p>';
			return;
		}
		echo '<select name="bm_show_on_posts[]" multiple class="bm-multi">';
		foreach ( $posts as $p ) {
			printf(
				'<option value="%d" %s>%s</option>',
				absint( $p->ID ),
				in_array( $p->ID, $selected, true ) ? 'selected' : '',
				esc_html( $p->post_title )
			);
		}
		echo '</select>';
	}

	private function picker_cpts( $selected ) {
		$selected = array_map( 'absint', (array) $selected );
		$cpts = get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' );
		if ( empty( $cpts ) ) {
			echo '<p>' . esc_html__( 'Nessun CPT trovato.', 'bannermonster' ) . '</p>';
			return;
		}
		foreach ( $cpts as $cpt ) {
			$items = get_posts( array(
				'post_type'      => $cpt->name,
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'fields'         => 'all',
			) );
			if ( empty( $items ) ) {
				continue;
			}
			echo '<p><strong>' . esc_html( $cpt->label ) . '</strong></p>';
			echo '<select name="bm_show_on_cpts[]" multiple class="bm-multi">';
			foreach ( $items as $p ) {
				printf(
					'<option value="%d" %s>%s</option>',
					absint( $p->ID ),
					in_array( $p->ID, $selected, true ) ? 'selected' : '',
					esc_html( $p->post_title )
				);
			}
			echo '</select>';
		}
	}

	private function picker_taxonomies( $selected ) {
		$selected = (array) $selected;
		$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
		if ( empty( $taxonomies ) ) {
			echo '<p>' . esc_html__( 'Nessuna tassonomia trovata.', 'bannermonster' ) . '</p>';
			return;
		}
		foreach ( $taxonomies as $tax ) {
			$terms = get_terms( array( 'taxonomy' => $tax->name, 'hide_empty' => true ) );
			if ( empty( $terms ) || is_wp_error( $terms ) ) {
				continue;
			}
			echo '<p><strong>' . esc_html( $tax->label ) . '</strong></p>';
			echo '<select name="bm_show_on_taxonomies[' . esc_attr( $tax->name ) . '][]" multiple class="bm-multi">';
			foreach ( $terms as $term ) {
				$is_sel = false;
				foreach ( $selected as $s ) {
					if ( is_array( $s ) && ( $s['tax'] ?? '' ) === $tax->name && (int) ( $s['term_id'] ?? 0 ) === $term->term_id ) {
						$is_sel = true;
						break;
					}
				}
				printf(
					'<option value="%d" %s>%s (%d)</option>',
					absint( $term->term_id ),
					$is_sel ? 'selected' : '',
					esc_html( $term->name ),
					absint( $term->count )
				);
			}
			echo '</select>';
		}
	}
}
