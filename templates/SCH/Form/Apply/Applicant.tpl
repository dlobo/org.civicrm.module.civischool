{include file="SCH/Form/Family/Buttons.tpl"}
<div id="common-form-controls" class="form-item">
    <fieldset>
      <legend>{ts}Applicant Information{/ts}</legend>
      <dl>
        <dt>{$form.applicants_first_name.label}</dt><dd>{$form.applicants_first_name.html}</dd>
        <dt>{$form.applicants_middle_name.label}</dt><dd>{$form.applicants_middle_name.html}</dd>
        <dt>{$form.applicants_last_name.label}</dt><dd>{$form.applicants_last_name.html}</dd>
        <dt>{$form.prefered_name.label}</dt><dd>{$form.prefered_name.html}</dd>
        <dt>{$form.grade.label}</dt><dd>{$form.grade.html}</dd>
        <dt>{$form.year.label}</dt><dd>{$form.year.html}</dd>
        <dt>{$form.dob.label}</dt><dd>{include file="CRM/common/jcalendar.tpl" elementName=dob}</dd>
        <dt>{$form.gender.label}</dt><dd>{$form.gender.html}</dd>
      </dl>
    </fieldset>
  </div>
{include file="SCH/Form/Family/Buttons.tpl"}

