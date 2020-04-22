{**
* Prestashop Addons | Module by: <App1Pro>
*
* @author    Chuyen Nguyen [App1Pro].
* @copyright Chuyenim@gmail.com
* @license   http://app1pro.com/license.txt
*}

<!-- Related Products -->
<section class="page-product-box">
	<h3 class="page-product-heading">{l s='Related Products' mod='relatedproductpro'}</h3>

    <div id="related-product-pro" class="block_content related-product-pro">
		<ul class="products-block">
			{foreach from=$products item=product name=products}
				<li class="clearfix">
					<a class="products-block-image" href="{$product.link|escape:'htmlall':'UTF-8'}" title="{$product.legend|escape:'htmlall':'UTF-8'}"><img class="replace-2x img-responsive" src="{$link->getImageLink($product.link_rewrite, $product.id_image, $image_size)|escape:'htmlall':'UTF-8'}" alt="{$product.name|escape:'htmlall':'UTF-8'}" /></a>
					<div class="product-content product-container" itemscope itemtype="http://schema.org/Product">
						<h5>
							<a class="product-name" href="{$product.link|escape:'htmlall':'UTF-8'}" title="{$product.name|escape:'htmlall':'UTF-8'}">{$product.name|strip_tags|escape:'htmlall':'UTF-8'}</a>
						</h5>
                       	<p class="product-description">{$product.description_short|strip_tags:'UTF-8'|escape:'htmlall':'UTF-8'|truncate:95:'...'}</p>
						{if (!$PS_CATALOG_MODE AND ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
							{if isset($product.show_price) && $product.show_price && !isset($restricted_country_mode)}
								<div class="price-box">
									<span class="price">
									{if !$priceDisplay}
										{displayWtPrice p=$product.price}{else}{displayWtPrice p=$product.price_tax_exc}
									{/if}
									</span>
									{if $product.specific_prices}
										{assign var='specific_prices' value=$product.specific_prices}
										{if $specific_prices.reduction_type == 'percentage' && ($specific_prices.from == $specific_prices.to OR ($smarty.now|date_format:'%Y-%m-%d %H:%M:%S' <= $specific_prices.to && $smarty.now|date_format:'%Y-%m-%d %H:%M:%S' >= $specific_prices.from))}
										<span class="price-percent-reduction">-{$specific_prices.reduction*100|floatval}%</span>
										{/if}
										<span class="old-price">
											{if !$priceDisplay}
												{displayWtPrice p=$product.price_without_reduction}
                                            {else}
                                                {displayWtPrice p=$price_without_reduction_without_tax}
											{/if}
										</span>
									{/if}

								</div>
							{/if}
						{/if}
					</div>
				</li>
			{/foreach}
			<div class="clearfix"></div>
		</ul>
	</div>

</section>
<!--end Related Products -->