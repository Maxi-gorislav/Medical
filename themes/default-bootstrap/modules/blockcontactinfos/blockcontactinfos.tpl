{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA

*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<!-- MODULE Block contact infos -->
<section id="block_contact_infos" class="footer-block col-xs-12 col-sm-4">
	<div>
        <h4>{l s='Mobi Medical Supply' mod='blockcontactinfos'}</h4>
        <ul class="toggle-footer">
           
            {if $blockcontactinfos_phone != ''}
            	<li>
            		<i class="icon-phone"></i>{l s='Customer Service / Sales:' mod='blockcontactinfos'} 
            		<span>{$blockcontactinfos_phone|escape:'html':'UTF-8'}</span>
            	</li>
            {/if}
             {if $blockcontactinfos_company!= ''}
            	<li>
            		<i class="icon-file-text"></i>{l s='Fax:' mod='blockcontactinfos'} 
            		<span>{$blockcontactinfos_company|escape:'html':'UTF-8'}</span>
            	</li>
            {/if}
            {if $blockcontactinfos_email != ''}
            	<li>
            		<i class="icon-envelope-alt"></i>{l s='Email:' mod='blockcontactinfos'} 
            		<span>{mailto address=$blockcontactinfos_email|escape:'html':'UTF-8' encode="hex"}</span>
            	</li>
            {/if}
        </ul>
    </div>
	<!-- (c) 2005, 2017. Authorize.Net is a registered trademark of CyberSource Corporation --> <div class="AuthorizeNetSeal"> <script type="text/javascript" language="javascript">var ANS_customer_id="0c870eba-de7d-494c-b126-21bca3a99989";</script> <script type="text/javascript" language="javascript" src="//verify.authorize.net/anetseal/seal.js" ></script></div>

</section>
<!-- /MODULE Block contact infos -->
<section class="footer-block col-xs-12 col-sm-4" id="block_contact_infos">
	<div>
		<img src="/themes/default-bootstrap/img/new/img-pay-logos.jpg" alt="Way To Pay">
	</div>
</section>