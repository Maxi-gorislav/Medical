{*
 * Carts Guru
 *
 * @author    LINKT IT
 * @copyright Copyright (c) LINKT IT 2016
 * @license   Commercial license
 *}

<script>
    {literal}
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
        document,'script','//connect.facebook.net/en_US/fbevents.js');
    {/literal}
    {if isset($pixel) && $pixel}
        fbq('init', '{$pixel|escape:'htmlall':'UTF-8'}'); fbq('track', 'PageView');
        {if isset($track_product_view) && $track_product_view}
            fbq('track', 'ViewContent', {
                content_ids: ['{$track_product_view.id|escape:'htmlall':'UTF-8'}'],
                content_type: 'product',
                value: {$track_product_view.value|escape:'htmlall':'UTF-8'},
                currency: '{$currency_iso|escape:'htmlall':'UTF-8'}',
                product_catalog_id: '{$catalogId|escape:'htmlall':'UTF-8'}'
            });
        {/if}
        (function($){
            $(document).ajaxComplete(function(event, xhr, params) {
                if (xhr.responseJSON && xhr.responseJSON.productTotal && xhr.responseJSON.products) {
                    var products = [];
                    for (var i = 0; i < xhr.responseJSON.products.length; i++) {
                        products.push(xhr.responseJSON.products[i].id);
                    }
                    fbq('track', 'AddToCart', {
                        content_ids: products,
                        content_type: 'product',
                        value: parseFloat(xhr.responseJSON.productTotal.replace(',', '.')),
                        currency: '{$currency_iso|escape:'htmlall':'UTF-8'}',
                        product_catalog_id: '{$catalogId|escape:'htmlall':'UTF-8'}'
                    });
                }
            });
        })(jQuery);
    {/if}
</script>
