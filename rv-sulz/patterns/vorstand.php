<?php
/**
 * Title: Vorstand
 * Slug: rv-sulz/vorstand
 * Categories: rv-sulz
 * Keywords: vorstand, obmann, funktionäre, team, kontakt
 * Block Types: core/post-content
 * Post Types: page
 * Description: Vorstand mit hervorgehobenem Obmann, Karten für Funktionäre und sportliche Leitung, Kassaprüfer und Kontakt-Aufruf. Fotos per Klick auf den Bild-Platzhalter wählen (leer = Symbol), Platzhalter ersetzen, Karten per Duplizieren ergänzen.
 *
 * @package rv-sulz
 */

$rv_card = static function ( $role, $name, $task, $extra_class = '' ) {
	$class = trim( 'rv-board-card ' . $extra_class );
	?>
	<!-- wp:group {"className":"<?php echo esc_attr( $class ); ?>"} -->
	<div class="wp-block-group <?php echo esc_attr( $class ); ?>">
		<!-- wp:image {"sizeSlug":"medium","className":"rv-board-photo"} -->
		<figure class="wp-block-image size-medium rv-board-photo"><img alt=""/></figure>
		<!-- /wp:image -->
		<!-- wp:paragraph {"className":"rv-board-role"} -->
		<p class="rv-board-role"><?php echo esc_html( $role ); ?></p>
		<!-- /wp:paragraph -->
		<!-- wp:heading {"level":3,"className":"rv-board-name"} -->
		<h3 class="wp-block-heading rv-board-name"><?php echo esc_html( $name ); ?></h3>
		<!-- /wp:heading -->
		<!-- wp:paragraph {"className":"rv-board-task"} -->
		<p class="rv-board-task"><?php echo esc_html( $task ); ?></p>
		<!-- /wp:paragraph -->
		<!-- wp:paragraph {"className":"rv-board-contact"} -->
		<p class="rv-board-contact"><a href="/kontakt/">Kontakt aufnehmen</a></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
	<?php
};
?>
<!-- wp:paragraph {"className":"rv-board-intro","fontSize":"medium"} -->
<p class="rv-board-intro has-medium-font-size">Ehrenamtlich und mit viel Herzblut: Diese Menschen halten den RV Enzian Sulz am Laufen – vom Training über die Wettkämpfe bis zu Finanzen und Veranstaltungen.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"rv-board-h"} -->
<h2 class="wp-block-heading rv-board-h">Vereinsleitung</h2>
<!-- /wp:heading -->

<?php $rv_card( 'Obmann', 'Name Obmann', 'Vertritt den Verein nach außen, leitet Vorstand und Generalversammlung.', 'rv-board-lead' ); ?>

<!-- wp:group {"className":"rv-board-grid","layout":{"type":"grid","minimumColumnWidth":"14rem"},"style":{"spacing":{"blockGap":"1.25rem","margin":{"top":"1.25rem"}}}} -->
<div class="wp-block-group rv-board-grid" style="margin-top:1.25rem">
<?php
$rv_card( 'Obmann-Stellvertreter', 'Name Stellvertreter', 'Unterstützt und vertritt den Obmann.' );
$rv_card( 'Kassier', 'Name Kassier', 'Finanzen, Mitgliedsbeiträge und Förderungen.' );
$rv_card( 'Schriftführer', 'Name Schriftführer', 'Protokolle, Schriftverkehr und Mitgliederverwaltung.' );
?>
</div>
<!-- /wp:group -->

<!-- wp:heading {"className":"rv-board-h"} -->
<h2 class="wp-block-heading rv-board-h">Sportliche Leitung</h2>
<!-- /wp:heading -->

<!-- wp:group {"className":"rv-board-grid","layout":{"type":"grid","minimumColumnWidth":"14rem"},"style":{"spacing":{"blockGap":"1.25rem"}}} -->
<div class="wp-block-group rv-board-grid">
<?php
$rv_card( 'Sportwart Kunstrad', 'Name Sportwart', 'Training, Wettkämpfe und Nachwuchs im Kunstradfahren.', 'is-kunstrad' );
$rv_card( 'Sportwart Radball', 'Name Sportwart', 'Mannschaften, Spielbetrieb und Turniere im Radball.', 'is-radball' );
$rv_card( 'Jugendwart', 'Name Jugendwart', 'Ansprechperson für Kinder, Jugendliche und Eltern.' );
?>
</div>
<!-- /wp:group -->

<!-- wp:heading {"className":"rv-board-h"} -->
<h2 class="wp-block-heading rv-board-h">Kassaprüfer</h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"rv-board-chips"} -->
<ul class="wp-block-list rv-board-chips"><!-- wp:list-item -->
<li>Name Kassaprüfer 1</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Name Kassaprüfer 2</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:group {"className":"rv-board-cta","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"},"style":{"spacing":{"margin":{"top":"var:preset|spacing|60"}}}} -->
<div class="wp-block-group rv-board-cta" style="margin-top:var(--wp--preset--spacing--60)">
	<!-- wp:group {"layout":{"type":"flex","orientation":"vertical"},"style":{"spacing":{"blockGap":"0.2rem"}}} -->
	<div class="wp-block-group">
		<!-- wp:heading {"level":3,"className":"rv-board-cta-title"} -->
		<h3 class="wp-block-heading rv-board-cta-title">Lust mitzuhelfen?</h3>
		<!-- /wp:heading -->
		<!-- wp:paragraph -->
		<p>Ob bei Veranstaltungen, im Training oder im Vorstand – wir freuen uns über jede helfende Hand.</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
	<!-- wp:buttons -->
	<div class="wp-block-buttons">
		<!-- wp:button {"className":"is-style-rv-hell"} -->
		<div class="wp-block-button is-style-rv-hell"><a class="wp-block-button__link wp-element-button" href="/kontakt/">Schreib uns</a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
