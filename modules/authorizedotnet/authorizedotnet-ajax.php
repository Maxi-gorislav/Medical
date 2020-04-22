<?php
/* SSL Management */
$useSSL = true;

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/authorizedotnet.php');

$id_shop = (int) Tools::getValue('id_shop');

$orderId = Tools::getValue('orderId');
$adminOrder = Tools::getValue('adminOrder');
$type = intval(Tools::getValue('type'));
$secure_key = Tools::getValue('secure_key');
$id_employee = intval(Tools::getValue('id_employee'));
$adn = new AuthorizeDotNet();
$order = new Order(intval($orderId));
$id = $order->id;
$date = $order->date_add;
$total = $order->total_paid;
$id_lang = intval($_POST['id_lang']);
$states = OrderState::getOrderStates(intval($id_lang));

if ($secure_key != $adn->_adn_secure_key)
{
	$html = 
		'<table cellspacing="10" width="100%">
		<tr>
			<td align="left" width="155px" style="font-weight:bold;font-size:12px" nowrap>
				&nbsp;
			</td>
			<td align="left" style="font-weight:bold;font-size:12px;color:Red;" nowrap>
				'.$adn->l('Your transaction was not processed - Authentication Error').'
			</td>
		</tr>
		</table>';
}
else
{
	$result = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'authorizedotnet_refunds WHERE id_order='.$orderId);
	if (is_array($result) && sizeof($result) == 1)
	{
		$trxId = $result[0]['id_trans'];
		$card = $result[0]['card'];
		$auth_code = $result[0]['auth_code'];
		$captured = $result[0]['captured'];
    }
	else
	{
		$trxId = '';
		$card = '';
		$auth_code = '';
		$captured = '';
	}
	if (($type == 1 && ($trxId == '' || $card == '' || $auth_code == '')) || ($type == 2 && ($trxId == '' || $card == '')))
	{
		$html = '
			<table cellspacing="10" width="100%">
			<tr>
				<td align="left" width="155px" style="font-weight:bold;font-size:12px" nowrap>
					&nbsp;
				</td>
				<td align="left" style="font-weight:bold;font-size:12px;color:Red;" nowrap>
					'.$adn->l('This order was not processed using Authorize.net.').'
				</td>
			</tr>
			</table>';
	}
	else if ($type == 1 AND $captured == 1)
	{
		$html = 
			'<table cellspacing="10" width="100%">
			<tr>
				<td align="left" width="155px" style="font-weight:bold;font-size:12px" nowrap>
					&nbsp;
				</td>
				<td align="left" style="font-weight:bold;font-size:12px;color:Red;" nowrap>
					'.$adn->l('A capture transaction was already processed for this order.').'
				</td>
			</tr>
			</table>';
	}
	else if ($id == $orderId)
	{
		$html = '
			<script type="text/javascript">
    		function ajax_call(type) {
	        	var orderId = "";
				var decimal_char =  ".";
				if (type == 1)
				{
					var amt = $("#adn_capture_amt").val();
					if ((amt == "") || (!$("#adn_capture_amt").val().match(/^\d+(?:\.\d+)?$/)))
					{
						alert("'.$adn->l('Please enter a valid capture amount').'");
						$("#adn_capture_amt").focus();
						return false;
					}
					$.ajax({
						type: "POST",
						url: baseDir + "authorizedotnet-trans-ajax.php",
						async: false,
						cache: false,
						data: "id_shop='.$id_shop.'&orderId='.$orderId.'&trxId='.$trxId.'&id_employee='.$id_employee.'&adminOrder='.$adminOrder.'&auth_code='.$auth_code.'&card='.$card.'&adn_capture_status="+ $("#adn_capture_status").val() + "&type="+ type + "&amt="+ amt,
						success: function(html){ $("#capture_order_details").html(html); },
						error: function() {alert("ERROR:");}
					});
					$("#capture_order_id").val("");
				}
				if (type == 2)
				{
					var amt = $("#adn_refund_amt").val();
					if ((amt == "") || (!$("#adn_refund_amt").val().match(/^\d+(?:\.\d+)?$/)))
					{
						alert("'.$adn->l('Please enter a valid refund amount').'");
						$("#adn_refund_amt").focus();
						return false;
					}
					
					var vars = new Object();
					vars["id_shop"] = "'.$id_shop.'";
					vars["orderId"] = "'.$orderId.'";
					vars["trxId"] = "'.$trxId.'";
					vars["id_employee"] = "'.$id_employee.'";
					vars["auth_code"] = "'.$auth_code.'";
					vars["adminOrder"] = "'.$adminOrder.'";
					vars["card"] = "'.$card.'";
					vars["adn_refund_status"] = $("#adn_refund_status").val();
					vars["type"] = type;
					vars["amt"] = amt;
					$.ajax({
						type: "POST",
						url: baseDir + "authorizedotnet-trans-ajax.php",
						async: false,
						cache: false,
						data: vars,
						success: function(html){ $("#refund_order_details").html(html); },
						error: function() {alert("ERROR:");}
					});
					$("#refund_order_id").val("");
            	}
        	}
        	</script>';
		if ($type == 1)
			$html .= '<div id="capture_order_details">';
		if ($type == 2)
			$html .= '<div id="refund_order_details">';
		$html .= '
			<table cellspacing="10" width="100%">
			<tr height="31">
				<td align="left" width="155px" style="font-weight:bold;font-size:13px" nowrap>
					'.$adn->l('Order Date').':
				</td>
				<td align="left" colspan="2" style="font-size:12px" nowrap>
					'.$date.'
				</td>
			</tr>';
		if ($type == 1)
		{
			$html .= 
				'<tr height="31">
					<td align="left" width="155px" style="font-weight:bold;font-size:13px" nowrap>
						'.$adn->l('Order Amount').':
					</td>
					<td align="left" colspan="2" style="font-size:12px" nowrap>
						$'.$total.'
					</td>
				</tr>
				<tr height="31">
					<td align="left" width="155px" style="font-weight:bold;font-size:13px" nowrap>
						'.$adn->l('Change Order Status').':
					</td>
					<td align="left" colspan="2" style="font-size:12px" nowrap>
					  	<select name="adn_capture_status" id="adn_capture_status" style="width:170px;display:inline;">
						<option value="0" '.($adn->_adn_capture_status == 0?'selected="selected"':'').'>----------</option>';
			foreach ($states AS $state)
				$html .= '<option value="'.$state['id_order_state'].'" '.($adn->_adn_capture_status == $state['id_order_state']?'selected="selected"':'').'>'.$state['name'].'</option>';
			$html .= '</select> '.$adn->l('(Optional)').'
					</td>
				</tr>
				<tr height="31">
					<td align="left" width="155px" style="font-weight:bold;font-size:13px" nowrap>
						'.$adn->l('Capture Amount').':
					</td>
					<td align="left" width="150px" style="font-size:12px" nowrap>
						$&nbsp;<input type="text" style="width:100px;display: inline" id="adn_capture_amt" name="adn_capture_amt" value="'.$total.'" />
					</td>
					<td align="left" style="font-size:12px" nowrap>
						&nbsp;<input type="button" value="'.$adn->l('Capture').'" name="submitCapture" class="button" onclick="if(confirm(\'Are you sure you want to capture the transaction?\')) {ajax_call(1)}" />
					</td>
				</tr>';
		}
		if ($type == 2)
		{
			$html .= 
				'<tr height="31">
					<td align="left" width="155px" style="font-weight:bold;font-size:13px" nowrap>
						'.$adn->l('Order Amount').':
					</td>
					<td align="left" colspan="2" style="font-size:12px" nowrap>
						$'.$total.'
					</td>
				</tr>
				<tr height="31">
					<td align="left" width="155px" style="font-weight:bold;font-size:13px" nowrap>
						'.$adn->l('Change Order Status').':
					</td>
					<td align="left" colspan="2" style="font-size:12px" nowrap>
					  	<select name="adn_refund_status" id="adn_refund_status" style="width:170px;display:inline;">
						<option value="0" '.($adn->_adn_refund_status == 0?'selected="selected"':'').'>----------</option>';
			foreach ($states AS $state)
				$html .= '<option value="'.$state['id_order_state'].'" '.($adn->_adn_refund_status == $state['id_order_state']?'selected="selected"':'').'>'.$state['name'].'</option>';
			$html .= '</select> '.$adn->l('(Optional)').'
					</td>
				</tr>
				<tr height="31">
					<td align="left" width="155px" style="font-weight:bold;font-size:13px" nowrap>
						'.$adn->l('Refund Amount').':
					</td>
					<td align="left" width="150px" style="font-size:12px" nowrap>
						$&nbsp;<input type="text" style="width:100px;display: inline" id="adn_refund_amt" name="adn_refund_amt" value="'.$total.'" />
					</td>
					<td align="left" style="font-size:12px" nowrap>
						&nbsp;<input type="button" value="'.$adn->l('Refund').'" name="submitRefund" class="button" onclick="if(confirm(\'Are you sure you want to refund the transaction?\')) {ajax_call(2)}"/>
					</td>
				</tr>
				</table>
				';
			$result = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'authorizedotnet_refund_history WHERE id_order='.$orderId);
			if (is_array($result) && sizeof($result) > 0)
			{
				$html .= '
				<table cellspacing="10" width="100%"> 
					<tr height="31">
						<td align="left" style="font-weight:bold;font-size:13px">
							'.$adn->l('Refund History').'
						</td>
						<td align="left" nowrap colspan="' . (($adminOrder ) ? '2' : '3' ). '">
						</td>
					</tr>';
				foreach ($result as $row)
				{
					$html .= ' 
					
						<tr height="31">
							' . (($adminOrder ) ? '' : '<td align="left" width="155px" style="font-weight:bold;font-size:13px" nowrap></td>' ). '

							<td align="left" style="font-size:12px" nowrap>
								$'.$row['amount'].'
							</td>
							<td align="left" style="font-size:12px" nowrap>
								'.$row['details'].'
							</td>
							<td align="left" style="font-size:12px" nowrap>
								'.$row['date'].'
							</td>
						</tr>';
				}
				$html .= '</table>';
				
			}
		}
		$html .= '</div>';
	}
	else
	{
		$html = ' 
			<table cellspacing="10" width="100%">
			<tr>
				<td align="left" width="155px" style="font-weight:bold;font-size:12px" nowrap>
					&nbsp;
				</td>
				<td align="left" style="font-weight:bold;font-size:12px;color:Red;" nowrap>
					'.$adn->l('Invalid Order Number').'
				</td>
			</tr>
			</table>';
	}
}
echo $html;
?>