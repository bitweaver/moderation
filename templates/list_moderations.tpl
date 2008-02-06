{strip}

<h3>{tr}Requests You Can Moderate{/tr}</h3>
<ul>
{assign var=had_moderation value=false}
{foreach from=$myModerations item=moderation}
	{if $moderation.source_user_id != $gBitUser->mUserId}
		{assign var=had_moderation value=true}
		{include file='bitpackage:moderation/moderate.tpl'}
	{/if}
{/foreach}
{if !$had_moderation}
	<li>{tr}There are no requests you can moderate{/tr}</li>
{/if}
</ul>

<h3>{tr}Your Requests Pending Moderation{/tr}</h3>
<ul>
{assign var=had_moderation value=false}
{foreach from=$myModerations item=moderation}
	{if $moderation.source_user_id != $gBitUser->mUserId}
		{assign var=had_moderation value=true}
		{include file='bitpackage:moderation/request.tpl'}
	{/if}
{/foreach}
{if !$had_moderation}
	<li>{tr}You have no requests awaiting moderation.{/tr}</li>
{/if}
</ul>

{/strip}
