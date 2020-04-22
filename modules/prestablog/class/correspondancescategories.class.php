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

class CorrespondancesCategoriesClass extends ObjectModel
{
	public $id;
	public $categorie;
	public $news;

	protected $table = 'prestablog_correspondancecategorie';
	protected $identifier = 'id_prestablog_correspondancecategorie';

	protected static $table_static = 'prestablog_correspondancecategorie';
	protected static $identifier_static = 'id_prestablog_correspondancecategorie';

	public static function getCategoriesListe($news)
	{
		$return1 = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT	`categorie`
		FROM `'._DB_PREFIX_.self::$table_static.'`
		WHERE `news` = '.(int)$news);

		$return2 = array();
		foreach ($return1 as $value)
			$return2[] = $value['categorie'];

		return $return2;
	}

	public static function getCategoriesListeName($news, $lang, $only_actif = 0)
	{
		$context = Context::getContext();

		$actif = '';
		if ($only_actif)
			$actif = 'AND c.`actif` = 1';

		$filtre_groupes = '';
		/* uniquement sur front office */
		if (isset($context->employee->id) && (int)$context->employee->id > 0)
			$filtre_groupes = '';
		else
		{
			$customer = new Customer((int)$context->customer->id);

			$filtre_groupes = 'AND cc.`categorie` IN (
						SELECT id_prestablog_categorie
						FROM '._DB_PREFIX_.'prestablog_categorie_group
						WHERE id_group IN ('.implode(',', array_map('intval', $customer->getGroups())).')
					)';
		}
		/* /uniquement sur front office */

		$return1 = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT	cl.`title`, cl.`link_rewrite`, cc.`categorie`
		FROM `'._DB_PREFIX_.self::$table_static.'` as cc
		LEFT JOIN `'._DB_PREFIX_.'prestablog_categorie` as c
			ON (cc.`categorie` = c.`id_prestablog_categorie`)
		LEFT JOIN `'._DB_PREFIX_.'prestablog_categorie_lang` as cl
			ON (cc.`categorie` = cl.`id_prestablog_categorie`)
		WHERE cc.`news` = '.(int)$news.'
			AND cl.`id_lang` = '.(int)$lang.'
			'.$actif.'
			'.$filtre_groupes.'
		ORDER BY cl.`title`');

		$return2 = array();
		foreach ($return1 as $value)
		{
			$return2[$value['categorie']]['title'] = $value['title'];
			$return2[$value['categorie']]['link_rewrite'] = ($value['link_rewrite'] != '' ? $value['link_rewrite'] : $value['title']);
		}
		return $return2;
	}

	public static function delAllCategoriesNews($news)
	{
		Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('DELETE FROM `'._DB_PREFIX_.self::$table_static.'` WHERE `news`='.(int)$news);
	}

	public static function delAllCorrespondanceNewsAfterDelCat($cat)
	{
		Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('DELETE FROM `'._DB_PREFIX_.self::$table_static.'` WHERE `categorie`='.(int)$cat);
	}

	public static function updateCategoriesNews($categories, $news)
	{
		if (count($categories) > 0)
		{
			foreach ($categories as $value)
				Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
					INSERT INTO `'._DB_PREFIX_.self::$table_static.'`
						(`categorie`, `news`)
					VALUES ('.(int)$value.', '.(int)$news.')');
		}
	}

	public function registerTablesBdd()
	{
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
		CREATE TABLE `'._DB_PREFIX_.$this->table.'` (
		`'.$this->identifier.'` int(10) unsigned NOT null auto_increment,
		`categorie` int(10) unsigned NOT null,
		`news` int(10) unsigned NOT null,
		PRIMARY KEY (`'.$this->identifier.'`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'))
			return false;

		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
		INSERT INTO `'._DB_PREFIX_.$this->table.'`
			(`'.$this->identifier.'`, `categorie`, `news`)
		VALUES
			(1, 1, 1)'))
			return false;

		return true;
	}

	public function deleteTablesBdd()
	{
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.$this->table))
			return false;

		return true;
	}
}
