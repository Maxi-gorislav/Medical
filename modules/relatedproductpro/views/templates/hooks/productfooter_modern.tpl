{*
* Prestashop Addons | Module by: <App1Pro>
*
* @author    Chuyen Nguyen [App1Pro].
* @copyright Chuyenim@gmail.com
* @license   http://app1pro.com/license.txt
*}

<!-- Related Products -->
{if (count($products))}
<section class="page-product-box">
	<h3 class="page-product-heading">{l s='Related Products' mod='relatedproductpro'}</h3>
	<div class="mainList">
		{include file="$tpl_dir./product-list.tpl" products=$products}
	</div>
</section>
{/if}
<!--end Related Products -->