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
			var selectedCatFilter = new Object();
			$("div#categoriesFiltrage select[name=SelectCat]").change(function() {
				var keyCat = $(this).val();
				if(keyCat > 0) {
					if(!(keyCat in selectedCatFilter)) {
						selectedCatFilter[ keyCat ] = $("option:selected", this).text().trim();
						$("div#categoriesForFilter").append('<div class="filtrecat" rel="'+keyCat+'">'+$("option:selected", this).text().trim()+'<div class="deleteCat" rel="'+keyCat+'">X</div></div>');
						$("option:selected", this).attr('disabled','disabled');
						$('option:first-child', this).attr("selected", "selected");
					}
				}

				$("#prestablog_input_filtre_cat").html('');
				$("div#categoriesForFilter div.filtrecat").each(function() {
					$("#prestablog_input_filtre_cat").append('<input type="hidden" name="prestablog_search_array_cat[]" value="'+$(this).attr("rel")+'" />');
				});
			});

			$('div#categoriesFiltrage').delegate('div.deleteCat','click',function() {
				var keyCat = $(this).attr('rel');
				$("div#categoriesFiltrage select[name=SelectCat] option[value='"+keyCat+"']").removeAttr('disabled');
				$('div.filtrecat[rel="'+keyCat+'"]').remove();
				delete selectedCatFilter[keyCat];
				$('div#categoriesFiltrage select[name=SelectCat] option:first-child', this).attr("selected", "selected");

				$("#prestablog_input_filtre_cat").html('');
				$("div#categoriesForFilter div.filtrecat").each(function() {
					$("#prestablog_input_filtre_cat").append('<input type="hidden" name="prestablog_search_array_cat[]" value="'+$(this).attr("rel")+'" />');
				});
			});

			{/literal}
			{foreach from=$prestablog_search_array_cat item=cat_filtre}
				$("div#categoriesFiltrage select[name=SelectCat] option[value='{$cat_filtre|escape:'htmlall':'UTF-8'}']").attr('disabled','disabled');
			{/foreach}
			{literal}
		});
	} ) ( jQuery );
	{/literal}
</script>
<div id="categoriesFiltrage">
	{PrestaBlogContent return=$FiltrageCat}
	<form action="{PrestaBlogUrl}" method="post">
		<div id="prestablog_input_filtre_cat">
			{foreach from=$prestablog_search_array_cat item=cat_filtre}
				<input type="hidden" name="prestablog_search_array_cat[]" value="{$cat_filtre|escape:'htmlall':'UTF-8'}" />
			{/foreach}
		</div>
		<input class="search_query form-control ac_input" type="text" value="{$prestablog_search_query|escape:'htmlall':'UTF-8'}" placeholder="{l s='Search again on blog' mod='prestablog'}" name="prestablog_search" autocomplete="off">
		<button class="btn btn-default button-search" type="submit">
			<span>{l s='Search again on blog' mod='prestablog'}</span>
		</button>
		<div class="clear"></div>
	</form>
</div>
<!-- Module Presta Blog -->
