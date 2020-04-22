<?php
/**
 * Carts Guru
 *
 * @category advertising_marketing
 * @author    LINKT IT
 * @copyright Copyright (c) LINKT IT 2016
 * @version 1.3.6
 * @license   Commercial license
 *
 * +
 * + Languages: EN, FR
 * + PS version: 1.4,1.5,1.6
 * + Cloud compatible & tested
 */

if (! defined('_PS_VERSION_')) {
    exit();
}

define('_CARTSGURU_API_URL_', 'https://api.carts.guru');
define('_CARTSGURU_VERSION_', '1.3.6');
//Available for config : CARTSGURU_IMAGE_SIZE, CARTSGURU_ONLY_GROUP, CARTSGURU_DEBUG
require_once(_PS_ROOT_DIR_.'/modules/cartsguru/classes/ImageManager.php');
require_once(_PS_ROOT_DIR_.'/modules/cartsguru/classes/Helper.php');
require_once(_PS_ROOT_DIR_.'/modules/cartsguru/classes/RAPI.php');
require_once(_PS_ROOT_DIR_.'/modules/cartsguru/classes/RAPIException.php');
require_once(_PS_ROOT_DIR_.'/modules/cartsguru/classes/AbstractMapper.php');
require_once(_PS_ROOT_DIR_.'/modules/cartsguru/classes/AccountMapper.php');
require_once(_PS_ROOT_DIR_.'/modules/cartsguru/classes/ProductMapper.php');
require_once(_PS_ROOT_DIR_.'/modules/cartsguru/classes/CartMapper.php');
require_once(_PS_ROOT_DIR_.'/modules/cartsguru/classes/OrderMapper.php');


class CartsGuru extends Module
{
    /**
     * Indicate if the api is in debug or not
     *
     * @var bool
     */
    private $cg_debug = false;

    /**
     * FileLogger object if debug is enable
     *
     * @var object
     */
    private $logger = null;

    /**
     * setting errors for all process
     *
     * @var array
     */
    private $post_errors = array();
    /**
     * cache request
     * @var array
     */
    public static $c_calls = array();

    /**
     *
     * @see Module __construct()
     */
    public function __construct()
    {
        $this->name = 'cartsguru';
        $this->tab = 'advertising_marketing';
        $this->version = '1.3.6';
        $this->author = 'LINKT IT';
        $this->module_key = 'f841e8edc4514a141082e10c797c7c57';
        $this->bootstrap = true;
        $this->views_url = _PS_ROOT_DIR_ . '/' . basename(_PS_MODULE_DIR_) . '/' . $this->name . '/views';
        $this->module_url = __PS_BASE_URI__ . basename(_PS_MODULE_DIR_) . '/' . $this->name;
        $this->images_url = $this->module_url . '/views/img/';

        $this->cg_debug = defined('CARTSGURU_DEBUG') ? CARTSGURU_DEBUG : false;

        parent::__construct();
        $this->displayName = $this->l('Carts Guru');
        $this->description = $this->l('Your multichannel solution for easily recovering your abandoned shopping carts. Use Email & SMS Retargeting, Automatic Calls and Social Media!');
        if (! function_exists('curl_version')) {
            $this->warning = $this->l('cURL librairy is not available.');
        }
        if (version_compare(_PS_VERSION_, '1.5.0', '<')) {
            require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
        }

        if (!(int)Configuration::get('CARTSG_API_SUCCESS') && $this->canConfigure()) {
            $this->warning = $this->l('The module is not configured');
        }
    }

    /**
     * Log message in file
     */
    private function logDebug($message)
    {
        if ($this->cg_debug) {
            if (!$this->logger) {
                $this->logger = new FileLogger(0);
                $this->logger->setFilename(_PS_ROOT_DIR_.'/log/debug_cartguru_'.date('Ymd').'.log');
            }

            $this->logger->logDebug($message);
        }
    }

    /**
     * Module installation
     *
     * @see Module->install
     * @param bool|true $delete_params
     *            use for reinitialisation data
     * @return bool
     */
    public function install($delete_params = true)
    {
        if ($delete_params) {
            if (! Configuration::updateValue('CARTSG_TOKEN', $this->generateCode('', 15)) ||
                ! Configuration::updateValue('CARTSG_IMAGE_GENERATE', 0)) {
                return false;
            }

            //Images
            CartsGuruImageManager::initProductThumbnail();
        }
        if (parent::install() == false) {
            return false;
        }
        if (version_compare(_PS_VERSION_, '1.5.0', '<')) {
            if (! $this->registerHook('newOrder') ||
                ! $this->registerHook('postUpdateOrderStatus') ||
                ! $this->registerHook('cart') ||
                ! $this->registerHook('createAccount') ||
                ! $this->registerHook('backOfficeHeader')) {
                return false;
            }
        } else {
            if (! $this->registerHook('actionValidateOrder') ||
                ! $this->registerHook('actionObjectOrderUpdateAfter') ||
                ! $this->registerHook('actionCartSave') ||
                ! $this->registerHook('actionCustomerAccountAdd') ||
                ! $this->registerHook('actionObjectCustomerUpdateAfter') ||
                ! $this->registerHook('actionObjectAddressAddAfter') ||
                ! $this->registerHook('actionObjectAddressUpdateAfter') ||
                ! $this->registerHook('backOfficeHeader')) {
                return false;
            }
        }

        if (! $this->registerHeaderAndOrderConfirmationHooks()) {
            return false;
        }

        //Register dashboard widget on > 1.6
        if (! $this->registerDashboarHooks()) {
            return false;
        }

        $this->followProgress('installed');

        return true;
    }

