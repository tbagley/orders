<?php

namespace Models\Logic;

use Models\Logic\BaseLogic;

use Models\Data\AlertData;
use Models\Data\ContactData;
use Models\Logic\ContactLogic;
use Models\Logic\TerritoryLogic;
use Models\Logic\AddressLogic;

use GTC\Component\Utils\Date;
use GTC\Component\Utils\Arrayhelper;
use GTC\Component\Utils\CSV\CSVReader;
use GTC\Component\Form\Validation;


class AlertLogic extends BaseLogic
{
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        
        //$this->load->model('data/alert_data');
        $this->territory_logic = new TerritoryLogic;
        $this->alert_data = new AlertData;
        $this->contact_data = new ContactData;
        $this->contact_logic = new ContactLogic; 
        $this->validator = new Validation;
        $this->address_logic = new AddressLogic;
    }

    /**
     * Get the alert types
     *
     * @return array | bool
     */
    public function getAlertTypes()
    {
        return $this->alert_data->getAlertTypes();
    }

    /**
     * Get the alerts by account id
     *
     * @params: user_id
     *
     * @return array | bool
     */
    public function getAlertsByAccountId($account_id)
    {
        $this->validator->validate('record_id', $account_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->alert_data->getAlertsByAccountId($account_id);
        }

        return false;
    }

    /**
     * Get the alerts by user id
     *
     * @params: user_id
     *
     * @return array | bool
     */
    public function getAlertsByUserId($account_id)
    {
        $this->validator->validate('record_id', $account_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->alert_data->getAlertsByUserId($account_id);
        }

        return false;
    }

    /**
     * Get the filtered alerts by provided params
     *
     * @params: int $user_id
     * @params: array $params
     *
     * @return array
     */
    public function getFilteredAlerts($account_id, $params)
    {
        $total_alerts = array();
        $alerts['iTotalRecords']          = 0;
        $alerts['iTotalDisplayRecords']   = 0;
        $alerts['data']                   = array();

        $this->validator->validate('record_id', $account_id);

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('err_params');
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

                case 'string_search':

                    $searchfields = array('alertname');
                    $alert = $this->alert_data->getFilteredAlertsStringSearch($account_id, $params, $searchfields);
                    if ($alert !== false) {
                        $total_alerts = $alert;
                    }

                break;
                
                case 'group_filter':

                    if (isset($params['vehiclegroup_id']) AND strtolower($params['vehiclegroup_id']) == 'all') {
                        $params['vehiclegroup_id'] = array();
                    } elseif (! is_array($params['vehiclegroup_id'])) {
                        $params['vehiclegroup_id'] = array($params['vehiclegroup_id']);
                    }

                    if (isset($params['contactgroup_id']) AND strtolower($params['contactgroup_id']) == 'all') {
                        $params['contactgroup_id'] = array();
                    } elseif (! is_array($params['contactgroup_id'])) {
                        $params['contactgroup_id'] = array($params['contactgroup_id']);
                    }

                    if (isset($params['alert_type']) AND strtolower($params['alert_type']) == 'all') {
                        $params['alert_type'] = '';
                    }

                    $alert = $this->alert_data->getFilteredAlerts($account_id, $params);
                    if ($alert !== false) {
                        $total_alerts = $alert;
                    }

                break;
                
                default:

                break;
            }

            // for the formatted unit events, process for datatable return results
            if (! empty($total_alerts)) {

                // init total results
                $iTotal                             = count($total_alerts);
                $iFilteredTotal                     = count($total_alerts);
                $alerts['iTotalRecords']         = $iTotal;
                $alerts['iTotalDisplayRecords']  = $iFilteredTotal;
                $aColumns                           = array();        // datatable columns event field/key names

                for ( $i = 0 ; $i < intval( $params['iColumns'] ) ; $i++ ) {
                    $aColumns[] = $params['mDataProp_'.$i];
                }

                $formatted_results = array();
                if (! empty($total_alerts)) {
                    foreach ($total_alerts as $alert) {
                        $row = $alert;
                        $row['DT_RowId'] = 'alert-tr-'.$row['alert_id'];       // automatic tr id value for dataTable to set

                        if ($row['alertname'] == '' OR is_null($row['alertname'])){
                            $row['alertname'] = $params['default_value'];
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
                        else
                        {
	                        $unitname = 'All';
                        }
                        $row['unitname'] = $unitname;

                        // get last triggered date from alerthistory
                        $row['uniteventdate'] = 'n/a'; 
                        $last_history = $this->alert_data->getLastAlertTriggered($row['alert_id']);
                        if (! empty($last_history)) {
                            $last_history                       = array_pop($last_history);
                            $row['alerthistory_id']             = $last_history['alerthistory_id'];
                            $row['alerthistory_alert_id']       = $last_history['alert_id'];
                            $row['alerthistory_unit_id']        = $last_history['unit_id'];
                            $row['alerthistory_unitevent_id']   = $last_history['unitevent_id'];
                            $row['alertdetail']                 = $last_history['alertdetail'];
                            //$row['alerthistorydate']            = Date::date_to_display($last_history['alerthistorydate'], 'h:i A m/d/Y');
                            $row['uniteventdate']               = Date::utc_to_locale($last_history['uniteventdate'], $params['user_timezone'], 'h:i A m/d/Y');

                        }

                        $formatted_results[] = $row;
                    }

                    // if doing a column sorting
                    if ( isset( $params['iSortCol_0'] ) AND $params[ 'bSortable_'.intval($params['iSortCol_0']) ] == "true") {
                        $formatted_results = $this->filterAlertsSort($aColumns[ intval($params['iSortCol_0']) ], ($params['sSortDir_0']==='asc' ? 'asc' : 'desc'), $formatted_results);
                    }

                    // if doing paging, find correct page list
                    if ( isset($params['iDisplayStart']) AND $params['iDisplayLength'] != '-1' ) {
                        $formatted_results = array_splice($formatted_results, $params['iDisplayStart'], $params['iDisplayLength']);
                    }
                }

                $alerts['data'] = $formatted_results;
            }
        }

        return $alerts;
    }

    /**
     * Get the filtered alert history by provided params
     *
     * @params: int $user_id
     * @params: array $params
     *
     * @return array
     */
    public function getAlertHistory($account_id, $params)
    {
        $total_alerts = array();
        $alerts['iTotalRecords']          = 0;
        $alerts['iTotalDisplayRecords']   = 0;
        $alerts['data']                   = array();

        $this->validator->validate('record_id', $account_id);

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('err_params');
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

                case 'string_search':

                    $searchfields = array('alertname', 'unitgroup.unitgroupname');
                    $alert = $this->alert_data->getAlertHistoryStringSearch($account_id, $params, $searchfields);
                    if ($alert !== false) {
                        $total_alerts = $alert;
                    }

                break;

                case 'group_filter':

                    if (isset($params['vehiclegroup_id']) AND strtolower($params['vehiclegroup_id']) == 'all') {
                        $params['vehiclegroup_id'] = array();
                    } elseif (! is_array($params['vehiclegroup_id'])) {
                        $params['vehiclegroup_id'] = array($params['vehiclegroup_id']);
                    }

                    if (isset($params['contactgroup_id']) AND strtolower($params['contactgroup_id']) == 'all') {
                        $params['contactgroup_id'] = array();
                    } elseif (! is_array($params['contactgroup_id'])) {
                        $params['contactgroup_id'] = array($params['contactgroup_id']);
                    }

                    if (isset($params['alert_type']) AND strtolower($params['alert_type']) == 'all') {
                        $params['alert_type'] = '';
                    }

                    if (isset($params['alert_id']) AND strtolower($params['alert_id']) == 'all') {
                        $params['alert_id'] = '';
                    }

                    // the start and end dates have already been converted to utc time on the client side for consistency
                    /*
                    if (isset($params['start_date']) AND ! empty($params['start_date'])) {
                        $params['start_date'] = Date::locale_to_utc($params['start_date'], $params['user_timezone'], 'Y-m-d H:i:s');
                    }

                    if (isset($params['end_date']) AND ! empty($params['end_date'])) {
                        $params['end_date'] = Date::locale_to_utc($params['end_date'], $params['user_timezone'], 'Y-m-d H:i:s');
                    }
                    */

                    $alert = $this->alert_data->getAlertHistory($account_id, $params);
                    if ($alert !== false) {
                        $total_alerts = $alert;
                    }

                break;

                default:

                break;
            }

            // for the formatted unit events, process for datatable return results
            if (! empty($total_alerts)) {

                // init total results
                $iTotal                             = count($total_alerts);
                $iFilteredTotal                     = count($total_alerts);
                $alerts['iTotalRecords']         = $iTotal;
                $alerts['iTotalDisplayRecords']  = $iFilteredTotal;
                $aColumns                           = array();        // datatable columns event field/key names

                for ( $i = 0 ; $i < intval( $params['iColumns'] ) ; $i++ ) {
                    $aColumns[] = $params['mDataProp_'.$i];
                }

                $formatted_results = array();
                if (! empty($total_alerts)) {
                    foreach ($total_alerts as $alert) {
                        $row = $alert;
                        $row['DT_RowId'] = 'alert-tr-'.$row['alert_id'];       // automatic tr id value for dataTable to set

                        if ($row['alertname'] == '' OR is_null($row['alertname'])){
                            $row['alertname'] = $params['default_value'];
                        }

                        // format alerttriggerdate
                        if ($row['alerthistorydate'] == '0000-00-00 00:00:00') {
                            $row['triggerdate'] = 'n/a';
                        } else {
                            $row['triggerdate'] = Date::utc_to_locale($row['alerthistorydate'], $params['user_timezone'], 'h:i A m/d/Y');
                        }

                        // format uniteventdate
                        if ($row['uniteventdate'] == '0000-00-00 00:00:00') {
                            $row['deviceeventdate'] = 'n/a';
                        } else {
                            $row['deviceeventdate'] = Date::utc_to_locale($row['uniteventdate'], $params['user_timezone'], 'h:i A m/d/Y');
                        }

                        // get alert contacts
                        $row['contactname'] = '';
                        $alert_contacts = $this->getAlertContacts($row['alert_id']);
                        if ($alert_contacts !== false AND ! empty($alert_contacts) AND is_array($alert_contacts)) {
                            $alert_contacts = array_pop($alert_contacts);
                            $row['contactname'] = ((! empty($alert_contacts['alert_contact_id']) AND ! empty($alert_contacts['contactname'])) ? $alert_contacts['contactname'] : $alert_contacts['contactgroupname']);
                        }
                        
                        // format address
                        $row['formatted_address'] = '';
                        if (! empty($row['streetaddress']) OR ! empty($row['city']) OR ! empty($row['state']) OR ! empty($row['zipcode']) OR ! empty($row['country'])) {
                            $row['formatted_address'] = $this->address_logic->validateAddress($row['streetaddress'], $row['city'], $row['state'], $row['zipcode'], $row['country']);
                        }
                        
                        // add territoryname if location is in a territory
                        if (! empty($row['territoryname'])) {
                            $row['formatted_address'] = '(' . $row['territoryname'] . ') ' . $row['formatted_address'];
                        }
                        
                        $formatted_results[] = $row;
                    }

                    // if doing a column sorting
                    if ( isset( $params['iSortCol_0'] ) AND $params[ 'bSortable_'.intval($params['iSortCol_0']) ] == "true") {
                        if ($aColumns[ intval($params['iSortCol_0']) ] == 'deviceeventdate') {
                            $aColumns[ intval($params['iSortCol_0']) ] = 'uniteventdate';
                        }
                        $formatted_results = $this->filterAlertsSort($aColumns[ intval($params['iSortCol_0']) ], ($params['sSortDir_0']==='asc' ? 'asc' : 'desc'), $formatted_results);
                    }

                    // if doing paging, find correct page list
                    if ( isset($params['iDisplayStart']) AND $params['iDisplayLength'] != '-1' ) {
                        $formatted_results = array_splice($formatted_results, $params['iDisplayStart'], $params['iDisplayLength']);
                    }
                }

                $alerts['data'] = $formatted_results;
            }
        }

        return $alerts;
    }

    /**
     * Return alerts having sorted by column field by sort order
     *
     * @params: string $column_name
     * @params: string $sort_order
     * @params: array alerts
     *
     * @return array $results
     */
    public function filterAlertsSort($column_name, $sort_order, $alerts)
    {
        $results = $alerts;
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
     * Get alert contacts by alert id
     *
     * @param int alert_id
     *
     * @return array | boolean
     */ 
    public function getAlertContacts($alert_id)
    {
        $this->validator->validate('record_id', $alert_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->alert_data->getAlertContacts($alert_id);
        }

        return false;
    }

    /**
     * Add alert
     *
     * @param array params
     *
     * @return int alert_id
     */ 
    public function addAlert($params)
    {
        $this->validator->validate('record_id', $params['account_id']);

        if (! is_array($params) OR empty($params)) {
            $this->setErrorMessage('err_params');
        } else {
            $this->validator->validate('alert_name', $params['alertname']);
            if ($this->validator->hasError()) {
                $this->setErrorMessage($this->validator->getErrorMessage());
            } else {
                $duplicate = $this->alert_data->getAlertByName($params['account_id'], $params['alertname']); 
                if (! empty($duplicate)) {
                    $this->setErrorMessage('Duplicated Alert Name');
                }   
            }
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
                return $this->alert_data->addAlert($params);
        }

        return false;
    }

    /**
     * Delete alert (mark as inactive)
     *
     * @param int alert_id
     * @param int account_id
     *
     * @return bool
     */    
    public function deleteAlert($alert_id, $account_id) 
    {
        $this->validator->validate('record_id', $alert_id);
        $this->validator->validate('record_id', $account_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {

            return $this->alert_data->deleteAlert($alert_id, $account_id);
        }

        return false;
    }
    

    /**
     * Add alert unit
     *
     * @param array params
     *
     * @return int alert_id
     */ 
    public function addAlertUnit($params)
    {
        $this->validator->validate('record_id', $params['alert_id']);

        if (! empty($params['unit_id']) OR ! empty($params['unitgroup_id'])) {
            if (! empty($params['unit_id'])) {
                $this->validator->validate('record_id', $params['unit_id']);
            }

            if (! empty($params['unitgroup_id'])) {
                $this->validator->validate('record_id', $params['unitgroup_id']);
            }
        } else if (($params['unit_id']!=0)&&($params['unitgroup_id']!=0)) {
            $this->setErrorMessage('err_alert_unit');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            $alert = $this->alert_data->addAlertUnit($params);
            if ($alert !== false) {
                return $alert;
            }
        }

        return false;
    }
    
    /**
     * Add alert territory
     *
     * @param array params
     *
     * @return int alert_id
     */ 
    public function addAlertTerritory($params)
    {
        $this->validator->validate('record_id', $params['alert_id']);

        if ( (!(empty($params['territory_id']))) || (!(empty($params['territorygroup_id']))) ) {
            if (! empty($params['territory_id']) && $params['territory_id'] > 0) {
                $this->validator->validate('record_id', $params['territory_id']);        
            }
            
            if (! empty($params['territorygroup_id']) && $params['territorygroup_id'] > 0) {
                $this->validator->validate('record_id', $params['territorygroup_id']);       
            }
        } else if(($params['territory_id']!=0)&&($params['territorygroup_id']!=0)) {
            $this->setErrorMessage('err_alert_territory');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            $alert = $this->alert_data->addAlertTerritory($params);
            if ($alert !== false) {
                return $alert;
            }
        }

        return false;
    }
    
    /**
     * Add alert contact
     *
     * @param array params
     *
     * @return int alert_id
     */ 
    public function addAlertContact($params)
    {
        $this->validator->validate('record_id', $params['alert_id']);

        if (! empty($params['contact_id']) OR ! empty($params['contactgroup_id'])) {
            if (! empty($params['contact_id'])) {
                $this->validator->validate('record_id', $params['contact_id']);        
            }

            if (! empty($params['contactgroup_id'])) {
                $this->validator->validate('record_id', $params['contactgroup_id']);        
            }
        } else {
            $this->setErrorMessage('err_alert_contact');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            $alert = $this->alert_data->addAlertContact($params);
            if ($alert !== false) {
                return $alert;
            }
        }

        return false;
    }

    /**
     * Update alert info
     *
     * @param int alert_id
     * @param int account_id
     * @param array params
     *
     * @return bool
     */ 
    public function updateAlert($alert_id, $account_id, $params) 
    {
        $this->validator->validate('record_id', $alert_id);
        $this->validator->validate('record_id', $account_id);

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('Missing Require Parameter');
        } else {
            if (isset($params['time']) AND $params['time'] == 'range') {
                if (isset($params['endhour']) AND $params['endhour'] > 0) {
                    if ($params['starthour'] > $params['endhour']) {
                        $this->setErrorMessage('End Hour can not be before Start Hour');
                    }
                }
            }

            if (isset($params['alertname']) AND ! empty($params['alertname'])) {
                $this->validator->validate('alert_name', $params['alertname']);
                if ($this->validator->hasError()) {
                    $this->setErrorMessage($this->validator->getErrorMessage());
                } else {
                    $duplicate = $this->alert_data->getAlertByName($account_id, $params['alertname']); 
                    if (! empty($duplicate)) {
                        $this->setErrorMessage('Duplicated Alert Name');
                    }   
                }
            }
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {

            return $this->alert_data->updateAlert($alert_id, $account_id, $params);
        }

        return false;
    }

    /**
     * Update alert territory association
     *
     * @param int alert_id
     * @param array params
     *
     * @return bool
     */
    public function updateAlertTerritory($alert_id, $params)
    {   
        $this->validator->validate('record_id', $alert_id);

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('err_params');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->alert_data->updateAlertTerritory($alert_id, $params);
        }

        return false;
    }

    /**
     * Update alert unit association
     *
     * @param int alert_id
     * @param array params
     *
     * @return bool
     */
    public function updateAlertUnit($alert_id, $params)
    {   
        $this->validator->validate('record_id', $alert_id);

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('err_params');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->alert_data->updateAlertUnit($alert_id, $params);
        }

        return false;
    }

    /**
     * Update alert user association
     *
     * @param int alert_id
     * @param int user_id
     * @param array params
     *
     * @return bool
     */
    public function updateAlertUser($alert_id, $user_id, $params)
    {
        $this->validator->validate('record_id', $alert_id);
        $this->validator->validate('record_id', $user_id);

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('err_params');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->alert_data->updateAlertUser($alert_id, $user_id, $params);
        }

        return false;
    }

    /**
     * Update alert contact
     *
     * @param int alert_id
     * @param array params
     *
     * @return bool
     */
    public function updateAlertContact($alert_id, $params)
    {
        $this->validator->validate('record_id', $alert_id);

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('err_params');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->alert_data->updateAlertContact($alert_id, $params);
        }

        return false;
    }

    /**
     * Update alert history info
     *
     * @param int alert_id
     * @param int unit_id
     * @param array params
     *
     * @return bool
     */
    public function updateAlertHistory($alert_id, $unit_id, $params)
    {   
        $this->validator->validate('record_id', $alert_id);
        $this->validator->validate('record_id', $unit_id);

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('err_params');
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->alert_data->updateAlertHistory($alert_id, $unit_id, $params);
        }

        return false;
    }    

    /**
     * Call to get alerts (if $territory_data is true, pull territory info)
     *
     * @param int|bool $alert_id (if provided, look for this specicied alert)
     * @param bool $territory_data
     *
     * @return array
     */
    public function getAlerts($alert_id = false, $territory_data = false)
    {
        $alerts = array();

        if (isset($alert_id) AND ! empty($alert_id)) {
            $this->validator->validate('record_id', $alert_id);
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            // get all alerts
            $alerts = $this->alert_data->getAlerts($alert_id);
            if ($territory_data){
                if (! empty($alerts)) {
                    foreach($alerts as $key => $alert) {
                        if ($alert['alerttype_id'] == 3 OR $alert['alerttype_id'] == 1 ) {
                            if(isset($alert['territory_id']) AND $alert['territory_id'] != 0) {
                                // get territory
                                $alerts[$key]['territories'][] = $alert['territory_id'];
                            } else if (isset($alert['territorygroup_id']) AND $alert['territorygroup_id'] != 0) {
                                // get all territories for group
                                 $alerts[$key]['territories'] = array();
                                 $territories = $this->territory_logic->getTerritoryInfoByGroupId($alert['territorygroup_id']);
                                 if ($territories !== false AND ! empty($territories)) {
                                     foreach($territories as $id => $territory) {
                                         $alerts[$key]['territories'][] = $territory['territory_id'];
                                     }
                                 }
                            } else {
                                // get all territories
                                 $alerts[$key]['territories'] = array();
                                 $territories = $this->territory_logic->getTerritoriesByAccountId($alert['account_id']);
                                 if ($territories !== false AND ! empty($territories)) {
                                     foreach($territories as $id => $territory) {
                                         $alerts[$key]['territories'][] = $territory['territory_id'];
                                     }
                                 }
                            }
                        }
                    }
                }
            }
            
            return $alerts;
        }

        return false;
    }

    public function getAlertContactById($alert_id)
    {
        $this->validator->validate('record_id', $alert_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            $contacts = $this->alert_data->getAlertContactById($alert_id);
            
            return $contacts;
        }

        return false;  
    }

    /**
     * Get Alert by alert_id
     *
     * @param int alert_id
     *
     * @return bool|array
     */
    public function getAlertById($alert_id)
    {
        $this->validator->validate('record_id', $alert_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }
        
        if (! $this->hasError()) {
            $alert = $this->alert_data->getAlertById($alert_id);
            if ($alert !== false AND is_array($alert) AND ! empty($alert)) {
                $alert = array_pop($alert);    
            }
            return $alert;
        }

        return false;
    }

    /**
     * Gets alert emails that have not been processed
     *
     * @return array
     */
    public function getAlertDataInfoById($alert_id)
    {
        $this->validator->validate('record_id', $alert_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {

            $alert = $this->alert_data->getAlerts($alert_id);

            if (! empty($alert) AND count($alert) == 1) {
                $alert = array_pop($alert);

                $contacts = array();
                if (isset($alert['contact_id']) AND ! empty($alert['contact_id'])) {
                    // get this contact
                    $contacts = $this->contact_logic->getContactById($alert['contact_id']);
                } else if (isset($alert['contactgroup_id']) AND ! empty($alert['contactgroup_id'])) {
                    $contacts = $this->contact_logic->getContactByGroupId($alert['contactgroup_id']);
                }

                $alert['contacts'] = $contacts;
            }

            return $alert;
        }

        return false;
    }
    
    /**
     * Delete alert territory by alert_id
     *
     * @param int alert_id
     *
     * @return bool
     */
    public function deleteAlertTerritory($alert_id)
    {
        $this->validator->validate('record_id', $alert_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            return $this->alert_data->deleteAlertTerritory($alert_id);
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
