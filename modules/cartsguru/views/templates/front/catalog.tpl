{*
 * Carts Guru
 *
 * @author    LINKT IT
 * @copyright Copyright (c) LINKT IT 2016
 * @license   Commercial license
 *}

<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:g="http://base.google.com/ns/1.0">
    <title>{$store_name|escape:'htmlall':'UTF-8'}</title>
    <link rel="self" href="{$url|escape:'htmlall':'UTF-8'}"/>
    {foreach from=$products item=product}
    <entry>
        <g:id>{$product.id|escape:'htmlall':'UTF-8'}</g:id>
        <g:availability>{$product.availability|escape:'htmlall':'UTF-8'}</g:availability>
        <g:description><![CDATA[{$product.description|escape:'htmlall':'UTF-8'}]]></g:description>
        <g:image_link>{$product.image_link|escape:'htmlall':'UTF-8'}</g:image_link>
        <g:link>{$product.link|escape:'htmlall':'UTF-8'}</g:link>
        <g:title><![CDATA[{$product.title|escape:'htmlall':'UTF-8'}]]></g:title>
        <g:price>{$product.price|escape:'htmlall':'UTF-8'}</g:price>
        <g:mpn>{$product.id|escape:'htmlall':'UTF-8'}</g:mpn>
        <g:condition>new</g:condition>
    </entry>
    {/foreach}
</feed>
