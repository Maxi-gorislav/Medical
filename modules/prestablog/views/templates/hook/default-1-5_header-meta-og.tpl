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
{foreach from=$prestablog_fb_admins item=fbmoderator}
<meta property="fb:admins"			content="{$fbmoderator|escape:'html':'UTF-8'}" />
{/foreach}
<meta property="og:url"				content="{$prestablog_news_meta_url|escape:'html':'UTF-8'}" />
<meta property="og:image"			content="{$prestablog_news_meta_img|escape:'html':'UTF-8'}" />
<meta property="og:title"			content="{$prestablog_news_meta->title|escape:'htmlall':'UTF-8'}" />
<meta property="og:description"	content="{$prestablog_news_meta->paragraph|escape:'htmlall':'UTF-8'}" />
<!-- Module Presta Blog -->

