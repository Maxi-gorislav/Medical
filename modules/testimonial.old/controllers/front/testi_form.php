<?php
class testimonialtesti_formModuleFrontController extends ModuleFrontController
{
public $ssl = true;
   /**
    * @see FrontController::initContent()
    */
public function initContent()
   {
      parent::initContent();
      global $smarty;
      $teste_write = Configuration::get('teste_write');
      $smarty->assign('teste_write',$teste_write);
      $admin_approve=Configuration::get('teste_status');
      $smarty->assign('teste_status',$admin_approve);
      $smarty->assign('sucmsg','');

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
                    header("location:index.php?fc=module&module=testimonial&controller=testi_form&success");
               }
      }
      //success message passing here
      else if(isset($_REQUEST['success'])) 
      {
        $success="Successfully Submitted";
        $smarty->assign('sucmsg',$success);
      }
//display add testimonial form here 
$this->setTemplate('addtestimonial.tpl');// to display new form to write new testemonial 
   }
}
?>