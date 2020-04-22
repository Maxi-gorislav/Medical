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

class PrestaBlogArticlesModuleFrontController extends ModuleFrontController
{
	public $ssl = true;

	private $assign_page = 0;
	private $prestablog;

	private $news = array();
	private $news_count_all;
	private $path;
	private $pagination = array();
	private $config_theme;

	public function l($string)
	{
		$module = new PrestaBlog;
		return Translate::getModuleTranslation($module, $string, basename(__FILE__, '.php'));
	}

	public function getTemplatePathFix($template)
	{
		if (PrestaBlog::isPSVersion('>=', '1.6'))
			return $this->getTemplatePath($template);
		else
			return _PS_MODULE_DIR_.'prestablog/views/templates/front/'.$template;
	}

	public function __construct()
	{
		/* $this->display_column_left = false; */
		/* $this->display_column_right = false; */

		parent::__construct();

		include_once(_PS_MODULE_DIR_.'prestablog/prestablog.php');
		include_once(_PS_MODULE_DIR_.'prestablog/class/news.class.php');
		include_once(_PS_MODULE_DIR_.'prestablog/class/categories.class.php');
		include_once(_PS_MODULE_DIR_.'prestablog/class/correspondancescategories.class.php');
		include_once(_PS_MODULE_DIR_.'prestablog/class/commentnews.class.php');
		include_once(_PS_MODULE_DIR_.'prestablog/class/antispam.class.php');

		$this->config_theme = PrestaBlog::getConfigXmlTheme(Configuration::get('prestablog_theme'));
	}

	public function setMedia()
	{
		parent::setMedia();
		//$this->context->controller->addCSS(_MODULE_DIR_.'prestablog/views/css/'.Configuration::get('prestablog_theme').'-module.css', 'all');

		if (Configuration::get('prestablog_socials_actif'))
			$this->context->controller->addCSS(_MODULE_DIR_.'prestablog/views/css/rrssb.css', 'all');

		if (Configuration::get('prestablog_pageslide_actif'))
		{
			$config_theme_array = PrestaBlog::objectToArray($this->config_theme);
			if (is_array($config_theme_array['js']))
			{
				foreach ($config_theme_array['js'] as $vjs)
					$this->context->controller->addJS(_MODULE_DIR_.'prestablog/views/js/'.$vjs);
			}
			elseif ($config_theme_array['js'] != '')
				$this->context->controller->addJS(_MODULE_DIR_.'prestablog/views/js/'.$config_theme_array['js']);
		}

		if (PrestaBlog::isPSVersion('<', '1.6'))
			$this->addjqueryPlugin('fancybox');
	}

