<?php

class CRM_Continuousmembership_BAO_Continuousmembership {

  /**
   * Set renewal mode in session.
   */
  public static function setRenewalMode($formName, $form) {
    $session = CRM_Core_Session::singleton();
    $session->set("is_renewal", FALSE);
    if ($formName == 'CRM_Member_Form_MembershipRenewal' && !empty($_POST['renewal_date'])) {
      $session->set("is_renewal", TRUE);
    }
    if ($formName == 'CRM_Contribute_Form_Contribution_Confirm' && is_numeric($form->_params['selectMembership'])) {
      $currentMembership = CRM_Member_BAO_Membership::getContactMembership(
        $form->_contactID,
        $form->_params['selectMembership'],
        CRM_Utils_Array::value('is_test', $form->_params, 0),
        $form->_membershipId,
        TRUE
      );
      if (!empty($currentMembership)) {
        $session->set("is_renewal", TRUE);
      }
    }
  }

  /**
   * Get num terms required to renew the membership to current.
   */
  public static function getNumTerms($contactId, $memTypeID) {
    $existingMembership = civicrm_api3('Membership', 'get', [
      'sequential' => 1,
      'contact_id' => $contactId,
      'membership_type_id' => $memTypeID,
    ]);
    if (empty($existingMembership['id'])) {
      return NULL;
    }
    for ($i = 1; $i < 10; $i++) {
      $existingMembershipEndDate = date('Ymd',
        strtotime("+1 day",
        strtotime($existingMembership['values'][0]['end_date']))
      );
      $dates = CRM_Member_BAO_MembershipType::getRenewalDatesForMembershipType($existingMembership['id'], $existingMembershipEndDate, $memTypeID, $i);
      $status = CRM_Member_BAO_MembershipStatus::getMembershipStatusByDate(
        CRM_Utils_Array::value('start_date', $dates),
        CRM_Utils_Array::value('end_date', $dates),
        CRM_Utils_Array::value('join_date', $dates),
        NULL,
        TRUE,
        $memTypeID
      );
      if ($status['name'] == 'Current') {
        return $i;
      }
    }
    return NULL;
  }

  /**
   * Multiply num_terms fee in submitted params.
   */
  public static function modifyTotalAmountInParams($formName, &$form) {
    if ($formName == 'CRM_Contribute_Form_Contribution_Main') {
      $session = CRM_Core_Session::singleton();
      $session->set("renew_num_terms", FALSE);

      if (!empty($form->_amount)) {
        $form->_amount = $form->_amount * $form->_params['num_terms'];
        $form->set('amount', $form->_amount);
        $session->set("renew_num_terms", $form->_params['num_terms']);
      }
      elseif ($amt = $form->get('amount')) {
        $form->_amount = $amt * $form->_params['num_terms'];
        $form->set('amount', $form->_amount);
        $session->set("renew_num_terms", $form->_params['num_terms']);
      }
    }
  }

}