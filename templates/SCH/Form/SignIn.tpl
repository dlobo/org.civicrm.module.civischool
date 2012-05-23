<span class="success-status" id="new-status-top" style="display:none;">{ts}Student has been enrolled for the course.{/ts}</span>
<br/>
<div class="form-layout">
    <table class="form-layout">
        <tr id="addNew">
            <td>
                {$form.student_id_top.label}&nbsp;{$form.student_id_top.html}
                &nbsp;&nbsp;
                {$form.course_name_top.label}&nbsp;{$form.course_name_top.html}
                &nbsp;&nbsp;
	            {if $signOut}
                    &nbsp;
                    {$form.signout_add_top.html}
                    &nbsp;
                {/if}
                &nbsp;&nbsp;
                <input type="submit" name="Add_top" id="Add_top" value="Add">
            </td>
        </tr>
    </table>
</div>
<br/>

<div>
Attendance Sheet for {$displayDate} {$time}
</div>
<span class="success-status" id="existing-status" style="display:none;">{ts}Attendance is saved.{/ts}</span>

<table id="records" class="display">
    <thead>
        <tr>
            <th>{ts}Student Name{/ts}</th>
            <th>{ts}Grade{/ts}</th>
            <th>{ts}Class Name{/ts}</th>
            <th>{ts}Attended{/ts}</th>
	    {if $signOut}
            <th>{ts}Sign Out Time{/ts}</th>
            {/if}
        </tr>
    </thead>
    
    <tbody>
        {foreach from=$studentDetails item=row}
        {if ! $row.is_marked}
        <tr>
            <td id="display_name_{$row.contact_id}">{$row.display_name}</td>	
            <td>{$row.grade}</td>	
            <td>{$row.course_name}{if $row.course_location}&nbsp;({$row.course_location}){/if}</td>	
	    {if $signOut}
            <td>
            <select name="signout_{$row.contact_id}" id="signout_{$row.contact_id}" class="signout_select">
                <option value="">- select -</option>
                <option value="1" {if $row.signout_block eq 1}selected="selected"{/if}>Before 3:30 pm</option>
                <option value="2" {if $row.signout_block eq 2}selected="selected"{/if}>3:30 - 4:30 pm</option>
                <option value="3" {if $row.signout_block eq 3}selected="selected"{/if}>4:30 - 5:15 pm</option>
                <option value="4" {if $row.signout_block eq 4}selected="selected"{/if}>5:15 - 6:00 pm</option>
                <option value="5" {if $row.signout_block eq 5}selected="selected"{/if}>After 6:00 pm</option>
            </select> 
            </td>
           {/if}
            <td><input type="checkbox" class="status" id="check_{$row.contact_id}" name="check_{$row.contact_id}" value="{$row.contact_id}:::{$row.course_name}" {if $row.is_marked}checked="1"{/if}></td>
        </tr>
        {/if}
        {/foreach}
    </tbody>
</table>

<br/>

<span class="success-status" id="new-status" style="display:none;">{ts}Student has been enrolled for the course.{/ts}</span>
<br/>
<div class="form-layout">
    <table class="form-layout">
        <tr id="addNew">
            <td>
                {$form.student_id.label}&nbsp;{$form.student_id.html}
                &nbsp;&nbsp;
                {$form.course_name.label}&nbsp;{$form.course_name.html}
                &nbsp;&nbsp;
	            {if $signOut}
                    &nbsp;
                    {$form.signout_add.html}
                    &nbsp;
                {/if}
                &nbsp;&nbsp;
                <input type="submit" name="Add" id="Add" value="Add">
            </td>
        </tr>
    </table>
</div>

<br/>
<br/>

{if $someSignedIn}
<table id="records_added" class="display">
    <thead>
        <tr>
            <th>{ts}Student Name{/ts}</th>
            <th>{ts}Grade{/ts}</th>
            <th>{ts}Class Name{/ts}</th>
            <th>{ts}Attended{/ts}</th>
	    {if $signOut}
            <th>{ts}Sign Out Time{/ts}</th>
            {/if}
        </tr>
    </thead>
    
    <tbody>
        {foreach from=$studentDetails item=row}
        {if $row.is_marked}
        <tr>
            <td id="display_name_{$row.contact_id}">{$row.display_name}</td>	
            <td>{$row.grade}</td>	
            <td>{$row.course_name}{if $row.course_location}&nbsp;({$row.course_location}){/if}</td>	
	    {if $signOut}
            <td>
            <select name="signout_{$row.contact_id}" id="signout_{$row.contact_id}" class="signout_select">
                <option value="">- select -</option>
                <option value="1" {if $row.signout_block eq 1}selected="selected"{/if}>Before 3:30 pm</option>
                <option value="2" {if $row.signout_block eq 2}selected="selected"{/if}>3:30 - 4:30 pm</option>
                <option value="3" {if $row.signout_block eq 3}selected="selected"{/if}>4:30 - 5:15 pm</option>
                <option value="4" {if $row.signout_block eq 4}selected="selected"{/if}>5:15 - 6:00 pm</option>
                <option value="5" {if $row.signout_block eq 5}selected="selected"{/if}>After 6:00 pm</option>
            </select> 
            </td>
           {/if}
            <td><input type="checkbox" class="status" id="check_{$row.contact_id}" name="check_{$row.contact_id}" value="{$row.contact_id}:::{$row.course_name}" {if $row.is_marked}checked="1"{/if}></td>
        </tr>
        {/if}
        {/foreach}
    </tbody>
