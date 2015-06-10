<?php

namespace Controllers\Ajax;

use Models\Data\AlertData;
use Models\Logic\AlertLogic;
use Models\Data\VehicleData;
use Models\Logic\VehicleLogic;
use Models\Data\TerritoryData;
use Models\Logic\TerritoryLogic;
use Models\Data\ContactData;
use Models\Logic\ContactLogic;
use Models\Logic\CronLogic;
use GTC\Component\Utils\Date;
use GTC\Component\Utils\CSV\CSVBuilder;
use GTC\Component\Utils\PDF\TCPDFBuilder;

/**
 * Class Alert
 *
 */
class Alert extends BaseAjax
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->alert_data = new AlertData;
        $this->alert_logic = new AlertLogic;
        $this->vehicle_data = new VehicleData;
        $this->vehicle_logic = new VehicleLogic;
        $this->territory_data = new TerritoryData;
        $this->territory_logic = new TerritoryLogic;
        $this->contact_data = new ContactData;
        $this->contact_logic = new ContactLogic;
        $this->cron_logic = new CronLogic;

        $this->landmark_alert_triggers = array( 'Entering', 'Exiting', 'Both' );

    }

    /**
     * Get the alerts by filtered paramaters (called via ajax)
     *
     * POST params: filter_type
     * POST params: vehiclegroup_id
     * POST params: contactgroup_id
     * POST params: alert_type
     * POST params: search_string
     *
     * @return array
     */
    public function getFilteredAlerts()
    {
        $ajax_data      = array();
        $post           = $this->request->request->all();
        $account_id     = $this->user_session->getAccountId();
        $user_timezone  = $this->user_session->getUserTimeZone();

        $sEcho = 0;
        if (isset($post['sEcho']) AND $post['sEcho'] != '') {
            $sEcho = $post['sEcho'];
        }

        $output = array(
            "sEcho"                 => intval($sEcho),
            "iTotalRecords"         => 0,
            "iTotalDisplayRecords"  => 0,
            "data"                  => array()
        );

        $search_type                = (isset($post['filter_type']) AND !empty($post['filter_type'])) ? $post['filter_type'] : 'group_filter';
        $params                     = $post;
        $params['default_value']    = '-';
        $params['user_timezone'] = $user_timezone;

        if ($search_type != '') {
            $alerts = $this->alert_logic->getFilteredAlerts($account_id, $params);
            if ($alerts !== false) {

                $output['iTotalRecords']        = (isset($alerts['iTotalRecords']) AND ! empty($alerts['iTotalRecords'])) ? $alerts['iTotalRecords'] : 0;
                $output['iTotalDisplayRecords'] = (isset($alerts['iTotalDisplayRecords']) AND ! empty($alerts['iTotalDisplayRecords'])) ? $alerts['iTotalDisplayRecords'] : 0;
                $output['data']                 = (isset($alerts['data']) AND ! empty($alerts['data'])) ? $alerts['data'] : array();
            }
        }

        echo json_encode( $output );
        exit;
    }

    /**
     * Get the alerts history by filter paramaters (called via ajax)
     *
     * POST params: filter_type
     * POST params: vehiclegroup_id
     * POST params: contactgroup_id
     * POST params: alert_type
     * POST params: search_string
     *
     * @return array
     */
    public function getAlertHistory()
    {
        $ajax_data      = array();
        $post           = $this->request->request->all();
        $account_id     = $this->user_session->getAccountId();
        $user_timezone  = $this->user_session->getUserTimeZone();

        $sEcho = 0;
        if (isset($post['sEcho']) AND $post['sEcho'] != '') {
            $sEcho = $post['sEcho'];
        }

        $output = array(
            "sEcho"                 => intval($sEcho),
            "iTotalRecords"         => 0,
            "iTotalDisplayRecords"  => 0,
            "data"                  => array()
        );

        $search_type                = (isset($post['filter_type']) AND !empty($post['filter_type'])) ? $post['filter_type'] : 'group_filter';
        $params                     = $post;
        $params['default_value']    = '-';
        $params['user_timezone'] = $user_timezone;

        if ($search_type != '') {
            $alerts = $this->alert_logic->getAlertHistory($account_id, $params);
            if ($alerts !== false) {

                foreach ( $alerts['data'] as $k1 => $v1 ) {
                    foreach ( $v1 as $k2 => $v2 ) {
                        switch ($k2) {
                            case    'unitname': if (! isset($v2) OR empty($v2)) {
                                                    if (! isset($alerts['data'][$k1]['serialnumber']) OR empty($alerts['data'][$k1]['serialnumber'])) {
                                                        $alerts['data'][$k1][$k2] = '<i>Not Set</i>' ;
                                                    } else {
                                                        $alerts['data'][$k1][$k2] = $alerts['data'][$k1]['serialnumber'] ;
                                                    }
                                                }
                                                break;
                        }
                    }
                }

                $output['iTotalRecords']        = (isset($alerts['iTotalRecords']) AND ! empty($alerts['iTotalRecords'])) ? $alerts['iTotalRecords'] : 0;
                $output['iTotalDisplayRecords'] = (isset($alerts['iTotalDisplayRecords']) AND ! empty($alerts['iTotalDisplayRecords'])) ? $alerts['iTotalDisplayRecords'] : 0;
                $output['data']                 = (isset($alerts['data']) AND ! empty($alerts['data'])) ? $alerts['data'] : array();
                
            }
        }

        echo json_encode( $output );
        exit;
    }

    public function addAlert()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();
        $account_id = $this->user_session->getAccountId();

        $params = $alert_unit = $alert_territory = $alert_contact = array();
        $error = '';

        // validate alert name
        if (! empty($post['alert-new-name'])) {
            $params['alertname'] = $post['alert-new-name'];
        } else {
            $error = 'Invalid alert name';
        }

        // validate alert type
        if (! empty($post['alert-new-type'])) {

            // validate the territory if the alert is a territory type alert (i.e. landmark or boundary)
            $alert_type = $alertTypeId = $params['alerttype_id'] = $post['alert-new-type'];

            switch ($alertTypeId) {
                case 1:
                    $alert_type = 'boundary';
                    break;
                case 2:
                    $alert_type = 'extended-stop';
                    break;
                case 3:
                    $alert_type = 'landmark';
                    break;
                case 4:
                    $alert_type = 'low-voltage';
                    break;
                case 5:
                    $alert_type = 'moving';
                    break;
                case 6:
                    $alert_type = 'non-reporting';
                    break;
                case 7:
                    $alert_type = 'over-speed';
                    break;
                case 8:
                    $alert_type = 'tow';
                    break;
            }

            if ($alert_type == 'landmark') {
                if (! empty($post['alert-'.$alert_type.'-mode-new'])) {
                    $mode = $post['alert-'.$alert_type.'-mode-new'];
                    if ($mode == 'single' OR $mode == 'group' OR $mode == 'all') {
                        if (! empty($post['alert-'.$alert_type.'-'.$mode.'-new'])) {
                            if ($mode == 'single') {
                                $alert_territory['territory_id'] = $post['alert-'.$alert_type.'-'.$mode.'-new'];
                            } else if ($mode == 'group') {
                                $alert_territory['territorygroup_id'] = $post['alert-'.$alert_type.'-'.$mode.'-new'];
                            }
                        } else {
                        	if ($mode == 'single' OR $mode == 'group')
                        	{
                            	$error = 'Invalid '.$alert_type.'/'.$alert_type.'group';
                            }
                            else if ($mode == 'all') {
                                $alert_territory['territory_id'] = 0;
                                $alert_territory['territorygroup_id'] = 0;
                            }
                        }

                        $params['alerttrigger'] = $this->enforceLandmarkAlertTriggerString($post['alert-landmark-trigger-new']);
                    } else {
                        $error = 'Invalid '.$alert_type.' mode 1';
                    }
                } else {
                    $error = 'Invalid '.$alert_type.' mode 2';
                }
            } else if ($alert_type == 'boundary') {
                if (! empty($post['alert-'.$alert_type.'-mode-new'])) {
                    $mode = $post['alert-'.$alert_type.'-mode-new'];
                    if ($mode == 'single' OR $mode == 'group' OR $mode == 'all') {
                        if (! empty($post['alert-'.$alert_type.'-'.$mode.'-new'])) {
                            if ($mode == 'single') {
                                $alert_territory['territory_id'] = $post['alert-'.$alert_type.'-'.$mode.'-new'];
                            } else if ($mode == 'group') {
                                $alert_territory['territorygroup_id'] = $post['alert-'.$alert_type.'-'.$mode.'-new'];
                            }
                        } else {
                            if ($mode == 'single' OR $mode == 'group')
                            {
                                $error = 'Invalid '.$alert_type.'/'.$alert_type.'group';
                            }
                            else if ($mode == 'all') {
                                $alert_territory['territory_id'] = 0;
                                $alert_territory['territorygroup_id'] = 0;
                            }

                            // for now, we'll hardcode action for territory: boundary = exiting
                            $params['alerttrigger'] = 'Exiting';
                        }
                    } else {
                        $error = 'Invalid '.$alert_type.' mode 1';
                    }
                } else {
                    $error = 'Invalid '.$alert_type.' mode 2';
                }
            } else if ($alert_type == 'low-voltage') {
                /* // for now, users cannot select the voltage threshold
                // validate trigger for low-voltage
                if (! empty($post['alert-voltage-new'])) {
                    $params['alerttrigger'] = $post['alert-voltage-new'];
                } else {
                    $error = 'Invalid voltage';
                }
                */
            } else if ($alert_type == 'extended-stop') {
                // validate trigger for extended-stop
                if (! empty($post['alert-extended-stop-duration-new'])) {
                    $params['alerttrigger'] = $post['alert-extended-stop-duration-new'];
                } else {
                    $error = 'Invalid duration';
                }
            } else if ($alert_type == 'moving') {
                // validate trigger for moving
                // no duration value

            } else if ($alert_type == 'non-reporting') {
                // validate trigger for non-reporting
                if (! empty($post['alert-non-reporting-duration-new'])) {
                    $params['alerttrigger'] = $post['alert-non-reporting-duration-new'];
                } else {
                    $error = 'Invalid duration';
                }
            } else if ($alert_type == 'over-speed') {
                // validate trigger for over-speed
                if (! empty($post['alert-over-speed-new'])) {
                    $params['alerttrigger'] = $post['alert-over-speed-new'];
                } else {
                    $error = 'Invalid Speed';
                }
            } else if ($alert_type == 'tow') {
                // validate trigger for tow
                // no duration value

            } else {
                $error = 'Invalid alert type';
            }
        } else {
            $error = 'An alert type is not set';
        }

        // validate alert unit
        if (! empty($post['alert-vehicle-mode-new'])) {
            $mode = $post['alert-vehicle-mode-new'];
            $params['unit'] = ucfirst($mode);
            if ($mode == 'single' OR $mode == 'group') {
                if (! empty($post['alert-vehicle-'.$mode.'-new'])) {
                    if ($mode == 'single') {
                        $alert_unit['unit_id'] = $post['alert-vehicle-'.$mode.'-new'];
                    }
                    else {
                        $alert_unit['unitgroup_id'] = $post['alert-vehicle-'.$mode.'-new'];
                    }
                } else {
                    $error = 'Invalid unit/unit group';
                }
            } else if ($mode == 'all') {
	            // do nothing
            } else {
                $error = 'Invalid vehicle mode';
            }
        } else {
            $error = 'A vehicle mode is not set';
        }

        // validate alert contact
        if (! empty($post['alert-contact-mode-new'])) {
            $mode = $post['alert-contact-mode-new'];
            if ($mode == 'single' OR $mode == 'group') {
                if (! empty($post['alert-contact-'.$mode.'-new'])) {
                    if ($mode == 'single') {
                        $alert_contact['contact_id'] = $post['alert-contact-'.$mode.'-new'];
                        // validate contact method
                        if (! empty($post['alert-contact-method-new'])) {
                            $alert_contact['method'] = $post['alert-contact-method-new'];

                            // check to see if the selected single contact has the selected contact method
                            $contact = $this->contact_logic->getContactById($alert_contact['contact_id']);
                            if (! empty($contact) AND is_array($contact)) {
                                $contact = array_pop($contact);
                                $methods = array();

                                if (! empty($contact['email'])) {
                                    $methods[] = 'email';
                                }

                                if (! empty($contact['cellnumber'])) {
                                    $methods[] = 'sms';
                                }

                                if (empty($methods)) {
                                    $error = 'This contact does not have any contact methods. You can update this contact in the CONTACTS section.';
                                } else if (! in_array($alert_contact['method'], $methods) AND ! ($alert_contact['method'] == 'all')) {
                                    $error = 'This contact does not have ' . (($alert_contact['method'] == 'email') ? 'E-Mail' : 'SMS') . ' as a contact method. Please select another contact method.';
                                }
                            } else {
                                $error = 'Failed to retrieve contact info';
                            }
                        } else {
                            $error = 'Invalid contact method';
                        }
                    } else {
                        $alert_contact['contactgroup_id'] = $post['alert-contact-'.$mode.'-new'];
                    }
                } else {
                    $error = 'Invalid contact/contact group';
                }
            } else if ($mode == 'reportonly') {
            	// don't require a contact for "report only" alert type
            } else {
                $error = 'Invalid contact mode';
            }
        } else {
            $error = 'A contact mode is not set';
        }

        // validate days
        if (! empty($post['alert-days-new'])) {
            $params['day'] = $post['alert-days-new'];
        } else {
            $error = 'Invalid days';
        }

        // validate hour range
        if (! empty($post['alert-hours-new'])) {
            $params['time'] = $post['alert-hours-new'];
            if (strtolower($params['time']) == 'all') {
                $params['starthour'] = $params['endhour'] = 0;
            } else if (strtolower($params['time']) == 'range') {
                // validate start and end hours
                if (isset($post['alert-hours-start-new']) AND isset($post['alert-hours-end-new'])) {
                    if (((intval($post['alert-hours-end-new']) - intval($post['alert-hours-start-new'])) > 0) OR  // hours are valid if end hour is after start hours OR
                        $post['alert-hours-end-new'] == '0') {                                                     // end hour is 0 (12AM)
                        $params['starthour'] = $post['alert-hours-start-new'];
                        $params['endhour'] = $post['alert-hours-end-new'];
                    } else {                                                                                        // anything else, invalid
                        $error = 'Hour Start has to be before Hour End';
                    }
                } else {
                    $error = 'Invalid hours';
                }
            } else {
                $error = 'Invalid hours';
            }
        } else {
            $error = 'Invalid hours';
        }

        if ($error == '') {
            $params['account_id'] = $account_id;
            $params['active'] = 1;
            $alert_id = $this->alert_logic->addAlert($params);
            if ($alert_id !== false) {
                $ajax_data['code'] = 0;
                $ajax_data['data']['alert_id'] = $alert_id;
                $ajax_data['message'] = 'Added Alert Success';

                // save alert - unit association
                if (! empty($alert_unit)) {
                    $alert_unit['alert_id'] = $alert_id;
                    $saved_unit = $this->alert_logic->addAlertUnit($alert_unit);
                    if ($saved_unit === false) { // if we failed to add the alert units, delete the alert
                        $ajax_data['code'] = 1;
                        $ajax_data['message'] = 'Failed To Add the Unit(s) to the Alert';
                    }
                }

                // save alert - landmark association
                if (! empty($alert_territory)) {
                    $alert_territory['alert_id'] = $alert_id;
                    $saved_territory = $this->alert_logic->addAlertTerritory($alert_territory);
                    if ($saved_territory === false) { // if we failed to add the alert units, delete the alert
                        $ajax_data['code'] = 1;
                        $ajax_data['message'] = 'Failed To Add the Landamrk(s)/Boundary(s) to the Alert';
                    }
                }

                // save alert - contact association
                if (! empty($alert_contact)) {
                    $alert_contact['alert_id'] = $alert_id;
                    $saved_contact = $this->alert_logic->addAlertContact($alert_contact);
                    if ($saved_contact === false) {
                        $ajax_data['code'] = 1;
                        $ajax_data['message'] = 'Failed To Add the Contact(s) to the Alert';
                    }
                }

                // delete the alert if it failed to add the unit, landmark, or contact
                if ($ajax_data['code'] == 1) {
                    $this->alert_logic->deleteAlert($alert_id, $account_id);
                }
            } else {
                // error
                $ajax_data['code'] = 1;
                $ajax_data['data'] = $post;
                $errors = $this->alert_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(', ',$errors);
                }

                $ajax_data['validation_error'][] = $ajax_data['message'] = $errors;
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = $error;
        }

        $this->ajax_respond($ajax_data);
    }

    public function updateAlertInfo()
    {
        $ajax_data  = $alert_updates = $unit_updates = $landmark_updates = $contact_updates = array();
        $post       = $this->request->request->all();
        $alert_id   = $post['primary_keys']['alertPk'];
        $hourStart  = (isset($post['primary_keys']['hourStart'])) ? $post['primary_keys']['hourStart'] : '';
        $hourEnd    = (isset($post['primary_keys']['hourEnd'])) ? $post['primary_keys']['hourEnd'] : '';
        $account_id = $this->user_session->getAccountId();
        $contact_method_error = '';

        if (isset($post['id'])) {
            switch ($post['id']) {
                case 'alert-name':
                    $alert_updates['alertname'] = $post['value'];
                    break;
                case 'alert-type':

                    $alert_updates['alerttype_id'] = $post['value'];

                    if ($post['value'] == 4 OR
                        $post['value'] == 5 OR
                        $post['value'] == 8)
                    {
                        $alert_updates['day'] = 'all';
                        $alert_updates['time'] = 'all';
                        $alert_updates['starthour'] = 0;
                        $alert_updates['endhour'] = 0;
                        $alert_updates['alerttrigger'] = 0;
                    }

                    break;
                case 'alert-landmark-single':
                    $alert_updates['alerttype_id'] = 3;
                    $landmark_updates['territory_id'] = $post['value'];
                    $landmark_updates['territorygroup_id'] = 0;
                    break;
                case 'alert-landmark-group':
                    $alert_updates['alerttype_id'] = 3;
                    $landmark_updates['territorygroup_id'] = $post['value'];
                    $landmark_updates['territory_id'] = 0;
                    break;
                case 'alert-landmark-all':
                    $alert_updates['alerttype_id'] = 3;
                    $landmark_updates['territorygroup_id'] = 0;
                    $landmark_updates['territory_id'] = 0;
                    break;
                case 'alert-landmark-trigger':
                    $alert_updates['alerttype_id'] = 3;
                    $alert_updates['alerttrigger'] = $this->enforceLandmarkAlertTriggerString($post['value']);
                    break;
                case 'alert-boundary-single':
                    $alert_updates['alerttype_id'] = 1;
                    $alert_updates['alerttrigger'] = 'Exiting';
                    $landmark_updates['territory_id'] = $post['value'];
                    $landmark_updates['territorygroup_id'] = 0;
                    break;
                case 'alert-boundary-group':
                    $alert_updates['alerttype_id'] = 1;
                    $alert_updates['alerttrigger'] = 'Exiting';
                    $landmark_updates['territorygroup_id'] = $post['value'];
                    $landmark_updates['territory_id'] = 0;
                    break;
                case 'alert-voltage':
                    /* // for now, users cannot select the voltage threshold
                    $alert_updates['alerttrigger'] = $post['value'];
                    $alert_updates['alerttype_id'] = '4';
                    */
                    break;
                case 'alert-over-speed':
                    $alert_updates['alerttrigger'] = $post['value'];
                    $alert_updates['alerttype_id'] = 7;
                    $alert_updates['day'] = 'all';
                    $alert_updates['time'] = 'all';
                    $alert_updates['starthour'] = 0;
                    $alert_updates['endhour'] = 0;
                    break;
                case 'alert-non-reporting-duration':
                    $alert_updates['alerttrigger'] = $post['value'];
                    $alert_updates['alerttype_id'] = 6;
                    $alert_updates['day'] = 'all';
                    $alert_updates['time'] = 'all';
                    $alert_updates['starthour'] = 0;
                    $alert_updates['endhour'] = 0;
                    break;
                case 'alert-extended-stop-duration':
                    $alert_updates['alerttrigger'] = $post['value'];
                    $alert_updates['alerttype_id'] = 2;
                    $alert_updates['day'] = 'all';
                    $alert_updates['time'] = 'all';
                    $alert_updates['starthour'] = 0;
                    $alert_updates['endhour'] = 0;
                    break;
                case 'alert-vehicle-single':
                    $alert_updates['unit'] = 'Single';
                    $unit_updates['unit_id'] = $post['value'];
                    $unit_updates['unitgroup_id'] = 0;
                    break;
                case 'alert-vehicle-group':
                    $alert_updates['unit'] = 'Group';
                    $unit_updates['unitgroup_id'] = $post['value'];
                    $unit_updates['unit_id'] = 0;
                    break;
                case 'alert-vehicle-all':
                    $alert_updates['unit'] = 'All';
                    $unit_updates['unitgroup_id'] = 0;
                    $unit_updates['unit_id'] = 0;
                    break;
                case 'alert-days':
                    $alert_updates['day'] = $post['value'];
                    break;
                case 'alert-hours':
                    $alert_updates['time'] = $post['value'];
                    $alert_updates['starthour'] = $alert_updates['endhour'] = 0;
                    break;
                case 'alert-hours-start':
                    $alert_updates['time']      = 'range';
                    $alert_updates['starthour'] = $post['value'];

                    if (isset($hourEnd) AND ! empty($hourEnd)) {
                        $alert_updates['endhour']   = $hourEnd;
                    } else {
                        if ($post['value'] < 23) {
                            $alert_updates['endhour']   = $post['value'] + 1;
                        } else {
                            $alert_updates['endhour']   = $post['value'];
                        }
                    }
                    break;
                case 'alert-hours-end':
                    $alert_updates['time']      = 'range';
                    $alert_updates['endhour']   = $post['value'];
                    $alert_updates['starthour'] = $hourStart;
                    break;
                case 'alert-contact-single':
                    $contact_updates['contact_id'] = $post['value'];
                    $contact_updates['contactgroup_id'] = 0;
                    break;
                case 'alert-contact-group':
                    $contact_updates['contactgroup_id'] = $post['value'];
                    $contact_updates['contact_id'] = 0;
                    break;
                case 'alert-contact-method':
                    $contact_updates['method'] = $post['value'];
                    // validate contact method to see if current single contact has the selected method
                    $current_contact = $this->alert_logic->getAlertContacts($alert_id);
                    if (! empty($current_contact) AND is_array($current_contact) AND ! empty($current_contact[0]['alert_contact_id'])) {
                        $current_contact = array_pop($current_contact);
                        $methods = array();

                        if (! empty($current_contact['email'])) {
                            $methods[] = 'email';
                        }

                        if (! empty($current_contact['cellnumber'])) {
                            $methods[] = 'sms';
                        }

                        if (empty($methods)) {
                            $contact_method_error = 'This contact does not have any contact methods. You can update this contact in the CONTACTS section.';
                        } else if (! in_array($contact_updates['method'], $methods) AND ! ($contact_updates['method'] == 'all')) {
                            $contact_method_error = 'This contact does not have ' . (($contact_updates['method'] == 'email') ? 'E-Mail' : 'SMS') . ' as a contact method. Please select another contact method.';
                        }
                    }
                    break;
            }
        }

        if (! empty($alert_updates)) {
            if ($this->alert_logic->updateAlert($alert_id, $account_id, $alert_updates)) {
                $ajax_data['code'] = 0;
                $ajax_data['data'] = $post;
                $ajax_data['message'] = 'Updated Alert Information';

                if (! empty($landmark_updates)) {
                    if ($this->alert_logic->updateAlertTerritory($alert_id, $landmark_updates)) {
                        // success message has already been set from above
                    } else {
                        $ajax_data['code'] = 1;
                        $ajax_data['message'] = 'Failed to Update Alert Landmarks/Boundaries';
                    }
                }

                if (! empty($unit_updates)) {
                    if ($this->alert_logic->updateAlertUnit($alert_id, $unit_updates)) {
                        // success message has already been set from above
                    } else {
                        $ajax_data['code'] = 1;
                        $ajax_data['message'] = 'Failed to Update Alert Units';
                    }
                }

                // remove any/all alert landmarks/boundaries if the alert type is low-voltage or extended-stops
                if (! empty($alert_updates['alerttype_id']) AND in_array($alert_updates['alerttype_id'], array(4,2,7,6,8,5))) {
                    $this->alert_logic->deleteAlertTerritory($alert_id);
                }
            } else {
                // error
                $ajax_data['code'] = 1;
                $ajax_data['data'] = $post;
                $errors = $this->alert_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(', ',$errors);
                }

                $ajax_data['validation_error'][] = $ajax_data['message'] = $errors;
            }
        } else if (! empty($contact_updates)) {
            if ($contact_method_error == '') {
                if ($this->alert_logic->updateAlertContact($alert_id, $contact_updates)) {
                    $ajax_data['code'] = 0;
                    $ajax_data['data'] = $post;
                    $ajax_data['message'] = 'Updated Alert Contact Information';
                } else {
                    $ajax_data['code'] = 1;
                    $ajax_data['message'] = 'Failed to Update Alert Contact';
                }
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['data'] = $post;
                $ajax_data['message'] = $ajax_data['validation_error']['alert-contact-method'] = $contact_method_error;
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Nothing to Update';
        }

        $this->ajax_respond($ajax_data);
    }

    public function deleteAlert()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();
        $alert_id   = (! empty($post['alert_id'])) ? $post['alert_id'] : '';

        if ($alert_id != '') {
            $account_id = $this->user_session->getAccountId();
            $deleted_alert = $this->alert_logic->deleteAlert($alert_id, $account_id);
            if ($deleted_alert !== false) {
                $ajax_data['code'] = 0;
                $ajax_data['message'] = 'Deleted alert';
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Failed to delete alert';
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid alert id';
        }

        $this->ajax_respond($ajax_data);
    }

    public function getAlertOptions($placeholder = null, $value = '')
    {
        $output = '[';

        if ($placeholder !== null) {  // used when setting up alert triggers
            $output .= '
                {
                    "value": "'.$value.'",
                    "text":  "'.$placeholder.'"
                },
            ';
        }

        $alerttypes     = $this->alert_logic->getAlertTypes();

        if ($alerttypes !== false) {
            $last_index = count($alerttypes) - 1;
            foreach ($alerttypes as $index => $alerttype) {
                $separator = ',';

                if ($index == $last_index) {
                    $separator = '';
                }

                $output .= '{"value": "' . $alerttype['alerttype_id'] . '", "text": "' . $alerttype['alerttypename'] . '"}' . $separator;
            }
        }

        $output .= ']';

        die($output);
    }

    // return alerttypes options for report section (no default setting)
    public function getAlertTypeOptions()
    {
        $output = '[';
        $output .= '
            {
                "value": "0",
                "text":  "All"
            },
        ';

        $alerttypes = $this->alert_logic->getAlertTypes();

        if ($alerttypes !== false) {
            $last_index = count($alerttypes) - 1;
            foreach ($alerttypes as $index => $alerttype) {
                $separator = ',';

                if ($index == $last_index) {
                    $separator = '';
                }

                $output .= '{"value": "' . $alerttype['alerttype_id'] . '", "text": "' . $alerttype['alerttypename'] . '"}' . $separator;
            }
        }

        $output .= ']';

        die($output);
    }

    public function getDaysOptions($placeholder = null, $value = '')
    {
        $output = '[';

        if ($placeholder !== null) {  // used when setting up alert triggers
            $output .= '
                {
                    "value": "'.$value.'",
                    "text":  "'.$placeholder.'"
                },
            ';
        }

        $output .= '
            {
                "value": "weekday",
                "text": "Weekdays"
            },
            {
                "value": "weekend",
                "text": "Weekends"
            }
        ]
        ';

        die($output);
    }

    public function getHoursOptions()
    {
        $output = '[';

        for ($hours = 0; $hours <= 23; $hours++) {

            $meridiem       = 'am';
            $hours_friendly = $hours;
            $hint           = '';

            if ($hours > 12) {
                $meridiem        = 'pm';
                $hours_friendly -= 12;
            }

            if ($hours == 12) {
                $meridiem = 'pm';
                $hint     = ' (noon)';
            }

            if ($hours === 0) {
                $hours_friendly = 12;
                $hint           = ' (midnight)';
            }

            $output .= '
                {
                    "value": "'.$hours.'",
                    "text": "'.sprintf("%02d:00 %s%s", $hours_friendly, $meridiem, $hint).'"
                }
            ';

            if ($hours !== 23) {
                $output .= ',';
            }

        }

        $output .= ']';

        die($output);
    }

    public function getVoltageOptions()
    {
        $output =
        '[
            {
                "value": "1.1",
                "text": "1.1v"
            },
            {
                "value": "2.2",
                "text": "2.2v"
            },
            {
                "value": "3.3",
                "text":  "3.3v"
            }
        ]
        ';

        die($output);
    }

    public function getExtendedStopOptions()
    {
        $output =
        '[
            {
                "value": "7",
                "text": "7 Days"
            },
            {
                "value": "14",
                "text": "14 Days"
            },
            {
                "value": "30",
                "text":  "30 Days"
            }
        ]
        ';

        die($output);
    }

    public function getNonReportingOptions()
    {
        $output =
        '[
            {
                "value": "1",
                "text": "1 Day"
            },
            {
                "value": "3",
                "text": "3 Days"
            },
            {
                "value": "7",
                "text": "7 Days"
            },
            {
                "value": "30",
                "text":  "30 Days"
            }
        ]
        ';

        die($output);
    }

    public function getOverSpeedOptions()
    {
        $output =
        '[';

        for ($speed = 25; $speed <= 100; $speed++) {

            $output .= '
                {
                    "value": "'.$speed.'",
                    "text": "'.$speed.' MPH"
                }';

            if ($speed !== 100) {
                $output .= ',';
            }

        }

        $output .= '
        ]
        ';

        die($output);
    }

    public function getTowOptions()
    {
        $output =
        '[
            {
                "value": "1",
                "text": "1"
            },
            {
                "value": "2",
                "text": "2"
            },
            {
                "value": "3",
                "text":  "3v"
            }
        ]
        ';

        die($output);
    }

    public function getMovingOptions()
    {
        $output =
        '[
        ]
        ';

        die($output);
    }

    public function getAlertById()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();

        if (! empty($post['alert_id'])) {
            $alert_id = $post['alert_id'];
            $alert = $this->alert_logic->getAlertById($alert_id);
            if ($alert !== false) {
                $ajax_data['code'] = 0;
                $ajax_data['data']['alert'] = $alert;
                $ajax_data['message'] = 'Successfully retrieved alert details';
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'No alert was found for the given alert ID';
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'No alert ID was given';
        }

        $this->ajax_respond($ajax_data);
    }

    public function exportAlertHistory($format, $filterType, $filterValue1, $filterValue2, $filterValue3, $filterValue4, $filterValue5, $filterValue6)
    {
        $account_id                 = $this->user_session->getAccountId();
        $user_timezone              = $this->user_session->getUserTimeZone();
        $results                    = array();

        $params['sEcho']            = 0;
        $params['bSearchable_0']    = true;
        $params['bSearchable_1']    = true;
        $params['bSearchable_2']    = true;
        $params['bSearchable_3']    = true;
        $params['bSearchable_4']    = true;
        $params['bSearchable_5']    = true;
        $params['bSortable_0']      = true;
        $params['bSortable_1']      = true;
        $params['bSortable_2']      = true;
        $params['bSortable_3']      = true;
        $params['bSortable_4']      = true;
        $params['bSortable_5']      = true;
        $params['iSortCol_0']       = 0;
        $params['iSortingCols']     = 3;
        $params['iColumns']         = 6;
        $params['mDataProp_0']      = 'deviceeventdate';
        $params['mDataProp_1']      = 'triggerdate';
        $params['mDataProp_2']      = 'alerttypename';
        $params['mDataProp_3']      = 'alertname';
        $params['mDataProp_4']      = 'unitname';
        $params['mDataProp_5']      = 'contactname';
        $params['sSortDir_0']       = 'desc';

        $params['filter_type']      = $filterType;
        $params['default_value']    = '';
        $params['user_timezone']    = $user_timezone;

        // set default parameters
        $params['vehiclegroup_id'] = $params['contactgroup_id'] = $params['alert_id'] = $params['alert_type'] = 'all';

        // if filterType is string, set the search string
        if ($filterType == 'string_search') {
            $params['search_string']    = $filterValue1;
        } else {
            // if filterType is group_filter, set params according for the group
            if ($filterValue1 != '' AND strtolower($filterValue1) != 'all') {
                $params['vehiclegroup_id'] = $filterValue1;
            }

            if ($filterValue2 != '' AND strtolower($filterValue2) != 'all') {
                $params['contactgroup_id'] = $filterValue2;
            }

            if ($filterValue3 != '' AND strtolower($filterValue3) != 'all') {
                $params['alert_id'] = $filterValue3;
            }

            if ($filterValue4 != '' AND strtolower($filterValue4) != 'all') {
                $params['alert_type'] = $filterValue3;
            }

            if ($filterValue5 != '') {
                $params['start_date'] = str_replace('_', ' ', $filterValue5);
            }

            if ($filterValue6 != '') {
                $params['end_date'] = str_replace('_', ' ', $filterValue6);
            }
        }

        $alerts = $this->alert_logic->getAlertHistory($account_id, $params);

        if ($alerts !== false) {
            $results = (isset($alerts['data']) AND ! empty($alerts['data'])) ? $alerts['data'] : array();
        }

        $filename   = str_replace(' ', '_', $this->user_session->getAccountName().'_alerthistory_'.$filterType.'-'.$filterValue1);
        if ($filterType != 'string_search') {
            $filename .= '-'.$filterValue2.'-'.$filterValue3.'-'.$filterValue4.'-'.$filterValue5.'-'.$filterValue6;
        }

        $fields = array('deviceeventdate' => 'Device Date & Time', 'triggerdate' => 'Processed Date & Time', 'alerttypename' => 'Alert Type', 'alertname' => 'Alert Name', 'unitname' => 'Vehicle Name', 'contactname' => 'Contacts', 'serialnumber' => 'Vehicle Id');

        if($format == 'pdf') {
            $pdf_builder = new TCPDFBuilder('L');
            $pdf_builder->createTitle('Alert History');
            $pdf_builder->createTable($results, $fields);
            $pdf_builder->Output($filename, 'D');
        } else {
            $csv_builder = new CSVBuilder();
            $csv_builder->setSeparator(',');
            $csv_builder->setClosure('"');
            $csv_builder->setFields($fields);
            $csv_builder->format($results)->export($filename);
        }

        exit();
    }

    private function enforceLandmarkAlertTriggerString($name)
    {
        if (in_array($name, $this->landmark_alert_triggers))
        {
            return $name;
        }
        return $this->landmark_alert_triggers[0];
    }
}
