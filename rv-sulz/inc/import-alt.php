<?php
/**
 * Werkzeuge → „Alte Website importieren“
 *
 * Sucht in allen Beiträgen und Seiten Links/Bilder auf Dateien der alten Vereinswebsite
 * (www.rv-sulz.at – PDF, Excel, Word, Bilder, Videos), lädt sie auf dem Server herunter, legt sie in der
 * Mediathek ab (am jeweiligen Beitrag angehängt, Ordner nach Beitragsdatum) und ersetzt die Adressen in
 * den Inhalten durch relative Pfade (/wp-content/uploads/…). Links auf HTML-Seiten der alten Website
 * bleiben unverändert. Bereits importierte Dateien werden erkannt (Meta _rv_alt_quelle) und nicht doppelt
 * geladen. Jede Inhaltsänderung erzeugt eine Revision.
 *
 * @package rv-sulz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dateiendungen, die übernommen werden.
 *
 * @return string[]
 */
function rv_sulz_alt_exts() {
	return apply_filters( 'rv_sulz_alt_exts', array( 'pdf', 'xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'mp4', 'zip' ) );
}

/**
 * Regulärer Ausdruck für Datei-Adressen der alten Website.
 *
 * @return string
 */
function rv_sulz_alt_regex() {
	$hosts = apply_filters( 'rv_sulz_alt_hosts', array( 'www.rv-sulz.at', 'rv-sulz.at' ) );
	$hosts = implode( '|', array_map( 'preg_quote', $hosts ) );
	$exts  = implode( '|', array_map( 'preg_quote', rv_sulz_alt_exts() ) );
	return '#https?://(?:' . $hosts . ')/[^"\'\s<>()]+?\.(?:' . $exts . ')(?=["\'\s<>()?]|$)#i';
}

/**
 * Alle Datei-Adressen der alten Website mit den Inhalten, in denen sie vorkommen.
 *
 * @return array<string,int[]> Adresse => Beitrags-IDs (ältester zuerst).
 */
function rv_sulz_alt_scan() {
	global $wpdb;
	$like = '%rv-sulz.at/%';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT ID, post_content FROM {$wpdb->posts}
			WHERE post_type IN ('post','page') AND post_status NOT IN ('trash','auto-draft','inherit')
			AND post_content LIKE %s ORDER BY post_date ASC",
			$like
		)
	);
	$found = array();
	foreach ( (array) $rows as $row ) {
		if ( preg_match_all( rv_sulz_alt_regex(), $row->post_content, $m ) ) {
			foreach ( array_unique( $m[0] ) as $url ) {
				$url = html_entity_decode( $url );
				// Gleiche Datei, nur andere Schreibweise (http/https, mit/ohne www) zusammenfassen.
				$key             = rv_sulz_alt_key( $url );
				$found[ $key ]   = isset( $found[ $key ] ) ? $found[ $key ] : array();
				$found[ $key ][] = (int) $row->ID;
			}
		}
	}
	foreach ( $found as $k => $ids ) {
		$found[ $k ] = array_values( array_unique( $ids ) );
	}
	return $found;
}

/**
 * Einheitliche Schreibweise einer alten Adresse (https, www).
 *
 * @param string $url Adresse.
 * @return string
 */
function rv_sulz_alt_key( $url ) {
	return preg_replace( '#^https?://(?:www\.)?#i', 'https://www.', $url );
}

/**
 * Bereits importierter Anhang zu einer alten Adresse.
 *
 * @param string $key Adresse (einheitlich).
 * @return int Anhang-ID oder 0.
 */
function rv_sulz_alt_attachment( $key ) {
	$ids = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_rv_alt_quelle', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $key, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	return $ids ? (int) $ids[0] : 0;
}

/**
 * Lesbarer Dateiname aus der alten Adresse (auch alte Latin-1-Kodierung wie %D6 = Ö).
 *
 * @param string $url Adresse.
 * @return string
 */
