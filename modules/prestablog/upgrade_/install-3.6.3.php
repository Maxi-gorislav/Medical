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

function upgrade_module_3_6_3($module)
{
	if (!Configuration::get('prestablog_search_filtrecat'))
		Configuration::updateValue('prestablog_search_filtrecat', 1);
	if (!Configuration::get('prestablog_commentfb_actif'))
		Configuration::updateValue('prestablog_commentfb_actif', 0);
	if (!Configuration::get('prestablog_commentfb_nombre'))
		Configuration::updateValue('prestablog_commentfb_nombre', 5);
	if (!Configuration::get('prestablog_commentfb_apiId'))
		Configuration::updateValue('prestablog_commentfb_apiId', '');
	if (!Configuration::get('prestablog_commentfb_modosId'))
		Configuration::updateValue('prestablog_commentfb_modosId', '');

	/* update for PS 1.6 only */
	if (!Configuration::get('prestablog_id_meta')
		&& $module->isPSVersion('>=', '1.6'))
	{
		/* insertion du meta pour prestablog */
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
		INSERT INTO `'._DB_PREFIX_.'meta`
			(`page`, `configurable`)
		VALUES
			(\'module-prestablog-blog\', 1)'
			))
			return false;

		$id_meta = (int)Db::getInstance()->Insert_ID();

		Configuration::updateValue('prestablog_id_meta', (int)$id_meta);

		/* instertion des meta_lang */
		foreach (array_keys(Shop::getShops()) as $id_shop)
		{
			foreach (Language::getLanguages() as $lang)
			{
				if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
				INSERT INTO `'._DB_PREFIX_.'meta_lang`
					(`id_meta`, `id_shop`, `id_lang`, `title`, `description`, `url_rewrite`)
				VALUES
					('.(int)$id_meta.', '.(int)$id_shop.', '.(int)$lang['id_lang'].', \'PrestaBlog\', \'Blog\', \'module-blog\')'
					))
					return false;
			}
		}

		/* insertion du meta dans tous les themes */
		foreach (Theme::getThemes() as $theme)
		{
			if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
			INSERT INTO `'._DB_PREFIX_.'theme_meta`
				(`id_theme`, `id_meta`, `left_column`, `right_column`)
			VALUES
				('.(int)$theme->id.', '.(int)$id_meta.', 1, 1)'
				))
				return false;
		}
	}

	/* installation de la liaison news dans news */
	if (!Configuration::get('prestablog_nb_car_min_linknews'))
		Configuration::updateValue('prestablog_nb_car_min_linknews', 2);
	if (!Configuration::get('prestablog_nb_list_linknews'))
		Configuration::updateValue('prestablog_nb_list_linknews', 5);

	if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
		CREATE TABLE `'._DB_PREFIX_.'prestablog_news_newslink` (
		`id_prestablog_news_newslink` int(10) unsigned NOT null auto_increment,
		`id_prestablog_news` int(10) unsigned NOT null,
		`id_prestablog_newslink` int(10) unsigned NOT null,
		PRIMARY KEY (`id_prestablog_news_newslink`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'))
			return false;

	if (!$module->registerAdminTab())
		return false;

	if ($module->isPSVersion('>=', '1.6'))
	{
		if (!$module->registerHook('displayBackOfficeHeader'))
			return false;
	}

	Tools::clearCache();

	return true;
}
