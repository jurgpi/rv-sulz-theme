<?php
/**
 * Startseite „Mitglieder gewinnen“: drei Blöcke, gepflegt unter Design → Customizer → Mitmachen & Training.
 *
 * - rv-sulz/mitmachen   „Probier's aus“ mit Knopf und drei Schritten (direkt unter den Top-Beiträgen)
 * - rv-sulz/training    Trainingszeiten je Sparte (erscheint erst, wenn „Wann“ ausgefüllt ist)
 * - rv-sulz/weiterlesen Kacheln „Beiträge im Archiv“ und „Chronik“
 *
 * @package rv-sulz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vorgabewerte.
 *
 * @return array
 */
function rv_sulz_mm_defaults() {
	return array(
		'rv_mm_show_start'   => true,
		'rv_mm_title'        => 'Probier’s aus – das erste Training ist gratis.',
		'rv_mm_text'         => 'Kunstrad oder Radball, für Kinder und Jugendliche: Komm in die Halle, wir stellen das Rad und zeigen dir die ersten Tricks.',
		'rv_mm_btn_label'    => 'Schnuppertraining anfragen',
		'rv_mm_btn_url'      => '/kontakt/',
		'rv_mm_step1_title'  => 'Anfragen',
		'rv_mm_step1_text'   => 'Kurze Nachricht mit Alter und Sparte – wir melden uns.',
		'rv_mm_step2_title'  => 'Vorbeikommen',
		'rv_mm_step2_text'   => 'Turnschuhe und Sportkleidung genügen, Räder sind da.',
		'rv_mm_step3_title'  => 'Mitfahren',
		'rv_mm_step3_text'   => 'Gefällt’s dir, trainierst du einfach weiter mit.',
		'rv_mm_tr_title'     => 'Trainingszeiten',
		'rv_mm_tr1_cat'      => rv_sulz_cat_by_slug( 'kunstrad', 3 ),
		'rv_mm_tr2_cat'      => rv_sulz_cat_by_slug( 'radball', 4 ),
		'rv_mm_show_more'    => true,
		'rv_mm_founded'      => '1924',
		'rv_mm_chronik_url'  => '/chronik/',
		'rv_mm_chronik_img'  => 0,
	);
}

/**
 * Felder je Sparte (Training).
 *
 * @return array Schlüssel-Endung => Beschriftung.
 */
function rv_sulz_mm_tr_fields() {
	return array(
		'wann'    => __( 'Wann (z. B. „Di + Do · 17:00–18:30“)', 'rv-sulz' ),
		'wo'      => __( 'Wo (Halle, Ort)', 'rv-sulz' ),
		'alter'   => __( 'Alter (z. B. „ab 7 Jahren“)', 'rv-sulz' ),
		'kosten'  => __( 'Kosten', 'rv-sulz' ),
		'kontakt' => __( 'Ansprechperson (Funktion, z. B. „Sportliche Leitung Kunstrad“)', 'rv-sulz' ),
	);
}

/**
 * Einstellung lesen.
 *
 * @param string $key Schlüssel.
 * @return mixed
 */
function rv_sulz_mm( $key ) {
	$d = rv_sulz_mm_defaults();
	return get_theme_mod( $key, isset( $d[ $key ] ) ? $d[ $key ] : '' );
}

