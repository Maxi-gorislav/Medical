<?php


class ModuleFrontControllerCore extends FrontController
{
	/**
	 * @var Module
	 */
	public $module;
	public $context;

	/** 
	 * for Frontcontroler presta 1.4 
	 */
	public function preProcess() {
		$this->setMedia();
		$this->postProcess();	
	}
	
	/* Simulate addJS, in PS 1.4 call the funtion in Tools.
	* In PS under 1.3, the files will have to be added in the tpl file
	* we assign a variable $modulename_js which will then be printed in the tpl file
	*/

	public function addJS($js_uri)
	{
		if ((float)substr(_PS_VERSION_,0,3) > 1.3)
			Tools::addJS($js_uri);
		else
		{
			$existing_files = $this->context->smarty->get_template_vars($this->module->name.'_js');
			$existing_files .= "<script type=\"text/javascript\" src=\"$js_uri\"></script>\n";
			$this->context->smarty->assign($this->module->name.'_js', $existing_files);
		}
	}

	/* Simulate addCSS, in PS 1.4 call the funtion in Tools.
	* In PS under 1.3, the files will have to be added in the tpl file
	* we assign a variable $modulename_css which will then be printed in the tpl file
	*/

	public function addCSS($css_uri, $css_media_type = 'all')
	{
		if ((float)substr(_PS_VERSION_,0,3) > 1.3)
			Tools::addCSS($css_uri, $css_media_type);
		else
		{
			$existing_files = $this->context->smarty->get_template_vars($this->module->name.'_css');
			$existing_files .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$css_uri\" media=\"$css_media_type\" />\n";
			$this->context->smarty->assign($this->module->name.'_css', $existing_files);
		}
	}
	
	public function __construct($moduleName)
	{
		$this->controller_type = 'modulefront';
		$this->context = PrestoChangeoContext::getContext();
		$this->module = Module::getInstanceByName($moduleName);

		if (!$this->module->active)
			Tools::redirect('index');
		// $this->page_name = 'module-'.$this->module->name.'-'.Dispatcher::getInstance()->getController();
		parent::__construct();
	}

	/**
	 * Assign module template
	 *
	 * @param string $template
	 */
	public function setTemplate($template)
	{
		if (floatval(substr(_PS_VERSION_,0,3)) > 1.3) {
			if (Tools::file_exists_cache(_PS_THEME_DIR_.'modules/'.$this->module->name.'/'.$template))
				$this->template = _PS_THEME_DIR_.'modules/'.$this->module->name.'/'.$template;
			elseif (Tools::file_exists_cache($this->getTemplatePath().$template))
				$this->template = $this->getTemplatePath().$template;
			else
				throw new PrestaShopException("Template '$template'' not found");
		} else {
			if (file_exists(_PS_THEME_DIR_.'modules/'.$this->module->name.'/'.$template))
				$this->template = _PS_THEME_DIR_.'modules/'.$this->module->name.'/'.$template;
			elseif (file_exists($this->getTemplatePath().$template))
				$this->template = $this->getTemplatePath().$template;
			else
				throw new Exception("Template '$template'' not found");
		}
		
	}

	public function displayContent()
	{
		if (floatval(substr(_PS_VERSION_,0,3)) < 1.5) {
			PrestoChangeoContext::getContext()->smarty->display($this->template);
		} else {
			self::$smarty->display($this->template);
		}
	}

	/**
	 * Get path to front office templates for the module
	 *
	 * @return string
	 */
	public function getTemplatePath()
	{
		return _PS_MODULE_DIR_.$this->module->name.'/views/templates/front/';
	}
}

class ModuleFrontController extends ModuleFrontControllerCore {}
