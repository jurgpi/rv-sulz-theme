<?php
/**
 * Theme-Updates direkt aus den GitHub-Releases (ohne zusätzliches Plugin).
 *
 * Funktionsweise:
 * - style.css enthält „Update URI: https://github.com/<owner>/<repo>“. WordPress (ab 6.1)
 *   fragt dann nicht wordpress.org, sondern den Filter „update_themes_github.com“.
 * - Dieser Filter liest das neueste GitHub-Release und meldet dessen Version und ZIP.
 * - Beim Update lädt „upgrader_pre_download“ das ZIP mit dem Token herunter.
 *
 * Privates Repository → Token in wp-config.php eintragen (NICHT in die Datenbank):
 *   define( 'RV_SULZ_GITHUB_TOKEN', 'github_pat_…' );
 * Empfohlen: Fine-grained Token, nur dieses Repository, „Contents: Read-only“.
 * Der Token wird ausschließlich an api.github.com gesendet.
 *
 * @package rv-sulz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Repository „owner/name“ aus der Update-URI im Theme-Kopf.
 */
function rv_sulz_updater_repo() {
	$uri = (string) wp_get_theme( get_template() )->get( 'UpdateURI' );
	if ( preg_match( '#^https://github\.com/([A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+?)/?$#', $uri, $m ) ) {
		return $m[1];
	}
	return '';
}

/**
 * GitHub-API-Anfrage (mit Token, falls definiert).
 *
 * @param string $url    API-URL (muss mit https://api.github.com/ beginnen).
 * @param array  $extra  Zusätzliche Argumente für wp_remote_get.
 * @return array|WP_Error
 */
function rv_sulz_updater_request( $url, $extra = array() ) {
	if ( 0 !== strpos( $url, 'https://api.github.com/' ) ) {
		return new WP_Error( 'rv_sulz_updater_url', 'Unerwartete Adresse.' );
	}
	$headers = array(
		'Accept'               => 'application/vnd.github+json',
		'X-GitHub-Api-Version' => '2022-11-28',
		'User-Agent'           => 'rv-sulz-theme-updater',
	);
	if ( defined( 'RV_SULZ_GITHUB_TOKEN' ) && RV_SULZ_GITHUB_TOKEN ) {
		$headers['Authorization'] = 'Bearer ' . RV_SULZ_GITHUB_TOKEN;
	}
	$args = array_merge(
		array(
			'timeout' => 15,
			'headers' => $headers,
		),
		$extra
	);
	if ( isset( $extra['headers'] ) ) {
		$args['headers'] = array_merge( $headers, $extra['headers'] );
	}
	return wp_remote_get( $url, $args );
}

/**
 * Neuestes Release (eine Stunde zwischengespeichert; „Erneut prüfen“ umgeht den Speicher).
 *
 * @return array|null { version, url, asset_api, notes }
 */
function rv_sulz_updater_latest() {
	$repo = rv_sulz_updater_repo();
	if ( ! $repo ) {
		return null;
	}
	$force = is_admin() && isset( $_GET['force-check'] ); // phpcs:ignore WordPress.Security.NonceVerification
	$cache = get_site_transient( 'rv_sulz_updater_release' );
	if ( ! $force && is_array( $cache ) ) {
		return $cache['release'];
	}

	$release = null;
	$error   = '';
	$res     = rv_sulz_updater_request( 'https://api.github.com/repos/' . $repo . '/releases/latest' );
	if ( is_wp_error( $res ) ) {
		$error = $res->get_error_message();
	} else {
		$code = (int) wp_remote_retrieve_response_code( $res );
		$body = json_decode( wp_remote_retrieve_body( $res ), true );
		if ( 200 === $code && is_array( $body ) && ! empty( $body['tag_name'] ) ) {
			$version  = ltrim( (string) $body['tag_name'], 'vV' );
			$template = get_template();
			foreach ( (array) ( isset( $body['assets'] ) ? $body['assets'] : array() ) as $asset ) {
				if ( isset( $asset['name'], $asset['url'] ) && $asset['name'] === $template . '-' . $version . '.zip' ) {
					$release = array(
						'version'   => $version,
						'url'       => isset( $body['html_url'] ) ? esc_url_raw( $body['html_url'] ) : '',
						'asset_api' => esc_url_raw( $asset['url'] ),
					);
					break;
				}
			}
			if ( ! $release ) {
				$error = 'Im neuesten Release fehlt die Datei ' . $template . '-' . $version . '.zip.';
			}
		} elseif ( 404 === $code ) {
			$error = defined( 'RV_SULZ_GITHUB_TOKEN' )
				? 'Kein Release gefunden oder der Token hat keinen Zugriff auf das Repository.'
				: 'Kein Zugriff – für ein privates Repository fehlt RV_SULZ_GITHUB_TOKEN in wp-config.php.';
		} elseif ( 401 === $code ) {
			$error = 'Der GitHub-Token ist ungültig oder abgelaufen.';
		} else {
			$error = 'GitHub antwortete mit Code ' . $code . '.';
		}
	}

	set_site_transient(
		'rv_sulz_updater_release',
		array(
			'release' => $release,
			'error'   => $error,
		),
		$release ? HOUR_IN_SECONDS : 15 * MINUTE_IN_SECONDS
	);
	return $release;
}

