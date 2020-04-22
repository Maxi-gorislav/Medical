{*
 * Carts Guru
 *
 * @author    LINKT IT
 * @copyright Copyright (c) LINKT IT 2016
 * @license   Commercial license
 *}

<section id="cartsguru" class="panel widget">
    <div class="panel-heading">
        <i class="icon-time"></i>{l s='Carts Guru Dashboard' mod='cartsguru'}
        <span class="panel-heading-action">
            <a class="list-toolbar-btn" href="#" onclick="refreshDashboard('cartsguru'); return false;" title="{l s='Refresh' mod='cartsguru'}">
                <i class="process-icon-refresh"></i>
            </a>
        </span>
    </div>
    <section id="dash_cg_main" class="">
        <ul class="data_list_large">
            <li>
                <span class="data_label size_l"> {l s='Processed Carts' mod='cartsguru'}</span>
                <span class="data_value size_xxl">
                    <span id="cg_processed_carts"></span>
                </span>
            </li>
            <li>
                <span class="data_label size_l"> {l s='Orders' mod='cartsguru'}</span>
                <span class="data_value size_xxl">
                    <span id="cg_sales"></span>
                </span>
            </li>
            <li>
                <span class="data_label size_l">{l s='Revenue Recovered' mod='cartsguru'}</span>
                <span class="data_value size_xxl">
                    <span id="cg_turnover"></span>
                </span>
            </li>
        </ul>
    </section>
</section>
