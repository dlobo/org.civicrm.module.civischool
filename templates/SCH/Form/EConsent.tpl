<div class="form-item">	
<fieldset>
<legend>Electronic Consent Form</legend>
<dl>
    <dt></dt>
    <dd>{$form.buttons.html}<dd/>
</dl>

<dl>
    <dt>Date EConsent was signed</dt>
    <dd>{include file="CRM/common/jcalendar.tpl" elementName=econsent_date}<dd/>
</dl>

{section name="dates" start=1 step=1 loop=$numberOfSlots}
{assign var='namePrefix' value=student_parent_}
{assign var='nameName'   value=$namePrefix|cat:"`$smarty.section.dates.index`"}
<dl>
<dt></dt>
<dd>{$form.$nameName.html}</dd>
</dl>
{/section}

<dl>
    <dt></dt>
    <dd>{$form.buttons.html}<dd/>
</dl>
</fieldset>
</div>

