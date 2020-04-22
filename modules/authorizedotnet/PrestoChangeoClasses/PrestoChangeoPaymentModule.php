<?php 
/**
 * Base module class of Presta Changeo Module
 */

class PrestoChangeoPaymentModule extends PaymentModule
{
	protected $context = '';
	public    $smarty  = '';
	protected $_last_updated;
	protected $_full_version;	
 	protected $_html = '';
	/**
	 * Add to contructor instance of context  
	 */
	public function __construct()
	{
		parent::__construct();
		if ($this->getPSV() < 1.5) { 
			$this->context = PrestoChangeoContext::getContext();
			$this->smarty = $this->context->smarty;
		}
	}

	/**
	 * Get context 
	 */
	public function getContext() 
	{
		return $this->context;
	}

	/**
	 * get version of PrestaShop 
	 * return float value version 
	 */
	public function getPSV()
	{
		return floatval(substr($this->getRawPSV(),0,3));
	}

	/**
	 * get raw version of PrestaShop 
	 */	
	private function getRawPSV()
	{
		return _PS_VERSION_;
	}

	/**
	 * Retrun validation for all version prestashop
	 */
	public function getValidationLink($file = 'validation')
	{
		if ($this->getPSV() >= 1.5) {
			$validationLink = Context::getContext()->link->getModuleLink($this->name, $file, array(), true); 
		} else {
			$this_path_ssl = $this->getHttpPathModule();
			$validationLink = $this_path_ssl . $file . '.php';
		}
		return $validationLink;
	}
	
