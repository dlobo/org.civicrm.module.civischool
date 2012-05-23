    {include file="CRM/Report/Form/Fields.tpl"}
    
    {*Statistics at the Top of the page*}
    {if $outputMode eq 'html'} 
        {include file="CRM/Report/Form/Statistics.tpl" top=true}
    {/if}
    {if $printOnly}
        <h1>{$reportTitle}</h1>
        <div id="report-date">{$reportDate}</div>
        <br/>
    {/if}

    {*include the graph*}
    {include file="CRM/Report/Form/Layout/Graph.tpl"}
    
    {*include the table layout*}
    {include file="CRM/Report/Form/Layout/Table.tpl"}    
    
    {*Statistics at the bottom of the page(hidden)
    {include file="CRM/Report/Form/Statistics.tpl" bottom=true} *}
     
    {include file="CRM/Report/Form/ErrorMessage.tpl"}


