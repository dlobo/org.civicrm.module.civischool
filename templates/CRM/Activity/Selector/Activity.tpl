{* Displays Activities. *}

<div>
  {if !$noFieldSet}	
  <fieldset>
  <legend>{ts}Activities{/ts}</legend>
  {/if}
{if $rows}
  <form title="activity_pager" action="{crmURL}" method="post">
  {include file="CRM/common/pager.tpl" location="top"}

  {strip}
    <table class="selector">
      <tr class="columnheader">
      {foreach from=$columnHeaders item=header}
      {if $header.name ne "Added By" and $header.name ne "Status"}
        <th scope="col">
        {if $header.sort}
          {assign var='key' value=$header.sort}
          {$sort->_response.$key.link}
        {else}
          {$header.name}
        {/if}
        </th>
      {/if}
      {/foreach}
      </tr>

      {counter start=0 skip=1 print=false}
      {foreach from=$rows item=row}
      <tr class="{cycle values="odd-row,even-row"} {$row.class}">

        <td>{$row.activity_type}</td>
       
    	<td>{$row.subject}</td>

        <td>
        {if $row.mailingId}
          <a href="{$row.mailingId}" title="{ts}View Mailing Report{/ts}">{$row.recipients}</a>
        {elseif $row.recipients}
          {$row.recipients}
        {elseif !$row.target_contact_name}
          <em>n/a</em>
        {elseif $row.target_contact_name}
            {assign var="showTarget" value=0}
            {foreach from=$row.target_contact_name item=targetName key=targetID}
                {if $showTarget < 5}
                    {if $showTarget};&nbsp;{/if}<a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$targetID`"}" title="{ts}View contact{/ts}">{$targetName}</a>
                    {assign var="showTarget" value=$showTarget+1}
                {/if}
            {/foreach}
            {if count($row.target_contact_name) > 5}({ts}more{/ts}){/if}
        {/if}
        </td>

        <td>
        {if !$row.assignee_contact_name}
            <em>n/a</em>
        {elseif $row.assignee_contact_name}
            {assign var="showAssignee" value=0}
            {foreach from=$row.assignee_contact_name item=assigneeName key=assigneeID}
                {if $showAssignee < 5}
                    {if $showAssignee};&nbsp;{/if}<a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$assigneeID`"}" title="{ts}View contact{/ts}">{$assigneeName}</a>
                    {assign var="showAssignee" value=$showAssignee+1}
                {/if}
            {/foreach}
            {if count($row.assignee_contact_name) > 5}({ts}more{/ts}){/if}
        {/if}	
        </td>

        <td>{$row.activity_date_time|crmDate}</td>

        <td>{$row.action|replace:'xx':$row.id}</td>
      </tr>
      {/foreach}

    </table>
  {/strip}

  {include file="CRM/common/pager.tpl" location="bottom"}
  </form>

{else}

  <div class="messages status">
    {if isset($caseview) and $caseview}
      {ts}There are no Activities attached to this case record.{/ts}{if $permission EQ 'edit'} {ts}You can go to the Activities tab to create or attach activity records.{/ts}{/if}
    {elseif $context eq 'home'}
      {ts}There are no Activities to display.{/ts}
    {else}
      {ts}There are no Activites to display.{/ts}{if $permission EQ 'edit'} {ts}You can use the links above to schedule or record an activity.{/ts}{/if}
    {/if}
  </div>

{/if}
{if !$noFieldSet}
</fieldset>
{/if}
</div>