    /**
     * Called when module update to 1.2.0
     * @return bool
     */
    public function registerHeaderAndOrderConfirmationHooks()
    {
        if (version_compare(_PS_VERSION_, '1.5.0', '<')) {
            if (! $this->registerHook('header') ||
                ! $this->registerHook('orderConfirmation')) {
                return false;
            }
        } else {
            if (! $this->registerHook('displayHeader') ||
                ! $this->registerHook('displayOrderConfirmation')) {
                return false;
            }
        }
        return true;
    }

    /**
     * Called on install or on update to 1.2.1
     * @return bool
     */
    public function registerDashboarHooks()
    {
        if (version_compare(_PS_VERSION_, '1.6.0', '>=')) {
            if (!$this->registerHook('dashboardZoneOne') || !$this->registerHook('dashboardData')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Uninstall the module
     *
     * @see
     *
     *
     *
     *
     * @param bool|true $delete_params
     *            is use for delete all data in db
     * @return bool
     */
    public function uninstall($delete_params = true)
    {
        if (! parent::uninstall()) {
            return false;
        }
        if ($delete_params && (! Configuration::deleteByName('CARTSG_TOKEN') ||
            ! Configuration::deleteByName('CARTSG_API_AUTH_KEY') ||
            ! Configuration::deleteByName('CARTSG_SITE_ID') ||
            ! Configuration::deleteByName('CARTSG_FEATURE_FB') ||
            ! Configuration::deleteByName('CARTSG_FB_PIXEL') ||
            ! Configuration::deleteByName('CARTSG_FB_CATALOGID') ||
            ! Configuration::deleteByName('CARTSG_API_SUCCESS') ||
            ! Configuration::deleteByName('CARTSG_IMAGE_GENERATE')||
            ! Configuration::deleteByName('CARTSG_IMAGE_TYPE'))) {
            return false;
        } elseif ($delete_params) {
            if (!CartsGuruImageManager::deleteCartsGuruImageType()) {
                return false;
            }
        }

        $this->followProgress('uninstalled');

        return true;
    }

    /**
     * reset the module, no data is deleted
     *
     * @return bool
     */
    public function reset()
    {
        if (! $this->uninstall(false)) {
            return false;
        }
        if (! $this->install(false)) {
            return false;
        }

        return true;
    }

     /**
     * Get the admin module url
     * @return string
     */
    protected function getConfigModuleUrl()
    {
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $admin_base = 'index.php?tab=AdminModules';
        } else {
            $admin_base = $this->context->link->getAdminLink('AdminModules', false);
        }

        return $admin_base . '&configure=' . $this->name
        . '&tab_module=' . $this->tab . '&module_name=' . $this->name . '&token='
        . Tools::getAdminTokenLite('AdminModules');
    }

    /**
     * Generate Admin url for plugin
     * @return String
     */
    public function getAdminUrl()
    {
        $adminUrl = (version_compare(_PS_VERSION_, '1.5.0', '<') ? _PS_BASE_URL_.__PS_BASE_URI__ . 'modules/cartsguru/controllers14/admin.php' : $this->context->link->getModuleLink('cartsguru', 'admin'));
        if (strrpos($adminUrl, '?') === false) {
            $adminUrl .= '?cartsguru_admin_action=';
        } else {
            $adminUrl .= '&cartsguru_admin_action=';
        }
        return $adminUrl;
    }

    /**
     * Registrs the new plugin version after update
     * @return Boolean
     */
    public function registerPluginAfterUpdate()
    {
        if ($this->canConfigure()) {
            $configs = $this->getConfigFieldsValues();
            if (! empty($configs['CARTSG_API_AUTH_KEY']) && ! empty($configs['CARTSG_SITE_ID'])) {
                $api = new CartsGuruRAPI($configs['CARTSG_SITE_ID'], $configs['CARTSG_API_AUTH_KEY']);
                $adminUrl = $this->getAdminUrl();
                $result = $api->checkAccess($adminUrl);
                return ($result ? ($result->info->http_code == 200) : false);
            }
        }

        return false;
    }

     /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        $scripts = '';
        if (Tools::getValue('configure') == $this->name) {
            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                Context::getContext()->smarty->assign(
                    array(
                        'path' => $this->module_url . '/views'
                    )
                );

                $tplPath = $this->views_url . '/templates/admin/header.tpl';
                $scripts = Context::getContext()->smarty->fetch($tplPath);
            } else {
                $this->context->controller->addJquery();
                $this->context->controller->addJS($this->views_url . '/js/admin.js');
                $this->context->controller->addCSS($this->views_url . '/css/admin.css');
            }
        }

        return $scripts;
    }

