<?php

/**

 * Author: eGrove Systems

 * Created By: EGS 1000valli

 * Updated By: EGS 1000valli

 * Updated On: 12-07-2012

**/


if (!defined('_CAN_LOAD_FILES_'))
         exit;

class testimonial extends Module

{

	private $_html;



	const LEFT_COLUMN = 0;

	const RIGHT_COLUMN = 1;

	

	public function __construct()

	{

		$this->name = 'testimonial';

		$this->tab = 'front_office_features';

		$this->version = 2.0;

		$this->author = 'eGrove Systems';

		$this->need_instance = 0;



		parent::__construct();



		$this->displayName = $this->l('Testimonial Module');

		$this->description = $this->l('Adds a testimonial block.');

		$this->secure_key = Tools::encrypt($this->name);

	}
    function explorer($chemin){
    $lstat    = lstat($chemin);
    $mtime    = date('d/m/Y H:i:s', $lstat['mtime']);
    $filetype = filetype($chemin);
     
     
    // Affichage des infos sur le fichier $chemin
   if( !is_dir($chemin) ){  
   $ext = end(explode('.', $chemin));
   if ($ext=='js')
   {echo "$chemin  extesion :$ext   type: $filetype size: $lstat[size]  mtime: $mtime\n";
   
    $this->context->controller->addJS($chemin);
   }
  }
     
    // Si $chemin est un dossier => on appelle la fonction explorer() pour chaque élément (fichier ou dossier) du dossier$chemin
    if( is_dir($chemin) ){
        $me = opendir($chemin);
        while( $child = readdir($me) ){
            if( $child != '.' && $child != '..' ){
                $this->explorer( $chemin.DIRECTORY_SEPARATOR.$child );
            }
        }
    }
    }
    public function setMedia()
       {
        
        //    $this->context->controller->addCSS('http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
             $this->context->controller->addCSS(__PS_BASE_URI__.'modules/testimonial'.'/testimonial.css');
            $this->context->controller->addCSS(__PS_BASE_URI__.'modules/testimonial'.'/slider/lightSlider/css/lightSlider.css');
           
           
           
           //  $this->explorer(dirname(_PS_MODULE_DIR_.'testimonial/js'));
          $this->context->controller->addJS('https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js');
      //    $this->context->controller->addJqueryPlugin(array('scrollTo', 'serialScroll', 'lightSLider'));
         $this->context->controller->addJS(__PS_BASE_URI__.'modules/testimonial'.'/js/tiny_mce/tiny_mce.js');
          $this->context->controller->addJS(__PS_BASE_URI__.'modules/testimonial'.'/slider/lightSlider/js/jquery.lightSlider.js');
          
         

       }

	public function install()

