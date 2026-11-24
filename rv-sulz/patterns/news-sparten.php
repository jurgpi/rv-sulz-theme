<?php
/**
 * Title: News nach Sparte (zwei Kategorien)
 * Slug: rv-sulz/news-sparten
 * Categories: rv-sulz, query
 * Keywords: news, kunstrad, radball, beiträge
 *
 * Zwei Spalten mit den neuesten Beiträgen zweier Kategorien.
 * Auswahl unter Design → Customizer → Startseite (Vorgabe: Kunstrad | Radball).
 *
 * @package rv-sulz
 */

$rv_cols = array( 1 => 21, 2 => 22 );
?>
<!-- wp:group {"align":"full","className":"rv-sec-news","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|70"},"margin":{"top":"0"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull rv-sec-news" style="margin-top:0;padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--70)">
	<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60","top":"var:preset|spacing|60"}}}} -->
	<div class="wp-block-columns alignwide">
	<?php
	foreach ( $rv_cols as $rv_n => $rv_qid ) :
		$rv_cat   = (int) rv_sulz_fp( "rv_fp_col{$rv_n}_cat" );
		$rv_count = max( 1, (int) rv_sulz_fp( "rv_fp_col{$rv_n}_count" ) );
		$rv_tax   = $rv_cat ? ',"taxQuery":{"category":[' . $rv_cat . ']}' : '';
		?>
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|40"}},"border":{"bottom":{"color":"var:preset|color|enzian","width":"3px"}}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
			<div class="wp-block-group" style="border-bottom-color:var(--wp--preset--color--enzian);border-bottom-width:3px;margin-bottom:var(--wp--preset--spacing--40)">
				<!-- wp:heading {"className":"rv-h-col-<?php echo (int) $rv_n; ?>","fontSize":"x-large"} -->
				<h2 class="wp-block-heading rv-h-col-<?php echo (int) $rv_n; ?> has-x-large-font-size"><?php echo esc_html( rv_sulz_fp_col_title( $rv_n ) ); ?></h2>
				<!-- /wp:heading -->
				<!-- wp:paragraph {"className":"rv-l-col-<?php echo (int) $rv_n; ?>","style":{"typography":{"fontWeight":"700","fontSize":"var:preset|font-size|small"},"elements":{"link":{"typography":{"textDecoration":"none"}}}}} -->
				<p class="rv-l-col-<?php echo (int) $rv_n; ?> has-link-color" style="font-size:var(--wp--preset--font-size--small);font-weight:700"><a href="<?php echo esc_url( rv_sulz_fp_col_link( $rv_n ) ); ?>">Alle →</a></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:query {"queryId":<?php echo (int) $rv_qid; ?>,"className":"rv-q-col-<?php echo (int) $rv_n; ?>","query":{"perPage":<?php echo (int) $rv_count; ?>,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false,"sticky":""<?php echo $rv_tax; // phpcs:ignore WordPress.Security.EscapeOutput -- nur Ziffern ?>}} -->
			<div class="wp-block-query rv-q-col-<?php echo (int) $rv_n; ?>">
				<!-- wp:post-template {"style":{"spacing":{"blockGap":"1.1rem"}}} -->
					<!-- wp:group {"className":"rv-row rv-card","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"1rem"}}} -->
					<div class="wp-block-group rv-row rv-card">
						<!-- wp:post-featured-image {"isLink":true,"width":"120px","height":"84px","style":{"border":{"radius":"12px"}}} /-->
						<!-- wp:group {"style":{"spacing":{"blockGap":"0.25rem"}},"layout":{"type":"flex","orientation":"vertical"}} -->
						<div class="wp-block-group">
							<!-- wp:post-date {"format":"j. F Y"} /-->
							<!-- wp:post-title {"level":3,"isLink":true,"style":{"typography":{"fontSize":"var:preset|font-size|title","lineHeight":"1.1"}}} /-->
						</div>
						<!-- /wp:group -->
					</div>
					<!-- /wp:group -->
				<!-- /wp:post-template -->
			</div>
			<!-- /wp:query -->
		</div>
		<!-- /wp:column -->
	<?php endforeach; ?>
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
