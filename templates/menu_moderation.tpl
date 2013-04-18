{strip}
{if $gBitSystem->isPackageActive('moderation')}
{if $packageMenuTitle}<a class="dropdown-toggle" data-toggle="dropdown" href="#"> {tr}{$packageMenuTitle}{/tr} <b class="caret"></b></a>{/if}
<ul class="{$packageMenuClass}">
		<li><a href="{$smarty.const.MODERATION_PKG_URL}index.php">{booticon iname="icon-list" ipackage="icons" iexplain="Moderations" ilocation=menu}</a></li>
	</ul>
{/if}
{/strip}
