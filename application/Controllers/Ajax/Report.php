<?php

namespace Controllers\Ajax;

//use Models\Data\VehicleData;
use Models\Logic\VehicleLogic;
//use Models\Data\TerritoryData;
use Models\Logic\TerritoryLogic;
use Models\Logic\ReportLogic;
use Models\Logic\UserLogic;
use Models\Logic\ContactLogic;

use GTC\Component\Utils\CSV\CSVBuilder;
use GTC\Component\Utils\PDF\PDFDataAdapter;
use GTC\Component\Utils\PDF\TCPDFBuilder;

/**
 * Class Report
 *
 */
class Report extends BaseAjax
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->vehicle_logic = new VehicleLogic;
        $this->territory_logic = new TerritoryLogic;
        $this->report_logic = new ReportLogic;
        $this->user_logic = new UserLogic;
        $this->contact_logic = new ContactLogic;
        
        // generating report uses Twig
        $this->load_twig();

    }
     
    /*
     * Genereate Report
     *
     * POST params: reporttype, reporttype_id, start_date, end_date, unit_mode, unit_id, unitgroup_id, territory_mode, territory_id, territorygroup_id, filter_speed, filter_minutes, filter_days, filter_miles, filter_stop_number, user_id
     */
    public function runReport()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();
        $params     = array();
        $error      = '';

        $ajax_data['code'] = 0;
        $ajax_data['data']['head'] = '<tr><td>Ajax/Report.php:runReport</td></tr>';
        $ajax_data['data']['report'] = '<tr><td>Ajax/Report.php:runReport</td></tr>';
        $ajax_data['message'] = 'Ajax/Report.php:runReport';

        $processed_params = $this->report_logic->processReportPostParameters($post);
        $params = $processed_params['params'];
        $error  = $processed_params['error'];

        if ($error == '') {
        
            $params['account_id'] = $this->user_session->getAccountId();        
            // defaults report output to html for now
            $params['report_output'] = 'html';
            
            $params['user_timezone'] = $this->user_session->getUserTimeZone();
            
            $params['user_id'] = $this->user_session->getUserId();
            
            $params['method'] = 'Manual';

            $report_output = $this->report_logic->runReport($params);

            if ($report_output !== false) {

                $ajax_data['code'] = 0;
                $view_data = $report_output;
                $view_data['mapalladdresses'] = $this->report_logic->mapAllAddresses();

                $key=key($report_output['units']);
                
                if(isset($report_output['units'][@$key]['report_data'])){
                    $ajax_data['data']['head']  = $this->twig->render('page/report/generator/head.html.twig', $view_data);
                    $ajax_data['data']['report']  = $this->twig->render('page/report/generator/report.html.twig', $view_data);
                    $ajax_data['data']['reporthistory_id'] = $report_output['reporthistory_id'];
                    $ajax_data['data']['report_type_name'] = $report_output['report']['report_type'];
                    $ajax_data['message'] = 'Generated';  
                } else {
                    $ajax_data['data']['head'] = '<tr><td>Please Consider Adjusting Sidebar Filters</td></tr>';
                    $ajax_data['data']['report'] = '<tr><td>No Data Found</td></tr>';
                    $ajax_data['data']['reporthistory_id']='';
                    $ajax_data['data']['report_type_name']='';
                    $ajax_data['message'] = 'No Data';  
                }  

            } else {

                $errors = $this->report_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(',', $errors);
                } else {
                    $errors = 'Action failed due to a database issue';
                }
                
                $ajax_data['code'] = 1;
                $ajax_data['message'] = $errors; 
                $ajax_data['message'] = 'ERROR 2'; 

            }

        } else {

            $ajax_data['code'] = 1;
            $ajax_data['message'] = $error;
            $ajax_data['message'] = 'ERROR 1'; 

        }   

