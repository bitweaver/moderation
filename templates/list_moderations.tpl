{strip}

<h3>{tr}Requests You Can Moderate{/tr}</h3>
<ul>
{assign var=had_moderation value=false}
{assign var=last_package value=false}
{assign var=last_type value=false}
{foreach from=$myModerations item=moderation}
	{if $moderation.responsible == $smarty.const.MODERATION_NEEDED &&
		($moderation.moderator_id == $gBitUser->mUserId ||
	    	array_key_exists($moderation.moderator_group_id, $gBitUser->mGroups))}
		{assign var=had_moderation value=true}
		{if $last_package != $moderation.package}
			{if $last_package}
				</ul></li></ul></div>
			{/if}
			{assign var=last_package value=$moderation.package}
			{assign var=last_type value=$moderation.type}
				<div class="row">
					<h3>{$moderation.package|ucwords}</h3>
						<ul>
							<li><h4>{$moderation.type|ucwords}</h4>
								<ul>
		{elseif $last_type != $moderation.type}
			</ul></li>
			<li><h4>{$moderation.type|ucwords}</h4>
				</ul>
		{/if}
		<li>{include file=`$gModerationSystem->mPackages[$moderation.package].moderate_tpl`}</li>
	{/if}
{/foreach}
{if !$had_moderation}
	<li>{tr}There are no requests you can moderate{/tr}</li>
{else}
	</ul></li></ul></div>
{/if}
</ul>

<h3>{tr}Your Requests Pending Moderation{/tr}</h3>
<ul>
{assign var=had_moderation value=false}
{assign var=last_package value=false}
{assign var=last_type value=false}
{foreach from=$myModerations item=moderation}
	{if $moderation.source_user_id == $gBitUser->mUserId}
		{assign var=had_moderation value=true}
		{if $last_package != $moderation.package}
			{if $last_package}
				</ul></li></ul></div>
			{/if}
			{assign var=last_package value=$moderation.package}
			{assign var=last_type value=$moderation.type}
				<div class="row">
					<h3>{$moderation.package|ucwords}</h3>
						<ul>
							<li><h4>{$moderation.type|ucwords}</h4>
								<ul>
		{elseif $last_type != $moderation.type}
			</ul></li>
			<li><h4>{$moderation.type|ucwords}</h4>
				</ul>
		{/if}
		<li>{include file=`$gModerationSystem->mPackages[$moderation.package].request_tpl`}</li>
	{/if}
{/foreach}
{if !$had_moderation}
	<li>{tr}You have no requests awaiting moderation.{/tr}</li>
{else}
	</ul></li></ul></div>
{/if}
</ul>
{/strip}
