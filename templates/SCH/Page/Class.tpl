{foreach from=$schedule key=day item=dayValues}
<div>
<fieldset><legend>Activities for {$day}</legend>
<table class="report-layout">
  <tr>
     <th style="width: 20%">Class Name</th>
     <th style="width: 15%">Time</th>
     <th style="width: 15%">Instructor</th>
     <th style="width: 5%">Fees</th>
     <th style="width: 5%">Session Fees</th>
     <th style="width: 15%">Grade(s)</th>
     <th style="width: 20%">Location</th>
     <th style="width: 10%">&nbsp;</th>
{if $editClass}
     <th style="width: 10%">#</th>
     <th style="width: 10%">&nbsp;</th>
{/if}
  </tr>
  {foreach from=$dayValues item=class}
  <tr class="{cycle values="odd-row,even-row"}">
    <td>{$class.name}</td>
    <td>{$class.session}</td>
    <td>{$class.instructor}</td>
{if $class.fee_block gt 0}
    <td>{$class.fee_block}</td>
{else}
    <td>&nbsp;</td>
{/if}
{if $class.total_fee_block gt 0}
    <td>{$class.total_fee_block}</td>
{else}
    <td>&nbsp;</td>
{/if}
{if $class.min_grade == 1 && $class.max_grade == 8}
    <td>All Grades</td>
{else}
    <td>Grades {$class.min_grade} - {$class.max_grade}</td>
{/if}
    <td>{$class.location}</td>
{if $class.url}
    <td><a href="javascript:popUp('{$class.url}')">More Info</a></td>
{else}
    <td>&nbsp;</td>
{/if}
{if $editClass}
  <td><a href="{$class.num_url}">{$class.num_students}</a></td>	
  <td>{$class.action}</a></td>	
{/if}
  </tr>
  {/foreach}
</table>
</fieldset>
</div>
{/foreach}

{if $disableActivities}

<fieldset><legend>Disabled Activities </legend>
{foreach from=$disableActivities key=day item=dayValues}
<div>
<fieldset><legend>Disabled Activities for {$day}</legend>
<table class="report-layout">
  <tr>
     <th style="width: 20%">Class Name</th>
     <th style="width: 15%">Time</th>
     <th style="width: 15%">Instructor</th>
     <th style="width: 5%">Fees</th>
     <th style="width: 15%">Grade(s)</th>
     <th style="width: 20%">Location</th>
     <th style="width: 10%">&nbsp;</th>
{if $editClass}
     <th style="width: 10%">#</th>
     <th style="width: 10%">&nbsp;</th>
{/if}
  </tr>
  {foreach from=$dayValues item=class}
  <tr class="{cycle values="odd-row,even-row"}">
    <td>{$class.name}</td>
    <td>{$class.session}</td>
    <td>{$class.instructor}</td>
{if $class.fee_block gte 0}
    <td>{$class.fee_block}</td>
{else}
    <td>&nbsp;</td>
{/if}
{if $class.min_grade == 1 && $class.max_grade == 8}
    <td>All Grades</td>
{else}
    <td>Grades {$class.min_grade} - {$class.max_grade}</td>
{/if}
    <td>{$class.location}</td>
{if $class.url}
    <td><a href="javascript:popUp('{$class.url}')">More Info</a></td>
{else}
    <td>&nbsp;</td>
{/if}
{if $editClass}
  <td><a href="{$class.num_url}">{$class.num_students}</a></td>	
  <td>{$class.action}</td>	
{/if}
  </tr>
  {/foreach}
</table>
</fieldset>
</div>
{/foreach}
</fieldset>
{/if}

{if $editClass}
    <div class="action-link">
        <a href="{$addClass}" class="button"><span>&raquo; {ts}Add New Class{/ts}</span></a>
    </div>
    <div class="spacer"></div>
{/if}