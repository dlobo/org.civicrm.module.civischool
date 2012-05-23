<div id="help">
Welcome to the Medical Information Page (step 3 of 5 ). It is important that the school has accurate and updated medical information for your child in the unlikely case of a serious injury. Complete all relevant fields for all Emergency Contacts for your child. When complete, click "Save & Next."
<br/>
{include file="SFS/Form/Family/HelpInfo.tpl"}
</div>

<fieldset>
<legend>Medical Release Authorization of Consent to Treatment for Student {$studentName}</legend>

<p>{$form.child_insured.html}&nbsp;Please check here if your child is not currently insured.</p>
<p>
{$form.medical_authorization.html}&nbsp;I/we authorize The San Francisco School and its employees and representatives as my/our agent to consent to any medical, hospital, surgical and/or dental treatment or care, including x-ray, MRI and any other diagnostic imaging, testing or examination, anesthetic, dental, medical or surgical diagnosis or treatment, and hospital care, to be rendered under the supervision and upon the advice of a licensed physician and surgeon or dentist, or other licensed emergency medical personnel, in the event of any accident, emergency or other situation calling for medical care during the school day or on a school-sponsored activity, as the parent or legal guardian of the above-named Student, a minor. This authorization is given in advance of any specific diagnosis, treatment or hospital care, and is given to provide authority and power to our agent(s) to give specific consent to any and all such diagnosis, treatment or hospital care that the physician, surgeon or dentist in the exercise of his/her best judgment may deem advisable.
</p>
<p>
I/we agree that we are financially responsible for all medical, dental and emergency expenses incurred above and beyond expenses that may be covered by the School’s insurance policies. I/we hereby release The San Francisco School and its employees and representatives from all liability relating to or arising from such activities and travel, other than liabilities arising from gross negligence, willful misconduct or intentional injury. This authorization shall remain in effect throughout the duration of my child’s attendance at The San Francisco School.
</p>

<table id='insurance_section'>
  <tr>
    <td width=25%>{$form.insurance_company.label}</td><td>{$form.insurance_company.html|crmReplace:class:huge}</td>
  </tr>
  <tr>
    <td width=25%>{$form.group_number.label}</td><td>{$form.group_number.html|crmReplace:class:huge}</td>
  </tr>
  <tr>
    <td width=25%>{$form.policy_number.label}</td><td>{$form.policy_number.html|crmReplace:class:huge}</td>
  </tr>
  <tr>
    <td width=25%>{$form.physician_name.label}</td><td>{$form.physician_name.html|crmReplace:class:huge}</td>
  </tr>
  <tr>
    <td width=25%>{$form.physician_number.label}</td><td>{$form.physician_number.html|crmReplace:class:huge}</td>
  </tr>
</table>

<br />
<strong>Medical/Health Information</strong>
Specify health considerations (allergies, medications, medical conditions, etc.)<br/><br/>

<table style="border-style: none"><tr><td style="padding: 0px;"><strong>Allergies</strong>&nbsp;&nbsp;{$form.is_allergy.html}</td></tr></table>

<span id='allergies_section'>
<br />
<table>
  <tr>
    <td style="vertical-align:top;" width=25%>
        {$form.nuts_specifics.label}
    </td><td>	
        {$form.nuts_specifics.html}
    </td>
  </tr><tr>  
    <td style="vertical-align:top;" width=25%>
        {$form.dairy_products_specifics.label}
    </td><td>	 
        {$form.dairy_products_specifics.html}
    </td>
  </tr><tr>  
    <td style="vertical-align:top;" width=25%>
        {$form.animals_specifics.label}
    </td><td> 
        {$form.animals_specifics.html}
    </td>
  </tr><tr>  
    <td style="vertical-align:top;" width=25%>
        {$form.medicine_specifics.label}
    </td><td>
        {$form.medicine_specifics.html}
    </td>
  </tr><tr>  
    <td style="vertical-align:top;" width=25%>
        {$form.insects_specifics.label}
    </td><td> 
        {$form.insects_specifics.html}
    </td>
  </tr><tr>  
    <td style="vertical-align:top;" width=25%>
        {$form.other_specifics.label}
    </td><td>
        {$form.other_specifics.html}
    </td>
  </tr>
