<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include_once(dirname(__FILE__).'/blocktestimonial.php');
require_once('recaptchalib.php');

//define variables
$errors = array();
$uploadFile = null;
$testimonialPassed = null;
$testimonial_img = null;
$confirmation = null;
$testimonial_main_message = "";
$testimonial_title = "";
$testimonial_submitter_name = "";
//end define variables


$blockTestimonial = new blockTestimonial();

  if (Tools::isSubmit('testimonial'))
    {

   //Then the form has been submitted
  
  //get the submitter name and clean it
   $testimonial_submitter_name = $blockTestimonial->cleanInput($_POST['testimonial_submitter_name']);
  //validate the submitter name
    if (!$blockTestimonial->field_validator('Name', $testimonial_submitter_name, '2', '20', '1'))
     {
    $errors[] = Tools::displayError('Please enter a name - between 1 and 20 characters long. Please use numbers and letters only');
	$testimonialPassed = false;
    }
       
   //get the testimonial title and clean it
   $testimonial_title = $blockTestimonial->cleanInput($_POST['testimonial_title']);
   //validate the testimonial title 
    if (!$blockTestimonial->field_validator('Name', $testimonial_title, '2', '40', '1'))
    {
    $errors[] = Tools::displayError('Please enter a Summary between 1 and 40 characters long. Please use numbers and letters only');
	$testimonialPassed = false;
    }
  
   //get the testimonial body and clean it
   $testimonial_main_message = $blockTestimonial->cleanInput($_POST['testimonial_main_message']);
   //validate the testimonial body
    if (!$blockTestimonial->field_validator('Your Testimonial', $testimonial_main_message, '2', '250', '1'))
     {
    $errors[] = Tools::displayError('Please enter a testimonial between 1 and 250 characters long. Please use numbers and letters only');
	$testimonialPassed = false;
     }
	 
	 //Check for an uploaded image and validate it
     if (isset($_FILES["testimonial_img"]["size"]) && ($_FILES["testimonial_img"]["size"] > 0))  //does a file exist?
       {
         
             $extension = $blockTestimonial->checkImageExt();
               if (!$extension) {  //does its extension match .jpg, .jpeg or .png
                   $errors[] = Tools::displayError('Only files with .jpg, .jpeg or .png files allowed use numbers and letters only');
               $testimonialPassed = false;
            }
            //code to check file size
           if (!$blockTestimonial->checkfileSize())  {
               $errors[] = Tools::displayError('That file is too large!');
              $testimonialPassed = false;
           }
          else 
              $uploadFile = true;
              $testimonialPassed = true;
       }
   
	else {
	$testimonialPassed = true;  // the testimonial submission passed - 
	}
    
	if (Configuration::get('TESTIMONIAL_CAPTCHA') == 0 ) {$submit = 1;}  // if we don't use a recaptcha then let's let the form submit
	if (intval(Configuration::get('TESTIMONIAL_CAPTCHA')) ) // if we need to use a recaptcha
                     {
                      $privatekey = Configuration::get('TESTIMONIAL_CAPTCHA_PRIV');
                      $resp = recaptcha_check_answer ($privatekey,
                                                    $_SERVER["REMOTE_ADDR"],
                                                    $_POST["recaptcha_challenge_field"],
                                                    $_POST["recaptcha_response_field"]);

                          if ($resp->is_valid) {
                           $submit = 1;
                          }

                          if (!$resp->is_valid) {
                           $submit = 0;
                         }
    }
                 if ($submit) {
				 if ($testimonialPassed) {
					 {
					 $confirmation = 1;
                                          if ($uploadFile){ //check to see if an image was uploaded and complete upload process
                                            $testimonial_img =  $blockTestimonial->uploadImage();
                                          }
					 $blockTestimonial->writeTestimonial($testimonial_title,$testimonial_submitter_name,$testimonial_main_message,$testimonial_img);
					 }
      }             
   }
 }


global $smarty; //assign results to the smarty for displaying

$smarty->assign(array(
    'recaptcha'=> intval(Configuration::get('TESTIMONIAL_CAPTCHA'))
     ));
						
$smarty->assign('errors', $errors);

// $testimonial_submitter_name add add it to the view for displaying
    $smarty->assign('testimonial_submitter_name', $testimonial_submitter_name);


//$testimonial_title add it to the view for displaying
    $smarty->assign('testimonial_title', $testimonial_title);


// $testimonial_main_message add it to the view for displaying
    $smarty->assign('testimonial_main_message', $testimonial_main_message);


if($confirmation) { // if testimonial submitted okay lets let the view know
   $smarty->assign('confirmation', $confirmation);
}

$smarty->assign('imgUpload', Configuration::get('TESTIMONIAL_MAX_IMG'));
$smarty->assign('imgSize', Configuration::get('TESTIMONIAL_DISPLAY_IMG'));
$smarty->assign('the_captcha', recaptcha_get_html(Configuration::get('TESTIMONIAL_CAPTCHA_PUB')));
$smarty->display(dirname(__FILE__).'/addtestimonial.tpl');

include_once(dirname(__FILE__).'/../../footer.php');

?>