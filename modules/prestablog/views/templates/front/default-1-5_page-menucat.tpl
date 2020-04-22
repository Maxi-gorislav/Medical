{*
 * 2008 - 2015 HDClic
 *
 * MODULE PrestaBlog
 *
 * @version   3.6.3
 * @author    HDClic <prestashop@hdclic.com>
 * @link      http://www.hdclic.com
 * @copyright Copyright (c) permanent, HDClic
 * @license   Addons PrestaShop license limitation
 *
 * NOTICE OF LICENSE
 *
 * Don't use this module on several shops. The license provided by PrestaShop Addons
 * for all its modules is valid only once for a single shop.
 *}

<!-- Module Presta Blog -->
<script type="text/javascript">
	{literal}
	( function($) {
		$(function() {
			$("div#menu-mobile, div#menu-mobile-close").click(function() {
				$("#prestablog_menu_cat nav").toggle();
			});
		});
	} ) ( jQuery );
	{/literal}
</script>
<div id="prestablog_menu_cat">
	<div id="menu-mobile"></div>
	<nav>
		{PrestaBlogContent return=$MenuCatNews}
	</nav>
</div>
<!-- Module Presta Blog -->