/**
 * Customizer-Bereich „Mitmachen & Training“.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function rv_sulz_mm_customize( $wp_customize ) {
	$wp_customize->add_section(
		'rv_sulz_mitmachen',
		array(
			'title'       => __( 'Mitmachen & Training', 'rv-sulz' ),
			'description' => __( 'Bereiche der Startseite für neue Mitglieder. Die Trainingszeiten einer Sparte erscheinen erst, wenn „Wann“ ausgefüllt ist.', 'rv-sulz' ),
			'priority'    => 31,
		)
	);

	$cats = array( 0 => __( '– keine –', 'rv-sulz' ) );
	foreach ( get_categories( array( 'hide_empty' => false ) ) as $c ) {
		$cats[ $c->term_id ] = $c->name;
	}

	$d   = rv_sulz_mm_defaults();
	$add = function ( $id, $label, $type, $extra = array() ) use ( $wp_customize, $d ) {
		$sanitize = array(
			'checkbox' => 'rv_sulz_sanitize_checkbox',
			'select'   => 'absint',
			'text'     => 'sanitize_text_field',
			'textarea' => 'sanitize_textarea_field',
			'url'      => 'esc_url_raw',
		);
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => isset( $d[ $id ] ) ? $d[ $id ] : '',
				'sanitize_callback' => $sanitize[ $type ],
			)
		);
		$wp_customize->add_control(
			$id,
			array_merge(
				array(
					'label'   => $label,
					'section' => 'rv_sulz_mitmachen',
					'type'    => $type,
				),
				$extra
			)
		);
	};

	$add( 'rv_mm_show_start', __( '„Probier’s aus“ mit drei Schritten anzeigen', 'rv-sulz' ), 'checkbox' );
	$add( 'rv_mm_title', __( 'Überschrift (Text nach „ – “ wird blau hervorgehoben)', 'rv-sulz' ), 'text' );
	$add( 'rv_mm_text', __( 'Text', 'rv-sulz' ), 'textarea' );
	$add( 'rv_mm_btn_label', __( 'Knopf: Beschriftung', 'rv-sulz' ), 'text' );
	$add( 'rv_mm_btn_url', __( 'Knopf: Adresse', 'rv-sulz' ), 'url' );
	foreach ( array( 1, 2, 3 ) as $n ) {
		/* translators: %d: Nummer des Schritts */
		$add( "rv_mm_step{$n}_title", sprintf( __( 'Schritt %d: Titel', 'rv-sulz' ), $n ), 'text' );
		/* translators: %d: Nummer des Schritts */
		$add( "rv_mm_step{$n}_text", sprintf( __( 'Schritt %d: Text', 'rv-sulz' ), $n ), 'text' );
	}

	$add( 'rv_mm_tr_title', __( 'Trainingszeiten: Überschrift', 'rv-sulz' ), 'text' );
	foreach ( array( 1, 2 ) as $n ) {
		/* translators: %d: 1 oder 2 */
		$add( "rv_mm_tr{$n}_cat", sprintf( __( 'Sparte %d: Kategorie (Name, Foto und Link)', 'rv-sulz' ), $n ), 'select', array( 'choices' => $cats ) );
		foreach ( rv_sulz_mm_tr_fields() as $field => $label ) {
			/* translators: 1: 1 oder 2, 2: Feldname */
			$add( "rv_mm_tr{$n}_{$field}", sprintf( __( 'Sparte %1$d: %2$s', 'rv-sulz' ), $n, $label ), 'text' );
		}
		$wp_customize->add_setting(
			"rv_mm_tr{$n}_img",
			array(
				'default'           => 0,
				'sanitize_callback' => 'absint',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				"rv_mm_tr{$n}_img",
				array(
					/* translators: %d: 1 oder 2 */
					'label'       => sprintf( __( 'Sparte %d: Foto (leer = neuestes großes Beitragsbild der Kategorie)', 'rv-sulz' ), $n ),
					'section'     => 'rv_sulz_mitmachen',
					'mime_type'   => 'image',
				)
			)
		);
	}

	$add( 'rv_mm_show_more', __( 'Kacheln „Archiv“ und „Chronik“ anzeigen', 'rv-sulz' ), 'checkbox' );
	$add( 'rv_mm_founded', __( 'Gründungsjahr', 'rv-sulz' ), 'text' );
	$add( 'rv_mm_chronik_url', __( 'Chronik: Adresse', 'rv-sulz' ), 'url' );
	$wp_customize->add_setting(
		'rv_mm_chronik_img',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'rv_mm_chronik_img',
			array(
				'label'     => __( 'Chronik: Foto (leer = Beitragsbild der Chronik-Seite)', 'rv-sulz' ),
				'section'   => 'rv_sulz_mitmachen',
				'mime_type' => 'image',
			)
		)
	);
}
add_action( 'customize_register', 'rv_sulz_mm_customize', 20 );