function rv_sulz_alt_filename( $url ) {
	$name = rawurldecode( basename( (string) wp_parse_url( $url, PHP_URL_PATH ) ) );
	if ( ! seems_utf8( $name ) && function_exists( 'mb_convert_encoding' ) ) {
		$name = mb_convert_encoding( $name, 'UTF-8', 'ISO-8859-1' );
	}
	return $name;
}

/**
 * Eine Datei importieren und alle Vorkommen ersetzen.
 *
 * @param string $key Adresse (einheitlich).
 * @param int[]  $ids Inhalte, in denen sie vorkommt.
 * @return array Ergebnis.
 */
function rv_sulz_alt_import_one( $key, $ids ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$att = rv_sulz_alt_attachment( $key );
	if ( ! $att ) {
		$tmp = download_url( $key, 120 );
		if ( is_wp_error( $tmp ) ) {
			return array(
				'url'   => $key,
				'ok'    => false,
				'error' => $tmp->get_error_message(),
			);
		}
		$name  = rv_sulz_alt_filename( $key );
		$title = preg_replace( '/\.[^.]+$/', '', $name );
		$att   = media_handle_sideload(
			array(
				'name'     => $name,
				'tmp_name' => $tmp,
			),
			(int) $ids[0],
			$title
		);
		if ( is_wp_error( $att ) ) {
			wp_delete_file( $tmp );
			return array(
				'url'   => $key,
				'ok'    => false,
				'error' => $att->get_error_message(),
			);
		}
		update_post_meta( $att, '_rv_alt_quelle', $key );
	}

	$new     = wp_make_link_relative( wp_get_attachment_url( $att ) );
	$path    = preg_replace( '#^https://www\.#', '', $key ); // „rv-sulz.at/Dateien/…“
	$pattern = '#https?://(?:www\.)?' . preg_quote( $path, '#' ) . '#i';
	$changed = 0;
	foreach ( $ids as $id ) {
		$post = get_post( $id );
		if ( ! $post ) {
			continue;
		}
		$content = preg_replace( $pattern, $new, $post->post_content );
		if ( $content !== $post->post_content ) {
			wp_update_post(
				array(
					'ID'           => $id,
					'post_content' => wp_slash( $content ),
				)
			);
			++$changed;
		}
	}
	return array(
		'url'     => $key,
		'ok'      => true,
		'new'     => $new,
		'changed' => $changed,
	);
}

/**
 * Menüpunkt unter Werkzeuge.
 */
function rv_sulz_alt_menu() {
	add_management_page(
		__( 'Alte Website importieren', 'rv-sulz' ),
		__( 'Alte Website importieren', 'rv-sulz' ),
		'manage_options',
		'rv-sulz-alt-import',
		'rv_sulz_alt_page'
	);
}
add_action( 'admin_menu', 'rv_sulz_alt_menu' );

/**
 * Werkzeug-Seite.
 */