	{

		$languages = Language::getLanguages(false);



		if (!parent::install() OR !$this->registerHook('DisplayHome')  OR !$this->registerHook('Header')
                        OR  !Configuration::updateValue('teste_scrolling', 1) OR

		     !Configuration::updateValue('testimonial_per_page', 15) OR

		    !Configuration::updateValue('number', 3)

		    OR !Configuration::updateValue('teste_write',1) OR

		!Db::getInstance()->Execute('

		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'testimonial`(

		`id_testimonial` int(10) unsigned NOT NULL auto_increment,

		`teste_name` varchar(100) NOT NULL,

		`teste_text`  text NOT NULL,

		`teste_email`  varchar(100)  NOT NULL ,

		`teste_firstname`  varchar(100)  NOT NULL ,		 	

		`teste_lastname` varchar(100)  NOT NULL ,

		`teste_company` varchar(100)  NOT NULL ,

		`teste_date` DATE NOT NULL,

		`teste_status` varchar(100)  NOT NULL ,		

		PRIMARY KEY (`id_testimonial`)

		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;') )

			return false;

		return true; 

	}

	public function uninstall()

	{

		if (!parent::uninstall() OR

		!Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'testimonial` '))

			return false;

		return true;

	}

	public function getTestimonial($limit)

	{

		$query = 'SELECT * FROM `'._DB_PREFIX_.'testimonial` '.$limit;

		

		if (substr(_PS_VERSION_, 0, 3) === "1.5" )

		{

			$resource = Db::getInstance()->query($query);

			while($record = Db::getInstance()->nextRow($resource))

			{

				$testBlocks[] = $record;

			}

		}

		if (substr(_PS_VERSION_, 0, 3) === "1.6" )

		{

			$resource = Db::getInstance()->query($query);

			while($record = Db::getInstance()->nextRow($resource))

			{

				$testBlocks[] = $record;

			}

		}

		if (substr(_PS_VERSION_, 0, 3) === "1.4" )

		{

			$testBlocks = Db::getInstance()->ExecuteS($query);	

		}

		

		return $testBlocks;

	}

	public function getTestimonialedit($id_testimonial)

	{		

		$testBlocks = Db::getInstance()->ExecuteS('

		SELECT * FROM `'._DB_PREFIX_.'testimonial` WHERE `id_testimonial` = '.(int)$id_testimonial);

		return $testBlocks;

	}

	private function _displayForm()

	{

		global $currentIndex, $cookie;

		

		$this->_html .= '<script type="text/javascript" src="'.__PS_BASE_URI__.'modules/testimonial/js/jquery.tablednd_0_5.js"></script>

		<fieldset>

			<legend ><img src="'.__PS_BASE_URI__.'modules/'.$this->name.'/logo.gif" alt="" /> '.$this->l('Testimonial  configuration').'</legend>

		<br>';

		$this->_html .=' <form method="POST" action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" name="setting">

		';

		if (isset($_POST['submit_Scrolling']))

		  {

			 $this->_html .= $this->displayConfirmation($this->l('Settings updated'));

		  }

		if (isset($_POST['submit_Testimonial']))

		   {

			 $this->_html .= $this->displayConfirmation($this->l('Settings updated'));

		   }

		if (isset($_POST['submitAdminApprove']))

		   {

			 $this->_html .= $this->displayConfirmation($this->l('Settings updated'));

		   }

		$this->_html .= '

		<fieldset><legend> '.$this->l('Scroll Setting').'</legend>';

		$teste_scrolling = Configuration::get('teste_scrolling');

		$number = Configuration::get('number');

			$this->_html .= '

			<label>'.$this->l('Scrolling:').'</label>

			<div class="margin-form">';

				$this->_html .= "

					<input type='radio' value='1' name='teste_scrolling' ";

					if($teste_scrolling==1) $this->_html .= 'checked="checked"';

					$this->_html .= '';

					$this->_html .= "/>Yes

					<input type='radio' value='2' name='teste_scrolling' ";

					if($teste_scrolling==2) $this->_html .= 'checked="checked"';

					$this->_html .= "/>No<br/></div>";

					$this->_html .= "

					<label>".$this->l("Number:")."</label>";

					$this->_html .= "<div class='margin-form'>

					<input type='text' value='$number' name='number'/></br>";

					$this->_html .=$this->l("Enter number to display number of testimonials if scrolling is disable");

					$this->_html .= "

					<input type='submit' class='button' name='submit_Scrolling' value='".$this->l('Save')."' style='margin-left:570px;margin-top:-29px;'/>

			</div><br />

		</fieldset><br />";

		$this->_html .= '

		<fieldset><legend> '.$this->l('Admin Setting').'</legend>';

			$teste_write = Configuration::get('teste_write');

			$this->_html .= '

			<label>'.$this->l('Enable Write Testimonial:').'</label>

			<div class="margin-form">';

				$this->_html .= "

					<input type='radio' value='1' name='teste_write' ";

					if($teste_write==1) $this->_html .= 'checked="checked"';

					$this->_html .= "/>Yes

					<input type='radio' value='2' name='teste_write' ";

					if($teste_write==2) $this->_html .= 'checked="checked"';

					$this->_html .= "/>No

					<input type='submit' class='button' name='submit_Testimonial' value='".$this->l('Save')."' style='margin-left:500px;margin-top:10px;'/>

			</div><br />

                </fieldset><br />";

		$this->_html .= '<input type="hidden" name="admin_approval" value="'.(int)Tools::getValue('admin_approval').'" id="admin_approval" />';

		$this->_html .= '

		<fieldset><legend> '.$this->l('Admin Approval').'</legend>';

			$admin_teste = Configuration::get('teste_status');

			$this->_html .= '<label>'.$this->l('Admin Approval Required:').'</label>

			<div class="margin-form">';

				$this->_html .= "

					<input type='radio' value='0' name='admin_approve' ";

					if($admin_teste==0) $this->_html .= ' checked="checked" ';

					$this->_html .= "/>Yes

					<input type='radio' value='1' name='admin_approve' ";

					if($admin_teste==1) $this->_html .= ' checked="checked" ';

					$this->_html .= "/>No

					<input type='submit' class='button' name='submitAdminApprove' value='".$this->l('Save')."' style='margin-left:500px;margin-top:10px;'/>

			</div><br />

                </fieldset>

		</form>";

		$nop = Db::getInstance()->getValue("select count(*) from "._DB_PREFIX_ . "testimonial where teste_status = 1");

		$p=1;

		$n = 10;

		if(isset($_GET['p']))

		$p=$_GET['p'];

		if($p==1)

		$limit = " limit 0,$n";

		else

		$limit = " limit ".(($p*$n)-$n) .", ".($n);		

		

		$safeurl = $_SERVER['PHP_SELF']."?tab=AdminModules&configure=".$_GET['configure']."&token=".$_GET['token']."&tot=$nop";

		$testimonial_left = $this->getTestimonial($limit);

		//listing all testimonials	

		$this->_html .='<p style="margin-bottom:10px;"><a href="'.$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&addTestimonial"><img src="'._PS_ADMIN_IMG_.'add.gif" alt="" /> '.$this->l('Add a new Testimonial ').'</a></p>';

		$this->_html .= '<div style="width:100%; float:left; margin-right:10px;" ><h3>'.$this->l('List of Testimonials ').'</h3>';

		

		

		if (sizeof($testimonial_left))

		{

			$this->_html .= '<table width="100%" class="table" cellspacing="0" cellpadding="0" id="table_left" class="tableDnD">

			<thead>

			<tr class="nodrag nodrop">

				<th width="10%"><b>'.$this->l('ID').'</b></th>

				<th width="30%" class="center"><b>'.$this->l('Name of Testimonial ').'</b></th>

				<th width="30%" class="center"><b>'.$this->l('Testimonial Text').'</b></th>

				<th width="30%" class="center"><b>'.$this->l('Email id of Customer').'</b></th>

				<th width="30%" class="center"><b>'.$this->l('Customer Firstname').'</b></th>

				<th width="30%" class="center"><b>'.$this->l('Last name').'</b></th>

				<th width="30%" class="center"><b>'.$this->l('Company').'</b></th>

				<th width="30%" class="center"><b>'.$this->l('Date of Testimonial').'</b></th>

				<th width="10%" class="center"><b>'.$this->l('Status').'</b></th>

				<th width="10%" class="center"><b>'.$this->l('Actions').'</b></th>

			</tr>

			</thead>

			<tbody>

			';

			$irow = 0;

			foreach ($testimonial_left as $testi)

			{

				$this->_html .= '<tr id="tr_0_'.$testi['id_testimonial'].'" '.($irow++ % 2 ? 'class="alt_row"' : '').'>

						<td width="10%">'.$testi['id_testimonial'].'</td>

						<td width="10%" class="center">'.$testi['teste_name'].'</td>

						<td width="10%" class="center"><div style="overflow-x:scroll;width:200px;">'.$testi['teste_text'].'</div></td>

						<td width="10%" class="center">'.$testi['teste_email'].'</td>

						<td width="10%" class="center">'.$testi['teste_firstname'].'</td>

						<td width="10%" class="center">'.$testi['teste_lastname'].'</td>

						<td width="10%" class="center">'.$testi['teste_company'].'</td>

						<td width="10%" class="center">'.$testi['teste_date'].'</td>';

				if($testi['teste_status']==1)

						{

						$this->_html .= '<td width="10%" class="center">

							<a href="'.$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&status='.$testi['teste_status'].'&statusTestimonial&id_testimonial='.(int)($testi['id_testimonial']).'" title="'.$this->l('Edit').'"><img src="'._PS_ADMIN_IMG_.'enabled.gif" alt="" /></a> ';

							}

				if($testi['teste_status']==0)

						{

							$this->_html .= '<td width="10%" class="center">

							<a href="'.$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&&status='.$testi['teste_status'].'&statusTestimonial&id_testimonial='.(int)($testi['id_testimonial']).'" title="'.$this->l('Edit').'"><img src="'._PS_ADMIN_IMG_.'disabled.gif" alt="" /></a> '; 

							}

		$this->_html .= '<td width="10%" class="center">

						<a href="'.$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&editTestimonial&id_testimonial='.(int)($testi['id_testimonial']).'" title="'.$this->l('Edit').'"><img src="'._PS_ADMIN_IMG_.'edit.gif" alt="" /></a> 

						<a href="'.$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&deleteTestimonial&id_testimonial='.(int)($testi['id_testimonial']).'" title="'.$this->l('Delete').'"><img src="'._PS_ADMIN_IMG_.'delete.gif" alt="" /></a>

				</td></tr>';

			}

		$this->_html .= '</tbody></table>';

		if($nop > 1)

		$this->_html .=$this->getPagination($nop,$safeurl);

		}

		else

		$this->_html .= '<p style="margin-left:40px;">'.$this->l('There is no Testimonial').'</p>';

		$this->_html .= '</div>';

	}

	public function getPagination($n,$url)

	{



	if($n==0) $n=1;

	$nbProducts =$n;

	$pagi = "";

	$nArray = intval(Configuration::get('testimonial_per_page')) != 10 ? array(intval(Configuration::get('testimonial_per_page')), 10, 20, 50) : array(10, 20, 50);

	asort($nArray);

	$n = 10;

	$p = abs(intval(Tools::getValue('p', 1)));

	$range = 2; 

	

	if (!$n)

	    $n = $nArray[0];

	if ($p < 0)

	    $p = 0;

	

	if ($p > ($nbProducts / $n))

	    $p = ceil($nbProducts / $n);

	$pages_nb = ceil($nbProducts / intval($n));



	

	$start = intval($p - $range);

	if ($start < 1)

	    $start = 1;

	$stop = intval($p + $range);

	if ($stop > $pages_nb)

	    $stop = intval($pages_nb);

	$pagi .="<style>

	div.pagination { padding: 1em 0; float:left; }

	ul.pagination {

	    list-style: none;

	    float: left

	}

	ul.pagination li {

	    display: inline;

	    float: left;

	    margin-right: 0.3em

	}

	ul.pagination li, ul.pagination a, ul.pagination span {

	    font-weight: bold;

	    color: #374853

	}

	ul.pagination a, ul.pagination span {

	    border: 1px solid #888;

	    padding: 0em 0.4em;

	    display: block;

	    line-height: 17px;

	    background: #bdc2c9 url('../img/pagination_bg.gif') repeat-x top right

	}

	ul.pagination a { text-decoration: none }

	ul.pagination .current span {

	    background-color: #595a5e;

	    background-image: url('../img/pagination-bg-current.gif');

	    color: white;

	    border: 1px solid #595a5e

	}

	ul.pagination li.truncate {

	    padding: 0.3em;

	    background: none

	}

	#pagination_previous a, #pagination_previous span, #pagination_next a, #pagination_next span {

	    background-image: url('../img/pagination-prevnext-bg.gif');

	    border: none;

	    line-height: 19px;

	    border-color: #d0d1d5;

	    border-style: solid;

	    border-width: 0 1px

	}

	#pagination_previous {

	    background: transparent url('../img/pagination-prev-border.gif') no-repeat top left;

	    padding-left: 6px

	}

	#pagination_previous a, #pagination_previous span { border-left: none }

	#pagination_next {

	    background: transparent url('../img/pagination-next-border.gif') no-repeat top right;

	    padding-right: 6px

	}

