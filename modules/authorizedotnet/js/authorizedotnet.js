function paymentModulePCC(modulePrefix, isEmbeddedCheckout, translations, ajaxUrl)
{
	this.modulePrefix       = modulePrefix;
	this.isEmbeddedCheckout = isEmbeddedCheckout;
	this.translations       = translations;
	this.ajaxUrl            = ajaxUrl;


	this.filedsToValidate = [
		'fname', 
		'lname',
		'address',
		'city',
		'zip',
		'number', 
		'cvm'
	]
	
	//Card validation
	this.creditCardRegExp = {
	  'mc':'5[1-5][0-9]{14}',
	  'ec':'5[1-5][0-9]{14}',
	  'vi':'4(?:[0-9]{12}|[0-9]{15})',
	  'ax':'3[47][0-9]{13}',
	  'dc':'3(?:0[0-5][0-9]{11}|[68][0-9]{12})',
	  'bl':'3(?:0[0-5][0-9]{11}|[68][0-9]{12})',
	  'di':'6011[0-9]{12}',
	  'jcb':'(?:3[0-9]{15}|(2131|1800)[0-9]{11})',
	  'er':'2(?:014|149)[0-9]{11}'
	};
	// Add the card validator to them
	this.validateCards = function(value,ccType) {
	  value = String(value).replace(/[- ]/g,''); //ignore dashes and whitespaces
	
	
	  var cardinfo = this.creditCardRegExp, results = [];
	  if(ccType){
	    var expr = '^' + cardinfo[ccType.toLowerCase()] + '$';
	    return expr ? !!value.match(expr) : false; // boolean
	  }
	
	  for(var p in cardinfo){
	    if(value.match('^' + cardinfo[p] + '$')){
	      results.push(p);
	    }
	  }
	  return results.length ? results.join('|') : false; // String | boolean
	}	

	this.getNameField = function(fld)
	{
		return this.modulePrefix + '_cc_' + fld; 
	}

	this.showError = function(fld)
	{
		alert(this.translations['err_' + fld]);
	}
	
	this.validate = function(form)
	{
		for(var i = 0; i < this.filedsToValidate.length ; i++)
		{
			var fld = this.filedsToValidate[i];
			if (fld == 'number') {

				if (form[this.getNameField(fld)].value == "" || !this.validateCards(form[this.getNameField(fld)].value)) {
					this.showError(fld);
					return false;
				}
				
			}
			else
				if (form[this.getNameField(fld)] && form[this.getNameField(fld)].value == "") {
					this.showError(fld);
					return false;
				}
			
		}
		
		return true;		
	} 

	
	this.beforeSend = function()
	{
		
	}


	this.send = function(form)
	{
			
		if(this.validate(form)) {
			
			
			var oldTitleSubmitButton = $('#'+this.modulePrefix+'_submit span').length?$('#'+this.modulePrefix+'_submit span').html():$('#'+this.modulePrefix+'_submit').val();
			var modulePrefix = this.modulePrefix;
			if ($('#'+this.modulePrefix+'_submit span').length)
				$('#'+this.modulePrefix+'_submit span').html(this.translations.trl_wait);
			else
				$('#'+this.modulePrefix+'_submit').val(this.translations.trl_wait);
							
			$('#'+this.modulePrefix+'_submit').attr('disabled','disabled');
			$('#'+modulePrefix+'_ajax_container').hide();
			$('#'+modulePrefix+'_ajax_container').removeClass('error');

			this.beforeSend();
			if (this.isEmbeddedCheckout) {
				
				$.ajax({
					url: this.ajaxUrl,
					type: "post",
					dataType: "html",
					data:$(form).serialize(),
					success: function(strData) {
						
						if (strData.substring(0, 4) == 'url:') {
							window.location = strData.substring(4);
						} else {
							$('#'+modulePrefix+'_ajax_container').show();
							$('#'+modulePrefix+'_ajax_container').html(strData);
							$('#'+modulePrefix+'_ajax_container').addClass('error');
							if ($('#'+modulePrefix+'_submit span').length)
								$('#'+modulePrefix+'_submit span').html(oldTitleSubmitButton);
							else
								$('#'+modulePrefix+'_submit').val(oldTitleSubmitButton);
							$('#'+modulePrefix+'_submit').attr('disabled',false);
						}
					}
				});
			} else {
				form.submit();
			}			
		}
	}

	this.updateStates = function(modulePrefix) {
		if (modulePrefix) {
			var realModulePrefix = modulePrefix;
		} else {
			var realModulePrefix = this.modulePrefix;
		}
		
		
		$('select#'+realModulePrefix+'_id_state option:not(:first-child)').remove();
		var states = window[realModulePrefix+'_countries'][$('select#'+realModulePrefix+'_id_country').val()];

		

		if(typeof(states) != 'undefined')
		{
			$(states).each(function (key, item){
				$('select#'+realModulePrefix+'_id_state').append('<option value="'+item.id+'"'+ (window[realModulePrefix+'_idSelectedCountry'] == item.id ? ' selected="selected"' : '') + '">'+item.name+'</option>');
			});
			
			$('.'+realModulePrefix+'_id_state:hidden').slideDown('slow');
			$('#'+realModulePrefix+'_id_state:hidden').slideDown('slow');
		}
		else {
			$('.'+realModulePrefix+'_id_state').slideUp('fast');
			$('#'+realModulePrefix+'_id_state').slideUp('fast');
			
		}
					
	}

	this.updateNeedIDNumber = function(modulePrefix)
	{
		if (modulePrefix) {
			var realModulePrefix = modulePrefix;
		} else {
			var realModulePrefix = this.modulePrefix;
		}		
		
		var idCountry = parseInt($('select#'+realModulePrefix+'_id_country').val());
		
		if ($.inArray(idCountry, window[realModulePrefix+'_countriesNeedIDNumber']) >= 0)
			$('fieldset.dni').slideDown('slow');
		else
			$('fieldset.dni').slideUp('fast');
	}
	
	this.initStates = function (object) 
	{
		if (modulePrefix) {
			var realModulePrefix = modulePrefix;
		} else {
			var realModulePrefix = object.modulePrefix;
		}		
		
		$('select#'+realModulePrefix+'_id_country').change(function(){
			object.updateStates(realModulePrefix);
			object.updateNeedIDNumber(realModulePrefix);
		});
		object.updateStates(realModulePrefix);
		object.updateNeedIDNumber(realModulePrefix);		
	}

}