    /**
     *
     * @return String
     */
    public function getContent()
    {
        $html = '';
        $html .= $this->postProcess();

        $html .= $this->renderWelcomeView();

        return $html;
    }

    /**
     * Configuration update settings
     */
    protected function postProcess()
    {
        $html = '';
        if (Tools::isSubmit('submitConnect')) {
            $api_auth_key = Tools::getValue('authkey');
            $site_id = Tools::getValue('siteid');

            Configuration::updateValue('CARTSG_API_AUTH_KEY', $api_auth_key);
            Configuration::updateValue('CARTSG_SITE_ID', $site_id);

            $html .= $this->displayConfirmation($this->l('Settings updated'));
            $html .= $this->registerSite();
        }

        if (Tools::isSubmit('submitSubscribe')) {
            $html .= $this->subscribe();
        }

        if (Tools::isSubmit('submitHasNoAccount')) {
            $this->followProgress('subscribing');
        }

        return $html;
    }

    protected function generateProductImages($erase = false)
    {
        $html = '';
        if ($erase) {
            CartsGuruImageManager::deleteProductImageCG();
        }
        $img_generated = CartsGuruImageManager::generateProductImageCG();
        if ($img_generated === true) {
            $html .= $this->displayConfirmation($this->l('Image product for Cart Guru were successfully generated.'));
        } else {
            //Handle errors
            foreach ($img_generated as $error) {
                if ($error === 'timeout') {
                    $html .= $this->displayWarn(
                        $this->l('Only part of the images have been generated. The server timed out before finishing.')
                    );
                } else {
                    $this->post_errors[] = $error;
                }
            }
        }

        return $html;
    }

    protected function followProgress($step, $data = null)
    {
        $configs = $this->getConfigFieldsValues();
        $employee = $this->context->employee;

        $fields = array(
            'email'  => $employee->email,
            'siteId' => $configs['CARTSG_SITE_ID'] ? $configs['CARTSG_SITE_ID'] : '',
            'pluginVersion' => _CARTSGURU_VERSION_,
            'storeVersion' => _PS_VERSION_,
            'step' => $step
         );

        if ($step === 'installed') {
            $fields = array_merge($fields, $this->getStoreInformation());
        }
        
        if ($step === 'subscribed' || $step === 'registered') {
            $fields = array_merge($fields, $this->getOrderStatistics());
        }
        
        if ($data) {
            $fields = array_merge($fields, $data);
        }
        
        if (isset($fields['country'])) {
            $fields['country'] = CountryCore::getIsoById($fields['country']);
        }

        $api = new CartsGuruRAPI();
        $api->post(CartsGuruRAPI::API_PATH_PROGRESS, $fields);
    }

    protected function subscribe()
    {
        $html = '';
        $api = new CartsGuruRAPI();

        $fields = array(
            'country' => Tools::getValue('country'),
            'phoneNumber' => Tools::getValue('phoneNumber'),
            'website' =>  Tools::getValue('website'),

            'email'  => Tools::getValue('email'),
            'lastname' =>Tools::getValue('lastname'),
            'firstname' => Tools::getValue('firstname'),
            'password' => Tools::getValue('password'),

            'title' =>  Configuration::get('PS_SHOP_NAME'),
            'adminUrl' => $this->getAdminUrl()
        );

        $request = $api->subscribe($fields);
        $response = $request->decodeResponse();

        if (!$request || $request->info->http_code != 200) {
            if ($response->error == 'Country not supported') {
                $this->followProgress('subscribe-other-country', $fields);
                return $this->displayWarn($this->l('Your country is not supported for now. Please contact us on https://carts.guru?platform=prestashop'));
            } elseif ($response->error == 'Account already exists') {
                return $this->displayWarn($this->l('This email is already associated to an existing account'));
            } else {
                $this->followProgress('subscribe-error', $fields);
                return $this->displayWarn($this->l('An error occurs during the registration. Please contact us on https://carts.guru?platform=prestashop'));
            }
        }

        //Success
        Configuration::updateValue('CARTSG_API_AUTH_KEY', $response->apiToken);
        Configuration::updateValue('CARTSG_SITE_ID', $response->siteId);
        Configuration::updateValue('CARTSG_API_SUCCESS', 1);

        //Follow
        $this->followProgress('subscribed', $fields);

        //Run import
        $this->importCarts($response->siteId);
        $this->importOrders($response->siteId);

        if (!(int)Configuration::get('CARTSG_IMAGE_GENERATE')) {
            $html .= $this->generateProductImages();
        }

        return $html;
    }

