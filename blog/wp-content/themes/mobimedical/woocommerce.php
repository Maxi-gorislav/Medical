<?php
/**
 * Custom Woocommerce shop page.
 *
 * @package WPCharming
 */

global $woocommerce;
$page_layout = get_post_meta( woocommerce_get_page_id('shop'), '_wpc_page_layout', true );
$hide_page_title = get_post_meta( woocommerce_get_page_id('shop'), '_wpc_hide_page_title', true );

wpcharming_get_header() ?>
		
		<?php
		if(is_shop() || is_product_category() || is_product_tag() || is_product()) {
			wpcharming_get_page_header(woocommerce_get_page_id('shop'));
		}
		?>

		<?php 
		global $post;
		wpcharming_get_page_header($post->ID);
		?>
		
		<?php
		if ( $hide_page_title != 'on' ) {
			if ( $woocommerce && is_shop() || $woocommerce && is_product() || $woocommerce && is_product_category() || $woocommerce && is_product_tag() ) {
				?>
				<div class="page-title-wrap">
					<div class="container">
						<h1 class="page-entry-title left">
							<?php woocommerce_page_title(); ?>
						</h1>
						<div class="shop-elements right">
							<a href="<?php echo $woocommerce->cart->get_cart_url(); ?>" class="portfolio-next" title="<?php echo __('Your Shopping Cart', 'wpcharming') ?>"><i class="fa fa-shopping-cart"></i></a>
						</div>
					</div>
				</div>
				<?php
			}
		}
		?>

		<?php wpcharming_breadcrumb(); ?>

		<div id="content-wrap" class="<?php echo ( $page_layout == 'full-screen' ) ? '' : 'container'; ?> <?php echo wpcharming_get_layout_class(); ?>">
			<div id="primary" class="<?php echo ( $page_layout == 'full-screen' ) ? 'content-area-full' : 'content-area'; ?>">
				<main id="main" class="site-main" role="main">

					<?php woocommerce_content(); ?>

				</main><!-- #main -->
			</div><!-- #primary -->
			
			<?php echo wpcharming_get_sidebar(); ?>
				
		</div> <!-- /#content-wrap -->

<?php
get_footer();
