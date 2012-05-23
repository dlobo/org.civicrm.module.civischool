<div>
<fieldset><legend>Students enrolled in {$name} on {$day} at {$time}</legend>
<table class="report-layout">
  <tr>
     <th>Student Name</th>
     <th>&nbsp;</th>
  </tr>
  {foreach from=$values item=student}
  <tr class="{cycle values="odd-row,even-row"}">
    <td>{$student.display_name}</td>
    <td><a href="{$student.url}">View Student Record</td>
  </tr>
  {/foreach}
</table>
</fieldset>
</div>