    protected function registerSite()
    {
        $html = '';
        $configs = $this->getConfigFieldsValues();
        if (! empty($configs['CARTSG_API_AUTH_KEY']) && ! empty($configs['CARTSG_SITE_ID'])) {
            $api = new CartsGuruRAPI($configs['CARTSG_SITE_ID'], $configs['CARTSG_API_AUTH_KEY']);
            $adminUrl = $this->getAdminUrl();
            $result = $api->checkAccess($adminUrl);
            $access_cg = ($result ? ($result->info->http_code == 200) : false);
            if ($access_cg) {
                Configuration::updateValue('CARTSG_API_SUCCESS', 1);
                $this->followProgress('registered');
                $response = $result->decodeResponse();
                if ($response->isNew) {
                    $this->importCarts($configs['CARTSG_SITE_ID']);
                    $this->importOrders($configs['CARTSG_SITE_ID']);
                }

                if (!(int)Configuration::get('CARTSG_IMAGE_GENERATE')) {
                    $html .= $this->generateProductImages();
                }
            } else {
                Configuration::updateValue('CARTSG_API_SUCCESS', 0);
                $html .= $this->displayWarn($this->l('Impossible to connect with this credentials.'));
            }
        } else {
            Configuration::updateValue('CARTSG_API_SUCCESS', 0);
        }
        return $html;
    }

    // Check if multistore option is enabled, and if yes if a store is selected
    protected function canConfigure()
    {
        return (version_compare(_PS_VERSION_, '1.5.0', '<') ||
            ! Shop::isFeatureActive() || Shop::getContext() == Shop::CONTEXT_SHOP);
    }

    protected function getStoreInformation()
    {
        $employee = $this->context->employee;
        $shop = $this->context->shop;
        $phoneNumber = Configuration::get('PS_SHOP_PHONE', null, null, $shop->id);

        return array(
            'country' => Configuration::get('PS_COUNTRY_DEFAULT', null, null, $shop->id),
            'phoneNumber' => $phoneNumber ? $phoneNumber : '',
            'website' => _PS_BASE_URL_,

            'email'  => $employee->email,
            'lastname' => $employee->lastname,
            'firstname' => $employee->firstname,
            'language' => Language::getIsoById((int)$employee->id_lang)
         );
    }

    /**
    * Generate welcome view after install
    */
    public function renderWelcomeView()
    {
        $html = '';
        
        $employee = $this->context->employee;
        $countries = CountryCore::getCountries($employee->id_lang, false, false, false);
        $countries_array = array();
        foreach ($countries as $country) {
            $countries_array[$country['id_country']] = $country['name'];
        }

        $configs = $this->getConfigFieldsValues();
        $options = array(
           'isSubmitSuccess' =>  (int)$configs['CARTSG_API_SUCCESS'] == 1,
           'canConfigure' => $this->canConfigure()
        );

        $variables = array(
           'siteid' => $configs['CARTSG_SITE_ID'] ? $configs['CARTSG_SITE_ID'] : '',
           'authkey' => $configs['CARTSG_API_AUTH_KEY'] ? $configs['CARTSG_API_AUTH_KEY'] : '',

           'formUrl' => $this->getConfigModuleUrl(),
           'imagesUrl' => $this->images_url,
           'activeView' => '',
           'countries' => $countries_array
        );

        if ($options['canConfigure'] == false) {
            $variables['activeView'] = 'view-no-store-selected';
        } elseif ($options['isSubmitSuccess'] == true) {
            $variables['activeView'] = 'view-success';
        } elseif (Tools::isSubmit('submitHasNoAccount') || Tools::isSubmit('submitSubscribe')) {
            $variables['activeView'] = 'view-try-it';
        } elseif (!empty($configs['CARTSG_SITE_ID']) && !empty($configs['CARTSG_SITE_ID'])) {
            $variables['activeView'] = 'view-have-account';
        }

        $storeInformation = $this->getStoreInformation();
        $variables = array_merge($variables, $storeInformation);

        $this->context->smarty->assign($variables);
        $html .= $this->context->smarty->fetch($this->views_url . '/templates/admin/welcome.tpl');
        return $html;
    }

    /**
     * Get all configuration
     *
     * @return array
     */
    public function getConfigFieldsValues()
    {
        return array(
            'CARTSG_SITE_ID' => Tools::getValue('siteid', Configuration::get('CARTSG_SITE_ID')),
            'CARTSG_API_AUTH_KEY' => Tools::getValue('authkey', Configuration::get('CARTSG_API_AUTH_KEY')),
            'CARTSG_API_SUCCESS' => Configuration::get('CARTSG_API_SUCCESS')
        );
    }

