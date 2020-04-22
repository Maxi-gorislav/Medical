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

<!-- Module Presta Blog START PAGE -->

{capture name=path}<a href="{PrestaBlogUrl}" >{l s='Articles' mod='prestablog'}</a>{if $SecteurName}&nbsp;>&nbsp;{PrestaBlogContent return=$SecteurName}{/if}{/capture}

{if isset($tpl_filtre_cat) && $tpl_filtre_cat}{PrestaBlogContent return=$tpl_filtre_cat}{/if}
{if isset($tpl_menu_cat) && $tpl_menu_cat}{PrestaBlogContent return=$tpl_menu_cat}{/if}

{if isset($tpl_unique) && $tpl_unique}{PrestaBlogContent return=$tpl_unique}{/if}
{if isset($tpl_comment) && $tpl_comment}{PrestaBlogContent return=$tpl_comment}{/if}
{if isset($tpl_comment_fb) && $tpl_comment_fb}{PrestaBlogContent return=$tpl_comment_fb}{/if}

{if isset($tpl_slide) && $tpl_slide}{PrestaBlogContent return=$tpl_slide}{/if}
{if isset($tpl_cat) && $tpl_cat}{PrestaBlogContent return=$tpl_cat}{/if}
{if isset($tpl_all) && $tpl_all}{PrestaBlogContent return=$tpl_all}{/if}

<!-- /Module Presta Blog END PAGE -->
