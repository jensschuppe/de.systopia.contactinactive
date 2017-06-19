<?php

/*-------------------------------------------------------+
| SYSTOPIA - Contact Inactive                            |
| Copyright (C) 2017 SYSTOPIA                            |
| Author: M. Wire (mjw@mjwconsult.co.uk)                 |
| http://www.systopia.de/                                |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Contactinactive_Form_Set extends CRM_Contact_Form_Task {

  public function preProcess() {
    CRM_Utils_System::setTitle(ts('Set Contact(s) to Inactive'));
    parent::preProcess();
  }

  public function buildQuickForm() {
    parent::buildQuickForm();
  }

  public function postProcess() {
    foreach ($this->_contactIds as $contactId) {
      // Set all privacy options
      $this->setPrivacyOptions($contactId);
      // Cancel activities of type
      $this->cancelActivities($contactId, 'Phone Call');
    }
    parent::postProcess();
  }

  /**
   * Set all privacy options to enabled
   * @param $contactId
   */
  public function setPrivacyOptions($contactId) {
    $result = civicrm_api3('Contact', 'create', array(
      'id' => $contactId,
      'do_not_email' => 1,
      'do_not_phone' => 1,
      'do_not_mail' => 1,
      'do_not_sms' => 1,
      'do_not_trade' => 1,
      'is_opt_out' => 1,
    ));
  }

  /**
   * Disable all activities of type $activityTypeName
   * @param $contactId
   */
  public function cancelActivities($contactId, $activityTypeName) {
    $callCenterActivityId = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', $activityTypeName);
    $cancelledActivityStatus = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_status_id', 'Cancelled');

    $sql = "
UPDATE `civicrm_activity_contact` contact 
LEFT JOIN `civicrm_activity` activity 
ON contact.activity_id = activity.id 
SET activity.status_id = {$cancelledActivityStatus} 
WHERE activity_type_id = {$callCenterActivityId} 
  AND contact_id = {$contactId}
    ";
    CRM_Core_DAO::executeQuery($sql);
  }
}