    /**
     * generate a uniq code for token
     *
     * @param string $prefix
     * @param int $length
     * @return string
     */
    public static function generateCode($prefix = '', $length = 8)
    {
        $code = '';
        $possible = '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $maxlength = Tools::strlen($possible);
        if ($length > $maxlength) {
            $length = $maxlength;
        }
        $i = 0;
        while ($i < $length) {
            $char = Tools::substr($possible, mt_rand(0, $maxlength - 1), 1);
            if (! strstr($code, $char)) {
                $code .= $char;
                $i ++;
            }
        }
        return $prefix . $code;
    }

    /**
     * Only used for 1.4
     * @param $params
     */
    public function hookNewOrder($params)
    {
        return $this->hookActionValidateOrder($params);
    }
    /**
     * Only used for 1.4
     * @param $params
     */
    public function hookPostUpdateOrderStatus($params)
    {
        if ((int)$params['id_order']) {
            $order = new Order((int)$params['id_order']);
            $params['object'] = $order;
            return $this->hookActionObjectOrderUpdateAfter($params);
        }
    }

    /**
     * Display on each page
     *
     * @param array $params
     * @return boolean
     */
    public function hookDisplayHeader($params)
    {
        $trackingUrl = (version_compare(_PS_VERSION_, '1.5.0', '<') ? _PS_BASE_URL_.__PS_BASE_URI__ . 'modules/cartsguru/controllers14/tracking.php' : $this->context->link->getModuleLink('cartsguru', 'tracking'));
        
        //Avoid mixed content
        $trackingUrl = str_replace(array('https:', 'http:'), '', $trackingUrl);
        
        // Live tracking
        $variables = array(
            'trackingUrl' => $trackingUrl
        );
        
        
        $this->context->smarty->assign($variables);
        $html = $this->context->smarty->fetch($this->views_url . '/templates/hook/tracking.tpl');

        $facebook = Configuration::get('CARTSG_FEATURE_FB');
        // If FB not enabled return early
        if (!$facebook) {
            return $html;
        }
        // Set source cookie
        $utm_source = Tools::getValue('utm_source');
        $utm_campaign = Tools::getValue('utm_campaign');

        if (!empty($utm_source) && $utm_source === 'cartsguru-fb' && !empty($utm_campaign)) {
            Context::getContext()->cookie->__set('cartsguru-source', serialize(array(
                'type' => $utm_source,
                'campaign' => $utm_campaign,
                'timestamp' => time()
            )));
        }

        $pixel = Configuration::get('CARTSG_FB_PIXEL');
        $catalogId = Configuration::get('CARTSG_FB_CATALOGID');
        if ($pixel && $catalogId) {
            $variables = array(
                'pixel' => $pixel,
                'currency_iso' => $this->context->currency->iso_code,
                'catalogId' => $catalogId
            );
            // Product view
            if ($this->context->controller instanceof ProductController || Tools::getValue('id_product')) {
                $product_instance = method_exists($this->context->controller, 'getProduct') ? $this->context->controller->getProduct() : new Product(Tools::getValue('id_product'));
                if ($product_instance) {
                    $variables['track_product_view'] = array(
                        'id' => $product_instance->id,
                        'value' => Tools::ps_round($product_instance->getPrice(), 2)
                    );
                }
            }

            $this->context->smarty->assign($variables);
            $html .= $this->context->smarty->fetch($this->views_url . '/templates/hook/pixel.tpl');
        }
        return $html;
    }

    /**
     * Display when order is confirmed
     * warning params changed in 1.7!
     *
     * @param array $params
     * @return boolean
     */
    public function hookDisplayOrderConfirmation($params)
    {
        $facebook = Configuration::get('CARTSG_FEATURE_FB');
        // If FB not enabled return early
        if (!$facebook) {
            return;
        }
        $order = $params['objOrder'];
        // Track order
        if (isset($order) && (int) $order->id && (int) $order->id_customer) {
            // Set source if present
            if (Context::getContext()->cookie->__isset('cartsguru-source')) {
                $order->source = unserialize(Context::getContext()->cookie->__get('cartsguru-source'));
            }

            if (version_compare(_PS_VERSION_, '1.5.0', '<')) {
                $this->callCG(
                    CartsGuruRAPI::API_PATH_ORDERS,
                    'order',
                    $order,
                    0,
                    0,
                    false,
                    $params
                );
            } else {
                $this->callCG(
                    CartsGuruRAPI::API_PATH_ORDERS,
                    'order',
                    $order,
                    (int) $order->id_shop_group,
                    (int) $order->id_shop
                );
            }
        }
        // Unset the cookie after order is made
        if (Context::getContext()->cookie->__isset('cartsguru-source')) {
            Context::getContext()->cookie->__unset('cartsguru-source');
        }

        $pixel = Configuration::get('CARTSG_FB_PIXEL');
        $catalogId = Configuration::get('CARTSG_FB_CATALOGID');
        if ($pixel && $catalogId) {
            $order_items = $order->getProducts();
            if (count($order_items) > 0) {
                $productsIds = array();
                foreach ($order_items as $order_item) {
                    $productsIds[] = $order_item['product_attribute_id'];
                }
                $variables = array(
                    'order' => array(
                        'total' => Tools::ps_round($params['total_to_pay'], 2),
                        'products' => implode($productsIds, "', '")
                    ),
                    'currency_iso' => $params['currencyObj']->iso_code,
                    'catalogId' => $catalogId
                );

                $this->context->smarty->assign($variables);
                return $this->context->smarty->fetch($this->views_url . '/templates/hook/order_confirm.tpl');
            }
        }
    }

