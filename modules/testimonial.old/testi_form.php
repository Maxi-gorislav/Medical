<?php
include_once('../../config/config.inc.php');
include_once('../../header.php');                 

global $smarty;

$teste_write = Configuration::get('teste_write');
$smarty->assign('teste_write',$teste_write);

$admin_approve=Configuration::get('teste_status');
$smarty->assign('teste_status',$admin_approve);
$smarty->assign('sucmsg','');
//compatible for both 1.4 
      if (substr(_PS_VERSION_, 0, 3) === "1.4" )
	 $smarty->assign('this_version', "1.4");
if(isset($_REQUEST['testi_name']))
    {
       $testi_name= $_REQUEST['testi_name'];
       $testi_text=$_REQUEST[ 'testi_text' ];
       $testi_email=$_REQUEST['testi_email'];
       $testi_firstname=$_REQUEST['testi_firstname'];
       $testi_lastname=$_REQUEST['testi_lastname'];
       $testi_company=$_REQUEST['testi_company'];
       $testi_date=$_REQUEST['testi_date'];

            if($testi_name != "" &&  $testi_text != "" && $testi_email != "" && $testi_firstname != "" && $testi_lastname != "" &&  $testi_date != "" )
                {
                    Db::getInstance()->Execute("INSERT INTO "._DB_PREFIX_."testimonial (`teste_name`, `teste_text`, `teste_email`,`teste_firstname`,`teste_lastname`,`teste_company`,`teste_date`,`teste_status`) 
				       VALUES('".$testi_name."', '".$testi_text."','".$testi_email."', '".$testi_firstname."','".$testi_lastname."','".$testi_company."','".$testi_date."','$admin_approve')");
                    header("location:testi_form.php?success");
        
                }
    }
else if(isset($_REQUEST['success'])) 
   {
        $success="Successfully Submitted";
        $smarty->assign('sucmsg',$success);
   }
$smarty->display(dirname(__FILE__).'/views/templates/front/addtestimonial.tpl');// to display new form to write new testemonial 

include_once(dirname(__FILE__).'/../../footer.php');
 ?>