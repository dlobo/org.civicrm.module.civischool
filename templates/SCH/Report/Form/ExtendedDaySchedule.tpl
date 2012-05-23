
{include file="CRM/Report/Form/Fields.tpl"}
{include file="CRM/Report/Form/Statistics.tpl" top=true}

    {if $rows}
        <div class="report-pager">
            {include file="CRM/common/pager.tpl" noForm=1}
        </div>
	<table class="report-layout">
            <tr>
             <th rowspan={$freeCount+1}>
                 <div> <br>Standard <br> 
                       Extended<br> Day Fee           
		       With the <br>exception,<br>
                       of: Volleyball<br>Cross Country   
                 </div> 
              </th>
            {foreach from=$rows item=day}
	       {assign var=startDate value=$activityFree.$day.0.sfschool_extended_care_source_start_date}
	       {assign var=endDate   value=$activityFree.$day.0.sfschool_extended_care_source_end_date}
               <th>{$day}<br>{$startDate|date_format:"%m/%d"} - {$endDate|date_format:"%m/%d"}</th>
	    {/foreach}
            </tr>
            {foreach from=$freeRows item=flds key=index}
                <tr>
 	        {foreach from=$rows item=day}
                    <td>
                    {if $activityFree.$day.$index}
                    {assign  var= data value=$activityFree.$day.$index}
                       <strong>{$data.sfschool_extended_care_source_name}</strong><br/>
                       {if $data.sfschool_extended_care_source_instructor}
                            w/ {$data.sfschool_extended_care_source_instructor}<br/>
                       {/if}
                       {if $data.sfschool_extended_care_source_min_grade and $data.sfschool_extended_care_source_max_grade }
                             {$data.sfschool_extended_care_source_min_grade}th - {$data.sfschool_extended_care_source_max_grade}th <br/>
                       {/if}
                       {if $data.sfschool_extended_care_source_location}
                            in {$data.sfschool_extended_care_source_location}<br/>
                       {/if}
                    {/if}
                    </td>
	       {/foreach}
               </tr>
            {/foreach}
               <tr><th rowspan={$paidCount+1}>
                      <div> <br> Activity Fee<br> Classes(Normal<br>
			    Fees + additional<br>Activity Fee)
                      </div>
                   </th>
               <th colspan=5> Activity Free Class</th></tr>
            {foreach from=$paidRows item=flds key=index}
                <tr>
 	        {foreach from=$rows item=day}
                    <td>
                    {if $activityPaid.$day.$index}
                    {assign  var= data value=$activityPaid.$day.$index}
                       <strong>{$data.sfschool_extended_care_source_name}</strong><br/>
                       {if $data.sfschool_extended_care_source_instructor}
                            w/ {$data.sfschool_extended_care_source_instructor}<br/>
                       {/if}
                       {if $data.sfschool_extended_care_source_min_grade and $data.sfschool_extended_care_source_max_grade }
                             {$data.sfschool_extended_care_source_min_grade}th - {$data.sfschool_extended_care_source_max_grade}th <br/>
                       {/if}
                       {if $data.sfschool_extended_care_source_location}
                            in {$data.sfschool_extended_care_source_location}<br/>
                       {/if}
                       {if $data.sfschool_extended_care_source_fee_block}
                           ( {$data.sfschool_extended_care_source_fee_block} Activity Fee )
                       {/if}
                    {/if}
                    </td>
	       {/foreach}
               </tr>
            {/foreach}
	    <tr><th>Fee Based</th><th>Tutoring</th><th>Contact</th><th>Elyse Wolland @</th<th colspan=2>ewolland@sfschool.org</th></tr>
	</table>
	 {* hidden bottom statistics
         {include file="CRM/Report/Form/Statistics.tpl" bottom=true} *}              
     <br/>	
     <div>
       {$node->body}
     </div>
 {/if}
                          
 {include file="CRM/Report/Form/ErrorMessage.tpl"}