</table>
<br />
</span>

<table style="border-style: none"><tr><td style="padding: 0px;"><strong>Medical Conditions</strong>&nbsp;&nbsp;{$form.is_condition.html}</td></tr></table>
<span id='conditions_section'>
<br />
<table>
  <tr>
    <td style="vertical-align:top;" width=25%>
        {$form.asthma_specifics.label}
    </td><td>
        {$form.asthma_specifics.html}
    </td>
  </tr>
  <tr>
    <td style="vertical-align:top;" width=25%> 
        {$form.other.label}
    </td><td>
        {$form.other.html}
    </td>
  </tr> 
</table>
<br />
</span>
<strong>School Counseling Permission</strong>
<p>
The school employs a school counselor whose primary role is to address the emotional, social, and psychological needs of the students at the school. They meet with individual students on an as needed basis and services may include individual and group counseling, conflict mediation, mental health education, crisis intervention, and consultation with parents and teachers. If long-term counseling is needed (ie: more than three sessions), parents will be contacted to discuss the option of referrals for outside therapy services. By agreeing to this form, you are giving your permission for your child to have access to these counseling services.
</p>
<p>{$form.counselor_authorization.html}&nbsp;I give permission for my child to participate in on-campus counseling services offered by the school’s counselor. I understand that my child’s confidentiality will be respected, however, pertinent information will be shared with school personnel when appropriate for the purpose of serving the health and safety interests of the child and the school.  Additionally, information will be shared under the following circumstances: <br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 1 When the child and/or parent authorize a release of information <br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 2. When child abuse or neglect is suspected <br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 3. When a child is a danger to him/herself <br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 4. When a child is a danger to others <br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 5. In the event of a valid medical emergency <br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 6. Upon receipt of a court order. <br />
Additionally, if a student over the age of 12 presents a danger to him/ herself or others without intervention or counseling, or if a student over the age of 12 is an alleged victim of abuse, the Counselor may provide services without parental consent. 
</p>
<p>
I further authorize the exchange of educational and psychological information relating to my child between the School Counselor and appropriate school teachers and personnel.
</p>
</fieldset>

{include file="SFS/Form/Family/Buttons.tpl"}

{literal}
<script type="text/javascript">

var counselorMessage = {/literal}{ts}'We see that you did not check the Counselor Authorization checkbox. The School will be in touch with you to discuss your concerns. In case you forgot to check it click CANCEL, otherwise click OK to proceed.'{/ts}{literal};

cj(document).ready(function() {
cj('textarea').TextAreaResizer();

var childInsured = cj("#child_insured");
toggleSection(childInsured, 'insurance_section');

cj(childInsured).click(function() {
    toggleSection(childInsured, 'insurance_section');
});

var aEle = 'input[name=is_allergy]';
toggleMedicalSection(aEle, 'allergies_section');
cj(aEle).click(function() {
   toggleMedicalSection(aEle, 'allergies_section');
});

var cEle = 'input[name=is_condition]';
toggleMedicalSection(cEle, 'conditions_section');
cj(cEle).click(function() {
   toggleMedicalSection(cEle, 'conditions_section');
});

});

function toggleSection(ele, hideElement) {
  if( cj(ele).attr('checked') == true ) {
    cj('#' + hideElement).hide();
  } else {
    cj('#' + hideElement).show();
  }
}

function toggleMedicalSection(ele, hideElement) {
  if ( cj(ele).attr('checked') == false ) {
    cj('#' + hideElement).hide();
  } else {
    cj('#' + hideElement).show();
  }
}

function confirmClicks( ) {
  if ( cj('#counselor_authorization').attr('checked') != true ) {
     return window.confirm(counselorMessage);
  }
  return true;
}

</script>
{/literal}
