<?php
/**
 * Title: Ehrenmitglieder
 * Slug: rv-sulz/ehrenmitglieder
 * Categories: rv-sulz
 * Keywords: ehrenmitglieder, verein, ehrung, mitglieder
 * Block Types: core/post-content
 * Post Types: page
 * Description: Einleitung und Karten für die Ehrenmitglieder. In jeder Karte Foto aus der Mediathek wählen (leer = Stern), Platzhalter durch Namen ersetzen, Karten per Duplizieren ergänzen.
 *
 * @package rv-sulz
 */
?>
<!-- wp:paragraph {"className":"rv-honor-intro","fontSize":"medium"} -->
<p class="rv-honor-intro has-medium-font-size">Für ihre besonderen Verdienste um den RV Enzian Sulz hat der Verein folgende Mitglieder zu Ehrenmitgliedern ernannt. Herzlichen Dank für euren Einsatz!</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"rv-honor-grid","layout":{"type":"grid","minimumColumnWidth":"13rem"},"style":{"spacing":{"blockGap":"1.25rem","margin":{"top":"var:preset|spacing|50"}}}} -->
<div class="wp-block-group rv-honor-grid" style="margin-top:var(--wp--preset--spacing--50)">
<?php
foreach ( array( 'A', 'B', 'C', 'D', 'E', 'F' ) as $rv_letter ) :
	?>
	<!-- wp:group {"className":"rv-honor-card","layout":{"type":"flex","orientation":"vertical","justifyContent":"center"},"style":{"spacing":{"blockGap":"0.3rem"}}} -->
	<div class="wp-block-group rv-honor-card">
		<!-- wp:image {"sizeSlug":"medium","className":"rv-honor-photo"} -->
		<figure class="wp-block-image size-medium rv-honor-photo"><img alt=""/></figure>
		<!-- /wp:image -->
		<!-- wp:heading {"level":3,"className":"rv-honor-name"} -->
		<h3 class="wp-block-heading rv-honor-name">Ehrenmitglied <?php echo esc_html( $rv_letter ); ?></h3>
		<!-- /wp:heading -->
		<!-- wp:paragraph {"className":"rv-honor-since"} -->
		<p class="rv-honor-since">Ehrenmitglied seit 20XX</p>
		<!-- /wp:paragraph -->
		<!-- wp:paragraph {"className":"rv-honor-note"} -->
		<p class="rv-honor-note">Kurz die Verdienste, z. B. Obmann, Trainer, Funktionär</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:group -->
