{strip}
{if $gBitSystem->isPackageActive('moderation')}
	<ul>
		<li><a href="{$smarty.const.MODERATION_PKG_URL}"index.php">{tr}Moderations{/tr}</li>
	</ul>
{/if}
{/strip}