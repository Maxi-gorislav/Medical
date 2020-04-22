{*
* Prestashop Addons | Module by: <App1Pro>
*
* @author    Chuyen Nguyen [App1Pro].
* @copyright Chuyenim@gmail.com
* @license   http://app1pro.com/license.txt
*}

{if (count($products))}
<!-- Related Products -->
<section class="page-product-box">
	<h3 class="page-product-heading">{l s='Related Products' mod='relatedproductpro'}</h3>

    <div id="related-product-pro" class="block_content related-product-pro">
		<ul class="product_list grid clearfix">
			{foreach from=$products item=product name=products}
				<li class="item product-box ajax_block_product">
					<div class="product-container" itemscope itemtype="http://schema.org/Product">
						<div class="product-image-container">
							<a class="product_img_link" href="{$product.link|escape:'htmlall':'UTF-8'}" title="{$product.legend|escape:'htmlall':'UTF-8'}" class="product-image product_image">
								<img class="replace-2x img-responsive" src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'htmlall':'UTF-8'}" alt="{$product.name|escape:'htmlall':'UTF-8'}" {if isset($homeSize)} width="|escape:'htmlall':'UTF-8'{$homeSize.width|escape:'htmlall':'UTF-8'}" height="{$homeSize.height|escape:'htmlall':'UTF-8'}"{/if} itemprop="image"/>
							</a>
						{if (!$PS_CATALOG_MODE && ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
							<div class="content_price" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
								{if isset($product.show_price) && $product.show_price && !isset($restricted_country_mode)}
									<span itemprop="price" class="price product-price">
										{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
									</span>
									<meta itemprop="priceCurrency" content="{$currency->iso_code|escape:'htmlall':'UTF-8'}" />
									{if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
										{hook h="displayProductPriceBlock" product=$product type="old_price"}
										<span class="old-price product-price">
											{displayWtPrice p=$product.price_without_reduction}
										</span>
										{if $product.specific_prices.reduction_type == 'percentage'}
											<span class="price-percent-reduction">-{$product.specific_prices.reduction * 100|escape:'htmlall':'UTF-8'}%</span>
										{/if}
									{/if}
									{if $PS_STOCK_MANAGEMENT && isset($product.available_for_order) && $product.available_for_order && !isset($restricted_country_mode)}
										{if ($product.allow_oosp || $product.quantity > 0)}
												<link itemprop="availability" href="http://schema.org/InStock" />{if $product.quantity <= 0}{if $product.allow_oosp}{if isset($product.available_later) && $product.available_later}{$product.available_later|escape:'htmlall':'UTF-8'}{else}{l s='In Stock' mod='relatedproductpro'}{/if}{else}{l s='Out of stock' mod='relatedproductpro'}{/if}{else}{if isset($product.available_now) && $product.available_now}{$product.available_now|escape:'htmlall':'UTF-8'}{else}{l s='In Stock' mod='relatedproductpro'}{/if}{/if}
										{elseif (isset($product.quantity_all_versions) && $product.quantity_all_versions > 0)}
												<link itemprop="availability" href="http://schema.org/LimitedAvailability" />{l s='Product available with different options' mod='relatedproductpro'}

										{else}
												<link itemprop="availability" href="http://schema.org/OutOfStock" />{l s='Out of stock' mod='relatedproductpro'}
										{/if}
									{/if}
									{hook h="displayProductPriceBlock" product=$product type="price"}
									{hook h="displayProductPriceBlock" product=$product type="unit_price"}
								{/if}
							</div>
						{/if}
						</div>
						<h5 itemprop="name">
							{if isset($product.pack_quantity) && $product.pack_quantity}{$product.pack_quantity|intval|cat:' x '}{/if}
							<a class="product-name" href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}" itemprop="url" >
								{$product.name|truncate:45:'...'|escape:'html':'UTF-8'}
							</a>
						</h5>
						{hook h='displayProductListReviews' product=$product}
						<p class="product-desc" itemprop="description">
							{$product.description_short|strip_tags:'UTF-8'|escape:'htmlall':'UTF-8'|truncate:360:'...'}
						</p>
						{if (!$PS_CATALOG_MODE AND ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
						<div class="content_price">
							{if isset($product.show_price) && $product.show_price && !isset($restricted_country_mode)}
								<span class="price product-price">
									{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
								</span>
								{if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
									{hook h="displayProductPriceBlock" product=$product type="old_price"}
									<span class="old-price product-price">
										{displayWtPrice p=$product.price_without_reduction}
									</span>
									{hook h="displayProductPriceBlock" id_product=$product.id_product type="old_price"}
									{if $product.specific_prices.reduction_type == 'percentage'}
										<span class="price-percent-reduction">-{$product.specific_prices.reduction * 100|escape:'htmlall':'UTF-8'}%</span>
									{/if}
								{/if}
								{hook h="displayProductPriceBlock" product=$product type="price"}
								{hook h="displayProductPriceBlock" product=$product type="unit_price"}
							{/if}
						</div>
						{/if}
						<div class="clearfix" style="margin-top:5px">
							<div class="no-print">
								<a class="exclusive button ajax_add_to_cart_button" href="{$link->getPageLink('cart', true, NULL, "qty=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}&amp;add")|escape:'htmlall':'UTF-8'}" data-id-product="{$product.id_product|escape:'htmlall':'UTF-8'}" title="{l s='Add to cart' mod='relatedproductpro'}">
									<span>{l s='Add to cart' mod='relatedproductpro'}</span>
								</a>
							</div>
						</div>
					</div>
				</li>
			{/foreach}
			<div class="clearfix"></div>
		</ul>
	</div>

</section>

<script type="text/javascript">
	var slide_pager = {if (isset($slide_pager) && $slide_pager)}true{else}false{/if},
	slide_infiniteLoop = {if (isset($slide_infiniteLoop) && $slide_infiniteLoop)}true{else}false{/if},
	slide_auto = {if (isset($slide_auto) && $slide_auto)}true{else}false{/if},
	slide_hideControlOnEnd = {if (isset($slide_hideControlOnEnd) && $slide_hideControlOnEnd)}true{else}false{/if},
	slide_slideWidth = {if (isset($slide_slideWidth) && $slide_slideWidth)}{$slide_slideWidth|escape:'htmlall':'UTF-8'}{else}{$homeSize.width|escape:'htmlall':'UTF-8'} + 20{/if},
	slide_slideMargin = {if (isset($slide_slideMargin) && $slide_slideMargin != null)}{$slide_slideMargin|escape:'htmlall':'UTF-8'}{else}30{/if};
{literal}
	$('ul.product_list').bxSlider({
		pager: slide_pager,
		infiniteLoop: slide_infiniteLoop,
		hideControlOnEnd: slide_hideControlOnEnd,
		autoHover: true,
		auto: slide_auto,
		minSlides: 2,
		maxSlides: 4,
		slideWidth: slide_slideWidth,
		slideMargin: slide_slideMargin
	});
{/literal}
</script>
<!--end Related Products -->
{/if}