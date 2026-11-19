<?php
/**
 * Fußbereich verwaltbar wie bei HybridMag:
 * - Design → Widgets: vier Widget-Bereiche „Fußbereich 1–4“ (je eine Spalte).
 *   Ist ein Bereich befüllt, ersetzt er den Vorgabe-Inhalt der Spalte; leer = Vorgabe des Themes.
 * - Design → Menüs: Position „Fußzeile unten“ für die Links neben dem Copyright.
 * - Design → Customizer → Fußbereich: Copyright-Text.
 *
 * @package rv-sulz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Klassischer Widget-Bildschirm wie bei HybridMag (Überschrift + Menü per „Navigationsmenü“-Widget).
 */
add_filter( 'use_widgets_block_editor', '__return_false' );

/**
 * Widget-Bereiche registrieren.
 */
function rv_sulz_register_sidebars() {
	$names = array(
		1 => __( 'Fußbereich 1 (Logo und Text)', 'rv-sulz' ),
		2 => __( 'Fußbereich 2 (Vorgabe: Sport)', 'rv-sulz' ),
		3 => __( 'Fußbereich 3 (Vorgabe: Verein)', 'rv-sulz' ),
		4 => __( 'Fußbereich 4 (Vorgabe: Mitmachen)', 'rv-sulz' ),
	);
	foreach ( $names as $n => $name ) {
		register_sidebar(
			array(
				'id'            => 'footer-' . $n,
				'name'          => $name,
				'description'   => __( 'Leer lassen, um den Vorgabe-Inhalt des Themes zu zeigen. Tipp: Widget „Navigationsmenü“ mit Titel und Menü.', 'rv-sulz' ),
				'before_widget' => '<section id="%1$s" class="rv-widget widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h4 class="rv-widget-title">',
				'after_title'   => '</h4>',
			)
		);
	}
	register_nav_menus(
		array(
			'footer-bottom' => __( 'Fußzeile unten (neben dem Copyright)', 'rv-sulz' ),
		)
	);
}
add_action( 'widgets_init', 'rv_sulz_register_sidebars' );

/**
 * Customizer: Copyright-Text.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function rv_sulz_customize_footer( $wp_customize ) {
	$wp_customize->add_section(
		'rv_sulz_footer',
		array(
			'title'    => __( 'Fußbereich', 'rv-sulz' ),
			'priority' => 31,
		)
	);
	$wp_customize->add_setting(
		'rv_footer_copyright',
		array(
			'default'           => '',
			'sanitize_callback' => 'wp_kses_post',
		)
	);
	$wp_customize->add_control(
		'rv_footer_copyright',
		array(
			'label'       => __( 'Copyright-Text', 'rv-sulz' ),
			'description' => __( 'Leer = „© RV Enzian Sulz · seit 1924“. {jahr} wird durch das aktuelle Jahr ersetzt.', 'rv-sulz' ),
			'section'     => 'rv_sulz_footer',
			'type'        => 'text',
		)
	);
}
add_action( 'customize_register', 'rv_sulz_customize_footer' );

/**
 * Beim Ausgeben: Spalten durch Widgets ersetzen, untere Zeile anpassen.
 *
 * @param string $html  HTML.
 * @param array  $block Block.
 * @return string
 */
function rv_sulz_footer_render( $html, $block ) {
	if ( empty( $block['attrs']['className'] ) ) {
		return $html;
	}
	$a = $block['attrs'];

	if ( 'core/column' === $block['blockName'] ) {
		for ( $n = 1; $n <= 4; $n++ ) {
			if ( rv_sulz_has_class( $a, 'rv-fcol-' . $n ) && is_active_sidebar( 'footer-' . $n ) && preg_match( '/^\s*<div\b[^>]*>/', $html, $open ) ) {
				ob_start();
				dynamic_sidebar( 'footer-' . $n );
				return $open[0] . ob_get_clean() . '</div>';
			}
		}
		// Spalten „Sport“ (2) und „Verein“ (3): ohne Widget und ohne zugewiesenes Menü ausblenden.
		$menu_columns = array(
			'rv-fcol-2' => 'footer-1',
			'rv-fcol-3' => 'footer-2',
		);
		foreach ( $menu_columns as $cls => $location ) {
			if ( rv_sulz_has_class( $a, $cls ) && null === rv_sulz_menu_items( $location ) ) {
				return '';
			}
		}
		return $html;
	}

	if ( 'core/paragraph' !== $block['blockName'] ) {
		return $html;
	}

	if ( rv_sulz_has_class( $a, 'rv-copyright' ) ) {
		$text = trim( (string) get_theme_mod( 'rv_footer_copyright', '' ) );
		if ( '' !== $text && preg_match( '/^\s*<p\b[^>]*>/', $html, $open ) ) {
			$text = str_replace( '{jahr}', wp_date( 'Y' ), wp_kses_post( $text ) );
			return $open[0] . $text . '</p>';
		}
		return $html;
	}

	if ( rv_sulz_has_class( $a, 'rv-footer-links' ) ) {
		$items = rv_sulz_menu_items( 'footer-bottom' );
		if ( null !== $items && preg_match( '/^\s*<p\b[^>]*>/', $html, $open ) ) {
			$links = array();
			foreach ( $items as $item ) {
				if ( (int) $item->menu_item_parent ) {
					continue;
				}
				$target  = '_blank' === $item->target ? ' target="_blank" rel="noopener"' : '';
				$links[] = '<a href="' . esc_url( $item->url ) . '"' . $target . '>' . esc_html( $item->title ) . '</a>';
			}
			return $open[0] . implode( ' · ', $links ) . '</p>';
		}
	}
	return $html;
}
add_filter( 'render_block', 'rv_sulz_footer_render', 10, 2 );
