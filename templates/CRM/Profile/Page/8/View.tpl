{foreach from=$profileGroups item=group}
    <h2>{$group.title}</h2>
    <div id="profilewrap{$groupID}">
    	 {$group.content}
    </div>
{/foreach}

{include file="SFS/common/footer.tpl"}