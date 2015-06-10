<?php

namespace Models\Logic;

use Models\Data\TerritoryData;
use Models\Data\UnitData;
use Models\Data\VehicleData;
use Models\Logic\AddressLogic;
use Models\Logic\BaseLogic;
use Models\Logic\LandmarkLogic;
use Models\Logic\UnitCommandLogic;
use Models\Logic\UnitLogic;

use GTC\Component\Utils\Date;
use GTC\Component\Utils\Arrayhelper;
use GTC\Component\Utils\Measurement;
use GTC\Component\Utils\CSV\CSVReader;
use GTC\Component\Form\Validation;
use GTC\Component\Map\DecartaGeocoder;

use AnthonyMartin\GeoLocation\GeoLocation as GeoLocation;

class TerritoryLogic extends BaseLogic
{
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();

        $this->address_logic = new AddressLogic;
        $this->decarta_geocoder = new DecartaGeocoder;
        $this->landmark_logic   = new LandmarkLogic;
        $this->territory_data = new TerritoryData;
        $this->unitcommand_logic = new UnitCommandLogic;
        $this->unit_data = new UnitData;
        $this->unit_logic = new UnitLogic;
        $this->validator = new Validation;
        $this->vehicle_data = new VehicleData;
    }

    /**
     *  API Models Gateway
     */
    public function apiPartnerKey($apiKey)
    {
        return $this->territory_data->apiPartnerKey($apiKey);
    }

    /**
     *  API Models Gateway
     */
    public function apiSubscriberKey($apiKey)
    {
        return $this->territory_data->apiSubscriberKey($apiKey);
    }

    /**
     *  API Models Gateway
     */
    public function api($apiKey,$script,$params,$metric)
    {

        switch ( $script ) {

          case            'check' : foreach($params as $key => $parameter){
                                        if(($parameter[0])&&($parameter[1])){
                                            switch($parameter[0]){
                                                case            'command' :
                                                case       'command_type' : $command_type = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            break; 
                                                case       'serialnumber' : $serialnumber = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            break;                                                                      
                                                case               'unit' : $serialnumber = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            $unit = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            $unit_id = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            break;                                                                      
                                                case            'unit_id' : $unit_id = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            break;                                                                      
                                            }
                                        }
                                    }

                                    if($serialnumber){
                                        $id = $this->territory_data->apiSerialNumber($serialnumber);
                                        if($id>0){
                                            $unit_id = $id ;
                                        }
                                    }

                                    if($unit_id){

                                        switch ($command_type) {
                                            case 'locate_on_demand':
                                                $event_id = 7;
                                                break;
                                            case 'starter_enable':
                                                $event_id = 29;
                                                break;
                                            case 'starter_disable':
                                                $event_id = 28;
                                                break;
                                            case 'reminder_on':
                                                $event_id = 109;
                                                break;
                                            case 'reminder_off':
                                                $event_id = 110;
                                                break;
                                            default:
                                                $this->ajax_respond($ajax_data);
                                                return;
                                        }

                                        // $out[$script][0]['message'] = 'Processing... (' . $unit_id . ':' .$command_type . ':'  . $event_id .')';
                                        $out[$script][0]['message'] = 'Processing "CHECK:' . $command_type . '" for unit_id=' . $unit_id . '...' ;
                                        $out[$script][0]['check'] = 'EXPECTED RESPONSE FROM DEVICE WITHIN PAST 20 SECONDS NOT FOUND' ;
                                        $out[$script][0]['check_status'] = 9 ;

                                        $count=0;
                                        while( (!($response)) && ($count<6) ){
                                            if($count){
                                                sleep(1);
                                            }
                                            $count++;
                                            $response = $this->unitcommand_logic->getCommandResponse( $unit_id, $event_id );
                                        }
                                        // $out[$script][0]['count'] = $count ;

                                        if ($response !== false && !empty($response)) {

                                            // $out[$script][0]['console'] = 'unitcommand_logic->getCommandResponse:success:' . $response['id'] . ':' . $response['event_id'] . ':' . $response['servertime'];
                                            $out[$script][0]['message'] = 'Successfully Responded';

                                            $out[$script][0]['check'] = 'SUCCESS' ;

                                            $out[$script][0]['check_status'] = 1 ;

                                            switch ($command_type) {
                                                case   'locate_on_demand' : $result = $this->vehicle_data->ajaxLocate($user,$unit_id);
                                                                            $out[$script][0]['latitude'] = $result[0]['latitude'];
                                                                            $out[$script][0]['longitude'] = $result[0]['longitude'];
                                                                            $out[$script][0]['formatted_address'] = $this->address_logic->validateAddress($result[0]['streetaddress'], $result[0]['city'], $result[0]['state'], $result[0]['zipcode'], $result[0]['country']);
                                                                            $out[$script][0]['infomarker_address'] = $this->address_logic->validateAddress($result[0]['streetaddress'], '<br>'.$result[0]['city'], $result[0]['state'], $result[0]['zipcode'], $result[0]['country']);
                                                                            $out[$script][0]['event'] = $lastunitevent;
                                                                            $out[$script][0]['message'] = 'Locate Successful';
                                                                            $out[$script][0]['check_status'] = 2 ;
                                                                            break;
                                                case     'starter_enable' : $element='starterstatus';
                                                                            $value='Enabled';
                                                                            $out[$script][0]['message'] = 'Response: Starter Enabled';
                                                                            $out[$script][0]['check_status'] = 3 ;
                                                                            break;
                                                case    'starter_disable' : $element='starterstatus';
                                                                            $value='Disabled';
                                                                            $out[$script][0]['message'] = 'Response: Starter Disabled';
                                                                            $out[$script][0]['check_status'] = 4 ;
                                                                            break;
                                                case        'reminder_on' : $element='reminderstatus';
                                                                            $value='On';
                                                                            $out[$script][0]['message'] = 'Response: Reminder On';
                                                                            $out[$script][0]['check_status'] = 5 ;
                                                                            break;
                                                case       'reminder_off' : $element='reminderstatus';
                                                                            $value='Off';
                                                                            $out[$script][0]['message'] = 'Response: Reminder Off';
                                                                            $out[$script][0]['check_status'] = 6 ;
                                                                            break;
                                            }

                                        } else {
                                            $out['recheck'][0]['recheck'] = 'RECHECK' ;
                                        }

                                    }

                                    return $out;

                                    break;

          case          'command' : foreach($params as $key => $parameter){
                                        if(($parameter[0])&&($parameter[1])){
                                            switch($parameter[0]){
                                                case            'command' :
                                                case       'command_type' : $command_type = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            break; 
                                                case       'serialnumber' : $serialnumber = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            break;                                                                      
                                                case               'unit' : $serialnumber = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            $unit = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            $unit_id = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            break;                                                                      
                                                case            'unit_id' : $unit_id = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            break;                                                                      
                                            }
                                        }
                                    }

                                    if($serialnumber){
                                        $id = $this->territory_data->apiSerialNumber($serialnumber);
                                        if($id>0){
                                            $unit_id = $id ;
                                        }
                                    }

                                    if($unit_id){

                                        if ($command_type === 'starter_enable' OR $command_type === 'starter_disable') {                                                      // toggle starter
                                            if ($this->unitcommand_logic->toggleStarter($unit_id, ($command_type === 'starter_enable') ? true : false) !== false) {
                                                $out[$script][0]['message'] = 'Command Sent: ' . $command_type ;
                                                $out['recheck'][0]['recheck'] = 'RECHECK' ;
                                            } else {
                                                $errors = $this->unitcommand_logic->getErrorMessage();
                                                if (! empty($errors) AND is_array($errors)) {
                                                    $errors = implode(', ',$errors);
                                                } else {
                                                    $errors = 'Action failed due to database issue';
                                                }
                                                $out[$script][0]['message'] = $errors;
                                            }
                                        } else if ($command_type === 'reminder_on' OR $command_type === 'reminder_off') {                                                   // toggle reminder
                                            if ($this->unitcommand_logic->toggleReminder($unit_id, ($command_type === 'reminder_on') ? true : false) !== false) {
                                                $out[$script][0]['message'] = 'Command Sent: ' . $command_type ;
                                                $out['recheck'][0]['recheck'] = 'RECHECK' ;
                                            } else {
                                                $out[$script][0]['message'] = $this->unitcommand_logic->getErrorMessage();
                                            }
                                        } else if ($command_type === 'locate_on_demand') {                                                                                  // locate on demand
                                            if ($this->unitcommand_logic->locateOnDemand($unit_id, true)) {
                                                $out[$script][0]['message'] = 'Command Sent: ' . $command_type ;
                                                $out['recheck'][0]['recheck'] = 'RECHECK' ;
                                            } else {
                                                $out[$script][0]['message'] = $this->unitcommand_logic->getErrorMessage();
                                            }
                                        } else {
                                            $out[$script][0]['message'] = 'Invalid command type: "' . $command_type . '"' ;
                                        }

                                        $baseunitinfo = $this->unit_logic->getUnitInfo($unit_id);
                                        if(($baseunitinfo['twilio']=='y')||($baseunitinfo['twilio']=='Y')){
                                            $sms_params['direction'] = 'out';
                                            $sms_params['system_id'] = $baseunitinfo['simprovider_id'];
                                            $sms_params['unit_id'] = $unit_id;
                                            $sms_params['msisdn'] = $baseunitinfo['msisdn'];
                                            $sms_params['message'] = $command_type;
                                            $sms_params['messagestatus'] = 'Sent';
                                            $sms_params['server'] = $_SERVER['SERVER_NAME'];
                                            $sms_params['uri'] = $_SERVER['REQUEST_URI'];
                                            $out[$script][0]['twilio'] = $this->unitcommand_logic->smsTwilio($sms_params);
                                        }
        
                                    }

                                    return $out;

                                    break ;

                          default : foreach($params as $key => $parameter){

                                        if(($parameter[0])&&($parameter[1])){

                                            switch($parameter[0]){

                                                case           'latitude' : $latitude = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            break; 
                                            
                                                case          'longitude' : $longitude = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            break; 
                                            
                                                case             'radius' : $radius = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            break; 
                                            
                                            }

                                        }

                                    }

                                    if ( ( $latitude ) && ( $longitude ) && ( $radius ) ) {

                                        $latlngs[] = $latitude . ' ' . $longitude ;

                                        $boundingbox = $this->landmark_logic->getBoundingBoxValue('square', $latlngs, (! empty($radius) ? $radius : 0));

                                    }

                                    return $this->territory_data->api($apiKey,$script,$params,$boundingbox,$metric);

        }

    }

    /**
     * Get all permissions for a user
     *
     * @return array
     */
    public function getPermissions($account_id,$user_id)
    {
        return $this->territory_data->getPermissions($account_id,$user_id);
    }

    /**
     * Get the landmark groups info by user id
     *
     * @params: user_id
     *
     * @return array | bool
     */
    public function getTerritoryGroupsByUserId($user_id, $territorygroup_ids = array())
    {
        if (! is_numeric($user_id) OR $user_id <= 0) {
            $this->setErrorMessage('err_user');
        }

        if (! empty($territorygroup_ids)) {
            if (! is_array($territorygroup_ids)) {
                $territorygroup_ids = array($territorygroup_ids);
            }
            
            foreach($territorygroup_ids as $tgid) {
                if (! is_numeric($tgid) OR $tgid < 1) {
                    $this->setErrorMessage('Invalid territory group id');
                    break;    
                }
            }
        }

        if (! $this->hasError()) {
            return $this->territory_data->getTerritoryGroupsByUserId($user_id, $territorygroup_ids);
        }

        return false;
    }

    /**
     * Get the landmark groups info by account id
     *
     * @params: account_id
     *
     * @return array | bool
     */
    public function getTerritoryGroupsByAccountId($account_id)
    {
        if (! is_numeric($account_id) OR $account_id <= 0) {
            $this->setErrorMessage('Invalid account id');
        }

        if (! $this->hasError()) {
            return $this->territory_data->getTerritoryGroupsByAccountId($account_id);
        }

        return false;
    }

    /**
     * Get the territories by group_id
     *
     * @params: territorygroup_id
     *
     * @return array
     */
    public function getTerritoryInfoByGroupId($territorygroup_id)
    {
        if (! is_numeric($territorygroup_id) OR $territorygroup_id <= 0) {
            $this->setErrorMessage('err_id');
        }

        if (! $this->hasError()) {
            return $this->territory_data->getTerritoryInfoByGroupId($territorygroup_id);
        }

        return false;
    }
    
    /**
     * Get the landmark info by landmark group ids
     *
     * @params: landmark_groups
     *
     * @return array
     */
    public function getTerritoryByGroupIds($user_id, $territorygroups, $account_id)
    {

        $user['account_id'] = $account_id;
        $user['user_id'] = $user_id;
        $permission = $this->vehicle_data->ajaxPermissionCheck($user,'landmarks');

        return $this->territory_data->getTerritoryByGroupIds($user_id, $territorygroups, $account_id, $permission);

    }    

    /**
     * Get the filtered landmarks by provided params
     *
     * @params: int $user_id
     * @params: array $params
     *
     * @return array
     */
    public function getFilteredTerritory($user_id, $params)
    {
        $total_landmarks = array();
        
        $this->validator->validate('record_id', $user_id);

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('Invalid Parameters');
        } else {
            // validate search string (need to handle displaying the error message in the UI)
            if (isset($params['search_string']) AND $params['search_string'] !== '') {
                $this->validator->validate('alphanumeric', $params['search_string']);
            }
        }
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
    
            switch ($params['filter_type']) {
                
                case 'string_search':
                
                    $searchfields = array('territoryname');
                    $landmarks = $this->territory_data->getFilteredTerritoryStringSearch($user_id, $params, $searchfields);
                    if ($landmarks !== false) {
                        $total_landmarks = $landmarks;
                    }
                                 
                break;
                
                case 'group_filter':
                
                    if (isset($params['territorygroup_id']) AND strtolower($params['territorygroup_id']) == 'all') {
                        $params['territorygroup_id'] = array();
                    } elseif (! is_array($params['territorygroup_id'])) {
                        $params['territorygroup_id'] = array($params['territorygroup_id']);
                    }

                    if (isset($params['territorytype']) AND strtolower($params['territorytype']) == 'all') {
                        $params['territorytype'] = '';
                    }
                    
                    if (isset($params['territorycategory_id']) AND strtolower($params['territorycategory_id']) == 'all') {
                        $params['territorycategory_id'] = array();
                    } elseif (! is_array($params['territorycategory_id'])) {
                        $params['territorycategory_id'] = array($params['territorycategory_id']);
                    }

                    $landmarks = $this->territory_data->getFilteredTerritory($user_id, $params);

                    if ($landmarks !== false) {
                        $total_landmarks = $landmarks;
                    }
                
                break;
                
                default:
                
                break;
            }

            if (empty($params['territory_id'])) {   // if we do not need to paginate to any specific landmark
                // specialized personal paging and indexing
                if ( $params['paging'] == '+' ) {
                    $params['landmark_start_index'] = $params['landmark_start_index'] + $params['landmark_listing_length'];
                } elseif ($params['paging'] == '-') {
                    $params['landmark_start_index'] = $params['landmark_start_index'] - $params['landmark_listing_length'];
                    if ($params['landmark_start_index'] < 0) {
                        $params['landmark_start_index'] = 0;
                    }
                }
            } else {                                // else paginate to the specific page the landmark is located on
                $params['landmark_start_index'] = 0;
                if (! empty($total_landmarks)) {
                    $total_count = count($total_landmarks);
                    if ($total_count > $params['landmark_listing_length']) {
                        $key = false;
                        // loop through the landmarks and verify that this specific landmark does exist
                        foreach($total_landmarks as $index => $lm) {
                            if ($lm['territory_id'] == $params['territory_id']) {
                                $key = $index;        
                                break;
                            }
                        }

                        // if the specific landmark exist, find the starting index of the page it's on
                        if ($key !== false) {
                            if ($key < $params['landmark_listing_length']) {    // if it's on the first page, set the starting index to 0
                                $params['landmark_start_index'] = 0;
                            } else {                                            // else find the starting index using the current list length                                            
                                $starting_index = $params['landmark_listing_length'];
                                for ($i=0; $starting_index <= $total_count; $i++) {                                   
                                    if ($key < ($starting_index + $params['landmark_listing_length'])) {
                                        break;
                                    }                                    
                                    $starting_index = $starting_index + $params['landmark_listing_length'];   
                                }
                                $params['landmark_start_index'] = $starting_index;    
                            }
                        }
                    }
                }
                $data['landmark_start_index'] = $params['landmark_start_index'];        
            }

            $data['total_landmarks_count']  = count($total_landmarks);
            $total_key                      = intval(end(array_keys($total_landmarks)));
            $end_index                      = intval($params['landmark_start_index']) + intval($params['landmark_listing_length']);
            $data['landmarks']              = array_splice($total_landmarks, $params['landmark_start_index'], $params['landmark_listing_length']);
            $data['endpage']                = 0;

            if (intval($end_index) >= intval($total_key)) {
                $data['endpage'] = 1;
            }

            return $data;
        }

        return false;
    }


    /**
     * Get the filtered landmarks by provided params
     *
     * @params: int $user_id
     * @params: array $params
     *
     * @return array
     */
    public function getFilteredTerritoryList($user_id, $params)
    {
        $total_landmarks = array();
        $landmarks['iTotalRecords']          = 0;
        $landmarks['iTotalDisplayRecords']   = 0;
        $landmarks['data']                   = array();
        
        $this->validator->validate('record_id', $user_id);

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('Invalid Parameters');
        } else {
            // validate search string (need to handle displaying the error message in the UI)
            if (isset($params['search_string']) AND $params['search_string'] !== '') {
                $this->validator->validate('alphanumeric', $params['search_string']);
            }            
        }
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
    
            switch ($params['filter_type']) {
                
                case 'string_search':
                
                    $searchfields = array('territoryname');
                    $landmarks = $this->territory_data->getFilteredTerritoryStringSearch($user_id, $params, $searchfields);
                    if ($landmarks !== false) {
                        $total_landmarks = $landmarks;
                    }
                                 
                break;
                
                case 'group_filter':
                
                    if (isset($params['territorygroup_id'])) {
                        if (strtolower($params['territorygroup_id']) == 'all') {
                            $params['territorygroup_id'] = array();
                        } elseif (! is_array($params['territorygroup_id'])) {
                            $params['territorygroup_id'] = array($params['territorygroup_id']);
                        }
                    }

                    if (isset($params['territorytype']) AND strtolower($params['territorytype']) == 'all') {
                        $params['territorytype'] = '';
                    }

                    if (isset($params['territorycategory_id'])) {
                        if (strtolower($params['territorycategory_id']) == 'all') {
                            $params['territorycategory_id'] = array();
                        } else if (! is_array($params['territorycategory_id'])) {
                            $params['territorycategory_id'] = array($params['territorycategory_id']);
                        }
                    }
                    
                    $landmarks = $this->territory_data->getFilteredTerritory($user_id, $params);
                    if ($landmarks !== false) {
                        $total_landmarks = $landmarks;
                    }
                
                break;
                
                default:

                break;
            }

            // for the formatted unit events, process for datatable return results
            if (! empty($total_landmarks)) {

                // init total results
                $iTotal                             = count($total_landmarks);
                $iFilteredTotal                     = count($total_landmarks);
                $landmarks['iTotalRecords']         = $iTotal;
                $landmarks['iTotalDisplayRecords']  = $iFilteredTotal;
                $aColumns                           = array();        // datatable columns event field/key names
                
                for ( $i = 0 ; $i < intval( $params['iColumns'] ) ; $i++ ) {
                    $aColumns[] = $params['mDataProp_'.$i];
                }

                // if doing a string search in filter search box
                if ( isset($params['string_search']) AND $params['string_search'] != "" ) {
                    $total_landmarks = $this->filterTerritoryListStringSearch($params['string_search'], $aColumns, $total_landmarks);
                    $iTotal         = count($total_landmarks);
                    $iFilteredTotal = count($total_landmarks);
                }

                $landmarks['iTotalRecords'] = $iTotal;
                $landmarks['iTotalDisplayRecords'] = $iFilteredTotal;
        
                $formatted_results = array();
                if (! empty($total_landmarks)) {
                    foreach ($total_landmarks as $landmark) {
                        $row = $landmark;
                        $row['formatted_address'] = $this->address_logic->validateAddress($row['streetaddress'], $row['city'], $row['state'], '', $row['country']);
                        $row['radius_in_miles'] = Measurement::radiusFeetToFractionConverter($row['radius']);

                        $row['DT_RowId']           = 'landmark-tr-'.$row['territory_id'];       // automatic tr id value for dataTable to set

                        if ($row['territorygroupname'] == '' OR is_null($row['territorygroupname'])){
                            $row['territorygroupname'] = $params['default_value'];
                        }

                        $formatted_results[] = $row;
                    }

                    // if doing a column sorting
                    if ( isset( $params['iSortCol_0'] ) AND $params[ 'bSortable_'.intval($params['iSortCol_0']) ] == "true")
                    {
                        $formatted_results = $this->filterTerritoryListSort($aColumns[ intval($params['iSortCol_0']) ], ($params['sSortDir_0']==='asc' ? 'asc' : 'desc'), $formatted_results);
                    }

                    // if doing paging, find correct page list
                    if ( isset($params['iDisplayStart']) AND $params['iDisplayLength'] != '-1' )
                    {
                        $formatted_results = array_splice($formatted_results, $params['iDisplayStart'], $params['iDisplayLength']);
                    }
                }

                $landmarks['data'] = $formatted_results;
            }
        }

        return $landmarks;
    }


    /**
     * Return landmarks having column field values matching search string
     *
     * @params: string search_str
     * @params: array aColumns
     * @params: array landmarks
     *
     * @return array
     */
    public function filterTerritoryListStringSearch($search_str, $aColumns, $territories)
    {
        $results = array();
        if ( isset($search_str) AND $search_str != "" AND ! empty($territories)) {
            foreach($territories as $key => $territory) {                                    // loop through landmarks info and find if search string is found
                if (! array_key_exists($territory['territory_id'], $results)) {
                    for ($i = 0; $i < count($aColumns); $i++) {                         // loop for each field to search in
                        if (! array_key_exists($$territory['territory_id'], $results)) {        // only search if unit is not currently in result array
                            if (($pos = strpos(strtolower($territory[$aColumns[$i]]), strtolower($search_str))) !== false) {
                                $results[$territory['territory_id']] = $territory;
                                continue;
                            }
                        }
                    }
                } else {
                    continue;
                }
            }
        } else {
            $results = $territories;
        }
        
        return $results;
    }

    /**
     * Return landmarks events having sorted by column field by sort order
     *
     * @params: string $column_name
     * @params: string $sort_order
     * @params: array landmarks
     *
     * @return array $results
     */
    public function filterTerritoryListSort($column_name, $sort_order, $territories)
    {
        $results = $territories;
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
     * Save a landmark into the database
     *
     * @param int     account_id
     * @param float   latitude
     * @param float   longitude
     * @param string  title     
     * @param int     radius
     * @param string  street_address
     * @param string  city
     * @param string  state
     * @param string  country
     * @param string  shape
     * @param int     type
     * @param int     group
     * @param array   coordinates 
                      NOTE: 'coordinates' is an array of coordinates in the following format: 
                            array(array('latitude' => 12.355, 'longitude' => 23.677), array('latitude' => 22.222, 'longitude' => 33.333), ....)
     * @param bool    reference (indicator for whether or not it's a reference landmark - defaults to false)
     *
     * @return int|bool
     */    
    public function saveTerritory($account_id, $latitude, $longitude, $title, $radius, $street_address, $city, $state, $zip, $country, $shape, $type, $group, $coordinates, $category_id = 0, $reference = false , $landmarktype , $landmarktypetext) 
    {

        // $title = str_replace("'", "\'", $title);
        // $street_address = str_replace("'", "\'", $street_address);
        // $city = str_replace("'", "\'", $city);
        // $state = str_replace("'", "\'", $state);
        // $zip = str_replace("'", "\'", $zip);
        // $country = str_replace("'", "\'", $country);

        if ((! is_numeric($account_id)) OR ($account_id <= 0)) {
            $this->setErrorMessage('err_account');
        }

        if (! is_numeric($latitude) OR empty($latitude) OR ! is_numeric($longitude) OR empty($longitude)) {
            $this->setErrorMessage('err_coordinates');
        }

        $this->validator->validate('territory_name', $title);

        if (! empty($shape) AND ($shape == 'circle') AND (! is_numeric($radius) OR $radius <= 0)) {
            $this->setErrorMessage('err_radius');
        }

        if ((! isset($type)) OR empty($type)) {
            $this->setErrorMessage('err_landmark_type');
        } else if ($type == 'landmark') { // if it's a regular landmark, both type and group are required
            if ((! is_numeric($group)) OR ($group <= 0)) {
                $this->setErrorMessage('err_landmark_group');
            }
        }

        // if ((! empty($country)) AND ($country === 'USA') AND empty($state)) {
        //     $this->setErrorMessage('err_state');
        // }

        if (empty($shape) OR (! empty($shape) AND ($this->validateTerritoryShape($shape) === false))) {
            $this->setErrorMessage('err_landmark_shape');
        }

        if (($shape != 'circle')&&(empty($coordinates) OR ! is_array($coordinates))) {
            $this->setErrorMessage('err_invalid_coordinates');
        } else {
            $latlngs = array();

            // if shape is not circle, add the first lat/lng as the last lat/lng to connect the polygon
            if ($shape == 'circle') {
                $latlngs[0] = $latitude . ' ' . $longitude;
                $coordinates = $latlngs;
            } else {
                foreach($coordinates as $index => $coords) {
                    $latlngs[] = $coords['latitude'] . ' ' . $coords['longitude'];
                }
                $latlngs[] = $coordinates[0]['latitude'] . ' ' . $coordinates[0]['longitude'];
                if ($shape != 'square') {
                    // $street_address = $city = $state = $zip = $country = '';
                    $radius = 0;
                }
                $coordinates = $latlngs;
            }

        }

        if ($category_id !== NULL) {
            if (is_numeric($category_id)) {
                if ($category_id < 0) {
                    $this->setErrorMessage('err_invalid_category');
                }
            } else {
                $this->setErrorMessage('err_invalid_category');
            }
        } else {
            $category_id = 0;
        }
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            
            // check for duplication of landmark name
            $buffer = str_replace("'", "\'", $title);
            $landmark = $this->territory_data->getTerritoryByTitle($account_id, $buffer);

            if (empty($landmark)) {   // if there is no landmark in the account with the same name, save the new landmark
                $params = array(
                    'account_id' => $account_id,
                    'territorycategory_id' => $category_id,
                    'landmarktype' => $landmarktype,
                    'landmarktypetext' => $landmarktypetext,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'territoryname' => $title,
                    'radius' => $radius,
                    'territorytype' => $type,
                    'active' => 1,
                    'shape' => $shape,
                    'boundingbox' => $this->getBoundingBoxValue($shape, $coordinates, $radius)
                );

                if (! empty($street_address)) {
                    $params['streetaddress'] = $street_address;
                }

                if (! empty($city)) {
                    $params['city'] = $city;
                }

                if (! empty($state)) {
                    $params['state'] = $state;
                }
    
                if (! empty($zip)) {
                    $params['zipcode'] = $zip;
                }

                if (! empty($country)) {
                    $params['country'] = $country;
                }

                if (! empty($params['boundingbox'])) {
                    $territory_id = $this->territory_data->saveTerritory($params);
                    if ($territory_id !== false) {
                        if ($reference === false) { // if it's not a reference landmark, add it to the desired landmark group
                            if ($this->saveTerritoryToTerritoryGroup($territory_id, $group) !== false) {
                                return $territory_id;
                            } else {
                                $this->deleteTerritory($account_id, $territory_id, false);
                            }
                        } else {                    // else, it's a reference landmark, just return the landmark id
                            return $territory_id;
                        }
                    } else {
                        $this->setErrorMessage('Error: could not save landmark');
                    }
                } else {
                    $this->setErrorMessage('Error: invalid boundingbox ('.$shape.':'.$radius.')');
                }
            } else {                    // else, if there is an existing landmark with the same name, throw error
                $this->setErrorMessage('Error: landmark named ' . $title . ' alredy exists');
            }
        }

        return false;
    }

    /**
     * Create a New Verification Address in the database
     *
     */
    public function newVerification($user, $unit_id, $latitude, $longitude, $title, $radius, $street_address, $city, $state, $zip, $country, $shape)
    {

        $account_id = $user['account_id'] ;

        if($user){
            $permission = $this->vehicle_data->ajaxPermissionCheck($user,'verification-address-add');
        }

        if (($account_id)&&($permission)) {

            // check for duplication of landmark name
            $landmark = $this->territory_data->getUnitTerritoryByTitle($account_id, $unit_id, $title);

            if (empty($landmark)) {   // if there is no landmark in the account with the same name, save the new landmark
                $params = array(
                    'account_id' => $account_id,
                    'territorycategory_id' => '0',
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'territoryname' => $title,
                    'radius' => $radius,
                    'territorytype' => 'reference',
                    'active' => 1,
                    'shape' => $shape
                );

                if (! empty($street_address)) {
                    $params['streetaddress'] = $street_address;
                }

                if (! empty($city)) {
                    $params['city'] = $city;
                }

                if (! empty($state)) {
                    $params['state'] = $state;
                }
    
                if (! empty($zip)) {
                    $params['zipcode'] = $zip;
                }

                if (! empty($country)) {
                    $params['country'] = $country;
                }

                $params['boundingbox'] = $this->getBoundingBoxValue($params['shape'], array($params['latitude'] . ' ' . $params['longitude']), $params['radius']);

                if($params['boundingbox']){

                    $territory_id = $this->territory_data->saveTerritory($params);

                    if($territory_id){

                        $params = array(
                            'unit_id' => $unit_id,
                            'territory_id' => $territory_id
                        );
                        $result = $this->territory_data->addTerritoryToVehicle($params);

                        $result['error'] = $territory_id . ':xxx:' . $unit_id ;

                    } else {

                        $result['error'] = 'Territory Id missing' ;

                    }

                } else {

                    $result['error'] = 'Bounding box empty' ; // (' . $params['latitude'] . ':' . $params['longitude'] . ' >> ' . $params['radius'] . ' >> ' . $params['shape'] . ')' ;

                }

            } else {                    // else, if there is an existing landmark with the same name, throw error

                $result['error'] = 'New record exists' ;

            }

        } else {

            $result['error'] = 'Account Id missing' ;

        }

        $result['permission'] = $permission ;

        return $result;
    }

    /**
     * Add landmark to landmark group
     *
     * @param int landmark_id
     * @param int landmarkgroup_id
     *
     * @return bool
     */    
    public function saveTerritoryToTerritoryGroup($territory_id, $territorygroup_id) 
    {
        if (! is_numeric($territory_id) OR $territory_id <= 0) {
            $this->setErrorMessage('err_landmark_id');
        }

        if (! is_numeric($territorygroup_id) OR $territorygroup_id <= 0) {
            $this->setErrorMessage('err_landmarkgroup_id');
        }

        if (! $this->hasError()) {
            if ($this->territory_data->saveTerritoryToTerritoryGroup($territory_id, $territorygroup_id) !== false) {
                return true;
            }
        }

        return false; 
    }

    /**
     * Add landmark to vehicle
     *
     * @param int unit_id
     * @param int landmark_id
     *
     * @return int|bool
     */
    public function addTerritoryToVehicle($unit_id, $territory_id) 
    {
        if ((! is_numeric($unit_id)) OR ($unit_id <= 0)) {
            $this->setErrorMessage('err_unit');    
        }

        if ((! is_numeric($territory_id)) OR ($territory_id <= 0)) {
            $this->setErrorMessage('err_landmark');
        }

        if (! $this->hasError()) {
            $params = array(
                'unit_id' => $unit_id,
                'territory_id' => $territory_id
            );

            if ($this->territory_data->addTerritoryToVehicle($params)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Deletes a landmark
     *
     * @param int   landmark_id
     * @param int   account_id
     * @param bool  reference
     *
     * @return bool
     */    
    public function deleteTerritory($territory_id, $account_id, $reference = false) 
    {
        if ((! is_numeric($territory_id)) OR ($territory_id <= 0)) {
            $this->setErrorMessage('err_landmark');
        }

        if ((! is_numeric($account_id)) OR ($account_id <= 0)) {
            $this->setErrorMessage('err_account');
        }

        if ($reference !== true AND $reference !== false) {
            $this->setErrorMessage('err_landmark_reference');
        }

        if (! $this->hasError()) {
            if ($this->territory_data->deleteTerritory($territory_id, $account_id, $reference)) {
                return true;
           }
        }

        return false;
    }

    /**
     * Deletes an unfound landmark
     *
     * @param int   landmarkupload_id
     * @param int   account_id
     *
     * @return bool
     */    
    public function deleteTerritoryUpload($territoryupload_id, $account_id) 
    {
        if ((! is_numeric($territoryupload_id)) OR ($territoryupload_id <= 0)) {
            $this->setErrorMesage('err_landmark');
        }

        if ((! is_numeric($account_id)) OR ($account_id <= 0)) {
            $this->setErrorMesage('err_account');
        }

        if (! $this->hasError()) {
            if ($this->territory_data->deleteTerritoryUpload($territoryupload_id, $account_id)) {
                return true;
           }
        }

        return false;
    }
    
    /**
     * Update landmark
     *
     * @param int   landmark_id
     * @param int   account_id
     * @param array params
     *
     * @return bool
     */    
    public function updateTerritory($territory_id, $account_id, $params) 
    {
        if ((! is_numeric($territory_id)) OR ($territory_id <= 0)) {
            $this->setErrorMesage('err_landmark');
        }

        if ((! is_numeric($account_id)) OR ($account_id <= 0)) {
            $this->setErrorMesage('err_account');
        }
        
        if (! is_array($params) OR empty($params)) {
            $this->setErrorMessage('err_params');
        } else {
            if (! is_numeric($params['latitude']) OR empty($params['latitude']) OR ! is_numeric($params['longitude']) OR empty($params['longitude'])) {
                $this->setErrorMessage('err_coordinates');
            }
            
            $this->validator->validate('territory_name', $params['territoryname']);
            
            if (! is_numeric($params['radius']) OR $params['radius'] <= 0) {
                $this->setErrorMessage('err_radius');
            }
            
            if (! isset($params['territorytype']) OR $params['territorytype'] == '') {
                $this->setErrorMessage('err_landmark_reference');
            }
            
            if ((! empty($params['country'])) AND ($params['country'] === 'USA') AND empty($params['state'])) {
                $this->setErrorMessage('err_state');
            }

            if ($category_id !== NULL) {
                if (is_numeric($category_id)) {
                    if ($category_id == 0) {
                        $category_id = NULL;
                    } else if ($category_id < 0) {
                        $this->setErrorMessage('err_invalid_category');
                    }
                } else {
                    $this->setErrorMessage('err_invalid_category');
                }
            }
        }
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            //  check for landmark name duplication
            $valid_name = true;
            $landmark = $this->territory_data->getTerritoryByTitle($account_id, $params['territoryname']);

            if (is_array($landmark) AND ! empty($landmark)) {   // if there is a landmark with the same name, check to see if it's the same landmark
                $landmark = $landmark[0];
                if (isset($landmark['territory_id']) AND ($landmark['territory_id'] != $territory_id)) {   // if it's not the same landmark, landmark name is duplicated
                    $valid_name = false;
                    $this->setErrorMessage('err_duplicate_name');
                }
            }

            if ($valid_name) {
                if ($this->territory_data->updateTerritory($territory_id, $account_id, $params) !== false) {
                    return true;
                } else {
                    $this->setErrorMessage('err_db_update');
                }
            }
        }

        return false;
    }

    /**
     * Get data for Verification datatable by unit id
     *
     * @param int unit_id
     * @param bool params
     *
     * @return bool
     */
    public function getVehicleVerificationData($unit_id, $params) 
    {
        if (! is_numeric($unit_id) OR $unit_id <= 0) {
            $this->setErrorMessage('err_unit');
        }
        
        if (! isset($params['filter_type']) OR empty($params['filter_type'])) {
            $this->setErrorMessage('err_params');
        }
        
        if (! $this->hasError()) {

            // create output array of results
            $output = array(
                "sEcho"                 => intval($params['sEcho']),
                "iTotalRecords"         => 0,
                "iTotalDisplayRecords"  => 0,
                "data"                  => array()
            );

            $verified = '';

            if (! empty($params['filter_type'])) {
                $verified = $params['filter_type'];
            }
            
            $landmarks = $this->territory_data->getTerritoryByUnitId($unit_id, true, $verified);
            if ($landmarks !== false) {

                $output['iTotalRecords']        = count($landmarks);
                $output['iTotalDisplayRecords'] = count($landmarks);

                foreach ($landmarks as $index => $landmark) {
                    $landmarks[$index]['formatted_address'] = $this->address_logic->validateAddress($landmark['streetaddress'], $landmark['city'], $landmark['state'], $landmark['zipcode'], $landmark['country']);

                    // convert feet to miles and format string to three places after decimal (will put in another component) 
                    $landmarks[$index]['radius_in_miles'] = Measurement::radiusFeetToFractionConverter($landmark['radius']);

                    // format latitude/longitude to only display 5 numbers after the decimal
                    $landmarks[$index]['formatted_latitude'] = sprintf('%01.5f', $landmark['latitude']);
                    $landmarks[$index]['formatted_longitude'] = sprintf('%01.5f', $landmark['longitude']);

                    if ($landmark['verifydate'] !== '0000-00-00') {
                        $formatted_verified_date = Date::utc_to_locale($landmark['verifydate'], $params['user_timezone'], 'm/d/Y');
                        $verified = 1;   
                    } else {
                        $formatted_verified_date = '';
                        $verified = 0;
                    }

                    $landmarks[$index]['formatted_verified_date'] = $formatted_verified_date;
                    $landmarks[$index]['verified'] = $verified;

                    $landmarks[$index]['table_actions']  = '<span class="has-tooltip has-popover glyphicon glyphicon-pencil hidden" title="Edit" data-placement="bottom" data-popover-placement="left" data-popover-title-id="popover-head-verification-edit-address" data-popover-content-id="popover-content-verification-edit-address" data-popover-content-method="clone"></span>';
                    $landmarks[$index]['table_actions'] .= '&nbsp;';
                    $landmarks[$index]['table_actions'] .= '<span class="has-tooltip has-popover glyphicon glyphicon-remove hidden" title="Delete" data-placement="bottom" data-popover-placement="left" data-popover-title-id="popover-head-verification-delete-address" data-popover-content-id="popover-content-verification-delete-address" data-popover-content-method="clone"></span>';
                }

                if (! empty($landmarks)) {

                    if ( isset( $params['iSortCol_0'] ) AND $params[ 'bSortable_'.intval($params['iSortCol_0']) ] == "true")        // if doing a column sorting
                    {
                        $landmarks = $this->filterVehicleVerificationSort($params['mDataProp_'.intval($params['iSortCol_0'])], ($params['sSortDir_0'] === 'asc' ? 'asc' : 'desc'), $landmarks);
                    }

                    if ( isset( $params['iDisplayStart'] ) AND $params['iDisplayLength'] != '-1' )       // if doing paging, find correct page list
                    {
                        $landmarks = $this->filterVehicleVerificationPaging($params['iDisplayStart'], $params['iDisplayLength'], $landmarks);
                    }
                }
                
                $output['data'] = $landmarks;
            }

            return $output;
        }
        
        return false;
    }

    /**
     * Sort reference landmarks for Verification table
     *
     * @return bool
     */
    public function filterVehicleVerificationSort($column_name, $order, $landmarks)
    {
        if (! empty($landmarks)) {
            if (! empty($column_name)) {
                if ($order == 'asc') {
                    usort($landmarks, Arrayhelper::usort_compare_asc($column_name));
                } else if ($order == 'desc') {
                    usort($landmarks, Arrayhelper::usort_compare_desc($column_name));
                }
            }
        }

        return $landmarks;
    }

    /**
     * Sort reference landmarks for Verification table
     *
     * @return bool
     */
    public function filterVehicleVerificationPaging($start, $length, $landmarks)
    {
        if (! empty($landmarks)) {
            $landmarks = array_splice($landmarks, $start, $length);
        }
        
        return $landmarks;
    }
    
    /**
     * Get landmarks by unit id
     *
     * @param int unit_id
     * @param bool reference ('null' means get both reference and non reference landmarks)
     *
     * @return bool
     */
    public function getTerritoryByUnitId($unit_id, $user_timezone, $reference = null) {
        if (! is_numeric($unit_id) OR $unit_id <= 0) {
            $this->setErrorMessage('err_unit');
        }
        
        if ($reference !== true AND $reference !== false AND $reference !== null) {
            $this->setErrorMessage('err_landmark_reference');
        }

        if (! $this->hasError()) {
            $landmarks = $this->territory_data->getTerritoryByUnitId($unit_id, $reference);
            if ($landmarks !== false) {
                foreach ($landmarks as $index => $landmark) {
                    $landmark[$index]['landmark_index'] = $index;
                    $landmarks[$index]['formatted_address'] = $this->address_logic->validateAddress($landmark['streetaddress'], $landmark['city'], $landmark['state'], $landmark['zipcode'], $landmark['country']);    

                    // convert feet to miles and format string to three places after decimal (will put in another component)
                    //$landmarks[$index]['radius_decimal']  = Measurement::radiusFeetToMileConverter($landmark['radius']);
                    $landmarks[$index]['radius_in_miles'] = Measurement::radiusFeetToFractionConverter($landmark['radius']);

                    if ($reference === true OR ($reference === null AND ($landmark['reference'] == 1))) {       // if it's a reference landmark, clean up verify date
                        if ($landmark['verifydate'] !== '0000-00-00') {
                            $formatted_verified_date = Date::utc_to_locale($landmark['verifydate'], $user_timezone, 'm/d/Y');
                            $verified = 1;   
                        } else {
                            $formatted_verified_date = '';
                            $verified = 0;
                        }

                        $landmarks[$index]['formatted_verified_date'] = $formatted_verified_date;
                        $landmarks[$index]['verified'] = $verified;
                    }
                }

                return $landmarks;
            }
        }

        return false;
    }

    /**
     * Process uploaded reference landmarks for a unit
     *
     * @param int unit_id
     * @param int account_id
     * @param string file_path (path of uploaded file)
     *
     * @return bool
     */
    public function uploadTerritory($account_id, $user_id, $unit_id, $file_path, $type = 'landmark', $separator = ',', $enclosure = '"') 
    {
        if (! is_numeric($account_id) OR $account_id <= 0) {
            $this->setErrorMessage('err_account');
        }

        if (isset($unit_id) AND $unit_id != '') {
            if (! is_numeric($unit_id) OR $unit_id <= 0) {
                $this->setErrorMessage('err_unit');
            }
        }

        if (! $this->hasError()) {

            if (strlen($file_path) > 3) {

                // check if landmark upload or reference upload
                $islandmark_upload = true;
                $expected_columns = 10;
                if (isset($unit_id) AND $unit_id != '') {
                    $islandmark_upload = false;
                    $expected_columns = 9;
                    $type = 'reference';
                } else {
                    $this->territory_data->setTerritoryType(array($type));
                    $default_group = $this->territory_data->getTerritoryDefaultGroup($account_id);
                    $default_group_id = (! empty($default_group)) ? $default_group['territorygroup_id'] : 0;
                    $this->territory_data->resetTerritoryType();
                }

                $csv_reader= new CSVReader();
                $csv_reader->setSeparator($separator);
                $csv_reader->setEnclosure($enclosure);
                $csv_reader->setMaxRowSize(0);
                $csv_reader->setFile($file_path, $expected_columns, true);

                $process_landmarks_debug = '';
                $process_landmarks_debug_loop = 0;
                $complete_landmarks_count = 0;
                $incomplete_landmarks_count = 0;

                for ($i = 0; $row = $csv_reader->parseFileByLine(); $i++) {
$process_landmarks_debug_loop++;
$debug['error'] .= $process_landmarks_debug_loop ;
                    if ($i == 0) {
                        $validaterow = $this->validateTerritoryTemplate($row, $islandmark_upload);
                        if ( $validaterow === false) {
                            // $this->setErrorMessage('err_template');
                            $result['headererror'] = 1 ; 
                            return $result; 
                            exit();
                            // return false;    
                        }
                    } else if ($i < 101) {
                        $error = '';
                        $params = array(
                            'account_id'            => $account_id,
                            'unit_id'               => ((isset($unit_id) AND ! empty($unit_id)) ? trim($unit_id) : 0),
                            'territorycategory_id'  => 0,
                            'territorytype'         => $type,
                            'shape'                 => 'circle',
                            'territoryname'         => (isset($row['name']) ? trim($row['name']) : ''),
                            'territorygroupname'    => (isset($row['landmarkgroupname'])) ? trim($row['landmarkgroupname']) : '',
                            'latitude'              => ((is_numeric($row['latitude']) AND ! empty($row['latitude'])) ? trim($row['latitude']) : 0),
                            'longitude'             => ((is_numeric($row['longitude']) AND ! empty($row['longitude'])) ? trim($row['longitude']) : 0),
                            'streetaddress'         => (isset($row['street']) ? trim($row['street']) : ''),
                            'city'                  => (isset($row['city']) ? trim($row['city']) : ''),
                            'state'                 => (isset($row['state']) ? trim($row['state']) : ''),
                            'zipcode'               => (isset($row['zipcode']) ? trim($row['zipcode']) : ''),
                            'country'               => (isset($row['country']) ? trim($row['country']) : ''),
                            'radius'                => Measurement::radiusMileToFeetConverter($row['radius(miles)'])
                            //'radius'                => ((is_numeric($row['radius(miles)']) AND ! empty($row['radius(miles)'])) ? (trim($row['radius(miles)']) * 5280) : 0)
                        );

                        $this->validator->validate('territory_name', $params['territoryname']); // 1st step - validate landmark name    
                        if (! $this->validator->hasError()) {
                            $landmark = $this->getTerritoryByTitle($account_id, $params['territoryname'], false);
                            if ($landmark !== false) {     // check name for duplication
                                if (empty($landmark)) {
                                    if (! empty($params['radius']) AND ($this->validateTerritoryRadius($params['radius']) === true)) {       // 2nd step - validate radius
                                        if ($this->validateTerritoryShape($params['shape'])) {     // 3rd step - validate landmark type (shape)
                                            $params['boundingbox'] = $this->getBoundingBoxValue($params['shape'], array($params['latitude'] . ' ' . $params['longitude']), $params['radius']);
                                            if ($params['boundingbox'] !== '') {
                                                $valid_address = $valid_coords = false;

                                                if (!($params['country'])){
                                                    $params['country'] = 'USA';
                                                }

$debug['error'] .= '<br>' . $params['latitude'] . '/' . $params['longitude'] ;
$debug['error'] .= '<br>' . $params['streetaddress'] . ', ' . $params['city'] . ', ' . $params['state'] . ', ' . $params['zipcode'] . ', ' . $params['country'] ;

                                                if (! empty($params['latitude'])        AND 
                                                    is_numeric($params['latitude'])     AND 
                                                    ! empty($params['longitude'])       AND 
                                                    is_numeric($params['longitude'])) {      // 4rd step - validate coordinates
                                                    $valid_coords = true;

                                                    if (empty($params['streetaddress'])   OR 
                                                        empty($params['city'])            OR 
                                                        empty($params['state'])           OR 
                                                        empty($params['zipcode'])         OR 
                                                        empty($params['country'])) {

                                                        $geoBuff = $this->decarta_geocoder->reverseGeocode($params['latitude'], $params['longitude']);
$debug['error'] .= '<br>reverseGeocode:' . implode(', ',$geoBuff['error']) ;

                                                        $valid_address = true;

                                                        if($geoBuff['success']){

                                                            $override='';

                                                            if (!($params['streetaddress'])) {
                                                                $params['streetaddress'] = $geoBuff['result']['streetaddress'];
                                                                $override=1;
                                                            }
                                                            if ((!($params['city']))||(($geoBuff['result']['city'])&&($override))) {
                                                                $params['city'] = $geoBuff['result']['city'];
                                                                $override=1;
                                                            }
                                                            if ((!($params['state']))||(($geoBuff['result']['state'])&&($override))) {
                                                                $params['state'] = $geoBuff['result']['state'];
                                                                $override=1;
                                                            }
                                                            if ((!($params['zipcode']))||(($geoBuff['result']['zipcode'])&&($override))) {
                                                                $params['zipcode'] = $geoBuff['result']['zipcode'];
                                                                $override=1;
                                                            }
                                                            if ((!($params['country']))||($override)) {
                                                                $params['country'] = $geoBuff['result']['country'];
                                                                $override=1;
                                                            }

                                                        }

                                                    }

                                                }

                                                // if (! empty($params['streetaddress'])   AND 
                                                //     ! empty($params['city'])            AND 
                                                //     ! empty($params['state'])           AND 
                                                //     ! empty($params['zipcode'])         AND 
                                                //     ! empty($params['country'])) {     // 5th step - validate address
                                                if (! empty($params['streetaddress'])   AND 
                                                    ! empty($params['city'])            AND 
                                                    ! empty($params['state'])) {     // 5th step - validate address

                                                    $valid_address = true;

                                                    if ( (!($params['latitude'])) || (!($params['longitude'])) ) {

                                                        $address['streetaddress'] = $params['streetaddress']; 
                                                        $address['city'] = $params['city']; 
                                                        $address['state'] = $params['state']; 
                                                        $address['zipcode'] = $params['zipcode']; 
                                                        // $address['country'] = $params['country'];

                                                        $geoBuff = $this->decarta_geocoder->geocode($address);
$debug['error'] .= '<br>geocode:' . implode(', ',$geoBuff['error']) ;

                                                        if($geoBuff['success']){

                                                            if (!($params['latitude'])) {
                                                                $params['latitude'] = $geoBuff['result']['latitude'];
                                                            }
                                                            if (!($params['longitude'])) {
                                                                $params['longitude'] = $geoBuff['result']['longitude'];
                                                            }

                                                            if (! empty($params['latitude'])        AND 
                                                                is_numeric($params['latitude'])     AND 
                                                                ! empty($params['longitude'])       AND 
                                                                is_numeric($params['longitude'])) {      // 4rd step - validate coordinates
                                                                $valid_coords = true;

                                                            }

                                                        }

                                                    }

                                                }

$debug['error'] .= '<br>' . $params['latitude'] . '/' . $params['longitude'] ;
$debug['error'] .= '<br>' . $params['streetaddress'] . ', ' . $params['city'] . ', ' . $params['state'] . ', ' . $params['zipcode'] . ', ' . $params['country'] ;

$debug['error'] .= '<br>coords[' . $valid_coords . ']:address[' . $valid_address . ']' ;
                                                if ($valid_coords AND $valid_address) {     // if the landmark has both valid address and lat/lng - it's a valid landmark
$debug['error'] .= ':YES' ;
                                                    $params['active'] = 1;

                                                    $landmark_id = $this->territory_data->saveTerritory($params);
                                                    if ($landmark_id !== false) {       // if pass validation, save new landmark
                                                        if (isset($unit_id) AND $unit_id != '') {
                                                            // if reference landmark (unit_id provided)
                                                            if (($this->addTerritoryToVehicle($unit_id, $landmark_id)) !== false) {
                                                                // if new landmark was saved successfully, assign it to the intended unit
                                                                //  success - saved new landmark and assigned to unit   
                                                            } else {                                                                    
                                                                // else if we fail to assign the new landmark to unit, delete the new landmark
                                                                $this->deleteTerritory($landmark_id);
                                                            }
                                                        }

                                                        // for landmarks, landmarkgroup_id is required. default group if none provided
                                                        if ($params['territorytype'] == 'landmark') {
                                                            if (isset($params['territorygroupname']) AND $params['territorygroupname'] != '' ) {
                                                                // check for landmarkgroupname to get landmarkgroup_id, if not exist, create a new landmarkgroup and return that
                                                                $landmarkgroup = $this->getTerritoryGroupByTitle($account_id, $params['territorygroupname']);
                                                                if (! empty($landmarkgroup) AND isset($landmarkgroup['territorygroup_id']) AND $landmarkgroup['territorygroup_id'] > 0) {
                                                                    $params['territorygroup_id'] = $landmarkgroup['territorygroup_id'];
                                                                } else {
                                                                    //create new landmarkgroup for this new landmarkgroupname
                                                                    $landmarkgroup_id = $this->addTerritoryGroup($account_id, $params['territorygroupname']);
                                                                    if ($landmarkgroup_id) {
                                                                        $params['territorygroup_id'] = $landmarkgroup_id;
                                                                        
                                                                        // associate new group to this user
                                                                        if (isset($user_id) AND ! empty($user_id) ) {
                                                                            $this->addUserTerritoryGroup($user_id, $landmarkgroup_id);
                                                                        }
                                                                    }
                                                                }
                                                            } else {
                                                                // assign to account default landmarkgroup
                                                                $params['territorygroup_id'] = $default_group_id;
                                                            }
                                                            
                                                            // add landmarkgroup_landmark relation
                                                            $landmarkgroup_landmark['territorygroup_id'] = $params['territorygroup_id'];
                                                            $landmarkgroup_landmark['territory_id']      = $landmark_id;
                                                            
                                                            $this->addTerritorygroupTerritory($landmarkgroup_landmark);

                                                            $complete_landmarks_count++;

                                                        }
                                                    } else {
                                                        $error = 'db error - save landmark';
                                                    }
                                                } else {
                                                    // else save landmark as incomplete
                                                    if ($valid_coords) {
                                                        $error = 'requires rgeo';
                                                    } else if ($valid_address) {
                                                        $error = 'requires geo';
                                                    } else {
                                                        $error = 'invalid coords and/or address';
                                                    }    
                                                }
                                            } else {
                                                $error = 'invalid landmark type';
                                            }
                                        } else {
                                            $error = 'invalid landmark type';
                                        }
                                    } else {
                                        $error = 'invalid radius';    
                                    }
                                } else {
                                    $error = 'duplicate name';
                                }
                            } else {    // db error when trying to retreive landmark name
                                $error = 'db error - get landmark by title';
                            }
                        } else {
                            $error = 'invalid name';
                            $this->validator->clearError();
                        }

                        // save incomplete landmark
                        if ($error != '') {
                            $incomplete_landmarks_count++;
                            $params['reason'] = $error;
                            $params['user_id'] = $user_id;
                            
                            $this->territory_data->saveIncompleteTerritory($params);
                            $this->clearError();
                        }
                    }
$debug['error'] .= '<hr>' ;
                }

                // return $incomplete_landmarks_count;     // return incomplete landmarks counter
                $result['attempts'] = $process_landmarks_debug_loop;
                $result['complete'] = $complete_landmarks_count;
                $result['incomplete'] = $incomplete_landmarks_count;
                $result['debug'] = $process_landmarks_debug;
                $result['errorss'] = $debug['errorss'];
                $result['params'] = $debug;
                return $result;
            } else {
                $this->setError('err_file');
            }
        }

        $result['errorss'] = $debug['errorss'];
        return $result;
    }

    /**
     * Determines if the correct landmark upload template was used (check for valid headers)
     *
     * @param int       account_id
     * @param string    title
     * @param bool      include_units
     *
     * @return bool|array
     */
    public function getTerritoryByTitle($account_id, $title, $include_units = false)
    {
        if (! is_numeric($account_id) OR $account_id <= 0) {
            $this->setErrorMessage('err_account');
        }

        if (empty($title) OR strlen($title) < 3) {
            $this->setErrorMessage('err_title');
        }

        if ($include_units !== true AND $include_units !== false) {
            $this->setErrorMessage('err_include_units');
        }

        if (! $this->hasError()) {
            $landmark = $this->territory_data->getTerritoryByTitle($account_id, $title);
            if ($landmark !== false) {
                if (! empty($landmark)) {
                    $landmark = array_pop($landmark);
                    if (! empty($landmark['territory_id']) AND $include_units) {
                        $units = $this->territory_data->getUnitsByTerritoryId($landmark['territory_id']);
                        if ($units !== false) {
                            $landmark['units'] = $units;
                        }
                    }
                }
                return $landmark;
            }
        }

        return false;
    }

    /**
     * Get landmark by provided landmark_id
     *
     * @param int landmark_id
     *
     * @return bool|array
     */    
    public function getTerritoryByIds($territory_ids)
    {
        if (! is_array($territory_ids)) {
            $territory_ids = array($territory_ids);
        }

        if (count($territory_ids) == 0) {
            $this->setErrorMessage('err_id');
        }

        if (! $this->hasError()) {
            $territories = $this->territory_data->getTerritoryByIds($territory_ids);
            if ($territories !== false) {
                if (! empty($territories)) {
                    foreach($territories as $index => $territory) {

                        // get associated unit if a reference landmark
                        $territories[$index]['unit'] = $this->territory_data->getUnitTerritoryByTerritoryId($territory['territory_id']);

                        $territories[$index]['formatted_address'] = $this->address_logic->validateAddress($territory['streetaddress'], $territory['city'], $territory['state'], $territory['zipcode'], $territory['country']);
                        // convert feet to miles and format string to three places after decimal (will put in another component) 
                        $territories[$index]['radius_in_miles'] = Measurement::radiusFeetToFractionConverter($territory['radius']);

                        // get coordinates from boundingbox value
                        $territories[$index]['coordinates'] =  $this->getCoordinatesFromBoundingBoxValue($territory['boundingbox']);
                    }
                }

                return $territories;
            }
        }

        return false;
    }

    /**
     * Get unfound landmark by provided landmark_id
     *
     * @param int landmark_id
     *
     * @return bool|array
     */    
    public function getTerritoryUploadByIds($territory_ids)
    {
        if (! is_array($territory_ids)) {
            $territory_ids = array($territory_ids);
        }

        if (count($territory_ids) == 0) {
            $this->setErrorMessage('err_id');
        }
        
        if (! $this->hasError()) {
            $landmarks = $this->territory_data->getTerritoryUploadByIds($territory_ids);
            if ($landmarks !== false) {
                if (! empty($landmarks)) {
                    foreach($landmarks as $index => $landmark) {
                        // get associated unit if a reference landmark
                        $landmarks[$index]['unit'] = array();
                        if (isset($landmark['unit_id']) AND $landmark['unit_id'] != 0) {
                            $landmarks[$index]['unit'] = $this->vehicle_data->getVehicleInfo($landmark['unit_id']);
                        }

                        $landmarks[$index]['formatted_address'] = $this->address_logic->validateAddress($landmark['streetaddress'], $landmark['city'], $landmark['state'], $landmark['zipcode'], $landmark['country']);

                        // convert feet to miles and format string to three places after decimal (will put in another component) 
                        $landmarks[$index]['radius_in_miles'] = Measurement::radiusFeetToFractionConverter($landmark['radius']);     
                    }
                }
                return $landmarks;
            }
        }

        return false;
    }

    /**
     * Determines if the correct landmark upload template was used (check for valid headers)
     *
     * @param array
     *
     * @return bool
     */     
    public function validateTerritoryTemplate($row, $islandmarks_upload = true) {
        if (! empty($row)) {
            // if template is for Landmark upload, require landmarkgroupname column
            if ($islandmarks_upload) {
                return (array_keys($row) === array('name', 'street', 'city', 'state', 'zipcode', 'country', 'latitude', 'longitude', 'radius(miles)', 'landmarkgroupname'));
            }

            return (array_keys($row) === array('name', 'street', 'city', 'state', 'zipcode', 'country', 'latitude', 'longitude', 'radius(miles)'));    
        }
        return false;
    }
    
    /**
     * Determines if the correct landmark radius was uploaded
     *
     * @param int radius (in feet)
     *
     * @return bool
     */
    public function validateTerritoryRadius($radius) {
        if (is_numeric($radius) AND ! empty($radius)) {
            return in_array($radius, array(660, 1320, 2640, 5280, 15840, 26400));
        }
        return false;
    }
    
    /**
     * Determines if the correct landmark type was uploaded
     *
     * @param string type
     *
     * @return bool
     */
    public function validateTerritoryShape($type) 
    {
        if (! empty($type)) {
            return in_array($type, array('circle', 'square', 'rectangle', 'polygon'));
        }
        return false;
    }

    /**
     * Get landmark type sql
     *
     * @param string type
     * @param array coords 
     * FOR POLYGONS: 'coords' is an array of strings representing lat/lng pairs 
     * separated by a single space (i.e. array('12.456 -78.123', '33.566 -55.567', '12.456 -78.123'))
     * IMPORTANT (FOR POLYGON): Please make sure that the first lat/lng pair is also the last lat/lng pair in the array
     * FOR CIRCLES: 'coords' is an array containing a single element of type string, which represents the
     * lat/lng of the center of the circle (i.e. array('12.456 -45.234'))
     * @param int radius (in feet, required only for circle) 
     *
     * @return string
     */     
    public function getBoundingBoxValue($type, $coords = array(), $radius = 0) 
    {
        $ret = '';
        if (! empty($type) AND ! empty($coords)) {
            switch ($type) {
                case 'circle':
                    if (! empty($radius)) {
                        list($latitude, $longitude) = explode(' ', $coords[0]);
                        $geolocation = GeoLocation::fromDegrees($latitude, $longitude);
                        $radius = (float) sprintf('%01.3f', ($radius * 0.00018939393)); // convert feet to miles 
                        $coordinates = $geolocation->boundingCoordinates($radius, 'miles');
                        $tr = $coordinates[1]->getLatitudeInDegrees()." ".$coordinates[1]->getLongitudeInDegrees();
                        $tl = $coordinates[1]->getLatitudeInDegrees()." ".$coordinates[0]->getLongitudeInDegrees();
                        $br = $coordinates[0]->getLatitudeInDegrees()." ".$coordinates[1]->getLongitudeInDegrees();
                        $bl = $coordinates[0]->getLatitudeInDegrees()." ".$coordinates[0]->getLongitudeInDegrees();
                        $ret = "GEOMFROMTEXT('POLYGON(({$tl}, {$tr}, {$br}, {$bl}, {$tl}))')";
                    }
                    break;
                case 'square':
                case 'rectangle':
                case 'polygon':
                    $coords = implode(', ', $coords);
                    $ret = "GEOMFROMTEXT('POLYGON((" . $coords . "))')";
                    break;
            }
        }
        return $ret;
    }

    /**
     * Takes a boundingbox text value and returns and array of lat/lng pairs
     *
     * @param string $bbox_value
     * @return array
     */    
    public function getCoordinatesFromBoundingBoxValue($bbox_value)
    {
        $latlngs = array();

        if (! empty($bbox_value)) {
            $bbox_value = substr($bbox_value, 9, strlen($bbox_value) - 11);
            //print_rb($bbox_value);            
            $temp_coords = explode(',', $bbox_value);
            foreach($temp_coords as $index => $coord) {
                $latlng = explode(' ', $coord);
                $latlngs[] = array('latitude' => $latlng[0], 'longitude' => $latlng[1]);
            }

            // drop the last point since it's duplicate of the first one
            array_pop($latlngs);

        }
        //print_rb($latlngs);
        return $latlngs;
    }

    /**
     * Update the landmark info by landmark_id
     *
     * @params: landmark_id, params
     *
     * @return array
     */
    public function updateTerritoryInfo($territory_id, $params, $table)
    {
        if (! is_numeric($territory_id) OR $territory_id <= 0) {
            $this->setErrorMessage('err_id');
        }

        if (! is_array($params) OR count($params) <= 0) {
            $this->setErrorMessage('err_param');
        }

        if (empty($table)) {
            $this->setErrorMessage('err_param');
        } else if ($table == 'territory') {

            if (isset($params['latitude']) AND (! is_numeric($params['latitude']) OR empty($params['latitude']) OR ! is_numeric($params['longitude']) OR empty($params['longitude']))) {
                $this->setErrorMessage('err_coordinates');
            }

            if (isset($params['territoryname'])) {
                $this->validator->validate('territory_name', $params['territoryname']);
            }

            if (isset($params['radius']) AND (! is_numeric($params['radius']) OR $params['radius'] <= 0)) {
                $this->setErrorMessage('err_radius');
            }

            if (isset($params['territorytype']) AND $params['radius'] == '') {
                $this->setErrorMessage('err_landmark_type');
            }

            if (isset($params['country']) AND ! empty($params['country']) AND ($params['country'] == 'USA' AND empty($params['state']))) {
                $this->setErrorMessage('err_state');
            }

            if (isset($params['shape']) AND ! empty($params['shape'])) {
                if ($this->validateTerritoryShape($params['shape']) !== false) {
                    if (! isset($params['update_shape'])) { 
                        // if shape is a circle, a radius is required
                        if (($params['shape'] != 'circle') OR ($params['shape'] == 'circle' AND ! empty($params['radius']))) {
                            if (! empty($params['coordinates'])) {
                                $latlngs = array();
    
                                foreach($params['coordinates'] as $index => $coords) {
                                    $latlngs[] = $coords['latitude'] . ' ' . $coords['longitude'];
                                }
    
                                // if shape is not circle, add the first lat/lng as the last lat/lng to connect the polygon
                                if ($params['shape'] != 'circle') {
                                    $latlngs[] = $params['coordinates'][0]['latitude'] . ' ' . $params['coordinates'][0]['longitude'];
                                    if ($params['shape'] != 'square') {
                                        $params['streetaddress'] = $params['city'] = $params['state'] = $params['zipcode'] =  $params['country'] = '';
                                        $params['radius'] = NULL;
                                    }
                                }
    
                                $params['boundingbox'] = $this->getBoundingBoxValue($params['shape'], $latlngs, (! empty($params['radius']) ? $params['radius'] : 0));
                                unset($params['coordinates']);
                                //print_rb($params);
                            } else {
                                $this->setErrorMessage('err_landmark_coordinates');
                            }
                        } else {
                            $this->setErrorMessage('err_radius');
                        }
                    } else {
                        unset($params['update_shape']);
                        //proceed to update shape
                    }
                } else {
                    $this->setErrorMessage('err_landmark_shape');
                }
            }
        }
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            $ret = false;
            switch ($table) {
                case 'territory':
                    $ret = $this->territory_data->updateTerritoryInfo($territory_id, $params);
                    break;
                case 'territorygroup_territory':
                    $ret = $this->updateTerritorygroupTerritory($territory_id, $params);
                    break;
                default:
                    break;
            }
            return $ret;
        }

        return false;
    }

    /**
     * Update the landmarkgroup_landmark relations by landmark_id and params
     *
     * @params: landmark_id, params
     *
     * @return array
     */
    public function updateTerritorygroupTerritory($territory_id, $params)
    {
        if (! is_numeric($territory_id) OR $territory_id <= 0) {
            $this->setErrorMessage('err_unit');
        }
        
        if (! is_array($params) OR count($params) <= 0) {
            $this->setErrorMessage('err_param');
        } else {
            if (isset($params['territorygroup_id']) AND (! is_numeric($params['territorygroup_id']) OR $params['territorygroup_id'] <= 0)) {
                $this->setErrorMessage('err_param');
            }
        }

        if (! $this->hasError()) {
            // check if existing
            $landmarkgroup_landmark = $this->territory_data->getTerritorygroupTerritory($territory_id);
            if (! empty($landmarkgroup_landmark)) {
                if ($this->territory_data->updateTerritorygroupTerritory($territory_id, $params)) {
                    return true;
                }
            } else {
                $landmarkgroup_landmark_param['territorygroup_id'] = $params['territorygroup_id'];
                $landmarkgroup_landmark_param['territory_id'] = $territory_id;
                if ($this->territory_data->addTerritorygroupTerritory($landmarkgroup_landmark_param)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Update an incomplete landmark info by landmarkupload_id
     *
     * @params: landmarkupload_id, params
     *
     * @return array
     */
    public function updateIncompleteTerritory($territoryupload_id, $params, $table)
    {
        if (! is_numeric($territoryupload_id) OR $territoryupload_id <= 0) {
            $this->setErrorMessage('err_id');
        }

        if (! is_array($params) OR count($params) <= 0) {
            $this->setErrorMessage('err_param');
        } else {
            if (isset($params['territoryname'])) {
                $this->validator->validate('landmark_name', $params['territoryname']);
            }
        }

        if (empty($table)) {
            $this->setErrorMessage('err_param');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }
        
        if (! $this->hasError()) {
            if (isset($params['territorygroup_id']) AND $params['territorygroup_id'] != '') {
                // get landmarkgroup name
                $landmarkgroup = $this->territory_data->getTerritoryGroupById($params['territorygroup_id']);
                if ($landmarkgroup !== false) {
                    $params['territorygroupname'] = (isset($landmarkgroup[0]['territorygroupname']) AND $landmarkgroup[0]['territorygroupname'] != '') ? $landmarkgroup[0]['territorygroupname'] : '';
                    unset($params['territorygroup_id']);
                }
            }

            $ret = $this->territory_data->updateIncompleteTerritory($territoryupload_id, $params);

            return $ret;
        }

        return false;
    }

    /**
     * validate incomplete landmark info 
     *
     * POST params: field, $params
     *
     * @return void | error array
     */
    public function validateIncompleteTerritoryInfo($field = 'all', $params = array())
    {
        $error = array();

        // test for now
        $params['shape'] = 'circle';

        if (! is_array($params) OR empty($params)) {
            $this->setErrorMessage('err_landmark');    
        }

        if (! $this->hasError()) {

           switch ($field) {
                case 'landmark-name':
                    // 1st step - validate landmark name
                    $this->validator->validate('territory_name', $params['territoryname']);
                    if ($this->validator->hasError()) {
                        $validation_error = $this->validator->getErrorMessage();
                        if (! empty($validation_error) AND is_array($validation_error)) {
                            $validation_error = implode(',', $validation_error);
                            $error['landmark-name'] = $validation_error;
                        }
                    } else {
                        // check name for duplication
                        if (! empty($params['territoryname']) AND (strlen($params['territoryname']) >= 3)) {
                            $landmark = $this->getTerritoryByTitle($params['account_id'], $params['territoryname'], false);
    
                            if (! empty($landmark)) {
                                // landmark with name already exist, duplicate
                                $error['landmark-name'] = "Duplicate Landmark Name";
                            }
                        }
                    }
                    break;
                case 'landmark-group':

                    break;
                case 'landmark-type':

                    break;
                case 'landmark-radius':

                    // 2nd step - validate radius 
                    if ( !isset($params['radius']) OR empty($params['radius']) OR ($this->validateTerritoryRadius($params['radius']) === false)) {
                        // invalid radius
                        $error['landmark-radius']   = "Invalid Radius";
                    }

                    break;
                case 'landmark-shape':

                    // 3rd step - validate landmark type (shape)
                    if ( ! isset($params['shape']) OR ! $this->validateTerritoryShape($params['shape']) ) {
                        // invalid landmark shape
                        $error['landmark-shape']    = "Invalid Shape";
                    }

                    break;
                case 'landmark-address':

                    // 4th step - create bounding box
                    if (! isset($params['boundingbox']) OR $params['boundingbox'] == '') {

                        //$error['landmark-location'] = 'Invalid landmark Shape and/or Coordinates.';
                    }

                    // 5th step - validate coordinates
                    $valid_address  = true;
                    $valid_coords   = true;
                    if (empty($params['latitude'])          OR 
                        ! is_numeric($params['latitude'])   OR 
                        empty($params['longitude'])         OR 
                        ! is_numeric($params['longitude'])) {

                        // invalid coordinates 'requires geo';
                        $error['landmark-location']    = 'Invalid Coordinates.';
                        $valid_coords                   = false;
                    }

                    // 6th step - validate address (fails validation if it hasn't been rgeo and has missing address components)
                    if ((empty($params['streetaddress'])  OR 
                        empty($params['city'])            OR 
                        empty($params['state'])           OR 
                        empty($params['zipcode'])         OR 
                        empty($params['country'])) AND $params['process'] == 0) {
                        
                        // invalid address 'requires rgeo';
                        $error['landmark-location']  = 'Invalid Address.';
                        $valid_address              = false;
                    }

                    break;
                case 'landmark-map-click':


                    break;
                case 'all':

                    // 1st step - validate landmark name
                    $this->validator->validate('territory_name', $params['territoryname']);
                    if ($this->validator->hasError()) {
                        $validation_error = $this->validator->getErrorMessage();
                        if (! empty($validation_error) AND is_array($validation_error)) {
                            $validation_error = implode(',', $validation_error);
                            $error['landmark-name'] = $validation_error;
                        }
                    } else {
                        // check name for duplication
                        if (! empty($params['territoryname']) AND (strlen($params['territoryname']) >= 3)) {
                            $landmark = $this->getTerritoryByTitle($params['account_id'], $params['territoryname'], false);
    
                            if (! empty($landmark)) {
                                // landmark with name already exist, duplicate
                                $error['landmark-name'] = "Duplicate Landmark Name";
                            }
                        }
                    }

                    // 2nd step - validate radius 
                    if ( !isset($params['radius']) OR empty($params['radius']) OR ($this->validateTerritoryRadius($params['radius']) === false)) {
                        // invalid radius
                        $error['landmark-radius']   = "Invalid Radius";
                    }

                    // 3rd step - validate landmark type (shape)
                    if ( ! isset($params['shape']) OR ! $this->validateTerritoryShape($params['shape']) ) {
                        // invalid landmark shape
                        $error['landmark-shape']    = "Invalid Shape";
                    }

                    // 4th step - create bounding box
                    if (! isset($params['boundingbox']) OR $params['boundingbox'] == '') {
                    
                        //$error['landmark-location'] = 'Invalid landmark Shape and/or Coordinates.';
                    }

                    // 5th step - validate coordinates
                    $valid_address  = true;
                    $valid_coords   = true;
                    if (empty($params['latitude'])          OR 
                        ! is_numeric($params['latitude'])   OR 
                        empty($params['longitude'])         OR 
                        ! is_numeric($params['longitude'])) {

                        // invalid coordinates 'requires geo';
                        $error['landmark-location']    = 'Invalid Coordinates.';
                        $valid_coords                   = false;
                    }

                    // 6th step - validate address (fails validation if it hasn't been rgeo and has missing address components)
                    
                    if ((empty($params['streetaddress'])  OR 
                        empty($params['city'])            OR 
                        empty($params['state'])           OR 
                        empty($params['zipcode'])         OR 
                        empty($params['country'])) AND $params['process'] == 0) {

                        // invalid address 'requires rgeo';
                        $error['landmark-location']  = 'Invalid Address.';
                        $valid_address              = false;
                    }

                break;

                default:

                break;
            }

            if (! empty($error)) {
                return $error;
            }
        }

        return true;
    }

    /**
     * Save an incomplete landmark information (called via ajax) 
     *
     * POST params: incompletelandmark_id, params
     *
     * @return void
     */    
    public function saveIncompleteToTerritory($account_id, $user_id, $incompleteterritory_id, $params)
    {
        $transfer_success = false;

        if (! is_numeric($incompleteterritory_id) OR $incompleteterritory_id <= 0) {
            $this->setErrorMessage('err_id');
        }

        if (! is_array($params) OR empty($params)) {
            $this->setErrorMessage('err_landmark');    
        }

        if (! $this->hasError()) {

            // validate on default fields
            $validated = $this->validateIncompleteTerritoryInfo('all', $params);
            if ($validated === true) {
                
                if (isset($params['process'])) {
                    unset($params['process']);
                }
                
                // create new landmark
                $landmark_id = $this->territory_data->saveTerritory($params);
                if ($landmark_id !== false) {
                    $transfer_success   = true;
                    
                    // if reference landmark (unit_id provided), create unit to landmark association
                    if (isset($params['unit_id']) AND $params['unit_id'] != 0) {
                        unset($params['territorygroupname']);
                        //$params['reference'] = 1;
                        $params['territorytype'] = 'reference';
                        $this->addTerritoryToVehicle($params['unit_id'], $landmark_id);
                    } else {
                        $this->territory_data->setTerritoryType(array($params['territorytype']));
                        $default_group = $this->territory_data->getTerritoryDefaultGroup($account_id);
                        $default_group_id = ! empty($default_group) ? $default_group['territorygroup_id'] : 0;
                        $this->territory_data->resetTerritoryType(); 
                    }
    
                    // for landmarks, landmarkgroup_id is required. default group if none provided
                    if ($params['territorytype'] == 'landmark') {
                        if (isset($params['territorygroupname']) AND $params['territorygroupname'] != '' ) {
                            // check for landmarkgroupname to get landmarkgroup_id, if not exist, create a new landmarkgroup and return that
                            $landmarkgroup = $this->getTerritoryGroupByTitle($params['account_id'], $params['territorygroupname']);
                            if (! empty($landmarkgroup) AND isset($landmarkgroup['territorygroup_id']) AND $landmarkgroup['territorygroup_id'] > 0) {
                                $params['territorygroup_id'] = $landmarkgroup['territorygroup_id'];
                            } else {
                                // create new landmarkgroup for this new landmarkgroupname
                                $landmarkgroup_id = $this->addTerritoryGroup($params['account_id'], $params['territorygroupname']);
                                if ($landmarkgroup_id) {
                                    $params['territorygroup_id'] = $landmarkgroup_id;
                                    // associate the new territorygroup to the user
                                    if (isset($user_id) AND ! empty($user_id)) {
                                        $this->addUserTerritoryGroup($user_id, $landmarkgroup_id);
                                    }
                                }
                            }
                        } else {
                            // assign to account default landmarkgroup
                            //$params['territorygroup_id'] = 0;            
                            
                            // get account default landmarkgroup_id here
                            /*
                            $default_landmarkgroup_id = $this->getDefaultTerritoryGroup($account_id, 'landmark');
                            if ($default_landmarkgroup_id) {
                                $params['territorygroup_id'] = $default_landmarkgroup_id;
                            }*/
                            $params['territorygroup_id'] = $default_group_id;
                        }

                        // add landmarkgroup_landmark association
                        $landmarkgroup_landmark['territorygroup_id'] = $params['territorygroup_id'];
                        $landmarkgroup_landmark['territory_id']      = $landmark_id;
                        
                        $this->addTerritorygroupTerritory($landmarkgroup_landmark);
                    }
                }

                // Delete the incomplete landmark if transfer to landmark successful
                if ($transfer_success) {
                    $this->territory_data->deleteTerritoryUpload($incompleteterritory_id, $params['account_id']);
                }

                return true;
            } else {
                return $validated;
            }
        }

        return false;
    }

    /**
     * Get Default landmarkgroup info by account and type 
     *
     * @param int       account_id
     * @param string    type
     *
     * @return bool|array
     */    
    public function getDefaultTerritoryGroup($account_id, $territorytype = '')
    {
        if (! is_numeric($account_id) OR $account_id <= 0) {
            $this->setErrorMessage('Invalid Account Info');
        }
        
        if (! $this->hasError()) {
            $default_landmark = $this->territory_data->getDefaultTerritoryGroup($account_id, $territorytype);
            if ($default_landmark !== false) {
                if (! empty($default_landmark)) {
                    $default_landmark_id = $default_landmark['territorygroup_id'];
                }
                return $default_landmark_id;
            }   
        }
        
        return false;   
    }

    /**
     * Get landmarkgroup info by title
     *
     * @param int       account_id
     * @param string    title
     *
     * @return bool|array
     */    
    public function getTerritoryGroupByTitle($account_id, $title)
    {
        if (! is_numeric($account_id) OR $account_id <= 0) {
            $this->setErrorMessage('err_account');
        }
        
        if (empty($title) OR strlen($title) < 3) {
            $this->setErrorMessage('err_title');    
        }
        
        if (! $this->hasError()) {
            $landmark = $this->territory_data->getTerritoryGroupByTitle($account_id, $title);
            if ($landmark !== false) {
                if (! empty($landmark)) {
                    $landmark = array_pop($landmark);
                }
                return $landmark;
            }   
        }
        
        return false;   
    }

    /**
     * Add landmarkgroup
     *
     * @param int       account_id
     * @param string    title
     *
     * @return bool|array
     */    
    public function addTerritoryGroup($account_id, $title, $type = 'landmark')
    {
        $this->validator->validate('record_id', $account_id);

        $this->validator->validate('group_name', $title);
        
        // if group name has valid characters, check for group name duplication
        if (! $this->validator->hasError()) {
            $duplicate = $this->territory_data->getTerritoryGroupByTitle($account_id, $title);
            if (! empty($duplicate)) {
                $this->setErrorMessage('Duplicated Group Name');
            }                
        }
        
        if (empty($type)) {
            $this->setErrorMessage('Invalid Type');
        } else {
            if (! in_array($type, array('landmark', 'reference', 'boundary'))) {
                $this->setErrorMessage('Invalid Type');
            }
        }
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }
        
        if (! $this->hasError()) {
        
            $params['account_id']           = $account_id;
            $params['territorygroupname']   = $title;
            $params['active']               = 1;
            $params['territorytype']        = $type;
            
            $landmarkgroup_id = $this->territory_data->addTerritoryGroup($params);
            if ($landmarkgroup_id) {
                return $landmarkgroup_id;
            }   
        }
        
        return false;   
    }

    /**
     * Add user territorygroup association
     *
     * @param int       account_id
     * @param string    title
     *
     * @return bool|array
     */    
    public function addUserTerritoryGroup($user_id, $landmarkgroup_id)
    {
        if (! is_numeric($user_id) OR $user_id <= 0) {
            $this->setErrorMessage('Invalid User ID');
        }
        
        if (! is_numeric($landmarkgroup_id) OR $landmarkgroup_id <= 0) {
            $this->setErrorMessage('Invalid Group ID');
        }
        
        if (! $this->hasError()) {
        
            $params['user_id']              = $user_id;
            $params['territorygroup_id']    = $landmarkgroup_id;
            
            return $this->territory_data->addUserTerritoryGroup($params);
        }
        return false;   
    }

    /**
     * Add landmarkgroup to landmark association
     *
     * @param int       account_id
     * @param string    title
     *
     * @return bool|array
     */    
    public function addTerritorygroupTerritory($params)
    {
        if (! is_numeric($params['territorygroup_id']) OR $params['territorygroup_id'] <= 0) {
            $this->setErrorMessage('err_group');
        }
        
        if (! is_numeric($params['territory_id']) OR $params['territory_id'] <= 0) {
            $this->setErrorMessage('err_landmark');
        }
        
        if (! $this->hasError()) {
            $landmarkgroup = $this->territory_data->addTerritorygroupTerritory($params);
            if ($landmarkgroup) {
                if (! empty($landmark)) {
                    $landmarkgroup = array_pop($landmarkgroup);
                }
                return $landmarkgroup;
            }   
        }
        
        return false;   
    }

    /**
     * Get the incomplete landmark list by provided params
     *
     * @params: int $user_id
     * @params: array $params
     *
     * @return array
     */
    public function getIncompleteTerritoryList($account_id, $params)
    {
        $total_landmarks = array();
        $landmarks['iTotalRecords']          = 0;
        $landmarks['iTotalDisplayRecords']   = 0;
        $landmarks['data']                   = array();
        
        $this->validator->validate('record_id', $account_id);

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('err_params');
        } else {
            // validate search string (need to handle displaying the error message in the UI)
            if (isset($params['search_string']) AND $params['search_string'] !== '') {
                $this->validator->validate('alphanumeric', $params['search_string']);
            }
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
    
            switch ($params['filter_type']) {
                
                case 'string_search':
                
                    $searchfields = array('territoryname');
                    $landmarks = $this->territory_data->getIncompleteTerritoryStringSearch($account_id, $params, $searchfields);
                    if ($landmarks !== false) {
                        $total_landmarks = $landmarks;
                    }
                                 
                break;
                
                case 'group_filter':
                
                    if (isset($params['territorygroup_id']) AND strtolower($params['territorygroup_id']) == 'all') {
                        $params['territorygroup_id'] = array();
                    } elseif (! is_array($params['territorygroup_id'])) {
                        $params['territorygroup_id'] = array($params['territorygroup_id']);
                    }

                    if (isset($params['territorytype']) AND strtolower($params['territorytype']) == 'all') {
                        $params['territorytype'] = '';
                    }

                    if (isset($params['landmark_reason']) AND $params['landmark_reason'] != 'All' AND $params['landmark_reason'] == 'name') {
                        $params['reason'] = 'name';
                    }
                    
                    $landmarks = $this->territory_data->getFilteredIncompleteTerritory($account_id, $params);
                    if ($landmarks !== false) {
                        $total_landmarks = $landmarks;
                    }
                
                break;
                
                default:

                break;
            }
    
            // for the formatted unit events, process for datatable return results
            if (! empty($total_landmarks)) {

                // init total results
                $iTotal                             = count($total_landmarks);
                $iFilteredTotal                     = count($total_landmarks);
                $landmarks['iTotalRecords']         = $iTotal;
                $landmarks['iTotalDisplayRecords']  = $iFilteredTotal;
                $aColumns                           = array();        // datatable columns event field/key names
                
                for ( $i = 0 ; $i < intval( $params['iColumns'] ) ; $i++ ) {
                    $aColumns[] = $params['mDataProp_'.$i];
                }

                // if doing a string search in filter search box
                if ( isset($params['string_search']) AND $params['string_search'] != "" ) {
                    $total_landmarks = $this->filterTerritoryListStringSearch($params['string_search'], $aColumns, $total_landmarks);
                    $iTotal         = count($total_landmarks);
                    $iFilteredTotal = count($total_landmarks);
                }

                $landmarks['iTotalRecords'] = $iTotal;
                $landmarks['iTotalDisplayRecords'] = $iFilteredTotal;
        
                $formatted_results = array();
                if (! empty($total_landmarks)) {
                    foreach ($total_landmarks as $landmark) {
                        $row = $landmark;
                        $row['formatted_address'] = $this->address_logic->validateAddress($row['streetaddress'], $row['city'], $row['state'], $row['zipcode'], $row['country']);
                        $row['radius_in_miles'] = Measurement::radiusFeetToFractionConverter($row['radius']);

                        $row['DT_RowId']           = 'incomplete-tr-'.$row['territoryupload_id'];       // automatic tr id value for dataTable to set

                        if ($row['territorygroupname'] == '' OR is_null($row['territorygroupname'])){
                            $row['territorygroupname'] = $params['default_value'];
                        }

                        $formatted_results[] = $row;
                    }

                    // if doing a column sorting
                    if ( isset( $params['iSortCol_0'] ) AND $params[ 'bSortable_'.intval($params['iSortCol_0']) ] == "true")
                    {
                        $formatted_results = $this->filterTerritoryListSort($aColumns[ intval($params['iSortCol_0']) ], ($params['sSortDir_0']==='asc' ? 'asc' : 'desc'), $formatted_results);
                    }

                    // if doing paging, find correct page list
                    if ( isset($params['iDisplayStart']) AND $params['iDisplayLength'] != '-1' )
                    {
                        $formatted_results = array_splice($formatted_results, $params['iDisplayStart'], $params['iDisplayLength']);
                    }
                }

                $landmarks['data'] = $formatted_results;
            }

            //return $landmarks;
        }

        return $landmarks;
    }

    /**
     * Get the verification addresses
     *
     * @params: int $account_id
     *
     * @return array
     */
    public function getVerificationTerritoryList($user_id, $params)
    {
        $total_vaddress = array();
        $vaddress['iTotalRecords']          = 0;
        $vaddress['iTotalDisplayRecords']   = 0;
        $vaddress['data']                   = array();
        
        $this->validator->validate('record_id', $user_id);

        if (! is_array($params) OR empty($params)) {
            $this->setErrorMessage('Invalid Parameters');
        } else {
            // validate search string (need to handle displaying the error message in the UI)
            if (isset($params['string_search']) AND $params['string_search'] !== '') {
                $this->validator->validate('alphanumeric', $params['string_search']);
            }            
        }
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
    
            switch ($params['filter_type']) {
                
                case 'string_search':
                
                    $searchfields = array('territoryname');
                    $vaddress = $this->territory_data->getFilteredVerificationTerritoryStringSearch($user_id, $params, $searchfields);
                    if ($vaddress !== false) {
                        $total_vaddress = $vaddress;
                    }
                                 
                break;
                
                case 'group_filter':

                    if (isset($params['verified']) AND strtolower($params['verified']) == 'all') {
                        $params['verified'] = '';
                    }

                    if (isset($params['vehicle_id']) AND strtolower($params['vehicle_id']) == 'all') {
                        $params['vehicle_id'] = '';
                    }

                    $vaddress = $this->territory_data->getFilteredVerificationTerritory($user_id, $params);
                    if ($vaddress !== false) {
                        $total_vaddress = $vaddress;
                    }
                
                break;
                
                default:

                break;
            }
    
            // for the formatted unit events, process for datatable return results
            if (! empty($total_vaddress)) {

                // init total results
                $iTotal                             = count($total_vaddress);
                $iFilteredTotal                     = count($total_vaddress);
                $vaddress['iTotalRecords']         = $iTotal;
                $vaddress['iTotalDisplayRecords']  = $iFilteredTotal;
                $aColumns                           = array();        // datatable columns event field/key names
                
                for ( $i = 0 ; $i < intval( $params['iColumns'] ) ; $i++ ) {
                    $aColumns[] = $params['mDataProp_'.$i];
                }

                // if doing a string search in filter search box
                if ( isset($params['string_search']) AND $params['string_search'] != "" ) {
                    $total_vaddress = $this->filterTerritoryListStringSearch($params['string_search'], $aColumns, $total_vaddress);
                    $iTotal         = count($total_vaddress);
                    $iFilteredTotal = count($total_vaddress);
                }

                $vaddress['iTotalRecords'] = $iTotal;
                $vaddress['iTotalDisplayRecords'] = $iFilteredTotal;
        
                $formatted_results = array();
                if (! empty($total_vaddress)) {
                    foreach ($total_vaddress as $address) {
                        $row = $address;
                        $row['formatted_address'] = $this->address_logic->validateAddress($row['streetaddress'], $row['city'], $row['state'], '', $row['country']);
                        $row['radius_in_miles'] = Measurement::radiusFeetToFractionConverter($row['radius']);

                        $row['DT_RowId']           = 'landmark-tr-'.$row['territory_id'];       // automatic tr id value for dataTable to set

                        // format latitude/longitude to only display 5 numbers after the decimal
                        $row['formatted_latitude'] = sprintf('%01.5f', $row['latitude']);
                        $row['formatted_longitude'] = sprintf('%01.5f', $row['longitude']);
    
                        if ($row['verifydate'] !== '0000-00-00') {
                            $formatted_verified_date = Date::utc_to_locale($row['verifydate'], $params['user_timezone'], 'm/d/Y');
                            $verified = 1;   
                        } else {
                            $formatted_verified_date = '';
                            $verified = 0;
                        }
    
                        $row['formatted_verified_date'] = $formatted_verified_date;
                        $row['verified'] = $verified;

                        $formatted_results[] = $row;
                    }

                    // if doing a column sorting
                    if ( isset( $params['iSortCol_0'] ) AND $params[ 'bSortable_'.intval($params['iSortCol_0']) ] == "true")
                    {
                        $formatted_results = $this->filterTerritoryListSort($aColumns[ intval($params['iSortCol_0']) ], ($params['sSortDir_0']==='asc' ? 'asc' : 'desc'), $formatted_results);
                    }

                    // if doing paging, find correct page list
                    if ( isset($params['iDisplayStart']) AND $params['iDisplayLength'] != '-1' )
                    {
                        $formatted_results = array_splice($formatted_results, $params['iDisplayStart'], $params['iDisplayLength']);
                    }
                }

                $vaddress['data'] = $formatted_results;
            }

            //return $vaddress;
        }

        return $vaddress;
    }

    /**
     * Get the landmark groups
     *
     * @param int user_id 
     * @param array params
     *
     * @return array
     */
    public function getTerritoryGroupList($user_id, $params)
    {
        $total_groups = array();
        $territorygroups['iTotalRecords']          = 0;
        $territorygroups['iTotalDisplayRecords']   = 0;
        $territorygroups['data']                   = array();

        $this->validator->validate('record_id', $user_id);
        
        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('Invalid Parameters');
        } else {
            // validate search string (need to handle displaying the error message in the UI)
            if (isset($params['string_search']) AND $params['string_search'] !== '') {
                $this->validator->validate('alphanumeric', $params['string_search']);
            }            
        }
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {

            $searchfields = array();
            $string_search = '';

            if (! empty($params['string_search'])) {                
                $searchfields = array('territorygroupname');
                $string_search = $params['string_search'];
            }

            $groups = $this->territory_data->getTerritoryGroupListStringSearch($user_id, $params['string_search'], $searchfields);
            
            if ($groups !== false AND ! empty($groups) AND $groups[0]['territorygroup_id'] !== NULL) {
                $total_groups = $groups;
            }

            // for the formatted unit events, process for datatable return results
            if (! empty($total_groups)) {

                // init total results
                $iTotal                                     = count($total_groups);
                $iFilteredTotal                             = count($total_groups);
                $territorygroups['iTotalRecords']           = $iTotal;
                $territorygroups['iTotalDisplayRecords']    = $iFilteredTotal;
                $aColumns                                   = array();        // datatable columns event field/key names
                
                for ( $i = 0 ; $i < intval( $params['iColumns'] ) ; $i++ ) {
                    $aColumns[] = $params['mDataProp_'.$i];
                }

                $territorygroups['iTotalRecords'] = $iTotal;
                $territorygroups['iTotalDisplayRecords'] = $iFilteredTotal;
        
                $formatted_results = array();
                if (! empty($total_groups)) {
                    foreach ($total_groups as $group) {
                        $row = $group;
                        $row['DT_RowId'] = 'landmarkgroup-tr-'.$row['territorygroup_id'];       // automatic tr id value for dataTable to set

                        if ($row['territorygroupname'] == '' OR is_null($row['territorygroupname'])){
                            $row['territorygroupname'] = $params['default_value'];
                        }
                        
                        // get territories for each group
                        $row['territory_count'] = $this->territory_data->getTerritoryByGroupIds($user_id, array($row['territorygroup_id']));
                        $row['territory_count'] = (! empty($row['territory_count'])) ? count($row['territory_count']) : 0;

                        $formatted_results[] = $row;
                    }

                    // if doing a column sorting
                    if ( isset( $params['iSortCol_0'] ) AND $params[ 'bSortable_'.intval($params['iSortCol_0']) ] == "true")
                    {
                        $formatted_results = $this->filterTerritoryListSort($aColumns[ intval($params['iSortCol_0']) ], ($params['sSortDir_0']==='asc' ? 'asc' : 'desc'), $formatted_results);
                    }

                    // if doing paging, find correct page list
                    if ( isset($params['iDisplayStart']) AND $params['iDisplayLength'] != '-1' )
                    {
                        $formatted_results = array_splice($formatted_results, $params['iDisplayStart'], $params['iDisplayLength']);
                    }
                }

                $territorygroups['data'] = $formatted_results;
            }

            //return $vaddress;
        }

        return $territorygroups;
    }
    
    /**
     * Get all territory categories
     *
     * @return array
     */
    public function getAllTerritoryCategories()
    {
        $categories = $this->territory_data->getAllTerritoryCategories();
        if ($categories === false) {
            $categories = array();
        }
        return $categories;    
    }

    public function getUnitTerritoryByTerritoryId($territory_id)
    {
        $unit = $this->territory_data->getUnitTerritoryByTerritoryId($territory_id);
        if ($unit === false) {
            $unit = array();
        }
        
        return $unit;
    }
    
    public function getUnitTerritoryByUserId($user_id)
    {
        $unit = $this->territory_data->getUnitTerritoryByUserId($user_id);
        if ($unit === false) {
            $unit = array();
        }
        
        return $unit;
    }
    
    /**
     * Set the current territory type(s)
     *
     * @param array|string territory_type
     *
     * @return bool
     */    
     public function setTerritoryType($territory_type)
     {
         if (! is_array($territory_type)) {
             $territory_type = array($territory_type);
         }
         
         if (count($territory_type) <= 0) {
             $this->setErrorMessage('err_invalid_territory_type');
         } else {
             foreach ($territory_type as $index => $type) {
                if (! in_array($type, array('landmark','boundary','reference'))) {
                    unset($territory_type[$index]);
                }    
             }
         }
         
         if (! $this->hasError()) {
             return $this->territory_data->setTerritoryType($territory_type);
         }
         
         return false;
     }
     
    /**
     * Get the current territory type(s)
     *
     * @return array
     */
     public function getTerritoryType()
     {
         return $this->territory_data->getTerritoryType();
     }
     
    /**
     * Reset the territory types to default
     *
     * @return void
     */
     public function resetTerritoryType()
     {
         return $this->territory_data->resetTerritoryType();
     }

    /**
     * Add territory group to user
     * 
     * @param territorygroup_id
     * @param user_id
     *
     * @return bool
     */
    public function addTerritoryGroupToUser($territorygroup_id, $user_id)
    {
        if (! is_numeric($territorygroup_id) OR $territorygroup_id <= 0) {
            $this->setErrorMessage('Invalid territory group id');    
        }
        
        if (! is_numeric($user_id) OR $user_id <= 0) {
            $this->setErrorMessage('Invalid user id');    
        }
        
        if (! $this->hasError()) {
            return $this->territory_data->addTerritoryGroupToUser($territorygroup_id, $user_id);    
        }
        return false;
    }

    /**
     * Remove territory group from user
     * 
     * @param territorygroup_id
     * @param user_id
     *
     * @return bool
     */
    public function removeTerritoryGroupFromUser($territorygroup_id, $user_id)
    {
        if (! is_numeric($territorygroup_id) OR $territorygroup_id <= 0) {
            $this->setErrorMessage('Invalid territory group id');    
        }
        
        if (! is_numeric($user_id) OR $user_id <= 0) {
            $this->setErrorMessage('Invalid user id');    
        }
        
        if (! $this->hasError()) {
            return $this->territory_data->removeTerritoryGroupFromUser($territorygroup_id, $user_id);    
        }
        return false;
    }

    /**
     * Get the landmark groups info by ids
     *
     * @params: user_id, group_id
     *
     * @return array | bool
     */
    public function getTerritoryGroupsById($user_id, $group_id, $include_territories = false)
    {
        if (! is_numeric($user_id) OR $user_id <= 0) {
            $this->setErrorMessage('err_user');
        }
        
        if (! is_array($group_id)) {
            $group_id = array($group_id);
        }

        if (count($group_id) < 0) {
            $this->setErrorMessage('err_param');
        }
        
        if ($include_territories !== true AND $include_territories !== false) {
            $this->setErrorMessage('err_invalid_territory_indicator');
        }

        if (! $this->hasError()) {
        
            $groups = $this->territory_data->getTerritoryGroupsByIds($user_id, $group_id);

            // if include territories is true, create assigned and available territories lists
            if ($include_territories === true) {                
                if (! empty($groups)) {
                    $this->user_logic = new UserLogic;
                    $userdata = $this->user_logic->getUserById($user_id);
                                        
                    $available_territories = array();
                    $new_groups = array(
                        'groups' => array(),
                        'defaultgroup_id' => 0
                    );
                    
                    $default_group = $this->territory_data->getTerritoryDefaultGroup($userdata[0]['account_id']);
                    if (! empty($default_group) AND ! empty($default_group['territorygroup_id'])) {
                        
                        // if user has access to the default group, use the territories in there as the available territories
                        $territorygroup_exist = $this->territory_data->getTerritoryGroupsByUserId($user_id, array($default_group['territorygroup_id']));
                        if (! empty($territorygroup_exist)) {  
                            $available_territories = $this->territory_data->getTerritoryByGroupIds($user_id, array($default_group['territorygroup_id']));
                            $new_groups['defaultgroup_id'] = $default_group['territorygroup_id'];   
                        }
                    }
                    
                    // iterate through the unit groups and get all vehicles for each group
                    foreach ($groups as $index => $tg) {
                        // reindex the array of groups in order to avoid having to constantly check for default group id
                        if (! isset($new_groups['groups'][$tg['territorygroup_id']])) {
                            $tg['assigned_territories'] = $this->territory_data->getTerritoryByGroupIds($user_id, array($tg['territorygroup_id']));
                            $tg['available_territories'] = $available_territories;
                            $new_groups['groups'][$tg['territorygroup_id']] = $tg;
                        }
                    }
                    
                    // if the default group is the array of groups, set its available territories to none (default group shouldn't have any available territories)
                    if (! empty($default_group) AND ! empty($default_group['territorygroup_id']) AND isset($new_groups['groups'][$default_group['territorygroup_id']])) {
                        $new_groups['groups'][$default_group['territorygroup_id']]['available_territories'] = array();        
                    }
                    
                    $groups = $new_groups;
                }
            }

            return $groups;
        }

        return false;
    }

    /**
     * Update landmark group info by landmark group id
     *
     * @params: landmarkgroup_id, params
     *
     * @return array
     */
    public function updateTerritoryGroupInfo($territorygroup_id, $params)
    {
        $this->validator->validate('record_id', $territorygroup_id);
        
        if (! is_array($params) OR count($params) <= 0) {
            $this->setErrorMessage('err_param');
        } else {
            if (isset($params['territorygroupname'])) {
                $this->validator->validate('group_name', $params['territorygroupname']);
                
                // if group name has valid characters, check for group name duplication
                if (! $this->validator->hasError()) {
                    $duplicate = $this->territory_data->getTerritoryGroupByTitle($params['account_id'], $params['territorygroupname']);
                    if (! empty($duplicate)) {
                        $this->setErrorMessage('Duplicated Group Name');
                    }                
                }
            }
            
            if (isset($params['active']) AND $params['active'] !== 1 AND $params['active'] !== 0) {
                $this->setErrorMessage('err_invalid_active_value');
            }
        }
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }
        
        if (! $this->hasError()) {
            if ($this->territory_data->updateTerritoryGroupInfo($territorygroup_id, $params) !== false) {
                // if deleting territory group, remove all territories from this group
                if (isset($params['active']) AND $params['active'] === 0) {
                    $this->territory_data->removeAllTerritoriesFromGroup($territorygroup_id);

                    // should we remove user_territorygroup association?
                }
                return true;
            }
        }

        return false;
    }

    /**
     * Get the territory info by filtered paramaters
     *
     * @params: account_id, params
     *
     * @return array
     */
    public function getFilteredAvailableTerritories($account_id, $params = array())
    {
        $this->validator->validate('record_id', $account_id);
        
        if (empty($params)) {
            $this->setErrorMessage('Empty search parameter');
        } else {
            // validate search string (need to handle displaying the error message in the UI)
            if (isset($params['search_string']) AND $params['search_string'] !== '') {
                $this->validator->validate('alphanumeric', $params['search_string']);
            }            
        }
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
        
            $search_fields = $data = array();
            $search_string = '';
            $territorygroup_id = 0;
            
            if (isset($params['search_string'])) {
                $search_fields[] = 'territoryname';
                $search_string = $params['search_string'];
            }
 
             if (isset($params['territorygroup_id']) AND $params['territorygroup_id'] != 0) {
                $territorygroup_id = $params['territorygroup_id'];
            }
            
            // get all available territories by account id and search string
            $territories = $this->territory_data->getFilteredAvailableTerritories($account_id, $territorygroup_id, $search_string, $search_fields);
            
            if ($territories !== false) {
                $data = $territories;    
            }

            return $data;
        }

        return false;
    }

    /**
     * Get territories by account id
     *
     * @params: account_id
     *
     * @return bool|array
     */
    public function getTerritoriesByAccountId($account_id)
    {
        if (! is_numeric($account_id) OR $account_id <= 0) {
            $this->setErrorMessage('Invalid Account');
        }

        if (! $this->hasError()) {

            // get all available territories by account id and search string
            $territories = $this->territory_data->getTerritoriesByAccountId($account_id);
            
            if ($territories === false) {
                $territories = array();    
            }
            return $territories;
        }
        return false;
    }
    
    /**
     * Get reference territories by unit id, group id, or account id
     *
     * @param int account_id
     * @param array params
     *
     * @return bool|array
     */
    public function getVerificationOfReferenceReport($account_id, $params)
    {
        if (! is_numeric($account_id) OR $account_id <= 0) {
            $this->setErrorMessage('Invalid Account');
        }
        
        if (isset($params['vehicle_id']) AND (! is_numeric($params['vehicle_id']) OR $params['vehicle_id'] <= 0)) {
            $this->setErrorMessage('Invalid Vehicle Id');    
        }
        
        if (isset($params['vehiclegroup_id']) AND (! is_numeric($params['vehiclegroup_id']) OR $params['vehiclegroup_id'] <= 0)) {
            $this->setErrorMessage('Invalid Vehicle Group Id');    
        }
        
        if (! $this->hasError()) {
            return $this->territory_data->getVerificationOfReferenceReport($account_id, $params);    
        }
        return false;
    }

    /**
     * Get the territory default group by account id
     *
     * @params: account_id
     *
     * @return array | bool
     */
    public function getTerritoryDefaultGroup($account_id)
    {
        if (! is_numeric($account_id) OR $account_id <= 0) {
            $this->setErrorMessage('Invalid account id');
        }

        if (! $this->hasError()) {
            return $this->territory_data->getTerritoryDefaultGroup($account_id);
        }
        return false;        
    }

    /**
     * Get unprocess incomplete territories to be geo/rgeo by cron
     *
     * @return array
     */
    public function getIncompleteTerritoriesForProcess()
    {
        return $this->territory_data->getIncompleteTerritoriesForProcess();
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
