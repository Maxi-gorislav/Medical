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
<div class="block">
	<h4 class="title_block">{l s='Search on blog' mod='prestablog'}</h4>
	<div class="block_content">
		<form action="{PrestaBlogUrl}" method="post" id="prestablog_bloc_search">
			<input id="prestablog_search" type="text" value="{$prestablog_search_query|escape:'htmlall':'UTF-8'}" placeholder="{l s='Search on blog' mod='prestablog'}" name="prestablog_search" autocomplete="off">
			<input class="button button_search" type="submit" value="" name="submit_search">
		</form>
	</div>
</div>
<!-- /Module Presta Blog -->
