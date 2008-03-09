{strip}
<div class="row data">
	{tr}User{/tr}: {displayname user_id=$moderation.source_user_id}<br/>
	{if !empty($moderation.content_id)}
		{tr}Content{/tr}: <a href="{$smarty.const.BIT_ROOT_URL}index.php?content_id={$moderation.content_id}">{$moderation.title|escape:html}</a><br/>
	{/if}
	Status: {$moderation.status}<br/>
	{if !empty($moderation.request)}
		{tr}Request{/tr}: {$moderation.request|escape:html}<br/>
	{/if}
</div>
{form}
	<input type=hidden name=moderation_id value="{$moderation.moderation_id}" />
	<div class="row reply">
		{$moderation.reply|escape:html}
		{if $moderation.responsible == 0}
			<textarea name="reply" id="reply-{$moderation.moderation_id}"></textarea>
		{/if}
	</div>
	<div class="row submit">
		{foreach from=$moderation.transitions item=transition}
			<input type=submit name="transition" value="{$transition}" />&nbsp;
		{/foreach}
	</div>
{/form}
{/strip}
 
