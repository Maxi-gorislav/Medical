<?php
/**
* Prestashop Addons | Module by: <App1Pro>
*
* @author    Chuyen Nguyen [App1Pro].
* @copyright Chuyenim@gmail.com
* @license   http://app1pro.com/license.txt
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_0()
{
    Configuration::updateValue('RELATEDPRODUCTPRO_THEME', 1);
    Configuration::updateValue('RELATEDPRODUCTPRO_REVERSE', 0);
    return true;
}
