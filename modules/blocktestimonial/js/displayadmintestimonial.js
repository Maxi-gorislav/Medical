
		 
function confirmSubmit(confirmMsg)
		{
        var agree=confirm(confirmMsg);
			if (agree)
				return true ;
			else
				return false ;
		}

		
$(document).ready(function() {
   // do stuff when DOM is ready
   
   
   $("input:checkbox.testimonialselect").click(function(){  //add a function to the check boxes
   
   if (this.checked)
    {
      $('#controls').fadeIn('medium');
    }
    else
    {
        if ( !$('.checkbox:checked').length )
        {
            $('#controls').fadeOut('medium');
        }
    }
    

     
   
   
   });

   
 });