	public function canonicalRedirectionCustomController($url_real)
	{
		$match_url = (Configuration::get('PS_SSL_ENABLED') && ($this->ssl ||
				Configuration::get('PS_SSL_ENABLED_EVERYWHERE')) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$match_url = rawurldecode($match_url);
		if (!preg_match('/^'.Tools::pRegexp(rawurldecode($url_real), '/').'([&?].*)?$/', $match_url))
			Tools::redirectLink($url_real);
	}

	public function init()
	{
		$id_prestablog_news = null;
		parent::init();
		$secteur_name = '';

		$base = ((Configuration::get('PS_SSL_ENABLED')) ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true));

		$base .= __PS_BASE_URI__;

		$this->prestablog = new PrestaBlog();

		/* assignPage (1 = 1 news page, 2 = news listes, 0 = rien) */
		$this->context->smarty->assign(
			array(
				'prestablog_config' => Configuration::getMultiple(array_keys($this->prestablog->configurations)),
				'prestablog_theme' => Configuration::get('prestablog_theme'),
				'prestablog_theme_dir' => _MODULE_DIR_.'prestablog/views/',
				'md5pic' => md5(time())
			)
		);

		if (Tools::getValue('id') && $id_prestablog_news = (int)Tools::getValue('id'))
		{
			$this->assign_page = 1;
			$this->news = new NewsClass($id_prestablog_news, (int)$this->context->cookie->id_lang);

			if (!$this->prestablog->isPreviewMode((int)$this->news->id))
			{
				if (!$this->news->actif)
					Tools::redirect('404.php');

				if (!CategoriesClass::isCustomerPermissionGroups(CorrespondancesCategoriesClass::getCategoriesListe((int)$this->news->id)))
					Tools::redirect('404.php');

				if (!empty($this->news->url_redirect) && Validate::isAbsoluteUrl($this->news->url_redirect))
					Tools::redirect($this->news->url_redirect);
			}

			$this->context->smarty->assign(
				array(
					'SecteurName' => '&nbsp;>&nbsp;<a href="'.PrestaBlog::prestablogUrl(
							array(
								'id'		=> $this->news->id,
								'seo'		=> $this->news->link_rewrite,
								'titre'		=> $this->news->title
							)
						).'">'.$this->news->title.'</a>'
				)
			);
		}
		elseif (Tools::getValue('a') && Configuration::get('prestablog_comment_subscription'))
		{
			if (!$this->context->cookie->isLogged())
				Tools::redirect('index.php?controller=authentication&back='.
					urlencode('index.php?fc=module&module=prestablog&controller=blog&a='.Tools::getValue('a')));

			$this->news = new NewsClass((int)Tools::getValue('a'), (int)$this->context->cookie->id_lang);

			if ($this->news->actif)
			{
				CommentNewsClass::insertCommentAbo(
					$this->news->id,
					$this->context->cookie->id_customer
				);
			}

			Tools::redirect(
				PrestaBlog::prestablogUrl(
					array(
						'id'		=> $this->news->id,
						'seo'		=> $this->news->link_rewrite,
						'titre'	=> $this->news->title
					)
				)
			);
		}
		elseif (Tools::getValue('d') && Configuration::get('prestablog_comment_subscription'))
		{
			if ($this->context->cookie->isLogged())
			{
				$this->news = new NewsClass((int)Tools::getValue('d'), (int)$this->context->cookie->id_lang);
				if ($this->news->actif)
				{
					CommentNewsClass::deleteCommentAbo(
						$this->news->id,
						$this->context->cookie->id_customer
					);
				}
			}

			Tools::redirect(
				PrestaBlog::prestablogUrl(
					array(
						'id'		=> $this->news->id,
						'seo'		=> $this->news->link_rewrite,
						'titre'	=> $this->news->title
					)
				)
			);
		}
		else
		{
			$this->assign_page = 2;
			$categorie = null;
			$year = null;
			$month = null;

			if (Tools::getValue('c'))
			{
				if (!CategoriesClass::isCustomerPermissionGroups(array((int)Tools::getValue('c'))))
					Tools::redirect('404.php');

				$categorie = new CategoriesClass((int)Tools::getValue('c'), (int)$this->context->cookie->id_lang);

				$secteur_name = CategoriesClass::getBreadcrumb(CategoriesClass::getBranche($categorie->id));

				$this->context->smarty->assign(
					array(
						'prestablog_categorie'	=> $categorie->id,
						'prestablog_categorie_name' => $categorie->title,
						'prestablog_categorie_link_rewrite' => ($categorie->link_rewrite != '' ? $categorie->link_rewrite : $categorie->title),
					)
				);
			}
			else
			{
				$this->context->smarty->assign(
					array(
						'prestablog_categorie'	=> null,
						'prestablog_categorie_name' => null,
						'prestablog_categorie_link_rewrite' => null,
					)
				);
			}

			if (trim(Tools::getValue('prestablog_search')))
			{
				$secteur_name .= '<a href="#">';
				$secteur_name .= sprintf($this->l('Search %1$s in the blog'), '"'.trim(Tools::getValue('prestablog_search')).'"');
				$secteur_name .= '</a>';
			}

			if (Tools::getValue('y'))
			{
				$year = Tools::getValue('y');
				$secteur_name .= $year;
			}

			if (Tools::getValue('m'))
			{
				$month = Tools::getValue('m');
				$secteur_name .= ($secteur_name != '' ? ' > ' : '').'<a href="'.PrestaBlog::prestablogUrl(
						array(
							'y'		=> $year,
							'm'		=> $month
						)
					).'">'.$this->prestablog->mois_langue[$month].'</a>';
			}

			if (Tools::getValue('p'))
			{
				if ($secteur_name == '')
					$secteur_name = $this->l('All news');
				$secteur_name .= ' > '.$this->l('Page').' '.Tools::getValue('p');
			}

			$this->context->smarty->assign(
				array(
					'prestablog_month'	=> $month,
					'prestablog_year'		=> $year
				)
			);

			if (Tools::getValue('m') && Tools::getValue('y'))
			{
				$date_debut = Date('Y-m-d H:i:s', mktime(0, 0, 0, $month, + 1, $year));
				$date_fin = Date('Y-m-d H:i:s', mktime(0, 0, 0, $month + 1, + 1, $year));
				if ($date_fin > Date('Y-m-d H:i:s'))
					$date_fin = Date('Y-m-d H:i:s');
			}
			else
			{
				$date_debut = null;
				$date_fin = Date('Y-m-d H:i:s');
			}

			$categories_filtre = null;

			if (isset($categorie->id))
				$categories_filtre = (int)$categorie->id;
			elseif (Tools::getValue('prestablog_search_array_cat'))
				$categories_filtre = Tools::getValue('prestablog_search_array_cat');

			$this->news_count_all = NewsClass::getCountListeAll(
				(int)$this->context->cookie->id_lang,
				1,
				0,
				$date_debut,
				$date_fin,
				$categories_filtre,
				1,
				Tools::getValue('prestablog_search')
			);

			$this->news = NewsClass::getListe(
				(int)$this->context->cookie->id_lang,
				1,
				0,
				(int)Tools::getValue('start'),
				(int)Configuration::get('prestablog_nb_liste_page'),
				'n.`date`',
				'desc',
				$date_debut,
				$date_fin,
				$categories_filtre,
				1,
				(int)Configuration::get('prestablog_news_title_length'),
				(int)Configuration::get('prestablog_news_intro_length'),
				Tools::getValue('prestablog_search')
			);

			$this->context->smarty->assign(
				array(
					'SecteurName' => $secteur_name
				)
			);
		}

		if ($this->assign_page == 1)
			$this->context->smarty->assign(PrestaBlog::getPrestaBlogMetaTagsNewsOnly((int)$this->context->cookie->id_lang, (int)Tools::getValue('id')));
		elseif ($this->assign_page == 2 && Tools::getValue('c'))
			$this->context->smarty->assign(PrestaBlog::getPrestaBlogMetaTagsNewsCat((int)$this->context->cookie->id_lang, (int)Tools::getValue('c')));
		elseif ($this->assign_page == 2 && (Tools::getValue('y') || Tools::getValue('m')))
			$this->context->smarty->assign(PrestaBlog::getPrestaBlogMetaTagsNewsDate());
		else $this->context->smarty->assign(PrestaBlog::getPrestaBlogMetaTagsPage((int)$this->context->cookie->id_lang));

		if (!$this->prestablog->isPreviewMode((int)$id_prestablog_news))
			$this->gestionRedirectionCanonical((int)$this->assign_page);
	}

