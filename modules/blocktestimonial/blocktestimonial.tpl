<!-- Block testimonial module -->
			<div id="block_testimonials" class="block">
				<h4>{l s='Testimonials' mod='blockTestimonial'}</h4>
				    <div id="randomtestimonial">
				    	<h3 class="testimonialSpeech" >"<a href="{$this_path}testimonials.php#{$randomTestimonialid}">{$randomTestimonialtxt|strip_tags|truncate:60:'...'}</a>"</h3>
				    </div>
				   <div class="blocktestimonial">
					    <p><a href="{$this_path}testimonials.php">{l s='View All Testimonials' mod='blockTestimonial'}</a></p>
					    <p><a href="{$this_path}addtestimonial.php">{l s='Add Your Testimonial' mod='blockTestimonial'}</a></p>
				    </div>
			</div>
<!-- /Block testimonial module -->
