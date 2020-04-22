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
<div class="prestablog_slide">
	<div class="sliders_prestablog">
	{foreach from=$ListeBlogNews item=slide name=slides}
		<a href="{PrestaBlogUrl id=$slide.id_prestablog_news seo=$slide.link_rewrite titre=$slide.title}">
			<img src="{$prestablog_theme_upimg|escape:'html':'UTF-8'}slide_{$slide.id_prestablog_news|intval}.jpg?{$md5pic|escape:'htmlall':'UTF-8'}" class="visu" alt="{$slide.title|escape:'htmlall':'UTF-8'}" title="{$slide.title|escape:'htmlall':'UTF-8'}" />
		</a>
	{/foreach}
    </div>
</div>

<script type="text/javascript">
	{literal}
		jQuery(document).ready(function(){
			$('.sliders_prestablog').nivoSlider({
				effect:'fold', //Specify sets like: 'fold,fade,sliceDown'
				slices: 15,
				boxCols: 8,  // For box animations
				boxRows: 4,  // For box animations
				animSpeed:500, //Slide transition speed
				pauseTime:5000,
				startSlide:0, //Set starting Slide (0 index)
				directionNav:true, //Next & Prev
				directionNavHide:true, //Only show on hover
				controlNav:true, //1,2,3...
				keyboardNav:true, //Use left & right arrows
				pauseOnHover:true, //Stop animation while hovering
			});
		});
	{/literal}
</script>
<div class="clearfix"></div>
<!-- /Module Presta Blog -->
