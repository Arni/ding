<?php

/**
 * @file
 * Test availability provider functions.
 */

require_once "ProviderTestCase.php";
require_once 'FBS.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Test user provider functions.
 */
class UserProviderTest extends ProviderTestCase {

  /**
   * Test user authenticate.
   */
  public function testAuthenticate() {
    $this->provider = 'user';

    $json_responses = array(
      new Reply(
        array(
          // AuthenticatedPatron.
          'authenticated' => TRUE,
          'patron' => array(
            // Patron.
            'birthday' => '1946-10-15',
            'coAddress' => NULL,
            'address' => array(
              // Address
              'country' => 'Danmark',
              'city' => 'København',
              'street' => 'Alhambravej 1',
              'postalCode' => '1826',
            ),
            // ISIL of Vesterbro bibliotek
            'preferredPickupBranch' => '113',
            'onHold' => NULL,
            'patronId' => 234143,
            'recieveEmail' => TRUE,
            'blockStatus' => NULL,
            'recieveSms' => FALSE,
            'emailAddress' => 'onkel@danny.dk',
            'phoneNumber' => '80345210',
            'name' => 'Dan Turrell',
            'receivePostalMail' => FALSE,
            'defaultInterestPeriod' => 30,
            'resident' => TRUE,
          ),
        )
      ),

      // Unknown user.
      new Reply(
        array(
          // AuthenticatedPatron.
          'authenticated' => FALSE,
          'patron' => NULL,
        )
      ),
    );
    $httpclient = $this->getHttpClient($json_responses);

    // Run through tests.
    $fbs = fbs_service('1234', '', $httpclient, NULL, TRUE);

    // Check success.
    $res = $this->providerInvoke('authenticate', '151019463013', '1234');
    $expected = array(
      'success' => TRUE,
      'user' => array(
        'mail' => 'onkel@danny.dk',
        'data' => array(
          'display_name' => 'Dan Turrell',
        ),
        'private' => array(
          'branch' => 113,
        ),
      ),
      'creds' => TRUE,
    );
    $this->assertEquals($expected, $res);

    // Check failure.
    $res = $this->providerInvoke('authenticate', '151019463013', 'banana');
    $expected = array(
      'success' => FALSE,
    );
    $this->assertEquals($expected, $res);
  }
}
