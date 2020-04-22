{*
* 2007-2014 PrestaShop
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2015 PrestaShop SA
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of PrestaShop SA
*}
<item>
<!-- The following attributes are always required -->
<g:id>{$id_product}</g:id>
<g:title><![CDATA[{$title}]]></g:title>
<g:description><![CDATA[{$description}]]></g:description>
<g:link><![CDATA[{$product_link}]]></g:link>
<g:image_link><![CDATA[{$image_link}]]></g:image_link>
<g:condition>{$condition}</g:condition>
<g:availability>{$availability}</g:availability>
<g:price>{$price}</g:price>
{foreach from=$shipping item=carrier}
<g:shipping>
   <g:country>{$country_iso}</g:country>
   <g:service>{$carrier.name}</g:service>
   <g:price>{$carrier.price}</g:price>
</g:shipping>
{/foreach}
<g:google_product_category><![CDATA[{$google_category}]]></g:google_product_category>
{if !empty($specific_price)}
<g:sale_price>{$specific_price}</g:sale_price>
{/if}
{if !empty($product.avabilitydate)}
<g:availability_date>{$product.avabilitydate}</g:availability_date>
{/if}
{if !empty($product.imageslinks)}
<g:additional_image_link><![CDATA[{$product.imageslinks}]]></g:additional_image_link>
{/if}
{if !empty($product_type)}
<g:product_type><![CDATA[{$product_type}]]></g:product_type>
{/if}
{if !empty($brand)}
<g:brand><![CDATA[{$brand}]]></g:brand>
{/if}
{if !empty($product.effective)}
<g:sale_price_effective_date>{$product.effective}</g:sale_price_effective_date>
{/if}
{if !empty($gtin)}
<g:gtin>{$gtin}</g:gtin>
{/if}
{if !empty($mpn)}
<g:mpn>{$mpn}</g:mpn>
{/if}
{if empty($gtin) && empty($mpn)}
<g:identifier_exists>FALSE</g:identifier_exists>
{else}
<g:identifier_exists>TRUE</g:identifier_exists>
{/if}
{if !empty($gender)}
<g:gender><![CDATA[{$gender}]]></g:gender>
{/if}
{if !empty($agegroup)}
<g:age_group><![CDATA[{$agegroup}]]></g:age_group>
{/if}
{if !empty($color)}
<g:color><![CDATA[{$color}]]></g:color>
{/if}
{if !empty($size)}
<g:size><![CDATA[{$size}]]></g:size>
{/if}
{if !empty($product.sizetype)}
<g:size_type>{$product.sizetype}</g:size_type>
{/if}
{if !empty($product.sizesystem)}
<g:size_system>{{$product.sizesystem}}</g:size_syste>
{/if}
{if !empty($groupid)}
<g:item_group_id>{$groupid}</g:item_group_id>
{/if}
{if !empty($material)}
<g:material><![CDATA[{$material}]]></g:material>
{/if}
{if !empty($pattern)}
<g:pattern><![CDATA[{$pattern}]]></g:pattern>
{/if}
{if !empty($shipping_weight)}
<g:shipping_weight>{$shipping_weight}</g:shipping_weight>
{/if}
{if !empty($product.shippinglabel)}
<g:shipping_label>{$product.shippinglabel}</g:shipping_label>
{/if}
{if !empty($product.multipack)}
<g:multipack>{$product.multipack}</g:multipack>
{/if}
{if !empty($product.isbundle)}
<g:is_bundle>{$product.isbundle}</g:is_bundle>
{/if}
{if !empty($adult_warning)}
<g:adult>{$adult_warning}</g:adult>
{/if}
{if !empty($product.adwords)}
<g:adwords_redirect>{$product.adowrds}</g:adwords_redirect>
{/if}
{if !empty($product.excludeddestination)}
<g:excluded_destination>{$product.excludeddestination}</g:excluded_destination>
{/if}
{if !empty($product.unitpricingmeasure)}
<g:unit_pricing_measure>{$product.unitpricingmeasure}</g:unit_pricing_measure>
{/if}
{if !empty($product.basemeasure)}
<g:unit_pricing_base_measure>{$product.basemeasure}</g:unit_pricing_base_measure>
{/if}
{if !empty($product.energy)}
<g:energy_efficiency_class>{$product.energy}</g:energy_efficiency_class>
{/if}
</item>
