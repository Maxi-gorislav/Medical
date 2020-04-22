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

class Dispatcher extends DispatcherCore
{
	protected function loadRoutes()
	{
		foreach (Module::getModulesDirOnDisk() as $module)
			if (Module::isInstalled($module))
			{
				$content = Tools::file_get_contents(_PS_MODULE_DIR_.$module.'/'.$module.'.php');
				$pattern = '#\W((abstract\s+)?class|interface)\s+(?P<classname>'.basename($module, '.php')
					.'(Core)?)(\s+extends\s+[a-z][a-z0-9_]*)?(\s+implements\s+[a-z][a-z0-9_]*(\s*,\s*[a-z][a-z0-9_]*)*)?\s*\{#i';
				if (preg_match($pattern, $content, $m))
				{
					$class_name = $m['classname'];
					require_once( _PS_MODULE_DIR_.$module.'/'.$module.'.php' );
					if (method_exists($class_name, 'hookModuleRoutes'))
					{
						$module_route = new $class_name;
						foreach ($module_route->hookModuleRoutes() as $routes)
							array_push($this->default_routes, $routes);
					}
				}
			}
		parent::loadRoutes();
	}
}
