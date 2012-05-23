{include file="SFS/Form/Family/Buttons.tpl"}
<div id="common-form-controls" class="form-item">
    <fieldset>
      <legend>{ts}Family Information{/ts}</legend>
      <table>
      <tr>
        <td>{$form.p1_prefix_id.label} {$form.p1_prefix_id.html} {$form.p1_name.label} {$form.p1_name.html}</td>
        <td>{$form.p2_prefix_id.label} {$form.p2_prefix_id.html} {$form.p2_name.label} {$form.p2_name.html}</td>
      </tr>      
      <tr>
        <td>{$form.p1_relationship_type_id.label} {$form.p1_relationship_type_id.html}</td>
        <td>{$form.p2_relationship_type_id.label} {$form.p2_relationship_type_id.html}</td>
      </tr>
      <tr>
        <td>{$form.p1_home_address.label} {$form.p1_home_address.html}</td>
        <td>{$form.p2_home_address.label} {$form.p2_home_address.html}</td>
      </tr>
      <tr>
        <td>{$form.p1_country.label} {$form.p1_country.html}</td>
        <td>{$form.p2_country.label} {$form.p2_country.html}</td>
      </tr>  
      <tr>
        <td>{$form.p1_state.label} {$form.p1_state.html}</td>
        <td>{$form.p2_state.label} {$form.p2_state.html}</td>
      </tr>  
      <tr>
        <td>{$form.p1_city.label} {$form.p1_city.html}</td>
        <td>{$form.p2_city.label} {$form.p2_city.html}</td>
      </tr>  
      <tr>
        <td>{$form.p1_zip.label} {$form.p1_zip.html}</td>
        <td>{$form.p2_zip.label} {$form.p2_zip.html}</td>
      </tr>  
      <tr>
        <td>{$form.p1_home_phone.label} {$form.p1_home_phone.html}</td>
        <td>{$form.p2_home_phone.label} {$form.p2_home_phone.html}</td>
      </tr>  
      <tr>
        <td>{$form.p1_cell.label} {$form.p1_cell.html}</td>
        <td>{$form.p2_cell.label} {$form.p2_cell.html}</td>
      </tr>  
      <tr>
        <td>{$form.p1_email.label} {$form.p1_email.html}</td>
        <td>{$form.p2_email.label} {$form.p2_email.html}</td>
      </tr>  
      <tr>
        <td>{$form.p1_employer.label} {$form.p1_employer.html}</td>
        <td>{$form.p2_employer.label} {$form.p2_employer.html}</td>
      </tr>  
      <tr>
        <td>{$form.p1_occupation.label} {$form.p1_occupation.html}</td>
        <td>{$form.p2_occupation.label} {$form.p2_occupation.html}</td>
      </tr>  
      <tr>
        <td>{$form.p1_position.label} {$form.p1_position.html}</td>
        <td>{$form.p2_position.label} {$form.p2_position.html}</td>
      </tr>  
      <tr>
        <td>{$form.p1_business_address.label} {$form.p1_business_address.html}</td>
        <td>{$form.p2_business_address.label} {$form.p2_business_address.html}</td>
      </tr>  
      <tr>
        <td>{$form.p1_business_phone.label} {$form.p1_business_phone.html}</td>
        <td>{$form.p2_business_phone.label} {$form.p2_business_phone.html}</td>
      </tr>  
      </table>
      <table>
      {foreach key=key item=item from=$fieldNames}
               <tr>
                  <td width="50%">{$form.$item.label}</td><td>{$form.$item.html}</td>
               </tr>
      {/foreach}   
      </table>
    </fieldset>
</div>
{include file="SFS/Form/Family/Buttons.tpl"}