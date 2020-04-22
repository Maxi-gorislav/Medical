<?php
/**
 * WooCommerce plugin config
 *
 * @package WPCharming
 */

// Update product image sized.
function wpcharming_woo_thumb_sized() {
	$catalog = array('width'  => '350',	'height' => '400',  'crop'   => 1 );
	//$single  = array('width'  => '350',	'height' => '350',	'crop'   => 1 );
	$thumb   = array('width'  => '150',	'height' => '150',	'crop'   => 1 );
	update_option( 'shop_catalog_image_size', $catalog );
	update_option( 'shop_single_image_size', $catalog ); 
	update_option( 'shop_thumbnail_image_size', $thumb );
}
global $pagenow;
if ( is_admin() && isset( $_GET['activated'] ) && $pagenow == 'themes.php' ) {
	add_action( 'init', 'wpcharming_woo_thumb_sized', 1 );
}

// Product per row.
function products_per_row() {
	return 4;
}
add_filter('loop_shop_columns', 'products_per_row');

// Return product number on a page.
add_filter( 'loop_shop_per_page', create_function( '$number', 'return 12;' ), 20 );

// Hide the default shop title in content area.
add_filter('woocommerce_show_page_title', '__return_false');