	private function gestionRedirectionCanonical($assign_page)
	{

		switch ($assign_page)
		{
			case 1 :
				$news = new NewsClass((int)Tools::getValue('id'), (int)$this->context->cookie->id_lang);
				if (!Tools::getValue('submitComment'))
					$this->canonicalRedirectionCustomController(PrestaBlog::prestablogUrl(array(
						'id'		=> $news->id,
						'seo'		=> $news->link_rewrite,
						'titre'	=> $news->title
					)));
				break;

			case 2 :

				if (Tools::getValue('start') && Tools::getValue('p')
					&& !Tools::getValue('c') && !Tools::getValue('m') && !Tools::getValue('y'))
				{
					$this->canonicalRedirectionCustomController(PrestaBlog::prestablogUrl(array(
						'start'	=> (int)Tools::getValue('start'),
						'p'		=> (int)Tools::getValue('p')
					)));
				}
				if (Tools::getValue('c') && !Tools::getValue('start') && !Tools::getValue('p'))
				{
					$categorie = new CategoriesClass((int)Tools::getValue('c'), (int)$this->context->cookie->id_lang);
					$this->canonicalRedirectionCustomController(PrestaBlog::prestablogUrl(array(
						'c'			=> $categorie->id,
						'categorie'	=> ($categorie->link_rewrite != '' ? $categorie->link_rewrite
							: CategoriesClass::getCategoriesName((int)$this->context->cookie->id_lang, (int)Tools::getValue('c'))),
					)));
				}
				if (Tools::getValue('c') && Tools::getValue('start') && Tools::getValue('p'))
				{
					$categorie = new CategoriesClass((int)Tools::getValue('c'), (int)$this->context->cookie->id_lang);
					$this->canonicalRedirectionCustomController(PrestaBlog::prestablogUrl(array(
						'c'			=> $categorie->id,
						'start'		=> (int)Tools::getValue('start'),
						'p'			=> (int)Tools::getValue('p'),
						'categorie'	=> ($categorie->link_rewrite != '' ? $categorie->link_rewrite
							: CategoriesClass::getCategoriesName((int)$this->context->cookie->id_lang, (int)Tools::getValue('c'))),
					)));
				}
				if (Tools::getValue('m') && Tools::getValue('y')
					&& !Tools::getValue('start') && !Tools::getValue('p'))
				{
					$this->canonicalRedirectionCustomController(PrestaBlog::prestablogUrl(array(
						'y'		=> (int)Tools::getValue('y'),
						'm'		=> (int)Tools::getValue('m')
					)));
				}
				if (Tools::getValue('m') && Tools::getValue('y')
					&& Tools::getValue('start') && Tools::getValue('p'))
				{
					$this->canonicalRedirectionCustomController(PrestaBlog::prestablogUrl(array(
						'y'		=> (int)Tools::getValue('y'),
						'm'		=> (int)Tools::getValue('m'),
						'start'	=> (int)Tools::getValue('start'),
						'p'		=> (int)Tools::getValue('p'))));
				}
				if (!Tools::getValue('m') && !Tools::getValue('y')
					&& !Tools::getValue('c')
					&& !Tools::getValue('start') && !Tools::getValue('p'))
				{
					$title_h1_index = trim(Configuration::get('prestablog_h1pageblog_'.(int)$this->context->cookie->id_lang));
					if ($title_h1_index != '')
						$this->context->smarty->assign('prestablog_title_h1', $title_h1_index);
					$this->canonicalRedirectionCustomController(PrestaBlog::prestablogUrl(array()));
				}
				break;
		}
	}

