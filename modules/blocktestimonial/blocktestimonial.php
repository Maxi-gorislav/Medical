<?php

class blockTestimonial extends Module
{
	private $_html;

	public function __construct()
	{
		$this->name = 'blocktestimonial';
		$this->tab = 'front_office_features';
		$this->version = '1.4.2';

		parent::__construct();
		$this->displayName = $this->l('Customer Testimonials');
		$this->description = $this->l('Creates a customer testimonials page');
		$this->confirmUninstall = $this->l('Uninstalling will delete the actual testimonials as well as the module. You can use the backup function to save them first (Found under the >>Configure option) Are you sure?');
	}


	 	public function install()
 	{
 	 	if (parent::install() == false 
                        OR $this->registerHook('leftColumn') == false
						OR $this->registerHook('header') == false
                        OR !Configuration::updateValue('TESTIMONIAL_CAPTCHA', '0')
			OR !Configuration::updateValue('TESTIMONIAL_PERPAGE', '10')
                        OR !Configuration::updateValue('TESTIMONIAL_CAPTCHA_PUB', '12345')
                        OR !Configuration::updateValue('TESTIMONIAL_CAPTCHA_PRIV', '678910')
                        OR !Configuration::updateValue('TESTIMONIAL_DISPLAY_IMG', '0')
                        OR !Configuration::updateValue('TESTIMONIAL_MAX_IMG', '80')
                        )

 	 		return false;
 	 	return Db::getInstance()->Execute('
		CREATE TABLE '._DB_PREFIX_.'testimonials (
			`testimonial_id` int(5) NOT NULL AUTO_INCREMENT,
			`testimonial_title` varchar(64) NOT NULL DEFAULT \'My Testimonial\',
			`testimonial_submitter_name` varchar(50) NOT NULL DEFAULT \'anonymous\',
			`testimonial_main_message` text NOT NULL,
			`testimonial_img` varchar(250) DEFAULT NULL,
			`date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		     `status` char(8) NOT NULL DEFAULT \'Disabled\',
			PRIMARY KEY(`testimonial_id`)
		) ENGINE=MyISAM default CHARSET=utf8');
  	}

	public function uninstall()
        {
        if (!parent::uninstall()
        OR !Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'testimonials`;'))
        return false;
        Configuration::deleteByName('TESTIMONIAL_CAPTCHA');
	Configuration::deleteByName('TESTIMONIAL_PERPAGE');
        Configuration::deleteByName('TESTIMONIAL_CAPTCHA_PUB');
        Configuration::deleteByName('TESTIMONIAL_CAPTCHA_PRIV');
        Configuration::deleteByName('TESTIMONIAL_DISPLAY_IMG');
        Configuration::deleteByName('TESTIMONIAL_MAX_IMG');

        return true;
	   }

	    /**
		 Function for cleaning text input fields
		**/

        function cleanInput ($text) {  //clean the inputs
				$text = trim($text);
				$text = strip_tags($text);
				$text = htmlspecialchars($text, ENT_QUOTES);

			return ($text); //output clean text
		}
		
		
        /**
		 Function for validating text input fields
		**/

		function field_validator($field_descr, $field_data, $min_length="", $max_length="", $field_required=1) {
				$errors = array();
				if(!$field_data && !$field_required){ return; }

				# check for required fields
				if ($field_required && empty($field_data)) {
				return false;
     			}

				# field data min length checking:
				if ($min_length) {
					if (strlen($field_data) < $min_length) {
						return false;
					}
				}

				 # field data max length checking:
				if ($max_length) {
					if (strlen($field_data) > $max_length) {
						return false;
					}
               }

	   else
	   
		echo "ok";
		return true;
	
	}
	
	/** Function for check file ext **/
	public function checkImageExt() {  
             $allowedextlist = array('jpg', 'png', 'jpeg');
	     $notallowedextlist = array('php', 'php3', 'php4', 'phtml','exe');
             $fileName = strtolower($_FILES['testimonial_img']['name']); //check the correct extension
             if(!in_array(end(explode('.', $fileName)), $allowedextlist))	
				 {
				 echo "false";
                                 return false;
				 }
		
	        return true;
              
		}		
	
	/** Function for uploading file **/
	
	public function uploadImage(){
	
		      $uploadpath = "upload";
				
	               //upload the files
			move_uploaded_file($_FILES["testimonial_img"]["tmp_name"],
			_PS_ROOT_DIR_.DIRECTORY_SEPARATOR.$uploadpath.DIRECTORY_SEPARATOR.$_FILES["testimonial_img"]["name"]);

                        //store the path for displaying the image
			$testimonial_img = $uploadpath ."/".$_FILES["testimonial_img"]["name"];
                        $testimonial_img = addslashes($testimonial_img);

                       
                        return $testimonial_img; //return image path 
			 
	}


        /** Function for checking file size **/

        public function checkfileSize() {
           $MAX_SIZE = (Configuration::get('TESTIMONIAL_MAX_IMG') * 1024);
           
           if ( $_FILES["testimonial_img"]["size"] > $MAX_SIZE )
           {
               return false;
           }
           else return true;
        }



	/** Function for writing testimonials **/
	
        public function writeTestimonial($testimonial_title,$testimonial_submitter_name,$testimonial_main_message,  $testimonial_img)
        {
         $db = Db::getInstance();
		$result = $db->Execute('
		INSERT INTO `'._DB_PREFIX_.'testimonials`
		( `testimonial_title`, `testimonial_submitter_name`, `testimonial_main_message`, `testimonial_img`)
		VALUES
		("'.$testimonial_title.'"
		,"'.$testimonial_submitter_name.'"
		,"'.$testimonial_main_message.'"
                ,"'.$testimonial_img.'"
                )');
		return;
        }


	public function displayTestimonials()
	{
		$output = array(); // create an array named $output to store our testimonials. We will read the from the DB
		$db = Db::getInstance(); // create and object to represent the database
		$result = $db->Execute('SELECT COUNT(*) FROM `'._DB_PREFIX_.'testimonials`;'); // Query to count the total number of testimonials
		$rows = mysql_fetch_row($result); 
		$numrows = $rows[0]; // assign the number of rows to a variable $numrows
                $nextpage = "";
		$prevpage = "";
		// number of rows to show per page
		$rowsperpage = Configuration::get('TESTIMONIAL_PERPAGE');

		// find out total pages
		$totalpages = ceil($numrows / $rowsperpage);

				// get the current page or set a default
				if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage'])) {
				   // cast var as int
				   $currentpage = (int) $_GET['currentpage'];
				} else {
				   // default page num
				   $currentpage = 1;
				} // end if

				// the offset of the list, based on current page 
				$offset = ($currentpage - 1) * $rowsperpage;
				
			   // get the info from the db 

				$result = $db->Execute('SELECT * FROM `'._DB_PREFIX_.'testimonials` WHERE status = "enabled" ORDER BY testimonial_id DESC LIMIT '.$offset.', '.$rowsperpage.';'); // Query to return the testimonials on that page
				// while there are rows to be fetched...
				  while ($row = mysql_fetch_assoc($result)) 
				  {
					  $results[] = $row;
				  } // end while
				  
	 /****** pagination links ******/
		// range of num links to show
			$range = 3;

			// if not on page 1, don't show back links
			if ($currentpage > 1) {
			   // show << link to go back to page 1
			   
			   // get previous page num
			   $prevpage = $currentpage - 1;
			   // show < link to go back to 1 page
			} // end if 

			// if not on last page, show forward and last page links        
			if ($currentpage != $totalpages) {
			   // get next page
			   $nextpage = $currentpage + 1;
	
		} // end if
		/****** end pagination links ******/
		
			global $smarty; //assign results to the smarty for displaying
				  $smarty->assign(array(
				  'http_host' => $_SERVER['HTTP_HOST'],
			          'base_dir'=> __PS_BASE_URI__,
                                  'testimonials' => $results,
                                  'currentpage' => $currentpage,
                                  'prevpage' => $prevpage,
                                  'nextpage' => $nextpage,
                                  'totalpages' => $totalpages
				  ));
				  
		  return $this->display(__FILE__, 'displaytestimonials.tpl');
	 }

     public function displayrandomTestimonial()
     {
      $db = Db::getInstance(); // create and object to represent the database
      $result = $db->Execute('SELECT * FROM `'._DB_PREFIX_.'testimonials` where status = "enabled" ORDER BY RAND() LIMIT 1;');
      $row = mysql_fetch_assoc($result);
      return $row;
     }

	function hookLeftColumn()  //display a block link to the front office testimonials page
	{
            $randomTestimonial = $this->displayrandomTestimonial();
            global $smarty;
            	$smarty->assign(array(
			'this_path' => $this->_path,
                        'randomTestimonialid' => $randomTestimonial['testimonial_id'],
                        'randomTestimonialtxt' => $randomTestimonial['testimonial_main_message'],
		));
		 return $this->display(__FILE__, 'blocktestimonial.tpl');
	}

	function hookRightColumn()  //display a block link to the front office testimonials page Same as hookLeftColumn
	{
		return $this->hookLeftColumn();
	}

	
	public function hookHeader()
	{
		if (Configuration::get('PS_CATALOG_MODE'))
			return;

		Tools::addCSS(($this->_path).'css/testimonial.css', 'all');
		
	}
	
	
	
        /* Module Config Page Functions	*/

	/* This function administers the testimonials module
	 *
	 *
	 */
	 

	public function getContent()
	{
	  if (isset($_POST['Enable']) OR isset($_POST['Disable']) OR isset($_POST['Delete']) OR isset($_POST['Update'])  OR isset($_POST['submitConfig']) OR isset($_POST['Backup']))
         {
             $this->_postProcess();
         }

          // echo var_dump($_POST);
		$this->_html = $this->_html.'<h2>'.$this->displayName.' v.'.$this->version.'</h2><h3><a style="color:red;margin:5px;" target="_blank" href="http://www.techietips.net/prestashop-testimonials-manager.html">'.$this->l('>> Read Documentation on this module <<').'</a></h3>';
                $this->_html .= $this->_displayConfigForm();
                $this->_html .= $this->getadminTestimonials();

                return $this->_html;
        }

      private function _postProcess()
     {

          if (Tools::isSubmit('submitConfig'))
            {
                $reCaptcha = Tools::getValue('reCaptcha');
                if ($reCaptcha != 0 AND $reCaptcha != 1)
                        $output .= '<div class="alert error">'.$this->l('recaptcha : Invalid choice.').'</div>';
                else
                {
                        Configuration::updateValue('TESTIMONIAL_CAPTCHA', intval($reCaptcha));
                }

         
               $recaptchaPub = strval(Tools::getValue('recaptchaPub'));

                	if (!$recaptchaPub OR empty($recaptchaPub))
				$this->_html .= '<div class="alert error">'.$this->l('Please enter your public key').'</div>';
			else
                        {
                            Configuration::updateValue('TESTIMONIAL_CAPTCHA_PUB', strval($recaptchaPub));

                        }

               $recaptchaPriv = strval(Tools::getValue('recaptchaPriv'));
               
                	if (!$recaptchaPriv OR empty($recaptchaPriv))
				$this->_html .= '<div class="alert error">'.$this->l('Please enter your private key').'</div>';
			else
                        {
                            Configuration::updateValue('TESTIMONIAL_CAPTCHA_PRIV', strval($recaptchaPriv));

                        }
						
		$perPage = strval(Tools::getValue('perPage'));

                	if (!$perPage OR empty($perPage))
				$this->_html .= '<div class="alert error">'.$this->l('Please enter the amount of testimonials per page').'</div>';
			else
                        {
                            Configuration::updateValue('TESTIMONIAL_PERPAGE', strval($perPage));
                        }


               $displayImage = strval(Tools::getValue('displayImage'));

                	if ($displayImage != 0 AND $displayImage != 1)
				$this->_html .= '<div class="alert error">'.$this->l('Please select whether to allow users to upload the Testimonial Image').'</div>';
			else
                        {
                            Configuration::updateValue('TESTIMONIAL_DISPLAY_IMG', strval($displayImage));
                        }



                 $maxImagesize = strval(Tools::getValue('maximagesize'));

                echo $maxImagesize;
                 if (!$maxImagesize OR empty($maxImagesize))
				$this->_html .= '<div class="alert error">'.$this->l('Please Enter the maximum image file upload size').'</div>';

                 if (!is_numeric($maxImagesize))
                     $this->_html .= '<div class="alert error">'.$this->l('Please Enter the maximum image file as a number only').'</div>';
                 else
                        {
                            Configuration::updateValue('TESTIMONIAL_MAX_IMG', strval($maxImagesize));
                        }

               }

         if (isset($_POST['Backup']))
            {
                $result = Db::getInstance()->Execute("SELECT * from `"._DB_PREFIX_."testimonials`");
                if  (mysql_num_rows($result) > 0 ){
                        for ($i = 0; $i < mysql_num_rows($result); $i++) {
                            $dataArray[$i] = mysql_fetch_assoc($result);
                        }
                        $filename = dirname(__FILE__).'/backup.csv';
                        $fp = fopen($filename, 'w');
                        foreach ($dataArray as $line) {
                        fputcsv($fp,$line);
                        }
                        fclose($fp);
                        $this->_html .= $this->displayConfirmation(
                                        $this->l('The .CSV file has been successfully exported
                                        ') );
                     }
                else {
                    $this->_html .= $this->displayError(
                                        $this->l('No Testimonials to Backup
                                        ') );
                }
            }

          if (isset($_POST['Delete']))
         {
             foreach($_POST['moderate'] as $check => $val)
             {
                 $deleted=Db::getInstance()->Execute('
                 DELETE FROM `'._DB_PREFIX_.'testimonials`
                 WHERE testimonial_id =  "'.($val).'"
                 ');
             }
           }

         if (isset($_POST['Enable']))
                 {
                     foreach($_POST['moderate'] as  $check => $val)
                     {
                         $enabled=Db::getInstance()->Execute('
                         UPDATE `'._DB_PREFIX_.'testimonials`
                         SET `status` = "Enabled"
                         WHERE `testimonial_id` = "'.($val).'"');
                     }
                 }

       if (isset($_POST['Disable']))
             {
 			 
		 foreach($_POST['moderate'] as  $check => $val)
                    {
             		
			$disabled=Db::getInstance()->Execute('
                         UPDATE `'._DB_PREFIX_.'testimonials`
                         SET `status` = "Disabled"
                         WHERE `testimonial_id` = "'.($val).'"');
                     }
               }
			   
	if (isset($_POST['Update']))
             {
                    foreach($_POST['moderate'] as  $check => $val)
                    {
             		$testimonial_main_message =  "testimonial_main_message_".$val;
                        //echo $testimonial_main_message;
			$testimonial_main_message = $_POST[$testimonial_main_message];
                         
			 $update=Db::getInstance()->Execute('
                         UPDATE `'._DB_PREFIX_.'testimonials`
                         SET `testimonial_main_message` = "'.$testimonial_main_message.'"
                         WHERE `testimonial_id` = "'.($val).'"');
                    }
              }
			   
			   
return $this->_html;

     }
	 
	 function backupFile(){  //check if backup file exists
		if (file_exists(dirname(__FILE__).'/backup.csv')) {
			return true;
		 }
	 return false;
	 }
	 
	 
        function _displayConfigForm(){
 		global $smarty, $cookie;
		$smarty->assign('base_dir', __PS_BASE_URI__);
		$smarty->assign('requestUri', $_SERVER['REQUEST_URI']);
		$smarty->assign('recaptcha', Configuration::get('TESTIMONIAL_CAPTCHA'));
		$smarty->assign('recaptchaPriv', Configuration::get('TESTIMONIAL_CAPTCHA_PRIV'));
		$smarty->assign('recaptchaPub', Configuration::get('TESTIMONIAL_CAPTCHA_PUB'));
		$smarty->assign('recaptchaPerpage', Configuration::get('TESTIMONIAL_PERPAGE'));
                $smarty->assign('maximagesize', Configuration::get('TESTIMONIAL_MAX_IMG'));
                $smarty->assign('displayImage', Configuration::get('TESTIMONIAL_DISPLAY_IMG'));
                 $smarty->assign('backupfileExists', $this->backupFile());
		return $this->display(__FILE__,'displayadmincfgForm.tpl');
        }


      function getadminTestimonials()
     {
          $results = null;
		 $testimonials = Db::getInstance()->Execute('
         SELECT * FROM `'._DB_PREFIX_.'testimonials`
         ORDER BY date_added DESC
         ');
			  
			 // while there are rows to be fetched...
			 while ($row = mysql_fetch_assoc($testimonials)) 
				  {
					  $results[] = $row;
			  } // end while
			  
			 echo $_SERVER['HTTP_HOST'];
			global $smarty;
			$smarty->assign(array(
                              'testimonials' => $results,
			      'requestUri', $_SERVER['REQUEST_URI'],
                              'http_host', $_SERVER['HTTP_HOST'],
			      'base_dir', __PS_BASE_URI__,
			      'this_path' => $this->_path
				  ));
				  
			return $this->display(__FILE__,'displayadmintestimonialsForm.tpl');
        }

}
?>
