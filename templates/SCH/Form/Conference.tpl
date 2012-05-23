<div class="form-item">	
<fieldset>
<legend>Conference Creation Wizard</legend>
<dl>
    <dt></dt>
    <dd>{$form.buttons.html}<dd/>
</dl>
<dl>
<dt>{$form.advisor_id.label}</dt><dd>{$form.advisor_id.html}</dd>
{if ! $multipleDay}
<dt>{$form.ptc_date.label}</dt><dd>{include file="CRM/common/jcalendar.tpl" elementName=ptc_date}</dd>
{/if}
<dt>{$form.ptc_subject.label}</dt><dd>{$form.ptc_subject.html}</dd>
<dt>{$form.ptc_duration.label}</dt><dd>{$form.ptc_duration.html}</dd>
{section name="dates" start=1 step=1 loop=$numberOfSlots}
{assign var='datePrefix' value=ptc_date_}
{assign var='dateName'   value=$datePrefix|cat:"`$smarty.section.dates.index`"}
<dt>{$form.$dateName.label}</dt><dd>{include file="CRM/common/jcalendar.tpl" elementName=`$dateName`}</dd>
{/section}
</dl>
<dl>
    <dt></dt>
    <dd>{$form.buttons.html}<dd/>
</dl>
</fieldset>
</div>

{if $summary}
<div>
<table class="selector">
  <tr class="columnheader">
     <th>Name</th>
     <th>Total Blocks</th>
{if $showDetails}
     <th>Details</th>
{/if}
  </tr>
{foreach from=$summary item=row}
{if $row.blockCharge > 0 OR $showDetails}
  <tr class="{cycle values="odd-row,even-row"}">
    <td>{$row.name}</td>
    <td>{$row.blockCharge}</td>
{if $showDetails}
    <td>
<table>
{foreach from=$row.details item=detail}
<tr>
       <td>{$detail.charge}</td>
       <td>{$detail.class}</td>
       <td>{$detail.signout}{if $detail.pickup} by {$detail.pickup}{/if}</td>
       <td>{$detail.message}</td>
</tr>
{/foreach}
</table>
    </td>
{/if}
  </tr>
{/if}
{/foreach}
</table>
</div>
{/if}

{if ! $multipleDay}
{literal}
<script type="text/javascript">
    for (var i=1; i <= {/literal}{$numberOfSlots}{literal}; i++) {
        cj('#ptc_date_' + i).hide( );
        cj('label[for="ptc_date_' + i + '_time"]').hide( );
    }
</script>
{/literal}
{/if}