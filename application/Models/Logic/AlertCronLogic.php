<?php

namespace Models\Logic;

use Models\Logic\BaseLogic;

use Models\Data\AlertData;
use Models\Data\VehicleData;
use Models\Logic\VehicleLogic;
use Models\Logic\TerritoryLogic;

use GTC\Component\Utils\Date;
use GTC\Component\Utils\Arrayhelper;
use GTC\Component\Utils\CSV\CSVReader;

use Swift\Transport\Validate;

class AlertCronLogic extends BaseLogic
{
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();

        $this->alert_data       = new AlertData;
        $this->vehicle_data     = new VehicleData;
        $this->vehicle_logic    = new VehicleLogic;
        $this->territory_logic  = new TerritoryLogic;

        $this->landmarkCache = array();
    }

    /**
     * Process if event is in the alert date/time range trigger
     *
     * @param array $alert
     * @param array $event
     *
     * @return bool
     */
     public function inDateTimeRangeCheck($alert, $event)
     {
        // calculate event hour and event day
        $unit_event_datetime = Date::utc_to_locale($event['unittime'], $event['unit_timezone'], 'Y-m-d H:i:s');
        $event_hour = intval(date('H',strtotime($unit_event_datetime)));
        $event_day  = date ('w', strtotime($unit_event_datetime));

         switch ($alert['day']) {
             case 'all':
                    if ($alert['time'] == 'all') {
                        return true;
                    } else {
                        // if event hour is within alert time range
                        if (($event_hour >= intval($alert['starthour'])) AND ($event_hour <= intval($alert['endhour']))) {
                            return true;
                        }
                    }
                    return false;
                break;

            case 'weekday':
                    // weekday values (1,2,3,4,5,6)
                    $weekday_values = array(1,2,3,4,5);
                    if (in_array($event_day, $weekday_values)) {
                        if ($alert['time'] == 'all') {
                            return true;
                        } else {
                            // if event hour is within alert time range
                            if (($event_hour >= intval($alert['starthour'])) AND ($event_hour <= intval($alert['endhour']))) {
                                return true;
                            }
                        }
                    }
                    return false;
                break;

            case 'weekend':
                    // weekend values (0,6)
                    $weekend_values = array(0,6);
                    if (in_array($event_day, $weekend_values)) {
                        if ($alert['time'] == 'all') {
                            return true;
                        } else {
                            // if event hour is within alert time range
                            if (($event_hour >= intval($alert['starthour'])) AND ($event_hour <= intval($alert['endhour']))) {
                                return true;
                            }
                        }
                    }
                    return false;
                break;
         }

         return false;
     }

    /**
     * Process non reporting time if time triggers a Non Reporting Alert
     *
     * @param array $alert
     * @param array $unit
     * @param int    $index
     * @return array
     */
     public function nonReportingAlertCheck($alert, &$unit, $index = '')
     {
        $ret = array('triggered' => false, 'event' => '', 'trigger_id' => 0);

        if (! $unit['nonreportingstatus']) {
            //check for any event for alert time frame
            $alert_time_trigger = preg_replace("/[^0-9]/","", $alert['alerttrigger']);  //days

            $end_date   = Date::locale_to_utc(date('Y-m-d H:i:s'), SERVER_TIMEZONE);
            $start_date = Date::locale_to_utc(date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), (date('d') - $alert_time_trigger), date('Y'))), SERVER_TIMEZONE);    //    get alert time frame date
            $events     = $this->vehicle_logic->getVehicleHistory($unit['unit_id'], $unit['db'], $start_date, $end_date);

            if (empty($events) AND $events !== false) {
                // no events for date range, send non-reporting alert
                $ret['triggered']   = true;
                $ret['event'][]     = 'Non Reporting Alert for '.((preg_replace("/[^0-9]/","", $alert['alerttrigger']))).' Days';
                $ret['trigger_id']  = 1;

                // update status to be triggered
                $unit['temp_nonreportingstatus'] = 1;
            }
        }
        return $ret;
     }

    /**
     * Process total stop time and set alerts if total stop time triggers an Extended Stop Alert
     *
     * @param array $alert
     * @param array $unit
     * @param int    $index
     * @return array
     */
     public function extendedStopAlertCheck($alert, &$unit, $index = '')
     {
        $ret            = array('triggered' => false, 'event' => '', 'trigger_id' => 0);
        $drive_event    = $unit['events'][$index];
        $drive_event['unit_timezone']    = (isset($unit['unit_timezone']) AND ! empty($unit['unit_timezone'])) ? $unit['unit_timezone'] : SERVER_TIMEZONE;

        if (! empty($unit['temp_stopevent_id'])) {
            // get the event of the temp_extendedstop_id
            $stop_row = $this->vehicle_logic->getVehicleDataEventInfo($unit, $unit['temp_stopevent_id']);

            if (! empty($stop_row) AND isset($stop_row['unittime'])) {

                $time_diff                  = Date::time_difference_seconds($drive_event['unittime'], $stop_row['unittime']);
                $unit['total_stop_time']    = $time_diff;
            }
        }

        // convert max total stop days to seconds (days * 24 hours * 60 minutes * 60 seconds)
        $alert_trigger_time = preg_replace("/[^0-9]/","", $alert['alerttrigger']) * 24 * 60 * 60;

        // set alert if total drive time is greater than the set hour time
        if ($unit['total_stop_time'] > $alert_trigger_time)
        {
            $from_date  = Date::utc_to_locale($stop_row['unittime'], $drive_event['unit_timezone'], 'Y-m-d');
            $to_date    = Date::utc_to_locale($drive_event['unittime'], $drive_event['unit_timezone'], 'Y-m-d');

            $ret['triggered']   = true;
            $ret['event'][]     = "Extended Stop for ".((preg_replace("/[^0-9]/","", $alert['alerttrigger'])))." Days. '".$from_date."' To '".$to_date."'";
            $ret['trigger_id']  = $drive_event['id'];
        }

        return $ret;
     }

    /**
     * Checks if a landmark event occurred that triggers an alert
     *
     * @param array $alert
     * @param array $unit
     * @param int $index
     * @return array
     */
    public function speedAlertCheck($alert, &$unit, $index)
    {
        $res                    = array('triggered' => false, 'event' => '', 'trigger_id' => 0);
        $row                    = $unit['events'][$index];
        $over_speed_trigger     = preg_replace("/[^0-9]/","", $alert['alerttrigger']);

        // compare if event speed with alert trigger speed
        if (floor($row['speed']) > floor($over_speed_trigger) AND floor($row['speed']) < 150)
        {
            $row['speed']       = floor($row['speed']);
            $over_speed_trigger = floor($over_speed_trigger);

            $res['triggered']   = true;
            $res['event'][]     = "Over Speed: {$row['speed']} > {$over_speed_trigger}";
            $res['trigger_id']  = $row['id'];

            $unit['temp_speedevent_id'] =  $row['id'];
        }

        return $res;
    }

    /**
     * Checks if a landmark event occurred that triggers an alert
     *
     * @param array $alert
     * @param array $unit
     * @param int $index
     * @return array
     */
    public function landmarkAlertCheck($alert, &$unit, $index)
    {
        $ret                    = array('triggered' => false, 'event' => '', 'trigger_id' => 0);
        $row                    = $unit['events'][$index];
        $row['unit_timezone']   = (isset($unit['unit_timezone']) AND ! empty($unit['unit_timezone'])) ? $unit['unit_timezone'] : SERVER_TIMEZONE;
        $landmark_id            = (is_array($row['landmark']) AND ! empty($row['landmark']['territory_id'])) ? $row['landmark']['territory_id'] : 0;
        $alert_territory_array  = (isset($alert['territories'])) ? $alert['territories'] : array($alert['territory_id']);

        // check if the event landmark is the same as the landmark assign to the alert, if matching then mark alert as triggered (true), set what triggering event id
        if (($alert['alerttrigger'] == 'Entering' OR
             $alert['alerttrigger'] == 'Both')                      AND
            $landmark_id                                            AND     // Is a landmark
            $landmark_id !== $unit['temp_event_landmark_id']        AND     // Current value doesn't match last value
            in_array($landmark_id, $alert_territory_array)) {               // Current landmark is specified for alert OR alert on ALL landmarks

            // check if alert has a date/time range trigger
            $inside_datetime = $this->inDateTimeRangeCheck($alert, $row);
            if ($inside_datetime) {

                $ret['triggered']   = true;
                $ret['trigger_id']  = $row['id'];
                $ret['event'][]     = "Entering Landmark {$row['landmark']['territoryname']}";
            }
        }
        else if (($alert['alerttrigger'] == 'Exiting' OR
                 $alert['alerttrigger'] == 'Both')                  AND
                $unit['temp_event_landmark_id']                     AND                 // Is a previous landmark
                $unit['temp_event_landmark_id'] !== $landmark_id    AND                 // Current value doesn't match last value
                 in_array($unit['temp_event_landmark_id'], $alert_territory_array))     // Current landmark is specified for alert OR alert on ALL landmarks
        {
            // check if alert has a date/time range trigger
            $inside_datetime = $this->inDateTimeRangeCheck($alert, $row);
            if ($inside_datetime) {
                $ret['triggered']   = true;
                $ret['trigger_id']  = $row['id'];
                $ret['event'][]     = 'Exiting Landmark '.$this->cachedLandmarkName($unit['temp_event_landmark_id']);
            }
        }

        return $ret;
    }

    /**
     * Checks if a landmark event occurred that triggers an alert
     *
     * @param array $alert
     * @param array $unit
     * @param int $index
     * @return array
     */
    public function boundaryAlertCheck($alert, &$unit, $index)
    {
        $ret                    = array('triggered' => false, 'event' => '', 'trigger_id' => 0);
        $row                    = $unit['events'][$index];
        $boundary_id            = (isset($row['boundary']) AND $row['boundary'] != 0) ? $row['boundary'] : 0;
        $alert_territory_array  = (isset($alert['territories'])) ? $alert['territories'] : array($alert['territory_id']);

        // event boundary_id is not in the boundary alert ids, thus is is out of the boundary set out of boundary alert
        if (! in_array($boundary_id, $alert_territory_array) AND in_array($unit['temp_event_boundary_id'], $alert_territory_array) ){
            //NTD : Put landmark name in message
            //get boundary alert name ($alert['territory_id'])
            $ret['triggered']   = true;
            $ret['trigger_id']  = $row['id'];
            $ret['event'][]     = "Exiting Boundary";
        }

        return $ret;
    }

    /**
     * Updates Cron info for specified units
     *
     * Updates fields: alert_motion, alert_ignition, alertrid, onidle, onstop, stopped_reporting, landmark_alert_id
     *
     * @param array $units
     * @return bool
     */
    public function updateUnitAlertStatus($units)
    {
        if (is_array($units) AND ! empty($units))
        {
            foreach ($units as $index => $unit)
            {
                if ($unit['update'])
                {
                    if(isset($unit['process_to_id'])) {
                        $params['alertevent_id'] = $unit['process_to_id'];
                    }
                    if (isset($unit['temp_idleevent_id'])) {
                        $params['idleevent_id'] = $unit['temp_idleevent_id'];
                    }
                    if (isset($unit['temp_stopevent_id'])) {
                        $params['stopevent_id'] = $unit['temp_stopevent_id'];
                    }
                    if (isset($unit['temp_movingevent_id'])) {
                        $params['movingevent_id'] = $unit['temp_movingevent_id'];
                    }
                    if (isset($unit['temp_speedevent_id'])) {
                        $params['speedevent_id'] = $unit['temp_speedevent_id'];
                    }
                    if (isset($unit['temp_nonreportingstatus'])) {
                        $params['nonreportingstatus'] = $unit['temp_nonreportingstatus'];
                    }
                    if (isset($unit['temp_event_landmark_id'])) {
                        $params['landmark_id'] = $unit['temp_event_landmark_id'];
                    }
                    if (isset($unit['temp_event_boundary_id'])) {
                        $params['boundary_id'] = $unit['temp_event_boundary_id'];
                    }

                    $this->vehicle_data->updateUnitAlertStatus($unit['unit_id'], $params);
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Prepares an alert to be send via subsequent Cron script
     *
     * @param array $alert
     * @param array $unit
     * @param int $triggerid
     * @param string $textmessage
     * @return bool
     */
    public function setAlertSend($alert, $unit, $triggerid, $textmessage, $create_date)
    {
        if ( ! is_array($alert) OR empty($alert)) {
            $this->set_error_message('err_alert');
        }

        if ( ! is_array($unit) OR empty($unit)) {
            $this->set_error_message('err_unit');
        }

        if ( ! is_numeric($triggerid) OR $triggerid <= 0) {
            $this->set_error_message('err_parameters');
        }

        if (empty($textmessage)) {
            $this->set_error_message('err_parameters');
        }

        if (empty($alert_create_date)) {
            $alert_create_date = date('Y-m-d H:i:s');
        }

       if (! $this->hasError()) {
            $status_id = $this->alert_data->getAlertUnitStatus($unit['unit_id']);
            $textmessage = $textmessage . '(' . $status_id . ':' . $status_id['unitstatus_id'] . ')' ;
            return $this->alert_data->setAlertSend($alert['alert_id'], $alert['account_id'], $unit['unit_id'], $triggerid, $textmessage, $create_date);
        }

        return FALSE;
    }

    /**
     * Call to log the sent queued alert
     *
     * @param array $alert
     * @param string $alert_sent_date
     *
     * @return bool
     */
    public function logAlertHistory($alert, $alert_sent_date)
    {
        if (! is_array($alert) OR count($alert) == 0) {
            $this->setErrorMessage('err_unit');
        }

        if ($alert_sent_date == '') {
            $this->set_error_message('err_parameters');
        }

        if (! $this->hasError()) {

            return $this->alert_data->logAlertHistory($alert, $alert_sent_date);
        }

        return false;
    }

    /**
     * Call to update specified queued alert
     *
     * @param int $alertsend_id
     * @param array $params
     *
     * @return bool
     */
    public function updateAlertSend($alertsend_id, $params)
    {
        if (! is_numeric($alertsend_id) OR $alertsend_id <= 0) {
            $this->set_error_message('err_parameters');
        }

        if (! is_array($params) OR count($params) == 0) {
            $this->setErrorMessage('err_param');
        }

        if (! $this->hasError()) {

            return $this->alert_data->updateAlertSend($alertsend_id, $params);
        }

        return false;
    }

    /**
     * Call to delete queued alerts that has already been sent
     *
     * @return bool
     */
    public function deleteAlertSent()
    {
        return $this->alert_data->deleteAlertSent();
    }

    /**
     * Gets alert emails that have not been processed
     *
     * @return array
     */
    public function getAlertSendEmail($alertSendThreshold = null)
    {
        $ret            = array();
        $no_send        = array();
        $already_sent   = array();

        // pull alerts to send
        $results = $this->alert_data->getAlertSendEmail($alertSendThreshold);
        if (isset($results) AND ! empty($results)) {
            foreach ($results as $email) {
                if ($email['sent'] == 1) {
                    $already_sent[$email['unit_id'] . '.' . $email['alert_id'].'.'.$email['unitevent_id']] = $email['unit_id'] . '.' . $email['alert_id'].'.'.$email['unitevent_id'];
                } else if ($email['sent'] == 0 AND isset($already_sent[$email['unit_id'] . '.' . $email['alert_id'].'.'.$email['unitevent_id']])) {
                    $no_send[] = $email['alertsend_id'];
                } else {
                    if ( ! isset($ret[$email['alertsend_id']]) AND ! in_array($email['alertsend_id'], $no_send)) {
                        $ret[$email['alertsend_id']] = $email;
                        $already_sent[$email['unit_id'] . '.' . $email['alert_id'].'.'.$email['unitevent_id']] = $email;
                    }
                }
            }

            if ( ! empty($no_send)) {
                $alertsend_process_date = Date::locale_to_utc(date('Y-m-d H:i:s'), SERVER_TIMEZONE);
                foreach ($no_send as $key => $alertsend_id) {
                    $this->updateAlertSend($alertsend_id, array('sent' => 1, 'sentdate' => $alertsend_process_date));
                }
            }
        }

        return $ret;
    }

    /**
     * Pull range of events by date for specified unit
     *
     * @param int $unit_id
     * @param string $start_time
     * @param string $end_time
     * @return array
     */
    public function getVehicleEvents(&$account_units, $unit_id, $start_time = '', $end_time = '')
    {
        if (($start_time !== '') AND ($end_time !== '')) {      //    get events by start time and end time
            //    get events from the previous day
            $date = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')-1, date('Y')));

            $start_time = Date::locale_to_utc(date('Y-m-d H:i:s', strtotime($date.' ' . str_pad($start_time, 2, '0', STR_PAD_LEFT) . ':00:00')), SERVER_TIMEZONE);

            if ($end_time < 24) {
                $end_time = Date::locale_to_utc(date('Y-m-d H:i:s', strtotime($date.' ' . str_pad($end_time, 2, '0', STR_PAD_LEFT) . ':00:00')), SERVER_TIMEZONE);
            } else {
                $end_time = Date::locale_to_utc(date('Y-m-d H:i:s', strtotime($date.' ' . str_pad($end_time-1, 2, '0', STR_PAD_LEFT) . ':59:59')), SERVER_TIMEZONE);
            }

            // get events data
            $events = $this->vehicle_data->getVehicleUnitEvents($account_units[$unit_id], $start_time, $end_time);
            if (! empty($events)){
                foreach($events as $key => $event){
                    $landmark = '';
                    if ($event['landmark_id'] > 0) {
                        $landmark = $this->territory_logic->getTerritoryByIds($event['landmark_id'], false);
                        if (count($landmark) == 1) {
                            $landmark = array_pop($landmark);
                        }
                    }
                    $events[$key]['landmark'] = $landmark;

                    $boundary = '';
                    if ($event['boundary_id'] > 0) {
                        $boundary = $event['boundary_id'];
                    }
                    $events[$key]['boundary'] = $boundary;
                }
            }

            $account_units[$unit_id]['events'] = $events;

        } else {
            //    get event by last processed rid
            if ( ! empty($account_units[$unit_id]['alertevent_id'])) {

                // get event only if process_to_id is not currently set
                if (empty($account_units[$unit_id]['process_to_id'])) {
                    // get events data
                    $events = $this->vehicle_data->getVehicleUnitEventsAfterId($account_units[$unit_id], $account_units[$unit_id]['alertevent_id']);
                    if (! empty($events)){
                        foreach($events as $key => $event){
                            $landmark = '';
                            if ($event['landmark_id'] > 0) {
                                $landmark = $this->territory_logic->getTerritoryByIds($event['landmark_id'], false);
                                if (count($landmark) == 1) {
                                    $landmark = array_pop($landmark);
                                }
                            }
                            $events[$key]['landmark'] = $landmark;

                            $boundary_id = '';
                            if ($event['boundary_id'] > 0) {
                                $boundary_id = $event['boundary_id'];
                            }
                            $events[$key]['boundary'] = $boundary_id;
                        }
                    }

                    $account_units[$unit_id]['events'] = $events;

                    if (empty($account_units[$unit_id]['events'])) {
                        unset($account_units[$unit_id]['events']);
                        $account_units[$unit_id]['process_to_id'] = $account_units[$unit_id]['alertevent_id'];
                    } else {
                        $last_event                                 = end($account_units[$unit_id]['events']);
                        $account_units[$unit_id]['process_to_id']   = $last_event['id'];
                        $account_units[$unit_id]['update']          = true;
                    }
                }
            } else {
                // No alerts will be processed for this unit - pull last event to set appropriate values
                $last_event = $this->vehicle_data->getLastReportedEvent($account_units[$unit_id]);

                if ( ! empty($last_event)) {
                    $last_event                                 = $last_event;
                    $account_units[$unit_id]['alertevent_id']   = $last_event['id'];
                    $account_units[$unit_id]['process_to_id']   = $last_event['id'];
                    $account_units[$unit_id]['update']          = true;
                }
            }
        }
    }

    /**
     * Process the alert for targeted unit events, set/queue alert if triggered
     *
     * @param array $alert
     * @param array $unit
     *
     */
    public function processAlertEvents($alert, &$unit)
    {
        // get all event info for comparision
        $manufacture_events = $this->vehicle_logic->getManufacturerEvents();
        //$manufacture_events['eventnamearray'];
        //$manufacture_events['driveevent_keys'];
        //$manufacture_events['stopevent_keys'];
        //$manufacture_events['ignition_on_keys'];
        //$manufacture_events['ignition_off_keys'];

        if (isset($unit['events'])) {

            // Set up tmp_ references
            switch($alert['alerttype_id']) {
                case 1:     //'boundary':
                    $unit['temp_event_boundary_id'] = $unit['previous_boundary_id'];
                    break;
                case 2:     //'extended-stop':
                    $unit['total_stop_time']    = 0;
                    $unit['temp_stopevent_id']  = $unit['stopevent_id'];    //set to match the unitalertstatus table value

                    if ($unit['temp_stopevent_id'] == 0) {
                        $stop = false;
                    } else {
                        $stop = true;
                    }
                    break;
                case 3:     //'landmark':
                    $unit['temp_event_landmark_id'] = $unit['previous_landmark_id'];
                    break;
                case 5:     //'moving':
                    break;
                case 6:     //'non-reporting':
                    $unit['temp_nonreportingstatus'] = $unit['nonreportingstatus'];
                    break;
                case 7:     //'over-speed':
                    $unit['temp_speedevent_id']     = $unit['speedevent_id'];   //set to match the unitalertstatus table value
                    break;
                default:
                    break;
            }

            $unit['temp_movingevent_id'] = $unit['movingevent_id'];  //set to match the unitalertstatus table value

            // loop for each event of the unit
            foreach ($unit['events'] as $index => $row) {

                $res                    = array('triggered' => false, 'event' => '', 'trigger_id' => 0);
                $row['eventname']       = $manufacture_events['eventnamearray'][$row['event_id']];
                $row['unit_timezone']   = $unit['unit_timezone'];

                switch($alert['alerttype_id']) {
                    case 1:     //'boundary':

                        // if boundary alert
                        if ($alert['alerttrigger'] == 'Exiting') {
                            $res =  $this->boundaryAlertCheck($alert, $unit, $index);
                            if ( ! empty($row['boundary'])) {
                                // save boundary_id if any
                                $unit['temp_event_boundary_id'] = $row['boundary'];
                            } else {
                                // reset to 0
                                $unit['temp_event_boundary_id'] = 0;
                            }
                        }
                        break;

                    case 2:     //'extended-stop':

                        if ($stop) {
                            if (! empty($unit['temp_stopevent_id']) AND (array_key_exists($row['event_id'], $manufacture_events['driveevent_keys']))) {
                                // if previous stop AND this event is a Drive event, then check for stop duration
                                $res = $this->extendedStopAlertCheck($alert, $unit, $index);
                                if ( ! empty($unit['temp_stopevent_id'])) {
                                    $unit['temp_stopevent_id'] = 0;
                                }
                                $stop = false;
                            }
                        } else if (empty($unit['temp_stopevent_id']) AND (array_key_exists($row['event_id'], $manufacture_events['stopevent_keys']))) {
                            // no previous stop event (was in drive status) but this event is a stop event, store the stop event id
                            $unit['temp_stopevent_id'] = $row['id'];
                            $stop = true;
                        } else if (! empty($unit['temp_stopevent_id']) AND (array_key_exists($row['event_id'], $manufacture_events['driveevent_keys']))) {
                            // if stopevent_id is set and event is Drive event, then we need to calculate previous stop duration to this drive event
                            $res =  $this->extendedStopAlertCheck($alert, $unit, $index);
                            if ( ! empty($unit['temp_stopevent_id'])) {
                                $unit['temp_stopevent_id'] = 0;
                            }
                            $stop = false;
                        }
                        break;

                    case 3:     //'landmark':

                        // if landmark alert
                        $res =  $this->landmarkAlertCheck($alert, $unit, $index);

                        if ( ! empty($row['landmark'])) {
                            // save landmark id if any
                            $unit['temp_event_landmark_id'] = $row['landmark']['territory_id'];
                        } else {
                            // reset landmark id
                            $unit['temp_event_landmark_id'] = 0;
                        }
                        break;

                    case 4:     //'low-voltage':

                        //event_id = 26 (Low Battery)
                        if (isset($row['event_id']) AND in_array($row['event_id'], array(26))) {

                            $res['triggered']   = true;
                            $res['event'][]     = "Low Voltage Alert : {$row['eventname']}";
                            $res['trigger_id']  = $row['id'];
                        }
                        break;

                    case 5:     //'moving':

                        // event_id = 3, 4, 9, 11, 40, 41, 42, 46, 47, 49 (motion events)
                        //event_id = 2, 13, 15, 18 (ignition/power off)
                        $inside_datetime = false;

                        if ( empty($unit['temp_movingevent_id'])) {
                            if (isset($row['event_id']) AND array_key_exists($row['event_id'], $manufacture_events['driveevent_keys'])) {

                                // check if alert has a date/time range trigger
                                $inside_datetime = $this->inDateTimeRangeCheck($alert, $row);
                                if ($inside_datetime) {
                                    $res['triggered']   = true;
                                    $res['event'][]     = "Moving Alert : {$row['eventname']}";
                                    $res['trigger_id']  = $row['id'];
                                }
                            }
                        }
                        break;

                    case 6:     //'non-reporting':

                        // possible alert time frames (3, 7, 30 Days)
                        if (isset($row['id']) AND ! empty($row['id']) AND $unit['nonreportingstatus'] == 1) {
                            $unit['temp_nonreportingstatus'] = 0;
                        }
                        break;

                    case 7:     //'over-speed':

                        //is event a drive event
                        if (isset($row['event_id']) AND array_key_exists($row['event_id'], $manufacture_events['driveevent_keys'])) {
                            // is unit temp_speedevent_id already triggered
                            if (empty($unit['temp_speedevent_id'])) {

                                //event_id = 46 (speed alert event)
                                $res =  $this->speedAlertCheck($alert, $unit, $index);
                            }

                        } else if (isset($row['event_id']) AND array_key_exists($row['event_id'], $manufacture_events['stopevent_keys'])) {
                            //reset stored speedevent_id so that next overspeed can be triggered
                            $unit['temp_speedevent_id'] = 0;
                        }
                        break;

                    case 8:     //'tow':

                        //event_id = 48, 49, 50 (tow events)
                        if (isset($row['event_id']) AND in_array($row['event_id'], array(48, 49, 50))) {
                            $res['triggered']   = true;
                            $res['event'][]     = "Tow Alert : {$row['eventname']}";
                            $res['trigger_id']  = $row['id'];
                        }
                        break;

                    default:
                        break;
                }

                // update 'temp_movingevent_id'
                if (isset($row['event_id']) AND array_key_exists($row['event_id'], $manufacture_events['driveevent_keys'])) {
                    $unit['temp_movingevent_id'] = $row['id'];
                }
                // if ignition off event, then reset movingevent_id
                else if (isset($row['event_id']) AND array_key_exists($row['event_id'], $manufacture_events['ignition_off_keys'])) {
                    $unit['temp_movingevent_id'] = 0;
                }

                // if an alert is triggered, set alert into emailing queue
                if ($res['triggered']) {
                    $unit['update'] = true;
                    $alertsend_create_date = Date::locale_to_utc(date('Y-m-d H:i:s'), SERVER_TIMEZONE);
                    foreach($res['event'] as $index => $eventmessage) {
                         $this->setAlertSend($alert, $unit, $res['trigger_id'], $eventmessage, $alertsend_create_date);
                    }
                }
            }
        } else {

            // if unit has no events for the pulled event by the cron
            $res = array('triggered' => false, 'event' => '', 'trigger_id' => 0);

            if (! empty($unit) AND $alert['alerttype_id'] == 6) {
                // with no event and is a non-reporting alert, process for non reporting
                $res =  $this->nonReportingAlertCheck($alert, $unit);
            }

            // if an alert is triggered, set alert into emailing queue
            if ($res['triggered']) {
                $unit['update'] = true;
                foreach($res['event'] as $index => $eventmessage) {
                    $alertsend_create_date = Date::locale_to_utc(date('Y-m-d H:i:s'), SERVER_TIMEZONE);
                    $this->setAlertSend($alert, $unit, $res['trigger_id'], $eventmessage, $alertsend_create_date);
                }
            }
        }
    }

    /**
     * Send the alert for provided alert info
     *
     * @param array $alert
     * @param array $unit
     * @param array $email
     * @param array $event
     * @param array $contact_email
     * @param array $contact_cell
     *
     */
    public function sendAlertEmails($alert, $unit, $email, $event, $contact_email, $contact_cell)
    {
        // Create the mail transport configuration

        $transport = \Swift_SmtpTransport::newInstance(EMAIL_HOST, EMAIL_PORT, EMAIL_SECURITY);
        $transport->setUsername(EMAIL_USERNAME);
        $transport->setPassword(EMAIL_PASSWORD);

        //$transport = \Swift_SmtpTransport::newInstance('alerts@gpsdevicealerts.com', 465, 'ssl');
        //$transport->setUsername('sfjelstad');
        //$transport->setPassword('ceille');

        // send alert to emails
        if (! empty($contact_email))
        {
            //place holder only, need actual company logo and name
            $company_logo = 'http://www.notaboutthenumbers.com/wp-content/uploads/2012/01/iStock_000011787394XSmall-300x256.jpg';
            $company_name = 'Position Plus GPS';

            $body = '
            <html>
                <body>
                <table>
                    <tr>
                        <td><img width="175" height="150" src="'.$company_logo.'">
                        </td>
                        <td><b><font color="red">'.$company_name.'</font></b></td>
                    </tr>
                </table>';
            $body.= '
                <table>
                    <tr>
                        <td>Alert: </td>
                        <td><font color="red">'.$email['alerttext'].'</font></td>
                    </tr>
                    <tr>
                        <td>Device: </td>
                        <td><font color="green">'.$unit['unitname']." (".$unit['serialnumber'].')</font></td>
                    </tr>
                    <tr>
                        <td>Time: </td>
                        <td><font color="blue">'.Date::utc_to_locale($event['unittime'], $unit['unit_timezone'], 'm/d/Y g:i A').'</font></td>
                    </tr>
                    <tr>
                        <td>';

            $body.= ( ! empty($event['formatted_address'])) ? 'Address: ' : 'Lat/Long: ';
            $body.= '</td>
                    <td>';

            if ( ! empty($event['landmark'])) {
                $body.= '( ' . $event['landmark']['territoryname'] . ' ) ';
            }

            if ( ! empty($event['formatted_address'])) {
                $body.= $event['formatted_address'];
            } else {
                $body.= $event['LAT'] . ', ' . $event['LONG'];
            }

            $body.= '
                        </td>
                    </tr>
                    <tr>
                        <td>Speed: </td>
                        <td>'.$event['speed']." mph</td>
                    </tr>
                </table>
                </body>
            </html>";

            // plain text body content
            $body_text = $company_logo."  ".$company_name."\r\n\n";
            $body_text.= 'Alert: '.$email['alerttext']."\r\n";
            $body_text.= 'Device: '.$unit['unitname']." (".$unit['serialnumber'].")\r\n";
            $body_text.= 'Time: '.Date::utc_to_locale($event['unittime'], $unit['unit_timezone'], 'm/d/Y g:i A')."\r\n";
            $body_text.= ( ! empty($event['formatted_address'])) ? 'Address: ' : 'Lat/Long: ';

            if ( ! empty($event['landmark'])) {
                $body_text.= '( ' . $event['landmark']['territoryname'] . ' ) ';
            }

            if ( ! empty($event['formatted_address'])) {
                $body_text.= $event['formatted_address']."\r\n";
            } else {
                $body_text.= $event['LAT'] . ', ' . $event['LONG']."\r\n";
            }

            $body_text.= 'Speed: '.$event['speed']." mph\r\n";

            // Create the message
            $message = \Swift_Message::newInstance();
            $message->setSubject("Alert - {$alert[$email['alert_id']]['alerttypename']} - {$unit['unitname']}");
            $message->setBody($body, 'text/html');
            $message->addPart($body_text, 'text/plain');    // Add alternative plain text body
            $message->setFrom(array('alerts@'.EMAIL_FROM_DOMAIN => EMAIL_FROM_DOMAIN)); // NTD: determine if dealers will have email domains or not

            $message->setTo($contact_email);
            //$message->setTo(array('cyang@gpstrackit.net'));
$message->addBcc('tbagley@positionplusgps.com' , 'AlertCronLogic:sendAlertEmails'); // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< BCC: Todd Bagley

            // Create Attachment and add it to the message
            //$message->attach(Swift_Attachment::fromPath($fullpath));

            // Send the email
            $mailer = \Swift_Mailer::newInstance($transport);
            $mailer->send($message);

        }

        // send alert to cellphones
        if (! empty($contact_cell))
        {
            $body = "Alert - {$alert[$email['alert_id']]['alerttypename']} - {$unit['unitname']}";
            $body.= ' - '.Date::utc_to_locale($event['unittime'], $unit['unit_timezone'], 'm/d/Y g:i A').' - ';
            $body.= ( ! empty($event['formatted_address'])) ? 'Address: ' : 'Lat/Long: ';

            if ( ! empty($event['landmark']))
            {
                $body.= '(' . $event['landmark']['territoryname'] . ') ';
            }

            if ( ! empty($event['formatted_address']))
            {
                $body.= $event['formatted_address'] . ' ';
            }
            else
            {
                $body.= $event['LAT'] . ', ' . $event['LONG'] . ' ';
            }

            $body.= '- Speed: '.$event['speed']." mph";

            // Create the message
            $message = \Swift_Message::newInstance();
            $message->setSubject("");
            $message->setBody($body);
            $message->setFrom(array('alerts@'.EMAIL_FROM_DOMAIN => EMAIL_FROM_DOMAIN)); // NTD: determine if dealers will have email domains or not

            $message->setTo($contact_cell);
            //$message->setTo(array('xxxxxxxxxx@vtext.com'));

            // Send the email
            $mailer = \Swift_Mailer::newInstance($transport);
            $mailer->send($message);

        }
    }

    private function cachedLandmarkName($id)
    {
        if (!array_key_exists($id, $this->landmarkCache))
        {
            $territories = $this->territory_logic->getTerritoryByIds($id);
            if (!empty($territories))
            {
                $this->landmarkCache[$id] = $territories[0];
            }
        }
        if (!empty($this->landmarkCache[$id]))
        {
            return $this->landmarkCache[$id]['territoryname'];
        }
        else
        {
            return '(unfound)';
        }
    }

}
