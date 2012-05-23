{include file="SCH/Form/Family/Buttons.tpl"}
<div id="common-form-controls" class="form-item">
<fieldset>
      <legend>{ts}School Information{/ts}</legend>
      <table>
      {foreach key=key item=item from=$fieldNames}
          <tr>
              {assign var="name" value="child_name_$key"}
              {assign var="age" value=$item[0]}
              {assign var="school" value=$item[1]}
              {assign var="apply" value=$item[2]}
              <td>{$form.$name.label} {$form.$name.html}
              {$form.$age.label}{$form.$age.html}
              {$form.$school.label}{$form.$school.html}
              {$form.$apply.label}{$form.$apply.html}</td>
          </tr>
      {/foreach}
      </table>
</fieldset>
</div>
{include file="SCH/Form/Family/Buttons.tpl"}