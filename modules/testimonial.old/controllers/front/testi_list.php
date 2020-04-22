<?php
class testimonialtesti_listModuleFrontController extends ModuleFrontController
{
public $ssl = true;
    /**
     * @see FrontController::initContent()
     */
public function initContent()
    {
        parent::initContent();
     
        global $smarty;
        $results = null;
        
        $cnt = Db::getInstance()->getValue('SELECT count(*) FROM `'._DB_PREFIX_.'testimonial` where teste_status = 1');
	$start = 0;
	$n = Configuration::get('testimonial_per_page') ? Configuration::get('testimonial_per_page') : 5;
	if(Tools::getValue('p'))
		$start = (Tools::getValue('p')-1)*$n;
	
	$results = Db::getInstance()->ExecuteS('
        SELECT * FROM `'._DB_PREFIX_."testimonial` where teste_status = 1
        ORDER BY teste_date DESC limit $start, $n");
		
	$nArray = intval(Configuration::get('testimonial_per_page')) != 5 ? array(intval(Configuration::get('testimonial_per_page')),5, 10, 15, 20) : array(10, 15, 20);
	asort($nArray);
	$n = abs(intval(Tools::getValue('n', $n)));
	$p = abs(intval(Tools::getValue('p', 1)));
	$range = 2; /* how many pages around page selected */
	
	if (!$n)
		$n = $nArray[0];
	if ($p < 0)
		$p = 0;
	$total=$cnt;
	if ($p > ($total / $n))
		$p = ceil($total / $n);
	$pages_nb = ceil($total / intval($n));
	
	$start = intval($p - $range);
	if ($start < 1)
		$start = 1;
	$stop = intval($p + $range);
	if ($stop > $pages_nb)
		$stop = intval($pages_nb);
          $smarty->assign(array('testimonials' => $results));
	$pagination_infos = array('pages_nb' => intval($pages_nb), 'p' => intval($p), 'n' => intval($n), 'nArray' => $nArray, 'range' => intval($range), 'start' => intval($start),'stop' => intval($stop));
	$smarty->assign($pagination_infos);
	$smarty->assign("requestPage","index.php?fc=module&module=testimonial&controller=testi_list");
	$smarty->assign('total',count($results));
    //listing testimonials here
    $this->setTemplate('displaytestimonial.tpl'); 
    }
}	
?>
