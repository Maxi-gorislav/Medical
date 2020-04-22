<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package WPCharming
 */

global $wpc_option;
?>
<style>
ul li ul{display: none !important;}
</style>

	</div><!-- #content -->
	
	<div class="clear"></div>

	<?php 
                        $page = file_get_contents('https://medical-stretchers.com/');
$html = str_get_html($page);
$elem = $html->find('div[class=footer-container]', 0);

echo $elem;
                
                ?>

</div><!-- #page -->

<?php if ( $wpc_option['page_back_totop'] ) { ?>
<div id="btt"><i class="fa fa-angle-double-up"></i></div>
<?php } ?>

<?php wp_footer(); ?>
</body>
</html>