function rv_sulz_alt_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$found = rv_sulz_alt_scan();
	$done  = 0;
	foreach ( array_keys( $found ) as $key ) {
		if ( rv_sulz_alt_attachment( $key ) ) {
			++$done;
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Alte Website importieren', 'rv-sulz' ); ?></h1>
		<p><?php esc_html_e( 'Holt PDFs, Excel- und Word-Dateien, Bilder und Videos, auf die Beiträge und Seiten noch bei der alten Vereinswebsite (www.rv-sulz.at) verlinken, in die Mediathek und stellt die Links auf die neue Adresse um. Links auf HTML-Seiten der alten Website bleiben unverändert. Jede Änderung ist als Revision des Beitrags gespeichert.', 'rv-sulz' ); ?></p>
		<p>
			<?php
			printf(
				/* translators: 1: Anzahl Dateien, 2: davon schon in der Mediathek */
				esc_html__( 'Gefunden: %1$d Dateien, davon %2$d schon in der Mediathek.', 'rv-sulz' ),
				count( $found ),
				(int) $done
			);
			?>
		</p>
		<?php if ( $found ) : ?>
			<p><button type="button" class="button button-primary" id="rv-alt-start"><?php esc_html_e( 'Import starten', 'rv-sulz' ); ?></button></p>
			<p id="rv-alt-status" role="status" aria-live="polite"></p>
			<ol id="rv-alt-log" style="max-height:24rem;overflow:auto;background:#fff;border:1px solid #c3c4c7;padding:.5rem 1rem 0 2.5rem"></ol>
			<script>
			( function () {
				var btn = document.getElementById( 'rv-alt-start' ), status = document.getElementById( 'rv-alt-status' ), log = document.getElementById( 'rv-alt-log' );
				var ok = 0, fail = 0;
				function line( txt, bad ) { var li = document.createElement( 'li' ); li.textContent = txt; if ( bad ) { li.style.color = '#b32d2e'; } log.appendChild( li ); log.scrollTop = log.scrollHeight; }
				function step( offset ) {
					var body = new FormData();
					body.append( 'action', 'rv_sulz_alt_import' );
					body.append( '_wpnonce', <?php echo wp_json_encode( wp_create_nonce( 'rv_sulz_alt_import' ) ); ?> );
					body.append( 'offset', offset );
					fetch( ajaxurl, { method: 'POST', credentials: 'same-origin', body: body } )
						.then( function ( r ) { return r.json(); } )
						.then( function ( res ) {
							if ( !res.success ) { throw new Error( res.data || 'Fehler' ); }
							res.data.items.forEach( function ( it ) {
								if ( it.ok ) { ok++; line( '✓ ' + decodeURIComponent( it.url.split( '/' ).pop() ) + ' → ' + it.new + ' (' + it.changed + ' Inhalt/e)' ); }
								else { fail++; line( '✗ ' + it.url + ': ' + it.error, true ); }
							} );
							status.textContent = ok + ' übernommen, ' + fail + ' Fehler – noch ' + Math.max( 0, res.data.total - res.data.next ) + ' offen';
							if ( res.data.next < res.data.total ) { step( res.data.next ); }
							else { status.textContent += ' – fertig.'; btn.disabled = false; }
						} )
						.catch( function ( e ) { status.textContent = 'Abgebrochen: ' + e.message + ' – „Import starten“ setzt fort.'; btn.disabled = false; } );
				}
				btn.addEventListener( 'click', function () { btn.disabled = true; ok = 0; fail = 0; log.textContent = ''; status.textContent = 'Läuft …'; step( 0 ); } );
			} )();
			</script>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * AJAX: ein Paket von Dateien importieren.
 */
function rv_sulz_alt_ajax() {
	if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'rv_sulz_alt_import', '_wpnonce', false ) ) {
		wp_send_json_error( 'Keine Berechtigung.' );
	}
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 120 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	}
	$found  = rv_sulz_alt_scan();
	$keys   = array_keys( $found );
	$offset = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;
	// Bereits umgestellte Dateien fallen aus der Liste – deshalb die Position nur über die Fehlerzahl fortführen.
	$batch = array_slice( $keys, $offset, 4 );
	$items = array();
	$fails = 0;
	foreach ( $batch as $key ) {
		$res     = rv_sulz_alt_import_one( $key, $found[ $key ] );
		$items[] = $res;
		if ( ! $res['ok'] ) {
			++$fails;
		}
	}
	$remaining = count( rv_sulz_alt_scan() );
	wp_send_json_success(
		array(
			'items' => $items,
			// Erfolgreich umgestellte verschwinden aus dem Scan; fehlgeschlagene bleiben und werden übersprungen.
			'next'  => $offset + $fails,
			'total' => $offset + $fails + max( 0, $remaining - $offset - $fails ),
		)
	);
}
add_action( 'wp_ajax_rv_sulz_alt_import', 'rv_sulz_alt_ajax' );