/**
 * Blöcke registrieren.
 */
function rv_sulz_mm_register() {
	$uri = get_template_directory_uri();
	wp_register_style( 'rv-sulz-mitmachen', $uri . '/assets/css/mitmachen.css', array(), RV_SULZ_VERSION );
	wp_register_script(
		'rv-sulz-mitmachen-editor',
		$uri . '/assets/js/mitmachen-editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-server-side-render', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
		RV_SULZ_VERSION,
		true
	);
	$blocks = array(
		'mitmachen'   => array( __( 'RV Sulz: Probier’s aus', 'rv-sulz' ), 'rv_sulz_mm_render_start', 'groups' ),
		'training'    => array( __( 'RV Sulz: Trainingszeiten', 'rv-sulz' ), 'rv_sulz_mm_render_training', 'clock' ),
		'weiterlesen' => array( __( 'RV Sulz: Archiv & Chronik', 'rv-sulz' ), 'rv_sulz_mm_render_more', 'archive' ),
	);
	foreach ( $blocks as $slug => $b ) {
		register_block_type(
			'rv-sulz/' . $slug,
			array(
				'api_version'           => 3,
				'title'                 => $b[0],
				'description'           => __( 'Inhalt unter Design → Customizer → Mitmachen & Training.', 'rv-sulz' ),
				'category'              => 'widgets',
				'icon'                  => $b[2],
				'supports'              => array( 'html' => false ),
				'style_handles'         => array( 'rv-sulz-mitmachen' ),
				'editor_script_handles' => array( 'rv-sulz-mitmachen-editor' ),
				'render_callback'       => $b[1],
			)
		);
	}
}
add_action( 'init', 'rv_sulz_mm_register' );

/**
 * Pfeil-Symbol.
 *
 * @param int $size Größe in px.
 * @return string
 */
function rv_sulz_mm_arrow( $size = 18 ) {
	return '<svg width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
}

/**
 * Relative Adresse („/kontakt/“) zu einer vollständigen machen.
 *
 * @param string $url Adresse.
 * @return string
 */
function rv_sulz_mm_url( $url ) {
	$url = trim( (string) $url );
	if ( '' === $url ) {
		return '';
	}
	return ( '/' === $url[0] && ( ! isset( $url[1] ) || '/' !== $url[1] ) ) ? home_url( $url ) : $url;
}

/**
 * Hat mindestens eine Sparte Trainingszeiten?
 *
 * @return bool
 */
function rv_sulz_mm_has_training() {
	return '' !== trim( (string) rv_sulz_mm( 'rv_mm_tr1_wann' ) ) || '' !== trim( (string) rv_sulz_mm( 'rv_mm_tr2_wann' ) );
}

/**
 * Block „Probier's aus“.
 *
 * @return string
 */