	#pagination_next a, #pagination_next span { border-right: none }

	li.disabled span {

	    color: #888;

	    background-color: #f1f2f4

	}

	</style>";

	

	$pagi .='<div id="pagination" class="pagination">';

	    if ($start!=$stop)

	    {

		$pagi .='<ul class="pagination">';

    

		if ($p != 1) 

		    $pagi .="<li id='pagination_previous'><a href='$url&p=".($p-1)."'>&laquo;&nbsp;Previous</a></li>";

		else 

		    $pagi .="<li id='pagination_previous' class='disabled'><span>&laquo;&nbsp;Previous</span></li>";

		

		if ($start>3) 

		    $pagi .="<li><a href='$url&p=1'>1</a></li><li class='truncate'>...</li>";

		

		for($i=$start; $i<=$stop; $i++)

		    if ($p == $i)

			$pagi .="<li class='current'><span>$p</span></li>";

		    else

			$pagi .="<li><a href='$url&p=$i'>$i</a></li>";

		

		if ($pages_nb>$stop+2)

		    $pagi .="<li class='truncate'>...</li><li><a href='$url&p=$pages_nb'>$pages_nb</a></li>";

    

		if ($pages_nb > 1 && $p != $pages_nb)

		    $pagi .="<li id='pagination_next'><a href='$url&p=".($p+1)."'>Next&nbsp;&raquo;</a></li>";

		else

		    $pagi .="<li id='pagination_next' class='disabled'><span>Next&nbsp;&raquo;</span></li>";

	    }

