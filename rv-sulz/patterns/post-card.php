<?php
/**
 * Title: Beitragskarte
 * Slug: rv-sulz/post-card
 * Categories: rv-sulz
 * Inserter: no
 *
 * @package rv-sulz
 */
?>
<!-- wp:group {"className":"rv-card rv-post-card","style":{"border":{"radius":"18px"},"spacing":{"padding":{"bottom":"var:preset|spacing|40"},"blockGap":"0.6rem"}},"backgroundColor":"base","layout":{"type":"flex","orientation":"vertical","flexWrap":"nowrap"}} -->
<div class="wp-block-group rv-card rv-post-card has-base-background-color has-background" style="border-radius:18px;padding-bottom:var(--wp--preset--spacing--40)">
	<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"3/2","style":{"border":{"radius":"0px"}}} /-->
	<!-- wp:group {"style":{"spacing":{"padding":{"left":"var:preset|spacing|40","right":"var:preset|spacing|40","top":"0.4rem"},"blockGap":"0.5rem"}},"layout":{"type":"flex","orientation":"vertical"}} -->
	<div class="wp-block-group" style="padding-top:0.4rem;padding-right:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)">
		<!-- wp:group {"style":{"spacing":{"blockGap":"0.75rem"}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
		<div class="wp-block-group">
			<!-- wp:post-terms {"term":"category"} /-->
			<!-- wp:post-date {"format":"j. F Y"} /-->
		</div>
		<!-- /wp:group -->
		<!-- wp:post-title {"level":3,"isLink":true,"style":{"typography":{"fontSize":"var:preset|font-size|title"}}} /-->
		<!-- wp:post-excerpt {"excerptLength":20,"style":{"typography":{"fontSize":"var:preset|font-size|small"}},"textColor":"grau"} /-->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
