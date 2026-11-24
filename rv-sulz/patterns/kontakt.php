<?php
/**
 * Title: Kontakt & Schnuppertraining
 * Slug: rv-sulz/kontakt
 * Categories: rv-sulz, contact
 * Keywords: kontakt, formular, schnuppertraining, anfrage, contact form 7
 * Block Types: core/post-content
 * Post Types: page
 * Description: Links das Anfrage-Formular (Contact Form 7), rechts Trainingsort und Ansprechpersonen je Funktion. Im Shortcode-Block die Formular-ID eintragen, Platzhalter in eckigen Klammern ersetzen.
 *
 * @package rv-sulz
 */

$rv_contact = static function ( $role, $what ) {
	?>
		<!-- wp:group {"layout":{"type":"default"}} -->
		<div class="wp-block-group">
			<!-- wp:paragraph {"className":"rv-kontakt-role"} -->
			<p class="rv-kontakt-role"><?php echo esc_html( $role ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph {"textColor":"grau","fontSize":"small"} -->
			<p class="has-grau-color has-text-color has-small-font-size"><?php echo esc_html( $what ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph -->
			<p><a href="mailto:"><?php esc_html_e( '[E-Mail-Adresse eintragen]', 'rv-sulz' ); ?></a></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	<?php
};
?>
<!-- wp:columns {"align":"wide","className":"rv-kontakt","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60"}}}} -->
<div class="wp-block-columns alignwide rv-kontakt">
	<!-- wp:column {"width":"60%"} -->
	<div class="wp-block-column" style="flex-basis:60%">
		<!-- wp:group {"className":"is-style-rv-karte","layout":{"type":"default"}} -->
		<div class="wp-block-group is-style-rv-karte">
			<!-- wp:heading {"fontSize":"xx-large"} -->
			<h2 class="wp-block-heading has-xx-large-font-size"><?php esc_html_e( 'Schnuppertraining anfragen', 'rv-sulz' ); ?></h2>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"textColor":"grau"} -->
			<p class="has-grau-color has-text-color"><?php esc_html_e( 'Schreib uns kurz, für welche Sparte du dich interessierst und wie alt du bist – wir melden uns mit einem Termin. Das erste Training ist gratis, Räder sind da.', 'rv-sulz' ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:shortcode -->
			[contact-form-7 id="FORMULAR-ID" title="Schnuppertraining"]
			<!-- /wp:shortcode -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:column -->

	<!-- wp:column {"width":"40%","className":"rv-kontakt-info"} -->
	<div class="wp-block-column rv-kontakt-info" style="flex-basis:40%">
		<!-- wp:group {"className":"is-style-rv-karte","layout":{"type":"default"}} -->
		<div class="wp-block-group is-style-rv-karte">
			<!-- wp:heading {"level":3,"fontSize":"x-large"} -->
			<h3 class="wp-block-heading has-x-large-font-size"><?php esc_html_e( 'Hier trainieren wir', 'rv-sulz' ); ?></h3>
			<!-- /wp:heading -->
			<!-- wp:paragraph -->
			<p><strong><?php esc_html_e( '[Name der Halle]', 'rv-sulz' ); ?></strong><br><?php esc_html_e( '[Straße Hausnummer]', 'rv-sulz' ); ?><br><?php esc_html_e( '[PLZ Ort]', 'rv-sulz' ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:buttons -->
			<div class="wp-block-buttons">
				<!-- wp:button {"className":"is-style-outline"} -->
				<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="https://www.openstreetmap.org/"><?php esc_html_e( 'Auf der Karte zeigen', 'rv-sulz' ); ?></a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"is-style-rv-karte","layout":{"type":"default"}} -->
		<div class="wp-block-group is-style-rv-karte">
			<!-- wp:heading {"level":3,"fontSize":"x-large"} -->
			<h3 class="wp-block-heading has-x-large-font-size"><?php esc_html_e( 'Ansprechpersonen', 'rv-sulz' ); ?></h3>
			<!-- /wp:heading -->
			<!-- wp:group {"className":"rv-kontakt-list","layout":{"type":"default"}} -->
			<div class="wp-block-group rv-kontakt-list">
				<?php
				$rv_contact( __( 'Sportliche Leitung Kunstrad', 'rv-sulz' ), __( 'Training, Schnuppern, Wettkämpfe', 'rv-sulz' ) );
				$rv_contact( __( 'Sportliche Leitung Radball', 'rv-sulz' ), __( 'Training, Mannschaften, Turniere', 'rv-sulz' ) );
				$rv_contact( __( 'Verein', 'rv-sulz' ), __( 'Mitgliedschaft, Beiträge, allgemeine Fragen', 'rv-sulz' ) );
				?>
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:column -->
</div>
<!-- /wp:columns -->
