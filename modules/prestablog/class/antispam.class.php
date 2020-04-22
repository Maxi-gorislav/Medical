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

class	AntiSpamClass extends ObjectModel
{
	public $id;
	public $id_shop = 1;
	public $question;
	public $reply;
	public $checksum;
	public $actif = 1;

	protected $table = 'prestablog_antispam';
	protected $identifier = 'id_prestablog_antispam';

	protected static $table_static = 'prestablog_antispam';
	protected static $identifier_static = 'id_prestablog_antispam';

	public static $definition = array(
		'table' => 'prestablog_antispam',
		'primary' => 'id_prestablog_antispam',
		'multilang' => true,
		'fields' => array(
			'id_shop' =>		array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
			'actif' =>			array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),

			'question' =>		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'required' => true, 'size' => 255),
			'reply' =>			array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'required' => true, 'size' => 255),
			'checksum' =>		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 32),
		)
	);

	public function copyFromPost()
	{
		$object = $this;
		$table = $this->table;

		/* Classical fields */
		foreach ($_POST as $key => $value)
			if (array_key_exists($key, $object) && $key != 'id_'.$table)
			{
				/* Do not take care of password field if empty */
				if ($key == 'passwd' && Tools::getValue('id_'.$table) && empty($value))
					continue;
				/* Automatically encrypt password in MD5 */
				if ($key == 'passwd' && !empty($value))
					$value = Tools::encrypt($value);
				$object->{$key} = Tools::getValue($key);
			}

		/* Multilingual fields */
		$rules = call_user_func(array(get_class($object), 'getValidationRules'), get_class($object));
		if (count($rules['validateLang']))
		{
			$languages = Language::getLanguages(false);
			foreach ($languages as $language)
				foreach (array_keys($rules['validateLang']) as $field)
					if (Tools::getIsset($field.'_'.(int)$language['id_lang']))
						$object->{$field}[(int)$language['id_lang']] = Tools::getValue($field.'_'.(int)$language['id_lang']);
		}
	}

	public function registerTablesBdd()
	{
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
		CREATE TABLE `'._DB_PREFIX_.$this->table.'` (
		`'.$this->identifier.'` int(10) unsigned NOT null auto_increment,
		`id_shop` int(10) unsigned NOT null,
		`actif` tinyint(1) NOT null DEFAULT \'1\',
		PRIMARY KEY (`'.$this->identifier.'`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'))
			return false;

		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
		CREATE TABLE `'._DB_PREFIX_.$this->table.'_lang` (
		`'.$this->identifier.'` int(10) unsigned NOT null,
		`id_lang` int(10) unsigned NOT null,
		`question` varchar(255) NOT null,
		`reply` varchar(255) NOT null,
		`checksum` varchar(32) NOT null,
		PRIMARY KEY (`'.$this->identifier.'`, `id_lang`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'))
			return false;

		return true;
	}

	public function deleteTablesBdd()
	{
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.$this->table))
			return false;
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.$this->table.'_lang`'))
			return false;

		return true;
	}

	public static function getListe($id_lang = null, $only_actif = 0)
	{
		$context = Context::getContext();
		$multiboutique_filtre = 'AND c.`id_shop` = '.(int)$context->shop->id;

		$actif = '';
		if ($only_actif)
			$actif = 'AND c.`actif` = 1';

		if (empty($id_lang))
			$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		$liste = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT	c.*, cl.*
		FROM `'._DB_PREFIX_.self::$table_static.'` c
		JOIN `'._DB_PREFIX_.self::$table_static.'_lang` cl ON (c.`'.self::$identifier_static.'` = cl.`'.self::$identifier_static.'`)
		WHERE cl.id_lang = '.(int)$id_lang.'
		'.$multiboutique_filtre.'
		'.$actif);

		return $liste;
	}

	public static function getAntiSpamByChecksum($checksum)
	{
		$context = Context::getContext();
		$multiboutique_filtre = 'AND c.`id_shop` = '.(int)$context->shop->id;

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->GetRow('
				SELECT	c.*, cl.*
				FROM `'._DB_PREFIX_.self::$table_static.'` c
				JOIN `'._DB_PREFIX_.self::$table_static.'_lang` cl ON (c.`'.self::$identifier_static.'` = cl.`'.self::$identifier_static.'`)
				WHERE cl.checksum = \''.pSQL(Trim($checksum)).'\'
				'.$multiboutique_filtre.';');
	}

	public function changeEtat($field)
	{
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
			UPDATE `'._DB_PREFIX_.$this->table.'` SET `'.pSQL($field).'`=CASE `'.pSQL($field).'` WHEN 1 THEN 0 WHEN 0 THEN 1 END
			WHERE `'.$this->identifier.'`='.(int)$this->id))
			return false;
		return true;
	}

	public function reloadChecksum()
	{
		$liste = array();
		$liste = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT * FROM `'._DB_PREFIX_.self::$table_static.'_lang`');

		foreach ($liste as $antispam)
			Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
				UPDATE `'._DB_PREFIX_.$this->table.'_lang`
					SET `checksum` = \''.md5((int)$antispam[$this->identifier].(int)$antispam['id_lang']._COOKIE_KEY_.$antispam['question']).'\'
				WHERE `'.$this->identifier.'`='.(int)$antispam[$this->identifier].'
					AND `id_lang`='.(int)$antispam['id_lang']);
	}
}
