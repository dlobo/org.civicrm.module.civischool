<div class="form-item">	
<fieldset>
<legend>Electronic Consent Form</legend>
<dl>
    <dt></dt>
    <dd>{$form.buttons.html}<dd/>
</dl>

<div id="alpha-filter">
    <ul>
    {foreach from=$aToZ item=letter}
        <li {if $letter.class}class="{$letter.class}"{/if}>{$letter.link}</li>
    {/foreach}
    </ul>
</div>

<table>
<tr >
  <th width=30%>Student - Parent</th>
  <th width=30%>Date</th>
  <th width=40%>Current Recorded Date</th>
</tr>
{foreach from=$nameValues item=value key=dateName}
<tr>
<td>{$value.name}</td>
<td>{include file="CRM/common/jcalendar.tpl" elementName=$dateName}</td>
<td>{$value.currentDate}</td>
<td>&nbsp;</td>
</tr>
{/foreach}
</table>

<dl>
    <dt></dt>
    <dd>{$form.buttons.html}<dd/>
</dl>
</fieldset>
</div>

