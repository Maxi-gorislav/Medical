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

function upgrade_module_3_5_5($module)
{
	if ($module->isPSVersion('>=', '1.6'))
		Configuration::updateValue('prestablog_catnews_tree', 1);

	$languages = Language::getLanguages(true);
	foreach ($languages as $language)
	{
		Configuration::updateValue('prestablog_descpageblog_'.$language['id_lang'], '');
		Configuration::updateValue('prestablog_h1pageblog_'.$language['id_lang'], '');
	}

	Configuration::updateValue('prestablog_sitemap_actif', 0);
	Configuration::updateValue('prestablog_sitemap_articles', 1);
	Configuration::updateValue('prestablog_sitemap_categories', 1);
	Configuration::updateValue('prestablog_sitemap_limit', 5000);
	Configuration::updateValue('prestablog_sitemap_older', 12);
	Configuration::updateValue('prestablog_sitemap_token', $module->genererMDP(8));

	Configuration::updateValue('prestablog_s_facebook', 1);
	Configuration::updateValue('prestablog_s_twitter', 1);
	Configuration::updateValue('prestablog_s_googleplus', 1);
	Configuration::updateValue('prestablog_s_linkedin', 1);
	Configuration::updateValue('prestablog_s_email', 1);
	Configuration::updateValue('prestablog_s_pinterest', 0);
	Configuration::updateValue('prestablog_s_pocket', 0);
	Configuration::updateValue('prestablog_s_tumblr', 0);
	Configuration::updateValue('prestablog_s_reddit', 0);
	Configuration::updateValue('prestablog_s_hackernews', 0);

	if (!$module->registerHook('displayPrestaBlogList'))
		return false;

	$list_fields = Db::getInstance()->executeS('SHOW FIELDS FROM `'._DB_PREFIX_.'prestablog_categorie`');
	if (is_array($list_fields))
	{
		foreach ($list_fields as $k => $field)
			$list_fields[$k] = $field['Field'];
		if (!in_array('position', $list_fields))
		{
			if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
				ALTER TABLE `'._DB_PREFIX_.'prestablog_categorie` ADD `position` INT NOT null;'))
				return false;
		}
	}

	$list_fields = Db::getInstance()->executeS('SHOW FIELDS FROM `'._DB_PREFIX_.'prestablog_subblock`');
	if (is_array($list_fields))
	{
		foreach ($list_fields as $k => $field)
			$list_fields[$k] = $field['Field'];
		if (!in_array('langues', $list_fields))
		{
			if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
				ALTER TABLE `'._DB_PREFIX_.'prestablog_subblock` ADD `langues` TEXT NOT null AFTER `id_shop`;'))
				return false;
		}
		if (!in_array('template', $list_fields))
		{
			if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
				ALTER TABLE `'._DB_PREFIX_.'prestablog_subblock` ADD `template` varchar(255) NOT null AFTER `hook_name`;'))
				return false;
		}
		if (!in_array('blog_link', $list_fields))
		{
			if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
				ALTER TABLE `'._DB_PREFIX_.'prestablog_subblock` ADD `blog_link` tinyint(1) NOT null DEFAULT \'0\' AFTER `date_stop`;'))
				return false;
		}
	}

	$list_fields = Db::getInstance()->executeS('SHOW FIELDS FROM `'._DB_PREFIX_.'prestablog_news`');
	if (is_array($list_fields))
	{
		foreach ($list_fields as $k => $field)
			$list_fields[$k] = $field['Field'];
		if (!in_array('url_redirect', $list_fields))
		{
			if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
				ALTER TABLE `'._DB_PREFIX_.'prestablog_news` ADD `url_redirect` TEXT NOT null;'))
				return false;
		}
		if (!in_array('date_modification', $list_fields))
		{
			if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
				ALTER TABLE `'._DB_PREFIX_.'prestablog_news` ADD `date_modification` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;'))
				return false;
		}
	}

	/* for all old themes
	* copy old config.xml to the new config
	* old : /themes/[theme]/config.xml
	* new : /views/config/[theme].xml
	*/
	foreach ($module->scanDirectory(_PS_MODULE_DIR_.'prestablog/themes') as $value_theme)
	{
		if (!$module->copyRecursive(_PS_MODULE_DIR_.'prestablog/themes/'.$value_theme.'/config.xml',
											_PS_MODULE_DIR_.'prestablog/views/config/'.$value_theme.'.xml'))
			return false;
	}

	/* for all old themes
	* copy img up to the new structure
	* old : /themes/[theme]/up-img/*
	* new : /views/img/[theme]/up-img/*
	*/
	foreach ($module->scanDirectory(_PS_MODULE_DIR_.'prestablog/themes') as $value_theme)
	{
		if (!$module->copyRecursive(_PS_MODULE_DIR_.'prestablog/themes/'.$value_theme.'/up-img',
											_PS_MODULE_DIR_.'prestablog/views/img/'.$value_theme.'/up-img'))
			return false;
	}

	Tools::clearCache();

	return true;
}
