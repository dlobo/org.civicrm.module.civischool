<div class="form-item">	
<fieldset>
<legend>Extended Care Report</legend>
<dl>
<dt>{$form.start_date.label}</dt><dd>{include file="CRM/common/jcalendar.tpl" elementName=start_date}</dd>
<dt>{$form.end_date.label}</dt><dd>{include file="CRM/common/jcalendar.tpl" elementName=end_date}</dd>
<dt>{$form.student_id.label}</dt><dd>{$form.student_id.html}</dd>
<dt>&nbsp;</dt><dd>{$form.include_morning.html}&nbsp;{$form.include_morning.label}</dd>
<dt>&nbsp;</dt><dd>{$form.show_details.html}&nbsp;{$form.show_details.label}</dd>
<dt>&nbsp;</dt><dd>{$form.not_signed_out.html}&nbsp;{$form.not_signed_out.label}</dd>
<dt>&nbsp;</dt><dd>{$form.show_balances.html}&nbsp;{$form.show_balances.label}</dd>
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
{if $showBalances}
     <th>Total Charges</th>
     <th>Total Payments</th>
     <th>Balance Due</th>
     <th>Balance Credit</th>
{else}
     <th>Total Blocks</th>
{if $showDetails}
     <th>Details</th>
{/if}
{/if}
  </tr>
{foreach from=$summary item=row}
{if $showBalances}
  <tr class="{cycle values="odd-row,even-row"}">
    <td><a href="{crmURL p="civicrm/sfschool/extendedCare" q="reset=1&id=`$row.id`"}">{$row.name}</a></td>
    <td>{$row.totalCharges}</td>
    <td>{$row.totalPayments}</td>
    <td>{if $row.balanceDue}{$row.balanceDue}{else}&nbsp;{/if}</td>
    <td>{if $row.balanceCredit}{$row.balanceCredit}{else}&nbsp;{/if}</td>
  </tr>
{else}
{if $row.blockCharge > 0 OR $showDetails}
  <tr class="{cycle values="odd-row,even-row"}">
    <td><a href="{crmURL p="civicrm/sfschool/extendedCare" q="reset=1&id=`$row.id`"}">{$row.name}</a></td>
    <td>{if $row.doNotCharge}0 ({$row.doNotCharge}, {$row.blockCharge}){else}{$row.blockCharge}{/if}</td>
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
{/if} {*  showDetails *}
{/if} {*  showBalances *}
{/foreach}
</table>
</div>
{/if}
