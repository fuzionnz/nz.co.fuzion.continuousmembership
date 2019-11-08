<?php

require_once 'continuousmembership.civix.php';
use CRM_Continuousmembership_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function continuousmembership_civicrm_config(&$config) {
  _continuousmembership_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function continuousmembership_civicrm_xmlMenu(&$files) {
  _continuousmembership_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function continuousmembership_civicrm_install() {
  _continuousmembership_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function continuousmembership_civicrm_postInstall() {
  _continuousmembership_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function continuousmembership_civicrm_uninstall() {
  _continuousmembership_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function continuousmembership_civicrm_enable() {
  _continuousmembership_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function continuousmembership_civicrm_disable() {
  _continuousmembership_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function continuousmembership_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _continuousmembership_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function continuousmembership_civicrm_managed(&$entities) {
  _continuousmembership_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function continuousmembership_civicrm_caseTypes(&$caseTypes) {
  _continuousmembership_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function continuousmembership_civicrm_angularModules(&$angularModules) {
  _continuousmembership_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function continuousmembership_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _continuousmembership_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function continuousmembership_civicrm_entityTypes(&$entityTypes) {
  _continuousmembership_civix_civicrm_entityTypes($entityTypes);
}

function continuousmembership_civicrm_pre($op, $objectName, $id, &$params) {
  //r19521 Renew membership from existing membership's end date instead of current date.
  if ($op == 'edit' && $objectName == 'Membership' && !empty($id)) {
    $session = CRM_Core_Session::singleton();
    if (in_array($params['membership_type_id'], [1,2]) && !empty($session->get("is_renewal"))) {
      $existingMembershipEndDate = date('Ymd',
        strtotime("+1 day",
        strtotime(CRM_Core_DAO::singleValueQuery("SELECT end_date FROM civicrm_membership WHERE id = {$id}")))
      );
      $numterms = empty($session->get("renew_num_terms")) ? 1 : $session->get("renew_num_terms");
      $dates = CRM_Member_BAO_MembershipType::getRenewalDatesForMembershipType($id, $existingMembershipEndDate, $params['membership_type_id'], $numterms);
      $status = CRM_Member_BAO_MembershipStatus::getMembershipStatusByDate(
        CRM_Utils_Array::value('start_date', $dates),
        CRM_Utils_Array::value('end_date', $dates),
        CRM_Utils_Array::value('join_date', $dates),
        NULL,
        TRUE,
        $params['membership_type_id']
      );
      $params['num_terms'] = $numterms;
      $params['status_id'] = $status['id'];
      $params = array_merge($params, $dates);
    }
  }
}

function continuousmembership_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Contribute_Form_Contribution_Main' && !empty($form->_currentMemberships)) {
    $defaults = [];
    foreach ($form->_currentMemberships as $memType) {
      $currentMembership = CRM_Member_BAO_Membership::getContactMembership(
        $form->_contactID,
        $memType,
        0,
        $form->_membershipId,
        TRUE
      );
      $memtypeDetails = civicrm_api3('MembershipType', 'get', [
        'sequential' => 1,
        'return' => ["name"],
        'id' => $memType,
      ])['values'][0];
      if (!empty($currentMembership)) {
        $defaults['num_terms'] = CRM_Continuousmembership_BAO_Continuousmembership::getNumTerms($form->_contactID, $memType);
        CRM_Core_Resources::singleton()->addVars('num_terms', [$memtypeDetails['name'] => $defaults['num_terms']]);
      }
    }
    if (!empty($defaults['num_terms'])) {
      $form->add('text', 'num_terms', ts('Quantity'), ['size' => 6]);
      $templatePath = realpath(dirname(__FILE__)."/templates");
      CRM_Core_Region::instance('page-body')->add(array(
        'template' => "{$templatePath}/membership.tpl"
      ));
      $form->setDefaults($defaults);
    }
  }
}

function continuousmembership_civicrm_preProcess($formName, &$form) {
  if (in_array($formName, ['CRM_Contribute_Form_Contribution_Main', 'CRM_Member_Form_MembershipRenewal'])) {
    CRM_Continuousmembership_BAO_Continuousmembership::setRenewalMode($formName, $form);
  }
}

function continuousmembership_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Contribute_Form_Contribution_Main' && !empty($form->_params['num_terms'])) {
    CRM_Continuousmembership_BAO_Continuousmembership::modifyTotalAmountInParams($formName, $form);
  }
}


// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function continuousmembership_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function continuousmembership_civicrm_navigationMenu(&$menu) {
  _continuousmembership_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _continuousmembership_civix_navigationMenu($menu);
} // */
