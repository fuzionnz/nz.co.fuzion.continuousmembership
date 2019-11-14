<div id="num_terms-tr">
  <div class='label'>{$form.num_terms.label}</div>
  <div class='content'>{$form.num_terms.html}</div>
  <div id ="cm_help"></div>
</div>

<script type="text/javascript">
  {literal}
    CRM.$(function($) {
      $('#num_terms-tr').insertAfter('#priceset');
      if (typeof CRM.vars.num_terms != 'underfined') {
        $.each(CRM.vars.num_terms, function (memType, term) {
          if (term > 1) {
            var msg = '<div class="help" id="cm_alert">Enter ' + term + ' in the Quantity field to directly update your <b> '+ memType +' </b> membership to current status. Please note that the total membership fee amount due will reflect your total quantity selected.</div><br/>';
            $(msg).appendTo('#cm_help');
          }
        });
      }
      if (typeof CRM.vars.select_contact != 'underfined' && CRM.vars.select_contact.value) {
        $('#select_contact_id').on('change', function() {
          $.getJSON(CRM.url("civicrm/ajax/contactmembershipterms", {cid: this.value}))
          .done(function (result) {
            $.each(result, function (memType, values) {
              if (values.term > 1) {
                var msg = '<div class="help" id="cm_alert">Enter ' + values.term + ' in the Quantity field to directly update your <b> '+ memType +' </b> membership to current status. Please note that the total membership fee amount due will reflect your total quantity selected.</div><br/>';
                $('#cm_help').html("");
                $('#help').remove();
                $(msg).appendTo('#cm_help');
                $('#num_terms').val(values.term);
                var exp_msg = 'Your <strong>' + memType + '</strong> membership expired on ' + values.end_date;
                $("<div id='help'>"+exp_msg+"</div>").insertAfter('#membership-intro');
              }
            });
          });
        });
      }
    });
  {/literal}
</script>