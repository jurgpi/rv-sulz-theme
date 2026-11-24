<?php
/**
 * Title: Neuigkeiten – Karten-Raster
 * Slug: rv-sulz/news-grid
 * Categories: rv-sulz, query, posts
 * Keywords: news, beiträge, raster, grid
 *
 * @package rv-sulz
 */
?>
<!-- wp:group {"align":"full","className":"rv-sec-grid","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"},"margin":{"top":"0"}}},"backgroundColor":"flaeche","layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull rv-sec-grid has-flaeche-background-color has-background" style="margin-top:0;padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)">
	<!-- wp:group {"align":"wide","layout":{"type":"constrained","contentSize":"680px","justifyContent":"left"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:paragraph {"className":"rv-kicker","style":{"typography":{"fontSize":"var:preset|font-size|x-small","fontWeight":"700"}},"textColor":"enzian"} -->
		<p class="rv-kicker has-enzian-color has-text-color" style="font-size:var(--wp--preset--font-size--x-small);font-weight:700">Aus dem Vereinsleben</p>
		<!-- /wp:paragraph -->
		<!-- wp:heading {"className":"rv-h-grid","fontSize":"xx-large"} -->
		<h2 class="wp-block-heading rv-h-grid has-xx-large-font-size">Neuigkeiten</h2>
		<!-- /wp:heading -->
	</div>
	<!-- /wp:group -->

	<!-- wp:query {"queryId":31,"className":"rv-q-grid","query":{"perPage":6,"pages":0,"offset":3,"postType":"post","order":"desc","orderBy":"date","inherit":false,"sticky":""},"align":"wide"} -->
	<div class="wp-block-query alignwide rv-q-grid">
		<!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"grid","columnCount":3,"minimumColumnWidth":"18rem"}} -->
			<!-- wp:pattern {"slug":"rv-sulz/post-card"} /-->
		<!-- /wp:post-template -->

		<!-- wp:query-pagination {"paginationArrow":"arrow","layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"var:preset|spacing|60"}}}} -->
			<!-- wp:query-pagination-previous {"label":"Neuere"} /-->
			<!-- wp:query-pagination-numbers /-->
			<!-- wp:query-pagination-next {"label":"Ältere"} /-->
		<!-- /wp:query-pagination -->
	</div>
	<!-- /wp:query -->
</div>
<!-- /wp:group -->
