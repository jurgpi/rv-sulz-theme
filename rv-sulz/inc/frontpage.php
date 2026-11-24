<?php
/**
 * Startseiten-Einstellungen wie bei HybridMag: Design → Customizer → Startseite.
 *
 * Pro Bereich wählbar: Kategorie, Anzahl Beiträge, Überschrift, ein-/ausblenden.
 * Die Auswahl wirkt beim Ausgeben auf die markierten Blöcke (CSS-Klassen rv-sec-*, rv-q-*, rv-h-*),
 * bleibt also auch erhalten, wenn die Startseite im Website-Editor bearbeitet wurde.
 *
 * @package rv-sulz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kategorie-ID über den Slug finden (Vorgabe: Kunstrad / Radball), sonst Ersatz-ID.
 *
 * @param string $slug     Kategorie-Slug.
 * @param int    $fallback Ersatz-ID.
 * @return int
 */
function rv_sulz_cat_by_slug( $slug, $fallback ) {
	$term = get_term_by( 'slug', $slug, 'category' );
	return $term ? (int) $term->term_id : (int) $fallback;
}

/**
 * Vorgabewerte.
 *
 * @return array
 */
function rv_sulz_fp_defaults() {
	return array(
		'rv_fp_show_hero'   => true,
		'rv_fp_hero_cat'    => 0,
		'rv_fp_show_news'   => true,
		'rv_fp_col1_cat'    => rv_sulz_cat_by_slug( 'kunstrad', 3 ),
		'rv_fp_col1_title'  => '',
		'rv_fp_col1_count'  => 4,
		'rv_fp_col2_cat'    => rv_sulz_cat_by_slug( 'radball', 4 ),
		'rv_fp_col2_title'  => '',
		'rv_fp_col2_count'  => 4,
		'rv_fp_show_band'   => true,
		'rv_fp_show_grid'   => true,
		'rv_fp_grid_title'  => 'Neuigkeiten',
		'rv_fp_grid_cat'    => 0,
		'rv_fp_grid_count'  => 6,
		'rv_fp_show_cta'    => true,
	);
}

/**
 * Einstellung lesen.
 *
 * @param string $key Schlüssel.
 * @return mixed
 */
function rv_sulz_fp( $key ) {
	$d = rv_sulz_fp_defaults();
	return get_theme_mod( $key, isset( $d[ $key ] ) ? $d[ $key ] : null );
}

/**
 * Überschrift einer News-Spalte (leer = "<Kategorie> aktuell").
 *
 * @param int $n Spalte 1 oder 2.
 * @return string
 */
function rv_sulz_fp_col_title( $n ) {
	$title = trim( (string) rv_sulz_fp( "rv_fp_col{$n}_title" ) );
	if ( '' !== $title ) {
		return $title;
	}
	$cat = (int) rv_sulz_fp( "rv_fp_col{$n}_cat" );
	$term = $cat ? get_term( $cat, 'category' ) : null;
	return ( $term && ! is_wp_error( $term ) ) ? $term->name . ' aktuell' : 'Aktuell';
}

/**
 * Link "Alle →" einer News-Spalte.
 *
 * @param int $n Spalte 1 oder 2.
 * @return string
 */
function rv_sulz_fp_col_link( $n ) {
	$cat  = (int) rv_sulz_fp( "rv_fp_col{$n}_cat" );
	$link = $cat ? get_category_link( $cat ) : '';
	if ( ! $link || is_wp_error( $link ) ) {
		$page = get_option( 'page_for_posts' );
		$link = $page ? get_permalink( $page ) : home_url( '/' );
	}
	return $link;
}

