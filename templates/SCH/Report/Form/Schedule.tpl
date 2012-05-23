
{include file="CRM/Report/Form/Fields.tpl"}
{include file="CRM/Report/Form/Statistics.tpl" top=true}

    {if $contactDetails}
        <div class="report-pager">
            {include file="CRM/common/pager.tpl" noForm=1}
        </div>

        {foreach from=$contactDetails item=contacts key=contactId}
            <table ><tr><td>
	        <table class="report-layout"><tr>
	        
		    <th>Contact Name</th>
		
                </tr>
		<tr>
		
		    <td>{$contacts.display_name}</td>	 
		</tr></table>
	        {if $relationDetails}
		   {if $relationDetails.$contactId}
		   <table class="report-layout"><tr>
		   <th>{$relHeader}</th>
		   </tr>
		   {foreach from=$relationDetails.$contactId item=rel key=relId}
		   <tr>	    
		   <td>{$rel}</td>	    
		   </tr>
		   {/foreach}		   
		   {/if}
		{/if}	
		{if $activityDetails.$contactId}
		<table class="report-layout">
                    <tr>
	            {foreach from=$activityHeaders item=header}
		        <th>{$header.title}</th>
		    {/foreach}
		    </tr>
		    {foreach from=$activityDetails.$contactId item=contactActivity}
		        <tr>	
		        {foreach from=$activityHeaders item=header key=field}
                            <td>
			    {if $header.type & 12 }
			    {$contactActivity.$field|crmDate}
			    {else}
			    {$contactActivity.$field}
			    {/if}
			    </td>
                        {/foreach}
                        </tr>
		    {/foreach}
                </table>
		{/if}
	    </td></tr></table>
        {/foreach}
          {include file="CRM/Report/Form/Statistics.tpl" bottom=true}                
     {/if}

                          
 {include file="CRM/Report/Form/ErrorMessage.tpl"}