	public function initContent()
	{
		parent::initContent();

		/** affichage du menu cat */
		if ($this->assign_page == 1 && Configuration::get('prestablog_menu_cat_blog_article'))
			$this->voirListeCatMenu();
		/** ne pas afficher le menu cat sur la page search **/
		if ($this->assign_page == 2 && !trim(Tools::getValue('prestablog_search')))
		{
			if (Configuration::get('prestablog_menu_cat_blog_index')
				&&	!Tools::getValue('c')
				&&	!Tools::getValue('y')
				&&	!Tools::getValue('m')
				&&	!Tools::getValue('p'))
				$this->voirListeCatMenu();
			elseif (Configuration::get('prestablog_menu_cat_blog_list')
				&&	(Tools::getValue('c')
					||	Tools::getValue('y')
					||	Tools::getValue('m')
					||	Tools::getValue('p')))
				$this->voirListeCatMenu();
		}
		/** /affichage du menu cat */

		/** affichage du filtrage search */
		if ($this->assign_page == 2
			&& trim(Tools::getValue('prestablog_search'))
			&& Configuration::get('prestablog_search_filtrecat'))
			$this->voirFiltrageSearch();
		/** /affichage du filtrage search */

		if ($this->assign_page == 1)
		{
			$this->news->categories = CorrespondancesCategoriesClass::getCategoriesListeName((int)$this->news->id,
				(int)$this->context->cookie->id_lang, 1);

			/* liaison produits */
			$products_liaison = NewsClass::getProductLinkListe((int)$this->news->id, true);

			if (count($products_liaison) > 0)
			{
				foreach ($products_liaison as $product_link)
				{
					$product = new Product((int)$product_link, false, (int)$this->context->cookie->id_lang);
					$product_cover = Image::getCover($product->id);
					$image_product = new Image((int)$product_cover['id_image']);
					$image_thumb_path = ImageManager::thumbnail(_PS_IMG_DIR_.'p/'.$image_product->getExistingImgPath()
						.'.jpg', 'product_mini_2_'.$product->id.'.jpg', 100, 'jpg');

					$this->news->products_liaison[$product_link] = array(
						'name' => $product->name,
						'description_short' => $product->description_short,
						'thumb' => $image_thumb_path,
						'link' => $product->getLink($this->context)
					);
				}
			}
			/* /liaison produits */

			/* liaison articles */
			$articles_liaison = NewsClass::getArticleLinkListe((int)$this->news->id, true);

			if (count($articles_liaison) > 0)
			{
				foreach ($articles_liaison as $article_liaison)
				{
					$article = new NewsClass((int)$article_liaison, (int)$this->context->cookie->id_lang);

					$this->news->articles_liaison[$article_liaison] = array(
						'title' => $article->title,
						'link' => PrestaBlog::prestablogUrl(
							array(
								'id'		=> $article->id,
								'seo'		=> $article->link_rewrite,
								'titre'	=> $article->title
							)
						)
					);
				}
			}
			/* /liaison articles */

			$prestablog_current_url = PrestaBlog::prestablogUrl(
				array(
					'id'		=> $this->news->id,
					'seo'		=> $this->news->link_rewrite,
					'titre'	=> $this->news->title
				)
			);

			if (file_exists(_PS_MODULE_DIR_.'prestablog/views/img/'.Configuration::get('prestablog_theme').'/up-img/'.$this->news->id.'.jpg'))
				$this->context->smarty->assign('news_Image',
					'modules/prestablog/views/img/'.Configuration::get('prestablog_theme').'/up-img/'.$this->news->id.'.jpg');
			$this->context->smarty->assign(
				array(
					'LinkReal' 		=> PrestaBlog::getBaseUrlFront().'?fc=module&module=prestablog&controller=' . PrestaBlog::getUrlPath(),
					'news' 			=> $this->news,
					'prestablog_current_url' => $prestablog_current_url,
				)
			);

			/* INCREMENT NEWS READ */
			if (!$this->context->cookie->__isset('prestablog_news_read_'.(int)$this->context->cookie->id_lang))
			{
				$this->news->incrementRead((int)$this->news->id, (int)$this->context->cookie->id_lang);
				$this->context->cookie->__set('prestablog_news_read_'.(int)$this->context->cookie->id_lang, serialize(array((int)$this->news->id)));
			}
			else
			{
				$array_news_readed = unserialize($this->context->cookie->__get('prestablog_news_read_'.(int)$this->context->cookie->id_lang));
				if (!in_array((int)$this->news->id, $array_news_readed))
				{
					$array_news_readed[] = (int)$this->news->id;
					$this->news->incrementRead((int)$this->news->id, (int)$this->context->cookie->id_lang);
					$this->context->cookie->__set('prestablog_news_read_'.(int)$this->context->cookie->id_lang, serialize($array_news_readed));
				}
			}
			/* /INCREMENT NEWS READ */

			$this->context->controller->addJS(_MODULE_DIR_.'prestablog/views/js/rrssb.min.js', 'all');

			$this->context->smarty->assign(
				array(
					'tpl_unique'			=> $this->context->smarty->fetch($this->getTemplatePathFix(Configuration::get('prestablog_theme').'_page-unique.tpl'))
				)
			);

			if ($this->prestablog->gestComment($this->news->id))
			{
				if (Configuration::get('prestablog_antispam_actif'))
				{
					$anti_spam_load = $this->prestablog->gestAntiSpam();

					if ($anti_spam_load != false)
						$this->context->smarty->assign(
							array(
								'AntiSpam'			=> $anti_spam_load
							)
						);
				}
				$this->context->smarty->assign(
					array(
						'Is_Subscribe'		=> in_array($this->context->cookie->id_customer, CommentNewsClass::listeCommentAbo($this->news->id)),
					)
				);

				$this->context->smarty->assign(
					array(
						'tpl_comment'		=> $this->context->smarty->fetch($this->getTemplatePathFix(Configuration::get('prestablog_theme').'_page-comment.tpl'))
					)
				);
			}

			/** commentaires Facebook **/
			if (Configuration::get('prestablog_commentfb_actif'))
			{
				$this->context->smarty->assign(
					array(
						'fb_comments_url' => $prestablog_current_url,
						'fb_comments_nombre' => (int)Configuration::get('prestablog_commentfb_nombre'),
						'fb_comments_apiId' => Configuration::get('prestablog_commentfb_apiId'),
						'fb_comments_iso' => Tools::strtolower($this->context->language->iso_code).'_'.Tools::strtoupper($this->context->language->iso_code),
					)
				);

				$this->context->smarty->assign(
					array(
						'tpl_comment_fb'	=> $this->context->smarty->fetch($this->getTemplatePathFix(Configuration::get('prestablog_theme').'_page-comment-fb.tpl'))
					)
				);
			}
			/** /commentaires Facebook **/
		}
		elseif ($this->assign_page == 2 && !trim(Tools::getValue('prestablog_search')))
		{
			/** affichage du slide **/
			if (Configuration::get('prestablog_pageslide_actif')
				&& !Tools::getValue('c')
				&& !Tools::getValue('y')
				&& !Tools::getValue('m')
				&& !Tools::getValue('p'))
			{
				if ($this->prestablog->slideNews())
				{
					$this->context->smarty->assign(
						array(
							'tpl_slide'		=> $this->context->smarty->fetch($this->getTemplatePathFix(Configuration::get('prestablog_theme').'_slide.tpl'))
						)
					);
				}
			}

			/** affichage de la description catégorie */
			if ((Configuration::get('prestablog_view_cat_desc')
					||	Configuration::get('prestablog_view_cat_thumb')
					||	Configuration::get('prestablog_view_cat_img'))
				&&	Tools::getValue('c')
				&&	!Tools::getValue('y')
				&&	!Tools::getValue('m')
				&&	!Tools::getValue('p'))
			{
				$obj_categorie = new CategoriesClass((int)Tools::getValue('c'), (int)$this->context->cookie->id_lang);

				if (file_exists(_PS_MODULE_DIR_.'prestablog/views/img/'.Configuration::get('prestablog_theme').'/up-img/c/'
					.$obj_categorie->id.'.jpg'))
					$obj_categorie->image_presente = true;
				else
					$obj_categorie->image_presente = false;

				$this->context->smarty->assign(
					array(
						'prestablog_categorie_obj'		=> $obj_categorie,
					)
				);

				$this->context->smarty->assign(
					array(
						'tpl_cat'		=> $this->context->smarty->fetch($this->getTemplatePathFix(Configuration::get('prestablog_theme').'_category.tpl'))
					)
				);
			}
		}

		/** pour toutes les listes qui ont out $this->news avant **/
		if ($this->assign_page == 2)
		{
			/** /affichage de la description catégorie */

			$this->pagination = PrestaBlog::getPagination(	$this->news_count_all,
				null,
				(int)Configuration::get('prestablog_nb_liste_page'),
				(int)Tools::getValue('start'),
				(int)Tools::getValue('p')
			);

			$prestablog_search_query = '';
			if (trim(Tools::getValue('prestablog_search')))
			{
				if ((int)Configuration::get('PS_REWRITING_SETTINGS') && (int)Configuration::get('prestablog_rewrite_actif'))
					$prestablog_search_query = '?prestablog_search='.trim(Tools::getValue('prestablog_search'));
				else
					$prestablog_search_query = '&prestablog_search='.trim(Tools::getValue('prestablog_search'));
			}

			$this->context->smarty->assign(
				array(
					'prestablog_search_query'	=> $prestablog_search_query,
					'prestablog_pagination'	=> $this->getTemplatePathFix(Configuration::get('prestablog_theme').'_page-pagination.tpl'),
					'Pagination'		=> $this->pagination,
					'news'				=> $this->news,
					'NbNews'				=> $this->news_count_all
				)
			);

			$this->context->smarty->assign(
				array(
					'tpl_all'			=> $this->context->smarty->fetch($this->getTemplatePathFix(Configuration::get('prestablog_theme').'_page-all.tpl'))
				)
			);
		}

		$this->setTemplate(Configuration::get('prestablog_theme').'_page.tpl');
	}