/**
 * WordPress fragt nach einem Update für Themes mit „Update URI: https://github.com/…“.
 */
add_filter(
	'update_themes_github.com',
	function ( $update, $theme_data, $theme_stylesheet ) {
		if ( get_template() !== $theme_stylesheet ) {
			return $update;
		}
		$release = rv_sulz_updater_latest();
		if ( ! $release ) {
			return $update;
		}
		return array(
			'theme'        => $theme_stylesheet,
			'version'      => $release['version'],
			'new_version'  => $release['version'],
			'url'          => $release['url'],
			'package'      => $release['asset_api'],
			'requires'     => '6.6',
			'requires_php' => '7.4',
		);
	},
	10,
	3
);

/**
 * Download des Release-ZIPs: zuerst mit Token bei der API anfragen, dann der
 * Weiterleitung (signierte Download-Adresse) OHNE Token folgen.
 */
add_filter(
	'upgrader_pre_download',
	function ( $reply, $package ) {
		$repo = rv_sulz_updater_repo();
		if ( ! $repo || 0 !== strpos( (string) $package, 'https://api.github.com/repos/' . $repo . '/releases/assets/' ) ) {
			return $reply;
		}
		$res = rv_sulz_updater_request(
			$package,
			array(
				'redirection' => 0,
				'headers'     => array( 'Accept' => 'application/octet-stream' ),
			)
		);
		if ( is_wp_error( $res ) ) {
			return $res;
		}
		$location = wp_remote_retrieve_header( $res, 'location' );
		if ( ! $location ) {
			return new WP_Error( 'rv_sulz_updater_download', 'GitHub hat keine Download-Adresse geliefert (Code ' . (int) wp_remote_retrieve_response_code( $res ) . ').' );
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		return download_url( $location, 300 );
	},
	10,
	2
);

/**
 * Sicherheitsnetz: Der Ordner im ZIP muss wie das installierte Theme heißen.
 */
add_filter(
	'upgrader_source_selection',
	function ( $source, $remote_source, $upgrader, $hook_extra = array() ) {
		global $wp_filesystem;
		if ( empty( $hook_extra['theme'] ) || get_template() !== $hook_extra['theme'] || ! $wp_filesystem ) {
			return $source;
		}
		$wanted = trailingslashit( $remote_source ) . get_template() . '/';
		if ( untrailingslashit( $source ) === untrailingslashit( $wanted ) ) {
			return $source;
		}
		if ( $wp_filesystem->move( untrailingslashit( $source ), untrailingslashit( $wanted ) ) ) {
			return $wanted;
		}
		return new WP_Error( 'rv_sulz_updater_rename', 'Der Theme-Ordner konnte nicht korrekt benannt werden – Update abgebrochen.' );
	},
	10,
	4
);

/**
 * Hinweis für Admins, wenn die Update-Prüfung nicht funktioniert (Themes- und Aktualisierungen-Seite).
 */
add_action(
	'admin_notices',
	function () {
		if ( ! current_user_can( 'update_themes' ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || ! in_array( $screen->id, array( 'themes', 'update-core' ), true ) ) {
			return;
		}
		$cache = get_site_transient( 'rv_sulz_updater_release' );
		if ( is_array( $cache ) && ! empty( $cache['error'] ) ) {
			printf(
				'<div class="notice notice-warning"><p><strong>%1$s</strong> %2$s</p></div>',
				esc_html__( 'RV-Sulz-Theme-Updates:', 'rv-sulz' ),
				esc_html( $cache['error'] )
			);
		}
	}
);
