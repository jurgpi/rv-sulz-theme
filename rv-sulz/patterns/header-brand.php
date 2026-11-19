<?php
/**
 * Title: Vereinslogo (Kopfbereich)
 * Slug: rv-sulz/header-brand
 * Categories: rv-sulz, header
 * Inserter: no
 *
 * Nutzt das in WordPress gesetzte Website-Logo, sonst das mitgelieferte Vereinslogo.
 * Im Dunkelmodus wird automatisch die weiße Logo-Variante gezeigt.
 *
 * @package rv-sulz
 */
?>
<!-- wp:group {"className":"rv-brand-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group rv-brand-wrap">
<?php if ( has_custom_logo() ) : ?>
<!-- wp:site-logo {"width":130,"className":"rv-brand rv-logo-light"} /-->
<?php else : ?>
<!-- wp:html -->
<a class="rv-brand rv-logo-light" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><img src="<?php echo esc_url( rv_sulz_logo_url() ); ?>" width="130" height="92" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"></a>
<!-- /wp:html -->
<?php endif; ?>
<!-- wp:html -->
<a class="rv-brand rv-logo-dark" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><img src="<?php echo esc_url( rv_sulz_logo_url( 'weiss' ) ); ?>" width="130" height="92" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"></a>
<!-- /wp:html -->
</div>
<!-- /wp:group -->
