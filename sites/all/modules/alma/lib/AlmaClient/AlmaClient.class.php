<?php
// $Id$

/**
 * @file AlmaClient.php
 * Provides a client for the Axiell Alma library information webservice.
 */
class AlmaClient {
  /**
   * @var AlmaClientBaseURL
   * The base server URL to run the requests against.
   */
  private $base_url;

  /**
   * Constructor, checking if we have a sensible value for $base_url.
   */
  function __construct($base_url) {
    if (stripos($base_url, 'http') === 0 && filter_var($base_url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
      $this->base_url = $base_url;
    }
    else {
      // TODO: Use a specialised exception for this.
      throw new Exception('Invalid base URL: ' . $base_url);
    }
  }

  /**
   * Perform request to the Alma server.
   *
   * @param string $method
   *    The REST method to call e.g. 'patron/status'. borrCard and pinCode
   *    are required for all request related to library patrons.
   * @param array $params
   *    Query string parameters in the form of key => value.
   * @param boolean $check_status
   *    Check the status element, and throw an exception if it is not ok.
   * @return DOMDocument
   *    A DOMDocument object with the response.
   */
  public function request($method, $params = array(), $check_status = TRUE) {
    // For use with a non-Drupal-system, we should have a way to swap
    // the HTTP client out.
    $request = drupal_http_request(url($this->base_url . $method, array('query' => $params)));

    if ($request->code == 200) {
      // Since we currently have no neat for the more advanced stuff
      // SimpleXML provides, we'll just use DOM, since that is a lot
      // faster in most cases.
      $doc = new DOMDocument();
      $doc->loadXML($request->data);
      if (!$check_status || $doc->getElementsByTagName('status')->item(0)->getAttribute('value') == 'ok') {
        return $doc;
      }
      else {
        $message = $doc->getElementsByTagName('status')->item(0)->getAttribute('key');
        switch($message) {
          case '':
          case 'borrCardNotFound':
            throw new AlmaClientBorrCardNotFound('Invalid borrower credentials');
            break;
          default:
            throw new AlmaClientCommunicationError('Status is not okay: ' . $message);
        }
      }
    }
    else {
      throw new AlmaClientHTTPError('Request error: ' . $request->code . $request->error);
    }
  }

  /**
   * Get branch names from Alma.
   *
   * Formats the list of branches in an array usable for form API selects.
   *
   * @return array
   *    List of branches, keyed by branch_id
   */
  public function get_branches() {
    $branches = array();
    $doc = $this->request('organisation/branches');

    foreach ($doc->getElementsByTagName('branch') as $branch) {
      $branches[$branch->getAttribute('id')] = $branch->getElementsByTagName('name')->item(0)->nodeValue;
    }

    return $branches;
  }

  /**
   * Get reservation branch names from Alma.
   *
   * Formats the list of branches in an array usable for form API selects.
   *
   * @return array
   *    List of branches, keyed by branch_id
   */
  public function get_reservation_branches() {
    $branches = array();
    $doc = $this->request('reservation/branches');

    foreach ($doc->getElementsByTagName('branch') as $branch) {
      $branches[$branch->getAttribute('id')] = $branch->getElementsByTagName('name')->item(0)->nodeValue;
    }

    return $branches;
  }

  /**
   * Get department names from Alma.
   *
   * Formats the list of branches in an array usable for form API selects.
   *
   * @return array
   *    List of departments, keyed by department id.
   */
  public function get_departments() {
    $departments = array();
    $doc = $this->request('organisation/departments');

    foreach ($doc->getElementsByTagName('department') as $department) {
      $departments[$department->getAttribute('id')] = $department->getElementsByTagName('name')->item(0)->nodeValue;
    }

    return $departments;
  }

  /**
   * Get location names from Alma.
   *
   * Formats the list of branches in an array usable for form API selects.
   *
   * @return array
   *    List of locations, keyed by location id.
   */
  public function get_locations() {
    $locations = array();
    $doc = $this->request('organisation/locations');

    foreach ($doc->getElementsByTagName('location') as $location) {
      $locations[$location->getAttribute('id')] = $location->getElementsByTagName('name')->item(0)->nodeValue;
    }

    return $locations;
  }

  /**
   * Get sublocation names from Alma.
   *
   * Formats the list of branches in an array usable for form API selects.
   *
   * @return array
   *    List of sublocations, keyed by sublocation id.
   */
  public function get_sublocations() {
    $sublocations = array();
    $doc = $this->request('organisation/subLocations');

    foreach ($doc->getElementsByTagName('subLocation') as $sublocation) {
      $sublocations[$sublocation->getAttribute('id')] = $sublocation->getElementsByTagName('name')->item(0)->nodeValue;
    }

    return $sublocations;
  }

  /**
   * Get collection names from Alma.
   *
   * Formats the list of branches in an array usable for form API selects.
   *
   * @return array
   *    List of collections, keyed by collection id.
   */
  public function get_collections() {
    $collections = array();
    $doc = $this->request('organisation/collections');

    foreach ($doc->getElementsByTagName('collection') as $collection) {
      $collections[$collection->getAttribute('id')] = $collection->getElementsByTagName('name')->item(0)->nodeValue;
    }

    return $collections;
  }

  /**
   * Get patron information from Alma.
   */
  public function get_patron_info($borr_card, $pin_code, $extended = FALSE) {
    $path = ($extended) ? 'patron/informationExtended' : 'patron/information';
    $info_node = ($extended) ? 'patronInformationExtended' : 'patronInformation';

    $doc = $this->request($path, array('borrCard' => $borr_card, 'pinCode' => $pin_code));

    $info = $doc->getElementsByTagName($info_node)->item(0);

    $data = array(
      'patron_id' => $info->getAttribute('patronId'),
      'patron_name' => $info->getAttribute('patronName'),
      'addresses' => array(),
      'mails' => array(),
      'phones' => array(),
    );

    foreach ($info->getElementsByTagName('address') as $address) {
      $data['addresses'][] = array(
        'id' => $address->getAttribute('id'),
        'type' => $address->getAttribute('type'),
        'active' => (bool) ($address->getAttribute('isActive') == 'yes'),
        'care_of' => $address->getAttribute('careOf'),
        'street' => $address->getAttribute('streetAddress'),
        'postal_code' => $address->getAttribute('zipCode'),
        'city' => $address->getAttribute('city'),
        'country' => $address->getAttribute('country'),
      );
    }

    foreach ($info->getElementsByTagName('emailAddress') as $mail) {
      $data['mails'][] = array(
        'id' => $mail->getAttribute('id'),
        'mail' => $mail->getAttribute('address'),
      );
    }

    foreach ($info->getElementsByTagName('phoneNumber') as $phone) {
      $data['phones'][] = array(
        'id' => $phone->getAttribute('id'),
        'phone' => $phone->getAttribute('localCode'),
        'sms' => (bool) ($phone->getElementsByTagName('sms')->item(0)->getAttribute('useForSms') == 'yes'),
      );
    }

    if ($prefs = $info->getElementsByTagName('patronPreferences')->item(0)) {
      $data['preferences'] = array(
        'patron_branch' => $prefs->getAttribute('patronBranch'),
      );
    }

    foreach ($info->getElementsByTagName('patronBlock') as $block) {
      $data['blocks'][] = array(
        'code' => $block->getAttribute('code'),
        'is_system' => (bool) ($block->getElementsByTagName('isSystemBlock') == 'yes'),
      );
    }

    foreach ($info->getElementsByTagName('absentPeriod') as $period) {
      $data['absent_periods'][] = array(
        'id' => $period->getAttribute('absentId'),
        'from_date' => $period->getAttribute('absentFromDate'),
        'to_date' => $period->getAttribute('absentToDate'),
      );
    }

    return $data;
  }

  /**
   * Get reservation info.
   */
  public function get_reservations($borr_card, $pin_code) {
    $doc = $this->request('patron/reservations', array('borrCard' => $borr_card, 'pinCode' => $pin_code));

    $reservations = array();
    foreach ($doc->getElementsByTagName('reservation') as $item) {
      $reservation = array(
        'id' => $item->getAttribute('id'),
        'status' => $item->getAttribute('status'),
        'pickup_branch' => $item->getAttribute('reservationPickUpBranch'),
        'create_date' => $item->getAttribute('createDate'),
        'valid_from' => $item->getAttribute('validFromDate'),
        'valid_to' => $item->getAttribute('validToDate'),
        'queue_no' => $item->getAttribute('queueNo'),
        'organisation_id' => $item->getAttribute('organisationId'),
        'record_id' => $item->getElementsByTagName('catalogueRecord')->item(0)->getAttribute('id'),
        'record_available' => $item->getElementsByTagName('catalogueRecord')->item(0)->getAttribute('isAvailable'),
      );

      if ($reservation['status'] == 'fetchable') {
        $reservation['pickup_number'] = (integer) $item->getAttribute('pickUpNo');
        $reservation['pickup_expire_date'] = $item->getAttribute('pickUpExpireDate');
      }

      $reservations[] = $reservation;
    }
    usort($reservations, 'AlmaClient::reservation_sort');
    return $reservations;
  }

  /**
   * Helper function for sorting reservations.
   */
  private static function reservation_sort($a, $b) {
    return strcmp($a['create_date'], $b['create_date']);
  }

  /**
   * Get patron's current loans.
   */
  public function get_loans($borr_card, $pin_code) {
    $doc = $this->request('patron/loans', array('borrCard' => $borr_card, 'pinCode' => $pin_code));

    $loans = array();
    foreach ($doc->getElementsByTagName('loan') as $item) {
      $id = $item->getAttribute('id');
      $loans[$id] = array(
        'id' => $id,
        'branch' => $item->getAttribute('loanBranch'),
        'loan_date' => $item->getAttribute('loanDate'),
        'due_date' => $item->getAttribute('loanDueDate'),
        'is_renewable' => ($item->getElementsByTagName('loanIsRenewable')->item(0)->getAttribute('value') == 'yes') ? TRUE : FALSE,
        'record_id' => $item->getElementsByTagName('catalogueRecord')->item(0)->getAttribute('id'),
        'record_available' => $item->getElementsByTagName('catalogueRecord')->item(0)->getAttribute('isAvailable'),
      );
    }
    uasort($loans, 'AlmaClient::loan_sort');
    return $loans;
  }

  /**
   * Helper function for sorting loans.
   */
  private static function loan_sort($a, $b) {
    return strcmp($a['due_date'], $b['due_date']);
  }

  /**
   * Get patron's debts.
   */
  public function get_debts($borr_card, $pin_code) {
    $doc = $this->request('patron/debts', array('borrCard' => $borr_card, 'pinCode' => $pin_code));

    $data = array(
      'total_formatted' => 0,
      'debts' => array(),
    );

    if ($debts_attr = $doc->getElementsByTagName('debts')->item(0)) {
      $data['total_formatted'] = $debts_attr->getAttribute('totalDebtAmountFormatted');
    }

    foreach ($doc->getElementsByTagName('debt') as $item) {
      $id = $item->getAttribute('debtId');
      $data['debts'][$id] = array(
        'id' => $id,
        'date' => $item->getAttribute('debtDate'),
        'type' => $item->getAttribute('debtType'),
        'amount' => $item->getAttribute('debtAmount'),
        'amount_formatted' => $item->getAttribute('debtAmountFormatted'),
        'note' => $item->getAttribute('debtNote'),
      );
    }

    return $data;
  }

  /**
   * Add a reservation.
   */
  public function add_reservation($borr_card, $pin_code, $reservation) {
    // Initialise the query parameters with the current value from the
    // reservation array.
    $params = array(
      'borrCard' => $borr_card,
      'pinCode' => $pin_code,
      'reservable' => $reservation['id'],
      'reservationPickUpBranch' => $reservation['pickup_branch'],
      'reservationValidFrom' => $reservation['valid_from'],
      'reservationValidTo' => $reservation['valid_to'],
    );

    // If there's not a validFrom date, set it as today.
    if (empty($params['reservationValidFrom'])) {
      $params['reservationValidFrom'] = date('Y-m-d', $_SERVER['REQUEST_TIME']);
    }

    // If there's not a validTo date, set it a year in the future.
    if (empty($params['reservationValidTo'])) {
      $params['reservationValidTo'] = intval(date('Y', $_SERVER['REQUEST_TIME'])) + 1 . date('-m-d', $_SERVER['REQUEST_TIME']);
    }

    $doc = $this->request('patron/reservations/add', $params);
    return TRUE;
  }

  /**
   * Change a reservation.
   */
  public function change_reservation($borr_card, $pin_code, $reservation, $changes) {
    // Initialise the query parameters with the current value from the
    // reservation array.
    $params = array(
      'borrCard' => $borr_card,
      'pinCode' => $pin_code,
      'reservation' => $reservation['id'],
      'reservationPickUpBranch' => $reservation['pickup_branch'],
      'reservationValidFrom' => $reservation['valid_from'],
      'reservationValidTo' => $reservation['valid_to'],
    );

    // Then overwrite the values with those from the changes array.
    if (!empty($changes['pickup_branch'])) {
      $params['reservationPickUpBranch'] = $changes['pickup_branch'];
    }

    if (!empty($changes['valid_to'])) {
      $params['reservationValidTo'] = $changes['valid_to'];
    }

    $doc = $this->request('patron/reservations/change', $params);
    return TRUE;
  }

  /**
   * Remove a reservation.
   */
  public function remove_reservation($borr_card, $pin_code, $reservation) {
    $params = array(
      'borrCard' => $borr_card,
      'pinCode' => $pin_code,
      'reservation' => $reservation['id'],
    );

    $doc = $this->request('patron/reservations/remove', $params);
    return TRUE;
  }

  /**
   * Renew a loan.
   */
  public function renew_loan($borr_card, $pin_code, $loan_ids) {
    $params = array(
      'borrCard' => $borr_card,
      'pinCode' => $pin_code,
      'loans' => (is_array($loan_ids)) ? join(',', $loan_ids) : $loan_ids,
    );
    $doc = $this->request('patron/loans/renew', $params);
    return TRUE;
  }

  /**
   * Add phone number.
   */
  public function add_phone_number($borr_card, $pin_code, $new_number, $sms = TRUE) {
    $params = array(
      'borrCard' => $borr_card,
      'pinCode' => $pin_code,
      'localCode' => $new_number,
      'useForSms' => ($sms) ? 'yes' : 'no',
    );
    $doc = $this->request('patron/phoneNumbers/add', $params);
    return TRUE;
  }

  /**
   * Change phone number.
   */
  public function change_phone_number($borr_card, $pin_code, $number_id, $new_number, $sms = TRUE) {
    $params = array(
      'borrCard' => $borr_card,
      'pinCode' => $pin_code,
      'phoneNumber' => $number_id,
      'localCode' => $new_number,
      'useForSms' => ($sms) ? 'yes' : 'no',
    );
    $doc = $this->request('patron/phoneNumbers/change', $params);
    return TRUE;
  }

  /**
   * Delete phone number.
   */
  public function remove_phone_number($borr_card, $pin_code, $number_id) {
    $params = array(
      'borrCard' => $borr_card,
      'pinCode' => $pin_code,
      'phoneNumber' => $number_id,
    );
    $doc = $this->request('patron/phoneNumbers/remove', $params);
    return TRUE;
  }

  /**
   * Add e-mail address.
   */
  public function add_email_address($borr_card, $pin_code, $new_email) {
    $params = array(
      'borrCard' => $borr_card,
      'pinCode' => $pin_code,
      'address' => $new_email,
    );
    $doc = $this->request('patron/email/add', $params);
    return TRUE;
  }

  /**
   * Change e-mail address.
   */
  public function change_email_address($borr_card, $pin_code, $email_id, $new_email= FALSE) {
    $params = array(
      'borrCard' => $borr_card,
      'pinCode' => $pin_code,
      'emailAddress' => $email_id,
      'address' => $new_email,
    );

    if ($new_number) {
      $params['localCode'] = $new_number;
    }
    $doc = $this->request('patron/email/change', $params);
    return TRUE;
  }

  /**
   * Delete e-mail address.
   */
  public function remove_email_address($borr_card, $pin_code, $email_id) {
    $params = array(
      'borrCard' => $borr_card,
      'pinCode' => $pin_code,
      'emailAddress' => $email_id,
    );
    $doc = $this->request('patron/email/remove', $params);
    return TRUE;
  }

  /**
   * Change PIN code.
   */
  public function change_pin($borr_card, $pin_code, $new_pin) {
    $params = array(
      'borrCard' => $borr_card,
      'pinCode' => $pin_code,
      'pinCodeChange' => $new_pin,
    );
    $doc = $this->request('patron/pinCode/change', $params);
    return TRUE;
  }

  /**
   * Get details about one or more catalogue record.
   */
  public function catalogue_record_detail($alma_ids) {
    $params = array(
      'catalogueRecordKey' => $alma_ids,
    );
    $doc = $this->request('catalogue/detail', $params, FALSE);
    $data = array(
      'request_status' => $doc->getElementsByTagName('status')->item(0)->getAttribute('value'),
      'records' => array(),
    );

    foreach ($doc->getElementsByTagName('detailCatalogueRecord') as $elem) {
      $record = AlmaClient::process_catalogue_record_details($elem);
      $data['records'][$record['alma_id']] = $record;
    }

    return $data;
  }

  /**
   * Helper function for processing the catalogue records.
   */
  private static function process_catalogue_record_details($elem) {
    $record = array(
      'alma_id' => $elem->getAttribute('id'),
      'target_audience' => $elem->getAttribute('targetAudience'),
      'show_reservation_button' => ($elem->getAttribute('showReservationButton') == 'yes') ? TRUE : FALSE,
      'reservation_count' => $elem->getAttribute('nofReservations'),
      'loan_count_year' => $elem->getAttribute('nofLoansYear'),
      'loan_count_total' => $elem->getAttribute('nofLoansTotal'),
      'available_count' => $elem->getAttribute('nofAvailableForLoan'),
      'title_series' => $elem->getAttribute('titleSeries'),
      'title_original' => $elem->getAttribute('titleOriginal'),
      'resource_type' => $elem->getAttribute('resourceType'),
      'publication_year' => $elem->getAttribute('publicationYear'),
      'media_class' => $elem->getAttribute('mediaClass'),
      'extent' => $elem->getAttribute('extent'),
      'edition' => $elem->getAttribute('edition'),
      'category' => $elem->getAttribute('category'),
    );

    foreach ($elem->getElementsByTagName('author') as $item) {
      $record['authors'][] = $item->getAttribute('value');
    }

    foreach ($elem->getElementsByTagName('description') as $item) {
      $record['descriptions'][] = $item->getAttribute('value');
    }

    foreach ($elem->getElementsByTagName('isbn') as $item) {
      $record['isbns'][] = $item->getAttribute('value');
    }

    foreach ($elem->getElementsByTagName('language') as $item) {
      $record['languages'][] = $item->getAttribute('value');
    }

    foreach ($elem->getElementsByTagName('note') as $item) {
      $record['notes'][] = $item->getAttribute('value');
    }

    foreach ($elem->getElementsByTagName('title') as $item) {
      $record['titles'][] = $item->getAttribute('value');
    }

    if ($record['media_class'] != 'periodical') {
      $record['holdings'] = AlmaClient::process_catalogue_record_holdings($elem);
    }
    // Periodicals are nested holdings, which we want to keep that way.
    else {
      foreach ($elem->getElementsByTagName('compositeHoldings') as $holdings) {
        foreach ($holdings->childNodes as $year_holdings) {
          $year = $year_holdings->getAttribute('value');
          foreach ($year_holdings->childNodes as $issue_holdings) {
            $issue = $issue_holdings->getAttribute('value');
            $holdings = AlmaClient::process_catalogue_record_holdings($issue_holdings);
            $record['holdings'][$year][$issue] = $holdings;
            $issue_list = array(
              'available_count' => 0,
              'branches' => array(),
              'reservable' => $holdings[0]['reservable'],
            );

            // Also create an array with the totals for each issue.
            foreach ($holdings as $holding) {
              if ($holding['available_count'] > 0) {
                $issue_list['available_count'] += (int) $holding['available_count'];
                if (isset($issue_list['branches'][$holding['branch_id']])) {
                  $issue_list['branches'][$holding['branch_id']] += (int) $holding['available_count'];
                }
                else {
                  $issue_list['branches'][$holding['branch_id']] = (int) $holding['available_count'];
                }
              }
            }

            $record['issues'][$year][$issue] = $issue_list;
          }
        }
      }
    }

    return $record;
  }

  /**
   * Helper function for processing the catalogue record holdings.
   */
  private static function process_catalogue_record_holdings($elem) {
    $holdings = array();

    foreach ($elem->getElementsByTagName('holding') as $item) {
      $holdings[] = array(
        'reservable' => $item->getAttribute('reservable'),
        'status' => $item->getAttribute('status'),
        'ordered_count' => (int) $item->getAttribute('nofOrdered'),
        'checked_out_count' => (int) $item->getAttribute('nofCheckedOut'),
        'reference_count' => (int) $item->getAttribute('nofReference'),
        'total_count' => (int) $item->getAttribute('nofTotal'),
        'collection_id' => $item->getAttribute('collectionId'),
        'sublocation_id' => $item->getAttribute('subLocationId'),
        'location_id' => $item->getAttribute('locationId'),
        'department_id' => $item->getAttribute('departmentId'),
        'branch_id' => $item->getAttribute('branchId'),
        'organisation_id' => $item->getAttribute('organisationId'),
        'available_count' => (int) $item->getAttribute('nofAvailableForLoan'),
        'shelf_mark' => $item->getAttribute('shelfMark'),
      );
    }

    return $holdings;
  }

  /**
   * Get availability data for one or more records.
   */
  public function get_availability($alma_ids) {
    $data = array();
    $doc = $this->request('catalogue/availability', array('catalogueRecordKey' => $alma_ids));
    foreach ($doc->getElementsByTagName('catalogueRecord') as $record) {
      $data[$record->getAttribute('id')] = ($record->getAttribute('isAvailable') == 'yes') ? TRUE : FALSE;
    }
    return $data;
  }

  /**
   * Pay debts.
   */
  public function add_payment($debt_ids, $order_id = NULL) {
    $params = array('debts' => $debt_ids);

    if (!empty($order_id)) {
      $params['orderId'] = $order_id;
    }

    $doc = $this->request('patron/payments/add', $params);
    return TRUE;
  }

  /**
   * Change user’s preferred branch.
   *
   * @param string $borr_card
   *    Library patron's borrowing card number. Either just an arbitrary
   *    number printed on their library card or their CPR-code.
   * @param string $pin_code
   *    Library patron's current four digit PIN code.
   * @param string $branch_code
   *    New preferred branch.
   */
  public function change_patron_preferences($borr_card, $pin_code, $branch_code) {
    $params = array(
      'borrCard' => $borr_card,
      'pinCode' => $pin_code,
      'patronBranch' => $branch_code,
    );

    $doc = $this->request('patron/preferences/change', $params);
    return TRUE;
  }

  /**
   * Add an Alma absent period.
   *
   * @param string $borr_card
   *    Library patron's borrowing card number. Either just an arbitrary
   *    number printed on their library card or their CPR-code.
   * @param string $pin_code
   *    Library patron's current four digit PIN code.
   * @param string $from_date
   *    Absent period start date.
   * @param string $to_date
   *    Absent period start date.
   */
  public function add_absent_period($borr_card, $pin_code, $from_date, $to_date) {
    $params = array(
      'borrCard' => $borr_card,
      'pinCode' => $pin_code,
      'absentFromDate' => $from_date,
      'absentToDate' => $to_date,
    );

    $doc = $this->request('patron/absentPeriod/add', $params);
    return TRUE;
  }

  /**
   * Change existing absent period.
   *
   * @param string $borr_card
   *    Library patron's borrowing card number. Either just an arbitrary
   *    number printed on their library card or their CPR-code.
   * @param string $pin_code
   *    Library patron's current four digit PIN code.
   * @param string $absent_id
   *    ID for existing period.
   * @param string $from_date
   *    Absent period start date.
   * @param string $to_date
   *    Absent period start date.
   */
  public function change_absent_period($borr_card, $pin_code, $absent_id, $from_date, $to_date) {
    $params = array(
      'borrCard' => $borr_card,
      'pinCode' => $pin_code,
      'absentId' => $absent_id,
      'absentFromDate' => $from_date,
      'absentToDate' => $to_date,
    );

    $doc = $this->request('patron/absentPeriod/change', $params);
    return TRUE;
  }

  /**
   * Remove existing absent period.
   *
   * @param string $borr_card
   *    Library patron's borrowing card number. Either just an arbitrary
   *    number printed on their library card or their CPR-code.
   * @param string $pin_code
   *    Library patron's current four digit PIN code.
   * @param string $absent_id
   *    ID for existing period.
   */
  public function remove_absent_period($borr_card, $pin_code, $absent_id) {
    $params = array(
      'borrCard' => $borr_card,
      'pinCode' => $pin_code,
      'absentId' => $absent_id,
    );

    $doc = $this->request('patron/absentPeriod/remove', $params);
    return TRUE;
  }
}

/**
 * Define exceptions for different error conditions inside the Alma client.
 */

class AlmaClientInvalidURLError extends Exception {
}


class AlmaClientHTTPError extends Exception {
}


class AlmaClientCommunicationError extends Exception {
}


class AlmaClientBorrCardNotFound extends Exception {
}

