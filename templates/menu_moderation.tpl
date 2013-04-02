{strip}
{if $gBitSystem->isPackageActive('moderation')}
	<ul>
		<li><a href="{$smarty.const.MODERATION_PKG_URL}index.php">{booticon iname="icon-list" ipackage="icons" iexplain="Moderations" ilocation=menu}</a></li>
	</ul>
{/if}
{/strip}
