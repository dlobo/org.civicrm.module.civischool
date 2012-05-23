<fieldset>
<legend>Household Information</legend>
<table>
{counter start=0 skip=1 print=false}
{foreach from=$values.household key=pid item=pValues}
<tr>
    <td width=20%><strong>Parent {$pValues.parent_index}</strong></td>
    <td width=80%>{$pValues.display_name}
{if $pValues.address_display}
<br/>{$pValues.address_display}
{/if}
{if $pValues.email_display}
<br/><a href="mailto:{$pValues.email_display}">{$pValues.email_display}</a>
{/if}
{if $pValues.phone_display}
<br/>{$pValues.phone_display}
{/if}
<br/><strong>Counselor Authorization</strong>: {$pValues.counselor_authorization}
</td>
</tr>
{/foreach}
</table>
</fieldset>

<fieldset>
<legend>Emergency Information</legend>
<table>
{counter start=0 skip=1 print=false}
{foreach from=$values.emergency key=pid item=pValues}
<tr>
    <td width=20%><strong>Emergency Contact {counter}</strong></td>
    <td width=80%>{$pValues.display_name}
{if $pValues.email_display}
<br/><a href="mailto:{$pValues.email_display}">{$pValues.email_display}</a>
{/if}
{if $pValues.phone_display}
<br/>{$pValues.phone_display}
{/if}
{if $pValues.relationship_name}
<br/>{$pValues.relationship_name}
{/if}
</td>
    </tr>
{/foreach}
</table>
</fieldset>

{if $values.medical.info.details || $values.medical.details}
<fieldset>
<legend>Medical Information</legend>
<table>
{if $values.medical.info.details}
{foreach from=$values.medical.info.details key=dontCare item=pValues}
<tr>
  <td width=20%><strong>{$pValues.title}</strong></td>
  <td width=80%>{$pValues.value}</td>
</tr>
{/foreach}
{/if}
{if $values.medical.details}
{foreach from=$values.medical.details key=dontCare item=pValues}
<tr>
  <td width=20%><strong>{$pValues.medical_type}</strong></td>
  <td width=80%>{$pValues.description}</td>
</tr>
{/foreach}
</table>
{/if}
</fieldset>
{/if}

<fieldset>
<legend>Release Information</legend>
<table>
{foreach from=$values.release item=customValues key=customGroupId}
    {foreach from=$customValues item=cd_edit key=cvID}
      {foreach from=$cd_edit.fields item=element key=field_id}
	{if $element.field_type eq 'Radio'}
	  <tr><td width=20%><strong>{$element.field_title}</strong></td>
          <td width=80%>{$element.field_value}</td></tr>
	{/if}
      {/foreach}
    {/foreach}
{/foreach}
</table>
</fieldset>

{if $values.race || $values.family_structure}
<fieldset>
<legend>Diversity Information</legend>
<table>
{if $values.race}
<tr>
  <td width=20%><strong>Race</strong></td>
  <td width=80%>{$values.race}</td>
</tr>
{/if}
{if $values.family_structure}
<tr>
  <td><strong>Family Structure</strong></td>
  <td>{$values.family_structure}</td>
</tr>
{/if}
</table>
</fieldset>
{/if}
