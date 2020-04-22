<br />
{if ($ps_version < 1.6)}
<fieldset {if ($ps_version < 1.5)}  style="width: 400px;"  {/if}>
	<legend><img src="../modules/authorizedotnet/logo.gif"> {l s='Authorize.net Refund Transaction' mod='authorizedotnet'}</legend>
	<div id="refund_order_details" {if ($ps_version < 1.5)}  style="width: 400px;"  {/if}>
		
	</div>
</fieldset>
{else}
<div class="row">
	<div class="col-lg-7">
		<div class="panel">
			<h3>
				<img src="../modules/authorizedotnet/logo.gif"> {l s='Authorize.net Refund Transaction' mod='authorizedotnet'}
			</h3>
			<div id="refund_order_details">
				
			</div>
		</div>
	</div>
</div>
{/if}


{if ($isCanCapture)}
	{if ($ps_version < 1.6)}
	<br />
	<fieldset {if ($ps_version < 1.5)}  style="width: 400px;"  {/if}>
		<legend><img src="../modules/authorizedotnet/logo.gif"> {l s='Authorize.net Capture Transaction' mod='authorizedotnet'}</legend>
		<div id="capture_order_details" {if ($ps_version < 1.5)}  style="width: 400px;"  {/if}>
			
		</div>	
	</fieldset>
	{else}
		<div class="row">
			<div class="col-lg-7">
				<div class="panel">
					<h3>
						<img src="../modules/authorizedotnet/logo.gif"> {l s='Authorize.net Capture Transaction' mod='authorizedotnet'}
					</h3>
					<div id="capture_order_details">
						
					</div>
				</div>
			</div>
		</div>	
	{/if}
{/if}

<script type="text/javascript">
		var baseDir = '{$module_basedir}';
		function search_orders(type)
		{ldelim}
			// var type = 2;
			var orderId = {$order_id};

			if (type == 1)
			{ldelim}
				$.ajax({ldelim}
					type: "POST",
					url: baseDir + "authorizedotnet-ajax.php",
					async: true,
					cache: false,
					data: "id_shop={$id_shop}&orderId=" + orderId + "&adminOrder=1&id_lang={$cookie->id_lang}&id_employee={$cookie->id_employee}&type="+ type + "&secure_key={$_adn_secure_key}",
					success: function(html){ldelim} $("#capture_order_details").html(html); {rdelim},
					error: function() {ldelim} alert("ERROR:");  {rdelim}
				{rdelim});
			{rdelim}

			if (type == 2)
			{ldelim}
				$.ajax({ldelim}
					type: "POST",
					url: baseDir + "authorizedotnet-ajax.php",
					async: true,
					cache: false,
					data: "id_shop={$id_shop}&orderId=" + orderId + "&adminOrder=1&id_lang={$cookie->id_lang}&id_employee={$cookie->id_employee}&type="+ type + "&secure_key={$_adn_secure_key}",
					success: function(html){ldelim} $("#refund_order_details").html(html); {rdelim},
					error: function() {ldelim} alert("ERROR:"); {rdelim}
				{rdelim});
			{rdelim}
		{rdelim}
	
		$(document).ready(function() {ldelim}
				search_orders(2);
			{if ($isCanCapture)}
				search_orders(1);
			{/if}
		{rdelim});
</script>