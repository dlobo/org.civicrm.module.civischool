<tr id="row_{$fieldName}">
  <td style="width:30%">{$form.$fieldName.label}</td>
  <td>
    <table class="form-layout-compressed" style="margin-top: -0.5em;">
      <tr><td class="labels font-light">
        {assign var="index" value="1"}
        {foreach name=outer key=key item=item from=$form.$fieldName}
        {if $index < 10}
          {assign var="index" value=`$index+1`}
        {else}
          {$form.$fieldName.$key.html}
          <br/>
        {/if}
        {/foreach}
      </td></tr>
      </table>
  </td>
</tr>
{if $otherName}
<tr id="row_{$otherName}">
  <td>{$form.$otherName.label}</td><td>{$form.$otherName.html|crmReplace:class:huge}</td>
</tr>
{/if}