function rv_sulz_mm_render_start() {
	if ( ! rv_sulz_mm( 'rv_mm_show_start' ) ) {
		return '';
	}
	$title = (string) rv_sulz_mm( 'rv_mm_title' );
	$parts = preg_split( '/\s[–-]\s/u', $title, 2 );
	$h     = esc_html( $parts[0] );
	if ( isset( $parts[1] ) && '' !== trim( $parts[1] ) ) {
		$h .= '&nbsp;– <em>' . esc_html( $parts[1] ) . '</em>';
	}

	$btn_label = trim( (string) rv_sulz_mm( 'rv_mm_btn_label' ) );
	$btn_url   = rv_sulz_mm_url( rv_sulz_mm( 'rv_mm_btn_url' ) );

	$html  = '<section class="rv-mm rv-mm-start alignfull" aria-labelledby="rv-mm-start-h"><div class="rv-mm-wrap">';
	$html .= '<div class="rv-mm-start__text"><h2 id="rv-mm-start-h">' . $h . '</h2>';
	$text  = trim( (string) rv_sulz_mm( 'rv_mm_text' ) );
	if ( '' !== $text ) {
		$html .= '<p class="rv-mm-lead">' . esc_html( $text ) . '</p>';
	}
	$html .= '<div class="rv-mm-btns">';
	if ( $btn_label && $btn_url ) {
		$html .= '<a class="rv-mm-btn rv-mm-btn--p" href="' . esc_url( $btn_url ) . '">' . esc_html( $btn_label ) . ' ' . rv_sulz_mm_arrow() . '</a>';
	}
	if ( rv_sulz_mm_has_training() ) {
		$html .= '<a class="rv-mm-btn rv-mm-btn--s" href="#training">' . esc_html__( 'Trainingszeiten ansehen', 'rv-sulz' ) . '</a>';
	}
	$html .= '</div></div>';

	$steps = '';
	foreach ( array( 1, 2, 3 ) as $n ) {
		$st = trim( (string) rv_sulz_mm( "rv_mm_step{$n}_title" ) );
		if ( '' === $st ) {
			continue;
		}
		$steps .= '<li class="rv-mm-step"><span class="rv-mm-num" aria-hidden="true">' . $n . '</span><h3>' . esc_html( $st ) . '</h3>';
		$sx     = trim( (string) rv_sulz_mm( "rv_mm_step{$n}_text" ) );
		if ( '' !== $sx ) {
			$steps .= '<p>' . esc_html( $sx ) . '</p>';
		}
		$steps .= '</li>';
	}
	if ( $steps ) {
		$html .= '<ol class="rv-mm-steps" aria-label="' . esc_attr__( 'So startest du', 'rv-sulz' ) . '">' . $steps . '</ol>';
	}
	$html .= '</div></section>';
	return $html;
}

/**
 * Neuestes ausreichend großes Beitragsbild einer Kategorie (ID), zwischengespeichert.
 *
 * @param int $cat Kategorie.
 * @return int Anhang-ID oder 0.
 */
function rv_sulz_mm_cat_image( $cat ) {
	if ( ! $cat ) {
		return 0;
	}
	$key = 'rv_mm_img_' . (int) $cat . '_' . md5( (string) get_option( 'rv_sulz_archiv_ver', '1' ) );
	$id  = get_transient( $key );
	if ( false !== $id ) {
		return (int) $id;
	}
	$id    = 0;
	$posts = get_posts(
		array(
			'cat'            => (int) $cat,
			'posts_per_page' => 15,
			'meta_key'       => '_thumbnail_id', // phpcs:ignore WordPress.DB.SlowDBQuery
			'fields'         => 'ids',
		)
	);
	foreach ( $posts as $pid ) {
		$thumb = (int) get_post_thumbnail_id( $pid );
		$meta  = $thumb ? wp_get_attachment_metadata( $thumb ) : array();
		if ( ! empty( $meta['width'] ) && (int) $meta['width'] >= 900 && (int) $meta['width'] > (int) $meta['height'] ) {
			$id = $thumb;
			break;
		}
	}
	set_transient( $key, $id, 12 * HOUR_IN_SECONDS );
	return $id;
}

/**
 * Link zur Sparten-Seite: Seite mit gleichem Slug, sonst Kategorie-Archiv.
 *
 * @param WP_Term $term Kategorie.
 * @return string
 */
function rv_sulz_mm_sparte_link( $term ) {
	$page = get_page_by_path( $term->slug );
	if ( $page && 'publish' === $page->post_status ) {
		return get_permalink( $page );
	}
	$link = get_term_link( $term );
	return is_wp_error( $link ) ? '' : $link;
}

