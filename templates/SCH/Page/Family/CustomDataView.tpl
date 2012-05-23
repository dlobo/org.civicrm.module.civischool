{if $layout eq 'oneliner'}
{foreach from=$viewCustomData item=customValues key=customGroupId}
  {foreach from=$customValues item=cd_edit key=cvID}
    <strong>{$cd_edit.title}</strong><br/>
      {foreach from=$cd_edit.fields item=element key=field_id}
	{if $element.options_per_line != 0}
	  {foreach from=$element.field_value item=val}
	    {$val},
	  {/foreach}
	{else}
	  {if $element.field_type == 'File'}
	    {if $element.field_value.displayURL}
	      <a href="javascript:imagePopUp('{$element.field_value.displayURL}')" ><img src="{$element.field_value.displayURL}" height = "100" width="100"></a>
	    {else}
	      <a href="{$element.field_value.fileURL}">{$element.field_value.fileName}</a>
	    {/if}
	  {else}
	    {$element.field_value}
	  {/if}
	{/if}
      {/foreach}
    {/foreach}
{/foreach}

{else}

{foreach from=$viewCustomData item=customValues key=customGroupId}
    {foreach from=$customValues item=cd_edit key=cvID}
	<table class="no-border">
	    {assign var='index' value=$groupId|cat:"_$cvID"}
	    <tr>
		<td id="{$cd_edit.name}_{$index}">		    
		    {$cd_edit.title}
			{foreach from=$cd_edit.fields item=element key=field_id}
			    <table class="view-layout">
				<tr>
				    {if $element.options_per_line != 0}
					<td class="label">{$element.field_title}</td>
					<td class="html-adjust">
					    {* sort by fails for option per line. Added a variable to iterate through the element array*}
					    {foreach from=$element.field_value item=val}
						{$val}<br/>
					    {/foreach}
					</td>
				    {else}
					<td class="label">{$element.field_title}</td>
					{if $element.field_type == 'File'}
					    {if $element.field_value.displayURL}
						<td class="html-adjust"><a href="javascript:imagePopUp('{$element.field_value.displayURL}')" ><img src="{$element.field_value.displayURL}" height = "100" width="100"></a></td>
					    {else}
						<td class="html-adjust"><a href="{$element.field_value.fileURL}">{$element.field_value.fileName}</a></td>
					    {/if}
					{else}
					    <td class="html-adjust">{$element.field_value}</td>
					{/if}
				    {/if}
				</tr>
			    </table>
			{/foreach}
		</td>
	    </tr>
	</table>

    {/foreach}
{/foreach}

{/if}
