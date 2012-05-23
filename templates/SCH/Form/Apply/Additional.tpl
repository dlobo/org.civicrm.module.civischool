{include file="SCH/Form/Family/Buttons.tpl"}

<div id="common-form-controls" class="form-item">
    <fieldset>
      <legend>{ts}Additional Information{/ts}</legend>
      Tell us more about your child.Be as candid as possible in your description and include your childs strengths ,speacial interests or any other information that you think will help the Admissions Commitee know your child better.<br /><br />

Describe the Educational environment and experience you envision for your child.Please identify those elements of your education that you would like your child to experience
that you would like your child to experience or to avoid experiencing.<br /><br />

		    Our Goal is to meet every childs needs.To help us achieve this, please share with us if you anticipate that your child will benefit from any support services such as speech,physical,occupation therapies or accelerated pacing.<br /><br />

<table>
<tr>
 <td>{$form.how_do_hear.label} {$form.how_do_hear.html}</td>
 </tr>
 <tr>
 <td>{$form.ideal_refer.label} {$form.ideal_refer.html}</td>
 </tr>

 {foreach key=key item=item from=$fieldNames}
               <tr>
                  <td>{$form.$item.label}{$form.$item.html}</td>
               </tr>
      {/foreach}

</table>

    </fieldset>


</div>
