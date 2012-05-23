$(document).ready( function() {
  // Hide the breadcrumb details, if no breadcrumb.
  $('#edit-sfschool-breadcrumb').change(
    function() {
      div = $('#div-sfschool-breadcrumb-collapse');
      if ($('#edit-sfschool-breadcrumb').val() == 'no') {
        div.slideUp('slow');
      } else if (div.css('display') == 'none') {
        div.slideDown('slow');
      }
    }
  );
  if ($('#edit-sfschool-breadcrumb').val() == 'no') {
    $('#div-sfschool-breadcrumb-collapse').css('display', 'none');
  }
  $('#edit-sfschool-breadcrumb-title').change(
    function() {
      checkbox = $('#edit-sfschool-breadcrumb-trailing');
      if ($('#edit-sfschool-breadcrumb-title').attr('checked')) {
        checkbox.attr('disabled', 'disabled');
      } else {
        checkbox.removeAttr('disabled');
      }
    }
  );
  $('#edit-sfschool-breadcrumb-title').change();
} );
