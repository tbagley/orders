<?php

namespace Models\Logic;

use Models\Logic\BaseLogic;
use Models\Data\VehicleData;
use Models\Logic\LandmarkLogic;
use Models\Logic\TerritoryLogic;
use Models\Logic\AddressLogic;
use Models\Logic\UserLogic;

use GTC\Component\Utils\Date;
use GTC\Component\Utils\Arrayhelper;
use GTC\Component\Utils\CSV\CSVBuilder;
use GTC\Component\Unit\Unit;

use Swift\Transport\Validate;

use GTC\Component\Form\Validation;

class VehicleLogic extends BaseLogic
{
    /**
     * Container for error messages
     *
     * @var array
     */
    private $errors = array();

    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();

        //$this->load->model('data/vehicle_data');
        //$this->load->model('logic/address_logic');

        $this->vehicle_data     = new VehicleData;
        $this->address_logic    = new AddressLogic;
        $this->landmark_logic   = new LandmarkLogic;
        $this->territory_logic  = new TerritoryLogic;
        $this->validator        = new Validation;
    }

    /**
     * CRON Device Sweep
     *
     * @params: unit_id
     *
     * @return array
     */
    public function cronDeviceSweep()
    {
        return $this->vehicle_data->cronDeviceSweep();
    }

    public function codebaseMetrics($codebase,$user_id,$mode,$value)
    {
        return $this->vehicle_data->codebaseMetrics($codebase,$user_id,$mode,$value);
    }

    public function activity($account_id,$user_id,$description,$v,$u)
    {
        return $this->vehicle_data->activity($account_id,$user_id,$description,$v,$u);
    }

    /**
     * Support Ajax Update Requests
     */
    public function ajaxAllDevices($user,$names,$ids)
    {
        return $this->vehicle_data->ajaxAllDevices($user,$names,$ids);
    }

    /**
     * Support Ajax Update Requests
     */
    public function ajaxBatch($account_id,$user_id,$command,$units)
    {
        return $this->vehicle_data->ajaxBatch($account_id,$user_id,$command,$units);
    }

    /**
     * Support Ajax Update Requests
     */
    public function ajaxDbDelete($user,$db_t,$rid,$value,$post)
    {
        return $this->vehicle_data->ajaxDbDelete($user,$db_t,$rid,$value,$post);
    }

    /**
     * Support Ajax Update Requests
     */
    public function ajaxDbUpdate($user,$db_t,$rid,$value,$post)
    {
        return $this->vehicle_data->ajaxDbUpdate($user,$db_t,$rid,$value,$post);
    }

    /**
     * Support Ajax Requests for Filling Forms
     */
    public function ajaxFormFill($user_id,$user,$form,$uid)
    {
        return $this->vehicle_data->ajaxFormFill($user_id,$user,$form,$uid);
    }

    /**
     * Support Ajax Requests for Filling Forms
     */
    public function ajaxGroups($group,$aid,$uid)
    {
        return $this->vehicle_data->ajaxGroups($group,$aid,$uid);
    }

    /**
     * Support Ajax Requests for Filling Forms
     */
    public function ajaxGotIt($user_id,$gotit)
    {
        return $this->vehicle_data->ajaxGotIt($user_id,$gotit);
    }

    /**
     * Support Ajax Requests for Filling Forms
     */
    public function gotIt($user_id,$gotit)
    {
        return $this->vehicle_data->gotIt($user_id,$gotit);
    }

    /**
     * Support Ajax Init Requests
     */
    public function ajaxInit($user,$init)
    {
        return $this->vehicle_data->ajaxInit($user,$init);
    }

    /**
     * Support Ajax Locate Requests
     */
    public function ajaxLocate($user,$unit_id)
    {
        return $this->vehicle_data->ajaxLocate($user,$unit_id);
    }

    /**
     * Support Ajax Load List Requests
     */
    public function ajaxLoadList($user_id,$account_id,$ele,$uid,$search)
    {
        return $this->vehicle_data->ajaxLoadList($user_id,$account_id,$ele,$uid,$search);
    }

    /**
     * Support Ajax Load Transerfee Requests
     */
    public function ajaxLoadTransferee($account_id,$createdate)
    {
        return $this->vehicle_data->ajaxLoadTransferee($account_id,$createdate);
    }

    /**
     * Support Ajax Requests for Update Options
     */
    public function ajaxOptions($user,$unit_id,$element,$post)
    {
        return $this->vehicle_data->ajaxOptions($user,$unit_id,$element,$post);
    }

    /**
     * Support Ajax Requests for Permission Updates
     */
    public function ajaxPermissions($user,$usertype_id,$permission_id,$checked,$post)
    {
        return $this->vehicle_data->ajaxPermissions($user,$usertype_id,$permission_id,$checked,$post);
    }

    /**
     * Support Ajax Requests for Permission Updates
     */
    public function ajaxRepo($user,$unit_id,$email,$post)
    {
        return $this->vehicle_data->ajaxRepo($user,$unit_id,$email,$post);
    }

    /**
     * Support Ajax Schedule Report Requests
     */
    public function ajaxScheduleReport($account_id,$user_id,$post)
    {
        return $this->vehicle_data->ajaxScheduleReport($account_id,$user_id,$post);
    }

    /**
     * Support Ajax Selections Requests
     */
    public function ajaxSelections($account_id,$user_id,$element,$uid,$value)
    {
        $user['account_id'] = $account_id;
        $user['user_id'] = $user_id;
        
        $permission = $this->vehicle_data->ajaxPermissionCheck($user,$element,$context);

        $buffer = $this->vehicle_data->ajaxSelections($account_id,$user_id,$element,$uid,$value);

        $buffer['permission'] = $permission ;

        return $buffer;
    }

    /**
     * Support Ajax TranserAccept Requests
     */
    public function ajaxTransferAccept($account_id,$units)
    {
        return $this->vehicle_data->ajaxTransferAccept($account_id,$units);
    }

    /**
     * Support Ajax TranserCancel Requests
     */
    public function ajaxTransferCancel($account_id,$unit,$export)
    {
        return $this->vehicle_data->ajaxTransferCancel($account_id,$unit,$export);
    }

    /**
     * Support Ajax TranserOffer Requests
     */
    public function ajaxTransferOffer($account_id,$user_id,$routing_number,$units)
    {
        return $this->vehicle_data->ajaxTransferOffer($account_id,$user_id,$routing_number,$units);
    }

    /**
     * Support Ajax TranserReject Requests
     */
    public function ajaxTransferReject($account_id,$units)
    {
        return $this->vehicle_data->ajaxTransferReject($account_id,$units);
    }

    /**
     * Support Ajax Update Requests
     */
    public function ajaxUpdate($user,$unit_id,$element,$value,$context)
    {

        switch($element){

            case  'landmark-shape' : $result = $this->vehicle_data->ajaxGetLandmarkData($user,$unit_id);
                                     switch ($value) {
                                        case 'polygon': $current = $this->vehicle_data->ajaxGetLandmarkData($user,$uid);
                                                        if($current['boundingbox']){
                                                            $post['boundingbox'] = $current['boundingbox'];
                                                        } else {
                                                            $latlngs[] = $result['latitude'] . ' ' . $result['longitude'];
                                                            $post['boundingbox'] = $this->landmark_logic->getBoundingBoxValue('square', $latlngs, (! empty($result['radius']) ? $result['radius'] : 0));
                                                        }
                                                        break;
                                               default: $latlngs[] = $result['latitude'] . ' ' . $result['longitude'];
                                                        $post['boundingbox'] = $this->landmark_logic->getBoundingBoxValue($value, $latlngs, (! empty($result['radius']) ? $result['radius'] : 0));
                                                        break;
                                     }
                                     break;

            case 'landmark-radius' : $result = $this->vehicle_data->ajaxGetLandmarkData($user,$unit_id);
                                     $latlngs[] = $result['latitude'] . ' ' . $result['longitude'];
                                     $post['boundingbox'] = $this->landmark_logic->getBoundingBoxValue($result['shape'], $latlngs, (! empty($value) ? $value : 0));
                                     break;

        }

        $permission = $this->vehicle_data->ajaxPermissionCheck($user,$element,$context);

        $buffer = $this->vehicle_data->ajaxUpdate($user,$unit_id,$element,$value,$post,$permission);

        $buffer['permission'] = $permission ;

        return $buffer;

    }

    /**
     * Support Ajax Update List Requests
     */
    public function ajaxUpdateList($user,$unit_id,$element,$value)
    {

        $permission = $this->vehicle_data->ajaxPermissionCheck($user,$element);

        if($permission){
            $buffer = $this->vehicle_data->ajaxUpdateList($user,$unit_id,$element,$value,$post);
        }

        $buffer['permission'] = $permission ;

        return $buffer;

    }

    public function fixLandmark($aid,$uid,$post)
    {
        $latlngs[] = $post['latitude'] . ' ' . $post['longitude'];
        $post['boundingbox'] = $this->landmark_logic->getBoundingBoxValue($post['shape'], $latlngs, (! empty($post['radius']) ? $post['radius'] : 0));
        // return $post['boundingbox'];
        return $this->vehicle_data->fixLandmark($aid,$uid,$post);
    }

    /**
     * Support Batch Commands
     */
    public function getBatchCommands()
    {
        return $this->vehicle_data->getBatchCommands();
    }

    /**
     * Support Repo Requests
     */
    public function getRepo($repoKey)
    {
        return $this->vehicle_data->getRepo($repoKey);
    }

    /**
     * Get the vehicle info by id
     *
     * @params: unit_id
     *
     * @return array
     */
    public function getVehicleInfoById($unit_id)
    {
        $this->validator->validate('record_id', $unit_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            $vehicle = $this->vehicle_data->getVehicleInfo($unit_id);

            if ($vehicle !== false AND count($vehicle) == 1) {
                $vehicle = array_pop($vehicle);
            }

            return $vehicle;
        }

        return false;
    }

    /**
     * Get the vehicle info by group id
     *
     * @params: unitgroup_id
     *
     * @return array
     */
    public function getVehicleInfoByGroupId($unitgroup_id, $device_status = 0, $usertimezone = '', $unit_status = 0)
    {
        $this->validator->validate('record_id', $unitgroup_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->vehicle_data->getVehicleInfoByGroupId($unitgroup_id, $device_status, $usertimezone, $unit_status);
        }

        return false;
    }

    /**
     * Get the vehicle info by vehicle group ids
     *
     * @params: vehicle_groups
     *
     * @return array
     */
    public function getVehiclesByGroupIds($user_id, $vehicle_groups, $account_id)
    {
        $data = array();

        $this->validator->validate('record_id', $user_id);

        if (! is_array($vehicle_groups)) {
            $this->validator->validate('record_id', $vehicle_groups);
            if (! $this->validator->hasError()) {
                $vehicle_groups = array($vehicle_groups);
            }
        } else {
            if (! empty($vehicle_groups)) {
                foreach ($vehicle_groups as $gid) {
                    $this->validator->validate('record_id', $gid);
                }
            }
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }


        if (! $this->hasError()) {
            $vehicles = $this->vehicle_data->getVehiclesByGroupIds($user_id, $vehicle_groups, $account_id);
            if (! empty($vehicles) AND $vehicles !== false) {
                foreach ($vehicles as $vehicle) {
                    $unit = $vehicle;
                    $unit['event'] = $this->vehicle_data->getLastReportedEvent($vehicle);

                    if (! empty($unit['event'])) {
                        $unit['event']['direction'] = Unit::headingToDirection($unit['event']['heading']);
                    }

                    $data[] = $unit;
                }
            }
            return $data;
        }
        return false;
    }

    /**
     * Get the unit data and it's event info for the unit
     *
     * @param array $unit
     * @param int $unitevent_id
     *
     * @return bool
     */
    public function getVehicleDataByLastEvent($unit_id)
    {

        if ($unit_id>0) {
            $unit_db = $this->vehicle_data->getUnitDb($unit_id);
        }

        if ( ($unit_db) && ($unit_id>0) ) {

            $unitevent = $this->vehicle_data->getVehicleDataByLastEvent($unit_db,$unit_id);
            if ($unitevent !== false AND ! empty($unitevent)) {
                $unitevent = $unitevent;

                $unitevent['SQL'] = $unitevent['SQL'];
                $unitevent['LAT'] = $unitevent['latitude'];
                $unitevent['LONG'] = $unitevent['longitude'];
                $unitevent['formatted_address']  = $this->address_logic->validateAddress($unitevent['streetaddress'], $unitevent['city'], $unitevent['state'], $unitevent['zipcode'], $unitevent['country']);
                $unitevent['display_servertime'] = $unitevent['servertime'];
                $unitevent['display_unittime']   = $unitevent['unittime'];
                $unitevent['direction'] = Unit::headingToDirection($unitevent['heading']);

            }

            return $unitevent;
        }

        $unitevent['SQL'] = $unit_db . ' / ' . $unit_id;
        $unitevent['DB'] = $unit_db;
        $unitevent['ID'] = $unit_id;
        $unitevent['LAT'] = '34.65978';
        $unitevent['LONG'] = '-117.69684';

        return $unitevent;

    }

    /**
     * Get the unit data and it's event info for the unit and specified unitevent_id
     *
     * @param array $unit
     * @param int $unitevent_id
     *
     * @return bool
     */
    public function getVehicleDataEventInfo($unit, $unitevent_id)
    {
        if (! is_array($unit) OR count($unit) == 0) {
            $this->setErrorMessage('Invalid Vehicle');
        }

        $this->validator->validate('record_id', $unitevent_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {

            $unitevent = $this->vehicle_data->getVehicleDataEventInfo($unit, $unitevent_id);
            if ($unitevent !== false AND ! empty($unitevent)) {
                $unitevent = $unitevent;

                $unitevent['LAT'] = $unitevent['latitude'];
                $unitevent['LONG'] = $unitevent['longitude'];
                $unitevent['formatted_address']  = $this->address_logic->validateAddress($unitevent['streetaddress'], $unitevent['city'], $unitevent['state'], $unitevent['zipcode'], $unitevent['country']);
                $unitevent['display_servertime'] = $unitevent['servertime'];
                $unitevent['display_unittime']   = $unitevent['unittime'];
                $unitevent['direction'] = Unit::headingToDirection($unitevent['heading']);

                $unitevent['landmark'] = '';
                if (! empty($unitevent['landmark_id'])) {
                    // get landmark info
                    $eventlandmark = $this->territory_logic->getTerritoryByIds($unitevent['landmark_id'], false);
                    if ($eventlandmark !== false AND count($eventlandmark) == 1) {
                        $unitevent['landmark'] = array_pop($eventlandmark);
                    }
                }

                $unitevent['boundary'] = '';
                if (! empty($unitevent['boundary_id'])) {
                    // get landmark info
                    $unitevent['boundary'] = $unitevent['boundary_id'];
                }
            }

            return $unitevent;
        }

        return false;
    }

    /**
     * Get vehicles list data info according to datatable params
     *
     * @params: array vehicles
     * @params: array params
     *
     * @return array
     */
    public function getVehicleListDataInfo($vehicles, $params, $default_char = '-')
    {
        // create output array of results
        $output = array(
            "sEcho"                 => intval($params['sEcho']),
            "iTotalRecords"         => 0,
            "iTotalDisplayRecords"  => 0,
            "data"                  => array()
        );

        if (! empty($vehicles)) {
            // process and format vehicle data info
            $total_units = $this->processVehicleListDataInfo($vehicles, $default_char, $params);

            // pass total units and process for filtered status params
            $total_units = $this->processStatusFilteredVehicles($total_units, $params);

            // for the formatted unit events, process for datatable return results
            if (! empty($total_units)) {

                // init total results
                $iTotal                         = count($total_units);
                $iFilteredTotal                 = count($total_units);
                $output['iTotalRecords']        = $iTotal;
                $output['iTotalDisplayRecords'] = $iFilteredTotal;
                $aColumns                       = array();        // datatable columns event field/key names
                $searchfields                   = array();

                for ( $i = 0 ; $i < intval( $params['iColumns'] ) ; $i++ ) {
                    $aColumns[] = $params['mDataProp_'.$i];

                    if ($params['bSearchable_'.$i] == "true") {
                        $searchfields[] = $params['mDataProp_'.$i];
                    }
                }

                // if doing a string search in filter search box
                if ( isset($params['string_search']) AND $params['string_search'] != "" ) {
                    $total_units    = $this->filterVehicleDataInfoStringSearch($params['string_search'], $searchfields, $total_units);
                    $iTotal         = count($total_units);
                    $iFilteredTotal = count($total_units);
                }

                // if doing a string search using dataTable's searchbox
                if ( isset($params['sSearch']) AND $params['sSearch'] != "" ) {
                    $total_events   = $this->filterQuickHistorySearch($params['sSearch'], $aColumns, $total_events);
                    $iTotal         = count($total_units);
                    $iFilteredTotal = count($total_units);
                }

                $output['iTotalRecords'] = $iTotal;
                $output['iTotalDisplayRecords'] = $iFilteredTotal;

                $formatted_results = array();
                if (! empty($total_units)) {
                    $formatted_results = $total_units;

                    // if doing a column sorting
                    if ( isset($params['iSortCol_0']) AND $params[ 'bSortable_'.intval($params['iSortCol_0']) ] == "true")
                    {
                        if ($aColumns[ intval($params['iSortCol_0']) ] == 'display_unittime') {
                            $aColumns[ intval($params['iSortCol_0']) ] = 'unittime';
                        }
                        $formatted_results = $this->filterQuickHistorySort($aColumns[ intval($params['iSortCol_0']) ], ($params['sSortDir_0']==='asc' ? 'asc' : 'desc'), $formatted_results);
                    }

                    // if doing paging, find correct page list
                    if ( isset($params['iDisplayStart']) AND $params['iDisplayLength'] != '-1' )
                    {
                        $formatted_results = $this->filterQuickHistoryPaging($params['iDisplayStart'], $params['iDisplayLength'], $formatted_results);
                    }

                    // format duration to time string for display
                    $formatted_results = $this->formatDurationTime($formatted_results);
                }

                $output['data'] = $formatted_results;
            }
        }

        return $output;
    }

    /**
     * Process Command Batch Import
     *
     * @return array
     */
    public function importCommandBatch($account_id, $user_id, $file_path)
    {
        return $this->vehicle_data->importCommandBatch($account_id, $user_id, $file_path);
    }

    /**
     * Process and format vehicles list data info
     *
     * @params: array vehicles
     *
     * @return array
     */
    public function processVehicleListDataInfo($vehicles, $default_char = '-', $params)
    {
        $total_units = array();

//print_rb($vehicles);

        if (! empty($vehicles))
        {
            // storage arrays
            $manufacture_events = $this->getManufacturerEvents();
            $eventnamearray     = $manufacture_events['eventnamearray'];
            $driveevent_keys    = $manufacture_events['driveevent_keys'];
            $stopevent_keys     = $manufacture_events['stopevent_keys'];

            // loop through each vehicle to process
            foreach ($vehicles as $key => $vehicle)
            {
                $unit                       = $vehicle;
                $unit['DT_RowId']           = 'vehicle-tr-'.$unit['unit_id'];       // automatic tr id value for dataTable to set
                $unit['rid']                = (isset($unit['event']['id']) AND $unit['event']['id'] != '') ? $unit['event']['id'] : '';
                $unit['id']                 = (isset($unit['event']['id']) AND $unit['event']['id'] != '') ? $unit['event']['id'] : '';
                $unit['eventstatus']        = $default_char;
                $unit['lastevent']          = $default_char;
                $unit['duration']           = $default_char;
                $unit['mileage']            = $default_char;
                $unit['display_unittime']   = $default_char;
                $unit['formatted_address']  = $default_char;
                $unit['display_servertime'] = $default_char;

                // process for eventnames and stop and drive event ids for unit
                if( array_key_exists($unit['event']['event_id'], $eventnamearray)) {
                    $unit['event']['eventname'] = $eventnamearray[$unit['event']['event_id']];
                }
                else {
                    $unit['event']['eventname'] = $default_char;
                }

                // process/format for additional vehicle informations and fields to be returned
                if (isset($unit['event']['event_id']) AND $unit['event']['event_id'] != '') {
                    if( array_key_exists($unit['event']['event_id'], $driveevent_keys)) {
                        $unit['eventstatus']  = 'Moving';
                    } else {
                        $unit['eventstatus']  = 'Stopped';
                    }

                    $unit['lastevent']          = $unit['event']['eventname'];
                    $unit['formatted_address']  = $this->address_logic->validateAddress($unit['event']['streetaddress'], $unit['event']['city'], $unit['event']['state'], $unit['event']['zipcode'], $unit['event']['country']);

                    $unit['display_servertime'] = Date::utc_to_locale($unit['event']['servertime'], $params['user_timezone'], 'h:i A m/d/Y');
                    $unit['display_unittime']   = Date::utc_to_locale($unit['event']['unittime'], $params['user_timezone'], 'h:i A m/d/Y');
                    $utctime                   = Date::locale_to_utc(date('Y-m-d H:i:s'), SERVER_TIMEZONE, 'Y-m-d H:i:s');  //get correct user utc time (timezone)
                    $stop_duration_time         = Date::time_difference_seconds($utctime, $unit['event']['unittime']);      // calculate stop duration
                    $unit['duration']           = $stop_duration_time;

                    $initialodometer = (isset($unit['initialodometer']) AND ! empty($unit['initialodometer'])) ? $unit['initialodometer'] : 0;

                    $currentodometer = (isset($unit['currentodometer']) AND ! empty($unit['currentodometer'])) ? $unit['currentodometer'] : 0;

                    $unit['mileage']            = $initialodometer + $currentodometer;
                }

                $total_units[] = $unit;
            }
        }

        return $total_units;
    }

    /**
     * Format the vehicle duration time to timespan for display
     *
     * @params: array vehicles, default_char
     *
     * @return array
     */
    public function formatDurationTime($vehicles, $default_char = '-')
    {
        $data = array();
        if (! empty($vehicles)) {
            foreach($vehicles as $key => $vehicle) {
                $row = $vehicle;

                if (isset($vehicle['event']) AND is_array($vehicle['event'])) {
                    if(isset($vehicle['event']['duration']) AND $vehicle['event']['duration'] != '') {
                        $row['duration'] = Date::seconds_to_timespan($vehicle['event']['duration'],true,true,true,false);      // format duration timespan for display
                    } elseif (isset($vehicle['duration']) AND $vehicle['duration'] != '') {
                        $row['duration'] = Date::seconds_to_timespan($vehicle['duration'],true,true,true,false);      // format duration timespan for display
                    } else {
                        $row['duration'] = $default_char;
                    }
                } elseif (isset($vehicle['id']) AND $vehicle['id'] != '') {
                    if(isset($vehicle['duration']) AND $vehicle['duration'] != '') {
                        $row['duration'] = Date::seconds_to_timespan($vehicle['duration'],true,true,true,false);      // format duration timespan for display
                    } else {
                        $row['duration'] = $default_char;
                    }
                }

                $data[] = $row;
            }
        }

        return $data;
    }

    /**
     * Get the vehicle status options
     *
     * @return array | bool
     */
    public function getVehicleStatusOptions($user,$unit_id)
    {
        $results = $this->vehicle_data->ajaxOptions($user,$unit_id,'unitstatus',$post);
        return $results;
    }

    /**
     * Get the vehicle groups info by user id
     *
     * @params: user_id
     *
     * @return array | bool
     */
    public function getVehicleGroupsByUserId($user_id, $unitgroup_ids = array(), $include_units = false, $account_id)
    {
        $this->validator->validate('record_id', $user_id);

        if (! empty($unitgroup_ids)) {
            if (! is_array($unitgroup_ids)) {
                $unitgroup_ids = array($unitgroup_ids);
            }

            foreach($unitgroup_ids as $ugid) {
                $this->validator->validate('record_id', $ugid);
            }
        }

        if ($include_units !== true AND $include_units !== false) {
            $this->setErrorMessage('Invalid Include Units Indicator');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {

            $groups = $this->vehicle_data->getVehicleGroupsByUserId($user_id, $unitgroup_ids, $account_id);

            // if include units is true, create assigned and available units lists
            if ($include_units === true) {
                if (! empty($groups)) {

                    $this->user_logic = new UserLogic;
                    $userdata = $this->user_logic->getUserById($user_id);

                    $available_units = array();
                    $new_groups = array(
                        'groups' => array(),
                        'defaultgroup_id' => 0
                    );

                    $default_group = $this->vehicle_data->getVehicleDefaultGroup($userdata[0]['account_id']);
                    if (! empty($default_group) AND ! empty($default_group['unitgroup_id'])) {
                        $has_default = $this->vehicle_data->getVehicleGroupsByUserId($user_id, array($default_group['unitgroup_id']), $account_id);
                        if (! empty($has_default)) {
                            $available_units = $this->vehicle_data->getVehicleInfoByGroupId($default_group['unitgroup_id']);
                            $new_groups['defaultgroup_id'] = $default_group['unitgroup_id'];
                        }
                    }

                    // iterate through the unit groups and get all vehicles for each group
                    foreach ($groups as $index => $ug) {
                        // reindex the array of groups in order to avoid having to constantly check for default group id
                        if (! isset($new_groups['groups'][$ug['unitgroup_id']])) {
                            $ug['assigned_vehicles'] = $this->vehicle_data->getVehicleInfoByGroupId($ug['unitgroup_id']);
                            $ug['available_vehicles'] = $available_units;
                            $new_groups['groups'][$ug['unitgroup_id']] = $ug;
                        }
                    }

                    // if the default group is the array of groups, set its available units to none (default groups shouldn't have any available units)
                    if (! empty($default_group) AND ! empty($default_group['unitgroup_id']) AND isset($new_groups['groups'][$default_group['unitgroup_id']])) {
                        $new_groups['groups'][$default_group['unitgroup_id']]['available_vehicles'] = array();
                    }

                    $groups = $new_groups;
                }
            }

            return $groups;
        }

        return false;
    }

    /**
     * Get the vehicle groups by account id
     *
     * @params: account_id
     *
     * @return array | bool
     */
    public function getVehicleGroupsByAccountId($account_id)
    {
        $this->validator->validate('record_id', $account_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->vehicle_data->getVehicleGroupsByAccountId($account_id);
        }

        return false;
    }

    /**
     * Get the vehicles by account id
     *
     * @params: account_id
     *
     * @return array | bool
     */
    //public function getVehiclesByAccountId($account_id, $device_status = 0, $usertimezone = '', $unit_status = 0)
    public function getVehiclesByAccountId($account_id, $device_status = 0, $usertimezone = '', $unit_status = 0, $vehicle_group_id)
    {
        $this->validator->validate('record_id', $account_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            //return $this->vehicle_data->getVehiclesByAccountId($account_id, $device_status, $usertimezone, $unit_status);
            return $this->vehicle_data->getVehiclesByAccountId($account_id, $device_status, $usertimezone, $unit_status, $vehicle_group_id);
        }

        return false;
    }

    /**
     * Get the vehicle groups info by ids
     *
     * @params: account_id, group_id
     *
     * @return array | bool
     */
    public function getVehicleGroupsById($account_id, $group_id, $include_units = false)
    {
        $this->validator->validate('record_id', $account_id);

        if (! is_array($group_id)) {
            $group_id = array($group_id);
        }

        if (count($group_id) < 0) {
            $this->setErrorMessage('Invalid Group');
        } else {
            foreach ($group_id as $gid) {
                $this->validator->validate('record_id', $gid);
            }
        }

        if ($include_units !== true AND $include_units !== false) {
            $this->setErrorMessage('Invalid Vehicle Indicator');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            /*
            $this->user_logic = new UserLogic;
            $userdata = $this->user_logic->getUserById($user_id);
            */
            $groups = $this->vehicle_data->getVehicleGroupsById($account_id, $group_id);

            // if include units is true, create assigned and available units lists
            if ($include_units === true) {
                if (! empty($groups)) {
                    //$available_units = $this->vehicle_data->getVehiclesWithNoGroup($account_id);

                    $available_units = $new_groups = array();

                    $default_group = $this->vehicle_data->getVehicleDefaultGroup($account_id);
                    if (! empty($default_group) AND ! empty($default_group['unitgroup_id'])) {
                        $available_units = $this->vehicle_data->getVehicleInfoByGroupId($default_group['unitgroup_id']);
                    }

                    // iterate through the unit groups and get all vehicles for each group
                    foreach ($groups as $index => $ug) {
                        // reindex the array of groups in order to avoid having to constantly check for default group id
                        if (! isset($new_groups[$ug['unitgroup_id']])) {
                            $ug['assigned_vehicles'] = $this->vehicle_data->getVehicleInfoByGroupId($ug['unitgroup_id']);
                            $ug['available_vehicles'] = $available_units;
                            $new_groups[$ug['unitgroup_id']] = $ug;
                        }
                    }

                    // if the default group is the array of groups, set its available units to none (default groups shouldn't have any available units)
                    if (! empty($default_group) AND ! empty($default_group['unitgroup_id']) AND isset($new_groups[$default_group['unitgroup_id']])) {
                        $new_groups[$default_group['unitgroup_id']]['available_vehicles'] = array();
                    }

                    $groups = $new_groups;
                }
            }
            return $groups;
        }

        return false;
    }

    /**
     * Get the vehicle groups info by ids
     *
     * @params: user_id, group_id
     *
     * @return array | bool
     */
    public function searchVehicleByName($user_id, $search_str, $params, $searchfields = array())
    {
        $data           = array();
        $total_units    = array();
        $searchfields   = array_merge(array("unitname"), $searchfields); //hardcoded for now

        $this->validator->validate('record_id', $user_id);

        if ($search_str == '') {    //no search string provided, so searchfields set to blank
            $searchfields = array();
        } else {
            $this->validator->validate('alphanumeric', $search_str);
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {

            //return $this->vehicle_data->searchVehicleByName($user_id, $vehicle_name);
            if (($vehicles = $this->vehicle_data->searchVehicleByName($user_id, $search_str, $searchfields)) !== false) {
                if (! empty($vehicles)) {
                    foreach ($vehicles as $vehicle) {
                        $unit           = $vehicle;
                        $unit['eventdata']  = $this->vehicle_data->getLastReportedEvent($vehicle);

                        if (! empty($unit['eventdata'])) {
                            $unit['eventdata']['direction'] = Unit::headingToDirection($unit['event']['heading']);
                        }

                        $total_units[]  = $unit;
                    }
                }
            }

            // specialized personal paging and indexing
            if ( $params['paging'] == '+' ) {
                $params['vehicle_start_index'] = $params['vehicle_start_index'] + $params['vehicle_listing_length'];
            } elseif ($params['paging'] == '-') {
                $params['vehicle_start_index'] = $params['vehicle_start_index'] - $params['vehicle_listing_length'];
                if ($params['vehicle_start_index'] < 0) {
                    $params['vehicle_start_index'] = 0;
                }
            }

            $data['total_vehicles_count']   = count($total_units);
            $total_key                      = intval(end(array_keys($total_units)));
            $end_index                      = intval($params['vehicle_start_index']) + intval($params['vehicle_listing_length']);
            $data['vehicles']               = array_splice($total_units, $params['vehicle_start_index'], $params['vehicle_listing_length']);
            $data['endpage']                = 0;

            if (intval($end_index) >= intval($total_key)) {
                $data['endpage'] = 1;
            }

            return $data;
        }

        return false;
    }

    /**
     * Get the vehicle info by filtered paramaters
     *
     * @params: group_id, event_id, sort_by
     *
     * @return array
     */
    public function getFilteredVehicles($user_id, $vehicle_groups, $params = array())
    {
        $data           = array();
        $total_units    = array();

        if (strtolower($vehicle_groups) == 'all') {
            $vehicle_groups = array();
        }

        if (! is_array($vehicle_groups)) {
            $vehicle_groups = array($vehicle_groups);
        }

        if (count($vehicle_groups) > 0) {
            foreach ($vehicle_groups as $gid) {
                $this->validator->validate('record_id', $gid);
            }
        }

        $this->validator->validate('record_id', $user_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }
/*
        if (! is_array($params['vehicle_state_status'])) {
            //$this->setErrorMessage('err_param');
        }
*/
        if (! $this->hasError()) {
            // get all vehicles and pull last reported event for the vehicles
            if (($vehicles = $this->vehicle_data->getFilteredVehicles($user_id, $vehicle_groups)) !== false) {
                if (! empty($vehicles)) {
                    foreach ($vehicles as $vehicle) {
                        $unit           = $vehicle;
                        $event          = $this->vehicle_data->getLastReportedEvent($vehicle);

                        if (! empty($event)) {
                            $event['direction'] = Unit::headingToDirection($event['heading']);
                        }

                        $unit['event']  = $event;
                        $unit['id']     = $event['id'];

                        $total_units[]  = $unit;
                    }
                }
            }

            // get count of the quick filters
            $quickfiltercounts = $this->processQuickFilterCount($total_units);

            // pass total units and process for filtered status params
            $total_units = $this->processStatusFilteredVehicles($total_units, $params);

            // specialized personal paging and indexing
            if ( $params['paging'] == '+' ) {
                $params['vehicle_start_index'] = $params['vehicle_start_index'] + $params['vehicle_listing_length'];
            } elseif ($params['paging'] == '-') {
                $params['vehicle_start_index'] = $params['vehicle_start_index'] - $params['vehicle_listing_length'];
                if ($params['vehicle_start_index'] < 0) {
                    $params['vehicle_start_index'] = 0;
                }
            }

            $data['quick_filters']          = $quickfiltercounts;
            $data['total_vehicles_count']   = count($total_units);
            $total_key                      = intval(end(array_keys($total_units)));
            $end_index                      = intval($params['vehicle_start_index']) + intval($params['vehicle_listing_length']);
            $data['vehicles']               = array_splice($total_units, $params['vehicle_start_index'], $params['vehicle_listing_length']);
            $data['endpage']                = 0;

            if (intval($end_index) >= intval($total_key)) {
                $data['endpage'] = 1;
            }

            return $data;
        }

        return false;
    }

    /**
     * Process for a count of the quick filter icons from total array of unit
     *
     * @params: array total_units
     *
     * @return array
     */
    public function processQuickFilterCount($total_units)
    {
        $filter_results['In_a_Landmark']            = 0;
        //$filter_results['Outside_Boundary']         = 0;
        $filter_results['No_Movement_in_7_Days']    = 0;
        $filter_results['Not_Reported_in_7_Days']   = 0;
        $filter_results['Starter_Disabled']         = 0;

        if (! empty($total_units)) {
            foreach ($total_units as $key => $unit) {

            	if($unit['unitstatus_id']==1)
            	{
	                if (! isset($unit['event'])) {
	                    $unit['event'] = array();
	                    if (($eventinfo = $this->vehicle_data->getEventById($unit['unit_id'], $unit['db'], $unit['id'])) !== false) {
	                        $unit['event'] = $eventinfo;
	                    }
	                }

	                if (isset($unit['event']) AND is_array($unit['event']) AND ! empty($unit['event'])) {
	                    if (isset($unit['event']['landmark_id']) AND $unit['event']['landmark_id'] > 0) {
	                        $filter_results['In_a_Landmark'] += 1;
	                    }
	                }
					/*
	                if (isset($unit['event']) AND is_array($unit['event']) AND ! empty($unit['event'])) {
	                    if (isset($unit['event']['country']) AND $unit['event']['country'] != 'USA') {
	                        $filter_results['Outside_Boundary'] += 1;
	                    }
	                }
					*/
	                if (isset($unit['event']) AND is_array($unit['event']) AND ! empty($unit['event'])) {
	                    if(($has_movement = $this->checkForVehicleMovement($unit, 7)) === false) {
	                        $filter_results['No_Movement_in_7_Days'] += 1;
	                    }
	                } else {
	                    // unit had no last event therefore it has not moved
	                    $filter_results['No_Movement_in_7_Days'] += 1;
	                }

	                if (isset($unit['event']) AND is_array($unit['event']) AND ! empty($unit['event'])) {
	                    // process if any report event in last 7 days
	                    if(($recent_reported = $this->checkForVehicleReporting($unit, 7)) === false) {
	                        $filter_results['Not_Reported_in_7_Days'] += 1;
	                    }
	                } else {
	                    // unit had no last event therefore it has not reported in
	                    $filter_results['Not_Reported_in_7_Days'] += 1;
	                }

	                if (isset($unit['event']) AND is_array($unit['event']) AND ! empty($unit['event'])) {
	                    if (isset($unit['event']['event_id']) AND $unit['event']['event_id'] > 0) {
	                        // get event info for this event_id
	                        if(($eventinfo = $this->vehicle_data->getEventInfoById($unit['event']['event_id'])) !== false) {
	                            if (isset($eventinfo['eventname']) AND strtolower($eventinfo['eventname']) == "starter disable") {       // find if starter disable event
	                                $filter_results['Starter_Disabled'] += 1;
	                            }
	                        }
	                    }
	                }
	            }
            }
        }

        return $filter_results;
    }

    /**
     * Process the vehicles for matching filtered paramaters
     *
     * @params: array $total_units
     * @params: array $params
     *
     * @return array
     */
    public function processStatusFilteredVehicles($total_units, $params)
    {
        if (! empty($total_units)) {
            switch($params['vehicle_state_status']) {

                case 'in-a-landmark':
                    if (! empty($total_units)) {
                        $filter_results = array();
                        foreach ($total_units as $key => $unit) {
                            if($unit['unitstatus_id']==1)
                            {
                                if (isset($unit['event']) AND is_array($unit['event'])) {
                                    if (isset($unit['event']['landmark_id']) AND $unit['event']['landmark_id'] > 0) {
                                        $filter_results[] = $unit;
                                    }
                                }
                            }
                        }

                        $total_units = $filter_results;
                    }

                    break;

                case 'outside-boundary':
                    if (! empty($total_units)) {
                        $filter_results = array();
                        foreach ($total_units as $key => $unit) {
                            if($unit['unitstatus_id']==1)
                            {
                                if (isset($unit['event']) AND is_array($unit['event'])) {
                                    if (isset($unit['event']['country']) AND $unit['event']['country'] != 'USA') {
                                        $filter_results[] = $unit;
                                    }
                                }
                            }
                        }

                        $total_units = $filter_results;
                    }

                    break;

                case 'no-movement-in-7-days':
                    if (! empty($total_units)) {
                        $filter_results = array();
                        foreach ($total_units as $key => $unit) {
                            if($unit['unitstatus_id']==1)
                            {
                                // process if any move event in last 7 days
                                //if no event in unitlastevent
                                if (isset($unit['event']) AND is_array($unit['event']) AND ! empty($unit['event'])) {
                                    if(($has_movement = $this->checkForVehicleMovement($unit, 7)) === false) {
                                        $filter_results[] = $unit;
                                    }
                                } else {
                                    // unit had no last event therefore it has not moved
                                    $filter_results[] = $unit;
                                }
                            }
                        }

                        $total_units = $filter_results;
                    }

                    break;

                case 'not-reported-in-7-days':
                    if (! empty($total_units)) {
                        $filter_results = array();
                        foreach ($total_units as $key => $unit) {
                            if($unit['unitstatus_id']==1)
                            {
                                if (isset($unit['event']) AND is_array($unit['event']) AND ! empty($unit['event'])) {
                                    // process if any report event in last 7 days
                                    if(($recent_reported = $this->checkForVehicleReporting($unit, 7)) === false) {
                                        $filter_results[] = $unit;
                                    }
                                } else {
                                    // unit had no last event therefore it has not reported in
                                    $filter_results[] = $unit;
                                }
                            }
                        }


                        $total_units = $filter_results;
                    }

                    break;

                case 'starter-disabled':
                    if (! empty($total_units)) {
                        $filter_results = array();
                        foreach ($total_units as $key => $unit) {
                            if($unit['unitstatus_id']==1)
                            {
                                if (isset($unit['event']) AND is_array($unit['event']) AND ! empty($unit['event'])) {
                                    if (isset($unit['event']['event_id']) AND $unit['event']['event_id'] > 0) {
                                        // get event info for this event_id
                                        if(($eventinfo = $this->vehicle_data->getEventInfoById($unit['event']['event_id'])) !== false) {
                                            if (isset($eventinfo['eventname']) AND strtolower($eventinfo['eventname']) == "starter disable") {       // find if starter disable event
                                                $filter_results[] = $unit;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $total_units = $filter_results;
                    }

                    break;

                default:
                    // All
                    // return all results no status filtering

                    break;
            }
        }
        return $total_units;
    }

    /**
     * Check to see if the vehicle has made any movement for days ago range, true if any movement
     *
     * @params: array $unit
     * @params: int $days_ago
     *
     * @return bool
     */
    public function checkForVehicleMovement($unit, $days_ago, $start_date = '', $end_date = '', $return_lastevent = false)
    {
        // set up from and to date according to $days_ago
        if (isset($start_date) AND ! empty($start_date)) {
            $from_date = $start_date;
        } else {
            $from_date  = date('Y-m-d H:i:s', mktime(0, 0, 0, date("m")  , date("d") - $days_ago, date("Y")));
        }

        if (isset($end_date) AND ! empty($end_date)) {
            $to_date = $end_date;
        } else {
            $to_date    = date("Y-m-d H:i:s");
        }

        // pull any event for unit within date range
        $unit_events = $this->vehicle_data->getVehicleUnitEvents($unit, $from_date, $to_date);

        // for each event, it event_id is in the $driveevent_keys array, the unit did have a move event within the date range
        if (! empty($unit_events)) {
            // storage arrays
            $driveevent_keys = $this->getEventsBySubsetId(1);

            foreach ($unit_events as $key => $event) {
                if (array_key_exists($event['event_id'], $driveevent_keys)) {
                    return true;
                }
            }

            // unit did not report within filter day range
            if ($return_lastevent) {
                // return the last event
                return array_pop($unit_events);
            }
        }

        return false;
    }

    /**
     * Check to see if the vehicle has reported any event for the days ago range, true if any event found
     *
     * @params: array $unit
     * @params: int $days_ago
     *
     * @return bool
     */
    public function checkForVehicleReporting($unit, $days_ago, $filter_date = '', $return_lastevent = false)
    {
        if (! is_array($unit)) {
            $this->setErrorMessage('Invalid Vehicle Info');
        }

        if (! $this->hasError()) {

            // set up from and to date according to $days_ago
            if (! isset($filter_date) OR empty($filter_date)) {
                $filter_date = date('Y-m-d H:i:s', mktime(0, 0, 0, date("m"), date("d") - $days_ago, date("Y")));
            }

            $lastevent = $this->vehicle_data->getLastReportedEvent($unit);
            if ($lastevent !== false AND ! empty($lastevent)) {
                // if there was an event reported
                if (isset($lastevent['unittime']) AND ! empty($lastevent['unittime'])) {
                    if (strtotime($lastevent['unittime']) >= strtotime($filter_date)) {
                        // unit reported within filter day range
                        return true;
                    } else {
                        // unit did not report within filter day range
                        if ($return_lastevent) {
                            // return the last event
                            return $lastevent;
                        }
                    }
                }
            }
            // no event found for date range, return false (not reported in 7 days)
            return false;
        }

        // error
        return false;
    }

	/**
     * Get vehicle data info for the provided account_id
     *
     * @param int $account_id
     *
     * @return array
     */
    public function getVehicleDataInfoByAccountId($account_id)
    {
        $this->validator->validate('record_id', $account_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            $vehicledata = $this->vehicle_data->getVehicleDataInfoByAccountId($account_id);
            return $vehicledata;
        }

        return false;
    }

    /**
     * Get the vehicle info by unit_id
     *
     * unitgroup_columns, unitattribute_columns, customer_columns are all arrays of strings, each containing
     * string values represent the column names from their respected table for which you want to retreive the
     * desired data from.
     *
     * ex: $unitattribute_columns = array('unitattribute_id', 'unit_id', 'vin', 'make');
     *
     *       The unitattribute_columns array should contain column names from the 'unitattribute' table in the
     *       crossbones database. This example will return you the values from the unitattribute_id, unit_id,
     *       vin, and make columns for the unit based on the unit_id.
     *
     * @params int unit_id
     * @params array unitgroup_columns
     * @params array unitattribute_columns
     * @params array customer_columns
     *
     * @return void
     */
    function getVehicleInfo($unit_id, $unitgroup_columns = array(), $unitattribute_columns = array(), $customer_columns = array(), $unitinstallation_columns = array(), $unitodometer_columns = array())
    {
        $this->validator->validate('record_id', $unit_id);

        if (! is_array($unitgroup_columns)) {
            $this->setErrorMessage('Invalid Vehicle Group Info');
        }

        if (! is_array($unitattribute_columns)) {
            $this->setErrorMessage('Invalid Vehicle Info');
        }

        if (! is_array($customer_columns)) {
            $this->setErrorMessage('Invalid Customer Info');
        }

        if (! is_array($unitinstallation_columns)) {
            $this->setErrorMessage('Invalid Installation Info');
        }

        if (! is_array($unitodometer_columns)) {
            $this->setErrorMessage('Invalid Installation Info');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->vehicle_data->getVehicleInfo($unit_id, $unitgroup_columns, $unitattribute_columns, $customer_columns, $unitinstallation_columns, $unitodometer_columns);
        }

        return false;
    }

    /**
     * Support System Reports
     */
    public function systemActivations()
    {
        return $this->vehicle_data->systemActivations();
    }

    /**
     * Support System Reports
     */
    public function systemAirs()
    {
        return $this->vehicle_data->systemAirs();
    }

    /**
     * Support System Reports
     */
    public function systemDevices()
    {
        return $this->vehicle_data->systemDevices();
    }

    /**
     * Support System Reports
     */
    public function systemLogins()
    {
        return $this->vehicle_data->systemLogins();
    }

    /**
     * Support System Reports
     */
    public function systemNons()
    {
        return $this->vehicle_data->systemNons();
    }

    /**
     * Support System Reports
     */
    public function systemUris()
    {
        return $this->vehicle_data->systemUris();
    }

    /**
     * Support System Reports
     */
    public function systemUxs()
    {
        return $this->vehicle_data->systemUxs();
    }

    /**
     * Update the vehicle info by unit_id
     *
     * @params: unit_id, params
     *
     * @return array
     */
    public function updateVehicleInfo($unit_id, $params, $table)
    {
        $this->validator->validate('record_id', $unit_id);

        if (! is_array($params) OR count($params) <= 0) {
            $this->setErrorMessage('Invalid Parameters');
        }

        if (empty($table)) {
            $this->setErrorMessage('Invalid Vehicle Info');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            $ret = false;
            switch ($table) {
                case 'unit':
                    $ret = $this->updateUnitInfo($unit_id, $params);
                    break;
                case 'unitattribute':
                    $ret = $this->updateUnitAttributeByUnitId($unit_id, $params);
                    break;
                case 'unitinstallation':
                    $ret = $this->updateUnitInstallationByUnitId($unit_id, $params);
                    break;
                case 'unitodometer':
                    if (isset($params['unitodometer_id'])) {
                        if ($params['unitodometer_id'] == 0) {  // if unitodometer_id is 0, create a odometer record for the unit first
                            unset($params['unitodometer_id']);
                            $unitodometer_id = $this->createUnitOdometer($params);
                            if ($unitodometer_id !== false) {   // update unit with odometer id
                                $update = $this->vehicle_data->updateUnitInfo($unit_id, array('unitodometer_id' => $unitodometer_id));
                                if ($update !== false) {        // if we were able to update the unit with the unitodometer_id, return the unit odometer id
                                    $ret = $unitodometer_id;
                                } else {                        // else, delete the newly created unitodometer record from the table
                                    $this->vehicle_data->deleteUnitOdometer($unitodometer_id);
                                    $this->setErrorMessage('Failed to update vehicle odometer for unit');
                                }
                            } else {
                                $this->setErrorMessage('Failed to create vehicle odometer for unit');
                            }
                        } else {                                // update unitodometer record
                            $ret = $this->updateUnitOdometer($params['unitodometer_id'], $params);
                        }
                    } else {
                        $this->setErrorMessage('Invalid vehicle odometer id');
                    }
                    break;
                default:
                    break;
            }
            return $ret;
        }

        return false;
    }

    /**
     * Update the unit info by unit_id
     *
     * @params: unit_id, params
     *
     * @return array
     */
    public function updateUnitInfo($unit_id, $params)
    {
        $this->validator->validate('record_id', $unit_id);

        if (! is_array($params) OR count($params) <= 0) {
            $this->setErrorMessage('Invalid Parameters');
        } else {
            if (isset($params['unitstatus_id'])) {
                $this->validator->validate('record_id', $params['unitstatus_id']);
            }

            if (isset($params['unitname'])) {
                $this->validator->validate('unit_name', $params['unitname']);
            }

            if (isset($params['unitgroup_id'])) {
                $this->validator->validate('record_id', $params['unitgroup_id']);
            }
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            if ($this->vehicle_data->updateUnitInfo($unit_id, $params)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Update the unit attribute by unit_id
     *
     * @params: unit_id, params
     *
     * @return array
     */
    public function updateUnitAttributeByUnitId($unit_id, $params)
    {
        $this->validator->validate('record_id', $unit_id);

        if (! is_array($params) OR count($params) <= 0) {
            $this->setErrorMessage('Invalid Parameters');
        } else {
            if (isset($params['vin'])) {
                $this->validator->validate('vin_number', $params['vin']);
            }

            if (isset($params['make'])) {
                $this->validator->validate('vehicle_make', $params['make']);
            }

            if (isset($params['model'])) {
                $this->validator->validate('vehicle_model', $params['model']);
            }

            if (isset($params['year'])) {
                $this->validator->validate('vehicle_year', $params['year']);
            }

            if (isset($params['color'])) {
                $this->validator->validate('vehicle_color', $params['color']);
            }

            if (isset($params['licenseplatenumber'])) {
                $this->validator->validate('license_plate', $params['licenseplatenumber']);
            }

            if (isset($params['loannumber'])) {
                $this->validator->validate('loan_id', $params['loannumber']);
            }

            if (isset($params['stocknumber'])) {
                $this->validator->validate('stock_number', $params['stocknumber']);
            }
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            if ($this->vehicle_data->updateUnitAttributeByUnitId($unit_id, $params)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Update the unit attribute by unit_id
     *
     * @params: unit_id, params
     *
     * @return array
     */
    public function updateUnitInstallationByUnitId($unit_id, $params)
    {
        $this->validator->validate('record_id', $unit_id);

        if (! is_array($params) OR count($params) <= 0) {
            $this->setErrorMessage('Invalid Parameters');
        } else {
            if (isset($params['installer'])) {
                $this->validator->validate('installer_name', $params['installer']);
            }

            if (isset($params['installdate'])) {
                $this->validator->validate('date', $params['installdate']);
                if (! $this->validator->hasError()) {
                    $params['installdate'] = Date::locale_to_utc(date('Y-m-d', strtotime($params['installdate'])), $params['user_timezone']);
                    unset($params['user_timezone']);
                }
            }
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            if ($this->vehicle_data->updateUnitInstallationByUnitId($unit_id, $params)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Update the unit odometer by unitodometer_id
     *
     * @params: unitodometer_id, params
     *
     * @return array
     */
    public function updateUnitOdometer($unitodometer_id, $params)
    {
        $this->validator->validate('record_id', $unitodometer_id);

        if (! is_array($params) OR count($params) <= 0) {
            $this->setErrorMessage('Invalid Parameters');
        } else {
            if (isset($params['initialodometer']) OR isset($params['currentodometer']) OR isset($params['odometerevent_id'])) {
                if (isset($params['initialodometer'])) {
                    $this->validator->validate('odometer', $params['initialodometer']);
                }

                if (isset($params['currentodometer'])) {
                    $this->validator->validate('odometer', $params['currentodometer']);
                }

                if (isset($params['odometerevent_id'])) {
                    $this->validator->validate('record_id', $params['odometerevent_id']);
                }
            } else {
                $this->setErrorMessage('Invalid Parameters');
            }
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            if ($this->vehicle_data->updateUnitOdometer($unitodometer_id, $params)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create unit odometer
     *
     * @param array params
     *
     * @return bool|int
     */
    public function createUnitOdometer($params)
    {
        if (empty($params) OR ! is_array($params)) {
            $this->setErrorMessage('Invalid parameters');
        } else {
            $save = array();

            if (isset($params['initialodometer'])) {
                $this->validator->validate('odometer', $params['initialodometer']);
                if (! $this->validator->hasError()) {
                    $save['initialodometer'] = $params['initialodometer'];
                }
            }

            if (isset($params['currentodometer'])) {
                $this->validator->validate('odometer', $params['currentodometer']);
                if (! $this->validator->hasError()) {
                    $save['currentodometer'] = $params['currentodometer'];
                }
            }

            if (isset($params['odometerevent_id'])) {
                $this->validator->validate('record_id', $params['odometerevent_id']);
                if (! $this->validator->hasError()) {
                    $save['odometerevent_id'] = $params['odometerevent_id'];
                }
            }

            if (empty($save)) {
                $this->setErrorMessage('Invalid parameters');
            }
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->vehicle_data->createUnitOdometer($save);
        }
        return false;
    }

    /**
     * Delete unit odometer
     *
     * @param array params
     *
     * @return bool|int
     */
    public function deleteUnitOdometer($unitodometer_id)
    {
        $this->validator->validate('record_id', $unitodometer_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->vehicle_data->deleteUnitOdometer($unitodometer_id);
        }
        return false;
    }

    /**
     * Get unit odometer
     *
     * @param array params
     *
     * @return bool|int
     */
    public function getUnitOdometer($unitodometer_id)
    {
        $this->validator->validate('record_id', $unitodometer_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->vehicle_data->getUnitOdometer($unitodometer_id);
        }
        return false;
    }

    /**
     * Get the vehicle's last reported event
     *
     * @params: unit_id
     *
     * @return void
     */
    public function getLastReportedEvent($unit_id)
    {
        $this->validator->validate('record_id', $unit_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            $unit_event = array();
            //get unit info first
            $unit_info = $this->getVehicleInfo($unit_id);
            if ($unit_info !== false AND ! empty($unit_info)) {
                $unit_event = $this->vehicle_data->getLastReportedEvent($unit_info);
                if (! empty($unit_event)) {
                    $unit_event['direction'] = Unit::headingToDirection($unit_event['heading']);
                }
            }
            return $unit_event;
        }

        return false;
    }

    /**
     * Get the vehicle's last reported event
     *
     * @params: unit_id
     *
     * @return void
     */
    public function getLastReportedStopMoveEvent($unit_id)
    {
        $this->validator->validate('record_id', $unit_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            $unit_event = array();
            //get unit info first
            $unit_info = $this->getVehicleInfo($unit_id);
            if ($unit_info !== false AND ! empty($unit_info)) {
                $unit_event = $this->vehicle_data->getLastReportedStopMoveEvent($unit_info);
                if (! empty($unit_event)) {
                    $unit_event['direction'] = Unit::headingToDirection($unit_event['heading']);
                }
            }
            return $unit_event;
        }

        return false;
    }

    /**
     * Get a vehicle's event data by unit_id, database, event id
     *
     *    @params: unit_id, database, event_id
     *
     * @return void
     */
    public function getEventById($unit_id, $database, $event_id)
    {
        $this->validator->validate('record_id', $unit_id);

        $this->validator->validate('record_id', $event_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (empty($database)) {
            $this->setErrorMessage('Invalid Vehicle Info');
        }

        if (! $this->hasError()) {
            if (($event = $this->vehicle_data->getEventById($unit_id, $database, $event_id)) !== false) {
                if (! empty($event)) {
                    $event['direction'] = Unit::headingToDirection($event['heading']);
                }
                return $event;
            } else {
                $this->setErrorMessage('Invalid Event');
            }
        }
        return false;
    }

    /**
     * Get a unit's event info for the event_id
     *
     *    @params: event_id
     *
     * @return void
     */
    public function getEventInfoById($id)
    {
        $this->validator->validate('record_id', $id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            if (($event_info = $this->vehicle_data->getEventInfoById($id)) !== false) {
                return $event_info;
            } else {
                $this->setErrorMessage('Invalid Event');
            }
        }
        return false;
    }

    /**
     * Update a unit's group
     *
     *    @params: unit_id, group_id
     *
     * @return bool
     */
    public function updateAssignedVehicleGroup($unit_id, $group_id)
    {
        $this->validator->validate('record_id', $unit_id);

        $this->validator->validate('record_id', $group_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->vehicle_data->updateAssignedVehicleGroup($unit_id, $group_id);
        }

        return false;
    }

    /**
     * Get the vehicle history by unit_id for set date range
     *
     * @params int unit_id
     * @params string $event_db
     * @params string $start_date
     * @params string $end_date
     *
     * @return void
     */
    public function getVehicleHistory($unit_id, $event_db, $start_date, $end_date)
    {
        $this->validator->validate('record_id', $unit_id);

        if (! isset($event_db) OR empty($event_db)) {
            $this->setErrorMessage('Invalid Vehicle Info');
        }

        if (! isset($start_date) OR empty($start_date)) {
            $this->setErrorMessage('Invalid Date Range');
        }

        if (! isset($end_date) OR empty($end_date)) {
            $this->setErrorMessage('Invalid Date Range');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            // get quick history for the vehicle
            //return $this->vehicle_data->getVehicleHistory($unit_id, $event_db, $start_date, $end_date);
            $events = $this->vehicle_data->getVehicleHistory($unit_id, $event_db, $start_date, $end_date);
            if (! empty($events)) {
                foreach ($events as $index => $ev) {
                    $events[$index]['direction'] = Unit::headingToDirection($ev['heading']);
                }
            }
            return $events;
        }

        return false;
    }

    /**
     * Get Vehicle Data Info for Quick History tab
     *
     * @params: user_id, unit_id, params
     *
     * @return array|bool
     */
    public function getVehicleQuickHistory($user_id, $unit_id, $params)
    {
        $this->validator->validate('record_id', $user_id);

        $this->validator->validate('record_id', $unit_id);

        if (! isset($params['start_date']) OR empty($params['start_date'])) {
            $this->setErrorMessage('Invalid Date Range');
        }

        if (! isset($params['end_date']) OR empty($params['end_date'])) {
            $this->setErrorMessage('Invalid Date Range');
        }

        if (! isset($params['event_db']) OR empty($params['event_db'])) {
            $this->setErrorMessage('Invalid Vehicle Info');
        }

        $this->validator->validate('event_type', $params['event_type']);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            // No error, get quick history for the vehicle
            $event_history = $this->vehicle_data->getVehicleQuickHistory($unit_id, $params);
            if ($event_history !== false) {

                if (empty($params['export'])) { // if we're not exporting the data, process it for use with datatable
                    // process for the vehicle quick history events according to datatable params
                    $ret = $this->getVehicleQuickHistoryEvents($event_history, $params);
                } else {                        // else process it for exporting (for CSV or PDF)
                    $ret = $this->getVehicleQuickHistoryEventsExport($event_history, $params);
                }

                return $ret;
            }
        }

        return false;
    }

    /**
     * Get vehicle quick history event results according to datatable params
     *
     * @params: array $event_history
     * @params: array $params
     *
     * @return array
     */
    public function getVehicleQuickHistoryEvents($event_history, $params)
    {
        // output array of results for datatable
        $output = array(
            "sEcho"                 => intval($params['sEcho']),
            "iTotalRecords"         => 0,
            "iTotalDisplayRecords"  => 0,
            "data"                  => array()
        );

        if (! empty($event_history)) {
            $total_events   = array();      // returning result events
            $unit_events    = array();      // normalized unit events
            $aColumns       = array();      // datatable columns event field/key names
            for ( $i = 0 ; $i < intval( $params['iColumns'] ) ; $i++ ) {
                if (isset($params['mDataProp_'.$i]) AND $params['mDataProp_'.$i] != '') {
                    $aColumns[] = $params['mDataProp_'.$i];
                }
            }

            // take event_history and process/format for correct type of unit events
            $unit_events = $this->processQuickHistoryEvents($event_history, $params);

            if ($params['event_type'] == 'frequent') {
                //hardcoded sort by stop counter highest first
                usort($unit_events, Arrayhelper::usort_compare_desc("stop_counter"));
            }

            // for the formatted unit events, process for datatable return results
            if (! empty($unit_events)) {
                // reset array keys
                foreach ($unit_events as $index => $event) {
                    $total_events[] = $event;
                }

                // if doing a string search using dataTable's searchbox
                if ( isset($params['sSearch']) && $params['sSearch'] != "" ) {
                    $total_events = $this->filterQuickHistorySearch($params['sSearch'], $aColumns, $total_events);
                }

                // number of results found
                $iTotal         = count($total_events);
                $iFilteredTotal = count($total_events);
                $output['iTotalRecords'] = $iTotal;
                $output['iTotalDisplayRecords'] = $iFilteredTotal;

                $formatted_results = array();
                if (! empty($total_events)) {
                    foreach($total_events as $key => $event) {        // for each event, format fields
                        $data               = $event;
                        $data['mappoint']   = $key+1;

                        if ($params['event_type'] == 'frequent') {
                            if (! isset($data['stop_counter'])) {
                                $data['stop_counter'] = 0;
                            }
                        }

                        $formatted_results[] = $data;
                    }

                    // if doing a column sorting
                    if ( isset( $params['iSortCol_0'] ) AND $params[ 'bSortable_'.intval($params['iSortCol_0']) ] == "true") {
                        if ($aColumns[ intval($params['iSortCol_0']) ] == 'display_unittime') {
                            $aColumns[ intval($params['iSortCol_0']) ] = 'unittime';
                        }
                        $formatted_results = $this->filterQuickHistorySort($aColumns[ intval($params['iSortCol_0']) ], ($params['sSortDir_0']==='asc' ? 'asc' : 'desc'), $formatted_results);
                    }

                    // if doing paging, find correct page list
                    if ( isset( $params['iDisplayStart'] ) AND $params['iDisplayLength'] != '-1' ) {
                        $formatted_results = $this->filterQuickHistoryPaging($params['iDisplayStart'], $params['iDisplayLength'], $formatted_results);
                    }

                    // format duration to time string for display
                    $formatted_results = $this->formatDurationTime($formatted_results);

                    $output['data'] = $formatted_results;
                }
            }
        }

        return $output;
    }

    /**
     * Get vehicle quick history event results for CSV Exporting
     *
     * @params: array $event_history
     * @params: array $params
     *
     * @return array
     */
    function getVehicleQuickHistoryEventsExport($event_history, $params)
    {
        $results = array();

        if (! empty($event_history)) {
            $total_events   = array();      // returning result events
            $unit_events    = array();      // normalized unit events

            // take event_history and process/format for correct type of unit events
            $unit_events = $this->processQuickHistoryEvents($event_history, $params);

            // for the formatted unit events, process for datatable return results
            if (! empty($unit_events)) {
                // reset array keys
                foreach ($unit_events as $index => $event) {
                    $total_events[] = $event;
                }

                $formatted_results = array();
                if (! empty($total_events)) {
                    foreach($total_events as $key => $event) {        // for each event, format fields
                        $data = $event;
                        $data['mappoint']   = $key+1;

                        // setup mappoint value if need
                        if ($params['event_type'] == 'frequent') {
                            if (! isset($data['stop_counter'])) {
                                $data['stop_counter'] = 0;
                            }
                        }

                        $formatted_results[] = $data;
                    }

                    if ($params['event_type'] == 'frequent') {
                         usort($formatted_results, Arrayhelper::usort_compare_desc('stop_counter'));
                    } else {
                        // quickhistory sort by unittime descending
                        $formatted_results = $this->filterQuickHistorySort('unittime', 'desc', $formatted_results);
                    }

                    // format duration to time string for display
                    $formatted_results = $this->formatDurationTime($formatted_results, '');

                    $results = $formatted_results;
                }
            }
        }

        return $results;
    }

    /**
     * Process for and format the vehicle quick history events
     *
     * @params: array $event_history
     * @params: array $params
     *
     * @return array
     */
    public function processQuickHistoryEvents($event_history, $params)
    {
        $unit_events        = array();      // normalized unit events to return
        $manufacture_events = array();      // get list of manufacture events
        $eventnames         = array();      // list of manufacture event id and names
        $stopevent_keys     = array();      // list of manufacture stop events
        $driveevent_keys    = array();      // list of manufacture drive events
        $current_stop_event = array();      // tracks the current formatted stop event needing duration calculation
        $last_event         = array();      // tracks the last formatted event

        // filter duration
        $filter_duration_time = 0;
        if (isset($params['duration']) AND ! empty($params['duration'])) {
            $filter_duration_time = Date::convertDurationTimeToSeconds($params['duration']);
        }
        /*
        // filter # of stops (used in Frequent Stops Report)
        $filter_stop_counter = 0;
        if (isset($params['filter_stop_counter']) AND ! empty($params['filter_stop_counter'])) {
            $filter_stop_counter = $params['filter_stop_counter'];
        }
        */
        if (! empty($event_history)) {
            // process for manufacture event info
            $manufacture_events = $this->getManufacturerEvents();
            //$manufacture_events['eventnamearray']
            //$manufacture_events['ignition_on_keys']
            //$manufacture_events['ignition_off_keys']
            //$manufacture_events['stopevent_keys']
            //$manufacture_events['driveevent_keys']

            $stopevent_keys     = $manufacture_events['stopevent_keys'];
            $driveevent_keys    = $manufacture_events['driveevent_keys'];
            $eventnames         = $manufacture_events['eventnamearray'];

            switch($params['event_type']) {

                case 'all':
                    //loop through events and get stop events
                    foreach ($event_history as $index => $event) {
                        $unit_events[$event['id']]                          = $event;
                        $unit_events[$event['id']]['eventname']             = $eventnames[$event['event_id']];
                        $unit_events[$event['id']]['duration']              = '';
                        $unit_events[$event['id']]['formatted_address']     = $this->address_logic->validateAddress($event['streetaddress'], $event['city'], $event['state'], $event['zipcode'], $event['country']);
                        $unit_events[$event['id']]['display_servertime']    = Date::utc_to_locale($event['servertime'], $params['user_timezone'], 'h:i A m/d/Y');
                        $unit_events[$event['id']]['display_unittime']      = Date::utc_to_locale($event['unittime'], $params['user_timezone'], 'h:i A m/d/Y');
                        $unit_events[$event['id']]['speed']                 = intval($event['speed']);

                        if ( array_key_exists($event['event_id'], $stopevent_keys)) {
                            $current_stop_event = $event;
                        }

                        if ( isset($current_stop_event['event_id']) AND array_key_exists($event['event_id'], $driveevent_keys) AND $event['id'] != $current_stop_event['id']) {
                            $unit_events[$current_stop_event['id']]['duration'] = Date::time_difference_seconds($event['unittime'], $current_stop_event['unittime']);      // calculate stop duration
                            $current_stop_event = array();      // reset the current stored stop event
                        }

                        $last_event = $event;
                    }

                    // if there was a stop and last event was not a driving event, then the vehicle is still in a stop state
                    if (isset($current_stop_event) AND ! empty($current_stop_event) AND ($current_stop_event['id'] != $last_event['id'])) {
                        $unit_events[$current_stop_event['id']]['duration'] = Date::time_difference_seconds($last_event['unittime'], $current_stop_event['unittime']);    // calculate stop duration
                        $current_stop_event = array();      // reset the current stored stop event
                    } else if (isset($current_stop_event) AND ! empty($current_stop_event) AND ($current_stop_event['id'] == $last_event['id'])) {
                        // last event is currently a stop event, return it as recent stop event
                        $utctime               = Date::locale_to_utc(date('Y-m-d H:i:s'), SERVER_TIMEZONE, 'Y-m-d H:i:s');  //need to get correct user time (timezone)
                        $unit_events[$current_stop_event['id']]['duration'] = Date::time_difference_seconds($utctime, $current_stop_event['unittime']);      // calculate stop duration

                        $current_stop_event = array();      // reset the current stored stop event
                    }

                    break;

                case 'recent':
                    //loop through events and get stop events
                    foreach ($event_history as $index => $event) {
                        // if event is a stop event, track this stop event
                        if ( array_key_exists($event['event_id'], $stopevent_keys)) {
                            $current_stop_event                         = $event;
                            $current_stop_event['eventname']            = $eventnames[$event['event_id']];
                            $current_stop_event['duration']             = '';
                            $current_stop_event['formatted_address']    = $this->address_logic->validateAddress($event['streetaddress'], $event['city'], $event['state'], $event['zipcode'], $event['country']);
                            $current_stop_event['display_servertime']   = Date::utc_to_locale($event['servertime'], $params['user_timezone'], 'h:i A m/d/Y');
                            $current_stop_event['display_unittime']     = Date::utc_to_locale($event['unittime'], $params['user_timezone'], 'h:i A m/d/Y');
                        }

                        // if this event is a drive event and is not the current stop event, calculate stop duration
                        if ( isset($current_stop_event['event_id'])                         AND
                                    array_key_exists($event['event_id'], $driveevent_keys)  AND
                                    $event['id'] != $current_stop_event['id']) {

                            $stop_duration_time = Date::time_difference_seconds($event['unittime'], $current_stop_event['unittime']);      // calculate stop duration
                            if ($stop_duration_time >= $filter_duration_time){                  // if stop duration time pass duration condition
                                $current_stop_event['duration']   = $stop_duration_time;        // keep stop duration
                                $unit_events[]                    = $current_stop_event;        // save stop for return result
                            }

                            $current_stop_event = array();      // reset the current stored stop event
                        }

                        $last_event = $event;
                    }

                    // if there was a stop and last event was not a driving event, then the vehicle is still in a stop state
                    if (isset($current_stop_event) AND ! empty($current_stop_event) AND ($current_stop_event['id'] != $last_event['id'])) {
                        $stop_duration_time = Date::time_difference_seconds($last_event['unittime'], $current_stop_event['unittime']);      // calculate stop duration
                        if ($stop_duration_time >= $filter_duration_time){       // save the current stored stop event if stop time pass duration condition
                            $current_stop_event['duration'] = $stop_duration_time;      // keep stop duration
                            $unit_events[]                  = $current_stop_event;      // save stop for return result
                        }
                        $current_stop_event = array();      // reset the current stored stop event
                    } else if (isset($current_stop_event) AND ! empty($current_stop_event) AND ($current_stop_event['id'] == $last_event['id'])) {
                        // last event is currently a stop event, return it as recent stop event
                        $utctime               = Date::locale_to_utc(date('Y-m-d H:i:s'), SERVER_TIMEZONE, 'Y-m-d H:i:s');  //need to get correct user time (timezone)
                        $stop_duration_time = Date::time_difference_seconds($utctime, $current_stop_event['unittime']);      // calculate stop duration
                        if ($stop_duration_time >= $filter_duration_time){       // save the current stored stop event if stop time pass duration condition
                            $current_stop_event['duration'] = $stop_duration_time;      // keep stop duration
                            $unit_events[]                  = $current_stop_event;      // save stop for return result
                        }
                        $current_stop_event = array();      // reset the current stored stop event
                    }

                    break;

                case 'frequent':
                    $frequent_stop_array = array();

                    //loop through events and get stop events
                    foreach ($event_history as $index => $event) {
                        //$unique_address = $this->address_logic->createUniqueEventAddress($event['streetaddress'], $event['city'], $event['state'], '', $event['country']);
                        //$event['unique_event_address']  = $unique_address;
                        $unique_latlong_value =$this->createUniqueLatLongIndexValue($event);

                        //$unique_latlong = $event['latitude'].'.'.$event['longitude'];
                        $event['unique_event_address']  = $unique_latlong_value;

                        // if event is a stop event, track this stop event
                        if ( array_key_exists($event['event_id'], $stopevent_keys)) {
                            $current_stop_event                         = $event;
                            $current_stop_event['eventname']            = $eventnames[$event['event_id']];
                            $current_stop_event['duration']             = '';
                            $current_stop_event['formatted_address']    = $this->address_logic->validateAddress($event['streetaddress'], $event['city'], $event['state'], $event['zipcode'], $event['country']);
                            $current_stop_event['display_servertime']   = Date::utc_to_locale($event['servertime'], $params['user_timezone'], "h:i A m/d/Y");
                            $current_stop_event['display_unittime']     = Date::utc_to_locale($event['unittime'], $params['user_timezone'], 'h:i A m/d/Y');
                        }

                        // if this event is a drive event and is not the current stop event, calculate stop duration
                        if ( isset($current_stop_event['event_id']) AND array_key_exists($event['event_id'], $driveevent_keys) AND $event['id'] != $current_stop_event['id']) {
                            $stop_duration_time = Date::time_difference_seconds($event['unittime'], $current_stop_event['unittime']);      // calculate stop duration

                            if (! array_key_exists($current_stop_event['unique_event_address'], $frequent_stop_array)) {        // increment the stop counter of this stop
                                $frequent_stop_array[$current_stop_event['unique_event_address']]                           = $current_stop_event;
                                $frequent_stop_array[$current_stop_event['unique_event_address']]['stop_counter']           = 1;
                                $frequent_stop_array[$current_stop_event['unique_event_address']]['total_stop_duration']    = $stop_duration_time;
                            } else {
                                // increment the stop counter for this stop event location in frequent stop array
                                $frequent_stop_array[$current_stop_event['unique_event_address']]['stop_counter']           += 1;
                                $frequent_stop_array[$current_stop_event['unique_event_address']]['total_stop_duration']    += $stop_duration_time;
                                $total_stop_count   = $frequent_stop_array[$current_stop_event['unique_event_address']]['stop_counter'];
                                $total_duration     = $frequent_stop_array[$current_stop_event['unique_event_address']]['total_stop_duration'];

                                //calculate average duration
                                $average_duration_time  = $total_duration/$total_stop_count;
                                $frequent_stop_array[$current_stop_event['unique_event_address']]['duration'] = $average_duration_time;

                                //set territoryname if event has territory
                                if (! empty($current_stop_event['territoryname'])) {
                                    $frequent_stop_array[$current_stop_event['unique_event_address']]['territoryname'] = $current_stop_event['territoryname'];
                                }
                            }

                            $current_stop_event = array();      // reset the current stored stop event
                        }

                        $last_event = $event;
                    }

                    // if there was a stop and last event was not a driving event, then the vehicle is still in a stop state
                    if (isset($current_stop_event) AND ! empty($current_stop_event) AND ($current_stop_event['id'] != $last_event['id'])) {
                        $stop_duration_time = Date::time_difference_seconds($last_event['unittime'], $current_stop_event['unittime']);      // calculate stop duration

                        if (! array_key_exists($current_stop_event['unique_event_address'], $frequent_stop_array)) {        // increment the stop counter of this stop
                            $frequent_stop_array[$current_stop_event['unique_event_address']]                           = $current_stop_event;
                            $frequent_stop_array[$current_stop_event['unique_event_address']]['stop_counter']           = 1;
                            $frequent_stop_array[$current_stop_event['unique_event_address']]['total_stop_duration']    = $stop_duration_time;
                        } else {
                            // increment the stop counter for this stop event location in frequent stop array
                            $frequent_stop_array[$current_stop_event['unique_event_address']]['stop_counter']           += 1;
                            $frequent_stop_array[$current_stop_event['unique_event_address']]['total_stop_duration']    += $stop_duration_time;
                            $total_stop_count   = $frequent_stop_array[$current_stop_event['unique_event_address']]['stop_counter'];
                            $total_duration     = $frequent_stop_array[$current_stop_event['unique_event_address']]['total_stop_duration'];

                            //calculate average duration
                            $average_duration_time  = $total_duration/$total_stop_count;
                            $frequent_stop_array[$current_stop_event['unique_event_address']]['duration'] = $average_duration_time;

                            //set territoryname if event has territory
                            if (! empty($current_stop_event['territoryname'])) {
                                $frequent_stop_array[$current_stop_event['unique_event_address']]['territoryname'] = $current_stop_event['territoryname'];
                            }
                        }

                        $current_stop_event = array();      // reset the current stored stop event
                    } else if (isset($current_stop_event) AND ! empty($current_stop_event) AND ($current_stop_event['id'] == $last_event['id'])) {

                        $utctime               = Date::locale_to_utc(date('Y-m-d H:i:s'), SERVER_TIMEZONE, 'Y-m-d H:i:s');
                        $stop_duration_time = Date::time_difference_seconds($utctime, $current_stop_event['unittime']);      // calculate stop duration

                        if (! array_key_exists($current_stop_event['unique_event_address'], $frequent_stop_array)) {        // increment the stop counter of this stop
                            $frequent_stop_array[$current_stop_event['unique_event_address']]                           = $current_stop_event;
                            $frequent_stop_array[$current_stop_event['unique_event_address']]['stop_counter']           = 1;
                            $frequent_stop_array[$current_stop_event['unique_event_address']]['total_stop_duration']    = $stop_duration_time;
                        } else {
                            // increment the stop counter for this stop event location in frequent stop array
                            $frequent_stop_array[$current_stop_event['unique_event_address']]['stop_counter']           += 1;
                            $frequent_stop_array[$current_stop_event['unique_event_address']]['total_stop_duration']    += $stop_duration_time;
                            $total_stop_count   = $frequent_stop_array[$current_stop_event['unique_event_address']]['stop_counter'];
                            $total_duration     = $frequent_stop_array[$current_stop_event['unique_event_address']]['total_stop_duration'];

                            //calculate average duration
                            $average_duration_time  = $total_duration/$total_stop_count;
                            $frequent_stop_array[$current_stop_event['unique_event_address']]['duration'] = $average_duration_time;

                            //set territoryname if event has territory
                            if (! empty($current_stop_event['territoryname'])) {
                                $frequent_stop_array[$current_stop_event['unique_event_address']]['territoryname'] = $current_stop_event['territoryname'];
                            }
                        }

                        $current_stop_event = array();      // reset the current stored stop event
                    }

                    if (! empty($frequent_stop_array)) {
                        // loop through frequent stops, return stop if it passes filter duration condition
                        foreach( $frequent_stop_array as $key => $location) {
                            //$valid_event = false;

                            //calculate average duration
                            $average_duration_time  = $location['total_stop_duration']/$location['stop_counter'];
                            $location['duration']   = $average_duration_time;

                            if ($location['duration'] >= $filter_duration_time){        // if stop duration time pass duration condition
                                $unit_events[] = $location;        // save stop for return result
                                //$valid_event = true;
                            }
                            /*
                            if ($location['stop_counter'] <= $filter_stop_counter) {     // if stop counter is less than stop counter filter
                                $valid_event = false;               // do not save stop
                            }

                            if ($valid_event === true) {
                                $unit_events[] = $location;
                            }
                            */
                        }
                    }

                    break;
            }
        } else {
            $unit_events = $event_history;
        }

        return $unit_events;
    }

    /**
     *
     *
     * @params: array event
     *
     * @return array
     */
    public function createUniqueLatLongIndexValue($event)
    {
        // get longitude to 3 digit after decimal
        $lat = explode('.', $event['latitude']);
        $length = strlen($lat[1]);
        if ($length > 3) {
            $lat_sub = substr($lat[1], 0, 3);
        } else {
            $lat_sub = $lat[1];
        }

        $latitude = $lat[0].'.'.$lat_sub;

        // get longitude to 3 digit after decimal
        $lon = explode('.', $event['longitude']);
        $length = strlen($lon[1]);
        if ($length > 3) {
            $long_sub = substr($lon[1], 0, 3);
        } else {
            $long_sub = $lon[1];
        }

        $longitude = $lon[0].'.'.$long_sub;


        return $latitude.'.'.$longitude;
    }

    /**
     * Return vehicles having column field values matching search string
     *
     * @params: string search_str
     * @params: array aColumns
     * @params: array vehicles
     *
     * @return array
     */
    public function filterVehicleDataInfoStringSearch($search_str, $aColumns, $vehicles)
    {
        $results = array();
        if ( isset($search_str) AND $search_str != "" AND ! empty($vehicles)) {
            foreach($vehicles as $key => $vehicle) {                                    // loop through vehicle info and find if search string is found
                if (! array_key_exists($vehicle['unit_id'], $results)) {
                    for ($i = 0; $i < count($aColumns); $i++) {                         // loop for each field to search in
                        if (! array_key_exists($vehicle['unit_id'], $results)) {        // only search if unit is not currently in result array
                            $pos = strpos(strtolower($vehicle[$aColumns[$i]]), strtolower($search_str));
                            if ($pos !== false) {

                                $results[$vehicle['unit_id']] = $vehicle;
                                continue;
                            }
                        }
                    }
                } else {
                    continue;
                }
            }
        } else {
            $results = $vehicles;
        }

        return $results;
    }

    /**
     * Return vehicles events having column field values matching search string
     *
     * @params: string $search_str
     * @params: array $aColumns
     * @params: array $unit_events
     *
     * @return array $results
     */
    public function filterQuickHistorySearch($search_str, $aColumns, $unit_events)
    {
        $results = array();
        if ( isset($search_str) && $search_str != "" AND ! empty($unit_events)){
            foreach($unit_events as $key => $event){                                // loop through vehicle events and find if search string is found
                if (! array_key_exists($event['id'], $results)){
                    for ($i = 0; $i < count($aColumns); $i++){                      // loop for each field to search in
                        if (! array_key_exists($event['id'], $results)) {           // only search if unit is not currently in result array
                            if (($pos = strpos(strtolower($aColumns[$i]), strtolower($search_str))) !== false) {
                                $results[$event['id']] = $event;
                                continue;
                            }
                        }
                    }
                } else {
                    continue;
                }
            }
        } else {
            $results = $vehicles;
        }

        return $results;
    }

    /**
     * Return vehicles events having sorted by column field by sort order
     *
     * @params: string $column_name
     * @params: string $sort_order
     * @params: array $unit_events
     *
     * @return array $results
     */
    public function filterQuickHistorySort($column_name, $sort_order, $unit_events)
    {
        $results = $unit_events;
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
     * Return vehicles events within the paging start and length of results
     *
     * @params: int $start
     * @params: int $length
     * @params: array $unit_events
     *
     * @return array $results
     */
    public function filterQuickHistoryPaging($start, $length, $unit_events)
    {
        $results = array();
        $end = $start + $length;

        if(! empty($unit_events)) {
            $j = 0;     // result index counter
            for ( $i = $start; $i < $end; $i++ ) {
                $j = $i+1;
                if (! array_key_exists(($j), $results)){
                    if (isset($unit_events[$i])) {
                        $results[$j] = $unit_events[$i];
                    }
                }
            }
        } else {
            $results = $unit_events;
        }

        return $results;
    }

    /**
     * Update Customer Info
     *
     * @params int unit_id
     * @params params
     *
     * @return bool
     */
    function updateCustomerInfo($unit_id, $params)
    {
        $this->validator->validate('record_id', $unit_id);

        if (! is_array($params) OR count($params) <= 0) {
            $this->setErrorMessage('Invalid Parameters');
        } else {
            if (isset($params['cellphone'])){
                $this->validator->validate('customer_phone_number', $params['cellphone']);
                // if valid phone number, strip out all non numeric characters
                if (! $this->validator->hasError()) {
                    $params['cellphone'] = $this->address_logic->formatPhoneForSaving($params['cellphone']);
                }
            }

            if (isset($params['homephone'])) {
                $this->validator->validate('customer_phone_number', $params['homephone']);
                // if valid phone number, strip out all non numeric characters
                if (! $this->validator->hasError()) {
                    $params['homephone'] = $this->address_logic->formatPhoneForSaving($params['homephone']);
                }
            }

            if (isset($params['firstname'])) {
                $this->validator->validate('customer_first_last_name', $params['firstname']);
            }

            if (isset($params['lastname'])) {
                $this->validator->validate('customer_first_last_name', $params['lastname']);
            }

            if (isset($params['streetaddress'])) {
                $this->validator->validate('customer_address_full', $params['streetaddress']);
            }

            if (isset($params['city'])) {
                $this->validator->validate('customer_address_city', $params['city']);
            }

            if (isset($params['state'])) {
                $this->validator->validate('address_state', $params['state']);
            }

            if (isset($params['zipcode'])) {
                $this->validator->validate('customer_address_zip', $params['zipcode']);
            }

            if (isset($params['email'])) {
                $this->validator->validate('customer_email', $params['email']);
            }
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
	        if ($this->vehicle_data->updateCustomerInfo($unit_id, $params) !== false) {
    	        return true;
	        }
        }

        return false;
    }

    /**
     * Turns reminder on/off for the unit
     *
     * @return array|bool
     */
    function getAllUnitStatus()
    {
        if (($status = $this->vehicle_data->getAllUnitStatus()) !== false) {
            return $status;
        }

        return false;
    }

    public function getEventsBySubsetId($eventsubset_id)
    {
        $this->validator->validate('record_id', $eventsubset_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            $ret = array();
            $events = $this->vehicle_data->getEventsBySubsetId($eventsubset_id);
            if (! empty($events)) {
                foreach($events as $event) {
                    $ret[$event['event_id']] = $event['eventname'];
                }
            }
            return $ret;
        }

        return false;
    }

    /**
     * Get all manufacturer events
     *
     * @return array|bool
     */
    public function getManufacturerEvents()
    {
        $results['stopevent_keys']          = array();
        $results['driveevent_keys']         = array();
        $results['starter_disable_keys']    = array();
        $results['eventnamearray']          = array();
        $results['ignition_on_keys']        = array();
        $results['ignition_off_keys']       = array();

        $manufacture_events                 = array();
        $manufacture_events                 = $this->vehicle_data->getManufacturerEvents();
        $results['ignition_on_keys']        = $this->getEventsBySubsetId(4);
        $results['ignition_off_keys']       = $this->getEventsBySubsetId(5);
        $results['stopevent_keys']          = $this->getEventsBySubsetId(3);
        $results['driveevent_keys']         = $this->getEventsBySubsetId(1);

        foreach ( $manufacture_events as $key => $manufacture_event ) {
            if (strtolower($manufacture_event['eventname']) == "starter disable") {                     // find if unit is disabled
                $results['starter_disable_keys'][$manufacture_event['event_id']] = $manufacture_event['eventname'];
            }

            $results['eventnamearray'][$manufacture_event['event_id']] = $manufacture_event['eventname'];
        }

        return $results;
    }

    /**
     * Generate and email a CSV file of vehicle's Quick History
     *
     * @params int      user_id
     * @params int      unit_id
     * @params array    emails
     * $params array    params
     *
     * @return bool | array
     */
     public function sendEmailVehicleQuickHistory($user_id, $unit_id, $emails, $params)
     {
        $this->validator->validate('record_id', $user_id);

        $this->validator->validate('record_id', $unit_id);

        $this->validator->validate('event_type', $params['event_type']);

        if (! isset($params['start_date']) OR empty($params['start_date'])) {
            $this->setErrorMessage('Invalid Date Range');
        }

        if (! isset($params['end_date']) OR empty($params['end_date'])) {
            $this->setErrorMessage('Invalid Date Range');
        }

        if (! isset($params['event_db']) OR empty($params['event_db'])) {
            $this->setErrorMessage('Invalid Vehicle Info');
        }

        if (empty($emails)) {
            $this->setErrorMessage('Invalid Email');
        } else {
            if (! is_array($emails)) {
                $emails = array($emails);
            }

            $valid_emails = $invalid_emails = array();

            // iterate through and check for valid emails
            foreach($emails as $email) {
                if (\Swift_Validate::email($email)) {
                    $valid_emails[] = $email;
                } else {
                    $invalid_emails[] = $email;
                }
            }

            // if there are no valid emails, set error message
            if (empty($valid_emails)) {
                $this->setErrorMessage('No Valid Emails');
            }
        }

        if (! defined('EMAIL_HOST') OR
            ! defined('EMAIL_PORT') OR
            ! defined('EMAIL_USERNAME') OR
            ! defined('EMAIL_PASSWORD')) {
            $this->setErrorMessage('Invalid Email Configuration');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            $params['export'] = true; // indicator to process data for exporting and not for use with datatable

            if (($data = $this->getVehicleQuickHistory($user_id, $unit_id, $params)) !== false) {
                $event_type = $params['event_type'];
                switch ($event_type) {
                    case 'all':
                        $event_type .= '_events';
                        $fields = array('display_unittime' => 'Date & Time', 'eventname' => 'Event', 'formatted_address' => 'Location', 'speed' => 'Speed', 'duration' => 'Duration');
                        break;
                    case 'recent':
                        $event_type .= '_stops';
                        $fields = array('display_unittime' => 'Date & Time', 'eventname' => 'Event', 'formatted_address' => 'Location', 'duration' => 'Duration');
                        break;
                    case 'frequent':
                        $event_type .= '_stops';
                        $fields = array('stop_counter' => 'Number of Stops', 'formatted_address' => 'Location', 'duration' => 'Duration');
                        break;
                }

                $filepath = ROOTPATH . 'temp_files/';
                $filename = date("Y-m-d") . "_" . preg_replace('/[^A-Za-z0-9]/','_',trim($params['account_name'])) . '_' . preg_replace('/[^A-Za-z0-9]/','_',trim($params['unit_name'])) . '_' . $event_type . ".csv";
                $fullpath = $filepath . $filename;

                $csv_builder = new CSVBuilder();
                $csv_builder->setSeparator(',');
                $csv_builder->setClosure('"');
                $csv_builder->setFields($fields);
                $export_data = $csv_builder->format($data)->getFormattedRows();

                if (! empty($export_data)) {
                    $fstream = fopen($fullpath, 'w+');
                    if ($fstream !== false) {
                        if (fwrite($fstream, $export_data)) {
                            if (file_exists($fullpath)) {

                                $failed_recipients = array();

                                // Create the mail transport configuration
                                $transport = \Swift_SmtpTransport::newInstance(EMAIL_HOST, EMAIL_PORT, EMAIL_SECURITY);
                                $transport->setUsername(EMAIL_USERNAME);
                                $transport->setPassword(EMAIL_PASSWORD);

                                // Create the message
                                $message = \Swift_Message::newInstance();
                                $message->setSubject('Vehicle Quick History for ' . $params['unit_name']);
                                $message->setBody('<html><body>You can open these attachments at anytime.<br>Some attachments may take a while to load because of the size.<br></body></html>', 'text/html');
                                //$message->setFrom('');  // NTD: determine if dealers will have email domains or not
                                // $message->setFrom(array('quickhistory@'.EMAIL_FROM_DOMAIN => EMAIL_FROM_DOMAIN)); // NTD: determine if dealers will have email domains or not
                                $message->setFrom(array('quickhistory@'.$_SERVER['SERVER_NAME'] => $_SERVER['SERVER_NAME']));

                                $message->setTo($valid_emails);

                                // Create Attachment and add it to the message
                                $message->attach(\Swift_Attachment::fromPath($fullpath));

                                // Send the email
                                $mailer = \Swift_Mailer::newInstance($transport);
                                $mailer->send($message, $failed_recipients);

                                $ret = array('failed_recipients' => $failed_recipients, 'invalid_emails' => $invalid_emails);

                                // remove file after sending emails
                                unlink($fullpath);
                                return $ret;
                            }
                        }
                    }
                }
            }
        }

        return false;
     }

    /**
     * Add vehicle group to user
     *
     * @param vehiclegroup_id
     * @param user_id
     *
     * @return bool
     */
    public function addVehicleGroupToUser($vehiclegroup_id, $user_id)
    {
        $this->validator->validate('record_id', $vehiclegroup_id);

        $this->validator->validate('record_id', $user_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->vehicle_data->addVehicleGroupToUser($vehiclegroup_id, $user_id);
        }
        return false;
    }

    /**
     * Add vehicle group to account
     *
     * @param vehiclegroup_id
     * @param account_id
     *
     * @return bool
     */
    public function addVehicleGroup($account_id, $vehiclegroupname)
    {
        $this->validator->validate('group_name', $vehiclegroupname);
        // if vehicle group name is valid, check for name duplication
        if (! $this->validator->hasError()) {
            $duplicate = $this->vehicle_data->getVehicleGroupByName($vehiclegroupname, $account_id, 1);
            if (! empty($duplicate)) {
                $this->setErrorMessage('Duplicated Vehicle Group Name');
            }
        }

        $this->validator->validate('record_id', $account_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            $params = array(
                'account_id'    => $account_id,
                'unitgroupname' => $vehiclegroupname,
                'active'        => 1
            );

            return $this->vehicle_data->addVehicleGroup($params);
        }
        return false;
    }

    /**
     * Remove vehicle group from user
     *
     * @param vehiclegroup_id
     * @param user_id
     *
     * @return bool
     */
    public function removeVehicleGroupFromUser($vehiclegroup_id, $user_id)
    {
        $this->validator->validate('record_id', $vehiclegroup_id);

        $this->validator->validate('record_id', $user_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->vehicle_data->removeVehicleGroupFromUser($vehiclegroup_id, $user_id);
        }
        return false;
    }

    /**
     * Get the filtered contact groups by provided params
     *
     * @params: int $account_id
     * @params: array $params
     *
     * @return array
     */
    public function getFilteredVehicleGroupList($user_id, $params)
    {
        $total_vehiclegroup = array();
        $vehiclegroups['iTotalRecords']          = 0;
        $vehiclegroups['iTotalDisplayRecords']   = 0;
        $vehiclegroups['data']                   = array();

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('Invalid Parameters');
        }

        $this->validator->validate('record_id', $user_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {

            $searchfields = array();
            $string_search = '';

            if (! empty($params['string_search'])) {
                $searchfields = array('unitgroupname');
                $string_search = $params['string_search'];
            }

            $result = $this->vehicle_data->getFilteredVehicleGroupStringSearch($user_id, $string_search, $searchfields);

            if ($result !== false) {
                $total_vehiclegroups = $result;
            }

            //process for datatable return results
            if (! empty($total_vehiclegroups)) {

                // init total results
                $iTotal                                 = count($total_vehiclegroups);
                $iFilteredTotal                         = count($total_vehiclegroups);
                $vehiclegroups['iTotalRecords']         = $iTotal;
                $vehiclegroups['iTotalDisplayRecords']  = $iFilteredTotal;
                $aColumns                               = array();        // datatable columns event field/key names

                for ( $i = 0 ; $i < intval( $params['iColumns'] ) ; $i++ ) {
                    $aColumns[] = $params['mDataProp_'.$i];
                }

                $vehiclegroups['iTotalRecords'] = $iTotal;
                $vehiclegroups['iTotalDisplayRecords'] = $iFilteredTotal;

                $formatted_results = array();
                if (! empty($total_vehiclegroups)) {
                    foreach ($total_vehiclegroups as $vehiclegroup) {
                        $row = $vehiclegroup;
                        $row['DT_RowId'] = 'vehiclegroup-tr-'.$row['unitgroup_id'];       // automatic tr id value for dataTable to set
                        $row['table-actions'] = '<a href="#" class="edit-user">User Access</a>';
                        $formatted_results[] = $row;
                    }

                    // if doing a column sorting
                    if ( isset( $params['iSortCol_0'] ) AND $params[ 'bSortable_'.intval($params['iSortCol_0']) ] == "true")
                    {
                        $formatted_results = $this->filterQuickHistorySort($aColumns[ intval($params['iSortCol_0']) ], ($params['sSortDir_0']==='asc' ? 'asc' : 'desc'), $formatted_results);
                    }

                    // if doing paging, find correct page list
                    if ( isset($params['iDisplayStart']) AND $params['iDisplayLength'] != '-1' )
                    {
                        $formatted_results = array_splice($formatted_results, $params['iDisplayStart'], $params['iDisplayLength']);
                    }
                }

                $vehiclegroups['data'] = $formatted_results;
            }
        }

        return $vehiclegroups;
    }


    /**
     * Update vehiclegroup info by unitgroup_id
     *
     * @params: unit_id, params
     *
     * @return array
     */
    public function updateVehicleGroupIds($account_id, $group_id, $devices)
    {
        $deviceOrs = NULL;
        $result = false;
        $devicesArray = explode(',',$devices);
        foreach ($devicesArray as $key => $value) {
            if(isset($deviceOrs)){
                $deviceOrs = "{$deviceOrs} OR ";
            } else {
                $deviceOrs = "(";
            }
            $deviceOrs = "{$deviceOrs} unit_id = '{$value}'";
        }
        if(isset($deviceOrs)){
            $deviceOrs = "{$deviceOrs} ) AND account_id = '{$account_id}'";
            // if($this->vehicle_data->updateVehicleGroupIds($user_id, $group_id, $deviceOrs)){
            //     $result = true;
            // }
            return $this->vehicle_data->updateVehicleGroupIds($user_id, $group_id, $deviceOrs);
        }
        return $result;
    }

    /**
     * Update vehiclegroup info by unitgroup_id
     *
     * @params: unit_id, params
     *
     * @return array
     */
    public function updateVehicleGroupInfo($unitgroup_id, $account_id, $params)
    {
        $this->validator->validate('record_id', $unitgroup_id);

        $this->validator->validate('record_id', $account_id);

        $default_group = $this->vehicle_data->getVehicleDefaultGroup($account_id);
        if ($default_group !== false AND ! empty($default_group)) {
            $default_group_id = $default_group['unitgroup_id'];

            // Can not modify the Default group
            if ($default_group_id == $unitgroup_id) {
                $this->setErrorMessage('Can Not Modify Default Group');
            }
        } else {
            $this->setErrorMessage('Can Not Delete. Account has no vehicle Default Group');
        }

        if (! is_array($params) OR count($params) <= 0) {
            $this->setErrorMessage('Invalid Parameters');
        } else {
            if (isset($params['unitgroupname'])) {
                $this->validator->validate('group_name', $params['unitgroupname']);
                // if group name has valid characters, check for group name duplication
                if (! $this->validator->hasError()) {
                    $duplicate = $this->vehicle_data->getVehicleGroupByName($params['unitgroupname'], $account_id, 1);
                    if (! empty($duplicate)) {
                        $this->setErrorMessage('Duplicate Name');
                    }
                }
            }

            if (isset($params['active']) AND $params['active'] !== 1 AND $params['active'] !== 0) {
                $this->setErrorMessage('Invalid Active Value');
            }
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            if ($this->vehicle_data->updateVehicleGroupInfo($unitgroup_id, $params) !== false) {
                // if deleting vehicle group, remove all vehicles from this group
                if (isset($params['active']) AND $params['active'] === 0) {
                    $this->vehicle_data->removeAllVehiclesFromGroup($unitgroup_id, $default_group_id);
                }
                return true;
            }
        }

        return false;
    }

    /**
     * Get the vehicle info by filtered paramaters
     *
     * @params: account_id, params
     *
     * @return array
     */
    public function getFilteredAvailableVehicles($account_id, $params = array())
    {
        $this->validator->validate('record_id', $account_id);

        if (empty($params)) {
            $this->setErrorMessage('Empty search parameter');
        } else {
            if (isset($params['search_string']) AND $params['search_string'] !== '') {
                $this->validator->validate('alphanumeric', $params['search_string']);
            }
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {

            $search_fields = $data = array();
            $search_fields[] = 'unitname';

            // get all available vehicles by account id and search string and/or unitgroup_id
            $vehicles = $this->vehicle_data->getFilteredAvailableVehicles($account_id, $params['unitgroup_id'], $params['search_string'], $search_fields);

            if ($vehicles !== false) {
                $data = $vehicles;
            }

            return $data;
        }

        return false;
    }

    /**
     * Get users by vehicle group id (optional - include all other users)
     *
     * @params: group_id,
     *
     * @return bool|array
     */
    public function getUsersByVehicleGroupId($account_id, $group_id, $include_all = false)
    {
        $this->validator->validate('record_id', $account_id);

        $this->validator->validate('record_id', $group_id);

        if ($include_all !== true AND $include_all !== false) {
            $this->setErrorMessage('Invalid Indicator');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            $result = array();

            // get the assigned users
            $assigned_users = $this->vehicle_data->getUsersByVehicleGroupId($account_id, $group_id);

            if ($assigned_users === false) {
                $assigned_users = array();
            }

            if ($include_all) {
                $user_ids = array();

                if (! empty($assigned_users)) {
                    // iterate through and get their user ids
                    foreach ($assigned_users as $index => $user) {
                        $user_ids[] = $user['user_id'];
                    }
                }
                $this->user_logic = new UserLogic;
                $available_users = $this->user_logic->getUserWhereNotIn($account_id, array('user_id' => $user_ids));

                if ($available_users === false) {
                    $available_users = array();
                }
/*
                $result = array(
                    'assigned_users' => $assigned_users,
                    'available_users' => $available_users
                );
*/
                $result = array_merge($assigned_users, $available_users);
            } else {
                $result = $assigned_users;
            }

            return $result;
        }

        return false;
    }


    /**
     * Get the filtered contacts by provided params
     *
     * @params: int $account_id
     * @params: array $params
     *
     * @return array
     */
    public function getFilteredDeviceList($account_id, $params)
    {
        $total_units = array();
        $units['iTotalRecords']          = 0;
        $units['iTotalDisplayRecords']   = 0;
        $units['data']                   = array();

        $this->validator->validate('record_id', $account_id);

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('Invalid Parameters');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {

            switch ($params['filter_type']) {

                case 'string_search':

                    $searchfields = array('unitname');
                    $result = $this->vehicle_data->getFilteredDeviceStringSearch($account_id, $params, $searchfields);
                    if ($result !== false) {
                        $total_units = $result;
                    }

                break;

                case 'group_filter':

                    if (isset($params['vehicle_group_id']) AND strtolower($params['vehicle_group_id']) == 'all') {
                        $params['unitgroup_id'] = array();
                    } else {
                        $params['unitgroup_id'] = array($params['vehicle_group_id']);
                    }

                    if (isset($params['unitstatus_id']) AND strtolower($params['unitstatus_id']) == 'all') {
                        $params['unitstatus_id'] = array();
                    } else {
                        $params['unitstatus_id'] = array($params['unitstatus_id']);
                    }

                    $result = $this->vehicle_data->getFilteredDeviceList($account_id, $params);
                    if ($result !== false) {
                        $total_units = $result;
                    }

                break;

                default:

                break;
            }

            // for the formatted unit events, process for datatable return results
            if (! empty($total_units)) {

                // init total results
                $iTotal                          = count($total_units);
                $iFilteredTotal                  = count($total_units);
                $units['iTotalRecords']          = $iTotal;
                $units['iTotalDisplayRecords']   = $iFilteredTotal;
                $aColumns                        = array();        // datatable columns event field/key names

                for ( $i = 0 ; $i < intval( $params['iColumns'] ) ; $i++ ) {
                    $aColumns[] = $params['mDataProp_'.$i];
                }

                // if doing a string search in filter search box
                if ( isset($params['string_search']) AND $params['string_search'] != "" ) {
                    $total_units = $this->filterDeviceStringSearch($params['string_search'], $aColumns, $total_units);
                    $iTotal         = count($total_units);
                    $iFilteredTotal = count($total_units);
                }

                $units['iTotalRecords'] = $iTotal;
                $units['iTotalDisplayRecords'] = $iFilteredTotal;

                $formatted_results = array();
                if (! empty($total_units)) {
                    foreach ($total_units as $unit) {
                        $row = $unit;

                        $row['DT_RowId'] = 'device-tr-'.$row['unit_id'];       // automatic tr id value for dataTable to set

                        if ($row['name'] == '' OR is_null($row['unitname'])){
                            $row['name'] = $params['default_value'];
                        } else {
                            $row['name'] = $row['unitname'];
                        }

                        if (isset($row['unitgroupname']) AND empty($row['unitgroupname'])){
                            $row['unitgroupname'] = $params['default_value'];
                        }

                        $row['formatted_purchasedate']      = Date::utc_to_locale($row['purchasedate'], $params['user_timezone'], 'm/d/Y');
                        $row['formatted_expirationdate']    = Date::utc_to_locale($row['renewaldate'].'+1 day', $params['user_timezone'], 'm/d/Y');
                        $row['formatted_installdate']       = Date::utc_to_locale($row['installdate'], $params['user_timezone'], 'm/d/Y');

                        $formatted_results[] = $row;
                    }

                    // if doing a column sorting
                    if ( isset( $params['iSortCol_0'] ) AND $params[ 'bSortable_'.intval($params['iSortCol_0']) ] == "true") {
                        $formatted_results = $this->filterDeviceSort($aColumns[ intval($params['iSortCol_0']) ], ($params['sSortDir_0']==='asc' ? 'asc' : 'desc'), $formatted_results);
                    }

                    // if doing paging, find correct page list
                    if ( isset($params['iDisplayStart']) AND $params['iDisplayLength'] != '-1' ) {
                        $formatted_results = array_splice($formatted_results, $params['iDisplayStart'], $params['iDisplayLength']);
                    }
                }

                $units['data'] = $formatted_results;
            }
        }

        return $units;
    }


    /**
     * Return vehicles having column field values matching search string
     *
     * @params: string search_str
     * @params: array aColumns
     * @params: array vehicles
     *
     * @return array
     */
    public function filterDeviceStringSearch($search_str, $aColumns, $vehicles)
    {
        $results = array();
        if ( isset($search_str) AND $search_str != "" AND ! empty($vehicles)) {
            foreach($vehicles as $key => $vehicle) {                                    // loop through vehicle info and find if search string is found
                if (! array_key_exists($vehicle['unit_id'], $results)) {
                    for ($i = 0; $i < count($aColumns); $i++) {                         // loop for each field to search in
                        if (! array_key_exists($vehicle['unit_id'], $results)) {        // only search if unit is not currently in result array
                            if (($pos = strpos(strtolower($vehicle[$aColumns[$i]]), strtolower($search_str))) !== false) {
                                $results[$vehicle['unit_id']] = $vehicle;
                                continue;
                            }
                        }
                    }
                } else {
                    continue;
                }
            }
        } else {
            $results = $vehicles;
        }

        return $results;
    }

    /**
     * Return contacts having sorted by column field by sort order
     *
     * @params: string $column_name
     * @params: string $sort_order
     * @params: array contact
     *
     * @return array $results
     */
    public function filterDeviceSort($column_name, $sort_order, $users)
    {
        $results = $users;
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
     * Get vehicles by account id
     *
     * @param int   account_id
     *
     * @return bool
     */
    public function getDeviceTransferDataByAccountId($account_id)
    {
        $device = array();

        $this->validator->validate('record_id', $account_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {

            // get account vehicle groups
            $vehicle_groups = $this->getVehicleGroupsByAccountId($account_id);
            if ($vehicle_groups !== false AND ! empty($vehicle_groups)) {
                $device['vehicle_groups'] = $vehicle_groups;
            }

            // get account vehicles
            $vehicles = $this->getVehiclesByAccountId($account_id);
            if ($vehicles !== false AND ! empty($vehicles)) {
                $device['vehicles'] = $vehicles;
            }
        }

        return $device;
    }

    /**
     * Get all unitstatus
     *
     * @return bool
     */
    public function getDeviceStatus()
    {
        $devicestatus = array();

        // get all unitstatus
        $status = $this->vehicle_data->getDeviceStatus();
        if ($status !== false AND ! empty($status)) {
            $devicestatus = $status;
        }

        return $devicestatus;
    }

    /**
     * Format vehicle recent stop events
     *
     * @params: array $unit_events
     * @params: string $sortfield (default unittime)
     * @params: string $sorting_order (default desc)
     * @params: int $return_amount (default 10)
     *
     * @return array
     */
    public function formatVehicleRecentStopEvents($unit_events, $sortfield = 'unittime', $sorting_order = 'desc', $return_amount = 10)
    {
        $formatted_results   = array();      // returning result events
        if (! empty($unit_events)) {
            foreach($unit_events as $key => $event) {
                $formatted_results[] = $event;
            }

            // sort by unittime desc
            $formatted_results = $this->filterQuickHistorySort($sortfield, $sorting_order, $formatted_results);

            // get top 10 results
            $formatted_results = $this->filterQuickHistoryPaging(0, $return_amount, $formatted_results);

            // format duration to time string for display
            $formatted_results = $this->formatDurationTime($formatted_results);
        }

        return $formatted_results;
    }

    /**
     * Pull all events for a unit from the specified event_rid
     *
     * @return array|bool
     */
    public function getVehicleUnitEventsAfterId($unit, $event_rid, $limit = null)
	{
        if (! is_array($unit) OR empty($unit)) {
           $this->setErrorMessage('Invalid Vehicle Info');
        }

        //$this->validator->validate('record_id', $event_rid);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {

            // get events for unit
            $unit_event = $this->vehicle_data->getVehicleUnitEventsAfterId($unit, $event_rid, $limit);
            if ($unit_event !== false AND ! empty($unit_event)) {
                foreach ($unit_event as $index => $ue) {
                    $unit_event[$index]['direction'] = Unit::headingToDirection($ue['heading']);
                }
                return $unit_event;
            }

            return array();
        }

        return false;
	}

    /**
     * Pull all events for a unit from the specified event_rid
     *
     * @return array|bool
     */
    public function getVehicleUnitEventsFromId($unit, $event_rid, $limit = null)
	{
        if (! is_array($unit) OR empty($unit)) {
           $this->setErrorMessage('Invalid Vehicle Info');
        }

        //$this->validator->validate('record_id', $event_rid);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {

            // get events for unit
            $unit_event = $this->vehicle_data->getVehicleUnitEventsFromId($unit, $event_rid, $limit);
            if ($unit_event !== false AND ! empty($unit_event)) {
                foreach ($unit_event as $index => $ue) {
                    $unit_event[$index]['direction'] = Unit::headingToDirection($ue['heading']);
                }
                return $unit_event;
            }

            return array();
        }

        return false;
	}

    /**
     * Get the vehicle's last 10 reported power off events
     *
     * @params: unit
     *
     * @return void
     */
    public function getLastTenVehicleOffEvents($unit)
    {
        if (! is_array($unit) OR empty($unit)) {
           $this->setErrorMessage('Invalid Vehicle Info');
        }

        if (! $this->hasError()) {

            // ignition off events (event_id 2 and 13)
            $unit_event = $this->vehicle_data->getLastTenVehicleOffEvents($unit, $ignition_off = array(2,5,13));
            if ($unit_event !== false AND ! empty($unit_event)) {
                foreach ($unit_event as $index => $ue) {
                    $unit_event[$index]['direction'] = Unit::headingToDirection($ue['heading']);
                }
                return $unit_event;
            }

            return array();
        }

        return false;
    }

    /**
     * Get last drive event for vehicle, return last drive event if it is greater than filter_date
     *
     * @params: array $unit
     * @params: int $days_ago
     *
     * @return bool
     */
    public function getVehicleLastDriveEvent($unit, $days_ago, $filter_date = '', $return_lastdriveevent = false)
    {
        if (! is_array($unit)) {
            $this->setErrorMessage('Invalid Vehicle Info');
        }

        if (! $this->hasError()) {
            // set up from and to date according to $days_ago
            if (! isset($filter_date) OR empty($filter_date)) {
                $filter_date = Date::locale_to_utc(date('Y-m-d H:i:s', mktime(0, 0, 0, date("m"), date("d") - $days_ago, date("Y"))));
            }

            $drive_events = array();
            $driveevent_keys = $this->getEventsBySubsetId(1);
            foreach($driveevent_keys as $event_id => $eventname) {
                $drive_events[] = $event_id;
            }

            // pull lastest drive event for unit within date range
            $lastevent = $this->vehicle_data->getVehicleLastDriveEvent($unit, $drive_events);
            if (is_array($lastevent) AND ! empty($lastevent)) {
                $lastevent['direction'] = Unit::headingToDirection($lastevent['heading']);
                // if there was a drive event reported, compare drive event time to filter time range
                if (isset($lastevent['unittime']) AND ! empty($lastevent['unittime'])) {
                    if (strtotime($lastevent['unittime']) < strtotime($filter_date)) {
                        // unit did not have drive event within filter day range and is currently stationary
                        return $lastevent;
                    } else {
                        // unit had drive event within filter date range, so exclude from report
                        return false;
                    }
                } else {
                    // has drive event, but no unittime indication, include as stationary
                    return array();
                }
            } else {
                // could not find a last drive event, unit is currently stationary
                return array();
            }
        }

        // error
        return false;
    }

    /**
     * Get vehicle info report
     *
     * @return bool|array
     */
    public function getVehicleInfoReport($account_id)
    {
        $this->validator->validate('record_id', $account_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->vehicle_data->getVehicleInfoReport($account_id);
        }
        return false;
    }

    /**
     * Get vehicle mileage summary report
     *
     * @return bool|array
     */
    public function getVehicleMileageByAccountId($account_id, $filter_params)
    {
        $this->validator->validate('record_id', $account_id);

        if (! empty($filter_params) AND is_array($filter_params)) {
            if (isset($filter_params['total_miles'])) {
                $this->validator->validate('natural_number', $filter_params['total_miles']);
            }

            if (isset($filter_params['unit_id'])) {
                $this->validator->validate('record_id', $filter_params['unit_id']);
            }

            if (isset($filter_params['unitgroup_id'])) {
                $this->validator->validate('record_id', $filter_params['unitgroup_id']);
            }
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->vehicle_data->getVehicleMileageByAccountId($account_id, $filter_params);
        }
        return false;
    }

    public function getVehicleInfoWhere($account_id, $filter_params)
    {
        $this->validator->validate('record_id', $account_id);

        if (! empty($filter_params) AND is_array($filter_params)) {
            if (isset($filter_params['starterstatus']) AND ! in_array($filter_params['starterstatus'], array('Enabled','Disabled'))) {
                $this->setErrorMessgage('Invalid Starter Status');
            }

            if (isset($filter_params['unit_id'])) {
                $this->validator->validate('record_id', $filter_params['unit_id']);
            }

            if (isset($filter_params['unitgroup_id'])) {
                $this->validator->validate('record_id', $filter_params['unitgroup_id']);
            }
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->vehicle_data->getVehicleInfoWhere($account_id, $filter_params);
        }
        return false;
    }

    /**
     * Get the vehicle default group by account id
     *
     * @params: account_id
     *
     * @return array | bool
     */
    public function getVehicleDefaultGroup($account_id)
    {
        $this->validator->validate('record_id', $account_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->vehicle_data->getVehicleDefaultGroup($account_id);
        }
        return false;
    }

    /**
     * Get all active vehicles for account
     *
     * @params: account_id
     *
     * @return array | bool
     */
    public function getActiveVehicles($account_id)
    {
        $this->validator->validate('record_id', $account_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->vehicle_data->getActiveVehicles($account_id);
        }

        return false;
    }

    /**
     * Get all active vehicles for account with odometer info
     *
     * @params: account_id
     *
     * @return array | bool
     */
    public function getActiveVehicleOdometer($account_id)
    {
        $this->validator->validate('record_id', $account_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->vehicle_data->getActiveVehicleOdometer($account_id);
        }

        return false;
    }

    /**
     * Get error messages (calls the parent method)
     *
     * @return bool|array
     */
    public function getErrorMessage()
    {
        return parent::getErrorMessage();
    }
}