	public function getRedirectBaseUrl()
	{
		if ($this->getPSV() < 1.5) {
			$redirect_url = 'order-confirmation.php?';
			return (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ . $redirect_url;
		} else {
			$redirect_url = Context::getContext()->link->getPageLink('order-confirmation');
			return $redirect_url = strpos($redirect_url, '?') !== false ? $redirect_url . '&' : $redirect_url . '?' ;
		}
	}

	/**
	 * Return path to http module directory.
	 */
	public function getHttpPathModule()
	{
		return (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/';
	}

	/**
	 * get content for back end settings 
	 */
	public function getContent()
	{
		$this->_postProcess();
		$this->_displayForm();
		return $this->_html;
	}

	/**
	 * Send email error
	 * $email - email which will be sent error
	 * $cartObj - PS cart object 
	 * $errorText - text that return payment gateway
	 */
	public function sendErrorEmail($email, $cartObj, $errorText, 
			$template = 'error', $cartInfo = array(), $isCustomAddress = 0
			)
	{
		$customerObj = new Customer($cartObj->id_customer);
		$address     = new Address(intval($cartObj->id_address_invoice));

		$addressHTML = '';
		$addressHTML .= $this->l('Cart ') . '# ' . $cartObj->id . '<br /><br />' . "\n\r" . "\n\r"; 

		if (!empty($cartInfo['number'])) {
			$addressHTML .= $this->l('Card Number') . ': XXXX XXXX XXXX ' . $cartInfo['number'] . '<br /><br />' . "\n\r" . "\n\r"; 
		}

		if ($isCustomAddress) {
			$addressHTML .= $cartInfo['firstname'] . ' ' . $cartInfo['lastname'] . '<br />' . "\n\r";
			$addressHTML .= $cartInfo['address'] . '<br />' . "\n\r";
			$addressHTML .= $cartInfo['city'] . ' ' . $cartInfo['zip'] . '<br />' . "\n\r";

			if (!empty($cartInfo['country'])) {
				$country = new Country($cartInfo['country']);
				$addressHTML .= $this->l('Country') . ': ' . $country->name[$cartObj->id_lang] . '<br />' . "\n\r";
			} elseif (!empty($cartInfo['country_name'])) {
				$addressHTML .= $this->l('Country') . ': ' . $cartInfo['country_name'] . '<br />' . "\n\r";
			}

			if (!empty($cartInfo['state'])) {
				$state = new State($cartInfo['state']);
				$addressHTML .= $this->l('State') . ': ' . $state->name . '<br />' . "\n\r";
			} elseif (!empty($cartInfo['state_name'])) {
				$addressHTML .= $this->l('State') . ': ' . $cartInfo['state_name'] . '<br />' . "\n\r";
			}

		} else {
			$addressHTML .= $address->firstname . ' ' . $address->lastname . '<br />' . "\n\r";
			$addressHTML .= !empty($address->company) ? $address->company . '<br />' . "\n\r" : '';
			$addressHTML .= $address->address1 . ' ' . $address->address2 . '<br />' . "\n\r";
			$addressHTML .= $address->postcode . ' ' . $address->city . '<br />' . "\n\r";			

			if (!empty($address->country)) {
				$addressHTML .= $this->l('Country') . ': ' . $address->country . '<br />' . "\n\r";
			}

			if (!empty($address->id_state)) {
				$state = new State($address->id_state);
				$addressHTML .= $this->l('State') . ': ' . $state->name . '<br />' . "\n\r";
			}			
		}

		$cartHTML = '<table cellpadding="2">' . "\n\r";
		foreach($cartObj->getProducts() AS $product) {
			$cartHTML .= '<tr>';
			$cartHTML .= '<td> ' . $product['quantity'] . '</td>';
			$cartHTML .= '<td>x</td>';
			$cartHTML .= '<td> ' . Tools::displayPrice($product['price']) . '</td>';
			$cartHTML .= '<td> ' . Tools::displayPrice($product['total']) . '</td>';

			$cartHTML .= '<td> ' . $product['name'] . '</td>';
			$cartHTML .= '</tr>' . "\n\r";
		}
		
		$cartHTML .= '<tr>';
		$cartHTML .= '<td colspan="2"></td>';
		
		$cartHTML .= '<td align="right"> ' . $this->l('Total') . '</td>';
		$cartHTML .= '<td> ' . Tools::displayPrice($cartObj->getOrderTotal()) . '</td>';
		$cartHTML .= '</tr>' . "\n\r";
		
		$cartHTML .= '</table>';
		Mail::Send(
			Language::getIdByIso('en'), 
			$template,
			$this->l('Transaction failed'),
			array(
				'{customer_email}' => $customerObj->email,
				'{customer_ip}'    => $_SERVER['REMOTE_ADDR'],
				'{error}'          => $errorText,
				'{cartHTML}'       => $cartHTML,
				'{cartTXT}'        => strip_tags($cartHTML),
				'{addressHTML}'    => $addressHTML,
				'{addressTXT}'     => strip_tags($addressHTML)
				
			),
			$email,
			null,
			null,
			null,
			null,
			null,
			_PS_MODULE_DIR_ . strtolower($this->name) . '/views/templates/emails/'
		);
	}

	/**
	 * Compare version of prestashop curent with $version2
	 *  
	 */
	public function comparePSV($operator, $version2)
	{
		return version_compare(substr($this->getRawPSV(), 0, strlen($version2)), $version2, $operator);
	}


	/*
	 * Check if override files were properly copied.
	 */
	protected function overrideCheck($mod, $srv)
	{
		if (!is_array($srv))
			return false;
		$class_found = false;
		foreach ($mod as $row)
		{
			if (!$class_found)
			{
				if (substr($row,0,5) == 'class')
				{
					$class_found = true;
					//print "Class found<br />";
				}
				continue;
			}
			else
			{
				$row = trim($row);
				$row_found = false;
				if (is_array($srv))
					foreach ($srv as $key => $orow)
					{
						if ($row == trim($orow))
						{
							$srv = array_slice($srv, $key);
							$row_found = true;
							//print "Found $row<br />";
							break;
						}
					}
				if (!$row_found)
				{
					//print "Not Found $row<br />";
					return false;
				}
			}
		}
		return true;
	}
	
	
	/**
	 *  Does module need updating
	 */
	protected function upgradeCheck($module)
	{
		// Only run upgrae check if module is loaded in the backoffice.
		if (($this->getPSV() > 1.1  && $this->getPSV() < 1.5) && (!is_object($this->context->cookie) || !$this->context->cookie->isLoggedBack()))
			return;
		if ($this->getPSV() >= 1.5)
		{
			if (!isset($this->context->employee) || !$this->context->cookie->passwd || !$this->context->employee->isLoggedBack())
				return;			
		}
		// Get Presto-Changeo's module version info
		$mod_info_str = Configuration::get('PRESTO_CHANGEO_SV');
		if (!function_exists('json_decode'))
		{
			if (!file_exists(dirname(__FILE__).'/JSON.php'))
				return false; 
			include_once(dirname(__FILE__).'/JSON.php');
			$j = new JSON();
			$mod_info = $j->unserialize($mod_info_str);
		}
		else
			$mod_info = json_decode($mod_info_str);
		// Get last update time.
		$time = time();
		// If not set, assign it the current time, and skip the check for the next 7 days. 
		if ($this->_last_updated <= 0)
		{
			Configuration::updateValue('PRESTO_CHANGEO_UC', $time);
			$this->_last_updated = $time;
		}
		// If haven't checked in the last 1-7+ days
		$update_frequency = max(86400, isset($mod_info->{$module}->{'T'})?$mod_info->{$module}->{'T'}:86400);
		if ($this->_last_updated < $time - $update_frequency)
		{	
			// If server version number exists and is different that current version, return URL
			if (isset($mod_info->{$module}->{'V'}) && $mod_info->{$module}->{'V'} > $this->_full_version)
				return $mod_info->{$module}->{'U'};
			$url = 'http://updates.presto-changeo.com/?module_info='.$module.'_'.$this->version.'_'.$this->_last_updated.'_'.$time.'_'.$update_frequency;
			$mod = @file_get_contents($url);
			if ($mod == '' && function_exists('curl_init'))
			{
				$ch = curl_init();
				curl_setopt ($ch, CURLOPT_URL, $url);
				curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
				$mod = curl_exec($ch);
			}
			Configuration::updateValue('PRESTO_CHANGEO_UC', $time);
			$this->_last_updated = $time;
			if (!function_exists('json_decode') )
			{
				$j = new JSON();
				$mod_info = $j->unserialize($mod);
			}
			else
				$mod_info = json_decode($mod);
			if (!isset($mod_info->{$module}->{'V'}))
				return false;
			if (Validate::isCleanHtml($mod))
				Configuration::updateValue('PRESTO_CHANGEO_SV', $mod);
			if ($mod_info->{$module}->{'V'} > $this->_full_version)
				return $mod_info->{$module}->{'U'};
			else 
				return false;
		}
		elseif (isset($mod_info->{$module}->{'V'}) && $mod_info->{$module}->{'V'} > $this->_full_version)
			return $mod_info->{$module}->{'U'};
		else
			return false;
	}	
	
	public function getModuleRecommendations($module)
	{
		$arr = unserialize(Configuration::get('PC_RECOMMENDED_LIST'));
		// Get a new recommended module list every 10 days //
		if (!is_array($arr) || sizeof($arr) == 0 || Configuration::get('PC_RECOMMENDED_LAST') < time() - 864000)
		{
			$url = 'http://updates.presto-changeo.com/recommended.php';
			$str = @file_get_contents($url);
			if ($str == '' && function_exists('curl_init'))
			{
				$ch = curl_init();
				curl_setopt ($ch, CURLOPT_URL, $url);
				curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
				$str = curl_exec($ch);
			}
			Configuration::updateValue('PC_RECOMMENDED_LIST', $str);
			Configuration::updateValue('PC_RECOMMENDED_LAST', time());
			$arr = unserialize($str);
		}
		$dupe = false;
		$rand = array_rand($arr, 5);
		$out = '<div style="width:100%">
					<div style="float:left;width:100%;">
						<div style="float:left; padding: 10px;">
							<a href="https://www.presto-changeo.com/en/contact_us" target="_index"><img src="http://updates.presto-changeo.com/logo.jpg" border="0" /></a>
						</div>
						<div style="min-height:69px;float:left;border: 1px solid #c0d2d2;background-color: #e3edee">
							<div style="width: 80px;float: left;padding-top: 12px;">
								<div style="color:#5d707e;font-weight:bold;text-align:center">'.$this->l('Explore').'<br />'.$this->l('Our').'<br />'.$this->l('Modules').'</div>
							</div>
							<div style="float: left;">';
		for ($j = 0 ; $j < 4 ; $j++)
		{
			// Make sure to exclude the current module //
			if ($arr[$rand[$j]]['code'] == $module)
				$dupe = true;
			$i = $rand[$dupe?$j+1:$j];
			$out .= '
							<div style="margin-right: 8px;width: 143px;height:57px;float: left;margin-top:5px;border: 1px solid #c0d2d2;background-color: #ffffff">
								<div style="width:45px; height: 45px;margin: 6px 8px 6px 6px; float:left;">
									<a target="_index" href="'.$arr[$i]['url'].'">
										<img border="0" src="'.$arr[$i]['img'].'" width="45" height="45" />
									</a>
								</div>
								<div style="width:80px; height: 45px; float:left;margin-top: 6px;font-weight: bold;">
									<a style="color:#085372;" target="_index" href="'.$arr[$i]['url'].'">
										'.$arr[$i]['name'].'
									</a>
								</div>
							</div>';
		}
		$out .= '
							</div>
						</div>
					</div>
				</div>';
		return $out;
	}			
}