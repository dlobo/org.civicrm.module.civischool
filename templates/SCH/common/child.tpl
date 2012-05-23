{if $childInfo.reportCards}
<fieldset>
<legend>Report Cards</legend>
{foreach from=$childInfo.reportCards key=dontCare item=reportCard}
<div>
<a href="{$reportCard.url}"><strong>{$reportCard.title}</strong></a>
</div>
{/foreach}
</fieldset>
{/if}
<fieldset>
<legend>Online Forms for Family Information</legend>
<div>
<a href="{$childInfo.familyURL}"><strong>Submit {$childInfo.name}'s SCH Family Information Form</strong></a>
</div>
</fieldset>
{if $childInfo.meeting}
<fieldset>
<legend>Parent Teacher Conference Information</legend>
<div>
{$childInfo.meeting.title}
</div>
<br/>
<div>
{$childInfo.meeting.edit}
</div>
</fieldset>
{/if}
{if $childInfo.extendedCare OR $childInfo.extendedCareEdit}
<fieldset>
<legend>Extended Care Information</legend>
{if $childInfo.extendedCare}
<table class="form-layout-compressed">
  <tr><th>Day</th><th>Time</th><th>Class</th><th>Description</th><th>Instructor</th><th></th></tr>
  {foreach from=$childInfo.extendedCare key=dontCare item=class}
  <tr>
     <td>{$class.day}</td>
     <td>{$class.time}</td>
     <td>{$class.name}</td>
     <td>{$class.desc}</td>
     <td>{$class.instructor}</td>
     <td><a href="{$childInfo.extendedCareEdit}">Edit</a></td>
  </tr>
  {/foreach}
</table>
<br/>
{/if}
{if $childInfo.extendedCareEdit}
<div>
<a href="{$childInfo.extendedCareEdit}">Manage extended care schedule for {$childInfo.name}</a>
<br/>
<br/>
<a href="{$childInfo.extendedCareView}">View extended care block charges for {$childInfo.name}</a>
</div>
{/if}
<div>
<br/>
<a href="http://sfschool.org/drupal/civicrm/sfschool/extended/class?reset=1">Extended Care Program Schedule and Details</a>
</div>
</fieldset>
{/if}
