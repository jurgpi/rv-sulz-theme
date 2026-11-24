<?php
/**
 * Ladezeit: was zuerst sichtbar ist, kommt zuerst – alles andere später oder gar nicht.
 *
 * - Großes Bild der Top-Kachel (größtes sichtbares Element) sofort und mit hoher Priorität laden.
 * - Weißes Logo (nur im Dunkelmodus bzw. im Fuß sichtbar) erst laden, wenn es angezeigt wird.
 * - Contact Form 7 nur auf Seiten mit Formular laden.
 * - Die zwei wichtigsten Schriften vorab laden (weniger Umspringen der Überschriften).
 *
 * @package rv-sulz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Top-Kachel: Bild nicht „lazy“, sondern sofort mit hoher Priorität und passender Größe.
 *
 * @param string $html  HTML.
 * @param array  $block Block.
 * @return string
 */
function rv_sulz_perf_hero_image( $html, $block ) {
	if ( 'core/cover' !== $block['blockName'] || empty( $block['attrs']['className'] ) || ! rv_sulz_has_class( $block['attrs'], 'rv-tile--big' ) ) {
		return $html;
	}
	return preg_replace_callback(
		'#<img\b[^>]*>#',
		function ( $m ) {
			$img = preg_replace( '#\sloading=(["\'])lazy\1#', '', $m[0] );
			$img = preg_replace( '#\sfetchpriority=(["\'])[^"\']*\1#', '', $img );
			// Breite der Kachel: am Handy fast volle Breite, am Desktop ca. 64 % von 1240 px.
			$img = preg_replace( '#\ssizes=(["\'])[^"\']*\1#', ' sizes="(max-width: 781px) 100vw, 800px"', $img );
			return str_replace( '<img', '<img fetchpriority="high" loading="eager"', $img );
		},
		$html,
		1
	);
}
add_filter( 'render_block', 'rv_sulz_perf_hero_image', 20, 2 );

/**
 * Weißes Logo erst laden, wenn es zu sehen ist (Kopf im Dunkelmodus, Fußbereich).
 * Ausgeblendete Bilder mit loading="lazy" lädt der Browser gar nicht.
 *
 * @param string $html  HTML.
 * @param array  $block Block.
 * @return string
 */
function rv_sulz_perf_lazy_logo( $html, $block ) {
	if ( ! in_array( $block['blockName'], array( 'core/html', 'core/image' ), true ) || false === strpos( $html, 'logo-rv-enzian-weiss' ) ) {
		return $html;
	}
	return preg_replace_callback(
		'#<img\b[^>]*logo-rv-enzian-weiss[^>]*>#',
		function ( $m ) {
			if ( preg_match( '#\sloading=#', $m[0] ) ) {
				return $m[0];
			}
			return str_replace( '<img', '<img loading="lazy" decoding="async"', $m[0] );
		},
		$html
	);
}
add_filter( 'render_block', 'rv_sulz_perf_lazy_logo', 20, 2 );

/**
 * Steht auf dieser Seite ein Contact-Form-7-Formular?
 *
 * @return bool
 */
function rv_sulz_perf_has_form() {
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	return has_shortcode( $post->post_content, 'contact-form-7' )
		|| has_block( 'contact-form-7/contact-form-selector', $post )
		|| false !== strpos( $post->post_content, '[contact-form-7' );
}

/**
 * Contact Form 7: Skripte und Styles nur dort, wo ein Formular steht.
 *
 * @param bool $load Laden?
 * @return bool
 */
function rv_sulz_perf_cf7_assets( $load ) {
	if ( is_admin() ) {
		return $load;
	}
	return $load && rv_sulz_perf_has_form();
}
add_filter( 'wpcf7_load_js', 'rv_sulz_perf_cf7_assets' );
add_filter( 'wpcf7_load_css', 'rv_sulz_perf_cf7_assets' );

/**
 * Wichtigste Schriften vorab laden: Inter (Text) und Barlow Condensed 800 (Überschriften).
 */
function rv_sulz_perf_preload_fonts() {
	$base = get_template_directory_uri() . '/assets/fonts/';
	foreach ( array( 'inter-variable.woff2', 'barlow-condensed-800.woff2' ) as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( $base . $font )
		);
	}
}
add_action( 'wp_head', 'rv_sulz_perf_preload_fonts', 2 );
