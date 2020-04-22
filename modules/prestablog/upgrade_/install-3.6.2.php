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

function upgrade_module_3_6_2()
{
	$context = Context::getContext();

	if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'prestablog_categorie_group` (
			`id_prestablog_categorie` int(10) unsigned NOT NULL,
			`id_group` int(10) unsigned NOT NULL
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;'))
		return false;

	$groups = Group::getGroups((int)$context->language->id);
	$categories = CategoriesClass::getListeNoArbo();
	if (count($groups) > 0 && count($categories) > 0)
	{
		$sql_values = 'VALUES ';
		foreach ($groups as $group)
		{
			foreach ($categories as $categorie)
				$sql_values .= '('.(int)$categorie['id_prestablog_categorie'].', '.(int)$group['id_group'].'),';
		}
		$sql_values = rtrim($sql_values, ',');

		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
		INSERT INTO `'._DB_PREFIX_.'prestablog_categorie_group`
			(`id_prestablog_categorie`, `id_group`)
			'.$sql_values))
			return false;
	}

	Tools::clearCache();

	return true;
}