    /**
     * When order is validate, indicate it is the reminder permit
     * journal is close
     *
     * @param
     *            $params
     */
    public function hookActionValidateOrder($params)
    {
        $order = $params['order'];
        $params['object'] = $order;
        return $this->hookActionObjectOrderUpdateAfter($params);
    }

    /**
     * Order update
     *
     * @param array $params
     * @return boolean
     */
    public function hookActionObjectOrderUpdateAfter($params)
    {
        $order = $params['object'];
        if (! Validate::isLoadedObject($order)) {
            return false;
        }
        if (isset($order) && (int) $order->id && (int) $order->id_customer) {
            // Set source if present
            if (Context::getContext()->cookie->__isset('cartsguru-source')) {
                $order->source = unserialize(Context::getContext()->cookie->__get('cartsguru-source'));
            }

            if (version_compare(_PS_VERSION_, '1.5.0', '<')) {
                $this->callCG(
                    CartsGuruRAPI::API_PATH_ORDERS,
                    'order',
                    $order,
                    0,
                    0,
                    false,
                    $params
                );
            } else {
                $this->callCG(
                    CartsGuruRAPI::API_PATH_ORDERS,
                    'order',
                    $order,
                    (int) $order->id_shop_group,
                    (int) $order->id_shop
                );
            }
        }
        return true;
    }

    /**
     * Customer add address
     *
     * @param array $params
     * @return boolean
     */
    public function hookActionObjectAddressAddAfter($params)
    {
        return $this->hookActionObjectAddressUpdateAfter($params);
    }

    /**
     * Customer update address
     *
     * @param array $params
     * @return boolean
     */
    public function hookActionObjectAddressUpdateAfter($params)
    {
        $address = $params['object'];
        if (! Validate::isLoadedObject($address)) {
            return false;
        }
        if (isset($address) && (int) $address->id) {
            if ((int) $address->id_customer) {
                $customer = new Customer((int) $address->id_customer);
                if (version_compare(_PS_VERSION_, '1.5.0', '<')) {
                    $this->callCG(
                        CartsGuruRAPI::API_PATH_ACCOUNTS,
                        'customer',
                        $customer
                    );
                } else {
                    $this->callCG(
                        CartsGuruRAPI::API_PATH_ACCOUNTS,
                        'customer',
                        $customer,
                        (int) $customer->id_shop_group,
                        (int) $customer->id_shop
                    );
                }
            }
        }
        return true;
    }

    /**
     * Customer update information
     *
     * @param array $params
     * @return boolean
     */
    public function hookActionObjectCustomerUpdateAfter($params)
    {
        $customer = $params['object'];
        if (! Validate::isLoadedObject($customer)) {
            return false;
        }
        if (isset($customer) && (int) $customer->id) {
            if (version_compare(_PS_VERSION_, '1.5.0', '<')) {
                $this->callCG(
                    CartsGuruRAPI::API_PATH_ACCOUNTS,
                    'customer',
                    $customer
                );
            } else {
                $this->callCG(
                    CartsGuruRAPI::API_PATH_ACCOUNTS,
                    'customer',
                    $customer,
                    (int) $customer->id_shop_group,
                    (int) $customer->id_shop
                );
            }
        }
        return true;
    }

    /**
     * hookHeader only used for 1.4
     * @param $params
     * @return boolean
     */
    public function hookHeader($params)
    {
        return ($this->hookDisplayHeader($params));
    }

    /**
     * orderConfirmation only used for 1.4
     * @param $params
     * @return boolean
     */
    public function hookOrderConfirmation($params)
    {
        return $this->hookDisplayOrderConfirmation($params);
    }

    /**
     * Only used for 1.4
     * @param $params
     */
    public function hookCreateAccount($params)
    {
        return ($this->hookActionCartSave($params));
    }

