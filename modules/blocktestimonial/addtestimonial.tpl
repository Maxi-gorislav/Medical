<!-- Block testimonial module -->
{include file="$tpl_dir./breadcrumb.tpl"}
<link href="{$this_path}css/testimonial.css" rel="stylesheet" type="text/css" media="all" />
<div id="block_testimonials_submit">
     <form class="testimonialForm" id="testimonialForm" name="testimonialForm" method="post" enctype="multipart/form-data" action="addtestimonial.php" >
         {if isset($confirmation)}
	      <h3>{l s='Your testimonial was submitted successfully.'}</h3>
	     {/if}
         <fieldset>
              <legend>{l s='Submit Your Testimonial' mod='blockTestimonial'}</legend>
                    <h3 class="blocktestimonials" >We welcome your testimonials - please enter yours using the form below</h3>
                    <ol>
                            <li><label for="name">Name <em>*</em></label> <input name="testimonial_submitter_name"  value="{$testimonial_submitter_name}" id="testimonial_submitter_name" class="required" minlength="2" /></li>
                            <li><label for="testimonial_title">Summary <em>*</em></label> <input name="testimonial_title" value="{$testimonial_title}" id="testimonial_title" class="required" minlength="2" /></li>
                            
                            <li><label for="testimonial_main_message">Your Testimonial </label><textarea  cols=45 rows=5 name="testimonial_main_message" id="testimonial_main_message" class="required" minlength="2" >{$testimonial_main_message}</textarea></li>
                        {if $imgUpload}
                            <li><label for="testimonial_img">Optionally Add an Image (up to {$imgUpload}KB filesize)</label> <input type="file" name="testimonial_img" /></li>
                        {/if}
                </ol>

            </fieldset>
   {if $recaptcha}
     <fieldset>
       {l s='Please complete this test to prove you are a real person and not a bot'}
   {$the_captcha}
      </fieldset>
   {/if}
        <input type="submit" class="testimonialsubmit" name="testimonial" value="{l s='Submit Testimonial' mod='blockTestimonial'}"  />
    </form>

</div>
<!-- /Block testimonial module -->
