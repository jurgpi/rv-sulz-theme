<?php
/**
 * Title: Hero – aktuelle Top-Beiträge
 * Slug: rv-sulz/hero-news
 * Categories: rv-sulz, featured, query
 * Keywords: hero, slider, aktuell, beiträge
 *
 * Großer neuester Beitrag links, die zwei nächsten rechts – automatisch aus den Beiträgen.
 *
 * @package rv-sulz
 */
?>
<!-- wp:group {"align":"full","className":"rv-hero-news rv-sec-hero","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|60"},"margin":{"top":"0"}}},"gradient":"enzian-hell-verlauf","layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull rv-hero-news rv-sec-hero has-enzian-hell-verlauf-gradient-background has-background" style="margin-top:0;padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"left":"1rem","top":"1rem"}}}} -->
	<div class="wp-block-columns alignwide">
		<!-- wp:column {"width":"64%"} -->
		<div class="wp-block-column" style="flex-basis:64%">
			<!-- wp:query {"queryId":11,"className":"rv-q-hero-main","query":{"perPage":1,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false,"sticky":""}} -->
			<div class="wp-block-query rv-q-hero-main">
				<!-- wp:post-template -->
					<!-- wp:cover {"useFeaturedImage":true,"dimRatio":0,"minHeight":520,"contentPosition":"bottom left","className":"rv-tile rv-tile--big","style":{"border":{"radius":"18px"},"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
					<div class="wp-block-cover has-custom-content-position is-position-bottom-left rv-tile rv-tile--big" style="border-radius:18px;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50);min-height:520px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container">
						<!-- wp:post-terms {"term":"category","className":"rv-pill"} /-->
						<!-- wp:post-title {"level":2,"isLink":true,"style":{"elements":{"link":{"color":{"text":"var:preset|color|base"}}}},"fontSize":"xx-large"} /-->
						<!-- wp:post-date {"style":{"color":{"text":"#ffffffcc"}}} /-->
					</div></div>
					<!-- /wp:cover -->
				<!-- /wp:post-template -->
			</div>
			<!-- /wp:query -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"width":"36%"} -->
		<div class="wp-block-column" style="flex-basis:36%">
			<!-- wp:query {"queryId":12,"className":"rv-q-hero-side","query":{"perPage":2,"pages":0,"offset":1,"postType":"post","order":"desc","orderBy":"date","inherit":false,"sticky":""}} -->
			<div class="wp-block-query rv-q-hero-side">
				<!-- wp:post-template {"style":{"spacing":{"blockGap":"1rem"}}} -->
					<!-- wp:cover {"useFeaturedImage":true,"dimRatio":0,"minHeight":252,"contentPosition":"bottom left","className":"rv-tile","style":{"border":{"radius":"18px"},"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
					<div class="wp-block-cover has-custom-content-position is-position-bottom-left rv-tile" style="border-radius:18px;padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40);min-height:252px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container">
						<!-- wp:post-terms {"term":"category","className":"rv-pill"} /-->
						<!-- wp:post-title {"level":3,"isLink":true,"style":{"elements":{"link":{"color":{"text":"var:preset|color|base"}}}},"fontSize":"x-large"} /-->
					</div></div>
					<!-- /wp:cover -->
				<!-- /wp:post-template -->
			</div>
			<!-- /wp:query -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
