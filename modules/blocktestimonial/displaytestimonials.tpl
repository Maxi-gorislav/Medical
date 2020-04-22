<li class="htmlcontent-item-5 col-xs-8">
<!-- Block testimonial module -->

<link rel="stylesheet" type="text/css" href="{$this_path}/fancybox/jquery.fancybox-1.2.6.css" media="screen" />

<script type="text/javascript" src="{$this_path}/fancybox/jquery.fancybox-1.2.6.pack.js"></script>

<!-- <div id="block_testimonials" > -->
 <h3>{l s='Testimonials' mod='blockTestimonial'}</h3>
  <div class="addblocktestimonial"><a class="addblocktestimonial" href="{$this_path}addtestimonial.php">Add Your Testimonial</a></div>
     	     <div id="paginationTop" >
		   {if $currentpage > 1}
		   <a href='{$smarty.server.PHP_SELF}?currentpage=1'>{l s=' << Last' mod='blockTestimonial'}</a>
		   {* show < link to go back to 1 page *}
		   <a href='{$smarty.server.PHP_SELF}?currentpage={$prevpage}'>{l s=' < Previous' mod='blockTestimonial'}</a>
		   {/if}
		   
		   [{$currentpage}]  
				   
		   {if $currentpage != $totalpages}	    
			<a href='{$smarty.server.PHP_SELF}?currentpage={$nextpage}'>{l s='Next >' mod='blockTestimonial'}</a>
		   <a href='{$smarty.server.PHP_SELF}?currentpage={$totalpages}'>{l s='Last >>' mod='blockTestimonial'}</a>
			{/if}
		 </div> <!-- /end paginationTop div -->
   <div id="testimonials">
        {section name=nr loop=$testimonials}
     <div class="testimonial" >
	 <h3 class="testimonialhead" ><a name="{$testimonials[nr].testimonial_id}">{$testimonials[nr].testimonial_title}</a></h3>
	  <div id="text" >
   
        <p class="testimonialbody" >
          {if $testimonials[nr].testimonial_img != NULL}
			   <a class="zoom" ALIGN="left" href="http://{$http_host}{$base_dir}{$testimonials[nr].testimonial_img}">  <img class="testimonialImage" src="http://{$http_host}{$base_dir}{$testimonials[nr].testimonial_img}" height="50" width="50" /></a>
 	  {/if}
	  {$testimonials[nr].testimonial_main_message}
        </p>
      </div>
     
	  <ul>
         <li>{l s='Submitted By:' mod='blockTestimonial'} {$testimonials[nr].testimonial_submitter_name}</li>
         <li>{l s='Submitted Date:' mod='blockTestimonial'} {$testimonials[nr].date_added|strip_tags}
         </ul>
			   
      </div>
         {sectionelse}
           <h1>{l s='No Testimonials Yet!' mod='blockTestimonial'}</h1>
        {/section}
		
   </div>
   	     <div id="paginationTop" >
		   {if $currentpage > 1}
		   <a href='{$smarty.server.PHP_SELF}?currentpage=1'>{l s=' << Last' mod='blockTestimonial'}</a>
		   {* show < link to go back to 1 page *}
		   <a href='{$smarty.server.PHP_SELF}?currentpage={$prevpage}'>{l s=' < Previous' mod='blockTestimonial'}</a>
		   {/if}
		   
		   [{$currentpage}]  
				   
		   {if $currentpage != $totalpages}	    
			<a href='{$smarty.server.PHP_SELF}?currentpage={$nextpage}'>{l s='Next >' mod='blockTestimonial'}</a>
		   <a href='{$smarty.server.PHP_SELF}?currentpage={$totalpages}'>{l s='Last >>' mod='blockTestimonial'}</a>
			{/if}
		 </div> <!-- /end paginationTop div -->
</div>
<script type="text/javascript">
    {literal}
		$(document).ready(function() {
			$("a.zoom").fancybox();
		});
  {/literal}
	</script>
<!-- /Block testimonial module -->
</li>
</ul>