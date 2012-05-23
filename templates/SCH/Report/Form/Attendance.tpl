{include file="CRM/Report/Form/Fields.tpl"}
 
{if $printOnly}
    <h1>{$reportTitle}</h1>
    <div id="report-date">{$reportDate}</div>
{/if}
{if $outputMode eq 'html'}   
{*Statistics at the Top of the page*}
{include file="CRM/Report/Form/Statistics.tpl" top=true}
{/if}
<br/>

{*include the graph*}
{include file="CRM/Report/Form/Layout/Graph.tpl"}
    
{if (!$chartEnabled || !$chartSupported )&& $rows}
    {if $pager and $pager->_response and $pager->_response.numPages > 1}
        <br />
        <div class="report-pager">
            {include file="CRM/common/pager.tpl" noForm=1}
        </div>
    {/if}
    <br />

    <table style="border: 0; width: 100%">
    {assign  var="count" value="0"}
    {foreach from=$rows item=rows key=sfield}
    {assign var="count" value=`$count+1`}
    {if $count is not div by 2}<tr>{/if}
    <td style="padding: 4px">

    <table class="report-layout">
        <tr><th style="text-align: left" colspan=6>{$sessionInfo.$sfield.title}&nbsp;&nbsp;{if $sessionInfo.$sfield.session eq 'First'}3:30 pm - 4:30 pm{else}4:30 pm - 5:30 pm{/if}<br/><u>{$sessionInfo.$sfield.instRoom}</u>&nbsp;</th></tr>
        <tr style="font-size:72%"> 
            {foreach from=$columnHeaders item=header key=field}
                {assign var=class value=""}
                {if $header.type eq 1024 OR $header.type eq 1}
        		    {assign var=class value="class='reports-header-right'"}
                {else}
                    {assign var=class value="class='reports-header'"}
                {/if}
                {if !$skip}
                   {if $header.colspan}
                       <th colspan={$header.colspan}>{$header.title}</th>
                      {assign var=skip value=true}
                      {assign var=skipCount value=`$header.colspan`}
                      {assign var=skipMade  value=1}
                   {else}
		       {if $header.type eq 'signout' or $header.type eq 'signin'}
                           <th style="width: 1%">{$header.title}</th>
		       {else} 
		           {if $header.type eq 'parent'}
                               <th style="width: 18%">{$header.title}</th>
                           {else}
                               <th {$class}>{$header.title}</th>
                           {/if}
                       {/if}
                   {assign var=skip value=false}
                   {/if}
                {else} {* for skip case *}
                   {assign var=skipMade value=`$skipMade+1`}
                   {if $skipMade >= $skipCount}{assign var=skip value=false}{/if}
                {/if}
            {/foreach}
        </tr>          
       
        {foreach from=$rows item=row}
            <tr>
                {foreach from=$columnHeaders item=header key=field}
                    {assign var=fieldLink value=$field|cat:"_link"}
                    {assign var=fieldHover value=$field|cat:"_hover"}
                    <td {if $header.type eq 1024 OR $header.type eq 1} class="report-contents-right"{elseif $row.$field eq 'Subtotal'} class="report-label"{/if}>
                        {if $row.$fieldLink}
                            <a title="{$row.$fieldHover}" href="{$row.$fieldLink}">
                        {/if}
                        
                        {if $row.$field eq 'Subtotal'}
                            {$row.$field}
                        {elseif $header.type & 4}
                            {if $header.group_by eq 'MONTH' or $header.group_by eq 'QUARTER'}
                                {$row.$field|crmDate:$config->dateformatPartial}
                            {elseif $header.group_by eq 'YEAR'}	
                                {$row.$field|crmDate:$config->dateformatYear}
                            {else}		
                                {$row.$field|truncate:10:''|crmDate}
                            {/if}	
                        {elseif $header.type eq 1024}
                            {$row.$field|crmMoney}
                        {else}
                            {$row.$field}
                        {/if}
                        
                        {if $row.$fieldLink}</a>{/if}
                    </td>
                {/foreach}
            </tr>
        {/foreach}
        
        {if $grandStat}
            {* foreach from=$grandStat item=row*}
            <tr class="total-row">
                {foreach from=$columnHeaders item=header key=field}
                    <td class="report-label">
                        {if $header.type eq 1024}
                            {$grandStat.$field|crmMoney}
                        {else}
                            {$grandStat.$field}
                        {/if}
                    </td>
                {/foreach}
            </tr>
            {* /foreach*}
        {/if}
    </table>

    </td>
    {if $count is div by 2}</tr>{/if}
    {/foreach}
    {if $count is not div by 2}<td>&nbsp;</td></tr>{/if}

    </table>

{/if}        

    
{*Statistics at the bottom of the page*}
{*include file="CRM/Report/Form/Statistics.tpl" bottom=true*}    
    
{include file="CRM/Report/Form/ErrorMessage.tpl"}
