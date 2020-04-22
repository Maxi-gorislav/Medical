<link rel="stylesheet" type="text/css" href="{$this_path}testimonial.css" media="screen" />
    <h3>Testimonial Message</h3>
 
        {foreach from=$testimonials item=testmonial name=testimonial}
       	    <a id="testmonial{$testmonial.id_testimonial}"></a>
                <div class="dis_testi">
                    <div class="title">{$testmonial.teste_name}</div>
                        <p><i style="font-weight:normal;font-family:arial;">
                            "{$testmonial.teste_text|replace:'<p>':''|replace:'</p>':''}"
                            </i>
                        </p>                                  
                        <span>-<b>{$testmonial.teste_firstname}{$testmonial.teste_lastname}</b></span>
                        {if $testmonial.teste_company neq ''}
                            <span>-{$testmonial.teste_company}</span>
                        {/if}
               	</div>
        {/foreach}
    {if $total<1}
        {l s='Be The First to Write A Testimonial!' mod='testimonial'}
    {/if}
    {if $teste_write==1}
	{if $this_version=="1.4"}
	    <a href="testi_form.php"  style="margin:0 0 0 450px">{l s='Write Testimonial' mod='testimonial'} </a>
	    {else}
	<a href="index.php?fc=module&module=testimonial&controller=testi_form"  style="float:right; margin-right:10px; margin-top:10px">{l s='Write Testimonial' mod='testimonial'} </a>
	{/if}
    {/if}
    {if $this_version=="1.4"}
      {if $start!=$stop}

    <ul class="pagination">
    {if $p != 1}
        {assign var='p_previous' value=$p-1}
        <li id="pagination_previous"><a href="{$link->goPage($requestPage, $p_previous)}">&laquo;&nbsp;{l s='Previous'}</a></li>
    {else}
        <li id="pagination_previous" class="disabled"><span>&laquo;&nbsp;{l s='Previous'}</span></li>
    {/if}

    {if $start-$range<$range}
        {section name=pagination_start start=1 loop=$start step=1}
            <li><a href="{$link->goPage($requestPage, $smarty.section.pagination_start.index)}">{$smarty.section.pagination_start.index|escape:'htmlall':'UTF-8'}</a></li>
        {/section}
    {/if}

    {if $start>3}
        <li><a href="{$link->goPage($requestPage, 1)}">1</a></li>
        <li class="truncate">...</li>
    {/if}
    {section name=pagination start=$start loop=$stop+1 step=1}
        {if $p == $smarty.section.pagination.index}
            <li class="current"><span>{$p|escape:'htmlall':'UTF-8'}</span></li>
        {else}
            <li><a href="{$link->goPage($requestPage, $smarty.section.pagination.index)}">{$smarty.section.pagination.index|escape:'htmlall':'UTF-8'}</a></li>
        {/if}
    {/section}
    {if $pages_nb>$stop+2}
        <li class="truncate">...</li>
        <li><a href="{$link->goPage($requestPage, $pages_nb)}">{$pages_nb|intval}</a></li>
    {/if}

    {if $stop+$range>$pages_nb-1}
        {section name=pagination_start start=$stop+1 loop=$pages_nb+1 step=1}
            <li><a href="{$link->goPage($requestPage, $smarty.section.pagination_start.index)}">{$smarty.section.pagination_start.index|escape:'htmlall':'UTF-8'}</a></li>
        {/section}
    {/if}

    {if $pages_nb > 1 AND $p != $pages_nb}
        {assign var='p_next' value=$p+1}
        <li id="pagination_next"><a href="{$link->goPage($requestPage, $p_next)}">{l s='Next'}&nbsp;&raquo;</a></li>
    {else}
        <li id="pagination_next" class="disabled"><span>{l s='Next'}&nbsp;&raquo;</span></li>
    {/if}
    </ul>
{/if}{/if}

 {if $this_version=="1.5"}
      {if $start!=$stop}

    <ul class="pagination">
    {if $p != 1}
        {assign var='p_previous' value=$p-1}
        <li id="pagination_previous"><a href="{$link->goPage($requestPage, $p_previous)}">&laquo;&nbsp;{l s='Previous'}</a></li>
    {else}
        <li id="pagination_previous" class="disabled"><span>&laquo;&nbsp;{l s='Previous'}</span></li>
    {/if}

    {if $start-$range<$range}
        {section name=pagination_start start=1 loop=$start step=1}
            <li><a href="{$link->goPage($requestPage, $smarty.section.pagination_start.index)}">{$smarty.section.pagination_start.index|escape:'htmlall':'UTF-8'}</a></li>
        {/section}
    {/if}

    {if $start>3}
        <li><a href="{$link->goPage($requestPage, 1)}">1</a></li>
        <li class="truncate">...</li>
    {/if}
    {section name=pagination start=$start loop=$stop+1 step=1}
        {if $p == $smarty.section.pagination.index}
            <li class="current"><span>{$p|escape:'htmlall':'UTF-8'}</span></li>
        {else}
            <li><a href="{$link->goPage($requestPage, $smarty.section.pagination.index)}">{$smarty.section.pagination.index|escape:'htmlall':'UTF-8'}</a></li>
        {/if}
    {/section}
    {if $pages_nb>$stop+2}
        <li class="truncate">...</li>
        <li><a href="{$link->goPage($requestPage, $pages_nb)}">{$pages_nb|intval}</a></li>
    {/if}

    {if $stop+$range>$pages_nb-1}
        {section name=pagination_start start=$stop+1 loop=$pages_nb+1 step=1}
            <li><a href="{$link->goPage($requestPage, $smarty.section.pagination_start.index)}">{$smarty.section.pagination_start.index|escape:'htmlall':'UTF-8'}</a></li>
        {/section}
    {/if}

    {if $pages_nb > 1 AND $p != $pages_nb}
        {assign var='p_next' value=$p+1}
        <li id="pagination_next"><a href="{$link->goPage($requestPage, $p_next)}">{l s='Next'}&nbsp;&raquo;</a></li>
    {else}
        <li id="pagination_next" class="disabled"><span>{l s='Next'}&nbsp;&raquo;</span></li>
    {/if}
    </ul>
{/if}{/if}

