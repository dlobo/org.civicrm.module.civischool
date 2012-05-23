{if $action neq 4}
    {if $monthlySignout OR $feeDetail}
    <div>
    <h2>Extended Care Fee Details for {$feeDetail.name}</h2>
    <br/>
{if $balanceDetails}
    <div class="status">
<table>
  <tr>
     <td><strong>Total Blocks Paid</strong></td>
     <td>{$balanceDetails.totalPayments}</td>
  </tr>
  <tr>
     <td><strong>Total Blocks Charged (Standard + Activity Blocks)</strong></td>
     <td>{$balanceDetails.totalCharges}</td>
  </tr>
  <tr>
     <td>&nbsp;&nbsp;&raquo;<strong>Standard Extended Care Charges</strong></td>
     <td>{$balanceDetails.blockCharges}</td>
  </tr>
  <tr>
     <td>&nbsp;&nbsp;&raquo;<strong>Activity Class Charges</strong></td>
     <td>{$balanceDetails.classCharges}</td>
  </tr>
{if $balanceDetails.balanceDue GT 0}
  <tr>
     <td><strong>Block Balance Due</strong></td>
     <td>{$balanceDetails.balanceDue}</td>
  </tr>
  <tr>
     <td><strong>Block Balance Due In Dollars</strong></td>
     <td>&nbsp;</td>
  </tr>
  <tr>
     <td>&nbsp;&nbsp;&raquo;<strong>Full Pay or Paying Under 100 Blocks</strong> @ $11.25 per block</td>
{assign var=total value=`$balanceDetails.balanceDue*11.25`}
     <td>{$total|crmMoney}</td>
  </tr>
  <tr>
     <td>&nbsp;&nbsp;&raquo;<strong>Indexed Tuition or Paying Over 100 Blocks</strong> @ $9.5 per block</td>
{assign var=total value=`$balanceDetails.balanceDue*9.5`}
     <td>{$total|crmMoney}</td>
  </tr>
{else} 
  <tr>
     <td><strong><strong>Block Balance Credit</strong></td>
     <td>{$balanceDetails.balanceCredit}</td>
  </tr>
{/if}
</table>
<br/>
<strong>Please make cheques payable to The San Francisco School. Questions? Contact
the Business Office at 239-1410 or business@sfschool.org.</strong>
<br/>
</div>
</div>
{/if}

    <table class="selector">
        <tr class="columnheader">
     	    <th>Category</th>
     	    <th>Description</th>
     	    <th>Date</th>
     	    <th>Total Blocks</th>
            <th>&nbsp;</th>
  	</tr>
        {foreach from=$monthlySignout key=month item=detail}
          <tr>
       	    <td>{ts}Standard Fee{/ts}</td>
	    <td>{$detail.description}</td>
       	    <td>{$month}</td>
      	    <td>{$detail.blockCharge}</td>
            <td>{$detail.action}</td>
          </tr>
        {/foreach}
	{foreach from=$feeDetail.details item=detail}
	    {if $detail.fee_type eq 'Payment' OR $detail.fee_type eq 'Credit'}
                <tr class="row-selected">
	    {else}
	        <tr>
	    {/if}
       	    <td>{$detail.category}</td>
	    <td>{$detail.description}</td>
       	    <td>{$detail.fee_date}</td>
       	    <td>{$detail.total_blocks}</td>
            <td>
       	    {if $enableActions}{$detail.action}{else}&nbsp;{/if}
            </td>
	    </tr>
	{/foreach}
    </table>
    </div>
  {/if}
{/if}

{if $enableActions}
     <div class="action-link">
         <a href="{$addFeeEntity}" class="button"><span>&raquo; {ts}Add Fee Entry{/ts}</span></a>
         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         <a href="{$addActivityBlock}" class="button"><span>&raquo; {ts}Add Activity Block{/ts}</span></a>
     </div>
     <div class="spacer"></div>
{/if}

{if $signoutDetail}
    {if $action neq 4}
         <br/><br/>
    {/if}	 
    <div>
    {if $action eq 4}
        <h2>Total Extended Care Activity Blocks for {$signoutDetail.name}: {if $signoutDetail.doNotCharge}0 ({$signoutDetail.doNotCharge}, {$signoutDetail.blockCharge}){else}{$signoutDetail.blockCharge}{/if}</h2>
    {else} 
	<h2>Recent Extended Care Activity for {$signoutDetail.name} </h2>
    {/if}
    <br/>
    <table class="selector">
        <tr class="columnheader">
            <th>Number of Blocks</th>
            <th>Class</th>
            <th>Time</th>
            <th>Message</th>
            {if $enableActions}
               <th>&nbsp;</th>
            {/if}
        </tr>
        {foreach from=$signoutDetail.details item=detail}
            <tr>
                <td>{$detail.charge}</td>
                <td>{$detail.class}</td>
                <td>{$detail.signout}{if $detail.pickup} by {$detail.pickup}{/if}</td>
                <td>{$detail.message}</td>
                {if $enableActions}
                    <td>{$detail.action}</td>
                {/if}
            </tr>
        {/foreach}
        </table>
        </div>
{else}
    {if $action eq 4}
        <div>
        No Extended Care Activity recorded for {$displayName}
        </div>
    {/if}
{/if}

{if $action eq 4}
    <div class="action-link">
        <a href="{$backButtonUrl}" class="button"><span>&raquo; {ts}Done{/ts}</span></a>
    </div>
    <div class="spacer"></div>
{/if}

{if ($action neq 4) AND ($monthlySignout OR $feeDetail) }
    <div class="footer" id="civicrm-footer">
        If the above information is incorrect, please send a detailed email to <a href="mailto:sning@sfschool.org">Surrina Ning</a>
    </div>
{/if}