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
{capture name=path}<a href="{PrestaBlogUrl}" >{l s='Blog' mod='prestablog'}</a>{if $SecteurName}&nbsp;>&nbsp;{$SecteurName}{/if}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

{if isset($prestablog_title_h1)}<h1>{$prestablog_title_h1|escape:'htmlall':'UTF-8'}</h1>{/if}
<h2><span>{$NbNews|intval} {if $NbNews <> 1}{l s='articles' mod='prestablog'}{else}{l s='article' mod='prestablog'}{/if}</span></h2>

{if sizeof($news)}
	{include file="$prestablog_pagination"}
	<ul id="blog_list">
	{foreach from=$news item=news_item name=NewsName}
		<li>
			{if isset($news_item.image_presente)}
			<div class="block_gauche">
				{if isset($news_item.link_for_unique)}<a href="{PrestaBlogUrl id=$news_item.id_prestablog_news seo=$news_item.link_rewrite titre=$news_item.title}" class="product_img_link" title="{$news_item.title|escape:'htmlall':'UTF-8'}">{/if}
					<img src="{$prestablog_theme_upimg|escape:'html':'UTF-8'}thumb_{$news_item.id_prestablog_news|intval}.jpg?{$md5pic|escape:'htmlall':'UTF-8'}" alt="{$news_item.title|escape:'htmlall':'UTF-8'}" />
				{if isset($news_item.link_for_unique)}</a>{/if}
			</div>
			{/if}
			<div class="block_droite">
				<h3>
					{if isset($news_item.link_for_unique)}<a href="{PrestaBlogUrl id=$news_item.id_prestablog_news seo=$news_item.link_rewrite titre=$news_item.title}" title="{$news_item.title|escape:'htmlall':'UTF-8'}">{/if}{$news_item.title|escape:'htmlall':'UTF-8'}{if isset($news_item.link_for_unique)}</a>{/if}
				<br /><span class="date_blog-cat">{l s='Published :' mod='prestablog'}
						{dateFormat date=$news_item.date full=1}
						{if $prestablog_config.prestablog_comment_actif==1}
							{if $news_item.count_comments>0}| {$news_item.count_comments|intval} {if $news_item.count_comments>1}{l s='comments' mod='prestablog'}{else}{l s='comment' mod='prestablog'}{/if} {/if}
						{/if}
						{if $prestablog_config.prestablog_commentfb_actif==1}
							{if $news_item.count_commentsfb>0}| {$news_item.count_commentsfb|intval} {if $news_item.count_commentsfb>1}{l s='comments' mod='prestablog'}{else}{l s='comment' mod='prestablog'}{/if} {/if}
						{/if}
						{if sizeof($news_item.categories)} | {l s='Categories :' mod='prestablog'}
							{foreach from=$news_item.categories item=categorie key=key name=current}
								<a href="{PrestaBlogUrl c=$key titre=$categorie.link_rewrite}" class="categorie_blog">{$categorie.title|escape:'htmlall':'UTF-8'}</a>
								{if !$smarty.foreach.current.last},{/if}
							{/foreach}
						{/if}</span>
				</h3>
				<p class="blog_desc">
					{if $news_item.paragraph_crop!=''}
						{$news_item.paragraph_crop|escape:'htmlall':'UTF-8'}
					{/if}
				</p>
				{if isset($news_item.link_for_unique)}
					<p>
						<a href="{PrestaBlogUrl id=$news_item.id_prestablog_news seo=$news_item.link_rewrite titre=$news_item.title}" class="blog_link">{l s='Read more' mod='prestablog'}</a>
					</p>
				{/if}
			</div>
		</li>
	{/foreach}
	</ul>
	{include file="$prestablog_pagination"}
{else}
	<p class="warning">{l s='Empty' mod='prestablog'}</p>
{/if}
<!-- /Module Presta Blog -->
