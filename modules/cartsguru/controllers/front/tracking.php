<?php
/**
 * Carts Guru
 *
 * @author    LINKT IT
 * @copyright Copyright (c) LINKT IT 2016
 * @license   Commercial license
 */

class CartsGuruTrackingModuleFrontController extends ModuleFrontController
{
    public function display()
    {
        $fields = array('email', 'homePhoneNumber', 'firstname', 'lastname', 'mobilePhoneNumber', 'countryCode');
        $customerData = array();

        foreach ($fields as $field) {
            $value = Tools::getValue($field);
            if ($value) {
                $customerData[$field] = $value;
            }
        }

        // If no email do not proceed
        if (!$customerData['email']) {
            die;
        }
        // Process country
        if (array_key_exists('countryCode', $customerData)) {
            $customerData['countryCode'] = CountryCore::getIsoById($customerData['countryCode']);
        }
        // Set account id
        $customerData['accountId'] = $customerData['email'];

        $cart = $this->context->cart;
        $cartsguru = Module::getInstanceByName('cartsguru');
        if (isset($cart) && (int) $cart->id) {
            if (version_compare(_PS_VERSION_, '1.5.0', '<')) {
                $cartsguru->callCG(
                    CartsGuruRAPI::API_PATH_CARTS,
                    'cart',
                    $cart,
                    0,
                    0,
                    false,
                    array('customer' => $customerData)
                );
            } else {
                $cartsguru->callCG(
                    CartsGuruRAPI::API_PATH_CARTS,
                    'cart',
                    $cart,
                    (int) $cart->id_shop_group,
                    (int) $cart->id_shop,
                    false,
                    array('customer' => $customerData)
                );
            }
            die('success');
        }
        die;
    }
}
