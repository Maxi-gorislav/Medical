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

function upgrade_module_3_5()
{
	$list_fields = Db::getInstance()->executeS('SHOW FIELDS FROM `'._DB_PREFIX_.'prestablog_news_lang`');
	if (is_array($list_fields))
	{
		foreach ($list_fields as $k => $field)
			$list_fields[$k] = $field['Field'];
		if (!in_array('read', $list_fields))
			if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute(
				'ALTER TABLE `'._DB_PREFIX_.'prestablog_news_lang` ADD `read` int(10) unsigned NOT null DEFAULT \'0\';'))
				return false;
	}

	if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'prestablog_subblock`
		(	`id_prestablog_subblock` int(10) unsigned NOT null auto_increment,
			`hook_name` varchar(255) NOT null, `id_shop` int(10) unsigned NOT null,
			`select_type` int(10) unsigned NOT null,
			`nb_list` int(10) unsigned NOT null,
			`random` tinyint(1) NOT null DEFAULT \'0\',
			`position` int(10) unsigned NOT null,
			`title_length` int(10) unsigned NOT null,
			`intro_length` int(10) unsigned NOT null,
			`use_date_start` tinyint(1) NOT null DEFAULT \'0\',
			`date_start` datetime NOT null,
			`use_date_stop` tinyint(1) NOT null DEFAULT \'0\',
			`date_stop` datetime NOT null,
			`actif` tinyint(1) NOT null DEFAULT \'1\',
			PRIMARY KEY (`id_prestablog_subblock`)
		) DEFAULT CHARSET=utf8;'))
		return false;

	if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'prestablog_subblock_lang`
		(	`id_prestablog_subblock` int(10) unsigned NOT null,
			`id_lang` int(10) unsigned NOT null,
			`title` varchar(255) NOT null,
			PRIMARY KEY (`id_prestablog_subblock`, `id_lang`)
		) DEFAULT CHARSET=utf8;'))
		return false;

	if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'prestablog_subblock_categories`
		(	`id_prestablog_subblock` int(10) unsigned NOT null,
			`categorie` int(10) unsigned NOT null,
			PRIMARY KEY (`id_prestablog_subblock`, `categorie`)
		) DEFAULT CHARSET=utf8;'))
		return false;

	return true;
}