/**
 * Customizer-Bereich "Startseite".
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function rv_sulz_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'rv_sulz_frontpage',
		array(
			'title'       => __( 'Startseite', 'rv-sulz' ),
			'description' => __( 'Welche Kategorien in welchem Bereich der Startseite erscheinen.', 'rv-sulz' ),
			'priority'    => 30,
		)
	);

	$cats = array( 0 => __( '– Alle Kategorien –', 'rv-sulz' ) );
	foreach ( get_categories( array( 'hide_empty' => false ) ) as $c ) {
		$cats[ $c->term_id ] = $c->name;
	}

	$d   = rv_sulz_fp_defaults();
	$add = function ( $id, $label, $type, $extra = array() ) use ( $wp_customize, $d ) {
		$sanitize = array(
			'checkbox' => 'rv_sulz_sanitize_checkbox',
			'select'   => 'absint',
			'number'   => 'absint',
			'text'     => 'sanitize_text_field',
		);
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $d[ $id ],
				'sanitize_callback' => $sanitize[ $type ],
			)
		);
		$wp_customize->add_control(
			$id,
			array_merge(
				array(
					'label'   => $label,
					'section' => 'rv_sulz_frontpage',
					'type'    => $type,
				),
				$extra
			)
		);
	};
	$num = array( 'input_attrs' => array( 'min' => 1, 'max' => 12, 'step' => 1 ) );

	$add( 'rv_fp_show_hero', __( 'Top-Beiträge (große Kacheln) anzeigen', 'rv-sulz' ), 'checkbox' );
	$add( 'rv_fp_hero_cat', __( 'Top-Beiträge: Kategorie', 'rv-sulz' ), 'select', array( 'choices' => $cats ) );

	$add( 'rv_fp_show_news', __( 'News-Spalten anzeigen', 'rv-sulz' ), 'checkbox' );
	$add( 'rv_fp_col1_cat', __( 'Linke Spalte: Kategorie', 'rv-sulz' ), 'select', array( 'choices' => $cats ) );
	$add( 'rv_fp_col1_title', __( 'Linke Spalte: Überschrift (leer = „Kategorie aktuell“)', 'rv-sulz' ), 'text' );
	$add( 'rv_fp_col1_count', __( 'Linke Spalte: Anzahl Beiträge', 'rv-sulz' ), 'number', $num );
	$add( 'rv_fp_col2_cat', __( 'Rechte Spalte: Kategorie', 'rv-sulz' ), 'select', array( 'choices' => $cats ) );
	$add( 'rv_fp_col2_title', __( 'Rechte Spalte: Überschrift (leer = „Kategorie aktuell“)', 'rv-sulz' ), 'text' );
	$add( 'rv_fp_col2_count', __( 'Rechte Spalte: Anzahl Beiträge', 'rv-sulz' ), 'number', $num );

	$add( 'rv_fp_show_band', __( 'Blaues Sparten-Band anzeigen', 'rv-sulz' ), 'checkbox' );

	$add( 'rv_fp_show_grid', __( 'Neuigkeiten-Raster anzeigen (nur ältere Startseiten-Vorlage)', 'rv-sulz' ), 'checkbox' );
	$add( 'rv_fp_grid_title', __( 'Neuigkeiten: Überschrift', 'rv-sulz' ), 'text' );
	$add( 'rv_fp_grid_cat', __( 'Neuigkeiten: Kategorie', 'rv-sulz' ), 'select', array( 'choices' => $cats ) );
	$add( 'rv_fp_grid_count', __( 'Neuigkeiten: Anzahl Beiträge pro Seite', 'rv-sulz' ), 'number', $num );

	$add( 'rv_fp_show_cta', __( '„Komm ins Training!“ anzeigen (nur ältere Startseiten-Vorlage)', 'rv-sulz' ), 'checkbox' );
}
add_action( 'customize_register', 'rv_sulz_customize_register' );

/**
 * Checkbox bereinigen.
 *
 * @param mixed $v Wert.
 * @return bool
 */
function rv_sulz_sanitize_checkbox( $v ) {
	return (bool) $v;
}

/**
 * Hat der Block eine bestimmte Klasse?
 *
 * @param array  $attrs Block-Attribute.
 * @param string $cls   Klasse.
 * @return bool
 */
function rv_sulz_has_class( $attrs, $cls ) {
	return isset( $attrs['className'] ) && in_array( $cls, preg_split( '/\s+/', $attrs['className'] ), true );
}

/**
 * Abfrage eines Query-Blocks setzen.
 *
 * @param array $parsed_block Block.
 * @param int   $cat          Kategorie (0 = alle).
 * @param int   $count        Anzahl.
 * @param int   $offset       Versatz.
 * @return array
 */
