<?php
/**
 * Class CRM_Extendedreport_Form_Report_Price_Lineitemparticipant
 */
class CRM_Extendedreport_Form_Report_Price_Lineitemparticipant extends CRM_Extendedreport_Form_Report_ExtendedReport {
  protected $_addressField = FALSE;

  protected $_emailField = FALSE;

  protected $_summary = NULL;

  protected $_customGroupExtends = array('Participant', 'Individual', 'Contact');

  protected $_baseTable = 'civicrm_line_item';

  protected $_aclTable = 'civicrm_contact';

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->_columns = $this->getColumns('Contact') +
    array(
      'civicrm_phone' => array(
        'dao' => 'CRM_Core_DAO_Phone',
        'fields' => array(
          'phone' => array(
            'title' => ts('Phone'),
            'no_repeat' => TRUE,
          ),
        ),
        'grouping' => 'contact-fields',
      ),
    ) +
    $this->getColumns('Email') +
    $this->getColumns('Event') +
    $this->getColumns('Participant') +
    $this->getColumns('Contribution') +
    $this->getColumns('PriceField') +
    $this->getColumns('PriceFieldValue') +
    $this->getColumns('LineItem');
    parent::__construct();
  }

  /**
   * PreProcess function.
   */
  public function preProcess() {
    parent::preProcess();
  }

  /**
   * Select from clauses to use.
   *
   * (from those advertised using $this->getAvailableJoins())
   *
   * @return array
   */
  public function fromClauses() {
    return array(
      'priceFieldValue_from_lineItem',
      'priceField_from_lineItem',
      'participant_from_lineItem',
      'contribution_from_participant',
      'contact_from_participant',
      'event_from_participant',
      'email_from_contact',
    );
  }

  /**
   * Generate report FROM clause.
   */
  public function from() {
    if ($this->isTableSelected('civicrm_phone')) {
      $this->_extraFrom .= "
        LEFT JOIN civicrm_phone {$this->_aliases['civicrm_phone']}
           ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_phone']}.contact_id AND
              {$this->_aliases['civicrm_phone']}.is_primary = 1 ";
    }
    parent::from();
  }

}
