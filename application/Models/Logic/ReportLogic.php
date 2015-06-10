<?php

namespace Models\Logic;

use Models\Logic\BaseLogic;
use Models\Data\ReportData;
use Models\Data\AlertData;
use Models\Data\VehicleData;
use Models\Logic\VehicleLogic;
use Models\Logic\AddressLogic;
use Models\Logic\TerritoryLogic;
use Models\Logic\AlertLogic;
use Models\Logic\UserLogic;
use Models\Logic\ContactLogic;
use GTC\Component\Utils\Date;
use GTC\Component\Utils\Arrayhelper;
use GTC\Component\Utils\Measurement;
use GTC\Component\Form\Validation;

class ReportLogic extends BaseLogic
{
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();

        $this->base_logic       = new BaseLogic;
        $this->report_data      = new ReportData;
        $this->alert_data       = new AlertData;
        $this->vehicle_data     = new VehicleData;
        $this->vehicle_logic    = new VehicleLogic;
        $this->address_logic    = new AddressLogic;
        $this->alert_logic      = new AlertLogic;
        $this->territory_logic  = new TerritoryLogic;
        $this->user_logic       = new UserLogic;
        $this->contact_logic    = new ContactLogic;
        $this->validator        = new Validation;

    }

    /**
     * Get the report types
     *
     * @return array | bool
     */
    public function getReportTypes($reporttype_id_as_indices = false)
    {
        $reporttypes = $this->report_data->getReportTypes();
        $new_reporttypes = array();

        if ($reporttypes !== false) {
            if (! empty($reporttypes)) {
                foreach ($reporttypes as $index => $rt) {
                    switch ($rt['reporttypename']) {
                        case 'Alert':
                            $reporttypes[$index]['url'] = $reporttypes[$index]['dom_id'] = 'alert';
                            break;
                        case 'Boundary':
                            $reporttypes[$index]['url'] = $reporttypes[$index]['dom_id'] = 'boundary';
                            break;
                        case 'Detailed Event':
                            $reporttypes[$index]['url'] = $reporttypes[$index]['dom_id'] = 'detail';
                            break;
                        case 'Frequent Stops':
                            $reporttypes[$index]['url'] = 'frequentStops';
                            $reporttypes[$index]['dom_id'] = 'frequent-stops';
                            break;
                        case 'Landmark':
                            $reporttypes[$index]['url'] = $reporttypes[$index]['dom_id'] = 'landmark';
                            break;
                        case 'Last Ten Stops':
                            $reporttypes[$index]['url'] = 'lastTenStops';
                            $reporttypes[$index]['dom_id'] = 'last-ten-stops';
                            break;
                        case 'Mileage Summary':
                            $reporttypes[$index]['url'] = 'mileageSummary';
                            $reporttypes[$index]['dom_id'] = 'mileage-summary';
                            break;
                        case 'Non Reporting':
                            $reporttypes[$index]['url'] = 'nonReporting';
                            $reporttypes[$index]['dom_id'] = 'non-reporting';
                            break;
                        case 'Speed Summary':
                            $reporttypes[$index]['url'] = 'speedSummary';
                            $reporttypes[$index]['dom_id'] = 'speed-summary';
                            break;
                        case 'Starter Disable Summary':
                            $reporttypes[$index]['url'] = 'starterDisableSummary';
                            $reporttypes[$index]['dom_id'] = 'starter-disable-summary';
                            break;
                        case 'Stationary':
                            $reporttypes[$index]['url'] = $reporttypes[$index]['dom_id'] = 'stationary';
                            break;
                        case 'Stop':
                            $reporttypes[$index]['url'] = $reporttypes[$index]['dom_id'] = 'stop';
                            break;
                        case 'User Command':
                            $reporttypes[$index]['url'] = 'userCommand';
                            $reporttypes[$index]['dom_id'] = 'user-command';
                            break;
                        case 'Vehicle Information':
                            $reporttypes[$index]['url'] = 'vehicleInformation';
                            $reporttypes[$index]['dom_id'] = 'vehicle-information';
                            break;
                        case 'Address Verification':
                            $reporttypes[$index]['url'] = 'verificationOfReference';
                            $reporttypes[$index]['dom_id'] = 'reference';
                            break;
                    }

                    if ($reporttype_id_as_indices === true) {
                        if (! isset($new_reporttypes[$rt['reporttype_id']])) {
                            $new_reporttypes[$rt['reporttype_id']] = $reporttypes[$index];
                        }
                    }
                }
            }
            return (($reporttype_id_as_indices === true) ? $new_reporttypes : $reporttypes);
        }
        return false;
    }

    /**
     * Get the all alert types for report section
     *
     * @return array | bool
     */
    public function getReportAlertTypes()
    {
        $ret = array();
        $alerttypes = $this->alert_logic->getAlertTypes();
        // if ($alerttypes !== false AND ! empty($alerttypes)) {
        //     foreach( $alerttypes as $key => $alert) {
        //         $ret[$alert['alerttype_id']] = $alert['alerttypename'];
        //     }
        // }
        // return $ret;
        return $alerttypes;
    }

    /*
     */
    public function mapAllAddresses()
    {
        return $this->base_logic->wizardMapAllLink();
    }

    /*
     * helper function process post parameters
     *
     * POST params: post
     */
    public function processReportPostParameters($args)
    {
        $ret = array();
        $params = array();
        $error = '';

        if (! empty($args['reporttype'])) {
            $params['reporttype'] = $args['reporttype'];
        } else {
            $error = 'Invalid Report Type';
        }

        if (! empty($args['reporttype_id'])) {
            $params['reporttype_id'] = $args['reporttype_id'];
        } else {
            $error = 'Invalid Report Type Id';
        }

        if (! empty($args['reporttype_name'])) {
            $params['reporttypename'] = $args['reporttype_name'];
        } else {
            $error = 'Invalid Report Type';
        }

        if (! empty($args['start_date'])) {
            $params['starttime'] = $args['start_date'];
        }

        if (! empty($args['end_date'])) {
            $params['endtime'] = $args['end_date'];
        }

        if (! empty($args['unit_mode'])) {
            $params['unit_mode'] = $args['unit_mode'];
            if ($params['unit_mode'] !== 'all') {
                if (! empty($args['unit_id']) OR ! empty($args['unitgroup_id'])) {
                    if (! empty($args['unit_id'])) {
                        $params['unit_id'] = $args['unit_id'];
                    } else if (! empty($args['unitgroup_id'])) {
                        $params['unitgroup_id'] = $args['unitgroup_id'];
                    } else {
                        $error = 'Please select a Vehicle or Vehicle Group';
                    }
                } else {
                    $error = 'Please select a Vehicle or Vehicle Group';
                }
            }
        }

        if (! empty($args['territory_mode'])) {
            $params['territory_mode'] = $args['territory_mode'];
            if ($params['territory_mode'] !== 'all') {
                if (! empty($args['territory_id']) OR ! empty($args['territorygroup_id'])) {
                    if (! empty($args['territory_id'])) {
                        $params['territory_id'] = $args['territory_id'];
                    } else if (! empty($args['territorygroup_id'])) {
                        $params['territorygroup_id'] = $args['territorygroup_id'];
                    } else {
                        $error = 'Please select a Landmark/Boundary or Landmark/Boundary Group';
                    }
                } else {
                    $error = 'Please select a Landmark/Boundary or Landmark/Boundary Group';
                }
            }
        }

        if (! empty($args['filter_speed'])) {
            $params['mph'] = $args['filter_speed'];
        }

        if (! empty($args['filter_minutes'])) {
            $params['minute'] = $args['filter_minutes'];
        }

        if (! empty($args['filter_miles'])) {
            $params['mile'] = $args['filter_miles'];
        }

        if (! empty($args['filter_alert_type'])) {
            $params['alerttype_id'] = $args['filter_alert_type'];
        }

        if (! empty($args['filter_verified'])) {
            $params['verification'] = ucfirst($args['filter_verified']);
        }

        // day threshold value
        if (! empty($args['filter_days'])) {
            $params['day'] = $args['filter_days'];
        }

        // date range for pull data from
        if (! empty($args['filter_daterange'])) {
            $params['range'] = $this->processDateRangeString($args['filter_daterange']);
        }

        // contact
        if (! empty($args['contact_mode'])) {
            $params['contact_mode'] = $args['contact_mode'];
        }

        if (! empty($args['contact_id'])) {
            $params['contact_id'] = $args['contact_id'];
        }

        if (! empty($args['contactgroup_id'])) {
            $params['contactgroup_id'] = $args['contactgroup_id'];
        }

        // recurrence section
        if (! empty($args['schedule_recurrence'])) {
            $params['schedule'] = $args['schedule_recurrence'];
        }

        if (! empty($args['schedule_monthly'])) {
            $params['monthday'] = $args['schedule_monthly'];
        }

        if (! empty($args['schedule_day'])) {
            $params['scheduleday'] = $args['schedule_day'];
        }

        if (isset($args['schedule_time'])) {
            $params['sendhour'] = ($args['schedule_time'] != 0) ? $args['schedule_time'] : 0;
        }

        if (! empty($args['schedule_format'])) {
            $params['format'] = $args['schedule_format'];
        }

        if (! empty($args['report_name'])) {
            $this->validator->validate('report_name', $args['report_name']);
            if ($this->validator->hasError()) {
                $validation_errors = $this->validator->getErrorMessage();
                if (! empty($validation_errors) AND is_array($validation_errors)) {
                    $error = 'Report Name - ' . implode(',', $validation_errors);
                }
            } else {
                $params['reporthistoryname'] = $params['schedulereportname'] = $params['report_name'] = $args['report_name'];
            }
        }

        // user mode
        if (isset($args['user_id'])) {
            if (! empty($args['user_id'])) {
                $params['filter_user_id'] = $args['user_id'];
                $params['selection'] = 'user';
            } else {
                $params['selection'] = 'all';
            }
        }

        if (! empty($args['user_mode'])) {
            $params['selection'] = $args['user_mode'];
        }

        $ret['params'] = $params;
        $ret['error'] = $error;

        return $ret;
    }

    /**
     * Run report
     *
     * @param array params
     * @return bool|string
     */
    public function runReport($params)
    {
        if (! empty($params)) {
            $validation = $this->validateReportParameters($params);
            if ($validation !== false) {

                // get the report method name
                $method = $params['reporttype'];

                $report_data = $this->$method($params);

                // default report output to html
                $output_type = 'html';
                if (! empty($params['report_output'])) {
                    $output_type = $params['report_output'];
                }

                if ($output_type == 'csv') {                        // if outputting to CSV, build the csv array
                    $report_output = $this->formatReportCsv($report_data);
                } else {                                            // else if output is HTML or PDF, simply return the array
                    $report_output = $report_data;
                }

                if (isset($params['report_name'])) {
                    $params['reporthistoryname'] = $params['report_name'];
                }

                if (isset($params['starttime']) AND isset($params['endtime'])) {
                    // convert start date and end date to user's local time and then to UTC
                    $params['starttime'] = Date::locale_to_utc(date('Y-m-d H:i:s', strtotime($params['starttime'])), $params['user_timezone']);
                    $params['endtime'] = Date::locale_to_utc(date('Y-m-d H:i:s', strtotime($params['endtime'])), $params['user_timezone']);
                }

                if (isset($report_data['report']['time_generated'])) {
                    $params['createdate'] = Date::locale_to_utc(date('Y-m-d H:i:s', strtotime($report_data['report']['time_generated'])), $params['user_timezone']);
                }

                // get the proper parameters to be inserted into the reporthistory table
                $reporthistory_params = $this->filterForReportTableParams('reporthistory', $params);

                // save report history
                $reporthistory_id = $this->saveReportHistory($reporthistory_params);

                if ($reporthistory_id !== false) {
                    // return the report history id
                    $report_output['reporthistory_id'] = $reporthistory_id;

                    // save reporthistory unit
                    if (! empty($params['unit_mode'])) {
                        $report_unit = array();

                        $report_unit['reporthistory_id'] = $reporthistory_id;

                        if ($params['unit_mode'] == 'all') {
                            $report_unit['selection'] = 'all';
                        } else {
                            if (! empty($params['unit_id'])) {
                                $report_unit['selection'] = 'unit';
                                $report_unit['unit_id'] = $params['unit_id'];
                            } else if (! empty($params['unitgroup_id'])) {
                                $report_unit['selection'] = 'group';
                                $report_unit['unitgroup_id'] = $params['unitgroup_id'];
                            }
                        }
                        //print_rb($report_unit);
                        $this->saveReportHistoryUnit($report_unit);
                    }

                    // save reporthistory territory
                    if (! empty($params['territory_mode'])) {
                        $report_territory = array();

                        $report_territory['reporthistory_id'] = $reporthistory_id;

                        if ($params['territory_mode'] == 'all') {
                            $report_territory['selection'] = 'all';
                        } else {
                            if (! empty($params['territory_id'])) {
                                $report_territory['selection'] = 'territory';
                                $report_territory['territory_id'] = $params['territory_id'];
                            } else if (! empty($params['territorygroup_id'])) {
                                $report_territory['selection'] = 'group';
                                $report_territory['territorygroup_id'] = $params['territorygroup_id'];
                            }
                        }

                        $this->saveReportHistoryTerritory($report_territory);
                    }

                    // save reporthistory user
                    if (! empty($params['selection'])) {
                        $report_user = array();

                        $report_user['reporthistory_id'] = $reporthistory_id;

                        if (! empty($params['filter_user_id'])) {
                            $report_user['selection'] = 'user';
                            $report_user['user_id'] = $params['filter_user_id'];
                        } else {
                            $report_user['selection'] = 'all';
                        }

                        $this->saveReportHistoryUser($report_user);
                    }

                } else {
                    $this->setErrorMessage('Failed to log the report');
                }

                return $report_output;
            }
        } else {
            $this->setErrorMessage('Missing parameters for generating report');
        }
        return false;
    }

    /**
     * Save Schedule report
     *
     * @param array params
     * @return bool|string
     */
    public function saveScheduleReport($params)
    {
        // account and user
        $this->validator->validate('record_id', $params['account_id']);
        $this->validator->validate('record_id', $params['user_id']);
        $this->validator->validate('record_id', $params['reporttype_id']);

        // validate schedule report name
        $this->validator->validate('report_name', $params['schedulereportname']);

        if (empty($params['schedule'])) {
            $this->setErrorMessage('Invalid Schedule Recurrence');
        }
        if (isset($params['scheduleday']) AND empty($params['scheduleday'])) {
            $this->setErrorMessage('Invalid Report Day');
        }
        if (isset($params['monthday']) AND empty($params['monthday'])) {
            $this->setErrorMessage('Invalid Month Day');
        }
        if (isset($params['sendhour']) AND (! is_numeric($params['sendhour']) OR $params['sendhour'] < 0)) {
            $this->setErrorMessage('Invalid Send Hour');
        }
        if (empty($params['format'])) {
            $this->setErrorMessage('Invalid Report Format');
        }
        if (isset($params['alerttype_id'])) {
            $this->validator->validate('record_id', $params['alerttype_id']);
        }

        // schedulereport_contact
        if (isset($params['contact_id'])) {
            $this->validator->validate('record_id', $params['contact_id']);
        }
        if (isset($params['contactgroup_id'])) {
            $this->validator->validate('record_id', $params['contactgroup_id']);
        }

        // schedulereport_territory
        if (isset($params['territory_id'])) {
            if (empty($params['territory_id']) OR ! is_numeric($params['territory_id']) OR $params['territory_id'] <= 0) {
                $this->setErrorMessage('Invalid Landmark');
            } else {
                $params['territory_mode'] = 'territory';
            }
        }

        if (isset($params['territoryrgroup_id'])) {
            if (empty($params['territorygroup_id']) OR ! is_numeric($params['territorygroup_id']) OR $params['territorygroup_id'] <= 0) {
                $this->setErrorMessage('Invalid Landmark Group');
            } else {
                $params['territory_mode'] = 'group';
            }
        }

        // schedulereport_unit
        if (isset($params['unit_id'])) {
            if (empty($params['unit_id']) OR ! is_numeric($params['unit_id']) OR $params['unit_id'] <= 0) {
                $this->setErrorMessage('Invalid Vehicle Info');
            } else {
                $params['unit_mode'] = 'unit';
            }
        }

        if (isset($params['unitgroup_id'])) {
            if (empty($params['unitgroup_id']) OR ! is_numeric($params['unitgroup_id']) OR $params['unitgroup_id'] <= 0) {
                $this->setErrorMessage('Invalid Vehicle Group Info');
            } else {
                $params['unit_mode'] = 'group';
            }
        }

        if (isset($params['unit_mode']) AND empty($params['unit_mode'])) {
            $this->setErrorMessage('Invalid Vehicle Mode');
        }

        // schedulereport_user
        if (isset($params['filter_user_id'])) {
            if (! is_numeric($params['filter_user_id']) OR $params['filter_user_id'] < 0) {
                $this->setErrorMessage('Invalid User Info');
            } else if ($params['filter_user_id'] == 0) {
                $params['selection'] = 'all';
            } else {
                $params['selection'] = 'user';
            }
        }

        if (isset($params['selection']) AND empty($params['selection'])) {
            $this->setErrorMessage('Invalid User Mode');
        }

        // filter parameters
        if (isset($params['mph']) AND (! is_numeric($params['mph']) OR $params['mph'] <= 0)) {
            $this->setErrorMessage('Invalid filter speed');
        }
        if (isset($params['minute']) AND (! is_numeric($params['minute']) OR $params['minute'] <= 0)) {
            $this->setErrorMessage('Invalid filter minutes');
        }
        if (isset($params['mile']) AND (! is_numeric($params['mile']) OR $params['mile'] <= 0)) {
            $this->setErrorMessage('Invalid filter miles');
        }

        if (isset($params['range']) AND empty($params['range'])) {
            $this->setErrorMessage('Invalid Date Range Filter');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {

            //does schedule report already exist
            $newReportName = $params['schedulereportname'] ;
            while($exist_report === true OR is_array($exist_report) OR !empty($exist_report)) {
                $exist_report = $this->report_data->getScheduleReportByName($params['account_id'], $newReportName);
                $params['schedulereportname'] = $newReportName ;
                $newReportCounter++;
                $newReportName = $params['reportTypeName'] . ' ' . $newReportCounter;
            }

            if ($exist_report === false OR ! is_array($exist_report) OR empty($exist_report)) {

                $params['nextruntime']  = $this->calculateNextRunTime($params);
                $schedulereport_params  = $this->filterForReportTableParams('schedulereport', $params);

                $schedulereport_id      = '';

                if (! empty($schedulereport_params)) {
                    // save schedule report
                    $schedulereport_id = $this->report_data->saveScheduleReport($schedulereport_params);
                    if ($schedulereport_id != false AND ! empty($schedulereport_id)) {
                        // save contact
                        $contact_params = $this->filterForReportTableParams('contact', $params);

                        if (! empty($contact_params)) {
                            $contact_params['schedulereport_id'] = $schedulereport_id;
                            $reportcontact_id = $this->report_data->saveScheduleReportContact($contact_params);
                        }

                        // save unit
                        $unit_params = $this->filterForReportTableParams('unit', $params);
                        if (! empty($unit_params)) {
                            $unit_params['schedulereport_id'] = $schedulereport_id;
                            $reportunit_id = $this->report_data->saveScheduleReportUnit($unit_params);
                        }

                        // save territory
                        $territory_params = $this->filterForReportTableParams('territory', $params);
                        if (! empty($territory_params)) {
                            $territory_params['schedulereport_id'] = $schedulereport_id;
                            $reportterritory_id = $this->report_data->saveScheduleReportTerritory($territory_params);
                        }

                        // save user
                        $user_params = $this->filterForReportTableParams('user', $params);
                        if (! empty($user_params)) {
                            $user_params['schedulereport_id'] = $schedulereport_id;
                            $reportuser_id = $this->report_data->saveScheduleReportUser($user_params);
                        }

                        return $schedulereport_id;
                    }
                }

                $this->setErrorMessage('Missing Scheduling Report Criterias.');
                return false;
            } else {

                $this->setErrorMessage('This report name already exist. Rename you report.');
                return false;
            }
        } else {
             $this->setErrorMessage('Missing parameters for generating report');
             return false;
        }
    }


    /**
     * Helper function to calculate the day range
     *
     * @param array params
     * @return date string
     */
    public function processDateRangeString($daterange)
    {
        $days_range = '90 days';

        if (isset($daterange) AND ! empty($daterange)) {
            switch ($daterange) {

                case 'Today':
                    $days_range = '0 day';
                    break;
                case 'Yesterday':
                    $days_range = '1 day';
                    break;
                case 'Last 3 Days':
                    $days_range = '3 days';
                    break;
                case 'Last 7 Days':
                    $days_range = '7 days';
                    break;
                case 'Last 14 Days':
                    $days_range = '14 days';
                    break;
                case 'Last 30 Days':
                    $days_range = '30 days';
                    break;
                case 'Last 60 Days':
                    $days_range = '60 days';
                    break;
                case 'Last 90 Days':
                    $days_range = '90 days';
                    break;
                case 'This Month':
                    $days_range = '0 month';
                    break;
                case 'Last Month':
                    $days_range = '1 month';
                    break;
                default:
                    break;
            }
        }

        return $days_range;
    }

    /**
     * Helper function to calculate the next run time by schedule/recurrence and params
     *
     * @param array params
     * @return date string
     */
    public function calculateNextRunTime($params)
    {
        switch ($params['schedule']) {

            case 'Daily':
                $nextrundate = date('Y-m-d '.substr('0'.$params['sendhour'], -2).':00:00', strtotime("next day"));
            break;

            case 'Weekly':

                if ($params['scheduleday'] == 'Weekdays') {
                   // get day of the week
                   $day = date('l');
                   if (! in_array($day, array('Friday','Saturday','Sunday'))) {
                       // get the next week day
                       $nextrundate = date('Y-m-d '.substr('0'.$params['sendhour'], -2).':00:00', strtotime("next day"));
                   } else {
                       // next run day is Monday
                       $nextrundate = date('Y-m-d '.substr('0'.$params['sendhour'], -2).':00:00', strtotime("next Monday"));
                   }
               } else if ($params['scheduleday'] == 'Weekends') {
                   $day = date('l');
                   if ($day == 'Saturday') {
                       // get the next week day
                       $nextrundate = date('Y-m-d '.substr('0'.$params['sendhour'], -2).':00:00', strtotime("next day"));
                   } else {
                       // next run day is Monday
                       $nextrundate = date('Y-m-d '.substr('0'.$params['sendhour'], -2).':00:00', strtotime("next Saturday"));
                   }
               } else if ($params['scheduleday'] != 'Everyday') {
                   $nextrundate = date('Y-m-d '.substr('0'.$params['sendhour'], -2).':00:00', strtotime("next ".$params['scheduleday']));
               } else {
                   $nextrundate = date('Y-m-d '.substr('0'.$params['sendhour'], -2).':00:00', strtotime("next day"));
               }
            break;

            case 'Monthly':
                $nextrundate = date('Y-m-'.substr('0'.$params['monthday'], -2).' '.substr('0'.$params['sendhour'], -2).':00:00', strtotime('next month'));
            break;

            case 'Quarterly':
                // get current month
                $current_month = date('m');

               // calculate next quarter
               if ($current_month < 4) {
                   $month = 4;
                   $year = date('Y');
               } else if ($current_month < 7) {
                   $month = 7;
                   $year = date('Y');
               } else if ($current_month < 10) {
                   $month = 10;
                   $year = date('Y');
               } else {
                   $month = 1;
                   $year = date('Y')+1;
               }

               $nextrundate = date($year.'-'.substr('0'.$month, -2).'-01 '.substr('0'.$params['sendhour'], -2).':00:00');
            break;

            default:
                $nextrundate = date('Y-m-d '.substr('0'.$params['sendhour'], -2).':00:00', strtotime("+1 day"));
            break;

       }

       $nextruntime = Date::locale_to_utc($nextrundate, $params['user_timezone']);

       return $nextruntime;
    }

    /**
     * Helper function to grab needed report parameters
     *
     * @param array params
     * @return array
     */
    public function filterForReportTableParams($table, $params)
    {
        $ret = array();

        switch ($table) {

            case 'schedulereport':

                $schedulereport = array('account_id', 'user_id', 'reporttype_id', 'alerttype_id', 'schedulereportname', 'minute', 'day', 'mile', 'mph', 'verification', 'schedule', 'scheduleday', 'monthday', 'range', 'sendhour', 'format', 'lastruntime', 'nextruntime', 'active');

                foreach($schedulereport as $key => $field) {
                    if (array_key_exists($field,$params)) {
                        $ret[$field] = $params[$field];
                    }
                }

            break;

            case 'reporthistory':

                $reporthistory = array('account_id', 'user_id', 'reporttype_id', 'alerttype_id', 'reporthistoryname', 'minute', 'day', 'mile', 'mph', 'verification', 'starttime', 'endtime', 'method', 'createdate');

                foreach($reporthistory as $key => $field) {
                    if (array_key_exists($field,$params)) {
                        $ret[$field] = $params[$field];
                    }
                }

            break;

            case 'territory':

                $territory = array('territory_id', 'territorygroup_id', 'territory_mode');

                foreach($territory as $key => $field) {
                    if (array_key_exists($field,$params)) {
                        if ($field == 'territory_mode') {
                            $ret['selection'] = $params[$field];
                        } else {
                            $ret[$field] = $params[$field];
                        }
                    }
                }

            break;

            case 'contact':

                $contact = array('contact_id', 'contactgroup_id');

                foreach($contact as $key => $field) {
                    if (array_key_exists($field,$params)) {
                        $ret[$field] = $params[$field];
                    }
                }

            break;

            case 'unit':

                $unit = array('unit_id', 'unitgroup_id', 'unit_mode');

                foreach($unit as $key => $field) {
                    if (array_key_exists($field,$params)) {
                        if ($field == 'unit_mode') {
                            $ret['selection'] = $params[$field];
                        } else {
                            $ret[$field] = $params[$field];
                        }
                    }
                }

            break;

            case 'user':

                $user = array('filter_user_id', 'selection');

                foreach($user as $key => $field) {
                    if (array_key_exists($field,$params)) {
                        if ($field == 'filter_user_id') {
                            $ret['user_id'] = $params[$field];
                        } else {
                            $ret[$field] = $params[$field];
                        }
                    }
                }

            break;

            default:

            $ret = $params;
            break;

        }

        return $ret;

    }

    /**
     * Validate report parameters
     *
     * @param array params
     * @return bool|string
     */
    public function validateReportParameters(&$params)
    {
        if (! empty($params)) {
            $this->validator->validate('record_id', $params['account_id']);
            $this->validator->validate('record_id', $params['user_id']);
            $this->validator->validate('report_name', $params['report_name']);

            if (isset($params['unit_mode'])) {
                if (! empty($params['unit_mode']) AND in_array($params['unit_mode'], array('all', 'single', 'group'))) {
                    if ($params['unit_mode'] !== 'all') {
                        if (isset($params['unit_id'])) {
                            if (! is_numeric($params['unit_id']) OR $params['unit_id'] <= 0 AND $params['unit_mode'] == 'single') {
                                $this->setErrorMessage('Invalid Vehicle Id');
                            }
                        } else if (isset($params['unitgroup_id'])) {
                            if (! is_numeric($params['unitgroup_id']) OR $params['unitgroup_id'] <= 0 AND $params['unit_mode'] == 'group') {
                                $this->setErrorMessage('Invalid Vehicle Group Id');
                            }
                        } else {
                            $this->setErrorMessage('No Vehicle or Vehicle Group was selected for report');
                        }
                    }
                } else {
                    $this->setErrorMessage('Invalid Vehicle Mode');
                }
            }

            if (isset($params['territory_mode'])) {
                if (! empty($params['territory_mode']) AND in_array($params['territory_mode'], array('all', 'single', 'group'))) {
                    if ($params['territory_mode'] !== 'all') {
                        if (isset($params['territory_id'])) {
                            if (! is_numeric($params['territory_id']) OR $params['territory_id'] <= 0 AND $params['territory_mode'] == 'single') {
                                $this->setErrorMessage('Invalid Territory Id');
                            }
                        } else if (isset($params['territorygroup_id'])) {
                            if (! is_numeric($params['territorygroup_id']) OR $params['territorygroup_id'] <= 0 AND $params['territory_mode'] == 'group') {
                                $this->setErrorMessage('Invalid Territory Group Id');
                            }
                        } else {
                            $this->setErrorMessage('No Territory or Territory Group was selected for report');
                        }
                    }
                } else {
                    $this->setErrorMessage('Invalid Territory Mode');
                }
            }

            $this->validator->validate('record_id', $params['reporttype_id']);

            if (empty($params['reporttype'])) {
                $this->setErrorMessage('Invalid Report Type');
            }

            if (isset($params['day']) AND (! is_numeric($params['day']) OR $params['day'] <= 0)) {
                $this->setErrorMessage('Invalid filter day');
            }

            if (isset($params['mph']) AND (! is_numeric($params['mph']) OR $params['mph'] <= 0)) {
                $this->setErrorMessage('Invalid filter speed');
            }

            if (isset($params['minute']) AND (! is_numeric($params['minute']) OR $params['minute'] <= 0)) {
                $this->setErrorMessage('Invalid filter minutes');
            }

            if (isset($params['mile']) AND (! is_numeric($params['mile']) OR $params['mile'] <= 0)) {
                $this->setErrorMessage('Invalid filter miles');
            }
            /*
            if (isset($params['filter_stop_number']) AND (! is_numeric($params['filter_stop_number']) OR $params['filter_stop_number'] <= 0)) {
                $this->setErrorMessage('Invalid filter number of stops');
            }
            */
            if (isset($params['alerttype_id'])) {
                $this->validator->validate('record_id', $params['alerttype_id']);
            }

            if (isset($params['filter_user_id'])) {
                $this->validator->validate('record_id', $params['filter_user_id']);
            }

            if (isset($params['verification']) AND empty($params['verification'])) {
                $this->setErrorMessage('Invalid verification filter');
            }

            if (isset($params['report_output']) AND ! in_array($params['report_output'], array('html', 'csv', 'pdf'))) {
                $this->setErrorMessage('Invalid report format');
            }

            // only calculate the starttime and endtime for this report if it's ran manually (i.e. not from the scheduled report cron)
            if (! empty($params['starttime']) AND ! empty($params['endtime']) AND ! empty($params['method']) AND $params['method'] !== 'Scheduled') {

                $start_date_arr = explode('/', $params['starttime']);
                $end_date_arr = explode('/', $params['endtime']);

                $user_date_object = new \DateTime(NULL, new \DateTimeZone($params['user_timezone']));

                // get the user's current timestamp
                $current_timestamp = $user_date_object->getTimestamp();
                $current_date = $user_date_object->format('Y-m-d');

                // get the user's start date timestamp
                $user_date_object->setDate($start_date_arr[2], $start_date_arr[0], $start_date_arr[1]);
                $start_timestamp = $user_date_object->getTimestamp();

                // get the user's end date timestamp
                $user_date_object->setDate($end_date_arr[2], $end_date_arr[0], $end_date_arr[1]);
                $end_timestamp = $user_date_object->getTimestamp();
                $end_date = $user_date_object->format('Y-m-d');

                if ($start_timestamp > $end_timestamp) {          // if start date is after end date, throw error
                    $this->setErrorMessage('Start Date must be before End Date ('.$start_timestamp.'>'.$end_timestamp.') '.$params['starttime'].' > '.$params['endtime']);
                } else {
                    if ($start_timestamp == $end_timestamp) {  // else if start date and end date are on the same day, set start date and end date accordingly
                        // set start date time to 12:00 AM
                        $user_date_object->setTimestamp($start_timestamp);
                        $params['starttime'] = $user_date_object->format('Y-m-d 00:00:00');

                        // else set end date time to 11:59 PM
                        $user_date_object->setTimestamp($end_timestamp);
                        $params['endtime'] = $user_date_object->format('Y-m-d 23:59:59');
                    }

                    if ($end_date == $current_date) {  // if end date is current date, set end date time to current date time
                        $user_date_object->setTimestamp($current_timestamp);
                        $params['endtime'] = $user_date_object->format('Y-m-d H:i:s');

                    }
                }
            }

            if ($this->validator->hasError()) {
                $this->setErrorMessage($this->validator->getErrorMessage());
            }

            if (! $this->hasError()) {
                return true;
            }
        } else {
            $this->setErrorMessage('Missing parameters for generating report');
        }
        return false;
    }

    /**
     * Alert report
     *
     * @param array param
     *
     * @return array
     */
    private function alert($params)
    {

        $report_params = $units = $alert_types = $alerttypes = array();
        $data = array(
            'report' => array(
                'title'                     => $params['report_name'],
                'report_type'               => $params['reporttypename'],
                'time_generated'            => (empty($params['createdate'])) ? Date::locale_to_locale(date('Y-m-d H:i:s'), SERVER_TIMEZONE, $params['user_timezone'], 'm/d/Y h:i A') : Date::utc_to_locale($params['createdate'], $params['user_timezone'], 'm/d/Y h:i A'),
                'criteria'  => array(
                    'time_range'            => array(
                        'label'             => 'Date Range',
                        'value'             => Date::locale_to_locale(date('Y-m-d H:i:s', strtotime($params['starttime'])), $params['user_timezone'], $params['user_timezone'], 'm/d/Y h:i A') . ' - ' . Date::locale_to_locale(date('Y-m-d H:i:s', strtotime($params['endtime'])), $params['user_timezone'], $params['user_timezone'], 'm/d/Y h:i A')
                    ),
                    'selected_vehicles'     => array(
                        'label'             => 'Vehicles Selected',
                        'value'             => 'All Vehicles'
                    ),
                    'filter_alerttype'      => array(
                        'label'             => 'Alert Type',
                        'value'             => 'All Alert Type'
                    )
                ),
                'columns' => array(
                    'formatted_date'        => 'Date/Time',
                    'alerttypename'         => 'Alert Type',
                    'unitname'              => 'Vehicle Name',
                    'formatted_address'     => 'Address'
                ),
                'summary' => array(
                    'total_units'           => array('label' => 'Vehicles Returned', 'value' => 0),
                    'total_alerts'          => array('label' => 'Total Alerts', 'value' => 0),
                    'greatest_alert_type'   => array('label' => 'Greatest Alert Type', 'value' => '')
                )
            ),
            'units' => array()
        );

        // convert start date and end date to user's local time and then to UTC
        $report_params['start_date'] = Date::locale_to_utc(date('Y-m-d H:i:s', strtotime($params['starttime'])), $params['user_timezone']);
        $report_params['end_date'] = Date::locale_to_utc(date('Y-m-d H:i:s', strtotime($params['endtime'])), $params['user_timezone']);
        $report_params['alert_type'] = ''; // default alert type filter to 'all'

        if (! empty($params['unit_mode'])) {
            if ($params['unit_mode'] == 'group' AND ! empty($params['unitgroup_id'])) {
                $units = $this->vehicle_logic->getVehicleInfoByGroupId($params['unitgroup_id'], 1, $params['user_timezone'], 1); // get only active units
                $report_params['vehiclegroup_id'] = array($params['unitgroup_id']);
                $data['report']['criteria']['selected_vehicles']['value'] = $units[0]['unitgroupname'];
            } else if ($params['unit_mode'] == 'single' AND ! empty($params['unit_id'])) {
                $single_unit = $this->vehicle_logic->getVehicleInfoById($params['unit_id']);
                $data['report']['criteria']['selected_vehicles']['value'] = $single_unit['unitname'];
                $units = array($single_unit);
                $report_params['vehicle_id'] = array($params['unit_id']);
            } else {
                $units = $this->vehicle_logic->getVehiclesByAccountId($params['account_id'], 1, $params['user_timezone'], 1); // get only active units
            }
        }

        if (! empty($units)) {
            // iterate through the units and build the report data array
            foreach ($units as $unit) {
                $unit['report_title'] = 'Vehicle: ' . $unit['unitname'];
                $unit['report_data'] = array();
                $data['units'][$unit['unit_id']] = $unit;
            }

            $report_params['alerttype_id'] = 0;
            if (! empty($params['alerttype_id'])) {
                if ($params['alerttype_id'] != 0) {
                    $report_params['alerttype_id'] = $params['alerttype_id'];

                    $alerttypes = $this->getReportAlertTypes();

                    $data['report']['criteria']['filter_alerttype']['value'] = $alerttypes[$params['alerttype_id']];
                }
            }

            //print_rb($report_params);
            $alerthistory = $this->alert_data->getAlertHistoryReport($params['account_id'], $report_params);

            //print_rb(count($alerthistory));
            if ($alerthistory !== false) {
                if (! empty($alerthistory)) {
                    //print_rb($alerthistory);
                    foreach ($alerthistory as $ah) {
                        if (isset($data['units'][$ah['unit_id']])) {
                            $event = $this->vehicle_logic->getVehicleDataEventInfo($data['units'][$ah['unit_id']], $ah['unitevent_id']);
                            if (empty($event['formatted_address'])) {
                                $ah['formatted_address'] = 'Unknown Address';
                            } else {
                                $ah['formatted_address'] = $event['formatted_address'];
                            }

                            //$ah['formatted_date'] = Date::utc_to_locale($ah['alerthistorydate'], $params['user_timezone'], 'h:i A m/d/Y');
                            $ah['formatted_date'] = Date::utc_to_locale($ah['uniteventdate'], $params['user_timezone'], 'h:i A m/d/Y');

                            //$data['units'][$ah['unit_id']]['report_data'][] = $ah;
                            $data['units'][$ah['unit_id']]['report_data'][] = array(
                                'formatted_date'        => $ah['formatted_date'],
                                'alerttypename'         => $ah['alerttypename'],
                                'unitname'              => $ah['unitname'],
                                'formatted_address'     => $ah['formatted_address'],
                                'latitude'              => (! empty($event['latitude'])) ? $event['latitude'] : 0,
                                'longitude'             => (! empty($event['longitude'])) ? $event['longitude'] : 0,
                                'eventname'             => (isset($event['eventname']) AND ! empty($event['eventname'])) ? $event['eventname'] : ''
                            );

                            // get the total number of alerts triggered
                            $data['report']['summary']['total_alerts']['value'] += 1;

                            // increment the alert types to find out which alert type was triggered the most
                            if (! isset($alert_types[$ah['alerttype_id']])) {
                                $alert_types[$ah['alerttype_id']] = array(
                                    'count' => 1,
                                    'alerttypename' => $ah['alerttypename']
                                );
                            } else {
                                $alert_types[$ah['alerttype_id']]['count'] += 1;
                            }
                        }
                    }

                    // get the greatest alert type
                    if (! empty($alert_types)) {
                        //print_rb($alert_types);
                        $greatest_type = array();
                        foreach ($alert_types as $at) {
                            if (empty($greatest_type)) {
                                $greatest_type = $at;
                            } else {
                                if ($greatest_type['count'] < $at['count']) {
                                    $greatest_type = $at;
                                }
                            }
                        }

                        $data['report']['summary']['greatest_alert_type']['value'] = $greatest_type['alerttypename'] . ' (' . $greatest_type['count'] . ')';
                    }
                }
            }
        }

        $data['report']['summary']['total_units']['value'] = count($units);

        return $data;
    }

    /**
     * Detail report
     *
     * @param array param
     *
     * @return array
     */
    private function detail($params)
    {
        $report_params = $units = array();
        $data = array(
            'report' => array(
                'title'                     => $params['report_name'],
                'report_type'               => $params['reporttypename'],
                'time_generated'            => (empty($params['createdate'])) ? Date::locale_to_locale(date('Y-m-d H:i:s'), SERVER_TIMEZONE, $params['user_timezone'], 'm/d/Y h:i A') : Date::utc_to_locale($params['createdate'], $params['user_timezone'], 'm/d/Y h:i A'),
                'criteria'  => array(
                    'time_range'            => array(
                        'label'             => 'Date Range',
                        'value'             => Date::locale_to_locale(date('Y-m-d H:i:s', strtotime($params['starttime'])), $params['user_timezone'], $params['user_timezone'], 'm/d/Y h:i A') . ' - ' . Date::locale_to_locale(date('Y-m-d H:i:s', strtotime($params['endtime'])), $params['user_timezone'], $params['user_timezone'], 'm/d/Y h:i A')
                    ),
                    'selected_vehicles'     => array(
                        'label'             => 'Vehicles Selected',
                        'value'             => ''
                    )
                ),
                'columns' => array(
                    'formatted_date'        => 'Date/Time',
                    'formatted_address'     => 'Address',
                    'eventname'             => 'Event'
                )
            ),
            'units' => array()
        );

        // convert start date and end date to user's local time and then to UTC
        $start_date = Date::locale_to_utc(date('Y-m-d H:i:s', strtotime($params['starttime'])), $params['user_timezone']);
        $end_date = Date::locale_to_utc(date('Y-m-d H:i:s', strtotime($params['endtime'])), $params['user_timezone']);

        // get units
        if (! empty($params['unit_mode'])) {
            if ($params['unit_mode'] == 'single' AND ! empty($params['unit_id'])) {
                $unit = $this->vehicle_logic->getVehicleInfoById($params['unit_id']);
                $data['report']['criteria']['selected_vehicles']['value'] = $unit['unitname'];
            }
        }

        if (! empty($unit)) {
            // iterate through the units and build the report data array
            $unit['report_title'] = 'Vehicle: ' . $unit['unitname'];
            $unit['report_data'] = array();
            $data['units'][$unit['unit_id']] = $unit;

            $events = $this->vehicle_logic->getVehicleHistory($unit['unit_id'], $unit['db'], $start_date, $end_date);

            if ($events !== false) {
                if (! empty($events)) {
                    $report_events = array();
                    foreach ($events as $index => $event) {
                        // format address
                        $events[$index]['formatted_address'] = $this->address_logic->validateAddress($event['streetaddress'], $event['city'], $event['state'], $event['zipcode'], $event['country']);

                        // convert unit time to user time
                        $events[$index]['formatted_date'] = Date::utc_to_locale($event['unittime'], $params['user_timezone'], 'h:i A m/d/Y');

                        if (! empty($events[$index]['landmark_id'])) {
		                    // get landmark info
		                    $eventlandmark = $this->territory_logic->getTerritoryByIds($events[$index]['landmark_id'], false);
		                    if ($eventlandmark !== false AND count($eventlandmark) == 1) {
		                        $events[$index]['landmark'] = array_pop($eventlandmark);
		                    }
		                }

                        $report_events[] = array(
                            'unitname'              => $unit['unitname'], // this column is for csv export
                            'formatted_date'        => $events[$index]['formatted_date'],
                            'formatted_address'     => (isset($events[$index]['landmark']) AND ! empty($events[$index]['landmark'])) ? '('.$events[$index]['landmark']['territoryname'].') '.$events[$index]['formatted_address'] : $events[$index]['formatted_address'],
                            'eventname'             => (isset($events[$index]['eventname']) AND ! empty($events[$index]['eventname'])) ? $events[$index]['eventname'] : '',
                            'latitude'              => (! empty($events[$index]['latitude'])) ? $events[$index]['latitude'] : 0,
                            'longitude'             => (! empty($events[$index]['longitude'])) ? $events[$index]['longitude'] : 0
                        );
                    }

                    $data['units'][$unit['unit_id']]['report_data'] = $report_events;
                }
            }
        }
        return $data;
    }

    /**
     * Stop report
     *
     * @param array params
     *
     * @return array
     */
    private function stop($params)
    {
        $report_params = $unit = array();
        $data = array(
            'report' => array(
                'title'                     => $params['report_name'],
                'report_type'               => $params['reporttypename'],
                'time_generated'            => (empty($params['createdate'])) ? Date::locale_to_locale(date('Y-m-d H:i:s'), SERVER_TIMEZONE, $params['user_timezone'], 'm/d/Y h:i A') : Date::utc_to_locale($params['createdate'], $params['user_timezone'], 'm/d/Y h:i A'),
                'criteria'  => array(
                    'time_range'            => array(
                        'label'             => 'Date Range',
                        'value'             => Date::locale_to_locale(date('Y-m-d H:i:s', strtotime($params['starttime'])), $params['user_timezone'], $params['user_timezone'], 'm/d/Y h:i A') . ' - ' . Date::locale_to_locale(date('Y-m-d H:i:s', strtotime($params['endtime'])), $params['user_timezone'], $params['user_timezone'], 'm/d/Y h:i A')
                    ),
                    'selected_vehicles'     => array(
                        'label'             => 'Vehicles Selected',
                        'value'             => 'All Vehicles'
                    ),
                    'filter_duration'       => array(
                        'label'             => 'Stop Threshold',
                        'value'             => '> ' . Date::seconds_to_timespan(Date::convertDurationTimeToSeconds($params['minute'].'-mins'))
                    )
                ),
                'columns' => array(
                    'eventname'       => 'Event',
                    'display_unittime'    => 'Date',
                    'formatted_address' => 'Address',
                    'duration'          => 'Duration'
                 ),
                'summary'                   => array()
            ),
            'units' => array()
        );

        $start_date = Date::locale_to_utc(date('Y-m-d 00:00:00', strtotime($params['starttime'])), $params['user_timezone'], 'Y-m-d H:i:s');
        $end_date   = Date::locale_to_utc(date('Y-m-d 23:59:59', strtotime($params['endtime'])), $params['user_timezone'], 'Y-m-d H:i:s');

        // this stop report will use the processQuickHistoryEvents for a 'recent' type stop report
        $report_params['event_type']    = 'recent';
        $report_params['user_timezone'] = $params['user_timezone'];
        $report_params['duration']      = '30-mins';  // default
        if (isset($params['minute']) AND ! empty($params['minute'])) {
            $report_params['duration'] = $params['minute'].'-mins';    // add '-mins' duration format sample (30-mins)
        }

        // get this unit info
        $unit = $this->vehicle_logic->getVehicleInfoById($params['unit_id']);

        if (! empty($unit)) {
            // iterate through the units and build the report data array
            $data['units'][$unit['unit_id']] = $unit;
            $data['units'][$unit['unit_id']]['report_title'] = 'Stop Report for: ' . $unit['unitname'];
            $data['units'][$unit['unit_id']]['report_data'] = array();
            $data['report']['criteria']['selected_vehicles']['value'] = $unit['unitname'];

            /*
            $report_data['unitname']            = $unit['unitname'];
            $report_data['eventname']           = '-';
            $report_data['display_unittime']    = '-';
            $report_data['formatted_address']   = '-';
            $report_data['duration']            = '-';
            */

            // this unit event db
            $event_db = $unit['db'];

            // get this vehicle history for the dates
            $unithistory = $this->vehicle_logic->getVehicleHistory($unit['unit_id'], $event_db, $start_date, $end_date);
            if ($unithistory !== false) {
                if (! empty($unithistory)) {
                    // process the unit history events for stops
                    $processed_events = $this->vehicle_logic->processQuickHistoryEvents($unithistory, $report_params);
                    // format the processed events
                    $stop_results = $this->vehicle_logic->formatVehicleRecentStopEvents($processed_events,'unittime','desc',count($unithistory));

                    // loop through results and add to report data return results
                    foreach ($stop_results as $event) {
                        $report_data = array();
                        $report_data['unitname']            = $unit['unitname']; // this column is for csv export
                        $report_data['eventname']           = $event['eventname'];
                        $report_data['display_unittime']    = $event['display_unittime'];
                        $report_data['formatted_address']   = $event['formatted_address'];
                        $report_data['duration']            = $event['duration'];

                        if (empty($report_data['formatted_address'])) {
                            $report_data['formatted_address'] = 'Unknown Address';
                        }

                        $report_data['latitude'] = (! empty($event['latitude'])) ? $event['latitude'] : 0;
                        $report_data['longitude'] = (! empty($event['longitude'])) ? $event['longitude'] : 0;

                        $data['units'][$unit['unit_id']]['report_data'][] = $report_data;
                    }
                }
            }
            // no data found
            //$data['units'][$unit['unit_id']]['report_data'][] = $report_data;
        }

        return $data;
    }

    /**
     * Last Ten Stops report
     *
     * @param array params
     *
     * @return array
     */
    private function lastTenStops($params)
    {
        $report_params = $unit = array();
        $data = array(
            'report' => array(
                'title'                     => $params['report_name'],
                'report_type'               => $params['reporttypename'],
                'time_generated'            => (empty($params['createdate'])) ? Date::locale_to_locale(date('Y-m-d H:i:s'), SERVER_TIMEZONE, $params['user_timezone'], 'm/d/Y h:i A') : Date::utc_to_locale($params['createdate'], $params['user_timezone'], 'm/d/Y h:i A'),
                'criteria'  => array(
                    'time_range'            => array(
                        'label'             => 'Date Range',
                        'value'             => ''
                    ),
                    'selected_vehicles'     => array(
                        'label'             => 'Vehicles Selected',
                        'value'             => 'All Vehicles'
                    ),
                    'filter_duration'       => array(
                        'label'             => 'Duration',
                        'value'             => ''
                    )
                ),
                'columns' => array(
                    'display_unittime'  => 'Date',
                    'formatted_address' => 'Address',
                    'duration'          => 'Duration'
                 ),
                'summary'                   => array()
            ),
            'units' => array()
        );

        // this stop report will use the processQuickHistoryEvents for a 'recent' type stop report
        $report_params['event_type']    = 'recent';
        $report_params['user_timezone'] = $params['user_timezone'];
        $report_params['duration']      = '1-min';  // default

        // get this unit info
        $unit = $this->vehicle_logic->getVehicleInfoById($params['unit_id']);

        if (! empty($unit)) {
            // iterate through the units and build the report data array
            $data['units'][$unit['unit_id']] = $unit;
            $data['units'][$unit['unit_id']]['report_title'] = 'Last Ten Stops Report for: ' . $unit['unitname'];

            $report_data                        = array();

            // this unit event db
            $event_db           = $unit['db'];
            $earliest_event_id  = 0;
            $unithistory        = $this->vehicle_logic->getLastTenVehicleOffEvents($unit);
            if (is_array($unithistory) AND ! empty($unithistory)) {
                $earliest_event = $unithistory[count($unithistory)-1];//array_pop($unithistory);
                if (is_array($earliest_event) AND isset($earliest_event['id'])) {
                    $earliest_event_id = $earliest_event['id'];
                }
            }

            // get this vehicle history events after
            $unithistory = $this->vehicle_logic->getVehicleUnitEventsFromId($unit, $earliest_event_id);
            if ($unithistory !== false) {
                if (! empty($unithistory)) {
                    // process the unit history events for stops
                    $processed_events = $this->vehicle_logic->processQuickHistoryEvents($unithistory, $report_params);
                    // format the processed events
                    $stop_results = $this->vehicle_logic->formatVehicleRecentStopEvents($processed_events);

                    // loop through results and add to report data return results
                    foreach ($stop_results as $event) {
                        $report_data = array();
                        $report_data['display_unittime']    = $event['display_unittime'];
                        $report_data['formatted_address']   = $event['formatted_address'];
                        $report_data['duration']            = $event['duration'];
                        $report_data['latitude']            = (! empty($event['latitude'])) ? $event['latitude'] : 0;
                        $report_data['longitude']           = (! empty($event['longitude'])) ? $event['longitude'] : 0;
                        $report_data['eventname']           = (isset($event['eventname']) AND ! empty($event['eventname'])) ? $event['eventname'] : '';

                        if (empty($report_data['formatted_address'])) {
                            $report_data['formatted_address'] = 'Unknown Address';
                        }

                        $data['units'][$unit['unit_id']]['report_data'][] = $report_data;
                    }
                }
            }
			else
			{
            	$data['units'][$unit['unit_id']]['report_data'][] = $report_data;
            }
        }

        return $data;
    }

    /**
     * Stationary report
     *
     * @param array params
     *
     * @return array
     */
    private function stationary($params)
    {
        $report_params = $unit = array();
        $data = array(
            'report' => array(
                'title'                     => (isset($params['report_name']) AND $params['report_name'] != '') ? $params['report_name'] : 'Stationary Report',
                'report_type'               => $params['reporttypename'],
                'time_generated'            => (empty($params['createdate'])) ? Date::locale_to_locale(date('Y-m-d H:i:s'), SERVER_TIMEZONE, $params['user_timezone'], 'm/d/Y h:i A') : Date::utc_to_locale($params['createdate'], $params['user_timezone'], 'm/d/Y h:i A'),
                'criteria'  => array(
                    'time_range'            => array(
                        'label'             => 'Date Range',
                        'value'             => ''
                    ),
                    'selected_vehicles'     => array(
                        'label'             => 'Vehicles Selected',
                        'value'             => 'All Vehicles'
                    ),
                    'filter_duration'       => array(
                        'label'             => 'Not Reported for ',
                        'value'             => $params['day']. ' Days'
                    )
                ),
                'columns'   => array(
                    'unitname'          => 'Vehicle Name',
                    'formatted_address' => 'Current Address',
                    'formatted_date'    => 'Date/Time Last Event',
                    'duration'          => 'Stationary Duration'
                 ),
                'summary'                   => array()
            ),
            'units' => array()
        );

        $days_ago_date = date('Y-m-d H:i:s', mktime(0, 0, 0, date("m"), (date("d") - $params['day']), date("Y")));
        $filter_date = Date::locale_to_utc($days_ago_date, $params['user_timezone'], 'Y-m-d H:i:s');

        // get units
        if (! empty($params['unit_mode'])) {
            if ($params['unit_mode'] == 'group' AND ! empty($params['unitgroup_id'])) {
                $units = $this->vehicle_logic->getVehicleInfoByGroupId($params['unitgroup_id'], 1, $params['user_timezone'], 1);
                $report_params['vehiclegroup_id'] = array($params['unitgroup_id']);

                $data['report']['criteria']['selected_vehicles']['value'] = $units[0]['unitgroupname'];

            } else if ($params['unit_mode'] == 'single' AND ! empty($params['unit_id'])) {
                $single_unit = $this->vehicle_logic->getVehicleInfoById($params['unit_id']);
                $units = array($single_unit);
                $report_params['vehicle_id'] = array($params['unit_id']);

                $data['report']['criteria']['selected_vehicles']['value'] = $single_unit['unitname'];

            } else {
                $units = $this->vehicle_logic->getVehiclesByAccountId($params['account_id'], 1, $params['user_timezone'], 1);
            }
        }

        // set table title and data to default values
        $data['units'][1] = array(
            'report_title' => '',
            'report_data' => array()
        );

        if (isset($units) AND ! empty($units)) {
            $return_lastevent = true;
            //$data['units'][1] = array();

            // iterate through the units and build the report data array
            foreach ($units as $unit) {
                $report_data = array();
                $report_data['unitname']           = $unit['unitname'];
                $report_data['formatted_address']  = '-';
                $report_data['formatted_date']     = '-';
                $report_data['duration']           = '-';

                // check if vehicle is reporting, return last event if $return_lastevent is set to true
                $results = $this->vehicle_logic->getVehicleLastDriveEvent($unit, 7, $filter_date, $return_lastevent);
                if (is_array($results) AND ! empty($results)) {
                    $unit['event']              = $results;

                    // format event address and date
                    $report_data['formatted_address']  = $this->address_logic->validateAddress($unit['event']['streetaddress'], $unit['event']['city'], $unit['event']['state'], $unit['event']['zipcode'], $unit['event']['country']);

                    if (empty($report_data['formatted_address'])) {
                        $report_data['formatted_address'] = 'Unknown Address';
                    }

                    $report_data['eventname'] = (isset($unit['event']['eventname']) AND ! empty($unit['event']['eventname'])) ? $unit['event']['eventname'] : '';
                    $report_data['latitude'] = (! empty($unit['event']['latitude'])) ? $unit['event']['latitude'] : 0;
                    $report_data['longitude'] = (! empty($unit['event']['longitude'])) ? $unit['event']['longitude'] : 0;

                    $report_data['formatted_date'] = Date::utc_to_locale($unit['event']['unittime'], $params['user_timezone'], 'h:i A m/d/Y');

                    //get correct user utc time (timezone), calculate duration since unit last reported
                    $usertime           = Date::locale_to_utc(date('Y-m-d H:i:s'), $params['user_timezone'], 'Y-m-d H:i:s');
                    $duration_time      = Date::time_difference_seconds($usertime, $unit['event']['unittime']);
                    $report_data['duration']   = Date::seconds_to_timespan($duration_time,true,true,true,false);

                    $data['units'][1]['report_data'][] = $report_data;

                } else if ($results !== false AND empty($results)) {
                    // unknown last drive event,  so get last known event that came in
                    $lastevent = $this->vehicle_data->getLastReportedEvent($unit);
                    if (is_array($lastevent) AND ! empty($lastevent)) {
                        $unit['event'] = $lastevent;

                        // format event address and date
                        $report_data['formatted_address']  = $this->address_logic->validateAddress($unit['event']['streetaddress'], $unit['event']['city'], $unit['event']['state'], $unit['event']['zipcode'], $unit['event']['country']);

                        if (empty($report_data['formatted_address'])) {
                            $report_data['formatted_address'] = 'Unknown Address';
                        }
                        $report_data['eventname'] = (isset($unit['event']['eventname']) AND ! empty($unit['event']['eventname'])) ? $unit['event']['eventname'] : '';
                        $report_data['latitude'] = (! empty($unit['event']['latitude'])) ? $unit['event']['latitude'] : 0;
                        $report_data['longitude'] = (! empty($unit['event']['longitude'])) ? $unit['event']['longitude'] : 0;

                        $report_data['formatted_date'] = Date::utc_to_locale($unit['event']['unittime'], $params['user_timezone'], 'h:i A m/d/Y');

                        //get correct user utc time (timezone), calculate duration since unit last reported
                        $usertime           = Date::locale_to_utc(date('Y-m-d H:i:s'), $params['user_timezone'], 'Y-m-d H:i:s');
                        $duration_time      = Date::time_difference_seconds($usertime, $unit['event']['unittime']);
                        $report_data['duration']   = Date::seconds_to_timespan($duration_time,true,true,true,false);

                        $data['units'][1]['report_data'][] = $report_data;
                    } else {
                        //no last event found, but still stationary unit
                        $data['units'][1]['report_data'][] = $report_data;
                    }
                } else {
                    // db error returned false, still show unit but with no data info
                }
            }
        } else {
            // no result, show empty report
            //$data['units'][1] = array();
        }

        return $data;
    }
    /**
     * Non Reporting report
     *
     * @param array params
     *
     * @return array
     */
    private function nonReporting($params)
    {
        $report_params = $unit = array();
        $data = array(
            'report' => array(
                'title'                     => (isset($params['report_name']) AND $params['report_name'] != '') ? $params['report_name'] : 'Non Reporting Report',
                'report_type'               => $params['reporttypename'],
                'time_generated'            => (empty($params['createdate'])) ? Date::locale_to_locale(date('Y-m-d H:i:s'), SERVER_TIMEZONE, $params['user_timezone'], 'm/d/Y h:i A') : Date::utc_to_locale($params['createdate'], $params['user_timezone'], 'm/d/Y h:i A'),
                'criteria'  => array(
                    'time_range'            => array(
                        'label'             => 'Date Range',
                        'value'             => ''
                    ),
                    'selected_vehicles'     => array(
                        'label'             => 'Vehicles Selected',
                        'value'             => 'All Vehicles'
                    ),
                    'filter_duration'       => array(
                        'label'             => 'Not Reported for ',
                        'value'             => $params['day']. ' Days'
                    )
                ),
                'columns'   => array(
                    'unitname'          => 'Vehicle Name',
                    'eventname'         => 'Event Type',
                    'formatted_address' => 'Address',
                    'formatted_date'    => 'Date/Time Last Event',
                    'duration'          => 'Duration'
                 ),
                'summary'                   => array()
            ),
            'units' => array()
        );

        $filter_date = date('Y-m-d H:i:s', mktime(0, 0, 0, date("m"), (date("d") - $params['day']), date("Y")));
        $start_date = Date::locale_to_utc($filter_date, $params['user_timezone'], 'Y-m-d H:i:s');
        $end_date   = Date::locale_to_utc(date('Y-m-d H:i:s'), $params['user_timezone'], 'Y-m-d H:i:s');

        // get units
        if (! empty($params['unit_mode'])) {
            if ($params['unit_mode'] == 'group' AND ! empty($params['unitgroup_id'])) {
                $units = $this->vehicle_logic->getVehicleInfoByGroupId($params['unitgroup_id'], 1, $params['user_timezone'], 1);
                $report_params['vehiclegroup_id'] = array($params['unitgroup_id']);
                $data['report']['criteria']['selected_vehicles']['value'] = $units[0]['unitgroupname'];
            } else if ($params['unit_mode'] == 'single' AND ! empty($params['unit_id'])) {
                $single_unit = $this->vehicle_logic->getVehicleInfoById($params['unit_id']);
                $units = array($single_unit);
                $report_params['vehicle_id'] = array($params['unit_id']);
                $data['report']['criteria']['selected_vehicles']['value'] = $single_unit['unitname'];
            } else {
                $units = $this->vehicle_logic->getVehiclesByAccountId($params['account_id'], 1, $params['user_timezone'], 1);
            }
        }

        if (! empty($units)) {
            $return_lastevent = true;
            $data['units'][1]['report_data'] = array();
            // iterate through the units and build the report data array
            foreach ($units as $unit) {

                $report_data['unitname']           = $unit['unitname'];
                $report_data['eventname']          = '-';
                $report_data['formatted_address']  = '-';
                $report_data['formatted_date']     = '-';
                $report_data['duration']           = '-';

                // check if vehicle is reporting, return last event if $return_lastevent is set to true
                $results = $this->vehicle_logic->checkForVehicleReporting($unit, $params['day'], $start_date, $return_lastevent);

                if (is_array($results)) {
                    $unit['event']              = $results;
                    $report_data['eventname']   = $unit['event']['eventname'];

                    // format event address and date
                    $report_data['formatted_address']  = $this->address_logic->validateAddress($unit['event']['streetaddress'], $unit['event']['city'], $unit['event']['state'], $unit['event']['zipcode'], $unit['event']['country']);

                    if (empty($report_data['formatted_address'])) {
                        $report_data['formatted_address'] = 'Unknown Address';
                    }

                    $report_data['eventname']           = (isset($unit['event']['eventname']) AND ! empty($unit['event']['eventname'])) ? $unit['event']['eventname'] : '';
                    $report_data['latitude'] = (! empty($unit['event']['latitude'])) ? $unit['event']['latitude'] : 0;
                    $report_data['longitude'] = (! empty($unit['event']['longitude'])) ? $unit['event']['longitude'] : 0;

                    $report_data['formatted_date'] = Date::utc_to_locale($unit['event']['unittime'], $params['user_timezone'], 'h:i A m/d/Y');

                    //get correct user utc time (timezone), calculate duration since unit last reported
                    $usertime           = Date::locale_to_utc(date('Y-m-d H:i:s'), $params['user_timezone'], 'Y-m-d H:i:s');
                    $duration_time      = Date::time_difference_seconds($usertime, $unit['event']['unittime']);
                    $report_data['duration']   = Date::seconds_to_timespan($duration_time,true,true,true,false);

                    $data['units'][1]['report_data'][] = $report_data;
                } else if ($results === false) {
                    //has not reported within date range but lastevent not found
                    $data['units'][1]['report_data'][] = $report_data;
                } else {
                    // unit is reporting within date range, do nothing (exclude unit from report)
                }
            }
        } else {
            // no result, show empty report
           $data['units'][1]['report_data'] = array();
        }

        //print_rb($data);
        return $data;
    }

    /**
     * Frequent Stops Report
     *
     * @param array params
     *
     * @return array data
     */
    public function frequentStops($params)
    {
        $report_params = $units = array();
        $data = array(
            'report' => array(
                'title'                     => $params['report_name'],
                'report_type'               => $params['reporttypename'],
                'time_generated'            => (empty($params['createdate'])) ? Date::locale_to_locale(date('Y-m-d H:i:s'), SERVER_TIMEZONE, $params['user_timezone'], 'm/d/Y h:i A') : Date::utc_to_locale($params['createdate'], $params['user_timezone'], 'm/d/Y h:i A'),
                'criteria'  => array(
                    'time_range'            => array(
                        'label'             => 'Date Range',
                        'value'             => Date::locale_to_locale(date('Y-m-d H:i:s', strtotime($params['starttime'])), $params['user_timezone'], $params['user_timezone'], 'm/d/Y h:i A') . ' - ' . Date::locale_to_locale(date('Y-m-d H:i:s', strtotime($params['endtime'])), $params['user_timezone'], $params['user_timezone'], 'm/d/Y h:i A')
                    ),
                    'selected_vehicles'     => array(
                        'label'             => 'Vehicles Selected',
                        'value'             => ''
                    ),
                    'filter_duration'       => array(
                        'label'             => 'Avg Duration Threshold',
                        'value'             => '> ' . Date::seconds_to_timespan(Date::convertDurationTimeToSeconds($params['minute'].'-mins'))
                    )
                ),
                'columns' => array(
                    'stop_counter'          => 'No of Stops',
                    'formatted_address'     => 'Address',
                    'duration'              => 'Avg Duration'
                ),
                'summary'                   => array()
            ),
            'units' => array()
        );

        // convert start date and end date to user's local time and then to UTC
        $report_params['start_date'] = Date::locale_to_utc(date('Y-m-d H:i:s', strtotime($params['starttime'])), $params['user_timezone']);
        $report_params['end_date'] = Date::locale_to_utc(date('Y-m-d H:i:s', strtotime($params['endtime'])), $params['user_timezone']);

        // get units
        if (! empty($params['unit_mode'])) {
            if ($params['unit_mode'] == 'single' AND ! empty($params['unit_id'])) {
                $unit = $this->vehicle_logic->getVehicleInfoById($params['unit_id']);
                $data['report']['criteria']['selected_vehicles']['value'] = $unit['unitname'];
            }
        }

        if (! empty($unit)) {
            // iterate through the units and build the report data array
            $unit['report_title'] = 'Vehicle: ' . $unit['unitname'];
            $unit['report_data'] = array();
            $data['units'][$unit['unit_id']] = $unit;

            $report_params['event_type'] = 'frequent';
            $report_params['event_db'] = $unit['db'];
            $report_params['user_timezone'] = $params['user_timezone'];
            $report_params['duration'] = $params['minute'] . '-mins';
            //$report_params['filter_stop_counter'] = $params['filter_stop_number'];

            $events = $this->vehicle_data->getVehicleQuickHistory($unit['unit_id'], $report_params);

            if ($events !== false) {
                $report_events = array();
                if (! empty($events)) {
                    $events = $this->vehicle_logic->getVehicleQuickHistoryEventsExport($events, $report_params);
                    foreach ($events as $event) {
                        $report_events[] = array(
                            'unitname'              => $unit['unitname'],   // this column is for csv export
                            'stop_counter'          => $event['stop_counter'],
                            'formatted_address'     => $event['formatted_address'],
                            'duration'              => $event['duration'],
                            'latitude'              => (! empty($event['latitude'])) ? $event['latitude'] : 0,
                            'longitude'             => (! empty($event['longitude'])) ? $event['longitude'] : 0,
                            'eventname'             => (isset($event['eventname']) AND ! empty($event['eventname'])) ? $event['eventname'] : ''
                        );
                    }
                }
                $data['units'][$unit['unit_id']]['report_data'] = $report_events;
            }
        }
        //print_rb($data);
        return $data;
    }

    /**
     * Mileage Summary Report
     *
     * @param array params
     *
     * @return array data
     */
    public function mileageSummary($params)
    {
        $report_params = $units = $filter_params = array();
        $data = array(
            'report' => array(
                'title'                     => $params['report_name'],
                'report_type'               => $params['reporttypename'],
                'time_generated'            => (empty($params['createdate'])) ? Date::locale_to_locale(date('Y-m-d H:i:s'), SERVER_TIMEZONE, $params['user_timezone'], 'm/d/Y h:i A') : Date::utc_to_locale($params['createdate'], $params['user_timezone'], 'm/d/Y h:i A'),
                'criteria'  => array(
                    'selected_vehicles'     => array(
                        'label'             => 'Vehicles Selected',
                        'value'             => 'All Vehicles'
                    ),
                    'filter_total_miles'    => array(
                        'label'             => 'Total Miles Filter',
                        'value'             => 'None'
                    )
                ),
                'columns' => array(
                    'unitname'              => 'Vehicle Name',
                    'currentodometer'       => 'Miles Driven',
                    'initialodometer'       => 'Initial Odometer',
                    'total_mileage'         => 'Total Miles'
                ),
                'summary'                   => array()
            ),
            'units' => array()
        );

        // set total miles filter
        if (! empty($params['mile'])) {
            $filter_params['total_miles'] = $params['mile'];
            $data['report']['criteria']['filter_total_miles']['value'] = '> ' . number_format($params['mile']);
        }

        // get units
        if (! empty($params['unit_mode'])) {
            if ($params['unit_mode'] == 'single' AND ! empty($params['unit_id'])) {
                $filter_params['unit_id'] = $params['unit_id'];
                $single_unit = $this->vehicle_logic->getVehicleInfoById($params['unit_id']);
                $data['report']['criteria']['selected_vehicles']['value'] = $single_unit['unitname'];
            } else if ($params['unit_mode'] == 'group' AND ! empty($params['unitgroup_id'])) {
                $filter_params['unitgroup_id'] = $params['unitgroup_id'];
                $unitgroup = $this->vehicle_logic->getVehicleGroupsById($params['account_id'], $params['unitgroup_id']);
                $data['report']['criteria']['selected_vehicles']['value'] = $unitgroup[0]['unitgroupname'];
            }

            // get active units only
            $filter_params['active'] = 1;
            $filter_params['user_timezone'] = $params['user_timezone'];

            $units = $this->vehicle_logic->getVehicleMileageByAccountId($params['account_id'], $filter_params);
        }

        if (! empty($units)) {
            $data['units'][0]['report_title'] = '';
            $data['units'][0]['report_data'] = $units;
        }
        return $data;
    }

    /**
     * Speed Summary Report
     *
     * @param array params
     *
     * @return array data
     */
    public function speedSummary($params)
    {
        $report_params = $units = array();
        $data = array(
            'report' => array(
                'title'                     => $params['report_name'],
                'report_type'               => $params['reporttypename'],
                'time_generated'            => (empty($params['createdate'])) ? Date::locale_to_locale(date('Y-m-d H:i:s'), SERVER_TIMEZONE, $params['user_timezone'], 'm/d/Y h:i A') : Date::utc_to_locale($params['createdate'], $params['user_timezone'], 'm/d/Y h:i A'),
                'criteria'  => array(
                    'time_range'            => array(
                        'label'             => 'Date Range',
                        'value'             => Date::locale_to_locale(date('Y-m-d H:i:s', strtotime($params['starttime'])), $params['user_timezone'], $params['user_timezone'], 'm/d/Y h:i A') . ' - ' . Date::locale_to_locale(date('Y-m-d H:i:s', strtotime($params['endtime'])), $params['user_timezone'], $params['user_timezone'], 'm/d/Y h:i A')
                    ),
                    'selected_vehicles'     => array(
                        'label'             => 'Vehicles Selected',
                        'value'             => 'All Vehicles'
                    ),
                    'filter_speed'          => array(
                        'label'             => 'Speed Filter',
                        'value'             => '> 75 MPH' // defaults to 75 mph
                    )
                ),
                'columns' => array(
                    'formatted_date'        => 'Date/Time',
                    'formatted_address'     => 'Address',
                    'speed'                 => 'Speed'
                ),
                'summary'                   => array()
            ),
            'units' => array()
        );

        // convert start date and end date to user's local time and then to UTC
        $start_date = Date::locale_to_utc(date('Y-m-d H:i:s', strtotime($params['starttime'])), $params['user_timezone']);
        $end_date = Date::locale_to_utc(date('Y-m-d H:i:s', strtotime($params['endtime'])), $params['user_timezone']);


        // get units
        if (! empty($params['unit_mode'])) {
            if ($params['unit_mode'] == 'group' AND ! empty($params['unitgroup_id'])) {
                $units = $this->vehicle_logic->getVehicleInfoByGroupId($params['unitgroup_id'], 1, $params['user_timezone']);
                $report_params['vehiclegroup_id'] = array($params['unitgroup_id']);
                $data['report']['criteria']['selected_vehicles']['value'] = $units[0]['unitgroupname'];
            } else if ($params['unit_mode'] == 'single' AND ! empty($params['unit_id'])) {
                $single_unit = $this->vehicle_logic->getVehicleInfoById($params['unit_id']);
                $data['report']['criteria']['selected_vehicles']['value'] = $single_unit['unitname'];
                $units = array($single_unit);
                $report_params['vehicle_id'] = array($params['unit_id']);
            } else {
                $units = $this->vehicle_logic->getVehiclesByAccountId($params['account_id'], 1, $params['user_timezone']);
            }
        }

        // set speed filter (defaults to 75 mph)
        $filter_speed = 75;
        if (! empty($params['mph'])) {
            $filter_speed = $params['mph'];
            $data['report']['criteria']['filter_speed']['value'] = '> ' . $filter_speed . ' MPH';
        }

        if (! empty($units)) {
            // iterate through the units and build the report data array
            foreach ($units as $index => $unit) {

                $unit['report_title'] = 'Vehicle: ' . $unit['unitname'];
                $unit['report_data'] = array();
                $data['units'][$unit['unit_id']] = $unit;

                $events = $this->vehicle_logic->getVehicleHistory($unit['unit_id'], $unit['db'], $start_date, $end_date);

                if ($events !== false) {
                    if (! empty($events)) {

                        $over_speed_events = array();

                        foreach ($events as $index => $event) {
                            if (! empty($event['speed']) AND ($event['speed'] > $filter_speed)) {
                                $over_speed_events[] = array(
                                    'unitname'          => $unit['unitname'],
                                    'formatted_date'    => Date::utc_to_locale($event['unittime'], $params['user_timezone'], 'h:i A m/d/Y'),
                                    'formatted_address' => $this->address_logic->validateAddress($event['streetaddress'], $event['city'], $event['state'], $event['zipcode'], $event['country']),
                                    'speed'             => intval($event['speed']),
                                    'latitude'          => (! empty($event['latitude'])) ? $event['latitude'] : 0,
                                    'longitude'         => (! empty($event['longitude'])) ? $event['longitude'] : 0,
                                    'eventname'         => (isset($event['eventname']) AND ! empty($event['eventname'])) ? $event['eventname'] : ''
                                );
                            }
                        }

                        $data['units'][$unit['unit_id']]['report_data'] = $over_speed_events;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Starter Disable (INCOMPLETE)
     * NTD: need to save the event id of the Starter Disable event in order to calculate the time duration;
     *      also need a way to know which user sent the Starter Disable command
     *
     * @param array params
     *
     * @return array
     */
    public function starterDisableSummary($params)
    {
        $report_params = $units = $filter_params = $report_data = array();

        $data = array(
            'report' => array(
                'title'                     => $params['report_name'],
                'report_type'               => $params['reporttypename'],
                'time_generated'            => (empty($params['createdate'])) ? Date::locale_to_locale(date('Y-m-d H:i:s'), SERVER_TIMEZONE, $params['user_timezone'], 'm/d/Y h:i A') : Date::utc_to_locale($params['createdate'], $params['user_timezone'], 'm/d/Y h:i A'),
                'criteria'  => array(
                    'selected_vehicles'     => array(
                        'label'             => 'Vehicles Selected',
                        'value'             => 'All Vehicles'
                    )
                ),
                'columns' => array(
                    'unitname'              => 'Vehicle Name',
                    'formatted_date'        => 'Date/Time',
                    'duration'              => 'Disabled Duration',
                    'username'              => 'Disabled By',
                    'formatted_address'     => 'Address'
                ),
                'summary'                   => array()
            ),
            'units' => array()
        );

        // get active units only
        $filter_params['active'] = 1;
        $filter_params['user_timezone'] = $params['user_timezone'];
        $filter_params['starterstatus'] = 'Disabled';

        // get units
        if (! empty($params['unit_mode'])) {
            if ($params['unit_mode'] == 'group' AND ! empty($params['unitgroup_id'])) {
                $units = $this->vehicle_logic->getVehicleGroupsById($params['account_id'], $params['unitgroup_id']); //unit group
                $filter_params['unitgroup_id'] = $params['unitgroup_id'];
                $data['report']['criteria']['selected_vehicles']['value'] = $unitgroup[0]['unitgroupname'];
            } else if ($params['unit_mode'] == 'single' AND ! empty($params['unit_id'])) {
                $units = $this->vehicle_logic->getVehicleInfoById($params['unit_id']); //single unit
                $data['report']['criteria']['selected_vehicles']['value'] = $single_unit['unitname'];
                $filter_params['unit_id'] = $params['unit_id'];
            } else {
                //$units = $this->vehicle_logic->getVehiclesByAccountId($params['account_id']);
                $units = $this->vehicle_logic->getVehicleInfoWhere($params['account_id'], $filter_params);
            }
        }

        //print_r($units);
        //exit();

        if (! empty($units)) {
            foreach ($units as $unit) {
                //$commandhistory_event = $this->user_logic->getUserCommandHistory($params['account_id'], array('unit_id' => $unit['unit_id']));
                $commandhistory_event = $this->user_logic->getUserStarterDisabledUnit($params['account_id'], array('unit_id' => $unit['unit_id']));

                if (!empty($commandhistory_event) && $commandhistory_event[0]['commandname']=='Starter Disable') {
                    $commandhistory_event = $commandhistory_event[0];
                    $user_currenttime = Date::locale_to_locale(date('Y-m-d H:i:s'), SERVER_TIMEZONE, $params['user_timezone'], 'Y-m-d H:i:s');
                    $sentdate = Date::utc_to_locale($commandhistory_event['messagedate'], $params['user_timezone'], 'Y-m-d H:i:s');
                    $report_data[] = array(
                        'unitname'          => $unit['unitname'],
                        'formatted_date'    => Date::locale_to_locale($sentdate, $params['user_timezone'], $params['user_timezone'], 'm/d/Y h:i A'),
                        'duration'          => Date::datetime_to_timespan($sentdate, $user_currenttime),
                        'username'          => $commandhistory_event['username'],
                        'formatted_address' => (! empty($commandhistory_event['receiveaddress'])) ? $commandhistory_event['receiveaddress'] : ''
                    );
                }
            }
        }
        //print_r($report_data);
        //exit();
        //print_rb($report_data);
        $data['units'][0]['report_title'] = '';
        $data['units'][0]['report_data'] = $report_data;
        return $data;
    }

    /**
     * Vehicle Information Report (INCOMPLETE)
     * NOTE: waiting on 'stock', 'last renewal date', 'deactivation date', and 'plan' columns to be created in the DB
     *
     * @param array params
     * @return array
     */
    public function vehicleInformation($params)
    {
        $report_params = $units = array();

        $data = array(
            'report' => array(
                'title'                     => $params['report_name'],
                'report_type'               => $params['reporttypename'],
                'time_generated'            => (empty($params['createdate'])) ? Date::locale_to_locale(date('Y-m-d H:i:s'), SERVER_TIMEZONE, $params['user_timezone'], 'm/d/Y h:i A') : Date::utc_to_locale($params['createdate'], $params['user_timezone'], 'm/d/Y h:i A'),
                'criteria'  => array(
                    'selected_vehicles'     => array(
                        'label'             => 'Vehicles Selected',
                        'value'             => 'All Vehicles'
                    )
                ),
                'columns' => array(
                    'unitname'              => 'Vehicle Name',
                    'serialnumber'          => 'Serial',
                    'unitgroupname'         => 'Group',
                    'unitstatusname'        => 'Status',
                    'vin'                   => 'VIN',
                    'stock'                 => 'Stock',
                    'make'                  => 'Make',
                    'model'                 => 'Model',
                    'year'                  => 'Year',
                    'color'                 => 'Color',
                    'licenseplatenumber'    => 'License Plate',
                    'loannumber'            => 'Loan ID',
                    'installdate'           => 'Install Date',
                    'installer'             => 'Installer',
                    'firstname'             => 'Customer First Name',
                    'lastname'              => 'Customer Last Name',
                    'formatted_address'     => 'Formatted Address',
                    'cellphone'             => 'Mobile Phone',
                    'homephone'             => 'Home Phone',
                    'email'                 => 'Email',
                    'simcardstatus'         => 'Sim Card Status',
                    'plan'                  => 'Plan',
                    'purchasedate'          => 'Purchase Date',
                    'renewaldate'           => 'Renewal Date',
                    'lastrenewaldate'       => 'Last Renewed On',
                    'deactivationdate'      => 'Deactivation Date'  // need this column added to the DB
                ),
                'summary'                   => array()
            ),
            'units' => array()
        );

        if (! empty($params['unit_mode']) AND $params['unit_mode'] == 'all') {
            $units = $this->vehicle_logic->getVehicleInfoReport($params['account_id']);
            $data['units'][0]['report_title'] = '';
            $data['units'][0]['report_data'] = array();
        }

        if (! empty($units)) {
            $report_data = array();
            foreach ($units as $unit) {
                $report_data[] = array(
                    'unitname'              => $unit['unitname'],
                    'serialnumber'          => $unit['serialnumber'],
                    'unitgroupname'         => $unit['unitgroupname'],
                    'unitstatusname'        => $unit['unitstatusname'],
                    'vin'                   => $unit['vin'],
                    'stock'                 => $unit['stocknumber'],
                    'make'                  => $unit['make'],
                    'model'                 => $unit['model'],
                    'year'                  => $unit['year'],
                    'color'                 => $unit['color'],
                    'licenseplatenumber'    => $unit['licenseplatenumber'],
                    'loannumber'            => $unit['loannumber'],
                    'installdate'           => Date::utc_to_locale($unit['installdate'], $params['user_timezone'], 'm/d/Y'),
                    'installer'             => $unit['installer'],
                    'firstname'             => $unit['firstname'],
                    'lastname'              => $unit['lastname'],
                    'formatted_address'     => $this->address_logic->validateAddress($unit['streetaddress'], $unit['city'], $unit['state'], $unit['zipcode'], $unit['country']),
                    'cellphone'             => $unit['cellphone'],
                    'homephone'             => $unit['homephone'],
                    'email'                 => $unit['email'],
                    'simcardstatus'         => $unit['simcardstatus'],
                    'plan'                  => $unit['plan'],
                    'purchasedate'          => Date::utc_to_locale($unit['purchasedate'], $params['user_timezone'], 'm/d/Y'),
                    'renewaldate'           => Date::utc_to_locale($unit['renewaldate'], $params['user_timezone'], 'm/d/Y'),
                    'lastrenewaldate'       => Date::utc_to_locale($unit['lastrenewaldate'], $params['user_timezone'], 'm/d/Y'),
                    'deactivationdate'      => Date::utc_to_locale($unit['renewaldate'], $params['user_timezone'], 'm/d/Y')                       // need this column added to the DB
                );
            }

            $data['units'][0]['report_data'] = $report_data;
        }
        //print_rb($data);
        return $data;
    }

    /**
     * Address Verification
     *
     * @param array params
     * @return array
     */
    public function verificationOfReference($params)
    {
        $report_params = $units = $references = $full = $partial = $none = array();

        $data = array(
            'report' => array(
                'title'                     => $params['report_name'],
                'report_type'               => $params['reporttypename'],
                'time_generated'            => (empty($params['createdate'])) ? Date::locale_to_locale(date('Y-m-d H:i:s'), SERVER_TIMEZONE, $params['user_timezone'], 'm/d/Y h:i A') : Date::utc_to_locale($params['createdate'], $params['user_timezone'], 'm/d/Y h:i A'),
                'criteria'  => array(
                    'selected_vehicles'     => array(
                        'label'             => 'Vehicles Selected',
                        'value'             => 'All Vehicles'
                    ),
                    'verification'          => array(
                        'label'             => 'Verification',
                        'value'             => 'All' // defaults to All
                    )
                ),
                'columns' => array(
                    'territoryname'         => 'Name',
                    'formatted_address'     => 'Address',
                    'latitude'              => 'Latitude',
                    'longitude'             => 'Longitude',
                    'radius_in_miles'       => 'Radius',    // in miles
                    'verified'              => 'Verified',
                    'formatted_date'        => 'Last Date At'
                ),
                'summary'                   => array() // if All Vehicles or Vehicle Group was selected, show the percentage
            ),
            'units' => array()
        );

        // get units
        if (! empty($params['unit_mode'])) {
            if ($params['unit_mode'] == 'group' AND ! empty($params['unitgroup_id'])) {
                $unitgroup = $this->vehicle_logic->getVehicleGroupsById($params['account_id'], $params['unitgroup_id']);
                $data['report']['criteria']['selected_vehicles']['value'] = $unitgroup[0]['unitgroupname'];
                $report_params['vehiclegroup_id'] = $params['unitgroup_id'];
            } else if ($params['unit_mode'] == 'single' AND ! empty($params['unit_id'])) {
                $single_unit = $this->vehicle_logic->getVehicleInfoById($params['unit_id']);
                $data['report']['criteria']['selected_vehicles']['value'] = $single_unit['unitname'];
                /*
                $report_unitdata = array(
                    'report_title'  => 'Vehicle: ' . $single_unit['unitname'],
                    'report_data'   => array()
                );
                $data['units'][$single_unit['unit_id']] = $report_unitdata;
                */
                $report_params['vehicle_id'] = $params['unit_id'];
            }
        }

        // get verification filter
        $filter_verified = 'all';
        if (! empty($params['verification'])) {
            $filter_verified = $params['verification'];
        }

        // get active units
        $report_params['active'] = 1;
        $report_params['user_timezone'] = $params['user_timezone'];

        // get the reference addresses of all vehicles
        $references = $this->territory_logic->getVerificationOfReferenceReport($params['account_id'], $report_params);

        if (! empty($references)) {
            // set vehicles selected
            if ($params['unit_mode'] == 'single') {
                $data['report']['criteria']['selected_vehicles']['value'] = $references[0]['unitname'];
            } else if ($params['unit_mode'] == 'group') {
                $data['report']['criteria']['selected_vehicles']['value'] = $references[0]['unitgroupname'];
            }

            // iterate through each reference addresses and group them by unit
            foreach($references as $r) {
                if (! isset($units[$r['unit_id']])) {
                    $units[$r['unit_id']] = array(
                        'report_title'  => 'Vehicle: ' . $r['unitname'],
                        'report_data'   => array(),
                        'verified'      => 0,
                        'total'         => 0
                    );
                }

                if ($r['verified']==1) {
                    //$r['verified'] = htmlentities('<span class="label label-success">Verified</span>');
                    //$r['verified'] = 'Verified';
                    $r['verified'] = '#Verified#';
                } else {
                    //$r['verified'] = htmlentities('<span class="label label-danger">Not Verified</span>');
                    //$r['verified'] = 'Not Verified';
                    $r['verified'] = '#Not Verified#';
                }

                $units[$r['unit_id']]['report_data'][] = array(
                    'unitname'          => $r['unitname'],
                    'territoryname'     => $r['territoryname'],
                    'formatted_address' => $this->address_logic->validateAddress($r['streetaddress'], $r['city'], $r['state'], $r['zipcode'], $r['country']),
                    'latitude'          => $r['latitude'],
                    'longitude'         => $r['longitude'],
                    'eventname'         => (isset($r['eventname']) AND ! empty($r['eventname'])) ? $r['eventname'] : '',
                    //'radius'            => (float) sprintf('%01.3f', ($r['radius'] * 0.00018939393)), // convert feet to miles
                    'radius_in_miles'   => Measurement::radiusFeetToFractionConverter($r['radius']),
                    'verified'          => $r['verified'],
                    'formatted_date'    => ($r['verifydate'] != '0000-00-00') ? Date::utc_to_locale($r['verifydate'], $params['user_timezone'], 'm/d/Y') : ''
                );

                if ($r['verified'] == 1) {
                    $units[$r['unit_id']]['verified'] += 1;
                }

                $units[$r['unit_id']]['total'] += 1;

                // update the amount of verified reference for this unit
                $units[$r['unit_id']]['report_title'] = 'Vehicle: ' . $r['unitname'] . ' ('. $units[$r['unit_id']]['verified'] .'/'. $units[$r['unit_id']]['total'] .' verified)';

                // determine if the unit's references are fully, partial, or not verified
                if ($units[$r['unit_id']]['verified'] == $units[$r['unit_id']]['total']) {  // if the unit's current verified references and current total references are equal (either fully or none)
                    if ($units[$r['unit_id']]['verified'] !== 0) {      // if verified is not zero, it's fully verified
                        $full[$r['unit_id']] = $units[$r['unit_id']];

                        // since unit's references are currently fully verified, unset this unit if it was in partial or none
                        if (isset($partial[$r['unit_id']])) {
                            unset($partial[$r['unit_id']]);
                        } else if (isset($none[$r['unit_id']])) {
                            unset($none[$r['unit_id']]);
                        }
                    } else {
                        $none[$r['unit_id']] = $units[$r['unit_id']];   // else it's not verified
                    }
                } else if ($units[$r['unit_id']]['verified'] == 0) {                                                                    // else the unit's references are partially verified
                    $none[$r['unit_id']] = $units[$r['unit_id']];
                } else {
                    $partial[$r['unit_id']] = $units[$r['unit_id']];
                    // unset this unit if it was previously set as partial
                    if (isset($full[$r['unit_id']])) {
                        unset($full[$r['unit_id']]);
                    } else if (isset($none[$r['unit_id']])) {
                        unset($none[$r['unit_id']]);
                    }
                }
            }

            $report_units = array();
            $total_units = count($units);

            // determine which set of units to show in the report depending on the verified filter and calculate report summary
            switch($filter_verified) {
                case 'All':
                    $report_units = array_merge($full, $partial, $none);
                    //$total_units = count($report_units);
                    $data['report']['summary'] = array(
                        'fully_verified'        => array(
                            'label' => 'Fully Verified',
                            'value' => count($full) . '/' . $total_units
                        ),
                        'partially_verified'    => array(
                            'label' => 'Partial Verification',
                            'value' => count($partial) . '/' . $total_units
                        ),
                        'no_verification'       => array(
                            'label' => 'No Verification',
                            'value' => count($none) . '/' . $total_units
                        )
                    );
                    break;
                case 'Full':
                    $report_units = $full;
                    $data['report']['summary'] = array(
                        'fully_verified'        => array(
                            'label' => 'Fully Verified',
                            'value' => count($full) . '/' . count($total_units)
                        )
                    );
                    break;
                case 'Partial':
                    $report_units = $partial;
                    $data['report']['summary'] = array(
                        'partially_verified'        => array(
                            'label' => 'Partial Verification',
                            'value' => count($partial) . '/' . count($total_units)
                        )
                    );
                    break;
                case 'None':
                    $report_units = $none;
                    $data['report']['summary'] = array(
                        'no_verification'        => array(
                            'label' => 'No Verification',
                            'value' => count($none) . '/' . count($total_units)
                        )
                    );
                    break;
            }

            $data['units'] = $report_units;
        }

        //$data['units'][0]['report_data'] = array();
        return $data;
    }

    /**
     * User Command
     *
     * @param array params
     * @return array
     */
    public function userCommand($params)
    {
        $report_params = $units = $filter_params = $report_data = array();

        $data = array(
            'report' => array(
                'title'                     => $params['report_name'],
                'report_type'               => $params['reporttypename'],
                'time_generated'            => (empty($params['createdate'])) ? Date::locale_to_locale(date('Y-m-d H:i:s'), SERVER_TIMEZONE, $params['user_timezone'], 'm/d/Y h:i A') : Date::utc_to_locale($params['createdate'], $params['user_timezone'], 'm/d/Y h:i A'),
                'criteria'  => array(
                    'time_range'            => array(
                        'label'             => 'Date Range',
                        'value'             => Date::locale_to_locale(date('Y-m-d H:i:s', strtotime($params['starttime'])), $params['user_timezone'], $params['user_timezone'], 'm/d/Y h:i A') . ' - ' . Date::locale_to_locale(date('Y-m-d H:i:s', strtotime($params['endtime'])), $params['user_timezone'], $params['user_timezone'], 'm/d/Y h:i A')
                    ),
                    'selected_users'        => array(
                        'label'             => 'Selected Users',
                        'value'             => 'All' // defaults to All
                    )
                ),
                'columns' => array(
                    'fullname'              => 'User',
                    'username'              => 'Username',
                    'commandname'           => 'Command',
                    'formatted_date'        => 'Date'
                ),
                'summary'                   => array()
            ),
            'units' => array()
        );

        // convert start date and end date to user's local time and then to UTC
        $filter_params['starttime'] = Date::locale_to_utc(date('Y-m-d H:i:s', strtotime($params['starttime'])), $params['user_timezone']);
        $filter_params['endtime'] = Date::locale_to_utc(date('Y-m-d H:i:s', strtotime($params['endtime'])), $params['user_timezone']);

        if (! empty($params['filter_user_id'])) {
            $user = $this->user_logic->getUserById($params['filter_user_id']);
            $data['report']['criteria']['selected_users']['value'] = $user[0]['fullname'];
            $filter_params['user_id'] = $params['filter_user_id'];
        }

        $user_commands = $this->user_logic->getUserCommandHistory($params['account_id'], $filter_params);

        //print_rb($user_commands);
        if (! empty($user_commands)) {
            foreach($user_commands as $uc) {
                $report_data[] = array(
                    'fullname'  => $uc['fullname'],
                    'username'  => $uc['username'],
                    'commandname'   => $uc['commandname'],
                    'formatted_date'    => Date::utc_to_locale($uc['sentdate'], $params['user_timezone'], 'm/d/Y h:i A')
                );
            }
        }

        $data['units'][0]['report_title'] = '';
        $data['units'][0]['report_data'] = $report_data;

        return $data;
    }

    /**
     * Landmark Report
     *
     * @param array params
     * @return array
     */
    public function landmark($params)
    {
        $report_params = $units = $vehicles_entered = $landmarks_entered = array();

        $data = array(
            'report' => array(
                'title'                     => $params['report_name'],
                'report_type'               => $params['reporttypename'],
                'time_generated'            => (empty($params['createdate'])) ? Date::locale_to_locale(date('Y-m-d H:i:s'), SERVER_TIMEZONE, $params['user_timezone'], 'm/d/Y h:i A') : Date::utc_to_locale($params['createdate'], $params['user_timezone'], 'm/d/Y h:i A'),
                'criteria'  => array(
                    'time_range'            => array(
                        'label'             => 'Date Range',
                        'value'             => Date::locale_to_locale(date('Y-m-d H:i:s', strtotime($params['starttime'])), $params['user_timezone'], $params['user_timezone'], 'm/d/Y h:i A') . ' - ' . Date::locale_to_locale(date('Y-m-d H:i:s', strtotime($params['endtime'])), $params['user_timezone'], $params['user_timezone'], 'm/d/Y h:i A')
                    ),
                    'selected_vehicles'        => array(
                        'label'             => 'Selected Vehicles',
                        'value'             => 'All Vehicles' // defaults to All
                    ),
                    'selected_territories'  => array(
                        'label'             => 'Selected Landmarks',
                        'value'             => 'All Landmarks' // defaults to All
                    )
                ),
                'columns' => array(
                    'unitname'              => 'Vehicle',
                    'enter_datetime'        => 'Entered Landmark',
                    'exit_datetime'         => 'Exit Landmark',
                    'duration'              => 'Time at Landmark'
                ),
                'summary'                   => array(
                    'landmarks_entered'     => array(
                        'label'             => 'Landmarks Entered',
                        'value'             => 0
                    ),
                    'vehicles_entered'      => array(
                        'label'             => 'Vehicles that Entered Landmark',
                        'value'             => 0
                    ),
                    'total_duration'        => array(
                        'label'             => 'Duration at Landmarks',
                        'value'             => 0
                    )
                )
            ),
            'units' => array()
        );

        // convert start date and end date to user's local time and then to UTC
        $start_date = Date::locale_to_utc(date('Y-m-d H:i:s', strtotime($params['starttime'])), $params['user_timezone']);
        $end_date = Date::locale_to_utc(date('Y-m-d H:i:s', strtotime($params['endtime'])), $params['user_timezone']);

        // get landmarks
        if (! empty($params['territory_mode'])) {
            $this->territory_logic->setTerritoryType('landmark');
            if ($params['territory_mode'] == 'group' AND ! empty($params['territorygroup_id'])) {
                $territories = $this->territory_logic->getTerritoryInfoByGroupId($params['territorygroup_id']);
                $data['report']['criteria']['selected_territories']['value'] = $territories[0]['territorygroupname'];
            } else if ($params['territory_mode'] == 'single' AND ! empty($params['territory_id'])) {
                $territories = $this->territory_logic->getTerritoryByIds($params['territory_id']);
                $data['report']['criteria']['selected_territories']['value'] = $territories[0]['territoryname'];
            } else {
                $territories = $this->territory_logic->getTerritoriesByAccountId($params['account_id']);
            }
            $this->territory_logic->resetTerritoryType();
        }

        // get units
        if (! empty($params['unit_mode'])) {
            if ($params['unit_mode'] == 'group' AND ! empty($params['unitgroup_id'])) {
                $units = $this->vehicle_logic->getVehicleInfoByGroupId($params['unitgroup_id'], 1, $params['user_timezone']);
                $data['report']['criteria']['selected_vehicles']['value'] = $units[0]['unitgroupname'];
            } else if ($params['unit_mode'] == 'single' AND ! empty($params['unit_id'])) {
                $single_unit = $this->vehicle_logic->getVehicleInfoById($params['unit_id']);
                $data['report']['criteria']['selected_vehicles']['value'] = $single_unit['unitname'];
                $units = array($single_unit);
            } else {
                $units = $this->vehicle_logic->getVehiclesByAccountId($params['account_id'], 1, $params['user_timezone']);
            }
        }

        if (! empty($territories)) {

            // iterate through the territories and set the report title and date
            foreach($territories as $t) {
                $data['units'][$t['territory_id']]['report_title'] = $t['territoryname'];
                $data['units'][$t['territory_id']]['report_data'] = array();
            }

            if (! empty($units)) {
                // iterate through the units and process their events
                foreach($units as $u) {
                    $events = $this->vehicle_logic->getVehicleHistory($u['unit_id'], $u['db'], $start_date, $end_date);
                    if (! empty($events)) {
                        $current_landmark = 0;
                        foreach($events as $i => $e) {
                            if (isset($e['landmark_id'])) {
                                $landmark_id = $e['landmark_id'];
                                if ($landmark_id != 0) {                            // if unit entered a landmark
                                    if ($current_landmark != 0) {                   // if unit is currently inside another landmark, check the current and new landmark
                                        if ($current_landmark != $landmark_id) {    // if current and new landmarks are not the same, set the appropriate exit and enter datetime for the current landmark and enter the new landmark

                                            // set the exit datetime for the current landmark
                                            if (isset($data['units'][$current_landmark])) {
                                                $index = count($data['units'][$current_landmark]['report_data']) - 1;
                                                $enter_datetime = $data['units'][$current_landmark]['report_data'][$index]['enter_datetime_format'];
                                                $exit_datetime = Date::utc_to_locale($e['unittime'], $params['user_timezone'], 'Y-m-d H:i:s');

                                                $data['units'][$current_landmark]['report_data'][$index]['exit_datetime'] = Date::utc_to_locale($e['unittime'], $params['user_timezone'], 'm/d/Y h:i A');
                                                $data['units'][$current_landmark]['report_data'][$index]['exit_datetime_format'] = $exit_datetime;
                                                $data['units'][$current_landmark]['report_data'][$index]['duration'] = Date::datetime_to_timespan($exit_datetime, $enter_datetime, true);

                                                // get overall time duration in landmarks for all units
                                                $seconds = Date::time_difference_seconds($exit_datetime, $enter_datetime, true);
                                                $data['report']['summary']['total_duration']['value'] += $seconds;

                                                // create a total time duration record for this vehicle in this landmark
                                                if (! isset($data['units'][$current_landmark]['summary'][$u['unit_id']])) {
                                                    $data['units'][$current_landmark]['summary'][$u['unit_id']] = array(
                                                        'label' => $u['unitname'],
                                                        'value' => 0
                                                    );
                                                }

                                                $data['units'][$current_landmark]['summary'][$u['unit_id']]['value'] += $seconds;
                                            }

                                            // set the enter datetime for the new landmark
                                            if (isset($data['units'][$landmark_id])) {
	
	                                            $enter_datetime = Date::utc_to_locale($e['unittime'], $params['user_timezone'], 'm/d/Y h:i A');
	                                            $exit_datetime = Date::utc_to_locale(date('Y-m-d H:i:s',time()), $params['user_timezone'], 'm/d/Y h:i A');
	                                            
                                                $data['units'][$landmark_id]['report_data'][] = array(
                                                    'territoryname'         => $data['units'][$landmark_id]['report_title'],    // this column is for csv export
                                                    'unitname'              => $u['unitname'],
                                                    'enter_datetime'        => Date::utc_to_locale($e['unittime'], $params['user_timezone'], 'm/d/Y h:i A'),
                                                    'exit_datetime'         => 'In Landmark',
                                                    'duration'              => Date::datetime_to_timespan($exit_datetime, $enter_datetime, true),
                                                    'enter_datetime_format' => Date::utc_to_locale($e['unittime'], $params['user_timezone'], 'Y-m-d H:i:s'),
                                                    'exit_datetime_format'  => ''
                                                );

                                                // add vehicle to the array of vehicles that have entered a landmark
                                                if (! isset($vehicles_entered[$u['unit_id']])) {
                                                    $vehicles_entered[$u['unit_id']] = $u;
                                                }

                                                // increment the number of times a vehicle has entered a landmark
                                                $data['report']['summary']['landmarks_entered']['value'] += 1;

                                                // create a total time duration record for this vehicle in this landmark
                                                if (! isset($data['units'][$landmark_id]['summary'][$u['unit_id']])) {
                                                    $data['units'][$landmark_id]['summary'][$u['unit_id']] = array(
                                                        'label' => $u['unitname'],
                                                        'value' => strtotime($exit_datetime) - strtotime($enter_datetime)
                                                    );
                                                }
                                            }
                                        }
                                    } else {                                        // else if not in a landmark, set the datetime it entered the new landmark

                                        // set the enter datetime for the landmark
                                        if (isset($data['units'][$landmark_id])) {
	
                                            $enter_datetime = Date::utc_to_locale($e['unittime'], $params['user_timezone'], 'm/d/Y h:i A');
                                            $exit_datetime = Date::utc_to_locale(date('Y-m-d H:i:s',time()), $params['user_timezone'], 'm/d/Y h:i A');
                                            
                                            $data['units'][$landmark_id]['report_data'][] = array(
                                                'territoryname'         => $data['units'][$landmark_id]['report_title'],    // this column is for csv export
                                                'unitname'              => $u['unitname'],
                                                'enter_datetime'        => Date::utc_to_locale($e['unittime'], $params['user_timezone'], 'm/d/Y h:i A'),
                                                'exit_datetime'         => 'In Landmark',
                                                'duration'              => Date::datetime_to_timespan($exit_datetime, $enter_datetime, true),
                                                'enter_datetime_format' => Date::utc_to_locale($e['unittime'], $params['user_timezone'], 'Y-m-d H:i:s'),
                                                'exit_datetime_format'  => ''
                                            );

                                            // add vehicle to the array of vehicles that have entered a landmark
                                            if (! isset($vehicles_entered[$u['unit_id']])) {
                                                $vehicles_entered[$u['unit_id']] = $u;
                                            }

                                            // increment the number of times a vehicle has entered a landmark
                                            $data['report']['summary']['landmarks_entered']['value'] += 1;

                                            // create a total time duration record for this vehicle in this landmark
                                            if (! isset($data['units'][$landmark_id]['summary'][$u['unit_id']])) {
                                                $data['units'][$landmark_id]['summary'][$u['unit_id']] = array(
                                                    'label' => $u['unitname'],
                                                    'value' => 'Total Time at Landmark: 0'
                                                );
                                            }
                                        }
                                    }
                                } else {                                            // else exited a landmark

                                    // if currently in a landmark, stamp the end time and calculate the time duration for that landmark
                                    if ($current_landmark != 0) {
                                        if (isset($data['units'][$current_landmark])) {
                                            $index = count($data['units'][$current_landmark]['report_data']) - 1;

                                            $enter_datetime = $data['units'][$current_landmark]['report_data'][$index]['enter_datetime_format'];
                                            $exit_datetime = Date::utc_to_locale($e['unittime'], $params['user_timezone'], 'Y-m-d H:i:s');

                                            $data['units'][$current_landmark]['report_data'][$index]['exit_datetime'] = Date::utc_to_locale($e['unittime'], $params['user_timezone'], 'm/d/Y h:i A');
                                            $data['units'][$current_landmark]['report_data'][$index]['exit_datetime_format'] = $exit_datetime;
                                            $data['units'][$current_landmark]['report_data'][$index]['duration'] = Date::datetime_to_timespan($exit_datetime, $enter_datetime, true);

                                            // get overall time duration in landmarks for all units
                                            $seconds = Date::time_difference_seconds($exit_datetime, $enter_datetime, true);
                                            $data['report']['summary']['total_duration']['value'] += $seconds;

                                            // add vehicle to the array of vehicles that have entered a landmark
                                            if (! isset($vehicles_entered[$u['unit_id']])) {
                                                $vehicles_entered[$u['unit_id']] = $u;
                                            }

                                            // increment the number of times a vehicle has entered a landmark
                                            $data['report']['summary']['landmarks_entered']['value'] += 1;

                                            // create a total time duration record for this vehicle in this landmark
                                            if (! isset($data['units'][$current_landmark]['summary'][$u['unit_id']])) {
                                                $data['units'][$current_landmark]['summary'][$u['unit_id']] = array(
                                                    'label' => $u['unitname'],
                                                    'value' => 0
                                                );
                                            }

                                            $data['units'][$current_landmark]['summary'][$u['unit_id']]['value'] += $seconds;
                                        }
                                    }
                                }

                                $current_landmark = $landmark_id;
                            }


                        }
                    }
                }
            }
        }

        // calculate the total time duration for each unit
        foreach($data['units'] as $id => $territory) {

            $data['units'][$id]['report_title'] = 'Landmark: ' . $data['units'][$id]['report_title'];

            if (! empty($territory['summary'])) {
                foreach($territory['summary'] as $unit_id => $unit) {
                    $seconds = $unit['value'];
                    $data['units'][$id]['summary'][$unit_id]['value'] = '<b>Total Time at Landmark:</b> ' . Date::seconds_to_timespan($seconds);
                }
            }
        }

        $data['report']['summary']['vehicles_entered']['value'] = count($vehicles_entered);
        $data['report']['summary']['total_duration']['value'] = Date::seconds_to_timespan($data['report']['summary']['total_duration']['value']);
        //print_rb($data);
        return $data;
    }

    /**
     * Clean report data and return an array for csv export
     *
     * @param array report_data
     *
     * @return array
     */
    public function formatReportCsv($report_data)
    {
        //print_rb($report_data);

        $csv = array(
            'title'     => $report_data['report']['title'],
            'columns'   => $report_data['report']['columns'],
            'data'      => array()
        );

        // add 'unitname' column for all report except User Command for exporting to CSV
        if ($report_data['report']['report_type'] !== 'User Command' AND ! isset($csv['columns']['unitname'])) {
           $csv['columns'] = array_merge(array('unitname' => 'Vehicle Name'), $csv['columns']);
        }

        // add a coumn for the landmark name for Landmark reports
        if ($report_data['report']['report_type'] == 'Landmark' AND ! isset($csv['columns']['territoryname'])) {
            $csv['columns'] = array_merge(array('territoryname' => 'Landmark'), $csv['columns']);
        }

        $units = $report_data['units'];

        foreach ($units as $index => $unit) {
            if (! empty($unit['report_data'])) {
                foreach ($unit['report_data'] as $data) {
                    $csv['data'][] = $data;
                }
            }
        }

        return $csv;
    }


    /**
     * Get the scheduled reports by filtered paramaters (called via ajax)
     *
     * POST params: filter_type
     * POST params: format
     * POST params: recurance
     * POST params: contactgroup_id
     * POST params: report_type
     * POST params: search_string
     *
     * @return array
     */
    public function getReport($account_id, $user_id, $params)
    {
        if($params['pageCount']<1){
            $params['pageCount']=1;
        }

        $sqlPlaceHolder = array();

        $params['strpos'] = strtolower ( $params['search'] ) ;
        $params['search'] = str_replace ( "'" , "\'" , $params['search'] ) ;

        $report['message'] = 'pid="' . $params['pid'] . '"';                                             
        $report['code'] = 1; 
        $report['pag'] = $params['pag'];
        $report['pid'] = $params['pid'];
        $report['length'] = $params['length'];
        $report['search'] = $params['search'];
        $report['records'] = 0;
        $report['sidebarType'] = $params['sidebarType'];
        $report['sidebarContactMode'] = $params['sidebarContactMode'];
        $report['sidebarContactSingle'] = $params['sidebarContactSingle'];
        $report['sidebarRecurrence'] = $params['sidebarRecurrence'];
        $report['sidebarVehicleStatus'] = str_replace( "undefined" , "" , $params['sidebarVehicleStatus'] );
        $report['mobile'] = $params['mobile'];
        $timezone = $params['user_timezone'];

        if(!($account_id)){
            $account_id = $params['account_id'];
        }
        $sqlPlaceHolder = array($account_id);

        $evenOdd = 'report-even-odd ';

        $report['code'] = 0; 

        if(($account_id)&&($user_id)){
            //
            // ROLES
            //
            $buf = array($account_id, $user_id);
            $sql = "SELECT roles FROM crossbones.user WHERE account_id = ? AND user_id = ?";
            $res = $this->report_data->getReport($sql, $buf);
            switch($res[0]['roles']){
                case 'ROLE_ACCOUNT_OWNER' : $role_account_owner = 1;
                                            break;
            }
            //
            // REAL TIME Permissions Settings FIX
            //
            $sql = "SELECT p.label,
                    p.object,
                    p.action,
                    u.roles
                    FROM crossbones.user u 
                    LEFT JOIN crossbones.usertype ut ON ut.usertype_id = u.usertype_id
                    LEFT JOIN crossbones.usertype_permission utp ON utp.usertype_id = u.usertype_id
                    LEFT JOIN crossbones.permission p ON p.permission_id = utp.permission_id
                    WHERE u.account_id = ?
                    AND u.user_id = ?
                    AND ut.active = ? 
                    ORDER BY p.sortorder, p.object, p.label";
            $res = $this->report_data->getReport($sql, array($account_id,$user_id,1));
            $access = null;
            foreach ( $res as $key => $val ) {
                $access[$val['object']][$val['action']] = $val['label'];
            }
            $this->access = $access;

        }

        $tonight = date('Y-m-d 23:59:59' , 
            strtotime(
                date('Y-m-d 23:59:59',
                    strtotime(
                        $this->base_logic->timezoneDelta($timezone,date('Y-m-d H:i:s'),1)
                    )
                )
            )
        ) ;
        $tonightUtc = date('Y-m-d H:i:s' , strtotime($this->base_logic->timezoneDelta($timezone,$tonight))) ;
        // $tonightUtc = Date::utc_to_locale($tonight, $timezone, 'h:i A m/d/Y');
                                                        
        if($params['unit_id']){
            $sql = "SELECT db FROM crossbones.unit WHERE unit_id = ? ORDER BY unit_id ASC LIMIT 1";
            $rows = $this->report_data->getReport($sql, array($params['unit_id']));
            $params['db'] = $rows[0]['db'] ;
        } else {
            // foreach ( $params['unitIds'] as $key => $val ){
            //   $sql = "SELECT db FROM crossbones.unit WHERE unit_id = ? ORDER BY unit_id ASC LIMIT 1";
            //   $rows = $this->report_data->getReport($sql, array($val));
            //   $params['db'] = $rows[0]['db'] ;
            // }
        }
        if (!($params['db'])){
            $params['db'] = 'unitevent1' ;
        }

// $report['code'] = 0;
// // $report['message'] = date('Y-m-d 23:59:59') ;
// // $report['message'] .= ':' ;
// // $report['message'] .= $tonight ;
// // $report['message'] .= ':' ;
// // $report['message'] .= $tonightUtc ;
// $report['thead'] = '<tr><th class="tiniwidth">'.$params['pid'].'</th></tr>';
// return $report;
// exit();

                                                                                                                                                                                            
        switch ($params['pid']){

            case            'alert-history-table' : $report['message'] = '&nbsp;<p>';
                                                    if($params['search']){
                                                        $search = ' AND ( ah.streetaddress LIKE \'' . $params['search'] . '%\' OR ah.streetaddress LIKE \'%' . $params['search'] . '%\' OR ah.city LIKE \'' . $params['search'] . '%\' OR ah.city LIKE \'%' . $params['search'] . '%\' OR ah.state LIKE \'' . $params['search'] . '%\' OR ah.state LIKE \'%' . $params['search'] . '%\' OR ah.zipcode LIKE \'' . $params['search'] . '%\' OR ah.zipcode LIKE \'%' . $params['search'] . '%\' OR a.alertname LIKE \'' . $params['search'] . '%\' OR a.alertname LIKE \'%' . $params['search'] . '%\' OR at.alerttypename LIKE \'' . $params['search'] . '%\' OR at.alerttypename LIKE \'%' . $params['search'] . '%\' OR c.firstname LIKE \'' . $params['search'] . '%\' OR c.firstname LIKE \'%' . $params['search'] . '%\' OR c.lastname LIKE \'' . $params['search'] . '%\' OR c.lastname LIKE \'%' . $params['search'] . '%\' OR c.email LIKE \'' . $params['search'] . '%\' OR c.email LIKE \'' . $params['search'] . '%\' OR a.alertname LIKE \'' . $params['search'] . '%\' OR a.alertname LIKE \'%' . $params['search'] . '%\' OR cg.contactgroupname LIKE \'' . $params['search'] . '%\' OR cg.contactgroupname LIKE \'%' . $params['search'] . '%\' OR  ug.unitgroupname LIKE \'' . $params['search'] . '%\' OR  ug.unitgroupname LIKE \'%' . $params['search'] . '%\' )' ;
                                                        // $sqlPlaceHolder[] = $params['sidebarContactMethod'];
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarAlertAlert']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $alertalert = ' AND a.alert_id = ?' ;
                                                                                    $sqlPlaceHolder[] = $params['sidebarAlertAlert'];
                                                                                    $report['message'] .= ' Alert Type "' . $params['sidebarAlertAlert'] . '" Not Found<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarAlertType']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $alerttype = ' AND a.alerttype_id = ?' ;
                                                                                    $sqlPlaceHolder[] = $params['sidebarAlertType'];
                                                                                    $report['message'] .= ' Alert Type "' . $params['sidebarAlertType'] . '" Not Found<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarContactGroup']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $contactgroup = ' AND cg.contactgroup_id = ?' ;
                                                                                    $sqlPlaceHolder[] = $params['sidebarContactGroup'];
                                                                                    $report['message'] .= ' Contact Group "' . $params['sidebarContactGroup'] . '" Not Found<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarTriggeredLast']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $triggered = ' AND ah.uniteventdate >= DATE_SUB(?, INTERVAL ? DAY)' ;
                                                                                    $sqlPlaceHolder[] = $tonightUtc;
                                                                                    $sqlPlaceHolder[] = $params['sidebarTriggeredLast'];
                                                                                    $report['message'] .= ' Days Since Last Event Trigger: "' . $params['sidebarTriggeredLast'] . '"<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarVehicleGroup']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $vehiclegroup = ' AND u.unitgroup_id = ?' ;
                                                                                    $sqlPlaceHolder[] = $params['sidebarVehicleGroup'];
                                                                                    $report['message'] .= ' Vehicle Group Id "' . $params['sidebarVehicleGroup'] . '" Not Found<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    $sql = "SELECT
                                                                a.alert_id as alert_id,
                                                                a.alertname as alertname,
                                                                ac.contact_id as contact_id,
                                                                ac.contactgroup_id as contactgroup_id,
                                                                ac.mode as contact_mode,
                                                                au.unit_id as unit_id,
                                                                au.unitgroup_id as unitgroup_id,
                                                                au.mode as unit_mode,
                                                                ah.*,
                                                                at.alerttypename as alerttypename,
                                                                c.firstname as firstname,
                                                                c.lastname as lastname,
                                                                c.email as email,
                                                                cg.contactgroupname as contactgroupname,
                                                                u.db as db,
                                                                u.unitgroup_id as unitgroup_id,
                                                                u.unitname as unitname,
                                                                ug.unitgroupname as unitgroupname
                                                            FROM crossbones.alerthistory ah
                                                            LEFT JOIN crossbones.alert a ON a.alert_id = ah.alert_id
                                                            LEFT JOIN crossbones.alerttype at ON at.alerttype_id = a.alerttype_id
                                                            LEFT JOIN crossbones.alert_contact ac ON ac.alert_id = ah.alert_id
                                                            LEFT JOIN crossbones.alert_unit au ON au.alert_id = ah.alert_id
                                                            LEFT JOIN crossbones.contact c ON c.contact_id = ac.contact_id
                                                            LEFT JOIN crossbones.contactgroup cg ON cg.contactgroup_id = ac.contactgroup_id
                                                            LEFT JOIN crossbones.unit u ON u.unit_id = ah.unit_id
                                                            LEFT JOIN crossbones.unitgroup ug ON ug.unitgroup_id = au.unitgroup_id
                                                            WHERE u.account_id = ?"
                                                            . $search
                                                            . $alertalert
                                                            . $alerttype
                                                            . $contactgroup
                                                            . $triggered
                                                            . $vehiclegroup
                                                            . " ORDER BY ah.uniteventdate DESC";

                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);
// $report['message'] = $sql . ' - ' . implode(', ', $sqlPlaceHolder);
                                                    
                                                    //$report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Name</th><th>Alert Type</th><th>Vehicles</th><th>Contacts</th><th>Last Event</th></tr>';
                                                    // $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Id</th><th>Device Log</th><th>Network Log</th><th>Alert Type</th><th>Alert Name</th><th>Address</th><th>Contact</th><th>Vehicle</th></tr>';
                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Device Date & Time</th><th>Processed Date & Time</th><th>Alert Type</th><th>Alert Name</th><th>Address</th><th>Vehicle</th><th>Contacts</th></tr>';
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        $report['code'] = 0; 
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){
                                                            $onClick="Core.DataTable.pop('modal-edit-scheduled-report','modal-title=" . str_replace("'", "\'", $row['reporttypename']) . ";','modal-edit-scheduled-report-name=" . str_replace("'", "\'", $row['schedulereportname']) . ";','scheduled-recurrence=" . str_replace("'", "\'", $row['schedule']) . ";scheduled-day=" . str_replace("'", "\'", $row['scheduleday']) . ";scheduled-monthly=" . str_replace("'", "\'", $row['monthday']) . ";scheduled-time=" . str_replace("'", "\'", $row['sendhour']) . ";scheduled-format=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-mode=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-single=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-group=" . str_replace("'", "\'", $row['format']) . ";');";
                                                            $territory = NULL ;
                                                            if($row['territoryname']){
                                                                $territory = '(' . $row['territoryname'] . ') &nbsp; ' ;
                                                            }
                                                            $address = $this->address_logic->validateAddress($row['streetaddress'], $row['city'], $row['state'], $row['zipcode'], $row['country']);
                                                            $label = str_replace('"','\"',$address);
                                                            $sql = "SELECT longitude, latitude 
                                                                    FROM " . $row['db'] . ".unit" . $row['unit_id'] . " 
                                                                    WHERE streetaddress = ? AND city = ? AND state = ? AND zipcode = ? 
                                                                    LIMIT 1";
                                                            $report['message'] = $sql;
                                                            $ll = $this->report_data->getReport($sql, array($row['streetaddress'], $row['city'], $row['state'], $row['zipcode']));
                                                            
                                                            if($row['contactgroup_id']){
                                                                $contact = $row['contactgroupname'];
                                                                $contactmode = 'Group';
                                                            } else if($row['contact_id']){
                                                                $contact = $row['firstname'] . ' ' . $row['lastname'];
                                                                $contactmode = 'Contact';
                                                            }

                                                            switch($row['unit_mode']){

                                                                        case  '1' : $unit = $row['unitgroupname'];
                                                                                    $unitmode = 'Group';
                                                                                    break;

                                                                        case  '2' : $unit = $row['unitname'];
                                                                                    $unitmode = 'Vehicle';
                                                                                    break;

                                                                          default : if($row['unitgroup_id']){
                                                                                        $unit = $row['unitgroupname'];
                                                                                        $unitmode = 'Group';
                                                                                    } else {
                                                                                        $unit = $row['unitname'];
                                                                                        $unitmode = 'Vehicle';
                                                                                    }

                                                            }

                                                            if(!($contact)){ 
                                                                $contact = '<span class="text-grey-10">Report&nbsp;Only</span>' ; 
                                                            }
                                                            
                                                            if(!($unit)){ 
                                                                $unit = '<span class="text-grey-10">Not Set</span>' ;
                                                                $unit = $row['unitname'];
                                                                $unitmode = 'Vehicle';
                                                            }

                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }
                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                // . '<td class="' . $evenOdd . '">' . $row['alert_id'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . date('M d, Y h:i a' , strtotime($this->base_logic->tzUtc2Local($timezone,$row['uniteventdate'])))
                                                                . '<td class="' . $evenOdd . '">' . date('M d, Y h:i a' , strtotime($this->base_logic->tzUtc2Local($timezone,$row['alerthistorydate'])))
                                                                . '<td class="' . $evenOdd . '">' . $row['alerttypename'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['alertname'] . '</td>'
                                                                . '<td class="' . $evenOdd . 'address_map_link" data-eventname="' . $row['alertname'] . '" data-name="' . $row['alertname'] . ' - ' . $row['territoryname'] . '" data-id="' . $row['alert_id'] . '" data-latitude="' . $ll[0]['latitude'] . '" data-longitude="' . $ll[0]['longitude'] . '" data-label="' . $label . '" title="' . $row['territoryname'] . '">' . $territory . '&nbsp;<a href="#">' . $address . '</a></td>'
                                                                // . '<td class="' . $evenOdd . '">' . $contact . '<br><span class="text-grey-10">' . $contactmode . '</span></td>'
                                                                // . '<td class="' . $evenOdd . '">' . $unit . '<br><span class="text-grey-10">' . $unitmode . '</span></td>'
                                                                . '<td class="' . $evenOdd . '">' . $unit . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $contact . '</td>'
                                                                . '</tr>'; 
                                                        }
                                                    }
                                                    if(!($report['tbody'])){
                                                        $report['tbody'] = '<tr><td colspan="9"><i>No Data Found</i></td></tr>';
                                                    } else {
                                                        $report['lastReport'] = $this->base_logic->wizardMapAllLink();
                                                    }
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    break;

            case               'alert-list-table' : $report['message'] = '&nbsp;<p>';
                                                    if($params['search']){
                                                        $search = ' AND ( cg.contactgroupname LIKE \'' . $params['search'] . '%\' OR cg.contactgroupname LIKE \'%' . $params['search'] . '%\' OR ug.unitgroupname LIKE \'' . $params['search'] . '%\' OR ug.unitgroupname LIKE \'%' . $params['search'] . '%\' OR c.firstname LIKE \'' . $params['search'] . '%\' OR c.firstname LIKE \'%' . $params['search'] . '%\' OR c.lastname LIKE \'' . $params['search'] . '%\' OR c.lastname LIKE \'%' . $params['search'] . '%\' OR c.email LIKE \'' . $params['search'] . '%\' OR c.email LIKE \'' . $params['search'] . '%\' OR a.alertname LIKE \'' . $params['search'] . '%\' OR a.alertname LIKE \'%' . $params['search'] . '%\' OR at.alerttypename LIKE \'' . $params['search'] . '%\' OR at.alerttypename LIKE \'%' . $params['search'] . '%\' )' ;
                                                        // $sqlPlaceHolder[] = $params['sidebarContactMethod'];
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarAlertType']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $alerttype = ' AND a.alerttype_id = ?' ;
                                                                                    $sqlPlaceHolder[] = $params['sidebarAlertType'];
                                                                                    $report['message'] .= ' Alert Type "' . $params['sidebarAlertType'] . '" Not Found<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarContactGroup']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $contactgroup = ' AND cgc.contactgroup_id = ?' ;
                                                                                    $sqlPlaceHolder[] = $params['sidebarContactGroup'];
                                                                                    $report['message'] .= ' Contact Group "' . $params['sidebarContactGroup'] . '" Not Found<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarVehicleGroup']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $vehiclegroup = ' AND ug.unitgroup_id = ?' ;
                                                                                    $sqlPlaceHolder[] = $params['sidebarVehicleGroup'];
                                                                                    $report['message'] .= ' Vehicle Group "' . $params['sidebarVehicleGroup'] . '" Not Found<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    $sql = "SELECT
                                                                a.*,
                                                                a.alert_id as alert_id,
                                                                ac.contact_id as contact_id,
                                                                ac.contactgroup_id as contactgroup_id,
                                                                ac.mode as contact_mode,
                                                                at.alerttypename as alerttypename,
                                                                au.mode as unit_mode,
                                                                a_t.territory_id as territory_id,
                                                                a_t.territorygroup_id as territorygroup_id,
                                                                au.unit_id as unit_id,
                                                                au.unitgroup_id as unitgroup_id,
                                                                c.firstname as firstname,
                                                                c.lastname as lastname,
                                                                c.email as email,
                                                                cg.contactgroupname as contactgroupname,
                                                                t.territoryname as territoryname,
                                                                tg.territorygroupname as territorygroupname,
                                                                u.unitname as unitname,
                                                                ug.unitgroupname as unitgroupname
                                                            FROM crossbones.alert a
                                                            LEFT JOIN crossbones.alerttype at ON at.alerttype_id = a.alerttype_id
                                                            LEFT JOIN crossbones.alert_contact ac ON ac.alert_id = a.alert_id
                                                            LEFT JOIN crossbones.alert_territory a_t ON a_t.alert_id = a.alert_id
                                                            LEFT JOIN crossbones.territory t ON t.territory_id = a_t.territory_id
                                                            LEFT JOIN crossbones.territorygroup tg ON tg.territorygroup_id = a_t.territorygroup_id
                                                            LEFT JOIN crossbones.alert_unit au ON au.alert_id = a.alert_id
                                                            LEFT JOIN crossbones.contact c ON c.contact_id = ac.contact_id
                                                            LEFT JOIN crossbones.contactgroup cg ON cg.contactgroup_id = ac.contactgroup_id
                                                            LEFT JOIN crossbones.unit u ON u.unit_id = au.unit_id
                                                            LEFT JOIN crossbones.unitgroup ug ON ug.unitgroup_id = au.unitgroup_id
                                                            WHERE a.account_id = ?
                                                            AND a.active = '1'"
                                                            . $search
                                                            . $alerttype
                                                            . $contactgroup
                                                            . $vehiclegroup
                                                            . " ORDER BY a.alertname ASC";

                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    
                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Name</th><th>Alert Type</th><th>Vehicles</th><th>Landmarks</th><th>Contacts</th><th>Last Triggered</th><th class="tinywidth">Delete</th></tr>';
                                                    if(!($rows)){$report['tbody'] = '<tr><td colspan="5"><i>No Data Found</i></td></tr>';}
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        $report['code'] = 0; 
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){
                                                            $onClick="Core.DataTable.pop('modal-edit-scheduled-report','modal-title=" . str_replace("'", "\'", $row['reporttypename']) . ";','modal-edit-scheduled-report-name=" . str_replace("'", "\'", $row['schedulereportname']) . ";','scheduled-recurrence=" . str_replace("'", "\'", $row['schedule']) . ";scheduled-day=" . str_replace("'", "\'", $row['scheduleday']) . ";scheduled-monthly=" . str_replace("'", "\'", $row['monthday']) . ";scheduled-time=" . str_replace("'", "\'", $row['sendhour']) . ";scheduled-format=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-mode=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-single=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-group=" . str_replace("'", "\'", $row['format']) . ";');";
                                                            $vehicle = 'n/a :' . $row['alert_id'] . '=' . $row['unit_id'] . '=' . $row['unitgroup_id'] ;
                                                            if($row['unit_id']>0){
                                                                $vehicle = $row['unitname'] . '(v)' ;
                                                            } else if($row['unitgroup_id']>0){
                                                                $vehicle = $row['unitgroupname'] . '(g)' ;
                                                            }

                                                            if($row['contact_id']){
                                                                $contact = '<td#_#>' . $row['firstname'] . ' ' . $row['lastname'] . '</td>';
                                                            } else if($row['contactgroup_id']){
                                                                $contact = '<td#_#>' . $row['contactgroupname'] . '&nbsp;<span class="text-grey text-10">Group</span></td>';
                                                            } else {
                                                                $contact = '<td#_#>All Contacts</td>'; 
                                                            }

                                                            $landmarktrigger = '<td#_#>' . $this->base_logic->wizardSelect($params['pid'],$report['records'],'alertlandmarktrigger',$row['alert_id'],$row['landmarktrigger'],'refreshReportOnChange') . ' <span class="text-grey-8">Vehicle Mode</span></td>';
                                                            
                                                            if($row['territory_id']){
                                                                $landmark = '<td#_#>' . $row['territoryname'] . '</td>';
                                                            } else if($row['territorygroup_id']){
                                                                $landmark = '<td#_#>' . $row['territorygroupname'] . '&nbsp;<span class="text-grey text-10">Group</span></td>';
                                                            } else {
                                                                $landmark = '<td#_#>All Landmarks</td>'; 
                                                            }

                                                            if($row['unit_id']){
                                                                $unit = '<td#_#>' . $row['unitname'] . '</td>';
                                                            } else if($row['unitgroup_id']){
                                                                $unit = '<td#_#>' . $row['unitgroupname'] . '&nbsp;<span class="text-grey text-10">Group</span></td>';
                                                            } else {
                                                                $unit = '<td#_#>All Vehicles</td>'; 
                                                            }

                                                            if((!($contact))||($contact=='<td#_#> </td>')){ $contact = '<td#_#><span class="text-grey text-10">Not Set</span></td>' ; }
                                                            if(!($landmark)){ $landmark = '<td#_#><span class="text-grey text-10">Not Set</span></td>' ; }
                                                            if(!($unit)){ $unit = '<td#_#><span class="text-grey text-10">Not Set</span></td>' ; }

                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }

                                                            switch($row['alerttype_id']){
                                                                case                 3  :
                                                                case                '3' : break; 
                                                                                default : $landmark = '<td#_#>n/a</td>';
                                                            }

                                                            $report['tbody'] .= str_replace('#_#',' class="' . $evenOdd . '"','<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '"><a class="alert-edit" href="javascript:void(0);" id="alert-list-table-' . $row['alert_id'] . '" onclick="Core.ClearForm(\'alert-edit\',\'' . str_replace("'","\'",$row['alertname']) . '\',\'' . $row['alert_id'] . '\');">' . $row['alertname'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['alerttypename'] .'</td>'
                                                                . $unit
                                                                . $landmark
                                                                . $contact
                                                                . '<td class="' . $evenOdd . '">' . date('M d, Y h:i a' , strtotime($this->base_logic->tzUtc2Local ( $timezone , $row['updated'] ))) . '</td>' // this is it
                                                                . '<td class="' . $evenOdd . '">' . $this->base_logic->wizardDeleteRecord('alert',$params['pid'],$report['records'],$row['alert_id']) . '</td>'
                                                                . '</tr>'); 


                                                        }
                                                    }
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    break;

            case            'batch-command-table' : $report['message'] = '&nbsp;<p>';
                                                    if($params['search']){
                                                        // $search = ' AND ( cg.contactgroupname LIKE \'' . $params['search'] . '%\' OR cg.contactgroupname LIKE \'%' . $params['search'] . '%\' OR ug.unitgroupname LIKE \'' . $params['search'] . '%\' OR ug.unitgroupname LIKE \'%' . $params['search'] . '%\' OR c.firstname LIKE \'' . $params['search'] . '%\' OR c.firstname LIKE \'%' . $params['search'] . '%\' OR c.lastname LIKE \'' . $params['search'] . '%\' OR c.lastname LIKE \'%' . $params['search'] . '%\' OR c.email LIKE \'' . $params['search'] . '%\' OR c.email LIKE \'' . $params['search'] . '%\' OR a.alertname LIKE \'' . $params['search'] . '%\' OR a.alertname LIKE \'%' . $params['search'] . '%\' OR at.alerttypename LIKE \'' . $params['search'] . '%\' OR at.alerttypename LIKE \'%' . $params['search'] . '%\' )' ;
                                                        // $sqlPlaceHolder[] = $params['sidebarContactMethod'];
                                                        // $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0; 
                                                    }
                                                    $sql = "SELECT
                                                                cq.*,
                                                                a.account_id as account_id,
                                                                bc.batchcommand as batchcommand,
                                                                u.unitname as unitname,
                                                                user.username as username
                                                            FROM crossbones.commandqueue cq
                                                            LEFT JOIN crossbones.batchcommand bc ON bc.batchcommand_id = cq.batchcommand_id
                                                            LEFT JOIN crossbones.unit u ON u.unit_id = cq.unit_id
                                                            LEFT JOIN crossbones.user user ON user.user_id = cq.user_id
                                                            LEFT JOIN crossbones.account a ON a.account_id = u.account_id
                                                            WHERE a.account_id = ?
                                                            AND u.unitstatus_id > 0
                                                            AND u.unitstatus_id < 4"
                                                            . $search
                                                            . " ORDER BY cq.status_id ASC , cq.updated DESC , cq.createdate DESC , u.unitname ASC";
                                                    $queue = $this->report_data->getReport($sql, $sqlPlaceHolder);

                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Vehicle</th><th>Command</th><th>Status</th><th>User</th><th>Created</th><th>Updated</th></tr>';
                                                    if((!($rows))&&(!($queue))){$report['tbody'] = '<tr><td colspan="5"><i>No Data Found</i></td></tr>';}
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($queue as $key => $row) {
                                                        $report['code'] = 0; 
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){
                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }
                                                            switch($row['status_id']){
                                                                case              1  :
                                                                case             '1' : $status = 'SENT' ;
                                                                                       break;
                                                                             default : $status = 'QUEUED' ;
                                                            }
                                                            $report['tbody'] .= str_replace('#_#',' class="' . $evenOdd . '"','<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['unitname'] .'</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['batchcommand'] .'</td>'
                                                                . '<td class="' . $evenOdd . '">' . $status . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['username'] .'</td>'
                                                                . '<td class="' . $evenOdd . '">' . date('M d, Y h:i a' , strtotime($this->base_logic->tzUtc2Local ( $timezone , $row['createdate'] ))) . '</td>' // this is it
                                                                . '<td class="' . $evenOdd . '">' . date('M d, Y h:i a' , strtotime($this->base_logic->tzUtc2Local ( $timezone , $row['updated'] ))) . '</td>' // this is it
                                                                . '</tr>'); 
                                                        }
                                                    }
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    break;

            case              'batch-queue-table' : $report['message'] = '&nbsp;<p>';
                                                    if($params['search']){
                                                        // $search = ' AND ( cg.contactgroupname LIKE \'' . $params['search'] . '%\' OR cg.contactgroupname LIKE \'%' . $params['search'] . '%\' OR ug.unitgroupname LIKE \'' . $params['search'] . '%\' OR ug.unitgroupname LIKE \'%' . $params['search'] . '%\' OR c.firstname LIKE \'' . $params['search'] . '%\' OR c.firstname LIKE \'%' . $params['search'] . '%\' OR c.lastname LIKE \'' . $params['search'] . '%\' OR c.lastname LIKE \'%' . $params['search'] . '%\' OR c.email LIKE \'' . $params['search'] . '%\' OR c.email LIKE \'' . $params['search'] . '%\' OR a.alertname LIKE \'' . $params['search'] . '%\' OR a.alertname LIKE \'%' . $params['search'] . '%\' OR at.alerttypename LIKE \'' . $params['search'] . '%\' OR at.alerttypename LIKE \'%' . $params['search'] . '%\' )' ;
                                                        // $sqlPlaceHolder[] = $params['sidebarContactMethod'];
                                                        // $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0; 
                                                    }
                                                    $sql = "SELECT
                                                                cp.*,
                                                                a.account_id as account_id,
                                                                u.unitname as unitname,
                                                                user.username as username
                                                            FROM crossbones.commandpending cp
                                                            LEFT JOIN crossbones.unit u ON u.unit_id = cp.unit_id
                                                            LEFT JOIN crossbones.user user ON user.user_id = cp.user_id
                                                            LEFT JOIN crossbones.account a ON a.account_id = u.account_id
                                                            WHERE cp.active < 1
                                                            AND a.account_id = ?
                                                            AND u.unitstatus_id > 0
                                                            AND u.unitstatus_id < 4"
                                                            . $search
                                                            . " ORDER BY u.unitname ASC";
                                                    $queue = $this->report_data->getReport($sql, $sqlPlaceHolder);

                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Vehicle</th><th>Reminder Off</th><th>Reminder On</th><th>Starter Enable</th><th>Starter Disable</th><th>User</th><th>Updated</th><th class="tinywidth">Delete</th></tr>';
                                                    if((!($rows))&&(!($queue))){$report['tbody'] = '<tr><td colspan="5"><i>No Data Found</i></td></tr>';}
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($queue as $key => $row) {
                                                        $report['code'] = 0; 
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){
                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }
                                                            $reminder_off_processed = ' text-red';
                                                            $reminder_on_processed = ' text-red';
                                                            $starter_enable_processed = ' text-red';
                                                            $starter_disable_processed = ' text-red';
                                                            if($row['reminder_off_processed']!='0000-00-00 00:00:00'){
                                                                $reminder_off_processed = ' text-green';
                                                            }
                                                            if($row['reminder_on_processed']!='0000-00-00 00:00:00'){
                                                                $reminder_on_processed = ' text-green';
                                                            }
                                                            if($row['starter_enable_processed']!='0000-00-00 00:00:00'){
                                                                $starter_enable_processed = ' text-green';
                                                            }
                                                            if($row['starter_disable_processed']!='0000-00-00 00:00:00'){
                                                                $starter_disable_processed = ' text-green';
                                                            }
                                                            $report['tbody'] .= str_replace('#_#',' class="' . $evenOdd . '"','<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['unitname'] .'</td>'
                                                                . '<td class="' . $evenOdd . $reminder_off_processed . '">' . $row['reminder_off'] .'</td>'
                                                                . '<td class="' . $evenOdd . $reminder_on_processed . '">' . $row['reminder_on'] .'</td>'
                                                                . '<td class="' . $evenOdd . $starter_enable_processed . '">' . $row['starter_enable'] .'</td>'
                                                                . '<td class="' . $evenOdd . $starter_disable_processed . '">' . $row['starter_disable'] .'</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['username'] .'</td>'
                                                                . '<td class="' . $evenOdd . '">' . date('M d, Y h:i a' , strtotime($this->base_logic->tzUtc2Local ( $timezone , $row['updated'] ))) . '</td>' // this is it
                                                                . '<td class="' . $evenOdd . '">' . $this->base_logic->wizardDeleteRecord('commandpending',$params['pid'],$report['records'],$row['commandpending_id']) . '</td>'
                                                                . '</tr>'); 
                                                        }
                                                    }
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    break;

            case        'contacts-contacts-table' : $report['message'] = '&nbsp;<p>';
                                                    $sqlPlaceHolder[] = '0'; 
                                                    if($params['search']){
                                                        $search = ' AND ( c.cellnumber LIKE \'' . $params['search'] . '%\' OR c.cellnumber LIKE \'%' . $params['search'] . '%\' OR cc.gateway LIKE \'' . $params['search'] . '%\' OR cc.gateway LIKE \'%' . $params['search'] . '%\' OR c.firstname LIKE \'' . $params['search'] . '%\' OR c.firstname LIKE \'%' . $params['search'] . '%\' OR c.lastname LIKE \'' . $params['search'] . '%\' OR c.lastname LIKE \'%' . $params['search'] . '%\' OR c.email LIKE \'' . $params['search'] . '%\' OR c.email LIKE \'%' . $params['search'] . '%\' OR cg.contactgroupname LIKE \'' . $params['search'] . '%\' OR cg.contactgroupname LIKE \'%' . $params['search'] . '%\' OR cgc.method LIKE \'' . $params['search'] . '%\' OR cgc.method LIKE \'%' . $params['search'] . '%\' )' ;
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0; 
                                                    }
                                                    // switch($params['sidebarContactMethod']){  //////////////////////////// HANDLED BELOW IN FOREACH LOOP
                                                    //     case                   '' :
                                                    //     case                'all' : break;
                                                    //                       default : $method = ' AND cgc.method = ?' ;
                                                    //                                 $sqlPlaceHolder[] = $params['sidebarContactMethod'];
                                                    //                                 $report['message'] .= ' Method "' . $params['sidebarContactMethod'] . '" Not Found<p>';
                                                    //                                 $report['code'] = 0; 
                                                    // }
                                                    switch($params['sidebarContactGroup']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $contactgroup = ' AND cgc.contactgroup_id = ?' ;
                                                                                    $sqlPlaceHolder[] = $params['sidebarContactGroup'];
                                                                                    $report['message'] .= ' Contact Group "' . $params['sidebarContactGroup'] . '" Not Found<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    $sql = "SELECT
                                                                c.*,
                                                                c.cellnumber as cellnumber,
                                                                c.cellcarrier_id as cellcarrier_id,
                                                                c.email as email,
                                                                c.contact_id as contact_id,
                                                                cgc.method as method,
                                                                cgc.contactgroup_id as contactgroup_id,
                                                                cg.contactgroupname as contactgroupname,
                                                                cc.gateway as gateway
                                                            FROM crossbones.contact c
                                                            LEFT JOIN crossbones.contactgroup_contact cgc ON cgc.contact_id = c.contact_id
                                                            LEFT JOIN crossbones.contactgroup cg ON cg.contactgroup_id = cgc.contactgroup_id
                                                            LEFT JOIN crossbones.cellcarrier cc ON cc.cellcarrier_id = c.cellcarrier_id
                                                            WHERE c.account_id = ?
                                                            AND c.active = ?"
                                                            . $search
                                                            . $contactgroup
                                                            . " ORDER BY c.firstname ASC, c.lastname ASC";

                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    
                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Name</th><th>Group</th><th>Contact Method</th><th>Details</th><th class="tinywidth">Delete</th></tr>';
                                                    $page=1;
                                                    foreach ($rows as $key => $row) {
                                                        $report['records']++;
                                                        $report['code'] = 0; 
                                                        if(strtolower($row['method'])!='all'){
                                                            if ( ($row['email']) && (strtolower($row['method']) == 'sms') ) { $row['method'] = 'all' ; }
                                                            else if ( ($row['email']) && (!($row['method'])) ) { $row['method'] = 'email' ; }
                                                            else if ( ($row['cellnumber']) && (strtolower($row['method']) == 'email' ) ) { $row['method'] = 'all' ; }
                                                            else if ( ($row['cellnumber']) && (!($row['method'])) ) { $row['method'] = 'sms' ; }
                                                        }
                                                        if ( (!($row['email'])) && (!($row['cellnumber'])) ) {
                                                            $row['method'] = '' ; 
                                                        }
                                                        if ( ($params['sidebarContactMethod'] === 'all') || (strtolower($row['method']) === $params['sidebarContactMethod']) ) {
                                                            if ( $row['contact_id'] != $contact_id ) {
                                                                if ( ( $contact_id ) && ( $last ) ) {
                                                                    $last['contactgroupname'] = implode ( ',&nbsp;&nbsp;&nbsp;' , array_unique($contactgroupname) ) ;
                                                                    $last['method'] = implode ( ',&nbsp;&nbsp;&nbsp;' , array_unique($contactgroupmethod) ) ;
                                                                    // $contactsms[] = '</table>' ;
                                                                    $last['email'] = implode ( ' / ' , array_unique($contactsms) ) ;
                                                                    $frows[] = $last ;
                                                                }
                                                                $buffer_cellnumber = array() ;
                                                                $buffer_email = array() ;
                                                                $buffer_method = array() ;
                                                                $last = array() ;
                                                                $contact_id = $row['contact_id'] ;
                                                                $email = $row['email'] ;
                                                                $contactgroupname = array() ;
                                                                $contactgroupmethod = array() ;
                                                                $contactsms = array() ;
                                                                // $contactsms[] = '<table border="0" cellpadding="0" cellspacing="0">' ;
                                                            }
                                                            if ( ($row['contactgroupname']) && (!($buffer_contactgroupname[$row['contactgroupname']])) ) {
                                                                $buffer_contactgroupname[$row['contactgroupname']] = 1 ; 
                                                                // $contactgroupname[] = $this->base_logic->wizardDelete($row['contactgroupname'],$params['pid'],$report['records'],'crossbones-contact-contactgroup',$row['contact_id'],$row['contactgroup_id']) ;
                                                                $contactgroupname[] = $row['contactgroupname'] ;
                                                            }
                                                            if ( ($row['method']!='email') && ($row['cellnumber']) && ($row['gateway']) ) {
                                                                $buffer_cellnumber[$row['cellnumber']] = 1 ; 
                                                                // $contactsms[] = '<td class="#_#"><span class="text-grey-8">SMS:</span><div class="wizard-div">' . $this->base_logic->wizardInput($params['pid'],$report['records'],'crossbones-contact-cellnumber',$row['contact_id'],$row['cellnumber']) . '</div> <span class="text-grey-8">'  . $row['contactgroupname'] . '</span></td><td class="#_#">@</td><td class="#_#">' . $this->base_logic->wizardSelect($params['pid'],$report['records'],'crossbones-cellcarrier-gateway',$row['contact_id'],$row['gateway']) . '</td>' ;
                                                                $contactsms[] = $row['cellnumber'] . ' ' . $row['gateway'] ;
                                                                if (!($buffer_method['sms'])) {
                                                                    $buffer_method['sms'] = 1 ; 
                                                                    $contactgroupmethod[] = 'SMS' ;
                                                                }
                                                            }
                                                            if ( ($row['method']!='sms') && ($row['email']) ) { 
                                                                $buffer_email[$row['email']] = 1;
                                                                // $contactsms[] = '<td class="#_#" colspan="3"><div class="wizard-div"><span class="text-grey-8">Email:</span>&nbsp;' . $this->base_logic->wizardInput($params['pid'],$report['records'],'crossbones-contact-email',$row['contact_id'],$row['email']) . '<span class="text-grey-8">'  . $row['contactgroupname'] . '</span></div></td>' ;
                                                                $contactsms[] = $row['email'] ;
                                                                if (!($buffer_method['email'])) {
                                                                    $buffer_method['email'] = 1 ; 
                                                                    $contactgroupmethod[] = 'Email' ;
                                                                }
                                                            }
                                                            $last = $row ;
                                                        }
                                                    }
                                                    if ( ( $contact_id ) && ( $last ) ) {
                                                            $last['contactgroupname'] = implode ( ',&nbsp;&nbsp;&nbsp;' , array_unique($contactgroupname) ) ;
                                                            $last['method'] = implode ( ',&nbsp;&nbsp;&nbsp;' , array_unique($contactgroupmethod) ) ;
                                                            // $contactsms[] = '</table>' ;
                                                            $last['email'] = implode ( ' / ' , array_unique($contactsms) ) ;
                                                            $frows[] = $last ;
                                                    }
                                                    $report['records']=0;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($frows as $key => $row) {
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($row['lastruntime']=='0000-00-00 00:00:00'){
                                                            $row['lastruntime'] = $row['createdate'];
                                                        }

                                                        $contact = $this->base_logic->wizardSelect($params['pid'],$report['records'],'alertcontactcontact',$row['alert_id'],$row['firstname'] . ' ' . $row['lastname']);

                                                        if($page==$params['pageCount']){
                                                            $onClick="Core.DataTable.pop('modal-edit-scheduled-report','modal-title=" . str_replace("'", "\'", $row['reporttypename']) . ";','modal-edit-scheduled-report-name=" . str_replace("'", "\'", $row['schedulereportname']) . ";','scheduled-recurrence=" . str_replace("'", "\'", $row['schedule']) . ";scheduled-day=" . str_replace("'", "\'", $row['scheduleday']) . ";scheduled-monthly=" . str_replace("'", "\'", $row['monthday']) . ";scheduled-time=" . str_replace("'", "\'", $row['sendhour']) . ";scheduled-format=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-mode=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-single=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-group=" . str_replace("'", "\'", $row['format']) . ";');";
                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }
                                                            $report['tbody'] .= str_replace('#_#',$evenOdd,'<tr>'
                                                                . '<td class="' . $evenOdd . ' pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</td>'
                                                                . '<td class="' . $evenOdd . '"><a class="edit-contact" href="javascript:void(0);" id="alert-contacts-contacts-' . $row['contact_id'] . '" onclick="Core.ClearForm(\'edit-contact\',\'' . str_replace("'","\'",$row['firstname'] . ' ' . $row['lastname']) . '\',\'' . $row['contact_id'] . '\');">' . $row['firstname'] . ' ' . $row['lastname'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['contactgroupname'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $last['method'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['email'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $this->base_logic->wizardDeleteRecord('contact',$params['pid'],$report['records'],$row['contact_id']) . '</td>'
                                                                . '</tr>'); 
                                                        }
                                                    }
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    if(!($report['tbody'])){$report['tbody'] = '<tr><td colspan="5"><i>No Data Found</i></td></tr>';}
                                                    break;

            case          'contacts-groups-table' : $report['message'] = '&nbsp;<p>';
                                                    $sqlPlaceHolder[] = '0'; 
                                                    if($params['search']){
                                                        $search = ' AND ( cg.contactgroupname LIKE \'' . $params['search'] . '%\' OR cg.contactgroupname LIKE \'%' . $params['search'] . '%\' )' ;
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarContactGroup']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $contactgroup = ' AND cg.contactgroup_id = ?' ;
                                                                                    $sqlPlaceHolder[] = $params['sidebarContactGroup'];
                                                                                    $report['message'] .= ' Contact Group "' . $params['sidebarContactGroup'] . '" Not Found<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    $sql = "SELECT DISTINCT cg.contactgroupname as contactgroupname,
                                                            cg.contactgroup_id as contactgroup_id
                                                            FROM crossbones.contactgroup cg
                                                            WHERE cg.account_id = ?
                                                            AND cg.active = ?"
                                                            . $search
                                                            . $contactgroup
                                                            . " ORDER BY cg.contactgroupname ASC";
                                                    $groups = $this->report_data->getReport($sql, $sqlPlaceHolder);

                                                    $sql = "SELECT
                                                                c.*,
                                                                cgc.method as method,
                                                                cg.contactgroupname as contactgroupname,
                                                                cg.contactgroup_id as contactgroup_id
                                                            FROM crossbones.contact c
                                                            LEFT JOIN crossbones.contactgroup_contact cgc ON cgc.contact_id = c.contact_id
                                                            LEFT JOIN crossbones.contactgroup cg ON cg.contactgroup_id = cgc.contactgroup_id
                                                            WHERE cg.account_id = ?
                                                            AND cg.active = ?"
                                                            . $search
                                                            . $contactgroup
                                                            . " ORDER BY cg.contactgroupname ASC, c.firstname ASC , c.lastname ASC";

                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    
                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Name</th><th>Contact Method</th><th>Contacts</th><th class="tinywidth">Delete</th></tr>';
                                                    $page=1;
                                                    foreach ($rows as $key => $row) {
                                                        $report['code'] = 0; 
                                                        if ( $row['contactgroup_id'] != $contactgroup_id ) {
                                                            if ( ( $contactgroup_id ) && ( $last ) ) {
                                                                $last['contactgroupcontacts'] = implode ( ', ' , array_unique($contactgroupcontacts) ) ;
                                                                $last['contactgroupname'] = implode ( ', ' , array_unique($contactgroupname) ) ;
                                                                $last['method'] = implode ( ', ' , array_unique($contactgroupmethod) ) ;
                                                                $last['count'] = $count;
                                                                $frows[$contactgroup_id] = $last ;
                                                            }
                                                            $last = NULL ;
                                                            $count=0;
                                                            $contactgroup_id = $row['contactgroup_id'] ;
                                                            $email = $row['email'] ;
                                                            $contactgroupcontacts = array() ;
                                                            $contactgroupname = array() ;
                                                            $contactgroupmethod = array() ;
                                                        }
                                                        if ( ( $params['sidebarContactMethod'] == 'all' ) || ( $params['sidebarContactMethod'] == $row['method'] ) ) {
                                                            if ( $row['method'] ) { $contactgroupmethod[] = $row['method'] ; }
                                                            $contactgroupcontacts[] = $row['firstname'] . ' ' . $row['lastname'] ;
                                                            $count++;
                                                        }
                                                        if ( $row['contactgroupname'] ) { $contactgroupname[] = $row['contactgroupname'] ; }
                                                        $last = $row ;
                                                    }
                                                    if ( ( $contactgroup_id ) && ( $last ) ) {
                                                        $last['contactgroupcontacts'] = implode ( ', ' , array_unique($contactgroupcontacts) ) ;
                                                        $last['contactgroupname'] = implode ( ', ' , array_unique($contactgroupname) ) ;
                                                        $last['method'] = implode ( ', ' , array_unique($contactgroupmethod) ) ;
                                                        $last['count'] = $count;
                                                        $frows[$contactgroup_id] = $last ;
                                                    }
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($groups as $key => $group) {
                                                        $row = $frows[$group['contactgroup_id']] ;
                                                        $row['contactgroup_id'] = $group['contactgroup_id'];
                                                        $row['contactgroupname'] = $group['contactgroupname'];
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($row['lastruntime']=='0000-00-00 00:00:00'){
                                                            $row['lastruntime'] = $row['createdate'];
                                                        }
                                                        if($row['count']<1){
                                                            $row['delete'] = $this->base_logic->wizardDeleteRecord('contactgroup',$params['pid'],$report['records'],$row['contactgroup_id']) ;
                                                        } else {
                                                            $row['delete'] = '&nbsp;&nbsp;<a class="text-grey-10" href="#" title="Empty to Unlock...">Locked</a>' ;
                                                        }
                                                        if($page==$params['pageCount']){
                                                            $onClick="Core.DataTable.pop('modal-edit-scheduled-report','modal-title=" . str_replace("'", "\'", $row['reporttypename']) . ";','modal-edit-scheduled-report-name=" . str_replace("'", "\'", $row['schedulereportname']) . ";','scheduled-recurrence=" . str_replace("'", "\'", $row['schedule']) . ";scheduled-day=" . str_replace("'", "\'", $row['scheduleday']) . ";scheduled-monthly=" . str_replace("'", "\'", $row['monthday']) . ";scheduled-time=" . str_replace("'", "\'", $row['sendhour']) . ";scheduled-format=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-mode=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-single=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-group=" . str_replace("'", "\'", $row['format']) . ";');";
                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }
                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '"><a class="edit-contact-group" href="javascript:void(0);" id="contact-group-table-' . $row['contactgroup_id'] . '" onclick="Core.ClearForm(\'edit-contact-group\',\'' . str_replace("'","\'",$row['contactgroupname']) . '\',\'' . $row['contactgroup_id'] . '\');">' . $row['contactgroupname'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . ucwords($row['method']) . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['count'] . '&nbsp;&nbsp;&nbsp; <span class="text-grey-12">'  . $row['contactgroupcontacts'] . '</span></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['delete'] . '</td>'
                                                                . '</tr>'; 
                                                        }
                                                    }
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    if(!($report['tbody'])){$report['tbody'] = '<tr><td colspan="4"><i>No Data Found</i></td></tr>';}
                                                    break;

            case              'device-list-table' : $report['message'] = '&nbsp;<p>';
                                                    if($params['search']){
                                                        $search = ' AND ( u.unitname LIKE \'' . $params['search'] . '%\' OR u.unitname LIKE \'%' . $params['search'] . '%\' OR ug.unitgroupname LIKE \'' . $params['search'] . '%\' OR ug.unitgroupname LIKE \'%' . $params['search'] . '%\' OR u.serialnumber LIKE \'' . $params['search'] . '%\' OR u.serialnumber LIKE \'%' . $params['search'] . '%\' OR ua.vin LIKE \'' . $params['search'] . '%\' OR ua.vin LIKE \'%' . $params['search'] . '%\' OR ua.make LIKE \'' . $params['search'] . '%\' OR ua.make LIKE \'%' . $params['search'] . '%\' OR ua.model LIKE \'' . $params['search'] . '%\' OR ua.model LIKE \'%' . $params['search'] . '%\' OR ua.year LIKE \'' . $params['search'] . '%\' OR ua.year LIKE \'%' . $params['search'] . '%\' OR ua.color LIKE \'' . $params['search'] . '%\' OR ua.color LIKE \'%' . $params['search'] . '%\' OR ua.licenseplatenumber LIKE \'' . $params['search'] . '%\' OR ua.licenseplatenumber LIKE \'%' . $params['search'] . '%\' OR us.unitstatusname LIKE \'' . $params['search'] . '%\' OR us.unitstatusname LIKE \'%' . $params['search'] . '%\' OR ua.purchasedate LIKE \'' . $params['search'] . '%\' OR ua.purchasedate LIKE \'%' . $params['search'] . '%\' OR ua.renewaldate LIKE \'' . $params['search'] . '%\' OR ua.renewaldate LIKE \'%' . $params['search'] . '%\' )' ;
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0; 
                                                    }
                                                    $sql = "SELECT u.*,
                                                            u.unit_id as unit_id,
                                                            ua.activatedate as activatedate,
                                                            ua.purchasedate as purchasedate,
                                                            ua.renewaldate as renewaldate,
                                                            ua.make as make,
                                                            ua.model as model,
                                                            ua.year as year,
                                                            ua.color as color,
                                                            ua.licenseplatenumber as licenseplatenumber,
                                                            ua.loannumber as loannumber,
                                                            ua.vin as vin,
                                                            us.unitstatusname as unitstatusname,
                                                            ug.unitgroupname as unitgroupname
                                                            FROM crossbones.unit u
                                                            LEFT JOIN crossbones.unitattribute ua ON ua.unit_id = u.unit_id
                                                            LEFT JOIN crossbones.unitgroup ug ON ug.unitgroup_id = u.unitgroup_id
                                                            LEFT JOIN crossbones.unitstatus us ON us.unitstatus_id = u.unitstatus_id
                                                            WHERE u.account_id = ?"
                                                            . $search
                                                            . " ORDER BY u.unitname ASC";
                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);

                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Vehicle Name</th><th>Group</th><th>Serial Number</th><th>VIN</th><th>Make</th><th>Model</th><th>Year</th><th>Color</th><th>License Plate</th><th>Loan Number</th><th>Device Status</th><th>Purchase</th><th>Activation</th><th>Expiration</th><th>Auto&nbsp;Renew</th></tr>'; 
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        $report['code'] = 0; 
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }

                                                        if($page==$params['pageCount']){
                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }
                                                            if($row['purchasedate']=='0000-00-00'){
                                                                $row['purchasedate']=$row['activatedate'];
                                                            }
                                                            if($row['purchasedate']=='0000-00-00'){
                                                                $row['purchasedate']=null;
                                                            }
                                                            if($row['activatedate']){
                                                                $ts = $row['activatedate'] . ' 00:00:00' ;
                                                                switch($row['subscription']){
                                                                    case           '1 Year' : $row['expirationdate'] = date( 'Y-m-d' , strtotime( '+1 year' , strtotime($ts) ) ) ;
                                                                                              break;
                                                                    case           '2 Year' : $row['expirationdate'] = date( 'Y-m-d' , strtotime( '+2 year' , strtotime($ts) ) ) ;
                                                                                              break;
                                                                    case           '3 Year' : $row['expirationdate'] = date( 'Y-m-d' , strtotime( '+3 year' , strtotime($ts) ) ) ;
                                                                                              break;
                                                                    case           '4 Year' : $row['expirationdate'] = date( 'Y-m-d' , strtotime( '+4 year' , strtotime($ts) ) ) ;
                                                                                              break;
                                                                    case           '5 Year' : $row['expirationdate'] = date( 'Y-m-d' , strtotime( '+5 year' , strtotime($ts) ) ) ;
                                                                                              break;
                                                                }
                                                            }
                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '"><a href="#modal-edit-device" data-toggle="modal" onclick="Core.ClearForm(\'device-edit\',\'' . str_replace("'","\'",$row['unitname']) . '\',\'' . $row['unit_id'] . '\');" title="Edit Device">' . $row['unitname'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['unitgroupname'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['serialnumber'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['vin'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['make'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['model'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['year'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['color'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['licenseplatenumber'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['loannumber'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['unitstatusname'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['purchasedate'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['activatedate'] . '</td>'
                                                                . '<td class="' . $evenOdd . '" title="' . $row['subscription'] . '">' . $row['expirationdate'] . '</td>'
                                                                . '<td class="' . $evenOdd . '"><input id="' . $row['unit_id'] . '" type="checkbox" checked></input></td>'
                                                                . '</tr>'; 
                                                        }
                                                    }
                                                    if(!($report['tbody'])){$report['tbody'] = '<tr><td colspan="3"><i>No Data Found</i></td></tr>';}
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    break;

            case              'devices-exporting' : // $report['lastReport'] = 'Export Device Report';
                                                    $report['message'] = '&nbsp;<p>';
                                                    if($params['search']){
                                                        $search = ' AND ( u.unitname LIKE \'' . $params['search'] . '%\' OR u.unitname LIKE \'%' . $params['search'] . '%\' OR ug.unitgroupname LIKE \'' . $params['search'] . '%\' OR ug.unitgroupname LIKE \'%' . $params['search'] . '%\' OR u.serialnumber LIKE \'' . $params['search'] . '%\' OR u.serialnumber LIKE \'%' . $params['search'] . '%\' OR ua.vin LIKE \'' . $params['search'] . '%\' OR ua.vin LIKE \'%' . $params['search'] . '%\' OR ua.make LIKE \'' . $params['search'] . '%\' OR ua.make LIKE \'%' . $params['search'] . '%\' OR ua.model LIKE \'' . $params['search'] . '%\' OR ua.model LIKE \'%' . $params['search'] . '%\' OR ua.year LIKE \'' . $params['search'] . '%\' OR ua.year LIKE \'%' . $params['search'] . '%\' OR ua.color LIKE \'' . $params['search'] . '%\' OR ua.color LIKE \'%' . $params['search'] . '%\' OR ua.licenseplatenumber LIKE \'' . $params['search'] . '%\' OR ua.licenseplatenumber LIKE \'%' . $params['search'] . '%\' OR us.unitstatusname LIKE \'' . $params['search'] . '%\' OR us.unitstatusname LIKE \'%' . $params['search'] . '%\' OR ua.purchasedate LIKE \'' . $params['search'] . '%\' OR ua.purchasedate LIKE \'%' . $params['search'] . '%\' OR ua.renewaldate LIKE \'' . $params['search'] . '%\' OR ua.renewaldate LIKE \'%' . $params['search'] . '%\' )' ;
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0; 
                                                    }
                                                    $sqlPlaceHolder[] = $account_id ;
                                                    $sqlPlaceHolder[] = $account_id ;
                                                    $sql = "SELECT u.*,
                                                            a.accountname as accountname,
                                                            a.phonenumber as phonenumber,
                                                            e.export_id as export_id,
                                                            e.transferee_account_id as transferee_account_id,
                                                            u.unit_id as unit_id,
                                                            ua.purchasedate as purchasedate,
                                                            ua.renewaldate as renewaldate,
                                                            ua.make as make,
                                                            ua.model as model,
                                                            ua.year as year,
                                                            ua.color as color,
                                                            ua.licenseplatenumber as licenseplatenumber,
                                                            ua.loannumber as loannumber,
                                                            ua.vin as vin,
                                                            us.unitstatusname as unitstatusname,
                                                            ug.unitgroupname as unitgroupname
                                                            FROM crossbones.unit u
                                                            LEFT JOIN crossbones.unitattribute ua ON ua.unit_id = u.unit_id
                                                            LEFT JOIN crossbones.unitgroup ug ON ug.unitgroup_id = u.unitgroup_id
                                                            LEFT JOIN crossbones.unitstatus us ON us.unitstatus_id = u.unitstatus_id
                                                            LEFT JOIN crossbones.export e ON e.unit_id = u.unit_id
                                                            LEFT JOIN crossbones.account a ON a.account_id = e.transferee_account_id
                                                            WHERE u.account_id = ?
                                                            AND ( 
                                                                    ( e.transferor_account_id IS NULL )
                                                                    OR 
                                                                    ( e.transferor_account_id = ? AND e.transfered IS NULL AND e.canceled IS NULL AND e.rejected IS NULL )
                                                                    OR 
                                                                    ( e.transferee_account_id = ? AND e.transfered IS NOT NULL )
                                                                )"
                                                            . $search
                                                            . " ORDER BY u.unitname ASC";
                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);

                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right"><input class="export-select-all" type="checkbox"></div></th><th>Vehicle Name</th><th>Group</th><th>Serial Number</th><th>VIN</th><th>Make</th><th>Model</th><th>Year</th><th>Color</th><th>License Plate</th><th>Loan Number</th><th>Device Status</th><th colspan="2">Authorized Transferee</th></tr>'; 
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        $report['code'] = 0; 
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }

                                                        if($page==$params['pageCount']){
                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }
                                                            if($row['transferee_account_id']==$account_id){
                                                                $row['accountname'] = null ;
                                                                $row['phonenumber'] = null ;
                                                                $row['transferee_account_id'] = null ;
                                                            }
                                                            if($row['transferee_account_id']){
                                                                $checkbox = '<a href="javascript:void(0);" onClick="if(confirm(\'Are Your Sure?\')){Core.Ajax(\'transfer-canceled\',\'' . $row['unit_id'] . '\',\'' . $row['export_id'] . '\',\'transfer-canceled\');}">[X]</a>' ;
                                                                $highlight = 'released ' ;
                                                            } else {
                                                                $checkbox = '<div class="pull-right"><input class="device-for-export" type="checkbox" data-unit="' . $row['unit_id'] . '" value="' . $row['unitname'] . ' <div class=\'pull-right\' style=\'width:50%;\'><span class=\'pull-left text-grey\'>' . $row['serialnumber'] . '</span></div>"></div>';
                                                                $highlight = null ;
                                                            }
                                                            if(($evenOdd)&&($highlight)){
                                                                $eohClass = trim($evenOdd) . '-' . $highlight;
                                                            } else if($evenOdd) {
                                                                $eohClass = $evenOdd;
                                                            } else {
                                                                $eohClass = $highlight;
                                                            }
                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $eohClass . '">' . $checkbox . '</td>'
                                                                . '<td class="' . $eohClass . '"><a href="#modal-edit-device" data-toggle="modal" onclick="Core.ClearForm(\'device-edit\',\'' . str_replace("'","\'",$row['unitname']) . '\',\'' . $row['unit_id'] . '\');" title="Edit Device">' . $row['unitname'] . '</a></td>'
                                                                . '<td class="' . $eohClass . '">' . $row['unitgroupname'] . '</td>'
                                                                . '<td class="' . $eohClass . '">' . $row['serialnumber'] . '</td>'
                                                                . '<td class="' . $eohClass . '">' . $row['vin'] . '</td>'
                                                                . '<td class="' . $eohClass . '">' . $row['make'] . '</td>'
                                                                . '<td class="' . $eohClass . '">' . $row['model'] . '</td>'
                                                                . '<td class="' . $eohClass . '">' . $row['year'] . '</td>'
                                                                . '<td class="' . $eohClass . '">' . $row['color'] . '</td>'
                                                                . '<td class="' . $eohClass . '">' . $row['licenseplatenumber'] . '</td>'
                                                                . '<td class="' . $eohClass . '">' . $row['loannumber'] . '</td>'
                                                                . '<td class="' . $eohClass . '">' . $row['unitstatusname'] . '</td>'
                                                                . '<td class="' . $eohClass . '">' . $row['accountname'] . '</td>'
                                                                . '<td class="' . $eohClass . '">' . $row['phonenumber'] . '</td>'
                                                                . '</tr>'; 
                                                        }
                                                    }
                                                    if(!($report['tbody'])){
                                                        $report['tbody'] = '<tr><td colspan="3"><i>No Data Found</i></td></tr>';
                                                        $report['code'] = 0; 
                                                    }
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    break;

            case              'devices-importing' : // $report['lastReport'] = 'Import Device Report';
                                                    $report['message'] = '&nbsp;<p>';
                                                    if($params['search']){
                                                        $search = ' AND ( u.unitname LIKE \'' . $params['search'] . '%\' OR u.unitname LIKE \'%' . $params['search'] . '%\' OR ug.unitgroupname LIKE \'' . $params['search'] . '%\' OR ug.unitgroupname LIKE \'%' . $params['search'] . '%\' OR u.serialnumber LIKE \'' . $params['search'] . '%\' OR u.serialnumber LIKE \'%' . $params['search'] . '%\' OR ua.vin LIKE \'' . $params['search'] . '%\' OR ua.vin LIKE \'%' . $params['search'] . '%\' OR ua.make LIKE \'' . $params['search'] . '%\' OR ua.make LIKE \'%' . $params['search'] . '%\' OR ua.model LIKE \'' . $params['search'] . '%\' OR ua.model LIKE \'%' . $params['search'] . '%\' OR ua.year LIKE \'' . $params['search'] . '%\' OR ua.year LIKE \'%' . $params['search'] . '%\' OR ua.color LIKE \'' . $params['search'] . '%\' OR ua.color LIKE \'%' . $params['search'] . '%\' OR ua.licenseplatenumber LIKE \'' . $params['search'] . '%\' OR ua.licenseplatenumber LIKE \'%' . $params['search'] . '%\' OR us.unitstatusname LIKE \'' . $params['search'] . '%\' OR us.unitstatusname LIKE \'%' . $params['search'] . '%\' OR ua.purchasedate LIKE \'' . $params['search'] . '%\' OR ua.purchasedate LIKE \'%' . $params['search'] . '%\' OR ua.renewaldate LIKE \'' . $params['search'] . '%\' OR ua.renewaldate LIKE \'%' . $params['search'] . '%\' )' ;
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0; 
                                                    }
                                                    $sql = "SELECT u.*,
                                                            a.accountname as accountname,
                                                            a.phonenumber as phonenumber,
                                                            e.transferor_account_id as transferor_account_id,
                                                            e.export_id as export_id,
                                                            u.unit_id as unit_id,
                                                            ua.purchasedate as purchasedate,
                                                            ua.renewaldate as renewaldate,
                                                            ua.make as make,
                                                            ua.model as model,
                                                            ua.year as year,
                                                            ua.color as color,
                                                            ua.licenseplatenumber as licenseplatenumber,
                                                            ua.loannumber as loannumber,
                                                            ua.vin as vin,
                                                            us.unitstatusname as unitstatusname,
                                                            ug.unitgroupname as unitgroupname
                                                            FROM crossbones.unit u
                                                            LEFT JOIN crossbones.unitattribute ua ON ua.unit_id = u.unit_id
                                                            LEFT JOIN crossbones.unitgroup ug ON ug.unitgroup_id = u.unitgroup_id
                                                            LEFT JOIN crossbones.unitstatus us ON us.unitstatus_id = u.unitstatus_id
                                                            LEFT JOIN crossbones.export e ON e.unit_id = u.unit_id
                                                            LEFT JOIN crossbones.account a ON a.account_id = e.transferor_account_id
                                                            WHERE e.transferee_account_id = ?
                                                            AND e.canceled IS NULL
                                                            AND e.transfered IS NULL
                                                            AND e.rejected IS NULL"
                                                            . $search
                                                            . " ORDER BY a.accountname ASC, u.unitname ASC";
                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);

                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right"><input class="import-select-all" type="checkbox"></div></th><th colspan="2">Transferor</th><th>Vehicle Name</th><th>Serial Number</th><th>VIN</th><th>Make</th><th>Model</th><th>Year</th><th>Color</th><th>License Plate</th><th>Loan Number</th><th>Device Status</th></tr>'; 
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        $report['code'] = 0; 
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }

                                                        if($page==$params['pageCount']){
                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }
                                                            $checkbox = '<div class="pull-right"><input class="device-for-import" type="checkbox" data-unit="' . $row['export_id'] . '" value="<div class=\'pull-right\' style=\'width:40%;\'><span class=\'pull-left text-grey\'>' . $row['accountname'] . '<br>' . $row['phonenumber'] . '</span></div><div class=\'pull-right\' style=\'width:30%;\'><span class=\'pull-left text-grey\'>' . $row['unitname'] . '</span></div><div class=\'pull-right\' style=\'width:30%;\'><span class=\'pull-left\'>' . $row['serialnumber'] . '</span></div>"></div>';
                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $checkbox . '</div></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['accountname'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['phonenumber'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['unitname'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['serialnumber'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['vin'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['make'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['model'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['year'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['color'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['licenseplatenumber'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['loannumber'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['unitstatusname'] . '</td>'
                                                                . '</tr>'; 
                                                        }
                                                    }
                                                    if(!($report['tbody'])){
                                                        $report['tbody'] = '<tr><td colspan="3"><i>No Device Import Tickets Found</i></td></tr>';
                                                        $report['code'] = 0; 
                                                    }
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    break;
                                                    
            case           'landmark-group-table' : $report['message'] = '&nbsp;<p>';
                                                    $sqlPlaceHolder[] = '1';
                                                    $user['account_id'] = $account_id;
                                                    $user['user_id'] = $user_id;
                                                    $permission = $this->vehicle_data->ajaxPermissionCheck($user,'landmarkgroups');
                                                    if((!($permission))&&(!($role_account_owner))){
                                                        $userpermission = ' AND utg.user_id = ?' ;
                                                        $sqlPlaceHolder[] = $user_id;
                                                    }
                                                    if($params['search']){
                                                        $search = ' AND ( tg.territorygroupname LIKE \'' . $params['search'] . '%\' OR tg.territorygroupname LIKE \'%' . $params['search'] . '%\' )' ;
                                                        // $sqlPlaceHolder[] = $params['sidebarContactMethod'];
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0; 
                                                    }
                                                    $sql = "SELECT IFNULL(COUNT(DISTINCT t.territory_id),0) as total,
                                                                tg.territorygroup_id as territorygroup_id
                                                            FROM crossbones.territorygroup tg
                                                            LEFT JOIN crossbones.territory t ON t.territorygroup_id = tg.territorygroup_id
                                                            LEFT JOIN crossbones.user_territorygroup utg ON utg.territorygroup_id = tg.territorygroup_id
                                                            WHERE tg.account_id = ?
                                                            AND tg.active = ?"
                                                            . $userpermission
                                                            . $search
                                                            . " GROUP BY t.territorygroup_id
                                                            ORDER BY tg.territorygroupname ASC";
                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    foreach ($rows as $key => $row) {
                                                        $buffer[$row['territorygroup_id']] = $row['total'] ; 
                                                    }

                                                    // $report['message'] .= ' User Id: #' . $user_id . '#, SQL: "' . $sql . ', sqlPlaceHolder: "' . $sqlPlaceHolder[0] . ', ' . $sqlPlaceHolder[1] . '"<p>';
                                                    
                                                    $sql = "SELECT tg.default as d_fault,
                                                                tg.territorygroup_id as territorygroup_id,
                                                                tg.territorygroupname as territorygroupname
                                                            FROM crossbones.territorygroup tg
                                                            LEFT JOIN crossbones.user_territorygroup utg ON utg.territorygroup_id = tg.territorygroup_id
                                                            WHERE tg.account_id = ? 
                                                            AND tg.active = ?"
                                                            . $userpermission
                                                            . $search
                                                            . " GROUP BY tg.territorygroup_id"
                                                            . " ORDER BY tg.default DESC, tg.territorygroupname ASC";
                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    
                                                    // $report['message'] .= ' SQL: "' . $sql . ', sqlPlaceHolder: "' . $sqlPlaceHolder[0] . ', ' . $sqlPlaceHolder[1] . '"<p>';
                                                    
                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Landmark Group</th><th>Count</th><th class="tinywidth">Delete</th></tr>';
                                                    if(!($rows)){$report['tbody'] = '<tr><td colspan="3"><i>No Data Found</i></td></tr>';}
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        $report['code'] = 0; 
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){
                                                            $onClick="Core.DataTable.pop('modal-edit-scheduled-report','modal-title=" . str_replace("'", "\'", $row['reporttypename']) . ";','modal-edit-scheduled-report-name=" . str_replace("'", "\'", $row['schedulereportname']) . ";','scheduled-recurrence=" . str_replace("'", "\'", $row['schedule']) . ";scheduled-day=" . str_replace("'", "\'", $row['scheduleday']) . ";scheduled-monthly=" . str_replace("'", "\'", $row['monthday']) . ";scheduled-time=" . str_replace("'", "\'", $row['sendhour']) . ";scheduled-format=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-mode=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-single=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-group=" . str_replace("'", "\'", $row['format']) . ";');";
                                                            $row['total'] = $buffer[$row['territorygroup_id']] ;
                                                            if( $row['total'] < 1){
                                                                $row['total'] = '0';
                                                            } 
                                                            if(!($row['d_fault'])){
                                                                // $row['territorygroupname'] = $this->base_logic->wizardInput($params['pid'],$report['records'],'crossbones-territorygroup-territorygroupname',$row['territorygroup_id'],$row['territorygroupname']);
                                                                if( $row['total'] < 1){
                                                                    $row['delete'] = $this->base_logic->wizardDeleteRecord('territorygroup',$params['pid'],$report['records'],$row['territorygroup_id']);
                                                                } else {
                                                                    $row['delete'] = '<a class="text-grey-10" href="#" title="Empty to Unlock...">Locked</a>';
                                                                }
                                                            } else {
                                                                $row['delete'] = NULL ;
                                                            }
                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }
                                                            if(!($row['territorygroupname'])){
                                                                $row['territorygroupname'] = 'Landmark Group #' . $row['territorygroup_id'] ;
                                                            }
                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '"><a class="landmarkgroup-edit" href="javascript:void(0);" id="landmarkgroup-list-table-' . $row['territorygroup_id'] . '" onClick="Core.ClearForm(\'edit-landmark-group\',\'' . $row['territorygroupname'] . '\',\'' . $row['territorygroup_id'] . '\');">' . $row['territorygroupname'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['total'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['delete'] . '</td>'
                                                                . '</tr>'; 
                                                        }
                                                    }
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    break;

            case      'landmark-incomplete-table' : $report['message'] = '&nbsp;<p>';
                                                    $sqlPlaceHolder[] = '1';
                                                    if($params['search']){
                                                        $search = ' AND ( tu.streetaddress LIKE \'' . $params['search'] . '%\' OR tu.streetaddress LIKE \'%' . $params['search'] . '%\' OR tu.city LIKE \'' . $params['search'] . '%\' OR tu.city LIKE \'%' . $params['search'] . '%\' OR tu.state LIKE \'' . $params['search'] . '%\' OR tu.state LIKE \'%' . $params['search'] . '%\' OR tu.zipcode LIKE \'' . $params['search'] . '%\' OR tu.zipcode LIKE \'%' . $params['search'] . '%\' OR tu.territoryname LIKE \'' . $params['search'] . '%\' OR tu.territoryname LIKE \'%' . $params['search'] . '%\' OR tu.territorygroupname LIKE \'' . $params['search'] . '%\' OR tu.territorygroupname LIKE \'%' . $params['search'] . '%\' )' ;
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarLandmarkGroup']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $landmarkgroup = ' AND ( tu.territorygroupname LIKE \'' . $params['sidebarLandmarkGroup'] . '%\' OR tu.territorygroupname LIKE \'%' . $params['sidebarLandmarkGroup'] . '%\' )' ;
                                                                                    // $sqlPlaceHolder[] = $params['sidebarLandmarkGroup'];
                                                                                    $report['message'] .= ' Landmark Group Id "' . $params['sidebarLandmarkGroup'] . '" Not Found<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarTerritoryType']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $territorytype = ' AND tu.territorytype = ?' ;
                                                                                    $sqlPlaceHolder[] = $params['sidebarTerritoryType'];
                                                                                    $report['message'] .= ' Landmark Territory Type "' . $params['sidebarTerritoryType'] . '" Not Found<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarReason']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $reason = ' AND ( tu.reason LIKE \'' . $params['sidebarReason'] . '%\' OR tu.reason LIKE \'%' . $params['sidebarReason'] . '%\' )' ;
                                                                                    // $sqlPlaceHolder[] = $params['sidebarReason'];
                                                                                    $report['message'] .= ' Landmark Reason Code "' . $params['sidebarReason'] . '" Not Found<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    $sql = "SELECT
                                                                tu.*,
                                                                tc.territorycategoryname as territorycategoryname
                                                            FROM crossbones.territoryupload tu
                                                            LEFT JOIN crossbones.territorycategory tc ON tc.territorycategory_id = tu.territorycategory_id
                                                            WHERE tu.account_id = ?
                                                            AND tu.active != ?"
                                                            . $search
                                                            . $landmarkgroup
                                                            . $territorytype
                                                            . $reason
                                                            . " ORDER BY tu.territoryname ASC";

                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    
                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Landmark</th><th>Group</th><th>Type</th><th>Radius</th><th>Address</th><th>Latitude</th><th>Longitude</th><th>Reason for Unfound</th><th class="tinywidth">Delete</th></tr>';
                                                    if(!($rows)){$report['tbody'] = '<tr><td colspan="5"><i>No Data Found</i></td></tr>';}
                                                    $page=1;
                                                    $evenOdd='report-even-odd';
                                                    foreach ($rows as $key => $row) {
                                                        $report['code'] = 0; 
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){
                                                            $address = $this->address_logic->validateAddress($row['streetaddress'], $row['city'], $row['state'], $row['zipcode'], $row['country']);
                                                            $label=str_replace('"','\"',$address);
                                                            switch ($row['radius']){

                                                                case            '' :
                                                                case           '0' : $radius = '-';
                                                                                     break;

                                                                case         '330' : $radius = '1/16 Mile';
                                                                                     break;

                                                                case         '660' : $radius = '1/8 Mile';
                                                                                     break;

                                                                case        '1320' : $radius = '1/4 Mile';
                                                                                     break;

                                                                case        '2640' : $radius = '1/2 Mile';
                                                                                     break;

                                                                case        '5280' : $radius = '1 Mile';
                                                                                     break;

                                                                           default : $milecount=0;
                                                                                     $rcnt = $row['radius'] ;
                                                                                     while($rcnt>5279){
                                                                                        $milecount++;
                                                                                        $rcnt = $rcnt - 5280;
                                                                                        $radius = $milecount . ' Miles';
                                                                                     }
                                                                                     if ($rcnt) {
                                                                                        $radius .= ' ' . $rcnt . ' feet';
                                                                                     }

                                                            }
                                                            switch ($row['shape']){

                                                                case      'circle' : $shape = 'Circle';
                                                                                     break;

                                                                case     'polygon' : $shape = 'Polygon';
                                                                                     break;

                                                                case      'square' : $shape = 'Square';
                                                                                     break;

                                                                           default : $shape = 'models/logic/reportlogic.php';

                                                            }
                                                            $onClick="Core.DataTable.pop('modal-edit-scheduled-report','modal-title=" . str_replace("'", "\'", $row['reporttypename']) . ";','modal-edit-scheduled-report-name=" . str_replace("'", "\'", $row['schedulereportname']) . ";','scheduled-recurrence=" . str_replace("'", "\'", $row['schedule']) . ";scheduled-day=" . str_replace("'", "\'", $row['scheduleday']) . ";scheduled-monthly=" . str_replace("'", "\'", $row['monthday']) . ";scheduled-time=" . str_replace("'", "\'", $row['sendhour']) . ";scheduled-format=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-mode=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-single=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-group=" . str_replace("'", "\'", $row['format']) . ";');";
                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }
                                                            $onClick='';
                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '"><a class="landmark-fix" href="javascript:void(0);" id="landmark-incomplete-table-' . $row['territoryupload_id'] . '" data-streetaddress="' . $row['streetaddress'] . '" data-city="' . $row['city'] . '" data-state="' . $row['state'] . '" data-zipcode="' . $row['zipcode'] . '" data-country="' . $row['country'] . '" data-name="' . $row['territoryname'] . '">' . $row['territoryname'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['territorygroupname'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . ucwords( $row['territorytype'] ) . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $radius . '</td>'
                                                                . '<td class="' . $evenOdd . '"><a class="landmark-fix" href="javascript:void(0);" id="landmark-incomplete-table-' . $row['territoryupload_id'] . '" data-streetaddress="' . $row['streetaddress'] . '" data-city="' . $row['city'] . '" data-state="' . $row['state'] . '" data-zipcode="' . $row['zipcode'] . '" data-country="' . $row['country'] . '" data-name="' . $row['territoryname'] . '">' . $address . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['latitude'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['longitude'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['reason'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $this->base_logic->wizardDeleteRecord('incomplete',$params['pid'],$report['records'],$row['territoryupload_id']) . '</td>'
                                                                . '</tr>'; 
                                                        }
                                                    }
                                                    if(!($report['tbody'])){
                                                        $report['tbody'] = '<tr><td colspan="9"><i>No Data Found</i></td></tr>';
                                                    } else {
                                                        // $report['lastReport'] = $this->base_logic->wizardMapAllLink();
                                                    }
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    break;
                                                    
            case            'landmark-list-table' : $sqlPlaceHolder[] = '1';
                                                    $sql_params = array($account_id,$user_id);
                                                    $user['account_id'] = $account_id;
                                                    $user['user_id'] = $user_id;
                                                    $permission = $this->vehicle_data->ajaxPermissionCheck($user,'landmarks');
                                                    if((!($permission))&&(!($role_account_owner))){
                                                        $sqlPlaceHolder[] = $user_id;
                                                    }
                                                    $report['message'] = '&nbsp;<p>';
                                                    if($params['search']){
                                                        $search = ' AND ( t.streetaddress LIKE \'' . $params['search'] . '%\' OR t.streetaddress LIKE \'%' . $params['search'] . '%\' OR t.city LIKE \'' . $params['search'] . '%\' OR t.city LIKE \'%' . $params['search'] . '%\' OR t.state LIKE \'' . $params['search'] . '%\' OR t.state LIKE \'%' . $params['search'] . '%\' OR t.zipcode LIKE \'' . $params['search'] . '%\' OR t.zipcode LIKE \'%' . $params['search'] . '%\' OR t.territoryname LIKE \'' . $params['search'] . '%\' OR t.territoryname LIKE \'%' . $params['search'] . '%\' OR tg.territorygroupname LIKE \'' . $params['search'] . '%\' OR tg.territorygroupname LIKE \'%' . $params['search'] . '%\' )' ;
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarLandmarkGroup']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $landmarkgroup = ' AND t.territorygroup_id = ?' ;
                                                                                    $sqlPlaceHolder[] = $params['sidebarLandmarkGroup'];
                                                                                    $report['message'] .= ' Landmark Group Id "' . $params['sidebarLandmarkGroup'] . '" Not Found<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarLandmarkCategories']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $landmarkcategory = ' AND t.territorycategory_id = ?' ;
                                                                                    $sqlPlaceHolder[] = $params['sidebarLandmarkCategories'];
                                                                                    $report['message'] .= ' Landmark Category Id "' . $params['sidebarLandmarkCategories'] . '" Not Found<p>';
                                                                                    $report['code'] = 0; 
                                                    }

                                                    if(($permission)||($role_account_owner)){
                                                    
                                                        $sql = "SELECT t.*,
                                                                    t.territory_id as territory_id,
                                                                    t.territoryname as territoryname,
                                                                    tg.territorygroupname as territorygroupname,
                                                                    tc.territorycategoryname as territorycategoryname
                                                                FROM crossbones.territory t
                                                                LEFT JOIN crossbones.territorycategory tc ON tc.territorycategory_id = t.territorycategory_id
                                                                LEFT JOIN crossbones.territorygroup tg ON tg.territorygroup_id = t.territorygroup_id
                                                                LEFT JOIN crossbones.user_territorygroup utg ON utg.territorygroup_id = tg.territorygroup_id
                                                                LEFT JOIN crossbones.user user ON user.user_id = utg.user_id
                                                                WHERE t.account_id = ?
                                                                AND t.active = ?
                                                                AND t.territorytype = 'landmark'"
                                                                . $search
                                                                . $landmarkgroup
                                                                . $landmarkcategory
                                                                . " ORDER BY t.territoryname ASC";

                                                    } else {

                                                        $sql = "SELECT t.*,
                                                                    t.territory_id as territory_id,
                                                                    t.territoryname as territoryname,
                                                                    tg.territorygroupname as territorygroupname,
                                                                    tc.territorycategoryname as territorycategoryname
                                                                FROM crossbones.territory t
                                                                LEFT JOIN crossbones.territorycategory tc ON tc.territorycategory_id = t.territorycategory_id
                                                                LEFT JOIN crossbones.territorygroup tg ON tg.territorygroup_id = t.territorygroup_id
                                                                LEFT JOIN crossbones.user_territorygroup utg ON utg.territorygroup_id = tg.territorygroup_id
                                                                LEFT JOIN crossbones.user user ON user.user_id = utg.user_id
                                                                WHERE t.account_id = ?
                                                                AND t.active = ?
                                                                AND utg.user_id = ?
                                                                AND t.territorytype = 'landmark'"
                                                                . $search
                                                                . $landmarkgroup
                                                                . $landmarkcategory
                                                                . " ORDER BY t.territoryname ASC";
                                                    }

                                                    // $sql = "SELECT
                                                    //             t.*,
                                                    //             tg.territorygroupname as territorygroupname,
                                                    //             tc.territorycategoryname as territorycategoryname
                                                    //         FROM crossbones.territory t
                                                    //         LEFT JOIN crossbones.territorycategory tc ON tc.territorycategory_id = t.territorycategory_id
                                                    //         LEFT JOIN crossbones.territorygroup tg ON tg.territorygroup_id = t.territorygroup_id
                                                    //         LEFT JOIN crossbones.user_territorygroup utg ON utg.territorygroup_id = tg.territorygroup_id
                                                    //         LEFT JOIN crossbones.user user ON user.user_id = utg.user_id
                                                    //         WHERE t.account_id = ? 
                                                    //         AND t.active = ?
                                                    //         AND ( utg.user_id = ? OR ( user.user_id = ? AND user.roles = ? ) )
                                                    //         AND t.territorytype = 'landmark'"
                                                    //         . $search
                                                    //         . $landmarkgroup
                                                    //         . $landmarkcategory
                                                    //         . " ORDER BY t.territoryname ASC";

                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    
                                                    if($access['landmark']['write']){
                                                        $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Landmark</th><th>Group</th><th>Radius</th><th>Address</th><th class="tinywidth">Delete</th></tr>';
                                                    } else {
                                                        $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Landmark</th><th>Group</th><th>Radius</th><th>Address</th></tr>';
                                                    }

                                                    if(!($rows)){$report['tbody'] = '<tr><td colspan="5"><i>No Data Found</i></td></tr>';}
                                                    $page=1;
                                                    $evenOdd='report-even-odd';
                                                    foreach ($rows as $key => $row) {
                                                        if(($row['territory_id'])&&($row['territory_id']!=$lastTerritory)){
                                                            $lastTerritory = $row['territory_id'];
                                                            $report['code'] = 0; 
                                                            $report['records']++;
                                                            $length++;
                                                            if(($params['length']>0)&&($length>$params['length'])){
                                                                $length=1;
                                                                $page++;
                                                            }
                                                            if($page==$params['pageCount']){
                                                                $address = $this->address_logic->validateAddress($row['streetaddress'], $row['city'], $row['state'], $row['zipcode'], $row['country']);
                                                                $label=str_replace('"','\"',$address);
                                                                switch ($row['radius']){

                                                                    case            '' :
                                                                    case           '0' : $radius = '1/16 Mile*';
                                                                                         $row['radius'] = 330;
                                                                                         break;

                                                                    case         '330' : $radius = '1/16 Mile';
                                                                                         break;

                                                                    case         '660' : $radius = '1/8 Mile';
                                                                                         break;

                                                                    case        '1320' : $radius = '1/4 Mile';
                                                                                         break;

                                                                    case        '2640' : $radius = '1/2 Mile';
                                                                                         break;

                                                                    case        '5280' : $radius = '1 Mile';
                                                                                         break;

                                                                               default : $milecount=0;
                                                                                         $rcnt = $row['radius'] ;
                                                                                         while($rcnt>5279){
                                                                                            $milecount++;
                                                                                            $rcnt = $rcnt - 5280;
                                                                                            $radius = $milecount . ' Miles';
                                                                                         }
                                                                                         if ($rcnt) {
                                                                                            $radius .= ' ' . $rcnt . ' feet';
                                                                                         }

                                                                }
                                                                switch ($row['shape']){

                                                                    case      'circle' : $shape = 'Circle';
                                                                                         break;

                                                                    case     'polygon' : $shape = 'Polygon';
                                                                                         break;

                                                                    case      'square' : $shape = 'Square';
                                                                                         break;

                                                                               default : $shape = 'models/logic/reportlogic.php';

                                                                }
                                                                $onClick="Core.DataTable.pop('modal-edit-scheduled-report','modal-title=" . str_replace("'", "\'", $row['reporttypename']) . ";','modal-edit-scheduled-report-name=" . str_replace("'", "\'", $row['schedulereportname']) . ";','scheduled-recurrence=" . str_replace("'", "\'", $row['schedule']) . ";scheduled-day=" . str_replace("'", "\'", $row['scheduleday']) . ";scheduled-monthly=" . str_replace("'", "\'", $row['monthday']) . ";scheduled-time=" . str_replace("'", "\'", $row['sendhour']) . ";scheduled-format=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-mode=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-single=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-group=" . str_replace("'", "\'", $row['format']) . ";');";
                                                                switch($evenOdd){
                                                                    case     '' : $evenOdd = 'report-even-odd ';
                                                                                    break;
                                                                        default : $evenOdd = '';
                                                                }
                                                                $onClick='';
                                                                if($access['landmark']['write']){
                                                                    $delete = '<td class="' . $evenOdd . '">' . $this->base_logic->wizardDeleteRecord('landmark',$params['pid'],$report['records'],$row['territory_id']) . '</td>' ;
                                                                } else {
                                                                    $delete = null ;
                                                                }
                                                                $report['tbody'] .= '<tr>'
                                                                    . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                    . '<td class="' . $evenOdd . '"><a class="landmark-edit" href="javascript:void(0);" id="landmark-list-table-' . $row['territory_id'] . '">' . $row['territoryname'] . '</a></td>'
                                                                    . '<td class="' . $evenOdd . '">' . $row['territorygroupname'] . '</td>'
                                                                    . '<td class="' . $evenOdd . '">' . $radius . '</td>'
                                                                    . '<td class="' . $evenOdd . 'address_map_link landmark_map_link" data-eventname="' . $label . '" data-mode="landmark" data-name="' . $row['territoryname'] . '" data-id="' . $row['territory_id'] . '" data-latitude="' . $row['latitude'] . '" data-longitude="' . $row['longitude'] . '" data-radius="' . $row['radius'] . '" data-shape="' . $row['shape'] . '" data-label="' . $label . '"><a href="#" title="click here to inspect on map...">' . $address . '</a></td>'
                                                                    . $delete
                                                                    . '</tr>'; 
                                                            }
                                                        }
                                                    }
                                                    if(!($report['tbody'])){
                                                        $report['tbody'] = '<tr><td colspan="9"><i>No Data Found</i></td></tr>';
                                                    } else {
                                                        $report['lastReport'] = $this->base_logic->wizardMapAllLink();
                                                    }
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    break;

            case   'landmark-verification-table' :  $sqlPlaceHolder[] = 1 ;
                                                    $report['message'] = '&nbsp;<p>';
                                                    if($params['search']){
                                                        $search = ' AND ( t.streetaddress LIKE \'' . $params['search'] . '%\' OR t.streetaddress LIKE \'%' . $params['search'] . '%\' OR t.city LIKE \'' . $params['search'] . '%\' OR t.city LIKE \'%' . $params['search'] . '%\' OR t.state LIKE \'' . $params['search'] . '%\' OR t.state LIKE \'%' . $params['search'] . '%\' OR t.zipcode LIKE \'' . $params['search'] . '%\' OR t.zipcode LIKE \'%' . $params['search'] . '%\' OR t.territoryname LIKE \'' . $params['search'] . '%\' OR t.territoryname LIKE \'%' . $params['search'] . '%\' OR tg.territorygroupname LIKE \'' . $params['search'] . '%\' OR tg.territorygroupname LIKE \'%' . $params['search'] . '%\' OR u.unitname LIKE \'' . $params['search'] . '%\' OR u.unitname LIKE \'%' . $params['search'] . '%\' )' ;
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarVerification']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : if ( $params['sidebarVerification'] == 'verified' ) {
                                                                                        $verification = ' AND t.verifydate != \'0000-00-00\'' ;
                                                                                    } else {
                                                                                        $verification = ' AND t.verifydate = \'0000-00-00\'' ;
                                                                                    }
                                                                                    $report['message'] .= ' Verification Type "' . $params['sidebarVerification'] . '" Not Found<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarVehicleSingle']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $vehicle = ' AND u.unit_id = ?' ;
                                                                                    $sqlPlaceHolder[] = $params['sidebarVehicleSingle'];
                                                                                    $report['message'] .= ' Vehicle Id "' . $params['sidebarVehicleSingle'] . '" Not Found<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    $sql = "SELECT
                                                                t.*,
                                                                tg.territorygroupname as territorygroupname,
                                                                ut.unit_id as unit_id,
                                                                u.unitname as unitname
                                                            FROM crossbones.territory t
                                                            LEFT JOIN crossbones.territorygroup tg ON tg.territorygroup_id = t.territorygroup_id
                                                            LEFT JOIN crossbones.unit_territory ut ON ut.territory_id = t.territory_id
                                                            LEFT JOIN crossbones.unit u ON u.unit_id = ut.unit_id
                                                            WHERE t.account_id = ? 
                                                            AND t.active =? 
                                                            AND t.territorytype = 'reference'"
                                                            . $search
                                                            . $verification
                                                            . $vehicle
                                                            . " ORDER BY t.territoryname ASC";

                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    
                                                    // $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Landmark / Vehicle</th><th>Boundary</th><th colspan="2">Location</th><th colspan="2"><a class="verification-add" href="#" title="create a new record...">add a new address</a></th><th class="tinywidth"></th></tr>';
                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Verification Name</th><th>Vehicle</th><th>Address&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a class="verification-add" href="#" title="create a new record...">+&nbsp;add&nbsp;a&nbsp;new&nbsp;address</a></th><th>Radius</th><th>Verified</th><th>Verify Date</th><th class="tinywidth">Delete</th></tr>';
                                                    if(!($rows)){$report['tbody'] = '<tr><td colspan="8"><i>No Data Found</i></td></tr>';}
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    $report['code'] = 0; 
                                                    foreach ($rows as $key => $row) {
                                                        $report['code'] = 0; 
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){
                                                            $address = $this->address_logic->validateAddress($row['streetaddress'], $row['city'], $row['state'], $row['zipcode'], $row['country']);
                                                            $label = str_replace('"','\"',$address);
                                                            switch ($row['radius']){

                                                                case            '' :
                                                                case           '0' : $radius = 'n/a';
                                                                                     break;

                                                                case         '330' : $radius = '1/16 Mile';
                                                                                     break;

                                                                case         '660' : $radius = '1/8 Mile';
                                                                                     break;

                                                                case        '1320' : $radius = '1/4 Mile';
                                                                                     break;

                                                                case        '2640' : $radius = '1/2 Mile';
                                                                                     break;

                                                                case        '5280' : $radius = '1 Mile';
                                                                                     break;

                                                                           default : $milecount=0;
                                                                                     $rcnt = $row['radius'] ;
                                                                                     while($rcnt>5279){
                                                                                        $milecount++;
                                                                                        $rcnt = $rcnt - 5280;
                                                                                        $radius = $milecount . ' Miles';
                                                                                     }
                                                                                     if ($rcnt) {
                                                                                        $radius .= ' ' . $rcnt . ' feet';
                                                                                     }

                                                            }
                                                            switch ($row['shape']){

                                                                case      'circle' : $shape = 'Circle';
                                                                                     break;

                                                                case     'polygon' : $shape = 'Polygon';
                                                                                     break;

                                                                case      'square' : $shape = 'Square';
                                                                                     break;

                                                                           default : $shape = 'models/logic/reportlogic.php';

                                                            }
                                                            if($row['verifydate'] == '0000-00-00'){
                                                                $status = '<span class="label label-danger report-label">Not Verified</span>' ;
                                                                $date = NULL ;
                                                            } else {
                                                                $date = date('m/d/Y' , strtotime($row['verifydate'])) ;
                                                                $status = '<span class="label label-success report-label">Verified</span>' ;
                                                            }
                                                            $onClick="Core.DataTable.pop('modal-edit-scheduled-report','modal-title=" . str_replace("'", "\'", $row['reporttypename']) . ";','modal-edit-scheduled-report-name=" . str_replace("'", "\'", $row['schedulereportname']) . ";','scheduled-recurrence=" . str_replace("'", "\'", $row['schedule']) . ";scheduled-day=" . str_replace("'", "\'", $row['scheduleday']) . ";scheduled-monthly=" . str_replace("'", "\'", $row['monthday']) . ";scheduled-time=" . str_replace("'", "\'", $row['sendhour']) . ";scheduled-format=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-mode=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-single=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-group=" . str_replace("'", "\'", $row['format']) . ";');";
                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }
                                                            if(!($row['territoryname'])){
                                                                $row['territoryname'] = 'Landmark #' . $row['territory_id'] ;
                                                            }
                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '"><a class="landmark-edit" href="javascript:void(0);" id="landmark-list-table-' . $row['territory_id'] . '">' . $row['territoryname'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['unitname'] . '</td>'
                                                                . '<td class="' . $evenOdd . 'address_map_link landmark_map_link" data-eventname="' . $label . '" data-mode="landmark" data-name="' . $row['unitname'] . ' ' . $row['territoryname'] . '" data-id="' . $row['territory_id'] . '" data-latitude="' . $row['latitude'] . '" data-longitude="' . $row['longitude'] . '" data-radius="' . $row['radius'] . '" data-shape="' . $row['shape'] . '" data-label="' . $label . '"><a href="#" title="click here to inspect on map...">' . $address . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $radius . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $status . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $date . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $this->base_logic->wizardDeleteRecord('landmark',$params['pid'],$report['records'],$row['territory_id']) . '</td>'
                                                                . '</tr>'; 
                                                        }
                                                    }
                                                    if(!($report['tbody'])){
                                                        $report['tbody'] = '<tr><td colspan="9"><i>No Data Found</i></td></tr>';
                                                    } else {
                                                        $report['lastReport'] = $this->base_logic->wizardMapAllLink();
                                                    }
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    break;

            case            'library-list-table' :  $sqlPlaceHolder[] = 1 ;
                                                    $report['message'] = '&nbsp;<p>';
                                                    if($params['search']){
                                                        $search = ' AND ( streetaddress LIKE \'' . $params['search'] . '%\' OR streetaddress LIKE \'%' . $params['search'] . '%\' OR city LIKE \'' . $params['search'] . '%\' OR city LIKE \'%' . $params['search'] . '%\' OR state LIKE \'' . $params['search'] . '%\' OR state LIKE \'%' . $params['search'] . '%\' OR zipcode LIKE \'' . $params['search'] . '%\' OR zipcode LIKE \'%' . $params['search'] . '%\' OR territoryname LIKE \'' . $params['search'] . '%\' OR territoryname LIKE \'%' . $params['search'] . '%\' OR createdate LIKE \'' . $params['search'] . '%\' OR createdate LIKE \'%' . $params['search'] . '%\' )' ;
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0; 
                                                    }
                                                    $sql = "SELECT *
                                                            FROM crossbones.library
                                                            WHERE active = 1"
                                                            . $search
                                                            . " ORDER BY territoryname ASC";

                                                    $rows = $this->report_data->getReport($sql, array());
                                                    
                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Landmark Name</th><th>Address</th><th>Created</th></tr>';
                                                    if(!($rows)){$report['tbody'] = '<tr><td colspan="8"><i>No Data Found</i></td></tr>';}
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    $report['code'] = 0; 
                                                    foreach ($rows as $key => $row) {
                                                        $report['code'] = 0; 
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){
                                                            $address = $this->address_logic->validateAddress($row['streetaddress'], $row['city'], $row['state'], $row['zipcode'], $row['country']);
                                                            $label = str_replace('"','\"',$address);
                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }

                                                            if(!($row['territoryname'])){
                                                                $row['territoryname'] = 'Landmark #' . $row['territory_id'] ;
                                                            }

                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '"><a class="library-edit" href="javascript:void(0);" id="library-list-table-' . $row['territory_id'] . '">' . $row['territoryname'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . 'address_map_link landmark_map_link" data-eventname="' . $label . '" data-mode="landmark" data-name="' . $row['unitname'] . ' ' . $row['territoryname'] . '" data-id="' . $row['territory_id'] . '" data-latitude="' . $row['latitude'] . '" data-longitude="' . $row['longitude'] . '" data-radius="' . $row['radius'] . '" data-shape="' . $row['shape'] . '" data-label="' . $label . '"><a href="#" title="click here to inspect on map...">' . $address . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['createdate'] . '</td>'
                                                                // . '<td class="' . $evenOdd . '">' . $this->base_logic->wizardDeleteRecord('landmark',$params['pid'],$report['records'],$row['territory_id']) . '</td>'
                                                                . '</tr>'; 
                                                        }
                                                    }
                                                    if(!($report['tbody'])){
                                                        $report['tbody'] = '<tr><td colspan="9"><i>No Data Found</i></td></tr>';
                                                    }
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    break;

            case              'options-landmarks' : $report['pid'] = 'secondary-sidebar-scroll';

                                                    $sql_params = array($account_id,$user_id);
                                                    $user['account_id'] = $account_id;
                                                    $user['user_id'] = $user_id;
                                                    $permission = $this->vehicle_data->ajaxPermissionCheck($user,'landmarks');
                                                    if((!($permission))&&(!($role_account_owner))){
                                                        $sqlPlaceHolder[] = $user_id;
                                                    }
                                                    $sqlPlaceHolder[] = 'landmark' ;
                                                    $sqlPlaceHolder[] = 1 ;

                                                    if($params['search']){
                                                        $search = ' AND ( t.territoryname LIKE \'' . $params['search'] . '%\' OR t.territoryname LIKE \'%' . $params['search'] . '%\' )' ;
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0;
                                                        $params['sidebarLandmarkGroup'] = NULL ; 
                                                    }
                                                    switch($params['sidebarLandmarkGroup']){
                                                        case                   '' :
                                                        case                'all' :
                                                        case          'undefined' : break;
                                                                          default : $landmarkgroup = ' AND t.territorygroup_id = ?' ;
                                                                                    $sqlPlaceHolder[] = $params['sidebarLandmarkGroup'];
                                                                                    $report['message'] .= ' Vehicle Group Id "' . $params['sidebarLandmarkGroup'] . '" Not Found"<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarLandmarkCategories']){
                                                        case                    0 :
                                                        case                   '' :
                                                        case                'all' :
                                                        case          'undefined' : break;
                                                                          default : $landmarkcategories = ' AND t.territorycategory_id = ?' ;
                                                                                    $sqlPlaceHolder[] = $params['sidebarLandmarkCategories'];
                                                                                    $report['message'] .= ' Vehicle Group Id "' . $params['sidebarLandmarkCategories'] . '" Not Found"<p>';
                                                                                    $report['code'] = 0; 
                                                    }

                                                    if(($permission)||($role_account_owner)){
                                                    
                                                        $sql = "SELECT
                                                                    t.territory_id as territory_id,
                                                                    t.territoryname as territoryname
                                                                FROM crossbones.territory t
                                                                WHERE t.account_id = ?
                                                                AND t.territorytype = ?
                                                                AND t.active = ?"
                                                                . $search
                                                                . $landmarkcategories
                                                                . $landmarkgroup
                                                                . " ORDER BY t.territoryname ASC";

                                                    } else {

                                                        $sql = "SELECT
                                                                    t.territory_id as territory_id,
                                                                    t.territoryname as territoryname
                                                                FROM crossbones.territory t
                                                                LEFT JOIN crossbones.user_territorygroup utg ON utg.territorygroup_id = t.territorygroup_id
                                                                WHERE t.account_id = ?
                                                                AND utg.user_id = ? 
                                                                AND t.territorytype = ?
                                                                AND t.active = ?"
                                                                . $search
                                                                . $landmarkcategories
                                                                . $landmarkgroup
                                                                . " ORDER BY t.territoryname ASC";

                                                    }

                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    
                                                    if($params['all']) {
                                                        $params['all'] = ' class="active"' ;
                                                    }

// $report['lis'] .='<li id="landmark-li-XXX"><a href="#">aaa hello world</a></li>';

                                                    foreach ($rows as $key => $row) {
                                                        if(!($row['territoryname'])){
                                                            $row['territoryname'] = 'Landmark ' . $row['territory_id'] ;
                                                        }
                                                        $report['records']++;
                                                        $report['length']++;
                                                        $report['lis'] .='<li id="landmark-li-' . $row['territory_id'] . '"' . $params['all'] . '><a href="#">' . $row['territoryname'] . '</a></li>';
                                                    }
                                                    break;

            case               'options-vehicles' : $report['pid'] = 'secondary-sidebar-scroll';

                                                    $sql_params = array($account_id,$user_id);
                                                    $user['account_id'] = $account_id;
                                                    $user['user_id'] = $user_id;
                                                    // $permission = $this->vehicle_data->ajaxPermissionCheck($user,'vehicles');
                                                    if((!($permission))&&(!($role_account_owner))){
                                                        $sqlPlaceHolder[] = $user_id;
                                                    }

                                                    if($params['search']){
                                                        $search = ' AND (' ;
                                                        $sa = explode(' ',$params['search']);
                                                        foreach ( $sa as $k => $v ) {
                                                            $arg = trim($v);
                                                            if($arg){
                                                                if($args){
                                                                    $search .= ' ) AND (' ;
                                                                }
                                                                $args = 1;
                                                                $search .= ' u.unitname LIKE \'' . $arg . '%\' OR u.unitname LIKE \'%' . $arg . '%\'' ;
                                                                $search .= ' OR c.firstname             LIKE \'' . $arg . '%\' OR c.firstname              LIKE \'%' . $arg . '%\'' ;
                                                                $search .= ' OR c.lastname              LIKE \'' . $arg . '%\' OR c.lastname               LIKE \'%' . $arg . '%\'' ;
                                                                $search .= ' OR u.serialnumber          LIKE \'' . $arg . '%\' OR u.serialnumber           LIKE \'%' . $arg . '%\'' ;
                                                                $search .= ' OR ua.color                LIKE \'' . $arg . '%\' OR ua.color                 LIKE \'%' . $arg . '%\'' ;
                                                                $search .= ' OR ua.licenseplatenumber   LIKE \'' . $arg . '%\' OR ua.licenseplatenumber    LIKE \'%' . $arg . '%\'' ;
                                                                $search .= ' OR ua.loannumber           LIKE \'' . $arg . '%\' OR ua.loannumber            LIKE \'%' . $arg . '%\'' ;
                                                                $search .= ' OR ua.make                 LIKE \'' . $arg . '%\' OR ua.make                  LIKE \'%' . $arg . '%\'' ;
                                                                $search .= ' OR ua.model                LIKE \'' . $arg . '%\' OR ua.model                 LIKE \'%' . $arg . '%\'' ;
                                                                $search .= ' OR ua.stocknumber          LIKE \'' . $arg . '%\' OR ua.stocknumber           LIKE \'%' . $arg . '%\'' ;
                                                                $search .= ' OR ua.vin                  LIKE \'' . $arg . '%\' OR ua.vin                   LIKE \'%' . $arg . '%\'' ;
                                                                $search .= ' OR ua.year                 LIKE \'' . $arg . '%\' OR ua.year                  LIKE \'%' . $arg . '%\'' ;
                                                            }
                                                        }
                                                        $search .= ' )' ;
                                                        if(!($args)){
                                                            $search = null ;
                                                        } else {
                                                            $report['search'] = $params['search'];
                                                            $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                            $report['code'] = 0; 
                                                            $params['sidebarVehicleGroup'] = NULL ; 
                                                        }
                                                    }
                                                    switch($params['sidebarVehicleGroup']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $vehiclegroup = ' AND u.unitgroup_id = ?' ;
                                                                                    $sqlPlaceHolder[] = $params['sidebarVehicleGroup'];
                                                                                    $report['message'] .= ' Vehicle Group Id "' . $params['sidebarVehicleGroup'] . '" Not Found"<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarVehicleStatus']){
                                                        case                   '' :
                                                        case                'all' : 
                                                                                    break;
                                                        case          'installed' : $vehiclestatus = ' AND u.unitstatus_id = ?' ;
                                                                                    $sqlPlaceHolder[] = '1';
                                                                                    $report['message'] .= ' Vehicle Status "' . $params['sidebarVehicleStatus'] . '" Not Found"<p>';
                                                                                    $report['code'] = 0; 
                                                                                    break; 
                                                        case          'inventory' : $vehiclestatus = ' AND u.unitstatus_id = ?' ;
                                                                                    $sqlPlaceHolder[] = '2';
                                                                                    $report['message'] .= ' Vehicle Status "' . $params['sidebarVehicleStatus'] . '" Not Found"<p>';
                                                                                    $report['code'] = 0; 
                                                                                    break; 
                                                        case      'in-a-landmark' : $vehiclestatus = ' AND u.unitstatus_id = ? AND uas.landmark_id != ?' ;
                                                                                    $sqlPlaceHolder[] = '1';
                                                                                    $sqlPlaceHolder[] = '0';
                                                                                    $report['message'] .= ' Vehicle Status "' . $params['sidebarVehicleStatus'] . '" Not Found"<p>';
                                                                                    $report['code'] = 0;
                                                                                    break; 
                                                     case 'no-movement-in-7-days' : $vehiclestatus = " AND u.unitstatus_id != ? AND ( u.lastmove < DATE_SUB(NOW(), INTERVAL 7 DAY) OR u.lastmove IS NULL )" ;
                                                                                    // $vehiclestatus = ' AND u.unitstatus_id = ? AND uas.movingevent_id != ?' ;
                                                                                    $sqlPlaceHolder[] = '2';
                                                                                    // $sqlPlaceHolder[] = '0';
                                                                                    $report['message'] .= ' Vehicle Status "' . $params['sidebarVehicleStatus'] . '" Not Found"<p>';
                                                                                    $report['code'] = 0; 
                                                                                    break; 
                                                    case 'not-reported-in-7-days' : $vehiclestatus = " AND u.unitstatus_id != ? AND ( u.lastreport < DATE_SUB(NOW(), INTERVAL 7 DAY) OR u.lastreport IS NULL )" ;
                                                                                    // $vehiclestatus = ' AND u.unitstatus_id = ? AND uas.nonreportingstatus != ?' ;
                                                                                    $sqlPlaceHolder[] = '2';
                                                                                    // $sqlPlaceHolder[] = '0';
                                                                                    $report['message'] .= ' Vehicle Status "' . $params['sidebarVehicleStatus'] . '" Not Found"<p>';
                                                                                    $report['code'] = 0; 
                                                                                    break; 
                                                        case        'reminder-on' : $vehiclestatus = ' AND u.reminderstatus = ?' ;
                                                                                    $sqlPlaceHolder[] = 'On';
                                                                                    $report['message'] .= ' Vehicle Status "' . $params['sidebarVehicleStatus'] . '" Not Found"<p>';
                                                                                    $report['code'] = 0; 
                                                                                    break; 
                                                        case       'repossession' : $vehiclestatus = ' AND u.unitstatus_id = ?' ;
                                                                                    $sqlPlaceHolder[] = '3';
                                                                                    $report['message'] .= ' Vehicle Status "' . $params['sidebarVehicleStatus'] . '" Not Found"<p>';
                                                                                    $report['code'] = 0; 
                                                                                    break; 
                                                        case   'starter-disabled' : $vehiclestatus = ' AND u.starterstatus = ?' ;
                                                                                    $sqlPlaceHolder[] = 'Disabled';
                                                                                    $report['message'] .= ' Vehicle Status "' . $params['sidebarVehicleStatus'] . '" Not Found"<p>';
                                                                                    $report['code'] = 0; 
                                                                                    break; 
                                                    }


                                                    if(($permission)||($role_account_owner)){

                                                        $sql = "SELECT
                                                                    u.*,
                                                                    uas.alertevent_id as alertevent_id,
                                                                    uas.idleevent_id as idleevent_id,
                                                                    uas.movingevent_id as movingevent_id,
                                                                    uas.speedevent_id as speedevent_id,
                                                                    uas.stopevent_id as stopevent_id,
                                                                    ug.unitgroupname as unitgroupname
                                                                FROM crossbones.unit u
                                                                LEFT JOIN crossbones.customer c ON c.unit_id = u.unit_id
                                                                LEFT JOIN crossbones.unitalertstatus uas ON uas.unit_id = u.unit_id
                                                                LEFT JOIN crossbones.unitattribute ua ON ua.unit_id = u.unit_id
                                                                LEFT JOIN crossbones.unitgroup ug ON ug.unitgroup_id = u.unitgroup_id
                                                                WHERE u.account_id = ?"
                                                                . $search
                                                                . $vehiclegroup
                                                                . $vehiclestatus
                                                                . " ORDER BY u.unitstatus_id ASC, u.unitname ASC";

                                                    } else {

                                                        $sql = "SELECT
                                                                    u.*,
                                                                    uas.alertevent_id as alertevent_id,
                                                                    uas.idleevent_id as idleevent_id,
                                                                    uas.movingevent_id as movingevent_id,
                                                                    uas.speedevent_id as speedevent_id,
                                                                    uas.stopevent_id as stopevent_id,
                                                                    ug.unitgroupname as unitgroupname
                                                                FROM crossbones.unit u
                                                                LEFT JOIN crossbones.customer c ON c.unit_id = u.unit_id
                                                                LEFT JOIN crossbones.unitalertstatus uas ON uas.unit_id = u.unit_id
                                                                LEFT JOIN crossbones.unitattribute ua ON ua.unit_id = u.unit_id
                                                                LEFT JOIN crossbones.unitgroup ug ON ug.unitgroup_id = u.unitgroup_id
                                                                LEFT JOIN crossbones.user_unitgroup utg ON utg.unitgroup_id = ug.unitgroup_id
                                                                WHERE u.account_id = ?
                                                                AND utg.user_id = ?
                                                                AND utg.unitgroup_id IS NOT NULL"
                                                                . $search
                                                                . $vehiclegroup
                                                                . $vehiclestatus
                                                                . " ORDER BY u.unitstatus_id ASC, u.unitname ASC";

                                                    }

// $report['alert'] = $sql . ' : ' . implode(', ', $sqlPlaceHolder);

                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                            
                                                    if($params['all']) {
                                                        $params['all'] = ' class="active"' ;
                                                    }

                                                    foreach ($rows as $key => $row) {
                                                        
                                                        switch($row['unitstatus_id']) {

                                                            case                   1  :
                                                            case                  '1' : $unitstatus_id = '';
                                                                                        break;

                                                            case                   2  :
                                                            case                  '2' : $unitstatus_id = 'class="li-inventory" title="Inventory"';
                                                                                        break;

                                                            case                   3  :
                                                            case                  '3' : $unitstatus_id = 'class="li-repossession" title="Repossession"';
                                                                                        break;

                                                                              default : $unitstatus_id = null ;
                                                        
                                                        }

                                                        $report['code'] = 0; 

                                                        $sql = "SELECT
                                                                    ue1.*,
                                                                    ume.eventname as eventname
                                                                FROM " . $row['db'] . ".unit" . $row['unit_id'] . " ue1
                                                                LEFT JOIN unitmanagement.event ume ON ume.event_id = ue1.event_id
                                                                WHERE ue1.id IS NOT NULL AND ue1.event_id < 14 AND ue1.event_id != 1 AND ue1.event_id != 7 AND ue1.event_id != 8 
                                                                ORDER BY ue1.unittime DESC LIMIT 1";
                                                        $laststatus = $this->report_data->getReport($sql, $sqlPlaceHolder);

                                                        $stopped = NULL;
                                                        $status = NULL;

                                                        switch ($laststatus[0]['event_id']) {

                                                            case                          2 :
                                                            case                          5 :
                                                            case                          6 :
                                                            case                         10 :
                                                            case                         13 :   $status = '<span class="label label-danger">Stopped</span>' ;
                                                                                                $stopped = 1; 
                                                                                                break;

                                                            case                          3 :
                                                            case                          4 :
                                                            case                          9 :
                                                            case                         11 :
                                                            case                         12 :   if($days<1){
                                                                                                    $status = '<span class="label label-success">Moving</span>' ; 
                                                                                                } else {
                                                                                                    $status = 'Moving*' ; 
                                                                                                }
                                                                                                break;
                                                        }

                                                        if(!($row['unitname'])){
                                                            $row['unitname'] = $row['serialnumber'] ;
                                                        }

                                                        $report['lis'] .='<li id="vehicle-li-' . $row['unit_id'] . '"' . $params['all'] . ' title="' . $row['unitgroupname'] . '"><a href="#"' . $unitstatus_id . '>' . $row['unitname'] . '</a></li>';

                                                    }
                                                    break;

            case             'sales-report-table' : $report['message'] = '&nbsp;<p>';
                                                    if($params['search']){
                                                        // $search = ' AND ( cg.contactgroupname LIKE \'' . $params['search'] . '%\' OR cg.contactgroupname LIKE \'%' . $params['search'] . '%\' OR ug.unitgroupname LIKE \'' . $params['search'] . '%\' OR ug.unitgroupname LIKE \'%' . $params['search'] . '%\' OR c.firstname LIKE \'' . $params['search'] . '%\' OR c.firstname LIKE \'%' . $params['search'] . '%\' OR c.lastname LIKE \'' . $params['search'] . '%\' OR c.lastname LIKE \'%' . $params['search'] . '%\' OR c.email LIKE \'' . $params['search'] . '%\' OR c.email LIKE \'' . $params['search'] . '%\' OR a.alertname LIKE \'' . $params['search'] . '%\' OR a.alertname LIKE \'%' . $params['search'] . '%\' OR at.alerttypename LIKE \'' . $params['search'] . '%\' OR at.alerttypename LIKE \'%' . $params['search'] . '%\' )' ;
                                                        // $sqlPlaceHolder[] = $params['sidebarContactMethod'];
                                                        // $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0; 
                                                    }
                                                    $sql = "SELECT
                                                                o.*,
                                                                r.name as rep_name
                                                            FROM marketing.orders o
                                                            LEFT JOIN marketing.reps r ON r.reps_id = o.reps_id
                                                            WHERE o.orders_id > 0
                                                            AND r.reps_id > 0"
                                                            . $search
                                                            . " ORDER BY r.name ASC";
                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);

                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Revenue</th><th>Units</th><th>Transactions</th><th>Representative</th><th>Manager</th></tr>';
                                                    if(!($rows)){$report['tbody'] = '<tr><td colspan="5"><i>No Data Found</i></td></tr>';}
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        $report['code'] = 0; 
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){
                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }
                                                            $report['tbody'] .= str_replace('#_#',' class="' . $evenOdd . '"','<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['orders_id'] .'</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['rep_name'] .'</td>'
                                                                . '<td class="' . $evenOdd . '">' . date('M d, Y h:i a' , strtotime($this->base_logic->tzUtc2Local ( $timezone , $row['updated'] ))) . '</td>' // this is it
                                                                . '</tr>'); 
                                                        }
                                                    }
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    break;

            case                'repo-list-table' : $report['message'] = '&nbsp;<p>';
                                                    if($params['search']){
                                                        $search = ' AND ( r.email LIKE \'' . $params['search'] . '%\' OR r.email LIKE \'%' . $params['search'] . '%\' )' ;
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0; 
                                                    }
                                                    $expirationDays = 15;
                                                    $sql = "SELECT
                                                            r.*,
                                                            u.unitname as unitname
                                                            FROM crossbones.repo r
                                                            LEFT JOIN crossbones.unit u ON u.unit_id = r.unit_id
                                                            WHERE r.account_id = ?
                                                            AND r.createdate > DATE_SUB(now(),INTERVAL " . $expirationDays . " DAY)
                                                            AND r.active != 1"
                                                            . $search
                                                            . " ORDER BY r.email ASC";

                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    
                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Vehicle</th><th>URL</th><th>Name</th><th>Email</th><th>Phone</th><th>Expiration</th><th class="tinywidth">Delete</th></tr>';
                                                    $page=1;
                                                    foreach ($rows as $key => $row) {
                                                        $report['code'] = 0; 
                                                        $report['records']++;
                                                        $length++;
                                                        if($page==$params['pageCount']){
                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }
                                                            $expiration = date("Y-m-d 00:00:00",strtotime(date("Y-m-d 00:00:00", strtotime($row['createdate'])) . " +" . $expirationDays . " days")) ;
                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['unitname'] . '</td>'
                                                                . '<td class="' . $evenOdd . ' repo-url"><a href="/repo/' . $row['url'] . '">/repo/' . $row['url'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . ' repo-name"><a class="modal-repo-edit-link" href="javascript:void(0);">' . $row['name'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . ' repo-email"><a class="modal-repo-edit-link" href="javascript:void(0);">' . $row['email'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . ' repo-phone"><a class="modal-repo-edit-link" href="javascript:void(0);">' . $row['phone'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $this->base_logic->wizardTzAdj('m/d/Y',$expiration,$timezone) . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $this->base_logic->wizardDeleteRecord('repo',$params['pid'],$report['records'],$row['repo_id']) . '</td>'
                                                                . '</tr>'; 
                                                        }
                                                    }
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    if(!($report['tbody'])){$report['tbody'] = '<tr><td colspan="4"><i>No Data Found</i></td></tr>';}
                                                    break;

            case           'report-history-table' : $report['message'] = '&nbsp;<p>';
                                                    if($params['search']){
                                                        $search = ' AND ( rh.reporthistoryname LIKE \'' . $params['search'] . '%\' OR rh.reporthistoryname LIKE \'%' . $params['search'] . '%\' OR rt.reporttypename LIKE \'' . $params['search'] . '%\' OR rt.reporttypename LIKE \'%' . $params['search'] . '%\' OR u.firstname LIKE \'' . $params['search'] . '%\' OR u.firstname LIKE \'%' . $params['search'] . '%\' OR u.lastname LIKE \'' . $params['search'] . '%\' OR u.lastname LIKE \'%' . $params['search'] . '%\' OR u.email LIKE \'' . $params['search'] . '%\' OR u.email LIKE \'' . $params['search'] . '%\' )' ;
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarContactSingle']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $contact = ' AND rh.user_id = ?' ;
                                                                                    $sqlPlaceHolder[] = $params['sidebarContactSingle'] ;
                                                                                    $report['message'] .= ' Contact Not Found"<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarDateRange']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $daterange = ' AND rh.createdate > DATE_SUB(now(),INTERVAL ' . $params['sidebarDateRange'] . ' DAY)' ;
                                                                                    $report['message'] .= ' Contact Not Found"<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarReportType']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $reporttype = ' AND rt.reporttype_id = ?' ;
                                                                                    $sqlPlaceHolder[] = $params['sidebarReportType'] ;
                                                                                    $report['message'] .= ' Type Not Found<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    $sqlPlaceHolder[] = $user_id;
                                                    $sql = "SELECT
                                                                rh.*,
                                                                rt.reporttypename as reporttypename,
                                                                u.firstname as firstname,
                                                                u.lastname as lastname,
                                                                u.email as email
                                                            FROM crossbones.reporthistory rh
                                                            LEFT JOIN crossbones.user u ON u.user_id = rh.user_id
                                                            LEFT JOIN crossbones.reporttype rt ON rt.reporttype_id = rh.reporttype_id
                                                            LEFT JOIN crossbones.reporthistory_user rhu ON rhu.reporthistory_id = rh.reporthistory_id
                                                            WHERE rh.account_id = ?
                                                            AND rhu.user_id = ?
                                                            AND u.userstatus_id > 0"
                                                            . $search
                                                            . $contact
                                                            . $daterange
                                                            . $reporttype
                                                            . " ORDER BY rh.reporthistoryname ASC";

                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    
                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Report Name</th><th>Report Type</th><th>Scheduled/Manual</th><th>Contact</th><th>Ran On</th></tr>';
                                                    if(!($rows)){$report['tbody'] = '<tr><td colspan="6"><i>No Data Found</i></td></tr>';}
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        $report['code'] = 0; 
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){
                                                            $onClick="Core.DataTable.pop('modal-edit-scheduled-report','modal-title=" . str_replace("'", "\'", $row['reporttypename']) . ";','modal-edit-scheduled-report-name=" . str_replace("'", "\'", $row['schedulereportname']) . ";','scheduled-recurrence=" . str_replace("'", "\'", $row['schedule']) . ";scheduled-day=" . str_replace("'", "\'", $row['scheduleday']) . ";scheduled-monthly=" . str_replace("'", "\'", $row['monthday']) . ";scheduled-time=" . str_replace("'", "\'", $row['sendhour']) . ";scheduled-format=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-mode=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-single=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-group=" . str_replace("'", "\'", $row['format']) . ";');";
                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }
                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['reporthistoryname'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['reporttypename'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['method'] . '</td>'
                                                                . '<td class="' . $evenOdd . '" title="' . $row['email'] . '">' . $row['firstname'] . ' ' . $row['lastname'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $this->base_logic->wizardTzAdj('m/d/Y h:ia',$row['createdate'],$timezone) . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . date('m/d/Y h:ia' , strtotime($row['createdate'])) . '</td>'
                                                                . '</tr>'; 
                                                        }
                                                    }
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    break;

            case         'report-scheduled-table' : $report['message'] = '&nbsp;<p>';
                                                    $sqlPlaceHolder[] = 1 ;
                                                    $report['code'] = 0; 
                                                    if($params['search']){
                                                        $search = ' AND ( sr.schedulereportname LIKE \'' . $params['search'] . '%\' OR sr.schedulereportname LIKE \'%' . $params['search'] . '%\' OR rt.reporttypename LIKE \'' . $params['search'] . '%\' OR rt.reporttypename LIKE \'%' . $params['search'] . '%\' OR c.firstname LIKE \'' . $params['search'] . '%\' OR c.firstname LIKE \'%' . $params['search'] . '%\' OR c.lastname LIKE \'' . $params['search'] . '%\' OR c.lastname LIKE \'%' . $params['search'] . '%\' OR cg.contactgroupname LIKE \'%' . $params['search'] . '%\' )' ;
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                    }
                                                    switch($params['sidebarType']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $type = ' AND sr.reporttype_id = \'' . $params['sidebarType'] . '\'' ;
                                                                                    $report['message'] .= ' Type Not Found<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarContactMode']){
                                                        case                     'group' : $group = ' AND sr.unitgroup_id = \'' . $params['sidebarContactGroup'] . '\'' ;
                                                                                           $report['message'] .= ' Contact Group Not Found<p>';
                                                                                           $report['code'] = 0; 
                                                                                           break;
                                                        case                    'single' : $single = ' AND sr.user_id = \'' . $params['sidebarContactSingle'] . '\'' ;
                                                                                           $report['message'] .= ' Contact Not Found<p>';
                                                                                           $report['code'] = 0; 
                                                                                           break;
                                                    }
                                                    switch($params['sidebarRecurrence']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $recurrence = ' AND sr.schedule = \'' . $params['sidebarRecurrence'] . '\'' ;
                                                                                    $report['message'] .= ' Schedule Not Found"<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    $sql = "SELECT
                                                                sr.*,
                                                                src.contact_id as contact_id,
                                                                src.contactgroup_id as contactgroup_id,
                                                                rt.reporttypename as reporttypename,
                                                                c.active as contactactive,
                                                                c.firstname as firstname,
                                                                c.lastname as lastname,
                                                                cg.active as contactgroupactive,
                                                                cg.contactgroupname as contactgroupname
                                                            FROM crossbones.schedulereport sr
                                                            LEFT JOIN crossbones.schedulereport_contact src ON src.schedulereport_id = sr.schedulereport_id
                                                            LEFT JOIN crossbones.contact c ON c.contact_id = src.contact_id
                                                            LEFT JOIN crossbones.contactgroup cg ON cg.contactgroup_id = src.contactgroup_id
                                                            LEFT JOIN crossbones.reporttype rt ON rt.reporttype_id = sr.reporttype_id
                                                            WHERE sr.account_id = ?
                                                            AND sr.active = ?"
                                                            . $type
                                                            . $search
                                                            . $group
                                                            . $single
                                                            . $recurrence
                                                            . " ORDER BY sr.schedulereportname ASC";

                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);

                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Name</th><th>Type</th><th>Contact</th><th>Schedule</th><th>Days</th><th>Time</th><th>Next Run</th><th class="tinywidth">Delete</th></tr>';
                                                    if(!($rows)){$report['tbody'] = '<tr><td colspan="8"><i>No Data Found</i></td></tr>';}
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        $report['code'] = 0; 
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($row['lastruntime']=='0000-00-00 00:00:00'){
                                                            $row['lastruntime'] = $row['createdate'];
                                                        }
                                                        if($page==$params['pageCount']){
                                                            // $onClick="Core.DataTable.pop('modal-edit-scheduled-report','modal-title=" . str_replace("'", "\'", $row['reporttypename']) . ";','modal-edit-scheduled-report-name=" . str_replace("'", "\'", $row['schedulereportname']) . ";','scheduled-recurrence=" . str_replace("'", "\'", $row['schedule']) . ";scheduled-day=" . str_replace("'", "\'", $row['scheduleday']) . ";scheduled-monthly=" . str_replace("'", "\'", $row['monthday']) . ";scheduled-time=" . str_replace("'", "\'", $row['sendhour']) . ";scheduled-format=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-mode=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-single=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-group=" . str_replace("'", "\'", $row['format']) . ";');";
                                                            $onClick="Core.ClearForm('scheduled-report','" . str_replace("'", "\'", $row['schedulereportname']) . "','" . $row['schedulereport_id'] . "','" . str_replace("'", "\'", $row['reporttypename']) . "');";
                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }
                                                            switch($row['sendhour']){
                                                                case              0 : $sendhour = '12:00 a' ;
                                                                                      break;
                                                                case              1 : 
                                                                case              2 : 
                                                                case              3 : 
                                                                case              4 : 
                                                                case              5 : 
                                                                case              6 : 
                                                                case              7 : 
                                                                case              8 :
                                                                case              9 : 
                                                                case             10 : 
                                                                case             11 : $sendhour = $row['sendhour'] . ':00 a' ;
                                                                                      break;
                                                                            default : $row['sendhour'] = $row['sendhour'] + 12 ;
                                                                                      $sendhour = $row['sendhour'] . ':00 p' ;
                                                            }
                                                            if($row['contactactive']){
                                                                $row['firstname'] = null;
                                                                $row['lastname'] = null;
                                                            }
                                                            if($row['contactgroupactive']){
                                                                $row['contactgroupname'] = null;
                                                            }
                                                            if($row['contact_id']){
                                                                $contact = $row['firstname'] . ' ' . $row['lastname'];
                                                            } else if($row['contactgroup_id']){
                                                                $contact = $row['contactgroupname'];
                                                            } else {
                                                                $contact = 'All Contacts' ;
                                                            }
                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '"><a href="#modal-edit-scheduled-report" data-toggle="modal" onClick="' . $onClick . '">' . $row['schedulereportname'] . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['reporttypename'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $contact . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['schedule'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['scheduleday'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $sendhour . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . Date::utc_to_locale($row['nextruntime'], $timezone, 'h:i A m/d/Y') . '</td>'
                                                                // . '<td class="' . $evenOdd . '">' . date('m/d/Y h:00 a', strtotime($this->base_logic->timezoneDelta( $timezone , $row['nextruntime'] , 1 , 1 ))) . '</td>'        
                                                                . '<td class="' . $evenOdd . '">' . $this->base_logic->wizardDeleteRecord('scheduled-report',$params['pid'],$report['records'],$row['schedulereport_id']) . '</td>'
                                                                . '</tr>'; 
                                                        }
                                                    }
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    break;

            case               'stops-report-all' : $sqlPlaceHolder = array();
                                                    $report['message'] = '&nbsp;<p>';
                                                    if($params['search']){
                                                        $search = ' AND ( ume.eventname LIKE \'' . $params['search'] . '%\' OR ume.eventname LIKE \'%' . $params['search'] . '%\' OR ue1.streetaddress LIKE \'' . $params['search'] . '%\' OR ue1.streetaddress LIKE \'%' . $params['search'] . '%\' OR ue1.city LIKE \'' . $params['search'] . '%\' OR ue1.city LIKE \'%' . $params['search'] . '%\' OR ue1.state LIKE \'' . $params['search'] . '%\' OR ue1.state LIKE \'%' . $params['search'] . '%\' OR ue1.zipcode LIKE \'' . $params['search'] . '%\' OR ue1.zipcode LIKE \'%' . $params['search'] . '%\' )' ;
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0; 
                                                    }
                                                    switch($params['daterange']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $params['daterange']++;
                                                                                    $daterange = " AND ue1.unittime >= DATE_SUB(?, INTERVAL ? DAY)" ;
                                                                                    $sqlPlaceHolder[] = $tonightUtc ;
                                                                                    $sqlPlaceHolder[] = $params['daterange'];
                                                                                    $report['message'] .= ' Date Range Not Found<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    $report['code'] = 0; 
                                                    $sql = "SELECT
                                                                t.account_id as account_id,
                                                                t.territoryname as territoryname,
                                                                ue1.*,
                                                                ume.eventname as eventname
                                                            FROM " . $params['db'] . ".unit" . $params['unit_id'] . " ue1
                                                            LEFT JOIN unitmanagement.event ume ON ume.event_id = ue1.event_id
                                                            LEFT JOIN crossbones.territory t ON t.territory_id = ue1.landmark_id
                                                            WHERE ue1.id IS NOT NULL"
                                                            // . " AND ue1.event_id < 6"
                                                            . $search
                                                            . $daterange
                                                            . " AND ue1.event_id IS NOT NULL 
                                                            ORDER BY ue1.unittime DESC";

                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    
                                                    $lastunittime=date('Y-m-d H:i:s');
                                                    $report['thead'] = '<tr><th>Map Point</th><th>Date & Time <span class="text-grey" title="' . $tonight . '">(' . str_replace('_', ' ', $timezone) . ')</span></th><th>Event</th><th>Location</th><th>Speed</th><th>Duration</th></tr>'; 
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        if (!($lastunittime)) {
                                                            $lastunittime = $row['unittime'] ;
                                                        }
                                                        $report['records']++;
                                                        $duration = null ;
                                                        if( $lastunittime ){
                                                            $duration=strtotime($lastunittime)-strtotime($row['unittime']);
                                                            if($duration>=$params['duration']){
                                                                $days = floor ( $duration / 86400 );
                                                                $duration = $duration - ( $days * 86400 ) ;
                                                                $hours = floor ( $duration / 3600 );
                                                                $duration = $duration - ( $hours * 3600 ) ;
                                                                $minutes = floor ( $duration / 60 );
                                                                $seconds = $duration - ( $minutes * 60 ) ;
                                                                $duration = null ;
                                                                if ( $days ) { $duration .= $days . ' Days'; }
                                                                if ( ( $duration ) && ( $hours ) ) { $duration .= ', '; }
                                                                if ( $hours ) { $duration .= $hours . ' Hours'; }
                                                                if ( ( $duration ) && ( $minutes ) ) { $duration .= ', '; }
                                                                if ( $minutes ) { $duration .= $minutes . ' Minutes'; }
                                                                // if ( $duration ) { $duration .= ', '; }
                                                                // if ( $seconds ) { $duration .= $seconds . ' Seconds'; }
                                                            } else {
                                                                $duration = null ;
                                                            }
                                                        }
                                                        switch($row['event_id']){
                                                            case             1  :
                                                            case            '1' :
                                                            case             3  :
                                                            case            '3' :
                                                            case             4  :
                                                            case            '4' : $duration = null ;
                                                                                  $lastunittime = $row['unittime'] ;
                                                                                  break;
                                                            case             2  :
                                                            case            '2' :
                                                            case             5  :
                                                            case            '5' :
                                                            case            13  :
                                                            case           '13' :
                                                            case            44  :
                                                            case           '44' :
                                                            case           112  :
                                                            case          '112' : $lastunittime = $row['unittime'] ;
                                                                                  break;
                                                                        default : $duration = null ;
                                                        }
                                                        $report['code'] = 0; 
                                                        $address = $this->address_logic->validateAddress($row['streetaddress'], $row['city'], $row['state'], $row['zipcode'], $row['country']);
                                                        if(($row['territoryname'])&&($row['account_id']==$account_id)){
                                                            $address = '(' . $row['territoryname'] . ') ' . $address;
                                                        }
                                                        $label = str_replace('"','\"',$address);
                                                        // $unittime = date('m/d/Y h:ia' , strtotime($this->base_logic->timezoneDelta($timezone,$row['unittime'],1))) ;
                                                        $unittime = Date::utc_to_locale($row['unittime'], $timezone, 'h:i A m/d/Y');
                                                        if($params['breadcrumbs']){
                                                            $report['breadcrumbs']++;
                                                            $breadcrumb[$report['breadcrumbs']]['address'] = $address ;
                                                            $breadcrumb[$report['breadcrumbs']]['latitude'] = $row['latitude'] ;
                                                            $breadcrumb[$report['breadcrumbs']]['longitude'] = $row['longitude'] ;
                                                            // $breadcrumb[$report['breadcrumbs']]['mappoint'] = $records ;
                                                            $breadcrumb[$report['breadcrumbs']]['speed'] = $row['speed'] ;
                                                            $breadcrumb[$report['breadcrumbs']]['unittime'] = $unittime ;
                                                            $breadcrumb[$report['breadcrumbs']]['eventname'] = $row['eventname'] ;
                                                            $report['breadcrumbtrail'] = $breadcrumb; 
                                                        }
                                                        switch($evenOdd){
                                                            case     '' : $evenOdd = 'report-even-odd ';
                                                                            break;
                                                                default : $evenOdd = '';
                                                        }
                                                        $final['evenOdd'] =  $evenOdd ;
                                                        $final['unittime'] = $unittime ;
                                                        $final['event_id'] = $row['event_id'] ;
                                                        $final['eventname'] = $row['eventname'] ;
                                                        $final['latitude'] = $row['latitude'] ;
                                                        $final['longitude'] = $row['longitude'] ;
                                                        $final['label'] = $label ;
                                                        $final['title'] = $row['latitude'] . ' / ' . $row['longitude'] ;
                                                        $final['address'] = $address ;
                                                        $final['speed'] = floor($row['speed']) ;
                                                        $final['duration'] = $duration ;
                                                        $finals[] = $final;
                                                    }

                                                    $records=0;
                                                    foreach ($finals as $key => $final) {
                                                        $records++;
                                                    }
                                                    
                                                    foreach ($finals as $key => $final) {
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if( $page==$params['pageCount'] ){

                                                            if (!( trim($final['address']) )){
                                                                $final['address'] = $final['latitude'] . ' / ' . $final['longitude'] ;
                                                            }
                                                            
                                                            $report['tbody'] .= '<tr>
                                                                <td class="' . $final['evenOdd'] . '">' . $records . '</td>
                                                                <td class="' . $final['evenOdd'] . '">' . $final['unittime'] . '</td>
                                                                <td class="' . $final['evenOdd'] . '" title="Event Id #' . $final['event_id'] . '">' . $final['eventname'] . '</td>
                                                                <td class="' . $final['evenOdd'] . 'address_map_toggle" data-rank="' . $records . '" data-latitude="' . $final['latitude'] . '" data-longitude="' . $final['longitude'] . '" data-label="' . $final['label'] . '" title="' . $final['title'] . '"><a href="#">' . $final['address'] . '</a></td>
                                                                <td class="' . $final['evenOdd'] . '">' . $final['speed'] . '</td>
                                                                <td class="' . $final['evenOdd'] . '">' . $final['duration'] . '</td>
                                                                </tr>'; 
                                                            $report['breadcrumbtrail'][$records]['show'] = 1; 
                                                        } else {
                                                            $report['breadcrumbtrail'][$records]['skip'] = 1; 
                                                        }
                                                        $report['breadcrumbtrail'][$records]['mappoint'] = $records; 
                                                        $records--;
                                                    }

                                                    if(!($report['tbody'])){$report['tbody'] = '<tr><td colspan="7"><i>No Data Found</i>&nbsp;<br>Please adjust filters and try again</td></tr>';}
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    break;

            case          'stops-report-frequent' : $sqlPlaceHolder = array();
                                                    $report['message'] = '&nbsp;<p>';
                                                    if($params['repoKey']){
                                                        $repoKey = explode ( 'X' , $params['repoKey'] ) ; 
                                                        $params['unit_id'] = $repoKey[0] ; 
                                                        $sql = "SELECT
                                                                ua.*,
                                                                tz.timezone as timezone
                                                                FROM crossbones.repo r
                                                                LEFT JOIN crossbones.unit u ON u.unit_id = r.unit_id
                                                                LEFT JOIN crossbones.unitattribute ua ON ua.unit_id = r.unit_id
                                                                LEFT JOIN unitmanagement.timezone tz ON tz.timezone_id = u.timezone_id
                                                                WHERE r.url = ?
                                                                AND r.active = ?
                                                                LIMIT 1";

                                                        $rows = $this->report_data->getReport($sql, array($params['repoKey'],'0'));

                                                        $params['unit_id'] = $rows[0]['unit_id'];
                                                        $timezone = $rows[0]['timezone'];
                                                        $color = $rows[0]['color'];
                                                        $licenseplatenumber = $rows[0]['licenseplatenumber'];
                                                        $make = $rows[0]['make'];
                                                        $model = $rows[0]['model'];
                                                        $vin = $rows[0]['vin'];
                                                        $year = $rows[0]['year'];
                                                    }
                                                    $report['message'] = '&nbsp;<p>';
                                                    if($params['search']){
                                                        $search = ' AND ( ume.eventname LIKE \'' . $params['search'] . '%\' OR ume.eventname LIKE \'%' . $params['search'] . '%\' OR ue1.streetaddress LIKE \'' . $params['search'] . '%\' OR ue1.streetaddress LIKE \'%' . $params['search'] . '%\' OR ue1.city LIKE \'' . $params['search'] . '%\' OR ue1.city LIKE \'%' . $params['search'] . '%\' OR ue1.state LIKE \'' . $params['search'] . '%\' OR ue1.state LIKE \'%' . $params['search'] . '%\' OR ue1.zipcode LIKE \'' . $params['search'] . '%\' OR ue1.zipcode LIKE \'%' . $params['search'] . '%\' )' ;
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0; 
                                                    }
                                                    switch($params['daterange']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $params['daterange']++;
                                                                                    $daterange = " AND ue1.unittime >= DATE_SUB(?, INTERVAL ? DAY)" ;
                                                                                    $sqlPlaceHolder[] = $tonightUtc ;
                                                                                    $sqlPlaceHolder[] = $params['daterange'];
                                                                                    $report['message'] .= ' Date Range Not Found"<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    $report['code'] = 0; 
                                                    $sql = "SELECT
                                                                t.account_id as account_id,
                                                                t.active as tact,
                                                                t.territoryname as territoryname,
                                                                t.latitude as tlat,
                                                                t.longitude as tlong,
                                                                t.radius as tradius,
                                                                ue1.*,
                                                                ume.eventname as eventname
                                                            FROM " . $params['db'] . ".unit" . $params['unit_id'] . " ue1
                                                            LEFT JOIN unitmanagement.event ume ON ume.event_id = ue1.event_id
                                                            LEFT JOIN crossbones.territory t ON t.territory_id = ue1.landmark_id
                                                            WHERE ue1.id IS NOT NULL"
                                                            . $search
                                                            . $daterange                                                            
                                                            . " AND ue1.event_id IS NOT NULL" 
                                                            // . " AND ( ue1.landmark_id < 1 OR t.active > 0 )" 
                                                            . " ORDER BY ue1.unittime DESC";

                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    
                                                    // $lastunittime=date('Y-m-d H:i:s');
                                                    $lastunittime = Date::locale_to_locale(date('Y-m-d H:i:s'), SERVER_TIMEZONE, $timezone, 'Y-m-d H:i:s');
                                                    $records=0;
                                                    foreach ($rows as $key => $row) {
                                                        $duration = null ;
                                                        if( ($lastunittime) && ( ($row['eventname']=='Stop') || ($row['event_id']==2) || ($row['event_id']==5) ) ){
                                                            $duration=strtotime($lastunittime)-strtotime($row['unittime']);
                                                            $aveDuration=$duration;
                                                            $durationx=1000000000+$duration;
                                                            if($duration>=$params['duration']){
                                                                $days = floor ( $duration / 86400 );
                                                                $duration = $duration - ( $days * 86400 ) ;
                                                                $hours = floor ( $duration / 3600 );
                                                                $duration = $duration - ( $hours * 3600 ) ;
                                                                $minutes = floor ( $duration / 60 );
                                                                $seconds = $duration - ( $minutes * 60 ) ;
                                                                $duration = null ;
                                                                if ( $days ) { 
                                                                    $duration .= $days . 'd'; 
                                                                }
                                                                if ( $hours ) {
                                                                    $duration .= $hours . 'h' ;
                                                                }
                                                                if ( $minutes ) { 
                                                                    $duration .= $minutes . 'm' ; 
                                                                }
                                                            } else {
                                                                $duration = null ;
                                                            }
                                                        }
                                                        $lastunittime = $row['unittime'] ;

                                                        if(($duration)&&( ( ($row['eventname']=='Stop') || ($row['event_id']==2) || ($row['event_id']==5) ) )){
                                                            //
                                                            //
                                                            // $tzTime = $this->base_logic->timezoneDelta($timezone,$row['unittime'],1);
                                                            $tzTime = Date::utc_to_locale($row['unittime'], $timezone, 'Y-m-d H:i:s');
                                                            //
                                                            //
                                                            $address = $this->address_logic->validateAddress(str_replace('no address','',$row['streetaddress']), $row['city'], $row['state'], $row['zipcode'], $row['country']);
                                                            if(($row['active'])&&($row['territoryname'])&&($row['account_id']==$account_id)){
                                                                $address = '(' . $row['territoryname'] . ') ' . $address;
                                                            }
                                                            $label = str_replace('"','\"',$address);
                                                            $unittime = date('h:ia m/d/Y' , strtotime($tzTime)) ;
                                                            //
                                                            if(($row['tact'])&&($row['territory_id'])&&($row['tlat'])&&($row['tlong'])&&($row['tradius']<5500)){
                                                                $lat = $row['tlat'];
                                                                $lng = $row['tlong'];
                                                            } else {
                                                                $lat = $row['latitude'];
                                                                $lng = $row['longitude'];
                                                            }
                                                            $softLat = floor($lat * 100);
                                                            $softLng = floor($lng * 100);
                                                            $buffer = $softLat . '_' . $softLng ;
                                                            //
                                                            $frequent[$buffer]['stops']++ ;
                                                            $frequent[$buffer]['address'] = $address ;
                                                            $frequent[$buffer]['latitude'] = $row['latitude'] ;
                                                            $frequent[$buffer]['longitude'] = $row['longitude'] ;
                                                            $frequent[$buffer]['landmark_id'] = $row['landmark_id'] ;
                                                            $frequent[$buffer]['tlat'] = $row['tlat'] ;
                                                            $frequent[$buffer]['tlong'] = $row['tlong'] ;
                                                            //
                                                            // $frequent[$buffer]['utc'][date('U' , strtotime($tzTime))]++;
                                                            $frequent[$buffer]['tod'][date('a_ga' , strtotime($tzTime))]++;
                                                            $frequent[$buffer]['dow'][date('D' , strtotime($tzTime))]++;
                                                            $frequent[$buffer]['details'][date('N' , strtotime($tzTime))][date('U' , strtotime($tzTime))][] = '<th>' . date('m/d/Y g:ia' , strtotime($tzTime)) . '&nbsp;&nbsp;<span class="text-10 text-grey">' . $duration . '</span></th>' ; 
                                                            $frequent[$buffer]['aveDurationTime'] = $frequent[$buffer]['aveDurationTime'] + $aveDuration;
                                                            $frequent[$buffer]['aveDurationCount']++;
                                                            //
                                                            $sort[$buffer] = $frequent[$buffer]['stops'] ;
                                                            //
                                                        }
                                                    }
                                                    arsort($sort);

                                                    $report['thead'] = '<tr><th class="tinywidth">Map Point</th><th>Stops</th><th>Location</th><th>Average Duration</th><th>Most&nbsp;Frequent&nbsp;Arrival Day&nbsp;of&nbsp;Week</th><th colspan="2">Most&nbsp;Frequent&nbsp;Arrival Hour&nbsp;of&nbsp;Day</th></tr>'; 
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($sort as $key => $stops) {
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){
                                                            
                                                            $report['code'] = 0; 
                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }

                                                            $aveDuration = floor ( $frequent[$key]['aveDurationTime'] / $frequent[$key]['aveDurationCount'] ) ;
                                                            if($aveDuration>=0){
                                                                $days = floor ( $aveDuration / 86400 );
                                                                $aveDuration = $aveDuration - ( $days * 86400 ) ;
                                                                $hours = floor ( $aveDuration / 3600 );
                                                                $aveDuration = $aveDuration - ( $hours * 3600 ) ;
                                                                $minutes = floor ( $aveDuration / 60 );
                                                                $seconds = $aveDuration - ( $minutes * 60 ) ;
                                                                $aveDuration = null ;
                                                                if ( $days ) { 
                                                                    $aveDuration .= $days . 'd'; 
                                                                }
                                                                if ( $hours ) {
                                                                    $aveDuration .= $hours . 'h' ;
                                                                }
                                                                if ( $minutes ) { 
                                                                    $aveDuration .= $minutes . 'm' ; 
                                                                }
                                                            }

                                                            $dow = null;
                                                            $dowCnt = 0 ;
                                                            foreach ($frequent[$key]['dow'] as $kk => $vv ) {
                                                                if($vv > $dowCnt){
                                                                    $dowCnt = $vv ;                                                                    
                                                                    $dow = $kk;
                                                                } else if($vv == $dowCnt){                                                                    
                                                                    $dow .= ', ' . $kk;
                                                                }
                                                            }
                                                            $buffer =  floor ( $dowCnt / $frequent[$key]['stops'] * 100 ) ;
                                                            $dowLink = $dow . ' ' . $buffer . '%';
                                                            $dow .= ' <span class="text-grey">' . $buffer . '%</span>';

                                                            $in = array('am_','pm_');
                                                            $out = array('','');
                                                            $tod = null;
                                                            $todCnt = 0 ;
                                                            ksort($frequent[$key]['tod']);
                                                            foreach ($frequent[$key]['tod'] as $kk => $vv ) {
                                                                $kk = str_replace ( $in, $out, $kk ) ;
                                                                if($vv > $todCnt){
                                                                    $todCnt = $vv ;                                                                    
                                                                    $tod = $kk;
                                                                } else if($vv == $todCnt){                                                                    
                                                                    $tod .= ', ' . $kk;
                                                                }
                                                            }
                                                            $buffer =  floor ( $todCnt / $frequent[$key]['stops'] * 100 ) ;
                                                            $todLink = $tod . ' ' . $buffer . '%';
                                                            $tod .= ' <span class="text-grey">' . $buffer . '%</span>';

                                                            $details = null;
                                                            ksort($frequent[$key]['details']);
                                                            foreach ($frequent[$key]['details'] as $ak => $av ) {
                                                                $details .= '<td class="fsr" style="vertical-align: top !important;" valign="top"><b>' . $this->base_logic->wizardDow($ak) . '</b><br><table>';
                                                                krsort($av);
                                                                foreach ($av as $bk => $bv ) {
                                                                    foreach ($bv as $kk => $vv ) {
                                                                        $details .=  '<tr>' . $vv . '</tr>';
                                                                    }
                                                                }
                                                                $details .= '</table></td>';
                                                            }
                                                            if(!($frequent[$key]['label'])){
                                                                $frequent[$key]['label'] = $frequent[$key]['address'];
                                                            }

                                                            if (!( trim($frequent[$key]['address']) )){
                                                                $frequent[$key]['address'] = $frequent[$key]['latitude'] . ' / ' . $frequent[$key]['longitude'] ;
                                                            }
                                                            
                                                            $report['tbody'] .= str_replace('#_#',$evenOdd,'<tr>
                                                                <td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>
                                                                <td class="' . $evenOdd . '">' . $stops . '</td>
                                                                <td class="' . $evenOdd . 'address_map_toggle" data-rank="' . $report['records'] . '" data-latitude="' . $frequent[$key]['latitude'] . '" data-longitude="' . $frequent[$key]['longitude'] . '" data-label="' . $frequent[$key]['label'] . '" data-average-duration="' . $aveDuration . '" data-dow="' . $dowLink . '" data-tod="' . $todLink . '" data-color="' . $color . '" data-license="' . $licenseplatenumber . '" data-vin="' . $vin . '" data-make="' . $make . '" data-model="' . $model . '" data-year="' . $year . '" title="' . $key . ' : ' . $frequent[$key]['latitude'] . ' / ' . $frequent[$key]['longitude'] . '"><a href="#">' . $frequent[$key]['address'] . '</a></td>
                                                                <td class="' . $evenOdd . '">' . $aveDuration . '</td>
                                                                <td class="' . $evenOdd . '">' . $dow . '</td>
                                                                <td class="' . $evenOdd . '">' . $tod . '</td>
                                                                <td class="' . $evenOdd . '"><a href="javascript:void(0);" onClick="Core.Toggle(\'fsr-details-' . $report['records'] . '\')">details</a>&nbsp;</td>
                                                                </tr>');
                                                            $report['tbody'] .= str_replace('#_#',$evenOdd,'<tr>
                                                                <td class="' . $evenOdd . 'fsr" colspan="7"><div class="background-green" id="fsr-details-' . $report['records'] . '" style="display: none;"><span class="text-10 text-grey">Timezone:&nbsp;&nbsp;' . str_replace('_',' ',$timezone) . '</span><table class="background-green" style="width:100%;"><tr>' . $details . '</tr></table><br><a class="pull-right" href="javascript:void(0);" onClick="Core.Toggle(\'fsr-details-' . $report['records'] . '\')">close&nbsp;&nbsp;&nbsp;</a><br>&nbsp;</div></td>
                                                                </tr>');
                                                            if($params['breadcrumbs']){
                                                                $report['breadcrumbs']++;
                                                                $report['breadcrumbtrail'][$report['records']]['show'] = 1; 
                                                                $report['breadcrumbtrail'][$report['records']]['address'] = $frequent[$key]['address'] ;
                                                                $report['breadcrumbtrail'][$report['records']]['formatted_address'] = $frequent[$key]['address'] ;
                                                                // $report['breadcrumbtrail'][$report['records']]['duration'] = $duration ;
                                                                $report['breadcrumbtrail'][$report['records']]['latitude'] = $frequent[$key]['latitude'] ;
                                                                $report['breadcrumbtrail'][$report['records']]['longitude'] = $frequent[$key]['longitude'] ;
                                                                $report['breadcrumbtrail'][$report['records']]['mappoint'] = $report['records']; 
                                                                // $report['breadcrumbtrail'][$report['records']]['speed'] = $row['speed'] ;
                                                                $report['breadcrumbtrail'][$report['records']]['stops'] = $frequent[$buffer]['stops'] ;
                                                                // $report['breadcrumbtrail'][$report['records']]['unittime'] = $unittime ;
                                                                $report['breadcrumbtrail'][$report['records']]['eventname'] = 'Stop' ;
                                                                // $report['breadcrumbtrail'] = $breadcrumb; 
                                                            }
                                                            //
                                                        } else {
                                                            // $report['breadcrumbtrail'][$report['records']]['skip'] = 1; 
                                                        }
                                                    }
                                                    if(!($report['tbody'])){$report['tbody'] = '<tr><td colspan="7"><i>No Data Found</i>&nbsp;<br>Please adjust filters and try again</td></tr>';}
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    break;

            case            'stops-report-recent' : $sqlPlaceHolder = array();
                                                    $report['message'] = '&nbsp;<p>';
                                                    if($params['search']){
                                                        $search = ' AND ( ume.eventname LIKE \'' . $params['search'] . '%\' OR ume.eventname LIKE \'%' . $params['search'] . '%\' OR ue1.streetaddress LIKE \'' . $params['search'] . '%\' OR ue1.streetaddress LIKE \'%' . $params['search'] . '%\' OR ue1.city LIKE \'' . $params['search'] . '%\' OR ue1.city LIKE \'%' . $params['search'] . '%\' OR ue1.state LIKE \'' . $params['search'] . '%\' OR ue1.state LIKE \'%' . $params['search'] . '%\' OR ue1.zipcode LIKE \'' . $params['search'] . '%\' OR ue1.zipcode LIKE \'%' . $params['search'] . '%\' )' ;
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0; 
                                                    }
                                                    switch($params['daterange']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $params['daterange']++;
                                                                                    $daterange = " AND ue1.unittime >= DATE_SUB(?, INTERVAL ? DAY)" ;
                                                                                    $sqlPlaceHolder[] = $tonightUtc ;
                                                                                    $sqlPlaceHolder[] = $params['daterange'];
                                                                                    $report['message'] .= ' Date Range Not Found"<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    $report['code'] = 0; 
                                                    $sql = "SELECT
                                                                t.account_id as account_id,
                                                                t.territoryname as territoryname,
                                                                ue1.*,
                                                                ume.eventname as eventname
                                                            FROM " . $params['db'] . ".unit" . $params['unit_id'] . " ue1
                                                            LEFT JOIN unitmanagement.event ume ON ume.event_id = ue1.event_id
                                                            LEFT JOIN crossbones.territory t ON t.territory_id = ue1.landmark_id
                                                            WHERE ue1.id IS NOT NULL"
                                                            . $search
                                                            . $daterange
                                                            . " AND ue1.event_id IS NOT NULL 
                                                            ORDER BY ue1.unittime DESC";

                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    
                                                    $lastunittime=date('Y-m-d H:i:s');
                                                    $report['thead'] = '<tr><th>Map Point</th><th>Date & Time <span class="text-grey" title="' . $tonight . '">(' . str_replace('_', ' ', $timezone) . ')</span></th><th>Event</th><th>Location</th><th>Speed</th><th>Duration</th></tr>'; 
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        if (!($lastunittime)) {
                                                            $lastunittime = $row['unittime'] ;
                                                        }
                                                        $report['records']++;
                                                        $duration = null ;
                                                        $durationTest = null ;
                                                        if( $lastunittime ){
                                                            $duration=strtotime($lastunittime)-strtotime($row['unittime']);
                                                            $durationTest = $duration ;
                                                            if($duration>=$params['duration']){
                                                                $days = floor ( $duration / 86400 );
                                                                $duration = $duration - ( $days * 86400 ) ;
                                                                $hours = floor ( $duration / 3600 );
                                                                $duration = $duration - ( $hours * 3600 ) ;
                                                                $minutes = floor ( $duration / 60 );
                                                                $seconds = $duration - ( $minutes * 60 ) ;
                                                                $duration = null ;
                                                                if ( $days ) { $duration .= $days . ' Days'; }
                                                                if ( ( $duration ) && ( $hours ) ) { $duration .= ', '; }
                                                                if ( $hours ) { $duration .= $hours . ' Hours'; }
                                                                if ( ( $duration ) && ( $minutes ) ) { $duration .= ', '; }
                                                                if ( $minutes ) { $duration .= $minutes . ' Minutes'; }
                                                                // if ( $duration ) { $duration .= ', '; }
                                                                // if ( $seconds ) { $duration .= $seconds . ' Seconds'; }
                                                            } else {
                                                                $duration = null ;
                                                            }
                                                        }
                                                        switch($row['event_id']){
                                                            case             1  :
                                                            case            '1' :
                                                            case             3  :
                                                            case            '3' :
                                                            case             4  :
                                                            case            '4' : $duration = null ;
                                                                                  $lastunittime = $row['unittime'] ;
                                                                                  break;
                                                            case             2  :
                                                            case            '2' :
                                                            case             5  :
                                                            case            '5' : $lastunittime = $row['unittime'] ;
                                                                                  break;
                                                                        default : $duration = null ;
                                                        }
                                                        $report['code'] = 0; 
                                                        $address = $this->address_logic->validateAddress($row['streetaddress'], $row['city'], $row['state'], $row['zipcode'], $row['country']);
                                                        if(($row['territoryname'])&&($row['account_id']==$account_id)){
                                                            $address = '(' . $row['territoryname'] . ') ' . $address;
                                                        }
                                                        $label = str_replace('"','\"',$address);
                                                        // $unittime = date('m/d/Y h:ia' , strtotime($this->base_logic->timezoneDelta($timezone,$row['unittime'],1))) ;
                                                        $unittime = Date::utc_to_locale($row['unittime'], $timezone, 'h:i A m/d/Y');
                                                        switch($evenOdd){
                                                            case     '' : $evenOdd = 'report-even-odd ';
                                                                            break;
                                                                default : $evenOdd = '';
                                                        }
                                                        if(($params['duration']==0)||($durationTest>=$params['duration'])){
                                                            switch($row['event_id']){
                                                                case               2  :
                                                                case              '2' :
                                                                case               5  :
                                                                case              '5' : $final['evenOdd'] =  $evenOdd ;
                                                                                        $final['unittime'] = $unittime ;
                                                                                        $final['event_id'] = $row['event_id'] ;
                                                                                        $final['eventname'] = $row['eventname'] ;
                                                                                        $final['latitude'] = $row['latitude'] ;
                                                                                        $final['longitude'] = $row['longitude'] ;
                                                                                        $final['label'] = $label ;
                                                                                        $final['title'] = $row['latitude'] . ' / ' . $row['longitude'] ;
                                                                                        $final['address'] = $address ;
                                                                                        $final['speed'] = floor($row['speed']) ;
                                                                                        $final['duration'] = $duration ;
                                                                                        $finals[] = $final;
                                                                                        if($params['breadcrumbs']){
                                                                                            $report['breadcrumbs']++;
                                                                                            $breadcrumb[$report['breadcrumbs']]['address'] = $address ;
                                                                                            $breadcrumb[$report['breadcrumbs']]['formatted_address'] = $address ;
                                                                                            $breadcrumb[$report['breadcrumbs']]['latitude'] = $row['latitude'] ;
                                                                                            $breadcrumb[$report['breadcrumbs']]['longitude'] = $row['longitude'] ;
                                                                                            // $breadcrumb[$report['breadcrumbs']]['mappoint'] = $records ;
                                                                                            $breadcrumb[$report['breadcrumbs']]['speed'] = $row['speed'] ;
                                                                                            $breadcrumb[$report['breadcrumbs']]['unittime'] = $unittime ;
                                                                                            $breadcrumb[$report['breadcrumbs']]['eventname'] = $row['eventname'] ;
                                                                                            $report['breadcrumbtrail'] = $breadcrumb; 
                                                                                        }
                                                                                        break;
                                                            }
                                                        }
                                                    }

                                                    $records=0;
                                                    foreach ($finals as $key => $final) {
                                                        $records++;
                                                    }
                                                    
                                                    foreach ($finals as $key => $final) {
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if( $page==$params['pageCount'] ){

                                                            if (!( trim($final['address']) )){
                                                                $final['address'] = $final['latitude'] . ' / ' . $final['longitude'] ;
                                                            }

                                                            $report['tbody'] .= '<tr>
                                                                <td class="' . $final['evenOdd'] . '">' . $records . '</td>
                                                                <td class="' . $final['evenOdd'] . '">' . $final['unittime'] . '</td>
                                                                <td class="' . $final['evenOdd'] . '" title="Event Id #' . $final['event_id'] . '">' . $final['eventname'] . '</td>
                                                                <td class="' . $final['evenOdd'] . 'address_map_toggle" data-rank="' . $records . '" data-latitude="' . $final['latitude'] . '" data-longitude="' . $final['longitude'] . '" data-label="' . $final['label'] . '" title="' . $final['title'] . '"><a href="#">' . $final['address'] . '</a></td>
                                                                <td class="' . $final['evenOdd'] . '">' . $final['speed'] . '</td>
                                                                <td class="' . $final['evenOdd'] . '">' . $final['duration'] . '</td>
                                                                </tr>'; 
                                                            $report['breadcrumbtrail'][$records]['show'] = 1; 
                                                        } else {
                                                            $report['breadcrumbtrail'][$records]['skip'] = 1; 
                                                        }
                                                        $report['breadcrumbtrail'][$records]['mappoint'] = $records; 
                                                        $records--;
                                                    }

                                                    if(!($report['tbody'])){$report['tbody'] = '<tr><td colspan="7"><i>No Data Found</i>&nbsp;<br>Please adjust filters and try again</td></tr>';}
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;                                                    
                                                    // if($params['search']){
                                                    //     $search = ' AND ( ume.eventname LIKE \'' . $params['search'] . '%\' OR ume.eventname LIKE \'%' . $params['search'] . '%\' OR ue1.streetaddress LIKE \'' . $params['search'] . '%\' OR ue1.streetaddress LIKE \'%' . $params['search'] . '%\' OR ue1.city LIKE \'' . $params['search'] . '%\' OR ue1.city LIKE \'%' . $params['search'] . '%\' OR ue1.state LIKE \'' . $params['search'] . '%\' OR ue1.state LIKE \'%' . $params['search'] . '%\' OR ue1.zipcode LIKE \'' . $params['search'] . '%\' OR ue1.zipcode LIKE \'%' . $params['search'] . '%\' )' ;
                                                    //     $report['search'] = $params['search'];
                                                    //     $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                    //     $report['code'] = 0; 
                                                    // }
                                                    // switch($params['daterange']){
                                                    //     case                   '' :
                                                    //     case                'all' : break;
                                                    //                       default : $params['daterange']++;
                                                    //                                 $daterange = " AND ue1.unittime >= DATE_SUB(?, INTERVAL ? DAY)" ;
                                                    //                                 $sqlPlaceHolder[] = $tonightUtc ;
                                                    //                                 $sqlPlaceHolder[] = $params['daterange'];
                                                    //                                 $report['message'] .= ' Date Range Not Found"<p>';
                                                    //                                 $report['code'] = 0; 
                                                    // }
                                                    // $report['code'] = 0; 
                                                    // $sql = "SELECT
                                                    //             t.territoryname as territoryname,
                                                    //             ue1.*,
                                                    //             ume.eventname as eventname
                                                    //         FROM " . $params['db'] . ".unit" . $params['unit_id'] . " ue1
                                                    //         LEFT JOIN unitmanagement.event ume ON ume.event_id = ue1.event_id
                                                    //         LEFT JOIN crossbones.territory t ON t.territory_id = ue1.landmark_id
                                                    //         WHERE ue1.id IS NOT NULL
                                                    //         AND ( t.account_id = ? OR t.account_id IS NULL )"
                                                    //         . $search
                                                    //         . $daterange
                                                    //         . " AND ue1.event_id IS NOT NULL 
                                                    //         ORDER BY ue1.id DESC";

                                                    // $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    
                                                    // $report['thead'] = '<tr><th>Map Point</th><th>Date & Time <span class="text-grey" title="' . $tonight . '">(' . str_replace('_', ' ', $timezone) . ')</span></th><th>Event</th><th>Location</th><th>Speed</th><th>Duration</th></tr>'; 
                                                    // $page=1;
                                                    // $evenOdd = 'report-even-odd ';
                                                    // foreach ($rows as $key => $row) {
                                                    //     if($row['event_id']==5){
                                                    //         $report['records']++;
                                                    //         $length++;
                                                    //         if(($params['length']>0)&&($length>$params['length'])){
                                                    //             $length=1;
                                                    //             $page++;
                                                    //         }
                                                    //     }
                                                    //     $duration = null ;
                                                    //     if( ($lastunittime) && ($row['event_id']==5) ){
                                                    //         $duration=strtotime($lastunittime)-strtotime($row['unittime']);
                                                    //         if($duration>=$params['duration']){
                                                    //             $days = floor ( $duration / 86400 );
                                                    //             $duration = $duration - ( $days * 86400 ) ;
                                                    //             $hours = floor ( $duration / 3600 );
                                                    //             $duration = $duration - ( $hours * 3600 ) ;
                                                    //             $minutes = floor ( $duration / 60 );
                                                    //             $seconds = $duration - ( $minutes * 60 ) ;
                                                    //             $duration = null ;
                                                    //             if ( $days ) { $duration .= $days . ' Days'; }
                                                    //             if ( $duration ) { $duration .= ', '; }
                                                    //             if ( $hours ) { $duration .= $hours . ' Hours'; }
                                                    //             if ( $duration ) { $duration .= ', '; }
                                                    //             if ( $minutes ) { $duration .= $minutes . ' Minutes'; }
                                                    //             // if ( $duration ) { $duration .= ', '; }
                                                    //             // if ( $seconds ) { $duration .= $seconds . ' Seconds'; }
                                                    //         } else {
                                                    //             $duration = null ;
                                                    //         }
                                                    //     }
                                                    //     $lastunittime = $row['unittime'] ;
                                                    //     // if($duration){
                                                    //         if( ($page==$params['pageCount']) && ($row['event_id']==5) ){
                                                    //             $report['code'] = 0; 
                                                    //             $address = $this->address_logic->validateAddress($row['streetaddress'], $row['city'], $row['state'], $row['zipcode'], $row['country']);
                                                    //             if($row['territoryname']){
                                                    //                 $address = '(' . $row['territoryname'] . ') ' . $address;
                                                    //             }
                                                    //             $label = str_replace('"','\"',$address);
                                                    //             $unittime = date('m/d/Y h:ia' , strtotime($this->base_logic->timezoneDelta($timezone,$row['unittime'],1))) ;
                                                    //             if($params['breadcrumbs']){
                                                    //                 $report['breadcrumbs']++;
                                                    //                 $breadcrumb[$report['breadcrumbs']]['address'] = $address ;
                                                    //                 $breadcrumb[$report['breadcrumbs']]['latitude'] = $row['latitude'] ;
                                                    //                 $breadcrumb[$report['breadcrumbs']]['longitude'] = $row['longitude'] ;
                                                    //                 $breadcrumb[$report['breadcrumbs']]['mappoint'] = $records ;
                                                    //                 $breadcrumb[$report['breadcrumbs']]['speed'] = $row['speed'] ;
                                                    //                 $breadcrumb[$report['breadcrumbs']]['unittime'] = $unittime ;
                                                    //                 $breadcrumb[$report['breadcrumbs']]['eventname'] = $row['eventname'] ;
                                                    //                 $report['breadcrumbtrail'] = $breadcrumb; 
                                                    //             }
                                                    //             switch($evenOdd){
                                                    //                 case     '' : $evenOdd = 'report-even-odd ';
                                                    //                                 break;
                                                    //                     default : $evenOdd = '';
                                                    //             }
                                                    //             $final['evenOdd'] =  $evenOdd ;
                                                    //             $final['unittime'] = $unittime ;
                                                    //             $final['event_id'] = $row['event_id'] ;
                                                    //             $final['eventname'] = $row['eventname'] ;
                                                    //             $final['latitude'] = $row['latitude'] ;
                                                    //             $final['longitude'] = $row['longitude'] ;
                                                    //             $final['label'] = $label ;
                                                    //             $final['title'] = $row['latitude'] . ' / ' . $row['longitude'] ;
                                                    //             $final['address'] = $address ;
                                                    //             $final['speed'] = floor($row['speed']) ;
                                                    //             $final['duration'] = $duration ;
                                                    //             $finals[] = $final;
                                                    //         }
                                                    //     // }
                                                    // }

                                                    // $records=0;
                                                    // foreach ($finals as $key => $final) {
                                                    //     $records++;
                                                    // }

                                                    // foreach ($finals as $key => $final) {
                                                    //     $report['tbody'] .= '<tr>
                                                    //         <td class="' . $final['evenOdd'] . '">' . $records . '</td>
                                                    //         <td class="' . $final['evenOdd'] . '">' . $final['unittime'] . '</td>
                                                    //         <td class="' . $final['evenOdd'] . '" title="Event Id #' . $final['event_id'] . '">' . $final['eventname'] . '</td>
                                                    //         <td class="' . $final['evenOdd'] . 'address_map_toggle" data-latitude="' . $final['latitude'] . '" data-longitude="' . $final['longitude'] . '" data-label="' . $final['label'] . '" title="' . $final['title'] . '"><a href="#">' . $final['address'] . '</a></td>
                                                    //         <td class="' . $final['evenOdd'] . '">' . $final['speed'] . '</td>
                                                    //         <td class="' . $final['evenOdd'] . '">' . $final['duration'] . '</td>
                                                    //         </tr>'; 
                                                    //     $records--;
                                                    // }

                                                    // if(!($report['tbody'])){$report['tbody'] = '<tr><td colspan="7"><i>No Data Found</i>&nbsp;<br>Please adjust filters and try again</td></tr>';}
                                                    // $report['pageCount'] = $params['pageCount'];
                                                    // $report['pageTotal'] = $page;
                                                    break;

            case               'users-type-table' : $report['message'] = '&nbsp;<p>';
                                                    //
                                                    //
                                                    // --- User Head Counts
                                                    //
                                                    $sqlPlaceHolder = array($account_id);
                                                    $sqlPlaceHolder[] = $account_id;
                                                    if($params['search']){
                                                        $search = ' AND ( ut.usertype LIKE \'' . $params['search'] . '%\' OR ut.usertype LIKE \'' . $params['search'] . '%\' )' ;
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0; 
                                                    }
                                                    $sql = "SELECT IFNULL(COUNT(u.user_id),0) as total,
                                                            ut.account_id as account_id,
                                                            ut.active as active,
                                                            ut.canned as canned,
                                                            ut.usertype as usertype,
                                                            ut.usertype_id as usertype_id
                                                            FROM crossbones.usertype ut
                                                            LEFT JOIN crossbones.user u ON u.usertype_id = ut.usertype_id
                                                            WHERE 
                                                            ( 
                                                                ( ut.account_id = ? )
                                                                OR
                                                                ( ut.canned = '1' AND u.account_id = ? )
                                                            )
                                                            AND ut.active = '1' "
                                                            . $search
                                                            . " GROUP BY usertype_id
                                                            ORDER BY ut.canned DESC, ut.usertype ASC";
                                                    $totals = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    //
                                                    //
                                                    // --- Details
                                                    //
                                                    $sqlPlaceHolder = array($account_id);
                                                    $sql = "SELECT DISTINCT ut.usertype_id as usertype_id,
                                                            ut.usertype as usertype,
                                                            ut.canned as canned
                                                            FROM crossbones.usertype ut
                                                            WHERE
                                                            ( ut.account_id = ? OR ut.canned = '1' )
                                                            AND ut.active = '1'
                                                            ORDER BY ut.canned DESC, ut.usertype ASC";
                                                    $usertypeids = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    $sqlPlaceHolder = array();
                                                    foreach ($usertypeids as $key => $row) {
                                                        if( ($row['usertype_id']) && (!($buffer[$row['usertype_id']])) ){
                                                            $buffer[$row['usertype_id']] = 1 ;
                                                            if($usertypesql){
                                                                $usertypesql .= ' OR' ;
                                                            }
                                                            $usertypesql .= ' usertype_id = ?' ;
                                                            $sqlPlaceHolder[] = $row['usertype_id'];
                                                        }
                                                    }
                                                    $sql = "SELECT permission_id, usertype_id
                                                            FROM crossbones.usertype_permission
                                                            WHERE" . $usertypesql ;
                                                    $perms = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    foreach ($perms as $key => $row) {
                                                        // $report['thead'] .= '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>' . $row['usertype_id']  . '</th><th>' . $row['permission_id']  . '</th></tr>'; 
                                                        if(($row['permission_id'])&&(!($checked['ut_'.$row['usertype_id'].'_p_'.$row['permission_id']]))){
                                                            $checked['ut_'.$row['usertype_id'].'_p_'.$row['permission_id']] = 1 ;
                                                        }
                                                    }
                                                    
                                                    $report['thead'] = '<tr><th>User Type</th><th>User Count</th><th>Editable</th><th class="tinywidth">Delete</th></tr>'; 
                                                    $page=1;
                                                    $records = array();
                                                    foreach ($totals as $key => $row) {
                                                        $buffer = $records[$row['usertype_id']];
                                                        $buffer['usertype_id'] = $row['usertype_id'];
                                                        $buffer['usertype'] = $row['usertype'];
                                                        $buffer['total'] = $row['total'];
                                                        $records[$row['usertype_id']] = $buffer;
                                                    }
                                                    foreach ($usertypes as $key => $row) {
                                                        $buffer = $records[$row['usertype_id']];
                                                        $buffer['usertype_id'] = $row['usertype_id'];
                                                        $buffer['usertype'] = $row['usertype'];
                                                        $buffer['canned'] = $row['canned'];
                                                        $records[$row['usertype_id']] = $buffer;
                                                    }                                                    
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($usertypeids as $key => $usertypeid) {
                                                        $row = $records[$usertypeid['usertype_id']];
                                                        $row['usertype_id'] = $usertypeid['usertype_id'];
                                                        $row['usertype'] = $usertypeid['usertype'];
                                                        $row['canned'] = $usertypeid['canned'];
                                                        $report['code'] = 0; 
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($row['canned']){
                                                            $editable='<span class="text-red-12">Not&nbsp;Editable</span>';
                                                            $usertype=$row['usertype'];
                                                        } else {
                                                            $editable='<span class="text-green-12" style="font-weight:700;">Editable</span>';
                                                            $usertype=$row['usertype'];
                                                        }
                                                        if($row['total']<1){
                                                            $row['total'] = "0" ;
                                                        }
                                                        if((!($row['canned']))&&(!($row['total']))){
                                                            $delete = $this->base_logic->wizardDeleteRecord('usertype',$params['pid'],$report['records'],$row['usertype_id']);
                                                        } else {
                                                            $delete = null;
                                                        }
                                                        if($page==$params['pageCount']){
                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }
                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><a href="#modal-edit-usertype" data-toggle="modal" onclick="Core.ClearForm(\'user-type-edit\',\'' . str_replace("'","\'",$usertype) . '\',\'' . $row['usertype_id'] . '\',\'' . $row['canned'] . '\');" title="Edit User Type">' . $usertype . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['total'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $editable . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $delete . '</td>'
                                                                . '</tr>'; 
                                                        }
                                                        $totalusers = $totalusers + $row['total'];
                                                    }
                                                    if($totalusers){
                                                        // $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>User Type</th><th>Admin Permissions</th><th>Vehicle Permissions</th><th>Landmark Permissions</th><th>Alert Permissions</th><th>Report Permissions</th><th>Users&nbsp;&nbsp;&nbsp;<span style="font-size:60%;font-weight:700;">Total:&nbsp;' . $totalusers . '</span></th><th>Editable</th></tr>'; 
                                                    }
                                                    if(!($report['tbody'])){$report['tbody'] = '<tr><td colspan="3"><i>No Data Found</i></td></tr>';}
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    break;

            case              'users-users-table' : $report['message'] = '&nbsp;<p>';
                                                    if($params['search']){
                                                        $search = ' AND ( c.firstname LIKE \'' . $params['search'] . '%\' OR c.firstname LIKE \'%' . $params['search'] . '%\' OR c.lastname LIKE \'' . $params['search'] . '%\' OR c.lastname LIKE \'%' . $params['search'] . '%\' OR u.username LIKE \'' . $params['search'] . '%\' OR u.username LIKE \'%' . $params['search'] . '%\' OR us.userstatusname LIKE \'' . $params['search'] . '%\' OR us.userstatusname LIKE \'%' . $params['search'] . '%\' OR ut.usertype LIKE \'' . $params['search'] . '%\' OR ut.usertype LIKE \'%' . $params['search'] . '%\' OR c.email LIKE \'' . $params['search'] . '%\' OR c.email LIKE \'%' . $params['search'] . '%\' OR u.firstname LIKE \'' . $params['search'] . '%\' OR u.firstname LIKE \'%' . $params['search'] . '%\' OR u.lastname LIKE \'' . $params['search'] . '%\' OR u.lastname LIKE \'%' . $params['search'] . '%\' )' ;
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0; 
                                                    }
                                                    $sql = "SELECT u.*,
                                                            c.cellnumber as cellnumber, 
                                                            c.contact_id as contact_id, 
                                                            c.firstname as firstname, 
                                                            c.lastname as lastname,
                                                            c.email as email,
                                                            cc.cellcarrier as cellcarrier, 
                                                            u.email as uemail,
                                                            u.firstname as ufirstname, 
                                                            u.lastname as ulastname,
                                                            u.user_id as user_id,
                                                            u.username as username,
                                                            u.userstatus_id as userstatus_id,
                                                            u.lastlogin as lastlogin,
                                                            u.roles as roles,
                                                            us.userstatusname as userstatusname,
                                                            ut.usertype as usertype,
                                                            cg.contactgroupname as contactgroupname
                                                            FROM crossbones.user u
                                                            LEFT JOIN crossbones.contact c ON c.user_id = u.user_id
                                                            LEFT JOIN crossbones.userstatus us ON us.userstatus_id = u.userstatus_id
                                                            LEFT JOIN crossbones.usertype ut ON ut.usertype_id = u.usertype_id
                                                            LEFT JOIN crossbones.cellcarrier cc ON cc.cellcarrier_id = c.cellcarrier_id
                                                            LEFT JOIN crossbones.contactgroup_contact cgc ON cgc.contact_id = c.contact_id
                                                            LEFT JOIN crossbones.contactgroup cg ON cg.contactgroup_id = cgc.contactgroup_id
                                                            WHERE u.account_id = ?
                                                            AND u.userstatus_id > 0"
                                                            . $search
                                                            . " ORDER BY "
                                                            . "us.userstatus_id ASC"; // Show "Pending" before "Active"
                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    foreach ($rows as $key => $row) {
                                                        if (!($row['firstname'])){
                                                            $row['firstname'] = $row['ufirstname'] ;
                                                        }
                                                        if(!($row['lastname'])){
                                                            $row['lastname'] = $row['ulastname'] ;
                                                        }
                                                        if(!($row['uemail'])){
                                                            $row['uemail'] = $row['email'] ;
                                                        }
                                                        $key = null ;
                                                        if(($row['contactstatus']=='pending')||($row['userstatus_id']==1)){
                                                            $key .= '0_' ;
                                                        } else {
                                                            $key .= '1_' ;
                                                        }
                                                        $key .= preg_replace("/\W|_/", "", strtolower( $row['firstname'] . $row['lastname'] ) );
                                                        $sort[$key] = $row;
                                                    }
                                                    ksort($sort);

                                                    // $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Last Name</th><th>First Name</th><th>Login Username</th><th>User Type</th><th>Group</th><th>Status</th><th>Mobile</th><th>Carrier</th><th>Email</th><th class="tinywidth">Delete</th></tr>'; 
                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Name</th><th>Username</th><th>User Type</th><th>Status</th><th>Email</th><th class="tinywidth">Delete</th></tr>'; 
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($sort as $key => $row) {
                                                        $report['code'] = 0; 
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){
                                                            switch($evenOdd){
                                                                case        '' :
                                                                case 'pending' : $evenOdd = 'report-even-odd ';
                                                                                 break;
                                                                       default : $evenOdd = '';
                                                            }
                                                            $pTitle = null;
                                                            if(($row['contactstatus']=='pending')||($row['userstatus_id']==1)){
                                                                $evenOdd = $evenOdd . 'pending';
                                                                $pTitle = '" title="' . $row['firstname'] . ' ' . $row['lastname'] . ' has not Logged In';
                                                            } else if ($row['lastlogin'] > 0) {
                                                                $pTitle = '" title="Last Login: ' . $row['lastlogin'];
                                                            }
                                                            $delete = null ;
                                                            if(($permission)||($role_account_owner)){
                                                            // if($row['roles']!='ROLE_ACCOUNT_OWNER'){
                                                                $delete = $this->base_logic->wizardDeleteRecord('user',$params['pid'],$report['records'],$row['user_id']);
                                                            }
                                                            // $delete = $row['roles'] ;
                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '"><a href="#modal-edit-user" data-toggle="modal" data-placement="bottom" onclick="Core.ClearForm(\'user-edit\',\'' . str_replace("'","\'", $row['firstname'] . ' ' . $row['lastname'] . ' (' . $row['username'] . ')') . '\',\'' . $row['user_id'] . '\');" title="Edit User">' . ucwords($row['firstname'] . ' ' . $row['lastname']) . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['username'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['usertype'] . '</td>'
                                                                . '<td class="' . $evenOdd . $pTitle . '">' . ucwords($row['userstatusname']) . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['uemail'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $delete . '</td>'
                                                                . '</tr>'; 
                                                        }
                                                    }
                                                    if(!($report['tbody'])){$report['tbody'] = '<tr><td colspan="3"><i>No Data Found</i></td></tr>';}
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    break;

            case            'vehicle-group-table' : $report['message'] = '&nbsp;<p>';
                                                    $user['account_id'] = $account_id;
                                                    $user['user_id'] = $user_id;
                                                    $permission = $this->vehicle_data->ajaxPermissionCheck($user,'vehiclegroups');
                                                    if((!($permission))&&(!($role_account_owner))){
                                                        $user_id_related = ' AND uug.user_id = ?' ;
                                                        $sqlPlaceHolder[] = $user_id;
                                                    }
                                                    if($params['search']){
                                                        $search = ' AND ( ug.unitgroupname LIKE \'' . $params['search'] . '%\' OR ug.unitgroupname LIKE \'%' . $params['search'] . '%\' )' ;
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                        $report['code'] = 0; 
                                                    }
                                                    $sql = "SELECT
                                                                COUNT(DISTINCT utug.usertype_id) as total,
                                                                utug.unitgroup_id as unitgroup_id
                                                            FROM crossbones.usertype_unitgroup utug
                                                            LEFT JOIN crossbones.unitgroup ug ON ug.unitgroup_id = utug.unitgroup_id
                                                            LEFT JOIN crossbones.user_unitgroup uug ON uug.unitgroup_id = utug.unitgroup_id
                                                            WHERE ug.account_id = ? 
                                                            AND ug.active = 1 
                                                            AND utug.usertype_id IS NOT NULL"
                                                            . $user_id_related
                                                            // . $search
                                                            . " GROUP BY ug.unitgroup_id
                                                            ORDER BY ug.unitgroup_id ASC";

                                                    $usertypes = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    foreach ($usertypes as $k => $ug) {
                                                        $buff_usertypes[$ug['unitgroup_id']] = $ug['total'] ;
                                                    }
                                                    
                                                    $sql = "SELECT
                                                                COUNT(DISTINCT uug.user_id) as total,
                                                                uug.unitgroup_id as unitgroup_id
                                                            FROM crossbones.unitgroup ug
                                                            LEFT JOIN crossbones.user_unitgroup uug ON uug.unitgroup_id = ug.unitgroup_id
                                                            LEFT JOIN crossbones.user u ON u.user_id = uug.user_id
                                                            WHERE ug.account_id = ? 
                                                            AND ug.active = 1 
                                                            AND u.userstatus_id < 4 
                                                            AND u.userstatus_id > 0 
                                                            AND uug.user_id IS NOT NULL"
                                                            . $user_id_related
                                                            // . $search
                                                            . " GROUP BY uug.unitgroup_id";

                                                    $users = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    foreach ($users as $k => $ug) {
                                                        // $buff_users[$ug['unitgroup_id']] = $ug['unitgroup_id'] . ':' . $ug['total'] . ' users' ;
                                                        $buff_users[$ug['unitgroup_id']] = $ug['total'] ;
                                                    }
                                                    
                                                    $sql = "SELECT
                                                                COUNT(DISTINCT u.unit_id) as total,
                                                                ug.unitgroup_id as unitgroup_id
                                                            FROM crossbones.unitgroup ug
                                                            LEFT JOIN crossbones.unit u ON u.unitgroup_id = ug.unitgroup_id
                                                            LEFT JOIN crossbones.user_unitgroup uug ON uug.unitgroup_id = ug.unitgroup_id
                                                            WHERE ug.account_id = ? 
                                                            AND ug.active = 1 
                                                            AND u.unit_id IS NOT NULL"
                                                            . $user_id_related
                                                            // . $search
                                                            . " GROUP BY ug.unitgroup_id
                                                            ORDER BY ug.unitgroup_id ASC";

                                                    $devices = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    foreach ($devices as $k => $ug) {
                                                        $buff_devices[$ug['unitgroup_id']] = $ug['total'] ;
                                                    }
                                                    
                                                    $sql = "SELECT ug.unitgroup_id, ug.unitgroupname
                                                            FROM crossbones.unitgroup ug
                                                            LEFT JOIN crossbones.user_unitgroup uug ON uug.unitgroup_id = ug.unitgroup_id
                                                            WHERE ug.account_id = ?
                                                            AND ug.active = 1"
                                                            . $user_id_related
                                                            . " ORDER BY ug.unitgroup_id DESC LIMIT 1";

                                                    $newest = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    
                                                    $sql = "SELECT ug.*,
                                                                u.unit_id as unit_id,
                                                                ug.default as ugdefault,
                                                                ug.unitgroupname as unitgroupname
                                                            FROM crossbones.unitgroup ug
                                                            LEFT JOIN crossbones.unit u ON u.unitgroup_id = ug.unitgroup_id
                                                            LEFT JOIN crossbones.user_unitgroup uug ON uug.unitgroup_id = ug.unitgroup_id
                                                            WHERE ug.account_id = ? 
                                                            AND ug.active = 1"
                                                            . $user_id_related
                                                            . $search
                                                            . " GROUP BY ug.unitgroup_id
                                                            ORDER BY ug.default DESC , ug.unitgroupname ASC";

                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    
                                                    if($report['mobile']){
                                                        $report['thead'] = null ;
                                                    } else {
                                                        if($access['vehicle_group']['write']){
                                                            $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Vehicle Group</th><th>Devices</th><th>Users</th><th class="tinywidth">Delete</th></tr>';
                                                        } else {
                                                            $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Vehicle Group</th><th>Devices</th><th>Users</th></tr>';
                                                        }
                                                    }
                                                    if(!($rows)){$report['tbody'] = '<tr><td colspan="3"><i>No Data Found</i></td></tr>';}
                                                    $page=1;
                                                    foreach ($rows as $key => $row) {
                                                        $report['code'] = 0; 
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){
                                                            if(!($row['unit_id'])){
                                                                $row['total'] = "0";
                                                            }
                                                            $onClick="Core.DataTable.pop('modal-edit-scheduled-report','modal-title=" . str_replace("'", "\'", $row['reporttypename']) . ";','modal-edit-scheduled-report-name=" . str_replace("'", "\'", $row['schedulereportname']) . ";','scheduled-recurrence=" . str_replace("'", "\'", $row['schedule']) . ";scheduled-day=" . str_replace("'", "\'", $row['scheduleday']) . ";scheduled-monthly=" . str_replace("'", "\'", $row['monthday']) . ";scheduled-time=" . str_replace("'", "\'", $row['sendhour']) . ";scheduled-format=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-mode=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-single=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-group=" . str_replace("'", "\'", $row['format']) . ";');";
                                                            if(!($row['default'])){
                                                                // $row['unitgroupname'] = $this->base_logic->wizardInput($params['pid'],$report['records'],'crossbones-unitgroup-unitgroupname',$row['unitgroup_id'],$row['unitgroupname']);
                                                            }
                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }
                                                            if(!($row['unitgroupname'])){
                                                                $row['unitgroupname'] = 'Vehicle Group #' . $row['unitgroup_id'] ;
                                                            }
                                                            if($access['vehicle_group']['write']){
                                                                $buff_label[$row['unitgroup_id']] = '<a id="edit-unitgroup-' . $row['unitgroup_id'] . '" href="#modal-edit-vehicle-group" data-toggle="modal" data-placement="bottom" onclick="Core.ClearForm(\'edit-vehicle-group\',\'' . str_replace("'","\'", $row['unitgroupname']) . '\',\'' . $row['unitgroup_id'] . '\');" title="Edit Vehicle Group">' . $row['unitgroupname'] . '</a>' ;
                                                                $buff_delete[$row['unitgroup_id']] = '<td class="' . $evenOdd . '">' . $this->base_logic->wizardDeleteRecord('unitgroup',$params['pid'],$report['records'],$row['unitgroup_id']) . '</td>' ;
                                                            } else {
                                                                $buff_label[$row['unitgroup_id']] = $row['unitgroupname'] ;
                                                                $buff_delete[$row['unitgroup_id']] = '<td class="' . $evenOdd . '"></td>' ;
                                                            }
                                                            if($report['mobile']){
                                                                $report['tbody'] .= '<tr>'
                                                                    . '<td class="' . $evenOdd . '"><a href="javascript:void(0);" class="text-black text-bold text-large group-name">' . $row['unitgroupname'] . '</a></td>'
                                                                    . '</tr>'; 
                                                            } else {
                                                                if(!($buff_devices[$row['unitgroup_id']])){
                                                                    $buff_devices[$row['unitgroup_id']]="0";
                                                                }
                                                                if(!($buff_users[$row['unitgroup_id']])){
                                                                    $buff_users[$row['unitgroup_id']]="0";
                                                                }
                                                                $report['tbody'] .= '<tr>'
                                                                    . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                    . '<td class="' . $evenOdd . '">' . $buff_label[$row['unitgroup_id']] . '</td>'
                                                                    . '<td class="' . $evenOdd . '">' . $buff_devices[$row['unitgroup_id']] . '</td>'
                                                                    . '<td class="' . $evenOdd . '">' . $buff_users[$row['unitgroup_id']] . '</td>'
                                                                    // . '<td class="' . $evenOdd . '">' . $buff_usertypes[$row['unitgroup_id']] . '</td>'
                                                                    . $buff_delete[$row['unitgroup_id']]
                                                                    . '</tr>'; 
                                                            }
                                                        }
                                                    }
                                                    if(!($report['tbody'])){
                                                        $report['tbody'] = '<tr><td colspan="9"><i>No Data Found</i></td></tr>';
                                                    } else if(!($report['mobile'])){
                                                    }
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    $report['unitgroup'] = $newest[0];
                                                    break;

            case             'vehicle-list-table' : $report['message'] = '&nbsp;<p>';
                                                    $roles_params = array($account_id,$user_id);
                                                    $sql = "SELECT roles
                                                            FROM crossbones.user
                                                            WHERE account_id = ?
                                                            AND user_id = ?";
                                                    $roles = $this->report_data->getReport($sql, $roles_params);
                                                    if($roles[0]['roles']!='ROLE_ACCOUNT_OWNER'){
                                                        $sqlPlaceHolder[] = $user_id;
                                                    }
                                                    if($params['search']){
                                                        $search = ' AND (' ;
                                                        $sa = explode(' ',$params['search']);
                                                        foreach ( $sa as $k => $v ) {
                                                            $arg = trim($v);
                                                            if($arg){
                                                                if($args){
                                                                    $search .= ' ) AND (' ;
                                                                }
                                                                $args = 1;
                                                                $search .= ' u.unitname LIKE \'' . $arg . '%\' OR u.unitname LIKE \'%' . $arg . '%\'' ;
                                                                $search .= ' OR u.serialnumber          LIKE \'' . $arg . '%\' OR u.serialnumber           LIKE \'%' . $arg . '%\'' ;
                                                                $search .= ' OR ua.color                LIKE \'' . $arg . '%\' OR ua.color                 LIKE \'%' . $arg . '%\'' ;
                                                                $search .= ' OR ua.licenseplatenumber   LIKE \'' . $arg . '%\' OR ua.licenseplatenumber    LIKE \'%' . $arg . '%\'' ;
                                                                $search .= ' OR ua.loannumber           LIKE \'' . $arg . '%\' OR ua.loannumber            LIKE \'%' . $arg . '%\'' ;
                                                                $search .= ' OR ua.make                 LIKE \'' . $arg . '%\' OR ua.make                  LIKE \'%' . $arg . '%\'' ;
                                                                $search .= ' OR ua.model                LIKE \'' . $arg . '%\' OR ua.model                 LIKE \'%' . $arg . '%\'' ;
                                                                $search .= ' OR ua.stocknumber          LIKE \'' . $arg . '%\' OR ua.stocknumber           LIKE \'%' . $arg . '%\'' ;
                                                                $search .= ' OR ua.vin                  LIKE \'' . $arg . '%\' OR ua.vin                   LIKE \'%' . $arg . '%\'' ;
                                                                $search .= ' OR ua.year                 LIKE \'' . $arg . '%\' OR ua.year                  LIKE \'%' . $arg . '%\'' ;
                                                                $search .= ' OR ug.unitgroupname        LIKE \'' . $arg . '%\' OR ug.unitgroupname         LIKE \'%' . $arg . '%\'' ;
                                                            }
                                                        }
                                                        $search .= ' )' ;
                                                        if(!($args)){
                                                            $search = null ;
                                                        } else {
                                                            $report['search'] = $params['search'];
                                                            $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                            $report['code'] = 0; 
                                                            $params['sidebarVehicleGroup'] = NULL ; 
                                                        }
                                                    }
                                                    switch($params['sidebarVehicleGroup']){
                                                        case                           '' : 
                                                        case                        'all' : break;
                                                                                  default : $vehiclegroup = ' AND u.unitgroup_id = ?' ;
                                                                                            $sqlPlaceHolder[] = $params['sidebarVehicleGroup'];
                                                                                            $report['message'] .= ' Vehicle Group Id "' . $params['sidebarVehicleGroup'] . '" Not Found"<p>';
                                                                                            $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarVehicleStatus']){
                                                        case              'in-a-landmark' : $vehiclestatus = " AND uas.landmark_id != ?" ;
                                                                                            $sqlPlaceHolder[] = 0;
                                                                                            $report['message'] .= ' Vehicle Status: "' . $params['sidebarVehicleStatus'] . '" Not Found"<p>';
                                                                                            $report['code'] = 0; 
                                                                                            break;
                                                        case                  'installed' : $vehiclestatus = " AND u.unitstatus_id = ?" ;
                                                                                            $sqlPlaceHolder[] = 1;
                                                                                            $report['message'] .= ' Vehicle Status: "' . $params['sidebarVehicleStatus'] . '" Not Found"<p>';
                                                                                            $report['code'] = 0; 
                                                                                            break;
                                                        case                  'inventory' : $vehiclestatus = " AND u.unitstatus_id = ?" ;
                                                                                            $sqlPlaceHolder[] = 2;
                                                                                            $report['message'] .= ' Vehicle Status: "' . $params['sidebarVehicleStatus'] . '" Not Found"<p>';
                                                                                            $report['code'] = 0; 
                                                                                            break;
                                                        case      'no-movement-in-7-days' : $vehiclestatus = " AND u.unitstatus_id != ? AND u.lastmove < DATE_SUB(NOW(), INTERVAL 7 DAY)" ;
                                                                                            $sqlPlaceHolder[] = '2';
                                                                                            $report['message'] .= ' Vehicle Status: "' . $params['sidebarVehicleStatus'] . '" Not Found"<p>';
                                                                                            $report['code'] = 0; 
                                                                                            break;
                                                        case     'not-reported-in-7-days' : $vehiclestatus = " AND u.unitstatus_id != ? AND u.lastreport < DATE_SUB(NOW(), INTERVAL 7 DAY)" ;
                                                                                            $sqlPlaceHolder[] = '2';
                                                                                            $report['message'] .= ' Vehicle Status: "' . $params['sidebarVehicleStatus'] . '" Not Found"<p>';
                                                                                            $report['code'] = 0; 
                                                                                            break;
                                                        case                'reminder-on' : $vehiclestatus = " AND u.reminderstatus = ?" ;
                                                                                            $sqlPlaceHolder[] = 'On';
                                                                                            $report['message'] .= ' Vehicle Status: "' . $params['sidebarVehicleStatus'] . '" Not Found"<p>';
                                                                                            $report['code'] = 0; 
                                                                                            break;
                                                        case               'repossession' : $unitstatus = " AND u.unitstatus_id = ?" ;
                                                                                            $sqlPlaceHolder[] = 3;
                                                                                            $report['message'] .= ' Vehicle Status: "' . $params['sidebarVehicleStatus'] . '" Not Found"<p>';
                                                                                            $report['code'] = 0; 
                                                                                            break;
                                                        case           'starter-disabled' : $vehiclestatus = " AND u.starterstatus = ?" ;
                                                                                            $sqlPlaceHolder[] = 'Disabled';
                                                                                            $report['message'] .= ' Vehicle Status: "' . $params['sidebarVehicleStatus'] . '" Not Found"<p>';
                                                                                            $report['code'] = 0; 
                                                                                            break;
                                                    }
                                                    
                                                    if(!($unitstatus)){
                                                        $unitstatus = " AND u.unitstatus_id = ?" ;
                                                        $sqlPlaceHolder[] = 1;
                                                    }

                                                    if($roles[0]['roles']=='ROLE_ACCOUNT_OWNER'){
                                                        $sql = "SELECT
                                                                    u.*,
                                                                    uas.alertevent_id as alertevent_id,
                                                                    uas.idleevent_id as idleevent_id,
                                                                    uas.movingevent_id as movingevent_id,
                                                                    uas.speedevent_id as speedevent_id,
                                                                    uas.stopevent_id as stopevent_id,
                                                                    ug.unitgroupname as unitgroupname,
                                                                    uo.currentodometer as currentodometer,
                                                                    uo.initialodometer as initialodometer
                                                                FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitalertstatus uas ON uas.unit_id = u.unit_id
                                                                LEFT JOIN crossbones.unitattribute ua ON ua.unit_id = u.unit_id
                                                                LEFT JOIN crossbones.unitgroup ug ON ug.unitgroup_id = u.unitgroup_id
                                                                LEFT JOIN crossbones.user_unitgroup uug ON uug.unitgroup_id = ug.unitgroup_id
                                                                LEFT JOIN crossbones.unitodometer uo ON uo.unitodometer_id = u.unitodometer_id
                                                                WHERE u.account_id = ?"
                                                                . $vehiclegroup
                                                                . $vehiclestatus
                                                                . $search
                                                                . $unitstatus
                                                                . " ORDER BY u.unitname ASC";
                                                        $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    } else {
                                                        $sql = "SELECT
                                                                    u.*,
                                                                    uas.alertevent_id as alertevent_id,
                                                                    uas.idleevent_id as idleevent_id,
                                                                    uas.movingevent_id as movingevent_id,
                                                                    uas.speedevent_id as speedevent_id,
                                                                    uas.stopevent_id as stopevent_id,
                                                                    ug.unitgroupname as unitgroupname,
                                                                    uo.currentodometer as currentodometer,
                                                                    uo.initialodometer as initialodometer
                                                                FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitalertstatus uas ON uas.unit_id = u.unit_id
                                                                LEFT JOIN crossbones.unitattribute ua ON ua.unit_id = u.unit_id
                                                                LEFT JOIN crossbones.unitgroup ug ON ug.unitgroup_id = u.unitgroup_id
                                                                LEFT JOIN crossbones.user_unitgroup uug ON uug.unitgroup_id = ug.unitgroup_id
                                                                LEFT JOIN crossbones.unitodometer uo ON uo.unitodometer_id = u.unitodometer_id
                                                                WHERE u.account_id = ?
                                                                AND uug.user_id = ?"
                                                                . $vehiclegroup
                                                                . $vehiclestatus
                                                                . $search
                                                                . $unitstatus
                                                                . " GROUP BY u.unit_id
                                                                ORDER BY u.unitname ASC";
                                                        $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    }
                                                    $sqll = $sql ;

                                                    if($report['mobile']){
                                                        $report['thead'] = null ;
                                                    } else {
                                                        $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Vehicle</th><th>Group</th><th>Status</th><th>Duration</th><th>Address</th><th>Last Event</th><th>Date & Time</th><th>Mileage</th></tr>';
                                                        // $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Vehicle'.$roles[0]['roles'].'-'.$account_id.'-'.$user_id.'</th><th>Group</th><th>Status</th><th>Duration</th><th>Address</th><th>Last Event</th><th>Date & Time</th><th>Mileage</th></tr>';
                                                    }
                                                    $page=1;
                                                    foreach ($rows as $key => $row) {
                                                        if(($row['unit_id'])&&($row['unit_id']!=$lastUnit)){
                                                            $lastUnit=$row['unit_id'];
                                                            $sql = "SELECT
                                                                        t.territoryname as territoryname,
                                                                        ue1.*,
                                                                        ume.eventname as eventname
                                                                    FROM " . $row['db'] . ".unit" . $row['unit_id'] . " ue1
                                                                    LEFT JOIN unitmanagement.event ume ON ume.event_id = ue1.event_id
                                                                    LEFT JOIN crossbones.territory t ON t.territory_id = ue1.landmark_id
                                                                    WHERE ue1.id IS NOT NULL
                                                                    ORDER BY ue1.unittime DESC LIMIT 1";
                                                            $lastevent = $this->report_data->getReport($sql, array());
                                                            // $sql = "SELECT
                                                            //             t.territoryname as territoryname,
                                                            //             ue1.*,
                                                            //             ume.eventname as eventname
                                                            //         FROM " . $params['db'] . ".unit" . $params['unit_id'] . " ue1
                                                            //         LEFT JOIN unitmanagement.event ume ON ume.event_id = ue1.event_id
                                                            //         LEFT JOIN crossbones.territory t ON t.territory_id = ue1.landmark_id
                                                            //         WHERE ue1.id IS NOT NULL"
                                                            //         . $search
                                                            //         . $daterange
                                                            //         . " AND ue1.event_id IS NOT NULL
                                                            //         ORDER BY ue1.id DESC";
                                                            // // WHERE ue1.id IS NOT NULL AND ue1.event_id < 14 AND ue1.event_id != 7 AND ue1.event_id != 8 
                                                            // $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                            $sql = "SELECT *
                                                                    FROM " . $row['db'] . ".unit" . $row['unit_id'] . " ue1
                                                                    WHERE event_id = '28'
                                                                    OR event_id = '29'
                                                                    ORDER BY unittime DESC 
                                                                    LIMIT 1";
                                                                    // 28 = Disabled
                                                                    // 29 = Enabled
                                                            $laststarter = $this->report_data->getReport($sql, array());
                                                            // $sql = "SELECT *
                                                            //         FROM " . $row['db'] . ".unit" . $row['unit_id'] . " ue1
                                                            //         WHERE event_id = '109'
                                                            //         OR event_id = '110'
                                                            //         ORDER BY id DESC 
                                                            //         LIMIT 1";
                                                            //         // 109 = ON
                                                            //         // 110 = OFF
                                                            // $lastreminder = $this->report_data->getReport($sql, array());
                                                            $address = $this->address_logic->validateAddress($lastevent[0]['streetaddress'], $lastevent[0]['city'], $lastevent[0]['state'], $lastevent[0]['zipcode'], $lastevent[0]['country']);
                                                            $label = str_replace('"','\"',$address);

                                                            $report['code'] = 0; 
                                                            $unittime=date('U',strtotime($lastevent[0]['unittime']));
                                                            $now = date('U');
                                                            $difference=$now-$unittime;
                                                            $days = floor ( $difference / 86400 );
                                                            $difference = $difference - ( $days * 86400 ) ;
                                                            $hours = floor ( $difference / 3600 );
                                                            $difference = $difference - ( $hours * 3600 ) ;
                                                            $minutes = floor ( $difference / 60 );
                                                            $seconds = $difference - ( $minutes * 60 ) ;
                                                            $difference = null ;
                                                            if($now>$unittime){
                                                                if ( $days ) { $difference .= $days . ' Days'; }
                                                                if ( $difference ) { $difference .= ', '; }
                                                                if ( $hours ) { $difference .= $hours . ' Hours'; }
                                                                if ( $difference ) { $difference .= ', '; }
                                                                if ( $minutes ) { $difference .= $minutes . ' Minutes'; }
                                                                // if ( $difference ) { $difference .= ', '; }
                                                                // if ( $seconds ) { $difference .= $seconds . ' Seconds'; }
                                                                if ( ! ( $difference ) ) {
                                                                    $difference = '<span title="Unit:' . $lastevent[0]['unittime'] . '; Server:' . date('Y-m-d H:i:s') . '">< 1 minute</span>' ;
                                                                }
                                                            } else {
                                                                $diff = $now  - $unittime;
                                                                $unittime = $unittime - 1800;
                                                                if($now<$unittime){
                                                                    $difference = '<span class="alert-warning" title="' . $diff . ' = ' . $now . ' - ' . $unittime . ' (' . $lastevent[0]['unittime'] . ')">Unit Reporting Future Timestamp</span>' ;
                                                                }
                                                                if ( ! ( $difference ) ) {
                                                                    $difference = '<span title="Unit:' . $lastevent[0]['unittime'] . '; Server:' . date('Y-m-d H:i:s') . '">< 5 minutes</span>' ;
                                                                }
                                                            }
                                                            
                                                            $sql = "SELECT
                                                                        ue1.*,
                                                                        ume.eventname as eventname
                                                                    FROM " . $row['db'] . ".unit" . $row['unit_id'] . " ue1
                                                                    LEFT JOIN unitmanagement.event ume ON ume.event_id = ue1.event_id
                                                                    WHERE ue1.id IS NOT NULL AND ue1.event_id < 14 AND ue1.event_id != 1 AND ue1.event_id != 7 AND ue1.event_id != 8 
                                                                    ORDER BY ue1.unittime DESC LIMIT 1";
                                                            $laststatus = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                            $stopped = NULL;
                                                            $status = NULL;
                                                            switch ($laststatus[0]['event_id']) {

                                                                case                          2 :
                                                                case                          5 :
                                                                case                          6 :
                                                                case                         10 :
                                                                case                         13 :   $status = '<span class="label label-danger">Stopped</span>' ;
                                                                                                    $stopped = 1; 
                                                                                                    break;

                                                                case                          3 :
                                                                case                          4 :
                                                                case                          9 :
                                                                case                         11 :
                                                                case                         12 :   if($days<1){
                                                                                                        $status = '<span class="label label-success">Moving</span>' ; 
                                                                                                    } else {
                                                                                                        $status = 'Moving*' ; 
                                                                                                    }
                                                                                                    break;

                                                                                        default :   $status = 'n/a';

                                                            }
                                                            $starterstatus = $laststarter[0]['unittime'] ;
                                                            switch ($laststarter[0]['event_id']) {
                                                            
                                                                case                          29 :  $starter = '<span class="label label-danger">Disabled</span>' ;
                                                                                                    break;

                                                                                        default :   $starter = '<span class="label label-success">Enabled</span>' ; 
                                                                                                    break;
                                                            }
                                                            $reminderstatus = $lastreminder[0]['unittime'] ;
                                                            switch ($lastreminder[0]['event_id']) {
                                                            
                                                                case                          110 :  $reminder = '<span class="label label-danger">Active</span>' ;
                                                                                                     break;

                                                                                        default :   $reminder = '<span class="label label-success">Off</span>' ; 
                                                                                                    break;
                                                            }
                                                            
                                                            $report['records']++;
                                                            $length++;
                                                            if(($params['length']>0)&&($length>$params['length'])){
                                                                $length=1;
                                                                $page++;
                                                            }
                                                            if($page==$params['pageCount']){

                                                                $onClick="Core.DataTable.pop('modal-edit-scheduled-report','modal-title=" . str_replace("'", "\'", $row['reporttypename']) . ";','modal-edit-scheduled-report-name=" . str_replace("'", "\'", $row['schedulereportname']) . ";','scheduled-recurrence=" . str_replace("'", "\'", $row['schedule']) . ";scheduled-day=" . str_replace("'", "\'", $row['scheduleday']) . ";scheduled-monthly=" . str_replace("'", "\'", $row['monthday']) . ";scheduled-time=" . str_replace("'", "\'", $row['sendhour']) . ";scheduled-format=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-mode=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-single=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-group=" . str_replace("'", "\'", $row['format']) . ";');";

                                                                $odometer=$row['initialodometer']+$row['currentodometer'];

                                                                switch($evenOdd){
                                                                    case     '' : $evenOdd = 'report-even-odd ';
                                                                                    break;
                                                                        default : $evenOdd = '';
                                                                }

                                                                if(!($row['unitname'])){
                                                                    $row['unitname'] = $row['serialnumber'] ;
                                                                }

                                                                if($report['mobile']){

                                                                    $status                    = $this->vehicle_data->getMoving($row['unit_id']);
                                                                    $moving                    = $this->vehicle_data->getDuration($row['unit_id'],$status);
                                                                    $battery                   = $this->vehicle_data->getBattery($row['unit_id']);
                                                                    $signal                    = $this->vehicle_data->getSignal($row['unit_id']);
                                                                    $satellites                = $this->vehicle_data->getSatellites($row['unit_id']);
                                                                    // $territoryname             = $this->vehicle_data->getTerritory($row['unit_id']);
                                                                    switch($status){
                                                                        case          1  :
                                                                        case         '1' :
                                                                        case          3  :
                                                                        case         '3' :
                                                                        case          4  :
                                                                        case         '4' : $stopMoveColor = 'moving';
                                                                                           $stopMove = 'Moving';
                                                                                           break;
                                                                                 default : $stopMoveColor = 'stopped';
                                                                                           $stopMove = 'Stopped';
                                                                    }
                                                                    if($moving['duration']=='n/a'){
                                                                        $stopMoveColor = 'Inventory';
                                                                        $stopMove = 'Inventory' ;
                                                                    }

                                                                    $report['tbody'] .= '<tr>'
                                                                        . '<td class="' . $evenOdd . 'col-vehicle no-wrap sorting_1"'
                                                                        . ' data-unit-id="' . $row['unit_id'] . '"'
                                                                        . '><a href="javascript:void(0);" id="map" class="navigation report-address-id unitname text-black text-bold text-large">' . $row['unitname'] . '</a></td>'
                                                                        . '<td class="' . $evenOdd . 'col-vehicle no-wrap sorting_1 text-right" rowspan="3"'
                                                                        . ' data-unit-id="' . $row['unit_id'] . '"'
                                                                        . '><a href="javascript:void(0);" id="map" class="navigation report-address-id unitname text-blue text-bold text-large">map</a></td>'
                                                                        . '</tr><tr>'
                                                                        // . '<td class="' . $evenOdd . '">' . $row['unitgroupname'] . '</td>'
                                                                        // . '</tr><tr>'
                                                                        // . '<td class="' . $evenOdd . '" title="Event #' . $row['unit_id'] . '_' . $laststatus[0]['id'] . ' (' . date('m/d/Y h:ia' , strtotime($laststatus[0]['servertime'])) . ')">' . $status . '</td>'
                                                                        // . '</tr><tr>'
                                                                        // . '<td class="' . $evenOdd . '">' . $difference . '</td>'
                                                                        // . '</tr><tr>'
                                                                        . '<td class="' . $evenOdd . 'address_map_link"'
                                                                        . ' data-battery="' . $battery['level'] . '"'
                                                                        . ' data-duration="' . $moving['duration'] . '"'
                                                                        . ' data-event="' . $lastevent[0]['eventname'] . '"'
                                                                        . ' data-id="' . $row['unit_id'] . '"'
                                                                        . ' data-label="' . $label . '"'
                                                                        . ' data-latitude="' . $lastevent[0]['latitude'] . '"'
                                                                        . ' data-longitude="' . $lastevent[0]['longitude'] . '"'
                                                                        . ' data-name="' . $row['unitname'] . '"'
                                                                        . ' data-satellites="' . $satellites['level'] . '"'
                                                                        . ' data-signal="' . $signal['level'] . '"'
                                                                        . ' data-speed="' . $lastevent[0]['speed'] . '"'
                                                                        . ' data-state="' . $moving['state'] . '"'
                                                                        . ' data-status="' . $status . '"'
                                                                        . ' data-stop-move="' . $stopMove . '"'
                                                                        . ' data-territoryname="' . $lastevent[0]['territoryname'] . '"'
                                                                        . ' data-unittime="' . Date::utc_to_locale($lastevent[0]['unittime'], $timezone, 'h:i A m/d/Y') . '"'
                                                                        . ' data-unit_id="' . $row['unit_id'] . '"'
                                                                        . ' data-unitname="' . $row['unitname'] . '"'
                                                                        . ' id="report-address-' . $row['unit_id'] . '"'
                                                                        . '><a href="javascript:void(0);" id="map" class="navigation report-address">' . $address . '</a></td>'
                                                                        . '</tr><tr>'
                                                                        . '<td class="' . $evenOdd . '"'
                                                                        . ' data-unit-id="' . $row['unit_id'] . '"'
                                                                        . '><a href="javascript:void(0);" id="map" class="navigation report-address-id unitname text-' . $stopMoveColor . '">' . $stopMove . '&nbsp;(' . $moving['duration'] . ')</a>&nbsp;&nbsp;&nbsp;' . $lastevent[0]['eventname'] . '&nbsp;&nbsp;<span class="text-grey">' . Date::utc_to_locale($lastevent[0]['unittime'], $timezone, 'h:i A m/d/Y') . '</span></td>'
                                                                        // . '</tr><tr>'
                                                                        // . '<td class="' . $evenOdd . '" title="Last Move ' . $row['lastmove'] . '">' . date('m/d/Y h:ia' , strtotime($row['updated'])) . '</td>'
                                                                        // . '</tr><tr>'
                                                                        // . '<td class="' . $evenOdd . '" title="' . number_format($row['initialodometer']) . ' Miles at Install + ' . number_format($row['currentodometer']) . ' Tracked Miles">' . number_format($odometer) . '</td>'
                                                                        . '</tr>';
                                                                } else {
                                                                    $report['tbody'] .= '<tr>'
                                                                        . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                        . '<td class="' . $evenOdd . 'col-vehicle no-wrap sorting_1">' . '<a class="vehicle-edit" href="javascript:void(0);" id="vehicle-list-table-' . $row['unit_id'] . '">' . $row['unitname'] . '</a></td>'
                                                                        . '<td class="' . $evenOdd . '">' . $row['unitgroupname'] . '</td>'
                                                                        . '<td class="' . $evenOdd . '" title="Event #' . $row['unit_id'] . '_' . $laststatus[0]['id'] . ' (' . date('m/d/Y h:ia' , strtotime($laststatus[0]['servertime'])) . ')">' . $status . '</td>'
                                                                        . '<td class="' . $evenOdd . '">' . $difference . '</td>'
                                                                        . '<td class="' . $evenOdd . 'address_map_link" data-eventname="' . $lastevent[0]['eventname'] . '" data-name="' . $row['unitname'] . '" data-id="' . $row['unit_id'] . '" data-latitude="' . $lastevent[0]['latitude'] . '" data-longitude="' . $lastevent[0]['longitude'] . '" data-label="' . $label . '"><a href="#">' . $address . '</a></td>'
                                                                        . '<td class="' . $evenOdd . '" title="Event #' . $row['unit_id'] . '_' . $lastevent[0]['id'] . ' (' . date('m/d/Y h:ia' , strtotime($lastevent['servertime'])) . ')">' . $lastevent[0]['eventname'] . '</td>'
                                                                        // . '<td class="' . $evenOdd . '" title="Last Move ' . $row['lastmove'] . '">' . date('m/d/Y h:ia', strtotime($this->base_logic->timezoneDelta($timezone,$lastevent[0]['unittime'],1))) . '</td>'
                                                                        . '<td class="' . $evenOdd . '" title="Last Move ' . $row['lastmove'] . '">' . date('m/d/Y h:ia', strtotime($this->base_logic->tzUtc2Local($timezone,$lastevent[0]['unittime']))) . '</td>'
                                                                        . '<td class="' . $evenOdd . '" title="' . number_format($row['initialodometer']) . ' Miles at Install + ' . number_format($row['currentodometer']) . ' Tracked Miles">' . number_format($odometer) . '</td>'
                                                                        . '</tr>';
                                                                        //
                                                                        //<td class="' . $evenOdd . '" title="Event #' . $row['unit_id'] . '_' . $lastreminder[0]['id'] . ' (' . date('m/d/Y h:ia' , strtotime($reminderstatus)) . ')">' . $reminder . '</td>
                                                                        // 
                                                                }
                                                            }
                                                        }
                                                    }
                                                    // $report['records'] = $report['records'] + 10000000;
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    if(!($report['tbody'])){
                                                        $report['tbody'] = '<tr><td colspan="9"><i>No Data Found</i></td></tr>';
                                                    } else if(!($report['mobile'])){
                                                        $report['lastReport'] = $this->base_logic->wizardMapAllLink();
                                                    }
// $report['tbody'] = '<tr><td colspan="9">' . $sqll . '---' . $params['sidebarVehicleStatus'] . '</td></tr>';
                                                    break;

            case     'verification-report-recent' : $sqlPlaceHolder[] = $params['unit_id'];
                                                    $report['message'] = '&nbsp;<p>';
                                                    $report['code'] = 0; 
                                                    if($params['search']){
                                                        $search = ' AND ( t.streetaddress LIKE \'' . $params['search'] . '%\' OR t.streetaddress LIKE \'%' . $params['search'] . '%\' OR t.city LIKE \'' . $params['search'] . '%\' OR t.city LIKE \'%' . $params['search'] . '%\' OR t.state LIKE \'' . $params['search'] . '%\' OR t.state LIKE \'%' . $params['search'] . '%\' OR t.zipcode LIKE \'' . $params['search'] . '%\' OR t.zipcode LIKE \'%' . $params['search'] . '%\' OR t.territoryname LIKE \'' . $params['search'] . '%\' OR t.territoryname LIKE \'%' . $params['search'] . '%\' OR tg.territorygroupname LIKE \'' . $params['search'] . '%\' OR tg.territorygroupname LIKE \'%' . $params['search'] . '%\' OR u.unitname LIKE \'' . $params['search'] . '%\' OR u.unitname LIKE \'%' . $params['search'] . '%\' )' ;
                                                        $report['search'] = $params['search'];
                                                        $report['message'] .= ' Searching for: "' . $report['search'] . '"<p>';
                                                    }
                                                    switch($params['sidebarVerification']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : if ( $params['sidebarVerification'] == 'verified' ) {
                                                                                        $verification = ' AND t.verifydate != \'0000-00-00\'' ;
                                                                                    } else {
                                                                                        $verification = ' AND t.verifydate = \'0000-00-00\'' ;
                                                                                    }
                                                                                    $report['message'] .= ' Verification Type "' . $params['sidebarVerification'] . '" Not Found<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    switch($params['sidebarVehicleSingle']){
                                                        case                   '' :
                                                        case                'all' : break;
                                                                          default : $vehicle = ' AND u.unit_id = ?' ;
                                                                                    $sqlPlaceHolder[] = $params['sidebarVehicleSingle'];
                                                                                    $report['message'] .= ' Vehicle Id "' . $params['sidebarVehicleSingle'] . '" Not Found<p>';
                                                                                    $report['code'] = 0; 
                                                    }
                                                    $sql = "SELECT
                                                                t.*,
                                                                tg.territorygroupname as territorygroupname,
                                                                ut.unit_id as unit_id,
                                                                u.unitname as unitname
                                                            FROM crossbones.territory t
                                                            LEFT JOIN crossbones.territorygroup tg ON tg.territorygroup_id = t.territorygroup_id
                                                            LEFT JOIN crossbones.unit_territory ut ON ut.territory_id = t.territory_id
                                                            LEFT JOIN crossbones.unit u ON u.unit_id = ut.unit_id
                                                            WHERE t.account_id = ? 
                                                            AND u.unit_id = ?
                                                            AND t.active > 0
                                                            AND t.territorytype = 'reference'"
                                                            . $search
                                                            . $verification
                                                            . $vehicle
                                                            . " ORDER BY t.territoryname ASC";

                                                    $rows = $this->report_data->getReport($sql, $sqlPlaceHolder);
                                                    
                                                    $report['thead'] = '<tr><th class="tiniwidth"><div class="pull-right">#&nbsp;</div></th><th>Name</th><th>Address</th><th>Lat/Long</th><th>Radius</th><th>Verified</th><th>Last at Location</th><th class="tinywidth">Delete</th></tr>';
                                                    // $report['tbody'] = '<tr><td colspan="12">' . $sql . '</td></tr>';
                                                    if(!($rows)){$report['tbody'] = '<tr><td colspan="7"><i>No Data Found</i></td></tr>';}
                                                    $page=1;
                                                    $evenOdd = 'report-even-odd ';
                                                    foreach ($rows as $key => $row) {
                                                        $report['code'] = 0; 
                                                        $report['records']++;
                                                        $length++;
                                                        if(($params['length']>0)&&($length>$params['length'])){
                                                            $length=1;
                                                            $page++;
                                                        }
                                                        if($page==$params['pageCount']){
                                                            $address = $this->address_logic->validateAddress($row['streetaddress'], $row['city'], $row['state'], $row['zipcode'], $row['country']);
                                                            $label = str_replace('"','\"',$address);
                                                            switch ($row['radius']){

                                                                case            '' :
                                                                case           '0' : $radius = 'n/a';
                                                                                     break;

                                                                case         '330' : $radius = '1/16 Mile';
                                                                                     break;

                                                                case         '660' : $radius = '1/8 Mile';
                                                                                     break;

                                                                case        '1320' : $radius = '1/4 Mile';
                                                                                     break;

                                                                case        '2640' : $radius = '1/2 Mile';
                                                                                     break;

                                                                case        '5280' : $radius = '1 Mile';
                                                                                     break;

                                                                           default : $milecount=0;
                                                                                     $rcnt = $row['radius'] ;
                                                                                     while($rcnt>5279){
                                                                                        $milecount++;
                                                                                        $rcnt = $rcnt - 5280;
                                                                                        $radius = $milecount . ' Miles';
                                                                                     }
                                                                                     if ($rcnt) {
                                                                                        $radius .= ' ' . $rcnt . ' feet';
                                                                                     }

                                                            }
                                                            switch ($row['shape']){

                                                                case      'circle' : $shape = 'Circle';
                                                                                     break;

                                                                case     'polygon' : $shape = 'Polygon';
                                                                                     break;

                                                                case      'square' : $shape = 'Square';
                                                                                     break;

                                                                           default : $shape = 'models/logic/reportlogic.php';

                                                            }
                                                            if($row['verifydate'] == '0000-00-00'){
                                                                $status = '<span class="label label-danger report-label">Not Verified</span>' ;
                                                                $date = NULL ;
                                                            } else {
                                                                $date = date('m/d/Y' , strtotime($row['verifydate'])) ;
                                                                $status = '<span class="label label-success report-label">Verified</span>' ;
                                                            }
                                                            $onClick="Core.DataTable.pop('modal-edit-scheduled-report','modal-title=" . str_replace("'", "\'", $row['reporttypename']) . ";','modal-edit-scheduled-report-name=" . str_replace("'", "\'", $row['schedulereportname']) . ";','scheduled-recurrence=" . str_replace("'", "\'", $row['schedule']) . ";scheduled-day=" . str_replace("'", "\'", $row['scheduleday']) . ";scheduled-monthly=" . str_replace("'", "\'", $row['monthday']) . ";scheduled-time=" . str_replace("'", "\'", $row['sendhour']) . ";scheduled-format=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-mode=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-single=" . str_replace("'", "\'", $row['format']) . ";scheduled-contact-group=" . str_replace("'", "\'", $row['format']) . ";');";
                                                            switch($evenOdd){
                                                                case     '' : $evenOdd = 'report-even-odd ';
                                                                                break;
                                                                    default : $evenOdd = '';
                                                            }
                                                            if(!($row['territoryname'])){
                                                                $row['territoryname']='[new record]';
                                                            }
                                                            if(!($address)){
                                                                $address = '[new address]' ;
                                                            }
                                                            $report['tbody'] .= '<tr>'
                                                                . '<td class="' . $evenOdd . '"><div class="pull-right">' . $report['records'] . '&nbsp;&nbsp;&nbsp;</div></td>'
                                                                . '<td class="' . $evenOdd . '">' . $this->base_logic->wizardInput($params['pid'],$report['records'],'crossbones-territory-territoryname',$row['territory_id'],$row['territoryname'],'',$evenOdd) . '</td>'
                                                                // . '<td class="' . $evenOdd . '">' . $row['territoryname'] . '</td>'
                                                                . '<td class="' . $evenOdd . 'address_map_link" data-eventname="Stop" data-name="' . $row['territoryname'] . '" data-id="' . $row['unit_id'] . '" data-latitude="' . $row['latitude'] . '" data-longitude="' . $row['longitude'] . '" data-label="' . $row['territoryname'] . '"><a href="#">' . $address . '</a></td>'
                                                                . '<td class="' . $evenOdd . '">' . $row['latitude'] . ' / ' . $row['longitude'] . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $radius . '</td>'
                                                                . '<td class="' . $evenOdd . '" valign="center">' . $status . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $date . '</td>'
                                                                . '<td class="' . $evenOdd . '">' . $this->base_logic->wizardDeleteRecord('landmark',$params['pid'],$report['records'],$row['territory_id']) . '</td>'
                                                                . '</tr>'; 
                                                        }
                                                    }
                                                    if(!($report['tbody'])){
                                                        $report['tbody'] = '<tr><td colspan="9"><i>No Data Found</i></td></tr>';
                                                    } else {
                                                        $report['lastReport'] .= $this->base_logic->wizardMapAllLink();
                                                        $report['lastReport'] .= '<span class="text-12 pull-right">&nbsp;&nbsp;</span>' ;
                                                        $report['lastReport'] .= '<a href="javascript:void(0);" id="popover-verification-export-pdf-confirm" class="btn btn-default btn-small btn-icon pull-right"><span class="wtext"><i class="gi gi-file_export"></i></span>&nbsp;PDF</a>' ;
                                                        $report['lastReport'] .= '<span class="text-12 pull-right">&nbsp;&nbsp;</span>' ;
                                                    }
                                                    $report['lastReport'] .= '<button class="btn btn-default btn-icon btn-small verification-add verification-add-show pull-right" href="#" title="create a new record..."><span class="wtext"><i class="gi gi-plus"></i></span>&nbsp;Address</button>' ;
                                                    $report['pageCount'] = $params['pageCount'];
                                                    $report['pageTotal'] = $page;
                                                    break;

                                         default :  $report['code'] = 0; 
                                                    $report['thead'] = '<tr><th>Models/Logic/ReportLogic.php:getReport</td></tr>'; 
                                                    $report['tbody'] = '<tr><td>' . $params['pid'] . '</td></tr>'; 
                                                    $report['records'] = 0;
                                                    
        }

        if ( ! ( $report['lastReport'] ) ) {
            // date_default_timezone_set($params['user_timezone']);
            // $report['lastReport'] = ' <span style="color:#808080;font-size:12px;">' . date( 'M d, Y h:i a' ) . '</span> <span style="color:#b3b3b3;font-size:8px;">(' . str_replace('_',' ',$params['user_timezone']) . ')</span>' ;
            $report['lastReport'] = ' ';
        }

        return $report;

    }


    /**
     * Get the scheduled reports by filtered paramaters (called via ajax)
     *
     * POST params: filter_type
     * POST params: format
     * POST params: recurance
     * POST params: contactgroup_id
     * POST params: report_type
     * POST params: search_string
     *
     * @return array
     */
    public function getFilteredScheduleReports($account_id, $params)
    {
        $total_reports = array();
        $reports['iTotalRecords']          = 0;
        $reports['iTotalDisplayRecords']   = 0;
        $reports['data']                   = array();

        $this->validator->validate('record_id', $account_id);

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('Invalid Parameters');
        } else {
            //alphanumeric $params['search_string']
            if (isset($params['string_search']) AND $params['string_search'] != "") {
                $this->validator->validate('alphanumeric', $params['string_search']);
            }
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {

            switch ($params['filter_type']) {

                case 'string_search':   $searchfields = array('schedulereportname', 'reporttypename', 'contactgroupname');
                                        $report = $this->report_data->getFilteredScheduleReportsStringSearch($account_id, $params, $searchfields);
                                        if ($report !== false) {
                                            $total_reports = $report;
                                        }
                                        break;

                case 'group_filter':    if (isset($params['recurrance']) AND strtolower($params['recurrance']) == 'all') {
                                            $params['schedule'] = array();
                                        } elseif (! is_array($params['recurrance'])) {
                                            $params['schedule'] = array($params['recurrance']);
                                        }

                                        if (isset($params['contactmode']) AND strtolower($params['contactmode']) == 'single') {
                                            $params['contactgroup_id'] = array();
                                            $params['contact_id'] = $params['contact_id'];
                                        } else if (isset($params['contactmode']) AND strtolower($params['contactmode']) == 'group') {
                                            $params['contact_id'] = array();
                                            $params['contactgroup_id'] = $params['contactgroup_id'];
                                        } else {
                                            $params['contactgroup_id'] = '';
                                            $params['contact_id'] = '';
                                        }

                                        if (isset($params['vehiclemode']) AND strtolower($params['vehiclemode']) == 'single') {
                                            $params['unitgroup_id'] = array();
                                            $params['unit_id'] = $params['vehicle_id'];
                                        } else if (isset($params['vehiclemode']) AND strtolower($params['vehiclemode']) == 'group') {
                                            $params['unit_id'] = array();
                                            $params['unitgroup_id'] = $params['vehiclegroup_id'];
                                        } else {
                                            $params['unitgroup_id'] = '';
                                            $params['unit_id'] = '';
                                        }

                                        if (isset($params['territorymode']) AND strtolower($params['territorymode']) == 'single') {
                                            $params['territorygroup_id'] = array();
                                            $params['territory_id'] = $params['territory_id'];
                                        } else if (isset($params['territorymode']) AND strtolower($params['territorymode']) == 'group') {
                                            $params['territory_id'] = array();
                                            $params['territorygroup_id'] = $params['territorygroup_id'];
                                        } else {
                                            $params['territorygroup_id'] = '';
                                            $params['territory_id'] = '';
                                        }

                                        if (isset($params['reporttype_id']) AND strtolower($params['reporttype_id']) == 'all') {
                                            $params['reporttype_id'] = '';
                                        }

                                        $report = $this->report_data->getFilteredScheduleReports($account_id, $params);
                                        if ($report !== false) {
                                            $total_reports = $report;
                                        }

                                        break;

                            default:    break;
            }

            // for the formatted unit events, process for datatable return results
            if (! empty($total_reports)) {

                // init total results
                $iTotal                             = count($total_reports);
                $iFilteredTotal                     = count($total_reports);
                $reports['iTotalRecords']         = $iTotal;
                $reports['iTotalDisplayRecords']  = $iFilteredTotal;
                $aColumns                           = array();        // datatable columns event field/key names

                for ( $i = 0 ; $i < intval( $params['iColumns'] ) ; $i++ ) {
                    $aColumns[] = $params['mDataProp_'.$i];
                }

                $formatted_results = array();
                if (! empty($total_reports)) {
                    foreach ($total_reports as $report) {
                        $row = $report;
                        $row['DT_RowId'] = 'schedulereport-tr-'.$row['schedulereport_id'];       // automatic tr id value for dataTable to set

                        if ($row['schedulereportname'] == '' OR is_null($row['schedulereportname'])){
                            $row['schedulereportname'] = $params['default_value'];
                        }

                        // determine if alert has contact or contact group
                        $contactname = '';
                        if (isset($row['contact_id']) AND isset($row['contactname']) AND $row['contact_id'] > 0 AND $row['contactname'] != '') {
                            $contactname = $row['contactname'];
                        } else if (isset($row['contactgroup_id']) AND isset($row['contactgroupname']) AND $row['contactgroup_id'] != '' AND $row['contactgroup_id'] > 0 AND $row['contactgroupname'] != '') {
                            $contactname = $row['contactgroupname'];
                        }
                        $row['contactname'] = $contactname;

                        // determine if alert has unit or unit group
                        $unitname = '';
                        if (isset($row['unit_id']) AND isset($row['unitname']) AND $row['unit_id'] > 0  AND $row['unitname'] != '') {
                            $unitname = $row['unitname'];
                        } else if (isset($row['unitgroup_id']) AND isset($row['unitgroupname']) AND $row['unitgroup_id'] > 0  AND $row['unitgroupname'] != '') {
                            $unitname = $row['unitgroupname'];
                        }
                        $row['unitname'] = $unitname;

                        // get last triggered date from alerthistory
                        $row['nextruntime'] = Date::utc_to_locale($row['nextruntime'], $params['user_timezone'], 'g:i a m/d/Y');

                        $row['sendhour'] = $this->convertHourToTimeString($row['sendhour']);

                        $formatted_results[] = $row;
                    }

                    // if doing a column sorting
                    if ( isset( $params['iSortCol_0'] ) AND $params[ 'bSortable_'.intval($params['iSortCol_0']) ] == "true") {
                        $formatted_results = $this->filterReportsSort($aColumns[ intval($params['iSortCol_0']) ], ($params['sSortDir_0']==='asc' ? 'asc' : 'desc'), $formatted_results);
                    }

                    // if doing paging, find correct page list
                    if ( isset($params['iDisplayStart']) AND $params['iDisplayLength'] != '-1' ) {
                        $formatted_results = array_splice($formatted_results, $params['iDisplayStart'], $params['iDisplayLength']);
                    }
                }

                $reports['data'] = $formatted_results;
            }
        }
                                                    
        return $reports;
    }

    /**
     * convert Hour to Time String
     *
     * @params: string $column_name
     * @params: string $sort_order
     * @params: array alerts
     *
     * @return array $results
     */
    public function convertHourToTimeString($send_hour)
    {
        $send_time = "12:00 AM (Midnight)";
        if ($send_hour != '') {
            $send_time = date("g:00 a", mktime($send_hour, 0, 0, 0, 0, 0));
        }

        return $send_time;
    }

    /**
     * Return schedule reports having sorted by column field by sort order
     *
     * @params: string $column_name
     * @params: string $sort_order
     * @params: array alerts
     *
     * @return array $results
     */
    public function filterReportsSort($column_name, $sort_order, $reports)
    {
        $results = $reports;
        $sorting_order = '<';       // ascending sort by default
        if ( $sort_order == 'desc') {
            $sorting_order = '>';       // descending sort
        }

        if ( isset($column_name) AND $column_name != "" ) {
            switch($sorting_order) {
                case '<':
                    usort($results, Arrayhelper::usort_compare_asc($column_name));
                break;
                case '>':
                    usort($results, Arrayhelper::usort_compare_desc($column_name));
                break;
            }
        }

        return $results;
    }


    /**
     * Get the report history by filtered paramaters (called via ajax)
     *
     * POST params: filter_type
     * POST params: user_id
     * POST params: date_range
     * POST params: report_type
     * POST params: search_string
     *
     * @return array
     */
    public function getFilteredReportHistory($account_id, $params)
    {
        $total_reports = array();
        $reports['iTotalRecords']          = 0;
        $reports['iTotalDisplayRecords']   = 0;
        $reports['data']                   = array();

        $this->validator->validate('record_id', $account_id);

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('Invalid Parameters');
        } else {
            //alphanumeric $params['search_string']
            if (isset($params['string_search']) AND $params['string_search'] != "") {
                $this->validator->validate('alphanumeric', $params['string_search']);
            }
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {

            // default values
            $params['fromdate'] = Date::locale_to_utc(date('Y-m-d 00:00:00', strtotime('90 days ago')), $params['user_timezone']);
            $params['todate'] = Date::locale_to_utc(date('Y-m-d H:i:s'), $params['user_timezone']);

            switch ($params['filter_type']) {

                case 'string_search':

                    $searchfields = array('reporthistoryname', 'reporttypename', 'username');
                    $report = $this->report_data->getFilteredReportHistoryStringSearch($account_id, $params, $searchfields);
                    if ($report !== false) {
                        $total_reports = $report;
                    }

                break;

                case 'group_filter':
                    if (isset($params['starttime']) AND ! empty($params['starttime'])) {
                        $params['fromdate'] = Date::locale_to_utc(date('Y-m-d 00:00:00', strtotime($params['starttime'])), $params['user_timezone']);

                    }
                    if (isset($params['endtime']) AND ! empty($params['endtime'])) {
                        if ($params['endtime'] != $params['starttime']) {
                            if ($params['dayrange'] == 'This Month' OR $params['dayrange'] == 'Last Month') {
                                $params['todate'] = Date::locale_to_utc(date('Y-m-d 23:59:59', strtotime($params['endtime'])), $params['user_timezone']);
                            } else {
                                $params['todate'] = Date::locale_to_utc(date('Y-m-d H:i:s', strtotime($params['endtime'])), $params['user_timezone']);
                            }
                        } else if ($params['dayrange'] == 'Yesterday') {
                            $params['todate'] = Date::locale_to_utc(date('Y-m-d 00:00:00'), $params['user_timezone']);
                        }
                    }

                    if (isset($params['user_id']) AND strtolower($params['user_id']) == 'all') {
                        $params['user_id'] = '';
                    } else {

                    }

                    if (isset($params['reporttype_id']) AND strtolower($params['reporttype_id']) == 'all') {
                        $params['reporttype_id'] = '';
                    }

                    $report = $this->report_data->getFilteredReportHistory($account_id, $params);
                    if ($report !== false) {
                        $total_reports = $report;
                    }

                break;

                default:
                break;
            }

            // for the formatted unit events, process for datatable return results
            if (! empty($total_reports)) {

                // init total results
                $iTotal                             = count($total_reports);
                $iFilteredTotal                     = count($total_reports);
                $reports['iTotalRecords']         = $iTotal;
                $reports['iTotalDisplayRecords']  = $iFilteredTotal;
                $aColumns                           = array();        // datatable columns event field/key names

                for ( $i = 0 ; $i < intval( $params['iColumns'] ) ; $i++ ) {
                    $aColumns[] = $params['mDataProp_'.$i];
                }

                $formatted_results = array();
                if (! empty($total_reports)) {
                    foreach ($total_reports as $report) {
                        $row = $report;
                        $row['DT_RowId'] = 'reporthistory-tr-'.$row['reporthistory_id'];       // automatic tr id value for dataTable to set
                        $row['link'] = '';

                        if ($row['reporthistoryname'] == '' OR is_null($row['reporthistoryname'])){
                            $row['reporthistoryname'] = $params['default_value'];
                        }

                        // get last triggered date from alerthistory
                        $row['reportrantime'] = Date::utc_to_locale($row['createdate'], $params['user_timezone'], 'g:i a m/d/Y');

                        $formatted_results[] = $row;
                    }

                    // if doing a column sorting
                    if ( isset( $params['iSortCol_0'] ) AND $params[ 'bSortable_'.intval($params['iSortCol_0']) ] == "true") {
                        $formatted_results = $this->filterReportsSort($aColumns[ intval($params['iSortCol_0']) ], ($params['sSortDir_0']==='asc' ? 'asc' : 'desc'), $formatted_results);
                    }

                    // if doing paging, find correct page list
                    if ( isset($params['iDisplayStart']) AND $params['iDisplayLength'] != '-1' ) {
                        $formatted_results = array_splice($formatted_results, $params['iDisplayStart'], $params['iDisplayLength']);
                    }
                }

                $reports['data'] = $formatted_results;
            }
        }

        return $reports;
    }


    /**
     * Get Schedule report by id
     *
     * @param int schedulereport_id
     *
     * @return bool|array
     */
    public function getScheduleReportById($schedulereport_id)
    {
        $this->validator->validate('record_id', $schedulereport_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            $report = $this->report_data->getScheduleReportById($schedulereport_id);
            if ($report !== false AND is_array($report) AND ! empty($report)) {
                $report = array_pop($report);
            }
            return $report;
        }

        return array();
    }

    /**
     * Save a record of the report that have just been ran
     *
     * @param array params
     * @return void
     */
    public function saveReportHistory($params)
    {
        if (! empty($params)) {

            $save = array();

            // validate account id
            $this->validator->validate('record_id', $params['account_id']);
            if (! $this->validator->hasError()) {
                $save['account_id'] = $params['account_id'];
            }

            // validate method (Manual/Scheduled)
            if (! empty($params['method']) AND ($params['method'] == 'Manual' OR $params['method'] == 'Scheduled')) {
                $save['method'] = $params['method'];

            } else {
                $this->setErrorMessage('Invalid Report Method');
            }

            // validate user id
            $this->validator->validate('record_id', $params['user_id']);
            if (! $this->validator->hasError()) {
                $save['user_id'] = $params['user_id'];
            }

            // validate report name
            $this->validator->validate('report_name', $params['reporthistoryname']);
            if (! $this->validator->hasError()) {
                $save['reporthistoryname'] = $params['reporthistoryname'];
            }

            // validate report type id
            if (! empty($params['reporttype_id']) AND is_numeric($params['reporttype_id']) AND ($params['reporttype_id'] > 0)) {
                $save['reporttype_id'] = $params['reporttype_id'];
            } else {
                $this->setErrorMessage('Invalid Report Type ID');
            }

            // validate alert type id
            if (isset($params['alerttype_id'])) {
                if (is_numeric($params['alerttype_id']) AND ($params['alerttype_id'] >= 0)) {
                    $save['alerttype_id'] = $params['alerttype_id'];
                } else {
                    $this->setErrorMessage('Invalid Alert Type ID');
                }
            }

            // validate minute
            if (isset($params['minute'])) {
                if (! empty($params['minute']) AND is_numeric($params['minute']) AND ($params['minute'] > 0)) {
                    $save['minute'] = $params['minute'];
                } else {
                    $this->setErrorMessage('Invalid Filter Minute');
                }
            }

            // validate day
            if (isset($params['day'])) {
                if (! empty($params['day']) AND is_numeric($params['day']) AND ($params['day'] > 0)) {
                    $save['day'] = $params['day'];
                } else {
                    $this->setErrorMessage('Invalid Filter Day');
                }
            }

            // validate mile
            if (isset($params['mile'])) {
                if (! empty($params['mile']) AND is_numeric($params['mile']) AND ($params['mile'] > 0)) {
                    $save['mile'] = $params['mile'];
                } else {
                    $this->setErrorMessage('Invalid Filter Mile');
                }
            }

            // validate day
            if (isset($params['mph'])) {
                if (! empty($params['mph']) AND is_numeric($params['mph']) AND ($params['mph'] > 0)) {
                    $save['mph'] = $params['mph'];
                } else {
                    $this->setErrorMessage('Invalid Filter Speed');
                }
            }

            // validate verification
            if (! empty($params['verification'])) {
                $save['verification'] = $params['verification'];
            }

            // validate starttime
            if (! empty($params['starttime'])) {
                $save['starttime'] = $params['starttime'];
            }

            // validate endtime
            if (! empty($params['endtime'])) {
                $save['endtime'] = $params['endtime'];
            }

            // validate createdate
            if (! empty($params['createdate'])) {
                $save['createdate'] = $params['createdate'];
            }

        } else {
            $this->setErrorMessage('Invalid Parameters');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            // log report history
            return $this->report_data->saveReportHistory($save);
        }
        return false;
    }

    /**
     * Method for exporting a previously ran report from the report history table
     *
     * @param array params
     * @return array
     */
    public function exportReportHistory($params)
    {
        $this->validator->validate('record_id', $params['reporthistory_id']);

        if (empty($params['report_output']) OR ! in_array($params['report_output'], array('csv','pdf', 'html'))) {
            $this->setErrorMessage('Invalid Export Format');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            // get the report
            $report = $this->getReportHistoryById($params['reporthistory_id']);

            if (! empty($report)) {

                $report['user_timezone'] = $params['user_timezone'];

                // converts the start and end time from utc to the user's local time before exporting the report
                $utc_starttime = (! empty($report['starttime']) AND ($report['starttime'] != '0000-00-00 00:00:00')) ? $report['starttime'] : Date::locale_to_utc(date('Y-m-d 00:00:00'), SERVER_TIMEZONE);
                $utc_endtime = (! empty($report['endtime']) AND ($report['endtime'] != '0000-00-00 00:00:00')) ? $report['endtime'] : Date::locale_to_utc(date('Y-m-d H:i:s'), SERVER_TIMEZONE);

                $report['starttime'] = Date::utc_to_locale($utc_starttime, $params['user_timezone'], 'm/d/Y h:i A');
                $report['endtime'] = Date::utc_to_locale($utc_endtime, $params['user_timezone'], 'm/d/Y h:i A');

                $method = $report['reporttype'];

                $report_data = $this->$method($report);

                if ($params['report_output'] == 'csv') {
                    $report_output = $this->formatReportCsv($report_data);
                } else {
                    $report_output = $report_data;
                }

                return $report_output;
            }
        }
        return false;
    }

    /**
     * Get a report that had been ran from the report history table
     *
     * @param int reporthistory_id
     * @return array
     */
    public function getReportHistoryById($reporthistory_id)
    {
        $this->validator->validate('record_id', $reporthistory_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {

            $report = $this->report_data->getReportHistoryById($reporthistory_id);

            if (! empty($report)) {

                $report = $report[0];

                // this 'report_name' key is required since it's used all report generating
                if (! empty($report['reporthistoryname'])) {
                    $report['report_name'] = $report['reporthistoryname'];
                }

                if (! empty($report['unit_mode']) AND $report['unit_mode'] == 'unit') {
                    $report['unit_mode'] = 'single';
                }

                if (! empty($report['territory_mode']) AND $report['territory_mode'] == 'territory') {
                    $report['territory_mode'] = 'single';
                }

                // get the method name to run the report
                $reporttypes = $this->getReportTypes();
                foreach($reporttypes as $rt) {
                    if ($rt['reporttype_id'] == $report['reporttype_id']) {
                        $report['reporttype'] = $rt['url'];
                        break;
                    }
                }

                return $report;
            }
        }
        return false;
    }

    /**
     * Save units that are associated to a previously ran report
     *
     * @param array params
     * @return bool|int
     */
    public function saveReportHistoryUnit($params)
    {
        $this->validator->validate('record_id', $params['reporthistory_id']);

        if (! empty($params['selection'])) {
            if ($params['selection'] !== 'all') {
                if (isset($params['unit_id'])) {
                    if (! is_numeric($params['unit_id']) OR $params['unit_id'] < 0) {
                        $this->setErrorMessage('Invalid Vehicle ID');
                    }
                } else if (isset($params['unitgroup_id'])) {
                    if (! is_numeric($params['unitgroup_id']) OR $params['unitgroup_id'] < 0) {
                        $this->setErrorMessage('Invalid Vehicle Group ID');
                    }
                }
            }
        } else {
            $this->setErrorMessage('Invalid Vehicle Selection');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->report_data->saveReportHistoryUnit($params);
        }
        return false;
    }

    /**
     * Save territories that are associated to a previously ran report
     *
     * @param array params
     * @return bool|int
     */
    public function saveReportHistoryTerritory($params)
    {
        $this->validator->validate('record_id', $params['reporthistory_id']);

        if (! empty($params['selection'])) {
            if ($params['selection'] !== 'all') {
                if (isset($params['territory_id'])) {
                    if (! is_numeric($params['territory_id']) OR $params['territory_id'] < 0) {
                        $this->setErrorMessage('Invalid Landmark/Boundary ID');
                    }
                } else if (isset($params['territorygroup_id'])) {
                    if (! is_numeric($params['territorygroup_id']) OR $params['territorygroup_id'] < 0) {
                        $this->setErrorMessage('Invalid Landmark/Boundary Group ID');
                    }
                } else {
                    $this->setErrorMessage('No Landmark/Boundary or Landmark/Boundary Group selected');
                }
            }
        } else {
            $this->setErrorMessage('Invalid Landmark/Boundary Selection');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->report_data->saveReportHistoryTerritory($params);
        }
        return false;
    }

    /**
     * Save users that are associated to a previously ran report
     *
     * @param array params
     * @return bool|int
     */
    public function saveReportHistoryUser($params)
    {
        $this->validator->validate('record_id', $params['reporthistory_id']);

        if (! empty($params['selection'])) {
            if ($params['selection'] !== 'all') {
                if (isset($params['user_id'])) {
                    if (! is_numeric($params['user_id']) OR $params['user_id'] < 0) {
                        $this->setErrorMessage('Invalid User ID');
                    }
                }
            }
        } else {
            $this->setErrorMessage('Invalid User Selection');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->report_data->saveReportHistoryUser($params);
        }
        return false;
    }

    /**
     * Get scheduled reports to run based on the provided search time
     *
     * @param datetime search_time
     * @return bool|array
     */
    public function getScheduledReportsToRun($search_time)
    {
        if (empty($search_time) OR (strlen($search_time) != 19)) {
            $this->setErrorMessage('Invalid run time');
        }

        if (! $this->hasError()) {
            $reports = $this->report_data->getScheduledReportsToRun($search_time);

            if (! empty($reports)) {
                //print_r($reports);
                // get the available report types
                $reporttypes = $this->getReportTypes(true);

                foreach ($reports as $index => $report) {
                    // set the report type method name
                    $reports[$index]['reporttype'] = $reporttypes[$report['reporttype_id']]['url'];

                    // get contacts
                    $contacts = array();
                    if (! empty($report['contact_id'])) {
                        $contacts = $this->contact_logic->getContactById($report['contact_id']);
                    } else if (! empty($report['contactgroup_id'])) {
                        $contacts = $this->contact_logic->getContactByGroupId($report['contactgroup_id']);
                    }
                    $reports[$index]['contacts'] = $contacts;

                    // rename unit and territory mode value to the ones use for generating reports
                    if (! empty($report['unit_mode']) AND $report['unit_mode'] == 'unit') {
                        $reports[$index]['unit_mode'] = 'single';
                    }

                    if (! empty($report['territory_mode']) AND $report['territory_mode'] == 'territory') {
                        $reports[$index]['territory_mode'] = 'single';
                    }
                }
            } else {
                $reports = array();
            }
            return $reports;
        }
        return false;
    }

    /**
     * Delete Scheduled Report (mark as inactive)
     *
     * @param int report_id
     * @param int account_id
     *
     * @return bool
     */
    public function deleteScheduledReport($report_id, $account_id)
    {
        $this->validator->validate('record_id', $report_id);
        $this->validator->validate('record_id', $account_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->report_data->deleteScheduledReport($report_id, $account_id);
        }

        return false;
    }

    /**
     * Update a scheduled report
     *
     * @param int report_id
     * @param array param
     * @return bool
     */
    public function updateScheduledReport($report_id, $params)
    {
        $this->validator->validate('record_id', $report_id);

        if (empty($params) OR ! is_array($params)) {
            $this->setErrorMessage('Invalid parameter');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->report_data->updateScheduledReport($report_id, $params);
        }
        return false;
    }

    /**
     * Update a scheduled report Info
     *
     * @param int $report_id
     * @param int account_id
     * @param array params
     *
     * @return bool
     */
    public function updateScheduledReportInfo($report_id, $account_id, $params)
    {
        $this->validator->validate('record_id', $report_id);
        $this->validator->validate('record_id', $account_id);

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('Invalid parameter');
        } else {
            if (isset($params['schedulereportname'])) {
                $this->validator->validate('report_name', $params['schedulereportname']);
                if (! $this->validator->hasError()) {
                    // check for report name duplication
                    $exist_report = $this->report_data->getScheduleReportByName($account_id, $params['schedulereportname']);
                    if (! empty($exist_report)) {
                        $this->setErrorMessage('Duplicated Report Name');
                    }
                }
            }
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->report_data->updateScheduledReport($report_id, $params);
        }

        return false;
    }

    /**
     * Update a scheduled report territory association
     *
     * @param int $report_id
     * @param array params
     *
     * @return bool
     */
    public function updateScheduledReportTerritory($report_id, $params)
    {
        $this->validator->validate('record_id', $report_id);

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('Invalid parameter');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->report_data->updateScheduledReportTerritory($report_id, $params);
        }

        return false;
    }

    /**
     * Delete a scheduled report territory association
     *
     * @param int $report_id
     *
     * @return bool
     */
    public function deleteScheduledReportTerritory($report_id)
    {
        $this->validator->validate('record_id', $report_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->report_data->deleteScheduledReportTerritory($report_id);
        }

        return false;
    }


    /**
     * Update a scheduled report unit association
     *
     * @param int $report_id
     * @param array params
     *
     * @return bool
     */
    public function updateScheduledReportUnit($report_id, $params)
    {
        $this->validator->validate('record_id', $report_id);

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('Invalid parameter');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {

            return $this->report_data->updateScheduledReportUnit($report_id, $params);
        }

        return false;
    }

    /**
     * Delete a scheduled report territory association
     *
     * @param int $report_id
     *
     * @return bool
     */
    public function deleteScheduledReportUnit($report_id)
    {
        $this->validator->validate('record_id', $report_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->report_data->deleteScheduledReportUnit($report_id);
        }

        return false;
    }

    /**
     * Update a scheduled report user association
     *
     * @param int $report_id
     * @param int user_id
     * @param array params
     *
     * @return bool
     */
    public function updateScheduledReportUser($report_id, $params)
    {
        $this->validator->validate('record_id', $report_id);

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('Invalid parameter');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {

            return $this->report_data->updateScheduledReportUser($report_id, $params);

        }

        return false;
    }

    /**
     * Delete a scheduled report user association
     *
     * @param int $report_id
     *
     * @return bool
     */
    public function deleteScheduledReportUser($report_id)
    {
        $this->validator->validate('record_id', $report_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->report_data->deleteScheduledReportUser($report_id);
        }

        return false;
    }

    /**
     * Update a scheduled report contact
     *
     * @param int $report_id
     * @param array params
     *
     * @return bool
     */
    public function updateScheduledReportContact($report_id, $params)
    {
        $this->validator->validate('record_id', $report_id);

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('Invalid parameter');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->report_data->updateScheduledReportContact($report_id, $params);
        }

        return false;
    }

    /**
     * Get error messages (calls the parent method)
     *
     * @param string token
     *
     * @return bool|array
     */
    public function getErrorMessage()
    {
        return parent::getErrorMessage();
    }

}
