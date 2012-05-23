<div id="help">
Welcome to the Household Page (step 1 of 5). We ask that you please complete all relevant fields for Household 1. If your child’s parents/guardians live at a different address, please complete Household 2 information as well. When complete, click "Save & Next."
<br/>
{include file="SCH/Form/Family/HelpInfo.tpl"}
</div>

{include file="SCH/Form/Family/Buttons.tpl}

{section name="parentNumber" start=1 step=1 loop=5}
{assign  var='parentNum'     value=$smarty.section.parentNumber.index}

{if $parentNum is odd}
<div class="crm-accordion-wrapper crm-accordion_title-accordion {if $parentNum gte 2}{if $form.contact.$parentNum.first_name.value or $form.contact.$parentNum.email.1.email.value}crm-accordion-open{else}crm-accordion-closed{/if}{else}crm-accordion-open{/if}">
  <div class="crm-accordion-header">
    <div class="icon crm-accordion-pointer"></div>
      Household {if $parentNum gte 2}2{else}1{/if}
    </div><!-- /.crm-accordion-header -->
    <div class="crm-accordion-body">
    <table>
     <tr>
{/if}

      <td style="padding:1px; width:45%">
             <table id=household_{$parentNum} style="border:1px solid #999999;">
               <tr>
                 <th colspan=2>Parent {$parentNum}</th>
               </tr>
               <tr>
                 <td style="padding:1px">
                    {$form.contact.$parentNum.first_name.label}<br />
                    {$form.contact.$parentNum.first_name.html}
                 </td>
                 <td style="padding:1px">
                    {$form.contact.$parentNum.last_name.label}<br />
                    {$form.contact.$parentNum.last_name.html}
                 </td>
               </tr>

               <tr><td colspan=2>
               <br />
               <table>
               {* Email *}
               <tr>
	          <td style="padding:1px" width=18%>{$form.contact.$parentNum.email.1.email.label}</td><td>{$form.contact.$parentNum.email.1.email.html|crmReplace:class:twenty}</td>
               </tr>

               {* Phone *}
               {if $form.contact.$parentNum.phone}
                   {if $form.contact.$parentNum.phone.1.phone}
                   <tr>
	               <td style="padding:1px" width=18%>{$form.contact.$parentNum.phone.1.phone.label}</td><td>{$form.contact.$parentNum.phone.1.phone.html|crmReplace:class:twenty}</td>
                   </tr>
                   {/if}
                   {if $form.contact.$parentNum.phone.2.phone}
                   <tr>
	               <td style="padding:1px" width=18%>{$form.contact.$parentNum.phone.2.phone.label}</td><td>{$form.contact.$parentNum.phone.2.phone.html|crmReplace:class:twenty}</td>
                   </tr>
	           {/if}
                   {if $form.contact.$parentNum.phone.3.phone}
                   <tr>
	               <td style="padding:1px" width=18%>{$form.contact.$parentNum.phone.3.phone.label}</td><td>{$form.contact.$parentNum.phone.3.phone.html|crmReplace:class:twenty}</td>
                   </tr>
	           {/if}
               {/if}
               </table>
               </td></tr>

	       <tr><td colspan=2>
               <table>
               {include file=CRM/Contact/Form/Edit/Address/street_address.tpl blockId="$parentNum"}

               {* city postal *}
               {if $form.address.$parentNum}
               <tr>
                  {if $form.address.$parentNum.city}
                    <td style="padding:1px">
                       {$form.address.$parentNum.city.label}<br />
                       {$form.address.$parentNum.city.html}
                    </td>
                  {/if}
                  {if $form.address.$parentNum.postal_code}
                    <td style="padding:1px">
                       {$form.address.$parentNum.postal_code.label}<br />
                       {$form.address.$parentNum.postal_code.html}
                       {$form.address.$parentNum.postal_code_suffix.html}<br />
                    </td>
                  {/if}
               </tr>
               {/if}

               {* country & state *}
               {if $form.address.$parentNum}
               <tr>
                  {if $form.address.$parentNum.country_id}
                    <td style="padding:1px">
                       {$form.address.$parentNum.country_id.label}<br />
                       {$form.address.$parentNum.country_id.html}
                    </td>
                  {/if}
                  {if $form.address.$parentNum.state_province_id}
                    <td style="padding:1px">
                       {$form.address.$parentNum.state_province_id.label}<br />
                       {$form.address.$parentNum.state_province_id.html}
                    </td>
                  {/if}
               </tr>
               {/if}
             </table>
             </td></tr>
             </table>
      </td>

{if $parentNum is even}
     </tr>
    </table>

    {include file="SCH/Form/Family/Buttons.tpl}

  </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->


{/if}

{/section}

{literal}
<script type="text/javascript">
cj(function() {
   cj(".crm-error:first").hide();
   cj().crmaccordions();
});
</script>
{/literal}
