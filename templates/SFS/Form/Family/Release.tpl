<div id="help">
Welcome to the Release Page (step 4 of 5). Please review these releases carefully and confirm that you have read and agree to each statement by checking each box. When complete, click "Save & Next."
<br/>
{include file="SFS/Form/Family/HelpInfo.tpl"}
</div>

<fieldset>
<legend>Release Information for Student {$studentName}</legend>

<h2>Activities Permission and Assumption of Risk (Required)</h2>
<p>
I/we permit my/our child to attend and participate in all San Francisco School activities, events, off-campus travel/transportation, field trips (including overnights), sporting events, and other school sponsored activities (including Walk(s) on Thursday), some of which involve a heightened risk of injury and/or reduced level of supervision. While I/we understand that the School will attempt to exercise reasonable diligence to ensure the safety of my/our child in general, I/we understand that the level of supervision of specific activities and travel may be limited, and it is not practicable for the School to provide continuous supervision of all activities at all times.
</p>
<p>
I/we understand that there are inherent risks of serious bodily injury and property damage involved in all of the above activities and travel. On behalf of my/our child, I/we voluntarily assume and accept such risks of personal injury and property damage arising from my/our child's attendance and participation in such activities and travel. 
</p>
<p>
I/we also agree to assume financial responsibility for emergency care and services for my/our child, including rescue and transportation services. This express assumption of risk does not apply to liability for gross negligence or intentional injury, and is not intended to apply to the School’s insurer or non-agent, third parties. This consent shall remain effective throughout the duration of my child’s attendance at The San Francisco School. 
</p>
<table class="form-layout-compressed">
  <tr>
    <td>
       {$form.activity_authorization.html}&nbsp;<strong>I have read and agree to the statement noted above.</strong>
    </td>
  </tr>
</table>
</p>
<p>
<h2>Handbook Acknowledgement (Required)</h2>
</p>
<p>
I/we acknowledge that I/we have read <a href="http://sfschool.org/drupal/sites/default/files/families/protected/sfs-family-handbook-2010-11.pdf">The San Francisco School Parent Handbook</a> and agree to abide by the policies therein. I/we understand that The San Francisco School reserves the right to amend its policies and Handbook from time to time and I/we hereby agree to abide by such amended policies. The latest version of the Handbook can be downloaded <a href="http://sfschool.org/drupal/sites/default/files/families/protected/sfs-family-handbook-2009-10.pdf">here</a> and will be posted on The San Francisco School website.
<table class="form-layout-compressed">
  <tr>
    <td>
       {$form.handbook_authorization.html}&nbsp;<strong>I have read and agree to the statement noted above.</strong>
    </td>
  </tr>
</table>
</p>
<p>
<h2>Photo/Electronic Media Release (Required)</h2>
</p>
<p>
Throughout the school year, students may be videotaped and photographed in their classrooms or while involved in other school activities. Some of the images may be used in newspaper articles, promotional material or publications, including the School’s publications and its website. Care will be taken with any student image to ensure that no detailed identifying information appears with images used in externally circulated materials. Images with family surnames will be used expressly by school personnel for internal school use only.
</p>
<p>
I/we authorize my/our child to be videotaped and photographed by The San Francisco School and its agents, for school photos (class and individual portraits; identifying photos in the school’s database; internal school documents; and student ID cards), and during activities, events, off-campus travel, field trips, sporting events, and other school sponsored activities. I/we give the school permission to use these images at its discretion throughout the duration of my/our child’s tenure at The San Francisco School.
<table class="form-layout-compressed">
  <tr>
    <td>
       {$form.media_authorization.html}&nbsp;<strong>I have read and agree to the statement noted above.</strong>
    </td>
  </tr>
</table>
</p>
{if $form.ms_release_authorization}
<p>
<h2>Middle School After School Release (Required)</h2>
</p>
<p>
Please agree to the following only if you give permission for your Middle School child to seek alternative ways of getting home other than participating in our Carpool or following after school care.
</p>
<p>
As the parent/guardian of the above named Student I/we authorize my child to leave The San Francisco School campus and ride public transit, or bicycle or walk home, or to after school activities alone (without an adult to accompany him/her). I understand and accept the associated risks. This authorization shall remain in effect unless and until revoked by me in writing.
<br />
<br />
<table class="form-layout-compressed">
  <tr>
    <td>
       {$form.ms_release_authorization.html}
    </td>
  </tr>
</table>
{/if}
</p>
<p>
</p>
</fieldset>

{include file="SFS/Form/Family/Buttons.tpl"}

{literal}
<script type="text/javascript">

var mediaRelease = {/literal}{ts}'Media Release checkbox'{/ts}{literal};
var schoolRelease = {/literal}{ts}'Middle School After School Release'{/ts}{literal};

function confirmClicks( ) {
  var showWarning = false;
  var showAlert   = false;

  if ( ( cj('#activity_authorization').attr('checked') != true ) ||
       ( cj('#handbook_authorization').attr('checked') != true ) ) {
    return true;
  }

  var msg = {/literal}{ts}'We see that you did not check'{/ts}{literal};
  if ( cj('#media_authorization').attr('checked') != true ) {
    showWarning = true;
    msg =  msg + ' ' + mediaRelease;
  }

  {/literal}{if $form.ms_release_authorization}{literal}
  if ( cj('input[name=ms_release_authorization]:checked').val() != 1 ) {
    showWarning = true;
    if ( cj('#media_authorization').attr('checked') != true ) {  
      msg = msg + {/literal}{ts}' and '{/ts}{literal} + schoolRelease;
    } else {
      msg =  msg + ' ' + schoolRelease;
    }
  }
  {/literal}{/if}{literal}

  if ( showWarning ) {
    return window.confirm(msg + '. ' + {/literal}{ts}'The School will be in touch with you to discuss your concerns. In case you forgot to check it click CANCEL, otherwise click OK to proceed.'{/ts}{literal});
  }

  return true;	
}

</script>
{/literal}
