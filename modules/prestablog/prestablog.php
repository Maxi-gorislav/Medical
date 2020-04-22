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

include_once(dirname(__FILE__) . '/class/news.class.php');
include_once(dirname(__FILE__) . '/class/categories.class.php');
include_once(dirname(__FILE__) . '/class/correspondancescategories.class.php');
include_once(dirname(__FILE__) . '/class/commentnews.class.php');
include_once(dirname(__FILE__) . '/class/antispam.class.php');
include_once(dirname(__FILE__) . '/class/subblocks.class.php');

class PrestaBlog extends Module
{
	/****************************/
	/******* DEMO MODE **********/
	/****************************/
	/*
	* true or false, if false, all upload files
	* and critical hoster informations are disable
	* feature @HDClic demo reserved
	*/
	protected $demo_mode = false;
	/****************************/

	// Url
	public $url_path = 'articles';

	public $html_out = '';
	public $module_path = '';
	public $mois_langue = array();
	public $rss_langue = array();

	private $checksum = '';

	protected $check_slide;
	protected $check_active;

	protected $check_comment_state = -2;

	protected $normal_image_size_width = 1024;
	protected $normal_image_size_height = 1024;

	protected $admin_crop_image_size_width = 400;
	protected $admin_crop_image_size_height = 400;

	protected $admin_thumb_image_size_width = 40;
	protected $admin_thumb_image_size_height = 40;

	protected $max_image_size = 25510464;
	protected $default_theme = 'default';

	protected $path_module_conf;

	public static function isPSVersion($compare, $version)
	{
		return version_compare(_PS_VERSION_, $version, $compare);
	}

	public static function getUrlPath()
	{
		return 'articles';
	}

	public static function getModuleDataBaseVersion()
	{
		$module = Db::getInstance()->getRow('
		SELECT `version` FROM `' . _DB_PREFIX_ . 'module`
		WHERE `name` = \'prestablog\'');
		return $module['version'];
	}

	public function __construct()
	{
		$this->name = 'prestablog';
		$this->tab = 'front_office_features';
		$this->version = '3.6.3';
		$this->author = 'HDClic';
		$this->need_instance = 0;
		$this->bootstrap = true;
		$this->module_key = '7aafe030447c17f08629e0319107b62b';

		parent::__construct();

		$this->displayName = $this->l('PrestaBlog');
		$this->description = $this->l('A module to add a blog on your web store.');

		$this->confirmUninstall = $this->l('Are you sure you want to delete this module ?');

		$this->path_module_conf = 'index.php?tab=AdminModules&configure=' . $this->name . '&token=' . Tools::getValue('token');
		$this->langue_default_store = (int)Configuration::get('PS_LANG_DEFAULT');

		$path = dirname(__FILE__);
		if (strpos(__FILE__, 'Module.php') !== false)
			$path .= '/../modules/' . $this->name;

		$this->mois_langue = array(
			1 => $this->l('January'),
			2 => $this->l('February'),
			3 => $this->l('March'),
			4 => $this->l('April'),
			5 => $this->l('May'),
			6 => $this->l('June'),
			7 => $this->l('July'),
			8 => $this->l('August'),
			9 => $this->l('September'),
			10 => $this->l('October'),
			11 => $this->l('November'),
			12 => $this->l('December')
		);

		$this->module_path = $path;

		$this->message_call_back = array(
			'Blog' => $this->l('Blog'),
			'no_result_found' => $this->l('No result found'),
			'no_result_listed' => $this->l('No result listed'),
			'total_results' => $this->l('Total results'),
			'next_results' => $this->l('Next'),
			'prev_results' => $this->l('Previous'),
			'blocRss' => $this->l('Block Rss all news'),
			'blocDateListe' => $this->l('Block date news'),
			'blocLastListe' => $this->l('Block last news'),
			'blocCatListe' => $this->l('Block categories news'),
			'blocSearch' => $this->l('Block search news'),
			'Yu3Tr9r7' => $this->l('The import XML was successfull'),
			'2yt6wEK7' => $this->l('No import selected'),
		);

		if ($this->isPSVersion('>=', '1.6'))
			$this->default_theme = 'grid-for-1-6';
		else
			$this->default_theme = 'default-1-5';

		$this->configurations = array(
			/** Thèmes et slide **************************/
			/* Thème */
			$this->name . '_theme' => $this->default_theme,
			/* Slideshow */
			$this->name . '_homenews_actif' => 0,
			$this->name . '_pageslide_actif' => 1,
			$this->name . '_homenews_limit' => 5,
			$this->name . '_slide_picture_width' => 555,
			$this->name . '_slide_picture_height' => 246,
			$this->name . '_slide_title_length' => 80,
			$this->name . '_slide_intro_length' => 160,
			/** /Thèmes et slide *************************/

			/** Blocs ************************************/
			/* Bloc derniers articles */
			$this->name . '_lastnews_limit' => 5,
			$this->name . '_lastnews_showall' => 1,
			$this->name . '_lastnews_actif' => 0,
			$this->name . '_lastnews_showintro' => 0,
			$this->name . '_lastnews_showthumb' => 1,
			$this->name . '_lastnews_title_length' => 80,
			$this->name . '_lastnews_intro_length' => 120,
			/* Bloc d'articles par date */
			$this->name . '_datenews_order' => 'desc',
			$this->name . '_datenews_showall' => 0,
			$this->name . '_datenews_actif' => 0,
			/* Bloc Rss pour tous les articles */
			$this->name . '_allnews_rss' => 0,
			$this->name . '_rss_title_length' => 80,
			$this->name . '_rss_intro_length' => 200,
			/* Bloc Search */
			$this->name . '_blocsearch_actif' => 0,
			$this->name . '_search_filtrecat' => 1,
			/* Dernières actualités en footer */
			$this->name . '_footlastnews_actif' => 0,
			$this->name . '_footlastnews_limit' => 3,
			$this->name . '_footlastnews_showall' => 1,
			$this->name . '_footlastnews_intro' => 0,
			$this->name . '_footer_title_length' => 80,
			$this->name . '_footer_intro_length' => 120,
			/* Ordre des blocs dans les colonnes */
			$this->name . '_sbr' => serialize(array(
				0 => ''
			)),
			$this->name . '_sbl' => serialize(array(
				0 => 'blocRss',
				1 => 'blocLastListe',
				2 => 'blocCatListe',
				3 => 'blocDateListe',
				4 => 'blocSearch'
			)),
			/** /Blocs ***********************************/

			/** SubBlocs *********************************/
			$this->name . '_subblocks_actif' => 0,
			/** /SubBlocs ********************************/

			/** Commentaires *****************************/
			$this->name . '_comment_actif' => 1,
			$this->name . '_comment_only_login' => 0,
			$this->name . '_comment_auto_actif' => 0,
			$this->name . '_comment_nofollow' => 1,
			$this->name . '_comment_alert_admin' => 1,
			$this->name . '_comment_admin_mail' => Configuration::get('PS_SHOP_EMAIL'),
			$this->name . '_comment_subscription' => 1,
			$this->name . '_comment_autoshow' => 1,
			/** /Commentaires ****************************/

			/** Commentaires Facebook ********************/
			$this->name . '_commentfb_actif' => 0,
			$this->name . '_commentfb_nombre' => 5,
			$this->name . '_commentfb_apiId' => '',
			$this->name . '_commentfb_modosId' => '',
			/** /Commentaires Facebook *******************/

			/** Categorie ********************************/
			/* Menu catégories dans la page du blog */
			$this->name . '_menu_cat_blog_index' => 1,
			$this->name . '_menu_cat_blog_list' => 0,
			$this->name . '_menu_cat_blog_article' => 0,
			$this->name . '_menu_cat_blog_empty' => 0,
			$this->name . '_menu_cat_home_link' => 1,
			$this->name . '_menu_cat_home_img' => 1,
			/* $this->name.'_menu_cat_blog_rss'			=> 0, */
			$this->name . '_menu_cat_blog_nbnews' => 0,
			/* Bloc de catégories d'article */
			$this->name . '_catnews_showall' => 0,
			$this->name . '_catnews_rss' => 0,
			$this->name . '_catnews_actif' => 0,
			$this->name . '_catnews_empty' => 0,
			$this->name . '_catnews_tree' => 1,
			/* page liste d'articles */
			$this->name . '_catnews_shownbnews' => 1,
			$this->name . '_catnews_showthumb' => 1,
			$this->name . '_catnews_showintro' => 1,
			/* liste des categories */
			$this->name . '_thumb_cat_width' => 150,
			$this->name . '_thumb_cat_height' => 150,
			$this->name . '_full_cat_width' => 535,
			$this->name . '_full_cat_height' => 236,
			$this->name . '_cat_title_length' => 80,
			$this->name . '_cat_intro_length' => 120,
			/** /Categorie *******************************/

			/** Globales *********************************/
			/* Configuration du rewrite */
			$this->name . '_rewrite_actif' => (int)Configuration::get('PS_REWRITING_SETTINGS'),
			/* Configuration générale du front-office */
			$this->name . '_nb_liste_page' => 5,
			$this->name . '_producttab_actif' => 1,
			$this->name . '_socials_actif' => 1,
			$this->name . '_s_facebook' => 1,
			$this->name . '_s_twitter' => 1,
			$this->name . '_s_googleplus' => 1,
			$this->name . '_s_linkedin' => 1,
			$this->name . '_s_email' => 1,
			$this->name . '_s_pinterest' => 0,
			$this->name . '_s_pocket' => 0,
			$this->name . '_s_tumblr' => 0,
			$this->name . '_s_reddit' => 0,
			$this->name . '_s_hackernews' => 0,
			$this->name . '_uniqnews_rss' => 0,
			$this->name . '_view_cat_desc' => 1,
			$this->name . '_view_cat_thumb' => 0,
			$this->name . '_view_cat_img' => 1,
			$this->name . '_view_news_img' => 0,
			/* liste d'articles */
			$this->name . '_thumb_picture_width' => 129,
			$this->name . '_thumb_picture_height' => 129,
			$this->name . '_news_title_length' => 80,
			$this->name . '_news_intro_length' => 200,
			/* Configuration globale de l'administration */
			$this->name . '_nb_car_min_linkprod' => 2,
			$this->name . '_nb_list_linkprod' => 5,
			$this->name . '_nb_car_min_linknews' => 2,
			$this->name . '_nb_list_linknews' => 5,
			$this->name . '_nb_news_pl' => 20,
			$this->name . '_nb_comments_pl' => 20,
			$this->name . '_comment_div_visible' => 0,
			/** /Globales ********************************/

			/** Outils ***********************************/
			/* Anitspam */
			$this->name . '_antispam_actif' => 0,
			/* Sitemap */
			$this->name . '_sitemap_actif' => 0,
			$this->name . '_sitemap_articles' => 1,
			$this->name . '_sitemap_categories' => 1,
			$this->name . '_sitemap_limit' => 5000,
			$this->name . '_sitemap_older' => 12,
			$this->name . '_sitemap_token' => $this->genererMDP(8),
			/* Importation depuis un XML de WordPress */
			$this->name . '_import_xml' => '',
			/** /Outils **********************************/
		);

		/* configurations liées aux langues de la boutique */
		$languages = Language::getLanguages(true);
		foreach ($languages as $language) {
			$this->configurations[$this->name . '_titlepageblog_' . $language['id_lang']] = $this->l('Blog');
			$this->configurations[$this->name . '_descpageblog_' . $language['id_lang']] = $this->l($language['iso_code']
				. ' - This blog was proposed by the PrestaBlog module which has been created through the team of H.D.Clic.');
			$this->configurations[$this->name . '_h1pageblog_' . $language['id_lang']] = '';
		}

		$this->context->smarty->assign(
			array(
				'prestablog_config' => Configuration::getMultiple(array_keys($this->configurations)),
				'md5pic' => md5(time()),
				'prestablog_theme_dir' => _MODULE_DIR_ . $this->name . '/views/',
				'prestablog_theme_upimg' => _MODULE_DIR_ . $this->name . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/',
			)
		);
	}

	private function registerHookPosition($hook_name, $position)
	{
		if ($this->registerHook($hook_name))
			$this->updatePosition((int)Hook::getIdByName($hook_name), 0, (int)$position);
		else
			return false;
		return true;
	}

	private function registerMetaAndColumnForEachThemes()
	{
		if ($this->isPSVersion('>=', '1.6')) {
			/* insertion du meta pour prestablog */
			if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
			INSERT INTO `' . _DB_PREFIX_ . 'meta`
				(`page`, `configurable`)
			VALUES
				(\'module-prestablog-blog\', 1)'
			)
			)
				return false;

			$id_meta = (int)Db::getInstance()->Insert_ID();

			if (!Configuration::get('prestablog_id_meta'))
				Configuration::updateValue('prestablog_id_meta', (int)$id_meta);

			/* instertion des meta_lang */
			foreach (array_keys(Shop::getShops()) as $id_shop) {
				foreach (Language::getLanguages() as $lang) {
					if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
					INSERT INTO `' . _DB_PREFIX_ . 'meta_lang`
						(`id_meta`, `id_shop`, `id_lang`, `title`, `description`, `url_rewrite`)
					VALUES
						(' . (int)$id_meta . ', ' . (int)$id_shop . ', ' . (int)$lang['id_lang'] . ', \'PrestaBlog\', \'Blog\', \'module-blog\')'
					)
					)
						return false;
				}
			}

			/* insertion du meta dans tous les themes */
			foreach (Theme::getThemes() as $theme) {
				if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
				INSERT INTO `' . _DB_PREFIX_ . 'theme_meta`
					(`id_theme`, `id_meta`, `left_column`, `right_column`)
				VALUES
					(' . (int)$theme->id . ', ' . (int)$id_meta . ', 1, 1)'
				)
				)
					return false;
			}
		}
		return true;
	}

	private function deleteMetaAndColumnForEachThemes($id_meta)
	{
		if ($this->isPSVersion('>=', '1.6')) {
			if ((int)$id_meta > 0) {
				if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
					DELETE FROM `' . _DB_PREFIX_ . 'meta`
					WHERE `id_meta` = ' . (int)$id_meta)
				)
					return false;
				if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
					DELETE FROM `' . _DB_PREFIX_ . 'meta_lang`
					WHERE `id_meta` = ' . (int)$id_meta)
				)
					return false;
				if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute('
					DELETE FROM `' . _DB_PREFIX_ . 'theme_meta`
					WHERE `id_meta` = ' . (int)$id_meta)
				)
					return false;

				if (!Configuration::deleteByName('prestablog_id_meta'))
					return false;
			}
		}
		return true;
	}

	public function initLangueModule($id_lang)
	{
		$this->rss_langue['id_lang'] = $id_lang;
		$this->rss_langue['channel_title'] = Configuration::get('PS_SHOP_NAME') . ' ' . $this->l('news feed');
	}

	public function registerAdminAjaxTab()
	{
		/* Prepare tab AdminPrestaBlogAjaxController */
		$tab = new Tab();
		$tab->active = 1;
		$tab->class_name = 'AdminPrestaBlogAjax';
		$tab->name = array();
		foreach (Language::getLanguages(true) as $lang)
			$tab->name[$lang['id_lang']] = 'PrestaBlogAjax';

		$tab->id_parent = (int)Tab::getCurrentTabId();
		$tab->module = $this->name;

		return $tab->add();
	}

	public function deleteAdminAjaxTab()
	{
		$id_tab = (int)Tab::getIdFromClassName('AdminPrestaBlogAjax');
		if ($id_tab) {
			$tab = new Tab($id_tab);
			return $tab->delete();
		}
	}

	public function registerAdminTab()
	{
		if (_PS_VERSION_ < '1.5')
			return true;

		$languages = Language::getLanguages(true);
		if (empty($languages))
			return false;

		/** @var TabCore $tab */
		$tab = new Tab();
		$tab->active = 1;
		$tab->class_name = 'AdminPrestaBlog';
		$tab->name = array();
		foreach ($languages as $lang)
			$tab->name[$lang['id_lang']] = 'PrestaBlog';

		$tab->id_parent = 0;
		$tab->module = 'prestablog';
		$added = $tab->add();

		// For PS 1.6 it is enough to have the main menu, for PS 1.5 we need a sub-menu.
		if ($added && _PS_VERSION_ < '1.6') {
			$tab = new Tab();
			$tab->active = 1;
			$tab->class_name = 'AdminPrestaBlogBOTabPS15';
			$tab->name = array();
			foreach ($languages as $lang)
				$tab->name[$lang['id_lang']] = $this->name;

			$tab->id_parent = (int)Tab::getIdFromClassName('AdminPrestaBlog');
			$tab->module = 'prestablog';
			$added = $tab->add();
		}

		return $added;
	}

	public function deleteAdminTab()
	{
		if (_PS_VERSION_ < '1.5')
			return true;

		foreach (array('AdminPrestaBlog', 'AdminPrestaBlogBOTabPS15') as $tab_name) {
			$id_tab = (int)Tab::getIdFromClassName($tab_name);
			if ($id_tab) {
				/** @var TabCore $tab */
				$tab = new Tab($id_tab);
				$tab->delete();
			}
		}

		return true;
	}

	private static function unlinkFile($file)
	{
		if (file_exists($file))
			return unlink($file);
	}

	private static function readDirectory($directory)
	{
		return readdir($directory);
	}

	private static function makeDirectory($directory)
	{
		return mkdir($directory);
	}

	public function install()
	{
		$this->uninstall();

		/* si multiboutique, alors activer le contexte pour installe le module */
		/* sur toutes les boutiques */
		if (Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);

		$news = new NewsClass();
		$categories = new CategoriesClass();
		$correspondances_categories = new CorrespondancesCategoriesClass();
		$comment_news = new CommentNewsClass();
		$antispam = new AntiSpamClass();
		$sub_blocks = new SubBlocksClass();

		self::unlinkFile(_PS_MODULE_DIR_ . $this->name . '/override/classes/Dispatcher.php');

		if (version_compare(_PS_VERSION_, '1.5.3.1', '<')) {
			if (self::copy(_PS_ROOT_DIR_ . '/override/classes/Dispatcher.php',
				_PS_MODULE_DIR_ . $this->name . '/backup_override/Dispatcher_' . md5(date('YmdHis')) . '.php')
			) {
				if (!self::copy(_PS_MODULE_DIR_ . $this->name . '/override_before_1531/Dispatcher.php',
					_PS_MODULE_DIR_ . $this->name . '/override/classes/Dispatcher.php')
				)
					return false;
			} else
				return false;
		}

		$this->installQuickAccess();

		if (!parent::install()
			/* ACCROCHES TEMPLATE */
			|| !$this->registerHookPosition('displayHeader', 1)
			|| !$this->registerHookPosition('displayHome', 1)
			|| !$this->registerHook('displayTop')
			|| !$this->registerHookPosition('displayRightColumn', 1)
			|| !$this->registerHookPosition('displayLeftColumn', 1)
			|| !$this->registerHook('displayFooter')
			|| !$this->registerHook('ModuleRoutes')
			|| !$this->registerHook('displayPrestaBlogList')

			/* ACCROCHES TEMPLATE PRESTASHOP 1.5 */
			|| !$this->installHookPS15()

			/*ACCROCHES TEMPLATE PRESTASHOP 1.6 */
			|| !$this->installHookPS16()

			/* CONFIGURATION & INTEGRATION BASE DE DONNEES */
			|| !$this->updateConfiguration('add')

			|| !$this->metaTitlePageBlog('add')

			/* STRUCTURE BASE DE DONNEES */
			|| !$news->registerTablesBdd()
			|| !$categories->registerTablesBdd()
			|| !$correspondances_categories->registerTablesBdd()
			|| !$comment_news->registerTablesBdd()
			|| !$antispam->registerTablesBdd()
			|| !$sub_blocks->registerTablesBdd()

			/* ADMIN CONTROLLERS */
			|| !$this->registerAdminTab()
			|| !$this->registerAdminAjaxTab()

			/* META LANG & THEME */
			|| !$this->registerMetaAndColumnForEachThemes()
		)
			return false;
		return true;
	}

	public function installHookPS15()
	{
		if ($this->isPSVersion('<', '1.6'))
			if (!$this->registerHook('displayProductTab')
				|| !$this->registerHook('displayProductTabContent')
			)
				return false;
		return true;
	}

	public function installHookPS16()
	{
		if ($this->isPSVersion('>=', '1.6'))
			if (!$this->registerHook('displayNav')
				|| !$this->registerHook('displayFooterProduct')
				|| !$this->registerHook('displayBackOfficeHeader')
			)
				return false;
		return true;
	}

	public function uninstall()
	{
		$news = new NewsClass();
		$categories = new CategoriesClass();
		$correspondances_categories = new CorrespondancesCategoriesClass();
		$comment_news = new CommentNewsClass();
		$antispam = new AntiSpamClass();
		$sub_blocks = new SubBlocksClass();

		$this->uninstallQuickAccess();

		self::unlinkFile(_PS_MODULE_DIR_ . $this->name . '/override/classes/Dispatcher.php');

		if (!parent::uninstall()

			/* META LANG & THEME */
			|| !$this->deleteMetaAndColumnForEachThemes((int)Configuration::get('prestablog_id_meta'))

			/* CONFIGURATION & INTEGRATION BASE DE DONNEES */
			|| !$this->updateConfiguration('del')

			|| !$this->metaTitlePageBlog('del')

			/* STRUCTURE BASE DE DONNEES */
			|| !$news->deleteTablesBdd()
			|| !$categories->deleteTablesBdd()
			|| !$correspondances_categories->deleteTablesBdd()
			|| !$comment_news->deleteTablesBdd()
			|| !$antispam->deleteTablesBdd()
			|| !$sub_blocks->deleteTablesBdd()

			/* ADMIN CONTROLLERS */
			|| !$this->deleteAdminTab()
			|| !$this->deleteAdminAjaxTab()

			/* SITEMAPS */
			|| !$this->deleteAllSitemap()
		)
			return false;
		return true;
	}

	public function installQuickAccess()
	{
		$qa = new QuickAccess;
		foreach (Language::getLanguages(true) as $language)
			$qa->name[(int)$language['id_lang']] = $this->displayName;
		$qa->link = 'index.php?controller=AdminModules&configure=' . $this->name . '&module_name=' . $this->name;
		$qa->new_window = 0;
		$qa->Add();
		Configuration::updateValue($this->name . '_QuickAccess', $qa->id);
		return true;
	}

	public function uninstallQuickAccess()
	{
		$qa = new QuickAccess((int)Configuration::get($this->name . '_QuickAccess'));
		$qa->delete();
		Configuration::deleteByName($this->name . '_QuickAccess');
		return true;
	}

	private function deleteAllSitemap()
	{
		$shops = Shop::getShops();
		foreach (array_keys($shops) as $key_shop)
			$this->deleteSitemapFromShop((int)$key_shop);

		return true;
	}

	private function updateConfiguration($action)
	{
		switch ($action) {
			case 'add' :
				$shops = Shop::getShops();
				foreach (array_keys($shops) as $key_shop) {
					foreach ($this->configurations as $configuration_key => $configuration_value)
						Configuration::updateValue($configuration_key, $configuration_value, false, null, $key_shop);
				}
				foreach ($this->configurations as $configuration_key => $configuration_value)
					Configuration::updateValue($configuration_key, $configuration_value);
				break;
			case 'del' :
				foreach ($this->configurations as $configuration_key => $configuration_value)
					Configuration::deleteByName($configuration_key);
				break;
		}
		return true;
	}

	private function checkConfiguration()
	{
		foreach ($this->configurations as $configuration_key => $configuration_value) {
			if (!Configuration::getIdByName($configuration_key, null, (int)$this->context->shop->id))
				Configuration::updateValue($configuration_key, $configuration_value, false, null, (int)$this->context->shop->id);
			if (!Configuration::getIdByName($configuration_key))
				Configuration::updateValue($configuration_key, $configuration_value);
		}
	}

	private function metaTitlePageBlog($action)
	{
		switch ($action) {
			case 'add' :
				$languages = Language::getLanguages(true);
				foreach ($languages as $language) {
					Configuration::updateValue($this->name . '_titlepageblog_' . $language['id_lang'],
						$this->configurations[$this->name . '_titlepageblog_' . $language['id_lang']]);
					Configuration::updateValue($this->name . '_descpageblog_' . $language['id_lang'],
						$this->configurations[$this->name . '_descpageblog_' . $language['id_lang']]);
					Configuration::updateValue($this->name . '_h1pageblog_' . $language['id_lang'],
						$this->configurations[$this->name . '_h1pageblog_' . $language['id_lang']]);
				}
				break;
			case 'del' :
				$languages = Language::getLanguages();
				foreach ($languages as $language) {
					Configuration::deleteByName($this->name . '_titlepageblog_' . $language['id_lang']);
					Configuration::deleteByName($this->name . '_descpageblog_' . $language['id_lang']);
					Configuration::deleteByName($this->name . '_h1pageblog_' . $language['id_lang']);
				}
				break;
		}
		return true;
	}

	private function postForm()
	{
		$errors = array();
		$post_en_cours = false;
		$config_theme = $this->getConfigXmlTheme(Configuration::get($this->name . '_theme'));
		$languages = Language::getLanguages();

		$this->check_slide = 0;
		$this->check_active = 0;

		if (Tools::getValue('submitFiltreNews')) {
			if (Tools::getValue('slide')) $this->check_slide = 1;
			else $this->check_slide = 0;
			if (Tools::getValue('activeNews')) $this->check_active = 1;
			else $this->check_active = 0;
		} else {
			if (Tools::getValue('slideget') == 1) $this->check_slide = 1;
			else $this->check_slide = 0;
			if (Tools::getValue('activeget') == 1) $this->check_active = 1;
			else $this->check_active = 0;
		}

		if (Tools::getValue('submitFiltreComment'))
			$this->check_comment_state = Tools::getValue('activeComment');
		else {
			if (Tools::getValue('activeCommentget')) $this->check_comment_state = Tools::getValue('activeCommentget');
			else $this->check_comment_state = -2;
		}

		$this->path_module_conf .= '&activeget=' . $this->check_active . '&slideget=' . $this->check_slide . '&activeCommentget=' . $this->check_comment_state;

		if (Tools::isSubmit('deleteNews') && Tools::getValue('idN')) {
			$post_en_cours = true;
			$news = new NewsClass((int)Tools::getValue('idN'));
			if (!$news->delete())
				$errors[] = $this->l('An error occurred while delete news.');
			else {
				$this->deleteAllImagesThemes((int)$news->id);
				CorrespondancesCategoriesClass::delAllCategoriesNews((int)$news->id);
				Tools::redirectAdmin($this->path_module_conf . '&newsListe');
			}
		} elseif (Tools::isSubmit('deleteCat') && Tools::getValue('idC')) {
			$post_en_cours = true;
			$categorie = new CategoriesClass((int)Tools::getValue('idC'));
			if (!$categorie->delete())
				$errors[] = $this->l('An error occurred while delete categorie.');
			else {
				$this->deleteAllImagesThemesCat((int)$categorie->id);
				CorrespondancesCategoriesClass::delAllCorrespondanceNewsAfterDelCat((int)$categorie->id);
				SubBlocksClass::delAllCorrespondanceAfterDelCat((int)$categorie->id);
				Tools::redirectAdmin($this->path_module_conf . '&catListe');
			}
		} elseif (Tools::isSubmit('deleteAntiSpam') && Tools::getValue('idAS')) {
			$post_en_cours = true;
			$antispam = new AntiSpamClass((int)Tools::getValue('idAS'));
			if (!$antispam->delete())
				$errors[] = $this->l('An error occurred while delete antispam question.');
			else
				Tools::redirectAdmin($this->path_module_conf . '&configAntiSpam');
		} elseif (Tools::isSubmit('etatNews') && Tools::getValue('idN')) {
			$post_en_cours = true;
			$news = new NewsClass((int)Tools::getValue('idN'));
			if (!$news->changeEtat('actif'))
				$errors[] = $this->l('An error occurred while change status of news.');
			else
				Tools::redirectAdmin($this->path_module_conf . '&newsListe');
		} elseif (Tools::isSubmit('slideNews') && Tools::getValue('idN')) {
			$post_en_cours = true;
			$news = new NewsClass((int)Tools::getValue('idN'));
			if (!$news->changeEtat('slide'))
				$errors[] = $this->l('An error occurred while change status of slide.');
			else
				Tools::redirectAdmin($this->path_module_conf . '&newsListe');
		} elseif (Tools::isSubmit('etatCat') && Tools::getValue('idC')) {
			$post_en_cours = true;
			$categories = new CategoriesClass((int)Tools::getValue('idC'));
			if (!$categories->changeEtat('actif'))
				$errors[] = Tools::displayError('An error occurred while change status object.') . ' <b>' . mysql_error() . '</b>';
			else
				Tools::redirectAdmin($this->path_module_conf . '&catListe');
		} elseif (Tools::isSubmit('etatAntiSpam') && Tools::getValue('idAS')) {
			$post_en_cours = true;
			$antispam = new AntiSpamClass((int)Tools::getValue('idAS'));
			if (!$antispam->changeEtat('actif'))
				$errors[] = $this->l('An error occurred while change status of antispam question.');
			else
				Tools::redirectAdmin($this->path_module_conf . '&configAntiSpam');
		} elseif (Tools::isSubmit('submitAddNews')) {
			$post_en_cours = true;

			if (!count(Tools::getValue('languesup')))
				$errors[] = $this->l('You must activate at least one language');
			else {
				foreach ($languages as $language) {
					if (!Tools::getValue('title_' . $language['id_lang']) && in_array($language['id_lang'], Tools::getValue('languesup')))
						$errors[] = '<img src="' . _PS_IMG_ . 'l/' . $language['id_lang'] . '.jpg" /> ' . $this->l('The title must be specified');
					if (!Tools::getValue('link_rewrite_' . $language['id_lang']) && in_array($language['id_lang'], Tools::getValue('languesup')))
						$errors[] = '<img src="' . _PS_IMG_ . 'l/' . $language['id_lang'] . '.jpg" /> ' . $this->l('The url rewrite must be specified');

					$summary = Tools::getValue('paragraph_' . $language['id_lang']);
					$content = Tools::getValue('content_' . $language['id_lang']);

					if (!$summary && !$content && in_array($language['id_lang'], Tools::getValue('languesup'))) {
						$errors[] = '<img src="' . _PS_IMG_ . 'l/' . $language['id_lang'] . '.jpg" /> '
							. $this->l('The content or introduction must be specified');
					}
				}
			}

			if (!count($errors)) {
				$news = new NewsClass();
				$news->id_shop = (int)$this->context->shop->id;
				$news->copyFromPost();
				$news->langues = serialize(Tools::getValue('languesup'));
				if (!$news->add())
					$errors[] = $this->l('An error occurred while add object.');

				NewsClass::removeAllProductsLinkNews((int)$news->id);
				if (Tools::getValue('productsLink'))
					foreach (Tools::getValue('productsLink') as $product_link)
						NewsClass::updateProductLinkNews((int)$news->id, (int)$product_link);

				NewsClass::removeAllArticlesLinkNews((int)$news->id);
				if (Tools::getValue('articlesLink'))
					foreach (Tools::getValue('articlesLink') as $article_link)
						NewsClass::updateArticleLinkNews((int)$news->id, (int)$article_link);

				$news->razEtatLangue((int)$news->id);
				foreach ($languages as $language) {
					if (in_array($language['id_lang'], Tools::getValue('languesup')))
						$news->changeActiveLangue((int)$news->id, (int)$language['id_lang']);
				}
				if (!$this->demo_mode)
					if ($_FILES['homepage_logo']['name']) {
						if (!$this->uploadImage($_FILES['homepage_logo'], $news->id, $this->normal_image_size_width,
							$this->normal_image_size_height)
						)
							$errors[] = $this->l('An error occurred while upload image.');
						else {
							foreach (self::scanListeThemes() as $value_theme) {
								$config_theme = $this->getConfigXmlTheme($value_theme);
								$this->imageResize(dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/' . $news->id . '.jpg',
									dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/admincrop_' . $news->id . '.jpg',
									(int)$this->admin_crop_image_size_width,
									(int)$this->admin_crop_image_size_height);

								$this->autocropImage(
									$news->id . '.jpg',
									dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/',
									dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/',
									(int)$this->admin_thumb_image_size_width,
									(int)$this->admin_thumb_image_size_height,
									'adminth_',
									null);

								$config_theme_array = PrestaBlog::objectToArray($config_theme);
								foreach ($config_theme_array['images'] as $key_theme_array => $value_theme_array) {
									$this->autocropImage(
										$news->id . '.jpg',
										dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/',
										dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/',
										(int)$value_theme_array['width'],
										(int)$value_theme_array['height'],
										$key_theme_array . '_',
										null);
								}
							}
						}
					}

				if (!count($errors)) {
					if (!Tools::getValue('categories'))
						CorrespondancesCategoriesClass::delAllCategoriesNews($news->id);
					else {
						CorrespondancesCategoriesClass::delAllCategoriesNews($news->id);
						CorrespondancesCategoriesClass::updateCategoriesNews(Tools::getValue('categories'), $news->id);
					}
					Tools::redirectAdmin($this->path_module_conf . '&newsListe');
				}
			}
		} elseif (Tools::isSubmit('submitAddCat')) {
			$post_en_cours = true;

			if (!Tools::getValue('title_' . $this->langue_default_store))
				$errors[] = '<img src="' . _PS_IMG_ . 'l/' . $this->langue_default_store . '.jpg" /> ' . $this->l('The title must be specified');

			$categories = new CategoriesClass();
			$categories->id_shop = (int)$this->context->shop->id;
			$categories->copyFromPost();
			$categories->position = (int)$categories->getLastPosition();

			if (!count($errors)) {
				if (!$categories->add())
					$errors[] = $this->l('An error occurred while add object.');
				else {
					if (!CategoriesClass::injectGroupsInCategorie(Tools::getValue('groupBox'), (int)$categories->id))
						$errors[] = $this->l('An error occurred while update object.') . ' - ' . $this->l('Groups');

					if (!$this->demo_mode) {
						if ($_FILES['imageCategory']['name']) {
							if (!$this->uploadImage($_FILES['imageCategory'], $categories->id, $this->normal_image_size_width,
								$this->normal_image_size_height, 'c')
							)
								$errors[] = $this->l('An error occurred while upload image.');
							else {
								foreach (self::scanListeThemes() as $value_theme) {
									$this->imageResize(dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/c/' . $categories->id . '.jpg',
										dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/c/admincrop_' . $categories->id . '.jpg',
										(int)$this->admin_crop_image_size_width,
										(int)$this->admin_crop_image_size_height);

									$this->autocropImage(
										$categories->id . '.jpg',
										dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/c/',
										dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/c/',
										(int)$this->admin_thumb_image_size_width,
										(int)$this->admin_thumb_image_size_height,
										'adminth_',
										null);

									$config_theme_array = PrestaBlog::objectToArray($config_theme);
									foreach ($config_theme_array['categories'] as $key_theme_array => $value_theme_array) {
										$this->autocropImage(
											$categories->id . '.jpg',
											dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/c/',
											dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/c/',
											(int)$value_theme_array['width'],
											(int)$value_theme_array['height'],
											$key_theme_array . '_',
											null);
									}
								}
							}
						}
					}
				}
			}

			if (!count($errors))
				Tools::redirectAdmin($this->path_module_conf . '&catListe');
		} elseif (Tools::isSubmit('submitAddAntiSpam')) {
			$post_en_cours = true;

			if (!Tools::getValue('question_' . $this->langue_default_store))
				$errors[] = '<img src="' . _PS_IMG_ . 'l/' . $this->langue_default_store . '.jpg" /> ' . $this->l('The question must be specified');
			if (!Tools::getValue('reply_' . $this->langue_default_store))
				$errors[] = '<img src="' . _PS_IMG_ . 'l/' . $this->langue_default_store . '.jpg" /> ' . $this->l('The reply must be specified');

			if (!count($errors)) {
				$antispam = new AntiSpamClass();
				$antispam->id_shop = (int)$this->context->shop->id;
				$antispam->copyFromPost();

				if (!$antispam->add())
					$errors[] = $this->l('An error occurred while add object.');
				else {
					$antispam->reloadChecksum();
					Tools::redirectAdmin($this->path_module_conf . '&configAntiSpam');
				}
			}
		} elseif (Tools::isSubmit('etatSubBlock') && Tools::getValue('idSB')) {
			$post_en_cours = true;
			$sub_blocks = new SubBlocksClass((int)Tools::getValue('idSB'));
			if (!$sub_blocks->changeEtat('actif'))
				$errors[] = $this->l('An error occurred while change status of custom articles list.');
			else
				Tools::redirectAdmin($this->path_module_conf . '&configSubBlocks');
		} elseif (Tools::isSubmit('randSubBlock') && Tools::getValue('idSB')) {
			$post_en_cours = true;
			$sub_blocks = new SubBlocksClass((int)Tools::getValue('idSB'));
			if (!$sub_blocks->changeEtat('random'))
				$errors[] = $this->l('An error occurred while change random status of custom articles list.');
			else
				Tools::redirectAdmin($this->path_module_conf . '&configSubBlocks');
		} elseif (Tools::isSubmit('blog_linkSubBlock') && Tools::getValue('idSB')) {
			$post_en_cours = true;
			$sub_blocks = new SubBlocksClass((int)Tools::getValue('idSB'));
			if (!$sub_blocks->changeEtat('blog_link'))
				$errors[] = $this->l('An error occurred while change random status of custom articles list.');
			else
				Tools::redirectAdmin($this->path_module_conf . '&configSubBlocks');
		} elseif (Tools::isSubmit('submitAddSubBlock')) {
			$post_en_cours = true;

			if (!count(Tools::getValue('languesup')))
				$errors[] = $this->l('You must activate at least one language');
			else
				foreach ($languages as $language)
					if (!Tools::getValue('title_' . $language['id_lang']) && in_array($language['id_lang'], Tools::getValue('languesup')))
						$errors[] = '<img src="' . _PS_IMG_ . 'l/' . $language['id_lang'] . '.jpg" /> ' . $this->l('The title must be specified');

			if (!count($errors)) {
				$sub_blocks = new SubBlocksClass();
				$sub_blocks->id_shop = (int)$this->context->shop->id;
				$sub_blocks->copyFromPost();
				$sub_blocks->langues = serialize(Tools::getValue('languesup'));

				$sub_blocks->position = (int)$sub_blocks->getLastPosition();

				if (!$sub_blocks->add())
					$errors[] = $this->l('An error occurred while add object.');
				else {
					if (!Tools::getValue('categories'))
						SubBlocksClass::delAllCategories($sub_blocks->id);
					else {
						SubBlocksClass::delAllCategories($sub_blocks->id);
						SubBlocksClass::updateCategories(Tools::getValue('categories'), $sub_blocks->id);
					}
					Tools::redirectAdmin($this->path_module_conf . '&configSubBlocks');
				}
			}
		} elseif (Tools::isSubmit('submitUpdateSubBlock') && Tools::getValue('idSB')) {
			$post_en_cours = true;

			if (!count(Tools::getValue('languesup')))
				$errors[] = $this->l('You must activate at least one language');
			else
				foreach ($languages as $language)
					if (!Tools::getValue('title_' . $language['id_lang']) && in_array($language['id_lang'], Tools::getValue('languesup')))
						$errors[] = '<img src="' . _PS_IMG_ . 'l/' . $language['id_lang'] . '.jpg" /> ' . $this->l('The title must be specified');

			if (!count($errors)) {
				$sub_blocks = new SubBlocksClass((int)Tools::getValue('idSB'));
				$sub_blocks->copyFromPost();
				$sub_blocks->langues = serialize(Tools::getValue('languesup'));

				if (!$sub_blocks->update())
					$errors[] = $this->l('An error occurred while update object.');

				if (!count($errors)) {
					if (!Tools::getValue('categories'))
						SubBlocksClass::delAllCategories($sub_blocks->id);
					else {
						SubBlocksClass::delAllCategories($sub_blocks->id);
						SubBlocksClass::updateCategories(Tools::getValue('categories'), $sub_blocks->id);
					}
				}
			}
		} elseif (Tools::isSubmit('deleteSubBlock') && Tools::getValue('idSB')) {
			$post_en_cours = true;
			$sub_blocks = new SubBlocksClass((int)Tools::getValue('idSB'));
			if (!$sub_blocks->delete())
				$errors[] = $this->l('An error occurred while delete object.');
			else {
				SubBlocksClass::delAllCategories((int)$sub_blocks->id);
				Tools::redirectAdmin($this->path_module_conf . '&configSubBlocks');
			}
		} elseif (Tools::isSubmit('addProductLink') && Tools::getValue('idN') && Tools::getValue('idP')) {
			$post_en_cours = true;

			NewsClass::updateProductLinkNews((int)Tools::getValue('idN'), (int)Tools::getValue('idP'));

			if (!count($errors))
				Tools::redirectAdmin($this->path_module_conf . '&editNews&idN=' . Tools::getValue('idN') . '#productLinkTable');
		} elseif (Tools::isSubmit('removeProductLink') && Tools::getValue('idN') && Tools::getValue('idP')) {
			$post_en_cours = true;

			NewsClass::removeProductLinkNews((int)Tools::getValue('idN'), (int)Tools::getValue('idP'));

			if (!count($errors))
				Tools::redirectAdmin($this->path_module_conf . '&editNews&idN=' . Tools::getValue('idN') . '#productLinkTable');
		} elseif (Tools::isSubmit('submitUpdateNews') && Tools::getValue('idN')) {
			$post_en_cours = true;

			if (!count(Tools::getValue('languesup')))
				$errors[] = $this->l('You must activate at least one language');
			else {
				foreach ($languages as $language) {
					if (!Tools::getValue('title_' . $language['id_lang']) && in_array($language['id_lang'], Tools::getValue('languesup')))
						$errors[] = '<img src="' . _PS_IMG_ . 'l/' . $language['id_lang'] . '.jpg" /> ' . $this->l('The title must be specified');
					if (!Tools::getValue('link_rewrite_' . $language['id_lang']) && in_array($language['id_lang'], Tools::getValue('languesup')))
						$errors[] = '<img src="' . _PS_IMG_ . 'l/' . $language['id_lang'] . '.jpg" /> ' . $this->l('The url rewrite must be specified');

					$summary = Tools::getValue('paragraph_' . $language['id_lang']);
					$content = Tools::getValue('content_' . $language['id_lang']);

					if (!$summary && !$content && in_array($language['id_lang'], Tools::getValue('languesup'))) {
						$errors[] = '<img src="' . _PS_IMG_ . 'l/' . $language['id_lang'] . '.jpg" /> '
							. $this->l('The content or introduction must be specified');
					}
				}
			}

			if (!Validate::isAbsoluteUrl(Tools::getValue('url_redirect')))
				$errors[] = sprintf($this->l('The field %1$s is not a correct.'), '<strong>' . $this->l('Permanent redirect url') . '</strong>');

			if (!count($errors)) {
				$news = new NewsClass((int)Tools::getValue('idN'));
				$news->id_shop = (int)$this->context->shop->id;
				$news->copyFromPost();
				$news->langues = serialize(Tools::getValue('languesup'));
				if (!$news->update())
					$errors[] = $this->l('An error occurred while update object.');

				NewsClass::removeAllProductsLinkNews((int)$news->id);
				if (Tools::getValue('productsLink'))
					foreach (Tools::getValue('productsLink') as $product_link)
						NewsClass::updateProductLinkNews((int)$news->id, (int)$product_link);

				NewsClass::removeAllArticlesLinkNews((int)$news->id);
				if (Tools::getValue('articlesLink'))
					foreach (Tools::getValue('articlesLink') as $article_link)
						NewsClass::updateArticleLinkNews((int)$news->id, (int)$article_link);

				$news->razEtatLangue((int)$news->id);
				foreach ($languages as $language)
					if (in_array($language['id_lang'], Tools::getValue('languesup')))
						$news->changeActiveLangue((int)$news->id, (int)$language['id_lang']);

				if (!$this->demo_mode) {
					if ($_FILES['homepage_logo']['name']) {
						if (!$this->uploadImage($_FILES['homepage_logo'], Tools::getValue('idN'), $this->normal_image_size_width, $this->normal_image_size_height))
							$errors[] = $this->l('An error occurred while upload image.');
						else {
							foreach (self::scanListeThemes() as $value_theme) {
								$config_theme = $this->getConfigXmlTheme($value_theme);
								$this->imageResize(dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/' . Tools::getValue('idN') . '.jpg',
									dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/admincrop_' . Tools::getValue('idN') . '.jpg',
									(int)$this->admin_crop_image_size_width,
									(int)$this->admin_crop_image_size_height);

								$this->autocropImage(
									Tools::getValue('idN') . '.jpg',
									dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/',
									dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/',
									(int)$this->admin_thumb_image_size_width,
									(int)$this->admin_thumb_image_size_height,
									'adminth_',
									null);

								$config_theme_array = PrestaBlog::objectToArray($config_theme);
								foreach ($config_theme_array['images'] as $key_theme_array => $value_theme_array) {
									$this->autocropImage(
										Tools::getValue('idN') . '.jpg',
										dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/',
										dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/',
										(int)$value_theme_array['width'],
										(int)$value_theme_array['height'],
										$key_theme_array . '_',
										null);
								}
							}
						}
					}
				}

				if (!count($errors)) {
					if (!Tools::getValue('categories'))
						CorrespondancesCategoriesClass::delAllCategoriesNews((int)Tools::getValue('idN'));
					else {
						CorrespondancesCategoriesClass::delAllCategoriesNews((int)Tools::getValue('idN'));
						CorrespondancesCategoriesClass::updateCategoriesNews(Tools::getValue('categories'), (int)Tools::getValue('idN'));
					}
				}
			}
		} elseif (Tools::isSubmit('submitUpdateCat') && Tools::getValue('idC')) {
			$post_en_cours = true;

			$categories = new CategoriesClass((int)Tools::getValue('idC'));
			$categories->id_shop = (int)$this->context->shop->id;
			$categories->copyFromPost();

			if (!CategoriesClass::injectGroupsInCategorie(Tools::getValue('groupBox'), (int)$categories->id))
				$errors[] = $this->l('An error occurred while update object.') . ' - ' . $this->l('Groups');

			if (!$categories->update())
				$errors[] = $this->l('An error occurred while update object.');

			if (!$this->demo_mode) {
				if ($_FILES['imageCategory']['name']) {
					if (!$this->uploadImage($_FILES['imageCategory'], $categories->id, $this->normal_image_size_width,
						$this->normal_image_size_height, 'c')
					)
						$errors[] = $this->l('An error occurred while upload image.');
					else {
						foreach (self::scanListeThemes() as $value_theme) {
							$this->imageResize(dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/c/' . $categories->id . '.jpg',
								dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/c/admincrop_' . $categories->id . '.jpg',
								(int)$this->admin_crop_image_size_width,
								(int)$this->admin_crop_image_size_height);

							$this->autocropImage(
								$categories->id . '.jpg',
								dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/c/',
								dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/c/',
								(int)$this->admin_thumb_image_size_width,
								(int)$this->admin_thumb_image_size_height,
								'adminth_',
								null);

							$config_theme_array = PrestaBlog::objectToArray($config_theme);
							foreach ($config_theme_array['categories'] as $key_theme_array => $value_theme_array) {
								$this->autocropImage(
									$categories->id . '.jpg',
									dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/c/',
									dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/c/',
									(int)$value_theme_array['width'],
									(int)$value_theme_array['height'],
									$key_theme_array . '_',
									null);
							}
						}
					}
				}
			}
			if (!count($errors))
				Tools::redirectAdmin($this->path_module_conf . '&catListe');
		} elseif (Tools::isSubmit('submitUpdateAntiSpam') && Tools::getValue('idAS')) {
			$post_en_cours = true;

			if (!count($errors)) {
				$antispam = new AntiSpamClass((int)Tools::getValue('idAS'));
				$antispam->id_shop = (int)$this->context->shop->id;
				$antispam->copyFromPost();

				if (!$antispam->update())
					$errors[] = $this->l('An error occurred while update object.');
				else {
					$antispam->reloadChecksum();
					Tools::redirectAdmin($this->path_module_conf . '&configAntiSpam');
				}
			}
		} elseif (Tools::isSubmit('submitUpdateComment') && Tools::getValue('idC')) {
			$post_en_cours = true;
			if (!Tools::getValue('name'))
				$errors[] = $this->l('The name must be specified');

			if (!count($errors)) {
				$comment = new CommentNewsClass((int)Tools::getValue('idC'));
				$comment->copyFromPost();

				if (!$comment->update())
					$errors[] = $this->l('An error occurred while update object.');
			}
		} elseif (Tools::isSubmit('deleteComment') && Tools::getValue('idC')) {
			$post_en_cours = true;
			$comment_news = new CommentNewsClass((int)Tools::getValue('idC'));
			if (!$comment_news->delete())
				$errors[] = $this->l('An error occurred while delete object.');
			else
				if (Tools::getValue('idN'))
					Tools::redirectAdmin($this->path_module_conf . '&editNews&idN=' . Tools::getValue('idN') . '&showComments');
				else
					Tools::redirectAdmin($this->path_module_conf . '&commentListe');
		} elseif (Tools::isSubmit('enabledComment') && Tools::getValue('idC')) {
			$post_en_cours = true;
			$comment_news = new CommentNewsClass((int)Tools::getValue('idC'));
			if (!$comment_news->changeEtat('actif', 1))
				$errors[] = $this->l('An error occurred while update object.');
			else {
				$liste_abo = CommentNewsClass::listeCommentMailAbo((int)$comment_news->news);

				if (Configuration::get($this->name . '_comment_subscription')
					&& count($liste_abo)
				) {
					$news = new NewsClass((int)$comment_news->news, $this->langue_default_store);

					foreach ($liste_abo as $value_abo) {
						Mail::Send(
							$this->langue_default_store,
							'feedback-subscribe',
							$this->l('New comment') . ' / ' . $news->title,
							array(
								'{news}' => (int)$news->id_prestablog_news,
								'{title_news}' => $news->title,
								'{url_news}' => Tools::getShopDomainSsl(true) . __PS_BASE_URI__
									. '?fc=module&module=prestablog&controller=' . self::getUrlPath() . '&id=' . (int)$comment_news->news,
								'{url_desabonnement}' => Tools::getShopDomainSsl(true) . __PS_BASE_URI__
									. '?fc=module&module=prestablog&controller=' . self::getUrlPath() . '&d=' . (int)$comment_news->news
							),
							$value_abo,
							null,
							(Configuration::get('PS_SHOP_EMAIL')),
							(Configuration::get('PS_SHOP_NAME')),
							null,
							null,
							dirname(__FILE__) . '/mails/'
						);
					}
				}

				if (Tools::getValue('idN'))
					Tools::redirectAdmin($this->path_module_conf . '&editNews&idN=' . (int)Tools::getValue('idN') . '&showComments');
				else
					Tools::redirectAdmin($this->path_module_conf . (Tools::isSubmit('commentListe') ? '&commentListe' : ''));
			}
		} elseif (Tools::isSubmit('pendingComment') && Tools::getValue('idC')) {
			$post_en_cours = true;
			$comment_news = new CommentNewsClass((int)Tools::getValue('idC'));
			if (!$comment_news->changeEtat('actif', -1))
				$errors[] = $this->l('An error occurred while update object.');
			else
				Tools::redirectAdmin($this->path_module_conf . (Tools::isSubmit('commentListe') ? '&commentListe' : ''));
		} elseif (Tools::isSubmit('disabledComment') && Tools::getValue('idC')) {
			$post_en_cours = true;
			$comment_news = new CommentNewsClass((int)Tools::getValue('idC'));
			if (!$comment_news->changeEtat('actif', 0))
				$errors[] = $this->l('An error occurred while update object.');
			else
				if (Tools::getValue('idN'))
					Tools::redirectAdmin($this->path_module_conf . '&editNews&idN=' . Tools::getValue('idN') . '&showComments');
				else
					Tools::redirectAdmin($this->path_module_conf . (Tools::isSubmit('commentListe') ? '&commentListe' : ''));
		} elseif (Tools::isSubmit('deleteImageBlog') && Tools::getValue('idN')) {
			$post_en_cours = true;
			if (!file_exists(dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/' . Tools::getValue('idN') . '.jpg'))
				$errors[] = $this->l('This action cannot be taken.');
			else
				$this->deleteAllImagesThemes(Tools::getValue('idN'));
			if (!count($errors))
				Tools::redirectAdmin($this->path_module_conf . '&editNews&idN=' . Tools::getValue('idN'));
		} elseif (Tools::isSubmit('deleteImageBlog') && Tools::getValue('idC')) {
			$post_en_cours = true;
			if (!file_exists(dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/c/' . Tools::getValue('idC') . '.jpg'))
				$errors[] = $this->l('This action cannot be taken.');
			else
				$this->deleteAllImagesThemesCat(Tools::getValue('idC'));
			if (!count($errors))
				Tools::redirectAdmin($this->path_module_conf . '&editCat&idC=' . Tools::getValue('idC'));
		} elseif (Tools::isSubmit('submitCrop') && Tools::getValue('idN')) {
			$post_en_cours = true;
			if (!file_exists(dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/admincrop_' . Tools::getValue('idN') . '.jpg'))
				$errors[] = $this->l('This action cannot be taken.');
			else {
				$config_theme = $this->getConfigXmlTheme(Configuration::get($this->name . '_theme'));
				$config_theme_array = PrestaBlog::objectToArray($config_theme);

				list($w_image_base, $h_image_base) = getimagesize(dirname(__FILE__) . '/views/img/'
					. Configuration::get($this->name . '_theme') . '/up-img/admincrop_' . Tools::getValue('idN') . '.jpg');

				$this->cropImage(
					Tools::getValue('idN') . '.jpg',
					dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/',
					dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/',
					(int)$w_image_base,
					(int)$h_image_base,
					(int)$config_theme_array['images'][Tools::getValue('pfx')]['width'],
					(int)$config_theme_array['images'][Tools::getValue('pfx')]['height'],
					(int)Tools::getValue('x'),
					(int)Tools::getValue('y'),
					(int)Tools::getValue('w'),
					(int)Tools::getValue('h'),
					Tools::getValue('pfx') . '_',
					null
				);

				if (Tools::getValue('pfx') == 'thumb')
					$this->autocropImage(
						Tools::getValue('idN') . '.jpg',
						dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/',
						dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/',
						(int)$this->admin_thumb_image_size_width,
						(int)$this->admin_thumb_image_size_height,
						'adminth_',
						null
					);

			}
			if (!count($errors))
				Tools::redirectAdmin($this->path_module_conf . '&editNews&idN=' . Tools::getValue('idN') . '&pfx=' . Tools::getValue('pfx'));
		} elseif (Tools::isSubmit('submitCrop') && Tools::getValue('idC')) {
			$post_en_cours = true;
			if (!file_exists(dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/c/admincrop_' . Tools::getValue('idC') . '.jpg'))
				$errors[] = $this->l('This action cannot be taken.');
			else {
				$config_theme = $this->getConfigXmlTheme(Configuration::get($this->name . '_theme'));
				$config_theme_array = PrestaBlog::objectToArray($config_theme);

				list($w_image_base, $h_image_base) = getimagesize(dirname(__FILE__) . '/views/img/'
					. Configuration::get($this->name . '_theme') . '/up-img/c/admincrop_' . Tools::getValue('idC') . '.jpg');

				$this->cropImage(
					Tools::getValue('idC') . '.jpg',
					dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/c/',
					dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/c/',
					(int)$w_image_base,
					(int)$h_image_base,
					(int)$config_theme_array['categories'][Tools::getValue('pfx')]['width'],
					(int)$config_theme_array['categories'][Tools::getValue('pfx')]['height'],
					(int)Tools::getValue('x'),
					(int)Tools::getValue('y'),
					(int)Tools::getValue('w'),
					(int)Tools::getValue('h'),
					Tools::getValue('pfx') . '_',
					null
				);

				if (Tools::getValue('pfx') == 'thumb')
					$this->autocropImage(
						Tools::getValue('idC') . '.jpg',
						dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/c/',
						dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/c/',
						(int)$this->admin_thumb_image_size_width,
						(int)$this->admin_thumb_image_size_height,
						'adminth_',
						null
					);
			}
			if (!count($errors))
				Tools::redirectAdmin($this->path_module_conf . '&editCat&idC=' . Tools::getValue('idC') . '&pfx=' . Tools::getValue('pfx'));
		} elseif (Tools::isSubmit('submitAntiSpamConfig')) {
			if (is_numeric(Tools::getValue($this->name . '_antispam_actif')))
				Configuration::updateValue($this->name . '_antispam_actif', (int)Tools::getValue($this->name . '_antispam_actif'));

			Tools::redirectAdmin($this->path_module_conf . '&configAntiSpam');
		} elseif (Tools::isSubmit('submitSitemapConfig')) {
			if (is_numeric(Tools::getValue($this->name . '_sitemap_actif')))
				Configuration::updateValue($this->name . '_sitemap_actif', (int)Tools::getValue($this->name . '_sitemap_actif'));
			if (is_numeric(Tools::getValue($this->name . '_sitemap_articles')))
				Configuration::updateValue($this->name . '_sitemap_articles', (int)Tools::getValue($this->name . '_sitemap_articles'));
			if (is_numeric(Tools::getValue($this->name . '_sitemap_categories')))
				Configuration::updateValue($this->name . '_sitemap_categories', (int)Tools::getValue($this->name . '_sitemap_categories'));
			if (is_numeric(Tools::getValue($this->name . '_sitemap_limit')))
				Configuration::updateValue($this->name . '_sitemap_limit', (int)Tools::getValue($this->name . '_sitemap_limit'));
			if (is_numeric(Tools::getValue($this->name . '_sitemap_older')))
				Configuration::updateValue($this->name . '_sitemap_older', (int)Tools::getValue($this->name . '_sitemap_older'));

			Tools::redirectAdmin($this->path_module_conf . '&sitemap');
		} elseif (Tools::isSubmit('submitSitemapGenerate')) {
			$this->createTheShopSitemap();
			Tools::redirectAdmin($this->path_module_conf . '&sitemap');
		} elseif (Tools::isSubmit('deleteSitemap')) {
			$this->deleteSitemapFromShop((int)$this->context->shop->id);
			Tools::redirectAdmin($this->path_module_conf . '&sitemap');
		} elseif (Tools::isSubmit('submitSubBlocksConfig')) {
			if (is_numeric(Tools::getValue($this->name . '_subblocks_actif')))
				Configuration::updateValue($this->name . '_subblocks_actif', (int)Tools::getValue($this->name . '_subblocks_actif'));

			Tools::redirectAdmin($this->path_module_conf . '&configSubBlocks');
		} elseif (Tools::isSubmit('submitTheme')) {
			Configuration::updateValue($this->name . '_theme', Tools::getValue('theme'));
			Tools::redirectAdmin($this->path_module_conf . '&configTheme');
		} elseif (Tools::isSubmit('submitWizard'))
			Tools::redirectAdmin($this->path_module_conf . '&configWizard');
		elseif (Tools::isSubmit('submitPageBlog')) {
			if (is_numeric(Tools::getValue($this->name . '_pageslide_actif')))
				Configuration::updateValue($this->name . '_pageslide_actif', (int)Tools::getValue($this->name . '_pageslide_actif'));

			$languages = Language::getLanguages(true);
			foreach ($languages as $language) {
				Configuration::updateValue($this->name . '_titlepageblog_' . $language['id_lang'],
					Tools::getValue('meta_title_' . $language['id_lang']));
				Configuration::updateValue($this->name . '_descpageblog_' . $language['id_lang'],
					Tools::getValue('meta_description_' . $language['id_lang']));
				Configuration::updateValue($this->name . '_h1pageblog_' . $language['id_lang'],
					Tools::getValue('title_h1_' . $language['id_lang']));
			}

			Tools::redirectAdmin($this->path_module_conf . '&pageBlog');
		} elseif (Tools::isSubmit('submitConfSlideNews')) {
			if (is_numeric(Tools::getValue($this->name . '_homenews_limit')))
				Configuration::updateValue($this->name . '_homenews_limit', (int)Tools::getValue($this->name . '_homenews_limit'));
			if (is_numeric(Tools::getValue($this->name . '_homenews_actif')))
				Configuration::updateValue($this->name . '_homenews_actif', (int)Tools::getValue($this->name . '_homenews_actif'));
			if (is_numeric(Tools::getValue($this->name . '_pageslide_actif')))
				Configuration::updateValue($this->name . '_pageslide_actif', (int)Tools::getValue($this->name . '_pageslide_actif'));
			if (is_numeric(Tools::getValue($this->name . '_slide_title_length')))
				Configuration::updateValue($this->name . '_slide_title_length', (int)Tools::getValue($this->name . '_slide_title_length'));
			if (is_numeric(Tools::getValue($this->name . '_slide_intro_length')))
				Configuration::updateValue($this->name . '_slide_intro_length', (int)Tools::getValue($this->name . '_slide_intro_length'));

			$xml = Tools::file_get_contents(_PS_MODULE_DIR_ . 'prestablog/views/config/' . Configuration::get($this->name . '_theme') . '.xml');
			$config_theme = $this->getConfigXmlTheme(Configuration::get($this->name . '_theme'));
			$config_theme_array = PrestaBlog::objectToArray($config_theme);

			$remplacement = '
		<thumb> <!--Image prevue pour les miniatures dans les listes -->
			<width>' . (int)$config_theme_array['images']['thumb']['width'] . '</width>
			<height>' . (int)$config_theme_array['images']['thumb']['height'] . '</height>
		</thumb>
		<slide> <!--Image prevue pour les slides -->
			<width>' . Tools::getValue('slide_picture_width') . '</width>
			<height>' . Tools::getValue('slide_picture_height') . '</height>
		</slide>
	';

			$xml = preg_replace('#<images[^>]*>.*?</images>#si', '<images>' . $remplacement . '</images>', $xml);

			if (is_writable(_PS_MODULE_DIR_ . $this->name . '/views/config/')) {
				file_put_contents(_PS_MODULE_DIR_ . 'prestablog/views/config/' . Configuration::get($this->name . '_theme') . '.xml', utf8_encode($xml));
				Tools::redirectAdmin($this->path_module_conf . '&configTheme');
			}
		} elseif (Tools::isSubmit('submitConfListeArticles')) {
			if (is_numeric(Tools::getValue($this->name . '_news_title_length')))
				Configuration::updateValue($this->name . '_news_title_length', (int)Tools::getValue($this->name . '_news_title_length'));
			if (is_numeric(Tools::getValue($this->name . '_news_intro_length')))
				Configuration::updateValue($this->name . '_news_intro_length', (int)Tools::getValue($this->name . '_news_intro_length'));

			$xml = Tools::file_get_contents(_PS_MODULE_DIR_ . 'prestablog/views/config/' . Configuration::get($this->name . '_theme') . '.xml');
			$config_theme = $this->getConfigXmlTheme(Configuration::get($this->name . '_theme'));
			$config_theme_array = PrestaBlog::objectToArray($config_theme);

			$remplacement = '
		<thumb> <!--Image prevue pour les miniatures dans les listes -->
			<width>' . Tools::getValue('thumb_picture_width') . '</width>
			<height>' . Tools::getValue('thumb_picture_height') . '</height>
		</thumb>
		<slide> <!--Image prevue pour les slides -->
			<width>' . (int)$config_theme_array['images']['slide']['width'] . '</width>
			<height>' . (int)$config_theme_array['images']['slide']['height'] . '</height>
		</slide>
	';

			$xml = preg_replace('#<images[^>]*>.*?</images>#si', '<images>' . $remplacement . '</images>', $xml);

			if (is_writable(_PS_MODULE_DIR_ . $this->name . '/views/config/')) {
				file_put_contents(_PS_MODULE_DIR_ . $this->name . '/views/config/' . Configuration::get($this->name . '_theme') . '.xml', utf8_encode($xml));
				Tools::redirectAdmin($this->path_module_conf . '&configCategories');
			}
		} elseif (Tools::isSubmit('submitConfBlocSearch')) {
			if (is_numeric(Tools::getValue($this->name . '_blocsearch_actif')))
				Configuration::updateValue($this->name . '_blocsearch_actif', (int)Tools::getValue($this->name . '_blocsearch_actif'));
			if (is_numeric(Tools::getValue($this->name . '_search_filtrecat')))
				Configuration::updateValue($this->name . '_search_filtrecat', (int)Tools::getValue($this->name . '_search_filtrecat'));

			Tools::redirectAdmin($this->path_module_conf . '&configBlocs');
		} elseif (Tools::isSubmit('submitConfBlocRss')) {
			if (is_numeric(Tools::getValue($this->name . '_allnews_rss')))
				Configuration::updateValue($this->name . '_allnews_rss', (int)Tools::getValue($this->name . '_allnews_rss'));
			if (is_numeric(Tools::getValue($this->name . '_rss_title_length')))
				Configuration::updateValue($this->name . '_rss_title_length', (int)Tools::getValue($this->name . '_rss_title_length'));
			if (is_numeric(Tools::getValue($this->name . '_rss_intro_length')))
				Configuration::updateValue($this->name . '_rss_intro_length', (int)Tools::getValue($this->name . '_rss_intro_length'));

			Tools::redirectAdmin($this->path_module_conf . '&configBlocs');
		} elseif (Tools::isSubmit('submitConfBlocLastNews')) {
			if (is_numeric(Tools::getValue($this->name . '_lastnews_limit')))
				Configuration::updateValue($this->name . '_lastnews_limit', (int)Tools::getValue($this->name . '_lastnews_limit'));
			if (is_numeric(Tools::getValue($this->name . '_lastnews_actif')))
				Configuration::updateValue($this->name . '_lastnews_actif', (int)Tools::getValue($this->name . '_lastnews_actif'));
			if (is_numeric(Tools::getValue($this->name . '_lastnews_showintro')))
				Configuration::updateValue($this->name . '_lastnews_showintro', (int)Tools::getValue($this->name . '_lastnews_showintro'));
			if (is_numeric(Tools::getValue($this->name . '_lastnews_showthumb')))
				Configuration::updateValue($this->name . '_lastnews_showthumb', (int)Tools::getValue($this->name . '_lastnews_showthumb'));
			if (is_numeric(Tools::getValue($this->name . '_lastnews_showall')))
				Configuration::updateValue($this->name . '_lastnews_showall', (int)Tools::getValue($this->name . '_lastnews_showall'));
			if (is_numeric(Tools::getValue($this->name . '_lastnews_title_length')))
				Configuration::updateValue($this->name . '_lastnews_title_length', (int)Tools::getValue($this->name . '_lastnews_title_length'));
			if (is_numeric(Tools::getValue($this->name . '_lastnews_intro_length')))
				Configuration::updateValue($this->name . '_lastnews_intro_length', (int)Tools::getValue($this->name . '_lastnews_intro_length'));

			Tools::redirectAdmin($this->path_module_conf . '&configBlocs');
		} elseif (Tools::isSubmit('submitConfFooterLastNews')) {
			if (is_numeric(Tools::getValue($this->name . '_footlastnews_limit')))
				Configuration::updateValue($this->name . '_footlastnews_limit', (int)Tools::getValue($this->name . '_footlastnews_limit'));
			if (is_numeric(Tools::getValue($this->name . '_footlastnews_actif')))
				Configuration::updateValue($this->name . '_footlastnews_actif', (int)Tools::getValue($this->name . '_footlastnews_actif'));
			if (is_numeric(Tools::getValue($this->name . '_footlastnews_showall')))
				Configuration::updateValue($this->name . '_footlastnews_showall', (int)Tools::getValue($this->name . '_footlastnews_showall'));
			if (is_numeric(Tools::getValue($this->name . '_footlastnews_intro')))
				Configuration::updateValue($this->name . '_footlastnews_intro', (int)Tools::getValue($this->name . '_footlastnews_intro'));
			if (is_numeric(Tools::getValue($this->name . '_footer_title_length')))
				Configuration::updateValue($this->name . '_footer_title_length', (int)Tools::getValue($this->name . '_footer_title_length'));
			if (is_numeric(Tools::getValue($this->name . '_footer_intro_length')))
				Configuration::updateValue($this->name . '_footer_intro_length', (int)Tools::getValue($this->name . '_footer_intro_length'));

			Tools::redirectAdmin($this->path_module_conf . '&configBlocs');
		} elseif (Tools::isSubmit('submitConfBlocDateNews')) {
			if (is_numeric(Tools::getValue($this->name . '_datenews_actif')))
				Configuration::updateValue($this->name . '_datenews_actif', (int)Tools::getValue($this->name . '_datenews_actif'));
			if (is_numeric(Tools::getValue($this->name . '_datenews_showall')))
				Configuration::updateValue($this->name . '_datenews_showall', (int)Tools::getValue($this->name . '_datenews_showall'));
			Configuration::updateValue($this->name . '_datenews_order', Tools::getValue($this->name . '_datenews_order'));

			Tools::redirectAdmin($this->path_module_conf . '&configBlocs');
		} elseif (Tools::isSubmit('submitConfBlocCatNews')) {
			if (is_numeric(Tools::getValue($this->name . '_catnews_actif')))
				Configuration::updateValue($this->name . '_catnews_actif', (int)Tools::getValue($this->name . '_catnews_actif'));
			if (is_numeric(Tools::getValue($this->name . '_catnews_showall')))
				Configuration::updateValue($this->name . '_catnews_showall', (int)Tools::getValue($this->name . '_catnews_showall'));
			if (is_numeric(Tools::getValue($this->name . '_catnews_empty')))
				Configuration::updateValue($this->name . '_catnews_empty', (int)Tools::getValue($this->name . '_catnews_empty'));
			if ($this->isPSVersion('>=', '1.6'))
				if (is_numeric(Tools::getValue($this->name . '_catnews_tree')))
					Configuration::updateValue($this->name . '_catnews_tree', (int)Tools::getValue($this->name . '_catnews_tree'));
			if (is_numeric(Tools::getValue($this->name . '_catnews_shownbnews')))
				Configuration::updateValue($this->name . '_catnews_shownbnews', (int)Tools::getValue($this->name . '_catnews_shownbnews'));
			if (is_numeric(Tools::getValue($this->name . '_catnews_showthumb')))
				Configuration::updateValue($this->name . '_catnews_showthumb', (int)Tools::getValue($this->name . '_catnews_showthumb'));
			if (is_numeric(Tools::getValue($this->name . '_catnews_showintro')))
				Configuration::updateValue($this->name . '_catnews_showintro', (int)Tools::getValue($this->name . '_catnews_showintro'));
			if (is_numeric(Tools::getValue($this->name . '_cat_title_length')))
				Configuration::updateValue($this->name . '_cat_title_length', (int)Tools::getValue($this->name . '_cat_title_length'));
			if (is_numeric(Tools::getValue($this->name . '_cat_intro_length')))
				Configuration::updateValue($this->name . '_cat_intro_length', (int)Tools::getValue($this->name . '_cat_intro_length'));
			if (is_numeric(Tools::getValue($this->name . '_catnews_rss')))
				Configuration::updateValue($this->name . '_catnews_rss', (int)Tools::getValue($this->name . '_catnews_rss'));

			Tools::redirectAdmin($this->path_module_conf . '&configCategories');
		} elseif (Tools::isSubmit('submitConfRewrite')) {
			if (is_numeric(Tools::getValue($this->name . '_rewrite_actif')))
				Configuration::updateValue($this->name . '_rewrite_actif', (int)Tools::getValue($this->name . '_rewrite_actif'));

			Tools::redirectAdmin($this->path_module_conf . '&configModule');
		} elseif (Tools::isSubmit('submitConfGobalFront')) {
			if (is_numeric(Tools::getValue($this->name . '_nb_liste_page')))
				Configuration::updateValue($this->name . '_nb_liste_page', (int)Tools::getValue($this->name . '_nb_liste_page'));
			if (is_numeric(Tools::getValue($this->name . '_producttab_actif')))
				Configuration::updateValue($this->name . '_producttab_actif', (int)Tools::getValue($this->name . '_producttab_actif'));
			if (is_numeric(Tools::getValue($this->name . '_socials_actif')))
				Configuration::updateValue($this->name . '_socials_actif', (int)Tools::getValue($this->name . '_socials_actif'));

			if (is_numeric(Tools::getValue($this->name . '_s_facebook')))
				Configuration::updateValue($this->name . '_s_facebook', (int)Tools::getValue($this->name . '_s_facebook'));
			if (is_numeric(Tools::getValue($this->name . '_s_twitter')))
				Configuration::updateValue($this->name . '_s_twitter', (int)Tools::getValue($this->name . '_s_twitter'));
			if (is_numeric(Tools::getValue($this->name . '_s_googleplus')))
				Configuration::updateValue($this->name . '_s_googleplus', (int)Tools::getValue($this->name . '_s_googleplus'));
			if (is_numeric(Tools::getValue($this->name . '_s_linkedin')))
				Configuration::updateValue($this->name . '_s_linkedin', (int)Tools::getValue($this->name . '_s_linkedin'));
			if (is_numeric(Tools::getValue($this->name . '_s_email')))
				Configuration::updateValue($this->name . '_s_email', (int)Tools::getValue($this->name . '_s_email'));
			if (is_numeric(Tools::getValue($this->name . '_s_pinterest')))
				Configuration::updateValue($this->name . '_s_pinterest', (int)Tools::getValue($this->name . '_s_pinterest'));
			if (is_numeric(Tools::getValue($this->name . '_s_pocket')))
				Configuration::updateValue($this->name . '_s_pocket', (int)Tools::getValue($this->name . '_s_pocket'));
			if (is_numeric(Tools::getValue($this->name . '_s_tumblr')))
				Configuration::updateValue($this->name . '_s_tumblr', (int)Tools::getValue($this->name . '_s_tumblr'));
			if (is_numeric(Tools::getValue($this->name . '_s_reddit')))
				Configuration::updateValue($this->name . '_s_reddit', (int)Tools::getValue($this->name . '_s_reddit'));
			if (is_numeric(Tools::getValue($this->name . '_s_hackernews')))
				Configuration::updateValue($this->name . '_s_hackernews', (int)Tools::getValue($this->name . '_s_hackernews'));

			if (is_numeric(Tools::getValue($this->name . '_uniqnews_rss')))
				Configuration::updateValue($this->name . '_uniqnews_rss', (int)Tools::getValue($this->name . '_uniqnews_rss'));
			if (is_numeric(Tools::getValue($this->name . '_view_news_img')))
				Configuration::updateValue($this->name . '_view_news_img', (int)Tools::getValue($this->name . '_view_news_img'));

			Tools::redirectAdmin($this->path_module_conf . '&configModule');
		} elseif (Tools::isSubmit('submitConfCategory')) {
			if (is_numeric(Tools::getValue($this->name . '_view_cat_desc')))
				Configuration::updateValue($this->name . '_view_cat_desc', (int)Tools::getValue($this->name . '_view_cat_desc'));
			if (is_numeric(Tools::getValue($this->name . '_view_cat_thumb')))
				Configuration::updateValue($this->name . '_view_cat_thumb', (int)Tools::getValue($this->name . '_view_cat_thumb'));
			if (is_numeric(Tools::getValue($this->name . '_view_cat_img')))
				Configuration::updateValue($this->name . '_view_cat_img', (int)Tools::getValue($this->name . '_view_cat_img'));

			$xml = Tools::file_get_contents(_PS_MODULE_DIR_ . 'prestablog/views/config/' . Configuration::get($this->name . '_theme') . '.xml');

			$remplacement = '
		<thumb> <!--Image prevue pour les miniatures dans les listes -->
			<width>' . Tools::getValue('thumb_cat_width') . '</width>
			<height>' . Tools::getValue('thumb_cat_height') . '</height>
		</thumb>
		<full> <!--Image prevue pour la description de la categorie en liste 1ere page -->
			<width>' . Tools::getValue('full_cat_width') . '</width>
			<height>' . Tools::getValue('full_cat_height') . '</height>
		</full>
	';

			$xml = preg_replace('#<categories[^>]*>.*?</categories>#si', '<categories>' . $remplacement . '</categories>', $xml);

			if (is_writable(_PS_MODULE_DIR_ . $this->name . '/views/config/')) {
				file_put_contents(_PS_MODULE_DIR_ . 'prestablog/views/config/' . Configuration::get($this->name . '_theme') . '.xml', utf8_encode($xml));
				Tools::redirectAdmin($this->path_module_conf . '&configCategories');
			}
		} elseif (Tools::isSubmit('submitConfGobalAdmin')) {
			if (is_numeric(Tools::getValue($this->name . '_nb_car_min_linkprod')))
				Configuration::updateValue($this->name . '_nb_car_min_linkprod', (int)Tools::getValue($this->name . '_nb_car_min_linkprod'));
			if (is_numeric(Tools::getValue($this->name . '_nb_list_linkprod')))
				Configuration::updateValue($this->name . '_nb_list_linkprod', (int)Tools::getValue($this->name . '_nb_list_linkprod'));
			if (is_numeric(Tools::getValue($this->name . '_nb_car_min_linknews')))
				Configuration::updateValue($this->name . '_nb_car_min_linknews', (int)Tools::getValue($this->name . '_nb_car_min_linknews'));
			if (is_numeric(Tools::getValue($this->name . '_nb_list_linknews')))
				Configuration::updateValue($this->name . '_nb_list_linknews', (int)Tools::getValue($this->name . '_nb_list_linknews'));
			if (is_numeric(Tools::getValue($this->name . '_nb_news_pl')))
				Configuration::updateValue($this->name . '_nb_news_pl', (int)Tools::getValue($this->name . '_nb_news_pl'));
			if (is_numeric(Tools::getValue($this->name . '_nb_comments_pl')))
				Configuration::updateValue($this->name . '_nb_comments_pl', (int)Tools::getValue($this->name . '_nb_comments_pl'));
			if (is_numeric(Tools::getValue($this->name . '_comment_div_visible')))
				Configuration::updateValue($this->name . '_comment_div_visible', (int)Tools::getValue($this->name . '_comment_div_visible'));

			Tools::redirectAdmin($this->path_module_conf . '&configModule');
		} elseif (Tools::isSubmit('submitConfMenuCatBlog')) {
			if (is_numeric(Tools::getValue($this->name . '_menu_cat_blog_index')))
				Configuration::updateValue($this->name . '_menu_cat_blog_index', (int)Tools::getValue($this->name . '_menu_cat_blog_index'));
			if (is_numeric(Tools::getValue($this->name . '_menu_cat_blog_list')))
				Configuration::updateValue($this->name . '_menu_cat_blog_list', (int)Tools::getValue($this->name . '_menu_cat_blog_list'));
			if (is_numeric(Tools::getValue($this->name . '_menu_cat_blog_article')))
				Configuration::updateValue($this->name . '_menu_cat_blog_article', (int)Tools::getValue($this->name . '_menu_cat_blog_article'));
			if (is_numeric(Tools::getValue($this->name . '_menu_cat_blog_empty')))
				Configuration::updateValue($this->name . '_menu_cat_blog_empty', (int)Tools::getValue($this->name . '_menu_cat_blog_empty'));
			if (is_numeric(Tools::getValue($this->name . '_menu_cat_home_link')))
				Configuration::updateValue($this->name . '_menu_cat_home_link', (int)Tools::getValue($this->name . '_menu_cat_home_link'));
			if (is_numeric(Tools::getValue($this->name . '_menu_cat_home_img')))
				Configuration::updateValue($this->name . '_menu_cat_home_img', (int)Tools::getValue($this->name . '_menu_cat_home_img'));
			if (is_numeric(Tools::getValue($this->name . '_menu_cat_blog_nbnews')))
				Configuration::updateValue($this->name . '_menu_cat_blog_nbnews', (int)Tools::getValue($this->name . '_menu_cat_blog_nbnews'));

			Tools::redirectAdmin($this->path_module_conf . '&configCategories');
		} elseif (Tools::isSubmit('submitConfComment')) {
			if (is_numeric(Tools::getValue($this->name . '_comment_actif'))) {
				Configuration::updateValue($this->name . '_comment_actif', (int)Tools::getValue($this->name . '_comment_actif'));
				if ((int)Tools::getValue($this->name . '_comment_actif') == 1)
					Configuration::updateValue($this->name . '_commentfb_actif', 0);
			}
			if (is_numeric(Tools::getValue($this->name . '_comment_only_login')))
				Configuration::updateValue($this->name . '_comment_only_login', (int)Tools::getValue($this->name . '_comment_only_login'));
			if (is_numeric(Tools::getValue($this->name . '_comment_auto_actif')))
				Configuration::updateValue($this->name . '_comment_auto_actif', (int)Tools::getValue($this->name . '_comment_auto_actif'));
			if (is_numeric(Tools::getValue($this->name . '_comment_autoshow')))
				Configuration::updateValue($this->name . '_comment_autoshow', (int)Tools::getValue($this->name . '_comment_autoshow'));
			if (is_numeric(Tools::getValue($this->name . '_comment_nofollow')))
				Configuration::updateValue($this->name . '_comment_nofollow', (int)Tools::getValue($this->name . '_comment_nofollow'));
			if (is_numeric(Tools::getValue($this->name . '_comment_alert_admin')))
				Configuration::updateValue($this->name . '_comment_alert_admin', (int)Tools::getValue($this->name . '_comment_alert_admin'));
			if (is_numeric(Tools::getValue($this->name . '_comment_subscription')))
				Configuration::updateValue($this->name . '_comment_subscription', (int)Tools::getValue($this->name . '_comment_subscription'));

			Configuration::updateValue($this->name . '_comment_admin_mail', Tools::getValue($this->name . '_comment_admin_mail'));

			Tools::redirectAdmin($this->path_module_conf . '&configComments');
		} elseif (Tools::isSubmit('submitConfCommentFB')) {
			if (is_numeric(Tools::getValue($this->name . '_commentfb_actif'))) {
				Configuration::updateValue($this->name . '_commentfb_actif', (int)Tools::getValue($this->name . '_commentfb_actif'));
				if ((int)Tools::getValue($this->name . '_commentfb_actif') == 1)
					Configuration::updateValue($this->name . '_comment_actif', 0);
			}
			if (is_numeric(Tools::getValue($this->name . '_commentfb_nombre')))
				Configuration::updateValue($this->name . '_commentfb_nombre', (int)Tools::getValue($this->name . '_commentfb_nombre'));

			if (is_numeric(Tools::getValue($this->name . '_commentfb_apiId')))
				Configuration::updateValue($this->name . '_commentfb_apiId', Tools::getValue($this->name . '_commentfb_apiId'));

			if (is_numeric(Tools::getValue($this->name . '_commentfb_modosId'))) {
				$list_fb_moderators = unserialize(Configuration::get($this->name . '_commentfb_modosId'));
				$list_fb_moderators[] = Tools::getValue($this->name . '_commentfb_modosId');
				$list_fb_moderators = array_unique($list_fb_moderators);
				Configuration::updateValue($this->name . '_commentfb_modosId', serialize($list_fb_moderators));
			}

			Tools::redirectAdmin($this->path_module_conf . '&configComments');
		} elseif (Tools::isSubmit('deleteFacebookModerator')) {
			if (is_numeric(Tools::getValue('fb_moderator_id'))) {
				$list_fb_moderators = unserialize(Configuration::get($this->name . '_commentfb_modosId'));
				$list_fb_moderators = array_diff($list_fb_moderators, array(Tools::getValue('fb_moderator_id')));
				$list_fb_moderators = array_unique($list_fb_moderators);
				Configuration::updateValue($this->name . '_commentfb_modosId', serialize($list_fb_moderators));
			}

			Tools::redirectAdmin($this->path_module_conf . '&configComments');
		} elseif (Tools::isSubmit('submitParseXml')) {
			include_once($this->module_path . '/class/Xml.php');
			$xml_string = Tools::file_get_contents(_PS_UPLOAD_DIR_ . Configuration::get($this->name . '_import_xml'));
			$xml_array = Xml::toArray(Xml::build($xml_string));

			if (count($xml_array['rss']['channel']['wp:category']) > 0) {
				$langue = (int)Tools::getValue('import_xml_langue');
				$modif_categories_parents = array();
				$categories_title = array();
				foreach ($xml_array['rss']['channel']['wp:category'] as $value) {
					$categories_title[$value['wp:category_nicename']] = $value['wp:cat_name'];
					$id_import_category = (int)CategoriesClass::isCategoriesExist((int)$langue, $value['wp:cat_name']);
					if (!$id_import_category) {
						$categorie = new CategoriesClass();
						$categorie->id_shop = (int)$this->context->shop->id;
						$categorie->title[(int)$langue] = $value['wp:cat_name'];
						$categorie->link_rewrite[(int)$langue] = PrestaBlog::prestablogFilter(Tools::link_rewrite($value['wp:cat_name']));
						$categorie->add();
						$id_import_category = $categorie->id;
					}
					if ($value['wp:category_parent'] != '') {
						$modif_categories_parents[$value['wp:category_nicename']]['id_import_category'] = $id_import_category;
						$modif_categories_parents[$value['wp:category_nicename']]['parent'] = $value['wp:category_parent'];
						$modif_categories_parents[$value['wp:category_nicename']]['title'] = $value['wp:cat_name'];
					}
				}

				foreach ($modif_categories_parents as $value) {
					$id_import_category = (int)CategoriesClass::isCategoriesExist((int)$langue, $value['title']);

					$categorie = new CategoriesClass((int)$id_import_category);
					$categorie->parent = (int)CategoriesClass::isCategoriesExist((int)$langue, $categories_title[$value['parent']]);

					$categorie->save();
				}
			}

			if (count($xml_array['rss']['channel']['item']) > 0) {
				$liste_items = array();

				if (isset($xml_array['rss']['channel']['item']['title']))
					$liste_items[0] = $xml_array['rss']['channel']['item'];
				else
					$liste_items = $xml_array['rss']['channel']['item'];

				foreach ($liste_items as $v_item) {
					if ($v_item['wp:post_type'] == 'post') {
						$post = new NewsClass();
						$post->id_shop = (int)$this->context->shop->id;
						$post->date = $v_item['wp:post_date'];
						$post->langues = serialize(
							array(
								0 => (int)Tools::getValue('import_xml_langue')
							)
						);

						if ($v_item['wp:status'] == 'publish')
							$post->actif = 1;
						else
							$post->actif = 0;

						$post->title[(int)Tools::getValue('import_xml_langue')] = $v_item['title'];
						$post->paragraph[(int)Tools::getValue('import_xml_langue')] = $v_item['excerpt:encoded'];
						$post->content[(int)Tools::getValue('import_xml_langue')] = $v_item['content:encoded'];
						$post->meta_title[(int)Tools::getValue('import_xml_langue')] = $v_item['title'];

						if (trim($v_item['wp:post_name']) == '')
							$v_item['wp:post_name'] = PrestaBlog::prestablogFilter(Tools::link_rewrite($v_item['title']));
						else
							$v_item['wp:post_name'] = PrestaBlog::prestablogFilter(Tools::link_rewrite($v_item['wp:post_name']));

						$post->link_rewrite[(int)Tools::getValue('import_xml_langue')] = $v_item['wp:post_name'];

						/** gestion des catégories et tags */
						if (isset($v_item['category']) && count($v_item['category']) > 0) {
							$import_categories = array();
							$import_categories_id = array();
							if (isset($v_item['category']['@domain'])) {
								/** gestion des catégories */
								if ($v_item['category']['@domain'] == 'category')
									$import_categories[] = $v_item['category']['@'];

								/** gestion des tags > keywords */
								if ($v_item['category']['@domain'] == 'post_tag')
									$key_words = $v_item['category']['@'];
							} else {
								/** gestion des catégories */
								if (count($v_item['category']) > 0) {
									foreach ($v_item['category'] as $v_category)
										if ($v_category['@domain'] == 'category')
											$import_categories[] = $v_category['@'];
									$import_categories = array_unique($import_categories);
								}

								/** gestion des tags > keywords */
								$import_tags = array();
								if (count($v_item['category']) > 0) {
									foreach ($v_item['category'] as $v_tag)
										if ($v_tag['@domain'] == 'post_tag')
											$import_tags[] = $v_tag['@'];
									$import_tags = array_unique($import_tags);
								}
								$key_words = '';
								if (count($import_tags) > 0)
									foreach ($import_tags as $v_import_tag)
										$key_words .= $v_import_tag . ', ';
								$key_words = rtrim($key_words, ', ');
							}

							if (count($import_categories) > 0) {
								foreach ($import_categories as $v_import_categorie) {
									if ($id_import_category = CategoriesClass::isCategoriesExist((int)Tools::getValue('import_xml_langue'),
										$v_import_categorie)
									)
										$import_categories_id[] = $id_import_category;
									else {
										$categorie = new CategoriesClass();
										$categorie->id_shop = (int)$this->context->shop->id;
										$categorie->title[(int)Tools::getValue('import_xml_langue')] = $v_import_categorie;
										$categorie->link_rewrite[(int)Tools::getValue('import_xml_langue')] =
											PrestaBlog::prestablogFilter(Tools::link_rewrite($v_import_categorie));
										$categorie->add();
										$import_categories_id[] = $categorie->id;
									}
								}
							}

							$post->meta_keywords[(int)Tools::getValue('import_xml_langue')] = Tools::substr($key_words, 0, 254);
						}

						$post->add();
						if ($post->id) {
							$post->razEtatLangue((int)$post->id);
							$post->changeActiveLangue((int)$post->id, (int)Tools::getValue('import_xml_langue'));

							/** gestion des commentaires */
							if (isset($v_item['wp:comment']) && count($v_item['wp:comment']) > O) {
								$comment = new CommentNewsClass();
								if (isset($v_item['wp:comment']['wp:comment_author'])) {
									$comment->news = $post->id;

									$v_item['wp:comment']['wp:comment_author'] = Tools::substr($v_item['wp:comment']['wp:comment_author'], 0, 254);
									$comment->name = (trim($v_item['wp:comment']['wp:comment_author']) == '' ? $this->l('Nobody') : $v_item['wp:comment']['wp:comment_author']);
									if (Validate::isUrlOrEmpty($v_item['wp:comment']['wp:comment_author_url']))
										$comment->url = $v_item['wp:comment']['wp:comment_author_url'];

									$comment->comment = $v_item['wp:comment']['wp:comment_content'];
									$comment->date = $v_item['wp:comment']['wp:comment_date'];

									if ((int)$v_item['wp:comment']['wp:comment_approved'] == 1)
										$comment->actif = 1;
									else
										$comment->actif = 0;

									$comment->add();
								} else {
									foreach ($v_item['wp:comment'] as $v_comment) {
										$comment = new CommentNewsClass();

										$comment->news = $post->id;

										$v_comment['wp:comment_author'] = Tools::substr($v_comment['wp:comment_author'], 0, 254);
										$comment->name = (trim($v_comment['wp:comment_author']) == '' ? $this->l('Nobody') : $v_comment['wp:comment_author']);
										if (Validate::isUrlOrEmpty($v_comment['wp:comment_author_url']))
											$comment->url = $v_comment['wp:comment_author_url'];

										$comment->comment = $v_comment['wp:comment_content'];
										$comment->date = $v_comment['wp:comment_date'];

										if ((int)$v_comment['wp:comment_approved'] == 1)
											$comment->actif = 1;
										else
											$comment->actif = 0;

										$comment->add();
									}
								}
							}
							/** liaison des catégories aux articles */
							if (count($import_categories_id) > 0)
								CorrespondancesCategoriesClass::updateCategoriesNews($import_categories_id, $post->id);
						}
					}
				}
			} else
				$errors[] = $this->l('No items to import');

			if (!count($errors)) {
				self::unlinkFile(_PS_UPLOAD_DIR_ . Configuration::get($this->name . '_import_xml'));
				Configuration::updateValue($this->name . '_import_xml', null);
				Tools::redirectAdmin($this->path_module_conf . '&import&feedback=Yu3Tr9r7');
			}
		} elseif (Tools::isSubmit('submitImportXml')) {
			if (!$this->demo_mode) {
				if (isset($_FILES[$this->name . '_import_xml']) && is_uploaded_file($_FILES[$this->name . '_import_xml']['tmp_name'])) {
					if ($_FILES[$this->name . '_import_xml']['size'] > (Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1024 * 1024))
						$errors[] = sprintf(
							$this->l('The file is too large. Maximum size allowed is: %1$d kB. The file you\'re trying to upload is: %2$d kB.'),
							(Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1024),
							number_format(($_FILES[$this->name . '_import_xml']['size'] / 1024), 2, '.', '')
						);
					else {
						do $uniqid = sha1(microtime());
						while (file_exists(_PS_UPLOAD_DIR_ . $uniqid));
						if (!self::copy($_FILES[$this->name . '_import_xml']['tmp_name'], _PS_UPLOAD_DIR_ . $uniqid))
							$errors[] = $this->l('File copy failed');

						self::unlinkFile($_FILES[$this->name . '_import_xml']['tmp_name']);
						self::unlinkFile(_PS_UPLOAD_DIR_ . Configuration::get($this->name . '_import_xml'));
						Configuration::updateValue($this->name . '_import_xml', $uniqid);
					}
				} else
					Tools::redirectAdmin($this->path_module_conf . '&import&feedback=2yt6wEK7');
			}

			if (!count($errors))
				Tools::redirectAdmin($this->path_module_conf . '&import');
		}

		if ($post_en_cours) {
			if (count($errors) > 0)
				$this->html_out .= $this->displayError(implode('<br />', $errors));
			else
				$this->html_out .= $this->displayConfirmation($this->l('Settings updated successfully'));
		}
	}

	public function displayError($error)
	{
		if ($this->isPSVersion('>=', '1.6')) {
			$output = '	<div class="bootstrap">
								<div class="alert alert-danger">
									<button class="close" data-dismiss="alert" type="button">×</button>
									<strong>' . $this->l('Error') . '</strong><br>
									' . $error . '
								‌</div>
							</div>';
		} else {
			$output = '	<div class="error">
							‌	<span style="float:right">
							‌			<a id="hideError" href="#"><img src="../img/admin/close.png" alt="X"></a>
								</span>
								<strong>' . $this->l('Error') . '</strong><br>
								' . $error . '
							</div>';
		}

		$this->error = true;
		return $output;
	}

	public function displayWarning($warn)
	{
		if ($this->isPSVersion('>=', '1.6'))
			$output = '	<div class="bootstrap">
								<div class="alert alert-warning">
									<button class="close" data-dismiss="alert" type="button">×</button>
									<strong>' . $this->l('Warning') . '</strong><br/>
									' . $warn . '
								‌</div>
							</div>';
		else
			$output = '	<div class="warn">
								<span style="float:right">
									<a id="hideWarn" href="">
										<img src="../img/admin/close.png" alt="X">
									</a>
								</span>
								<strong>' . $this->l('Warning') . '</strong><br/>
								' . $warn . '
							</div>';

		return $output;
	}

	public function displayInfo($info)
	{
		if ($this->isPSVersion('>=', '1.6'))
			$output = '	<div class="bootstrap">
								<div class="alert alert-info">
									<strong>' . $this->l('Information') . '</strong><br/>
									' . $info . '
								‌</div>
							</div>';
		else
			$output = '	<div class="hint" style="display:block;">
								<strong>' . $this->l('Information') . '</strong><br/>
								' . $info . '
							</div>';

		return $output;
	}

	private function moduleDatepicker($class, $time)
	{
		$return = '';
		if ($time)
			$return = '
			var dateObj = new Date();
			var hours = dateObj.getHours();
			var mins = dateObj.getMinutes();
			var secs = dateObj.getSeconds();
			if (hours < 10) { hours = "0" + hours; }
			if (mins < 10) { mins = "0" + mins; }
			if (secs < 10) { secs = "0" + secs; }
			var time = " "+hours+":"+mins+":"+secs;';
		$return .= '
		$(function() {
			$("#' . Tools::htmlentitiesUTF8($class) . '").datepicker({
				prevText:"",
				nextText:"",
				dateFormat:"yy-mm-dd"' . ($time ? '+time' : '') . '});
		});';

		return '<script type="text/javascript">' . $return . '</script>';
	}

	public function checkPresenceFoldersCritiques()
	{
		$errors = array();
		$success = array();

		if (!is_dir(_PS_MODULE_DIR_ . $this->name . '/mails/en')) {
			$errors[] = $this->l('No existing the module\'s default "en" mails folder.');
			if (!Tools::ZipExtract(_PS_MODULE_DIR_ . $this->name . '/lost/mails/en.zip', _PS_MODULE_DIR_ . $this->name . '/mails/'))
				$errors[] = $this->l('Error extract the module\'s default "en" mails folder.');
			else
				$success[] = $this->l('Restore the module\'s default "en" mails folder successfull.');
		}

		if (Configuration::get($this->name . '_sitemap_actif')) {
			if (!is_dir(_PS_MODULE_DIR_ . $this->name . '/sitemap/' . (int)$this->context->shop->id)) {
				$errors[] = sprintf($this->l('No existing the sitemap folder for %1$s'), $this->context->shop->name);
				if (!self::makeDirectory(_PS_MODULE_DIR_ . $this->name . '/sitemap/' . (int)$this->context->shop->id))
					$errors[] = sprintf($this->l('Error creating the sitemap folder for %1$s.'), $this->context->shop->name);
				else
					$success[] = sprintf($this->l('Creating sitemap folder for %1$s successfull'), $this->context->shop->name);
			}
			if (!is_writable(_PS_MODULE_DIR_ . $this->name . '/sitemap/' . (int)$this->context->shop->id))
				$errors[] = sprintf($this->l('The folder %1$s not have the write permissions.'), '<strong>' . _PS_MODULE_DIR_
					. $this->name . '/sitemap/' . (int)$this->context->shop->id . '</strong>');

			if (count($errors) > 0) {
				$this->html_out = $this->displayError(implode('<br />', $errors));
				if (count($success) > 0)
					$this->html_out .= $this->displayConfirmation(implode('<br />', $success));
				$this->html_out .= '<a href="' . $this->path_module_conf . '" class="button"><img src="../modules/' . $this->name
					. '/views/img/refresh.png" />&nbsp;' . $this->l('Refresh this page to enter again on the configuration of module.') . '</a>';

				return $this->html_out;
			}
		}
	}

	public function getContent()
	{
		$this->backoffice_content = true;

		if (Tools::version_compare($this->version, self::getModuleDataBaseVersion(), '>')) {
			if (file_exists(_PS_MODULE_DIR_ . $this->name . '/config.xml'))
				self::unlinkFile(_PS_MODULE_DIR_ . $this->name . '/config.xml');

			foreach (glob(_PS_MODULE_DIR_ . $this->name . '/config_[a-z][a-z].{xml}', GLOB_BRACE) as $config_module_file) {
				if (!is_dir($config_module_file))
					self::unlinkFile($config_module_file);
			}

			Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules'));
		}

		if ($error_critique = $this->checkPresenceFoldersCritiques())
			return $error_critique;

		/* $this->checkConfiguration(); */

		$sub_blocks = new SubBlocksClass();
		$sub_blocks->registerTablesBdd();

		$this->postForm();

		if (Tools::getValue('feedback')) {
			$this->html_out .= '<script type="text/javascript">
			$(document).ready(function() {
				jAlert("' . $this->message_call_back[Tools::getValue('feedback')] . '", "' . $this->l('Information') . '");
			});
			</script>';
		}

		$this->context->controller->addJqueryUI('ui.datepicker');

		$this->context->controller->addCSS($this->_path . 'views/css/admin.css');

		$this->html_out .= '<link type="text/css" rel="stylesheet" href="' . (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http')
			. '://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />' . "\n";
		$this->html_out .= '<div id="hdclicconfiguration">';

		$this->html_out .= '
		<nav>
			<ul >
				<li class="logohdclicmini">
					<a href="#"><img src="../modules/' . $this->name . '/views/img/logohdclicmini.jpg" /></a>
				</li>
				<li>
					<a href="' . $this->path_module_conf . '"><img src="../modules/' . $this->name . '/views/img/home.png" />&nbsp;' . $this->l('Home') . '</a>
				</li>';
		$this->html_out .= '
				<li>
					<a href="#"><img src="../modules/' . $this->name . '/views/img/content.png" />&nbsp;' . $this->l('Manage content') . '</a>
					<ul>
						<li>
							<a href="' . $this->path_module_conf . '&newsListe"><img src="../modules/' . $this->name . '/views/img/copy_files.gif" />&nbsp;'
			. $this->l('News') . '</a>
						</li>
						<li>
							<a href="' . $this->path_module_conf . '&commentListe"><img src="../modules/' . $this->name . '/views/img/comments.png" />&nbsp;'
			. $this->l('Comments') . '</a>
						</li>
						<li>
							<a href="' . $this->path_module_conf . '&catListe"><img src="../modules/' . $this->name . '/views/img/categories.png" />&nbsp;'
			. $this->l('Categories') . '</a>
						</li>
					</ul>
				</li>';
		$this->html_out .= '
				<li>
					<a href="#"><img src="../modules/' . $this->name . '/views/img/tools.png" />&nbsp;' . $this->l('Tools') . '</a>
					<ul>
						<li>
							<a href="' . $this->path_module_conf . '&configAntiSpam"><img src="../modules/' . $this->name . '/views/img/shield.png" />&nbsp;'
			. $this->l('Anti-spam') . '</a>
						</li>
						<li>
							<a href="' . $this->path_module_conf . '&import"><img src="../modules/' . $this->name . '/views/img/import.png" />&nbsp;'
			. $this->l('Import WordPress XML') . '</a>
						</li>
						<li>
							<a href="' . $this->path_module_conf . '&sitemap"><img src="../modules/' . $this->name . '/views/img/sitemap.png" />&nbsp;'
			. $this->l('Sitemap') . '</a>
						</li>';
		$this->html_out .= '			</ul>
				</li>';

		$this->html_out .= '
				<li>
					<a href="#"><img src="../modules/' . $this->name . '/views/img/cog.gif" />&nbsp;' . $this->l('Configuration') . '</a>
					<ul>';
		/*
		 BETA DEV
		 $this->html_out .= '				<li>
							<a href="'.$this->path_module_conf.'&configWizard"><img src="../modules/'.$this->name.'/views/img/wizard.png" />&nbsp;'
							.$this->l('Wizard templating').'</a>
						</li>';
		*/

		$this->html_out .= '				<li>
							<a href="' . $this->path_module_conf . '&configTheme"><img src="../modules/' . $this->name . '/views/img/theme.png" />&nbsp;'
			. $this->l('Theme and slide') . '</a>
						</li>
						<li>
							<a href="' . $this->path_module_conf . '&pageBlog"><img src="../modules/' . $this->name . '/views/img/blog.png" />&nbsp;'
			. $this->l('Blog page') . '</a>
						</li>';
		if ($this->isPSVersion('>=', '1.6'))
			$this->html_out .= '				<li>
							<a href="' . $this->path_module_conf . '&configSubBlocks"><img src="../modules/' . $this->name . '/views/img/brick.png" />&nbsp;'
				. $this->l('Customize news list') . '</a>
						</li>';
		$this->html_out .= '				<li>
							<a href="' . $this->path_module_conf . '&configCategories"><img src="../modules/' . $this->name . '/views/img/categories.png" />&nbsp;'
			. $this->l('Categories') . '</a>
						</li>
						<li>
							<a href="' . $this->path_module_conf . '&configBlocs"><img src="../modules/' . $this->name . '/views/img/blocs.png" />&nbsp;'
			. $this->l('Blocks') . '</a>
						</li>
						<li>
							<a href="' . $this->path_module_conf . '&configComments"><img src="../modules/' . $this->name . '/views/img/comments.png" />&nbsp;'
			. $this->l('Comments') . '</a>
						</li>
						<li>
							<a href="' . $this->path_module_conf . '&configModule"><img src="../modules/' . $this->name . '/views/img/globalconf.png" />&nbsp;'
			. $this->l('Global') . '</a>
						</li>
					</ul>
				</li>';
		$this->html_out .= '
				<li>
					<a href="#"><img src="../modules/' . $this->name . '/views/img/help.png" />&nbsp;' . $this->l('Help') . '</a>
					<ul>
						<li>
							<a href="' . $this->path_module_conf . '&informations"><img src="../modules/' . $this->name . '/views/img/informations.png" />&nbsp;'
			. $this->l('Informations') . '</a>
						</li>
					</ul>
				</li>';

		/*
						<li>
							<a href="'.$this->path_module_conf.'&debug" title="'.$this->l('Debug').'"><img src="../modules/'
							.$this->name.'/views/img/debug.png" />&nbsp;'.$this->l('Debug').'</a>
						</li>
						*/

		$this->html_out .= '
					<li id="nav-version">
						' . $this->l('Version') . ' : ' . $this->version . ($this->demo_mode ? ' / ' . $this->l('Demo mode') : '') . '
					</li>
					<li class="nav-extra-link">
						<a href="' . self::prestablogUrl(array()) . '" target="_blank"><img src="../modules/' . $this->name . '/views/img/frontoffice.png" />&nbsp;'
			. $this->l('View blog page') . '</a>
					</li>
				</ul>
			</nav>
			<div id="contenu_config_prestablog">';

		if (Tools::isSubmit('addNews')
			|| Tools::isSubmit('editNews')
			|| Tools::isSubmit('submitAddNews')
			|| (Tools::isSubmit('submitUpdateNews') && Tools::getValue('idN'))
		)
			$this->displayFormNews();
		elseif (Tools::isSubmit('addCat')
			|| Tools::isSubmit('editCat')
			|| Tools::isSubmit('submitAddCat')
			|| (Tools::isSubmit('submitUpdateCat') && Tools::getValue('idC'))
		)
			$this->displayFormCategories();
		elseif (Tools::isSubmit('orderCat')) {
			if ($this->isPSVersion('>=', '1.6'))
				$this->displayOrderCategories();
			else
				Tools::redirectAdmin($this->path_module_conf . '&catListe');
		} elseif (Tools::isSubmit('submitOrderCat')) {
			if (Tools::getValue('newOrderCat')) {
				$new_order_cat = array();
				foreach (preg_split('/\&/', Tools::getValue('newOrderCat')) as $key => $value) {
					$current_order_list = preg_split('/\=/', $value);

					if (preg_match('/\d+/', $current_order_list[0], $match_id))
						$id_prestablog_categorie = (int)$match_id[0];
					else
						$id_prestablog_categorie = 0;

					$parent = (int)$current_order_list[1];

					$new_order_cat[] = array(
						'id_prestablog_categorie' => (int)$id_prestablog_categorie,
						'parent' => (int)$parent,
						'position' => (int)$key,
					);
				}

				foreach ($new_order_cat as $value)
					CategoriesClass::updatePosition(
						(int)$value['id_prestablog_categorie'],
						(int)$value['parent'],
						(int)$value['position']
					);
			}
			$this->displayOrderCategories();
		} elseif (Tools::isSubmit('addAntiSpam')
			|| Tools::isSubmit('editAntiSpam')
			|| Tools::isSubmit('submitAddAntiSpam')
			|| (Tools::isSubmit('submitUpdateAntiSpam') && Tools::getValue('idAS'))
		)
			$this->displayFormAntiSpam();
		elseif (Tools::isSubmit('editComment') || (Tools::isSubmit('submitUpdateComment') && Tools::getValue('idC')))
			$this->displayFormComments();
		elseif (Tools::isSubmit('addSubBlock')
			|| Tools::isSubmit('editSubBlock')
			|| Tools::isSubmit('submitAddSubBlock')
			|| (Tools::isSubmit('submitUpdateSubBlock') && Tools::getValue('idSB'))
		)
			$this->displayFormSubBlocks();
		elseif (Tools::isSubmit('pageBlog'))
			$this->displayPageBlog();
		elseif (Tools::isSubmit('configAntiSpam'))
			$this->displayConfigAntiSpam();
		elseif (Tools::isSubmit('sitemap'))
			$this->displaySitemap();
		elseif (Tools::isSubmit('configModule'))
			$this->displayConf();
		elseif (Tools::isSubmit('configTheme')) {
			$this->displayConfTheme();
			$this->displayConfSlide();
		} elseif (Tools::isSubmit('configWizard'))
			$this->displayConfWizard();
		elseif (Tools::isSubmit('configSubBlocks')) {
			if ($this->isPSVersion('>=', '1.6'))
				$this->displayListeSubBlocks();
			else
				$this->displayHome();
		} elseif (Tools::isSubmit('configCategories'))
			$this->displayConfCategories();
		elseif (Tools::isSubmit('configBlocs'))
			$this->displayConfBlocs();
		elseif (Tools::isSubmit('configProductTab'))
			$this->displayConfProductTab();
		elseif (Tools::isSubmit('configComments'))
			$this->displayConfComments();
		elseif (Tools::isSubmit('debug'))
			$this->displayDebug();
		elseif (Tools::isSubmit('informations'))
			$this->displayInformations();
		elseif (Tools::isSubmit('import'))
			$this->displayImport();
		elseif (Tools::isSubmit('catListe'))
			$this->displayListeCategories();
		elseif (Tools::isSubmit('newsListe'))
			$this->displayListeNews();
		elseif (Tools::isSubmit('commentListe'))
			$this->displayListeComments();
		else
			$this->displayHome();

		$this->html_out .= '
			</div>';
		$this->html_out .= '</div>';

		return $this->html_out;
	}

	private function compareTraductionsEtMergeAll($new, $old)
	{
		$traduction_merge = array();
		if (preg_match_all('#\$\_MODULE\[\'\<\{prestablog\}prestashop\>(.*)\'\][ =]*[ ]*\'(.*)\'[ ]*\;#',
			$new,
			$matchs
		)) {
			foreach ($matchs[1] as $key => $value)
				$traduction_merge[$value]['new'] = $matchs[2][$key];
		}

		if (preg_match_all('#\$\_MODULE\[\'\<\{prestablog\}prestashop\>(.*)\'\][ =]*[ ]*\'(.*)\'[ ]*\;#',
			$old,
			$matchs
		)) {
			foreach ($matchs[1] as $key => $value)
				$traduction_merge[$value]['old'] = $matchs[2][$key];
		}

		$trad = array();
		foreach ($traduction_merge as $key => $value) {
			if (isset($value['new']) && $value['new'] != '')
				$trad[$key] = $value['new'];
			elseif (isset($value['old']) && $value['old'] != '')
				$trad[$key] = $value['old'];
		}
		$file_traduction_merge = '<?php' . "\n\n";
		$file_traduction_merge .= 'global $_MODULE;' . "\n";
		$file_traduction_merge .= '$_MODULE = array();' . "\n";
		foreach ($trad as $key => $value)
			$file_traduction_merge .= '$_MODULE[\'<{prestablog}prestashop>' . $key . '\'] = \'' . $value . '\';' . "\n";

		return $file_traduction_merge;
	}

	private function displayHome()
	{
		$comments_non_lu = CommentNewsClass::getListeNonLu();

		if ($this->isPSVersion('>=', '1.6'))
			$this->displayDashBoard();

		$this->html_out .= '
		<div id="comments">' . "\n";
		if (Configuration::get('prestablog_commentfb_actif')) {
			$this->html_out .= '<div class="blocs col-sm-4 ' . (self::isPSVersion('<', '1.6') ? 'fixBloc15' : '') . '">' . "\n";
			$this->html_out .= '	<h3><img src="../modules/' . $this->name . '/views/img/facebook.png" />' . $this->l('Facebook comments') . '</h3>' . "\n";
			$this->html_out .= $this->displayInfo(
				'<p>' . $this->l('To moderate comments, go on the front office at bottom of each posts.') . '</p>');
			$this->html_out .= '</div>';
		} else {
			$this->html_out .= '
					<div class="blocs col-sm-4 ' . (self::isPSVersion('<', '1.6') ? 'fixBloc15' : '') . '">
						<h3><img src="../modules/' . $this->name . '/views/img/question.gif" alt="' . $this->l('Pending') . '" />'
				. count($comments_non_lu) . '&nbsp;' . sprintf($this->l('comment%1$s pending'),
					(count($comments_non_lu) > 1 ? 's' : '')) . '</h3>' . "\n";
			if (count($comments_non_lu) > 0) {
				$this->html_out .= '<div class="wrap">' . "\n";
				foreach ($comments_non_lu as $value_c) {
					$news = new NewsClass((int)$value_c['news'], (int)$this->context->language->id);
					$this->html_out .= '<div>' . "\n";
					$this->html_out .= '	<h2>
						<a href="' . $this->path_module_conf . '&deleteComment&idC=' . $value_c['id_' . $this->name . '_commentnews']
						. '" class="hrefComment" onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');" style="float:right;">
						<img src="../img/admin/delete.gif" alt="' . $this->l('Delete') . '" /><span style="display:none;">' . $this->l('Delete') . '</span></a>
						<a href="' . $this->path_module_conf . '&editComment&idC=' . $value_c['id_' . $this->name . '_commentnews']
						. '" class="hrefComment" style="float:right;"><img src="../modules/' . $this->name . '/views/img/edit.gif" />
						<span style="display:none;">' . $this->l('Edit') . '</span></a>
						<a href="' . $this->path_module_conf . '&editNews&idN=' . $value_c['news'] . '">' . $news->title . '</a></h2>' . "\n";
					$this->html_out .= '	<h4>' . ToolsCore::displayDate($value_c['date'], null, true) . ', ' . $this->l('by') . ' <strong>'
						. $value_c['name'] . '</strong></h4>' . "\n";
					if ($value_c['url'] != '')
						$this->html_out .= '	<h5><a href="' . $value_c['url'] . '" target="_blank">' . $value_c['url'] . '</a></h5>' . "\n";
					$this->html_out .= '	<p>' . $value_c['comment'] . '</p>' . "\n";
					$this->html_out .= '
						<p class="center">
							<a href="' . $this->path_module_conf . '&enabledComment&idC=' . $value_c['id_' . $this->name . '_commentnews'] . '" class="hrefComment">
							<img src="../img/admin/enabled.gif" alt="' . $this->l('Approuved') . '" /><span style="display:none;">'
						. $this->l('Approuved') . '</span></a>
							<a href="' . $this->path_module_conf . '&disabledComment&idC=' . $value_c['id_' . $this->name . '_commentnews'] . '" class="hrefComment">
							<img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" /><span style="display:none;">'
						. $this->l('Disabled') . '</span></a>
						</p>' . "\n";
					$this->html_out .= '</div>' . "\n";
				}
				$this->html_out .= '</div>' . "\n";
			}
			$this->html_out .= '
					</div>' . "\n";
		}

		$liste_news = NewsClass::getListe((int)$this->context->language->id,
			1,
			0,
			0,
			(int)Configuration::get($this->name . '_lastnews_limit'),
			'n.`date`',
			'desc',
			null,
			null,
			null,
			0,
			(int)Configuration::get('prestablog_news_title_length'),
			(int)Configuration::get('prestablog_news_intro_length'));

		$this->html_out .= '
			<div class="blocs col-sm-4 ' . (self::isPSVersion('<', '1.6') ? 'fixBloc15' : '') . '">
				<h3><img src="../modules/' . $this->name . '/views/img/lastnews.png" alt="' . $this->l('News') . '" />'
			. (int)Configuration::get($this->name . '_lastnews_limit') . ' ' . $this->l('latest news') . '</h3>' . "\n";
		if (count($liste_news) > 0) {
			$this->html_out .= '<div class="wrap">' . "\n";
			foreach ($liste_news as $value_n) {
				$this->html_out .= '<div class="homeblog">' . "\n";
				if (file_exists(dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/adminth_'
					. $value_n['id_' . $this->name . '_news'] . '.jpg'))
					$this->html_out .= '	<img src="' . $this->_path . 'views/img/' . Configuration::get($this->name . '_theme') . '/up-img/adminth_'
						. $value_n['id_' . $this->name . '_news'] . '.jpg?' . md5(time()) . '" class="thumb"/>' . "\n";
				$this->html_out .= '	<h2><a href="' . $this->path_module_conf . '&editNews&idN=' . $value_n['id_' . $this->name . '_news'] .
					'" class="hrefComment" style="float:right;"><img src="../img/admin/edit.gif" alt="' . $this->l('Edit') . '" />
				<span style="display:none;">' . $this->l('Edit') . '</span></a>' . $value_n['title'] . '</h2>' . "\n";
				$this->html_out .= '	<h4>' . ToolsCore::displayDate($value_n['date'], null, true) . '</h4>' . "\n";
				$this->html_out .= '	<p>' . "\n";
				$this->html_out .= ($value_n['paragraph_crop'] ? $value_n['paragraph_crop'] : '<span style="color:red">'
					. $this->l('... empty content ...') . '</span>');
				$this->html_out .= '	</p>' . "\n";
				$this->html_out .= '	<div class="clear"></div>' . "\n";
				$this->html_out .= '</div>' . "\n";
			}
			$this->html_out .= '</div>' . "\n";
		}
		$this->html_out .= '
			</div>' . "\n";

		$stats = self::statistiques();

		$this->html_out .= '
			<div class="blocs col-sm-3 ' . (self::isPSVersion('<', '1.6') ? 'fixBloc15' : '') . '">
				<h3><img src="../modules/' . $this->name . '/views/img/stats.png" alt="' . $this->l('Statistics') . '" />'
			. $this->l('Statistics') . '</h3>
				<h2>' . $this->l('Total news') . ' : <span style="color:#000;"><strong>' . $stats['allnews']
			. '</strong> <span style="color:#7F7F7F;">&rArr; <small>(<img src="../modules/' . $this->name
			. '/views/img/enabled.gif" width="10px;" />' . $stats['allnewsactives'] . ' | <img src="../modules/' . $this->name
			. '/views/img/disabled.gif" width="10px;" />' . $stats['allnewsinactives'] . ')</small></span></span></h2>
				<h2>' . $this->l('Total slides') . ' : <span style="color:#000;"><strong>' . $stats['allslides']
			. '</strong> <span style="color:#7F7F7F;">&rArr; <small>(<img src="../modules/' . $this->name
			. '/views/img/enabled.gif" width="10px;" />' . $stats['allslidesactives'] . ' | <img src="../modules/' . $this->name
			. '/views/img/disabled.gif" width="10px;" />' . $stats['allslidesinactives'] . ')</small></span></span></h2>
				<h2>' . $this->l('Total comments') . ' : <span style="color:#000;"><strong>' . $stats['allcomments']
			. '</strong> <span style="color:#7F7F7F;">&rArr; <small>(<img src="../modules/' . $this->name
			. '/views/img/enabled.gif" width="10px;" />' . $stats['allcommentsactives'] . ' | <img src="../modules/' . $this->name
			. '/views/img/disabled.gif" width="10px;" />' . $stats['allcommentsinactives'] . ' | <img src="../modules/' . $this->name
			. '/views/img/question.gif" width="10px;" />' . $stats['allcommentspending'] . ')</small></span></span></h2>
				<h2>' . $this->l('Total categories') . ' : <span style="color:#000;"><strong>' . $stats['allcategories']
			. '</strong> <span style="color:#7F7F7F;">&rArr; <small>(<img src="../modules/' . $this->name
			. '/views/img/enabled.gif" width="10px;" />' . $stats['allcategoriesactives'] . ' | <img src="../modules/' . $this->name
			. '/views/img/disabled.gif" width="10px;" />' . $stats['allcategoriesinactives'] . ')</small></span></span></h2>
				<h2>' . $this->l('Total subscribe news') . ' : <span style="color:#000;"><strong>' . $stats['allabonnements']
			. '</strong> <span style="color:#7F7F7F;"><small>(' . $this->l('only registered user') . ')</small></span></span></h2>
			</div>' . "\n";

		$this->html_out .= '
		</div>
		<div class="clear"></div>' . "\n";
		$this->html_out .= '
			<script type="text/javascript">
				$(document).ready(function() {
					$("a.hrefComment").mouseenter(function() {
						$("span:first", this).show(\'slow\');
					}).mouseleave(function() {
						$("span:first", this).hide();
					});
				});
			</script>' . "\n";
	}

	public function displayDashBoard()
	{
		$blocks_dash_board = SubBlocksClass::getListe((int)$this->context->language->id, 1, 'displayModuleBoard');

		$this->html_out .= '	<div id="dashboard" class="row">';
		if (count($blocks_dash_board) > 0) {
			$languages = Language::getLanguages(true);

			$languages_shop = array();
			foreach (Language::getLanguages() as $value)
				$languages_shop[$value['id_lang']] = $value['iso_code'];

			foreach ($blocks_dash_board as $v_block) {
				$news_liste = self::returnUniversalNewsListSubBlocks($v_block, unserialize($v_block['langues']));

				$this->html_out .= '		<div class="dashblock">
												<h4>
													' . $v_block['title'];
				$this->html_out .= '			</h4>';
				$this->html_out .= '			<h5>';
				$lang_liste_news = unserialize($v_block['langues']);
				if (is_array($lang_liste_news) && count($lang_liste_news) > 0)
					foreach ($lang_liste_news as $val_langue)
						$this->html_out .= ((count($languages) >= 1 && array_key_exists((int)$val_langue, $languages_shop)) ?
							'<img class="lang" src="../img/l/' . (int)$val_langue . '.jpg" />' : '');
				else
					$this->html_out .= $this->l('All languages');
				$this->html_out .= '			</h5>';

				if (count($news_liste) > 0) {
					if ($v_block['random'])
						shuffle($news_liste);

					foreach ($news_liste as $v_news) {
						$this->html_out .= '<a class="dropdown-toggle notifs" data-toggle="dropdown" href="#">';
						if (file_exists(dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/adminth_'
							. (int)$v_news['id_' . $this->name . '_news'] . '.jpg'))
							$this->html_out .= '<img class="item" rel="' . (int)$v_news['id_' . $this->name . '_news'] . '" src="'
								. $this->_path . 'views/img/' . Configuration::get($this->name . '_theme') . '/up-img/adminth_'
								. (int)$v_news['id_' . $this->name . '_news'] . '.jpg?' . md5(time()) . '" />';
						else
							$this->html_out .= '<div class="item" rel="' . (int)$v_news['id_' . $this->name . '_news'] . '">
						<p><i class="icon-eye-slash"></i><br/>#' . (int)$v_news['id_' . $this->name . '_news'] . '</p></div>';
						$this->html_out .= '	<div class="appear">';
						$this->html_out .= '		<h4>';
						if (file_exists(dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/adminth_'
							. (int)$v_news['id_' . $this->name . '_news'] . '.jpg'))
							$this->html_out .= '<img class="thumb" src="' . $this->_path . 'views/img/' . Configuration::get($this->name . '_theme')
								. '/up-img/adminth_' . (int)$v_news['id_' . $this->name . '_news'] . '.jpg?' . md5(time()) . '" />';
						$this->html_out .= $v_news['title'];
						$this->html_out .= '		</h4>';
						$this->html_out .= '		<div class="clearfix"></div>';
						$total_read = 0;
						$out_read = '';
						foreach (Language::getLanguages() as $value) {
							$cur_read = (int)NewsClass::getRead((int)$v_news['id_' . $this->name . '_news'], (int)$value['id_lang']);
							$out_read .= '<img src="../img/l/' . (int)$value['id_lang'] . '.jpg" /> ' . $this->l('Read') . ' : <strong>'
								. $cur_read . '</strong> ';
							$total_read += $cur_read;
						}
						$this->html_out .= '		<p class="rubrique">' . $this->l('Total read') . ' : <strong>' . $total_read . '</strong></p>';
						$this->html_out .= '		<p class="details">' . $out_read . '</p>';

						$all_comments = count(CommentNewsClass::getListe(-2, (int)$v_news['id_' . $this->name . '_news']));
						$all_comments_actives = count(CommentNewsClass::getListe(1, (int)$v_news['id_' . $this->name . '_news']));
						$all_comments_pending = count(CommentNewsClass::getListe(-1, (int)$v_news['id_' . $this->name . '_news']));
						$all_comments_inactives = count(CommentNewsClass::getListe(0, (int)$v_news['id_' . $this->name . '_news']));

						$this->html_out .= '		<p class="rubrique">' . $this->l('Total comments') . ' : <strong>' . $all_comments . '</strong></p>';
						$this->html_out .= '		<p class="details">' . $this->l('Activated') . ' : <strong>' . $all_comments_actives . '</strong></p>';
						$this->html_out .= '		<p class="details">' . $this->l('Pending') . ' : <strong>' . $all_comments_pending . '</strong></p>';
						$this->html_out .= '		<p class="details">' . $this->l('Desactivated') . ' : <strong>' . $all_comments_inactives . '</strong></p>';

						$this->html_out .= '	</div>';
						$this->html_out .= '</a>';
					}
				}

				$this->html_out .= '		</div>';
			}
		}
		$this->html_out .= '			<a class="dashadd" href="' . $this->path_module_conf . '&addSubBlock&preselecthook=displayModuleBoard"
											title="' . $this->l('Your custom list') . '" style="display:none;">
											<i class="icon-plus"></i><br/>
											' . sprintf($this->l('Custom%1$slist'), '<br/>') . '
										</a>';
		$this->html_out .= '	</div>';
		$this->html_out .= '
			<script type="text/javascript">
				$(document).ready(function() {
					$("#dashboard").mouseenter(function() {
						$("a.dashadd", this).fadeIn(\'slow\');
					}).mouseleave(function() {
						$("a.dashadd", this).fadeOut();
					});
					$("a.notifs .item").click(function(){
						location.href="' . $this->path_module_conf . '&editNews&idN="+$(this).attr("rel");
					});
				});
			</script>' . "\n";
	}

	public static function statistiques()
	{
		$context = Context::getContext();
		$stats = array();
		$stats['allnews'] = NewsClass::getCountListeAllNoLang(0, 0, null, null, null);
		$stats['allnewsactives'] = NewsClass::getCountListeAllNoLang(1, 0, null, null, null);
		$stats['allnewsinactives'] = $stats['allnews'] - $stats['allnewsactives'];

		$stats['allslides'] = NewsClass::getCountListeAllNoLang(0, 1, null, null, null);
		$stats['allslidesactives'] = NewsClass::getCountListeAllNoLang(1, 1, null, null, null);
		$stats['allslidesinactives'] = $stats['allslides'] - $stats['allslidesactives'];

		$stats['allcomments'] = count(CommentNewsClass::getListe(-2, 0));
		$stats['allcommentsactives'] = count(CommentNewsClass::getListe(1, 0));
		$stats['allcommentspending'] = count(CommentNewsClass::getListe(-1, 0));
		$stats['allcommentsinactives'] = count(CommentNewsClass::getListe(0, 0));

		$stats['allcategories'] = count(CategoriesClass::getListeNoArbo(0, (int)$context->language->id));
		$stats['allcategoriesactives'] = count(CategoriesClass::getListeNoArbo(1, (int)$context->language->id));
		$stats['allcategoriesinactives'] = $stats['allcategories'] - $stats['allcategoriesactives'];

		$stats['allabonnements'] = count(CommentNewsClass::listeCommentAbo());

		return $stats;
	}

	private function displayListeNews()
	{
		$languages_shop = array();
		foreach (Language::getLanguages() as $value)
			$languages_shop[$value['id_lang']] = $value['iso_code'];

		$nb_par_page = (int)Configuration::get($this->name . '_nb_news_pl');

		$tri_champ = 'n.`date`';
		$tri_ordre = 'desc';
		$languages = Language::getLanguages(true);

		if (Tools::getValue('c') && (int)Tools::getValue('c') > 0) {
			$categorie = (int)Tools::getValue('c');
			$this->path_module_conf .= $this->path_module_conf . '&c=' . $categorie;
		} else
			$categorie = null;

		$count_liste = NewsClass::getCountListeAll(
			0,
			(int)$this->check_active,
			(int)$this->check_slide,
			null,
			null,
			$categorie,
			0
		);

		$liste = NewsClass::getListe(
			0,
			(int)$this->check_active,
			(int)$this->check_slide,
			(int)Tools::getValue('start'),
			$nb_par_page,
			$tri_champ,
			$tri_ordre,
			null,
			null,
			$categorie,
			0,
			(int)Configuration::get('prestablog_news_title_length'),
			(int)Configuration::get('prestablog_news_intro_length')
		);

		$pagination = self::getPagination(
			$count_liste,
			null,
			$nb_par_page,
			(int)Tools::getValue('start'),
			(int)Tools::getValue('p')
		);

		$categories = CategoriesClass::getListe((int)$this->context->language->id, 0);

		if ($this->isPSVersion('>=', '1.6')) {
			$this->html_out .= '
				<div class="panel">
					<form method="post" action="' . $this->path_module_conf . '&newsListe" enctype="multipart/form-data">
						<fieldset>
							<input type="hidden" name="submitFiltreNews" value="1" />
							<div class="col-sm-3">
								<a class="btn btn-primary" href="' . $this->path_module_conf . '&addNews">
									<i class="icon-plus"></i>
									' . $this->l('Add a news') . '
								</a>
							</div>
							<div class="col-sm-2">
								<img src="../modules/' . $this->name . '/views/img/filter.png" />
								' . $this->l('Filter list') . ' :
							</div>' . "\n";
			if (count($categories) > 0) {
				$categories = new CategoriesClass();
				$liste_categories = CategoriesClass::getListe((int)$this->context->language->id, 0);
				$this->html_out .= '<div class="col-sm-2">' . "\n";
				$this->html_out .= $categories->displaySelectArboCategories($liste_categories, 0, 0,
						$this->l('None'), 'c', 'form.submit();', (int)Tools::getValue('c')) . "\n";
				$this->html_out .= '</div>' . "\n";
			}
			$this->html_out .= '
							<div class="col-sm-2">
								<input type="checkbox" name="activeNews" ' . ($this->check_active == 1 ? 'checked' : '') . '
								onchange="form.submit();"> ' . $this->l('Active') . '
							</div>' . "\n";
			$this->html_out .= '
							<div class="col-sm-2">
								<input type="checkbox" name="slide" ' . ($this->check_slide == 1 ? 'checked' : '') . '
								onchange="form.submit();"> ' . $this->l('Slide') . '
							</div>' . "\n";

			$this->html_out .= '
						</fieldset>
					</form>
				</div>';
		} else {
			$this->html_out .= '
				<form method="post" action="' . $this->path_module_conf . '&newsListe" enctype="multipart/form-data">
				<input type="hidden" name="submitFiltreNews" value="1" />
				<table class="table" cellpadding="0" cellspacing="0" style="margin:auto;text-align:center;">
					<tr style="height:30px">
						<th>
							<img src="../modules/' . $this->name . '/views/img/add.gif" />
							<a href="' . $this->path_module_conf . '&addNews" title="' . $this->l('Add a news') . '">' . $this->l('Add a news') . '</a>
						</th>
						<th style="border-left:3px solid #A0A0A0;">
							<img src="../modules/' . $this->name . '/views/img/filter.png" />
							' . $this->l('Filter list') . ' :
						</th>' . "\n";

			if (count($categories) > 0) {
				$categories = new CategoriesClass();
				$liste_categories = CategoriesClass::getListe((int)$this->context->language->id, 0);
				$this->html_out .= '
							<th style="border-left:1px solid #A0A0A0;">
								' . $categories->displaySelectArboCategories($liste_categories, 0, 0, $this->l('None'),
						'c', 'form.submit();', (int)Tools::getValue('c')) . '
							</th>' . "\n";
			}

			$this->html_out .= '
						<th style="border-left:1px solid #A0A0A0;">
							<input type="checkbox" name="activeNews" ' . ($this->check_active == 1 ? 'checked' : '') . '
							onchange="form.submit();"> ' . $this->l('Active') . '
						</th>' . "\n";
			$this->html_out .= '
						<th style="border-left:1px solid #A0A0A0;">
							<input type="checkbox" name="slide" ' . ($this->check_slide == 1 ? 'checked' : '') . '
							onchange="form.submit();"> ' . $this->l('Slide') . '
						</th>' . "\n";

			$this->html_out .= '
					</tr>
				</table>
				</form>';
		}

		if ($this->isPSVersion('>=', '1.6'))
			$this->html_out .= '<div class="panel">';
		else
			$this->html_out .= '<br/>';

		$this->html_out .= '<fieldset>';

		$this->html_out .= '<legend style="margin-bottom:10px;">' . $this->l('News') . ' :
							<span style="color: green;">' . ($categorie ? sprintf($this->l('%1$s currents items on %2$s'),
				$count_liste, CategoriesClass::getCategoriesName((int)$this->context->language->id,
					(int)$categorie)) : sprintf($this->l('%1$s currents items'), $count_liste)) . '
							</span>
						</legend>';
		$this->html_out .= '<table class="table" cellpadding="0" cellspacing="0" style="margin:auto;width:100%;">';
		$this->html_out .= '	<thead class="center">';
		$this->html_out .= '		<tr>';
		$this->html_out .= '			<th>Id</th>';
		$this->html_out .= '			<th>' . $this->l('Preview') . '</th>';
		$this->html_out .= '			<th>' . $this->l('Date') . '</th>';
		$this->html_out .= '			<th>' . $this->l('Image') . '</th>';
		$this->html_out .= '			<th width="400px">' . $this->l('Title') . '</th>';
		$this->html_out .= '			<th>' . $this->l('Read') . '</th>';
		$this->html_out .= '			<th>' . $this->l('Comments') . '</th>';
		$this->html_out .= '			<th>' . $this->l('Products linked') . '</th>';
		$this->html_out .= '			<th>' . $this->l('Slide') . '</th>';
		$this->html_out .= '			<th>' . $this->l('Activate') . '</th>';
		$this->html_out .= '			<th>' . $this->l('Actions') . '</th>';
		$this->html_out .= '		</tr>';
		$this->html_out .= '	</thead>';
		if (count($liste) > 0) {
			foreach ($liste as $value) {
				$lang_liste_news = unserialize($value['langues']);

				$this->html_out .= '	<tr>';
				$this->html_out .= '		<td class="center">';
				$this->html_out .= $value['id_prestablog_news'];
				if (!empty($value['url_redirect']) && Validate::isAbsoluteUrl($value['url_redirect']))
					$this->html_out .= '	<a href="' . $value['url_redirect'] . '" target="_blank" title="' . $this->l('Permanent redirect url') . '">
												<img src="../modules/' . $this->name . '/views/img/rewrite.png" alt="' . $this->l('Permanent redirect url') . '" />
											</a>';
				$this->html_out .= '		</td>';
				$this->html_out .= '		<td class="center">';
				foreach ($lang_liste_news as $val_langue) {
					if (count($languages) >= 1 && array_key_exists((int)$val_langue, $languages_shop)) {
						$news_tempo = new NewsClass((int)$value['id_' . $this->name . '_news']);
						$this->html_out .= '
							<img src="../img/l/' . (int)$val_langue . '.jpg" />
							<a target="_blank" href="' . PrestaBlog::prestablogUrl(array(
								'id' => (int)$news_tempo->id,
								'seo' => $news_tempo->link_rewrite[(int)$val_langue],
								'titre' => $news_tempo->title[(int)$val_langue],
								'id_lang' => (int)$val_langue,
							)) . ((int)Configuration::get('PS_REWRITING_SETTINGS') && (int)Configuration::get('prestablog_rewrite_actif') ? '?' : '&') . '
							preview=' . $this->generateToken((int)$news_tempo->id) . '">
								<img src="../modules/' . $this->name . '/views/img/preview.gif" />
							</a>
							<br />';
					}
				}

				$this->html_out .= '		</td>';
				$this->html_out .= '		<td class="center">' . ((new DateTime($value['date'])) > (new DateTime()) ?
						'<img src="../modules/' . $this->name . '/views/img/postdate.gif" alt="' . $this->l('Post Date') . '" />' : '')
					. ToolsCore::displayDate($value['date'], null, true) . '</td>';
				if (file_exists(dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/adminth_'
					. $value['id_' . $this->name . '_news'] . '.jpg'))
					$this->html_out .= '		<td class="center"><img src="' . $this->_path . 'views/img/' . Configuration::get($this->name . '_theme')
						. '/up-img/adminth_' . $value['id_' . $this->name . '_news'] . '.jpg?' . md5(time()) . '" /></td>';
				else
					$this->html_out .= '		<td class="center">-</td>';

				$this->html_out .= '		<td>';
				foreach ($lang_liste_news as $val_langue)
					$this->html_out .= ((count($languages) >= 1 && array_key_exists((int)$val_langue, $languages_shop)) ?
						'<img src="../img/l/' . (int)$val_langue . '.jpg" /> ' . NewsClass::getTitleNews((int)$value['id_' . $this->name . '_news'],
							(int)$val_langue) . '<br/>' : '');
				$this->html_out .= '		</td>';
				$this->html_out .= '		<td>';
				foreach ($lang_liste_news as $val_langue)
					$this->html_out .= ((count($languages) >= 1 && array_key_exists((int)$val_langue, $languages_shop)) ?
						'<img src="../img/l/' . (int)$val_langue . '.jpg" /> ' . NewsClass::getRead((int)$value['id_' . $this->name . '_news'],
							(int)$val_langue) . '<br/>' : '');
				$this->html_out .= '		</td>';

				$this->html_out .= '		<td class="center">';
				$comments_actif = CommentNewsClass::getListe(1, (int)$value['id_' . $this->name . '_news']);
				$comments_all = CommentNewsClass::getListe(-2, (int)$value['id_' . $this->name . '_news']);
				if (count($comments_all) > 0)
					$this->html_out .= count($comments_actif) . ' ' . $this->l('of') . ' ' . count($comments_all) . ' ' . $this->l('active');
				else
					$this->html_out .= '-';

				$this->html_out .= '		</td>';
				$this->html_out .= '		<td class="center">';

				$products_link = NewsClass::getProductLinkListe((int)$value['id_prestablog_news']);

				$this->html_out .= (count($products_link) > 0 ? count($products_link) : '-');

				$this->html_out .= '		</td>';

				$this->html_out .= '		<td class="center">
					<a href="' . $this->path_module_conf . '&slideNews&idN=' . $value['id_' . $this->name . '_news'] . '">
					' . ($value['slide'] ? '<img src="../modules/' . $this->name . '/views/img/enabled.gif" />'
						: '<img src="../modules/' . $this->name . '/views/img/disabled.gif" />') . '
					</a>
				</td>';
				$this->html_out .= '		<td class="center">
					<a href="' . $this->path_module_conf . '&etatNews&idN=' . $value['id_' . $this->name . '_news'] . '">
					' . ($value['actif'] ? '<img src="../modules/' . $this->name . '/views/img/enabled.gif" />'
						: '<img src="../modules/' . $this->name . '/views/img/disabled.gif" />') . '
					</a>
				</td>';
				$this->html_out .= '		<td class="center">
					<a href="' . $this->path_module_conf . '&editNews&idN=' . $value['id_' . $this->name . '_news'] . '"
					title="' . $this->l('Edit') . '"><img src="../modules/' . $this->name . '/views/img/edit.gif" /></a>
					<a href="' . $this->path_module_conf . '&deleteNews&idN=' . $value['id_' . $this->name . '_news'] . '"
					onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');">
					<img src="../img/admin/delete.gif" alt="' . $this->l('Delete') . '" /></a>
				</td>';
				$this->html_out .= '	</tr>';
			}
			$page_type = 'newsListe';

			if ((int)$pagination['NombreTotalPages'] > 1) {
				$this->html_out .= '<tfooter>';
				$this->html_out .= '	<tr>';
				$this->html_out .= '	<td colspan="6">';
				$this->html_out .= '<div class="prestablog_pagination">' . "\n";
				if ((int)$pagination['PageCourante'] > 1) {
					$this->html_out .= '<a href="' . $this->path_module_conf . '&' . $page_type . '&start=' . $pagination['StartPrecedent']
						. '&p=' . $pagination['PagePrecedente'] . '">&lt;&lt;</a>' . "\n";
				} else $this->html_out .= '<span class="disabled">&lt;&lt;</span>' . "\n";

				if ($pagination['PremieresPages']) {
					foreach ($pagination['PremieresPages'] as $key_page => $value_page) {
						if (((int)Tools::getValue('p') == $key_page) || ((Tools::getValue('p') == '') && $key_page == 1))
							$this->html_out .= '<span class="current">' . $key_page . '</span>' . "\n";
						else {
							if ($key_page == 1)
								$this->html_out .= '<a href="' . $this->path_module_conf . '&' . $page_type . '">' . $key_page . '</a>' . "\n";
							else {
								$this->html_out .= '<a href="' . $this->path_module_conf . '&' . $page_type
									. '&start=' . $value_page . '&p=' . $key_page . '">' . $key_page . '</a>' . "\n";
							}
						}
					}
				}
				if (isset($pagination['Pages']) && $pagination['Pages']) {
					$this->html_out .= '<span class="more">...</span>' . "\n";

					foreach ($pagination['Pages'] as $key_page => $value_page) {
						if (!in_array($value_page, $pagination['PremieresPages'])) {
							if (((int)Tools::getValue('p') == $key_page) || ((Tools::getValue('p') == '') && $key_page == 1))
								$this->html_out .= '<span class="current">' . $key_page . '</span>' . "\n";
							else {
								$this->html_out .= '<a href="' . $this->path_module_conf . '&' . $page_type
									. '&start=' . $value_page . '&p=' . $key_page . '">' . $key_page . '</a>' . "\n";
							}
						}
					}
				}
				if ($pagination['PageCourante'] < $pagination['NombreTotalPages']) {
					$this->html_out .= '<a href="' . $this->path_module_conf . '&' . $page_type . '&start=' . $pagination['StartSuivant']
						. '&p=' . $pagination['PageSuivante'] . '">&gt;&gt;</a>' . "\n";
				} else
					$this->html_out .= '<span class="disabled">&gt;&gt;</span>' . "\n";

				$this->html_out .= '</div>' . "\n";
				$this->html_out .= '	</td>';
				$this->html_out .= '	</tr>';
				$this->html_out .= '</tfooter>';
			}
		} else
			$this->html_out .= '<tr><td colspan="8" class="center">' . $this->l('No content registered') . '</td></tr>';

		$this->html_out .= '</table>';
		$this->html_out .= '</fieldset>';

		if ($this->isPSVersion('>=', '1.6'))
			$this->html_out .= '</div>';
	}

	private function displayListeComments()
	{
		$nb_par_page = (int)Configuration::get($this->name . '_nb_comments_pl');

		if (Tools::getValue('n') && (int)Tools::getValue('n') > 0) {
			$news = (int)Tools::getValue('n');
			$this->path_module_conf .= $this->path_module_conf . '&n=' . $news;
		} else
			$news = null;

		$count_liste = CommentNewsClass::getCountListeAll(
			$this->check_comment_state,
			$news
		);

		$liste = CommentNewsClass::getListeNavigate(
			$this->check_comment_state,
			(int)Tools::getValue('start'),
			$nb_par_page
		);

		$pagination = self::getPagination(
			$count_liste,
			null,
			$nb_par_page,
			(int)Tools::getValue('start'),
			(int)Tools::getValue('p')
		);

		if ($this->isPSVersion('>=', '1.6')) {
			$this->html_out .= '
				<div class="panel">
					<form method="post" action="' . $this->path_module_conf . '&commentListe" enctype="multipart/form-data">
						<fieldset>
							<input type="hidden" name="submitFiltreComment" value="1" />
							<div class="col-sm-2">
								<img src="../modules/' . $this->name . '/views/img/filter.png" />
								' . $this->l('Filter list') . ' :
							</div>
							<div class="col-sm-2">
								<input type="radio" name="activeComment" ' . ($this->check_comment_state == -2 ? 'checked' : '') . '
								onchange="form.submit();" value="-2" > <img src="../modules/' . $this->name . '/views/img/refresh.png" /> ' . $this->l('All') . '
							</div>
							<div class="col-sm-2">
								<input type="radio" name="activeComment" ' . ($this->check_comment_state == -1 ? 'checked' : '') . '
								onchange="form.submit();" value="-1" > <img src="../modules/' . $this->name . '/views/img/question.gif" /> ' . $this->l('Pending') . '
							</div>
							<div class="col-sm-2">
								<input type="radio" name="activeComment" ' . ($this->check_comment_state == 1 ? 'checked' : '') . '
								onchange="form.submit();" value="1"> <img src="../modules/' . $this->name . '/views/img/enabled.gif" /> ' . $this->l('Enabled') . '
							</div>
							<div class="col-sm-2">
								<input type="radio" name="activeComment" '
				. (is_numeric($this->check_comment_state) && ($this->check_comment_state == 0) ? 'checked' : '')
				. ' onchange="form.submit();" value="0" > <img src="../modules/' . $this->name . '/views/img/disabled.gif" /> ' . $this->l('Disabled') . '
							</div>
						</fieldset>
					</form>
				</div>';
		} else {
			$this->html_out .= '
				<form method="post" action="' . $this->path_module_conf . '&commentListe" enctype="multipart/form-data">
				<input type="hidden" name="submitFiltreComment" value="1" />
				<table class="table" cellpadding="0" cellspacing="0" style="margin:auto;text-align:center;">
					<tr style="height:30px">
						<th style="border-left:3px solid #A0A0A0;">
							<img src="../modules/' . $this->name . '/views/img/filter.png" />
							' . $this->l('Filter list') . ' :
						</th>' . "\n";

			$this->html_out .= '
						<th style="border-left:1px solid #A0A0A0;">
							<input type="radio" name="activeComment" ' . ($this->check_comment_state == -2 ? 'checked' : '') . '
							onchange="form.submit();" value="-2" > <img src="../modules/' . $this->name . '/views/img/refresh.png" /> ' . $this->l('All') . '
						</th>
						<th style="border-left:1px solid #A0A0A0;">
							<input type="radio" name="activeComment" ' . ($this->check_comment_state == -1 ? 'checked' : '') . '
							onchange="form.submit();" value="-1" > <img src="../modules/' . $this->name . '/views/img/question.gif" /> ' . $this->l('Pending') . '
						</th>
						<th style="border-left:1px solid #A0A0A0;">
							<input type="radio" name="activeComment" ' . ($this->check_comment_state == 1 ? 'checked' : '') . '
							onchange="form.submit();" value="1"> <img src="../modules/' . $this->name . '/views/img/enabled.gif" /> ' . $this->l('Enabled') . '
						</th>
						<th style="border-left:1px solid #A0A0A0;">
							<input type="radio" name="activeComment" '
				. (is_numeric($this->check_comment_state) && ($this->check_comment_state == 0) ? 'checked' : '')
				. ' onchange="form.submit();" value="0" > <img src="../modules/' . $this->name . '/views/img/disabled.gif" /> ' . $this->l('Disabled') . '
						</th>' . "\n";

			$this->html_out .= '
					</tr>
				</table>
				</form>';
		}

		if ($this->isPSVersion('>=', '1.6'))
			$this->html_out .= '<div class="panel">';
		else
			$this->html_out .= '<br/>';

		$this->html_out .= '<fieldset>';
		$this->html_out .= '<legend style="margin-bottom:10px;">' . $this->l('Comments') . ' :</legend>';
		$this->html_out .= '<table class="table" cellpadding="0" cellspacing="0" style="margin:auto;width:100%;">';
		$this->html_out .= '	<thead class="center">';
		$this->html_out .= '		<tr>';
		$this->html_out .= '			<th>Id</th>';
		$this->html_out .= '			<th>' . $this->l('Date') . '</th>';
		$this->html_out .= '			<th>' . $this->l('News') . '</th>';
		$this->html_out .= '			<th>' . $this->l('Name') . '</th>';
		$this->html_out .= '			<th>' . $this->l('Url') . '</th>';
		$this->html_out .= '			<th>' . $this->l('Comment') . '</th>';
		$this->html_out .= '			<th class="center" style="width:70px;">' . $this->l('Status') . '</th>';
		$this->html_out .= '			<th class="center">' . $this->l('Actions') . '</th>';
		$this->html_out .= '		</tr>';
		$this->html_out .= '	</thead>';

		if (count($liste) > 0) {
			foreach ($liste as $value) {
				$this->html_out .= '	<tr>';
				$this->html_out .= '		<td class="center">' . ($value['id_prestablog_commentnews']) . '</td>';
				$this->html_out .= '		<td class="center">' . ToolsCore::displayDate($value['date'], null, true) . '</td>';
				$title_news = NewsClass::getTitleNews((int)$value['news'], (int)$this->context->language->id);

				$this->html_out .= '		<td><a href="' . $this->path_module_conf . '&editNews&idN=' . $value['news'] . '">'
					. self::cleanCut($title_news, 40, '...') . '</a></td>';

				$this->html_out .= '		<td>' . $value['name'] . '</td>';
				$this->html_out .= '		<td><a href="' . $value['url'] . '" target="_blank">' . $value['url'] . '</a></td>';
				$this->html_out .= '		<td><small>' . self::cleanCut($value['comment'], 120, '...') . '</small></td>';
				$this->html_out .= '		<td class="status">
					<a href="' . $this->path_module_conf . '&enabledComment&commentListe&idC=' . $value['id_prestablog_commentnews'] . '" '
					. ((int)$value['actif'] != 1 ? 'style="display:none;"' : 'rel="1"') . ' >
						<img src="../modules/' . $this->name . '/views/img/enabled.gif" title="' . $this->l('Approuved') . '" />
					</a>
					<a href="' . $this->path_module_conf . '&disabledComment&commentListe&idC=' . $value['id_prestablog_commentnews'] . '" '
					. ((int)$value['actif'] != 0 ? 'style="display:none;"' : 'rel="1"') . ' >
						<img src="../modules/' . $this->name . '/views/img/disabled.gif" title="' . $this->l('Disabled') . '" />
					</a>
					<a href="' . $this->path_module_conf . '&pendingComment&commentListe&idC=' . $value['id_prestablog_commentnews'] . '" '
					. ((int)$value['actif'] != -1 ? 'style="display:none;"' : 'rel="1"') . ' >
						<img src="../modules/' . $this->name . '/views/img/question.gif"' . $this->l('Pending') . '" />
					</a>
				</td>
				<script language="javascript" type="text/javascript">
					$(document).ready(function() {
						$("td.status").mouseenter(function() {
							$(this).find("a").fadeIn();
						}).mouseleave(function() {
							$(this).find("a").hide(function() {
								if ($(this).attr(\'rel\') == 1)
									$(this).fadeIn();
							});
						});
					});
				</script>
				';
				$this->html_out .= '		<td class="center">
					<a href="' . $this->path_module_conf . '&editComment&idC=' . $value['id_prestablog_commentnews'] . '"
					title="' . $this->l('Edit') . '"><img src="../modules/' . $this->name . '/views/img/edit.gif" /></a>
					<a href="' . $this->path_module_conf . '&deleteComment&idC=' . $value['id_prestablog_commentnews'] . '"
					onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');">
					<img src="../img/admin/delete.gif" alt="' . $this->l('Delete') . '" /></a>
				</td>';
				$this->html_out .= '	</tr>';
			}
			$page_type = 'commentListe';

			if ((int)$pagination['NombreTotalPages'] > 1) {
				$this->html_out .= '<tfooter>';
				$this->html_out .= '	<tr>';
				$this->html_out .= '	<td colspan="6">';
				$this->html_out .= '<div class="prestablog_pagination">' . "\n";
				if ((int)$pagination['PageCourante'] > 1) {
					$this->html_out .= '<a href="' . $this->path_module_conf . '&' . $page_type . '&start=' . $pagination['StartPrecedent']
						. '&p=' . $pagination['PagePrecedente'] . '">&lt;&lt;</a>' . "\n";
				} else $this->html_out .= '<span class="disabled">&lt;&lt;</span>' . "\n";

				if ($pagination['PremieresPages']) {
					foreach ($pagination['PremieresPages'] as $key_page => $value_page) {
						if (((int)Tools::getValue('p') == $key_page) || ((Tools::getValue('p') == '') && $key_page == 1))
							$this->html_out .= '<span class="current">' . $key_page . '</span>' . "\n";
						else {
							if ($key_page == 1)
								$this->html_out .= '<a href="' . $this->path_module_conf . '&' . $page_type . '">' . $key_page . '</a>' . "\n";
							else {
								$this->html_out .= '<a href="' . $this->path_module_conf . '&' . $page_type . '&start=' . $value_page . '&p=' . $key_page . '">'
									. $key_page . '</a>' . "\n";
							}
						}
					}
				}
				if (isset($pagination['Pages']) && $pagination['Pages']) {
					$this->html_out .= '<span class="more">...</span>' . "\n";

					foreach ($pagination['Pages'] as $key_page => $value_page) {
						if (!in_array($value_page, $pagination['PremieresPages'])) {
							if (((int)Tools::getValue('p') == $key_page) || ((Tools::getValue('p') == '') && $key_page == 1))
								$this->html_out .= '<span class="current">' . $key_page . '</span>' . "\n";
							else {
								$this->html_out .= '<a href="' . $this->path_module_conf . '&' . $page_type . '&start=' . $value_page . '&p=' . $key_page . '">'
									. $key_page . '</a>' . "\n";
							}
						}
					}
				}
				if ($pagination['PageCourante'] < $pagination['NombreTotalPages']) {
					$this->html_out .= '<a href="' . $this->path_module_conf . '&' . $page_type . '&start=' . $pagination['StartSuivant'] . '&p='
						. $pagination['PageSuivante'] . '">&gt;&gt;</a>' . "\n";
				} else
					$this->html_out .= '<span class="disabled">&gt;&gt;</span>' . "\n";

				$this->html_out .= '</div>' . "\n";
				$this->html_out .= '	</td>';
				$this->html_out .= '	</tr>';
				$this->html_out .= '</tfooter>';
			}
		} else
			$this->html_out .= '<tr><td colspan="8" class="center">' . $this->l('No content registered') . '</td></tr>';

		$this->html_out .= '</table>';
		$this->html_out .= '</fieldset>';

		if ($this->isPSVersion('>=', '1.6'))
			$this->html_out .= '</div>';
	}

	private function displayListeSubBlocks()
	{
		$languages = Language::getLanguages(true);

		$languages_shop = array();
		foreach (Language::getLanguages() as $value)
			$languages_shop[$value['id_lang']] = $value['iso_code'];

		$this->html_out .= $this->displayFormOpen('icon-shield', $this->l('Customize articles list'), $this->path_module_conf);

		$this->html_out .= $this->displayWarning(
			'<p>' . $this->l('Advanced user only') . '</p>'
		);

		$this->html_out .= $this->displayInfo(
			'<p>' . $this->l('Insert your own lists of articles in your own hook.') . ' '
			. $this->l('These blocks will appear in the order you chose, for each hook.') . '</p>'
		);

		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-2', $this->l('News list general activation'),
			$this->name . '_subblocks_actif');

		$this->html_out .= $this->displayFormSubmit('submitSubBlocksConfig', 'icon-save', $this->l('Update configuration'));

		$this->html_out .= $this->displayFormClose();

		if ($this->isPSVersion('>=', '1.6')) {
			$this->html_out .= '
				<div class="panel">
					<fieldset>
						<div class="col-sm-3">
							<a class="btn btn-primary" href="' . $this->path_module_conf . '&addSubBlock">
								<i class="icon-plus"></i>
								' . $this->l('Create a list') . '
							</a>
						</div>
					</fieldset>
				</div>';
		} else {
			$this->html_out .= '
				<table class="table" cellpadding="0" cellspacing="0" style="margin:auto;text-align:center">
					<tr>
						<th>
								<img src="../modules/' . $this->name . '/views/img/add.gif" />
								<a href="' . $this->path_module_conf . '&addSubBlock" title="' . $this->l('Create a list') . '">'
				. $this->l('Create a list') . '</a>
						</th>
					</tr>
				</table>';
		}

		$liste_hook = SubBlocksClass::getHookListe((int)$this->context->language->id, 0);

		if (count($liste_hook) > 0) {
			$this->html_out .= '
				<script src="' . (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http') . '://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
				<script type="text/javascript" src="' . __PS_BASE_URI__ . 'modules/prestablog/views/js/jquery.mjs.nestedSortable.js"></script>';

			foreach ($liste_hook as $hook_name) {
				if ($this->isPSVersion('>=', '1.6'))
					$this->html_out .= '<div class="panel">';

				$liste = SubBlocksClass::getListe(null, 0, $hook_name);

				$this->html_out .= '<fieldset>';
				$this->html_out .= '<legend style="margin-bottom:10px;">' . $this->l('Articles list for ') . '
				<span class="label label-success">' . $hook_name . '</span></legend>';
				$this->html_out .= '<table class="table" cellpadding="0" cellspacing="0" style="margin:auto;width:100%;">';
				$this->html_out .= '	<thead class="center">';
				$this->html_out .= '		<tr>';
				$this->html_out .= '			<th>Id</th>';

				if ($hook_name == 'displayCustomHook')
					$this->html_out .= '			<th>' . $this->l('Add this shortcode directly in your tpl') . '</th>';
				else
					$this->html_out .= '			<th>' . $this->l('Positions') . '</th>';

				$this->html_out .= '			<th>' . $this->l('Type') . '</th>';
				$this->html_out .= '			<th>' . $this->l('Languages') . '</th>';
				$this->html_out .= '			<th>' . $this->l('Categories') . '</th>';
				$this->html_out .= '			<th>' . $this->l('Limit list') . '</th>';
				$this->html_out .= '			<th>' . $this->l('Custom template') . '</th>';
				$this->html_out .= '			<th class="center">' . $this->l('Random') . '</th>';
				$this->html_out .= '			<th class="center">' . $this->l('Blog link') . '</th>';
				$this->html_out .= '			<th class="center">' . $this->l('Activate') . '</th>';
				$this->html_out .= '			<th class="center">' . $this->l('Actions') . '</th>';
				$this->html_out .= '		</tr>';
				$this->html_out .= '	</thead>';

				if (count($liste) > 0) {
					$sub_blocks = new SubBlocksClass();
					if ($hook_name == 'displayCustomHook')
						$this->html_out .= '	<tbody>';
					else
						$this->html_out .= '	<tbody id="subblocks_positions_' . $liste[0]['hook_name'] . '">';

					foreach ($liste as $value) {
						if ($hook_name == 'displayCustomHook')
							$this->html_out .= '	<tr>';
						else
							$this->html_out .= '	<tr class="odd" order-id="' . (int)$value['id_' . $this->name . '_subblock'] . '">';

						$this->html_out .= '		<td class="center">' . (int)$value['id_' . $this->name . '_subblock'] . '</td>';

						if ($hook_name == 'displayCustomHook') {
							$this->html_out .= '	<td><strong>{hook h=\'displayPrestaBlogList\' id=\''
								. (int)$value['id_' . $this->name . '_subblock'] . '\' mod=\'prestablog\'}</strong></td>';
						} else {
							$this->html_out .= '	<td class="center pointer" style="text-align:center;">
															<img src="../modules/' . $this->name . '/views/img/move.png" />
														</td>';
						}

						$select_type = $sub_blocks->getListeSelectType();
						$this->html_out .= '		<td >' . $select_type[(int)$value['select_type']] . '</td>';

						$this->html_out .= '		<td>';
						$lang_liste_news = unserialize($value['langues']);
						if (is_array($lang_liste_news) && count($lang_liste_news) > 0) {
							foreach ($lang_liste_news as $val_langue)
								$this->html_out .= ((count($languages) >= 1 && array_key_exists((int)$val_langue, $languages_shop)) ?
									'<img src="../img/l/' . (int)$val_langue . '.jpg" /> ' . SubBlocksClass::getTitleSubBlock((int)$value['id_' . $this->name . '_subblock'],
										(int)$val_langue) . '<br/>' : '');
						} else
							$this->html_out .= '-';
						$this->html_out .= '		</td>';

						$this->html_out .= '		<td >';
						$cat_verbose = '';

						if (is_array($value['blog_categories']) && count($value['blog_categories']) > 1) {
							foreach ($value['blog_categories'] as $id_category) {
								$category = new CategoriesClass((int)$id_category, (int)$this->context->cookie->id_lang);
								$cat_verbose .= $category->title . ', ';
							}
						} elseif (is_int($value['blog_categories'])) {
							$category = new CategoriesClass((int)$value['blog_categories'], (int)$this->context->cookie->id_lang);
							$cat_verbose .= $category->title;
						} else
							$cat_verbose = '-';

						$cat_verbose = rtrim(trim($cat_verbose), ',');
						$this->html_out .= $cat_verbose;
						$this->html_out .= '		</td>';
						$this->html_out .= '		<td class="center">' . (int)$value['nb_list'] . '</td>';
						$this->html_out .= '		<td class="center">' . ($value['template'] != '' ? $value['template'] : '-') . '</td>';
						$this->html_out .= '		<td class="center">
							<a href="' . $this->path_module_conf . '&randSubBlock&idSB=' . $value['id_' . $this->name . '_subblock'] . '">
							' . ($value['random'] ? '<img src="../modules/' . $this->name . '/views/img/enabled.gif" />' :
								'<img src="../modules/' . $this->name . '/views/img/disabled.gif" />') . '
							</a>
						</td>';
						$this->html_out .= '		<td class="center">
							<a href="' . $this->path_module_conf . '&blog_linkSubBlock&idSB=' . $value['id_' . $this->name . '_subblock'] . '">
							' . ($value['blog_link'] ? '<img src="../modules/' . $this->name . '/views/img/enabled.gif" />' :
								'<img src="../modules/' . $this->name . '/views/img/disabled.gif" />') . '
							</a>
						</td>';
						$this->html_out .= '		<td class="center">
							<a href="' . $this->path_module_conf . '&etatSubBlock&idSB=' . $value['id_' . $this->name . '_subblock'] . '">
							' . ($value['actif'] ? '<img src="../modules/' . $this->name . '/views/img/enabled.gif" />' :
								'<img src="../modules/' . $this->name . '/views/img/disabled.gif" />') . '
							</a>
						</td>';
						$this->html_out .= '		<td class="center">
							<a href="' . $this->path_module_conf . '&editSubBlock&idSB=' . $value['id_' . $this->name . '_subblock'] . '"
							title="' . $this->l('Edit') . '"><img src="../modules/' . $this->name . '/views/img/edit.gif" /></a>';
						$this->html_out .= '		<a href="' . $this->path_module_conf . '&deleteSubBlock&idSB=' . $value['id_' . $this->name . '_subblock'] . '"
						onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');">
						<img src="../img/admin/delete.gif" alt="' . $this->l('Delete') . '" /></a>';
						$this->html_out .= '		</td>';
						$this->html_out .= '	</tr>';
					}
					$this->html_out .= '	</tbody>';
					$this->html_out .= '
						<script type="text/javascript">
							$(function() {
								$("#subblocks_positions_' . $value['hook_name'] . '").sortable({
										axis: \'y\',
										placeholder: "ui-state-highlight",
										update: function(event, ui) {
											$.ajax({
												url: \'' . $this->context->link->getAdminLink('AdminPrestaBlogAjax') . '\',
												type: "GET",
												data: {
													action: \'prestablogrun\',
													items: $(this).sortable(\'toArray\', { attribute: \'order-id\' }),
													ajax: true,
													do: \'sortSubBlocks\',
													id_shop: \'' . $this->context->shop->id . '\',
													hook_name: \'' . $value['hook_name'] . '\'
												},
												success:function(data){}
											});
										}
								}).disableSelection();
							});
						</script>';
				} else
					$this->html_out .= '<tr><td colspan="5" class="center">' . $this->l('No content registered') . '</td></tr>';

				$this->html_out .= '</table>';
				$this->html_out .= '</fieldset>';
				if ($this->isPSVersion('>=', '1.6'))
					$this->html_out .= '</div>';
			}
		}
	}

	private function displayListeCategories()
	{
		$liste = CategoriesClass::getListe((int)$this->context->language->id, 0);

		if ($this->isPSVersion('>=', '1.6')) {
			$this->html_out .= '
				<div class="panel">
					<fieldset class="row">
						<div class="col-sm-3">
							<a class="btn btn-primary" href="' . $this->path_module_conf . '&addCat">
								<i class="icon-plus"></i>
								' . $this->l('Add a category') . '
							</a>
						</div>
						<div class="col-sm-3">
							<a class="btn btn-primary" href="' . $this->path_module_conf . '&orderCat">
								<i class="icon-sort-numeric-asc"></i>
								' . $this->l('Order of categories') . '
							</a>
						</div>
					</fieldset>
				</div>';
		} else {
			$this->html_out .= '
				<table class="table" cellpadding="0" cellspacing="0" style="margin:auto;text-align:center">
					<tr>
						<th>
								<img src="../modules/' . $this->name . '/views/img/add.gif" />
								<a href="' . $this->path_module_conf . '&addCat" title="' . $this->l('Add a category') . '">'
				. $this->l('Add a category') . '</a>
						</th>';
			if ($this->isPSVersion('>=', '1.6'))
				$this->html_out .= '
							<th>
									<img src="../modules/' . $this->name . '/views/img/filter.png" />
									<a href="' . $this->path_module_conf . '&orderCat" title="' . $this->l('Order of categories') . '">'
					. $this->l('Order of categories') . '</a>
							</th>';
			$this->html_out .= '
					</tr>
				</table>';
		}

		if ($this->isPSVersion('>=', '1.6'))
			$this->html_out .= '<div class="panel">';

		$this->html_out .= '<fieldset>';
		$this->html_out .= '<legend style="margin-bottom:10px;">' . $this->l('Categories') . '</legend>';
		$this->html_out .= '<table class="table" cellpadding="0" cellspacing="0" style="margin:auto;width:100%;">';
		$this->html_out .= '	<thead class="center">';
		$this->html_out .= '		<tr>';
		$this->html_out .= '			<th>Id</th>';
		$this->html_out .= '			<th>' . $this->l('Image') . '</th>';
		$this->html_out .= '			<th>' . $this->l('Title') . '</th>';
		$this->html_out .= '			<th>' . $this->l('Title Meta') . '</th>';
		$this->html_out .= '			<th><img src="' . $this->_path . 'views/img/group.png">&nbsp;' . $this->l('Groups permissions') . '</th>';
		$this->html_out .= '			<th>' . $this->l('Use in articles') . '</th>';
		$this->html_out .= '			<th class="center">' . $this->l('Activate') . '</th>';
		$this->html_out .= '			<th class="center">' . $this->l('Actions') . '</th>';
		$this->html_out .= '		</tr>';
		$this->html_out .= '	</thead>';

		if (count($liste) > 0)
			$this->html_out .= $this->displayListeArborescenceCategories($liste);
		else
			$this->html_out .= '<tr><td colspan="5" class="center">' . $this->l('No content registered') . '</td></tr>';

		$this->html_out .= '</table>';
		$this->html_out .= '</fieldset>';

		if ($this->isPSVersion('>=', '1.6'))
			$this->html_out .= '</div>';
	}

	private function displayOrderCategories()
	{
		$html_libre = '';
		$liste = CategoriesClass::getListe((int)$this->context->language->id, 0);
		$html_libre .= $this->displayOrderTreeCategories($liste);
		$this->html_out .= '
			<script src="' . (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http') . '://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
			<script type="text/javascript" src="' . __PS_BASE_URI__ . 'modules/prestablog/views/js/jquery.mjs.nestedSortable.js"></script>
			<script type="text/javascript">
				$(document).ready(function(){
					$(\'div#hdclicconfiguration .treeordercat ol.sortable\').nestedSortable({
						forcePlaceholderSize: true,
						handle: \'div\',
						helper:	\'clone\',
						items: \'li\',
						opacity: .6,
						placeholder: \'placeholder\',
						revert: 250,
						tabSize: 25,
						tolerance: \'pointer\',
						toleranceElement: \'> div\',
						maxLevels: 10,

						isTree: true,
						expandOnHover: 700,
						startCollapsed: true
					});

					$(\'div#hdclicconfiguration .treeordercat .disclose\').on(\'click\', function() {
						$(this).closest(\'li\').toggleClass(\'mjs-nestedSortable-collapsed\').toggleClass(\'mjs-nestedSortable-expanded\');
					})

					$(\'form[name=formOrderCat]\').submit(function() {
						serialized = $(\'div#hdclicconfiguration .treeordercat ol.sortable\').nestedSortable(\'serialize\');
						$(\'input[name=newOrderCat]\').val(serialized);
					})
				});
			</script>';

		if ($this->isPSVersion('>=', '1.6')) {
			$this->html_out .= '
				<div class="panel">
					<fieldset class="row">
						<div class="col-sm-3">
							<a class="btn btn-primary" href="' . $this->path_module_conf . '&catListe">
								<i class="icon-list"></i>
								' . $this->l('Return to list of categories') . '
							</a>
						</div>
					</fieldset>
				</div>';
		} else {
			$this->html_out .= '
				<table class="table" cellpadding="0" cellspacing="0" style="margin:auto;text-align:center">
					<tr>
						<th>
								<img src="../modules/' . $this->name . '/views/img/categories.png" />
								<a href="' . $this->path_module_conf . '&catListe" title="' . $this->l('Return to list of categories') . '">'
				. $this->l('Return to list of categories') . '</a>
						</th>
					</tr>
				</table>';
		}

		$this->html_out .= $this->displayFormOpen('filter.png', $this->l('Order categories'), $this->path_module_conf, 'formOrderCat');
		$this->html_out .= '<input type="hidden" name="newOrderCat" value="" />';
		$this->html_out .= $this->displayInfo($this->l('Change the order of categories with a simple drag&drop.'));
		$this->html_out .= $this->displayFormLibre('col-lg-2', null, $html_libre, 'col-lg-7 treeordercat');
		$this->html_out .= $this->displayFormSubmit('submitOrderCat', 'icon-save', $this->l('Update'));
		$this->html_out .= $this->displayFormClose();
	}

	private function displayOrderTreeCategories($liste, &$count = 0)
	{
		$html_out = '';
		$html_out .= '<ol ' . ((int)$count == 0 ? 'class="sortable"' : '') . '>';
		foreach ($liste as $value) {
			$count += 1;
			$html_out .= '		<li id="list_' . (int)$value['id_prestablog_categorie'] . '">
									<div>
										<span class="disclose"><span></span></span>';
			if (file_exists(dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/c/adminth_'
				. $value['id_' . $this->name . '_categorie'] . '.jpg'))
				$html_out .= '<img class="thumb" src="' . $this->_path . 'views/img/' . Configuration::get($this->name . '_theme')
					. '/up-img/c/adminth_' . $value['id_' . $this->name . '_categorie'] . '.jpg?' . md5(time()) . '" />';
			$html_out .= $value['title'];
			$html_out .= '			</div>';
			if (count($value['children']) > 0)
				$html_out .= $this->displayOrderTreeCategories($value['children'], $count);

			$html_out .= '		</li>';
		}
		$html_out .= '</ol>';
		return $html_out;
	}

	private function displayListeArborescenceCategoriesNews($liste_cat, $decalage = 0, $liste_id_branch_deploy = array())
	{
		$html_out = '';
		foreach ($liste_cat as $value) {
			$active = false;
			if ((Tools::getValue('idN') && in_array((int)$value['id_prestablog_categorie'],
						CorrespondancesCategoriesClass::getCategoriesListe((int)Tools::getValue('idN'))))
				|| (Tools::getValue('categories') && in_array((int)$value['id_prestablog_categorie'], Tools::getValue('categories')))
			)
				$active = true;

			$html_out .= '
			<tr class="prestablog_branch' . ($decalage > 0 ? ' childs' : '') . ($active ? ' alt_row' : '') . '" rel="' . $value['branch'] . '"
			id="prestablog_categorie_' . (int)$value['id_prestablog_categorie'] . '">
				<td>
					<input type="checkbox" name="categories[]" value="' . (int)$value['id_prestablog_categorie'] . '" '
				. ($active ? 'checked=checked' : '') . ' />
				</td>
				<td>' . $value['id_prestablog_categorie'] . '</td>';
			if (file_exists(dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/c/adminth_'
				. $value['id_' . $this->name . '_categorie'] . '.jpg')) {
				$html_out .= '		<td class="center"><img src="' . $this->_path . 'views/img/' . Configuration::get($this->name . '_theme')
					. '/up-img/c/adminth_' . $value['id_' . $this->name . '_categorie'] . '.jpg?' . md5(time()) . '" /></td>';
			} else
				$html_out .= '		<td class="center">-</td>';

			$html_out .= '<td>';

			$liste_cat_lang = CategoriesClass::getListeNoArbo();
			$languages = Language::getLanguages(true);

			foreach ($languages as $language) {
				foreach ($liste_cat_lang as $cat_lang) {
					if ((int)$cat_lang['id_prestablog_categorie'] == (int)$value['id_prestablog_categorie']
						&& (int)$cat_lang['id_lang'] == (int)$language['id_lang']
					) {
						$html_out .= '<div class="catlang" rel="' . (int)$language['id_lang'] . '">
												<span style="' . ($decalage > 0 ? 'padding-left:' . ($decalage * 20) . 'px;background: url(../modules/'
								. $this->name . '/views/img/decalage.png) no-repeat right center;' : '') . '"></span>';

						if (count($value['children']) > 0 && in_array((int)$value['id_prestablog_categorie'], $liste_id_branch_deploy))
							$html_out .= '<img src="' . $this->_path . 'views/img/collapse.gif" class="expand-cat" rel="' . $value['branch'] . '" />';
						elseif (count($value['children']) > 0 && !in_array((int)$value['id_prestablog_categorie'], $liste_id_branch_deploy))
							$html_out .= '<img src="' . $this->_path . 'views/img/expand.gif" class="expand-cat" rel="' . $value['branch'] . '" />';

						if ($active)
							$html_out .= '<strong>' . $cat_lang['title'] . '</strong>';
						else
							$html_out .= $cat_lang['title'];

						$liste_groupes_categorie = CategoriesClass::getGroupsFromCategorie((int)$value['id_prestablog_categorie']);

						if (count($liste_groupes_categorie) > 0) {
							$html_out .= '<div><small>';
							$html_out_loop = '<img src="' . $this->_path . 'views/img/group.png">&nbsp;';
							foreach ($liste_groupes_categorie as $groupe) {
								$group = new Group((int)$groupe, (int)$language['id_lang']);
								$html_out_loop .= $group->name . ', ';
							}
							$html_out_loop = rtrim(trim($html_out_loop), ',');
							$html_out .= $html_out_loop . '</small></div>';
						}

						$html_out .= '</div>';
					}
				}
			}
			$html_out .= '
				</td>
			</tr>';
			if (count($value['children']) > 0)
				$html_out .= $this->displayListeArborescenceCategoriesNews($value['children'], $decalage + 1, $liste_id_branch_deploy);
		}
		return $html_out;
	}

	private function displayListeArborescenceCategoriesSubBlocks($liste_cat, $decalage = 0, $liste_id_branch_deploy = array())
	{
		$html_out = '';
		foreach ($liste_cat as $value) {
			$active = false;
			if ((Tools::getValue('idSB') && in_array((int)$value['id_prestablog_categorie'],
						SubBlocksClass::getCategories((int)Tools::getValue('idSB'), 0)))
				|| (Tools::getValue('categories') && in_array((int)$value['id_prestablog_categorie'], Tools::getValue('categories')))
			)
				$active = true;

			$html_out .= '
			<tr class="prestablog_branch' . ($decalage > 0 ? ' childs' : '') . ($active ? ' alt_row' : '') . '" rel="' . $value['branch'] . '"
			id="prestablog_categorie_' . (int)$value['id_prestablog_categorie'] . '">
				<td>
					<input type="checkbox" name="categories[]" value="' . (int)$value['id_prestablog_categorie'] . '" ' . ($active ? 'checked=checked' : '') . ' />
				</td>
				<td>' . $value['id_prestablog_categorie'] . '</td>';
			if (file_exists(dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/c/adminth_'
				. $value['id_' . $this->name . '_categorie'] . '.jpg')) {
				$html_out .= '		<td class="center"><img src="' . $this->_path . 'views/img/' . Configuration::get($this->name . '_theme')
					. '/up-img/c/adminth_' . $value['id_' . $this->name . '_categorie'] . '.jpg?' . md5(time()) . '" /></td>';
			} else
				$html_out .= '		<td class="center">-</td>';

			$html_out .= '<td>';

			$liste_cat_lang = CategoriesClass::getListeNoArbo();
			$languages = Language::getLanguages(true);

			foreach ($languages as $language) {
				foreach ($liste_cat_lang as $cat_lang) {
					if ((int)$cat_lang['id_prestablog_categorie'] == (int)$value['id_prestablog_categorie']
						&& (int)$cat_lang['id_lang'] == (int)$language['id_lang']
					) {
						$html_out .= '<div class="catlang" rel="' . (int)$language['id_lang'] . '">
												<span style="' . ($decalage > 0 ? 'padding-left:' . ($decalage * 20) . 'px;background: url(../modules/'
								. $this->name . '/views/img/decalage.png) no-repeat right center;' : '') . '"></span>';

						if (count($value['children']) > 0 && in_array((int)$value['id_prestablog_categorie'], $liste_id_branch_deploy))
							$html_out .= '<img src="' . $this->_path . 'views/img/collapse.gif" class="expand-cat" rel="' . $value['branch'] . '" />';
						elseif (count($value['children']) > 0 && !in_array((int)$value['id_prestablog_categorie'], $liste_id_branch_deploy))
							$html_out .= '<img src="' . $this->_path . 'views/img/expand.gif" class="expand-cat" rel="' . $value['branch'] . '" />';

						if ($active)
							$html_out .= '<strong>' . $cat_lang['title'] . '</strong>';
						else
							$html_out .= $cat_lang['title'];

						$html_out .= '</div>';
					}
				}
			}
			$html_out .= '
				</td>
			</tr>';
			if (count($value['children']) > 0)
				$html_out .= $this->displayListeArborescenceCategoriesSubBlocks($value['children'], $decalage + 1, $liste_id_branch_deploy);
		}
		return $html_out;
	}

	private function displayListeArborescenceCategories($liste, $decalage = 0)
	{
		$html_out = '';
		foreach ($liste as $value) {
			$html_out .= '	<tr>';
			$html_out .= '		<td class="center">' . ($value['id_' . $this->name . '_categorie']) . '</td>';
			if (file_exists(dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/c/adminth_'
				. $value['id_' . $this->name . '_categorie'] . '.jpg')) {
				$html_out .= '		<td class="center"><img src="' . $this->_path . 'views/img/' . Configuration::get($this->name . '_theme')
					. '/up-img/c/adminth_' . $value['id_' . $this->name . '_categorie'] . '.jpg?' . md5(time()) . '" /></td>';
			} else
				$html_out .= '		<td class="center">-</td>';
			$html_out .= '		<td><span style="' . ($decalage > 0 ? 'padding-left:' . ($decalage * 20) . 'px;background: url(../modules/'
					. $this->name . '/views/img/decalage.png) no-repeat right center;' : '') . '"></span>' . $value['title'] . '</td>';
			$html_out .= ($value['meta_title'] ? '<td style="font-size:90%;">' . $value['meta_title'] . '</td>' : '<td style="text-align:center;">-</td>');
			$html_out .= '<td style="text-align:center;">';

			$liste_groupes_categorie = CategoriesClass::getGroupsFromCategorie((int)$value['id_prestablog_categorie']);

			if (count($liste_groupes_categorie) > 0) {
				$html_out .= '<div><small>';
				$html_out_loop = '';
				foreach ($liste_groupes_categorie as $groupe) {
					$group = new Group((int)$groupe, (int)$this->context->language->id);
					$html_out_loop .= $group->name . ', ';
				}
				$html_out_loop = rtrim(trim($html_out_loop), ',');
				$html_out .= $html_out_loop . '</small></div>';
			} else
				$html_out .= '-';

			$html_out .= '</td>';
			$html_out .= '<td style="text-align:center;">' . CategoriesClass::getNombreNewsDansCat((int)$value['id_' . $this->name . '_categorie']) . '</td>';
			$html_out .= '		<td class="center">
				<a href="' . $this->path_module_conf . '&etatCat&idC=' . $value['id_' . $this->name . '_categorie'] . '">
				' . ($value['actif'] ? '<img src="../modules/' . $this->name . '/views/img/enabled.gif" />' : '<img src="../modules/'
					. $this->name . '/views/img/disabled.gif" />') . '
				</a>
			</td>';
			$html_out .= '		<td class="center">
				<a href="' . $this->path_module_conf . '&editCat&idC=' . $value['id_' . $this->name . '_categorie'] . '" title="' . $this->l('Edit') . '">
				<img src="../modules/' . $this->name . '/views/img/edit.gif" /></a>';
			if (!count($value['children'])) {
				if ((count($liste) > 1 && $decalage == 0) || $decalage > 0) {
					$html_out .= '		<a href="' . $this->path_module_conf . '&deleteCat&idC=' . $value['id_' . $this->name . '_categorie'] . '"
						onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');">
						<img src="../img/admin/delete.gif" alt="' . $this->l('Delete') . '" /></a>';
				} else {
					$html_out .= '		<a href="#" onclick="return confirm(\''
						. $this->l('You must add an new category before delete this last one !', __CLASS__, true, false) . '\');">
							<img src="../img/admin/delete.gif" alt="' . $this->l('Delete') . '" /></a>';
				}
			} else {
				$html_out .= '		<a href="#" onclick="return alert(\''
					. $this->l('For delete parent category, you should delete all child before !') . '\');">
						<img src="../img/admin/delete.gif" alt="' . $this->l('Delete') . '" /></a>';
			}
			$html_out .= '		</td>';
			$html_out .= '	</tr>';
			if (count($value['children']) > 0)
				$html_out .= $this->displayListeArborescenceCategories($value['children'], $decalage + 1);
		}
		return $html_out;
	}

	private function displayDebug()
	{
		$this->html_out .= '<fieldset style="margin:auto;">';
		$this->html_out .= '<legend style="margin-bottom:10px;"><img src="../modules/' . $this->name . '/views/img/debug.png" /> '
			. $this->l('Debug module') . '</legend>';
		$this->html_out .= $this->displayWarning('
				<p>' . $this->l('This debug is expected to reveal the errors after an installation or upgrade.') . '</p>
				<p>' . $this->l('It is based on the knowledge base of customer feedback, bugs or simple errors.') . '</p>
			');
		$this->html_out .= '</fieldset>';
	}

	private function displayInformations()
	{
		$informations = array(
			'host' => array(
				'version' => array(
					'php' => phpversion(),
					'server' => $_SERVER['SERVER_SOFTWARE'],
					'user_agent' => $_SERVER['HTTP_USER_AGENT'],
					'uname' => function_exists('php_uname') ? php_uname('s') . ' ' . php_uname('v') . ' ' . php_uname('m') : '',
					'memory_limit' => ini_get('memory_limit'),
					'max_execution_time' => ini_get('max_execution_time'),
					'display_errors' => ini_get('display_errors'),
					'magic_quotes' => (_PS_MAGIC_QUOTES_GPC_ ? 'true' : 'false'),
				),
				'database' => array(
					'version' => Db::getInstance()->getVersion(),
					'prefix' => _DB_PREFIX_,
					'engine' => _MYSQL_ENGINE_,
					'ps_version' => Configuration::get('PS_VERSION_DB'),
				),
			),
			'prestashop' => array(
				'ps_version' => _PS_VERSION_,
				'ps_version_install' => Configuration::get('PS_INSTALL_VERSION'),
				'ps_ssl' => Configuration::get('PS_SSL_ENABLED'),
				'url_front' => Tools::getHttpHost(true) . __PS_BASE_URI__,
				'url_admin' => $_SERVER['PHP_SELF'],
				'domain' => Configuration::get('PS_SHOP_DOMAIN'),
				'domain_ssl' => Configuration::get('PS_SHOP_DOMAIN_SSL'),
				'theme' => _THEME_NAME_,
				'mobile' => Configuration::get('PS_ALLOW_MOBILE_DEVICE'),
				'mail_method' => Configuration::get('PS_MAIL_METHOD'),
				'ps_rewrite' => Configuration::get('PS_REWRITING_SETTINGS'),
				'accented_chars_url' => Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL'),
				'css_cache' => Configuration::get('PS_CSS_THEME_CACHE'),
				'js_cache' => Configuration::get('PS_JS_THEME_CACHE'),
				'html_compression' => Configuration::get('PS_HTML_THEME_COMPRESSION'),
				'js_html_compression' => Configuration::get('PS_JS_HTML_THEME_COMPRESSION'),
				'mode_dev' => (_PS_MODE_DEV_ ? 'true' : 'false'),
				'debug_sql' => (_PS_DEBUG_SQL_ ? 'true' : 'false'),
				'display_compatibility_warning' => (_PS_DISPLAY_COMPATIBILITY_WARNING_ ? 'true' : 'false'),
				'mode_demo' => (_PS_MODE_DEMO_ ? 'true' : 'false'),
			),
			'prestablog' => array(
				'core' => array(
					'version' => $this->version,
					'module_key' => $this->module_key,
				),
			),
		);

		$shops = Shop::getShops();
		foreach ($shops as $key_shop => $value_shop) {
			foreach ($value_shop as $key_s => $value_s)
				$informations['shop' . $key_shop][$key_s] = $value_s;

			$configuration_mail = Configuration::getMultiple(array(
				'PS_SHOP_EMAIL',
				'PS_MAIL_METHOD',
				'PS_MAIL_SERVER',
				'PS_MAIL_USER',
				'PS_SHOP_NAME',
				'PS_MAIL_SMTP_ENCRYPTION',
				'PS_MAIL_SMTP_PORT',
				'PS_MAIL_TYPE'
			), null, null, $key_shop);

			foreach ($configuration_mail as $key_conf_mail => $value_conf_mail)
				$informations['shop' . $key_shop][$key_conf_mail] = $value_conf_mail;

			foreach (array_keys($this->configurations) as $configuration_key)
				$informations['shop' . $key_shop]['config'][$configuration_key] = Configuration::get($configuration_key, false, null, $key_shop);
		}

		$this->html_out .= $this->displayFormOpen('icon-info', $this->l('Informations'), $this->path_module_conf);

		$infos = '';
		foreach ($informations as $kcore => $vcore) {
			if (is_array($vcore)) {
				foreach ($vcore as $kth => $vth) {
					if (is_array($vth)) {
						foreach ($vth as $kinfo => $vinfo)
							$infos .= $kcore . '_' . $kth . '_' . $kinfo . ' : ' . $vinfo . "\n";
					} else
						$infos .= $kcore . '_' . $kth . ' : ' . $vth . "\n";
				}
			} else
				$infos .= $kcore . ' : ' . $vcore . "\n";
			$infos .= "\n";
		}

		if (!$this->demo_mode)
			$html_libre = '<textarea ' . (self::isPSVersion('<', '1.6') ? 'style="width:500px;height:300px;"' :
					'style="height:300px;"') . '>' . $infos . '</textarea></p>';
		else
			$html_libre = $this->displayWarning($this->l('Feature disabled on the demo mode'));
		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Informations'), $html_libre, 'col-lg-7');

		$this->html_out .= $this->displayFormClose();
	}

	private function displayImport()
	{
		$languages = Language::getLanguages(true);

		if ($this->demo_mode)
			$this->html_out .= $this->displayWarning($this->l('Feature disabled on the demo mode'));

		$this->html_out .= $this->displayFormOpen('icon-upload', $this->l('Import from Wordpress XML file'), $this->path_module_conf);

		$this->html_out .= $this->displayWarning($this->l('Be carrefull ! Select only "Articles" exportation on your WordPress.'));

		$this->html_out .= $this->displayFormFile('col-lg-2', $this->l('Upload file'), $this->name . '_import_xml',
			'col-lg-5', $this->l('Format:') . ' *.XML');

		$this->html_out .= $this->displayFormSubmit('submitImportXml', 'icon-cloud-upload', $this->l('Send file'));

		$this->html_out .= $this->displayFormClose();

		if (Configuration::get($this->name . '_import_xml')) {
			if (!file_exists(_PS_UPLOAD_DIR_ . Configuration::get($this->name . '_import_xml')))
				$this->html_out .= $this->displayError($this->l('The XML file in the configuration is not locate in the ./download directory')
					. '<br/>' . $this->l('You must upload a new import XML file.'));
			else {
				$file_content = Tools::file_get_contents(_PS_UPLOAD_DIR_ . Configuration::get($this->name . '_import_xml'));
				if (strpos($file_content, '<?xml') === false)
					$this->html_out .= $this->displayError(
						$this->l('The file is not an XML content') . '<br/>' . $this->l('You must upload a new import XML file.')
					);
				else {
					$this->html_out .= $this->displayFormOpen('icon-gear', $this->l('Import the XML file to language'), $this->path_module_conf);

					$html_libre = $this->l('Current XML import file in configuration :') . ' ' . Configuration::get($this->name . '_import_xml');
					$this->html_out .= $this->displayFormLibre('col-lg-2', '', $html_libre, 'col-lg-7');

					$html_libre = '';
					foreach ($languages as $language) {
						$html_libre .= '	<input type="radio" name="import_xml_langue" value="' . (int)$language['id_lang'] . '" '
							. ($this->langue_default_store == (int)$language['id_lang'] ? 'checked' : '') . '>
							<img src="../img/l/' . (int)$language['id_lang'] . '.jpg" />&nbsp;&nbsp;&nbsp;';
					}

					$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Select language'), $html_libre, 'col-lg-7');
					$this->html_out .= $this->displayFormSubmit('submitParseXml', 'icon-gears', $this->l('Import the current file'));

					$this->html_out .= $this->displayFormClose();
				}
			}
		}
	}

	private function displayConfigAntiSpam()
	{
		$liste = AntiSpamClass::getListe((int)$this->context->language->id, 0);

		$this->html_out .= $this->displayFormOpen('icon-shield', $this->l('Antispam questions'), $this->path_module_conf);

		$this->html_out .= $this->displayInfo('
				<p>' . $this->l('This Antispam option can protect you to comments of spammers robots.') . '</p>
				<p>' . $this->l('A question from all those you recorded will be placed randomly in the submission of a comment.'));

		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-2', $this->l('Antispam activation'),
			$this->name . '_antispam_actif');

		$this->html_out .= $this->displayFormSubmit('submitAntiSpamConfig', 'icon-save', $this->l('Update the configuration'));

		$this->html_out .= $this->displayFormClose();

		if ($this->isPSVersion('>=', '1.6')) {
			$this->html_out .= '
				<div class="panel">
					<fieldset>
						<div class="col-sm-3">
							<a class="btn btn-primary" href="' . $this->path_module_conf . '&addAntiSpam">
								<i class="icon-plus"></i>
								' . $this->l('Add an antispam question') . '
							</a>
						</div>
					</fieldset>
				</div>';
		} else {
			$this->html_out .= '
				<table class="table" cellpadding="0" cellspacing="0" style="margin:auto;text-align:center">
					<tr>
						<th>
								<img src="../modules/' . $this->name . '/views/img/add.gif" />
								<a href="' . $this->path_module_conf . '&addAntiSpam" title="' . $this->l('Add an antispam question') . '">'
				. $this->l('Add an antispam question') . '</a>
						</th>
					</tr>
				</table>';
		}

		$this->html_out .= '<div class="panel">
								<table class="table" cellpadding="0" cellspacing="0" style="width:100%;margin:auto;">';
		$this->html_out .= '	<thead class="center">';
		$this->html_out .= '		<tr>';
		$this->html_out .= '			<th></th>';
		$this->html_out .= '			<th>' . $this->l('Question') . '</th>';
		$this->html_out .= '			<th>' . $this->l('Expected reply') . '</th>';
		$this->html_out .= '			<th class="center">' . $this->l('Activate') . '</th>';
		$this->html_out .= '			<th class="center">' . $this->l('Actions') . '</th>';
		$this->html_out .= '		</tr>';
		$this->html_out .= '	</thead>';
		if (count($liste) > 0) {
			foreach ($liste as $value) {
				$this->html_out .= '	<tr>';
				$this->html_out .= '		<td class="center">' . $value['id_' . $this->name . '_antispam'] . '</td>';
				$this->html_out .= '		<td>' . $value['question'] . '</td>';
				$this->html_out .= '		<td>' . $value['reply'] . '</td>';

				$this->html_out .= '		<td class="center">
					<a href="' . $this->path_module_conf . '&etatAntiSpam&idAS=' . $value['id_' . $this->name . '_antispam'] . '">
					' . ($value['actif'] ? '<img src="../modules/' . $this->name . '/views/img/enabled.gif" />' : '<img src="../modules/'
						. $this->name . '/views/img/disabled.gif" />') . '
					</a>
				</td>';
				$this->html_out .= '		<td class="center">
					<a href="' . $this->path_module_conf . '&editAntiSpam&idAS=' . $value['id_' . $this->name . '_antispam'] . '"
					title="' . $this->l('Edit') . '"><img src="../modules/' . $this->name . '/views/img/edit.gif" /></a>';
				$this->html_out .= '		<a href="' . $this->path_module_conf . '&deleteAntiSpam&idAS=' . $value['id_' . $this->name . '_antispam'] . '"
				onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');">
				<img src="../img/admin/delete.gif" alt="' . $this->l('Delete') . '" /></a>';
				$this->html_out .= '		</td>';
				$this->html_out .= '	</tr>';
			}
		} else
			$this->html_out .= '<tr><td colspan="5" class="center">' . $this->l('No content registered') . '</td></tr>';

		$this->html_out .= '</table>';
		$this->html_out .= '</div>';
	}

	public function deleteSitemapFromShop($id_shop)
	{
		$directory_site_map = _PS_MODULE_DIR_ . $this->name . '/sitemap/' . (int)$id_shop;

		foreach (glob($directory_site_map . '/*.{xml}', GLOB_BRACE) as $file) {
			if (!is_dir($file))
				self::unlinkFile($directory_site_map . '/' . basename($file));
		}
	}

	public function createTheShopSitemap()
	{
		$this->deleteSitemapFromShop((int)$this->context->shop->id);

		$languages = Language::getLanguages(true, (int)$this->context->shop->id);
		$module_shop_domain = ((Configuration::get('PS_SSL_ENABLED')) ? Tools::getShopDomainSsl(true) :
				Tools::getShopDomain(true)) . __PS_BASE_URI__;

		$xml = '<?xml version="1.0" encoding="UTF-8" ?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>';
		$xml_feed = new SimpleXMLElement($xml);

		$list_sitemap = array();
		if (Configuration::get($this->name . '_sitemap_articles'))
			$list_sitemap[] = 'articles';
		if (Configuration::get($this->name . '_sitemap_categories'))
			$list_sitemap[] = 'categories';

		$all_urls_form_language = array();

		foreach ($languages as $lang) {
			foreach ($list_sitemap as $type_list) {
				switch ($type_list) {
					case 'articles':
						$all_urls_form_language = NewsClass::getListe(
							(int)$lang['id_lang'],
							1,
							0,
							0,
							null,
							'n.`date`',
							'desc',
							Date('Y-m-d H:i:s', strtotime('-' . (int)Configuration::get($this->name . '_sitemap_older') . ' months')),
							null,
							null,
							1,
							(int)Configuration::get('prestablog_news_title_length'),
							(int)Configuration::get('prestablog_news_intro_length')
						);
						break;

					case 'categories':
						$all_urls_form_language = CategoriesClass::getListeNoArbo(1, (int)$lang['id_lang']);
						break;

					default:
						$all_urls_form_language = array();
						break;
				}

				$xmls_urls = array_chunk($all_urls_form_language, (int)Configuration::get($this->name . '_sitemap_limit'));
				foreach ($xmls_urls as $xml_key => $xml_urls) {
					$location_file = $this->name . '/sitemap/' . (int)$this->context->shop->id . '/' . $type_list . '_' . $lang['iso_code'] . '_'
						. (int)$xml_key . '.xml';
					$this->createSplitSitemap($location_file, $xml_urls, $type_list);

					$sitemap = $xml_feed->addChild('sitemap');
					$sitemap->addAttribute('lang', $lang['iso_code']);
					$sitemap->addAttribute('type', 'text/html');
					$sitemap->addAttribute('charset', 'UTF-8');

					$sitemap->addChild('loc', $module_shop_domain . 'modules/' . $location_file);
					$sitemap->addChild('lastmod', date('c'));
				}
			}
		}

		file_put_contents(_PS_MODULE_DIR_ . $this->name . '/sitemap/' . (int)$this->context->shop->id . '/master.xml', $xml_feed->asXML());

		return true;
	}

	public function createSplitSitemap($location_file, $urls, $type_list = '')
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
		xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"></urlset>';
		$xml_feed = new SimpleXMLElement($xml);
		$module_shop_domain = ((Configuration::get('PS_SSL_ENABLED')) ? Tools::getShopDomainSsl(true) :
				Tools::getShopDomain(true)) . __PS_BASE_URI__;

		foreach ($urls as $child) {
			switch ($type_list) {
				case 'articles':
					$sitemap = $xml_feed->addChild('url');
					$sitemap->addChild('priority', '0.9');
					$sitemap->addChild('loc', self::prestablogUrl(
						array(
							'id' => (int)$child['id_prestablog_news'],
							'seo' => $child['link_rewrite'],
							'titre' => $child['title'],
							'id_lang' => (int)$child['id_lang']
						)
					));

					$sitemap->addChild('lastmod', date('c', strtotime($child['date_modification'])));
					$sitemap->addChild('changefreq', 'weekly');

					if ($child['image_presente']) {
						$imagechild = $sitemap->addChild('image:image', null, 'http://www.google.com/schemas/sitemap-image/1.1');
						$imagechild->addChild('image:loc', $module_shop_domain . 'modules/' . $this->name . '/views/img/'
							. Configuration::get($this->name . '_theme') . '/up-img/' . $child['id_prestablog_news'] . '.jpg',
							'http://www.google.com/schemas/sitemap-image/1.1');
						$imagechild->addChild('image:title', $child['title'], 'http://www.google.com/schemas/sitemap-image/1.1');
					}

					break;

				case 'categories':
					if ($child['title'] != '') {
						$sitemap = $xml_feed->addChild('url');
						$sitemap->addChild('priority', '0.5');
						$sitemap->addChild('loc', self::prestablogUrl(
							array(
								'c' => (int)$child['id_prestablog_categorie'],
								'titre' => ($child['link_rewrite'] != '' ? $child['link_rewrite'] : $child['title']),
								'id_lang' => (int)$child['id_lang']
							)
						));

						$sitemap->addChild('changefreq', 'yearly');

						if ($child['image_presente']) {
							$imagechild = $sitemap->addChild('image:image', null, 'http://www.google.com/schemas/sitemap-image/1.1');
							$imagechild->addChild('image:loc', $module_shop_domain . 'modules/' . $this->name . '/views/img/'
								. Configuration::get($this->name . '_theme') . '/up-img/c/' . $child['id_prestablog_categorie'] . '.jpg',
								'http://www.google.com/schemas/sitemap-image/1.1');
							$imagechild->addChild('image:title', $child['title'], 'http://www.google.com/schemas/sitemap-image/1.1');
						}
					}
					break;
			}
		}

		file_put_contents(_PS_MODULE_DIR_ . $location_file, $xml_feed->asXML());

		return true;
	}

	private function checkCurrentSitemap()
	{
		$directory_site_map = _PS_MODULE_DIR_ . $this->name . '/sitemap/' . (int)$this->context->shop->id;
		$module_shop_domain = ((Configuration::get('PS_SSL_ENABLED')) ? Tools::getShopDomainSsl(true) :
				Tools::getShopDomain(true)) . __PS_BASE_URI__;

		$html_out = '';

		if ($this->isPSVersion('>=', '1.6'))
			$html_out .= '	<p>
								<a onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');"
								class="btn btn-primary" href="' . $this->path_module_conf . '&deleteSitemap">
									<i class="icon-trash-o"></i>
									' . $this->l('Delete all sitemap xml for this shop') . '
								</a>
							</p>';
		else
			$html_out .= '	<p>
								<img src="../modules/' . $this->name . '/views/img/delete.gif" />
								<a onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');"
								href="' . $this->path_module_conf . '&deleteSitemap" title="' . $this->l('Delete all sitemap xml for this shop') . '">'
				. $this->l('Delete all sitemap xml for this shop') . '</a>
							</p>';

		$html_out .= '<p>' . $this->l('This sitemap is regrouping all other, you must use it if you want all your blog page to be crawled :') . '</p>';
		$liste_sitemap = glob($directory_site_map . '/master.{xml}', GLOB_BRACE);
		if (count($liste_sitemap) > 0) {
			$html_out .= '<ul>';
			foreach ($liste_sitemap as $file) {
				if (!is_dir($file)) {
					$url_sitemap = $module_shop_domain . 'modules/' . $this->name . '/sitemap/' . (int)$this->context->shop->id . '/' . basename($file);
					$html_out .= '<li><a href="' . $url_sitemap . '" target="_blank">' . $url_sitemap . '</a></li>';
				}
			}
			$html_out .= '</ul>';
		} else
			$html_out = '<p>' . $this->l('no xml file available') . '</p>';

		$html_out .= '<hr/>';

		$html_out .= '<p>' . sprintf($this->l('All sitemaps can be crawled individually for %1$s store :'),
				'<strong>' . $this->context->shop->name . '</strong>') . '</p>';
		$liste_sitemap = glob($directory_site_map . '/*[^master]*.{xml}', GLOB_BRACE);
		if (count($liste_sitemap) > 0) {
			$html_out .= '<ul>';
			foreach ($liste_sitemap as $file) {
				if (!is_dir($file)) {
					$url_sitemap = $module_shop_domain . 'modules/' . $this->name . '/sitemap/' . (int)$this->context->shop->id . '/' . basename($file);
					$html_out .= '<li><a href="' . $url_sitemap . '" target="_blank">' . $url_sitemap . '</a></li>';
				}
			}
			$html_out .= '</ul>';
		} else
			$html_out = '<p>' . $this->l('no xml file available') . '</p>';

		return $html_out;
	}

	private function displaySitemap()
	{
		$this->html_out .= '<div class="' . ($this->isPSVersion('>=', '1.6') ? 'col-md-5' : 'demi') . '">';

		$this->html_out .= $this->displayFormOpen('icon-sitemap', $this->l('Sitemap configuration'), $this->path_module_conf);

		$this->html_out .= $this->displayInfo(
			$this->l('The Sitemaps protocol allows a webmaster to inform search engines about URLs on a website that are available for crawling.'));
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-4', $this->l('Sitemap activation'),
			$this->name . '_sitemap_actif');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-4', $this->l('Articles'),
			$this->name . '_sitemap_articles');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-4', $this->l('Categories'),
			$this->name . '_sitemap_categories');
		$this->html_out .= $this->displayFormInput('col-lg-4', $this->l('Limit url number per xml file'),
			$this->name . '_sitemap_limit', Configuration::get($this->name . '_sitemap_limit'), 10, 'col-lg-4', $this->l('Urls/xml'));
		$this->html_out .= $this->displayFormInput('col-lg-4', $this->l('Date since'), $this->name . '_sitemap_older',
			Configuration::get($this->name . '_sitemap_older'), 10, 'col-lg-3', $this->l('Month(s)'),
			$this->l('Since : ') . date('d/m/Y', strtotime('-' . (int)Configuration::get($this->name . '_sitemap_older') . ' months')));
		$this->html_out .= $this->displayFormInput('col-lg-4', $this->l('Token security for cron'),
			$this->name . '_sitemap_token', Configuration::get($this->name . '_sitemap_token'), 10, 'col-lg-6', null, $this->l('Locked'));
		$this->html_out .= $this->displayFormSubmit('submitSitemapConfig', 'icon-save', $this->l('Update the configuration'));

		$this->html_out .= $this->displayFormClose();

		$this->html_out .= '</div>';
		$this->html_out .= '<div class="' . ($this->isPSVersion('>=', '1.6') ? 'col-md-7' : 'demi') . '">';
		$this->html_out .= $this->displayFormOpen('icon-cogs', $this->l('Sitemap manual'), $this->path_module_conf);

		$this->html_out .= $this->displayWarning(sprintf($this->l('This action will erase all current sitemaps for the shop %1$s'),
			'<strong>' . $this->context->shop->name . '</strong>'));
		$this->html_out .= $this->displayFormSubmit('submitSitemapGenerate', 'icon-cog', $this->l('Generate sitemap'));

		$this->html_out .= $this->displayFormClose();

		$this->html_out .= $this->displayFormOpen('icon-cogs', $this->l('Sitemap automatic with cron url'), $this->path_module_conf);

		$this->html_out .= $this->displayWarning(sprintf($this->l('This action will erase all current sitemaps for the shop %1$s'),
			'<strong>' . $this->context->shop->name . '</strong>'));

		$this->html_out .= '<p>'
			. $this->l('Ask your hosting provider to setup a "Cron task" to load the following URL at the time you would like:') . '</p>';
		$this->html_out .= '<p><strong>' . $this->l(
				(Configuration::get('PS_SSL_ENABLED') ?
					Tools::getShopDomainSsl(true) :
					Tools::getShopDomain(true)) . __PS_BASE_URI__ .
				'index.php?fc=module&module=prestablog&controller=sitemap&id_shop='
				. (int)$this->context->shop->id . '&token=' . Configuration::get($this->name . '_sitemap_token')
			) . '</strong></p>';
		$this->html_out .= '<p>' . $this->l('It will automatically generate your XML Sitemaps.') . '</p>';

		$this->html_out .= $this->displayFormClose();

		$this->html_out .= $this->displayInfo($this->checkCurrentSitemap());

		$this->html_out .= '</div>';
	}

	private function displayPageBlog()
	{
		$languages = Language::getLanguages(true);
		$div_lang_name = 'meta_title¤meta_description¤title_h1';

		$this->html_out .= '<script type="text/javascript">id_language = Number(' . $this->langue_default_store . ');</script>';

		$this->html_out .= $this->displayFormOpen('blog.png', $this->l('Blog page configuration'), $this->path_module_conf);

		$info = '<p>' . $this->l('If you have a custom menu, or if you want to make an acces to your blog page, you can use this link :') . '</p>
						<ul>';
		$multilang = (Language::countActiveLanguages() > 1);

		if ($multilang) {
			$languages = Language::getLanguages(true);
			foreach ($languages as $language) {
				if ((int)Configuration::get('prestablog_rewrite_actif')) {
					if ((int)Configuration::get('PS_REWRITING_SETTINGS'))
						$url_page_blog = ((Configuration::get('PS_SSL_ENABLED')) ? Tools::getShopDomainSsl(true) :
								Tools::getShopDomain(true)) . __PS_BASE_URI__ . Language::getIsoById((int)$language['id_lang']) . '/blog';
					else
						$url_page_blog = ((Configuration::get('PS_SSL_ENABLED')) ? Tools::getShopDomainSsl(true) :
								Tools::getShopDomain(true)) . __PS_BASE_URI__ . '?fc=module&module=prestablog&controller=' . self::getUrlPath() . '&id_lang=' . (int)$language['id_lang'];
				} else {
					if ((int)Configuration::get('PS_REWRITING_SETTINGS'))
						$url_page_blog = ((Configuration::get('PS_SSL_ENABLED')) ? Tools::getShopDomainSsl(true) :
								Tools::getShopDomain(true)) . __PS_BASE_URI__ . Language::getIsoById((int)$language['id_lang'])
							. '/?fc=module&module=prestablog&controller=' . self::getUrlPath() . '';
					else
						$url_page_blog = ((Configuration::get('PS_SSL_ENABLED')) ? Tools::getShopDomainSsl(true) :
								Tools::getShopDomain(true)) . __PS_BASE_URI__ . '?fc=module&module=prestablog&controller=' . self::getUrlPath() . '&id_lang='
							. (int)$language['id_lang'];
				}

				$info .= '<li><img src="../../img/l/' . $language['id_lang'] . '.jpg" style="vertical-align:middle;" />
									<a href="' . $url_page_blog . '" target="_blank">' . $url_page_blog . '</a>
								</li>';
			}
		} else {
			if ((int)Configuration::get('PS_REWRITING_SETTINGS') && (int)Configuration::get('prestablog_rewrite_actif'))
				$url_page_blog = ((Configuration::get('PS_SSL_ENABLED')) ? Tools::getShopDomainSsl(true) :
						Tools::getShopDomain(true)) . __PS_BASE_URI__ . '' . self::getUrlPath() . '';
			else
				$url_page_blog = ((Configuration::get('PS_SSL_ENABLED')) ? Tools::getShopDomainSsl(true) :
						Tools::getShopDomain(true)) . __PS_BASE_URI__ . '?fc=module&module=prestablog&controller=' . self::getUrlPath() . '';

			$info .= '<li>
									<a href="' . $url_page_blog . '" target="_blank">' . $url_page_blog . '</a>
								</li>';
		}

		$info .= '</ul>';

		$this->html_out .= $this->displayInfo($info);

		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-2', $this->l('Slide on blogpage'),
			$this->name . '_pageslide_actif');

		/***********************************************************/
		$html_libre = '';
		foreach ($languages as $language) {
			$html_libre .= '<div id="meta_title_' . $language['id_lang'] . '" style="display: '
				. ($language['id_lang'] == $this->langue_default_store ? 'block' : 'none') . ';">
						<input ' . (self::isPSVersion('<', '1.6') ? 'class="fixInput15"' : '') . ' type="text" name="meta_title_'
				. $language['id_lang'] . '" id="meta_title_' . $language['id_lang'] . '" value="'
				. Configuration::get($this->name . '_titlepageblog_' . $language['id_lang']) . '" />
					</div>';
		}
		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Title Meta'), $html_libre, 'col-lg-7',
			$this->displayFlagsFor('meta_title', $div_lang_name));
		/***********************************************************/
		$html_libre = '';
		foreach ($languages as $language) {
			$html_libre .= '<div id="meta_description_' . $language['id_lang'] . '" style="display: '
				. ($language['id_lang'] == $this->langue_default_store ? 'block' : 'none') . ';">
						<input ' . (self::isPSVersion('<', '1.6') ? 'class="fixInput15"' : '') . ' type="text" name="meta_description_'
				. $language['id_lang'] . '" id="meta_description_' . $language['id_lang'] . '" value="'
				. Configuration::get($this->name . '_descpageblog_' . $language['id_lang']) . '" />
					</div>';
		}
		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Description Meta'), $html_libre, 'col-lg-7',
			$this->displayFlagsFor('meta_description', $div_lang_name));
		/***********************************************************/
		$html_libre = '';
		foreach ($languages as $language) {
			$html_libre .= '<div id="title_h1_' . $language['id_lang'] . '" style="display: '
				. ($language['id_lang'] == $this->langue_default_store ? 'block' : 'none') . ';">
						<input ' . (self::isPSVersion('<', '1.6') ? 'class="fixInput15"' : '') . ' type="text" name="title_h1_'
				. $language['id_lang'] . '" id="title_h1_' . $language['id_lang'] . '" value="'
				. Configuration::get($this->name . '_h1pageblog_' . $language['id_lang']) . '" />
					</div>';
		}
		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Title page H1'), $html_libre, 'col-lg-7',
			$this->displayFlagsFor('title_h1', $div_lang_name));
		/***********************************************************/

		$this->html_out .= $this->displayFormSubmit('submitPageBlog', 'icon-save', $this->l('Update'));
		$this->html_out .= $this->displayFormClose();
	}

	private function displayConfWizard()
	{
		$this->html_out .= $this->displayFormOpen('wizard.png', $this->l('Wizard templating'), $this->path_module_conf);
		$this->html_out .= $this->displayFormSubmit('submitWizard', 'icon-save', $this->l('Update'));
		$this->html_out .= $this->displayFormClose();
	}

	public static function scanListeThemes()
	{
		$liste = array();
		foreach (glob(_PS_MODULE_DIR_ . 'prestablog/views/config/*.{xml}', GLOB_BRACE) as $file) {
			if (!is_dir($file))
				$liste[] = rtrim(basename($file), '.xml');
		}
		return $liste;
	}

	private function displayConfTheme()
	{
		$this->html_out .= $this->displayFormOpen('theme.png', $this->l('Theme'), $this->path_module_conf);
		$themes = array();
		foreach (self::scanListeThemes() as $value_theme)
			$themes[$value_theme] = basename($value_theme);

		$this->html_out .= $this->displayFormSelect('col-lg-5',
			$this->l('Choose your module theme :'),
			'theme',
			Configuration::get($this->name . '_theme'),
			$themes,
			null,
			'col-lg-5');
		$this->html_out .= '
				<script language="javascript" type="text/javascript">
					$(document).ready(function() {
						$("#theme").change(function() {
							var src = $(this).val();
							$("#imagePreview").hide();
							$("#imagePreview").html(src ? "<img src=\'../modules/' . $this->name . '/views/img/" + src + "-preview.jpg\'>" : "");
							$("#imagePreview").fadeIn();
						});
					});
				</script>

				<label>' . $this->l('Preview :') . '</label>
				<div class="margin-form">
					<div id="imagePreview" style="border: 1px #ccc solid;text-align:center;padding:10px;">
						<img src="../modules/' . $this->name . '/views/img/' . Configuration::get($this->name . '_theme') . '-preview.jpg" />
					</div>
					<div class="clear"></div>
				</div>';
		$this->html_out .= $this->displayFormSubmit('submitTheme', 'icon-save', $this->l('Update'));
		$this->html_out .= $this->displayFormClose();
	}

	private function displayConfSlide()
	{
		$config_theme = $this->getConfigXmlTheme(Configuration::get($this->name . '_theme'));

		$this->html_out .= $this->displayFormOpen('slide.png', $this->l('Slideshow'), $this->path_module_conf);
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Slide on homepage'),
			$this->name . '_homenews_actif', $this->l('The slide will be displayed in the center column of the home page of your shop.'));
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Slide on blogpage'),
			$this->name . '_pageslide_actif', $this->l('The slide will be displayed in the top of first page articles list of blog.'));
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Number of slide to display'),
			$this->name . '_homenews_limit', Configuration::get($this->name . '_homenews_limit'), 10, 'col-lg-2');
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Title length'), $this->name . '_slide_title_length',
			Configuration::get($this->name . '_slide_title_length'), 10, 'col-lg-2', $this->l('caracters'));
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Introduction length'), $this->name . '_slide_intro_length',
			Configuration::get($this->name . '_slide_intro_length'), 10, 'col-lg-2', $this->l('caracters'));
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Slide picture width'), 'slide_picture_width',
			$config_theme->images->slide->width, 10, 'col-lg-2', $this->l('px'));
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Slide picture height'), 'slide_picture_height',
			$config_theme->images->slide->height, 10, 'col-lg-2', $this->l('px'));
		$this->html_out .= $this->displayFormSubmit('submitConfSlideNews', 'icon-save', $this->l('Update'));
		$this->html_out .= $this->displayFormClose();
	}

	private function displayConfBlocs()
	{
		$this->html_out .= '<div class="' . ($this->isPSVersion('>=', '1.6') ? 'col-md-6' : 'demi') . '">';

		$this->html_out .= $this->displayFormOpen('blocs.png', $this->l('Block last news'), $this->path_module_conf);
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Activate'),
			$this->name . '_lastnews_actif');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Show introduction text'),
			$this->name . '_lastnews_showintro', $this->l('This option may penalize your SEO.'));
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Show thumb'),
			$this->name . '_lastnews_showthumb');
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Title length'), $this->name . '_lastnews_title_length',
			Configuration::get($this->name . '_lastnews_title_length'), 10, 'col-lg-4', $this->l('caracters'));
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Introduction length'), $this->name . '_lastnews_intro_length',
			Configuration::get($this->name . '_lastnews_intro_length'), 10, 'col-lg-4', $this->l('caracters'));
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Number of news to display'), $this->name . '_lastnews_limit',
			Configuration::get($this->name . '_lastnews_limit'), 10, 'col-lg-4');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Link "show all"'),
			$this->name . '_lastnews_showall');
		$this->html_out .= $this->displayFormSubmit('submitConfBlocLastNews', 'icon-save', $this->l('Update'));
		$this->html_out .= $this->displayFormClose();

		$this->html_out .= $this->displayFormOpen('blocs.png', $this->l('Block date news'), $this->path_module_conf);
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Activate'), $this->name . '_datenews_actif');
		$this->html_out .= $this->displayFormSelect('col-lg-5',
			$this->l('Order news'),
			$this->name . '_datenews_order',
			Configuration::get($this->name . '_datenews_order'),
			array('desc' => $this->l('Desc'), 'asc' => $this->l('Asc')),
			null,
			'col-lg-5');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Link "show all"'),
			$this->name . '_datenews_showall');
		$this->html_out .= $this->displayFormSubmit('submitConfBlocDateNews', 'icon-save', $this->l('Update'));
		$this->html_out .= $this->displayFormClose();

		$this->html_out .= $this->displayFormOpen('blocs.png', $this->l('Block categories news'), $this->path_module_conf);
		$this->html_out .= '<p class="center"><a class="button" href="' . $this->path_module_conf . '&configCategories">'
			. $this->l('Go to the categories configuration') . '</a></p>';
		$this->html_out .= $this->displayFormClose();

		$this->html_out .= $this->displayFormOpen('search.png', $this->l('Block search news'), $this->path_module_conf);
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Activate'), $this->name . '_blocsearch_actif');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5',
			$this->l('Advance search in the top of results, with filter of categories'),
			$this->name . '_search_filtrecat');
		$this->html_out .= $this->displayFormSubmit('submitConfBlocSearch', 'icon-save', $this->l('Update'));
		$this->html_out .= $this->displayFormClose();

		$this->html_out .= $this->displayFormOpen('rss.png', $this->l('Block Rss all news'), $this->path_module_conf);
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Activate'), $this->name . '_allnews_rss');
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Title length'), $this->name . '_rss_title_length',
			Configuration::get($this->name . '_rss_title_length'), 10, 'col-lg-4', $this->l('caracters'));
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Introduction length'), $this->name . '_rss_intro_length',
			Configuration::get($this->name . '_rss_intro_length'), 10, 'col-lg-4', $this->l('caracters'));
		$this->html_out .= $this->displayFormSubmit('submitConfBlocRss', 'icon-save', $this->l('Update'));
		$this->html_out .= $this->displayFormClose();

		$this->html_out .= '</div>';
		$this->html_out .= '<div class="' . ($this->isPSVersion('>=', '1.6') ? 'col-md-6' : 'demi') . '">';

		$this->html_out .= $this->displayFormOpen('order.png', $this->l('Order of the blocks on columns'), $this->path_module_conf);
		$this->html_out .= '		<ul id="sortblocLeft" class="connectedSortable">
												<li class="ui-state-default ui-state-disabled">Left</li>';
		$sbl = unserialize(Configuration::get($this->name . '_sbl'));
		if (count($sbl) > 0) {
			foreach ($sbl as $vs) {
				if ($vs != '')
					$this->html_out .= '				<li rel="' . $vs . '" class="ui-state-default ui-move">'
						. $this->message_call_back[$vs] . '</li>';
			}
		}

		$this->html_out .= '		</ul>
											<ul id="sortblocRight" class="connectedSortable">
												<li class="ui-state-default ui-state-disabled">Right</li>';
		$sbr = unserialize(Configuration::get($this->name . '_sbr'));
		if (count($sbr) > 0) {
			foreach ($sbr as $vs) {
				if ($vs != '')
					$this->html_out .= '				<li rel="' . $vs . '" class="ui-state-default ui-move">'
						. $this->message_call_back[$vs] . '</li>';
			}
		}

		$this->html_out .= '		</ul>
											<script src="' . (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http')
			. '://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
											<script type="text/javascript" src="' . __PS_BASE_URI__
			. 'modules/prestablog/views/js/jquery.mjs.nestedSortable.js"></script>

											<script type="text/javascript">
												$(function() {
													$("#sortblocLeft, #sortblocRight").sortable({
															placeholder: "ui-state-highlight",
															connectWith: ".connectedSortable",
															items: "li:not(.ui-state-disabled)",
															update: function(event, ui) {
																$.ajax({
																	url: \'' . $this->context->link->getAdminLink('AdminPrestaBlogAjax') . '\',
																	type: "GET",
																	data: {
																		ajax: true,
																		action: \'prestablogrun\',
																		do: \'sortBlocs\',
																		sortblocLeft: $("#sortblocLeft").sortable("toArray", { attribute: "rel" }),
																		sortblocRight: $("#sortblocRight").sortable("toArray", { attribute: "rel" }),
																		id_shop: \'' . $this->context->shop->id . '\'
																	},
																	success:function(data){}
																});
															}
													}).disableSelection();
												});
											</script>';
		$this->html_out .= $this->displayFormClose();

		$this->html_out .= $this->displayFormOpen('blocs.png', $this->l('Footer last news'), $this->path_module_conf);
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Activate'),
			$this->name . '_footlastnews_actif');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Show introduction text'),
			$this->name . '_footlastnews_intro', $this->l('This option may penalize your SEO.'));
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Number of news to display'),
			$this->name . '_footlastnews_limit', Configuration::get($this->name . '_footlastnews_limit'), 10, 'col-lg-4');
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Title length'), $this->name . '_footer_title_length',
			Configuration::get($this->name . '_footer_title_length'), 10, 'col-lg-4', $this->l('caracters'));
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Introduction length'), $this->name . '_footer_intro_length',
			Configuration::get($this->name . '_footer_intro_length'), 10, 'col-lg-4', $this->l('caracters'));
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Link "show all"'),
			$this->name . '_footlastnews_showall');
		$this->html_out .= $this->displayFormSubmit('submitConfFooterLastNews', 'icon-save', $this->l('Update'));
		$this->html_out .= $this->displayFormClose();

		$this->html_out .= '</div>';
	}

	private function displayConfCategories()
	{
		$config_theme = $this->getConfigXmlTheme(Configuration::get($this->name . '_theme'));

		$this->html_out .= '<div class="' . ($this->isPSVersion('>=', '1.6') ? 'col-md-6' : 'demi') . '">';

		$this->html_out .= $this->displayFormOpen('blocs.png', $this->l('Block categories news'), $this->path_module_conf);
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Activate'), $this->name . '_catnews_actif');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('View empty categories'),
			$this->name . '_catnews_empty', $this->l('Supports the count of items in the categories recursive children.'));
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Show news count by category'),
			$this->name . '_catnews_shownbnews', $this->l('Does not display zero values.'));
		if ($this->isPSVersion('>=', '1.6'))
			$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Tree view'), $this->name . '_catnews_tree');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Show thumb'), $this->name . '_catnews_showthumb');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Show crop description'),
			$this->name . '_catnews_showintro');
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Title length'), $this->name . '_cat_title_length',
			Configuration::get($this->name . '_cat_title_length'), 10, 'col-lg-4', $this->l('caracters'));
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Description length'), $this->name . '_cat_intro_length',
			Configuration::get($this->name . '_cat_intro_length'), 10, 'col-lg-4', $this->l('caracters'));
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Link "show all"'),
			$this->name . '_catnews_showall');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', '<img src="../modules/' . $this->name
			. '/views/img/rss.png" align="absmiddle" /> ' . $this->l('Rss feed'), $this->name . '_catnews_rss',
			$this->l('List only for selected category'));
		$this->html_out .= $this->displayFormSubmit('submitConfBlocCatNews', 'icon-save', $this->l('Update'));
		$this->html_out .= $this->displayFormClose();

		$this->html_out .= $this->displayFormOpen('categories.png', $this->l('Category menu in blog pages'), $this->path_module_conf);
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Activate menu on blog index'),
			$this->name . '_menu_cat_blog_index');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Activate menu on blog list'),
			$this->name . '_menu_cat_blog_list');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Activate menu on article'),
			$this->name . '_menu_cat_blog_article');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Show blog link'),
			$this->name . '_menu_cat_home_link');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Show blog image link'),
			$this->name . '_menu_cat_home_img', $this->l('Only if blog link is activated') . '<br/>'
			. sprintf($this->l('Show %1$s instead %2$s'), '<img style="vertical-align:top;background-color:#383838;padding:4px;"
						src="' . _MODULE_DIR_ . 'prestablog/views/img/home.gif" />', '"<strong>'
				. $this->l('Blog') . '</strong>"'));
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('View empty categories'),
			$this->name . '_menu_cat_blog_empty', $this->l('Supports the count of items in the categories recursive children.'));
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Show news count by category'),
			$this->name . '_menu_cat_blog_nbnews', $this->l('Does not display zero values.'));
		$this->html_out .= $this->displayFormSubmit('submitConfMenuCatBlog', 'icon-save', $this->l('Update'));
		$this->html_out .= $this->displayFormClose();

		$this->html_out .= '</div>';
		$this->html_out .= '<div class="' . ($this->isPSVersion('>=', '1.6') ? 'col-md-6' : 'demi') . '">';

		$this->html_out .= $this->displayFormOpen('top-category.png', $this->l('Top of first page of category'), $this->path_module_conf);
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Show description'), $this->name . '_view_cat_desc');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Show thumbnail'), $this->name . '_view_cat_thumb');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Show picture'), $this->name . '_view_cat_img');
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Thumb picture width for categories'), 'thumb_cat_width',
			$config_theme->categories->thumb->width, 10, 'col-lg-4', $this->l('px'));
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Thumb picture height for categories'), 'thumb_cat_height',
			$config_theme->categories->thumb->height, 10, 'col-lg-4', $this->l('px'));
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Full size picture width for category'), 'full_cat_width',
			$config_theme->categories->full->width, 10, 'col-lg-4', $this->l('px'));
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Full size picture height for category'), 'full_cat_height',
			$config_theme->categories->full->height, 10, 'col-lg-4', $this->l('px'));
		$this->html_out .= $this->displayFormSubmit('submitConfCategory', 'icon-save', $this->l('Update'));
		$this->html_out .= $this->displayFormClose();

		$this->html_out .= $this->displayFormOpen('textoptions.png', $this->l('Options in articles list'), $this->path_module_conf);
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Thumb picture width for news'), 'thumb_picture_width',
			$config_theme->images->thumb->width, 10, 'col-lg-4', $this->l('px'));
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Thumb picture height for news'), 'thumb_picture_height',
			$config_theme->images->thumb->height, 10, 'col-lg-4', $this->l('px'));
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Title length'), $this->name . '_news_title_length',
			Configuration::get($this->name . '_news_title_length'), 10, 'col-lg-4', $this->l('caracters'));
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Description length'), $this->name . '_news_intro_length',
			Configuration::get($this->name . '_news_intro_length'), 10, 'col-lg-4', $this->l('caracters'));
		$this->html_out .= $this->displayFormSubmit('submitConfListeArticles', 'icon-save', $this->l('Update'));
		$this->html_out .= $this->displayFormClose();

		$this->html_out .= '</div>';
	}

	private function displayConf()
	{
		$this->html_out .= '<div class="' . ($this->isPSVersion('>=', '1.6') ? 'col-md-6' : 'demi') . '">';

		$this->html_out .= $this->displayFormOpen('rewrite.png', $this->l('Rewrite configuration'), $this->path_module_conf);
		if (!Configuration::get('PS_REWRITING_SETTINGS') && Configuration::get('prestablog_rewrite_actif'))
			$this->html_out .= $this->displayError($this->l('The general rewrite option (Friendly URL) of your PrestaShop is not activate.')
				. '<br />' . $this->l('You must enable this general option to it works.'));

		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Enable rewrite (Friendly URL)'),
			$this->name . '_rewrite_actif', $this->l('Enable only if your server allows URL rewriting (recommended)'));
		$this->html_out .= $this->displayFormSubmit('submitConfRewrite', 'icon-save', $this->l('Update'));
		$this->html_out .= $this->displayFormClose();

		$this->html_out .= $this->displayFormOpen('frontoffice.png', $this->l('Global front configuration'), $this->path_module_conf);
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Number of news per page'), $this->name . '_nb_liste_page',
			Configuration::get($this->name . '_nb_liste_page'), 10, 'col-lg-4');

		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Show thumbnail image in article page'),
			$this->name . '_view_news_img');

		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', '<img src="../modules/'
			. $this->name . '/views/img/rss.png" align="absmiddle" /> ' . $this->l('Rss link for categories news'),
			$this->name . '_uniqnews_rss', $this->l('Rss link for categories in the news page.'));

		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5',
			$this->l('Add a new tab with associated blog posts directly in your product page'), $this->name . '_producttab_actif');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Socials buttons share'),
			$this->name . '_socials_actif');

		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Facebook'),
			$this->name . '_s_facebook');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Twitter'),
			$this->name . '_s_twitter');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Google +'),
			$this->name . '_s_googleplus');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Linkedin'),
			$this->name . '_s_linkedin');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Email'),
			$this->name . '_s_email');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Pinterest'),
			$this->name . '_s_pinterest');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Pocket'),
			$this->name . '_s_pocket');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Tumblr'),
			$this->name . '_s_tumblr');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Reddit'),
			$this->name . '_s_reddit');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Hackernews'),
			$this->name . '_s_hackernews');

		$this->html_out .= $this->displayFormSubmit('submitConfGobalFront', 'icon-save', $this->l('Update'));
		$this->html_out .= $this->displayFormClose();

		$this->html_out .= '</div>';
		$this->html_out .= '<div class="' . ($this->isPSVersion('>=', '1.6') ? 'col-md-6' : 'demi') . '">';

		$this->html_out .= $this->displayFormOpen('backoffice.png', $this->l('Global admin configuration'), $this->path_module_conf);
		$this->html_out .= $this->displayFormInput('col-lg-5',
			$this->l('Number of characters to search on related products for article edited'),
			$this->name . '_nb_car_min_linkprod', Configuration::get($this->name . '_nb_car_min_linkprod'), 10, 'col-lg-4',
			$this->l('caracters'));
		$this->html_out .= $this->displayFormInput('col-lg-5',
			$this->l('Number of results in search off related products for article edited'), $this->name . '_nb_list_linkprod',
			Configuration::get($this->name . '_nb_list_linkprod'), 10, 'col-lg-4');
		$this->html_out .= $this->displayFormInput('col-lg-5',
			$this->l('Number of characters to search on related articles for article edited'),
			$this->name . '_nb_car_min_linknews', Configuration::get($this->name . '_nb_car_min_linknews'), 10, 'col-lg-4',
			$this->l('caracters'));
		$this->html_out .= $this->displayFormInput('col-lg-5',
			$this->l('Number of results in search off related articles for article edited'), $this->name . '_nb_list_linknews',
			Configuration::get($this->name . '_nb_list_linknews'), 10, 'col-lg-4');
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('items/page on admin list news'),
			$this->name . '_nb_news_pl', Configuration::get($this->name . '_nb_news_pl'), 10, 'col-lg-4');
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('items/page on admin list comments'),
			$this->name . '_nb_comments_pl', Configuration::get($this->name . '_nb_comments_pl'), 10, 'col-lg-4');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Always show comments in article edition'),
			$this->name . '_comment_div_visible');
		$this->html_out .= $this->displayFormSubmit('submitConfGobalAdmin', 'icon-save', $this->l('Update'));
		$this->html_out .= $this->displayFormClose();

		$this->html_out .= '</div>';
	}

	private function displayConfComments()
	{
		$this->html_out .= $this->displayInfo(
			$this->l('The comments engine is different than Facebook comments.')
			. ' ' . $this->l('These two functions can not be activated simultaneously.')
		);
		$this->html_out .= '<div class="' . ($this->isPSVersion('>=', '1.6') ? 'col-md-6' : 'demi') . '">';
		$this->html_out .= $this->displayFormOpen('comments.png', $this->l('Comments'), $this->path_module_conf);
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Activate'), $this->name . '_comment_actif');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Only registered users can publish a comment'),
			$this->name . '_comment_only_login');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Always show comments on blog post'),
			$this->name . '_comment_autoshow', $this->l('If no is selected, user must click on "show comment" link in blog posts to toggle on.
'));
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Auto approve comments'),
			$this->name . '_comment_auto_actif');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Link href nofollow'),
			$this->name . '_comment_nofollow', $this->l('Indicates search engines not to follow the link'));
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Inform admin by email for a new comment'),
			$this->name . '_comment_alert_admin');
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Admin Mail'), $this->name . '_comment_admin_mail',
			Configuration::get($this->name . '_comment_admin_mail'), 40, 'col-lg-4', null, null, '<i class="icon-envelope-o"></i>');
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Mail user subscription'),
			$this->name . '_comment_subscription', $this->l('Only registered users can subscribe'));
		$this->html_out .= $this->displayFormSubmit('submitConfComment', 'icon-save', $this->l('Update'));
		$this->html_out .= $this->displayFormClose();

		$this->html_out .= '</div>';
		$this->html_out .= '<div class="' . ($this->isPSVersion('>=', '1.6') ? 'col-md-6' : 'demi') . '">';

		$this->html_out .= $this->displayFormOpen('facebook.png', $this->l('Facebook comments'), $this->path_module_conf);
		$this->html_out .= $this->displayFormEnableItemConfiguration('col-lg-5', $this->l('Activate'), $this->name . '_commentfb_actif');
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Number of comments visible'),
			$this->name . '_commentfb_nombre', Configuration::get($this->name . '_commentfb_nombre'), 10, 'col-lg-4');
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('API Facebook Id'),
			$this->name . '_commentfb_apiId', Configuration::get($this->name . '_commentfb_apiId'), 20, 'col-lg-7',
			'', $this->l('Optional') . ' - ' . $this->l('You can manage comments directly on facebook application.'));
		$this->html_out .= $this->displayFormInput('col-lg-5', $this->l('Add global moderator'),
			$this->name . '_commentfb_modosId', '', 20, 'col-lg-7', '',
			$this->l('Add a facebook accounts ID for beeing comments moderators.') . ' ' .
			$this->l('ID Can be found on')
			. ' <a href="http://findmyfbid.com" target="_blank">http://findmyfbid.com</a>');

		$this->html_out .= $this->displayFormSubmit('submitConfCommentFB', 'icon-save', $this->l('Update'));
		$this->html_out .= $this->displayFormClose();

		$this->html_out .= $this->displayFormOpen('facebook.png', $this->l('Facebook moderators'), $this->path_module_conf);
		$list_fb_moderators = unserialize(Configuration::get($this->name . '_commentfb_modosId'));
		if (is_array($list_fb_moderators) && count($list_fb_moderators) > 0) {
			foreach ($list_fb_moderators as $fb_moderator) {
				$this->html_out .= '<div class="panel">';
				$this->html_out .= '<i class="icon-facebook"></i> ' . $fb_moderator;
				$this->html_out .= '
						<a style="float:right;"
							onclick="return confirm(\'' . $this->l('This action will delete the moderator : ') . $fb_moderator
					. '\r\n' . $this->l('Are you sure?', __CLASS__, true, false) . '\');"
							href="' . $this->path_module_conf . '&deleteFacebookModerator&fb_moderator_id=' . $fb_moderator . '">
							<i class="icon-trash"></i> ' . $this->l('Delete') . '
						</a>';

				$this->html_out .= '</div>';
			}
		} else
			$this->html_out .= $this->l('No moderators configured');
		$this->html_out .= $this->displayFormClose();

		$this->html_out .= '</div>';
	}

	private function displayFormOpen($icon_legend = 'cog.gif', $label_legend = 'New Form', $action, $name = 'formblog')
	{
		if ($this->isPSVersion('>=', '1.6'))
			return '
			<div class="panel">
				<fieldset>
					<legend>
						' . (strpos($icon_legend, 'icon-') !== false ? '<i class="' . $icon_legend . '"></i>' : '<img src="../modules/'
					. $this->name . '/views/img/' . $icon_legend . '" />') . '&nbsp;' . $label_legend . '
					</legend>
					<form method="post" class="form-horizontal" action="' . $action . '" name="' . $name . '" enctype="multipart/form-data">';
		else
			return '
			<fieldset>
				<legend>
					' . (strpos($icon_legend, 'icon-') !== false ? '<img src="../modules/' . $this->name . '/views/img/cog.gif" />' :
					'<img src="../modules/' . $this->name . '/views/img/' . $icon_legend . '" />') . '&nbsp;' . $label_legend . '
				</legend>
				<form method="post" class="form-horizontal" action="' . $action . '" name="' . $name . '" enctype="multipart/form-data">';
	}

	private function displayFormClose()
	{
		if ($this->isPSVersion('>=', '1.6'))
			return '</form>
				</fieldset>
			</div>';
		else
			return '</form>
				</fieldset>
			<br />';
	}

	private function displayFormSelect($label_bootstrap = 'col-lg-5', $label_text, $name_item, $value = '', $options,
									   $sizecar = 20, $size_bootstrap = 'col-lg-5', $info_span = null, $help = null, $info_span_before = null)
	{
		$select = '';
		if ($this->isPSVersion('>=', '1.6')) {
			$select .= '<div class="form-group ">
								<label class="control-label ' . $label_bootstrap . '" for="' . $name_item . '">' . $label_text . '</label>
								<div class="' . $size_bootstrap . '">
									<div class="input-group">
										' . ($info_span_before ? '<span class="input-group-addon">' . $info_span_before . '</span>' : '') . '
										<select name="' . $name_item . '" id="' . $name_item . '" ' . ($sizecar ? 'size="' . $sizecar . '"' : '') . '>';
			if (count($options) > 0) {
				foreach ($options as $key => $val)
					$select .= '<option value="' . $key . '" ' . ($value == $key ? ' selected' : '') . '>' . $val . '</option>';
			}
			$select .= '			</select>
										' . ($info_span ? '<span class="input-group-addon">' . $info_span . '</span>' : '') . '
									</div>
									' . ($help ? '<p class="help-block">' . $help . '</p>' : '') . '
								</div>
							</div>';
			return $select;
		} else {
			$select .= '	<div class="form-ligne">
									<label for="' . $name_item . '">' . $label_text . '</label>
									<div class="margin-form">
										<select name="' . $name_item . '" id="' . $name_item . '" ' . ($sizecar ? 'size="' . $sizecar . '"' : '') . '>';
			if (count($options) > 0) {
				foreach ($options as $key => $val)
					$select .= '<option value="' . $key . '" ' . ($value == $key ? ' selected' : '') . '>' . $val . '</option>';
			}
			$select .= '</select>
										' . ($help ? '<p class="help-block">' . $help . '</p>' : '') . '
									</div>
									<div class="clear"></div>
								</div>';
			return $select;
		}
	}

	private function displayFormSubmit($submit_name, $icon, $label)
	{
		if ($this->isPSVersion('>=', '1.6'))
			return '		<div class="form-actions">
								<button class="btn btn-primary" name="' . $submit_name . '" type="submit"><i class="' . $icon . '">
								</i>&nbsp;' . $label . '</button>
							</div>';
		else
			return '		<div class="form-ligne">
								<div class="margin-form">
									<input type="submit" name="' . $submit_name . '" value="' . $label . '" class="button" />
								</div>
								<div class="clear"></div>
							</div>';
	}

	private function displayFormLibre($label_bootstrap = 'col-lg-5', $label_text, $libre_html, $size_bootstrap = 'col-lg-5',
									  $lang_flags = null)
	{
		if ($this->isPSVersion('>=', '1.6'))
			return '		<div class="form-group ">
								<label class="control-label ' . $label_bootstrap . '">' . $label_text . '</label>
								<div class="' . $size_bootstrap . '">
									' . $libre_html . '
								</div>
								' . ($lang_flags ? '<div class="col-lg-1">' . $lang_flags . '</div>' : '') . '
							</div>';
		else
			return '	<div class="form-ligne">
							<label>' . $label_text . '</label>
							<div class="margin-form">
								<div style="float:left;">' . $libre_html . '</div>
								' . ($lang_flags ? $lang_flags : '') . '
							</div>
							<div class="clear"></div>
						</div>';
	}

	private function displayFormFile($label_bootstrap = 'col-lg-5', $label_text, $name_item, $size_bootstrap = 'col-lg-5',
									 $help = null)
	{
		if ($this->isPSVersion('>=', '1.6'))
			return '		<div class="form-group ">
								<label class="control-label ' . $label_bootstrap . '" for="' . $name_item . '">' . $label_text . '</label>
								<div class="' . $size_bootstrap . '">
									<input id="' . $name_item . '" type="file" name="' . $name_item . '" class="hide" />
									<div class="dummyfile input-group">
										<span class="input-group-addon"><i class="icon-file"></i></span>
										<input id="' . $name_item . '-name" type="text" class="disabled" name="filename" readonly />
										<span class="input-group-btn">
											<button id="' . $name_item . '-selectbutton" type="button"
											name="submitAddAttachments" class="btn btn-default">
												<i class="icon-folder-open"></i> ' . $this->l('Choose a file') . '
											</button>
										</span>
									</div>
									' . ($help ? '<p class="help-block">' . $help . '</p>' : '') . '
								</div>
							</div>
							<script>
								$(document).ready(function(){
									$(\'#' . $name_item . '-selectbutton\').click(function(e){
										$(\'#' . $name_item . '\').trigger(\'click\');
									});
									$(\'#' . $name_item . '\').change(function(e){
										var val = $(this).val();
										var file = val.split(/[\\/]/);
										$(\'#' . $name_item . '-name\').val(file[file.length-1]);
									});
								});
							</script>';
		else
			return '		<div class="form-ligne">
								<label for="' . $name_item . '">' . $label_text . '</label>
								<div class="margin-form">
									<input type="file" name="' . $name_item . '"  id="' . $name_item . '" />' . ($help ? ' ' . $help : '') . '
								</div>
								<div class="clear"></div>
							</div>';
	}

	private function displayFormFileNoLabel($name_item, $size_bootstrap = 'col-lg-5', $help = null)
	{
		if ($this->isPSVersion('>=', '1.6'))
			return '
								<input id="' . $name_item . '" type="file" name="' . $name_item . '" class="hide" />
								<div class="dummyfile input-group ' . $size_bootstrap . '" >
									<span class="input-group-addon"><i class="icon-file"></i></span>
									<input id="' . $name_item . '-name" type="text" class="disabled" name="filename" readonly />
									<span class="input-group-btn">
										<button id="' . $name_item . '-selectbutton" type="button" name="submitAddAttachments"
										class="btn btn-default">
											<i class="icon-folder-open"></i> ' . $this->l('Choose a file') . '
										</button>
									</span>
								</div>
								' . ($help ? '<p class="help-block">' . $help . '</p>' : '') . '
								<script>
									$(document).ready(function(){
										$(\'#' . $name_item . '-selectbutton\').click(function(e){
											$(\'#' . $name_item . '\').trigger(\'click\');
										});
										$(\'#' . $name_item . '\').change(function(e){
											var val = $(this).val();
											var file = val.split(/[\\/]/);
											$(\'#' . $name_item . '-name\').val(file[file.length-1]);
										});
									});
								</script>
							';
		else
			return '		<input type="file" name="' . $name_item . '"  id="' . $name_item . '" />' . ($help ? ' ' . $help : '');
	}

	private function displayFormInput($label_bootstrap = 'col-lg-5', $label_text, $name_item, $value = '', $sizecar = 20,
									  $size_bootstrap = 'col-lg-5', $info_span = null, $help = null, $info_span_before = null)
	{
		if ($this->isPSVersion('>=', '1.6'))
			return '		<div class="form-group ">
								<label class="control-label ' . $label_bootstrap . '" for="' . $name_item . '">' . $label_text . '</label>
								<div class="' . $size_bootstrap . '">
									<div class="input-group">
										' . ($info_span_before ? '<span class="input-group-addon">' . $info_span_before . '</span>' : '') . '
										<input id="' . $name_item . '" ' . ($sizecar ? 'size="' . $sizecar . '"' : '') . ' type="text" value="'
				. $value . '" name="' . $name_item . '">
										' . ($info_span ? '<span class="input-group-addon">' . $info_span . '</span>' : '') . '
									</div>
									' . ($help ? '<p class="help-block">' . $help . '</p>' : '') . '
								</div>
							</div>';
		else
			return '		<div class="form-ligne">
								<label for="' . $name_item . '">' . $label_text . '</label>
								<div class="margin-form">
									<input type="text" name="' . $name_item . '" ' . ($sizecar ? 'size="' . $sizecar . '"' : '') . ' value="'
				. $value . '" />' . ($info_span ? '&nbsp;<strong>' . $info_span . '</strong>' : '') . '
									' . ($help ? '<p class="help-block">' . $help . '</p>' : '') . '
								</div>
								<div class="clear"></div>
							</div>';
	}

	private function displayFormDate($label_bootstrap = 'col-lg-5', $label_text, $name_item, $value, $time)
	{
		if (!$value) {
			if ($time)
				$value = date('Y-m-d H:i:s');
			else
				$value = date('Y-m-d');
		}

		if ($this->isPSVersion('>=', '1.6'))
			return '		<div class="form-group ">
								<label class="control-label ' . $label_bootstrap . '" for="' . $name_item . '">' . $label_text . '</label>
								<div class="' . ($time ? 'col-lg-4' : 'col-lg-3') . '">
									<div class="input-group">
										<span class="input-group-addon"><i class="icon-calendar"></i></span>
										<input id="' . $name_item . '" ' . ($time ? 'size="20"' : 'size="10"') . ' type="text" value="'
				. $value . '" name="' . $name_item . '">
									</div>
									<p class="help-block">' . $this->l('Format: YYYY-MM-DD') . ($time ? ' ' . $this->l('HH:MM:SS') : '') . '</p>
								</div>
							</div>' . $this->moduleDatepicker($name_item, true);
		else
			return '	<div class="form-ligne">
							<label for="' . $name_item . '">' . $label_text . '</label>
							<div class="margin-form">
								<input id="' . $name_item . '" type="text" name="' . $name_item . '" ' . ($time ? 'size="20"' : 'size="10"') . '
								value="' . htmlentities($value, ENT_COMPAT, 'UTF-8') . '" />
								' . $this->l('Format: YYYY-MM-DD') . ($time ? ' ' . $this->l('HH:MM:SS') : '') . '
							</div>
							<div class="clear"></div>
						</div>' . $this->moduleDatepicker($name_item, true);
	}

	private function displayFormDateWithActivation($label_bootstrap = 'col-lg-5', $label_text, $name_item, $value, $time,
												   $name_item_activation, $value_activation)
	{
		if (!$value) {
			if ($time)
				$value = date('Y-m-d H:i:s');
			else
				$value = date('Y-m-d');
		}

		if ($this->isPSVersion('>=', '1.6'))
			return '		<div class="form-group ">
								<label class="control-label ' . $label_bootstrap . '" for="' . $name_item . '">' . $label_text . '</label>
								<div class="' . ($time ? 'col-lg-10' : 'col-lg-6') . '">
									<div class="input-group">
										<span class="switch prestashop-switch fixed-width-lg" style="margin-right:5px;">
											<input name="' . $name_item_activation . '" id="' . $name_item_activation . '_on" value="1" '
				. ($value_activation ? 'checked="checked" ' : '') . ' type="radio">
											<label for="' . $name_item_activation . '_on">' . $this->l('Yes') . '</label>
											<input name="' . $name_item_activation . '" id="' . $name_item_activation . '_off" value="0" '
				. (!$value_activation ? 'checked="checked" ' : '') . ' type="radio">
											<label for="' . $name_item_activation . '_off">' . $this->l('No') . '</label>
											<a class="slide-button btn"></a>
										</span>
										<span class="input-group-addon"><i class="icon-calendar"></i></span>
										<input id="' . $name_item . '" ' . ($time ? 'size="20"' : 'size="10"') . ' type="text" value="'
				. $value . '" name="' . $name_item . '">
									</div>
									<p class="help-block">' . $this->l('Format: YYYY-MM-DD') . ($time ? ' ' . $this->l('HH:MM:SS') : '') . '</p>
								</div>
							</div>' . $this->moduleDatepicker($name_item, true);
		else
			return '	<div class="form-ligne">
							<label for="' . $name_item . '">' . $label_text . '</label>
							<div class="margin-form">
								<span style="margin-right:5px;">
									<input type="radio" name="' . $name_item_activation . '" value="1" ' . ($value_activation ? 'checked="checked" ' : '') . '/>
									<label class="t" > <img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '"
									title="' . $this->l('Enabled') . '" /></label>
									<input type="radio" name="' . $name_item_activation . '" value="0" ' . (!$value_activation ? 'checked="checked" ' : '') . '/>
									<label class="t" > <img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '"
									title="' . $this->l('Disabled') . '" /></label>
								</span>
								<input id="' . $name_item . '" type="text" name="' . $name_item . '" ' . ($time ? 'size="20"' : 'size="10"')
				. ' value="' . htmlentities($value, ENT_COMPAT, 'UTF-8') . '" />
								' . $this->l('Format: YYYY-MM-DD') . ($time ? ' ' . $this->l('HH:MM:SS') : '') . '
							</div>
							<div class="clear"></div>
						</div>' . $this->moduleDatepicker($name_item, true);
	}

	private function displayFormEnableItemConfiguration($label_bootstrap = 'col-lg-5', $label_text, $name_item, $help = null)
	{
		if ($this->isPSVersion('>=', '1.6'))
			return '	<div class="form-group">
							<label for="' . $name_item . '" class="control-label ' . $label_bootstrap . '">
								<span>' . $label_text . '</span>
							</label>
							<div class="col-lg-7">
								<span class="switch prestashop-switch fixed-width-lg">
									<input name="' . $name_item . '" id="' . $name_item . '_on" value="1" '
				. (Tools::getValue($name_item, Configuration::get($name_item)) ?
					'checked="checked" ' : '') . ' type="radio">
									<label for="' . $name_item . '_on">' . $this->l('Yes') . '</label>
									<input name="' . $name_item . '" id="' . $name_item . '_off" value="0" '
				. (!Tools::getValue($name_item, Configuration::get($name_item)) ?
					'checked="checked" ' : '') . ' type="radio">
									<label for="' . $name_item . '_off">' . $this->l('No') . '</label>
									<a class="slide-button btn"></a>
								</span>
								' . ($help ? '<p class="help-block">' . $help . '</p>' : '') . '
							</div>
						</div>';
		else
			return '	<div class="form-ligne">
							<label>' . $label_text . '</label>
							<div class="margin-form">
								<input type="radio" name="' . $name_item . '" value="1" ' . (Tools::getValue($name_item, Configuration::get($name_item)) ?
					'checked="checked" ' : '') . '/>
								<label class="t" > <img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" /></label>
								<input type="radio" name="' . $name_item . '" value="0" ' . (!Tools::getValue($name_item, Configuration::get($name_item)) ?
					'checked="checked" ' : '') . '/>
								<label class="t" > <img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" /></label>
								' . ($help ? '<br /><span>' . $help . '</span>' : '') . '
							</div>
							<div class="clear"></div>
						</div>';
	}

	private function displayFormEnableItem($label_bootstrap = 'col-lg-5', $label_text, $name_item, $value, $help = null)
	{
		if ($this->isPSVersion('>=', '1.6'))
			return '	<div class="form-group">
							<label for="' . $name_item . '" class="control-label ' . $label_bootstrap . '">
								<span>' . $label_text . '</span>
							</label>
							<div class="col-lg-7">
								<span class="switch prestashop-switch fixed-width-lg">
									<input name="' . $name_item . '" id="' . $name_item . '_on" value="1" ' . ($value ? 'checked="checked" ' : '') . ' type="radio">
									<label for="' . $name_item . '_on">' . $this->l('Yes') . '</label>
									<input name="' . $name_item . '" id="' . $name_item . '_off" value="0" ' . (!$value ? 'checked="checked" ' : '') . ' type="radio">
									<label for="' . $name_item . '_off">' . $this->l('No') . '</label>
									<a class="slide-button btn"></a>
								</span>
								' . ($help ? '<p class="help-block">' . $help . '</p>' : '') . '
							</div>
						</div>';
		else
			return '	<div class="form-ligne">
							<label>' . $label_text . '</label>
							<div class="margin-form">
								<input type="radio" name="' . $name_item . '" value="1" ' . ($value ? 'checked="checked" ' : '') . '/>
								<label class="t" > <img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" /></label>
								<input type="radio" name="' . $name_item . '" value="0" ' . (!$value ? 'checked="checked" ' : '') . '/>
								<label class="t" > <img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" /></label>
								' . ($help ? '<br /><span>' . $help . '</span>' : '') . '
							</div>
							<div class="clear"></div>
						</div>';
	}

	private function displayFlagsFor($item, $div_lang_name)
	{
		$languages = Language::getLanguages(true);
		return $this->displayFlags($languages, $this->langue_default_store, $div_lang_name, $item, true);
	}

	private function displayFormNews()
	{
		$html_libre = '';
		$config_theme = $this->getConfigXmlTheme(Configuration::get($this->name . '_theme'));

		$default_language = $this->langue_default_store;
		$languages = Language::getLanguages(true);
		$iso = Language::getIsoById((int)$this->context->language->id);
		$div_lang_name = 'title¤link_rewrite¤meta_title¤meta_description¤meta_keywords¤cpara1¤cpara2';

		$legend_title = $this->l('Add news');
		if (Tools::getValue('idN')) {
			$news = new NewsClass((int)Tools::getValue('idN'));
			$lang_liste_news = unserialize($news->langues);
			$legend_title = $this->l('Edit news') . ' #' . $news->id;
		} else
			$news = new NewsClass();

		if (Tools::isSubmit('submitUpdateNews') || Tools::isSubmit('submitAddNews')) {
			$news->id_shop = (int)$this->context->shop->id;
			$news->copyFromPost();
		}

		$iso_tiny_mce = (file_exists(_PS_ROOT_DIR_ . '/js/tinymce/jscripts/tiny_mce/langs/' . $iso . '.js') ? $iso : 'en');
		$ad = dirname($_SERVER['PHP_SELF']);
		$this->html_out .= '
			<script type="text/javascript">
			' . (Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL') ? 'var PS_ALLOW_ACCENTED_CHARS_URL = 1;' : 'var PS_ALLOW_ACCENTED_CHARS_URL = 0;') . '
			var iso = \'' . $iso_tiny_mce . '\' ;
			var pathCSS = \'' . _THEME_CSS_DIR_ . '\' ;
			var ad = \'' . $ad . '\' ;
			</script>
			<script type="text/javascript" src="' . __PS_BASE_URI__ . 'js/tiny_mce/tiny_mce.js"></script>
			' . (self::isPSVersion('>=', '1.6') ? '
			<script type="text/javascript" src="' . __PS_BASE_URI__ . 'modules/prestablog/views/js/tinymce.inc.1.6.js"></script>
			<script type="text/javascript">
				$(function() {
					tinySetup({ editor_selector :"autoload_rte" });
				});
			</script>
				' : '
			<script type="text/javascript" src="' . __PS_BASE_URI__ . 'modules/prestablog/views/js/tinymce.inc.js"></script>
				') . '
			<script type="text/javascript">
				id_language = Number(' . $default_language . ');

				function copy2friendlyURLPrestaBlog() {
					if (!$(\'#slink_rewrite_\'+id_language).attr(\'disabled\')) {
						$(\'#slink_rewrite_\'+id_language).val(str2url($(\'input#title_\'+id_language).val().replace(/^[0-9]+\./, \'\'), \'UTF-8\'));
					}
				}
				function updateFriendlyURLPrestaBlog() {
					$(\'#slink_rewrite_\'+id_language).val(str2url($(\'#slink_rewrite_\'+id_language).val().replace(/^[0-9]+\./, \'\'), \'UTF-8\'));
				}

				function RetourLangueCheckUp(ArrayCheckedLang, idLangEnCheck, idLangDefaut) {
					if (ArrayCheckedLang.length > 0)
						return ArrayCheckedLang[0];
					else
						return idLangDefaut;
				}

				$(function() {
					';

		if (Tools::getValue('idN'))
			if (!Tools::getValue('languesup') && count($lang_liste_news) == 1)
				$this->html_out .= 'changeTheLanguage(\'title\', \'' . $div_lang_name . '\', ' . (int)$lang_liste_news[0] . ', \'\');';
			else {
				$array_check_lang = array();
				if (Tools::getValue('languesup'))
					$array_check_lang = Tools::getValue('languesup');

				if (count($array_check_lang) == 1)
					$this->html_out .= 'changeTheLanguage(\'title\', \'' . $div_lang_name . '\', ' . (int)$array_check_lang[0] . ', \'\');';
				else
					$this->html_out .= 'changeTheLanguage(\'title\', \'' . $div_lang_name . '\', ' . (int)$default_language . ', \'\');';
			}

		$this->html_out .= '
					$(".catlang").hide();
					$(".catlang[rel="+id_language+"]").show();

					$("div.language_flags img, #check_lang_prestablog img").click(function() {
						$(".catlang").hide();
						$(".catlang[rel="+id_language+"]").show();
						$("#imgCatLang").attr("src", "../img/l/" + id_language + ".jpg");
					});

					$("input[name=\'languesup[]\']").click(function() {
						if (this.checked)
							changeTheLanguage(\'title\', \'' . $div_lang_name . '\', this.value, \'\');
						else {
							selectedL = new Array();
							$("input[name=\'languesup[]\']:checked").each(function() {selectedL.push($(this).val());});
							changeTheLanguage(\'title\', \'' . $div_lang_name . '\', RetourLangueCheckUp(selectedL, this.value, ' . $default_language . '), \'\');
						}
					});

					$("form[name=formWithSelectLang]").submit(function() {';
		foreach ($languages as $language)
			$this->html_out .= '$(\'#slink_rewrite_' . $language['id_lang'] . '\').removeAttr("disabled");';

		$this->html_out .= '
						selectedLangues = new Array();
						$("input[name=\'languesup[]\']:checked").each(function() {selectedLangues.push($(this).val());});

						if (selectedLangues.length == 0) {
							alert("' . $this->l('You must choose at least one language !') . '");
							$("html, body").animate({scrollTop: $("#menu_config_prestablog").offset().top}, 300);
							$("#check_lang_prestablog").css("background-color", "#FFA300");
							return false;
						}
						else return true;
					});

					$("#control").toggle(
						function () {
							$(\'#slink_rewrite_\'+id_language).removeAttr("disabled");
							$(\'#slink_rewrite_\'+id_language).css("background-color", "#fff");
							$(\'#slink_rewrite_\'+id_language).css("color", "#000");
							$(this).html("' . $this->l('Disable this rewrite') . '");
						},
						function () {
							$(\'#slink_rewrite_\'+id_language).attr("disabled", true);
							$(\'#slink_rewrite_\'+id_language).css("background-color", "#e0e0e0");
							$(\'#slink_rewrite_\'+id_language).css("color", "#7F7F7F");
							$(this).html("' . $this->l('Enable this rewrite') . '");
						}
					);
					';

		foreach ($languages as $language)
			$this->html_out .= '
					if ($("#slink_rewrite_' . $language['id_lang'] . '").val() == \'\') {
						$("#slink_rewrite_' . $language['id_lang'] . '").removeAttr("disabled");
						$("#slink_rewrite_' . $language['id_lang'] . '").css("background-color", "#fff");
						$("#slink_rewrite_' . $language['id_lang'] . '").css("color", "#000");
						$("#control").html("' . $this->l('Disable this rewrite') . '");
					}

					$("#paragraph_' . $language['id_lang'] . '").keyup(function(){
						var limit = parseInt($(this).attr("maxlength"));
						var text = $(this).val();
						var chars = text.length;
						if (chars > limit){
							var new_text = text.substr(0, limit);
							$(this).val(new_text);
						}
						$("#compteur-texte-' . $language['id_lang'] . '").html(chars+" / "+limit);
					});';

		$this->html_out .= '
					$("#productLinkSearch").bind("keyup click focusin", function() {
						ReloadLinkedSearchProducts();
					});

					$("#articleLinkSearch").bind("keyup click focusin", function() {
						ReloadLinkedSearchArticles();
					});

					ReloadLinkedProducts();
					ReloadLinkedArticles();
				});

				function ReloadLinkedSearchProducts(start) {
					var listLinkedProducts = \'\';
					$("input[name^=productsLink]").each(function() {
						listLinkedProducts += $(this).val() + ";";
					});

					if ($("#productLinkSearch").val() != \'\' && $("#productLinkSearch").val().length >= '
			. (int)Configuration::get($this->name . '_nb_car_min_linkprod') . ') {
						$.ajax({
							url: \'' . $this->context->link->getAdminLink('AdminPrestaBlogAjax') . '\',
							type: "GET",
							data: {
								ajax: true,
								action: \'prestablogrun\',
								do: \'searchProducts\',
								listLinkedProducts: listLinkedProducts,
								start: start,
								req: $("#productLinkSearch").attr("value"),
								id_shop: \'' . $this->context->shop->id . '\'
							},
							success:function(data){
								$("#productLinkResult").empty();
								$("#productLinkResult").append(data);
							}
						});
					}
					else {
						$("#productLinkResult").empty();
						$("#productLinkResult").append(\'<tr><td colspan="4" class="center">' . $this->l('You must search before') . ' (' .
			(int)Configuration::get($this->name . '_nb_car_min_linkprod') . ' ' .
			$this->l('caract. minimum') . ')</td></tr>\');
					}
				}

				function ReloadLinkedSearchArticles(start) {
					var listLinkedArticles = \'\';
					$("input[name^=articlesLink]").each(function() {
						listLinkedArticles += $(this).val() + ";";
					});

					if ($("#articleLinkSearch").val() != \'\' && $("#articleLinkSearch").val().length >= '
			. (int)Configuration::get($this->name . '_nb_car_min_linknews') . ') {
						$.ajax({
							url: \'' . $this->context->link->getAdminLink('AdminPrestaBlogAjax') . '\',
							type: "GET",
							data: {
								ajax: true,
								action: \'prestablogrun\',
								do: \'searchArticles\',
								listLinkedArticles: listLinkedArticles,
								start: start,
								req: $("#articleLinkSearch").attr("value"),
								id_shop: \'' . $this->context->shop->id . '\'
							},
							success:function(data){
								$("#articleLinkResult").empty();
								$("#articleLinkResult").append(data);
							}
						});
					}
					else {
						$("#articleLinkResult").empty();
						$("#articleLinkResult").append(\'<tr><td colspan="4" class="center">' . $this->l('You must search before') . ' (' .
			(int)Configuration::get($this->name . '_nb_car_min_linknews') . ' ' .
			$this->l('caract. minimum') . ')</td></tr>\');
					}
				}

				function ReloadLinkedProducts() {
					var req = \'\';
					$("input[name^=productsLink]").each(function() {
						req += $(this).val() + ";";
					});
					$.ajax({
						url: \'' . $this->context->link->getAdminLink('AdminPrestaBlogAjax') . '\',
						type: "GET",
						data: {
							ajax: true,
							action: \'prestablogrun\',
							do: \'loadProductsLink\',
							req: req,
							id_shop: \'' . $this->context->shop->id . '\'
						},
						success:function(data){
							$("#productLinked").empty();
							$("#productLinked").append(data);
						}
					});
				}

				function ReloadLinkedArticles() {
					var req = \'\';
					$("input[name^=articlesLink]").each(function() {
						req += $(this).val() + ";";
					});
					$.ajax({
						url: \'' . $this->context->link->getAdminLink('AdminPrestaBlogAjax') . '\',
						type: "GET",
						data: {
							ajax: true,
							action: \'prestablogrun\',
							do: \'loadArticlesLink\',
							req: req,
							id_shop: \'' . $this->context->shop->id . '\'
						},
						success:function(data){
							$("#articleLinked").empty();
							$("#articleLinked").append(data);
						}
					});
				}

				function changeTheLanguage(title, divLangName, id_lang, iso) {
					$("#imgCatLang").attr("src", "../img/l/" + id_lang + ".jpg");
					return changeLanguage(title, divLangName, id_lang, iso);
				}
			</script>';

		$this->html_out .= $this->displayFormOpen('icon-edit', $legend_title, $this->path_module_conf, 'formWithSelectLang');
		if (Tools::getValue('idN')) {
			$this->html_out .= '<input type="hidden" name="idN" value="' . Tools::getValue('idN') . '" />';

			$languages_shop = array();
			foreach (Language::getLanguages() as $value)
				$languages_shop[$value['id_lang']] = $value['iso_code'];

			foreach ($lang_liste_news as $val_langue) {
				if (count($languages) >= 1 && array_key_exists((int)$val_langue, $languages_shop)) {
					$html_libre .= '
							<a target="_blank" href="' . PrestaBlog::prestablogUrl(array(
							'id' => (int)$news->id,
							'seo' => $news->link_rewrite[(int)$val_langue],
							'titre' => $news->title[(int)$val_langue],
							'id_lang' => (int)$val_langue,
						)) . ((int)Configuration::get('PS_REWRITING_SETTINGS') && (int)Configuration::get('prestablog_rewrite_actif') ? '?' : '&') . '
							preview=' . $this->generateToken((int)$news->id) . '" class="indent-right">
								<img src="../img/l/' . (int)$val_langue . '.jpg" />
								<img src="../modules/' . $this->name . '/views/img/preview.gif" />
							</a>';
				}
			}
			$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Preview'), $html_libre, 'col-lg-7');
		}

		/***********************************************************/
		$html_libre = '<span id="check_lang_prestablog">' . (count($languages) == 1 ? '' :
				'<input type="checkbox" name="checkmelang" class="noborder" onclick="checkDelBoxes(this.form, \'languesup[]\', this.checked)" /> '
				. $this->l('All') . ' | ');
		foreach ($languages as $language) {
			$html_libre .= '<input type="checkbox" name="languesup[]" value="' . $language['id_lang'] . '"';
			if ((Tools::getValue('idN') && in_array((int)$language['id_lang'], $lang_liste_news))
				|| (Tools::getValue('languesup') && in_array((int)$language['id_lang'], Tools::getValue('languesup')))
				|| ((!Tools::getValue('idN') && !Tools::getValue('languesup'))
					&& ((int)$language['id_lang'] == (int)$default_language))
			)
				$html_libre .= ' checked=checked';

			$html_libre .= ' ' . (count($languages) == 1 ? 'style="display:none;"' : '') . ' />
					<img src="../img/l/' . (int)$language['id_lang'] . '.jpg" class="pointer indent-right"
					alt="' . $language['name'] . '" title="' . $language['name'] . '"
					onclick="changeTheLanguage(\'title\', \'' . $div_lang_name . '\', ' . $language['id_lang'] . ', \'' . $language['iso_code'] . '\');" />';
		}
		$html_libre .= '</span>';

		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Language'), $html_libre, 'col-lg-7');
		/***********************************************************/
		$html_libre = '';
		foreach ($languages as $language) {
			$html_libre .= '<div id="title_' . $language['id_lang'] . '" style="display: '
				. ($language['id_lang'] == $default_language ? 'block' : 'none') . ';">
										<input ' . (self::isPSVersion('<', '1.6') ? 'class="fixInput15"' : '') . ' type="text" name="title_'
				. $language['id_lang'] . '" id="title_' . $language['id_lang'] . '" maxlength="'
				. (int)Configuration::get('prestablog_news_title_length') . '" value="' . (isset($news->title[$language['id_lang']]) ?
					$news->title[$language['id_lang']] : '') . '"
										onkeyup="if (isArrowKey(event)) return; copy2friendlyURLPrestaBlog();" onchange="copy2friendlyURLPrestaBlog();" />
									</div>';
		}
		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Main title'), $html_libre, 'col-lg-7',
			$this->displayFlagsFor('title', $div_lang_name));
		/***********************************************************/
		$this->html_out .= $this->displayFormEnableItem('col-lg-2', $this->l('Activate'), 'actif', $news->actif);
		/***********************************************************/
		$this->html_out .= $this->displayFormEnableItem('col-lg-2', $this->l('Silde'), 'slide', $news->slide);
		/***********************************************************/
		if (Tools::getValue('idN')) {
			$comments_actif = CommentNewsClass::getListe(1, $news->id);
			$comments_all = CommentNewsClass::getListe(-2, $news->id);
			$comments_non_lu = CommentNewsClass::getListe(-1, $news->id);
			$comments_disabled = CommentNewsClass::getListe(0, $news->id);

			$html_libre = '
					<div id="labelComments">
						' . ((count($comments_all) > 0) ? '<strong>' . count($comments_actif) . '</strong> ' . $this->l('approuved') . ' '
					. $this->l('of') . ' <strong>' . count($comments_all) . '</strong>' : $this->l('No comment')) . ((count($comments_non_lu) > 0) ?
					'&nbsp;&mdash;-&nbsp;<span style="color:green;font-weight:bold;">' . count($comments_non_lu) . ' '
					. $this->l('Comments pending') . '</span>' : '') . '<br />
						' . ((count($comments_all) > 0) ? '<span onclick="$(\'#comments\').slideToggle();" style="cursor: pointer"
							class="link"><img src="../img/admin/cog.gif" alt="' . $this->l('Comments') . '" title="' . $this->l('Comments') . '" />'
					. $this->l('Click here to manage comments') . '</span>' : '') . '
					</div>' . "\n";

			$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Comments'), $html_libre, 'col-lg-10');

			if (count($comments_all) > 0) {
				$html_libre = '';
				if (Tools::isSubmit('showComments')) {
					$html_libre .= '<div id="comments">' . "\n";
					$html_libre .= '<script type="text/javascript">
						$(document).ready(function() { $("html, body").animate({scrollTop: $("#labelComments").offset().top}, 750); });
						</script>' . "\n";
				} else
					$html_libre .= '<div id="comments" style="'
						. (Configuration::get($this->name . '_comment_div_visible') ? '' : 'display: none;') . '">' . "\n";

				$html_libre .= '
						<div class="blocs col-sm-4 ' . (self::isPSVersion('<', '1.6') ? 'fixBloc15' : '') . '">
							<h3><img src="../modules/' . $this->name . '/views/img/question.gif" alt="' . $this->l('Pending') . '" title="'
					. $this->l('Pending') . '" />' . count($comments_non_lu) . '&nbsp;' . $this->l('Comments pending') . '</h3>' . "\n";
				if (count($comments_non_lu) > 0) {
					$html_libre .= '<div class="wrap">' . "\n";
					foreach ($comments_non_lu as $value_c) {
						$html_libre .= '<div>' . "\n";
						$html_libre .= '
								<h4>
									<a href="' . $this->path_module_conf . '&deleteComment&idN=' . Tools::getValue('idN') . '&idC='
							. $value_c['id_' . $this->name . '_commentnews'] . '" class="hrefComment" onclick="return confirm(\''
							. $this->l('Are you sure?', __CLASS__, true, false) . '\');" style="float:right;">
										<img src="../img/admin/delete.gif" alt="' . $this->l('Delete') . '" /><span style="display:none;">'
							. $this->l('Delete') . '</span></a>
									' . ToolsCore::displayDate($value_c['date'], null, true) . '<br />' . $this->l('by') . ' <strong>'
							. $value_c['name'] . '</strong>
								</h4>' . "\n";
						if ($value_c['url'] != '')
							$html_libre .= '	<h5><a href="' . $value_c['url'] . '" target="_blank">' . $value_c['url'] . '</a></h5>' . "\n";

						$html_libre .= '	<p>' . $value_c['comment'] . '</p>' . "\n";
						$html_libre .= '
							<p class="center">
								<a href="' . $this->path_module_conf . '&enabledComment&idN=' . Tools::getValue('idN') . '&idC='
							. $value_c['id_' . $this->name . '_commentnews'] . '" class="hrefComment">
								<img src="../img/admin/enabled.gif" alt="' . $this->l('Approuved') . '" /><span style="display:none;">'
							. $this->l('Approuved') . '</span></a>
								<a href="' . $this->path_module_conf . '&editComment&idC=' . $value_c['id_' . $this->name . '_commentnews'] . '"
								class="hrefComment"><img src="../modules/' . $this->name . '/views/img/edit.gif" /><span style="display:none;">'
							. $this->l('Edit') . '</span></a>
								<a href="' . $this->path_module_conf . '&disabledComment&idN=' . Tools::getValue('idN') . '&idC='
							. $value_c['id_' . $this->name . '_commentnews'] . '" class="hrefComment"><img src="../img/admin/disabled.gif"
								alt="' . $this->l('Disabled') . '" /><span style="display:none;">' . $this->l('Disabled') . '</span></a>
							</p>' . "\n";
						$html_libre .= '</div>' . "\n";
					}
					$html_libre .= '</div>' . "\n";
				}
				$html_libre .= '
						</div>' . "\n";

				$html_libre .= '
						<div class="blocs col-sm-4 ' . (self::isPSVersion('<', '1.6') ? 'fixBloc15' : '') . '">
							<h3><img src="../img/admin/enabled.gif" alt="' . $this->l('Approuved') . '" title="' . $this->l('Approuved') . '" />'
					. count($comments_actif) . '&nbsp;' . $this->l('Comments approuved') . '</h3>' . "\n";
				if (count($comments_actif) > 0) {
					$html_libre .= '<div class="wrap">' . "\n";
					foreach ($comments_actif as $value_c) {
						$html_libre .= '<div>' . "\n";
						$html_libre .= '
								<h4>
									<a href="' . $this->path_module_conf . '&deleteComment&idN=' . Tools::getValue('idN') . '&idC='
							. $value_c['id_' . $this->name . '_commentnews'] . '" class="hrefComment" onclick="return confirm(\''
							. $this->l('Are you sure?', __CLASS__, true, false) . '\');" style="float:right;">
										<img src="../img/admin/delete.gif" alt="' . $this->l('Delete') . '"/><span style="display:none;">'
							. $this->l('Delete') . '</span></a>
									' . ToolsCore::displayDate($value_c['date'], null, true) . '<br />' . $this->l('by') . ' <strong>'
							. $value_c['name'] . '</strong>
								</h4>' . "\n";
						if ($value_c['url'] != '')
							$html_libre .= '	<h5><a href="' . $value_c['url'] . '" target="_blank">' . $value_c['url'] . '</a></h5>' . "\n";
						$html_libre .= '	<p>' . $value_c['comment'] . '</p>' . "\n";
						$html_libre .= '
							<p class="center">
								<a href="' . $this->path_module_conf . '&editComment&idC=' . $value_c['id_' . $this->name . '_commentnews'] . '"
								class="hrefComment"><img src="../modules/' . $this->name . '/views/img/edit.gif" /><span style="display:none;">'
							. $this->l('Edit') . '</span></a>
								<a href="' . $this->path_module_conf . '&disabledComment&idN=' . Tools::getValue('idN') . '&idC='
							. $value_c['id_' . $this->name . '_commentnews'] . '" class="hrefComment"><img src="../img/admin/disabled.gif"
								alt="' . $this->l('Deleted') . '" /><span style="display:none;">' . $this->l('Disabled') . '</span></a>
							</p>' . "\n";
						$html_libre .= '</div>' . "\n";
					}
					$html_libre .= '</div>' . "\n";
				}
				$html_libre .= '
						</div>' . "\n";

				$html_libre .= '
						<div class="blocs col-sm-3 ' . (self::isPSVersion('<', '1.6') ? 'fixBloc15' : '') . '">
							<h3><img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" />'
					. count($comments_disabled) . '&nbsp;' . $this->l('Comments disabled') . '</h3>' . "\n";
				if (count($comments_disabled) > 0) {
					$html_libre .= '<div class="wrap">' . "\n";
					foreach ($comments_disabled as $value_c) {
						$html_libre .= '<div>' . "\n";
						$html_libre .= '
								<h4>
									<a href="' . $this->path_module_conf . '&deleteComment&idN=' . Tools::getValue('idN') . '&idC='
							. $value_c['id_' . $this->name . '_commentnews'] . '" class="hrefComment"
									onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');" style="float:right;">
									<img src="../img/admin/delete.gif" alt="' . $this->l('Delete') . '"/><span style="display:none;">'
							. $this->l('Delete') . '</span></a>
									' . ToolsCore::displayDate($value_c['date'], null, true) . '<br />' . $this->l('by') . ' <strong>'
							. $value_c['name'] . '</strong>
								</h4>' . "\n";
						if ($value_c['url'] != '')
							$html_libre .= '	<h5><a href="' . $value_c['url'] . '" target="_blank">' . $value_c['url'] . '</a></h5>' . "\n";
						$html_libre .= '	<p>' . $value_c['comment'] . '</p>' . "\n";
						$html_libre .= '
							<p class="center">
								<a href="' . $this->path_module_conf . '&editComment&idC=' . $value_c['id_' . $this->name . '_commentnews'] . '"
								class="hrefComment"><img src="../modules/' . $this->name . '/views/img/edit.gif" /><span style="display:none;">'
							. $this->l('Edit') . '</span></a>
								<a href="' . $this->path_module_conf . '&enabledComment&idN=' . Tools::getValue('idN') . '&idC='
							. $value_c['id_' . $this->name . '_commentnews'] . '" class="hrefComment" ><img src="../img/admin/enabled.gif"
								alt="' . $this->l('Enabled') . '" /><span style="display:none;">' . $this->l('Approuved') . '</span></a>
							</p>' . "\n";
						$html_libre .= '</div>' . "\n";
					}
					$html_libre .= '</div>' . "\n";
				}
				$html_libre .= '
						</div>' . "\n";

				$html_libre .= '
						</div>
						<div class="clear"></div>
						' . "\n";
				$html_libre .= '
						<script type="text/javascript">
							$(document).ready(function() {
								$("a.hrefComment").mouseenter(function() {
									$("span:first", this).show(\'slow\');
								}).mouseleave(function() {
									$("span:first", this).hide();
								});
							});
						</script>' . "\n";

				$this->html_out .= $this->displayFormLibre('col-lg-2', '', $html_libre, 'col-lg-10');
			}
		}
		/***********************************************************/
		/* DEBUT SEO */
		$html_libre = '<span onclick="$(\'#seo\').slideToggle();" style="cursor: pointer" class="link">
						<img src="../img/admin/cog.gif" alt="' . $this->l('SEO') . '" title="' . $this->l('SEO') . '" />'
			. $this->l('Click here to improve SEO') . '
					</span>';

		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('SEO'), $html_libre, 'col-lg-7');

		$this->html_out .= '<div id="seo" style="display: none;">';
		/***********************************************************/
		$html_libre = '';
		foreach ($languages as $language) {
			$html_libre .= '<div id="link_rewrite_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $default_language ?
					'block' : 'none') . ';">
						<input ' . (self::isPSVersion('<', '1.6') ? 'class="fixInput15"' : '') . ' type="text" name="link_rewrite_'
				. $language['id_lang'] . '" id="slink_rewrite_' . $language['id_lang'] . '" value="'
				. (isset($news->link_rewrite[$language['id_lang']]) ? $news->link_rewrite[$language['id_lang']] : '') . '"
						onkeyup="if (isArrowKey(event)) return ;updateFriendlyURLPrestaBlog();" onchange="updateFriendlyURLPrestaBlog();"
						' . (isset($news->id) ? ' style="color:#7F7F7F;background-color:#e0e0e0;" disabled="true"' : '') . '
						/><sup> *</sup>
					</div>';
		}
		$this->html_out .= $this->displayFormLibre('col-lg-2',
			$this->l('Url Rewrite') . '<br/><a href="#" id="control" />' . (isset($news->id) ?
				$this->l('Enable this rewrite') : $this->l('Disable this rewrite')) . '</a>',
			$html_libre, 'col-lg-7', $this->displayFlagsFor('link_rewrite', $div_lang_name));
		/***********************************************************/
		$html_libre = '';
		foreach ($languages as $language) {
			$html_libre .= '<div id="meta_title_' . $language['id_lang'] . '" style="display: '
				. ($language['id_lang'] == $default_language ? 'block' : 'none') . ';">
						<input ' . (self::isPSVersion('<', '1.6') ? 'class="fixInput15"' : '') . ' type="text" name="meta_title_'
				. $language['id_lang'] . '" id="meta_title_' . $language['id_lang'] . '" value="'
				. (isset($news->meta_title[$language['id_lang']]) ? $news->meta_title[$language['id_lang']] : '') . '" />
					</div>';
		}
		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Meta Title'), $html_libre, 'col-lg-7',
			$this->displayFlagsFor('meta_title', $div_lang_name));
		/***********************************************************/
		$html_libre = '';
		foreach ($languages as $language) {
			$html_libre .= '<div id="meta_description_' . $language['id_lang'] . '" style="display: '
				. ($language['id_lang'] == $default_language ? 'block' : 'none') . ';">
						<input ' . (self::isPSVersion('<', '1.6') ? 'class="fixInput15"' : '') . ' type="text" name="meta_description_'
				. $language['id_lang'] . '" id="meta_description_' . $language['id_lang'] . '" value="'
				. (isset($news->meta_description[$language['id_lang']]) ? $news->meta_description[$language['id_lang']] : '') . '" />
					</div>';
		}
		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Meta Description'), $html_libre, 'col-lg-7',
			$this->displayFlagsFor('meta_description', $div_lang_name));
		/***********************************************************/
		$html_libre = '';
		foreach ($languages as $language) {
			$html_libre .= '<div id="meta_keywords_' . $language['id_lang'] . '" style="display: '
				. ($language['id_lang'] == $default_language ? 'block' : 'none') . ';">
						<input ' . (self::isPSVersion('<', '1.6') ? 'class="fixInput15"' : '') . ' type="text" name="meta_keywords_'
				. $language['id_lang'] . '" id="meta_keywords_' . $language['id_lang'] . '" value="'
				. (isset($news->meta_keywords[$language['id_lang']]) ? $news->meta_keywords[$language['id_lang']] : '') . '" />
					</div>';
		}
		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Meta Keywords'), $html_libre, 'col-lg-7',
			$this->displayFlagsFor('meta_keywords', $div_lang_name));
		/***********************************************************/
		$this->html_out .= $this->displayFormInput('col-lg-2', $this->l('Permanent redirect url'), 'url_redirect',
			$news->url_redirect, null, 'col-lg-7', $this->l('Advanced user only'), $this->l('Completed url with http://') . '<br/>'
			. sprintf($this->l('This feature will redirect %1$s to this url with a redirect 301'), '<strong>'
				. $news->title[$language['id_lang']] . '</strong>'), '<i class="icon-external-link"></i>');
		/***********************************************************/

		$this->html_out .= '</div>';
		/* FIN SEO */
		/***********************************************************/

		/***********************************************************/
		/* DEBUT IMAGE */
		$html_libre = '';
		if ($this->demo_mode)
			$html_libre .= $this->displayWarning($this->l('Feature disabled on the demo mode'));
		if (Tools::getValue('idN')
			&& file_exists(dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/admincrop_'
				. Tools::getValue('idN') . '.jpg')
		) {
			$html_libre .= '<span id="labelPicture"></span>';
			$config_theme_array = PrestaBlog::objectToArray($config_theme);
			if (Tools::getValue('pfx'))
				$html_libre .= '<script type="text/javascript">
					$(document).ready(function() { $("html, body").animate({scrollTop: $("#labelPicture").offset().top}, 750); });
					</script>' . "\n";

			$html_libre .= '
					<script src="' . __PS_BASE_URI__ . 'modules/prestablog/views/js/Jcrop/jquery.Jcrop.prestablog.js"></script>
					<link rel="stylesheet" href="' . __PS_BASE_URI__ . 'modules/prestablog/views/css/jquery.Jcrop.css" type="text/css" />
					<script language="Javascript">' . "\n";

			$html_libre .= '							var ratioValue = new Array();' . "\n";
			foreach ($config_theme_array['images'] as $key_theme_array => $value_theme_array)
				$html_libre .= '							ratioValue[\'' . $key_theme_array . '\'] = '
					. $value_theme_array['width'] / $value_theme_array['height'] . ';' . "\n";

			$html_libre .= '
						var monRatio;
						var monImage;

						$(function(){
							$("div.togglePreview").hide();' . "\n";
			if (Tools::getValue('pfx'))
				$html_libre .= '
								$(\'input[name$="imageChoix"]\').filter(\'[value="' . Tools::getValue('pfx') . '"]\').attr(\'checked\', true);
								$(\'input[name$="imageChoix"]\').filter(\'[value="' . Tools::getValue('pfx') . '"]\').parent().next(1).slideDown();
								$("#pfx").val(\'' . Tools::getValue('pfx') . '\');
								$("#ratio").val(ratioValue[\'' . Tools::getValue('pfx') . '\']);
								monRatio = ratioValue[\'' . Tools::getValue('pfx') . '\'];
								$(\'#cropbox\').Jcrop({
									\'aspectRatio\' : monRatio,
									\'onSelect\' : updateCoords
								});
								nomImage = \'' . $this->l('Resize') . ' ' . Tools::getValue('pfx') . '\';
								' . ($this->isPSVersion('>=', '1.6') ? '$("#resizeText").html(nomImage);' : '$("#resizeBouton").val(nomImage);') . '
								' . "\n";
			$html_libre .= '
							$(\'input[name$="imageChoix"]\').change(function () {
								$("div.togglePreview").slideUp();
								$(this).parent().next().slideDown();
								$("#pfx").val($(this).val());
								$("#ratio").val(ratioValue[$(this).val()]);
								monRatio = ratioValue[$(this).val()];
								$(\'#cropbox\').Jcrop({
									\'aspectRatio\' : monRatio,
									\'onSelect\' : updateCoords
								});
								nomImage = \'' . $this->l('Resize') . ' \'+$("#pfx").val();
								' . ($this->isPSVersion('>=', '1.6') ? '$("#resizeText").html(nomImage);' : '$("#resizeBouton").val(nomImage);') . '
							});
						});

						function updateCoords(c)
						{
							$(\'#x\').val(c.x);
							$(\'#y\').val(c.y);
							$(\'#w\').val(c.w);
							$(\'#h\').val(c.h);
						};
						function checkCoords()
						{
							if (!$(\'input[name="imageChoix"]:checked\').val()) {
								alert(\'' . $this->l('Please select a picture to crop.') . '\');
								return false;
							}
							else {
								if (parseInt($(\'#w\').val()))
									return true;
								alert(\'' . $this->l('Please select a crop region then press submit.') . '\');
								return false;
							}
						};
					</script>';
			if ($this->isPSVersion('>=', '1.6')) {
				$html_libre .= '
							<div id="image" class="col-md-7">
								<div class="panel">
									<img id="cropbox" src="' . $this->_path . 'views/img/' . Configuration::get($this->name . '_theme') . '/up-img/admincrop_'
					. Tools::getValue('idN') . '.jpg?' . md5(time()) . '" />
									<p align="center">' . $this->l('Filesize') . ' ' . (filesize(dirname(__FILE__) . '/views/img/'
							. Configuration::get($this->name . '_theme') . '/up-img/' . Tools::getValue('idN') . '.jpg') / 1000) . 'kb</p>
									<p>
										<a href="' . $this->path_module_conf . '&deleteImageBlog&idN=' . Tools::getValue('idN') . '"
										onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');">
											<img src="../img/admin/delete.gif" alt="' . $this->l('Delete') . '" /> ' . $this->l('Delete') . '
										</a>
									</p>
									<p>' . $this->displayFormFileNoLabel('homepage_logo', 'col-lg-10', $this->l('Format:') . ' .jpg .png') . '</p>
								</div>
							</div>
							<div class="col-md-5">' . "\n";
				foreach ($config_theme_array['images'] as $key_theme_array => $value_theme_array) {
					$width_force = '';
					if (file_exists(dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/'
						. $key_theme_array . '_' . Tools::getValue('idN') . '.jpg')) {
						$attrib_image = getimagesize(dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme')
							. '/up-img/' . $key_theme_array . '_' . Tools::getValue('idN') . '.jpg');
						if ((int)$attrib_image[0] > 200)
							$width_force = 'width="200"';
					}

					$label_pic = $key_theme_array;
					switch ($key_theme_array) {
						case 'thumb' :
							$label_pic = $this->l('thumb for articles list');
							break;
						case 'slide' :
							$label_pic = $this->l('slide picture (home / blog page)');
							break;
					}
					$html_libre .= '
									<div class="panel">
										<p><input type="radio" name="imageChoix" value="' . $key_theme_array . '" />&nbsp;' . $label_pic . '
										<span style="font-size: 80%;">(' . ($width_force ? $this->l('Real size : ') : '')
						. $value_theme_array['width'] . ' * ' . $value_theme_array['height'] . ')</span></p>
										<div class="togglePreview" style="text-align:center;">
											<img style="border:1px solid #4D4D4D;padding:0px;" src="' . $this->_path . 'views/img/'
						. Configuration::get($this->name . '_theme') . '/up-img/' . $key_theme_array . '_' . Tools::getValue('idN') . '.jpg?'
						. md5(time()) . '" ' . $width_force . ' />
										</div>
									</div>' . "\n";
				}
				$html_libre .= '
									<div class="panel">
										<a class="btn btn-default" onclick="if (checkCoords()) {formCrop.submit();}"  >
											<i class="icon-crop"></i>&nbsp;<span id="resizeText">' . $this->l('Resize') . '</span>
										</a>
									</div>
							</div>' . "\n";
			} else {
				$html_libre .= '
							<div id="image" style="width:400px;float:left;margin-right:5px;">
								<img id="cropbox" src="' . $this->_path . 'views/img/' . Configuration::get($this->name . '_theme') . '/up-img/admincrop_'
					. Tools::getValue('idN') . '.jpg?' . md5(time()) . '" />
								<p align="center">' . $this->l('Filesize') . ' ' . (filesize(dirname(__FILE__) . '/views/img/'
							. Configuration::get($this->name . '_theme') . '/up-img/' . Tools::getValue('idN') . '.jpg') / 1000) . 'kb</p>
								<p>
									<a href="' . $this->path_module_conf . '&deleteImageBlog&idN=' . Tools::getValue('idN') . '"
									onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');">
										<img src="../img/admin/delete.gif" alt="' . $this->l('Delete') . '" /> ' . $this->l('Delete') . '
									</a>
								</p>
								<p>' . $this->displayFormFileNoLabel('homepage_logo', 'col-lg-10', $this->l('Format:') . ' .jpg .png') . '</p>
							</div>
							<div>' . "\n";

				foreach ($config_theme_array['images'] as $key_theme_array => $value_theme_array) {
					$width_force = '';
					if (file_exists(dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/'
						. $key_theme_array . '_' . Tools::getValue('idN') . '.jpg')) {
						$attrib_image = getimagesize(dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/'
							. $key_theme_array . '_' . Tools::getValue('idN') . '.jpg');
						if ((int)$attrib_image[0] > 200)
							$width_force = 'width="200"';
					}

					$label_pic = $key_theme_array;
					switch ($key_theme_array) {
						case 'thumb' :
							$label_pic = $this->l('thumb for articles list');
							break;
						case 'slide' :
							$label_pic = $this->l('slide picture (home / blog page)');
							break;
					}
					$html_libre .= '
									<div style="float:left;width:250px;border:1px solid #ccc;background-color:#fff;padding:5px;margin-bottom:10px;">
										<p><input type="radio" name="imageChoix" value="' . $key_theme_array . '" />&nbsp;'
						. $label_pic . ' <span style="font-size: 80%;">(' . ($width_force ? $this->l('Real size : ') : '')
						. $value_theme_array['width'] . ' * ' . $value_theme_array['height'] . ')</span></p>
										<div class="togglePreview" style="text-align:center;">
											<img style="border:1px solid #4D4D4D;padding:0px;" src="' . $this->_path . 'views/img/'
						. Configuration::get($this->name . '_theme') . '/up-img/' . $key_theme_array . '_' . Tools::getValue('idN') . '.jpg?'
						. md5(time()) . '" ' . $width_force . ' />
										</div>
									</div>' . "\n";
				}
				$html_libre .= '
									<div style="text-align:center;float:left;width:250px;border:1px solid #ccc;
									background-color:#fff;padding:5px;margin-bottom:10px;">
										<input type="button" value="' . $this->l('Resize') . '" id="resizeBouton"
										class="button" onclick="if (checkCoords()) {formCrop.submit();}" />
									</div>
								</div>' . "\n";
			}
		} else
			$html_libre .= $this->displayFormFileNoLabel('homepage_logo', 'col-lg-5', $this->l('Format:') . ' .jpg .png');

		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Main picture'), $html_libre, 'col-lg-10');
		/* FIN IMAGE */
		/***********************************************************/

		/***********************************************************/
		/* DEBUT INTRO */
		$html_libre = '';
		foreach ($languages as $language) {
			$html_libre .= '<div id="cpara1_' . $language['id_lang'] . '" style="display: '
				. ($language['id_lang'] == $default_language ? 'block' : 'none') . ';">
						<textarea ' . (self::isPSVersion('<', '1.6') ? 'style="width:500px;height:100px;"' : '') . ' maxlength="'
				. (int)Configuration::get('prestablog_news_intro_length') . '" id="paragraph_' . $language['id_lang'] . '" name="paragraph_'
				. $language['id_lang'] . '">' . (isset($news->paragraph[$language['id_lang']]) ? $news->paragraph[$language['id_lang']] : '')
				. '</textarea>
						<p>' . $this->l('Caracters remaining') . ' : <span id="compteur-texte-' . $language['id_lang'] . '" style="color:red;">'
				. Tools::strlen($news->paragraph[$language['id_lang']]) . ' / ' . (int)Configuration::get('prestablog_news_intro_length') . '</span>
						<br/>' . $this->l('You can configure the max length in the general configuration of the module theme.') . '</p>
					</div>';
		}
		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Introduction'), $html_libre, 'col-lg-7',
			$this->displayFlagsFor('cpara1', $div_lang_name));
		/* FIN INTRO */
		/***********************************************************/

		/***********************************************************/
		/* DEBUT CONTENU */
		$html_libre = '';
		foreach ($languages as $language) {
			$html_libre .= '<div id="cpara2_' . $language['id_lang'] . '" style="display: '
				. ($language['id_lang'] == $default_language ? 'block' : 'none') . ';">
						<textarea class="rte ' . (self::isPSVersion('>=', '1.6') ? 'autoload_rte rte' : '') . '" id="content_'
				. $language['id_lang'] . '" name="content_' . $language['id_lang'] . '">' . (isset($news->content[$language['id_lang']]) ?
					$news->content[$language['id_lang']] : '') . '</textarea>
					</div>';
		}
		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Content'), $html_libre, 'col-lg-7',
			$this->displayFlagsFor('cpara2', $div_lang_name));
		/* FIN CONTENU */
		/***********************************************************/

		/***********************************************************/
		/* DEBUT CATEGORIES */
		$html_libre = '';
		$html_libre .= '
			<div class="panel">
				<table cellspacing="0" cellpadding="0" class="table" ' . (self::isPSVersion('<', '1.6') ? 'style="width:60%"' : '') . '>
					<thead>
						<tr>
							<th style="width:20px;"><input type="checkbox" name="checkme" class="noborder"
							onclick="checkDelBoxes(this.form, \'categories[]\', this.checked)" /></th>
							<th style="width:20px;">' . $this->l('ID') . '</th>
							<th style="width:60px;">' . $this->l('Image') . '</th>
							<th>' . $this->l('Name') . '&nbsp;<img id="imgCatLang" src="../img/l/' . $default_language . '.jpg"
							style="vertical-align:middle;" /></th>
						</tr>
					</thead>';

		$liste_cat = CategoriesClass::getListe((int)$this->context->language->id, 0);
		$liste_cat_no_arbre = CategoriesClass::getListeNoArbo();
		$liste_cat_branches_actives = array();

		foreach (CorrespondancesCategoriesClass::getCategoriesListe((int)$news->id) as $value)
			$liste_cat_branches_actives = array_unique(array_merge($liste_cat_branches_actives,
				preg_split('/\./', CategoriesClass::getBranche((int)$value))));

		$html_libre .= $this->displayListeArborescenceCategoriesNews($liste_cat, 0, $liste_cat_branches_actives);

		$html_libre .= '
				</table>
			</div>
			<script language="javascript" type="text/javascript">
				$(document).ready(function() {';
		foreach ($liste_cat_branches_actives as $value)
			$html_libre .= '$("tr#prestablog_categorie_' . $value . '").show();';

		foreach ($liste_cat_no_arbre as $value)
			if (in_array((int)$value['parent'], $liste_cat_branches_actives))
				$html_libre .= '$("tr#prestablog_categorie_' . $value['id_prestablog_categorie'] . '").show();';

		$html_libre .= '
					$("img.expand-cat").click(function() {
						BranchClick=$(this).attr("rel");
						BranchClickSplit = BranchClick.split(\'.\');
						fixBranchClickSplit = "0,"+BranchClickSplit.toString();

						switch ($(this).attr("src")) {
							case "/modules/prestablog/views/img/expand.gif":
								$("tr.prestablog_branch").each(function() {
									BranchParent = $(this).attr("rel");
									BranchParentSplit = BranchParent.split(\'.\');
									fixBranchParentSplit = "0,"+BranchParentSplit.toString();

									if ($.isSubstring(fixBranchParentSplit, fixBranchClickSplit)
											&& BranchClick != BranchParent
											&& BranchClickSplit.length+1 == BranchParentSplit.length
										) {
											$(this).show();
									}
								});
								$(this).attr("src", "/modules/prestablog/views/img/collapse.gif");
								break;

							case "/modules/prestablog/views/img/collapse.gif":
								$("tr.prestablog_branch").each(function() {
									BranchParent = $(this).attr("rel");
									BranchParentSplit = BranchParent.split(\'.\');
									fixBranchParentSplit = "0,"+BranchParentSplit.toString();

									if ($.isSubstring(fixBranchParentSplit, fixBranchClickSplit)
											&&	BranchClick != BranchParent
										) {
											$(this).hide();
											$(this).find("img.expand-cat").each(function() {
												$(this).attr("src", "/modules/prestablog/views/img/expand.gif");
											});
									}
								});
								$(this).attr("src", "/modules/prestablog/views/img/expand.gif");
								break;
						}
					});
				});
				jQuery.isSubstring = function(haystack, needle) {
					 return haystack.indexOf(needle) !== -1;
				};
			</script>';
		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Categories'), $html_libre, 'col-lg-5');
		/* FIN CATEGORIES */
		/***********************************************************/

		/***********************************************************/
		/* DEBUT PRODUITS LIES */
		$html_libre = '';
		$html_libre .= '
				<div id="currentProductLink" style="display:none;">' . "\n";

		if (Tools::getValue('idN')) {
			$products_link = NewsClass::getProductLinkListe((int)Tools::getValue('idN'));

			if (count($products_link) > 0)
				foreach ($products_link as $product_link)
					$html_libre .= '<input type="text" name="productsLink[]" value="' . (int)$product_link . '"
						class="linked_' . (int)$product_link . '" />' . "\n";
		}
		if (Tools::getValue('productsLink') && !Tools::getValue('idN'))
			foreach (Tools::getValue('productsLink') as $product_link)
				$html_libre .= '<input type="text" name="productsLink[]" value="' . (int)$product_link['id_product'] . '"
					class="linked_' . (int)$product_link['id_product'] . '" />' . "\n";

		$html_libre .= '</div>';

		if ($this->isPSVersion('>=', '1.6'))
			$html_libre .= '<div class="panel col-sm-4">';
		else
			$html_libre .= '
					<table cellspacing="0" cellpadding="0" id="productLinkTable">
						<tr>
							<td style="padding-right:3px;width:50%;vertical-align:top;">';

		$html_libre .= '
							<table cellspacing="0" cellpadding="0" class="table" style="width:100%">
								<thead>
									<tr>
										<th class="center" style="width:30px;">' . $this->l('ID') . '</th>
										<th class="center" style="width:50px;">' . $this->l('Image') . '</th>
										<th class="center">' . $this->l('Name') . '</th>
										<th class="center" style="width:40px;">' . $this->l('Unlink') . '</th>
									</tr>
								</thead>
								<tbody id="productLinked">
									<tr>
										<td colspan="4" class="center">' . $this->l('No product linked') . '</td>
									</tr>
								</tbody>
							</table>';
		if ($this->isPSVersion('>=', '1.6')) {
			$html_libre .= '</div>';
			$html_libre .= '<div class="col-sm-1"></div>';
			$html_libre .= '<div class="panel col-sm-5">';
		} else
			$html_libre .= '		</td>
						<td style="padding-left:3px;width:50%;vertical-align:top;">';

		$html_libre .= '
							<p class="center">' . $this->l('Search') . ' : <input type="text" size="20" id="productLinkSearch"
							name="productLinkSearch" /></p>
							<table cellspacing="0" cellpadding="0" class="table" style="width:100%">
								<thead>
									<tr>
										<th class="center" style="width:40px;">' . $this->l('Link') . '</th>
										<th class="center" style="width:30px;">' . $this->l('ID') . '</th>
										<th class="center" style="width:50px;">' . $this->l('Image') . '</th>
										<th class="center">' . $this->l('Name') . '</th>
									</tr>
								</thead>
								<tbody id="productLinkResult">
									<tr>
										<td colspan="4" class="center">' . $this->l('You must search before') . '
										(' . (int)Configuration::get($this->name . '_nb_car_min_linkprod') . ' ' . $this->l('caract. minimum') . ')</td>
									</tr>
								</tbody>
							</table>';

		if ($this->isPSVersion('>=', '1.6'))
			$html_libre .= '</div>';
		else
			$html_libre .= '
							</td>
						</tr>
					</table>';

		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Related products'), $html_libre, 'col-lg-10');
		/* FIN PRODUITS LIES */
		/***********************************************************/

		/***********************************************************/
		/* DEBUT ARTICLES LIES */
		$html_libre = '';
		$html_libre .= '
				<div id="currentArticleLink" style="display:none;">' . "\n";

		if (Tools::getValue('idN')) {
			$articles_link = NewsClass::getArticleLinkListe((int)Tools::getValue('idN'));

			if (count($articles_link) > 0)
				foreach ($articles_link as $article_link)
					$html_libre .= '<input type="text" name="articlesLink[]" value="' . (int)$article_link . '"
						class="linked_' . (int)$article_link . '" />' . "\n";
		}
		if (Tools::getValue('articlesLink') && !Tools::getValue('idN'))
			foreach (Tools::getValue('articlesLink') as $article_link)
				$html_libre .= '<input type="text" name="articlesLink[]" value="' . (int)$article_link['id_prestablog_news'] . '"
					class="linked_' . (int)$article_link['id_prestablog_news'] . '" />' . "\n";

		$html_libre .= '</div>';

		if ($this->isPSVersion('>=', '1.6'))
			$html_libre .= '<div class="panel col-sm-4">';
		else
			$html_libre .= '
					<table cellspacing="0" cellpadding="0" id="articleLinkTable">
						<tr>
							<td style="padding-right:3px;width:50%;vertical-align:top;">';

		$html_libre .= '
							<table cellspacing="0" cellpadding="0" class="table" style="width:100%">
								<thead>
									<tr>
										<th class="center" style="width:30px;">' . $this->l('ID') . '</th>
										<th class="center" style="width:50px;">' . $this->l('Image') . '</th>
										<th class="center">' . $this->l('Title') . '</th>
										<th class="center" style="width:40px;">' . $this->l('Unlink') . '</th>
									</tr>
								</thead>
								<tbody id="articleLinked">
									<tr>
										<td colspan="4" class="center">' . $this->l('No article linked') . '</td>
									</tr>
								</tbody>
							</table>';
		if ($this->isPSVersion('>=', '1.6')) {
			$html_libre .= '</div>';
			$html_libre .= '<div class="col-sm-1"></div>';
			$html_libre .= '<div class="panel col-sm-5">';
		} else
			$html_libre .= '		</td>
						<td style="padding-left:3px;width:50%;vertical-align:top;">';

		$html_libre .= '
							<p class="center">' . $this->l('Search') . ' : <input type="text" size="20" id="articleLinkSearch"
							name="articleLinkSearch" /></p>
							<table cellspacing="0" cellpadding="0" class="table" style="width:100%">
								<thead>
									<tr>
										<th class="center" style="width:40px;">' . $this->l('Link') . '</th>
										<th class="center" style="width:30px;">' . $this->l('ID') . '</th>
										<th class="center" style="width:50px;">' . $this->l('Image') . '</th>
										<th class="center">' . $this->l('Title') . '</th>
									</tr>
								</thead>
								<tbody id="articleLinkResult">
									<tr>
										<td colspan="4" class="center">' . $this->l('You must search before') . '
										(' . (int)Configuration::get($this->name . '_nb_car_min_linknews') . ' ' . $this->l('caract. minimum') . ')</td>
									</tr>
								</tbody>
							</table>';

		if ($this->isPSVersion('>=', '1.6'))
			$html_libre .= '</div>';
		else
			$html_libre .= '
							</td>
						</tr>
					</table>';

		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Related Posts'), $html_libre, 'col-lg-10');
		/* FIN ARTICLES LIES */
		/***********************************************************/

		$this->html_out .= $this->displayFormDate('col-lg-2', $this->l('Date'), 'date', $news->date, true);

		$this->html_out .= '<div class="margin-form">';

		if ($this->isPSVersion('>=', '1.6'))
			if (Tools::getValue('idN'))
				$this->html_out .= '<button class="btn btn-primary" id="submitForm" name="submitUpdateNews" type="submit">
				<i class="icon-save"></i>&nbsp;' . $this->l('Update') . '</button>';
			else
				$this->html_out .= '<button class="btn btn-primary" id="submitForm" name="submitAddNews" type="submit">
				<i class="icon-plus"></i>&nbsp;' . $this->l('Add content') . '</button>';
		else
			if (Tools::getValue('idN'))
				$this->html_out .= '<input type="submit" id="submitForm" name="submitUpdateNews"
				value="' . $this->l('Update') . '" class="button" />';
			else
				$this->html_out .= '<input type="submit" id="submitForm" name="submitAddNews"
				value="' . $this->l('Add content') . '" class="button" />';

		$this->html_out .= '</div>';

		$this->html_out .= $this->displayFormClose();

		$this->html_out .= '
			<form name="formCrop" id="formCrop" action="' . $this->path_module_conf . '" method="post" onsubmit="return checkCoords();">
				<input type="hidden" name="idN" value="' . Tools::getValue('idN') . '" />
				<input type="hidden" id="pfx" name="pfx" value="' . Tools::getValue('pfx') . '" />
				<input type="hidden" id="x" name="x" />
				<input type="hidden" id="y" name="y" />
				<input type="hidden" id="w" name="w" />
				<input type="hidden" id="h" name="h" />
				<input type="hidden" id="ratio" name="ratio" />
				<input type="hidden" name="submitCrop" value="submitCrop" />
			</form>';
	}

	private function displayFormCategories()
	{
		$config_theme = $this->getConfigXmlTheme(Configuration::get($this->name . '_theme'));

		$default_language = $this->langue_default_store;
		$languages = Language::getLanguages(true);
		$iso = Language::getIsoById((int)$this->context->language->id);
		$div_lang_name = 'title¤link_rewrite¤meta_title¤meta_description¤meta_keywords¤cpara1';

		$legend_title = $this->l('Add a category');
		if (Tools::getValue('idC')) {
			$categories = new CategoriesClass((int)Tools::getValue('idC'));
			$legend_title = $this->l('Update the category') . ' #' . $categories->id;
		} else
			$categories = new CategoriesClass();

		if (Tools::isSubmit('submitUpdateCat') || Tools::isSubmit('submitAddCat')) {
			$categories->id_shop = (int)$this->context->shop->id;
			$categories->copyFromPost();
		}

		$iso_tiny_mce = (file_exists(_PS_ROOT_DIR_ . '/js/tinymce/jscripts/tiny_mce/langs/' . $iso . '.js') ? $iso : 'en');
		$ad = dirname($_SERVER['PHP_SELF']);
		$this->html_out .= '
			<script type="text/javascript">
			' . (Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL') ? 'var PS_ALLOW_ACCENTED_CHARS_URL = 1;' :
				'var PS_ALLOW_ACCENTED_CHARS_URL = 0;') . '
			var iso = \'' . $iso_tiny_mce . '\' ;
			var pathCSS = \'' . _THEME_CSS_DIR_ . '\' ;
			var ad = \'' . $ad . '\' ;
			</script>
			<script type="text/javascript" src="' . __PS_BASE_URI__ . 'js/tiny_mce/tiny_mce.js"></script>
			' . (self::isPSVersion('>=', '1.6') ? '
			<script type="text/javascript" src="' . __PS_BASE_URI__ . 'modules/prestablog/views/js/tinymce.inc.1.6.js"></script>
			<script type="text/javascript">
				$(function() {
					tinySetup({ editor_selector :"autoload_rte" });
				});
			</script>
				' : '
			<script type="text/javascript" src="' . __PS_BASE_URI__ . 'modules/prestablog/views/js/tinymce.inc.js"></script>
				') . '
			<script type="text/javascript">
				id_language = Number(' . $default_language . ');

				function copy2friendlyURLPrestaBlog() {
					if (!$(\'#slink_rewrite_\'+id_language).attr(\'disabled\')) {
						$(\'#slink_rewrite_\'+id_language).val(str2url($(\'input#title_\'+id_language).val().replace(/^[0-9]+\./, \'\'), \'UTF-8\'));
					}
				}
				function updateFriendlyURLPrestaBlog() {
					$(\'#slink_rewrite_\'+id_language).val(str2url($(\'#slink_rewrite_\'+id_language).val().replace(/^[0-9]+\./, \'\'), \'UTF-8\'));
				}

				$(function() {
					$("#submitForm").click(function() {';
		foreach ($languages as $language)
			$this->html_out .= '$(\'#slink_rewrite_' . $language['id_lang'] . '\').removeAttr("disabled");';

		$this->html_out .= '
					});

					$("#control").toggle(
						function () {
							$(\'#slink_rewrite_\'+id_language).removeAttr("disabled");
							$(\'#slink_rewrite_\'+id_language).css("background-color", "#fff");
							$(\'#slink_rewrite_\'+id_language).css("color", "#000");
							$(this).html("' . $this->l('Disable this rewrite') . '");
						},
						function () {
							$(\'#slink_rewrite_\'+id_language).attr("disabled", true);
							$(\'#slink_rewrite_\'+id_language).css("background-color", "#e0e0e0");
							$(\'#slink_rewrite_\'+id_language).css("color", "#7F7F7F");
							$(this).html("' . $this->l('Enable this rewrite') . '");
						}
					);
					';

		foreach ($languages as $language)
			$this->html_out .= '
					if ($("#slink_rewrite_' . $language['id_lang'] . '").val() == \'\') {
						$("#slink_rewrite_' . $language['id_lang'] . '").removeAttr("disabled");
						$("#slink_rewrite_' . $language['id_lang'] . '").css("background-color", "#fff");
						$("#slink_rewrite_' . $language['id_lang'] . '").css("color", "#000");
						$("#control").html("' . $this->l('Disable this rewrite') . '");
					}';

		$this->html_out .= '
				});
			</script>' . "\n";

		$this->html_out .= $this->displayFormOpen('icon-edit', $legend_title, $this->path_module_conf);
		if (Tools::getValue('idC'))
			$this->html_out .= '<input type="hidden" name="idC" value="' . Tools::getValue('idC') . '" />';
		/***********************************************************/
		$this->html_out .= $this->displayFormEnableItem('col-lg-2', $this->l('Activate'), 'actif', $categories->actif);
		/***********************************************************/
		$html_libre = '';
		foreach ($languages as $language)
			$html_libre .= '<div id="title_' . $language['id_lang'] . '" style="display: '
				. ($language['id_lang'] == $default_language ? 'block' : 'none') . ';">
					<input ' . (self::isPSVersion('<', '1.6') ? 'class="fixInput15"' : '') . ' type="text" name="title_'
				. $language['id_lang'] . '" id="title_' . $language['id_lang'] . '" maxlength="'
				. (int)Configuration::get('prestablog_news_title_length') . '" value="' . (isset($categories->title[$language['id_lang']]) ?
					$categories->title[$language['id_lang']] : '') . '" onkeyup="if (isArrowKey(event)) return; copy2friendlyURLPrestaBlog();"
					onchange="copy2friendlyURLPrestaBlog();" />
				</div>';

		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Title'), $html_libre, 'col-lg-7',
			$this->displayFlagsFor('title', $div_lang_name));
		/***********************************************************/
		$html_libre = $categories->displaySelectArboCategories(CategoriesClass::getListe((int)$this->context->language->id, 0),
			(int)$categories->parent, 0, $this->l('Top level'), 'parent');

		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Parent category'), $html_libre, 'col-lg-7');
		/***********************************************************/

		/***********************************************************/
		/* DEBUT SEO */
		$html_libre = '<span onclick="$(\'#seo\').slideToggle();" style="cursor: pointer" class="link">
						<img src="../img/admin/cog.gif" alt="' . $this->l('SEO') . '" title="' . $this->l('SEO') . '" />' . $this->l('Click here to improve SEO') . '
					</span>';

		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('SEO'), $html_libre, 'col-lg-7');

		$this->html_out .= '<div id="seo" style="display: none;">';
		/***********************************************************/
		$html_libre = '';
		foreach ($languages as $language)
			$html_libre .= '<div id="link_rewrite_' . $language['id_lang'] . '" style="display: '
				. ($language['id_lang'] == $default_language ? 'block' : 'none') . ';">
					<input ' . (self::isPSVersion('<', '1.6') ? 'class="fixInput15"' : '') . ' type="text" name="link_rewrite_' . $language['id_lang'] . '"
					id="slink_rewrite_' . $language['id_lang'] . '" value="' . (isset($categories->link_rewrite[$language['id_lang']]) ?
					$categories->link_rewrite[$language['id_lang']] : '') . '"
					onkeyup="if (isArrowKey(event)) return ;updateFriendlyURLPrestaBlog();" onchange="updateFriendlyURLPrestaBlog();"
					' . (isset($categories->id) ? ' style="color:#7F7F7F;background-color:#e0e0e0;" disabled="true"' : '') . '
					/><sup> *</sup>
				</div>';

		$this->html_out .= $this->displayFormLibre('col-lg-2',
			$this->l('Url Rewrite') . '<br/><a href="#" id="control" />' . (isset($categories->id) ?
				$this->l('Enable this rewrite') : $this->l('Disable this rewrite')) . '</a>',
			$html_libre, 'col-lg-7', $this->displayFlagsFor('link_rewrite', $div_lang_name));
		/***********************************************************/
		$html_libre = '';
		foreach ($languages as $language)
			$html_libre .= '<div id="meta_title_' . $language['id_lang'] . '" style="display: '
				. ($language['id_lang'] == $default_language ? 'block' : 'none') . ';">
					<input ' . (self::isPSVersion('<', '1.6') ? 'class="fixInput15"' : '') . ' type="text" name="meta_title_' . $language['id_lang'] . '"
					id="meta_title_' . $language['id_lang'] . '" value="' . (isset($categories->meta_title[$language['id_lang']]) ?
					$categories->meta_title[$language['id_lang']] : '') . '" />
				</div>';

		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Meta Title'), $html_libre, 'col-lg-7',
			$this->displayFlagsFor('meta_title', $div_lang_name));
		/***********************************************************/
		$html_libre = '';
		foreach ($languages as $language)
			$html_libre .= '<div id="meta_description_' . $language['id_lang'] . '" style="display: '
				. ($language['id_lang'] == $default_language ? 'block' : 'none') . ';">
					<input ' . (self::isPSVersion('<', '1.6') ? 'class="fixInput15"' : '') . ' type="text"
					name="meta_description_' . $language['id_lang'] . '" id="meta_description_' . $language['id_lang'] . '"
					value="' . (isset($categories->meta_description[$language['id_lang']]) ? $categories->meta_description[$language['id_lang']] : '') . '" />
				</div>';

		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Meta Description'), $html_libre, 'col-lg-7',
			$this->displayFlagsFor('meta_description', $div_lang_name));
		/***********************************************************/
		$html_libre = '';
		foreach ($languages as $language)
			$html_libre .= '<div id="meta_keywords_' . $language['id_lang'] . '" style="display: '
				. ($language['id_lang'] == $default_language ? 'block' : 'none') . ';">
					<input ' . (self::isPSVersion('<', '1.6') ? 'class="fixInput15"' : '') . ' type="text"
					name="meta_keywords_' . $language['id_lang'] . '" id="meta_keywords_' . $language['id_lang'] . '"
					value="' . (isset($categories->meta_keywords[$language['id_lang']]) ? $categories->meta_keywords[$language['id_lang']] : '') . '" />
				</div>';

		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Meta Keywords'), $html_libre, 'col-lg-7',
			$this->displayFlagsFor('meta_keywords', $div_lang_name));
		/***********************************************************/
		$this->html_out .= '</div>';
		/* FIN SEO */
		/***********************************************************/

		/***********************************************************/
		/* DEBUT IMAGE */
		$html_libre = '';
		if ($this->demo_mode)
			$html_libre .= $this->displayWarning($this->l('Feature disabled on the demo mode'));

		if (Tools::getValue('idC')
			&& file_exists(dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme')
				. '/up-img/c/admincrop_' . Tools::getValue('idC') . '.jpg')
		) {
			$html_libre .= '<span id="labelPicture"></span>';
			$config_theme_array = PrestaBlog::objectToArray($config_theme);
			if (Tools::getValue('pfx'))
				$html_libre .= '<script type="text/javascript">
					$(document).ready(function() { $("html, body").animate({scrollTop: $("#labelPicture").offset().top}, 750); });
					</script>' . "\n";

			$html_libre .= '
					<script src="' . __PS_BASE_URI__ . 'modules/prestablog/views/js/Jcrop/jquery.Jcrop.prestablog.js"></script>
					<link rel="stylesheet" href="' . __PS_BASE_URI__ . 'modules/prestablog/views/css/jquery.Jcrop.css" type="text/css" />
					<script language="Javascript">' . "\n";

			$html_libre .= '							var ratioValue = new Array();' . "\n";
			foreach ($config_theme_array['categories'] as $key_theme_array => $value_theme_array)
				$html_libre .= '							ratioValue[\'' . $key_theme_array . '\'] = '
					. $value_theme_array['width'] / $value_theme_array['height'] . ';' . "\n";

			$html_libre .= '
						var monRatio;
						var monImage;

						$(function(){
							$("div.togglePreview").hide();' . "\n";
			if (Tools::getValue('pfx'))
				$html_libre .= '
								$(\'input[name$="imageChoix"]\').filter(\'[value="' . Tools::getValue('pfx') . '"]\').attr(\'checked\', true);
								$(\'input[name$="imageChoix"]\').filter(\'[value="' . Tools::getValue('pfx') . '"]\').parent().next(1).slideDown();
								$("#pfx").val(\'' . Tools::getValue('pfx') . '\');
								$("#ratio").val(ratioValue[\'' . Tools::getValue('pfx') . '\']);
								monRatio = ratioValue[\'' . Tools::getValue('pfx') . '\'];
								$(\'#cropbox\').Jcrop({
									\'aspectRatio\' : monRatio,
									\'onSelect\' : updateCoords
								});
								nomImage = \'' . $this->l('Resize') . ' ' . Tools::getValue('pfx') . '\';
								' . ($this->isPSVersion('>=', '1.6') ? '$("#resizeText").html(nomImage);' : '$("#resizeBouton").val(nomImage);') . '
								' . "\n";
			$html_libre .= '
							$(\'input[name$="imageChoix"]\').change(function () {
								$("div.togglePreview").slideUp();
								$(this).parent().next().slideDown();
								$("#pfx").val($(this).val());
								$("#ratio").val(ratioValue[$(this).val()]);
								monRatio = ratioValue[$(this).val()];
								$(\'#cropbox\').Jcrop({
									\'aspectRatio\' : monRatio,
									\'onSelect\' : updateCoords
								});
								nomImage = \'' . $this->l('Resize') . ' \'+$("#pfx").val();
								' . ($this->isPSVersion('>=', '1.6') ? '$("#resizeText").html(nomImage);' : '$("#resizeBouton").val(nomImage);') . '
							});
						});

						function updateCoords(c)
						{
							$(\'#x\').val(c.x);
							$(\'#y\').val(c.y);
							$(\'#w\').val(c.w);
							$(\'#h\').val(c.h);
						};
						function checkCoords()
						{
							if (!$(\'input[name="imageChoix"]:checked\').val()) {
								alert(\'' . $this->l('Please select a picture to crop.') . '\');
								return false;
							}
							else {
								if (parseInt($(\'#w\').val()))
									return true;
								alert(\'' . $this->l('Please select a crop region then press submit.') . '\');
								return false;
							}
						};
					</script>';
			if ($this->isPSVersion('>=', '1.6')) {
				$html_libre .= '
							<div id="image" class="col-md-7">
								<div class="panel">
									<img id="cropbox" src="' . $this->_path . 'views/img/' . Configuration::get($this->name . '_theme')
					. '/up-img/c/admincrop_' . Tools::getValue('idC') . '.jpg?' . md5(time()) . '" />
									<p align="center">' . $this->l('Filesize') . ' ' . (filesize(dirname(__FILE__) . '/views/img/'
							. Configuration::get($this->name . '_theme') . '/up-img/c/' . Tools::getValue('idC') . '.jpg') / 1000) . 'kb</p>
									<p>
										<a href="' . $this->path_module_conf . '&deleteImageBlog&idC=' . Tools::getValue('idC') . '"
										onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');">
											<img src="../img/admin/delete.gif" alt="' . $this->l('Delete') . '" /> ' . $this->l('Delete') . '
										</a>
									</p>
									<p>' . $this->displayFormFileNoLabel('imageCategory', 'col-lg-10', $this->l('Format:') . ' .jpg .png') . '</p>
								</div>
							</div>
							<div class="col-md-5">' . "\n";
				foreach ($config_theme_array['categories'] as $key_theme_array => $value_theme_array) {
					$width_force = '';
					if (file_exists(dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/c/'
						. $key_theme_array . '_' . Tools::getValue('idC') . '.jpg')) {
						$attrib_image = getimagesize(dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme')
							. '/up-img/c/' . $key_theme_array . '_' . Tools::getValue('idC') . '.jpg');
						if ((int)$attrib_image[0] > 200)
							$width_force = 'width="200"';
					}

					$label_pic = $key_theme_array;
					switch ($key_theme_array) {
						case 'thumb' :
							$label_pic = $this->l('thumb for category list');
							break;
						case 'full' :
							$label_pic = $this->l('full picture for description category list');
							break;
					}
					$html_libre .= '
									<div class="panel">
										<p><input type="radio" name="imageChoix" value="' . $key_theme_array . '" />&nbsp;'
						. $label_pic . ' <span style="font-size: 80%;">('
						. ($width_force ? $this->l('Real size : ') : '') . $value_theme_array['width'] . ' * '
						. $value_theme_array['height'] . ')</span></p>
										<div class="togglePreview" style="text-align:center;">
											<img style="border:1px solid #4D4D4D;padding:0px;" src="' . $this->_path
						. 'views/img/' . Configuration::get($this->name . '_theme') . '/up-img/c/' . $key_theme_array . '_'
						. Tools::getValue('idC') . '.jpg?' . md5(time()) . '" ' . $width_force . ' />
										</div>
									</div>' . "\n";
				}
				$html_libre .= '
									<div class="panel">
										<a class="btn btn-default" onclick="if (checkCoords()) {formCrop.submit();}"  >
											<i class="icon-crop"></i>&nbsp;<span id="resizeText">' . $this->l('Resize') . '</span>
										</a>
									</div>
							</div>' . "\n";
			} else {
				$html_libre .= '
							<div id="image" style="width:400px;float:left;margin-right:5px;">
								<img id="cropbox" src="' . $this->_path . 'views/img/' . Configuration::get($this->name . '_theme') . '/up-img/c/admincrop_'
					. Tools::getValue('idC') . '.jpg?' . md5(time()) . '" />
								<p align="center">' . $this->l('Filesize') . ' ' . (filesize(dirname(__FILE__) . '/views/img/'
							. Configuration::get($this->name . '_theme') . '/up-img/c/' . Tools::getValue('idC') . '.jpg') / 1000) . 'kb</p>
								<p>
									<a href="' . $this->path_module_conf . '&deleteImageBlog&idC=' . Tools::getValue('idC') . '"
									onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');">
										<img src="../img/admin/delete.gif" alt="' . $this->l('Delete') . '" /> ' . $this->l('Delete') . '
									</a>
								</p>
								<p>' . $this->displayFormFileNoLabel('imageCategory', 'col-lg-10', $this->l('Format:') . ' .jpg .png') . '</p>
							</div>
							<div>' . "\n";

				foreach ($config_theme_array['categories'] as $key_theme_array => $value_theme_array) {
					$width_force = '';
					if (file_exists(dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme') . '/up-img/c/'
						. $key_theme_array . '_' . Tools::getValue('idC') . '.jpg')) {
						$attrib_image = getimagesize(dirname(__FILE__) . '/views/img/' . Configuration::get($this->name . '_theme')
							. '/up-img/c/' . $key_theme_array . '_' . Tools::getValue('idC') . '.jpg');
						if ((int)$attrib_image[0] > 200)
							$width_force = 'width="200"';
					}

					$label_pic = $key_theme_array;
					switch ($key_theme_array) {
						case 'thumb' :
							$label_pic = $this->l('thumb for category list');
							break;
						case 'full' :
							$label_pic = $this->l('full picture for description category list');
							break;
					}
					$html_libre .= '
									<div style="float:left;width:250px;border:1px solid #ccc;background-color:#fff;padding:5px;margin-bottom:10px;">
										<p><input type="radio" name="imageChoix" value="' . $key_theme_array . '" />&nbsp;' . $label_pic
						. ' <span style="font-size: 80%;">(' . ($width_force ? $this->l('Real size : ') : '')
						. $value_theme_array['width'] . ' * ' . $value_theme_array['height'] . ')</span></p>
										<div class="togglePreview" style="text-align:center;">
											<img style="border:1px solid #4D4D4D;padding:0px;" src="' . $this->_path . 'views/img/'
						. Configuration::get($this->name . '_theme') . '/up-img/c/' . $key_theme_array . '_' . Tools::getValue('idC')
						. '.jpg?' . md5(time()) . '" ' . $width_force . ' />
										</div>
									</div>' . "\n";
				}
				$html_libre .= '
			<div style="text-align:center;float:left;width:250px;border:1px solid #ccc;background-color:#fff;padding:5px;margin-bottom:10px;">
										<input type="button" value="' . $this->l('Resize') . '" id="resizeBouton" class="button"
										onclick="if (checkCoords()) {formCrop.submit();}" />
									</div>
								</div>' . "\n";
			}
		} else
			$html_libre .= $this->displayFormFileNoLabel('imageCategory', 'col-lg-5', $this->l('Format:') . ' .jpg .png');

		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Picture'), $html_libre, 'col-lg-10');
		/* FIN IMAGE */
		/***********************************************************/

		/***********************************************************/
		/* DEBUT description */
		$html_libre = '';
		foreach ($languages as $language)
			$html_libre .= '<div id="cpara1_' . $language['id_lang'] . '" style="display: '
				. ($language['id_lang'] == $default_language ? 'block' : 'none') . ';">
					<textarea class="rte ' . (self::isPSVersion('>=', '1.6') ? 'autoload_rte rte' : '') . '"
					id="description_' . $language['id_lang'] . '" name="description_' . $language['id_lang'] . '">'
				. (isset($categories->description[$language['id_lang']]) ? $categories->description[$language['id_lang']] : '') . '</textarea>
				</div>';

		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Description'), $html_libre, 'col-lg-7',
			$this->displayFlagsFor('cpara1', $div_lang_name));
		/* FIN description */
		/***********************************************************/

		/***********************************************************/
		/* Groups */
		$active_group = array();
		if (Tools::getValue('idC'))
			$active_group = CategoriesClass::getGroupsFromCategorie((int)Tools::getValue('idC'));

		$html_libre = $this->displayFormGroups($active_group);
		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Groups permissions'), $html_libre, 'col-lg-7');
		/* /Groups */
		/***********************************************************/

		$this->html_out .= '
			<div class="margin-form">';

		if ($this->isPSVersion('>=', '1.6'))
			if (Tools::getValue('idC'))
				$this->html_out .= '<button class="btn btn-primary" name="submitUpdateCat" type="submit"><i class="icon-save"></i>
				&nbsp;' . $this->l('Update the category') . '</button>';
			else
				$this->html_out .= '<button class="btn btn-primary" name="submitAddCat" type="submit"><i class="icon-plus"></i>
				&nbsp;' . $this->l('Add the category') . '</button>';
		else
			if (Tools::getValue('idC'))
				$this->html_out .= '<input type="submit" name="submitUpdateCat" value="' . $this->l('Update the category') . '" class="button" />';
			else
				$this->html_out .= '<input type="submit" name="submitAddCat" value="' . $this->l('Add the category') . '" class="button" />';

		$this->html_out .= '</div>';

		$this->html_out .= $this->displayFormClose();

		$this->html_out .= '
			<form name="formCrop" id="formCrop" action="' . $this->path_module_conf . '" method="post" onsubmit="return checkCoords();">
				<input type="hidden" name="idC" value="' . Tools::getValue('idC') . '" />
				<input type="hidden" id="pfx" name="pfx" value="' . Tools::getValue('pfx') . '" />
				<input type="hidden" id="x" name="x" />
				<input type="hidden" id="y" name="y" />
				<input type="hidden" id="w" name="w" />
				<input type="hidden" id="h" name="h" />
				<input type="hidden" id="ratio" name="ratio" />
				<input type="hidden" name="submitCrop" value="submitCrop" />
			</form>';
	}

	public function displayFormGroups($active_group)
	{
		$html_out = '
		<div class="panel">
			<table cellspacing="0" cellpadding="0" class="table" ' . (self::isPSVersion('<', '1.6') ? 'style="width:60%"' : '') . '>
				<thead>
					<tr>
						<th style="width:20px;"><input type="checkbox" name="checkme" class="noborder"
						onclick="checkDelBoxes(this.form, \'groupBox[]\', this.checked)" /></th>
						<th style="width:20px;">' . $this->l('ID') . '</th>
						<th>' . $this->l('Group name') . '</th>
					</tr>
				</thead>
				<tbody>';

		foreach (Group::getGroups((int)$this->context->language->id) as $group) {
			$html_out .= '
					<tr>
						<td>
							<input name="groupBox[]" class="groupBox" id="groupBox_' . (int)$group['id_group'] . '" value="' . (int)$group['id_group'] . '"
							' . (in_array((int)$group['id_group'], $active_group) ? 'checked="checked"' : '') . ' type="checkbox">
						</td>
						<td>' . (int)$group['id_group'] . '</td>
						<td>
							<label for="groupBox_' . (int)$group['id_group'] . '">' . $group['name'] . '</label>
						</td>
					</tr>';
		}
		$html_out .= '
				</tbody>
			</table>
		</div>';

		return $html_out;
	}

	private function displayFormSubBlocks()
	{
		$default_language = $this->langue_default_store;
		$languages = Language::getLanguages(true);
		$div_lang_name = 'title';

		$legend_title = $this->l('Create a list');
		if (Tools::getValue('idSB')) {
			$sub_blocks = new SubBlocksClass((int)Tools::getValue('idSB'));
			$lang_liste_news = unserialize($sub_blocks->langues);
			if (!is_array($lang_liste_news))
				$lang_liste_news = array();
			$legend_title = $this->l('Update the list');
		} else
			$sub_blocks = new SubBlocksClass();

		if (Tools::isSubmit('submitUpdateSubBlock') || Tools::isSubmit('submitAddSubBlock')) {
			$sub_blocks->id_shop = (int)$this->context->shop->id;
			$sub_blocks->copyFromPost();
		}

		$this->html_out .= '
			<script type="text/javascript">
				id_language = Number(' . $default_language . ');

				function RetourLangueCheckUp(ArrayCheckedLang, idLangEnCheck, idLangDefaut) {
					if (ArrayCheckedLang.length > 0)
						return ArrayCheckedLang[0];
					else
						return idLangDefaut;
				}

				$(function() {
					';

		if (Tools::getValue('idSB'))
			if (!Tools::getValue('languesup') && count($lang_liste_news) == 1)
				$this->html_out .= 'changeTheLanguage(\'title\', \'' . $div_lang_name . '\', ' . (int)$lang_liste_news[0] . ', \'\');';
			else {
				$array_check_lang = array();
				if (Tools::getValue('languesup'))
					$array_check_lang = Tools::getValue('languesup');

				if (count($array_check_lang) == 1)
					$this->html_out .= 'changeTheLanguage(\'title\', \'' . $div_lang_name . '\', ' . (int)$array_check_lang[0] . ', \'\');';
				else
					$this->html_out .= 'changeTheLanguage(\'title\', \'' . $div_lang_name . '\', ' . (int)$default_language . ', \'\');';
			}

		$this->html_out .= '
					$("input[name=\'languesup[]\']").click(function() {
						if (this.checked)
							changeTheLanguage(\'title\', \'' . $div_lang_name . '\', this.value, \'\');
						else {
							selectedL = new Array();
							$("input[name=\'languesup[]\']:checked").each(function() {selectedL.push($(this).val());});
							changeTheLanguage(\'title\', \'' . $div_lang_name . '\',
								RetourLangueCheckUp(selectedL, this.value, ' . $default_language . '), \'\');
						}
					});

					$("#submitForm").click(function( event ) {
						test = 0;
						$("input[name=\'languesup[]\']:checked").each(function() {
							test += 1;
						});
						if(test == 0) {
							$("input[name=\'languesup[]\'][value=' . $default_language . ']").prop("checked","true");
						}
					});
				});

				function changeTheLanguage(title, divLangName, id_lang, iso) {
					$("#imgCatLang").attr("src", "../img/l/" + id_lang + ".jpg");
					return changeLanguage(title, divLangName, id_lang, iso);
				}
			</script>';

		$this->html_out .= $this->displayFormOpen('icon-edit', $legend_title, $this->path_module_conf, 'formWithSelectLang');
		if (Tools::getValue('idSB')) {
			$this->html_out .= '<input type="hidden" name="idSB" value="' . (int)Tools::getValue('idSB') . '" />';
			$this->html_out .= '<input type="hidden" name="position" value="' . (int)$sub_blocks->position . '" />';
		}

		/***********************************************************/
		$html_libre = '<span id="check_lang_prestablog">' . (count($languages) == 1 ? '' : '<input type="checkbox" name="checkmelang"
				class="noborder" onclick="checkDelBoxes(this.form, \'languesup[]\', this.checked)" /> ' . $this->l('All') . ' | ');
		foreach ($languages as $language) {
			$html_libre .= '<input type="checkbox" name="languesup[]" value="' . $language['id_lang'] . '"';
			if ((Tools::getValue('idSB') && in_array((int)$language['id_lang'], $lang_liste_news))
				|| (Tools::getValue('languesup') && in_array((int)$language['id_lang'], Tools::getValue('languesup')))
			)
				$html_libre .= ' checked=checked';
			$html_libre .= ' ' . (count($languages) == 1 ? 'style="display:none;"' : '') . ' /><img src="../img/l/'
				. (int)$language['id_lang'] . '.jpg" class="pointer" alt="' . $language['name'] . '" title="' . $language['name'] . '"
					onclick="changeTheLanguage(\'title\', \'' . $div_lang_name . '\', ' . $language['id_lang'] . ', \'' . $language['iso_code'] . '\');"  />';
		}
		$html_libre .= '</span>';
		/*$html_libre .= '<p class="help-block">'.$this->l('Select a language to filter your list. No selection include all languages.').'</p>';*/

		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Language'), $html_libre, 'col-lg-7');
		/***********************************************************/
		$html_libre = '';
		foreach ($languages as $language)
			$html_libre .= '<div id="title_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $default_language ?
					'block' : 'none') . ';">
					<input ' . (self::isPSVersion('<', '1.6') ? 'class="fixInput15"' : '') . ' type="text" name="title_' . $language['id_lang'] . '"
					id="title_' . $language['id_lang'] . '" value="' . (isset($sub_blocks->title[$language['id_lang']]) ?
					$sub_blocks->title[$language['id_lang']] : '') . '" />
				</div>';

		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Title'), $html_libre, 'col-lg-7',
			$this->displayFlagsFor('title', $div_lang_name));
		/***********************************************************/
		$this->html_out .= $this->displayFormSelect('col-lg-2',
			$this->l('List'),
			'select_type',
			$sub_blocks->select_type,
			$sub_blocks->getListeSelectType(),
			null,
			'col-lg-5');
		/***********************************************************/
		$this->html_out .= $this->displayFormSelect('col-lg-2',
			$this->l('Hook'),
			'hook_name',
			(Tools::getValue('preselecthook') ? Tools::getValue('preselecthook') : $sub_blocks->hook_name),
			$sub_blocks->getListeHook(),
			null,
			'col-lg-5');
		/***********************************************************/
		$this->html_out .= $this->displayFormInput('col-lg-2', $this->l('Template'), 'template',
			$sub_blocks->template, 60, 'col-lg-6', null,
			sprintf($this->l('Leave blank to use the default template %1$s'),
				'<strong>' . Configuration::get($this->name . '_theme') . '_page-subblock.tpl</strong>'));
		/***********************************************************/
		$this->html_out .= $this->displayFormInput('col-lg-2', $this->l('Number of news to display'), 'nb_list',
			$sub_blocks->nb_list, 10, 'col-lg-4');
		/***********************************************************/
		$this->html_out .= $this->displayFormInput('col-lg-2', $this->l('Title length'), 'title_length',
			$sub_blocks->title_length, 10, 'col-lg-4', $this->l('caracters'));
		$this->html_out .= $this->displayFormInput('col-lg-2', $this->l('Description length'), 'intro_length',
			$sub_blocks->intro_length, 10, 'col-lg-4', $this->l('caracters'));
		/***********************************************************/
		/* DEBUT PERIODE */
		$html_libre = '<div class="panel">';
		$html_libre .= $this->displayFormDateWithActivation('col-lg-1', $this->l('From'), 'date_start',
			$sub_blocks->date_start, true, 'use_date_start', $sub_blocks->use_date_start);
		$html_libre .= $this->displayFormDateWithActivation('col-lg-1', $this->l('To'), 'date_stop',
			$sub_blocks->date_stop, true, 'use_date_stop', $sub_blocks->use_date_stop);
		$html_libre .= '</div>';

		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Use a period'), $html_libre, 'col-lg-6');
		/* FIN PERIODE */
		/***********************************************************/

		/***********************************************************/
		$this->html_out .= $this->displayFormEnableItem('col-lg-2', $this->l('Random list'), 'random',
			$sub_blocks->random, $this->l('This option will randomize your list.'));
		/***********************************************************/

		/***********************************************************/
		/* DEBUT CATEGORIES */
		$html_libre = '';
		$html_libre .= '
			<div class="panel">
				<table cellspacing="0" cellpadding="0" class="table" ' . (self::isPSVersion('<', '1.6') ? 'style="width:60%"' : '') . '>
					<thead>
						<tr>
							<th style="width:20px;"><input type="checkbox" name="checkme" class="noborder"
							onclick="checkDelBoxes(this.form, \'categories[]\', this.checked)" /></th>
							<th style="width:20px;">' . $this->l('ID') . '</th>
							<th style="width:60px;">' . $this->l('Image') . '</th>
							<th>' . $this->l('Name') . '&nbsp;<img id="imgCatLang" src="../img/l/' . $default_language . '.jpg"
							style="vertical-align:middle;" /></th>
						</tr>
					</thead>';

		$liste_cat = CategoriesClass::getListe((int)$this->context->language->id, 0);
		$liste_cat_no_arbre = CategoriesClass::getListeNoArbo();
		$liste_cat_branches_actives = array();

		foreach (SubBlocksClass::getCategories($sub_blocks->id, 0) as $value)
			$liste_cat_branches_actives = array_unique(array_merge($liste_cat_branches_actives, preg_split('/\./',
				CategoriesClass::getBranche((int)$value))));

		$html_libre .= $this->displayListeArborescenceCategoriesSubBlocks($liste_cat, 0, $liste_cat_branches_actives);

		$html_libre .= '
				</table>
			</div>
			<script language="javascript" type="text/javascript">
				$(document).ready(function() {
					$(".catlang").hide();
					$(".catlang[rel="+id_language+"]").show();

					$("div.language_flags img, #check_lang_prestablog img").click(function() {
						$(".catlang").hide();
						$(".catlang[rel="+id_language+"]").show();
						$("#imgCatLang").attr("src", "../img/l/" + id_language + ".jpg");
					});
					';

		foreach ($liste_cat_branches_actives as $value)
			$html_libre .= '$("tr#prestablog_categorie_' . $value . '").show();';

		foreach ($liste_cat_no_arbre as $value)
			if (in_array((int)$value['parent'], $liste_cat_branches_actives))
				$html_libre .= '$("tr#prestablog_categorie_' . $value['id_prestablog_categorie'] . '").show();';

		$html_libre .= '
					$("img.expand-cat").click(function() {
						BranchClick=$(this).attr("rel");
						BranchClickSplit = BranchClick.split(\'.\');
						fixBranchClickSplit = "0,"+BranchClickSplit.toString();

						switch ($(this).attr("src")) {
							case "/modules/prestablog/views/img/expand.gif":
								$("tr.prestablog_branch").each(function() {
									BranchParent = $(this).attr("rel");
									BranchParentSplit = BranchParent.split(\'.\');
									fixBranchParentSplit = "0,"+BranchParentSplit.toString();

									if ($.isSubstring(fixBranchParentSplit, fixBranchClickSplit)
											&& BranchClick != BranchParent
											&& BranchClickSplit.length+1 == BranchParentSplit.length
										) {
											$(this).show();
									}
								});
								$(this).attr("src", "/modules/prestablog/views/img/collapse.gif");
								break;

							case "/modules/prestablog/views/img/collapse.gif":
								$("tr.prestablog_branch").each(function() {
									BranchParent = $(this).attr("rel");
									BranchParentSplit = BranchParent.split(\'.\');
									fixBranchParentSplit = "0,"+BranchParentSplit.toString();

									if ($.isSubstring(fixBranchParentSplit, fixBranchClickSplit)
											&&	BranchClick != BranchParent
										) {
											$(this).hide();
											$(this).find("img.expand-cat").each(function() {
												$(this).attr("src", "/modules/prestablog/views/img/expand.gif");
											});
									}
								});
								$(this).attr("src", "/modules/prestablog/views/img/expand.gif");
								break;
						}
					});
				});
				jQuery.isSubstring = function(haystack, needle) {
					 return haystack.indexOf(needle) !== -1;
				};
			</script>';
		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Categories'), $html_libre, 'col-lg-5');
		/* FIN CATEGORIES */
		/***********************************************************/

		/***********************************************************/
		$this->html_out .= $this->displayFormEnableItem('col-lg-2', $this->l('Blog link'), 'blog_link',
			$sub_blocks->blog_link, $this->l('Show link to the blog'));
		/***********************************************************/

		/***********************************************************/
		$this->html_out .= $this->displayFormEnableItem('col-lg-2', $this->l('Activate'), 'actif', $sub_blocks->actif);
		/***********************************************************/

		$this->html_out .= '
			<div class="margin-form">';

		if ($this->isPSVersion('>=', '1.6'))
			if (Tools::getValue('idSB'))
				$this->html_out .= '<button class="btn btn-primary" id="submitForm" name="submitUpdateSubBlock"><i class="icon-save"></i>
				&nbsp;' . $this->l('Update') . '</button>';
			else
				$this->html_out .= '<button class="btn btn-primary" id="submitForm" name="submitAddSubBlock"><i class="icon-plus"></i>
				&nbsp;' . $this->l('Add') . '</button>';
		else
			if (Tools::getValue('idSB'))
				$this->html_out .= '<input type="submit" id="submitForm" name="submitUpdateSubBlock" value="' . $this->l('Update') . '" class="button" />';
			else
				$this->html_out .= '<input type="submit" id="submitForm" name="submitAddSubBlock" value="' . $this->l('Add') . '" class="button" />';

		$this->html_out .= '</div>';
		$this->html_out .= $this->displayFormClose();
	}

	private function displayFormAntiSpam()
	{
		$default_language = $this->langue_default_store;
		$languages = Language::getLanguages(true);
		$div_lang_name = 'question¤reply';

		$legend_title = $this->l('Add an AntiSpam question');
		if (Tools::getValue('idAS')) {
			$antispam = new AntiSpamClass((int)Tools::getValue('idAS'));
			$legend_title = $this->l('Update the AntiSpam question');
		} else
			$antispam = new AntiSpamClass();

		if (Tools::isSubmit('submitUpdateAntiSpam') || Tools::isSubmit('submitAddAntiSpam')) {
			$antispam->id_shop = (int)$this->context->shop->id;
			$antispam->copyFromPost();
		}

		$this->html_out .= '<script type="text/javascript">id_language = Number(' . $default_language . ');</script>';

		$this->html_out .= $this->displayFormOpen('icon-edit', $legend_title, $this->path_module_conf);
		if (Tools::getValue('idAS'))
			$this->html_out .= '<input type="hidden" name="idAS" value="' . Tools::getValue('idAS') . '" />';
		/***********************************************************/
		$html_libre = '';
		foreach ($languages as $language)
			$html_libre .= '<div id="question_' . $language['id_lang'] . '" style="display: '
				. ($language['id_lang'] == $default_language ? 'block' : 'none') . ';">
					<input ' . (self::isPSVersion('<', '1.6') ? 'class="fixInput15"' : '') . ' type="text" name="question_'
				. $language['id_lang'] . '" id="question_' . $language['id_lang'] . '" value="' . (isset($antispam->question[$language['id_lang']]) ?
					$antispam->question[$language['id_lang']] : '') . '" />
				</div>';

		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Question'), $html_libre, 'col-lg-7',
			$this->displayFlagsFor('question', $div_lang_name));
		/***********************************************************/
		$html_libre = '';
		foreach ($languages as $language)
			$html_libre .= '<div id="reply_' . $language['id_lang'] . '" style="display: '
				. ($language['id_lang'] == $default_language ? 'block' : 'none') . ';">
					<input ' . (self::isPSVersion('<', '1.6') ? 'class="fixInput15"' : '') . ' type="text" name="reply_' . $language['id_lang'] . '"
					id="question_' . $language['id_lang'] . '" value="' . (isset($antispam->reply[$language['id_lang']]) ?
					$antispam->reply[$language['id_lang']] : '') . '" />
				</div>';

		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Expected reply'), $html_libre, 'col-lg-7',
			$this->displayFlagsFor('reply', $div_lang_name));
		/***********************************************************/
		$this->html_out .= $this->displayFormEnableItem('col-lg-2', $this->l('Activate'), 'actif', $antispam->actif);
		/***********************************************************/

		$this->html_out .= '
			<div class="margin-form">';

		if ($this->isPSVersion('>=', '1.6'))
			if (Tools::getValue('idAS'))
				$this->html_out .= '<button class="btn btn-primary" name="submitUpdateAntiSpam" type="submit"><i class="icon-save"></i>
				&nbsp;' . $this->l('Update the AntiSpam question') . '</button>';
			else
				$this->html_out .= '<button class="btn btn-primary" name="submitAddAntiSpam" type="submit"><i class="icon-plus"></i>
				&nbsp;' . $this->l('Add the AntiSpam question') . '</button>';
		else
			if (Tools::getValue('idAS'))
				$this->html_out .= '<input type="submit" name="submitUpdateAntiSpam" value="'
					. $this->l('Update the AntiSpam question') . '" class="button" />';
			else
				$this->html_out .= '<input type="submit" name="submitAddAntiSpam" value="'
					. $this->l('Add the AntiSpam question') . '" class="button" />';

		$this->html_out .= '</div>';
		$this->html_out .= $this->displayFormClose();
	}

	private function displayFormComments()
	{
		$legend_title = $this->l('Add a comment');
		if (Tools::getValue('idC')) {
			$legend_title = $this->l('Update the comment');
			$comment = new CommentNewsClass((int)Tools::getValue('idC'));
		} else {
			$comment = new CommentNewsClass();
			$comment->copyFromPost();
		}

		$this->html_out .= $this->displayFormOpen('icon-edit', $legend_title, $this->path_module_conf);

		if (Tools::getValue('idC'))
			$this->html_out .= '<input type="hidden" name="idC" value="' . Tools::getValue('idC') . '" />';

		$title_news = NewsClass::getTitleNews((int)$comment->news, (int)$this->context->language->id);

		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Parent news'), '<a href="'
			. $this->path_module_conf . '&editNews&idN=' . $comment->news . '" onclick="return confirm(\''
			. $this->l('You will leave this page. Are you sure ?') . '\');" >' . $title_news . '</a>', 'col-lg-5');
		$this->html_out .= $this->displayFormInput('col-lg-2', $this->l('Name'), 'name', $comment->name, 50, 'col-lg-4');
		$this->html_out .= $this->displayFormInput('col-lg-2', $this->l('Url'), 'url', $comment->url, 80, 'col-lg-6',
			null, null, '<i class="icon-external-link"></i>');

		$html_libre = '<textarea ' . (self::isPSVersion('<', '1.6') ? 'style="width:500px;height:100px;"' : '') . '
								id="comment" name="comment">' . $comment->comment . '</textarea>';
		$this->html_out .= $this->displayFormLibre('col-lg-2', $this->l('Comment'), $html_libre, 'col-lg-7');

		$this->html_out .= $this->displayFormDate('col-lg-2', $this->l('Date'), 'date', $comment->date, true);
		$this->html_out .= $this->displayFormSelect('col-lg-2',
			$this->l('Status'),
			'actif',
			$comment->actif,
			array(
				'-1' => $this->l('Pending'),
				'1' => $this->l('Enabled'),
				'0' => $this->l('Disabled'),
			),
			null,
			'col-lg-3',
			null, null, '<i class="icon-eye"></i>');

		$this->html_out .= '<div class="margin-form">';
		if ($this->isPSVersion('>=', '1.6')) {
			if (Tools::getValue('idC')) {
				$this->html_out .= '<div class="col-lg-3">';
				$this->html_out .= '<button class="btn btn-primary" name="submitUpdateComment" type="submit"><i class="icon-save"></i>
					&nbsp;' . $this->l('Update the comment') . '</button>';
				$this->html_out .= '</div>';
				$this->html_out .= '<div class="col-lg-2">';
				$this->html_out .= '<a class="btn btn-default" href="' . $this->path_module_conf . '&deleteComment&idC=' . $comment->id . '"
					onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');" >
								<i class="icon-trash-o"></i>&nbsp;' . $this->l('Delete the comment') . '
							</a>';
				$this->html_out .= '</div>';
			} else {
				$this->html_out .= '<div class="col-lg-2">';
				$this->html_out .= '<button class="btn btn-primary" name="submitAddComment" type="submit"><i class="icon-plus"></i>
					&nbsp;' . $this->l('Add the comment') . '</button>';
				$this->html_out .= '</div>';
			}
		} else {
			if (Tools::getValue('idC')) {
				$this->html_out .= '	<input type="submit" name="submitUpdateComment" value="' . $this->l('Update the comment') . '" class="button" />';
				$this->html_out .= '	<a href="' . $this->path_module_conf . '&deleteComment&idC=' . $comment->id . '" title="'
					. $this->l('Delete the comment') . '" class="button" style="margin-left:10px;padding:4px;"
					onclick="return confirm(\'' . $this->l('Are you sure?', __CLASS__, true, false) . '\');" />' . $this->l('Delete the comment') . '</a>';
			} else
				$this->html_out .= '<input type="submit" name="submitAddComment" value="' . $this->l('Add the comment') . '" class="button" />';
		}
		$this->html_out .= '</div>';

		$this->html_out .= $this->displayFormClose();
	}

	private function deleteAllImagesThemes($id)
	{
		foreach (self::scanListeThemes() as $value_theme) {
			$config_theme = $this->getConfigXmlTheme($value_theme);
			$config_theme_array = PrestaBlog::objectToArray($config_theme);
			foreach (array_keys($config_theme_array['images']) as $key_theme_array)
				self::unlinkFile(_PS_MODULE_DIR_ . $this->name . '/views/img/' . $value_theme . '/up-img/' . $key_theme_array . '_' . $id . '.jpg');

			self::unlinkFile(_PS_MODULE_DIR_ . $this->name . '/views/img/' . $value_theme . '/up-img/' . $id . '.jpg');
			self::unlinkFile(_PS_MODULE_DIR_ . $this->name . '/views/img/' . $value_theme . '/up-img/admincrop_' . $id . '.jpg');
			self::unlinkFile(_PS_MODULE_DIR_ . $this->name . '/views/img/' . $value_theme . '/up-img/adminth_' . $id . '.jpg');
		}

		return true;
	}

	private function deleteAllImagesThemesCat($id)
	{
		foreach (self::scanListeThemes() as $value_theme) {
			$config_theme = $this->getConfigXmlTheme($value_theme);
			$config_theme_array = PrestaBlog::objectToArray($config_theme);
			foreach (array_keys($config_theme_array['categories']) as $key_theme_array)
				self::unlinkFile(_PS_MODULE_DIR_ . $this->name . '/views/img/' . $value_theme . '/up-img/c/' . $key_theme_array . '_' . $id . '.jpg');

			self::unlinkFile(_PS_MODULE_DIR_ . $this->name . '/views/img/' . $value_theme . '/up-img/c/' . $id . '.jpg');
			self::unlinkFile(_PS_MODULE_DIR_ . $this->name . '/views/img/' . $value_theme . '/up-img/c/admincrop_' . $id . '.jpg');
			self::unlinkFile(_PS_MODULE_DIR_ . $this->name . '/views/img/' . $value_theme . '/up-img/c/adminth_' . $id . '.jpg');
		}

		return true;
	}

	private function uploadImage($file_image, $id, $w, $h, $folder = null)
	{
		if (isset($file_image) && isset($file_image['tmp_name']) && !empty($file_image['tmp_name'])) {
			$tmpname = false;
			Configuration::set('PS_IMAGE_GENERATION_METHOD', 1);
			if (ImageManager::validateUpload($file_image, $this->max_image_size))
				return false;
			elseif (!$tmpname = tempnam(_PS_TMP_IMG_DIR_, 'PS'))
				return false;
			elseif (!move_uploaded_file($file_image['tmp_name'], $tmpname))
				return false;
			else
				foreach (self::scanListeThemes() as $value_theme)
					if (!$this->imageResize($tmpname, dirname(__FILE__) . '/views/img/' . $value_theme . '/up-img/'
						. ($folder ? $folder . '/' : '') . $id . '.jpg', $w, $h)
					)
						return false;

			if (isset($tmpname))
				unlink($tmpname);
		}

		return true;
	}

	private function imageResize($fichier_avant, $fichier_apres, $dest_width, $dest_height)
	{
		list($image_width, $image_height, $type) = getimagesize($fichier_avant);
		$source_image = ImageManager::create($type, $fichier_avant);

		if ($image_width > $dest_width || $image_height > $dest_height) {
			$proportion = $dest_width / $image_width;
			$dest_height = $image_height * $proportion;
			$dest_width = $dest_width;
		} else {
			$dest_height = $image_height;
			$dest_width = $image_width;
		}
		$dest_image = imagecreatetruecolor($dest_width, $dest_height);
		imagecopyresampled($dest_image, $source_image, 0, 0, 0, 0, $dest_width + 1, $dest_height + 1, $image_width, $image_height);
		return (ImageManager::write('jpg', $dest_image, $fichier_apres));
	}

	public function scanDirectory($directory)
	{
		$output = array();

		if (is_dir($directory)) {
			$my_directory = opendir($directory);

			while ($entry = self::readDirectory($my_directory)) {
				if ($entry != '.' && $entry != '..')
					if (is_dir($directory . '/' . $entry))
						$output[] = $entry;
			}
			closedir($my_directory);
		}

		return $output;
	}

	public function scanFilesDirectory($directory, $expections = null)
	{
		$output = array();
		if (!is_dir($directory))
			return array();

		$my_directory = opendir($directory);

		while ($entry = self::readDirectory($my_directory)) {
			if ($entry != '.' && $entry != '..')
				if (count($expections) > 0)
					if (is_file($directory . '/' . $entry) && !in_array($entry, $expections))
						$output[] = $entry;
					elseif (is_file($directory . '/' . $entry))
						$output[] = $entry;
		}
		closedir($my_directory);
		return $output;
	}

	public static function copyRecursive($source, $dest)
	{
		// Check for symlinks
		if (is_link($source))
			return symlink(readlink($source), $dest);

		// Simple copy for a file
		if (is_file($source))
			return self::copy($source, $dest);

		// Make destination directory
		if (!is_dir($dest))
			mkdir($dest);

		// Loop through the folder
		$dir = dir($source);
		while (false !== $entry = $dir->read()) {
			// Skip pointers
			if ($entry == '.' || $entry == '..')
				continue;

			// Deep copy directories
			self::copyRecursive($source . '/' . $entry, $dest . '/' . $entry);
		}

		// Clean up
		$dir->close();
		return true;
	}

	public static function getConfigXmlTheme($theme)
	{
		$config_file = _PS_MODULE_DIR_ . 'prestablog/views/config/' . $theme . '.xml';
		$xml_exist = file_exists($config_file);

		if ($xml_exist)
			return simplexml_load_file($config_file);
		else {
			self::generateConfigXmlTheme($theme);
			return self::getConfigXmlTheme($theme);
		}
	}

	private function retourneTexteBalise($text, $debut, $fin)
	{
		$debut = strpos($text, $debut) + Tools::strlen($debut);
		$fin = strpos($text, $fin);
		return Tools::substr($text, $debut, $fin - $debut);
	}

	protected static function generateConfigXmlTheme($theme)
	{
		$xml = '<?xml version="1.0" encoding="UTF-8" ?>
<theme>
	<!-- root js path is your theme prestablog folder -->
	<!-- modules/prestablog/views/js/ -->
	<js>slide.js</js>
	<images>
		<thumb> <!--Image prevue pour les miniatures dans les listes -->
			<width>' . Configuration::get('prestablog_thumb_picture_width') . '</width>
			<height>' . Configuration::get('prestablog_thumb_picture_height') . '</height>
		</thumb>
		<slide> <!--Image prevue pour les slides -->
			<width>' . Configuration::get('prestablog_slide_picture_width') . '</width>
			<height>' . Configuration::get('prestablog_slide_picture_height') . '</height>
		</slide>
	</images>
	<categories>
		<thumb> <!--Image prevue pour les miniatures des categories -->
			<width>' . Configuration::get('prestablog_thumb_cat_width') . '</width>
			<height>' . Configuration::get('prestablog_thumb_cat_height') . '</height>
		</thumb>
		<full> <!--Image prevue pour la description de la categorie en liste 1ere page -->
			<width>' . Configuration::get('prestablog_full_cat_width') . '</width>
			<height>' . Configuration::get('prestablog_full_cat_height') . '</height>
		</full>
	</categories>
</theme>';
		if (is_writable(_PS_MODULE_DIR_ . 'prestablog/views/config/'))
			file_put_contents(_PS_MODULE_DIR_ . 'prestablog/views/config/' . $theme . '.xml', utf8_encode($xml));
	}

	private function cleanMetaKeywords($keywords)
	{
		if (!empty($keywords) && $keywords != '') {
			$out = array();
			$words = explode(',', $keywords);
			foreach ($words as $word_item) {
				$word_item = trim($word_item);
				if (!empty($word_item) && $word_item != '')
					$out[] = $word_item;
			}
			return ((count($out) > 0) ? implode(',', $out) : '');
		} else
			return '';
	}

	public static function prestablogContent($params)
	{
		return $params['return'];
	}

	public static function prestablogUrl($params)
	{
		$param = null;
		$ok_rewrite = '';
		$ok_rewrite_id = '';
		$ok_rewrite_do = '';
		$ok_rewrite_cat = '';
		$ok_rewrite_categorie = '';
		$ok_rewrite_page = '';
		$ok_rewrite_titre = '';
		$ok_rewrite_seo = '';
		$ok_rewrite_year = '';
		$ok_rewrite_month = '';

		$ko_rewrite = '';
		$ko_rewrite_id = '';
		$ko_rewrite_do = '';
		$ko_rewrite_cat = '';
		$ko_rewrite_page = '';
		$ko_rewrite_year = '';
		$ko_rewrite_month = '';

		if (isset($params['do']) && $params['do'] != '') {
			$ko_rewrite_do = 'do=' . $params['do'];
			$ok_rewrite_do = $params['do'];
			$param += 1;
		}
		if (isset($params['id']) && $params['id'] != '') {
			$ko_rewrite_id = '&id=' . $params['id'];
			$ok_rewrite_id = '-n' . $params['id'];
			$param += 1;
		}
		if (isset($params['c']) && $params['c'] != '') {
			$ko_rewrite_cat = '&c=' . $params['c'];
			$ok_rewrite_cat = '-c' . $params['c'];
			$param += 1;
		}
		if (isset($params['start']) && isset($params['p']) && $params['start'] != '' && $params['p'] != '') {
			$ko_rewrite_page = '&start=' . $params['start'] . '&p=' . $params['p'];
			$ok_rewrite_page = $params['start'] . 'p' . $params['p'];
			$param += 1;
		}
		if (isset($params['titre']) && $params['titre'] != '') {
			$ok_rewrite_titre = PrestaBlog::prestablogFilter(Tools::link_rewrite($params['titre']));
			$param += 1;
		}
		if (isset($params['categorie']) && $params['categorie'] != '') {
			$ok_rewrite_categorie = PrestaBlog::prestablogFilter(Tools::link_rewrite($params['categorie']))
				. (isset($params['start']) && isset($params['p']) && $params['start'] != '' && $params['p'] != '' ? '-' : '');
			$param += 1;
		}
		if (isset($params['seo']) && $params['seo'] != '') {
			$ok_rewrite_titre = PrestaBlog::prestablogFilter(Tools::link_rewrite($params['seo']));
			$param += 1;
		}
		if (isset($params['y']) && $params['y'] != '') {
			$ko_rewrite_year = '&y=' . $params['y'];
			$ok_rewrite_year = 'y' . $params['y'];
			$param += 1;
		}
		if (isset($params['m']) && $params['m'] != '') {
			$ko_rewrite_month = '&m=' . $params['m'];
			$ok_rewrite_month = '-m' . $params['m'];
			$param += 1;
		}
		if (isset($params['seo']) && $params['seo'] != '') {
			$ok_rewrite_seo = $params['seo'];
			$ok_rewrite_titre = '';
			$param += 1;
		}

		if (count($param) > 0 && !isset($params['rss'])) {
			$ok_rewrite = 'articles/' . $ok_rewrite_do . $ok_rewrite_categorie . $ok_rewrite_page . $ok_rewrite_year . $ok_rewrite_month
				. $ok_rewrite_titre . $ok_rewrite_seo . $ok_rewrite_cat . $ok_rewrite_id;
			$ko_rewrite = '?fc=module&module=prestablog&controller=' . self::getUrlPath() . '&' . ltrim($ko_rewrite_do . $ko_rewrite_id . $ko_rewrite_cat
					. $ko_rewrite_page . $ko_rewrite_year . $ko_rewrite_month, '&');
		} elseif (isset($params['rss'])) {
			if ($params['rss'] == 'all') {
				$ok_rewrite = 'rss';
				$ko_rewrite = '?fc=module&module=prestablog&controller=rss';
			} else {
				$ok_rewrite = 'rss/' . $params['rss'];
				$ko_rewrite = '?fc=module&module=prestablog&controller=rss&rss=' . $params['rss'];
			}
		} else {
			$ok_rewrite = self::getUrlPath();
			$ko_rewrite = '?fc=module&module=prestablog&controller=' . self::getUrlPath() . '';
		}

		if (!isset($params['id_lang']))
			(int)$params['id_lang'] = null;

		if ((int)Configuration::get('PS_REWRITING_SETTINGS') && (int)Configuration::get('prestablog_rewrite_actif'))
			return self::getBaseUrlFront((int)$params['id_lang']) . $ok_rewrite;
		else
			return self::getBaseUrlFront((int)$params['id_lang']) . $ko_rewrite;
	}

	public static function getBaseUrlFront($id_lang = null)
	{
		$base = ((Configuration::get('PS_SSL_ENABLED')) ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true));

		$base .= __PS_BASE_URI__ . self::getLangLink($id_lang);

		return $base;
	}

	public static function getLangLink($id_lang = null)
	{
		$context = Context::getContext();

		if (!Configuration::get('PS_REWRITING_SETTINGS'))
			return '';
		if (Language::countActiveLanguages() <= 1)
			return '';
		if (!$id_lang)
			$id_lang = $context->language->id;

		return Language::getIsoById((int)$id_lang) . '/';
	}

	public static function prestablogFilter($retourne)
	{
		$search = array(
			'/--+/'
		);
		$replace = array(
			'-'
		);

		$retourne = Tools::strtolower(preg_replace($search, $replace, $retourne));

		$url_replace = array(
			'/А/' => 'A', '/а/' => 'a',
			'/Б/' => 'B', '/б/' => 'b',
			'/В/' => 'V', '/в/' => 'v',
			'/Г/' => 'G', '/г/' => 'g',
			'/Д/' => 'D', '/д/' => 'd',
			'/Е/' => 'E', '/е/' => 'e',
			'/Ж/' => 'J', '/ж/' => 'j',
			'/З/' => 'Z', '/з/' => 'z',
			'/И/' => 'I', '/и/' => 'i',
			'/Й/' => 'Y', '/й/' => 'y',
			'/К/' => 'K', '/к/' => 'k',
			'/Л/' => 'L', '/л/' => 'l',
			'/М/' => 'M', '/м/' => 'm',
			'/Н/' => 'N', '/н/' => 'n',
			'/О/' => 'O', '/о/' => 'o',
			'/П/' => 'P', '/п/' => 'p',
			'/Р/' => 'R', '/р/' => 'r',
			'/С/' => 'S', '/с/' => 's',
			'/Т/' => 'T', '/т/' => 't',
			'/У/' => 'U', '/у/' => 'u',
			'/Ф/' => 'F', '/ф/' => 'f',
			'/Х/' => 'H', '/х/' => 'h',
			'/Ц/' => 'C', '/ц/' => 'c',
			'/Ч/' => 'CH', '/ч/' => 'ch',
			'/Ш/' => 'SH', '/ш/' => 'sh',
			'/Щ/' => 'SHT', '/щ/' => 'sht',
			'/Ъ/' => 'A', '/ъ/' => 'a',
			'/Ь/' => 'X', '/ь/' => 'x',
			'/Ю/' => 'YU', '/ю/' => 'yu',
			'/Я/' => 'YA', '/я/' => 'ya',
		);
		$cyrillic_find = array_keys($url_replace);
		$cyrillic_replace = array_values($url_replace);

		$retourne = Tools::strtolower(preg_replace($cyrillic_find, $cyrillic_replace, $retourne));

		return $retourne;
	}

	public static function getPrestaBlogMetaTagsNewsOnly($id_lang, $id = null)
	{
		if ($id) {
			$row = array();

			$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT `title`, `meta_title`, `meta_description`, `meta_keywords`
			FROM `' . _DB_PREFIX_ . 'prestablog_news_lang`
			WHERE id_lang = ' . (int)$id_lang . ' AND id_prestablog_news = ' . (int)$id);
		}
		if ($row)
			return self::completeMetaTags($row, $row['title']);
	}

	public static function getPrestaBlogMetaTagsNewsCat($id_lang, $id = null)
	{
		if ($id) {
			$row = array();

			$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT `title`, `meta_title`, `meta_description`, `meta_keywords`
			FROM `' . _DB_PREFIX_ . 'prestablog_categorie_lang`
			WHERE id_lang = ' . (int)$id_lang . ' AND id_prestablog_categorie = ' . (int)$id);
		}
		if ($row)
			return self::completeMetaTags($row, $row['title']);
	}

	public static function getPrestaBlogMetaTagsPage($id_lang)
	{
		return self::completeMetaTags(null, Configuration::get('prestablog_titlepageblog_' . $id_lang),
			Configuration::get('prestablog_descpageblog_' . $id_lang));
	}

	public static function getPrestaBlogMetaTagsNewsDate()
	{
		return self::completeMetaTags(null, null, null);
	}

	public static function completeMetaTags($meta_tags, $meta_title = null, $meta_description = null)
	{
		$context = Context::getContext();

		$prestablog = new PrestaBlog();

		if (empty($meta_tags['meta_title']))
			$meta_tags['meta_title'] = ($meta_title ? $meta_title . ' - ' : '') . Configuration::get('PS_SHOP_NAME');
		if (empty($meta_tags['meta_description']))
			$meta_tags['meta_description'] = ($meta_description ? $meta_description : '');
		if (empty($meta_tags['meta_keywords']))
			$meta_tags['meta_keywords'] = Configuration::get('PS_META_KEYWORDS', (int)$context->language->id) ?
				Configuration::get('PS_META_KEYWORDS', (int)$context->language->id) : '';

		$meta_tags['meta_title'] .= (Tools::getValue('p') ? ' - ' . $prestablog->l('page') . ' ' . Tools::getValue('p') : '');
		$meta_tags['meta_title'] .= (Tools::getValue('y') ? ' - ' . Tools::getValue('y') : '');
		$meta_tags['meta_title'] .= (Tools::getValue('m') ? ' - ' . $prestablog->mois_langue[Tools::getValue('m')] : '');

		return $meta_tags;
	}

	public static function getPagination($count_liste, $entites_en_moins = 0, $end = 10, $start = 0, $p = 1)
	{
		$pagination = array();

		$pagination['NombreTotalEntites'] = ($count_liste - $entites_en_moins);

		$pagination['NombreTotalPages'] = ceil((int)$pagination['NombreTotalEntites'] / (int)$end);

		if ($pagination['NombreTotalEntites'] > 0) {
			if ($p) {
				$pagination['PageCourante'] = (int)$p;
				$pagination['PagePrecedente'] = (int)$p - 1;
				$pagination['PageSuivante'] = (int)$p + 1;
			} else {
				$pagination['PageCourante'] = 1;
				$pagination['PagePrecedente'] = 0;
				$pagination['PageSuivante'] = 2;
			}

			if ($start) {
				$pagination['StartCourant'] = (int)$start;
				$pagination['StartPrecedent'] = (int)$start - (int)$end;
				$pagination['StartSuivant'] = (int)$start + (int)$end;
			} else {
				$pagination['StartCourant'] = 0;
				$pagination['StartPrecedent'] = 0;
				$pagination['StartSuivant'] = (int)$end;
			}
			for ($icount = 1; $icount <= (int)$pagination['NombreTotalPages']; $icount++)
				$pagination['Pages'][$icount] = ($icount - 1) * (int)$end;

			if (count($pagination['Pages']) <= 5) {
				$pagination['PremieresPages'] = array_slice($pagination['Pages'], 0, 5, true);
				unset($pagination['Pages']);
			} else {
				$pagination['PremieresPages'] = array_slice($pagination['Pages'], 0, 1, true);
				if ($pagination['PageCourante'] == 1)
					$pagination['Pages'] = array_slice($pagination['Pages'], $pagination['PageCourante'] - 1, 6, true);
				else
					if ($pagination['PageCourante'] + 4 >= $pagination['NombreTotalPages'])
						$pagination['Pages'] = array_slice($pagination['Pages'], ($pagination['NombreTotalPages'] - 5), 5, true);
					else
						$pagination['Pages'] = array_slice($pagination['Pages'], $pagination['PageCourante'] - 1, 5, true);
			}
		}

		return $pagination;
	}

	public function autocropImage($image_source, $rep_source, $rep_dest, $tl, $th, $prefixe, $change_nom)
	{
		$tl = (int)$tl;
		$th = (int)$th;
		$tr = $tl / $th;

		$full_path = $rep_source . $image_source;
		$extensionsource = preg_replace('/.*\.([^.]+)$/', '\\1', $image_source);

		switch ($extensionsource) {
			case 'png':
				$imagesource = imagecreatefrompng($full_path);
				break;

			case 'jpg':
				$imagesource = imagecreatefromjpeg($full_path);
				break;

			case 'jpeg':
				$imagesource = imagecreatefromjpeg($full_path);
				break;

			default:
				/* $this->message_err('ATTENTION ! La librairie GD ne supporte pas cette extension => '.$ext, '', '', ''); */
				break;
		}

		$sl = (int)imagesx($imagesource);
		$sh = (int)imagesy($imagesource);

		$sr = $sl / $sh;

		if ($sr > $tr) {
			$nh = $th;
			$nl = ($nh * $sl) / $sh;
		} elseif ($sr < $tr) {
			$nl = $tl;
			$nh = ($nl * $sh) / $sl;
		} elseif ($sr == $tr) {
			$nh = $th;
			$nl = $tl;
		}

		if ($tr > 1) {
			$nx = 0;
			$ny = ($nh - $th) / 2;
		} elseif ($tr < 1) {
			$ny = 0;
			$nx = ($nl - $tl) / 2;
		} elseif ($tr == 1) {
			if ($sr > 1) {
				$ny = 0;
				$nx = ($nl - $tl) / 2;
			} elseif ($sr < 1) {
				$nx = 0;
				$ny = ($nh - $th) / 2;
			} elseif ($sr == 1) {
				$nx = 0;
				$ny = 0;
			}
		}

		$image_avant_crop = imagecreatetruecolor($nl, $nh);

		imagecopyresampled(
			$image_avant_crop,
			$imagesource,
			0,
			0,
			0,
			0,
			$nl,
			$nh,
			$sl,
			$sh
		);

		$dest_crop = imagecreatetruecolor($tl, $th);

		imagecopyresampled(
			$dest_crop,
			$image_avant_crop,
			0,
			0,
			$nx,
			$ny,
			$tl,
			$th,
			$tl,
			$th
		);

		if ($change_nom) $image_source = $change_nom . '.jpg';

		switch ($extensionsource) {
			case 'png':
				imagepng($dest_crop, $rep_dest . $prefixe . $image_source, 100);
				break;
			case 'jpg':
				imagejpeg($dest_crop, $rep_dest . $prefixe . $image_source, 100);
				break;
			case 'jpeg':
				imagejpeg($dest_crop, $rep_dest . $prefixe . $image_source, 100);
				break;
		}
		imagedestroy($image_avant_crop);
		imagedestroy($dest_crop);
	}

	public function cropImage($image_source,
							  $rep_source,
							  $rep_dest,
							  $w_image_base,
							  $h_image_base,
							  $w_image_dest,
							  $h_image_dest,
							  $x_crop_base,
							  $y_crop_base,
							  $w_crop_base,
							  $h_crop_base,
							  $prefixe,
							  $change_nom)
	{
		$full_path = $rep_source . $image_source;
		$ext = preg_replace('/.*\.([^.]+)$/', '\\1', $image_source);
		$dst_r = ImageCreateTrueColor($w_image_dest, $h_image_dest);

		list($w_image_source, $h_image_source) = getimagesize($full_path);

		$w_ratio = $w_image_source / $w_image_base;
		$h_ratio = $h_image_source / $h_image_base;

		$x_crop_base = $w_ratio * $x_crop_base;
		$y_crop_base = $h_ratio * $y_crop_base;
		$w_crop_base = $w_ratio * $w_crop_base;
		$h_crop_base = $h_ratio * $h_crop_base;

		switch ($ext) {
			case 'png':
				$image = imagecreatefrompng($full_path);
				break;
			case 'jpg':
				$image = imagecreatefromjpeg($full_path);
				break;
			case 'jpeg':
				$image = imagecreatefromjpeg($full_path);
				break;
			default:
				break;
		}
		imagecopyresampled(
			$dst_r,
			$image,
			0,
			0,
			$x_crop_base,
			$y_crop_base,
			$w_image_dest,
			$h_image_dest,
			$w_crop_base,
			$h_crop_base
		);

		if ($change_nom) $image_source = $change_nom . '.jpg';

		switch ($ext) {
			case 'png':
				imagepng($dst_r, $rep_dest . $prefixe . $image_source, 100);
				break;
			case 'jpg':
				imagejpeg($dst_r, $rep_dest . $prefixe . $image_source, 100);
				break;
			case 'jpeg':
				imagejpeg($dst_r, $rep_dest . $prefixe . $image_source, 100);
				break;
		}
		imagedestroy($dst_r);
	}

	public function gestAntiSpam()
	{
		if ($this->checksum != '')
			return AntiSpamClass::getAntiSpamByChecksum($this->checksum);
		else {
			$liste = AntiSpamClass::getListe((int)$this->context->language->id, 1);
			if (count($liste) > 0)
				return $liste[array_rand($liste, 1)];
			else
				return false;
		}
	}

	public function gestComment($id_news)
	{
		if (!Configuration::get($this->name . '_comment_actif'))
			return false;

		$errors = array();
		$is_submit = true;
		$content_form = array(
			'news' => (int)$id_news,
			'name' => trim(Tools::getValue('name')),
			'url' => trim(Tools::getValue('url')),
			'comment' => trim(Tools::getValue('comment')),
			'date' => Date('Y-m-d H:i:s'),
			'actif' => (Configuration::get($this->name . '_comment_auto_actif') ? 1 : 0 - 1),
			'antispam_checksum' => '',
		);

		if (Tools::getValue('submitComment')) {
			if (Configuration::get('prestablog_antispam_actif')) {
				$liste_as = AntiSpamClass::getListe((int)$this->context->language->id, 1);
				if (count($liste_as) > 0)
					foreach ($liste_as as $value_as)
						if (Tools::getIsset($value_as['checksum'])) {
							$content_form['antispam_checksum'] = Tools::getValue($value_as['checksum']);
							$this->checksum = $value_as['checksum'];
							if (Tools::getValue($value_as['checksum']) != $value_as['reply'])
								$errors[$value_as['checksum']] = $this->l('Your antispam reply is not correct.');
						}
			}

			$ereg_url = '#^\b(((http|https)\:\/\/)[^\s()]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))$#';
			if (Tools::strlen($content_form['name']) < 3)
				$errors['name'] = $this->l('Your name cannot be empty or inferior at 3 characters.');
			if (Tools::strlen($content_form['comment']) < 5)
				$errors['comment'] = $this->l('Your comment cannot be empty or inferior at 5 characters.');
			if (Tools::strlen($content_form['url']) != '' && !preg_match($ereg_url, $content_form['url']))
				$errors['url'] = $this->l('Make sure the url is correct.');

			if (count($errors) > 0)
				$is_submit = false;
			else {
				CommentNewsClass::insertComment(
					$content_form['news'],
					$content_form['date'],
					$content_form['name'],
					$content_form['url'],
					$content_form['comment'],
					$content_form['actif']
				);

				if (Configuration::get($this->name . '_comment_alert_admin')) {
					$news = new NewsClass((int)$content_form['news'], $this->langue_default_store);
					$content_form['title_news'] = $news->title;

					Mail::Send(
						$this->langue_default_store,
						'feedback-admin',
						$this->l('New comment') . ' / ' . $content_form['title_news'],
						array(
							'{news}' => $content_form['news'],
							'{title_news}' => $content_form['title_news'],
							'{date}' => ToolsCore::displayDate($content_form['date'], null, true),
							'{name}' => $content_form['name'],
							'{url}' => $content_form['url'],
							'{comment}' => $content_form['comment'],
							'{url_news}' => Tools::getShopDomainSsl(true) . __PS_BASE_URI__
								. '?fc=module&module=prestablog&controller=' . self::getUrlPath() . '&id=' . $content_form['news'],
							'{actif}' => $content_form['actif']
						),
						Configuration::get($this->name . '_comment_admin_mail'),
						null,
						Configuration::get('PS_SHOP_EMAIL'),
						Configuration::get('PS_SHOP_NAME'),
						null,
						null,
						dirname(__FILE__) . '/mails/'
					);
				}

				$liste_abo = CommentNewsClass::listeCommentMailAbo($content_form['news']);

				if (Configuration::get($this->name . '_comment_subscription')
					&& count($liste_abo) > 0
					&& Configuration::get($this->name . '_comment_auto_actif')
				) {
					$news = new NewsClass((int)$content_form['news'], $this->langue_default_store);
					$content_form['title_news'] = $news->title;

					foreach ($liste_abo as $value_abo) {
						Mail::Send(
							$this->langue_default_store,
							'feedback-subscribe',
							$this->l('New comment') . ' / ' . $content_form['title_news'],
							array(
								'{news}' => $content_form['news'],
								'{title_news}' => $content_form['title_news'],
								'{url_news}' => Tools::getShopDomainSsl(true) . __PS_BASE_URI__
									. '?fc=module&module=prestablog&controller=' . self::getUrlPath() . '&id=' . $content_form['news'],
								'{url_desabonnement}' => Tools::getShopDomainSsl(true) . __PS_BASE_URI__
									. '?fc=module&module=prestablog&controller=' . self::getUrlPath() . '&d=' . $content_form['news']
							),
							$value_abo,
							null,
							(Configuration::get('PS_SHOP_EMAIL')),
							(Configuration::get('PS_SHOP_NAME')),
							null,
							null,
							dirname(__FILE__) . '/mails/'
						);
					}
				}

				$is_submit = true;
			}
		} else
			$is_submit = false;

		$this->context->smarty->assign(
			array(
				'isSubmit' => $is_submit,
				'errors' => $errors,
				'content_form' => $content_form,
				'comments' => CommentNewsClass::getListe(1, $id_news),
			)
		);

		return true;
	}

	public function blocDateListe()
	{
		if (Configuration::get($this->name . '_datenews_actif')) {
			$result_date_liste = array();

			$fin_reelle = 'TIMESTAMP(n.`date`) <= \'' . Date('Y/m/d H:i:s') . '\'';

			$result_annee = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
				SELECT	DISTINCT YEAR(n.`date`) AS `annee`
				FROM `' . _DB_PREFIX_ . NewsClass::$table_static . '_lang` as nl
				LEFT JOIN `' . _DB_PREFIX_ . NewsClass::$table_static . '` as n
					ON (n.id_prestablog_news = nl.id_prestablog_news)
				WHERE n.`actif` = 1
				AND nl.`id_lang` = ' . (int)$this->context->language->id . '
				AND nl.`actif_langue` = 1
				AND ' . $fin_reelle . '
				ORDER BY annee ' . pSQL(Configuration::get($this->name . '_datenews_order')));

			if (count($result_annee) > 0) {
				foreach ($result_annee as $value_annee) {
					$result_count_annee = Db::getInstance(_PS_USE_SQL_SLAVE_)->GetRow('
						SELECT COUNT(DISTINCT nl.`id_prestablog_news`) AS `value`
						FROM `' . _DB_PREFIX_ . NewsClass::$table_static . '_lang` as nl
						LEFT JOIN `' . _DB_PREFIX_ . NewsClass::$table_static . '` as n
							ON (n.id_prestablog_news = nl.id_prestablog_news)
						WHERE n.`actif` = 1
						AND nl.`id_lang` = ' . (int)$this->context->language->id . '
						AND nl.`actif_langue` = 1
						AND ' . $fin_reelle . '
						AND YEAR(n.`date`) = \'' . pSQL($value_annee['annee']) . '\'');

					$result_date_liste[$value_annee['annee']]['nombre_news'] = $result_count_annee['value'];

					$result_mois = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
						SELECT	DISTINCT MONTH(n.`date`) AS `mois`
						FROM `' . _DB_PREFIX_ . NewsClass::$table_static . '_lang` as nl
						LEFT JOIN `' . _DB_PREFIX_ . NewsClass::$table_static . '` as n
							ON (n.id_prestablog_news = nl.id_prestablog_news)
						WHERE n.`actif` = 1
						AND nl.`id_lang` = ' . (int)$this->context->language->id . '
						AND nl.`actif_langue` = 1
						AND YEAR(n.`date`) = ' . pSQL($value_annee['annee']) . '
						AND ' . $fin_reelle . '
						ORDER BY mois ' . pSQL(Configuration::get($this->name . '_datenews_order')));

					if (count($result_mois) > 0) {
						foreach ($result_mois as $value_mois) {
							$result_count_mois = Db::getInstance(_PS_USE_SQL_SLAVE_)->GetRow('
								SELECT COUNT(DISTINCT n.`id_prestablog_news`) AS `value`
								FROM `' . _DB_PREFIX_ . NewsClass::$table_static . '_lang` as nl
								LEFT JOIN `' . _DB_PREFIX_ . NewsClass::$table_static . '` as n
									ON (n.id_prestablog_news = nl.id_prestablog_news)
								WHERE n.`actif` = 1
								AND nl.`id_lang` = ' . (int)$this->context->language->id . '
								AND nl.`actif_langue` = 1
								AND ' . $fin_reelle . '
								AND YEAR(n.`date`) = ' . pSQL($value_annee['annee']) . ' AND MONTH(n.`date`) = ' . pSQL($value_mois['mois']));

							$result_date_liste[$value_annee['annee']]['mois'][$value_mois['mois']]['nombre_news'] = $result_count_mois['value'];
							$result_date_liste[$value_annee['annee']]['mois'][$value_mois['mois']]['mois_value'] = $this->mois_langue[$value_mois['mois']];
						}
					}
				}
			}

			$this->context->smarty->assign(
				array(
					'ResultDateListe' => $result_date_liste,
					'prestablog_annee' => Tools::getValue('prestablog_annee'),
				)
			);

			return $this->display(__FILE__, Configuration::get($this->name . '_theme') . '_bloc-dateliste.tpl');
		}
	}


	public function blocCatListe()
	{
		if (Configuration::get($this->name . '_catnews_actif')) {
			$categorie_courante = new CategoriesClass((int)Tools::getValue('c'), (int)$this->context->cookie->id_lang);
			$categorie_parente = new CategoriesClass((int)$categorie_courante->parent, (int)$this->context->cookie->id_lang);

			if (!Configuration::get($this->name . '_catnews_tree'))
				$liste = CategoriesClass::getListe((int)$this->context->language->id, 1, (int)$categorie_courante->id);
			else {
				$this->context->controller->addJS(_MODULE_DIR_ . 'prestablog/views/js/treeCategories.js');
				$liste = CategoriesClass::getListe((int)$this->context->language->id, 1);
			}

			if (count($liste) > 0) {
				foreach ($liste as $key => $value) {
					if (!Configuration::get($this->name . '_catnews_empty') && (int)$value['nombre_news_recursif'] == 0)
						unset($liste[$key]);
					else
						$liste[$key]['nombre_news'] = (int)$value['nombre_news'];
				}

				$this->context->smarty->assign(
					array(
						'prestablog_categorie_courante' => $categorie_courante,
						'prestablog_categorie_parent' => $categorie_parente,
						'ListeBlocCatNews' => $liste,
						'isDhtml' => (Configuration::get('BLOCK_CATEG_DHTML') == 1 ? true : false),
						'tree_branch_path' => _PS_MODULE_DIR_ . 'prestablog/views/templates/hook/'
							. Configuration::get('prestablog_theme') . '_bloc-catliste-tree-branch.tpl'
					)
				);

				return $this->display(__FILE__, Configuration::get($this->name . '_theme') . '_bloc-catliste.tpl');
			}
		}
	}

	public function hookDisplayNav()
	{
		return $this->display(__FILE__, Configuration::get($this->name . '_theme') . '_nav-top.tpl', $this->getCacheId());
	}

	public function hookDisplayBackOfficeHeader()
	{
		$ctrl = $this->context->controller;
		if ($ctrl instanceof AdminController && method_exists($ctrl, 'addCss'))
			$ctrl->addCss($this->_path . 'views/css/prestablog-back-office.css');
	}

	public function hookDisplayHeader()
	{
		$this->context->controller->addCSS($this->_path . 'views/css/' . Configuration::get($this->name . '_theme') . '-module.css', 'all');

		/* That is a fix to solve the bug of other modules that duplicate the displayheader process */
		if (!isset($this->context->smarty->registered_plugins['function']['PrestaBlogUrl']))
			smartyRegisterFunction($this->context->smarty, 'function', 'PrestaBlogUrl', array('PrestaBlog', 'prestablogUrl'));

		/* permet d'échaper tout le contenu html / js */
		if (!isset($this->context->smarty->registered_plugins['function']['PrestaBlogContent']))
			smartyRegisterFunction($this->context->smarty, 'function', 'PrestaBlogContent', array('PrestaBlog', 'prestablogContent'));

		/* permettre de determiner les infos de partage pour facebook, */
		/* uniquement si on est sur une news */

		if (isset($this->context->controller->module->name)
			&& $this->context->controller->module->name == $this->name
			&& Tools::getValue('id')
		) {
			$this->news = new NewsClass((int)Tools::getValue('id'), (int)$this->context->cookie->id_lang);
			$front_url_base = (Configuration::get('PS_SSL_ENABLED') ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true)) . __PS_BASE_URI__;
			if (file_exists(_PS_MODULE_DIR_ . 'prestablog/views/img/' . Configuration::get('prestablog_theme') . '/up-img/' . $this->news->id . '.jpg'))
				$news_image_url = $front_url_base . 'modules/prestablog/views/img/' . Configuration::get('prestablog_theme') . '/up-img/' . $this->news->id . '.jpg';
			else
				$news_image_url = $front_url_base . 'img/logo.jpg';

			/* moderateurs commentaires facebook */
			$list_fb_moderators = array();
			if (Configuration::get($this->name . '_commentfb_actif'))
				$list_fb_moderators = unserialize(Configuration::get($this->name . '_commentfb_modosId'));
			/* /moderateurs commentaires facebook */

			$this->context->smarty->assign(
				array(
					'prestablog_news_meta' => $this->news,
					'prestablog_news_meta_img' => $news_image_url,
					'prestablog_news_meta_url' => PrestaBlog::prestablogUrl(
						array(
							'id' => $this->news->id,
							'seo' => $this->news->link_rewrite,
							'titre' => $this->news->title
						)
					),
					'prestablog_fb_admins' => $list_fb_moderators,
				)
			);

			return $this->display(__FILE__, Configuration::get($this->name . '_theme') . '_header-meta-og.tpl');
		}
	}

	public function hookDisplayTop()
	{
		/* return $this->showSlide(); */
	}

	public function hookDisplayHome()
	{
		$html_out = '';
		if (Configuration::get($this->name . '_homenews_actif'))
			$html_out .= $this->showSlide();

		if (Configuration::get($this->name . '_subblocks_actif'))
			$html_out .= $this->showSubBlocks('displayHome');

		$lastArticles = $this->blockLastArticlesHome();
		$html_out .= $lastArticles;

		return $html_out;
	}

	public function hookDisplayPrestaBlogList($params)
	{
		/* {hook h='displayPrestaBlogList' id='1' mod='prestablog'} */
		$html_out = '';
		$liste_subblocks = SubBlocksClass::getListe((int)$this->context->language->id, 1, 'displayCustomHook');

		if (count($liste_subblocks) > 0) {
			foreach ($liste_subblocks as $value) {
				if ((int)$value['id_prestablog_subblock'] == (int)$params['id']) {
					$news_liste = self::returnUniversalNewsListSubBlocks($value, (int)$this->context->language->id);

					if (count($news_liste) > 0) {
						if ($value['random'])
							shuffle($news_liste);

						$this->context->smarty->assign(
							array(
								'subblocks' => $value,
								'news' => $news_liste,
							)
						);

						$template = Configuration::get($this->name . '_theme') . '_page-subblock.tpl';
						if ($value['template'] != '')
							$template = $value['template'];

						$html_out .= $this->display(__FILE__, $template);
					}
				}
			}
		}

		return $html_out;
	}

	public static function returnUniversalNewsListSubBlocks($value, $id_lang)
	{
		$date_fin = Date('Y-m-d H:i:s');
		$liste = array();
		switch ((int)$value['select_type']) {
			case 1 :
				$liste = NewsClass::getListe(
					$id_lang,
					1,
					0,
					0,
					(int)$value['nb_list'],
					'n.`date`',
					'desc',
					($value['use_date_start'] ? $value['date_start'] : null),
					($value['use_date_stop'] ? $value['date_stop'] : $date_fin),
					$value['blog_categories'],
					1,
					(int)$value['title_length'],
					(int)$value['intro_length']
				);
				break;

			case 2 :
				$liste = NewsClass::getListe(
					$id_lang,
					1,
					0,
					0,
					(int)$value['nb_list'],
					'n.`date`',
					'asc',
					($value['use_date_start'] ? $value['date_start'] : null),
					($value['use_date_stop'] ? $value['date_stop'] : $date_fin),
					$value['blog_categories'],
					1,
					(int)$value['title_length'],
					(int)$value['intro_length']
				);
				break;

			case 3 :
				$liste = NewsClass::getListe(
					$id_lang,
					1,
					0,
					0,
					(int)$value['nb_list'],
					'`count_comments`',
					'desc',
					($value['use_date_start'] ? $value['date_start'] : null),
					($value['use_date_stop'] ? $value['date_stop'] : $date_fin),
					$value['blog_categories'],
					1,
					(int)$value['title_length'],
					(int)$value['intro_length']
				);
				break;

			case 4 :
				$liste = NewsClass::getListe(
					$id_lang,
					1,
					0,
					0,
					(int)$value['nb_list'],
					'`count_comments`',
					'asc',
					($value['use_date_start'] ? $value['date_start'] : null),
					($value['use_date_stop'] ? $value['date_stop'] : $date_fin),
					$value['blog_categories'],
					1,
					(int)$value['title_length'],
					(int)$value['intro_length']
				);
				break;

			case 5 :
				$liste = NewsClass::getListe(
					$id_lang,
					1,
					0,
					0,
					(int)$value['nb_list'],
					'nl.`read`',
					'desc',
					($value['use_date_start'] ? $value['date_start'] : null),
					($value['use_date_stop'] ? $value['date_stop'] : $date_fin),
					$value['blog_categories'],
					1,
					(int)$value['title_length'],
					(int)$value['intro_length']
				);
				break;

			case 6 :
				$liste = NewsClass::getListe(
					$id_lang,
					1,
					0,
					0,
					(int)$value['nb_list'],
					'nl.`read`',
					'asc',
					($value['use_date_start'] ? $value['date_start'] : null),
					($value['use_date_stop'] ? $value['date_stop'] : $date_fin),
					$value['blog_categories'],
					1,
					(int)$value['title_length'],
					(int)$value['intro_length']
				);
				break;

			default:
				$liste = array();
				break;
		}
		return $liste;
	}

	public function showSubBlocks($hook_name)
	{
		$html_out = '';
		$liste_subblocks = SubBlocksClass::getListe((int)$this->context->language->id, 1, $hook_name);

		if (count($liste_subblocks) > 0) {
			foreach ($liste_subblocks as $value) {
				$news_liste = self::returnUniversalNewsListSubBlocks($value, (int)$this->context->language->id);

				if (count($news_liste) > 0) {
					if ($value['random'])
						shuffle($news_liste);

					$this->context->smarty->assign(
						array(
							'subblocks' => $value,
							'news' => $news_liste,
						)
					);

					$template = Configuration::get($this->name . '_theme') . '_page-subblock.tpl';
					if ($value['template'] != '')
						$template = $value['template'];

					$html_out .= $this->display(__FILE__, $template);
				}
			}
		}

		return $html_out;
	}

	public function showSlide()
	{
		if ($this->slideNews()) {
			$config_theme_array = PrestaBlog::objectToArray($this->getConfigXmlTheme(Configuration::get($this->name . '_theme')));
			if (is_array($config_theme_array['js']))
				foreach ($config_theme_array['js'] as $vjs)
					$this->context->controller->addJS(_MODULE_DIR_ . 'prestablog/views/js/' . $vjs);
			elseif ($config_theme_array['js'] != '')
				$this->context->controller->addJS(_MODULE_DIR_ . 'prestablog/views/js/' . $config_theme_array['js']);

			return $this->display(__FILE__, Configuration::get($this->name . '_theme') . '_slide.tpl');
		}
	}

	public function slideNews()
	{
		$liste = array();

		$liste = NewsClass::getListe(
			(int)$this->context->language->id,
			1,
			1,
			0,
			(int)Configuration::get($this->name . '_homenews_limit'),
			'n.`date`',
			'desc',
			null,
			Date('Y/m/d H:i:s'),
			null,
			1,
			(int)Configuration::get('prestablog_slide_title_length'),
			(int)Configuration::get('prestablog_slide_intro_length')
		);

		if (count($liste) > 0) {
			$this->context->smarty->assign(
				array(
					'ListeBlogNews' => $liste
				)
			);
			return true;
		} else
			return false;
	}

	public function blocRss()
	{
		if (Configuration::get('prestablog_allnews_rss'))
			return $this->display(__FILE__, Configuration::get($this->name . '_theme') . '_bloc-rss.tpl');
	}

	public function blocSearch()
	{
		if (Configuration::get('prestablog_blocsearch_actif')) {
			$this->context->smarty->assign(
				array(
					'prestablog_search_query' => trim(Tools::getValue('prestablog_search')),
				)
			);
			return $this->display(__FILE__, Configuration::get($this->name . '_theme') . '_bloc-search.tpl');
		}
	}

	public function hookDisplayLeftColumn()
	{
		$result = null;

		$sbl = unserialize(Configuration::get($this->name . '_sbl'));
		if (count($sbl) > 0)
			foreach ($sbl as $vs)
				if ($vs != '')
					$result .= $this->$vs();

		return $result;
	}

	public function hookDisplayRightColumn()
	{
		$result = null;

		$sbr = unserialize(Configuration::get($this->name . '_sbr'));
		if (count($sbr) > 0)
			foreach ($sbr as $vs)
				if ($vs != '')
					$result .= $this->$vs();

		return $result;
	}

	public function hookDisplayFooterProduct()
	{
		if ($this->isPSVersion('>=', '1.6')) {
			$liste_news_linked = NewsClass::getNewsProductLinkListe((int)Tools::getValue('id_product'), true);
			if (Configuration::get($this->name . '_producttab_actif')
				&& count($liste_news_linked) > 0
			) {
				$returnliste = array();
				foreach ($liste_news_linked as $vnews) {
					$lang = (int)$this->context->language->id;
					$news = new NewsClass((int)$vnews);
					$lang_liste_news = unserialize($news->langues);

					if (in_array($lang, $lang_liste_news)) {
						$paragraph = $paragraph_crop = $news->paragraph[$lang];

						if ((Tools::strlen(trim($paragraph)) == 0)
							&& (Tools::strlen(trim(strip_tags($news->content[$lang]))) >= 1)
						)
							$paragraph_crop = trim(strip_tags($news->content[$lang]));

						$returnliste[(int)$vnews] = array(
							'id' => $news->id,
							'url' => PrestaBlog::prestablogUrl(
								array(
									'id' => $news->id,
									'seo' => $news->link_rewrite[$lang],
									'titre' => $news->title[$lang]
								)
							),
							'title' => $news->title[$lang],
							'paragraph_crop' => PrestaBlog::cleanCut($paragraph_crop, (int)Configuration::get('prestablog_news_intro_length'), ' [...]'),
							'image_presente' => file_exists($this->module_path . '/views/img/' . Configuration::get('prestablog_theme') . '/up-img/' . $news->id . '.jpg'),
						);
					}
				}
				$this->context->smarty->assign(
					array(
						'listeNewsLinked' => $returnliste
					)
				);
				return $this->display(__FILE__, Configuration::get($this->name . '_theme') . '_product-footer.tpl');
			}
		}
	}

	public function hookDisplayProductTab()
	{
		if ($this->isPSVersion('<', '1.6')) {
			$liste_news_linked = NewsClass::getNewsProductLinkListe((int)Tools::getValue('id_product'), true);
			if (Configuration::get($this->name . '_producttab_actif')
				&& count($liste_news_linked) > 0
			)
				return $this->display(__FILE__, Configuration::get($this->name . '_theme') . '_product-tab.tpl');
		}
	}

	public function hookDisplayProductTabContent()
	{
		if ($this->isPSVersion('<', '1.6')) {
			$liste_news_linked = NewsClass::getNewsProductLinkListe((int)Tools::getValue('id_product'), true);
			if (Configuration::get($this->name . '_producttab_actif')
				&& count($liste_news_linked) > 0
			) {
				$returnliste = array();
				foreach ($liste_news_linked as $vnews) {
					$lang = (int)$this->context->language->id;
					$news = new NewsClass((int)$vnews);
					$lang_liste_news = unserialize($news->langues);

					if (in_array($lang, $lang_liste_news)) {
						$paragraph = $paragraph_crop = $news->paragraph[$lang];

						if ((Tools::strlen(trim($paragraph)) == 0)
							&& (Tools::strlen(trim(strip_tags($news->content[$lang]))) >= 1)
						)
							$paragraph_crop = trim(strip_tags($news->content[$lang]));

						$returnliste[(int)$vnews] = array(
							'id' => $news->id,
							'url' => PrestaBlog::prestablogUrl(
								array(
									'id' => $news->id,
									'seo' => $news->link_rewrite[$lang],
									'titre' => $news->title[$lang]
								)
							),
							'title' => $news->title[$lang],
							'paragraph_crop' => PrestaBlog::cleanCut($paragraph_crop,
								(int)Configuration::get('prestablog_news_intro_length'), ' [...]'),
							'image_presente' => file_exists($this->module_path . '/views/img/' . Configuration::get('prestablog_theme') . '/up-img/' . $news->id . '.jpg'),
						);
					}
				}
				$this->context->smarty->assign(
					array(
						'listeNewsLinked' => $returnliste
					)
				);

				return $this->display(__FILE__, Configuration::get($this->name . '_theme') . '_product-tab-content.tpl');
			}
		}
	}

	public function hookDisplayFooter()
	{
		if (Configuration::get($this->name . '_footlastnews_actif')) {
			$tri_champ = 'n.`date`';
			$tri_ordre = 'desc';
			$liste = NewsClass::getListe(
				(int)$this->context->language->id,
				1,
				0,
				0,
				(int)Configuration::get($this->name . '_footlastnews_limit'),
				$tri_champ,
				$tri_ordre,
				null,
				Date('Y/m/d H:i:s'),
				null,
				1,
				(int)Configuration::get('prestablog_footer_title_length'),
				(int)Configuration::get('prestablog_footer_intro_length')
			);

			$this->context->smarty->assign(
				array(
					'ListeBlocLastNews' => $liste,
				)
			);
			return $this->display(__FILE__, Configuration::get($this->name . '_theme') . '_footer-lastliste.tpl');
		}
	}

	public function generateToken($add_text = null)
	{
		return md5($add_text . $this->module_key . _COOKIE_KEY_);
	}

	public function isPreviewMode($id_prestablog_news)
	{
		if (Tools::getValue('preview') == $this->generateToken($id_prestablog_news))
			return true;
		return false;
	}

	public function hookModuleRoutes()
	{
		return self::$module_routes;
	}

	public static $module_routes = array(
		'prestablog-blog-root' => array(
			'controller' => null,
			'rule' => '{controller}',
			'keywords' => array(
				'controller' => array('regexp' => 'articles', 'param' => 'controller'),
			),
			'params' => array(
				'fc' => 'module',
				'module' => 'prestablog'
			)
		),
		'prestablog-blog-news' => array(
			'controller' => null,
			'rule' => '{controller}/{urlnews}-n{n}',
			'keywords' => array(
				'urlnews' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'n' => array('regexp' => '[0-9]+', 'param' => 'id'),
				'controller' => array('regexp' => 'articles', 'param' => 'controller'),
			),
			'params' => array(
				'fc' => 'module',
				'module' => 'prestablog'
			)
		),
		'prestablog-blog-date' => array(
			'controller' => null,
			'rule' => '{controller}/y{y}-m{m}',
			'keywords' => array(
				'y' => array('regexp' => '[0-9]{4}', 'param' => 'y'),
				'm' => array('regexp' => '[0-9]+', 'param' => 'm'),
				'controller' => array('regexp' => 'articles', 'param' => 'controller'),
			),
			'params' => array(
				'fc' => 'module',
				'module' => 'prestablog'
			)
		),
		'prestablog-blog-date-pagignation' => array(
			'controller' => null,
			'rule' => '{controller}/{start}p{p}y{y}-m{m}',
			'keywords' => array(
				'y' => array('regexp' => '[0-9]{4}', 'param' => 'y'),
				'm' => array('regexp' => '[0-9]+', 'param' => 'm'),
				'start' => array('regexp' => '[0-9]+', 'param' => 'start'),
				'p' => array('regexp' => '[0-9]+', 'param' => 'p'),
				'controller' => array('regexp' => 'articles', 'param' => 'controller')
			),
			'params' => array(
				'fc' => 'module',
				'module' => 'prestablog'
			)
		),
		'prestablog-blog-pagignation' => array(
			'controller' => null,
			'rule' => '{controller}/{start}p{p}',
			'keywords' => array(
				'start' => array('regexp' => '[0-9]+', 'param' => 'start'),
				'p' => array('regexp' => '[0-9]+', 'param' => 'p'),
				'controller' => array('regexp' => 'articles', 'param' => 'controller')
			),
			'params' => array(
				'fc' => 'module',
				'module' => 'prestablog'
			)
		),
		'prestablog-blog-catpagination' => array(
			'controller' => null,
			'rule' => '{controller}/{urlcat}-{start}p{p}-c{c}',
			'keywords' => array(
				'c' => array('regexp' => '[0-9]+', 'param' => 'c'),
				'urlcat' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'start' => array('regexp' => '[0-9]+', 'param' => 'start'),
				'p' => array('regexp' => '[0-9]+', 'param' => 'p'),
				'controller' => array('regexp' => 'articles', 'param' => 'controller')
			),
			'params' => array(
				'fc' => 'module',
				'module' => 'prestablog'
			)
		),
		'prestablog-blog-cat' => array(
			'controller' => null,
			'rule' => '{controller}/{urlcat}-c{c}',
			'keywords' => array(
				'c' => array('regexp' => '[0-9]+', 'param' => 'c'),
				'urlcat' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				'controller' => array('regexp' => 'articles', 'param' => 'controller')
			),
			'params' => array(
				'fc' => 'module',
				'module' => 'prestablog'
			)
		),
		'prestablog-rss-root' => array(
			'controller' => null,
			'rule' => '{controller}',
			'keywords' => array(
				'controller' => array('regexp' => 'rss', 'param' => 'controller'),
			),
			'params' => array(
				'fc' => 'module',
				'module' => 'prestablog'
			)
		),
		'prestablog-blog-rss' => array(
			'controller' => null,
			'rule' => '{controller}/{rss}',
			'keywords' => array(
				'rss' => array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'rss'),
				'controller' => array('regexp' => 'rss', 'param' => 'controller')
			),
			'params' => array(
				'fc' => 'module',
				'module' => 'prestablog'
			)
		)
	);

	public static function getImgFlagByIso($iso)
	{
		if ((int)Language::getIdByIso(Tools::strtolower($iso)) > 0)
			return '<img src="../img/l/' . (int)Language::getIdByIso(Tools::strtolower($iso)) . '.jpg" />';
		elseif (file_exists(dirname(__FILE__) . '/views/img/l/' . Tools::strtolower($iso) . '.png'))
			return '<img src="../modules/prestablog/views/img/l/' . Tools::strtolower($iso) . '.png" />';
		else
			return '<img src="../img/l/none.jpg" />';
	}

	public static function cleanCut($string, $length, $cut_string = '...')
	{
		$string = strip_tags($string);
		if (Tools::strlen($string) <= $length)
			return $string;
		$str = Tools::substr($string, 0, $length - Tools::strlen($cut_string) + 1);
		return Tools::substr($str, 0, strrpos($str, ' ')) . $cut_string;
	}

	public static function arrayDeleteValue($array, $search)
	{
		$temp = array();
		foreach ($array as $key => $value)
			if ($value != $search) $temp[$key] = $value;
		return $temp;
	}

	private function dirSize($directory)
	{
		$size = 0;
		foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file)
			$size += $file->getSize();
		return $size;
	}

	private function rrmdir($directory)
	{
		foreach (glob($directory . '/*') as $file)
			if (is_dir($file))
				$this->rrmdir($file);
	}

	private function rcopy($src, $dst)
	{
		$dir = opendir($src);
		self::makeDirectory($dst);
		while (false !== ($file = readdir($dir))) {
			if (($file != '.') && ($file != '..')) {
				if (is_dir($src . '/' . $file))
					$this->rcopy($src . '/' . $file, $dst . '/' . $file);
				else
					self::copy($src . '/' . $file, $dst . '/' . $file);
			}
		}
		closedir($dir);
	}

	public static function copy($source, $destination, $stream_context = null)
	{
		if (version_compare(_PS_VERSION_, '1.5.6.0', '<'))
			return @copy($source, $destination);
		return Tools::copy($source, $destination, $stream_context);
	}

	public function genererMDP($longueur = 16)
	{
		$mdp = '';
		$possible = 'abcdfghijklmnopqrstuvwxyz012346789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$longueur_max = Tools::strlen($possible);
		if ($longueur > $longueur_max)
			$longueur = $longueur_max;
		$i = 0;
		while ($i < $longueur) {
			$mdp .= Tools::substr($possible, mt_rand(0, $longueur_max - 1), 1);
			$i++;
		}
		return $mdp;
	}

	public static function objectToArray($object)
	{
		if (!is_object($object) && !is_array($object))
			return $object;

		if (is_object($object))
			$object = get_object_vars($object);

		return array_map(array('PrestaBlog', 'objectToArray'), $object);
	}

	public static function createSqlFilterSearch($from_fields = array(), $search = '', $nb_car_per_item = 3)
	{
		if ($search != '') {
			$filtre_sujet2 = '';
			$filtre_cumul = '%';

			$filtre_search = 'AND (' . "\n";

			foreach (preg_split('/ /', $search) as $value_keywords) {
				if (Tools::strlen($value_keywords) >= (int)$nb_car_per_item) {
					$filtre_cumul .= $value_keywords . '%';
					foreach ($from_fields as $field)
						$filtre_sujet2 .= 'OR ' . $field . ' LIKE \'%' . pSQL($value_keywords) . '%\'' . "\n";
				}
			}
			if (($filtre_cumul != '%') && (strpos($filtre_sujet2, $filtre_cumul) === false)) {
				foreach ($from_fields as $field)
					$filtre_sujet2 .= 'OR ' . $field . ' LIKE \'' . pSQL($filtre_cumul) . '\'' . "\n";
			}
			$filtre_sujet2 = trim(ltrim($filtre_sujet2, 'OR'));

			if ($filtre_sujet2)
				$filtre_search .= $filtre_sujet2 . ')' . "\n";

			$filtre_search = trim(rtrim($filtre_search, 'AND (' . "\n"));
			//$filtre_search = trim(ltrim($filtre_search, 'AND'));
			$filtre_search = trim(ltrim($filtre_search, 'OR'));

			return $filtre_search;
		}
		return '';
	}

	/***
	 * Method for latest articles on homepage
	 * @return mixed
	 * @param $display
	 * @since version
	 */

	public function blockLastArticlesHome($display = false)
	{
		if (Configuration::get($this->name . '_lastnews_actif') && $display) {
			$tri_champ = 'n.`date`';
			$tri_ordre = 'desc';
			$liste = NewsClass::getListe(
				(int)$this->context->language->id,
				1,
				0,
				0,
				(int)Configuration::get($this->name . '_lastnews_limit'),
				$tri_champ,
				$tri_ordre,
				null,
				Date('Y/m/d H:i:s'),
				null,
				1,
				(int)Configuration::get('prestablog_lastnews_title_length'),
				(int)Configuration::get('prestablog_lastnews_intro_length')
			);

			$this->context->smarty->assign(
				array(
					'ListeBlocLastNews' => $liste,
				)
			);

			return $this->display(__FILE__, Configuration::get($this->name . '_theme') . '_bloc-lastliste.tpl');
		}
	}

	/**
	 *
	 * leftColumn Last atricles
	 * @return string
	 *
	 * @since version
	 */
	public function blocLastListe()
	{
		if (Configuration::get($this->name . '_lastnews_actif')) {
			$tri_champ = 'n.`date`';
			$tri_ordre = 'desc';
			$liste = NewsClass::getListe(
				(int)$this->context->language->id,
				1,
				0,
				0,
				(int)Configuration::get($this->name . '_lastnews_limit'),
				$tri_champ,
				$tri_ordre,
				null,
				Date('Y/m/d H:i:s'),
				null,
				1,
				(int)Configuration::get('prestablog_lastnews_title_length'),
				(int)Configuration::get('prestablog_lastnews_intro_length')
			);

			$this->context->smarty->assign(
				array(
					'ListeBlocLastNews' => $liste,
				)
			);

			return $this->display(__FILE__, Configuration::get($this->name . '_theme') . '_bloc-lastliste.tpl');
		}
	}
}

/*
	echo '<pre style="font-size:11px;text-align:left">';
		print_r();
	echo '</pre>';
*/