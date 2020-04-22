{if !$adn_payment_page}
	{capture name=path}{l s='Payment' mod='authorizedotnet'}{/capture}

	{if $adn_psv < 1.6}
	{include file="$tpl_dir./breadcrumb.tpl"}
	{/if}

	<h1 class="page-heading">{l s='Order summation' mod='authorizedotnet'}</h1>

	{assign var='current_step' value='payment'}
	{include file="$tpl_dir./order-steps.tpl"}

{/if}
<style>
body
{ldelim}
	overflow-x: hidden;
{rdelim}
</style>

<div class="row">
	<div class="col-xs-12 col-md-6">
		<div id="adn_payment" class="payment_module pc-eidition">

			<form name="adn_form" id="adn_form" method="post" class="std" action="{$post_url}">
				<input type="hidden" name="confirm" value="1" />
				<h2 class="title_accept">
					{l s='Billing Information - We Accept:' mod='authorizedotnet'}
				</h2>
				<div class="accept_cards">
						{if $adn_visa}
							<img src="{$this_path_ssl}img/visa_big.gif" alt="{l s='Visa' mod='authorizedotnet'}" />
						{/if}
						{if $adn_mc}
							<img src="{$this_path_ssl}img/mc_big.gif" alt="{l s='Mastercard' mod='authorizedotnet'}" />
						{/if}
						{if $adn_amex}
							<img src="{$this_path_ssl}img/amex_big.gif" alt="{l s='American Express' mod='authorizedotnet'}" />
						{/if}
						{if $adn_discover}
							<img src="{$this_path_ssl}img/discover_big.gif" alt="{l s='Discover' mod='authorizedotnet'}" />
						{/if}
						{if $adn_jcb}
							<img src="{$this_path_ssl}img/jcb.gif" alt="{l s='JCB' mod='authorizedotnet'}" />
						{/if}
						{if $adn_diners}
							<img src="{$this_path_ssl}img/diners.gif" alt="{l s='Diners' mod='authorizedotnet'}" />
						{/if}
				</div>

				<div class="form_row half-row f-l">
					<label for="adn_cc_fname">{l s='Firstname' mod='authorizedotnet'}: </label>	
					<input type="text" name="adn_cc_fname" id="adn_cc_fname" value="{$adn_cc_fname}" class="form-control"/> 
				</div>

				<div class="form_row half-row f-r">
					<label>{l s='Lastname' mod='authorizedotnet'}: </label>	
					<input type="text" name="adn_cc_lname" id="adn_cc_lname" value="{$adn_cc_lname}" class="form-control"/> 
				</div>

				{if $adn_get_address == "1"}

					<div class="form_row">
						<label>{l s='Address' mod='authorizedotnet'}: </label>	
						<input type="text" name="adn_cc_address" value="{$adn_cc_address}" class="form-control"/>
					</div>

					<div class="form_row half-row f-l">
						<label>{l s='City' mod='authorizedotnet'}: </label>
						<input type="text" name="adn_cc_city" value="{$adn_cc_city}" class="form-control"/>
					</div>

					<div class="form_row half-row f-r">
						<label>{l s='Zipcode' mod='authorizedotnet'}: </label>	
						<input type="text" name="adn_cc_zip" size="5" value="{$adn_cc_zip}" class="form-control"/>
					</div>

					<div class="form_row half-row f-l">
						<label>{l s='Country' mod='authorizedotnet'}: </label>
						<select name="adn_id_country" id="adn_id_country" class="form-control">{$countries_list}</select>
					</div>

					<div class="form_row half-row f-r">
						<div class="adn_id_state">
							<label>{l s='State' mod='authorizedotnet'}:  </label>
							<select name="adn_id_state" id="adn_id_state" class="form-control">
								<option value="">-</option>
							</select>
						</div>
					</div>
					<div class="clear"> </div>

				{/if}
				<div class="form_row">
					<label>{l s='Card Number' mod='authorizedotnet'}: </label>
					<input type="text" name="adn_cc_number" value="{if !empty($adn_cc_number)}{$adn_cc_number}{/if}" class="form-control"/>
				</div>

				<div class="form_row half-row f-l">
					<label>{l s='Expiration' mod='authorizedotnet'}: </label>
					<select name="adn_cc_Month" id="adn_exp_month" class="form-control">
						{foreach from=$adn_months  key=k item=v}
							<option value="{$k}" {if !empty($cardInfo.exp_date) && $cardInfo.exp_date.1 ==$k}selected="selected"{/if}>{$v}</option>
						{/foreach}
					</select>
				</div>

				<div class="form_row half-row f-r">
					<label>&nbsp;</label>
					<select name="adn_cc_Year" id="adn_exp_year" class="form-control">
						{foreach from=$adn_years  key=k item=v}
							<option value="{$k}" {if !empty($cardInfo.exp_date) && $cardInfo.exp_date.0 ==$k}selected="selected"{/if}>{$v}</option>
						{/foreach}
					</select>
				</div>

				{if $adn_get_cvm == "1"}
				<div class="form_row">
					<label>{l s='CVN code' mod='authorizedotnet'}:  </label>
					<input type="text" name="adn_cc_cvm" size="4" value="{if !empty($adn_cc_cvm)}{$adn_cc_cvm}{/if}" class="form-control half-row" />
					<span class="form-caption">{l s='3-4 digit number from the back of your card.' mod='authorizedotnet'}</span>
				</div>
				{/if}


				{if !$adn_payment_page}
					<div class="pcpm-total">
						<span style="float:left">{l s='The total amount of your order is' mod='authorizedotnet'}&nbsp;</span>
						<span id="amount_{$currencies.0.id_currency}" class="price">{convertPrice price=$adn_total}</span>
					</div>
					<div class="pcpm-confirm">
						{l s='Please confirm your order by clicking \'I confirm my order\'' mod='authorizedotnet'}.
					</div>
				{/if}
			
				<div id="adn_ajax_container" class="pcpm-ajax-container {if !empty($adn_cc_err)}error{/if}">
					{if !empty($adn_cc_err)} {$adn_cc_err} {/if} 
				</div>
				<div class="clear"></div>
				
				<p class="cart_navigation">
					{if !$adn_payment_page}
						{if $adn_psv < 1.5}
							<a href="{$base_dir_ssl}order.php?step=3" class="button_large">{l s='Other payment methods' mod='authorizedotnet'}</a>
						{else}
		
							{if $adn_psv >= 1.6}
								<a class="button-exclusive btn btn-default" href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" style="float:left;">
									<i class="icon-chevron-left"></i>
									{l s='Other payment methods' mod='authorizedotnet'}
								</a>			
							{else}
								<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button_large">{l s='Other payment methods' mod='authorizedotnet'}</a>
							{/if}
						{/if}
					{/if}
	
				
					{if $adn_psv >= 1.6}
						<button class="button btn btn-default button-medium" type="button" id="adn_submit">
						<span>
								{l s='I confirm my order' mod='authorizedotnet'}
						<i class="icon-chevron-right right"></i>
						</span>
						</button>			
					{else}
						<input type="button" id="adn_submit" value="{l s='I confirm my order' mod='authorizedotnet'}" class="exclusive_large" />
					{/if}			
				</p>			
				<div class="clear"></div>	

				<input type="hidden" name="x_delim_char" value="|" />
				<input type="hidden" name="x_duplicate_window" value="3" />
				<input type="hidden" id="x_card_num" name="x_card_num" />
				<input type="hidden" id="x_exp_date" name="x_exp_date" />
				<input type="hidden" id="x_card_code" name="x_card_code" />
				<input type="hidden" id="x_first_name" name="x_first_name" />
				<input type="hidden" id="x_last_name" name="x_last_name" />
				<input type="hidden" id="x_address" name="x_address" />
				<input type="hidden" id="x_city" name="x_city" />
				<input type="hidden" id="x_state" name="x_state" />
				<input type="hidden" id="x_zip" name="x_zip" />
				<input type="hidden" id="x_country" name="x_country" />
				<input type="hidden" name="x_company" value="{$adn_cc_company}" />
				<input type="hidden" name="x_phone" value="{$adn_cc_phone}" />
				<input type="hidden" name="x_description" value="{$x_description}" />
				<input type="hidden" name="x_line_item" value="{if isset($x_line_item)}{$x_line_item}{/if}" />
				<input type="hidden" name="x_customer_ip" value="{$x_customer_ip}" />
				<input type="hidden" name="x_cust_id" value="{$x_cust_id}" />
				<input type="hidden" name="x_ship_to_first_name" value="{$x_ship_to_first_name}" />
				<input type="hidden" name="x_ship_to_last_name" value="{$x_ship_to_last_name}" />
				<input type="hidden" name="x_ship_to_company" value="{$x_ship_to_company}" />
				<input type="hidden" name="x_ship_to_address" value="{$x_ship_to_address}" />
				<input type="hidden" name="x_ship_to_city" value="{$x_ship_to_city}" />
				<input type="hidden" name="x_ship_to_state" value="{$x_ship_to_state}" />
				<input type="hidden" name="x_ship_to_zip" value="{$x_ship_to_zip}" />
				<input type="hidden" name="x_ship_to_country" value="{$x_ship_to_country}" />
				<input type="hidden" name="x_freight" value="{$x_freight}" />
				<input type="hidden" name="x_type" value="{$x_type}" />
				<input type="hidden" name="x_tax" value="{$x_tax}" />
				<input type="hidden" name="id_cart" value="{$id_cart}" />
				<input type="hidden" name="x_invoice_num" value="{$x_invoice_num}" />
				<input type="hidden" name="x_amount" value="{$x_amount}" />
				<input type="hidden" name="x_fp_sequence" value="{$x_fp_sequence}" />
				<input type="hidden" name="x_fp_hash" value="{$x_fp_hash}" />
				<input type="hidden" name="x_fp_timestamp" value="{$x_fp_timestamp}" />
				<input type="hidden" name="x_login" value="{$x_login}" />
				<input type="hidden" name="x_relay_response" value="TRUE" />
				<input type="hidden" name="x_relay_url" value="{$x_relay_url}" />
				<input type="hidden" name="x_test_request" value="{$x_test_request}" />
				<input type="hidden" name="x_currency_code" value="{$x_currency_code}" />
				<input type="hidden" name="x_email_customer" value="{$x_email_customer}" />
				<input type="hidden" name="x_email" value="{$x_email}" />
				
				
				
				</form>
			</div>
		</div>

		<script type="text/javascript">
			//<![CDATA[
			
			adn_idSelectedCountry = {if isset($address->id_state)}{$address->id_state|intval}{else}false{/if};
			adn_countries = new Array();
			adn_countriesNeedIDNumber = new Array();
			adn_countriesIdToName = new Array()
			{foreach from=$countries item='country'}
				adn_countriesIdToName[{$country.id_country|intval}] = '{$country.iso_code}';
				{if isset($country.states) && $country.contains_states}
					adn_countries[{$country.id_country|intval}] = new Array();
					{foreach from=$country.states item='state' name='states'}
						adn_countries[{$country.id_country|intval}].push({ldelim}'id' : '{$state.iso_code}', 'name' : '{$state.name|escape:'htmlall':'UTF-8'}'{rdelim});
					{/foreach}
				{/if}
			{/foreach}
			
			
			var paymentModuleAND = new paymentModulePCC(
				'adn',
				0,
				{
					'err_fname'   :"{l s='You must enter your' mod='authorizedotnet'} {l s='First Name' mod='authorizedotnet'}",
					'err_lname'   :"{l s='You must enter your' mod='authorizedotnet'} {l s='Last Name' mod='authorizedotnet'}",
					'err_address' :"{l s='You must enter your' mod='authorizedotnet'} {l s='Address' mod='authorizedotnet'}",
					'err_city'    :"{l s='You must enter your' mod='authorizedotnet'} {l s='City' mod='authorizedotnet'}",
					'err_zip'     :"{l s='You must enter your' mod='authorizedotnet'} {l s='Zipcode' mod='authorizedotnet'}",
					'err_number'  :"{l s='You must enter a valid' mod='authorizedotnet'} {l s='Card Number' mod='authorizedotnet'}",
					// 'err_email'   :"{l s='You must enter your' mod='authorizedotnet'} {l s='Email' mod='authorizedotnet'}",
					'err_card_num':"{l s='You must enter a valid' mod='authorizedotnet'} {l s='Card Number' mod='authorizedotnet'}",
					'err_cvm'     :"{l s='You must enter your' mod='authorizedotnet'} {l s='CVM code' mod='authorizedotnet'}" ,
					'trl_wait'    :"{l s='Please Wait ...' mod='authorizedotnet'}"
				},
				{if !empty($adn_filename)} 
					'{$this_path}{$adn_filename}.php'
				{else}
					''
				{/if}
			);

			paymentModuleAND.beforeSend = function() {
				var form = document.adn_form
				$('#x_card_num').val(form.adn_cc_number.value);
				$('#x_exp_date').val(form.adn_cc_Month.value+form.adn_cc_Year.value.substring(2));
				$('#x_card_code').val(form.adn_cc_cvm?form.adn_cc_cvm.value:'');
				$('#x_first_name').val(form.adn_cc_fname.value);
				$('#x_last_name').val(form.adn_cc_lname.value);
				$('#x_address').val(form.adn_cc_address?form.adn_cc_address.value:'');
				$('#x_city').val(form.adn_cc_city?form.adn_cc_city.value:'');
				$('#x_state').val(form.adn_id_state?form.adn_id_state.value:'');
				$('#x_zip').val(form.adn_cc_zip?form.adn_cc_zip.value:'');
				$('#x_country').val(form.adn_id_country?adn_countriesIdToName[form.adn_id_country.value]:'');
				//alert(adn_countriesIdToName[form.adn_id_country.value]);
			}
			
			$('#adn_submit').click(function(){
				paymentModuleAND.send(document.adn_form);
			});
			paymentModuleAND.initStates(paymentModuleAND);

			$('.adn_id_state option[value={$address->id_state|escape:'htmlall':'UTF-8'}]').attr('selected', 'selected');			

			//]]>
		</script>
</div>

