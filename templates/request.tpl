{strip}
<div class="row data">
	{if !empty($moderation.content_id)}
		{tr}Content{/tr}: <a href="{$smarty.const.BIT_ROOT_URL}index.php?content_id={$moderation.content_id}">{$moderation.title}</a><br/>
	{/if}
	Status: {$moderation.status}<br/>
	{if !empty($moderation.request)}
		{tr}Request{/tr}: {$moderation.request}<br/>
	{/if}
</div>
{if $moderation.responsible == $smarty.const.MODERATION_GIVEN}
	{form}
		<input type=hidden name=moderation_id value="{$moderation.moderation_id}" />
		<div class="row reply">
			{if empty($moderation.reply)}
				<textarea name="reply" id="reply-{$moderation.moderation_id}">{$moderation.reply}</textarea>
			{else}
				{$moderation.reply}
			{/if}
		</div>
		<div class="row submit">
			{foreach from=$moderation.transitions item=transition}
				<input type=submit name="transition" value="{$transition}" />&nbsp;
			{/foreach}
		</div>
	{/form}
{/if}
{/strip}
