<?php
/* SSL Management */
$useSSL = true;

global $cookie;
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/authorizedotnet.php');

$orderId = Tools::getValue('orderId');
$adminOrder = Tools::getValue('adminOrder');
$trxId = Tools::getValue('trxId');
$card = Tools::getValue('card');
$auth_code = Tools::getValue('auth_code');
$amt = floatval(Tools::getValue('amt'));
$type = intval(Tools::getValue('type'));
$id_employee = intval(Tools::getValue('id_employee'));
$adn_capture_status = intval(Tools::getValue('adn_capture_status'));
$adn_refund_status = intval(Tools::getValue('adn_refund_status'));
$adn = new AuthorizeDotNet();
$order = new Order($orderId);
$html = ' 
	<table cellspacing="10" width="100%">
    <tr> ' . ( $adminOrder != 1 ? 
		'<td align="left" width="155px" style="font-weight:bold;font-size:12px" nowrap>
        	&nbsp;
		</td>' : '') ;

/**
  Do Capture
**/
if ($type == 1)
{
	if ($trxId != '' AND $card != '' AND $auth_code != '')
	{
    	$adn->doCapture($orderId,$trxId,$amt,$auth_code);
		Db::getInstance()->Execute("UPDATE `"._DB_PREFIX_."authorizedotnet_refunds` SET captured = '1' WHERE `id_order` = '".$orderId."'");
		$html .= '<td align="left" style="font-weight:bold;font-size:12px;color:Green;" nowrap>
             '.$adn->l('The transaction Capture was successful.').'';
		$message = new Message();
		$message->message = $adn->l('Transaction Capture of').' $'.$amt;
		$message->id_customer = $order->id_customer;
		$message->id_order = $order->id;
		$message->private = 1;
		$message->id_employee = $id_employee;
		$message->id_cart = $order->id_cart;
		$message->add();
		if ($adn_capture_status != 0)
		{
			$history = new OrderHistory();
			$history->id_order = $orderId;
			$history->changeIdOrderState(intval($adn_capture_status), intval($orderId));
			$history->id_employee = intval($id_employee);
			$carrier = new Carrier(intval($order->id_carrier), intval($order->id_lang));
			$templateVars = array('{followup}' => ($history->id_order_state == _PS_OS_SHIPPING_ AND $order->shipping_number) ? str_replace('@', $order->shipping_number, $carrier->url) : '');
			$history->addWithemail(true, $templateVars);
			Configuration::updateValue('ADN_CAPTURE_STATUS',$adn_capture_status);
		}
	}
}
/**
  Do Refund
**/
else if ($type == 2)
{
	if ($trxId != '' AND $card != '')
	{
		$res_arr = $adn->doRefund($orderId,$order->total_paid == $amt?true:false,$trxId,$card,$amt);
		if ($res_arr[0] == 1)
		{
			Db::getInstance()->Execute("INSERT INTO `"._DB_PREFIX_."authorizedotnet_refund_history` VALUES('','".$orderId."','".$amt."','".($res_arr[11] == "credit"?'Credit - ID: ':'Void - ID: ').$res_arr[6]."',NOW())");
			$html .= '<td align="left" style="font-weight:bold;font-size:12px;color:Green;" nowrap>';
			if ($res_arr[11] == "credit")
				$html .= $adn->l('The transaction "Credit" was successful.').' (Transaction ID :' .$res_arr[6] . ')';
			else
				$html .= $adn->l('The transaction "Void" was successful.').' (Transaction ID :' .$res_arr[6] . ')';
			$message = new Message();
			if ($res_arr[0] == 1)
			{
				if ($res_arr[11] == "credit")
					$message->message = $adn->l('Transaction "Credit" of').' $'.$amt.' (Transaction ID :' .$res_arr[6] . ')';
				else
					$message->message = $adn->l('Transaction "Void" of').' $'.$amt.' (Transaction ID :' .$res_arr[6] . ')';
			}
			$message->id_customer = $order->id_customer;
			$message->id_order = $order->id;
			$message->private = 1;
			$message->id_employee = $id_employee;
			$message->id_cart = $order->id_cart;
			$message->add();
			if ($adn_refund_status != 0)
			{
				$history = new OrderHistory();
				$history->id_order = $orderId;
				$history->changeIdOrderState(intval($adn_refund_status), intval($orderId));
				$history->id_employee = intval($id_employee);
				$carrier = new Carrier(intval($order->id_carrier), intval($order->id_lang));
				$templateVars = array('{followup}' => ($history->id_order_state == _PS_OS_SHIPPING_ AND $order->shipping_number) ? str_replace('@', $order->shipping_number, $carrier->url) : '');
				$history->addWithemail(true, $templateVars);
				Configuration::updateValue('ADN_REFUND_STATUS',$adn_refund_status);
			}
		}
		else
		{
			$html .= '<td align="left" style="font-weight:bold;font-size:12px;color:red;" nowrap>';
            $html .= $adn->l('The void / credit failed.').'<br />'. "\n\r".$res_arr[3];
            
        }
	}
}
$html .= '
		</td>
	</tr>
	</table>';
echo $html;
?>