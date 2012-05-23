<div class="form-item">	
<fieldset>

{if $action eq 8}
   {if $object eq 'fee'}
       <legend>Delete Fee Entry </legend>
   {else}	      
      <legend>Delete Acivity Block </legend>
   {/if}
<div class="messages status"> 
        <dl> 
            <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}" /></dt> 
	    {if $object eq 'fee'}
                <dd>{ts}Do you want to Delete Fee Entity of student '{$displayName}' ? {/ts}</dd>
            {else}
	        <dd>{ts}Do you want to Delete Activity block of student '{$displayName}' for class  '{$class}' ?{/ts}</dd>
	    {/if}
        </dl> 
</div> 
{/if}

{if $action eq 2 or $action eq 1}
{if $action eq 2}    
     {if $object eq 'fee'} 
        <legend> Edit Fee Entry </legend>
     {else}
	<legend> Edit Activity Block </legend>
     {/if}
{else}
    {if $object eq 'fee'} 
       <legend> Add Fee Entry </legend>	
    {else}
      <legend> Add Activity Block </legend>
    {/if}
{/if}
<dl>
{foreach from=$fields item=field}  
    <dt>{$form.$field.label}</dt>
    <dd>{if $field eq 'signin_time' or $field eq 'signout_time' or $field eq 'fee_date'}{include file="CRM/common/jcalendar.tpl" elementName=$field}{else}{$form.$field.html}{/if}</dd>	 	 	      	 
{/foreach}
</dl>
{/if}
<dl>
    <dt></dt>
    <dd>{$form.buttons.html}<dd/>
 </dl>
</fieldset>
</div>