/**
 * Block „Trainingszeiten“.
 *
 * @return string
 */
function rv_sulz_mm_render_training() {
	$labels = array(
		'wann'   => __( 'Wann', 'rv-sulz' ),
		'wo'     => __( 'Wo', 'rv-sulz' ),
		'alter'  => __( 'Alter', 'rv-sulz' ),
		'kosten' => __( 'Kosten', 'rv-sulz' ),
	);
	$cards  = '';
	foreach ( array( 1, 2 ) as $n ) {
		if ( '' === trim( (string) rv_sulz_mm( "rv_mm_tr{$n}_wann" ) ) ) {
			continue;
		}
		$cat  = (int) rv_sulz_mm( "rv_mm_tr{$n}_cat" );
		$term = $cat ? get_term( $cat, 'category' ) : null;
		$name = ( $term && ! is_wp_error( $term ) ) ? $term->name : '';
		$img  = (int) rv_sulz_mm( "rv_mm_tr{$n}_img" );
		if ( ! $img ) {
			$img = rv_sulz_mm_cat_image( $cat );
		}

		$cards .= '<article class="rv-mm-plan">';
		$cards .= '<div class="rv-mm-plan__img' . ( $img ? '' : ' is-empty' ) . '">';
		if ( $img ) {
			$cards .= wp_get_attachment_image(
				$img,
				'large',
				false,
				array(
					'alt'     => '',
					'loading' => 'lazy',
					'sizes'   => '(max-width: 900px) 100vw, 600px',
				)
			);
		}
		if ( $name ) {
			$cards .= '<h3>' . esc_html( $name ) . '</h3>';
		}
		$cards .= '</div><div class="rv-mm-plan__body"><dl class="rv-mm-facts">';
		foreach ( $labels as $field => $label ) {
			$val = trim( (string) rv_sulz_mm( "rv_mm_tr{$n}_{$field}" ) );
			if ( '' !== $val ) {
				$cards .= '<dt>' . esc_html( $label ) . '</dt><dd>' . esc_html( $val ) . '</dd>';
			}
		}
		$cards  .= '</dl>';
		$kontakt = trim( (string) rv_sulz_mm( "rv_mm_tr{$n}_kontakt" ) );
		$link    = ( $term && ! is_wp_error( $term ) ) ? rv_sulz_mm_sparte_link( $term ) : '';
		if ( $kontakt || $link ) {
			$cards .= '<div class="rv-mm-plan__foot">';
			if ( $kontakt ) {
				/* translators: %s: Funktion */
				$cards .= '<span>' . esc_html( sprintf( __( 'Ansprechperson: %s', 'rv-sulz' ), $kontakt ) ) . '</span>';
			}
			if ( $link ) {
				/* translators: %s: Sparte */
				$cards .= '<a class="rv-mm-link" href="' . esc_url( $link ) . '">' . esc_html( sprintf( __( 'Mehr über %s', 'rv-sulz' ), $name ) ) . ' ' . rv_sulz_mm_arrow( 16 ) . '</a>';
			}
			$cards .= '</div>';
		}
		$cards .= '</div></article>';
	}
	if ( '' === $cards ) {
		return '';
	}
	$title = trim( (string) rv_sulz_mm( 'rv_mm_tr_title' ) );
	$html  = '<section class="rv-mm rv-mm-training alignfull" id="training"' . ( $title ? ' aria-labelledby="rv-mm-tr-h"' : '' ) . '><div class="rv-mm-wrap">';
	if ( $title ) {
		$html .= '<h2 id="rv-mm-tr-h">' . esc_html( $title ) . '</h2>';
	}
	$html .= '<div class="rv-mm-plans">' . $cards . '</div></div></section>';
	return $html;
}

/**
 * Block „Archiv & Chronik“.
 *
 * @return string
 */
