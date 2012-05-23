{if ! empty( $row )}
{* wrap in crm-container div so crm styles are used *}
<div id="crm-container" lang="{$config->lcMessages|truncate:2:"":true}" xml:lang="{$config->lcMessages|truncate:2:"":true}">
<fieldset>
<table class="form-layout-compressed">
{assign var=fName value="First Name"}
{assign var=lName value="Last Name"}
  <tr id="contact_name"><td class="label">Parent Name</td><td class="view-value">{$row.$fName}&nbsp;{$row.$lName}</td></tr>
  <tr id="contact_email"><td class="label">Email</td><td class="view-value">{$row.Email}</td></tr>
  <tr id="contact_phone"><td class="label">Phone</td><td class="view-value">{$row.Phone}</td></tr>
</table>
</fieldset>
{if $childrenInfo}
{foreach from=$childrenInfo key=dontCare item=childInfo}
<fieldset>
<legend>{$childInfo.name} Information</legend>
<div>
<table class="form-layout-compressed">
   <tr><td class="label">Child Name</td><td class="view-value">{$childInfo.name}</td></tr>
   <tr><td class="label">Grade</td><td class="view-value">{$childInfo.grade}</td></tr>
</table>
</div>
<br/>

{include file="SCH/common/child.tpl"}
</fieldset>
{/foreach}
{/if}

{if $ptcValues}
<fieldset>
<legend>Your Parent Teacher Conference Schedule</legend>
<div>
<table class="form-layout-compressed">
  <tr>
     <th>Student Name</th>
     <th>Time</th>
 </tr>
{foreach from=$ptcValues item=ptcValue}
  <tr>
     <td><a href="{crmURL p='civicrm/profile/view' q="reset=1&gid=`$studentProfileID`&id=`$ptcValue.id`"}">{$ptcValue.name}</a></td>
     <td>{$ptcValue.time}</td>
  </tr>
{/foreach}
</table>
<br/>
<div>
<a href="{crmURL p='civicrm/profile/edit' q="reset=1&gid=`$parentProfileID`&id=`$cid`&mptc=1"}">Manage Parent Teacher Conference Schedule for your class</a>
</div>
<br/>
<div>
<a href="{crmURL p='civicrm/report/list' q="reset=1"}">Printable Schedules for your conference</a>
</div>
</div>
</fieldset>
{/if}

</div>
{/if}
{* fields array is not empty *}
