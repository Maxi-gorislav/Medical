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
<section class="footer-block col-xs-12 col-sm-2">
	<h4>{l s='Last blog articles' mod='prestablog'}</h4>
	<ul class="toggle-footer">
		{if $ListeBlocLastNews}
			{foreach from=$ListeBlocLastNews item=Item name=myLoop}
				<li>{$Item.date|escape:'htmlall':'UTF-8'}<br/>
					{if isset($Item.link_for_unique)}<a href="{PrestaBlogUrl id=$Item.id_prestablog_news seo=$Item.link_rewrite titre=$Item.title}">{/if}
						<strong>{$Item.title|escape:'htmlall':'UTF-8'}</strong>
						{if $prestablog_config.prestablog_footlastnews_intro}<br /><span>{$Item.paragraph_crop|escape:'htmlall':'UTF-8'}</span>{/if}
					{if isset($Item.link_for_unique)}</a>{/if}
				</li>
				{if !$smarty.foreach.myLoop.last}{/if}
			{/foreach}
		{else}
			<li>{l s='No news' mod='prestablog'}</li>
		{/if}
		{if $prestablog_config.prestablog_footlastnews_showall}
			<li>
				<a href="{PrestaBlogUrl}" class="button_large">{l s='See all' mod='prestablog'}</a>
			</li>
		{/if}
	</ul>
</section>
<!-- /Module Presta Blog -->