function rv_sulz_set_query( $parsed_block, $cat, $count, $offset ) {
	$q = isset( $parsed_block['attrs']['query'] ) ? $parsed_block['attrs']['query'] : array();
	$q['perPage'] = max( 1, (int) $count );
	$q['offset']  = max( 0, (int) $offset );
	if ( $cat ) {
		$q['taxQuery'] = array( 'category' => array( (int) $cat ) );
	} else {
		unset( $q['taxQuery'] );
	}
	$parsed_block['attrs']['query'] = $q;
	return $parsed_block;
}

/**
 * Vor dem Ausgeben: Abfragen der Startseite anpassen.
 *
 * @param array $parsed_block Block.
 * @return array
 */
function rv_sulz_fp_query_data( $parsed_block ) {
	if ( empty( $parsed_block['blockName'] ) || 'core/query' !== $parsed_block['blockName'] || empty( $parsed_block['attrs']['className'] ) ) {
		return $parsed_block;
	}
	$a        = $parsed_block['attrs'];
	$hero_cat = (int) rv_sulz_fp( 'rv_fp_hero_cat' );

	if ( rv_sulz_has_class( $a, 'rv-q-hero-main' ) ) {
		return rv_sulz_set_query( $parsed_block, $hero_cat, 1, 0 );
	}
	if ( rv_sulz_has_class( $a, 'rv-q-hero-side' ) ) {
		return rv_sulz_set_query( $parsed_block, $hero_cat, 2, 1 );
	}
	foreach ( array( 1, 2 ) as $n ) {
		if ( rv_sulz_has_class( $a, "rv-q-col-$n" ) ) {
			$parsed_block = rv_sulz_set_query( $parsed_block, (int) rv_sulz_fp( "rv_fp_col{$n}_cat" ), (int) rv_sulz_fp( "rv_fp_col{$n}_count" ), 0 );
			// Beiträge, die oben schon als Top-Kachel stehen, nicht noch einmal zeigen.
			$parsed_block['attrs']['query']['rvExclude'] = rv_sulz_fp_hero_ids();
			return $parsed_block;
		}
	}
	// Beitragsseite: „Weitere Neuigkeiten“ ohne den gerade gelesenen Beitrag.
	if ( rv_sulz_has_class( $a, 'rv-q-related' ) ) {
		$current = is_singular() ? (int) get_queried_object_id() : 0;
		if ( $current ) {
			$parsed_block['attrs']['query']['rvExclude'] = array( $current );
		}
		return $parsed_block;
	}
	if ( rv_sulz_has_class( $a, 'rv-q-grid' ) ) {
		$grid_cat = (int) rv_sulz_fp( 'rv_fp_grid_cat' );
		// Gleiche Quelle wie die Top-Beiträge: die 3 dort gezeigten Beiträge überspringen.
		$offset = ( rv_sulz_fp( 'rv_fp_show_hero' ) && $grid_cat === $hero_cat ) ? 3 : 0;
		return rv_sulz_set_query( $parsed_block, $grid_cat, (int) rv_sulz_fp( 'rv_fp_grid_count' ), $offset );
	}
	return $parsed_block;
}
add_filter( 'render_block_data', 'rv_sulz_fp_query_data' );

/**
 * IDs der Beiträge, die als Top-Kacheln erscheinen (gleiche Abfrage wie die Kacheln: die neuesten 3).
 *
 * @return int[]
 */
function rv_sulz_fp_hero_ids() {
	static $ids = null;
	if ( null !== $ids ) {
		return $ids;
	}
	$ids = array();
	if ( ! rv_sulz_fp( 'rv_fp_show_hero' ) ) {
		return $ids;
	}
	$args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 3,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'fields'              => 'ids',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	$cat = (int) rv_sulz_fp( 'rv_fp_hero_cat' );
	if ( $cat ) {
		$args['cat'] = $cat;
	}
	$ids = array_map( 'intval', get_posts( $args ) );
	return $ids;
}

/**
 * Ausschlussliste (rvExclude) aus dem Block-Kontext in die Abfrage übernehmen.
 *
 * @param array    $query Abfrage-Argumente.
 * @param WP_Block $block Block „Beitragsliste“.
 * @return array
 */
