<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package WPCharming
 */
global $wpc_option;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php wp_head(); ?>
        <link rel="shortcut icon" type="image/x-icon" href="/img/favicon.ico?1490818537" />
        
        
        <link rel="stylesheet" href="https://medical-stretchers.com/themes/default-bootstrap/css/global.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/themes/default-bootstrap/css/autoload/highdpi.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/themes/default-bootstrap/css/autoload/responsive-tables.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/themes/default-bootstrap/css/autoload/uniform.default.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/js/jquery/plugins/fancybox/jquery.fancybox.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/themes/default-bootstrap/css/product_list.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/themes/default-bootstrap/css/modules/blockcart/blockcart.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/themes/default-bootstrap/css/modules/blockcategories/blockcategories.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/themes/default-bootstrap/css/modules/blockcurrencies/blockcurrencies.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/modules/blockfacebook/css/blockfacebook.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/themes/default-bootstrap/css/modules/blocklanguages/blocklanguages.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/themes/default-bootstrap/css/modules/blockcontact/blockcontact.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/themes/default-bootstrap/css/modules/blocknewsletter/blocknewsletter.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/js/jquery/plugins/autocomplete/jquery.autocomplete.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/themes/default-bootstrap/css/modules/blocksearch/blocksearch.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/themes/default-bootstrap/css/modules/blocktags/blocktags.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/themes/default-bootstrap/css/modules/blockuserinfo/blockuserinfo.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/themes/default-bootstrap/css/modules/blockviewed/blockviewed.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/modules/themeconfigurator/css/hooks.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/themes/default-bootstrap/css/modules/blockwishlist/blockwishlist.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/modules/authorizedotnet/css/authorizedotnet.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/themes/default-bootstrap/css/modules/homeslider/homeslider.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/modules/relatedproductpro/views/css/style.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/themes/default-bootstrap/css/modules/blocktopmenu/css/blocktopmenu.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/themes/default-bootstrap/css/modules/blocktopmenu/css/superfish-modified.css" type="text/css" media="all" />
			<link rel="stylesheet" href="https://medical-stretchers.com/modules/blockcmsinfo/style.css" type="text/css" media="all" />
        
        		<script type="text/javascript">
                      

jQuery(document).ready(function(){
      jQuery(".cat-title").click(function(){
         jQuery(".sf-menu").toggle();
      });
   });

                        </script>
		
</head>

<body <?php body_class(); ?>>
<div id="page" class="hfeed site">

	<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'wpcharming' ); ?></a>

	<header id="header" class="site-header <?php if ( wpcharming_option('header_fixed') ) echo 'fixed-on' ?>" role="banner">
           <?php 
           $url = 'https://medical-stretchers.com/';
$content = file_get_contents($url);
$first_step = explode( '<header id="header">' , $content );
$second_step = explode("</header>" , $first_step[1] );
echo $second_step[0];
            ?>
	</header><!-- #masthead -->

	<div id="content" class="site-content">
            