    /**
     * Successful create account
     *
     * @param array $params
     * @return boolean
     */
    public function hookActionCustomerAccountAdd($params)
    {
        $new_customer = $params['newCustomer'];
        $params['object'] = $new_customer;
        return ($this->hookActionObjectCustomerUpdateAfter($params));
    }
    /**
     * Only used for Prestashop 1.4
     * @param $params
     * @return void|boolean
     */
    public function hookCart($params)
    {
        if (!$params['cart']->id) {
            return;
        }
        return ($this->hookActionCartSave($params));
    }

    /**
     * this hook is call many times update the cart
     * The module catch only cart have customer logged
     */
    public function hookActionCartSave($params)
    {
        $cart = $params['cart'];
        if (! Validate::isLoadedObject($cart)) {
            return false;
        }
        if (isset($cart) && (int) $cart->id && (int) $cart->id_customer) {
            if (version_compare(_PS_VERSION_, '1.5.0', '<')) {
                $this->callCG(
                    CartsGuruRAPI::API_PATH_CARTS,
                    'cart',
                    $cart
                );
            } else {
                $this->callCG(
                    CartsGuruRAPI::API_PATH_CARTS,
                    'cart',
                    $cart,
                    (int) $cart->id_shop_group,
                    (int) $cart->id_shop
                );
            }
        }
        return true;
    }

    /**
    *   Dashboard: Register widget on left area (> 1.6)
    */
    public function hookDashboardZoneOne($params)
    {
        $this->context->smarty->assign(
            array(
                'date_from' => Tools::displayDate($params['date_from']),
                'date_to' => Tools::displayDate($params['date_to'])
            )
        );
        return $this->display(__FILE__, 'dashboard_zone_one.tpl');
    }

    /**
    *   Dashboard: Retrieve the data from API
    */
    public function hookDashboardData($params)
    {
        $result = array(
            'data_value' => array(
                    'cg_processed_carts' => 0,
                    'cg_turnover' => 0,
                    'cg_sales' => 0
            )
        );

        $configs = $this->getConfigFieldsValues();

        //Check plugin is configured
        if (!$configs['CARTSG_API_SUCCESS'] || empty($configs['CARTSG_API_AUTH_KEY']) || empty($configs['CARTSG_SITE_ID'])) {
            return $result;
        }

        $apiParams = array(
                'dateFrom' =>  $params['date_from'],
                'dateTo' =>  $params['date_to']
        );

        $api = new CartsGuruRAPI($configs['CARTSG_SITE_ID'], $configs['CARTSG_API_AUTH_KEY']);
        $request = $api->getDashboardStatistics($apiParams);
        //Check API success request
        if (!$request || $request->info->http_code != 200) {
            return $result;
        }

        $response = $request->decodeResponse();
        //Result look like {"average":0,"turnover":0,"sales":0,"conversionRate":0,"remainingCarts":0,"processedCarts":0,"abandonedPercentage":0,"totalCarts":0};
        $result['data_value']['cg_processed_carts'] = $response->processedCarts;
        $result['data_value']['cg_turnover'] = $response->turnover;
        $result['data_value']['cg_sales'] = $response->sales;

        return $result;
    }

    /**
     * Return good initialize mapper depending name
     * @param string $mapper_name
     * @param int $id_lang_default
     * @param int $id_shop_group
     * @param int $id_shop
     * @throws InvalidArgumentException
     * @return AbstractCartsGuruMapper
     */
    public function getMapper($mapper_name, $id_lang_default, $id_shop_group, $id_shop)
    {
        switch ($mapper_name) {
            case 'cart':
                return new CartsGuruCartMapper($id_lang_default, (int) $id_shop_group, (int) $id_shop);
            case 'order':
                return new CartsGuruOrderMapper($id_lang_default, (int) $id_shop_group, (int) $id_shop);
            case 'product':
                return new CartsGuruProductMapper($id_lang_default, (int) $id_shop_group, (int) $id_shop);
            case 'customer':
                return new CartsGuruAccountMapper($id_lang_default, (int) $id_shop_group, (int) $id_shop);
        }

        throw new InvalidArgumentException();
    }

