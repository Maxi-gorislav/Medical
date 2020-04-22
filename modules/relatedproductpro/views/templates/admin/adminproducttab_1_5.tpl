{**
* Prestashop Addons | Module by: <App1Pro>
*
* @author    Chuyen Nguyen [App1Pro].
* @copyright Chuyenim@gmail.com
* @license   http://app1pro.com/license.txt
*}

{if isset($id_product)}
<div id="product-related" class="panel product-tab">
	<input type="hidden" name="submitted_tabs[]" value="Related Product Pro" />
	<input type="hidden" name="related_product_pro" value="1" />
	<input type="hidden" name="id_shop" value="{$id_shop|escape:'htmlall':'UTF-8'}" />
	<input type="hidden" name="related_delete" value="0" />
	<div class="panel-heading tab" >
		<i class="icon-link"></i> {l s='Related Products' mod='relatedproductpro'}
		<span class="link_setting" ><a href="{$link_to_setting|escape:'htmlall':'UTF-8'}">{l s='Settings' mod='relatedproductpro'}</a></span>
	</div>
	<div class="row">
		<div class="form-group">
			<label class="control-label col-lg-3" for="product_autocomplete_input">
				<span class="" >
					{l s='Search a product' mod='relatedproductpro'}
				</span>
			</label>
			<div class="col-lg-6">
					<div class="input-group">
						<input type="text" id="product_autocomplete_input" name="product_autocomplete_input" />
						<span class="input-group-addon"><i class="icon-search"></i></span>
					</div>
					<div class="ajax_result" style="display:none"></div>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-lg-3"></label>
			<div class="col-lg-6">
				<div id="ajax_add_product"></div>
			</div>
		</div>
	</div>

	<table class="table" cellpadding="0" cellspacing="0" style="width: 100%; margin-bottom:10px;">
		<thead>
			<tr class="nodrag nodrop" style="height: 40px">
				<th class="center">
				</th>
				<th class="left">
					<span class="title_box">
						{l s='Product ID' mod='relatedproductpro'}
					</span>
				</th>
				<th class="left">
					<span class="title_box">
						{l s='Product Name' mod='relatedproductpro'}
					</span>
				</th>
				<th class="center">{l s='Actions' mod='relatedproductpro'}</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$relatedproductpro item=products}
				<tr class="alt_row row_hover">
					<td class="center">
					</td>
					<td class="left">
						{$products.id_related|escape:'htmlall':'UTF-8'}
					</td>
					<td class="left name">
						{$products.name|escape:'html':'UTF-8'}
					</td>
					<td class="center">
						<a href="#" class="delete_product_related pull-right btn btn-default" rel="{$products.id_related|escape:'htmlall':'UTF-8'}">
							<img src="../img/admin/disabled.gif">
						</a>
					</td>
				</tr>
			{foreachelse}
				<tr>
					<td colspan="6" class="center">
						<b>{l s='No products' mod='relatedproductpro'}</b>
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
</div>
{/if}