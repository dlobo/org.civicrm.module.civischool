<div id="help">
Welcome to the Student Diversity Page (step 5 of 5). The National Association of Independent Schools requests annually that the school report the race/ethnicity and family structure of our student body.  In addition, the admissions office collects this data in order to most accurately represent the diversity profile of the School.  Completing this information is OPTIONAL. Please check all categories that apply for your child.  When complete, click "Save." 
<br/>
{include file="SFS/Form/Family/HelpInfo.tpl"}
</div>

<fieldset>
<legend>Student Diversity Information for Student {$studentName}</legend>

<h2>Student Diversity Profile (Optional)</h2>
<table class="form-layout-compressed" style="width:80%">
  {include file="SFS/Form/Family/FormatCheckboxes.tpl" fieldName=race otherName=race_other}
  {include file="SFS/Form/Family/FormatCheckboxes.tpl" fieldName=race_hispanic otherName=race_hispanic_other}
  {include file="SFS/Form/Family/FormatCheckboxes.tpl" fieldName=race_asian otherName=race_asian_other}
  {include file="SFS/Form/Family/FormatCheckboxes.tpl" fieldName=race_family_structure otherName=''}
</table>
</fieldset>

{include file="SFS/Form/Family/Buttons.tpl"}

{literal}
<script type="text/javascript"> 

cj(document).ready(function() {    
  // others
  var raceOther     = cj("#race\\[other\\]");
  var hispanicOther = cj("#race_hispanic\\[other\\]");
  var asianOther    = cj("#race_asian\\[other\\]");

  // sections
  var sectionHispanic = cj("#race\\[hispanic\\]");
  var sectionAsian    = cj("#race\\[asian\\]");

  // hide/show others
  toggleOtherFields(raceOther,     'row_race_other' );
  toggleOtherFields(hispanicOther, 'row_race_hispanic_other' );
  toggleOtherFields(asianOther,    'row_race_asian_other');


  // hide/show sections
  toggleSection(sectionHispanic, 'row_race_hispanic' );
  toggleSection(sectionAsian,    'row_race_asian' );

  cj(raceOther).click(function() {
    toggleOtherFields(raceOther, 'row_race_other' );
  });
  cj(hispanicOther).click(function() {
    toggleOtherFields(hispanicOther, 'row_race_hispanic_other' );
  });
  cj(asianOther).click(function() {
    toggleOtherFields(asianOther, 'row_race_asian_other');
  });

  cj(sectionHispanic).click(function() {
    toggleSection(sectionHispanic, 'row_race_hispanic' );
  });

  cj(sectionAsian).click(function() {
    toggleSection(sectionAsian, 'row_race_asian' );
  });
});

function toggleOtherFields( ele, hideElement ) {
  if( cj(ele).attr('checked') == true ) {
    cj('#' + hideElement).show();
  } else {
    cj('#' + hideElement).hide();
  }
} 
function toggleSection( ele, hideElement ) {
  if( cj(ele).attr('checked') == true ) {
    cj('#' + hideElement).show();
  } else {
    cj('#' + hideElement).hide();
    cj('#' + hideElement + '_other').hide();
  }
} 

</script>
{/literal} 