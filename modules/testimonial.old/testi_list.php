<?php 
include_once('../../config/config.inc.php');
include_once('../../header.php');

	global $smarty;
        $results = null;
	
	$cnt = Db::getInstance()->getValue('SELECT count(*) FROM `'._DB_PREFIX_.'testimonial` where teste_status = 1');
	$start = 0;
	$n = Configuration::get('testimonial_per_page') ? Configuration::get('testimonial_per_page') : 5;
	if(Tools::getValue('p'))
		$start = (Tools::getValue('p')-1)*5;
	
	$results = Db::getInstance()->ExecuteS('
        SELECT * FROM `'._DB_PREFIX_."testimonial` where teste_status = 1
        ORDER BY teste_date DESC limit $start, $n");
		
	$nArray = intval(Configuration::get('testimonial_per_page')) != 5 ? array(intval(Configuration::get('testimonial_per_page')),5, 10, 15, 20) : array(10, 15, 20);
	asort($nArray);
	$n = abs(intval(Tools::getValue('n', intval(Configuration::get('testimonial_per_page')))));
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
	$smarty->assign("requestPage",_MODULE_DIR_.'testimonial/testi_list.php');
	$smarty->assign('total',count($results));
//compatible for both 1.4
	if (substr(_PS_VERSION_, 0, 3) === "1.4" )
	$smarty->assign('this_version', "1.4");
	$smarty->display(dirname(__FILE__).'/views/templates/front/displaytestimonial.tpl'); 
//$this->display(__FILE__,'displaytestimonial.tpl');
include_once(dirname(__FILE__).'/../../footer.php');

?>