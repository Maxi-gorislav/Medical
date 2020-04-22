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
class CategoriesClass extends ObjectModel
{
	public $id;
	public $id_shop = 1;
	public $title;
	public $meta_title;
	public $meta_description;
	public $meta_keywords;
	public $link_rewrite;
	public $description;
	public $actif = 1;
	public $parent;
	public $image_presente = false;
	public $position = 0;
	public $group = array();

	protected $table = 'prestablog_categorie';
	protected $identifier = 'id_prestablog_categorie';

	protected static $table_static = 'prestablog_categorie';
	protected static $identifier_static = 'id_prestablog_categorie';

	public static $definition = array(
		'table' => 'prestablog_categorie',
		'primary' => 'id_prestablog_categorie',
		'multilang' => true,
		'fields' => array(
			'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
			'actif' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'parent' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'position' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),

			'title' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'size' => 255),
			'meta_title' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'size' => 255),
			'meta_description' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'size' => 255),
			'meta_keywords' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'size' => 255),
			'link_rewrite' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isLinkRewrite', 'size' => 255),

			'description' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString'),
		)
	);

	public function copyFromPost()
	{
		$object = $this;
		$table = $this->table;

		/* Classical fields */
		foreach ($_POST as $key => $value)
			if (array_key_exists($key, $object) && $key != 'id_' . $table) {
				/* Do not take care of password field if empty */
				if ($key == 'passwd' && Tools::getValue('id_' . $table) && empty($value))
					continue;
				/* Automatically encrypt password in MD5 */
				if ($key == 'passwd' && !empty($value))
					$value = Tools::encrypt($value);
				$object->{$key} = Tools::getValue($key);
			}

		/* Multilingual fields */
		$rules = call_user_func(array(get_class($object), 'getValidationRules'), get_class($object));
		if (count($rules['validateLang'])) {
			$languages = Language::getLanguages(false);
			foreach ($languages as $language) {
				foreach (array_keys($rules['validateLang']) as $field) {
					if (Tools::getIsset($field . '_' . (int)$language['id_lang']))
						$object->{$field}[(int)$language['id_lang']] = Tools::getValue($field . '_' . (int)$language['id_lang']);
				}
			}
		}
	}

	public function registerTablesBdd()
	{
		$context = Context::getContext();

		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
		CREATE TABLE `' . _DB_PREFIX_ . $this->table . '` (
		`' . $this->identifier . '` int(10) unsigned NOT null auto_increment,
		`id_shop` int(10) unsigned NOT null,
		`actif` tinyint(1) NOT null DEFAULT \'1\',
		`parent` int(10) unsigned NOT null,
		`position` int(10) unsigned NOT null,
		PRIMARY KEY (`' . $this->identifier . '`))
		ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8')
		)
			return false;

		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
		CREATE TABLE `' . _DB_PREFIX_ . $this->table . '_lang` (
		`' . $this->identifier . '` int(10) unsigned NOT null,
		`id_lang` int(10) unsigned NOT null,
		`title` varchar(255) NOT null,
		`meta_description` text NOT null,
		`meta_keywords` text NOT null,
		`meta_title` text NOT null,
		`link_rewrite` text NOT null,
		`description` text,
		PRIMARY KEY (`' . $this->identifier . '`, `id_lang`))
		ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8')
		)
			return false;

		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
		CREATE TABLE `' . _DB_PREFIX_ . $this->table . '_group` (
			`' . $this->identifier . '` int(10) unsigned NOT NULL,
			`id_group` int(10) unsigned NOT NULL
		) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;')
		)
			return false;

		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
		INSERT INTO `' . _DB_PREFIX_ . $this->table . '`
			(`' . $this->identifier . '`, `id_shop`, `actif`, `parent`)
		VALUES
			(1,1,1,0)'
		)
		)
			return false;

		$groups = Group::getGroups((int)$context->language->id);
		if (count($groups) > 0) {
			$sql_values = 'VALUES ';
			foreach ($groups as $group)
				$sql_values .= '(1, ' . (int)$group['id_group'] . '),';
			$sql_values = rtrim($sql_values, ',');

			if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
			INSERT INTO `' . _DB_PREFIX_ . $this->table . '_group`
				(`' . $this->identifier . '`, `id_group`)
				' . $sql_values)
			)
				return false;
		}

		$langues = Language::getLanguages(true);
		if (count($langues) > 0) {
			$title = Array(
				1 => 'Mode',
			);

			$meta_description = Array(
				1 => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.',
			);

			$meta_keywords = Array(
				1 => 'mode',
			);

			$meta_title = Array(
				1 => 'Tout sur la mode',
			);

			$link_rewrite = Array(
				1 => 'mode',
			);

			$description = Array(
				1 => '<h1><span>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</span></h1>
				<p><span>Sed eget pretium lectus, sed bibendum augue. In sollicitudin convallis blandit.
				Curabitur venenatis ut elit quis tempus. Sed eget sem pretium, consequat ante sit amet, accumsan nunc.
				Vestibulum adipiscing dapibus tortor, eget lacinia neque dapibus auctor. Integer a dui in tellus dignissim
				dictum eu eu orci. Integer venenatis libero a justo rutrum, eu facilisis libero aliquam.
				Praesent sit amet elit nunc. Vestibulum aliquam turpis tellus, sed sagittis velit suscipit molestie.
				Nullam eleifend convallis sodales. Aenean est magna, molestie quis viverra vitae,
				hendrerit nec dui.</span></p>',
			);

			$sql_values = 'VALUES ';
			for ($i = 1; $i <= 1; $i++) {
				foreach ($langues as $value) {
					$sql_values .= '
						(
							' . $i . ',
							' . $value['id_lang'] . ',
							\'' . pSQL($title[$i]) . '\',
							\'' . pSQL($meta_description[$i]) . '\',
							\'' . pSQL($meta_keywords[$i]) . '\',
							\'' . pSQL($meta_title[$i]) . '\',
							\'' . pSQL($link_rewrite[$i]) . '\',
							\'' . pSQL($description[$i]) . '\'
						),';
				}
			}
			$sql_values = rtrim($sql_values, ',');
			if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
				INSERT INTO `' . _DB_PREFIX_ . $this->table . '_lang`
					(
						`' . $this->identifier . '`,
						`id_lang`,
						`title`,
						`meta_description`,
						`meta_keywords`,
						`meta_title`,
						`link_rewrite`,
						`description`
					)
				' . $sql_values)
			)
				return false;
		}
		return true;
	}

	public function deleteTablesBdd()
	{
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $this->table))
			return false;
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $this->table . '_lang`'))
			return false;
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $this->table . '_group`'))
			return false;

		return true;
	}

	public static function isCustomerPermissionGroups($categories_news)
	{
		$context = Context::getContext();

		$customer = new Customer((int)$context->customer->id);

		$sql_cat_perm = 'SELECT id_prestablog_categorie
			FROM ' . _DB_PREFIX_ . 'prestablog_categorie_group
			WHERE id_group IN (' . implode(',', array_map('intval', $customer->getGroups())) . ')';

		foreach (Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql_cat_perm) as $value) {
			if (in_array((int)$value['id_prestablog_categorie'], $categories_news))
				return true;
		}

		return false;
	}

	public static function getGroupsFromCategorie($categorie)
	{
		$return1 = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT	DISTINCT `id_group`
		FROM `' . _DB_PREFIX_ . self::$table_static . '_group`
		WHERE `id_prestablog_categorie` = ' . (int)$categorie);

		$return2 = array();
		foreach ($return1 as $value)
			$return2[] = $value['id_group'];

		return $return2;
	}

	public static function injectGroupsInCategorie($active_group, $categorie)
	{
		self::delAllGroupsCategorie((int)$categorie);

		if (count($active_group) > 0) {
			foreach ($active_group as $group)
				Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
					INSERT INTO `' . _DB_PREFIX_ . self::$table_static . '_group`
						(`id_prestablog_categorie`, `id_group`)
					VALUES (' . (int)$categorie . ', ' . (int)$group . ')');
		}

		return true;
	}

	public static function delAllGroupsCategorie($categorie)
	{
		Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
		DELETE FROM `' . _DB_PREFIX_ . self::$table_static . '_group`	WHERE `id_prestablog_categorie`=' . (int)$categorie);
	}

	public static function isCategoriesExist($id_lang = null, $name)
	{
		if (empty($id_lang))
			$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		$context = Context::getContext();
		$multiboutique_filtre = 'AND c.`id_shop` = ' . (int)$context->shop->id;

		$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->GetRow('
		SELECT   cl.`' . self::$identifier_static . '`
		FROM `' . _DB_PREFIX_ . self::$table_static . '_lang` cl
		LEFT JOIN `' . _DB_PREFIX_ . self::$table_static . '` c ON (c.`' . self::$identifier_static . '` = cl.`' . self::$identifier_static . '`)
		WHERE cl.`title` = \'' . pSQL($name) . '\'
		' . $multiboutique_filtre . '
		AND cl.`id_lang` = ' . (int)$id_lang);

		if (count($row) > 0)
			return $row[self::$identifier_static];
		else
			return false;
	}

	public static function getCategoriesName($id_lang = null, $id_prestablog_categorie)
	{
		if (empty($id_lang))
			$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->GetRow('
		SELECT   cl.title
		FROM `' . _DB_PREFIX_ . self::$table_static . '_lang` cl
		WHERE cl.id_lang = ' . (int)$id_lang . '
		AND cl.`' . self::$identifier_static . '` = ' . (int)$id_prestablog_categorie);

		if (count($row) > 0)
			return $row['title'];
		else
			return false;
	}

	public static function getCategoriesMetaTitle($id_lang = null, $id_prestablog_categorie)
	{
		if (empty($id_lang))
			$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->GetRow('
		SELECT   cl.meta_title
		FROM `' . _DB_PREFIX_ . self::$table_static . '_lang` cl
		WHERE cl.id_lang = ' . (int)$id_lang . '
		AND cl.`' . self::$identifier_static . '` = ' . (int)$id_prestablog_categorie);

		if (count($row) > 0)
			return $row['meta_title'];
		else
			return false;
	}

	public static function getListeNoLang($only_actif = 0, $parent = 0)
	{
		$module = new PrestaBlog();
		$actif = '';
		if ($only_actif)
			$actif = 'AND c.`actif` = 1';

		$context = Context::getContext();
		$multiboutique_filtre = 'AND c.`id_shop` = ' . (int)$context->shop->id;

		$liste = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT   c.*, cl.*
		FROM `' . _DB_PREFIX_ . self::$table_static . '` c
		JOIN `' . _DB_PREFIX_ . self::$table_static . '_lang` cl ON (c.`' . self::$identifier_static . '` = cl.`' . self::$identifier_static . '`)
		WHERE c.`' . self::$identifier_static . '` > 0
		AND c.`parent` = ' . (int)$parent . '
		' . $multiboutique_filtre . '
		' . $actif . '
		ORDER BY c.`position`');

		if (count($liste) > 0) {
			foreach ($liste as $key => $value) {
				if (file_exists($module->module_path . '/views/img/' . Configuration::get($module->name . '_theme') .
					'/up-img/c/' . $value[self::$identifier_static] . '.jpg'))
					$liste[$key]['image_presente'] = 1;

				$liste[$key]['children'] = self::getListe($only_actif, (int)$value['id_prestablog_categorie']);
			}
		}

		return $liste;
	}

	public static function getNombreNewsDansCat($categorie = 0)
	{
		$context = Context::getContext();
		$nombre_news = Db::getInstance(_PS_USE_SQL_SLAVE_)->GetRow('
			SELECT COUNT(DISTINCT nl.`id_prestablog_news`) AS `value`
			FROM `' . _DB_PREFIX_ . 'prestablog_news_lang` as nl
			LEFT JOIN `' . _DB_PREFIX_ . 'prestablog_correspondancecategorie` as co
				ON (co.news = nl.id_prestablog_news)
			LEFT JOIN `' . _DB_PREFIX_ . 'prestablog_categorie` as c
				ON (co.categorie = c.id_prestablog_categorie)
			LEFT JOIN `' . _DB_PREFIX_ . 'prestablog_news` as n
				ON (nl.id_prestablog_news = n.id_prestablog_news)
			WHERE n.`actif` = 1
				AND nl.`id_lang` = ' . (int)$context->cookie->id_lang . '
				AND nl.`actif_langue` = 1
				AND TIMESTAMP(n.`date`) <= \'' . Date('Y/m/d H:i:s') . '\'
				AND   c.`actif` = 1
				AND   c.id_prestablog_categorie = ' . (int)$categorie);

		return (int)$nombre_news['value'];
	}

	public static function getNombreNewsRecursifCat($categorie = 0)
	{
		$news = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT   c.*
			FROM `' . _DB_PREFIX_ . self::$table_static . '` c
			WHERE c.`parent` = ' . (int)$categorie);

		$rcount = (int)self::getNombreNewsDansCat((int)$categorie);

		if (count($news) > 0) {
			foreach ($news as $value)
				$rcount = $rcount + (int)self::getNombreNewsRecursifCat((int)$value['id_prestablog_categorie']);
		}

		return $rcount;
	}

	public static function getListeNoArbo($only_actif = 0, $id_lang = 0)
	{
		$module = new PrestaBlog();
		$actif = '';
		if ($only_actif)
			$actif = 'AND c.`actif` = 1';
		$langue = '';
		if ($id_lang)
			$langue = 'AND cl.`id_lang` = ' . (int)$id_lang;

		$context = Context::getContext();
		$multiboutique_filtre = 'AND c.`id_shop` = ' . (int)$context->shop->id;

		$liste = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT   c.*, cl.*, c.`' . self::$identifier_static . '` as `id`
		FROM `' . _DB_PREFIX_ . self::$table_static . '` c
		JOIN `' . _DB_PREFIX_ . self::$table_static . '_lang` cl ON (c.`' . self::$identifier_static . '` = cl.`' . self::$identifier_static . '`)
		WHERE c.`' . self::$identifier_static . '` > 0
		' . $multiboutique_filtre . '
		' . $actif . '
		' . $langue . '
		ORDER BY c.`position`');

		if (count($liste) > 0) {
			foreach ($liste as $key => $value)
				if (file_exists($module->module_path . '/views/img/' . Configuration::get($module->name . '_theme') .
					'/up-img/c/' . (int)$value[self::$identifier_static] . '.jpg'))
					$liste[$key]['image_presente'] = 1;
		}

		return $liste;
	}

	public static function getBreadcrumb($branche)
	{
		$context = Context::getContext();
		$breadcrumb = '';

		$branche = preg_split('/\./', $branche);

		foreach ($branche as $value) {
			$categorie = new CategoriesClass((int)$value, (int)$context->cookie->id_lang);
			$breadcrumb .= '&nbsp;>&nbsp;<a href="' . PrestaBlog::prestablogUrl(
					array(
						'c' => $categorie->id,
						'titre' => ($categorie->link_rewrite != '' ? $categorie->link_rewrite : $categorie->title),
					)
				) . '">' . $categorie->title . '</a>';
		}

		return ltrim($breadcrumb, '&nbsp;>&nbsp');
	}

	public static function getBranche($idc, $branche = '')
	{
		$context = Context::getContext();
		$categorie = new CategoriesClass((int)$idc, (int)$context->cookie->id_lang);
		$branche = $categorie->id . ($branche ? '.' . $branche : '');

		if ((int)$categorie->parent > 0)
			$branche = self::getBranche((int)$categorie->parent, $branche);

		return $branche;
	}

	public static function getListe($id_lang = null, $only_actif = 0, $parent = 0, $branch_previous = null)
	{
		$module = new PrestaBlog();
		$actif = '';
		if ($only_actif)
			$actif = 'AND c.`actif` = 1';

		if (empty($id_lang))
			$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		$context = Context::getContext();
		$multiboutique_filtre = 'AND c.`id_shop` = ' . (int)$context->shop->id;

		$filtre_groupes = '';
		/* uniquement sur front office */
		if (isset($context->employee->id) && (int)$context->employee->id > 0)
			$filtre_groupes = '';
		else {
			$customer = new Customer((int)$context->customer->id);

			$filtre_groupes = 'AND c.`id_prestablog_categorie` IN (
						SELECT id_prestablog_categorie
						FROM ' . _DB_PREFIX_ . 'prestablog_categorie_group
						WHERE id_group IN (' . implode(',', array_map('intval', $customer->getGroups())) . ')
					)';
		}
		/* /uniquement sur front office */

		$liste = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT   c.*, cl.*, c.`' . self::$identifier_static . '` as `id`,
			LEFT(cl.`title`, ' . (int)Configuration::get('prestablog_cat_title_length') . ') as title
		FROM `' . _DB_PREFIX_ . self::$table_static . '` c
		JOIN `' . _DB_PREFIX_ . self::$table_static . '_lang` cl ON (c.`' . self::$identifier_static . '` = cl.`' . self::$identifier_static . '`)
		WHERE cl.`id_lang` = ' . (int)$id_lang . '
		AND c.`parent` = ' . (int)$parent . '
		' . $multiboutique_filtre . '
		' . $actif . '
		' . $filtre_groupes . '
		ORDER BY c.`position`');

		if (count($liste) > 0) {
			foreach ($liste as $key => $value) {
				if ($branch_previous)
					$liste[$key]['branch'] = $branch_previous . '.' . (int)$value['id_prestablog_categorie'];
				else
					$liste[$key]['branch'] = (int)$value['id_prestablog_categorie'];

				$liste[$key]['description_crop'] = trim(strip_tags(html_entity_decode($value['description'])));

				$liste[$key]['nombre_news'] = (int)self::getNombreNewsDansCat((int)$value['id_prestablog_categorie']);

				$liste[$key]['nombre_news_recursif'] = (int)self::getNombreNewsRecursifCat((int)$value['id_prestablog_categorie']);

				if (Tools::strlen(trim($liste[$key]['description_crop'])) > (int)Configuration::get('prestablog_cat_intro_length'))
					$liste[$key]['description_crop'] = PrestaBlog::cleanCut($liste[$key]['description_crop'],
						(int)Configuration::get('prestablog_cat_intro_length'), ' [...]');

				if (file_exists($module->module_path . '/views/img/' . Configuration::get($module->name . '_theme') .
					'/up-img/c/' . $value[self::$identifier_static] . '.jpg'))
					$liste[$key]['image_presente'] = 1;

				$liste[$key]['children'] = self::getListe($id_lang, $only_actif, (int)$value['id_prestablog_categorie'], $liste[$key]['branch']);
			}
		}

		return $liste;
	}

	public static function getLastPosition()
	{
		$context = Context::getContext();
		$sql = Db::getInstance()->getValue('
			SELECT MAX(`position`) + 1
			FROM `' . _DB_PREFIX_ . self::$table_static . '`
			WHERE  `id_shop` = ' . (int)$context->shop->id);

		return $sql;
	}

	public static function updatePosition($id_prestablog_categorie, $parent = 0, $position = 0)
	{
		if ((int)$id_prestablog_categorie > 0)
			Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
				UPDATE `' . _DB_PREFIX_ . self::$table_static . '`
					SET `position`=' . (int)$position . ', `parent`=' . (int)$parent . '
				WHERE `' . self::$identifier_static . '`=' . (int)$id_prestablog_categorie);
	}

	public function changeEtat($field)
	{
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
			UPDATE `' . _DB_PREFIX_ . $this->table . '` SET `' . pSQL($field) . '`=CASE `' . pSQL($field) . '` WHEN 1 THEN 0 WHEN 0 THEN 1 END
			WHERE `' . $this->identifier . '`=' . (int)$this->id)
		)
			return false;
		return true;
	}

	public static function isCategorieValide($categorie)
	{
		$context = Context::getContext();
		$multiboutique_filtre = 'AND c.`id_shop` = ' . (int)$context->shop->id;

		$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT   *
		FROM `' . _DB_PREFIX_ . self::$table_static . '` c
		WHERE c.`' . self::$identifier_static . '` = ' . (int)$categorie . '
		AND c.`actif`=1
		' . $multiboutique_filtre);

		if (count($row) > 0)
			return true;
		else
			return false;
	}

	public function displaySelectArboCategories($liste, $parent = 0, $decalage = 0, $label = 'Top level',
												$name_select = 'parent', $on_change = '', $selected = 0, $branche_disabled = false, $branch_previous = null)
	{
		$html_out = '';
		if ($decalage == 0) {
			$html_out .= '<select name="' . $name_select . '" ' . ($on_change != '' ? 'onchange="' . $on_change . '"' : '') . '>';
			$html_out .= ' <option value="0" ' . ((int)$parent == 0 ? 'selected' : '') . ' style="font-style:italic;font-weight:bold;">' . $label . '</option>';
		}

		foreach ($liste as $value) {
			if ((int)$value[self::$identifier_static] == (int)Tools::getValue('idC')
				|| (strpos($value['branch'], $branch_previous) !== false && strpos($value['branch'], Tools::getValue('idC') . '.') !== false)
				|| strpos($value['branch'], Tools::getValue('idC') . '.') !== false
			)
				$branche_disabled = true;
			else
				$branche_disabled = false;

			if (!Tools::getValue('idC'))
				$branche_disabled = false;

			$html_out .= '<option ' . ($branche_disabled ? 'disabled' : 'value="' . $value[self::$identifier_static] . '"') . ' ' .
				((int)$value[self::$identifier_static] == (int)$parent || (int)$value[self::$identifier_static] == (int)$selected ? 'selected' : '') . '>';

			for ($i = 0; $i <= $decalage; $i++)
				$html_out .= '&nbsp;&nbsp;&nbsp;';
			$html_out .= $value['title'] . '</option>';

			if (count($value['children']) > 0)
				$html_out .= $this->displaySelectArboCategories($value['children'], $parent, $decalage + 1, $label,
					$name_select, $on_change, $selected, $branche_disabled, $value['branch']);
		}

		if ($decalage == 0)
			$html_out .= '</select>';
		return $html_out;
	}
}