	private function voirListeCatMenu()
	{
		$liste_cat = CategoriesClass::getListe((int)$this->context->cookie->id_lang, 1);

		if (count($liste_cat) > 0)
		{
			$this->context->smarty->assign(
				array(
					'MenuCatNews' => $this->displayMenuCategories($liste_cat),
				)
			);

			$this->context->smarty->assign(
				array(
					'tpl_menu_cat'			=> $this->context->smarty->fetch($this->getTemplatePathFix(Configuration::get('prestablog_theme').'_page-menucat.tpl'))
				)
			);
		}
	}

	private function voirFiltrageSearch()
	{
		$liste_cat = CategoriesClass::getListe((int)$this->context->cookie->id_lang, 1);

		if (count($liste_cat) > 0)
		{
			$html_out = '';
			$categories = new CategoriesClass();
			$liste_categories = CategoriesClass::getListe((int)$this->context->language->id, 0);
			$html_out .= '	<div id="categoriesForFilter">'."\n";
			if (Tools::getValue('prestablog_search_array_cat') && count(Tools::getValue('prestablog_search_array_cat')) > 0)
			{
				foreach (Tools::getValue('prestablog_search_array_cat') as $cat_id)
				{
					$categorie_filtre = new CategoriesClass((int)$cat_id, (int)$this->context->cookie->id_lang);
					$html_out .= '<div class="filtrecat" rel="'.(int)$cat_id.'">'.$categorie_filtre->title
						.'<div class="deleteCat" rel="'.(int)$cat_id.'">X</div></div>'."\n";
				}
			}
			$html_out .= '	</div>'."\n";
			$html_out .= $categories->displaySelectArboCategories($liste_categories, 0, 0,
					$this->l('+ Select a category'), 'SelectCat', '', 0)."\n";

			$this->context->smarty->assign(
				array(
					'prestablog_search_query' => trim(Tools::getValue('prestablog_search')),
					'prestablog_search_array_cat' => (Tools::getValue('prestablog_search_array_cat') ? Tools::getValue('prestablog_search_array_cat') : null),
					'FiltrageCat' => $html_out,
				)
			);

			$this->context->smarty->assign(
				array(
					'tpl_filtre_cat'	=> $this->context->smarty->fetch($this->getTemplatePathFix(Configuration::get('prestablog_theme').'_page-filtrecat.tpl'))
				)
			);
		}
	}

