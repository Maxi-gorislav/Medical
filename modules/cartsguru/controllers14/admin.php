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
include_once(dirname(__FILE__).'/./../cartsguru.php');

$saved_auth_key = Configuration::get('CARTSG_API_AUTH_KEY');
$cartsguru_auth_key = Tools::getValue('cartsguru_auth_key');
$cartsguru_admin_action = Tools::getValue('cartsguru_admin_action');
$cartsguru_admin_data = Tools::getValue('cartsguru_admin_data');

//Check key
if (empty($saved_auth_key) || empty($cartsguru_auth_key) || $saved_auth_key !== $cartsguru_auth_key) {
    die;
}

//Get data
$data = !empty($cartsguru_admin_data) ? Tools::jsonDecode(stripcslashes($cartsguru_admin_data), true) : null;

header('Content-Type: application/json; charset=utf-8');
switch ($cartsguru_admin_action) {
    case 'toggleFeatures':
        // Enable facebook
        if ($data['facebook'] && $data['catalogId'] && $data['pixel']) {
            Configuration::updateValue('CARTSG_FEATURE_FB', true);
            Configuration::updateValue('CARTSG_FB_PIXEL', $data['pixel']);
            Configuration::updateValue('CARTSG_FB_CATALOGID', $data['catalogId']);
            // return catalogUrl
            $catalogUrl = _PS_BASE_URL_.__PS_BASE_URI__ . 'modules/cartsguru/controllers14/catalog.php';
            echo Tools::jsonEncode(array('catalogUrl' => $catalogUrl));
        } elseif ($data['facebook'] == false) {
            Configuration::updateValue('CARTSG_FEATURE_FB', false);
            echo Tools::jsonEncode(array('CARTSG_FEATURE_FB' => false));
        }
        break;
    case 'displayConfig':
        $curl_version = null;
        try {
            $info = curl_version();
            $curl_version = $info["version"];
        } catch (Exception $e) {
            $curl_version  = 'No curl';
        }

        $result = array(
            'CARTSG_API_SUCCESS' => Configuration::get('CARTSG_API_SUCCESS'),
            'CARTSG_SITE_ID' => Configuration::get('CARTSG_SITE_ID'),
            'CARTSG_IMAGE_TYPE' => Configuration::get('CARTSG_IMAGE_TYPE'),
            'CARTSG_FEATURE_FB' => Configuration::get('CARTSG_FEATURE_FB'),
            'CARTSG_FB_PIXEL' => Configuration::get('CARTSG_FB_PIXEL'),
            'CARTSG_FB_CATALOGID' => Configuration::get('CARTSG_FB_CATALOGID'),
            'PLUGIN_VERSION'=> _CARTSGURU_VERSION_,
            'CURL_VERSION' => $curl_version
        );

        echo Tools::jsonEncode($result);
        break;
}

exit;
