<?php
/**
 * The template for displaying single portfolio.
 *
 * @package WPCharming
 */
global $wpc_option;
$page_layout     = get_post_meta( $post->ID, '_wpc_page_layout', true );
$page_breadcrumb = get_post_meta( $post->ID, '_wpc_hide_breadcrumb', true );
$hide_page_title = get_post_meta( $post->ID, '_wpc_hide_page_title', true );

wpcharming_get_header() ?>

		<?php 
		global $post;
		wpcharming_get_page_header($post->ID);
		//wpcharming_get_page_title($post->ID);
		?>
		
		<?php if ( $hide_page_title != 'on' ) { ?>
		<div class="page-title-wrap">
			<div class="container">
				<h1 class="page-entry-title left">
					<?php the_title(); ?>
				</h1>
				<div class="portfolio-nav right">
					<?php
					$prev = get_adjacent_post(false, '', true);
    				$next = get_adjacent_post(false, '', false);
    				if($prev){
				        $url = get_permalink($prev->ID);
				        echo '<a href="' . $url . '" class="portfolio-prev" title="' . $prev->post_title . '"><i class="fa fa-angle-left"></i></a>';
				    }
				    if($next){
				        $url = get_permalink($next->ID);
				        echo '<a href="' . $url . '" class="portfolio-next" title="' . $next->post_title . '"><i class="fa fa-angle-right"></i></a>';
				    }
					?>
				</div>
			</div>
		</div>
		<?php } ?>
		
		<?php if ( $page_breadcrumb !== 'on' ) wpcharming_breadcrumb(); ?>

		<div id="content-wrap" class="<?php echo ( $page_layout == 'full-screen' ) ? '' : 'container'; ?> <?php echo wpcharming_get_layout_class(); ?>">
			<div id="primary" class="<?php echo ( $page_layout == 'full-screen' ) ? 'content-area-full' : 'content-area'; ?>">
				<main id="main" class="site-main" role="main">

					<?php while ( have_posts() ) : the_post(); ?>

						<?php get_template_part( 'content', 'page' ); ?>

					<?php endwhile; // end of the loop. ?>

				</main><!-- #main -->
			</div><!-- #primary -->
			
			<?php echo wpcharming_get_sidebar(); ?>

		</div> <!-- /#content-wrap -->

<?php get_footer(); ?>
