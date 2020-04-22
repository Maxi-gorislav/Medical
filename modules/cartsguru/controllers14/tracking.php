<?php
/**
 * Carts Guru
 *
 * @author    LINKT IT
 * @copyright Copyright (c) LINKT IT 2016
 * @license   Commercial license
 */

include(dirname(__FILE__).'/../../../config/config.inc.php');
include(dirname(__FILE__).'/../../../init.php');
if (!class_exists('CountryCore')) {
    include(dirname(__FILE__).'/../../../classes/Country.php');
}
if (!class_exists('Context')) {
    include(dirname(__FILE__).'/../backward_compatibility/Context.php');
}

$context = Context::getContext();
$fields = array('email', 'homePhoneNumber', 'firstname', 'lastname', 'mobilePhoneNumber', 'countryCode');
$customerData = array();

foreach ($fields as $field) {
    $value = Tools::getValue($field);
    if ($value) {
        $customerData[$field] = $value;
    }
}

// If no email do not proceed
if (!array_key_exists('email', $customerData)) {
    die;
}
// If no email do not proceed
if (array_key_exists('countryCode', $customerData)) {
    $customerData['countryCode'] = CountryCore::getIsoById($customerData['countryCode']);
}
// Set account id
$customerData['accountId'] = $customerData['email'];

$cartsguru = Module::getInstanceByName('cartsguru');

if (isset($context->cart) && (int) $context->cart->id) {
    if (version_compare(_PS_VERSION_, '1.5.0', '<')) {
        $cartsguru->callCG(
            CartsGuruRAPI::API_PATH_CARTS,
            'cart',
            $context->cart,
            0,
            0,
            false,
            array('customer' => $customerData)
        );
    } else {
        $cartsguru->callCG(
            CartsGuruRAPI::API_PATH_CARTS,
            'cart',
            $context->cart,
            (int) $context->cart->id_shop_group,
            (int) $context->cart->id_shop,
            false,
            array('customer' => $customerData)
        );
    }
    die('success');
}
die;
