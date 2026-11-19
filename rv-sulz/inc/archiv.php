<?php
/**
 * Durchsuchbares Archiv: Block „RV Sulz: Archiv“ (rv-sulz/archiv) und Shortcode [rv_archiv].
 *
 * Jahres-Leiste mit Anzahl, Filter nach Sparte (Kategorie), Suche und Beitragskarten mit Bild.
 * Funktioniert ohne JavaScript über normale Links (?jahr=2024&sparte=radball&suche=…&seite=2);
 * assets/js/archiv.js lädt Ergebnisse ohne Neuladen nach und macht aus „Ältere“ ein „Mehr laden“.
 * Die Seite mit der Adresse /archiv/ nutzt die Vorlage templates/page-archiv.html.
 *
 * @package rv-sulz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block, Styles und Skripte registrieren.
 */
function rv_sulz_archiv_register() {
	$uri = get_template_directory_uri();
	wp_register_style( 'rv-sulz-archiv', $uri . '/assets/css/archiv.css', array(), RV_SULZ_VERSION );
	wp_register_script( 'rv-sulz-archiv', $uri . '/assets/js/archiv.js', array(), RV_SULZ_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_register_script(
		'rv-sulz-archiv-editor',
		$uri . '/assets/js/archiv-editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-server-side-render', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
		RV_SULZ_VERSION,
		true
	);

	register_block_type(
		'rv-sulz/archiv',
		array(
			'api_version'     => 3,
			'title'           => __( 'RV Sulz: Archiv', 'rv-sulz' ),
			'description'     => __( 'Alle Beiträge mit Jahres-Leiste, Sparten-Filter und Suche.', 'rv-sulz' ),
			'category'        => 'widgets',
			'icon'            => 'archive',
			'attributes'      => array(
				'perPage'    => array(
					'type'    => 'number',
					'default' => 12,
				),
				'showSearch' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'align'      => array(
					'type'    => 'string',
					'default' => 'wide',
				),
			),
			'supports'        => array(
				'align' => array( 'wide', 'full' ),
				'html'  => false,
			),
			'style_handles'   => array( 'rv-sulz-archiv' ),
			'view_script_handles' => array( 'rv-sulz-archiv' ),
			'editor_script_handles' => array( 'rv-sulz-archiv-editor' ),
			'render_callback' => 'rv_sulz_archiv_render_block',
		)
	);

	add_shortcode( 'rv_archiv', 'rv_sulz_archiv_shortcode' );
}
add_action( 'init', 'rv_sulz_archiv_register' );

/**
 * Sparten (Kategorien) für den Filter: alle Kategorien mit Beiträgen, alphabetisch,
 * „Allgemein“ am Schluss. Anpassbar über den Filter `rv_sulz_archiv_sparten`.
 *
 * @return WP_Term[]
 */
function rv_sulz_archiv_sparten() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => true,
			'parent'     => 0,
			'orderby'    => 'name',
		)
	);
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	usort(
		$terms,
		function ( $a, $b ) {
			$a_last = in_array( $a->slug, array( 'allgemein', 'uncategorized' ), true );
			$b_last = in_array( $b->slug, array( 'allgemein', 'uncategorized' ), true );
			if ( $a_last !== $b_last ) {
				return $a_last ? 1 : -1;
			}
			return strcasecmp( $a->name, $b->name );
		}
	);
	return apply_filters( 'rv_sulz_archiv_sparten', $terms );
}

/**
 * Cache-Version: ändert sich, sobald ein Beitrag gespeichert oder gelöscht wird.
 *
 * @param int $post_id Beitrag.
 */
function rv_sulz_archiv_bump( $post_id ) {
	if ( 'post' === get_post_type( $post_id ) ) {
		update_option( 'rv_sulz_archiv_ver', (string) microtime( true ), false );
	}
}
add_action( 'clean_post_cache', 'rv_sulz_archiv_bump' );

/**
 * Anzahl veröffentlichter Beiträge je Jahr (optional nur einer Kategorie inkl. Unterkategorien).
 *
 * @param int $cat_id Kategorie-ID oder 0.
 * @return array<int,int> Jahr => Anzahl, neuestes zuerst.
 */