{if $this_version=="1.6"}
      {if $start!=$stop}

    <ul class="pagination">
    {if $p != 1}
        {assign var='p_previous' value=$p-1}
        <li id="pagination_previous"><a href="{$link->goPage($requestPage, $p_previous)}">&laquo;&nbsp;{l s='Previous'}</a></li>
    {else}
        <li id="pagination_previous" class="disabled"><span>&laquo;&nbsp;{l s='Previous'}</span></li>
    {/if}

    {if $start-$range<$range}
        {section name=pagination_start start=1 loop=$start step=1}
            <li><a href="{$link->goPage($requestPage, $smarty.section.pagination_start.index)}">{$smarty.section.pagination_start.index|escape:'htmlall':'UTF-8'}</a></li>
        {/section}
    {/if}

    {if $start>3}
        <li><a href="{$link->goPage($requestPage, 1)}">1</a></li>
        <li class="truncate">...</li>
    {/if}
    {section name=pagination start=$start loop=$stop+1 step=1}
        {if $p == $smarty.section.pagination.index}
            <li class="current"><span>{$p|escape:'htmlall':'UTF-8'}</span></li>
        {else}
            <li><a href="{$link->goPage($requestPage, $smarty.section.pagination.index)}">{$smarty.section.pagination.index|escape:'htmlall':'UTF-8'}</a></li>
        {/if}
    {/section}
    {if $pages_nb>$stop+2}
        <li class="truncate">...</li>
        <li><a href="{$link->goPage($requestPage, $pages_nb)}">{$pages_nb|intval}</a></li>
    {/if}

    {if $stop+$range>$pages_nb-1}
        {section name=pagination_start start=$stop+1 loop=$pages_nb+1 step=1}
            <li><a href="{$link->goPage($requestPage, $smarty.section.pagination_start.index)}">{$smarty.section.pagination_start.index|escape:'htmlall':'UTF-8'}</a></li>
        {/section}
    {/if}

    {if $pages_nb > 1 AND $p != $pages_nb}
        {assign var='p_next' value=$p+1}
        <li id="pagination_next"><a href="{$link->goPage($requestPage, $p_next)}">{l s='Next'}&nbsp;&raquo;</a></li>
    {else}
        <li id="pagination_next" class="disabled"><span>{l s='Next'}&nbsp;&raquo;</span></li>
    {/if}
    </ul>
{/if}{/if}

