<link rel="stylesheet" type="text/css" href="{$this_path}testimonial.css" media="screen" />
<link rel="stylesheet"  href="{$this_path}slider/lightSlider/css/lightSlider.css"/>
    
   
    <script src="{$this_path}slider/lightSlider/js/jquery.lightSlider.js"></script> 
    <script type="text/javascript">
  $(document).ready(function() {
    $("#lightSlider").lightSlider({
        item: 1,
        mode:"fade",
        auto:true,
        loop:true
    }); 
  });
</script>
{if $teste_scrolling==1}
      <div class="testimonialHolder block col-xs-8" >
 	 
            
	       {if $test_titles}
		  <ul id="lightSlider">
		     {foreach from=$test_titles item=testmonial name=test_title}
                         <li>
			<!-- HTML codes by Quackit.com -->
			<p class="testimonialItem">
			   <i style="font-weight:normal;font-family:arial;">
			      {if $this_version == '1.6'}
				
					 "{$testmonial.teste_text|truncate:100|replace:'<p>':''|replace:'</p>':''}"
				
				 {else if $this_version == '1.5'}
				
					 "{$testmonial.teste_text|truncate:100|replace:'<p>':''|replace:'</p>':''}"
				
				 {else if $this_version == '1.4'}
				
				 "{$testmonial.teste_text|truncate:100|replace:'<p>':''|replace:'</p>':''}"
				
			      {/if}
			   </i>
			          
			</p>
                         </li>
		     {/foreach}
                  </ul>
		  
		  {if $this_version == '1.6'}
		  <a href="index.php?fc=module&module=testimonial&controller=testi_list">{l s='Know more..' mod='testimonial'} </a>
		  {else if $this_version == '1.5'}
		  <a href="index.php?fc=module&module=testimonial&controller=testi_list">{l s='Know more..' mod='testimonial'} </a>
		  {else if $this_version == '1.4'}
		  <a href="{$this_path}testi_list.php">{l s='Know more..' mod='testimonial'} </a>
		  {/if}
	       {else} <a href="index.php?fc=module&module=testimonial&controller=testi_list">{l s='Know more..' mod='testimonial'} </a>
	       {/if}
	    
      </div>
   {/if}
       
   {if $teste_scrolling==2}
      <div class="block">
	 <h4>{l s='Testimonial' mod='testimonial'}</h4>
	    <div class="block_content" style="padding:10px; overflow-x:auto;">
	      {if $test_titles}
		  {assign var="x" value=0}
		  {foreach from=$test_titles item=testmonial name=test_title}
		     <p style="padding:10px;">
			{$testmonial.teste_text|truncate:100}<span>-{$testmonial.teste_name}</span>
		     </p>
		     <br /><br />
		     {assign var="x" value=$x+1}
		     {if $number == $x}
			{break}
		     {/if}
		  {/foreach}
	       {if $this_version == '1.5'}
	       <a href="index.php?fc=module&module=testimonial&controller=testi_list">{l s='Know more..' mod='testimonial'} </a>
	       {else if $this_version == '1.4'}
	       <a href="{$this_path}testi_list.php">{l s='Know more..' mod='testimonial'} </a>
	       {/if}
		  {else} {l s='No Testimonial..' mod='testimonial'}
	       {/if}
	   </div>
      </div>
   {/if}
 
