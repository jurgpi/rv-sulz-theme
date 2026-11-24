<?php
/**
 * RV Sulz – Theme-Funktionen
 *
 * @package rv-sulz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Version kommt aus dem Kopf von style.css (einzige Stelle, die gepflegt wird – siehe CHANGELOG.md).
// Sie hängt an CSS-URLs (?ver=…), damit Browser nach einem Update neu laden.
define( 'RV_SULZ_VERSION', (string) wp_get_theme( get_template() )->get( 'Version' ) );

/**
 * Theme-Unterstützung.
 */
function rv_sulz_setup() {
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'style.css' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 200,
			'width'       => 200,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	load_theme_textdomain( 'rv-sulz', get_template_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'rv_sulz_setup' );

/**
 * Stylesheet einbinden (Block-Themes laden style.css nicht automatisch).
 */
function rv_sulz_enqueue() {
	wp_enqueue_style( 'rv-sulz', get_stylesheet_uri(), array(), RV_SULZ_VERSION );
}
add_action( 'wp_enqueue_scripts', 'rv_sulz_enqueue' );

/**
 * Plugin „Simple Calendar“: eigenes Design laden, sobald das Plugin seine Styles einbindet
 * (nach dessen CSS, damit unsere Regeln gewinnen). Auch für spät eingebundene Kalender (Footer).
 */
function rv_sulz_simcal_styles() {
	if ( wp_style_is( 'rv-sulz-simcal', 'enqueued' ) ) {
		return;
	}
	$deps = array();
	foreach ( array( 'simcal-qtip', 'simcal-default-calendar-common', 'simcal-default-calendar-grid', 'simcal-default-calendar-list' ) as $handle ) {
		if ( wp_style_is( $handle, 'enqueued' ) ) {
			$deps[] = $handle;
		}
	}
	if ( $deps ) {
		wp_enqueue_style( 'rv-sulz-simcal', get_template_directory_uri() . '/assets/css/simple-calendar.css', $deps, RV_SULZ_VERSION );
	}
}
add_action( 'wp_enqueue_scripts', 'rv_sulz_simcal_styles', 100 );
add_action( 'wp_footer', 'rv_sulz_simcal_styles', 5 );

/**
 * Plugin „Contact Form 7“: Formulare im Theme-Design (nach dem Plugin-CSS, damit unsere Regeln gewinnen).
 * Außerdem für das Muster „Kontakt“, auch wenn das Plugin seine Styles einmal nicht lädt.
 */
function rv_sulz_form_styles() {
	if ( wp_style_is( 'rv-sulz-forms', 'enqueued' ) ) {
		return;
	}
	$cf7     = wp_style_is( 'contact-form-7', 'enqueued' );
	$kontakt = false;
	if ( is_singular() ) {
		$post    = get_queried_object();
		$kontakt = $post instanceof WP_Post && false !== strpos( $post->post_content, 'rv-kontakt' );
	}
	if ( $cf7 || $kontakt ) {
		wp_enqueue_style( 'rv-sulz-forms', get_template_directory_uri() . '/assets/css/forms.css', $cf7 ? array( 'contact-form-7' ) : array(), RV_SULZ_VERSION );
	}
}
add_action( 'wp_enqueue_scripts', 'rv_sulz_form_styles', 100 );
add_action( 'wp_footer', 'rv_sulz_form_styles', 5 );

/**
 * Chronik-Seite: Design für den eigenen HTML-Block (.rvc) nur dort laden, wo er vorkommt.
 */
function rv_sulz_chronik_styles() {
	if ( ! is_singular() ) {
		return;
	}
	$post = get_queried_object();
	if ( $post instanceof WP_Post && false !== strpos( $post->post_content, 'class="rvc' ) ) {
		wp_enqueue_style( 'rv-sulz-chronik', get_template_directory_uri() . '/assets/css/chronik.css', array( 'rv-sulz' ), RV_SULZ_VERSION );
	}
}
add_action( 'wp_enqueue_scripts', 'rv_sulz_chronik_styles', 20 );

/**
 * Simple Calendar: leeres Ortsfeld (nur Meta-Tags, kein Text) entfernen,
 * sonst erscheint ein einzelnes Ortssymbol ohne Ort.
 *
 * @param string $output Shortcode-Ausgabe.
 * @param string $tag    Shortcode-Name.
 * @return string
 */
function rv_sulz_simcal_clean( $output, $tag ) {
	if ( 'calendar' !== $tag || false === strpos( (string) $output, 'simcal-event-address' ) ) {
		return $output;
	}
	return preg_replace( '#<span class="simcal-event-address[^"]*"[^>]*>(?:\s*<meta\b[^>]*>)*\s*</span>#', '', $output );
}
add_filter( 'do_shortcode_tag', 'rv_sulz_simcal_clean', 10, 2 );

require_once get_template_directory() . '/inc/menus.php';
require_once get_template_directory() . '/inc/frontpage.php';
require_once get_template_directory() . '/inc/mitmachen.php';
require_once get_template_directory() . '/inc/footer.php';
require_once get_template_directory() . '/inc/updater.php';
require_once get_template_directory() . '/inc/archiv.php';
require_once get_template_directory() . '/inc/consent.php';

/**
 * Dunkelmodus: früh im <head>, damit beim Laden nichts hell aufblitzt.
 * Ohne gespeicherte Wahl folgt die Seite der Systemeinstellung des Geräts;
 * der Umschalter im Kopfbereich speichert die Wahl im Browser (localStorage, keine Cookies).
 */
function rv_sulz_darkmode_script() {
	$js = <<<'JS'
(function () {
	var KEY = 'rv-sulz-theme', root = document.documentElement;
	var mq = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;
	function stored() { try { return localStorage.getItem(KEY); } catch (e) { return null; } }
	function apply() {
		var s = stored();
		var dark = s ? s === 'dark' : !!(mq && mq.matches);
		root.classList.toggle('rv-dark', dark);
		var btns = document.querySelectorAll('.rv-theme-toggle');
		for (var i = 0; i < btns.length; i++) {
			btns[i].setAttribute('aria-pressed', dark ? 'true' : 'false');
			btns[i].setAttribute('aria-label', dark ? 'Hellen Modus einschalten' : 'Dunkelmodus einschalten');
		}
	}
	apply();
	if (mq && mq.addEventListener) { mq.addEventListener('change', function () { if (!stored()) { apply(); } }); }
	document.addEventListener('DOMContentLoaded', apply);
	document.addEventListener('visibilitychange', function () { if (!document.hidden && !stored()) { apply(); } });
	window.addEventListener('pageshow', apply);
	document.addEventListener('click', function (e) {
		var b = e.target.closest && e.target.closest('.rv-theme-toggle');
		if (!b) { return; }
		var next = root.classList.contains('rv-dark') ? 'light' : 'dark';
		try { localStorage.setItem(KEY, next); } catch (err) {}
		apply();
	});
})();
JS;
	wp_print_inline_script_tag( $js, array( 'id' => 'rv-sulz-darkmode' ) );
}
add_action( 'wp_head', 'rv_sulz_darkmode_script', 1 );

/**
 * Eigene Pattern-Kategorie.
 */
function rv_sulz_pattern_categories() {
	register_block_pattern_category(
		'rv-sulz',
		array( 'label' => __( 'RV Sulz', 'rv-sulz' ) )
	);
}
add_action( 'init', 'rv_sulz_pattern_categories' );

/**
 * Block-Stile: "Karte" für Gruppen.
 */
function rv_sulz_block_styles() {
	register_block_style(
		'core/group',
		array(
			'name'         => 'rv-karte',
			'label'        => __( 'Karte', 'rv-sulz' ),
			'inline_style' => '.wp-block-group.is-style-rv-karte{background:var(--wp--preset--color--base);border:1px solid var(--wp--preset--color--line);border-radius:18px;box-shadow:var(--wp--preset--shadow--soft);padding:var(--wp--preset--spacing--50);}',
		)
	);
	register_block_style(
		'core/button',
		array(
			'name'         => 'rv-hell',
			'label'        => __( 'Hell auf dunkel', 'rv-sulz' ),
			'inline_style' => '.wp-block-button.is-style-rv-hell .wp-block-button__link{background:#fff;color:var(--wp--preset--color--enzian);}.wp-block-button.is-style-rv-hell .wp-block-button__link:hover{background:var(--wp--preset--color--himmel);color:var(--wp--preset--color--nacht);}',
		)
	);
}
add_action( 'init', 'rv_sulz_block_styles' );

/**
 * URL des mitgelieferten Vereinslogos (Fallback, falls kein Logo in WordPress gesetzt ist).
 *
 * @param string $variant 'farbig' oder 'weiss'.
 * @return string
 */
function rv_sulz_logo_url( $variant = 'farbig' ) {
	$file = ( 'weiss' === $variant ) ? 'logo-rv-enzian-weiss-1024.png' : 'logo-rv-enzian-1024.jpg';
	return get_theme_file_uri( 'assets/images/' . $file );
}
