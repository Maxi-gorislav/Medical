{*
 * 2008 - 2015 HDClic
 *
 * MODULE PrestaBlog
 *
 * @version   3.6.3
 * @author    HDClic <prestashop@hdclic.com>
 * @link      http://www.hdclic.com
 * @copyright Copyright (c) permanent, HDClic
 * @license   Addons PrestaShop license limitation
 *
 * NOTICE OF LICENSE
 *
 * Don't use this module on several shops. The license provided by PrestaShop Addons
 * for all its modules is valid only once for a single shop.
 *}

<!-- Module Presta Blog -->
{if sizeof($comments)}
<h3><span id="toggle-comments">{l s='View all comments' mod='prestablog'}</span>&nbsp;<small>({count($comments)|intval})</small></h3>
<div id="comments">
{foreach from=$comments item=comment name=Comment}
	<div class="comment">
		<h4>
			{if $comment.url}
				<a href="{$comment.url|escape:'html':'UTF-8'}" {if $prestablog_config.prestablog_comment_nofollow}rel="nofollow"{/if}>{$comment.name|escape:'htmlall':'UTF-8'}</a>
			{else}
				{$comment.name|escape:'htmlall':'UTF-8'}
			{/if}
		</h4>
		<hr />
		<p class="date-comment">{dateFormat date=$comment.date full=1}</p>
		<p>{$comment.comment|escape:'htmlall':'UTF-8'}</p>
	</div>
{/foreach}
</div>
<script type="text/javascript">
	{literal}
	$(document).ready(function() {
		{/literal}{if $prestablog_config.prestablog_comment_autoshow==1}$('#comments').show();{/if}{literal}
		$('#toggle-comments').click(function() {
			$('#comments').toggle("slow");
		});
	});
	{/literal}
</script>
{/if}
{if ($prestablog_config.prestablog_comment_only_login && $logged) || !$prestablog_config.prestablog_comment_only_login}
	{if !$isSubmit}
		<form action="{$LinkReal|escape:'html':'UTF-8'}&id={$news->id|intval}" method="post" class="std">
			<fieldset id="prestablog-comment">
				<h3>
					{l s='Add a comment' mod='prestablog'}
					{if $prestablog_config.prestablog_comment_subscription}
						<div id="abo">
						{if $Is_Subscribe}
							<a href="{$LinkReal|escape:'html':'UTF-8'}&d={$news->id|intval}">{l s='Stop my subscription to comments' mod='prestablog'}</a>
						{else}
							<a href="{$LinkReal|escape:'html':'UTF-8'}&a={$news->id|intval}">{l s='Subscribe to comments' mod='prestablog'}</a>
						{/if}
						</div>
					{/if}
				</h3>
				{if sizeof($errors)}
				<p id="errors">{foreach from=$errors item=Ierror name=errors}{$Ierror|escape:'htmlall':'UTF-8'}<br />{/foreach}</p>
				<script type="text/javascript">{literal}$(document).ready(function() { $('html, body').animate({scrollTop: $("#errors").offset().top}, 750); });{/literal}</script>
				{/if}
				<p class="text">
					<label for="name">{l s='Name' mod='prestablog'}</label>
					<input type="text" class="text{if sizeof($errors) && array_key_exists('name', $errors)} errors{/if}" name="name" id="name" value="{$content_form.name|escape:'htmlall':'UTF-8'}" />
				</p>
				<p class="text">
					<label for="url">{l s='Url' mod='prestablog'}</label>
					<input type="text" class="text{if sizeof($errors) && array_key_exists('url', $errors)} errors{/if}" name="url" id="url" value="{$content_form.url|escape:'html':'UTF-8'}" />&nbsp;<small id="with-http">({l s='with http://' mod='prestablog'})</small>
				</p>
				<p class="textarea">
					<label for="comment">{l s='Comment' mod='prestablog'}</label>
					<textarea name="comment" id="comment" cols="26" rows="3" {if sizeof($errors) && array_key_exists('comment', $errors)}class="errors"{/if}>{$content_form.comment|escape:'htmlall':'UTF-8'}</textarea>
				</p>
				{if isset($AntiSpam)}
					<p class="text">
						<label for="{$AntiSpam.checksum|escape:'htmlall':'UTF-8'}">{l s='Antispam protection' mod='prestablog'} : <strong>{$AntiSpam.question|escape:'htmlall':'UTF-8'}</strong></label>
						<input type="text" class="text{if sizeof($errors) && array_key_exists($AntiSpam.checksum, $errors)} errors{/if}" name="{$AntiSpam.checksum|escape:'htmlall':'UTF-8'}" id="{$AntiSpam.checksum|escape:'htmlall':'UTF-8'}" value="{$content_form.antispam_checksum|escape:'htmlall':'UTF-8'}" />
					</p>
				{/if}
				<p class="submit">
					<input type="submit" class="button" name="submitComment" id="submitComment" value="{l s='Submit comment' mod='prestablog'}" />
				</p>
			</fieldset>
		</form>
		<script type="text/javascript">
			{literal}
			$(document).ready(function() {
				$("#with-http").hide();
				$("#url").focus(function() {
					$("#with-http").fadeIn();
				});
				$("#url").focusout(function() {
					$("#with-http").fadeOut();
				});
			});
			{/literal}
		</script>
	{else}
		<form id="submitOk" class="std">
			<fieldset>
				<h3>{l s='Your comment has successfully sent' mod='prestablog'}</h3>
				{if $prestablog_config.prestablog_comment_auto_actif}
				<p>{l s='This comment is automatically published.' mod='prestablog'}</p>
				{else}
				<p>{l s='Before published, your comment must be approve by an administrator.' mod='prestablog'}</p>
				{/if}
			</fieldset>
		</form>
		<script type="text/javascript">{literal}$(document).ready(function() { $('html, body').animate({scrollTop: $("#submitOk").offset().top}, 750); });{/literal}</script>
	{/if}
{else}
	<form class="std" >
		<fieldset id="prestablog-comment-register">
			<h3>{l s='You must be register' mod='prestablog'}</h3>
			<p style="text-align:center;">
				<a href="{$link->getPageLink('authentication', true)|escape:'html':'UTF-8'}">{l s='Clic here to register' mod='prestablog'}</a>
			</p>
		</fieldset>
		<fieldset id="prestablog-comment">
			<h3>
				{l s='Add a comment' mod='prestablog'}
				{if $prestablog_config.prestablog_comment_subscription}
					<div id="abo">
						<a>{l s='Subscribe to comments' mod='prestablog'}</a>
					</div>
				{/if}
			</h3>
			<p class="text">
				<label for="name">{l s='Name' mod='prestablog'}</label>
				<input type="text" class="text" name="name" id="name" value="" />
			</p>
			<p class="text">
				<label for="url">{l s='Url' mod='prestablog'}</label>
				<input type="text" class="text" name="url" id="url" value="" />
			</p>
			<p class="textarea">
				<label for="comment">{l s='Comment' mod='prestablog'}</label>
				<textarea name="comment" id="comment" cols="26" rows="3" ></textarea>
			</p>
			<p class="submit">
				<input type="submit" class="button" value="{l s='Submit comment' mod='prestablog'}" />
			</p>
		</fieldset>
	</form>
	<script type="text/javascript">
		{literal}
		$(document).ready(function() {
			$("form.std").submit(function() {
				return false;
			});
			$("#prestablog-comment-register").hide();
			$("#prestablog-comment").click(function() {
				$("#prestablog-comment").fadeOut();
				$("#prestablog-comment-register").fadeIn();
			});
		});
		{/literal}
	</script>
{/if}
<!-- /Module Presta Blog -->
