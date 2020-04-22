<?php

class AuthorizedotnetValidationModuleFrontController extends ModuleFrontController
{

	public $ssl = true;

	public function setMedia()
	{
		parent::setMedia();
		$this->addJS(__PS_BASE_URI__ .'modules/authorizedotnet/js/authorizedotnet.js');
		$this->addCSS(__PS_BASE_URI__ .'modules/authorizedotnet/css/authorizedotnet.css');
	}

    public function initContent()
    {
    	if (Configuration::get('ADN_SHOW_LEFT') == 0) {
        	$this->display_column_left = false;
		}
        parent::initContent();
    }

    public function init()
    {
    	if (Configuration::get('ADN_SHOW_LEFT') == 0) {
        	$this->display_column_left = false;
		}
        parent::init();
    }

	public function postProcess()
	{
		
		$cart = $this->context->cart;
		$psv = floatval(substr(_PS_VERSION_,0,3));

		$confirm = Tools::getValue('confirm');

		$adn = new AuthorizeDotNet();

		/* Validate order */
		$time = time();
		$adn_cc_err = "";
		if ($confirm)
		{
			$response_array = explode("|",$adn->doPayment());
			if ($response_array[0] == "1")
			{
				$customer = new Customer(intval($cart->id_customer));
				$total = $response_array[9];

				if ($psv < 1.4)
					$adn->validateOrder(intval($cart->id), $adn->_adn_type == 'AUTH_ONLY'?$adn->_adn_auth_status:_PS_OS_PAYMENT_, $total, $adn->displayName);
				else
					$adn->validateOrder(intval($cart->id), $adn->_adn_type == 'AUTH_ONLY'?$adn->_adn_auth_status:_PS_OS_PAYMENT_, $total, $adn->displayName, null, array(), null, false, $customer->secure_key);
				$order = new Order(intval($adn->currentOrder));
				$message = new Message();
				$message->message = ($adn->_adn_type == 'AUTH_ONLY'?$adn->l('Authorization Only - '):'').$adn->l('Transaction ID: ').$response_array[6].$adn->l(' - Last 4 digits of the card: ').substr(Tools::getValue('adn_cc_number'),-4).$adn->l(' - AVS Response: ').$response_array[5].$adn->l(' - Card Code Response: ').$response_array[38];
				$message->id_customer = $cart->id_customer;
				$message->id_order = $order->id;
				$message->private = 1;
				$message->id_employee = 0;
				$message->id_cart = $cart->id;
				$message->add();
				
				Db::getInstance()->Execute("INSERT INTO `"._DB_PREFIX_."authorizedotnet_refunds` VALUES('$order->id','".$response_array[6]."','".substr(Tools::getValue('adn_cc_number'),-4)."','".$response_array[4]."','".($adn->_adn_type == 'AUTH_ONLY'?'0':'1')."')");

				if (!$adn->_adn_payment_page) {
					Tools::redirectLink($adn->getRedirectBaseUrl() . 'key='.$customer->secure_key.'&id_cart='.intval($cart->id).'&id_module='.intval($adn->id).'&id_order='.intval($adn->currentOrder));
				} else {
					/**
					 * Redirect to ordr-confirmation - ajax verision 
					 * $adn->_adn_payment_page == 1
					 */
					@ob_end_clean();			
					echo 'url:' . $adn->getRedirectBaseUrl() . 'key='
					.$customer->secure_key.'&id_cart='.intval($cart->id).'&id_module='.intval($adn->id).'&id_order='.intval($adn->currentOrder);
					exit();
				}
			}

			$adn_cc_err = $adn->l('There was an error processing your payment')."<br />Details: ".$response_array[3];

			if ($adn->_adn_ft == 1 && !empty($adn->_adn_ft_email)) {
				$cartInfo = array();

				if ($adn->_adn_get_address) {
					$cartInfo = array(
						'firstname' => Tools::getValue('adn_cc_fname'),
						'lastname'  => Tools::getValue('adn_cc_lname'),
						'address'   => Tools::getValue('adn_cc_address'),
						'city'      => Tools::getValue('adn_cc_city'),
						'state'     => Tools::getValue('adn_id_state'),
						'country'   => Tools::getValue('adn_id_country'),
						'zip'       => Tools::getValue('adn_cc_zip')
					);
				}

				$cartInfo['number'] = substr(Tools::getValue('adn_cc_number'), -4);

				$adn->sendErrorEmail(
					$adn->_adn_ft_email, 
					$cart, 
					$response_array[3],
					'error',
					$cartInfo,
					$adn->_adn_get_address
				);
			}

			if ($adn->_adn_payment_page) {
				echo $adn_cc_err;
				exit();
			} else {
				$adn_cc_err = $adn_cc_err;
			}			
			
			$time = mktime(0,0,0,Tools::getValue('adn_cc_Month'),1,Tools::getValue('adn_cc_Year'));
			$address = new Address(intval($cart->id_address_invoice));
			$selectedState = intval(Tools::getValue('adn_id_state'));
			$selectedCountry = intval(Tools::getValue('adn_id_country'));
			$this->context->smarty->assign('id_state', $selectedState);
		}
		
		self::prepareVarsView($this->context, $adn, $adn_cc_err, $time);
		
		$this->setTemplate('validation.tpl');
	}

