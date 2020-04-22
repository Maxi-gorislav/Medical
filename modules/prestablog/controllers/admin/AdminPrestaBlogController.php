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

class AdminPrestaBlogController extends ModuleAdminController
{
	public function initContent()
	{
		if (!$this->viewAccess())
		{
			$this->errors[] = Tools::displayError('You do not have permission to view this.');
			return;
		}

		$id_tab = (int)Tab::getIdFromClassName('AdminModules');
		$id_employee = (int)$this->context->cookie->id_employee;
		$token = Tools::getAdminToken('AdminModules'.$id_tab.$id_employee);
		Tools::redirectAdmin('index.php?controller=AdminModules&configure=prestablog&token='.$token);
	}
}
