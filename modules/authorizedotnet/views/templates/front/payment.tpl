<div class="row">
	<div class="col-xs-12 col-md-6">
		{if $adn_payment_page == 0}
			<p class="payment_module" id="adn_container">
				<a href="{if $active}{$this_validation_link}{else}javascript:alert('{l s='The Merchant has not configures this payment method yet, Order will not be valid' mod='authorizedotnet'}');location.href='{$this_path_ssl}validation.php'{/if}" title="{l s='Pay with' mod='authorizedotnet'} {$adn_cards}">
					<img src="{$this_path}img/credit-card-icons.png" width="275" alt="{$adn_cards}" /><br>
					{l s='Pay with' mod='authorizedotnet'} {$adn_cards}
					<br style="clear:both;" />
				</a>
			</p>
		{else}
			<p class="payment_module" id="adn_container">
				<iframe src='{$this_path}{$adn_filename}.php?content_only=1' seamless border="0" style = "border:0; overflow-x: hidden;"
					class="pc-iframe-dpn{if $adn_get_address} get_address{/if}{if $adn_get_cvm} adn_get_cvm{/if} " width="100%" name="pc-iframe-dpn" id="pc-iframe-dpn"></iframe>
			</p>
		{/if}
    </div>
</div>