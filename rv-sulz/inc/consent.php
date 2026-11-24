<?php
/**
 * Einwilligung für Statistik (z. B. Google Analytics über das Plugin „Site Kit“).
 *
 * - Das externe Google-Skript wird beim Ausgeben „entschärft“ (type="text/plain", data-src) und lädt
 *   erst nach Klick auf „Erlauben“. Vorher gehen keine Daten an Google, es werden keine Cookies gesetzt.
 *   Die Konfiguration von Site Kit (gtag-Befehle) bleibt im Browser liegen und wird beim Laden nachgeholt.
 * - Banner mit gleichwertigen Knöpfen „Ablehnen“ / „Erlauben“, Link zur Datenschutzerklärung.
 * - „Cookie-Einstellungen“ in der Fußzeile öffnet das Banner wieder (Widerruf jederzeit).
 * - Die Wahl liegt nur im Browser (localStorage), nach 12 Monaten wird neu gefragt.
 * - Gibt es auf einer Seite kein Statistik-Skript, erscheint weder Banner noch Fußzeilen-Link.
 *
 * Weitere Skripte blockieren: Filter `rv_sulz_consent_handles` (Skript-Handles)
 * bzw. `rv_sulz_consent_hosts` (Adress-Teile).
 *
 * @package rv-sulz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wurde auf dieser Seite ein Skript blockiert?
 *
 * @param bool|null $set Setzen.
 * @return bool
 */
function rv_sulz_consent_blocked( $set = null ) {
	static $blocked = false;
	if ( null !== $set ) {
		$blocked = (bool) $set;
	}
	return $blocked;
}

/**
 * Externe Statistik-Skripte erst nach Einwilligung laden.
 *
 * @param string $tag    Script-Tag.
 * @param string $handle Handle.
 * @param string $src    Adresse.
 * @return string
 */
function rv_sulz_consent_script_tag( $tag, $handle, $src ) {
	if ( is_admin() || ! $src ) {
		return $tag;
	}
	$handles = apply_filters( 'rv_sulz_consent_handles', array( 'google_gtagjs' ) );
	$hosts   = apply_filters( 'rv_sulz_consent_hosts', array( 'googletagmanager.com/gtag/js', 'google-analytics.com/analytics.js' ) );
	$match   = in_array( $handle, $handles, true );
	foreach ( $hosts as $host ) {
		if ( false !== strpos( $src, $host ) ) {
			$match = true;
		}
	}
	if ( ! $match ) {
		return $tag;
	}
	rv_sulz_consent_blocked( true );
	// Nur das externe Skript entschärfen; davor/danach stehende Inline-Skripte bleiben unverändert.
	return preg_replace_callback(
		'#<script\b([^>]*)\bsrc=(["\'])([^"\']+)\2([^>]*)>\s*</script>#i',
		function ( $m ) use ( $src ) {
			if ( false === strpos( html_entity_decode( $m[3] ), wp_parse_url( $src, PHP_URL_HOST ) ) ) {
				return $m[0];
			}
			$attrs = preg_replace( '#\stype=(["\'])[^"\']*\1#i', '', $m[1] . $m[4] );
			return '<script type="text/plain" data-rv-consent="statistik" data-src="' . $m[3] . '"' . $attrs . '></script>';
		},
		$tag
	);
}
add_filter( 'script_loader_tag', 'rv_sulz_consent_script_tag', 20, 3 );

/**
 * Link „Cookie-Einstellungen“ an die Fußzeilen-Links anhängen (versteckt, das Skript blendet ihn ein).
 *
 * @param string $html  HTML.
 * @param array  $block Block.
 * @return string
 */
function rv_sulz_consent_footer_link( $html, $block ) {
	if ( empty( $block['attrs']['className'] ) || ! rv_sulz_has_class( $block['attrs'], 'rv-footer-links' ) ) {
		return $html;
	}
	$button = '<span class="rv-consent-open-wrap" hidden> · <button type="button" class="rv-consent-open">' . esc_html__( 'Cookie-Einstellungen', 'rv-sulz' ) . '</button></span>';
	$pos    = strrpos( $html, '</p>' );
	return false === $pos ? $html : substr_replace( $html, $button . '</p>', $pos, 4 );
}
add_filter( 'render_block', 'rv_sulz_consent_footer_link', 20, 2 );

/**
 * Banner, Styles und Skript in den Fuß der Seite – nur wenn etwas blockiert wurde.
 */
function rv_sulz_consent_footer() {
	if ( ! rv_sulz_consent_blocked() ) {
		return;
	}
	$privacy = get_privacy_policy_url();
	if ( ! $privacy ) {
		$privacy = home_url( '/datenschutz/' );
	}
	?>
	<div class="rv-consent" id="rv-consent" role="dialog" aria-modal="false" aria-labelledby="rv-consent-title" aria-describedby="rv-consent-text" hidden>
		<div class="rv-consent__box">
			<p class="rv-consent__title" id="rv-consent-title"><?php esc_html_e( 'Dürfen wir mitzählen?', 'rv-sulz' ); ?></p>
			<p class="rv-consent__text" id="rv-consent-text">
				<?php esc_html_e( 'Mit deiner Erlaubnis nutzen wir Google Analytics, um zu sehen, welche Seiten gelesen werden. Dabei werden Cookies gesetzt und Daten an Google übermittelt, auch in die USA. Ohne Erlaubnis funktioniert die Website genauso. Du kannst deine Wahl jederzeit unten unter „Cookie-Einstellungen“ ändern.', 'rv-sulz' ); ?>
				<a href="<?php echo esc_url( $privacy ); ?>"><?php esc_html_e( 'Mehr in der Datenschutzerklärung', 'rv-sulz' ); ?></a>
			</p>
			<div class="rv-consent__actions">
				<button type="button" class="rv-consent__btn" data-rv-consent-choice="0"><?php esc_html_e( 'Ablehnen', 'rv-sulz' ); ?></button>
				<button type="button" class="rv-consent__btn" data-rv-consent-choice="1"><?php esc_html_e( 'Erlauben', 'rv-sulz' ); ?></button>
			</div>
		</div>
	</div>
	<?php
	wp_print_inline_script_tag(
		(string) file_get_contents( get_template_directory() . '/assets/js/consent.js' ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		array( 'id' => 'rv-sulz-consent' )
	);
}
add_action( 'wp_footer', 'rv_sulz_consent_footer', 50 );
