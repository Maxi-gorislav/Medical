{*
 * Carts Guru
 *
 * @author    LINKT IT
 * @copyright Copyright (c) LINKT IT 2016
 * @license   Commercial license
 *}

<script>
    {if isset($order) && $order}
        fbq('track', 'Purchase', {
            content_ids: ['{$order.products|escape:'htmlall':'UTF-8'}'],
            content_type: 'product',
            value: {$order.total|escape:'htmlall':'UTF-8'},
            currency: '{$currency_iso|escape:'htmlall':'UTF-8'}',
            product_catalog_id: {$catalogId|escape:'htmlall':'UTF-8'}
        });
    {/if}
</script>