    /**
     * Map and send data to api
     *
     * @param string $path
     * @param string $mapper_name
     * @param Object $object
     * @param int $id_shop_group
     * @param int $id_shop
     * @param string $sync
     * @return boolean
     */
    public function callCG($path, $mapper_name, $object, $id_shop_group = 0, $id_shop = 0, $sync = false, $params = array())
    {
        $success = 0;
        $site_id = '';
        $auth_key = '';
        if (version_compare(_PS_VERSION_, '1.5.0', '<')) {
            $success = Configuration::get('CARTSG_API_SUCCESS');
            $site_id = Configuration::get('CARTSG_SITE_ID');
            $auth_key = Configuration::get('CARTSG_API_AUTH_KEY');
        } else {
            $success = Configuration::get('CARTSG_API_SUCCESS', null, $id_shop_group, $id_shop);
            $site_id = Configuration::get('CARTSG_SITE_ID', null, $id_shop_group, $id_shop);
            $auth_key = Configuration::get('CARTSG_API_AUTH_KEY', null, $id_shop_group, $id_shop);
        }

        if ($success && ! empty($site_id) && ! empty($auth_key)) {
            if ($mapper_name) {
                $id_lang_default = (int) Configuration::get('PS_LANG_DEFAULT');
                $mapper = $this->getMapper(
                    $mapper_name,
                    $id_lang_default,
                    (int) $id_shop_group,
                    (int) $id_shop
                );

                $object = $mapper->create($object, $params);

                if ($object == null) {
                    return false;
                }

                $object['siteId'] = $site_id;
            }

            $call_uid = $path.'_'.self::getUniqueVar($object);
            //Is already call in same request
            $this->logDebug('UID :'.$call_uid);

            if (!isset(self::$c_calls[$call_uid])) {
                try {
                    $api = new CartsGuruRAPI($site_id, $auth_key);
                    $this->logDebug('UID:'.$call_uid.' / START CALL API');

                    $api->post($path, $object, $sync);

                    $this->logDebug('UID:'.$call_uid.' / END CALL API');

                    self::$c_calls[$call_uid] = 1;
                    return true;
                } catch (Exception $e) {
                }
            } else {
                $this->logDebug('UID:'.$call_uid.' / NO CALL NEED');
            }
            return true;
        }
        return false;
    }
    public static function getUniqueVar($data)
    {
        if (!isset($data)) {
            return '';
        }
        return md5(serialize($data));
    }

    /**
     * Compatibility with all PS Version
     *
     * @param array/string $warning
     * @return string
     */
    public function displayWarn($warning)
    {
        $this->context->smarty->assign(
            array(
                'warning' => $warning,
                'ps_version' => _PS_VERSION_
            )
        );
        $output = $this->context->smarty->fetch(
            $this->views_url . '/templates/admin/helper_warning.tpl'
        );
        return $output;
    }

    private function import($siteId, $type, $api_path)
    {
        $isMultiStoreSupported = version_compare(_PS_VERSION_, '1.5.0', '>=');

        $sql = 'SELECT `id_' . bqSQL($type) . '` AS id FROM `' . _DB_PREFIX_ . bqSQL($type);
        if ($type === 'order') {
            $sql .= 's';
        }
        $sql .= '`';

        //Need filter on the good shop
        if ($isMultiStoreSupported) {
            $id_shop = (int)Context::getContext()->shop->id;

            $sql .= ' WHERE id_shop = ' . (int)$id_shop;
        }

        $sql .= ' ORDER BY date_add DESC LIMIT 250';

        if ($results = Db::getInstance()->ExecuteS($sql)) {
            $id_lang_default = (int) Configuration::get('PS_LANG_DEFAULT');
            $items = array();
            foreach ($results as $row) {
                $item = $type === 'order' ? new Order($row['id']) : new Cart($row['id']);


                $mapper = $this->getMapper(
                    $type,
                    $id_lang_default,
                    $isMultiStoreSupported ? $item->id_shop_group : 0,
                    $isMultiStoreSupported ? $item->id_shop : 0
                );

                $cart_guru_data = $mapper->create($item);
                if ($cart_guru_data) {
                    $cart_guru_data['siteId'] = $siteId;
                    $items[] = $cart_guru_data;
                }
            }

            $this->callCG(
                $api_path,
                null,
                $items
            );
        }
    }

    private function importCarts($siteId)
    {
        $this->import($siteId, 'cart', CartsGuruRAPI::API_PATH_IMPORT_CARTS);
    }

    private function importOrders($siteId)
    {
        $this->import($siteId, 'order', CartsGuruRAPI::API_PATH_IMPORT_ORDERS);
    }
    
    private function getOrderStatistics($sinceDays = 31)
    {
        $sinceDays = $sinceDays * -1;
        $isMultiStoreSupported = version_compare(_PS_VERSION_, '1.5.0', '>=');
        $currency = new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT'));
        
        $result = array(
            'orderCount' => 0,
            'orderTotal' => 0,
            'currency' => (string) $currency->iso_code,
        );
        
        $sql = 'SELECT count(id_order) as `order_count` , sum(total_paid / conversion_rate) AS `order_total` 
                FROM `' . _DB_PREFIX_ . 'orders`
                WHERE `invoice_date` >= DATE_ADD(SYSDATE(), INTERVAL ' . (int)$sinceDays  . ' DAY)
                AND valid = 1';
            
        //Need filter on the good shop
        if ($isMultiStoreSupported) {
            $id_shop = (int)Context::getContext()->shop->id;

            $sql .= ' AND id_shop = ' . (int)$id_shop;
        }
        
        if ($results = Db::getInstance()->ExecuteS($sql)) {
            $row = $results[0];
            $result['orderCount'] = round($row['order_count'], 0);
            $result['orderTotal'] = round($row['order_total'], 2);
        }

        return $result;
    }
}
