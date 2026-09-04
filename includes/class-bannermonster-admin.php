<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BannerMonster_Admin {

	const NONCE = 'bannermonster_save_metabox';

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

		wp_enqueue_style( 'bannermonster-admin', BANNERMONSTER_URL . 'admin/css/admin.css', array(), BANNERMONSTER_VERSION );
		wp_enqueue_script( 'bannermonster-admin', BANNERMONSTER_URL . 'admin/js/admin.js', array( 'jquery' ), BANNERMONSTER_VERSION, true );
	}

	public function register_metaboxes() {
		add_meta_box( 'bannermonster_type', __( 'Tipo & Configurazione', 'bannermonster' ), array( $this, 'box_type' ), BannerMonster_CPT::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'bannermonster_display', __( 'Regole di Visualizzazione', 'bannermonster' ), array( $this, 'box_display' ), BannerMonster_CPT::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'bannermonster_trigger', __( 'Trigger', 'bannermonster' ), array( $this, 'box_trigger' ), BannerMonster_CPT::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'bannermonster_style', __( 'Stile & Personalizzazione', 'bannermonster' ), array( $this, 'box_style' ), BannerMonster_CPT::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'bannermonster_debug', __( 'Debug & Controllo', 'bannermonster' ), array( $this, 'box_debug' ), BannerMonster_CPT::POST_TYPE, 'normal', 'default' );
	}

	/* ---- Metabox: Tipo ---- */

	public function box_type( $post ) {
		wp_nonce_field( self::NONCE, 'bannermonster_nonce' );
		$m = BannerMonster_CPT::get_meta( $post->ID );
		?>
		<table class="form-table bannermonster-table">
			<tr>
				<th><label for="bannermonster_type"><?php esc_html_e( 'Tipo di elemento', 'bannermonster' ); ?></label></th>
				<td>
					<select name="bannermonster_type" id="bannermonster_type">
						<optgroup label="<?php esc_attr_e( 'Banner', 'bannermonster' ); ?>">
							<option value="banner_top" <?php selected( $m['bannermonster_type'], 'banner_top' ); ?>><?php esc_html_e( 'Barra in alto (fissa)', 'bannermonster' ); ?></option>
							<option value="banner_bottom" <?php selected( $m['bannermonster_type'], 'banner_bottom' ); ?>><?php esc_html_e( 'Barra in basso (fissa)', 'bannermonster' ); ?></option>
						</optgroup>
						<optgroup label="<?php esc_attr_e( 'Popup', 'bannermonster' ); ?>">
							<option value="popup_center" <?php selected( $m['bannermonster_type'], 'popup_center' ); ?>><?php esc_html_e( 'Centrale', 'bannermonster' ); ?></option>
							<option value="popup_bottom_right" <?php selected( $m['bannermonster_type'], 'popup_bottom_right' ); ?>><?php esc_html_e( 'Basso a destra', 'bannermonster' ); ?></option>
							<option value="popup_bottom_left" <?php selected( $m['bannermonster_type'], 'popup_bottom_left' ); ?>><?php esc_html_e( 'Basso a sinistra', 'bannermonster' ); ?></option>
						</optgroup>
					</select>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Stato', 'bannermonster' ); ?></th>
				<td><label><input type="checkbox" name="bannermonster_enabled" value="1" <?php checked( $m['bannermonster_enabled'], 1 ); ?>> <?php esc_html_e( 'Attivo', 'bannermonster' ); ?></label></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Overlay (popup)', 'bannermonster' ); ?></th>
				<td><label><input type="checkbox" name="bannermonster_overlay" value="1" <?php checked( $m['bannermonster_overlay'], 1 ); ?>> <?php esc_html_e( 'Mostra overlay scuro', 'bannermonster' ); ?></label></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Chiudi su overlay', 'bannermonster' ); ?></th>
				<td><label><input type="checkbox" name="bannermonster_close_on_click" value="1" <?php checked( $m['bannermonster_close_on_click'], 1 ); ?>> <?php esc_html_e( 'Chiudi cliccando fuori', 'bannermonster' ); ?></label></td>
			</tr>
			<tr>
				<th><label for="bannermonster_width"><?php esc_html_e( 'Larghezza (%)', 'bannermonster' ); ?></label></th>
				<td><input type="number" name="bannermonster_width" id="bannermonster_width" value="<?php echo esc_attr( $m['bannermonster_width'] ); ?>" min="10" max="100" class="small-text"></td>
			</tr>
			<tr>
				<th><label for="bannermonster_max_width"><?php esc_html_e( 'Max width (px)', 'bannermonster' ); ?></label></th>
				<td><input type="number" name="bannermonster_max_width" id="bannermonster_max_width" value="<?php echo esc_attr( $m['bannermonster_max_width'] ); ?>" min="200" max="1200" class="small-text"></td>
			</tr>
		</table>
		<?php
	}

	/* ---- Metabox: Display Rules ---- */

	public function box_display( $post ) {
		$m = BannerMonster_CPT::get_meta( $post->ID );
		$where = $m['bannermonster_display_where'];
		?>
		<table class="form-table bannermonster-table">
			<tr>
				<th><label for="bannermonster_display_where"><?php esc_html_e( 'Dove visualizzare', 'bannermonster' ); ?></label></th>
				<td>
					<select name="bannermonster_display_where" id="bannermonster_display_where">
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
		<div class="bannermonster-display-section" data-show="specific_pages" <?php echo esc_attr( $where === 'specific_pages' ? '' : 'style="display:none"' ); ?>>
			<h4><?php esc_html_e( 'Seleziona pagine', 'bannermonster' ); ?></h4>
			<?php $this->picker_pages( $m['bannermonster_show_on_pages'] ); ?>
		</div>

		<?php /* Post */ ?>
		<div class="bannermonster-display-section" data-show="specific_posts" <?php echo esc_attr( $where === 'specific_posts' ? '' : 'style="display:none"' ); ?>>
			<h4><?php esc_html_e( 'Seleziona post', 'bannermonster' ); ?></h4>
			<?php $this->picker_posts( $m['bannermonster_show_on_posts'] ); ?>
		</div>

		<?php /* CPT */ ?>
		<div class="bannermonster-display-section" data-show="specific_cpts" <?php echo esc_attr( $where === 'specific_cpts' ? '' : 'style="display:none"' ); ?>>
			<h4><?php esc_html_e( 'Seleziona CPT', 'bannermonster' ); ?></h4>
			<?php $this->picker_cpts( $m['bannermonster_show_on_cpts'] ); ?>
		</div>

		<?php /* URL */ ?>
		<div class="bannermonster-display-section" data-show="urls" <?php echo esc_attr( $where === 'urls' ? '' : 'style="display:none"' ); ?>>
			<h4><?php esc_html_e( 'URL (una per riga)', 'bannermonster' ); ?></h4>
			<textarea name="bannermonster_show_on_urls" class="large-text" rows="4" placeholder="https://example.com/pagina"><?php echo esc_textarea( $m['bannermonster_show_on_urls'] ); ?></textarea>
			<p class="description"><?php esc_html_e('Confronto parziale: basta che l\'URL contenga la stringa inserita.', 'bannermonster'); ?></p>
		</div>

		<?php /* Tassonomie */ ?>
		<div class="bannermonster-display-section" data-show="taxonomies" <?php echo esc_attr( $where === 'taxonomies' ? '' : 'style="display:none"' ); ?>>
			<h4><?php esc_html_e( 'Seleziona tassonomie', 'bannermonster' ); ?></h4>
			<?php $this->picker_taxonomies( $m['bannermonster_show_on_taxonomies'] ); ?>
		</div>
		<?php
	}

	/* ---- Metabox: Trigger ---- */

	public function box_trigger( $post ) {
		$m = BannerMonster_CPT::get_meta( $post->ID );
		$trigger = $m['bannermonster_trigger'];
		$debug_active = isset( $_GET['bannermonster_debug'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['bannermonster_debug'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		?>
		<?php if ( $debug_active ) : ?>
			<div class="notice notice-warning inline" style="margin:10px 0"><p><strong>BannerMonster Debug:</strong> <?php esc_html_e( 'Modalità debug attiva - il cookie di chiusura viene ignorato.', 'bannermonster' ); ?></p></div>
		<?php endif; ?>
		<table class="form-table bannermonster-table">
			<tr>
				<th><label for="bannermonster_trigger"><?php esc_html_e( 'Tipo di trigger', 'bannermonster' ); ?></label></th>
				<td>
					<select name="bannermonster_trigger" id="bannermonster_trigger">
						<option value="immediate" <?php selected( $trigger, 'immediate' ); ?>><?php esc_html_e( 'Immediato', 'bannermonster' ); ?></option>
						<option value="timer" <?php selected( $trigger, 'timer' ); ?>><?php esc_html_e( 'Dopo X secondi', 'bannermonster' ); ?></option>
						<option value="exit_intent" <?php selected( $trigger, 'exit_intent' ); ?>><?php esc_html_e( 'Intento di chiusura scheda', 'bannermonster' ); ?></option>
						<option value="scroll" <?php selected( $trigger, 'scroll' ); ?>><?php esc_html_e( 'A X% di scroll', 'bannermonster' ); ?></option>
					</select>
				</td>
			</tr>
			<tr class="bannermonster-trigger-opt" data-trigger="timer" <?php echo esc_attr( $trigger === 'timer' ? '' : 'style="display:none"' ); ?>>
				<th><label for="bannermonster_trigger_seconds"><?php esc_html_e( 'Secondi', 'bannermonster' ); ?></label></th>
				<td><input type="number" name="bannermonster_trigger_seconds" id="bannermonster_trigger_seconds" value="<?php echo esc_attr( $m['bannermonster_trigger_seconds'] ); ?>" min="1" max="300" class="small-text"></td>
			</tr>
			<tr class="bannermonster-trigger-opt" data-trigger="scroll" <?php echo esc_attr( $trigger === 'scroll' ? '' : 'style="display:none"' ); ?>>
				<th><label for="bannermonster_trigger_scroll"><?php esc_html_e( 'Percentuale scroll', 'bannermonster' ); ?></label></th>
				<td><input type="number" name="bannermonster_trigger_scroll" id="bannermonster_trigger_scroll" value="<?php echo esc_attr( $m['bannermonster_trigger_scroll'] ); ?>" min="1" max="100" class="small-text"> %</td>
			</tr>
			<tr>
				<th><label for="bannermonster_reappear"><?php esc_html_e( 'Ricompari dopo (min)', 'bannermonster' ); ?></label></th>
				<td>
					<input type="number" name="bannermonster_reappear" id="bannermonster_reappear" value="<?php echo esc_attr( $m['bannermonster_reappear'] ); ?>" min="0" max="525600" class="small-text">
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
		<table class="form-table bannermonster-table">
			<tr>
				<th><label for="bannermonster_bg_color"><?php esc_html_e( 'Sfondo', 'bannermonster' ); ?></label></th>
				<td><input type="text" name="bannermonster_bg_color" id="bannermonster_bg_color" value="<?php echo esc_attr( $m['bannermonster_bg_color'] ); ?>" class="bannermonster-color"></td>
			</tr>
			<tr>
				<th><label for="bannermonster_text_color"><?php esc_html_e( 'Testo', 'bannermonster' ); ?></label></th>
				<td><input type="text" name="bannermonster_text_color" id="bannermonster_text_color" value="<?php echo esc_attr( $m['bannermonster_text_color'] ); ?>" class="bannermonster-color"></td>
			</tr>
			<tr>
				<th><label for="bannermonster_border_color"><?php esc_html_e( 'Bordo', 'bannermonster' ); ?></label></th>
				<td><input type="text" name="bannermonster_border_color" id="bannermonster_border_color" value="<?php echo esc_attr( $m['bannermonster_border_color'] ); ?>" class="bannermonster-color"></td>
			</tr>
			<tr>
				<th><label for="bannermonster_border_width"><?php esc_html_e( 'Spessore bordo (px)', 'bannermonster' ); ?></label></th>
				<td><input type="number" name="bannermonster_border_width" id="bannermonster_border_width" value="<?php echo esc_attr( $m['bannermonster_border_width'] ); ?>" min="0" max="20" class="small-text"></td>
			</tr>
			<tr>
				<th><label for="bannermonster_padding"><?php esc_html_e( 'Padding (px)', 'bannermonster' ); ?></label></th>
				<td><input type="number" name="bannermonster_padding" id="bannermonster_padding" value="<?php echo esc_attr( $m['bannermonster_padding'] ); ?>" min="0" max="100" class="small-text"></td>
			</tr>
			<tr>
				<th><label for="bannermonster_font_size"><?php esc_html_e( 'Font size (px)', 'bannermonster' ); ?></label></th>
				<td><input type="number" name="bannermonster_font_size" id="bannermonster_font_size" value="<?php echo esc_attr( $m['bannermonster_font_size'] ); ?>" min="10" max="48" class="small-text"></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Pulsante X', 'bannermonster' ); ?></th>
				<td><label><input type="checkbox" name="bannermonster_close_enabled" value="1" <?php checked( $m['bannermonster_close_enabled'], 1 ); ?>> <?php esc_html_e( 'Mostra pulsante di chiusura', 'bannermonster' ); ?></label></td>
			</tr>
			<tr>
				<th><label for="bannermonster_css_class"><?php esc_html_e( 'Classi CSS', 'bannermonster' ); ?></label></th>
				<td><input type="text" name="bannermonster_css_class" id="bannermonster_css_class" value="<?php echo esc_attr( $m['bannermonster_css_class'] ); ?>" class="regular-text"></td>
			</tr>
		</table>
		<?php
	}

	/* ---- Metabox: Debug & Controllo ---- */

	public function box_debug() {
		$site_url = home_url();
		?>
		<div style="max-width: 700px;">
			<h4 style="margin-top:0"><?php esc_html_e( 'Modalit&agrave; Debug', 'bannermonster' ); ?></h4>
			<p><?php esc_html_e( 'Il mode debug ti permette di testare i banner ignorando il localStorage del browser. Quando attivo, tutti i banner vengono mostrati ad ogni caricamento pagina, indipendentemente dal fatto che siano stati chiusi in precedenza.', 'bannermonster' ); ?></p>

			<table class="form-table bannermonster-table">
				<tr>
					<th style="width:180px"><?php esc_html_e( 'URL di test', 'bannermonster' ); ?></th>
					<td>
						<code style="background:#f1f1f1; padding:6px 12px; border-radius:4px; display:inline-block; word-break:break-all; font-size:13px;">
							<?php echo esc_html( $site_url ); ?>/?bannermonster_debug=1
						</code>
						<p class="description" style="margin-top:6px;"><?php esc_html_e( 'Aggiungi questo parametro a qualsiasi URL del tuo sito per attivare il debug.', 'bannermonster' ); ?></p>
					</td>
			 </tr>
			</table>

			<h4><?php esc_html_e( 'Come funziona', 'bannermonster' ); ?></h4>
			<ol style="line-height:2; padding-left:20px; color:#555;">
				<li><?php esc_html_e( 'Aggiungi <code>?bannermonster_debug=1</code> alla fine di qualsiasi URL.', 'bannermonster' ); ?></li>
				<li><?php esc_html_e( 'Tutti i banner attivi appariranno ad ogni caricamento pagina.', 'bannermonster' ); ?></li>
				<li><?php esc_html_e( 'Il localStorage viene ignorato: anche se il visitatore ha chiuso il banner, questo ricomparir&agrave;.', 'bannermonster' ); ?></li>
				<li><?php esc_html_e( 'Nell\'admin metabox comparir&agrave; un avviso giallo che indica che la modalit&agrave; debug &egrave; attiva.', 'bannermonster' ); ?></li>
			</ol>

			<h4 style="margin-top:24px"><?php esc_html_e( 'Controllo Ricomparsa', 'bannermonster' ); ?></h4>
			<p><?php esc_html_e( 'Nel metabox <strong>Trigger</strong> puoi impostare il campo "Ricompari dopo (min)" per controllare quante minuti dopo la chiusura un banner debba essere mostrato di nuovo. Impostando 0, il banner non ricomparir&agrave; mai pi&ugrave; dopo la chiusura.', 'bannermonster' ); ?></p>

			<h4 style="margin-top:24px"><?php esc_html_e( 'Note tecniche', 'bannermonster' ); ?></h4>
			<ul style="line-height:2; padding-left:20px; color:#555;">
				<li><?php esc_html_e( 'Lo stato di chiusura viene salvato nel <code>localStorage</code> del browser con chiave <code>bannermonster_closed</code>.', 'bannermonster' ); ?></li>
				<li><?php esc_html_e( 'Il formato &egrave; un oggetto JSON: <code>{"ID": timestamp, ...}</code>.', 'bannermonster' ); ?></li>
				<li><?php esc_html_e( 'Il parametro <code>?bannermonster_debug=1</code> viene passato al frontend via <code>wp_localize_script</code>.', 'bannermonster' ); ?></li>
				<li><?php esc_html_e( 'Nessun cookie viene utilizzato: tutto il sistema si basa su localStorage.', 'bannermonster' ); ?></li>
			</ul>
		</div>
		<?php
	}

	/* ---- Save ---- */

	public function save( $post_id, $post ) {
		$raw_nonce = isset( $_POST['bannermonster_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['bannermonster_nonce'] ) ) : '';
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
		$type = $sanitize( 'bannermonster_type' );
		$allowed = array( 'banner_top', 'banner_bottom', 'popup_center', 'popup_bottom_right', 'popup_bottom_left' );
		if ( $type && in_array( $type, $allowed, true ) ) {
			update_post_meta( $post_id, 'bannermonster_type', $type );
		}

		update_post_meta( $post_id, 'bannermonster_enabled', $checkbox( 'bannermonster_enabled' ) );
		update_post_meta( $post_id, 'bannermonster_overlay', $checkbox( 'bannermonster_overlay' ) );
		update_post_meta( $post_id, 'bannermonster_close_on_click', $checkbox( 'bannermonster_close_on_click' ) );
		update_post_meta( $post_id, 'bannermonster_close_enabled', $checkbox( 'bannermonster_close_enabled' ) );

		$width = $int( 'bannermonster_width' );
		update_post_meta( $post_id, 'bannermonster_width', $width ? min( $width, 100 ) : 100 );

		$max_w = $int( 'bannermonster_max_width' );
		update_post_meta( $post_id, 'bannermonster_max_width', $max_w ? min( $max_w, 1200 ) : 600 );

		// Display
		$display = $sanitize( 'bannermonster_display_where' );
		$display_allowed = array( 'all', 'posts', 'cpts', 'specific_posts', 'specific_pages', 'specific_cpts', 'urls', 'taxonomies' );
		if ( $display && in_array( $display, $display_allowed, true ) ) {
			update_post_meta( $post_id, 'bannermonster_display_where', $display );
		}

		$pages = isset( $_POST['bannermonster_show_on_pages'] ) && is_array( $_POST['bannermonster_show_on_pages'] ) ? array_map( 'absint', wp_unslash( $_POST['bannermonster_show_on_pages'] ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_post_meta( $post_id, 'bannermonster_show_on_pages', $pages );

		$posts = isset( $_POST['bannermonster_show_on_posts'] ) && is_array( $_POST['bannermonster_show_on_posts'] ) ? array_map( 'absint', wp_unslash( $_POST['bannermonster_show_on_posts'] ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_post_meta( $post_id, 'bannermonster_show_on_posts', $posts );

		$cpts = isset( $_POST['bannermonster_show_on_cpts'] ) && is_array( $_POST['bannermonster_show_on_cpts'] ) ? array_map( 'absint', wp_unslash( $_POST['bannermonster_show_on_cpts'] ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_post_meta( $post_id, 'bannermonster_show_on_cpts', $cpts );

		update_post_meta( $post_id, 'bannermonster_show_on_urls', $sanitize( 'bannermonster_show_on_urls', 'sanitize_textarea_field' ) );

		$taxonomies = array();
		$raw_taxonomies = isset( $_POST['bannermonster_show_on_taxonomies'] ) ? wp_unslash( $_POST['bannermonster_show_on_taxonomies'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
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
		update_post_meta( $post_id, 'bannermonster_show_on_taxonomies', $taxonomies );

		// Trigger
		$trigger = $sanitize( 'bannermonster_trigger' );
		$trigger_allowed = array( 'immediate', 'timer', 'exit_intent', 'scroll' );
		if ( $trigger && in_array( $trigger, $trigger_allowed, true ) ) {
			update_post_meta( $post_id, 'bannermonster_trigger', $trigger );
		}

		$seconds = $int( 'bannermonster_trigger_seconds' );
		update_post_meta( $post_id, 'bannermonster_trigger_seconds', $seconds ? min( $seconds, 300 ) : 5 );

		$scroll = $int( 'bannermonster_trigger_scroll' );
		update_post_meta( $post_id, 'bannermonster_trigger_scroll', $scroll ? min( $scroll, 100 ) : 50 );

		$reappear = $int( 'bannermonster_reappear' );
		update_post_meta( $post_id, 'bannermonster_reappear', $reappear ? min( $reappear, 525600 ) : 0 );

		// Stile
		update_post_meta( $post_id, 'bannermonster_bg_color', sanitize_hex_color( wp_unslash( $_POST['bannermonster_bg_color'] ?? '' ) ) ?: '#0073aa' );
		update_post_meta( $post_id, 'bannermonster_text_color', sanitize_hex_color( wp_unslash( $_POST['bannermonster_text_color'] ?? '' ) ) ?: '#ffffff' );
		update_post_meta( $post_id, 'bannermonster_border_color', sanitize_hex_color( wp_unslash( $_POST['bannermonster_border_color'] ?? '' ) ) );

		$bw = $int( 'bannermonster_border_width' );
		update_post_meta( $post_id, 'bannermonster_border_width', $bw ? min( $bw, 20 ) : 0 );

		$pad = $int( 'bannermonster_padding' );
		update_post_meta( $post_id, 'bannermonster_padding', $pad ? min( $pad, 100 ) : 15 );

		$fs = $int( 'bannermonster_font_size' );
		update_post_meta( $post_id, 'bannermonster_font_size', $fs ? min( $fs, 48 ) : 16 );

		update_post_meta( $post_id, 'bannermonster_css_class', $sanitize( 'bannermonster_css_class' ) );

		BannerMonster_CPT::clear_cache();
	}

	/* ---- Meta updated/deleted callbacks ---- */

	public function on_meta_updated( $meta_id, $post_id, $meta_key ) {
		if ( strpos( $meta_key, 'bannermonster_' ) === 0 ) {
			BannerMonster_CPT::clear_cache();
		}
	}

	public function on_meta_deleted( $meta_id, $post_id, $meta_key ) {
		if ( strpos( $meta_key, 'bannermonster_' ) === 0 ) {
			BannerMonster_CPT::clear_cache();
		}
	}

	/* ---- Pickers (lazy-loaded) ---- */

	private function picker_pages( $selected ) {
		$selected = array_map( 'absint', (array) $selected );
		$pages = get_pages( array( 'post_status' => 'publish', 'number' => 0, 'fields' => 'all' ) );
		if ( empty( $pages ) ) {
			echo '<p>' . esc_html__( 'Nessuna pagina trovata.', 'bannermonster' ) . '</p>';
			return;
		}
		echo '<select name="bannermonster_show_on_pages[]" multiple class="bannermonster-multi">';
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
		echo '<select name="bannermonster_show_on_posts[]" multiple class="bannermonster-multi">';
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
			echo '<select name="bannermonster_show_on_cpts[]" multiple class="bannermonster-multi">';
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
			echo '<select name="bannermonster_show_on_taxonomies[' . esc_attr( $tax->name ) . '][]" multiple class="bannermonster-multi">';
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
