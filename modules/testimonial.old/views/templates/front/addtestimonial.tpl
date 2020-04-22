<html>
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
<script type="text/javascript" src="{$this_path}js/tiny_mce/tiny_mce.js"></script>  
{literal}
     <script>
	  tinyMCE.init
	  ({
	    mode : "specific_textareas",
	    theme : "advanced",
	    skin:"cirkuit",
	    editor_selector : "rte",
	    editor_deselector : "noEditor",
	    // Theme options
	    theme_advanced_buttons1 : "bold,italic,underline,strikethrough,justifyleft,justifycenter,justifyright",
	    theme_advanced_buttons2 : "",
	    theme_advanced_buttons3 : "",
	    theme_advanced_toolbar_location : "top",
	    theme_advanced_toolbar_align : "left",
	    theme_advanced_statusbar_location : "bottom",
	    theme_advanced_resizing : false,
	    width: "400",
	    height: "auto",
	    font_size_style_values : "8pt, 10pt, 12pt, 14pt, 18pt, 24pt, 36pt",
	    language : 'en'
	  });
	  $(document).ready(function()  
	       {									
	       $("#demo").datepicker( {dateFormat:"yy-mm-dd"} );
	
	       });
     </script> 
{/literal}
 				
{literal}
     <script type="text/javascript">
	  function validate()
	  {
	  var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
	  var textarea = tinyMCE.get('testi_text').getContent();
	       if(document.sample.testi_name.value == "")
		    {
			alert ( "Enter The Testimonial Name" );
			document.sample.testi_name.focus();
			valid = false;
		    }
	       else if(textarea == "")
		    {
			alert("Enter Testimonial Text");
			document.sample.testi_text.focus();
			valid= false;
		    }
	       else if(reg.test(document.sample.testi_email.value) == "")
		    {
			alert ( "Enter the Valid Email" );
			document.sample.testi_email.focus();
			valid = false;
		    }
	       else if(document.sample.testi_firstname.value == "")
		    {
			alert ( "Enter the Firstname" );
			document.sample.testi_firstname.focus();
			valid = false;
		    }
	       else if(document.sample.testi_lastname.value == "")
		    {
			alert ( "Enter the lastname" );
			document.sample.testi_lastname.focus();
			valid = false;
		    }
	       else if(document.sample.testi_date.value == "")
		    {
			alert ( "Select the date" );
			document.sample.testi_date.focus();
			valid = false;
		    }
	       else
		    {
			document.sample.submit();
		    }
	       return valid;
	  }
     </script>
{/literal}				

<body style="color:black;">
<h1>Testimonial</h1>

{if $sucmsg!=""}
     <div style="padding:3px;width: 200px; margin-left:auto; margin-right: auto;">
     <div style="border: 1px solid #fff; background-color: #69c; padding: 5px;color: #fff; text-align:center;"><b>{$sucmsg}</b></div></div> 
{/if}
<form name="sample" action="{$request_uri}" method="POST" onSubmit="return validate()" style="margin-top:25px" class="testi_custom">
          <div style="width:550px;float:left"><div><u>Write testimonial</u></div><div style="position:relative;top:-10px;float:right" >
	  {if $this_version=="1.4"}
	       <a href="testi_list.php">Back To Testimonial</a>
	       {else}
	       <a href="index.php?fc=module&module=testimonial&controller=testi_list">Back To Testimonial</a>
	  {/if}
	  </div></div><br /><br />
                   <label>Name of Testimonial: <span style="color:red;">*</span></label>
			<div class="margin-form"><input type="text" name="testi_name" value="">	</div><br>
                   <label for="id_text">Testimonial Text:<span style="color:red;">*</span></label><br><br>
			<div class="margin-form" style="margin-left:-60px;">
                   <textarea name="testi_text" rows="10" cols="100" class="rte"></textarea> </div><br>
		   <label for="id_email">Email Address:<span style="color:red;">*</span></label>
			<div class="margin-form"><input type="text" name="testi_email" value=""></div><br>
                   <label for="id_firstname">Firstname:<span style="color:red;">*</span></label>
			<div class="margin-form"><input type="text" name="testi_firstname" value=""></div><br>
		   <label for="id_lasname">Last Name:<span style="color:red;">*</span></label>
			<div class="margin-form"><input type="text" name="testi_lastname" value=""></div><br>
		   <label for="id_company">Company Name:</label>
			<div class="margin-form"><input type="text" name="testi_company" value=""></div><br>
		   <label for="id_category">Date of Testimonial:<span style="color:red;">*</span></label>
			<div class="margin-form"><input type="text" name="testi_date" value="" id="demo"></div><br>
			<div style="margin-left:200px;">
                   <input type="submit" value="Save" name="submitTeste" style="cursor: pointer;">
		   <input type="reset" value="Reset" style="cursor: pointer;">
			</div>    
        </form>
</body>
</html>