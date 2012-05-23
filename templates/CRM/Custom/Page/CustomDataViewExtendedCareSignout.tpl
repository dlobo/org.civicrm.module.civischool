{assign var="showEdit" value=1}
{foreach from=$viewCustomData item=customValues key=customGroupId}
{foreach from=$customValues item=cd_edit key=cvID}
    {assign var='index' value=$groupId|cat:"_$cvID"}
    {assign var="showEdit" value=0}
    <span id="statusmessg_{$index}" class="success-status" style="display:none;"></span>    
{/foreach}
{/foreach}

<table>
<tr>
  <th>Pickup Person Name</th>
  <th>Sign In Time</th>
  <th>Pickup Time</th>
  <th>Class</th>
  <th>Morning Care?</th>
  <th></th>
</tr>
{assign var="showEdit" value=1}
{foreach from=$viewCustomData item=customValues key=customGroupId}
{foreach from=$customValues item=cd_edit key=cvID}
    {assign var='index' value=$groupId|cat:"_$cvID"}
    {if $showEdit and $editCustomData and $groupId}	
      <div class="action-link">
        <a href="{crmURL p="civicrm/contact/view/cd/edit" q="tableId=`$contactId`&cid=`$contactId`&groupId=`$groupId`&action=update&reset=1"}" class="button" style="margin-left: 6px;"><span>&raquo; {ts 1=$cd_edit.title}Edit %1 Records{/ts}</span></a><br/><br/>
      </div>      
    {/if}
    {assign var="showEdit" value=0}
<tr id=row_{$index}>
  {foreach from=$cd_edit.fields item=element key=field_id}
  <td>{$element.field_value}</td>
  {/foreach}
  <td>&nbsp;&nbsp;&nbsp;<a href="javascript:showDelete( {$cvID}, '{$cd_edit.name}_{$index}', {$customGroupId} );"><img title="delete this record" src="{$config->resourceBase}i/delete.png" class="action-icon" alt="{ts}delete this record{/ts}" /></a></td>
</tr>
{/foreach}
{/foreach}
</table>

{*currently delete is available only for tab custom data*}
{if $groupId}
<script type="text/javascript">
    {literal}
    function hideStatus( valueID, groupID ) {
        cj( '#statusmessg_'  + groupID + '_' + valueID ).hide( );
    }
    function showDelete( valueID, elementID, groupID ) {
        var confirmMsg = '{/literal}{ts}Are you sure you want to delete this record?{/ts}{literal} &nbsp; <a href="javascript:deleteCustomValue( ' + valueID + ',\'' + elementID + '\',' + groupID + ' );" style="text-decoration: underline;">{/literal}{ts}Yes{/ts}{literal}</a>&nbsp;&nbsp;&nbsp;<a href="javascript:hideStatus( ' + valueID + ', ' +  groupID + ' );" style="text-decoration: underline;">{/literal}{ts}No{/ts}{literal}</a>';
        cj( '#statusmessg_' + groupID + '_' + valueID ).show( ).html( confirmMsg );
    }
    function deleteCustomValue( valueID, elementID, groupID ) {
        var postUrl = {/literal}"{crmURL p='civicrm/ajax/customvalue' h=0 }"{literal};
        cj.ajax({
          type: "POST",
          data:  "valueID=" + valueID + "&groupID=" + groupID,    
          url: postUrl,
          success: function(html){
              cj( '#' + elementID ).hide( );
              var resourceBase   = {/literal}"{$config->resourceBase}"{literal};
              var successMsg = '{/literal}{ts}The selected record has been deleted.{/ts}{literal} &nbsp;&nbsp;<a href="javascript:hideStatus( ' + valueID + ',' + groupID + ');"><img title="{/literal}{ts}close{/ts}{literal}" src="' +resourceBase+'i/close.png"/></a>';
              cj( '#statusmessg_'  + groupID + '_' + valueID ).show( ).html( successMsg );
              cj( '#row_'  + groupID + '_' + valueID ).hide( );
          }
        });
    }
    {/literal}
</script>
{/if}
