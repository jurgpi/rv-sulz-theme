<?php
/**
 * Menüs wie bei klassischen Themes (z. B. HybridMag): Pflege unter Design → Menüs.
 *
 * Die Menüpositionen heißen gleich wie bei HybridMag ("primary", "social"), damit WordPress
 * beim Theme-Wechsel die bestehende Zuordnung übernimmt. Ist einer Position ein Menü
 * zugewiesen, ersetzt es beim Ausgeben die Vorgabe-Einträge des jeweiligen Navigations-Blocks.
 * Ohne Zuordnung werden die im Theme hinterlegten Vorgabe-Einträge gezeigt.
 *
 * @package rv-sulz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menüpositionen registrieren (blendet zugleich Design → Menüs ein).
 */
function rv_sulz_register_menus() {
	register_nav_menus(
		array(
			'primary'  => __( 'Hauptmenü (Kopfbereich)', 'rv-sulz' ),
			'footer-1' => __( 'Fußbereich – Spalte „Sport“', 'rv-sulz' ),
			'footer-2' => __( 'Fußbereich – Spalte „Verein“', 'rv-sulz' ),
			'social'   => __( 'Social-Media-Links', 'rv-sulz' ),
		)
	);
}
add_action( 'after_setup_theme', 'rv_sulz_register_menus' );

/**
 * Welche Block-Klasse gehört zu welcher Menüposition.
 */
function rv_sulz_menu_map() {
	return array(
		'rv-nav-primary'  => 'primary',
		'rv-nav-footer-1' => 'footer-1',
		'rv-nav-footer-2' => 'footer-2',
		'rv-social'       => 'social',
	);
}

/**
 * Liefert die Menüeinträge einer Position oder null, wenn kein Menü zugewiesen ist.
 *
 * @param string $location Menüposition.
 * @return WP_Post[]|null
 */
function rv_sulz_menu_items( $location ) {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return null;
	}
	$items = wp_get_nav_menu_items( $locations[ $location ], array( 'update_post_term_cache' => false ) );
	if ( empty( $items ) || is_wp_error( $items ) ) {
		return null;
	}
	return $items;
}

/**
 * Normalisierte URL zum Vergleich (doppelte Einträge erkennen).
 *
 * @param string $url URL.
 * @return string
 */
function rv_sulz_url_key( $url ) {
	$path = wp_parse_url( $url, PHP_URL_PATH );
	$host = wp_parse_url( $url, PHP_URL_HOST );
	return strtolower( (string) $host ) . '|' . untrailingslashit( (string) $path );
}

/**
 * Ein Menüeintrag als Block (navigation-link bzw. navigation-submenu).
 *
 * @param WP_Post $item     Menüeintrag.
 * @param array   $children Kind-Einträge, gruppiert nach Eltern-ID.
 * @param int     $depth    Tiefe.
 * @return array
 */
function rv_sulz_menu_item_block( $item, $children, $depth = 0 ) {
	$attrs = array(
		'label' => $item->title,
		'url'   => $item->url,
	);
	if ( 'post_type' === $item->type ) {
		$attrs['kind'] = 'post-type';
		$attrs['type'] = $item->object;
		$attrs['id']   = (int) $item->object_id;
	} elseif ( 'taxonomy' === $item->type ) {
		$attrs['kind'] = 'taxonomy';
		$attrs['type'] = $item->object;
		$attrs['id']   = (int) $item->object_id;
	} else {
		$attrs['kind'] = 'custom';
	}
	if ( '_blank' === $item->target ) {
		$attrs['opensInNewTab'] = true;
	}
	if ( ! empty( $item->attr_title ) ) {
		$attrs['title'] = $item->attr_title;
	}
	$classes = array_filter( (array) $item->classes );
	if ( $classes ) {
		$attrs['className'] = implode( ' ', $classes );
	}

	$inner = array();
	if ( ! empty( $children[ $item->ID ] ) && $depth < 3 ) {
		$own        = rv_sulz_url_key( $item->url );
		$seen       = array();
		$has_parent = false;
		foreach ( $children[ $item->ID ] as $child ) {
			$key = rv_sulz_url_key( $child->url );
			// Gleiche URL nicht zweimal im selben Untermenü.
			if ( isset( $seen[ $key ] ) ) {
				continue;
			}
			$seen[ $key ] = true;
			$block        = rv_sulz_menu_item_block( $child, $children, $depth + 1 );
			if ( $key === $own ) {
				// Unterpunkt, der auf den Hauptpunkt zeigt: nur am Desktop zeigen.
				$has_parent                  = true;
				$block['attrs']['className'] = trim( ( isset( $block['attrs']['className'] ) ? $block['attrs']['className'] : '' ) . ' rv-nav-parent' );
			}
			$inner[] = $block;
		}
		// Wie im bisherigen Menü: Hauptpunkt im Desktop-Aufklappmenü nochmals als ersten Unterpunkt
		// zeigen. Im geöffneten Handy-Menü blendet style.css ihn aus (keine Doppelung).
		if ( $inner && ! $has_parent && '' !== trim( (string) $item->url ) && '#' !== $item->url ) {
			$copy                       = $attrs;
			$copy['className']          = trim( ( isset( $copy['className'] ) ? $copy['className'] : '' ) . ' rv-nav-parent' );
			array_unshift(
				$inner,
				array(
					'blockName'    => 'core/navigation-link',
					'attrs'        => $copy,
					'innerBlocks'  => array(),
					'innerHTML'    => '',
					'innerContent' => array(),
				)
			);
		}
	}

	return array(
		'blockName'    => $inner ? 'core/navigation-submenu' : 'core/navigation-link',
		'attrs'        => $attrs,
		'innerBlocks'  => $inner,
		'innerHTML'    => '',
		'innerContent' => array_fill( 0, count( $inner ), null ),
	);
}

