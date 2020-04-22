<?php
/**
* Prestashop Addons | Module by: <App1Pro>
*
* @author    Chuyen Nguyen [App1Pro].
* @copyright Chuyenim@gmail.com
* @license   http://app1pro.com/license.txt
*/

class RelatedProductProModel extends ObjectModel
{
    public $id_product;
    public $id_related;
    public $id_shop;

    public static $definition = array(
        'table' => 'product_related_pro',
        'primary' => array('id_product', 'id_related', 'id_shop'),
        'multilang'=>true,
        'fields' => array(
                'id_product' 		=> array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
                'id_related' 		=> array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
                'id_shop' 			=> array('type' => self::TYPE_INT, 'validate' => 'isInt')
        ),
    );

    public function __construct($id_product, $id_shop = 1)
    {
        $this->id_product = (int)$id_product;
        $this->id_shop = (int)$id_shop;
    }

    public function getRelatedProducts($reverse = false, $relation = 0)
    {
        $id_lang = (int)Context::getContext()->language->id;

        if ($relation == 3) {
            return Db::getInstance()->executeS('
                    SELECT DISTINCT `id_product` AS `id_related`
                            FROM `'._DB_PREFIX_.'category_product` WHERE `id_product`!= '.(int)$this->id_product.'
                            AND `id_category` IN (SELECT `id_category` FROM `'._DB_PREFIX_.'category_product` WHERE `id_product` = '.(int)$this->id_product.' ORDER BY `position` ASC)');
        } elseif ($relation == 2) {
            return Db::getInstance()->executeS('
                    SELECT DISTINCT `id_product` AS `id_related`
                            FROM `'._DB_PREFIX_.'category_product` WHERE `id_product`!= '.(int)$this->id_product.'
                            AND `id_category` IN (SELECT `id_category_default` FROM `'._DB_PREFIX_.'product` WHERE `id_product` = '.(int)$this->id_product.')');
        } elseif ($relation == 1) {
            return Db::getInstance()->executeS('
                    SELECT DISTINCT `id_product` AS `id_related`
                            FROM `'._DB_PREFIX_.'product_tag` WHERE `id_product`!= '.(int)$this->id_product.'
                            AND `id_tag` IN (SELECT `id_tag` FROM `'._DB_PREFIX_.'product_tag` WHERE `id_product` = '.(int)$this->id_product.')');
        } else {
            $query1 = Db::getInstance()->executeS('SELECT DISTINCT prp.`id_related`, pl.`name`
                    FROM `'._DB_PREFIX_.'product_related_pro` prp
                    LEFT JOIN `'._DB_PREFIX_.'product` p ON (prp.id_related = p.id_product)
                    LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.id_product = p.id_product AND pl.`id_lang` = '.(int)$id_lang.')
                    WHERE p.`active` = 1 AND prp.`id_shop` = '.(int)$this->id_shop.' AND prp.`id_product` = '.(int)$this->id_product.'
                    ORDER BY prp.`id_related` ASC');

            $query2 = Db::getInstance()->executeS('SELECT DISTINCT prp.`id_product` AS `id_related`, pl.`name`
                    FROM `'._DB_PREFIX_.'product_related_pro` prp
                    LEFT JOIN `'._DB_PREFIX_.'product` p ON (prp.`id_product` = p.`id_product`)
                    LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.id_product = p.id_product AND pl.`id_lang` = '.(int)$id_lang.')
                    WHERE p.`active` = 1
                        AND prp.`id_shop` = '.(int)$this->id_shop.'
                        AND prp.`id_related` = '.(int)$this->id_product.'
                        AND prp.`id_product` NOT IN (SELECT `id_related` FROM `'._DB_PREFIX_.'product_related_pro` WHERE `id_product` = '.(int)$this->id_product.')
                    ORDER BY prp.`id_related` ASC');

            if ($reverse) {
                return array_filter(array_merge($query1, $query2));
            } else {
                return $query1;
            }
        }
    }

    public function relatedUpdate()
    {
        $related_add = Tools::getValue('add_id_product');
        if (is_array($related_add)) {
            foreach ($related_add as $id_related) {
                $this->addRelatedProduct($id_related);
            }
        }

        $related_delete = Tools::getValue('related_delete');
        if (is_array($related_delete)) {
            foreach ($related_delete as $id_deleted) {
                $this->removeRelatedProduct($id_deleted);
            }
        }
    }

    public function addRelatedProduct($id_related)
    {
        return Db::getInstance()->Execute('
            REPLACE INTO `'._DB_PREFIX_.'product_related_pro` SET
                `id_product` = '.(int)$this->id_product.',
                `id_related` = '.(int)$id_related.',
                `id_shop` = '.(int)$this->id_shop);
    }

    public function removeRelatedProduct($id_related)
    {
        return (int)Db::getInstance()->execute('
            DELETE FROM `'._DB_PREFIX_.'product_related_pro`
            WHERE (`id_product` = '.(int)$this->id_product.' AND `id_related` = '.(int)$id_related.')
                OR (`id_product` = '.(int)$id_related.' AND `id_related` = '.(int)$this->id_product.')
            AND `id_shop` = '.(int)$this->id_shop);
    }

    public function getProductData($id_product)
    {
        $sql = 'SELECT product_shop.id_product, product_attribute_shop.id_product_attribute
                FROM `'._DB_PREFIX_.'product` p
                '.Shop::addSqlAssociation('product', 'p').'
                LEFT JOIN  `'._DB_PREFIX_.'product_attribute` pa ON (product_shop.id_product = pa.id_product)
                '.Shop::addSqlAssociation('product_attribute', 'pa', false, 'product_attribute_shop.default_on = 1').'
                WHERE product_shop.`active` = 1 
                AND product_shop.`id_product`='.(int)$id_product.' 
                AND p.`id_product`='.(int)$id_product.' 
                AND product_shop.`visibility` IN ("both", "catalog") 
                AND (pa.id_product_attribute IS NULL OR product_attribute_shop.default_on = 1)
                GROUP BY product_shop.id_product';

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        $sql = 'SELECT p.*, product_shop.*, stock.`out_of_stock` out_of_stock, pl.`description`, pl.`description_short`,
                    pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`,
                    p.`ean13`, p.`upc`, image_shop.`id_image`, il.`legend`, t.`rate`
                FROM `'._DB_PREFIX_.'product` p
                LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (
                    p.`id_product` = pl.`id_product`
                    AND pl.`id_lang` = '.(int)Context::getContext()->cookie->id_lang.Shop::addSqlRestrictionOnLang('pl').'
                )
                '.Shop::addSqlAssociation('product', 'p').'
                LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`)'.
                Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
                LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)Context::getContext()->cookie->id_lang.')
                LEFT JOIN `'._DB_PREFIX_.'tax_rule` tr ON (product_shop.`id_tax_rules_group` = tr.`id_tax_rules_group`
                    AND tr.`id_country` = '.(int)Context::getContext()->country->id.'
                    AND tr.`id_state` = 0)
                LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = tr.`id_tax`)
                '.Product::sqlStock('p', 0).'
                WHERE p.id_product = '.(int)$id_product.'
                AND (i.id_image IS NULL OR image_shop.id_shop='.(int)Context::getContext()->shop->id.')';

        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        if (!$row) {
            return false;
        }

        if ($result['id_product_attribute']) {
            $row['id_product_attribute'] = $result['id_product_attribute'];
        }

        $product = Product::getProductProperties(Context::getContext()->cookie->id_lang, $row);

        if ($product['reduction']) {
            // Price without TAX
            $product['price_without_reduction_without_tax'] = Tools::displayPrice($product['price_tax_exc'] + $product['reduction']);
            // Price with tax included
            $product['price_without_reduction'] = Tools::displayPrice(
                Product::getPriceStatic(
                    $product['id_product'],
                    true,
                    $product['id_product_attribute'],
                    6,
                    null,
                    false,
                    false
                )
            );
        }

        $product['quantity'] = StockAvailable::getQuantityAvailableByProduct($product['id_product'], $product['id_product_attribute']);

        return $product;
    }
}
