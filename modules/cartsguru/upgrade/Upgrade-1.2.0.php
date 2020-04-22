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

// Upgrade module to 1.2.0
function upgrade_module_1_2_0($module)
{
    if ($module->registerHeaderAndOrderConfirmationHooks()) {
        // Register the new version in API
        $module->registerPluginAfterUpdate();
        return true;
    }
    return false;
}
