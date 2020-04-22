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
	<div id="idTabBlog">
		{if $listeNewsLinked}
			<h3>{l s='Related articles on blog' mod='prestablog'}</h3>
			<ul class="related_blog_product">
			{foreach from=$listeNewsLinked item=Item name=myLoop}
					<li>
						<a href="{$Item.url|escape:'html':'UTF-8'}">
							{if isset($Item.image_presente)}<img src="{$prestablog_theme_upimg|escape:'html':'UTF-8'}adminth_{$Item.id|intval}.jpg?{$md5pic|escape:'htmlall':'UTF-8'}" alt="{$Item.title|escape:'htmlall':'UTF-8'}" class="lastlisteimg" />{/if}
							<strong>{$Item.title|escape:'htmlall':'UTF-8'}</strong>
						</a>
					</li>
				{if !$smarty.foreach.myLoop.last}{/if}
			{/foreach}
			</ul>
		{else}
			<p>{l s='No related articles on blog' mod='prestablog'}</p>
		{/if}
	</div>
<!-- /Module Presta Blog -->
