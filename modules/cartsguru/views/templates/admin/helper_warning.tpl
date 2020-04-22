{*
 * Carts Guru
 *
 * @author    LINKT IT
 * @copyright Copyright (c) LINKT IT 2016
 * @license   Commercial license
 *}

{if $ps_version < "1.6"}
	<div class="warn">
{else}
	<div class="bootstrap">
		<div class="module_warning alert alert-warning" >
			<button type="button" class="close" data-dismiss="alert">&times;</button>
{/if}
{if $warning|is_array}
<ul>
	{foreach from=warning item="warnmessage"}
     <li>{$warnmessage|escape:'html':'UTF-8'}</li>
    {/foreach}
</ul>
{else}
	{$warning|escape:'html':'UTF-8'}
{/if}
{if $ps_version < "1.6"}
  </div></div>
{else}
  </div>
{/if}
