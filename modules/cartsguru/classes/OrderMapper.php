<?php
/**
 * Carts Guru
 *
 * @author    LINKT IT
 * @copyright Copyright (c) LINKT IT 2016
 * @license   Commercial license
 */

class CartsGuruOrderMapper extends CartsGuruMapperAbstract
{
    /**
     * @see CartsGuruMapperAbstract::mappObject()
     */
    public function mappObject($order, $params)
    {
        $customerGroups = Customer::getGroupsStatic((int) $order->id_customer);
        if (defined('CARTSGURU_ONLY_GROUP') && !in_array(CARTSGURU_ONLY_GROUP, $customerGroups)) {
            return null;
        }

        $order_items = $order->getProducts();
        $items = array();
        $product_mapper = new CartsGuruProductMapper($this->id_lang, $this->id_shop_group, $this->id_shop);
        foreach ($order_items as $order_item) {
            $pmap_params = array('id_product_attribute' => $order_item['product_attribute_id']);
            $product = new Product($order_item['product_id'], false, $this->id_lang);
            $product_mapped = $product_mapper->create($product, $pmap_params);
            $product_mapped['label'] = $order_item['product_name'];
            $product_mapped['quantity'] = (int) $order_item['product_quantity'];
            //Even if we have backward compatibility, be sure it will continue to work on > 1.6
            if (version_compare(_PS_VERSION_, '1.5.0', '<')) {
                $product_mapped['totalET'] = (float) $order_item['total_price'];
                $product_mapped['totalATI'] = (float) $order_item['total_wt'];
            } else {
                $product_mapped['totalET'] = (float) $order_item['total_price_tax_excl'];
                $product_mapped['totalATI'] = (float) $order_item['total_price_tax_incl'];
            }
            $items[] = $product_mapped;
        }

        $customer = new Customer((int) $order->id_customer);
        $account_mapper = new CartsGuruAccountMapper($this->id_lang, $this->id_shop_group, $this->id_shop);
        $account_mapped = $account_mapper->create($customer, $params);
        $status_cg_name = 'Undefined';
        $order_state_id = 0;
        if (version_compare(_PS_VERSION_, '1.5.0', '<')) {
            if (isset($params['newOrderStatus'])) {
                $order_state_id = (int) $params['newOrderStatus']->id;
            } else {
                $order_state_id = (int) $order->getCurrentState();
            }
        } else {
            $order_state_id = (int) $order->current_state;
        }
        if ((int) $order_state_id) {
            $current_status = new OrderState((int) $order_state_id, $this->id_lang);
            $status_cg_name = $current_status->name;
        }

        $currency = new Currency((int)$order->id_currency);

        $order_mapped = array(
            'id' => (string) $order->id,
            'cartId' => (string) $order->id_cart,
            'state' => $status_cg_name,
            'creationDate' => $this->formatDate($order->date_add), // Date of the order as string in json format
            'totalET' => (float) $order->getTotalProductsWithoutTaxes(), // Amount excluded taxes and excluded shipping
            'totalATI' => (float) $order->total_paid, // Total ttc
            'paymentMethod' => (string)$order->payment,
            'currency' => (string) $currency->iso_code,
            'items' => $items
        );

        if (!empty($order->source)) {
            $order_mapped['source'] = $order->source;
        }

        $order_mapped = array_merge($order_mapped, $account_mapped);

        // Add Custom fields
        $order_mapped['custom'] = $this->getCustomFields($order, $order_mapped, $customerGroups);

        return $order_mapped;
    }

     /**
     * Get custom fields of order, can be overrided for custom use
     *
     * @param $order
     * @param $order_mapped
     * @param $customerGroups
     * @return array
     */
    protected function getCustomFields($order, $order_mapped, $customerGroups)
    {
        $context = Context::getContext();

        return array(
            'language' => $context->language->iso_code,
            'customerGroup' => implode(',', CartsGuruHelper::getGroupNames($customerGroups, $context->language)),
            'isNewCustomer' => CartsGuruHelper::isNewCustomer($order_mapped['email'], true)
        );
    }

    /**
     * This method map prestashop status to api status
     * priority
     * 1 status paid/logable => 'confirmed'
     * 2 status is error => 'error'
     * 3 status is canceled or refunded => 'cancelled'
     * 4 status is cheque or bankwirer => 'wait'
     * 5 other return the prestatshop status name
     *
     * @param $status
     * @return string
     */
    public function getStatus($status)
    {
        if ((int) $status->paid || (int) $status->logable) {
            return 'confirmed';
        }
        $ps_os_error_id = (int) Configuration::get('PS_OS_ERROR');
        if ((int) $status->id == (int) $ps_os_error_id) {
            return 'error';
        }
        $ps_os_canceld_id = (int) Configuration::get('PS_OS_CANCELED');
        $ps_os_refund_id = (int) Configuration::get('PS_OS_REFUND');
        if ((int) $status->id == (int) $ps_os_canceld_id || (int) $status->id == (int) $ps_os_refund_id) {
            return 'cancelled';
        }
        $ps_os_cheque_id = (int) Configuration::get('PS_OS_CHEQUE');
        $ps_os_bankwire_id = (int) Configuration::get('PS_OS_BANKWIRE');
        if ((int) $status->id == (int) $ps_os_cheque_id || (int) $status->id == (int) $ps_os_bankwire_id) {
            return 'waiting';
        }
        return $status->name;
    }
    /*
     * $ps_os_payment_id = (int)Configuration::get('PS_OS_PAYMENT');
     * $ps_os_preparation_id = (int)Configuration::get('PS_OS_PREPARATION');
     * $ps_os_shipping_id = (int)Configuration::get('PS_OS_SHIPPING');
     * $ps_os_delivered_id = (int)Configuration::get('PS_OS_DELIVERED');
     * $ps_os_canceld_id = (int)Configuration::get('PS_OS_CANCELED');
     * $ps_os_refund_id = (int)Configuration::get('PS_OS_REFUND');
     * $ps_os_error_id = (int)Configuration::get('PS_OS_ERROR');
     * $ps_os_outofstock_id = (int)Configuration::get('PS_OS_OUTOFSTOCK');
     * $ps_os_cheque_id = (int)Configuration::get('PS_OS_CHEQUE');
     * $ps_os_bankwire_id = (int)Configuration::get('PS_OS_BANKWIRE');
     * $ps_os_paypal_id = (int)Configuration::get('PS_OS_PAYPAL');
     * $ps_os_ws_payment_id = (int)Configuration::get('PS_OS_WS_PAYMENT');
     * $ps_os_outofstock_paid_id = (int)Configuration::get('PS_OS_OUTOFSTOCK_PAID');
     * $ps_os_outofstock_unpaid_id = (int)Configuration::get('PS_OS_OUTOFSTOCK_UNPAID');
     */
}
