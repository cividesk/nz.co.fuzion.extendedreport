<?php

require_once __DIR__ . '../../BaseTestClass.php';

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Test contribution DetailExtended class.
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class ContributionOverviewExtendedTest extends BaseTestClass implements HeadlessInterface, HookInterface, TransactionalInterface {

  protected $contacts = array();

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
    parent::setUp();
    $components = array();
    $dao = new CRM_Core_DAO_Component();
    while ($dao->fetch()) {
      $components[$dao->id] = $dao->name;
    }
    civicrm_api3('Setting', 'create', array('enable_components' => $components));
    $contact = $this->callAPISuccess('Contact', 'create', array('first_name' => 'Wonder', 'last_name' => 'Woman', 'contact_type' => 'Individual'));
    $this->contacts[] = $contact['id'];

    $contribution = $this->callAPISuccess('Contribution', 'create', array(
      'contact_id' => $contact['id'],
      'received_date' => '2 months ago',
      'total_amount' => 5,
      'financial_type_id' => 'Donation',
    ));
    $contribution = $this->callAPISuccess('Contribution', 'create', array(
      'contact_id' => $contact['id'],
      'received_date' => '1 month ago',
      'total_amount' => 500,
      'financial_type_id' => 'Donation',
    ));
    $contribution = $this->callAPISuccess('Contribution', 'create', array(
      'contact_id' => $contact['id'],
      'received_date' => '1 month ago',
      'total_amount' => 10,
      'financial_type_id' => 'Member Dues',
    ));
  }

  public function tearDown() {
    parent::tearDown();
  }

  /**
   * Test the ContributionOverviewExtended report with group by.
   */
  public function testContributionExtendedReport() {
    $this->callAPISuccess('Order', 'create', array('contact_id' => $this->contacts[0], 'total_amount' => 5, 'financial_type_id' => 2));
    $params = array(
      'report_id' => 'contribution/overview',
      'fields' => array (
        'contribution_financial_type_id' => '1',
        'contribution_total_amount' => '1',
      ),
      'order_bys' => array(
        1 => array(
          'column' => '-',
        ),
      ),
      'group_bys' => array(
        'contribution_financial_type_id' => 1,
      ),
    );
    $rows = $this->getRows($params);
    $this->assertEquals('Member Dues', $rows[1]['civicrm_contribution_contribution_financial_type_id']);
  }

}