function rv_sulz_mm_render_more() {
	if ( ! rv_sulz_mm( 'rv_mm_show_more' ) ) {
		return '';
	}
	$tiles = '';

	$archiv = get_page_by_path( 'archiv' );
	if ( $archiv && 'publish' === $archiv->post_status ) {
		$count  = (int) wp_count_posts( 'post' )->publish;
		$years  = function_exists( 'rv_sulz_archiv_years' ) ? array_keys( rv_sulz_archiv_years( 0 ) ) : array();
		$tiles .= '<a class="rv-mm-tile rv-mm-tile--archiv" href="' . esc_url( get_permalink( $archiv ) ) . '">';
		$tiles .= '<span class="rv-mm-big">' . esc_html( number_format_i18n( $count ) ) . '</span>';
		$tiles .= '<h3>' . esc_html__( 'Beiträge im Archiv', 'rv-sulz' ) . '</h3>';
		$tiles .= '<p>' . esc_html(
			$years
				/* translators: %s: Jahr */
				? sprintf( __( 'Alle Berichte seit %s – nach Jahr und Sparte filtern.', 'rv-sulz' ), min( $years ) )
				: __( 'Alle Berichte – nach Jahr und Sparte filtern.', 'rv-sulz' )
		) . '</p>';
		$tiles .= '<span class="rv-mm-arrow">' . esc_html__( 'Zum Archiv', 'rv-sulz' ) . ' ' . rv_sulz_mm_arrow() . '</span></a>';
	}

	$chronik_url = rv_sulz_mm_url( rv_sulz_mm( 'rv_mm_chronik_url' ) );
	if ( $chronik_url ) {
		$img = (int) rv_sulz_mm( 'rv_mm_chronik_img' );
		if ( ! $img ) {
			$page = get_page_by_path( trim( (string) wp_parse_url( $chronik_url, PHP_URL_PATH ), '/' ) );
			$img  = $page ? (int) get_post_thumbnail_id( $page ) : 0;
		}
		$founded = trim( (string) rv_sulz_mm( 'rv_mm_founded' ) );
		$age     = ctype_digit( $founded ) ? (int) wp_date( 'Y' ) - (int) $founded : 0;
		$tiles  .= '<a class="rv-mm-tile rv-mm-tile--chronik' . ( $img ? ' has-img' : '' ) . '" href="' . esc_url( $chronik_url ) . '">';
		if ( $img ) {
			$tiles .= wp_get_attachment_image(
				$img,
				'large',
				false,
				array(
					'alt'     => '',
					'loading' => 'lazy',
					'sizes'   => '(max-width: 900px) 100vw, 600px',
				)
			);
		}
		if ( $founded ) {
			$tiles .= '<span class="rv-mm-big">' . esc_html( $founded ) . '</span>';
		}
		$tiles .= '<h3>' . esc_html(
			$age >= 100
				/* translators: %d: volle hundert Jahre, z. B. 100 */
				? sprintf( __( 'Über %d Jahre Vereinsgeschichte', 'rv-sulz' ), (int) ( floor( $age / 100 ) * 100 ) )
				: __( 'Unsere Geschichte', 'rv-sulz' )
		) . '</h3>';
		$tiles .= '<p>' . esc_html__( 'Vom Gründungsjahr bis heute: die Chronik des Vereins.', 'rv-sulz' ) . '</p>';
		$tiles .= '<span class="rv-mm-arrow">' . esc_html__( 'Zur Chronik', 'rv-sulz' ) . ' ' . rv_sulz_mm_arrow() . '</span></a>';
	}

	if ( '' === $tiles ) {
		return '';
	}
	return '<section class="rv-mm rv-mm-more alignfull" aria-label="' . esc_attr__( 'Weiterlesen', 'rv-sulz' ) . '"><div class="rv-mm-wrap">' . $tiles . '</div></section>';
}