// $ajax_data['message'] .= ' : ' . $ajax_data['code'];

        $this->ajax_respond($ajax_data);    
    }

    /*
     * Saving Schedule Report
     *
     * POST params: reporttype, reporttype_id, start_date, end_date, unit_mode, unit_id, unitgroup_id, territory_mode, territory_id, territorygroup_id, filter_speed, filter_minutes, filter_days, filter_miles, filter_stop_number, user_id
     */
    public function saveScheduleReport()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();
        $params     = array();
        $error      = '';
        
        $processed_params = $this->report_logic->processReportPostParameters($post);
        $params = $processed_params['params'];
        $error  = $processed_params['error'];

        if ($error == '') {
        
            $params['account_id']       = $this->user_session->getAccountId();
            $params['user_id']          = $this->user_session->getUserId();        
            $params['user_timezone']    = $this->user_session->getUserTimeZone();

            $scheduled_report = $this->report_logic->saveScheduleReport($params);
            if ($scheduled_report !== false) {
                $ajax_data['code'] = 0;
                $view_data = $scheduled_report;
                $ajax_data['data'] = $scheduled_report;
                $ajax_data['message'] = 'Scheduled';    
            } else {
                $errors = $this->report_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(',', $errors);
                } else {
                    $errors = 'Error';
                }
                
                $ajax_data['code'] = 1;
                $ajax_data['message'] = $errors;
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = $error;
        }   

        $this->ajax_respond($ajax_data);    
    }


    public function testRunReport()
    {

        $view_data = array(
            'report' => array(
                'title'                     => 'Mileage Summary',
                'time_generated'            => date('m/d/Y h:i A'),
                'criteria'  => array(
                    'selected_vehicles'     => array(
                        'label'             => 'Vehicles Selected',
                        'value'             => 'All Vehicles'
                    )
                ),
                'columns' => array(
                    'unitname'              => 'Vehicle Name',
                    'mileage'               => 'Miles Driven',
                    'odometer'              => 'Intial Odometer',
                    'total_mileage'         => 'Total Miles'
                ),
                'summary'                   => array(
                    'something' => array(
                        'label'             => 'Some Label',
                        'value'             => 'Some Value'
                    ),
                    'something_more' => array(
                        'label'             => 'Another Label',
                        'value'             => 'Another Value'
                    ),


                )
            ),
            'units' => array(
                0 => array( // table/unit id
                    'unit_id' => 0, // ignore
                    'report_title' => 'Vehicle: Some Fake Unit',
                    'report_data' => array(
                        /*0 => array( // row
                            'unitname'      => 'Some Fake Unit', // cols
                            'mileage'       => '555555555',
                            'odometer'      => '111111111',
                            'total_mileage' => '444444444',
                        )*/
                    )
                ),
                1 => array( // table/unit id
                    'unit_id' => 0, // ignore
                    'report_title' => 'Vehicle: Another Fake Unit',
                    'report_data' => array(
                        0 => array( // row
                            'unitname'      => 'Another Fake Unit', // cells
                            'mileage'       => '555555555',
                            'odometer'      => '111111111',
                            'total_mileage' => '444444444',
                            'map'           => array(
                                'lat'   => '1.111',
                                'long'  => '2.22'
                            )
                        ),
                        1 => array( // row
                            'unitname'      => ' Another Fake Unit', // cells
                            'mileage'       => '3333333',
                            'odometer'      => '333333',
                            'total_mileage' => '7777777',
                            'map'           => array(
                                'lat'   => '1.111',
                                'long'  => '2.22'
                            )
                        ),
                        2 => array( // row
                            'unitname'      => 'Another Fake Unit', // cells
                            'mileage'       => '555555555',
                            'odometer'      => '111111111',
                            'total_mileage' => '444444444',
                            'map'           => array(
                                'lat'   => '1.111',
                                'long'  => '2.22'
                            )
                        ),
                        3 => array( // row
                            'unitname'      => ' Another Fake Unit', // cells
                            'mileage'       => '3333333',
                            'odometer'      => '333333',
                            'total_mileage' => '7777777',
                            'map'           => array(
                                'lat'   => '1.111',
                                'long'  => '2.22'
                            )
                        ),
                        4 => array( // row
                            'unitname'      => 'Another Fake Unit', // cells
                            'mileage'       => '555555555',
                            'odometer'      => '111111111',
                            'total_mileage' => '444444444',
                            'map'           => array(
                                'lat'   => '1.111',
                                'long'  => '2.22'
                            )
                        ),
                        5 => array( // row
                            'unitname'      => ' Another Fake Unit', // cells
                            'mileage'       => '3333333',
                            'odometer'      => '333333',
                            'total_mileage' => '7777777',
                            'map'           => array(
                                'lat'   => '1.111',
                                'long'  => '2.22'
                            )
                        ),
                        6 => array( // row
                            'unitname'      => 'Another Fake Unit', // cells
                            'mileage'       => '555555555',
                            'odometer'      => '111111111',
                            'total_mileage' => '444444444',
                            'map'           => array(
                                'lat'   => '1.111',
                                'long'  => '2.22'
                            )
                        ),
                        7 => array( // row
                            'unitname'      => ' Another Fake Unit', // cells
                            'mileage'       => '3333333',
                            'odometer'      => '333333',
                            'total_mileage' => '7777777',
                            'map'           => array(
                                'lat'   => '1.111',
                                'long'  => '2.22'
                            )
                        )
                    ),
                    'summary' => array(
                        'something' => array(
                            'label' => 'summary item 1',
                            'value' => 'value 1'
                        ),
                        'something_else' => array(
                            'label' => 'summary item 2',
                            'value' => 'value 2'
                        )
                    )
                ),
                2 => array( // table/unit id
                    'unit_id' => 0, // ignore
                    'report_title' => 'Vehicle: Another Fake Unit',
                    'report_data' => array(
                        0 => array( // row
                            'unitname'      => 'Another Fake Unit', // cells
                            'mileage'       => '555555555',
                            'odometer'      => '111111111',
                            'total_mileage' => '444444444',
                        ),
                        1 => array( // row
                            'unitname'      => ' Another Fake Unit', // cells
                            'mileage'       => '3333333',
                            'odometer'      => '333333',
                            'total_mileage' => '7777777',
                        ),
                        2 => array( // row
                            'unitname'      => 'Another Fake Unit', // cells
                            'mileage'       => '555555555',
                            'odometer'      => '111111111',
                            'total_mileage' => '444444444',
                        ),
                        3 => array( // row
                            'unitname'      => ' Another Fake Unit', // cells
                            'mileage'       => '3333333',
                            'odometer'      => '333333',
                            'total_mileage' => '7777777',
                        ),
                        4 => array( // row
                            'unitname'      => 'Another Fake Unit', // cells
                            'mileage'       => '555555555',
                            'odometer'      => '111111111',
                            'total_mileage' => '444444444',
                        ),
                        5 => array( // row
                            'unitname'      => ' Another Fake Unit', // cells
                            'mileage'       => '3333333',
                            'odometer'      => '333333',
                            'total_mileage' => '7777777',
                        ),
                        6 => array( // row
                            'unitname'      => 'Another Fake Unit', // cells
                            'mileage'       => '555555555',
                            'odometer'      => '111111111',
                            'total_mileage' => '444444444',
                        ),
                        7 => array( // row
                            'unitname'      => ' Another Fake Unit', // cells
                            'mileage'       => '3333333',
                            'odometer'      => '333333',
                            'total_mileage' => '7777777',
                        )
                    ),
                    'summary' => array(
                        'something' => array(
                            'label' => 'summary item 1',
                            'value' => 'value 1'
                        ),
                        'something_else' => array(
                            'label' => 'summary item 2',
                            'value' => 'value 2'
                        )
                    )
                ),

            )
        );

        $ajax_data = array();

        $ajax_data['code'] = 0;

        $ajax_data['data']['head']  = $this->twig->render('page/report/generator/head.html.twig', $view_data);
        $ajax_data['data']['report']  = $this->twig->render('page/report/generator/report.html.twig', $view_data);

        $ajax_data['message'] = 'Success';

        $this->ajax_respond($ajax_data);

    }

    /*
     * Genereate Report
     *
     * POST params: format, reporthistory_id
     */
    public function exportReport($format, $reporthistory_id)
    {
        $ajax_data  = array();
        $params     = array();
        $error      = '';

        if (! empty($format)) {
            $params['report_output'] = $format;
        } else {
            $error = 'Invalid Report Output';
        }

        if (! empty($reporthistory_id)) {
            $params['reporthistory_id'] = $reporthistory_id;
        } else {
            $error = 'Invalid Report History Id';
        }

        if ($error == '') {
        
            $params['account_id'] = $this->user_session->getAccountId();        
            
            $params['user_timezone'] = $this->user_session->getUserTimeZone();
           
            //$report_output = $this->report_logic->runReport($params);
            $report_output = $this->report_logic->exportReportHistory($params);
            if ($report_output !== false) {
                if ($params['report_output'] == 'pdf') {
                    ini_set('memory_limit', '128M');
                    $pdf_output = PDFDataAdapter::format($report_output);
                    $pdf_builder = new TCPDFBuilder('L');
                    $pdf_builder->create($pdf_output);
                    $pdf_builder->Output(date('mdY').'_'.ucfirst($report_output['report']['title']).'_Report.pdf', 'D');
                } else if ($params['report_output'] == 'csv') {
                    // generate csv
                    $csv_builder = new CSVBuilder();
                    $csv_builder->setSeparator(',');
                    $csv_builder->setClosure('"');
                    $csv_builder->setFields($report_output['columns']);
                    $csv_builder->format($report_output['data'])->export(ucfirst($report_output['title']).'_Report');
                } else {
                    // assume html
                    $ajax_data = array();

                    $ajax_data['code'] = 0;

                    $ajax_data['data']['head']    = $this->twig->render('page/report/generator/head.html.twig', $report_output);
                    $ajax_data['data']['report']  = $this->twig->render('page/report/generator/report.html.twig', $report_output);
                    $ajax_data['data']['report_type_name'] = $report_output['report']['report_type'];
                    $ajax_data['message'] = 'Successfully generated the report';

                    $this->ajax_respond($ajax_data);
                }
            } else {
                $errors = $this->report_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(',', $errors);
                } else {
                    $errors = 'Action failed due to a database issue';
                }

                exit('Error: ' . $errors);
            }
        } else {
            exit('Error: ' . $error);  
        }   
        
        exit();    
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
    public function getFilteredScheduleReports()
    {
        $ajax_data      = array();
        $post           = $this->request->request->all();
        $user_id        = $this->user_session->getUserId();
        $account_id     = $this->user_session->getAccountId();
        $user_timezone  = $this->user_session->getUserTimeZone();

        $params                  = $post;
        $params['user_timezone'] = $user_timezone;
        $params['account_id']    = $account_id;
        
        $report = $this->report_logic->getReport($account_id, $user_id, $params);

        if ($report !== false) {
            $output              = $report;
            $output['records']   = (isset($report['records']) AND ! empty($report['records'])) ? $report['records'] : 0;
            $output['length']    = (isset($report['length'])  AND ! empty($report['length']))  ? $report['length']  : 0;
            $output['data']      = json_encode($report['json']);
            $output['code']      = $report['code'];
            $output['message']   = $report['message'];
        } else {
            $output['code']      = 86 ;
            $output['message']   = 'logout';
        }

        $this->ajax_respond($output);
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
    public function getFilteredScheduleReportsBak()
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
            $reports = $this->report_logic->getFilteredScheduleReports($account_id, $params);
            if ($reports !== false) {
                
                $output['iTotalRecords']        = (isset($reports['iTotalRecords']) AND ! empty($reports['iTotalRecords'])) ? $reports['iTotalRecords'] : 0;
                $output['iTotalDisplayRecords'] = (isset($reports['iTotalDisplayRecords']) AND ! empty($reports['iTotalDisplayRecords'])) ? $reports['iTotalDisplayRecords'] : 0;
                $output['data']                 = (isset($reports['data']) AND ! empty($reports['data'])) ? $reports['data'] : array();
            }
        }

        echo json_encode( $output );
        exit;
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
    public function getFilteredReportHistory()
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
        $params['account_id'] = $account_id;
        
        if ($search_type != '') {
            $reports = $this->report_logic->getFilteredReportHistory($account_id, $params);
            if ($reports !== false) {
                
                $output['iTotalRecords']        = (isset($reports['iTotalRecords']) AND ! empty($reports['iTotalRecords'])) ? $reports['iTotalRecords'] : 0;
                $output['iTotalDisplayRecords'] = (isset($reports['iTotalDisplayRecords']) AND ! empty($reports['iTotalDisplayRecords'])) ? $reports['iTotalDisplayRecords'] : 0;
                $output['data']                 = (isset($reports['data']) AND ! empty($reports['data'])) ? $reports['data'] : array();
            }
        }

        echo json_encode( $output );
        exit;
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
    public function getScheduleReportById()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();
        
        if (! empty($post['schedulereport_id'])) {
            $schedulereport_id = $post['schedulereport_id'];
            $report = $this->report_logic->getScheduleReportById($schedulereport_id);
            if ($report !== false AND ! empty($report)) {
                $ajax_data['code'] = 0;
                $ajax_data['data']['report'] = $report;
                $ajax_data['message'] = 'Successfully retrieved report details';
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'No report was found for the given report ID';
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'No report ID was given';
        }    
        
        $this->ajax_respond($ajax_data);                    
    }

    /**
     * update scheduled reports info
     *
     * POST params: array primary_keys (reportPk)
     * POST params: id
     * POST params: value
     *
     * @return array
     */
    public function updateScheduledReportInfo()
    {
        $ajax_data = $report_updates    = $unit_updates = $landmark_updates = $contact_updates = $user_updates = array();
        $post                           = $this->request->request->all();
        $account_id                     = $this->user_session->getAccountId();
        $user_timezone                  = $this->user_session->getUserTimeZone();
        $report_id                      = intval(trim($post['primary_keys']['reportPk']));
        $reporttype_id                  = (isset($post['primary_keys']['reportTypeId'])) ? $post['primary_keys']['reportTypeId'] : '';
        $current_schedule               = (isset($post['primary_keys']['schedule'])) ? $post['primary_keys']['schedule'] : '';
        $current_scheduleday            = (isset($post['primary_keys']['scheduleDay'])) ? $post['primary_keys']['scheduleDay'] : 'Everyday';
        $current_monthday               = (isset($post['primary_keys']['monthDay'])) ? $post['primary_keys']['monthDay'] : '';
        $current_sendhour               = (isset($post['primary_keys']['sendHour'])) ? $post['primary_keys']['sendHour'] : '';

        if (isset($post['id'])) {
        
            // get report current schedule info
            switch ($post['id']) {
                case 'report-schedule-name':
                    $report_updates['schedulereportname'] = $post['value'];
                    break;
                case 'report-schedule-type':
                    $report_updates['reporttype_id'] = $post['value'];
                    $reporttype_id = $post['value'];
                    break;
                case 'report-alert-type':
                    $report_updates['reporttype_id'] = 1;
                    $report_updates['alerttype_id'] = $post['value'];
                    break;

                case 'report-landmark-single':
                    $report_updates['reporttype_id'] = 5;
                    $landmark_updates['selection'] = 'territory';
                    $landmark_updates['territory_id'] = $post['value'];
                    $landmark_updates['territorygroup_id'] = 0;
                    break;
                case 'report-landmark-group':
                    $report_updates['reporttype_id'] = 5;
                    $landmark_updates['selection'] = 'group';
                    $landmark_updates['territorygroup_id'] = $post['value'];
                    $landmark_updates['territory_id'] = 0;
                    break;
                case 'report-landmark-mode':
                    if($post['value'] == 'single') {
                        $post['value'] = 'territory';
                    } else if($post['value'] == 'all') {
                        $landmark_updates['territory_id'] = 0;
                        $landmark_updates['territorygroup_id'] = 0;
                    }
                    $landmark_updates['selection'] = $post['value'];
                    break;

                case 'report-vehicle-single':
                    $unit_updates['selection'] = 'unit';
                    $unit_updates['unit_id'] = $post['value'];
                    $unit_updates['unitgroup_id'] = 0;
                    break;
                case 'report-vehicle-group':
                    $unit_updates['selection'] = 'group';
                    $unit_updates['unitgroup_id'] = $post['value'];
                    $unit_updates['unit_id'] = 0;
                    break;
                case 'report-vehicle-mode':
                    if($post['value'] == 'single') {
                        $post['value'] = 'unit';
                    } else if($post['value'] == 'all') {
                        $unit_updates['unit_id'] = 0;
                        $unit_updates['unitgroup_id'] = 0;
                    }
                    $unit_updates['selection'] = $post['value'];
                    break;   

                case 'report-frequent-stops-duration-threshold':
                    $report_updates['reporttype_id'] = 4;
                    $report_updates['minute'] = $post['value'];
                    break;
                case 'report-mileage-filter':
                    $report_updates['reporttype_id'] = 6;
                    $report_updates['mile'] = $post['value'];
                    break;
                case 'report-reference':
                    $report_updates['reporttype_id'] = 14;
                    $report_updates['verification'] = $post['value'];
                    break;
                case 'report-user-command-user':
                    $report_updates['reporttype_id'] = 12;
                    $user_updates['user_id'] = $post['value'];
                    if ($post['value'] != 0) {
                        $user_updates['selection'] = 'user';
                    } else {
                        $user_updates['selection'] = 'all';
                    }
                    break;
                case 'report-speed-filter':
                    $report_updates['reporttype_id'] = 8;
                    $report_updates['mph'] = $post['value'];
                    break;
                case 'report-not-reporting':
                    $report_updates['reporttype_id'] = 7;
                    $report_updates['day'] = $post['value'];
                    break;
                case 'report-stationary-filter':
                    $report_updates['reporttype_id'] = 10;
                    $report_updates['day'] = $post['value'];
                    break;
                case 'report-stop-threshold':
                    $report_updates['reporttype_id'] = 11;
                    $report_updates['minute'] = $post['value'];
                    break;
                case 'report-dates':
                    $report_updates['`range`'] = $this->report_logic->processDateRangeString($post['value']);
                    break;
                case 'scheduled-recurrence':
                    $report_updates['schedule'] = $post['value'];

                    $nextrun_params['schedule'] = $report_updates['schedule'];
                    $nextrun_params['sendhour'] = $current_sendhour;
                    $nextrun_params['scheduleday'] = $current_scheduleday;
                    $nextrun_params['monthday'] = $current_monthday;
                    $nextrun_params['user_timezone'] = $user_timezone;
                                        
                    $report_updates['nextruntime'] = $this->report_logic->calculateNextRunTime($nextrun_params);
                    break;
                case 'scheduled-day':
                    $report_updates['scheduleday'] = $post['value'];

                    $nextrun_params['schedule'] = $current_schedule;
                    $nextrun_params['sendhour'] = $current_sendhour;
                    $nextrun_params['scheduleday'] = $report_updates['scheduleday'];
                    $nextrun_params['monthday'] = $current_monthday;
                    $nextrun_params['user_timezone'] = $user_timezone;
                    
                    $report_updates['nextruntime'] = $this->report_logic->calculateNextRunTime($nextrun_params);
                    
                    $report_updates['schedule'] = $current_schedule;
                    $report_updates['monthday'] = 0;
                    break;
                case 'scheduled-monthly':
                    $report_updates['monthday'] = $post['value'];

                    $nextrun_params['schedule'] = $current_schedule;
                    $nextrun_params['sendhour'] = $current_sendhour;
                    $nextrun_params['scheduleday'] = $current_scheduleday;
                    $nextrun_params['monthday'] = $report_updates['monthday'];
                    $nextrun_params['user_timezone'] = $user_timezone;
                    
                    $report_updates['nextruntime'] = $this->report_logic->calculateNextRunTime($nextrun_params);
                    
                    $report_updates['schedule'] = 'Monthly';
                    $report_updates['scheduleday'] = 'Everyday';
                    break;
                case 'scheduled-time':
                    $report_updates['sendhour'] = $post['value'];

                    $nextrun_params['schedule'] = $current_schedule;
                    $nextrun_params['sendhour'] = $report_updates['sendhour'];
                    $nextrun_params['scheduleday'] = $current_scheduleday;
                    $nextrun_params['monthday'] = $current_monthday;
                    $nextrun_params['user_timezone'] = $user_timezone;

                    $report_updates['nextruntime'] = $this->report_logic->calculateNextRunTime($nextrun_params);
                    break;
                case 'scheduled-format':
                    $report_updates['format'] = $post['value'];
                    break;

                case 'report-contact-single':
                    $contact_updates['contact_id'] = $post['value'];            
                    $contact_updates['contactgroup_id'] = 0;
                    break;
                case 'report-contact-group':
                    $contact_updates['contactgroup_id'] = $post['value'];
                    $contact_updates['contact_id'] = 0;
                    break;
            }
        }

        $ajax_data['code'] = 0;
        $ajax_data['data'] = $post;
        $ajax_data['message'] = 'Updated Scheduled Report Information';

        if (! empty($report_updates)) {

            // if reporttype_id is set and is not empty, clear and reset default values
            if (isset($reporttype_id) AND ! empty($reporttype_id)) {
                // if is mileage/nonreporting/starter/stationary/vehicle info/last 10 stop reports, no need for date range value
                if (in_array($reporttype_id, array(6,7,9,10,13,15))) {
                    $report_updates['`range`'] = '0 day';
                }

                // if not alert type report, no need for alerttype_id set
                if (! in_array($reporttype_id, array(1))) {
                    $report_updates['alerttype_id'] = '0';
                }

                // if not speed report, no need for mph set
                if (! in_array($reporttype_id, array(8))) {
                    $report_updates['mph'] = '0';
                }

                // if not speed report, no need for mph set
                if (! in_array($reporttype_id, array(6))) {
                    $report_updates['mile'] = '0';
                }
                // if not verification report, no need for verification set
                if (! in_array($reporttype_id, array(14))) {
                    $report_updates['verification'] = 'None';
                }
                // if not frequent/nonreporting/stationary/stop report, no need for day set
                if (! in_array($reporttype_id, array(4, 7, 10, 11))) {
                    $report_updates['day'] = '0';
                }
                // if not frequest/stop report, no need for minute set
                if (! in_array($reporttype_id, array(4, 11))) {
                    $report_updates['minute'] = '0';
                }

                // if not landmark report
                if (! in_array($reporttype_id, array(5))) {
                    // if no unit selected, default to all units
                    if (! isset($unit_updates['selection']) OR empty($unit_updates['selection'])) {
                        $unit_updates['selection']    = 'all'; 
                        $unit_updates['unit_id']      = 0;    
                        $unit_updates['unitgroup_id'] = 0;                            
                    }

                    // if no landmark set, default to all landmarks
                    if (! isset($landmark_updates['selection']) OR empty($landmark_updates['selection'])) {
                        $landmark_updates['selection']    = 'all'; 
                        $landmark_updates['territory_id']      = 0;    
                        $landmark_updates['territorygroup_id'] = 0;                            
                    }
                }

                // if stationary/vehicle info report, set for all units
                if (in_array($reporttype_id, array(10,13))) {
                    if (! isset($unit_updates['selection']) OR empty($unit_updates['selection'])) {
                        $unit_updates['selection']    = 'all'; 
                        $unit_updates['unit_id']      = 0;    
                        $unit_updates['unitgroup_id'] = 0;                            
                    }
                }
            }

            // update the scheduledreport table info for this report_id
            if ($this->report_logic->updateScheduledReportInfo($report_id, $account_id, $report_updates)) {
                $ajax_data['code'] = 0;
                $ajax_data['data'] = $post;
                $ajax_data['message'] = 'Updated Scheduled Report Information';

                // if not landmark reporttype, remove landmark association
                if (! empty($report_updates['reporttype_id']) AND ! in_array($report_updates['reporttype_id'], array(5))) {
                    $this->report_logic->deleteScheduledReportTerritory($report_id);
                }
                
                // if user command reporttype, no need for vehicle association
                if (! empty($report_updates['reporttype_id']) AND in_array($report_updates['reporttype_id'], array(12))) {
                    $this->report_logic->deleteScheduledReportUnit($report_id);
                } else {
                    // if not a user command reporttype, no need for user association
                    $this->report_logic->deleteScheduledReportUser($report_id);
                }
            } else {
                $errors = $this->report_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(',', $errors);
                } else {
                    $errors = 'Action failed due to a database issue';
                }
                
                $ajax_data['code'] = 1;
                $ajax_data['validation_error'][] = $ajax_data['message'] = $errors;
            }
        }
        
        if (! empty($landmark_updates)) {
            // update relateion
            if ($this->report_logic->updateScheduledReportTerritory($report_id, $landmark_updates)) {
                // success message has already been set from above    
            } else {
                $errors = $this->report_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(',', $errors);
                } else {
                    $errors = 'Action failed due to a database issue';
                }
                
                $ajax_data['code'] = 1;
                $ajax_data['validation_error'][] = $ajax_data['message'] = $errors;
            }
        }
        
        if (! empty($unit_updates)) {
            if ($this->report_logic->updateScheduledReportUnit($report_id, $unit_updates)) {
                // success message has already been set from above    
            } else {
                $errors = $this->report_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(',', $errors);
                } else {
                    $errors = 'Action failed due to a database issue';
                }
                
                $ajax_data['code'] = 1;
                $ajax_data['validation_error'][] = $ajax_data['message'] = $errors;
            }
        }
        
        if (! empty($user_updates)) {
            if ($this->report_logic->updateScheduledReportUser($report_id, $user_updates)) {
                // success message has already been set from above    
            } else {
                $errors = $this->report_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(',', $errors);
                } else {
                    $errors = 'Action failed due to a database issue';
                }
                
                $ajax_data['code'] = 1;
                $ajax_data['validation_error'][] = $ajax_data['message'] = $errors;
            }
        }

        if (! empty($contact_updates)) {
            if ($this->report_logic->updateScheduledReportContact($report_id, $contact_updates)) {
                $ajax_data['code'] = 0;
                $ajax_data['data'] = $post;
                $ajax_data['message'] = 'Updated Scheduled Report Contact Information';
            } else {
                $errors = $this->report_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(',', $errors);
                } else {
                    $errors = 'Action failed due to a database issue';
                }
                
                $ajax_data['code'] = 1;
                $ajax_data['validation_error'][] = $ajax_data['message'] = $errors;
            }
        }

        $this->ajax_respond($ajax_data);
    }
    /**
     * Deleting a scheduled report
     *
     * POST params: schedulereport_id
     *
     * @return array
     */
    public function deleteScheduledReport()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();
        $report_id  = (! empty($post['schedulereport_id'])) ? $post['schedulereport_id'] : '';

        if ($report_id != '') {
            $account_id = $this->user_session->getAccountId();
            $deleted_report = $this->report_logic->deleteScheduledReport($report_id, $account_id);
            if ($deleted_report !== false) {
                $ajax_data['code'] = 0;
                $ajax_data['message'] = 'Deleted scheduled report';
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Failed to delete scheduled report';                   
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid report id';
        }

        $this->ajax_respond($ajax_data);
    }

    // return not reported options for report section
    public function getNotReportedOptions()
    {
        $output =
        '[
            {
                "value": "7",
                "text": "7 Days"
            },
            {
                "value": "14",
                "text":  "14 Days"
            },
            {
                "value": "30",
                "text": "30 Days"
            },
            {
                "value": "60",
                "text":  "60 Days"
            },
            {
                "value": "90",
                "text": "90 Days"
            }
        ]
        ';

        die($output);
    }

    // return vehicle group options for report section base on account_id
    public function getVehicleGroupOptions()
    {
        // this response need to be formatted exactly as below (whitespace is arbitrary). DO NOT JSON encode!
        $user_id = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();

        $output = '[';

        $unit_groups = $this->vehicle_logic->getVehicleGroupsByAccountId($account_id);

        if ($unit_groups !== false) {
            $last_index = count($unit_groups) - 1;
            foreach ($unit_groups as $index => $group) {
                $separator = ',';

                if ($index == $last_index) {
                    $separator = '';
                }

                $output .= '{"value": "' . $group['unitgroup_id'] . '", "text": "' . $group['unitgroupname'] . '"}' . $separator;
            }
        }

        $output .= ']';
        die($output);
    }

    // return vehicle options for report section base on account_id
    public function getVehicleOptions($placeholder = null, $value = '')
    {
        $output = '[';

        if ($placeholder !== null) {  // used when setting up alert triggers
            $value = ($value === null) ? '' : $value;
            $output .= '
                {
                    "value": "'.$value.'",
                    "text":  "'.$placeholder.'"
                },
            ';
        }

        $account_id = $this->user_session->getAccountId();

        $vehicles = $this->vehicle_logic->getVehiclesByAccountId($account_id);
        if (! empty($vehicles)) {
            $last_index = count($vehicles) - 1;
            foreach ($vehicles as $index => $vehicle) {
                $separator = ',';

                if ($index == $last_index) {
                    $separator = '';
                }

                $output .= '{"value": "' . $vehicle['unit_id'] . '", "text": "' . $vehicle['unitname'] . '"}' . $separator;
            }
        }

        $output .= ']';

        die($output);
    }

    // return not reported options for report section
    public function getDayofWeekOptions()
    {
        $output =
        '[
            {
                "value": "Everyday",
                "text": "Everyday"
            },
            {
                "value": "Weekdays",
                "text":  "Weekdays"
            },
            {
                "value": "Weekends",
                "text": "Weekends"
            },
            {
                "value": "Monday",
                "text":  "Monday"
            },
            {
                "value": "Tuesday",
                "text": "Tuesday"
            },
            {
                "value": "Wednesday",
                "text":  "Wednesday"
            },
            {
                "value": "Thursday",
                "text": "Thursday"
            },
            {
                "value": "Friday",
                "text":  "Friday"
            },
            {
                "value": "Saturday",
                "text": "Saturday"
            },
            {
                "value": "Sunday",
                "text":  "Sunday"
            }
        ]';

        die($output);
    }

    // return not reported options for report section
    public function getFormatOptions()
    {
        $output =
        '[
            {
                "value": "CSV",
                "text": "CSV"
            },
            {
                "value": "PDF",
                "text":  "PDF"
            }
        ]
        ';

        die($output);
    }

    // return send hour options for report section
    public function getSendHourOptions()
    {
        $output = '[';

           
        for( $i = 0; $i < 24; $i++)  {
            $separator = ',';

            if ($i == 23) {
                $separator = '';
            }
            
            if ($i == 0) {
                $str = " (midnight)";
            } else if ($i == 12) {
                $str = " (noon)";
            } else {
                $str = "";
            }
            
            $hour = date("h:i a", strtotime($i.":00:00"));
            
            $output .= '{"value": "'.$i.'", "text": "' .$hour.$str.'"}' . $separator;
        }


        $output .= ']';

        die($output);
    }

    // return day of month options for report section
    public function getDayofMonthOptions()
    {
        $output = '[';

        for( $i = 1; $i < 32; $i++)  {
            $separator = ',';

            if ($i == 31) {
                $separator = '';
            }
            
            $day = date("jS", mktime(0,0,0,0,$i,0));
            
            $output .= '{"value": "'.$i.'", "text": "' .$day.'"}' . $separator;
        }

        $output .= ']';

        die($output);
    }

    // return mileage options for report section
    public function getMileageOptions()
    {
        $output =
        '[
            {
                "value": "0",
                "text": "None"
            },
            {
                "value": "50000",
                "text":  "> 50,000"
            },
            {
                "value": "75000",
                "text": "> 75,000"
            },
            {
                "value": "100000",
                "text":  "> 100,000"
            },
            {
                "value": "150000",
                "text": "> 150,000"
            },
            {
                "value": "200000",
                "text":  "> 200,000"
            }
        ]
        ';

        die($output);
    }


    // return time duration options for report section
    public function getTimeDurationOptions()
    {
        $output =
        '[
            {
                "value": "30",
                "text": "> 30 Minutes"
            },
            {
                "value": "60",
                "text":  "> 1 Hour"
            },
            {
                "value": "90",
                "text": "> 1 Hour 30 Minutes"
            },
            {
                "value": "120",
                "text":  "> 2 Hours"
            },
            {
                "value": "150",
                "text": "> 2 Hours 30 Minutes"
            }
        ]
        ';

        die($output);
    }

    // return speed options for report section
    public function getOverSpeedOptions()
    {
        $output =
        '[
            {
                "value": "75",
                "text": "> 75 MPH"
            },
            {
                "value": "80",
                "text":  "> 80 MPH"
            },
            {
                "value": "85",
                "text": "> 85 MPH"
            },
            {
                "value": "90",
                "text":  "> 90 MPH"
            },
            {
                "value": "95",
                "text": "> 95 MPH"
            }
        ]
        ';

        die($output);
    }

    // return days threshold  options for report section
    public function getDayThresholdOptions()
    {
        $output =
        '[
            {
                "value": "3",
                "text": "> 3 Days"
            },
            {
                "value": "7",
                "text":  "> 7 Days"
            },
            {
                "value": "14",
                "text": "> 14 Days"
            },
            {
                "value": "30",
                "text":  "> 30 Days"
            },
            {
                "value": "60",
                "text": "> 60 Days"
            },
            {
                "value": "90",
                "text": "> 90 Days"
            }
        ]
        ';

        die($output);
    }


    // return time threshold options for report section
    public function getTimeThresholdOptions()
    {
        $output =
        '[
            {
                "value": "30",
                "text": "> 30 Minutes"
            },
            {
                "value": "60",
                "text":  "> 1 Hour"
            },
            {
                "value": "720",
                "text": "> 12 Hours"
            },
            {
                "value": "1440",
                "text":  "> 1 Day"
            },
            {
                "value": "10080",
                "text": "> 7 Days"
            },
            {
                "value": "20160",
                "text": "> 14 Days"
            },
            {
                "value": "43200",
                "text": "> 30 Days"
            }
        ]
        ';

        die($output);
    }

}