function rv_sulz_fp_query_exclude( $query, $block ) {
	if ( ! empty( $block->context['query']['rvExclude'] ) && is_array( $block->context['query']['rvExclude'] ) ) {
		$not_in                = isset( $query['post__not_in'] ) ? (array) $query['post__not_in'] : array();
		$query['post__not_in'] = array_values( array_unique( array_merge( $not_in, array_map( 'intval', $block->context['query']['rvExclude'] ) ) ) );
	}
	return $query;
}
add_filter( 'query_loop_block_query_vars', 'rv_sulz_fp_query_exclude', 10, 2 );

/**
 * Die Beitragsliste und die Seitennummerierung lesen die Abfrage aus dem Block-Kontext.
 * Neuere WordPress-Versionen übernehmen geänderte Attribute dort nicht immer –
 * deshalb die Abfrage zusätzlich direkt im Kontext der Kind-Blöcke setzen.
 *
 * @param array         $context      Kontext.
 * @param array         $parsed_block Block.
 * @param WP_Block|null $parent_block Eltern-Block.
 * @return array
 */
function rv_sulz_fp_query_context( $context, $parsed_block, $parent_block = null ) {
	if ( ! $parent_block instanceof WP_Block || 'core/query' !== $parent_block->name || empty( $parent_block->attributes['className'] ) ) {
		return $context;
	}
	$fake = rv_sulz_fp_query_data(
		array(
			'blockName' => 'core/query',
			'attrs'     => array(
				'className' => $parent_block->attributes['className'],
				'query'     => isset( $context['query'] ) ? $context['query'] : array(),
			),
		)
	);
	$context['query'] = $fake['attrs']['query'];
	return $context;
}
add_filter( 'render_block_context', 'rv_sulz_fp_query_context', 10, 3 );

/**
 * Beim Ausgeben: Bereiche ausblenden, Überschriften und Links setzen.
 *
 * @param string $html  HTML.
 * @param array  $block Block.
 * @return string
 */
function rv_sulz_fp_render( $html, $block ) {
	if ( empty( $block['attrs']['className'] ) ) {
		return $html;
	}
	$a = $block['attrs'];

	if ( 'core/group' === $block['blockName'] ) {
		$sections = array(
			'rv-sec-hero' => 'rv_fp_show_hero',
			'rv-sec-news' => 'rv_fp_show_news',
			'rv-sec-band' => 'rv_fp_show_band',
			'rv-sec-grid' => 'rv_fp_show_grid',
			'rv-sec-cta'  => 'rv_fp_show_cta',
		);
		foreach ( $sections as $cls => $key ) {
			if ( rv_sulz_has_class( $a, $cls ) && ! rv_sulz_fp( $key ) ) {
				return '';
			}
		}
		return $html;
	}

	if ( 'core/heading' === $block['blockName'] ) {
		$text = null;
		if ( rv_sulz_has_class( $a, 'rv-h-col-1' ) ) {
			$text = rv_sulz_fp_col_title( 1 );
		} elseif ( rv_sulz_has_class( $a, 'rv-h-col-2' ) ) {
			$text = rv_sulz_fp_col_title( 2 );
		} elseif ( rv_sulz_has_class( $a, 'rv-h-grid' ) ) {
			$text = trim( (string) rv_sulz_fp( 'rv_fp_grid_title' ) );
			$text = '' === $text ? null : $text;
		}
		if ( null !== $text ) {
			$html = preg_replace( '/(<h[1-6][^>]*>)[\s\S]*?(<\/h[1-6]>)/', '$1' . str_replace( array( '\\', '$' ), array( '\\\\', '\\$' ), esc_html( $text ) ) . '$2', $html, 1 );
		}
		return $html;
	}

	if ( 'core/paragraph' === $block['blockName'] ) {
		foreach ( array( 1, 2 ) as $n ) {
			if ( rv_sulz_has_class( $a, "rv-l-col-$n" ) ) {
				$url  = esc_url( rv_sulz_fp_col_link( $n ) );
				$html = preg_replace( '/href="[^"]*"/', 'href="' . str_replace( array( '\\', '$' ), array( '\\\\', '\\$' ), $url ) . '"', $html, 1 );
			}
		}
	}
	return $html;
}
add_filter( 'render_block', 'rv_sulz_fp_render', 10, 2 );
