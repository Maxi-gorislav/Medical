<?php
/**
 * Carts Guru
 *
 * @author    LINKT IT
 * @copyright Copyright (c) LINKT IT 2016
 * @license   Commercial license
 */

abstract class CartsGuruMapperAbstract
{
    public $id_lang;

    public $id_shop_group;

    public $id_shop;

    public $link;

    public function __construct($id_lang, $id_shop_group, $id_shop)
    {
        $this->id_lang = $id_lang;
        $this->id_shop_group = (int) $id_shop_group;
        $this->id_shop = (int) $id_shop;
        $protocol_link = Configuration::get(
            'PS_SSL_ENABLED',
            null,
            $this->id_shop_group,
            $this->id_shop
        ) ? 'https://' : 'http://';
        $use_ssl = Configuration::get('PS_SSL_ENABLED') ? true : false;
        $protocol_content = ($use_ssl) ? 'https://' : 'http://';
        $this->link = new Link($protocol_link, $protocol_content);
    }

    /**
     * Populate ps object to cart guru array
     *
     * @param Object $obj
     * @return array data
     */
    public function create($obj, $params = array())
    {
        $cart_guru_mapped = array();
        if ($obj) {
            $cart_guru_mapped = $this->mappObject($obj, $params);
        }
        return $cart_guru_mapped;
    }

    /**
     * If value is empty return ''
     *
     * @param
     *            $value
     * @return string
     */
    protected function notEmpty($value)
    {
        return ($value) ? $value : '';
    }

    /**
     * This method format date in json format
     *
     * @param
     *            $date
     * @return bool string
     */
    protected function formatDate($date)
    {
        return date('Y-m-d\TH:i:sP', strtotime($date));
    }

    /**
     * Populate the Carts Guru Data from PS object
     *
     * To be implemented by the concrete mapper class
     *
     * @param Object $psobj
     * @param array $params
     * @return array
     */
    abstract public function mappObject($obj, $params);
}
