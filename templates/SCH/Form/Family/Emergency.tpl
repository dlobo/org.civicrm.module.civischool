<div id="help">
Welcome to the Emergency Contact Page (step 2 of 5). Please identify at least two individuals the school can contact in the case of an emergency if (and only if) parents/ guardians cannot be reached. These individuals need to live in the Bay Area. Complete all relevant fields for all Emergency Contacts for your child. When complete, click "Save & Next."
<br/>
{include file="SCH/Form/Family/HelpInfo.tpl"}
</div>

{section name="numContacts" start=1 step=1 loop=4}
{assign var='contactIndex'   value=$smarty.section.numContacts.index}
<fieldset>
<legend>Emergency Contact Information for Contact {$contactIndex}</legend>
<table class="form-layout-compressed">
  <tr><td>
  <table><tr>
         <td>
            {$form.ec_contact.$contactIndex.first_name.label}<br />
            {$form.ec_contact.$contactIndex.first_name.html}
         </td>
         <td>
            {$form.ec_contact.$contactIndex.last_name.label}<br />
            {$form.ec_contact.$contactIndex.last_name.html}
         </td>
         <td>
            {$form.ec_contact.$contactIndex.email.1.email.label}<br />
            {$form.ec_contact.$contactIndex.email.1.email.html}
         </td>
         <td>
            {$form.ec_contact.$contactIndex.relationship.label}<br />
            {$form.ec_contact.$contactIndex.relationship.html}
         </td>
        </tr>
    </td>
  </tr>
  <tr>
      <td>
            {$form.ec_contact.$contactIndex.phone.1.phone.label}<br />
            {$form.ec_contact.$contactIndex.phone.1.phone.html}
      </td>
      <td>
            {$form.ec_contact.$contactIndex.phone.2.phone.label}<br />
            {$form.ec_contact.$contactIndex.phone.2.phone.html}
      </td>
      <td>
            {$form.ec_contact.$contactIndex.phone.3.phone.label}<br />
            {$form.ec_contact.$contactIndex.phone.3.phone.html}
      </td>
      <td>
      </td>
     </tr>
  </table>
  </td></tr>
</table>
</fieldset>
{/section}

{include file="SCH/Form/Family/Buttons.tpl"}