</table>
{/if}

{literal}
<script type="text/javascript">
    cj( function( ) {
        {/literal}
        var sDayOfWeek = '{$dayOfWeek}';
        var sDate      = '{$date}';
        var sTime      = '{$time}';
        var contactID  = '';
        {literal}

        cj('.display').dataTable( {
            "bPaginate": false,
            "bInfo": false,
            "aoColumns": [
                          null,
                          null,
                          null,
                          { "bSortable": false }
                         ],
            "aaSorting": [[2,'asc'], [0,'asc']]
        } );        
    
        cj('.status').click( function( ) {
            var dataUrl = {/literal}"{crmURL p='civicrm/ajax/school/signin' h=0 }"{literal}
            var selectedValues = cj(this).val();
            var values = selectedValues.split( ':::');
            contactID = values[0];

            cj.post( dataUrl, { contactID: cj('#check_' + contactID ).val(), 
                                dayOfWeek: sDayOfWeek, 
                                date: sDate, 
                                time: sTime, 
                                checked: cj('#check_' + contactID).attr('checked'){/literal}{if $signOut}, signout: cj('#signout_' + contactID ).val(){/if}{literal} },
               function(data){
                  cj("#existing-status").html( data );
                  cj("#existing-status").show( );
            });
        });

        cj('.signout_select').change( function( ) {
            var dataUrl = {/literal}"{crmURL p='civicrm/ajax/school/signin' h=0 }"{literal}
            var selectedValues = cj(this).attr('id');
            var values = selectedValues.split( '_');
            contactID = values[1];
            
            cj('#check_' + contactID).attr('checked', true);
            
            cj.post( dataUrl, { contactID: cj('#check_' + contactID ).val(), 
                                dayOfWeek: sDayOfWeek, 
                                date: sDate, 
                                time: sTime, 
                                checked: cj('#check_' + contactID).attr('checked'){/literal}{if $signOut}, signout: cj('#signout_' + contactID ).val(){/if}{literal} },
               function(data){
                  cj("#existing-status").html( data );
                  cj("#existing-status").show( );
            });
        });
    
        cj("#Add").click( function( event ) {
              event.preventDefault( );

              var contactID = cj("#student_id").val( );
    	      var course    = cj("#course_name").val( );
    	      if ( contactID && course ) {
          	     var dataUrl = {/literal}"{crmURL p='civicrm/ajax/school/addnew' h=0 }"{literal};
              	     cj.post( dataUrl, { contactID: cj("#student_id").val( ),
                                         course: cj("#course_name").val( ),{/literal}{if $signOut}signout: cj("#signout_add").val( ),{/if}{literal}
                                         dayOfWeek: sDayOfWeek, 
                                         date: sDate, 
                                         time: sTime },
                     function(data){
                         // success action
                         cj("#new-status").html( data );
                     	 cj("#new-status").show( );

                    	 cj("#student_id").val( '' )
                     	 cj("#course_name").val( 'Yard Play' )
                     	 cj("#signout_add").val( '' )
              	     });
    	      }
          });
      	
        cj("#Add_top").click( function( event ) {
              event.preventDefault( );

              var contactID = cj("#student_id_top").val( );
    	      var course    = cj("#course_name_top").val( );
    	      if ( contactID && course ) {
          	     var dataUrl = {/literal}"{crmURL p='civicrm/ajax/school/addnew' h=0 }"{literal};
              	     cj.post( dataUrl, { contactID: cj("#student_id_top").val( ),
                                         course: cj("#course_name_top").val( ),{/literal}{if $signOut}signout: cj("#signout_add_top").val( ),{/if}{literal}
                                         dayOfWeek: sDayOfWeek, 
                                         date: sDate, 
                                         time: sTime },
                     function(data){
                         // success action
                         cj("#new-status-top").html( data );
                     	 cj("#new-status-top").show( );

                    	 cj("#student_id_top").val( '' )
                     	 cj("#course_name_top").val( 'Yard Play' )
                     	 cj("#signout_add_top").val( '' )
              	     });
    	      }
          });

      	  cj(".success-status").click( function( ) {
      	      cj(this).hide( );
      	  });    
    });
    
</script>
{/literal}