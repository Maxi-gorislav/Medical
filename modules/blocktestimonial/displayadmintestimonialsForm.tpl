
<script type="text/javascript" src="{$this_path}js/displayadmintestimonial.js"></script>
<link href="{$this_path}css/admintestimonial.css" rel="stylesheet" type="text/css" media="all" />
<form action="{$requestUri}" method="post" name="form1">
	<fieldset>
	  <legend><img src="../img/admin/slip.gif" />{l s='View / Manage Testimonials' mod='blockTestimonial'}</legend>
	  <div style="display:none;" id="controls">
			<input  class="button" name="Enable" value="{l s='Enable Selected' mod='blockTestimonial'}" type="submit" type="submit" style="width: 200px;"/>
			<input class="button"  name="Disable" value="{l s='Disable Selected' mod='blockTestimonial'}" type="submit" type="submit" style="width: 200px;"/>
			<input class="button"  name="Delete" onClick="return confirmSubmit('{l s='Okay to Delete this Testimonial(s)?' mod='blockTestimonial'}')" value="{l s='Delete Selected' mod='blockTestimonial'}" type="submit" type="submit" style="width: 200px;"/>
			<input class="button"  name="Update" value="{l s='Update Selected' mod='blockTestimonial'}" type="submit" type="submit" style="width: 200px;"/>
	   </div>
			<table id="box-table-a" >
			<th>{l s='Select' mod='blockTestimonial'}</th> <!-- Select Column Header-->
			  <th>{l s='Status' mod='blockTestimonial'}</th> <!-- Status Column Header-->
			  <th>{l s='Name' mod='blockTestimonial'}</th> <!-- Name Column Header-->
			  <th>{l s='Date' mod='blockTestimonial'}</th> <!-- Date Column Header-->
			  <th>{l s='Testimonial' mod='blockTestimonial'}</th> <!-- Testimonial  Column Header-->
                          <th>{l s='Testimonial Image' mod='blockTestimonial'}</th> <!-- Testimonial Image  Column Header-->
			  {if $testimonials neq null}
				  {section name=nr loop=$testimonials}
						  <tr>
							<td> <!--Check Box -->
							<INPUT class="testimonialselect" TYPE=checkbox VALUE="{$testimonials[nr].testimonial_id}" NAME="moderate[]">
							</td>
						  
							<td> <!-- Status Column -->
							 {$testimonials[nr].status}
							</td>
							
							<td> <!-- Name Column -->
							 {$testimonials[nr].testimonial_submitter_name}
							</td>
							
							<td> <!-- Date Column -->
							 {$testimonials[nr].date_added|strip_tags}
							</td>
							
							<td> <!-- Testimonial Column -->
							<textarea name="testimonial_main_message_{$testimonials[nr].testimonial_id}" > {$testimonials[nr].testimonial_main_message} </textarea>
							</td>

							<td> <!-- Testimonial Image -->
                                                           {if $testimonials[nr].testimonial_img != NULL}
							    <img width="35" height="35" src="http://localhost{$base_dir}{$testimonials[nr].testimonial_img}" >
                                                            {$testimonials[nr].testimonial_img}
                                                           {/if}
							</td>
						  </tr>

				   {/section}
				    {else}
					 <tr><td>{l s='No Testimonials Yet' mod='blockTestimonial'}</td></tr>
                {/if}
			</table>
			
	</fieldset>
</form>
