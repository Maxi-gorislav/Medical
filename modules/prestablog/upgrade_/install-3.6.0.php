<?php
/**
 * 2008 - 2015 HDClic
 *
 * MODULE PrestaBlog
 *
 * @version   3.6.3
 * @author    HDClic <prestashop@hdclic.com>
 * @link      http://www.hdclic.com
 * @copyright Copyright (c) permanent, HDClic
 * @license   Addons PrestaShop license limitation
 *
 * NOTICE OF LICENSE
 *
 * Don't use this module on several shops. The license provided by PrestaShop Addons
 * for all its modules is valid only once for a single shop.
 */

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_3_6_0()
{
	Configuration::updateValue('prestablog_blocsearch_actif', 0);
	Configuration::updateValue('prestablog_nb_list_linkprod', 5);

	$sort_bloc_left = unserialize(Configuration::get('prestablog_sbl'));
	$sort_bloc_left[] = 'blocSearch';
	$sort_bloc_left = serialize($sort_bloc_left);
	Configuration::updateValue('prestablog_sbl', $sort_bloc_left, false, null, (int)Tools::getValue('id_shop'));
	Configuration::updateValue('prestablog_sbl', $sort_bloc_left);

	Tools::clearCache();

	return true;
}