function rv_sulz_archiv_years( $cat_id = 0 ) {
	global $wpdb;
	$key    = 'rv_archiv_y_' . (int) $cat_id . '_' . md5( (string) get_option( 'rv_sulz_archiv_ver', '1' ) );
	$cached = get_transient( $key );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$join = '';
	if ( $cat_id ) {
		$kids = get_term_children( $cat_id, 'category' );
		$ids  = array_map( 'intval', array_merge( array( $cat_id ), is_array( $kids ) ? $kids : array() ) );
		$join = " INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
			INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
				AND tt.taxonomy = 'category' AND tt.term_id IN (" . implode( ',', $ids ) . ')';
	}
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- nur Integer-IDs.
	$rows = $wpdb->get_results(
		"SELECT YEAR(p.post_date) AS y, COUNT(DISTINCT p.ID) AS n FROM {$wpdb->posts} p {$join}
		WHERE p.post_type = 'post' AND p.post_status = 'publish'
		GROUP BY y ORDER BY y DESC"
	);
	$years = array();
	foreach ( (array) $rows as $row ) {
		$years[ (int) $row->y ] = (int) $row->n;
	}
	set_transient( $key, $years, DAY_IN_SECONDS );
	return $years;
}

/**
 * Filterwerte aus der Adresse lesen und prüfen.
 *
 * @param WP_Term[] $sparten Erlaubte Sparten.
 * @return array{jahr:int,sparte:?WP_Term,suche:string,seite:int}
 */
function rv_sulz_archiv_state( $sparten ) {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- reine Anzeige-Filter.
	$jahr = isset( $_GET['jahr'] ) ? absint( $_GET['jahr'] ) : 0;
	if ( $jahr < 1900 || $jahr > 2100 ) {
		$jahr = 0;
	}
	$sparte = null;
	if ( isset( $_GET['sparte'] ) ) {
		$slug = sanitize_title( wp_unslash( $_GET['sparte'] ) );
		foreach ( $sparten as $term ) {
			if ( $term->slug === $slug ) {
				$sparte = $term;
			}
		}
	}
	$suche = isset( $_GET['suche'] ) ? trim( sanitize_text_field( wp_unslash( $_GET['suche'] ) ) ) : '';
	$suche = function_exists( 'mb_substr' ) ? mb_substr( $suche, 0, 80 ) : substr( $suche, 0, 80 );
	$seite = isset( $_GET['seite'] ) ? max( 1, absint( $_GET['seite'] ) ) : 1;
	// phpcs:enable
	return compact( 'jahr', 'sparte', 'suche', 'seite' );
}

/**
 * Adresse für einen Filterzustand bauen.
 *
 * @param string $base  Seitenadresse.
 * @param array  $state Zustand (jahr, sparte, suche, seite).
 * @return string
 */
function rv_sulz_archiv_url( $base, $state ) {
	$args = array();
	if ( ! empty( $state['jahr'] ) ) {
		$args['jahr'] = (int) $state['jahr'];
	}
	if ( ! empty( $state['sparte'] ) ) {
		$args['sparte'] = $state['sparte']->slug;
	}
	if ( '' !== (string) ( $state['suche'] ?? '' ) ) {
		$args['suche'] = rawurlencode( $state['suche'] );
	}
	if ( ! empty( $state['seite'] ) && $state['seite'] > 1 ) {
		$args['seite'] = (int) $state['seite'];
	}
	$url = add_query_arg( $args, $base );
	return $url . '#archiv';
}

/**
 * Archiv ausgeben.
 *
 * @param array $atts perPage, showSearch.
 * @return string
 */
function rv_sulz_archiv_render( $atts = array() ) {
	$per_page    = max( 3, min( 48, (int) ( $atts['perPage'] ?? 12 ) ) );
	$show_search = ! isset( $atts['showSearch'] ) || (bool) $atts['showSearch'];

	$sparten = rv_sulz_archiv_sparten();
	$state   = rv_sulz_archiv_state( $sparten );
	$base    = is_singular() ? get_permalink() : home_url( '/archiv/' );
	$base    = remove_query_arg( array( 'jahr', 'sparte', 'suche', 'seite' ), $base );

	$cat_id = $state['sparte'] ? (int) $state['sparte']->term_id : 0;
	$years  = rv_sulz_archiv_years( $cat_id );
	if ( $state['jahr'] && ! isset( $years[ $state['jahr'] ] ) ) {
		$years[ $state['jahr'] ] = 0;
		krsort( $years );
	}
	$alle_jahre = rv_sulz_archiv_years( 0 );

	$args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $per_page,
		'paged'               => $state['seite'],
		'ignore_sticky_posts' => true,
		'orderby'             => 'date',
		'order'               => 'DESC',
	);
	if ( $state['jahr'] ) {
		$args['date_query'] = array( array( 'year' => $state['jahr'] ) );
	}
	if ( $cat_id ) {
		$args['cat'] = $cat_id;
	}
	if ( '' !== $state['suche'] ) {
		$args['s'] = $state['suche'];
	}
	$query = new WP_Query( $args );

	$total   = (int) $query->found_posts;
	$filters = $state['jahr'] || $state['sparte'] || '' !== $state['suche'];
	$reset   = array_merge( $state, array( 'jahr' => 0, 'sparte' => null, 'suche' => '', 'seite' => 1 ) );

	ob_start();
	?>
	<div class="rv-archiv__kopf">
		<p class="rv-archiv__intro">
			<?php
			printf(
				/* translators: 1: Anzahl Beiträge, 2: erstes Jahr */
				esc_html__( '%1$s Beiträge seit %2$s – nach Jahr, Sparte oder Stichwort filtern.', 'rv-sulz' ),
				'<strong>' . esc_html( number_format_i18n( array_sum( $alle_jahre ) ) ) . '</strong>',
				esc_html( $alle_jahre ? (string) min( array_keys( $alle_jahre ) ) : '' )
			);
			?>
		</p>
		<?php if ( $show_search ) : ?>
			<form class="rv-archiv__suche" role="search" method="get" action="<?php echo esc_url( $base ); ?>#archiv">
				<?php if ( $state['jahr'] ) : ?>
					<input type="hidden" name="jahr" value="<?php echo esc_attr( $state['jahr'] ); ?>">
				<?php endif; ?>
				<?php if ( $state['sparte'] ) : ?>
					<input type="hidden" name="sparte" value="<?php echo esc_attr( $state['sparte']->slug ); ?>">
				<?php endif; ?>
				<label class="screen-reader-text" for="rv-archiv-suche"><?php esc_html_e( 'Archiv durchsuchen', 'rv-sulz' ); ?></label>
				<svg class="rv-archiv__lupe" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M10 3a7 7 0 0 1 5.6 11.2l4.6 4.6-1.4 1.4-4.6-4.6A7 7 0 1 1 10 3zm0 2a5 5 0 1 0 0 10 5 5 0 0 0 0-10z"/></svg>
				<input id="rv-archiv-suche" type="search" name="suche" value="<?php echo esc_attr( $state['suche'] ); ?>" placeholder="<?php esc_attr_e( 'Suchen, z. B. Meisterschaft', 'rv-sulz' ); ?>" autocomplete="off">
				<button type="submit"><?php esc_html_e( 'Suchen', 'rv-sulz' ); ?></button>
			</form>
		<?php endif; ?>
	</div>

	<?php if ( $sparten ) : ?>
		<nav class="rv-archiv__sparten" aria-label="<?php esc_attr_e( 'Nach Sparte filtern', 'rv-sulz' ); ?>">
			<a data-rv-nav class="rv-chip<?php echo $state['sparte'] ? '' : ' is-active'; ?>" href="<?php echo esc_url( rv_sulz_archiv_url( $base, array_merge( $state, array( 'sparte' => null, 'seite' => 1 ) ) ) ); ?>"<?php echo $state['sparte'] ? '' : ' aria-current="true"'; ?>><?php esc_html_e( 'Alle Sparten', 'rv-sulz' ); ?></a>
			<?php foreach ( $sparten as $term ) : $active = $state['sparte'] && $state['sparte']->term_id === $term->term_id; ?>
				<a data-rv-nav class="rv-chip rv-chip--<?php echo esc_attr( $term->slug ); ?><?php echo $active ? ' is-active' : ''; ?>" href="<?php echo esc_url( rv_sulz_archiv_url( $base, array_merge( $state, array( 'sparte' => $active ? null : $term, 'seite' => 1 ) ) ) ); ?>"<?php echo $active ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $term->name ); ?></a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>

	<nav class="rv-archiv__jahre" aria-label="<?php esc_attr_e( 'Nach Jahr filtern', 'rv-sulz' ); ?>">
		<a data-rv-nav class="rv-jahr<?php echo $state['jahr'] ? '' : ' is-active'; ?>" href="<?php echo esc_url( rv_sulz_archiv_url( $base, array_merge( $state, array( 'jahr' => 0, 'seite' => 1 ) ) ) ); ?>"<?php echo $state['jahr'] ? '' : ' aria-current="true"'; ?>>
			<span class="rv-jahr__j"><?php esc_html_e( 'Alle', 'rv-sulz' ); ?></span>
			<span class="rv-jahr__n"><?php echo esc_html( number_format_i18n( array_sum( $years ) ) ); ?></span>
		</a>
		<?php foreach ( $years as $year => $count ) : $active = $state['jahr'] === $year; ?>
			<a data-rv-nav class="rv-jahr<?php echo $active ? ' is-active' : ''; ?><?php echo $count ? '' : ' is-empty'; ?>" href="<?php echo esc_url( rv_sulz_archiv_url( $base, array_merge( $state, array( 'jahr' => $active ? 0 : $year, 'seite' => 1 ) ) ) ); ?>"<?php echo $active ? ' aria-current="true"' : ''; ?>>
				<span class="rv-jahr__j"><?php echo esc_html( $year ); ?></span>
				<span class="rv-jahr__n"><?php echo esc_html( number_format_i18n( $count ) ); ?></span>
			</a>
		<?php endforeach; ?>
	</nav>

	<div class="rv-archiv__status" role="status">
		<span>
			<?php
			$teile = array();
			if ( $state['sparte'] ) {
				$teile[] = $state['sparte']->name;
			}
			if ( $state['jahr'] ) {
				$teile[] = (string) $state['jahr'];
			}
			if ( '' !== $state['suche'] ) {
				/* translators: %s: Suchbegriff */
				$teile[] = sprintf( __( '„%s“', 'rv-sulz' ), $state['suche'] );
			}
			echo esc_html(
				sprintf(
					/* translators: %s: Anzahl */
					_n( '%s Beitrag', '%s Beiträge', $total, 'rv-sulz' ),
					number_format_i18n( $total )
				) . ( $teile ? ' · ' . implode( ' · ', $teile ) : '' )
			);
			?>
		</span>
		<?php if ( $filters ) : ?>
			<a data-rv-nav class="rv-archiv__reset" href="<?php echo esc_url( rv_sulz_archiv_url( $base, $reset ) ); ?>"><?php esc_html_e( 'Filter zurücksetzen', 'rv-sulz' ); ?></a>
		<?php endif; ?>
	</div>

	<?php if ( $query->have_posts() ) : ?>
		<div class="rv-archiv__grid">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				echo rv_sulz_archiv_card( get_post() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- in der Funktion escaped.
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	<?php else : ?>
		<div class="rv-archiv__leer">
			<p><?php esc_html_e( 'Keine Beiträge gefunden.', 'rv-sulz' ); ?></p>
			<?php if ( $filters ) : ?>
				<a data-rv-nav class="rv-chip is-active" href="<?php echo esc_url( rv_sulz_archiv_url( $base, $reset ) ); ?>"><?php esc_html_e( 'Alle Beiträge zeigen', 'rv-sulz' ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( $query->max_num_pages > 1 ) : ?>
		<nav class="rv-archiv__pager" aria-label="<?php esc_attr_e( 'Weitere Beiträge', 'rv-sulz' ); ?>">
			<?php if ( $state['seite'] > 1 ) : ?>
				<a data-rv-nav class="rv-archiv__prev" href="<?php echo esc_url( rv_sulz_archiv_url( $base, array_merge( $state, array( 'seite' => $state['seite'] - 1 ) ) ) ); ?>"><?php esc_html_e( '← Neuere', 'rv-sulz' ); ?></a>
			<?php endif; ?>
			<span class="rv-archiv__seiten">
				<?php
				/* translators: 1: aktuelle Seite, 2: Seitenzahl */
				echo esc_html( sprintf( __( 'Seite %1$s von %2$s', 'rv-sulz' ), $state['seite'], $query->max_num_pages ) );
				?>
			</span>
			<?php if ( $state['seite'] < $query->max_num_pages ) : ?>
				<a data-rv-nav data-rv-more class="rv-archiv__more" href="<?php echo esc_url( rv_sulz_archiv_url( $base, array_merge( $state, array( 'seite' => $state['seite'] + 1 ) ) ) ); ?>"
					data-label-js="<?php esc_attr_e( 'Mehr laden', 'rv-sulz' ); ?>"><?php esc_html_e( 'Ältere →', 'rv-sulz' ); ?></a>
			<?php endif; ?>
		</nav>
	<?php endif; ?>
	<?php
	return (string) ob_get_clean();
}

/**
 * Eine Beitragskarte.
 *
 * @param WP_Post $post Beitrag.
 * @return string
 */
function rv_sulz_archiv_card( $post ) {
	$link  = get_permalink( $post );
	$title = get_the_title( $post );
	$terms = get_the_category( $post->ID );
	// Konkrete Sparte vor „Veranstaltung“/„Allgemein“ zeigen, höchstens zwei.
	usort(
		$terms,
		function ( $a, $b ) {
			$rank = function ( $t ) {
				return in_array( $t->slug, array( 'allgemein', 'uncategorized', 'veranstaltung' ), true ) ? 1 : 0;
			};
			return $rank( $a ) - $rank( $b );
		}
	);
	$terms = array_slice( $terms, 0, 2 );

	$img = has_post_thumbnail( $post )
		? get_the_post_thumbnail(
			$post,
			'medium_large',
			array(
				'loading' => 'lazy',
				'sizes'   => '(max-width: 640px) 100vw, (max-width: 1000px) 50vw, 400px',
				'alt'     => '',
			)
		)
		: '<span class="rv-acard__platzhalter" aria-hidden="true"></span>';

	$html  = '<article class="rv-acard">';
	$html .= '<div class="rv-acard__bild">' . $img . '</div>';
	$html .= '<div class="rv-acard__text"><div class="rv-acard__meta">';
	foreach ( $terms as $term ) {
		$html .= '<span class="rv-acard__sparte rv-acard__sparte--' . esc_attr( $term->slug ) . '">' . esc_html( $term->name ) . '</span>';
	}
	$html .= '<time datetime="' . esc_attr( get_the_date( 'c', $post ) ) . '">' . esc_html( get_the_date( 'j. F Y', $post ) ) . '</time>';
	$html .= '</div><h3 class="rv-acard__titel"><a href="' . esc_url( $link ) . '">' . esc_html( wp_strip_all_tags( $title ) ) . '</a></h3>';
	$html .= '</div></article>';
	return $html;
}

/**
 * Block-Ausgabe.
 *
 * @param array $attributes Block-Attribute.
 * @return string
 */
function rv_sulz_archiv_render_block( $attributes ) {
	$wrapper = get_block_wrapper_attributes(
		array(
			'class' => 'rv-archiv',
			'id'    => 'archiv',
		)
	);
	return '<div ' . $wrapper . '>' . rv_sulz_archiv_render( $attributes ) . '</div>';
}

/**
 * Shortcode [rv_archiv anzahl="12" suche="ja"].
 *
 * @param array $atts Attribute.
 * @return string
 */
function rv_sulz_archiv_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'anzahl' => 12,
			'suche'  => 'ja',
		),
		$atts,
		'rv_archiv'
	);
	wp_enqueue_style( 'rv-sulz-archiv' );
	wp_enqueue_script( 'rv-sulz-archiv' );
	return '<div class="rv-archiv alignwide" id="archiv">' . rv_sulz_archiv_render(
		array(
			'perPage'    => (int) $atts['anzahl'],
			'showSearch' => ! in_array( strtolower( (string) $atts['suche'] ), array( 'nein', 'no', '0', 'false' ), true ),
		)
	) . '</div>';
}

/**
 * Auf der Archiv-Seite den alten Block „Archiv“ (Jahresliste) im Seiteninhalt ausblenden –
 * die Jahres-Leiste des neuen Archivs ersetzt ihn.
 *
 * @param string $content Block-HTML.
 * @return string
 */
function rv_sulz_archiv_hide_core_archives( $content ) {
	global $_wp_current_template_id;
	$template = (string) $_wp_current_template_id;
	if ( is_page() && ( '//page-archiv' === substr( $template, -13 ) || is_page( 'archiv' ) ) ) {
		return '';
	}
	return $content;
}
add_filter( 'render_block_core/archives', 'rv_sulz_archiv_hide_core_archives' );
