<?php

namespace Models\Logic;

use Models\Logic\BaseLogic;

use Models\Data\LandmarkData;

use GTC\Component\Utils\Date;
use GTC\Component\Utils\Arrayhelper;
use GTC\Component\Utils\Measurement;
use GTC\Component\Utils\CSV\CSVReader;

use AnthonyMartin\GeoLocation\GeoLocation as GeoLocation;

class LandmarkLogic extends BaseLogic
{
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();

        $this->landmark_data = new LandmarkData;
        $this->address_logic = new AddressLogic;
    }

    /**
     * Get the landmark groups info by user id
     *
     * @params: user_id
     *
     * @return array | bool
     */
    function getLandmarkGroupsByUserId($user_id)
    {
        if (! is_numeric($user_id) AND $user_id <= 0) {
            $this->setErrorMessage('err_user');
        }

        if (! $this->hasError()) {
            return $this->landmark_data->getLandmarkGroupsByUserId($user_id);
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
    public function getLandmarksByGroupIds($user_id, $landmark_groups)
    {
        $data = array();
        
        if (! is_numeric($user_id) AND $user_id <= 0) {
            $this->setErrorMessage('err_user');
        }

        if (! is_array($landmark_groups)) {
            $landmark_groups = array($landmark_groups);
        }

        if (! $this->hasError()) {
            $landmarks = $this->landmark_data->getLandmarksByGroupIds($user_id, $landmark_groups);
            if ($landmarks !== false) {
                $data = $landmarks;
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
    public function getFilteredLandmarks($user_id, $params)
    {
        $total_landmarks = array();
        
        if (! is_numeric($user_id) AND $user_id <= 0) {
            $this->setErrorMessage('err_user');
        }

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('err_params');
        }

        if (! $this->hasError()) {
    
            switch ($params['filter_type']) {
                
                case 'string_search':
                
                    $searchfields = array('landmarkname');
                    $landmarks = $this->landmark_data->getFilteredLandmarksStringSearch($user_id, $params, $searchfields);
                    if ($landmarks !== false) {
                        $total_landmarks = $landmarks;
                    }
                                 
                break;
                
                case 'group_filter':
                
                    if (isset($params['landmarkgroup_id']) AND strtolower($params['landmarkgroup_id']) == 'all') {
                        $params['landmarkgroup_id'] = array();
                    } elseif (! is_array($params['landmarkgroup_id'])) {
                        $params['landmarkgroup_id'] = array($params['landmarkgroup_id']);
                    }

                    if (isset($params['landmark_type']) AND strtolower($params['landmark_type']) == 'reference') {
                        $params['landmark_type'] = '1';
                    } elseif (isset($params['landmark_type']) AND strtolower($params['landmark_type']) == 'landmark') {
                        $params['landmark_type'] = '0';
                    } else {
                        $params['landmark_type'] = '';
                    }

                    $landmarks = $this->landmark_data->getFilteredLandmarks($user_id, $params);

//print_rb($landmarks);

                    if ($landmarks !== false) {
                        $total_landmarks = $landmarks;
                    }
                
                break;
                
                default:
                
                break;
            }


            // specialized personal paging and indexing
            if ( $params['paging'] == '+' ) {
                $params['landmark_start_index'] = $params['landmark_start_index'] + $params['landmark_listing_length'];
            } elseif ($params['paging'] == '-') {
                $params['landmark_start_index'] = $params['landmark_start_index'] - $params['landmark_listing_length'];
                if ($params['landmark_start_index'] < 0) {
                    $params['landmark_start_index'] = 0;
                }
            }

            $data['total_landmarks_count']   = count($total_landmarks);
            $total_key                      = intval(end(array_keys($total_landmarks)));
            $end_index                      = intval($params['landmark_start_index']) + intval($params['landmark_listing_length']);
            $data['landmarks']               = array_splice($total_landmarks, $params['landmark_start_index'], $params['landmark_listing_length']);
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
    public function getFilteredLandmarksList($user_id, $params)
    {
        $total_landmarks = array();
        $landmarks['iTotalRecords']          = 0;
        $landmarks['iTotalDisplayRecords']   = 0;
        $landmarks['data']                   = array();
        
        if (! is_numeric($user_id) AND $user_id <= 0) {
            $this->setErrorMessage('err_user');
        }

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('err_params');
        }

        if (! $this->hasError()) {
    
            switch ($params['filter_type']) {
                
                case 'string_search':
                
                    $searchfields = array('landmarkname');
                    $landmarks = $this->landmark_data->getFilteredLandmarksStringSearch($user_id, $params, $searchfields);
                    if ($landmarks !== false) {
                        $total_landmarks = $landmarks;
                    }
                                 
                break;
                
                case 'group_filter':
                
                    if (isset($params['landmarkgroup_id']) AND strtolower($params['landmarkgroup_id']) == 'all') {
                        $params['landmarkgroup_id'] = array();
                    } elseif (! is_array($params['landmarkgroup_id'])) {
                        $params['landmarkgroup_id'] = array($params['landmarkgroup_id']);
                    }

                    if (isset($params['landmark_type']) AND strtolower($params['landmark_type']) == 'reference') {
                        $params['landmark_type'] = '1';
                    } elseif (isset($params['landmark_type']) AND strtolower($params['landmark_type']) == 'landmark') {
                        $params['landmark_type'] = '0';
                    } else {
                        $params['landmark_type'] = '';
                    }
                    
                    $landmarks = $this->landmark_data->getFilteredLandmarks($user_id, $params);
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
                    $total_landmarks = $this->filterLandmarksListStringSearch($params['string_search'], $aColumns, $total_landmarks);
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
                        $row['DT_RowId']           = 'landmark-tr-'.$row['landmark_id'];       // automatic tr id value for dataTable to set

                        if ($row['landmarkgroupname'] == '' OR is_null($row['landmarkgroupname'])){
                            $row['landmarkgroupname'] = $params['default_value'];
                        }

                        $formatted_results[] = $row;
                    }

                    // if doing a column sorting
                    if ( isset( $params['iSortCol_0'] ) AND $params[ 'bSortable_'.intval($params['iSortCol_0']) ] == "true")
                    {
                        $formatted_results = $this->filterLandmarksListSort($aColumns[ intval($params['iSortCol_0']) ], ($params['sSortDir_0']==='asc' ? 'asc' : 'desc'), $formatted_results);
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
    public function filterLandmarksListStringSearch($search_str, $aColumns, $landmarks)
    {
        $results = array();
        if ( isset($search_str) AND $search_str != "" AND ! empty($landmarks)) {
            foreach($landmarks as $key => $landmark) {                                    // loop through landmarks info and find if search string is found
                if (! array_key_exists($landmark['landmark_id'], $results)) {
                    for ($i = 0; $i < count($aColumns); $i++) {                         // loop for each field to search in
                        if (! array_key_exists($$landmark['landmark_id'], $results)) {        // only search if unit is not currently in result array
                            if (($pos = strpos(strtolower($landmark[$aColumns[$i]]), $search_str)) !== false) {
                                $results[$landmark['landmark_id']] = $landmark;
                                continue;
                            }
                        }
                    }
                } else {
                    continue;
                }
            }
        } else {
            $results = $landmarks;
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
    public function filterLandmarksListSort($column_name, $sort_order, $landmarks)
    {
        $results = $landmarks;
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
    public function saveLandmark($account_id, $latitude, $longitude, $title, $radius, $street_address, $city, $state, $zip, $country, $shape, $type, $group, $coordinates, $reference = false) 
    {
        if ((! is_numeric($account_id)) OR ($account_id <= 0)) {
            $this->setErrorMessage('err_account');
        }
        
        if (! is_numeric($latitude) OR empty($latitude) OR ! is_numeric($longitude) OR empty($longitude)) {
            $this->setErrorMessage('err_coordinates');
        }
        
        if (strlen($title) < 3) {
            $this->setErrorMessage('err_title');
        }
        
        if (! empty($shape) AND ($shape == 'circle') AND (! is_numeric($radius) OR $radius <= 0)) {
            $this->setErrorMessage('err_radius');
        }
        
        if (($reference !== true) AND ($reference !== false)) {
            $this->setErrorMessage('err_landmark_reference');
        } else if ($reference === false) { // if it's a regular landmark, both type and group are required
            if ((! is_numeric($type)) OR ($type <= 0)) {
                $this->setErrorMessage('err_landmark_type');
            }
            
            if ((! is_numeric($group)) OR ($group <= 0)) {
                $this->setErrorMessage('err_landmark_group');
            }
        }
        
        if ((! empty($country)) AND ($country === 'USA') AND empty($state)) {
            $this->setErrorMessage('err_state');
        }
        
        if (empty($shape) OR (! empty($shape) AND ($this->validateLandmarkType($shape) === false))) {
            $this->setErrorMessage('err_landmark_shape');
        }
        
        if (empty($coordinates) OR ! is_array($coordinates)) {
            $this->setErrorMessage('err_invalid_coordinates');
        } else {
            $latlngs = array();
            
            foreach($coordinates as $index => $coords) {
                $latlngs[] = $coords['latitude'] . ' ' . $coords['longitude'];           
            }
            
            // if shape is not circle, add the first lat/lng as the last lat/lng to connect the polygon
            if ($shape != 'circle') {
                $latlngs[] = $coordinates[0]['latitude'] . ' ' . $coordinates[0]['longitude'];
                if ($shape != 'square') {
                    $street_address = $city = $state = $zip = $country = '';
                    $radius = NULL;
                }   
            }
            
            $coordinates = $latlngs;

        }
        
        if (! $this->hasError()) {
            
            // check for duplication of landmark name
            $landmark = $this->landmark_data->getLandmarkByTitle($account_id, $title);

            if (empty($landmark)) {   // if there is no landmark in the account with the same name, save the new landmark              
                $params = array(
                    'account_id' => $account_id,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'landmarkname' => $title,
                    'radius' => $radius,
                    'reference' => $reference ? 1 : 0,
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
                    $landmark_id = $this->landmark_data->saveLandmark($params);
                    if ($landmark_id !== false) {
                        if ($reference === false) { // if it's not a reference landmark, add it to the desired landmark group
                            if ($this->saveLandmarkToLandmarkGroup($landmark_id, $group) !== false) {
                                return $landmark_id;
                            } else {
                                $this->deleteLandmark($account_id, $landmark_id, false);
                            }
                        } else {                    // else, it's a reference landmark, just return the landmark id
                            return $landmark_id;
                        }
                    } else {
                        $this->setErrorMessage('err_save_landmark');
                    }
                } else {
                    $this->setErrorMessage('err_invalid_boundingbox');
                }
            } else {                    // else, if there is an existing landmark with the same name, throw error
                $this->setErrorMessage('err_duplicate_name');
            }
        }
        
        return false;
    }

    /**
     * Add landmark to landmark group
     *
     * @param int landmark_id
     * @param int landmarkgroup_id
     *
     * @return bool
     */    
    public function saveLandmarkToLandmarkGroup($landmark_id, $landmarkgroup_id) 
    {
        if (! is_numeric($landmark_id) OR $landmark_id <= 0) {
            $this->setErrorMessage('err_landmark_id');
        }
        
        if (! is_numeric($landmarkgroup_id) OR $landmarkgroup_id <= 0) {
            $this->setErrorMessage('err_landmarkgroup_id');
        }
        
        if (! $this->hasError()) {
            if ($this->landmark_data->saveLandmarkToLandmarkGroup($landmark_id, $landmarkgroup_id) !== false) {
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
    public function addLandmarkToVehicle($unit_id, $landmark_id) 
    {
        if ((! is_numeric($unit_id)) OR ($unit_id <= 0)) {
            $this->setErrorMessage('err_unit');    
        }
        
        if ((! is_numeric($landmark_id)) OR ($landmark_id <= 0)) {
            $this->setErrorMessage('err_landmark');
        }
        
        if (! $this->hasError()) {
            $params = array(
                'unit_id' => $unit_id,
                'landmark_id' => $landmark_id
            );
            
            if ($this->landmark_data->addLandmarkToVehicle($params)) {
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
    public function deleteLandmark($landmark_id, $account_id, $reference = false) 
    {
        if ((! is_numeric($landmark_id)) OR ($landmark_id <= 0)) {
            $this->setErrorMessage('err_landmark');
        }

        if ((! is_numeric($account_id)) OR ($account_id <= 0)) {
            $this->setErrorMessage('err_account');
        }
        
        if ($reference !== true AND $reference !== false) {
            $this->setErrorMessage('err_landmark_reference');
        }
        
        if (! $this->hasError()) {
            if ($this->landmark_data->deleteLandmark($landmark_id, $account_id, $reference)) {
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
    public function deleteLandmarkUpload($landmarkupload_id, $account_id) 
    {
        if ((! is_numeric($landmarkupload_id)) OR ($landmarkupload_id <= 0)) {
            $this->setErrorMesage('err_landmark');
        }

        if ((! is_numeric($account_id)) OR ($account_id <= 0)) {
            $this->setErrorMesage('err_account');
        }
        
        if (! $this->hasError()) {
            if ($this->landmark_data->deleteLandmarkUpload($landmarkupload_id, $account_id)) {
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
    public function updateLandmark($landmark_id, $account_id, $params) 
    {
        if ((! is_numeric($landmark_id)) OR ($landmark_id <= 0)) {
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
            
            if (strlen($params['landmarkname']) < 3) {
                $this->setErrorMessage('err_title');
            }
            
            if (! is_numeric($params['radius']) OR $params['radius'] <= 0) {
                $this->setErrorMessage('err_radius');
            }
            
            if (($params['reference'] !== 0) AND ($params['reference'] !== 1)) {
                $this->setErrorMessage('err_landmark_reference');
            }
            
            if ((! empty($params['country'])) AND ($params['country'] === 'USA') AND empty($params['state'])) {
                $this->setErrorMessage('err_state');
            }
        }
        
        if (! $this->hasError()) {
            //  check for landmark name duplication
            $valid_name = true;
            $landmark = $this->landmark_data->getLandmarkByTitle($account_id, $params['landmarkname']);
            
            if (is_array($landmark) AND ! empty($landmark)) {   // if there is a landmark with the same name, check to see if it's the same landmark
                $landmark = $landmark[0];
                if (isset($landmark['landmark_id']) AND ($landmark['landmark_id'] != $landmark_id)) {   // if it's not the same landmark, landmark name is duplicated
                    $valid_name = false;
                    $this->setErrorMessage('err_duplicate_name');                    
                }    
            }

            if ($valid_name) {
                if ($this->landmark_data->updateLandmark($landmark_id, $account_id, $params) !== false) {
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
            
            if (($landmarks = $this->landmark_data->getLandmarksByUnitId($unit_id, true, $verified)) !== false) {
                
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
    public function getLandmarksByUnitId($unit_id, $reference = null) {
        if (! is_numeric($unit_id) OR $unit_id <= 0) {
            $this->setErrorMessage('err_unit');
        }
        
        if ($reference !== true AND $reference !== false AND $reference !== null) {
            $this->setErrorMessage('err_landmark_reference');
        }
        
        if (! $this->hasError()) {
            $landmarks = $this->landmark_data->getLandmarksByUnitId($unit_id, $reference);
            if ($landmarks !== false) {
                foreach ($landmarks as $index => $landmark) {
                    $landmark[$index]['landmark_index'] = $index;
                    $landmarks[$index]['formatted_address'] = $this->address_logic->validateAddress($landmark['streetaddress'], $landmark['city'], $landmark['state'], '', $landmark['country']);    
                    
                    // convert feet to miles and format string to three places after decimal (will put in another component) 
                    $landmarks[$index]['radius_in_miles'] = Measurement::radiusFeetToFractionConverter($landmark['radius']);
                    
                    if ($reference === true OR ($reference === null AND ($landmark['reference'] == 1))) {       // if it's a reference landmark, clean up verify date
                        if ($landmark['verifydate'] !== '0000-00-00') {
                            $formatted_verified_date = Date::date_to_display($landmark['verifydate'], 'm/d/Y');
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
    public function uploadLandmarks($account_id, $unit_id, $file_path, $separator = ',', $enclosure = '"') 
    {
        if (! is_numeric($account_id) OR $account_id <= 0) {
            $this->setErrorMessage('err_account');    
        }
        
        if (isset($unit_id) AND $unit != '' AND (! is_numeric($unit_id) OR $unit_id <= 0)) {
            $this->setErrorMessage('err_unit');    
        }
        
        if (! $this->hasError()) {
            if (strlen($file_path) > 3) {
            
                // check if landmark upload or reference upload
                $islandmark_upload = true;
                $expected_columns = 10;
                if (isset($unit_id) AND $unit_id != '') {
                    $islandmark_upload = false;
                    $expected_columns = 9;
                }
            
                $csv_reader= new CSVReader();
                $csv_reader->setSeparator($separator);
                $csv_reader->setEnclosure($enclosure);
                $csv_reader->setMaxRowSize(0);
                $csv_reader->setFile($file_path, $expected_columns, true);
                
                $incomplete_landmarks_count = 0;

                for ($i = 0; $row = $csv_reader->parseFileByLine(); $i++) {
                    if ($i == 0) {
                        $validaterow = $this->validateLandmarkTemplate($row, $islandmark_upload);
                        
                        if ( $validaterow === false) {
                            $this->setErrorMessage('err_template');
                            return false;    
                        }    
                    } else {
                        $error = '';
                        $params = array(
                            'account_id'        => $account_id,
                            'landmarkname'      => (isset($row['name']) ? trim($row['name']) : ''),
                            'streetaddress'     => (isset($row['street']) ? trim($row['street']) : ''),
                            'city'              => (isset($row['city']) ? trim($row['city']) : ''),
                            'state'             => (isset($row['state']) ? trim($row['state']) : ''),
                            'zipcode'           => (isset($row['zipcode']) ? trim($row['zipcode']) : 0),
                            'country'           => (isset($row['country']) ? trim($row['country']) : ''),
                            'latitude'          => ((is_numeric($row['latitude']) AND ! empty($row['latitude'])) ? trim($row['latitude']) : 0),
                            'longitude'         => ((is_numeric($row['longitude']) AND ! empty($row['longitude'])) ? trim($row['longitude']) : 0),
                            'radius'            => ((is_numeric($row['radius(miles)']) AND ! empty($row['radius(miles)'])) ? (trim($row['radius(miles)']) * 5280) : 0),
                            'reference'         => ((isset($unit_id) AND $unit_id != '') ? 1 : 0),
                            'landmarkgroupname' => (isset($row['landmarkgroupname'])) ? trim($row['landmarkgroupname']) : '',
                            'shape'             => 'circle'//((isset($unit_id) AND $unit_id != '') ? 'circle' : ((isset($row['type'])) ? trim($row['type']) : ''))  // reference addresses' landmarktype defaults to 'circle'
                        );

                        if (! empty($params['landmarkname']) AND (strlen($params['landmarkname']) >= 3)) {     // 1st step - validate landmark name
                            $landmark = $this->getLandmarkByTitle($account_id, $params['landmarkname'], false);
                            if ($landmark !== false) {     // check name for duplication
                                if (empty($landmark)) {
                                    if (! empty($params['radius']) AND ($this->validateLandmarkRadius($params['radius']) === true)) {       // 2nd step - validate radius

                                        if ($this->validateLandmarkType($params['shape'])) {     // 3rd step - validate landmark type (shape)                                             
                                            $params['boundingbox'] = $this->getBoundingBoxValue($params['shape'], array($params['latitude'] . ' ' . $params['longitude']), $params['radius']);
                                            if ($params['boundingbox'] !== '') {
                                                $valid_address = $valid_coords = false;
                                                
                                                if (! empty($params['latitude'])        AND 
                                                    is_numeric($params['latitude'])     AND 
                                                    ! empty($params['longitude'])       AND 
                                                    is_numeric($params['longitude'])) {      // 4rd step - validate coordinates
                                                    $valid_coords = true;
                                                }
        
                                                if (! empty($params['streetaddress'])   AND 
                                                    ! empty($params['city'])            AND 
                                                    ! empty($params['state'])           AND 
                                                    ! empty($params['zipcode'])         AND 
                                                    ! empty($params['country'])) {     // 5th step - validate address           
                                                    $valid_address = true;                                                
                                                }
                                                
                                                if ($valid_coords AND $valid_address) {     // if the landmark has both valid address and lat/lng - it's a valid landmark                                           
                                                    $params['active'] = 1;
                                                                                                
                                                    $landmark_id = $this->landmark_data->saveLandmark($params);
                                                    if ($landmark_id !== false) {       // if pass validation, save new landmark
                                                        if (isset($unit_id) AND $unit_id != '') {                                       // if reference landmark (unit_id provided)
                                                            if (($this->addLandmarkToVehicle($unit_id, $landmark_id)) !== false) {      // if new landmark was saved successfully, assign it to the intended unit
                                                                //  success - saved new landmark and assigned to unit   
                                                            } else {                                                                    // else if we fail to assign the new landmark to unit, delete the new landmark
                                                                $this->deleteLandmark($landmark_id);        
                                                            }
                                                        }
        
                                                        // for landmarks, landmarkgroup_id is required. default group if none provided
                                                        if ($params['reference'] == 0) {
                                                            if (isset($params['landmarkgroupname']) AND $params['landmarkgroupname'] != '' ) {
                                                                // check for landmarkgroupname to get landmarkgroup_id, if not exist, create a new landmarkgroup and return that
                                                                $landmarkgroup = $this->getLandmarkGroupByTitle($account_id, $params['landmarkgroupname']);
                                                                if (! empty($landmarkgroup) AND isset($landmarkgroup['landmarkgroup_id']) AND $landmarkgroup['landmarkgroup_id'] > 0) {
                                                                    $params['landmarkgroup_id'] = $landmarkgroup['landmarkgroup_id'];
                                                                } else {
                                                                    //create new landmarkgroup for this new landmarkgroupname
                                                                    $landmarkgroup_id = $this->addLandmarkGroup($account_id, $params['landmarkgroupname']);
                                                                    if ($landmarkgroup_id) {
                                                                        $params['landmarkgroup_id'] = $landmarkgroup_id;
                                                                    }
                                                                }
                                                            } else {
                                                                // assign to account default landmarkgroup
                                                                $params['landmarkgroup_id'] = 1; // need to update this value to be account default landmarkgroup_id here
                                                            }
                                                            
                                                            // add landmarkgroup_landmark relation
                                                            $landmarkgroup_landmark['landmarkgroup_id'] = $params['landmarkgroup_id'];
                                                            $landmarkgroup_landmark['landmark_id']      = $landmark_id;
                                                            
                                                            $this->addLandmarkgroupLandmark($landmarkgroup_landmark);
                                                        }
        
                                                    } else {
                                                        $error = 'db error - save landmark';
                                                    }
                                                } else {                                                                            // else save landmark as incomplete
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
                            $error = 'empty name';    
                        }
                        
                        // save incomplete landmark
                        if ($error !== '') {
                            $incomplete_landmarks_count++;
                            $params['reason'] = $error;
                            $this->landmark_data->saveIncompleteLandmark($params);
                            $this->clearError();
                        }
                    }    
                }

                return $incomplete_landmarks_count;     // return incomplete landmarks counter    
            } else {
                $this->setError('err_file');
            }
        }
        
        return false;
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
    public function getLandmarkByTitle($account_id, $title, $include_units = false)
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
            $landmark = $this->landmark_data->getLandmarkByTitle($account_id, $title);
            if ($landmark !== false) {
                if (! empty($landmark)) {
                    $landmark = array_pop($landmark);
                    if (! empty($landmark['landmark_id']) AND $include_units) {
                        $units = $this->landmark_data->getUnitsByLandmarkId($landmark['landmark_id']);
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
    public function getLandmarkByIds($landmark_ids)
    {
        if (! is_array($landmark_ids)) {
            $landmark_ids = array($landmark_ids);
        }
        
        if (count($landmark_ids) == 0) {
            $this->setErrorMessage('err_id');    
        }
        
        if (! $this->hasError()) {
            $landmarks = $this->landmark_data->getLandmarkByIds($landmark_ids);
            if ($landmarks !== false) {
                if (! empty($landmarks)) {
                    foreach($landmarks as $index => $landmark) {
                        $landmarks[$index]['formatted_address'] = $this->address_logic->validateAddress($landmark['streetaddress'], $landmark['city'], $landmark['state'], $landmark['zipcode'], $landmark['country']);
                        // convert feet to miles and format string to three places after decimal (will put in another component) 
                        $landmarks[$index]['radius_in_miles'] = Measurement::radiusFeetToFractionConverter($landmark['radius']);
                        
                        // clean boundingbox value
                        $landmarks[$index]['coordinates'] =  $this->getCoordinatesFromBoundingBoxValue($landmark['boundingbox']);    
                    }
                }
                return $landmarks;
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
    public function getLandmarkUploadByIds($landmark_ids)
    {
        if (! is_array($landmark_ids)) {
            $landmark_ids = array($landmark_ids);
        }
        
        if (count($landmark_ids) == 0) {
            $this->setErrorMessage('err_id');    
        }
        
        if (! $this->hasError()) {
            $landmarks = $this->landmark_data->getLandmarkUploadByIds($landmark_ids);
            if ($landmarks !== false) {
                if (! empty($landmarks)) {
                    foreach($landmarks as $index => $landmark) {
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
    public function validateLandmarkTemplate($row, $islandmarks_upload = true) {
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
    public function validateLandmarkRadius($radius) {
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
    public function validateLandmarkType($type) 
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
                case      'circle':
                case      'square': if (! empty($radius)) {
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
                case   'rectangle':
                case     'polygon': if ( (! empty($radius)) && (!($coords)) ) {
                                        list($latitude, $longitude) = explode(' ', $coords[0]);
                                        $geolocation = GeoLocation::fromDegrees($latitude, $longitude);
                                        $radius = (float) sprintf('%01.3f', ($radius * 0.00018939393)); // convert feet to miles 
                                        $coordinates = $geolocation->boundingCoordinates($radius, 'miles');
                                        $tr = $coordinates[1]->getLatitudeInDegrees()." ".$coordinates[1]->getLongitudeInDegrees();
                                        $tl = $coordinates[1]->getLatitudeInDegrees()." ".$coordinates[0]->getLongitudeInDegrees();
                                        $br = $coordinates[0]->getLatitudeInDegrees()." ".$coordinates[1]->getLongitudeInDegrees();
                                        $bl = $coordinates[0]->getLatitudeInDegrees()." ".$coordinates[0]->getLongitudeInDegrees();
                                        $ret = "GEOMFROMTEXT('POLYGON(({$tl}, {$tr}, {$br}, {$bl}, {$tl}))')";
                                    } else {
                                        // $coords = array('33.1 -117.1');
                                        $coords = implode(', ', $coords);
                                        $ret = "GEOMFROMTEXT('POLYGON((" . $coords . "))')";
                                    }
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
    public function updateLandmarksInfo($landmark_id, $params, $table)
    {
        if (! is_numeric($landmark_id) OR $landmark_id <= 0) {
            $this->setErrorMessage('err_id');
        }
        
        if (! is_array($params) OR count($params) <= 0) {
            $this->setErrorMessage('err_param');
        }
        
        if (empty($table)) {
            $this->setErrorMessage('err_param');
        }
        
        if (! $this->hasError()) {
            $ret = false;
            switch ($table) {
                case 'landmark':
                    $ret = $this->updateLandmarkInfo($landmark_id, $params);
                    break;
                case 'landmarkgroup_landmark':
                    $ret = $this->updateLandmarkgroupLandmark($landmark_id, $params);
                    break;
                default:
                    break;
            }
            return $ret;
        }

        return false;
    }

    /**
     * Update the landmark info by landmark_id
     *
     * @params: landmark_id, params
     *
     * @return array
     */
    public function updateLandmarkInfo($landmark_id, $params)
    {
        if (! is_numeric($landmark_id) OR $landmark_id <= 0) {
            $this->setErrorMesage('err_landmark');
        }

        if (! is_array($params) OR empty($params)) {
            $this->setErrorMessage('err_params');
        } else {
            if (isset($params['latitude']) AND (! is_numeric($params['latitude']) OR empty($params['latitude']) OR ! is_numeric($params['longitude']) OR empty($params['longitude']))) {
                $this->setErrorMessage('err_coordinates');
            }
            
            if (isset($params['landmarkname']) AND strlen($params['landmarkname']) < 3) {
                $this->setErrorMessage('err_title');
            }
            
            if (isset($params['radius']) AND (! is_numeric($params['radius']) OR $params['radius'] <= 0)) {
                $this->setErrorMessage('err_radius');
            }
            
            if (isset($params['reference']) AND ! in_array($params['reference'], array(0,1,2))) {
                $this->setErrorMessage('err_landmark_reference');
            }
            
            if (isset($params['country']) AND ! empty($params['country']) AND ($params['country'] == 'USA' AND empty($params['state']))) {
                $this->setErrorMessage('err_state');
            }
            
            if (isset($params['shape']) AND ! empty($params['shape'])) {
                if ($this->validateLandmarkType($params['shape']) !== false) {
                    if (($params['shape'] != 'circle') OR ($params['shape'] == 'circle' AND ! empty($params['radius']))) { // if shape is a circle, a radius is required
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
                    $this->setErrorMessage('err_landmark_shape');
                }
            }
        }

        if (! $this->hasError()) {
            // if landmarkname update then check if new name already exist, only update if unique
            if ($this->landmark_data->updateLandmarkInfo($landmark_id, $params)) {
                return true;
            }
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
    public function updateLandmarkgroupLandmark($landmark_id, $params)
    {
        if (! is_numeric($landmark_id) OR $landmark_id <= 0) {
            $this->setErrorMessage('err_unit');
        }
        
        if (! is_array($params) OR count($params) <= 0) {
            $this->setErrorMessage('err_param');
        } else {
            if (isset($params['landmarkgroup_id']) AND (! is_numeric($params['landmarkgroup_id']) OR $params['landmarkgroup_id'] <= 0)) {
                $this->setErrorMessage('err_param');
            }
        }
        
        if (! $this->hasError()) {
            // check if existing
            $landmarkgroup_landmark = $this->landmark_data->getLandmarkgroupLandmark($landmark_id);
            if (! empty($landmarkgroup_landmark)) {
                if ($this->landmark_data->updateLandmarkgroupLandmark($landmark_id, $params)) {
                    return true;
                }
            } else {
                $landmarkgroup_landmark_param['landmarkgroup_id'] = $params['landmarkgroup_id'];
                $landmarkgroup_landmark_param['landmark_id'] = $landmark_id;
                if ($this->landmark_data->addLandmarkgroupLandmark($landmarkgroup_landmark_param)) {
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
    public function updateIncompleteLandmark($landmarkupload_id, $params, $table)
    {
        if (! is_numeric($landmarkupload_id) OR $landmarkupload_id <= 0) {
            $this->setErrorMessage('err_id');
        }
        
        if (! is_array($params) OR count($params) <= 0) {
            $this->setErrorMessage('err_param');
        }
        
        if (empty($table)) {
            $this->setErrorMessage('err_param');
        }
        
        if (! $this->hasError()) {
            if (isset($params['landmarkgroup_id']) AND $params['landmarkgroup_id'] != '') {
                // get landmarkgroup name
                $landmarkgroup = $this->landmark_data->getLandmarkGroupById($params['landmarkgroup_id']);
                if ($landmarkgroup !== false) {
                    $params['landmarkgroupname'] = (isset($landmarkgroup[0]['landmarkgroupname']) AND $landmarkgroup[0]['landmarkgroupname'] != '') ? $landmarkgroup[0]['landmarkgroupname'] : '';
                    unset($params['landmarkgroup_id']);
                }
            }

            $ret = $this->landmark_data->updateIncompleteLandmark($landmarkupload_id, $params);

            return $ret;
        }

        return false;
    }

    /**
     *	validate incomplete landmark info 
     *
     *	POST params: field, $params
     *
     *	@return void | error array
     */    
    public function validateIncompleteLandmarkInfo($field = 'all', $params = array())
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
                    if ( empty($params['landmarkname']) OR strlen($params['landmarkname']) <= 3) {
                        $error['landmark-name'] = "Invalid Name Length";
                    }
                    
                    // check name for duplication
                    if (! empty($params['landmarkname']) AND (strlen($params['landmarkname']) >= 3)) {
                        $landmark = $this->getLandmarkByTitle($params['account_id'], $params['landmarkname'], false);
                        
                        if (! empty($landmark)) {
                            // landmark with name already exist, duplicate
                            $error['landmark-name'] = "Duplicate Landmark Name";
                        }
                    }
                    
                    break;
                case 'landmark-group':

                    break;
                case 'landmark-type':

                    break;
                case 'landmark-radius':

                    // 2nd step - validate radius 
                    if ( !isset($params['radius']) OR empty($params['radius']) OR ($this->validateLandmarkRadius($params['radius']) === false)) {
                        // invalid radius
                        $error['landmark-radius']   = "Invalid Radius";
                    }

                    break;
                case 'landmark-shape':

                    // 3rd step - validate landmark type (shape)
                    if ( ! isset($params['shape']) OR ! $this->validateLandmarkType($params['shape']) ) {
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
                    if (empty($params['latitude'])        OR 
                        ! is_numeric($params['latitude'])     OR 
                        empty($params['longitude'])       OR 
                        ! is_numeric($params['longitude'])) {      
                        
                        // invalid coordinates 'requires geo';
                        $error['landmark-location']    = 'Invalid Coordinates.';
                        $valid_coords                   = false;
                    }
        
                    // 6th step - validate address
                    if (empty($params['streetaddress'])   OR 
                        empty($params['city'])            OR 
                        empty($params['state'])           OR 
                        empty($params['zipcode'])         OR 
                        empty($params['country'])) {                
                        
                        // invalid address 'requires rgeo';
                        $error['landmark-location']  = 'Invalid Address.';
                        $valid_address              = false;
                    }

                    break;
                case 'landmark-map-click':


                    break;
                case 'all':
                
                    // 1st step - validate landmark name
                    if ( empty($params['landmarkname']) OR strlen($params['landmarkname']) <= 3) {
                        $error['landmark-name'] = "Invalid Name Length";
                    }
                    
                    // check name for duplication
                    if (! empty($params['landmarkname']) AND (strlen($params['landmarkname']) >= 3)) {
                        $landmark = $this->getLandmarkByTitle($params['account_id'], $params['landmarkname'], false);
                        
                        if (! empty($landmark)) {
                            // landmark with name already exist, duplicate
                            $error['landmark-name'] = "Duplicate Landmark Name";
                        }
                    }
                    
                    // 2nd step - validate radius 
                    if ( !isset($params['radius']) OR empty($params['radius']) OR ($this->validateLandmarkRadius($params['radius']) === false)) {
                        // invalid radius
                        $error['landmark-radius']   = "Invalid Radius";
                    }
        
                    // 3rd step - validate landmark type (shape)
                    if ( ! isset($params['shape']) OR ! $this->validateLandmarkType($params['shape']) ) {
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
                    if (empty($params['latitude'])        OR 
                        ! is_numeric($params['latitude'])     OR 
                        empty($params['longitude'])       OR 
                        ! is_numeric($params['longitude'])) {      
                        
                        // invalid coordinates 'requires geo';
                        $error['landmark-location']    = 'Invalid Coordinates.';
                        $valid_coords                   = false;
                    }
        
                    // 6th step - validate address
                    if (empty($params['streetaddress'])   OR 
                        empty($params['city'])            OR 
                        empty($params['state'])           OR 
                        empty($params['zipcode'])         OR 
                        empty($params['country'])) {                
                        
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
     * Get landmarkgroup info by title
     *
     * @param int       account_id
     * @param string    title
     *
     * @return bool|array
     */    
    public function getLandmarkGroupByTitle($account_id, $title)
    {
        if (! is_numeric($account_id) OR $account_id <= 0) {
            $this->setErrorMessage('err_account');
        }
        
        if (empty($title) OR strlen($title) < 3) {
            $this->setErrorMessage('err_title');    
        }
        
        if (! $this->hasError()) {
            $landmark = $this->landmark_data->getLandmarkGroupByTitle($account_id, $title);
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
    public function addLandmarkGroup($account_id, $title)
    {
        if (! is_numeric($account_id) OR $account_id <= 0) {
            $this->setErrorMessage('err_account');
        }
        
        if (empty($title) OR strlen($title) < 3) {
            $this->setErrorMessage('err_title');    
        }
        
        if (! $this->hasError()) {
        
            $params['user_id']              = $account_id;
            $params['landmarkgroupname']    = $title;
            $params['active']               = 1;
            
            $landmarkgroup_id = $this->landmark_data->addLandmarkGroup($params);
            if ($landmarkgroup_id) {
                return $landmarkgroup_id;
            }   
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
    public function addLandmarkgroupLandmark($params)
    {
        if (! is_numeric($params['landmarkgroup_id']) OR $params['landmarkgroup_id'] <= 0) {
            $this->setErrorMessage('err_group');
        }
        
        if (! is_numeric($params['landmark_id']) OR $params['landmark_id'] <= 0) {
            $this->setErrorMessage('err_landmark');
        }
        
        if (! $this->hasError()) {
            $landmarkgroup = $this->landmark_data->addLandmarkgroupLandmark($params);
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
    public function getIncompleteLandmarksList($user_id, $params)
    {
        $total_landmarks = array();
        $landmarks['iTotalRecords']          = 0;
        $landmarks['iTotalDisplayRecords']   = 0;
        $landmarks['data']                   = array();
        
        if (! is_numeric($user_id) AND $user_id <= 0) {
            $this->setErrorMessage('err_user');
        }

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('err_params');
        }

        if (! $this->hasError()) {
    
            switch ($params['filter_type']) {
                
                case 'string_search':
                
                    $searchfields = array('landmarkname');
                    $landmarks = $this->landmark_data->getIncompleteLandmarksStringSearch($user_id, $params, $searchfields);
                    if ($landmarks !== false) {
                        $total_landmarks = $landmarks;
                    }
                                 
                break;
                
                case 'group_filter':
                
                    if (isset($params['landmarkgroup_id']) AND strtolower($params['landmarkgroup_id']) == 'all') {
                        $params['landmarkgroup_id'] = array();
                    } elseif (! is_array($params['landmarkgroup_id'])) {
                        $params['landmarkgroup_id'] = array($params['landmarkgroup_id']);
                    }

                    if (isset($params['landmark_type']) AND strtolower($params['landmark_type']) == 'reference') {
                        $params['landmark_type'] = '1';
                    } elseif (isset($params['landmark_type']) AND strtolower($params['landmark_type']) == 'landmark') {
                        $params['landmark_type'] = '0';
                    } else {
                        $params['landmark_type'] = '';
                    }
                    
                    if (isset($params['landmark_reason']) AND $params['landmark_reason'] != 'All' AND $params['landmark_reason'] == 'name') {
                        $params['reason'] = 'name';
                    }
                    
                    $landmarks = $this->landmark_data->getFilteredIncompleteLandmarks($user_id, $params);
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
                    $total_landmarks = $this->filterLandmarksListStringSearch($params['string_search'], $aColumns, $total_landmarks);
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
                        $row['DT_RowId']           = 'incomplete-tr-'.$row['landmarkupload_id'];       // automatic tr id value for dataTable to set

                        if ($row['landmarkgroupname'] == '' OR is_null($row['landmarkgroupname'])){
                            $row['landmarkgroupname'] = $params['default_value'];
                        }

                        $formatted_results[] = $row;
                    }

                    // if doing a column sorting
                    if ( isset( $params['iSortCol_0'] ) AND $params[ 'bSortable_'.intval($params['iSortCol_0']) ] == "true")
                    {
                        $formatted_results = $this->filterLandmarksListSort($aColumns[ intval($params['iSortCol_0']) ], ($params['sSortDir_0']==='asc' ? 'asc' : 'desc'), $formatted_results);
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
}
