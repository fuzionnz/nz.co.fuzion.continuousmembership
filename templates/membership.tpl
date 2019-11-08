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
          var msg = '<div class="help" id="cm_alert">Enter ' + term + ' in the Quantity field to directly update your <b> '+ memType +' </b> membership to current status. Note that the amount will also be charged '+ term +' times. </div><br/>';
          $(msg).appendTo('#cm_help');
        });
      }
    });
  {/literal}
</script>