	public function displayMenuCategories($liste, $first = true)
	{
		$html_out = '<ul>';
		if ($first && Configuration::get('prestablog_menu_cat_home_link'))
		{
			$prestablog = new PrestaBlog();
			$html_out .= '	<li>
								<a href="'.PrestaBlog::prestablogUrl(array()).'">
									'.(Configuration::get('prestablog_menu_cat_home_img') ?
					'<img src="'._MODULE_DIR_.'prestablog/views/img/home.gif" />' :
					$prestablog->message_call_back['Blog']).'
								</a>
							</li>';
			$first = false;
		}
		foreach ($liste as $value)
		{
			if (!Configuration::get('prestablog_menu_cat_blog_empty') && (int)$value['nombre_news_recursif'] == 0)
				$html_out .= '';
			else
			{
				$html_out .= '	<li>
									<a href="'.PrestaBlog::prestablogUrl(
						array(
							'c'		=> (int)$value['id_prestablog_categorie'],
							'titre'	=> ($value['link_rewrite'] != '' ? $value['link_rewrite'] : $value['title']),
						)
					).'" '.(count($value['children']) > 0 ?'class="mparent"':'').'>'.$value['title']
					.(Configuration::get('prestablog_menu_cat_blog_nbnews') && (int)$value['nombre_news_recursif'] > 0 ?
						'&nbsp;<span>('.(int)$value['nombre_news_recursif'].')</span>' : '').'</a>';

				if (count($value['children']) > 0)
					$html_out .= $this->displayMenuCategories($value['children'], $first);
				$html_out .= '	</li>';
			}
		}
		$html_out .= '</ul>';

		return $html_out;
	}
}

?>
