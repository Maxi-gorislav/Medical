<?php
/**
 * Carts Guru
 *
 * @author    LINKT IT
 * @copyright Copyright (c) LINKT IT 2016
 * @license   Commercial license
 */

include(dirname(__FILE__).'/../../../config/config.inc.php');
include(dirname(__FILE__).'/../../../init.php');
if (!class_exists('Context')) {
    include(dirname(__FILE__).'/../backward_compatibility/Context.php');
}

// Get input values
$offset = Tools::getValue('cartsguru_catalog_offset') ? Tools::getValue('cartsguru_catalog_offset') : 0;
$limit = Tools::getValue('cartsguru_catalog_limit') ? Tools::getValue('cartsguru_catalog_limit') : 50;

$context = Context::getContext();
$currency = new Currency((int)$context->currency->id);
$lang = Configuration::get('PS_LANG_DEFAULT');
$link = new Link();
$processed_products = array();
$isMultiStoreSupported = version_compare(_PS_VERSION_, '1.5.0', '>=');

$sql = 'SELECT p.id_product AS id FROM ' . _DB_PREFIX_ . 'product p';
$sqlTotal = 'SELECT count(p.id_product) as count FROM ' . _DB_PREFIX_ . 'product p';

// Need filter on the good shop
if ($isMultiStoreSupported) {
    $id_shop = (int)Context::getContext()->shop->id;

    $sql .= ' JOIN ' . _DB_PREFIX_ . 'product_shop s ON p.id_product = s.id_product WHERE id_shop = ' . $id_shop;
    $sqlTotal .= ' JOIN ' . _DB_PREFIX_ . 'product_shop s ON p.id_product = s.id_product WHERE id_shop = ' . $id_shop;
}

// Set limit and offset
$sql .= ' LIMIT ' . pSQL($limit) . ' OFFSET ' . pSQL($offset);

$results = Db::getInstance()->ExecuteS($sql);

if ($results) {
    foreach ($results as $row) {
        $product = new Product($row['id'], false, $lang);
        $image_link = '';
        $image = Image::getImages((int)($context->language->id), $product->id);
        if (is_array($image) && count($image) > 0) {
            $imageObj = new Image($image[0]['id_image']);
            $image_type = ImageType::getByNameNType('large', 'products');
            $image_link = $link->getImageLink($product->getLink(), $image[0]['id_image'], $image_type['name']);
        }
        $short_description = strip_tags($product->description_short);
        $description = strip_tags($product->description);
        $description = trim($short_description) === '' ? $description : $short_description;
        if (trim($description) === '') {
            continue;
        }
        $data = array(
            'id' => $product->id,
            'title' => $product->name,
            'description' => $description,
            'price' => Tools::ps_round($product->getPrice(), 2) . ' ' . $currency->iso_code,
            'link' => $link->getproductLink($product->id),
            'image_link' => $image_link,
            'availability' => $product->available_for_order == '1' ? 'in stock' : 'out of stock'
        );
        $processed_products[] = $data;
    }

    // Get total
    $total = Db::getInstance()->getRow($sqlTotal);

    header('Content-Type: application/json');
    echo Tools::jsonEncode(
        array(
            'url' => Tools::getShopDomainSsl(true, true),
            'store_name' => Configuration::get('PS_SHOP_NAME'),
            'products' => $processed_products,
            'total' => isset($total['count']) ? $total['count'] : 0
        )
    );
}
exit;