	public static function prepareVarsView($context = null, $adn = null, $adn_cc_err, $time)
	{
		$years = array();
		for ($i = date("Y");$i < date("Y") + 10; $i++ )
		{
			$years[$i]  = $i;
		}

		$months = array();
		for ($i = 1; $i < 13; $i++)
		{
			$pi = $i < 10 ? '0' . $i : $i;
			$months[$pi] = $pi;
		}		
		
		$cart = $context->cart;
		if (is_null($adn)) {
			$adn = new AuthorizeDotNet();
		}

		// get default currency 
		$currency_module = (int) Configuration::get('PS_CURRENCY_DEFAULT');
		// recalculate currency if Currency: User Selected
		if ($cart->id_currency != $currency_module && $adn->_adn_currency == 1)
		{
			$old_id = $cart->id_currency;
			$cart->id_currency = $currency_module;
			if (is_object($context->cookie))
				$context->cookie->id_currency = $currency_module;
			if ($adn->getPSV() >= 1.5) {
				$context->currency = new Currency($currency_module);
			} else {
				$context->smarty->ps_currency = new Currency($currency_module);
			}
			$cart->update();
		}

		$confirm = Tools::getValue('confirm');
		
		if (!$confirm) {
			$address = new Address(intval($cart->id_address_invoice));
			$customer = new Customer(intval($cart->id_customer));
			$state = new State($address->id_state);
			$selectedCountry = intval($address->id_country);			
		} else {
			$selectedCountry = intval(Tools::getValue('adn_id_country'));
			$address = new Address(intval($cart->id_address_invoice));
		}
		
		$countries = Country::getCountries(intval($context->cookie->id_lang), true);
		$countriesList = '';
		foreach ($countries AS $country)
		    $countriesList .= '<option value="'.intval($country['id_country']).'" '.($country['id_country'] == $selectedCountry ? 'selected="selected"' : '').'>'.htmlentities($country['name'], ENT_COMPAT, 'UTF-8').'</option>';

		$adn_filename  = $adn->getAdnFilename();
		$context->smarty->assign('countries_list', $countriesList);
		$context->smarty->assign('countries', $countries);
		$context->smarty->assign('address', $address);
		$context->smarty->assign(array(
			'adn_payment_page' => $adn->_adn_payment_page,
			'adn_filename' => $adn_filename,
			'adn_total' => number_format($cart->getOrderTotal(true, 3), 2, '.', ''),
			'this_path_ssl' => $adn->getHttpPathModule(),
			'adn_cc_fname' => $confirm?Tools::getValue('adn_cc_fname'):"$address->firstname",
			'adn_cc_lname' => $confirm?Tools::getValue('adn_cc_lname'):"$address->lastname",
			'adn_cc_address' => $confirm?Tools::getValue('adn_cc_address'):$address->address1,
			'adn_cc_city' => $confirm?Tools::getValue('adn_cc_city'):$address->city,
			'adn_cc_state' => $confirm?Tools::getValue('adn_cc_state'):$state->iso_code,
			'adn_cc_zip' => $confirm?Tools::getValue('adn_cc_zip'):$address->postcode,
			'adn_cc_number' => Tools::getValue('adn_cc_number'),
			'adn_cc_cvm' => Tools::getValue('adn_cc_cvm'),
			'adn_cc_err' => $adn_cc_err,
			'adn_get_address' => Configuration::get('ADN_GET_ADDRESS'),
			'adn_get_cvm' => Configuration::get('ADN_GET_CVM'),
			'adn_visa' => $adn->_adn_visa,
			'adn_mc' => $adn->_adn_mc,
			'adn_amex' => $adn->_adn_amex,
			'adn_discover' => $adn->_adn_discover,
			'adn_psv' => $adn->getPSV(),
			'adn_jcb' => $adn->_adn_jcb,
			'adn_diners' => $adn->_adn_diners,
			'adn_enroute' => $adn->_adn_enroute,
			'adn_years'  => $years,
			'adn_months' => $months,
			'adn_psv'    => $adn->getPSV(),			
			'time' => $time
		));
		if (intval(ceil(number_format($cart->getOrderTotal(true, 3), 2, '.', ''))) == 0)
		    Tools::redirect('order.php?step=1');
		$context->smarty->assign('this_path', __PS_BASE_URI__.'modules/authorizedotnet/');		
	}

}