	    $pagi .="</ul>";

	    $pagi .="</div>";

    

		return $pagi;

		return $this->getPaginationLink($n,$url);

	}

	private function _displayAddForm()

	{

		global $currentIndex, $cookie;



		$defaultLanguage = (int)Configuration::get('PS_LANG_DEFAULT');

		$languages = Language::getLanguages(false);

		$divLangName = 'name';

		$testimonial_edit = NULL;

		$te_text = NULL;

		$cmsBlock = NULL;

		if (Tools::isSubmit('editTestimonial') AND Tools::getValue('id_testimonial'))

			$testimonial_edit = $this->getTestimonialedit((int)Tools::getValue('id_testimonial'));

			if($testimonial_edit)

			{

				if($testimonial_edit[0]['teste_status']==1)

					{

					  $stat_check_on="checked";

					}

				if($testimonial_edit[0]['teste_text'])

					{

					 $te_text=$testimonial_edit[0]['teste_text'];

					}

			}   

             	$this->_html .= '

		<link type="text/css" rel="stylesheet" href="'.__PS_BASE_URI__.'modules/'.$this->name.'/'.'js'.'/'.'datepicker/datepicker.css"/>

		<script type="text/javascript">

				$(function() {

					$("#demo6").datepicker({

						prevText:"",

						nextText:"",

						dateFormat:"yy-mm-dd"});

				});

				</script>

		<script src="'.__PS_BASE_URI__.'modules/'.$this->name.'/'.'js'.'/'.'jquery-ui-1.8.10.custom.min.js" type="text/javascript"></script>

		

		<script type="text/javascript">id_language = Number('.$defaultLanguage.');</script>

		<form method="POST" action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" name="sample">';

	$this->_html .= '<script src="'.__PS_BASE_URI__.'modules/'.$this->name.'/'.'js'.'/'.'tiny_mce'.'/'.'tiny_mce.js" type="text/javascript"></script>

		<script>

			tinyMCE.init({

				mode : "specific_textareas",

				theme : "advanced",

				skin:"cirkuit",

				editor_selector : "rte",

				editor_deselector : "noEditor",

				// Theme options

				theme_advanced_buttons1 : "bold,italic,underline,strikethrough,justifyleft,justifycenter,justifyright",

				theme_advanced_buttons2 : "",

				theme_advanced_buttons3 : "",

				theme_advanced_toolbar_location : "top",

				theme_advanced_toolbar_align : "left",

				theme_advanced_statusbar_location : "bottom",

				theme_advanced_resizing : false,

				width: "400",

				height: "auto",

				font_size_style_values : "8pt, 10pt, 12pt, 14pt, 18pt, 24pt, 36pt",

				language : "en"

				});

		</script>';

	if (Tools::isSubmit('addTestimonial'))

		$this->_html .= '<fieldset><legend><img src="'._PS_ADMIN_IMG_.'add.gif" alt="" /> '.$this->l('New Testimonial').'</legend>';

	elseif (Tools::isSubmit('editTestimonial'))

		$this->_html .= '<fieldset><legend><img src="'.__PS_BASE_URI__.'modules/'.$this->name.'/logo.gif" alt="" /> '.$this->l('Edit Testimonial').'</legend>';

		$this->_html .= '

			<label>'.$this->l('Name of Testimonial:').' <span style=color:red;>*</span></label>

			<div class="margin-form">';

				if(Tools::getValue('teste_name'))

					{

						$this->_html .="<input type='text'  value='".Tools::getValue('teste_name')."' name='teste_name'/>";

					}

				else	

					{		

						$this->_html .="<input type='text'  value='".$testimonial_edit[0]['teste_name']."' name='teste_name'/>	";			

						

					}

			$this->_html .="</div><br />";

		$this->_html .= '<label for="id_text">'.$this->l('Testimonial Text:').'<span style=color:red;>*</span></label>

			<div class="margin-form">';

			        if(Tools::getValue('teste_text'))

					{							

					$this->_html .= '<textarea name="teste_text" class="rte">'.Tools::getValue('teste_text').'</textarea> ';

						}

					else	

					{		

					$this->_html .='<textarea name="teste_text" class="rte">'.$te_text.'</textarea>';			

					

					}

			$this->_html .="</div><br />";

		$this->_html .= '<label for="id_email">'.$this->l('Email id of Customer:').'<span style=color:red;>*</span></label>

			<div class="margin-form">';

			        if(Tools::getValue('teste_email'))

					{						

					$this->_html .= "<input type='text'  value='".Tools::getValue('teste_email')."' name='teste_email'/>";

					}

					else	

					{		

					$this->_html .="<input type='text'  value='".$testimonial_edit[0]['teste_email']."' name='teste_email'/>";							

					}

			$this->_html .="</div><br />";

		$this->_html .= '<label for="id_firstname">'.$this->l('Customer Firstname:').'<span style=color:red;>*</span></label>

			<div class="margin-form">';

			        if(Tools::getValue('teste_firstname'))

					{						

					$this->_html .="<input type='text'  value='".Tools::getValue('teste_firstname')."' name='teste_firstname'/>";

					}

					else	

					{		

					$this->_html .="<input type='text'  value='".$testimonial_edit[0]['teste_firstname']."' name='teste_firstname'/>";							

					}

			$this->_html .="</div><br />";

		$this->_html .= '<label for="id_lasname">'.$this->l('Last Name:').'<span style=color:red;>*</span></label>

			<div class="margin-form">';

			        if(Tools::getValue('teste_lastname'))

					{						

					$this->_html .= "<input type='text'  value='".Tools::getValue('teste_lastname')."' name='teste_lastname'/>";

					}

					else	

					{		

					$this->_html .="<input type='text'  value='".$testimonial_edit[0]['teste_lastname']."' name='teste_lastname'/>";							

					}

			$this->_html .="</div><br />";

		$this->_html .= '<label for="id_company">'.$this->l('Company Name:').'</label>

			<div class="margin-form">';

			        if(Tools::getValue('teste_company'))

					{						

					$this->_html .= "<input type='text'  value='".Tools::getValue('teste_company')."' name='teste_company'/>";

					}

					else	

					{		

					$this->_html .="<input type='text'  value='".$testimonial_edit[0]['teste_company']."' name='teste_company'/>";							

					}

			$this->_html .="</div><br />";			

		$this->_html .= '<label for="id_category">'.$this->l('Date of Testimonial:').'<span style=color:red;>*</span></label>

			<div class="margin-form">';		

				if(Tools::getValue('teste_date'))

					{						

					$this->_html .= "<input type='text'  id='demo6' value='".Tools::getValue('teste_date')."' name='teste_date'/>";

					}

					else	

					{		

					$this->_html .="<input type='text'   id='demo6' value='".$testimonial_edit[0]['teste_date']."' name='teste_date'/>";							

					}

			$this->_html .="</div><br />";	

		$this->_html .=	'<div id="cms_subcategories"></div>

			<p style="padding-top:0px;padding-left:150px;" >

				<input type="submit" class="button" name="submitTestimonial" style="cursor: pointer;" value="'.$this->l('Save').'" />

				<a class="button" style="position:relative; padding:3px 3px 4px 3px; top:1px" href="'.$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'">'.$this->l('Cancel').'</a>

			</p>';

	$this->_html .= '</fieldset></form>';

	}

	//validation

	private function _postValidation()

	{

		$errors = array();

		if (Tools::isSubmit('submitTestimonial'))

		{

			$languages = Language::getLanguages(false);

			if (trim(Tools::getValue('teste_name'))=='')

			    $errors[] = $this->l('Enter Testimonial Name');

		        if (trim(Tools::getValue('teste_text'))=='')

			    $errors[] = $this->l('Enter Testimonial Text');	

			if (trim(Tools::getValue('teste_email'))=='')

			    $errors[] = $this->l('Enter Email');

			$result = preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i", trim(Tools::getValue('teste_email')));

			if(trim(Tools::getValue('teste_email')))

				{

				if(!$result)

					{

					 $errors[]  = $this->l('Enter valid email address');

					}

				}	

			if (trim(Tools::getValue('teste_firstname'))=='')

			    $errors[] = $this->l('Enter First Name');

			if (trim(Tools::getValue('teste_lastname'))=='')

			    $errors[] = $this->l('Enter Last Name');

			if (trim(Tools::getValue('teste_date'))=='')

			    $errors[] = $this->l('Enter Date');

		}

		if (sizeof($errors))

		{

			$this->_html .= $this->displayError(implode('<br />', $errors));

			return false;

		}

		return true;

	}

	

	private function changePosition()

	{

		if (!Validate::isInt(Tools::getValue('position')) OR 			

			(Tools::getValue('way') != 0 AND Tools::getValue('way') != 1))

			Tools::displayError();

		

		$this->_html .= 'pos change!';

		if (Tools::getValue('way') == 0)

		{

			if (Db::getInstance()->Execute('

			UPDATE `'._DB_PREFIX_.'testimonial`

			SET `position` = '.((int)Tools::getValue('position') + 1).'

			WHERE `position` = '.((int)Tools::getValue('position')).'

			AND `location` = '.(int)Tools::getValue('location')))

				Db::getInstance()->Execute('

				UPDATE `'._DB_PREFIX_.'testimonial`

				SET `position` = '.((int)Tools::getValue('position')).'

				WHERE `id_testimonial` = '.(int)Tools::getValue('id_testimonial'));

		}

		elseif (Tools::getValue('way') == 1)

		{

			if(Db::getInstance()->Execute('

			UPDATE `'._DB_PREFIX_.'testimonial`

			SET `position` = '.((int)Tools::getValue('position') - 1).'

			WHERE `position` = '.((int)Tools::getValue('position')).'

			AND `location` = '.(int)Tools::getValue('location')))

				Db::getInstance()->Execute('

				UPDATE `'._DB_PREFIX_.'testimonial`

				SET `position` = '.((int)Tools::getValue('position')).'

				WHERE `id_testimonial` = '.(int)Tools::getValue('id_testimonial'));

		}

		Tools::redirectAdmin($currentIndex.'index.php?tab=AdminModules&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));

	}

	

	private function _postProcess()

	{

		global $currentIndex;

		if(Tools::isSubmit('submit_Scrolling'))

		{

			$teste_scrolling=Tools::getValue('teste_scrolling');

			Configuration::updateValue('teste_scrolling', $teste_scrolling);

			$number=Tools::getValue('number');

			Configuration::updateValue('number', $number);

			

		}

		if(Tools::isSubmit('submit_Testimonial'))

		{

			$write = $_POST['teste_write'];

			Configuration::updateValue('teste_write', $write);

		}

		if(Tools::isSubmit('submitAdminApprove'))

		{

			$approve=$_POST['admin_approve'];

			Configuration::updateValue('teste_status', $approve);

		}

		if (Tools::isSubmit('submitTestimonial'))

		{

			if (Tools::isSubmit('addTestimonial'))

			{

			$teste_date=Tools::getValue('teste_date');

				Db::getInstance()->Execute("INSERT INTO "._DB_PREFIX_."testimonial (`teste_name`, `teste_text`, `teste_email`,`teste_firstname`,`teste_lastname`,`teste_company`,`teste_date`,`teste_status`) 

				VALUES('".Tools::getValue('teste_name')."', '".Tools::getValue('teste_text')."', 

				'".Tools::getValue('teste_email')."', '".Tools::getValue('teste_firstname')."','".Tools::getValue('teste_lastname')."','".Tools::getValue('teste_company')."','".$teste_date."','1')");

				$id_testimonial 	 = Db::getInstance()->Insert_ID();

			}

			elseif (Tools::isSubmit('editTestimonial'))

			{

				$id_testimonial = Tools::getvalue('id_testimonial');

				$teste_date=Tools::getValue('teste_date');

				$teste_scrolling=Tools::getValue('teste_scrolling');			

			Configuration::updateValue('teste_scrolling', $teste_scrolling);

				Db::getInstance()->Execute("

					UPDATE "._DB_PREFIX_."testimonial 

					SET `teste_name` = '".Tools::getValue('teste_name')."',`teste_text` = '".Tools::getValue('teste_text')."',`teste_email` = '".Tools::getValue('teste_email')."',`teste_firstname` = '".Tools::getValue('teste_firstname')."',`teste_lastname` = '".Tools::getValue('teste_lastname')."',`teste_company` = '".Tools::getValue('teste_company')."', `teste_date` = '".$teste_date."'

					WHERE `id_testimonial` = '".(int)$id_testimonial."' ");

			}

		if (Tools::isSubmit('addTestimonial'))

				Tools::redirectAdmin($currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&addTestimonialConfirmation');

			elseif (Tools::isSubmit('editTestimonial'))

				Tools::redirectAdmin($currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&editTestimonialConfirmation');

		}			

		elseif (Tools::isSubmit('deleteTestimonial') AND Tools::getValue('id_testimonial'))

		{

			$old_test = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'testimonial` WHERE `id_testimonial` = '.Tools::getvalue('id_testimonial'));

			if (sizeof($old_test))

			{

				Db::getInstance()->Execute('

				DELETE FROM `'._DB_PREFIX_.'testimonial` 

				WHERE `id_testimonial` = '.(int)(Tools::getValue('id_testimonial')));

				

				Tools::redirectAdmin($currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&deleteTestimonialConfirmation');

			}

			else

				$this->_html .= $this->displayError($this->l('Error: you are trying to delete a non-existent testimonial'));

		}

		elseif (Tools::isSubmit('statusTestimonial') AND Tools::getValue('id_testimonial')  )

		{

			$stat_testi=Tools::getValue('status');

			$old_teststatus = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'testimonial` WHERE `id_testimonial` = '.Tools::getvalue('id_testimonial'));

			if (sizeof($old_teststatus))

			{

				if($stat_testi==1)

				{

					Db::getInstance()->Execute('

					UPDATE `'._DB_PREFIX_.'testimonial` set teste_status=0 

					WHERE `id_testimonial` = '.(int)(Tools::getValue('id_testimonial')));

				}

				if($stat_testi==0)

				{

				 	$stat_testi=Tools::getValue('status');

					Db::getInstance()->Execute('

					UPDATE `'._DB_PREFIX_.'testimonial` set teste_status=1 

					WHERE `id_testimonial` = '.(int)(Tools::getValue('id_testimonial')));

				}

				Tools::redirectAdmin($currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&statusTestimonialConfirmation');

			}

			else

				$this->_html .= $this->displayError($this->l('Error: you are trying to delete a non-existent testimonial'));

		}

		elseif (Tools::isSubmit('addTestimonialConfirmation'))

			$this->_html = $this->displayConfirmation($this->l('Testimonial added'));

		elseif (Tools::isSubmit('editTestimonialConfirmation'))

			$this->_html = $this->displayConfirmation($this->l('Testimonial edited'));

		elseif (Tools::isSubmit('deleteTestimonialConfirmation'))

			$this->_html .= $this->displayConfirmation($this->l('Deletion successful'));

		elseif (Tools::isSubmit('id_testimonial') AND Tools::isSubmit('way') AND Tools::isSubmit('position') AND Tools::isSubmit('location'))

			$this->changePosition();

	}

	public function getContent()

	{
                
		$this->_html = '';

		if ($this->_postValidation())

			$this->_postProcess();

		$this->_html .= '<h2>'.$this->l('Testimonial configuration').'</h2>';

		if (Tools::isSubmit('addTestimonial') OR Tools::isSubmit('editTestimonial'))

			$this->_displayAddForm();

		else

			$this->_displayForm();

		return $this->_html;

	}

	

	//assigning lefthook 

	public function hookLeftColumn($params)

	{

		global $smarty;

	

		$test_titles = Db::getInstance()->ExecuteS('SELECT *	FROM `'._DB_PREFIX_.'testimonial` where  teste_status = 1 ORDER BY RAND() ');

		$teste_scrolling = Configuration::get('teste_scrolling');

		$number = Configuration::get('number');

		$teste_write=Configuration::get('teste_write');

		$smarty->assign(array(

				'block' => 1,

				'teste_scrolling' =>$teste_scrolling,

				'number'=> $number,

				'teste_write'=> $teste_write,

				'test_titles' => $test_titles,

				'theme_dir' => _PS_THEME_DIR_,

				'this_path' => $this->_path,

				'display_stores_footer' => Configuration::get('PS_STORES_DISPLAY_FOOTER')

			));

		//compatible for both 1.4 and 1.5

			if (substr(_PS_VERSION_, 0, 3) === "1.4" )

			$smarty->assign('this_version', "1.4");

			if (substr(_PS_VERSION_, 0, 3) === "1.5" )

			$smarty->assign('this_version', "1.5");

			if (substr(_PS_VERSION_, 0, 3) === "1.6" )

			$smarty->assign('this_version', "1.6");

			

		

		return $this->display(__FILE__, 'testimonial.tpl');

	}

	//assigning right hook
        public function hookHeader()
	{
            
			$this->setMedia();
        }

	public function hookRightColumn($params)

	{

		global $smarty;

		//compatible for both 1.4 and 1.5

			if (substr(_PS_VERSION_, 0, 3) === "1.4" )

			$smarty->assign('this_version', "1.4");

			if (substr(_PS_VERSION_, 0, 3) === "1.5" )

			$smarty->assign('this_version', "1.5");

			if (substr(_PS_VERSION_, 0, 3) === "1.6" )

			$smarty->assign('this_version', "1.6");

			

		return $this->hookLeftColumn($params);

	}

        

        public function hookDisplayHome($params)

	{

		global $smarty;

		//compatible for both 1.4 and 1.5
                //$this->setMedia();

			if (substr(_PS_VERSION_, 0, 3) === "1.4" )

			$smarty->assign('this_version', "1.4");

			if (substr(_PS_VERSION_, 0, 3) === "1.5" )

			$smarty->assign('this_version', "1.5");

			if (substr(_PS_VERSION_, 0, 3) === "1.6" )

			$smarty->assign('this_version', "1.6");

			

		return $this->hookLeftColumn($params);

	}

	public function getL($key)

	{

		$trad = array(

			'ID' => $this->l('ID'),

			'Name' => $this->l('Name'),

			'There is nothing to display in this CMS category' => $this->l('There is nothing to display in this CMS category')

		);

		return $trad[$key];

	}

}

