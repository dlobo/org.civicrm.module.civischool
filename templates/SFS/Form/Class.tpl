<div class="form-item">	
<fieldset>
{if $action eq 64}
<legend>Disable Class</legend>
<div class="messages status"> 
        <dl> 
            <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}" /></dt> 
            <dd>{ts}Are you sure you want to disable {$classDetail.name.value} on {$classDetail.day_of_week.value}?{/ts}</dd>
       </dl>
</div>
{/if}
{if $action eq 32}
<legend> Enable Class </legend>
<div class="messages status"> 
        <dl> 
            <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}" /></dt> 
            <dd>{ts}Do you want to Enable following Class?{/ts}</dd>
        </dl> 
</div> 
<fieldset><legend>Class Deatils</legend>
          <dl>
	      {foreach from=$classDetail item=field }
	          <dt>{$field.title} :</dt><dd> {$field.value}</dd>
	      {/foreach}
              {if $moreInfo}
                 <dt></dt><dd><a href="javascript:popUp('{$moreInfo}')">More Info</a></dd>
              {/if}
         </dl>
</fieldset>
{/if}
{if $action eq 2 or $action eq 1}
{if $action eq 2}
<legend> Edit Class Information </legend>
{else}
<legend> Add Class</legend>
{/if}
<dl>
{foreach from=$elements key=field item=type}
<dt>{$form.$field.label}</dt><dd>{if $field eq 'start_date' or $field eq 'end_date'}{include file="CRM/common/jcalendar.tpl" elementName=$field}{else}{$form.$field.html}{/if}</dd>
{/foreach}
</dl>	 	
{/if}
<dl>
    <dt></dt>
    <dd>{$form.buttons.html}<dd/>
 </dl>
</fieldset>
</div>
