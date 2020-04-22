<?php
/**
 * Carts Guru
 *
 * @author    LINKT IT
 * @copyright Copyright (c) LINKT IT 2016
 * @license   Commercial license
 */

if (!defined('_PS_VERSION_')) {
    exit();
}

// Upgrade module to 1.2.1
function upgrade_module_1_2_1($module)
{
    //New image configuration
    Configuration::updateValue('CARTSG_IMAGE_TYPE', 'cartsguru');

    $module->registerHook('backOfficeHeader');

    if ($module->registerDashboarHooks()) {
        // Register the new version in API
        $module->registerPluginAfterUpdate();
        return true;
    }
    return false;
}
