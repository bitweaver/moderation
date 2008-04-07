<div class="display moderation">
	<div class="header">
		<h1>Request Completed</h1>
	</div>
	<div class="body">
		{if $modMsg}
			<p>{$modMsg}</p>
		{else}
			{tr}Moderation transaction is complete.{/tr}
		{/if}
		<p><a href="{$smarty.const.MODERATION_PKG_URL}">View all of your pending moderation submissions and requests</a></p>
	</div>
</div>