/**
 * Menüeinträge einer Position als Block-Liste für den Navigations-Block.
 *
 * @param WP_Post[] $items Menüeinträge.
 * @return array
 */
function rv_sulz_nav_blocks( $items ) {
	$children = array();
	$top      = array();
	foreach ( $items as $item ) {
		if ( (int) $item->menu_item_parent ) {
			$children[ (int) $item->menu_item_parent ][] = $item;
		} else {
			$top[] = $item;
		}
	}
	$blocks = array();
	$seen   = array();
	foreach ( $top as $item ) {
		$key = rv_sulz_url_key( $item->url );
		if ( isset( $seen[ $key ] ) ) {
			continue;
		}
		$seen[ $key ] = true;
		$blocks[]     = rv_sulz_menu_item_block( $item, $children );
	}
	return $blocks;
}

/**
 * Social-Menü (wie bei HybridMag) als Social-Link-Blöcke; der Dienst wird an der URL erkannt.
 *
 * @param WP_Post[] $items Menüeinträge.
 * @return array
 */
function rv_sulz_social_blocks( $items ) {
	$services = array(
		'facebook.com'  => 'facebook',
		'instagram.com' => 'instagram',
		'youtube.com'   => 'youtube',
		'youtu.be'      => 'youtube',
		'tiktok.com'    => 'tiktok',
		'x.com'         => 'x',
		'twitter.com'   => 'x',
		'linkedin.com'  => 'linkedin',
		'whatsapp.com'  => 'whatsapp',
		'wa.me'         => 'whatsapp',
		'threads.net'   => 'threads',
		'mailto:'       => 'mail',
	);
	$blocks = array();
	foreach ( $items as $item ) {
		$service = 'chain';
		foreach ( $services as $needle => $name ) {
			if ( false !== stripos( $item->url, $needle ) ) {
				$service = $name;
				break;
			}
		}
		$blocks[] = array(
			'blockName'    => 'core/social-link',
			'attrs'        => array(
				'url'     => $item->url,
				'service' => $service,
				'label'   => $item->title,
			),
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		);
	}
	return $blocks;
}

/**
 * Vor dem Ausgeben: Vorgabe-Einträge durch das zugewiesene Menü ersetzen.
 *
 * @param array $parsed_block Block-Daten.
 * @return array
 */
function rv_sulz_swap_menu_blocks( $parsed_block ) {
	$name = isset( $parsed_block['blockName'] ) ? $parsed_block['blockName'] : '';
	if ( 'core/navigation' !== $name && 'core/social-links' !== $name ) {
		return $parsed_block;
	}
	$class = isset( $parsed_block['attrs']['className'] ) ? ' ' . $parsed_block['attrs']['className'] . ' ' : '';
	if ( '  ' === $class || '' === trim( $class ) ) {
		return $parsed_block;
	}
	foreach ( rv_sulz_menu_map() as $css => $location ) {
		if ( false === strpos( $class, ' ' . $css . ' ' ) ) {
			continue;
		}
		$items = rv_sulz_menu_items( $location );
		if ( null === $items ) {
			return $parsed_block;
		}
		if ( 'core/social-links' === $name ) {
			$inner = rv_sulz_social_blocks( $items );
			// Social-Links speichern ihr <ul> im Block-Inhalt; Kinder kommen dazwischen.
			$open  = isset( $parsed_block['innerContent'][0] ) && is_string( $parsed_block['innerContent'][0] ) ? $parsed_block['innerContent'][0] : '<ul class="wp-block-social-links">';
			$close = '</ul>';
			$parsed_block['innerContent'] = array_merge( array( $open ), array_fill( 0, count( $inner ), null ), array( $close ) );
		} else {
			$inner = rv_sulz_nav_blocks( $items );
			unset( $parsed_block['attrs']['ref'] );
			$parsed_block['innerContent'] = array_fill( 0, count( $inner ), null );
		}
		$parsed_block['innerBlocks'] = $inner;
		return $parsed_block;
	}
	return $parsed_block;
}
add_filter( 'render_block_data', 'rv_sulz_swap_menu_blocks' );
