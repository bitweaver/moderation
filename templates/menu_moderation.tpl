{strip}
{if $gBitSystem->isPackageActive('moderation')}
	<ul>
		<li><a href="{$smarty.const.MODERATION_PKG_URL}index.php">{biticon ipackage="icons" iname="format-justify-fill" iexplain="Moderations" ilocation=menu}</a></li>
	</ul>
{/if}
{/strip}
