<?php
/**
* Prestashop Addons | Module by: <App1Pro>
*
* @author    Chuyen Nguyen [App1Pro].
* @copyright Chuyenim@gmail.com
* @license   http://app1pro.com/license.txt
*/

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'product_related_pro` (
            `id_product` int(11) unsigned NOT NULL,
            `id_related` int(11) NOT NULL,
            `id_shop` int(10) NOT NULL,
    PRIMARY KEY (`id_product`, `id_related`, `id_shop`)
    )ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
