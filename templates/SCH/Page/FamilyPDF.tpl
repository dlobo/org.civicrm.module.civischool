<h4>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$childName}: Grade {$childGrade}, Birth Date: {$childBirth}, School Year: 2010-2011</h4>
<table style="margin: 0em 1em 0em 4em; font-size: 11pt;">
{counter start=0 skip=1 print=false}
{foreach from=$values.household key=pid item=pValues}
<tr>
    <td width=20%><strong>Parent {$pValues.parent_index}</strong></td>
    <td width=75%>{$pValues.display_name}
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

{counter start=0 skip=1 print=false}
{foreach from=$values.emergency key=pid item=pValues}
<tr>
    <td width=20%><strong>Emergency Contact {counter}</strong></td>
    <td width=75%>{$pValues.display_name}
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

{if $values.medical.info.details || $values.medical.details}
{if $values.medical.info.details}
{foreach from=$values.medical.info.details key=dontCare item=pValues}
<tr>
  <td width=20%><strong>{$pValues.title}</strong></td>
  <td width=75%>{$pValues.value}</td>
</tr>
{/foreach}
{/if}
{if $values.medical.details}
{foreach from=$values.medical.details key=dontCare item=pValues}
{if $pValues.description}
<tr>
  <td width=20%><strong>{$pValues.medical_type}</strong></td>
  <td width=75%>{$pValues.description}</td>
</tr>
{/if}
{/foreach}
{/if}
{/if}

{foreach from=$values.release item=customValues key=customGroupId}
    {foreach from=$customValues item=cd_edit key=cvID}
      {foreach from=$cd_edit.fields item=element key=field_id}
	{if $element.field_type eq 'Radio' AND $element.field_title ne 'Currently Enrolled'}
        <tr>
          <td width=20%><strong>{$element.field_title}</strong></td>
          <td width=75%>{$element.field_value}</td>
        </tr>
	{/if}
      {/foreach}
    {/foreach}
{/foreach}

{if $values.race || $values.family_structure}
{if $values.race}
<tr>
  <td width=20%><strong>Race</strong></td>
  <td width=75%>{$values.race}</td>
</tr>
{/if}
{if $values.family_structure}
<tr>
  <td width=20%><strong>Family Structure</strong></td>
  <td width=75%>{$values.family_structure}</td>
</tr>
{/if}
{/if}

</table>
