      <form action="{$requestUri}" method="post">
                   <fieldset>
                   <legend><img src="../img/admin/cog.gif" />{l s='Configuration' mod='blockTestimonial'}</legend>
                     <table border="0" width="900" cellpadding="0" cellspacing="5" id="testimonialCfg">
				<div class="margin-form">
                     <label>{l s='Use ReCaptcha Anti Spam' mod='blockTestimonial'}</label>
                                        
					<input type="radio" name="reCaptcha" id="recaptcha_on" value="1" {if $recaptcha eq 1}checked="yes" {/if}/>
					<label class="t" for="recaptcha_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="reCaptcha" id="recaptcha_off" value="0" {if $recaptcha eq 0}checked="yes" {/if} />
					<label class="t" for="recaptcha_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
                            	</div>
                                <div class="margin-form">
                                  <label>{l s='ReCaptcha Public Key' mod='blockTestimonial'}</label>
                                  <input type="text" name="recaptchaPub" value="{$recaptchaPub}" />
                                 </div>

                                  <div class="margin-form">
                                  <label>{l s='ReCaptcha Private Key' mod='blockTestimonial'}</label>
                                  <input type="text" name="recaptchaPriv" value="{$recaptchaPriv}" />
                                 </div>
				 <hr />
				<div class="margin-form">
                                  <label>{l s='# of testimonials per page' mod='blockTestimonial'}</label>
                                  <input type="text" name="perPage" value="{$recaptchaPerpage}" />
                                 </div>
                                 <hr />


                                   <div class="margin-form">
                                        <label>{l s='Allow Image Upload' mod='blockTestimonial'}</label>
					<input type="radio" name="displayImage" id="displayImage_on" value="1" {if $displayImage eq 1}checked="yes" {/if}/>
					<label class="t" for="displayImage_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="displayImage" id="displayImage_off" value="0" {if $displayImage eq 0}checked="yes" {/if} />
					<label class="t" for="displayImage_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
                                    </div>


                                  <div class="margin-form">
                                  <label>{l s='Maximimum Image size in KiloBytes (KB)' mod='blockTestimonial'}</label>
                                  <input type="text" name="maximagesize" value="{$maximagesize}" />
                                 </div>

	                         <hr/>

				<div class="margin-form">
					<input type="submit" value="{l s='Save' mod='blockTestimonial'}" name="submitConfig" class="button" />
                      			
				</div>
                     </table>
	      </fieldset>
          </form><br />

          <fieldset>
		  <legend>Backup Testimonials</legend>
                   <p>{'Use this to create backup of your testimonials in a CSV File.  This will create a file called backup.csv in this /modules/blocktestimonial directory'}</p>

 <form id="backupform" action="{$requestUri}" method="post" name="backupform" >
		  <input class="button" name="Backup" value="{'Backup'}" type="submit" type="submit" style="width: 200px;"/>
		   {if $backupfileExists >0}
			<br><br> <span style="font-weight:bold"><a href="{$base_dir}modules/blocktestimonial/backup.csv" > >>Download Backup File<< </a></span>
		  {/if}
		  </form>
              </fieldset>
			  <br />
              
