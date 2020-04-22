$(document).ready(function(){
	$('select#adn_id_country').change(function(){
		updateStateAIM();
		updateNeedIDNumberAIM();
	});
	updateStateAIM();
	updateNeedIDNumberAIM();
});

function updateStateAIM()
{
	$('select#adn_id_state option:not(:first-child)').remove();
	var states = adn_countries[$('select#adn_id_country').val()];
	if(typeof(states) != 'undefined')
	{
		$(states).each(function (key, item){
			$('select#adn_id_state').append('<option value="'+item.id+'"'+ (adn_idSelectedCountry == item.id ? ' selected="selected"' : '') + '">'+item.name+'</option>');
		});
		
		$('#adn_id_state:hidden').slideDown('slow');
	}
	else
		$('#adn_id_state').slideUp('fast');
}

function updateNeedIDNumberAIM()
{
	var idCountry = parseInt($('select#adn_id_country').val());
	
	if ($.inArray(idCountry, adn_countriesNeedIDNumber) >= 0)
		$('fieldset.dni').slideDown('slow');
	else
		$('fieldset.dni').slideUp('fast');
}
