{strip}
<div class="display moderation">
	
	<div class="header">
		<h1>{tr}Moderations List{/tr}</h1>
	</div>
	
	{assign var=had_moderation value=false}
	{assign var=last_package value=false}
	{assign var=last_type value=false}
	{capture assign=list_moderations}{strip}
	{foreach from=$myModerations item=moderation}
		{if $moderation.responsible == $smarty.const.MODERATION_NEEDED &&
			($moderation.moderator_id == $gBitUser->mUserId ||
		    	array_key_exists($moderation.moderator_group_id, $gBitUser->mGroups) ||
			$gBitUser->isAdmin())}
			{assign var=had_moderation value=true}
			{if $last_package != $moderation.package}
				{if $last_package}
					</ul></li></ul></div>
				{/if}
				{assign var=last_package value=$moderation.package}
				{assign var=last_type value=$moderation.type}
					<div class="control-group">
						<h3>{$moderation.package|ucwords}</h3>
							<ul>
								<li><h4>{$moderation.type|ucwords}</h4>
									<ul>
			{elseif $last_type != $moderation.type}
				</ul></li>
				<li><h4>{$moderation.type|ucwords}</h4>
					</ul>
			{/if}
			<li>{include file=$gModerationSystem->mPackages[$moderation.package].moderate_tpl}</li>
		{/if}
	{/foreach}
	{if $had_moderation}
		</ul></li></ul></div>
	{/if}
	{/strip}{/capture}
	
	{if $had_moderation}
	<h3>{tr}Requests You Can Moderate{/tr}</h3>
	<ul>{$list_moderations}</ul>
	{/if}
	
	{assign var=had_request value=false}
	{assign var=last_package value=false}
	{assign var=last_type value=false}
	{capture assign=list_requests}{strip}
	{foreach from=$myModerations item=moderation}
		{if $moderation.source_user_id == $gBitUser->mUserId}
			{assign var=had_request value=true}
			{if $last_package != $moderation.package}
				{if $last_package}
					</ul></li></ul></div>
				{/if}
				{assign var=last_package value=$moderation.package}
				{assign var=last_type value=$moderation.type}
					<div class="control-group">
						<h3>{$moderation.package|ucwords}</h3>
							<ul>
								<li><h4>{$moderation.type|ucwords}</h4>
									<ul>
			{elseif $last_type != $moderation.type}
				</ul></li>
				<li><h4>{$moderation.type|ucwords}</h4>
					</ul>
			{/if}
			<li>{include file=$gModerationSystem->mPackages[$moderation.package].request_tpl}</li>
		{/if}
	{/foreach}
	{if $had_request}
		</ul></li></ul></div>
	{/if}
	{/strip}{/capture}
	
	{if $had_request}
	<h3>{tr}Your Requests Pending Moderation{/tr}</h3>
	<ul>{$list_requests}</ul>
	{/if}
	
	{if !$had_request && !$had_moderation}
	<div class="control-group">
		{tr}You have nothing to moderate or awaiting moderation{/tr}
	</div>
	{/if}

</div>
{/strip}
