<?php

namespace Models\Data;

use Models\Data\BaseData;
use GTC\Component\Utils\Date;

class AlertData extends BaseData
{
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * Get the alert types
     *
     * @return array | bool
     */
    public function getAlertTypes()
    {
        $sql = "SELECT 
                    *
                FROM alerttype
                ORDER BY alerttypename ASC";

        $alerts = $this->db_read->fetchAll($sql);

        return $alerts; 
    }

    /**
     * Get the alerts by account_id
     *
     * @params: account_id
     *
     * @return array | bool
     */
    public function getAlertsByAccountId($account_id)
    {
        $data = array();

        $sql = "SELECT 
                    alert.*,
                    alert_territory.*,
                    territorygroup.*,
                    alert_unit.*,
                    unit.*,
                    unitgroup.*,
                    crossbones.territory.territory_id,
                    crossbones.territory.territorycategory_id,
                    crossbones.territory.account_id,
                    crossbones.territory.territorytype,
                    crossbones.territory.shape,
                    crossbones.territory.territoryname,
                    crossbones.territory.latitude,
                    crossbones.territory.longitude,
                    crossbones.territory.radius,
                    crossbones.territory.streetaddress,
                    crossbones.territory.city,
                    crossbones.territory.state,
                    crossbones.territory.zipcode,
                    crossbones.territory.country,
                    crossbones.territory.verifydate,
                    crossbones.territory.active,
                    alert_contact.contact_id as alert_contact_id,
                    alert_contact.contactgroup_id as alert_contactgroup_id,
                    alert_contact.method as alert_contact_method,
                    contact.*,
                    contactgroup.*,
                    tz.*,
                    alerttype.*
                FROM alert 
                LEFT JOIN alert_territory ON alert.alert_id = alert_territory.alert_id
                LEFT JOIN territory ON territory.territory_id = alert_territory.territory_id
                LEFT JOIN territorygroup ON territorygroup.territorygroup_id = alert_territory.territorygroup_id
                LEFT JOIN alert_unit ON alert_unit.alert_id = alert.alert_id
                LEFT JOIN unit ON unit.unit_id = alert_unit.unit_id
                LEFT JOIN unitmanagement.timezone tz ON tz.timezone_id = unit.timezone_id
                LEFT JOIN unitgroup ON unitgroup.unitgroup_id = alert_unit.unitgroup_id
                LEFT JOIN alert_contact ON alert_contact.alert_id = alert.alert_id
                LEFT JOIN contact ON contact.contact_id = alert_contact.contact_id
                LEFT JOIN contactgroup ON contactgroup.contactgroup_id = alert_contact.contactgroup_id
                LEFT JOIN alerttype ON alerttype.alerttype_id = alert.alerttype_id
                WHERE alert.account_id = ? AND alert.active = 1
                ORDER BY alert.alertname ASC";


        $data = $this->db_read->fetchAll($sql, array($account_id));
        
        return $data;
    }

    /**
     * Get the alerts by account_id
     *
     * @params: account_id
     *
     * @return array | bool
     */
    public function getAlertsByUserId($account_id)
    {
        $data = array();

        $sql = "SELECT 
                    alert.*,
                    alert_territory.*,
                    territorygroup.*,
                    alert_unit.*,
                    unit.*,
                    unitgroup.*,
                    crossbones.territory.territory_id,
                    crossbones.territory.territorycategory_id,
                    crossbones.territory.account_id,
                    crossbones.territory.territorytype,
                    crossbones.territory.shape,
                    crossbones.territory.territoryname,
                    crossbones.territory.latitude,
                    crossbones.territory.longitude,
                    crossbones.territory.radius,
                    crossbones.territory.streetaddress,
                    crossbones.territory.city,
                    crossbones.territory.state,
                    crossbones.territory.zipcode,
                    crossbones.territory.country,
                    crossbones.territory.verifydate,
                    crossbones.territory.active,
                    alert_contact.contact_id as alert_contact_id,
                    alert_contact.contactgroup_id as alert_contactgroup_id,
                    alert_contact.method as alert_contact_method,
                    contact.*,
                    contactgroup.*,
                    tz.*,
                    alerttype.*
                FROM alert 
                LEFT JOIN alert_territory ON alert.alert_id = alert_territory.alert_id
                LEFT JOIN territory ON territory.territory_id = alert_territory.territory_id
                LEFT JOIN territorygroup ON territorygroup.territorygroup_id = alert_territory.territorygroup_id
                LEFT JOIN alert_unit ON alert_unit.alert_id = alert.alert_id
                LEFT JOIN unit ON unit.unit_id = alert_unit.unit_id
                LEFT JOIN unitmanagement.timezone tz ON tz.timezone_id = unit.timezone_id
                LEFT JOIN unitgroup ON unitgroup.unitgroup_id = alert_unit.unitgroup_id
                LEFT JOIN alert_contact ON alert_contact.alert_id = alert.alert_id
                LEFT JOIN contact ON contact.contact_id = alert_contact.contact_id
                LEFT JOIN contactgroup ON contactgroup.contactgroup_id = alert_contact.contactgroup_id
                LEFT JOIN alerttype ON alerttype.alerttype_id = alert.alerttype_id
                WHERE alert.account_id = ? AND alert.active = 1
                ORDER BY alert.alertname ASC";


        $data = $this->db_read->fetchAll($sql, array($account_id));
        
        return $data;
    }

    /**
     * Get the filtered alerts by $params (string search)
     *
     * @params: int account_id
     * @params: array $params
     * @params: array $searchfields
     *
     * @return array | bool
     */    
    public function getFilteredAlertsStringSearch($account_id, $params, $searchfields)
    {
        $sql_params = array($account_id);

        $where_search_string = "";
        if (isset($params['search_string']) AND $params['search_string'] != '') {

            $search_string = $params['search_string'];
            if (! empty($searchfields) AND is_array($searchfields)) {
                $where_search_string = "AND (";

                foreach ($searchfields as $key => $fieldname) {
                    $where_search_string .= "`".$fieldname."` LIKE ? OR ";
                    $sql_params[] = '%'.str_replace("_", "\_", $search_string).'%';
                }
                
                $where_search_string = substr($where_search_string, 0, -4);
                $where_search_string .= ")";
            }
        }

        $sql = "SELECT 
                    alert.*,
                    alert_territory.*,
                    territorygroup.*,
                    alert_unit.*,
                    unit.*,
                    unitgroup.*,
                    crossbones.territory.territory_id,
                    crossbones.territory.territorycategory_id,
                    crossbones.territory.account_id,
                    crossbones.territory.territorytype,
                    crossbones.territory.shape,
                    crossbones.territory.territoryname,
                    crossbones.territory.latitude,
                    crossbones.territory.longitude,
                    crossbones.territory.radius,
                    crossbones.territory.streetaddress,
                    crossbones.territory.city,
                    crossbones.territory.state,
                    crossbones.territory.zipcode,
                    crossbones.territory.country,
                    crossbones.territory.verifydate,
                    crossbones.territory.active,
                    alert_contact.contact_id as alert_contact_id,
                    alert_contact.contactgroup_id as alert_contactgroup_id,
                    alert_contact.method as alert_contact_method,
                    contact.*,
                    contactgroup.*,
                    tz.*,
                    alerttype.*
                FROM alert 
                LEFT JOIN alert_territory ON alert.alert_id = alert_territory.alert_id
                LEFT JOIN crossbones.territory ON crossbones.territory.territory_id = alert_territory.territory_id
                LEFT JOIN territorygroup ON territorygroup.territorygroup_id = alert_territory.territorygroup_id
                LEFT JOIN alert_unit ON alert_unit.alert_id = alert.alert_id
                LEFT JOIN unit ON unit.unit_id = alert_unit.unit_id
                LEFT JOIN unitmanagement.timezone tz ON tz.timezone_id = unit.timezone_id
                LEFT JOIN unitgroup ON unitgroup.unitgroup_id = alert_unit.unitgroup_id
                LEFT JOIN alert_contact ON alert_contact.alert_id = alert.alert_id
                LEFT JOIN contact ON contact.contact_id = alert_contact.contact_id
                LEFT JOIN contactgroup ON contactgroup.contactgroup_id = alert_contact.contactgroup_id
                LEFT JOIN alerttype ON alerttype.alerttype_id = alert.alerttype_id
                WHERE alert.account_id = ? AND alert.active = 1 {$where_search_string}
                ORDER BY alert.alertname ASC";

        $alerts = $this->db_read->fetchAll($sql, $sql_params);

        return $alerts;
    }

    /**
     * Get the filtered alerts by $params
     *
     * @params: int user_id
     * @params: array $params
     *
     * @return array | bool
     */    
    public function getFilteredAlerts($account_id, $params)
    {
        $sql_params = array($account_id);
        $where_vehicle_groups = "";
        if (isset($params['vehiclegroup_id']) AND ! empty($params['vehiclegroup_id'])) {
            $where_vehicle_groups = "AND unitgroup.unitgroup_id IN (" . substr(str_repeat('?,', count($params['vehiclegroup_id'])), 0, -1) . ") ";
            $sql_params = array_merge($sql_params, array_values($params['vehiclegroup_id']));
        }

        $where_alert_type = "";
        if (isset($params['alert_type']) AND $params['alert_type'] != '') {
            $alert_type = $params['alert_type'];
            $where_alert_type = "AND alert.alerttype_id = ? ";
            $sql_params[] = $alert_type;
        }
        
        $where_contact_groups = "";
        if (isset($params['contactgroup_id']) AND ! empty($params['contactgroup_id'])) {
            $where_contact_groups = "AND contactgroup.contactgroup_id IN ("  . substr(str_repeat('?,', count($params['contactgroup_id'])), 0, -1) . ") ";
            $sql_params = array_merge($sql_params, array_values($params['contactgroup_id']));
        }

        $sql = "SELECT 
                    alert.*,
                    alert_territory.*,
                    territorygroup.*,
                    alert_unit.*,
                    unit.*,
                    unitgroup.*,
                    crossbones.territory.territory_id,
                    crossbones.territory.territorycategory_id,
                    crossbones.territory.account_id,
                    crossbones.territory.territorytype,
                    crossbones.territory.shape,
                    crossbones.territory.territoryname,
                    crossbones.territory.latitude,
                    crossbones.territory.longitude,
                    crossbones.territory.radius,
                    crossbones.territory.streetaddress,
                    crossbones.territory.city,
                    crossbones.territory.state,
                    crossbones.territory.zipcode,
                    crossbones.territory.country,
                    crossbones.territory.verifydate,
                    crossbones.territory.active,
                    alert.alert_id as alert_id,
                    alert_contact.contact_id as alert_contact_id,
                    alert_contact.contactgroup_id as alert_contactgroup_id,
                    alert_contact.method as alert_contact_method,
                    contact.*,
                    CONCAT(contact.firstname, ' ', contact.lastname) as contactname,
                    contactgroup.*,
                    tz.*,
                    alerttype.*
                FROM alert 
                LEFT JOIN alert_territory ON alert.alert_id = alert_territory.alert_id
                LEFT JOIN territory ON territory.territory_id = alert_territory.territory_id
                LEFT JOIN territorygroup ON territorygroup.territorygroup_id = alert_territory.territorygroup_id
                LEFT JOIN alert_unit ON alert_unit.alert_id = alert.alert_id
                LEFT JOIN unit ON unit.unit_id = alert_unit.unit_id
                LEFT JOIN unitmanagement.timezone tz ON tz.timezone_id = unit.timezone_id
                LEFT JOIN unitgroup ON unitgroup.unitgroup_id = alert_unit.unitgroup_id
                LEFT JOIN alert_contact ON alert_contact.alert_id = alert.alert_id
                LEFT JOIN contact ON contact.contact_id = alert_contact.contact_id
                LEFT JOIN contactgroup ON contactgroup.contactgroup_id = alert_contact.contactgroup_id
                LEFT JOIN alerttype ON alerttype.alerttype_id = alert.alerttype_id
                WHERE alert.account_id = ? AND alert.active = 1 {$where_vehicle_groups}{$where_alert_type}{$where_contact_groups}
                ORDER BY alert.alertname ASC";

        $alerts = $this->db_read->fetchAll($sql, $sql_params);
        return $alerts;       
    }

    /**
     * Get the filtered alerts by $params (string search)
     *
     * @params: int user_id
     * @params: array $params
     * @params: array $searchfields
     *
     * @return array | bool
     */    
    public function getAlertHistoryStringSearch($account_id, $params, $searchfields)
    {
        $sql_params = array($account_id);
        
        $end_date = Date::locale_to_utc();
        
        // limit string search to 90 days ago for alert history
        $start_date = date('Y-m-d H:i:s', mktime(0, 0, 0, date("m"), date("d")-90, date("Y"))); 
        
        $start_date = Date::locale_to_utc($start_date);
         
        $where_search_string = "";
        if (isset($params['search_string']) AND $params['search_string'] != '') {

            $search_string = $params['search_string'];
            if (! empty($searchfields) AND is_array($searchfields)) {
                $where_search_string = "AND (";
                
                foreach ($searchfields as $key => $fieldname) {
                    $where_search_string .= "".$fieldname." LIKE ? OR ";
                    $sql_params[] = '%'.str_replace("_", "\_", $search_string).'%';
                }

                $where_search_string = substr($where_search_string, 0, -4);
                $where_search_string .= ")";
            }
        }
        
        $sql_params[] = $start_date;
        $sql_params[] = $end_date;

        $sql = "SELECT 
                    alert.*,
                    unit.*,
                    unitgroup.*,
                    alerthistory.*,
                    contactgroup.*,
                    tz.*,
                    alerttype.*
                FROM alert 
                LEFT JOIN alert_unit ON alert_unit.alert_id = alert.alert_id
                LEFT JOIN unit ON unit.unit_id = alert_unit.unit_id
                LEFT JOIN unitmanagement.timezone tz ON tz.timezone_id = unit.timezone_id
                LEFT JOIN unitgroup ON unitgroup.unitgroup_id = unit.unitgroup_id
                LEFT JOIN alert_contact ON alert_contact.alert_id = alert.alert_id
                LEFT JOIN contactgroup ON contactgroup.contactgroup_id = alert_contact.contactgroup_id
                LEFT JOIN alerttype ON alerttype.alerttype_id = alert.alerttype_id
                INNER JOIN alerthistory ON alerthistory.alert_id = alert.alert_id
                WHERE alert.account_id = ? AND alert.active = 1 {$where_search_string} AND uniteventdate > ? AND uniteventdate <= ? 
                ORDER BY alerthistory.uniteventdate DESC, alert.alertname ASC";

        $alerts = $this->db_read->fetchAll($sql, $sql_params);

        return $alerts;
    }

    /**
     * Get the filtered alerts by $params
     *
     * @params: int user_id
     * @params: array $params
     *
     * @return array | bool
     */    
    public function getAlertHistory($account_id, $params)
    {
        $sql_params = array($account_id);
        
        $where_vehicle_groups = "";
        if (isset($params['vehiclegroup_id']) AND ! empty($params['vehiclegroup_id']) AND $params['vehiclegroup_id'] != 'All') {
            $where_vehicle_groups = " AND unitgroup.unitgroup_id IN (" . substr(str_repeat('?,', count($params['vehiclegroup_id'])), 0, -1) . ") ";
            $sql_params = array_merge($sql_params, array_values($params['vehiclegroup_id']));
        }

        $where_contact_groups = "";
        if (isset($params['contactgroup_id']) AND ! empty($params['contactgroup_id']) AND $params['contactgroup_id'] != 'All') {
            $where_contact_groups  = " AND contactgroup.contactgroup_id IN (" . substr(str_repeat('?,', count($params['contactgroup_id'])), 0, -1) . ") ";
            $sql_params = array_merge($sql_params, array_values($params['contactgroup_id']));
        }

        $where_alert_type = "";
        if (isset($params['alert_type']) AND $params['alert_type'] != '' AND $params['alert_type'] != 'All') {
            $where_alert_type = " AND alert.alerttype_id = ? ";
            $sql_params[] = $params['alert_type'];
        }

        $where_alert_id = '';
        if (isset($params['alert_id']) AND $params['alert_id'] != '' AND $params['alert_id'] != 'All') {
            $where_alert_id = " AND alert.alert_id = ? ";
            $sql_params[] = $params['alert_id'];
        }

        $where_date = '';
        if (isset($params['start_date']) AND $params['start_date'] != '') {
            $where_date = " AND alerthistory.uniteventdate >= ? AND alerthistory.uniteventdate < ?";
            $sql_params[] = $params['start_date'];
            $sql_params[] = $params['end_date'];
        }

        $sql = "SELECT 
                    alert.*,
                    unit.*,
                    unitgroup.*,
                    alerthistory.*,
                    alert.alert_id as alert_id,
                    contactgroup.*,
                    tz.*,
                    alerttype.*
                FROM alert
                LEFT JOIN alert_unit ON alert_unit.alert_id = alert.alert_id
                LEFT JOIN unit ON unit.unit_id = alert_unit.unit_id
                LEFT JOIN unitmanagement.timezone tz ON tz.timezone_id = unit.timezone_id
                LEFT JOIN unitgroup ON unitgroup.unitgroup_id = unit.unitgroup_id
                LEFT JOIN alert_contact ON alert_contact.alert_id = alert.alert_id
                LEFT JOIN contactgroup ON contactgroup.contactgroup_id = alert_contact.contactgroup_id
                LEFT JOIN alerttype ON alerttype.alerttype_id = alert.alerttype_id
                INNER JOIN alerthistory ON alerthistory.alert_id = alert.alert_id
                WHERE alert.account_id = ? AND alert.active = 1 {$where_vehicle_groups}{$where_contact_groups}{$where_alert_type}{$where_alert_id}{$where_date}
                ORDER BY alerthistory.uniteventdate DESC, alert.alertname ASC";

        $alerts = $this->db_read->fetchAll($sql, $sql_params);

        return $alerts;       
    }

    /**
     * Get the filtered alerts by $params
     *
     * @params: int user_id
     * @params: array $params
     *
     * @return array | bool
     */    
    public function getAlertHistoryReport($account_id, $params)
    {
        $sql_params = array($account_id);
        
        $where_vehicles = "";
        if (isset($params['vehicle_id']) AND ! empty($params['vehicle_id'])) {
            $where_vehicles = " AND unit.unit_id IN (" . substr(str_repeat('?,', count($params['vehicle_id'])), 0, -1) . ") ";
            $sql_params = array_merge($sql_params, array_values($params['vehicle_id']));
        }
        
        $where_vehicle_groups = "";
        if (isset($params['vehiclegroup_id']) AND ! empty($params['vehiclegroup_id'])) {
            $where_vehicle_groups = " AND unitgroup.unitgroup_id IN (" . substr(str_repeat('?,', count($params['vehiclegroup_id'])), 0, -1) . ") ";
            $sql_params = array_merge($sql_params, array_values($params['vehiclegroup_id']));
        }

        $where_alert_type = "";
        if (isset($params['alerttype_id']) AND $params['alerttype_id'] != '') {
            $where_alert_type = " AND alert.alerttype_id = ? ";
            $sql_params[] = $params['alerttype_id'];
        }

        $where_alert_id = '';
        if (isset($params['alert_id']) AND $params['alert_id'] != '') {
            $where_alert_id = " AND alert.alert_id = ? ";
            $sql_params[] = $params['alert_id'];
        }

        $where_date = '';
        if (isset($params['start_date']) AND $params['start_date'] != '' AND isset($params['end_date']) AND $params['end_date'] != '') {
            $where_date = " AND alerthistory.uniteventdate >= ? AND alerthistory.uniteventdate < ?";
            $sql_params[] = $params['start_date'];
            $sql_params[] = $params['end_date'];
        }

        $sql = "SELECT 
                    alerthistory.*,
                    unit.*,
                    unitgroup.*,
                    alert.*,
                    alert.alert_id as alert_id,
                    alerttype.*
                FROM alerthistory
                LEFT JOIN unit ON unit.unit_id = alerthistory.unit_id
                LEFT JOIN unitgroup ON unitgroup.unitgroup_id = unit.unitgroup_id
                INNER JOIN alert ON alert.alert_id = alerthistory.alert_id
                LEFT JOIN alerttype ON alerttype.alerttype_id = alert.alerttype_id
                WHERE alert.account_id = ? {$where_vehicles}{$where_vehicle_groups}{$where_alert_type}{$where_alert_id}{$where_date}
                ORDER BY alerthistory.uniteventdate DESC, alert.alertname ASC";

        $alerts = $this->db_read->fetchAll($sql, $sql_params);

        return $alerts;       
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
        $sql = "SELECT 
                    CONCAT(contact.firstname, ' ', contact.lastname) as contactname,
                    alert_contact.alert_id as alert_id,
                    alert_contact.contact_id as alert_contact_id,
                    alert_contact.contactgroup_id as alert_contactgroup_id,
                    contact.*,
                    contactgroup.*
                FROM alert_contact
                LEFT JOIN contact ON alert_contact.contact_id = contact.contact_id
                LEFT JOIN contactgroup ON alert_contact.contactgroup_id = contactgroup.contactgroup_id
                WHERE alert_contact.alert_id = ? 
                ORDER BY contactname ASC";

        $alerts = $this->db_read->fetchAll($sql, array($alert_id));

        return $alerts; 
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
        $sql = $columns = $values = '';
        $sql_params = array();

        $columns = '`' . implode('`,`', array_keys($params)) . '`'; // column names
        $values = substr(str_repeat('?,', count($params)), 0, -1);  // placeholders
        $sql_params = array_values($params);                        // array of values
               
        $sql = 'INSERT INTO crossbones.alert (' . $columns . ') VALUES (' . $values . ')';
        
        // return $sql . ' : ' . implode(',',$sql_params);
        if ($this->db_read->executeQuery($sql, $sql_params)) {
            return $this->db_read->lastInsertId();
        } else {
            $this->setErrorMessage('err_database');
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
        if ($this->db_read->update('crossbones.alert', array('active' => 0), array('alert_id' => $alert_id, 'account_id' => $account_id))) {
            // else, remove the associations
            $this->db_read->delete('crossbones.alert_territory', array('alert_id' => $alert_id));
            $this->db_read->delete('crossbones.alert_unit', array('alert_id' => $alert_id));
            $this->db_read->delete('crossbones.alert_contact', array('alert_id' => $alert_id));
            
            return true;    
        }        

        return false;
    }
    
    /**
     * Add alert unit
     *
     * @param array params
     *
     * @return bool
     */ 
    public function addAlertUnit($params)
    {
        if ($this->db_read->insert('crossbones.alert_unit', $params)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Add alert territory
     *
     * @param array params
     *
     * @return bool
     */ 
    public function addAlertTerritory($params)
    {
        if ($this->db_read->insert('crossbones.alert_territory', $params)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Add alert contact
     *
     * @param array params
     *
     * @return bool
     */ 
    public function addAlertContact($params)
    {   
        if ($this->db_read->insert('crossbones.alert_contact', $params)) {
            return true;
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
        if ($this->db_read->update('crossbones.alert', $params, array('alert_id' => $alert_id, 'account_id' => $account_id)) !== false) {
            return true;    
        }        

        return false;
    }

    /**
     * Update alert territory association
     * NOTE: 'alert_id' is a unique key in this table
     *
     * @param int alert_id
     * @param array params
     *
     * @return bool
     */
    public function updateAlertTerritory($alert_id, $params)
    {
        $columns = $update = '';
        $values = '?';
        $values_arr = array();

        foreach ($params as $col => $value) {
            $columns .= ',' . $col;
            $values .= ',?';
            $update .= $col . ' = ?,';
            $values_arr[] = $value;     
        }

        $update = substr($update, 0, -1);        
        
        $sql_params = array_merge(array($alert_id), $values_arr, $values_arr);
        
        $sql = "INSERT INTO crossbones.alert_territory (alert_id{$columns}) 
                VALUES ({$values}) 
                ON DUPLICATE KEY UPDATE {$update}";

        if ($this->db_read->executeQuery($sql, $sql_params)) {
            return true;
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
        if ($this->db_read->update('crossbones.alert_unit', $params, array('alert_id' => $alert_id))) {
            return true;
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
        //if ($this->db_read->update('crossbones.alert_user', $params, array('alert_id' => $alert_id))) {
            //return true;
        //}

        return false;
    }
    
    /**
     * Update alert contact
     * NOTE: 'alert_id' is a unique key in this table
     *
     * @param int alert_id
     * @param array params
     *
     * @return bool
     */
    public function updateAlertContact($alert_id, $params)
    {
        $columns = $update = '';
        $values = '?';
        $values_arr = array();

        foreach ($params as $col => $value) {
            $columns .= ',' . $col;
            $values .= ',?';
            $update .= $col . ' = ?,';
            $values_arr[] = $value;     
        }
        
        $update = substr($update, 0, -1);

        $sql_params = array_merge(array($alert_id), $values_arr, $values_arr);
        
        $sql = "INSERT INTO crossbones.alert_contact (alert_id{$columns}) 
                VALUES ({$values}) 
                ON DUPLICATE KEY UPDATE {$update}";

        if ($this->db_read->executeQuery($sql, $sql_params)) {
            return true;
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
        //if ($this->db_read->update('crossbones.alerthistory', $params, array('alert_id' => $alert_id))) {
            //return true;
        //}

        return false;
    }


    /**
     * Get all the alerts info. if alert_id provided, then get that specified alert info
     *
     * @param int $alert_id
     *
     * @return bool
     */
    public function getAlerts($alert_id = '')
    {
        $sql_params = array();
        
        $where_alert_id = '';
        if (isset($alert_id) AND $alert_id != '') {
            $where_alert_id = " AND alert.alert_id = ?";
            $sql_params[] = $alert_id;
        }

        $sql = "SELECT 
                    alert_territory.*,
                    alert_unit.*,
                    alert_contact.*,
                    alerttype.*,
                    alert.*
                FROM alert 
                LEFT JOIN alert_territory ON alert.alert_id = alert_territory.alert_id
                LEFT JOIN alert_unit ON alert_unit.alert_id = alert.alert_id
                LEFT JOIN alert_contact ON alert_contact.alert_id = alert.alert_id
                LEFT JOIN alerttype ON alerttype.alerttype_id = alert.alerttype_id
                WHERE alert.active = 1 {$where_alert_id}
                ORDER BY alert.account_id, alert.alert_id ASC";

        $data = $this->db_read->fetchAll($sql, $sql_params);

        return $data;
    }

    /**
     * Check Unit Status
     *
     * @param int $unit_id
     *
     * @return array
     */
    public function getAlertUnitStatus($unit_id)
    {
        $sql_params = array($unit_id);
        
        $sql = "SELECT 
                    unit.unitstatus_id
                FROM unit 
                WHERE unit.unit_id = ?
                LIMIT 1";

        $data = $this->db_read->fetchAll($sql, $sql_params);

        return $data;
    }

    /**
     * Set the processed alert to the alertsent table to be pulled for send on another cron cronSendAlerts()
     *
     * @param int $alert_id
     * @param int $account_id
     * @param int $unit_id
     * @param int $triggerid
     * @param string $textmessage
     *
     * @return bool
     */
    public function setAlertSend($alert_id, $account_id, $unit_id, $triggerid, $textmessage, $create_date)
    {
        $params['alert_id']     = $alert_id;
        $params['account_id']   = $account_id;
        $params['unit_id']      = $unit_id;
        $params['unitevent_id'] = $triggerid;
        $params['alerttext']    = $textmessage;
        $params['createdate']   = $create_date;

        if ($this->db_read->insert('crossbones.alertsend', $params)) {
            return true;
        }

        return false;
    }

    /**
     * Update the alert to have sent = 1 indicating that the alert was sent
     *
     * @param int $alertsend_id
     * @param array $params
     *
     * @return bool
     */
    public function updateAlertSend($alertsend_id, $params)
    {
        if ($this->db_read->update('crossbones.alertsend', $params, array('alertsend_id' => $alertsend_id))) {
            return true;
        }
        
        return false;
    }

    /**
     * Delete those alerts that has already been sent
     *
     * @return bool
     */
    public function deleteAlertSent()
    {
        $sql = 'DELETE FROM alertsend WHERE `sent` = 1';
       
        if ($this->db_read->executeQuery($sql)) {
            return true;
        }

        return false;
    }

    /**
     * Get all the alerts to send
     *
     * @param array alert
     * @param date $alert_sent_date
     *
     * @return bool
     */
    public function getAlertSendEmail($alertSendThreshold = null)
    {
        $data = array();
        $limit = 500;
        
        if (isset($alertSendThreshold) AND ! empty($alertSendThreshold)) {
            $limit = $alertSendThreshold;
        }
        
        $sql = "SELECT 
                    *
                FROM alertsend
                WHERE `sent` = 0
                ORDER BY sentdate, alertsend_id
                LIMIT {$limit}";

        $data = $this->db_read->fetchAll($sql);
        
        return $data;
    }

    /**
     * Log the sent alert to the AlertHistory table
     *
     * @param array alert
     * @param date $alert_sent_date
     *
     * @return bool
     */
    public function logAlertHistory($alert, $alert_send_datetime)
    {
        $params['alert_id']         = $alert['alert_id'];
        $params['unit_id']          = $alert['unit_id'];
        $params['unitevent_id']     = $alert['unitevent_id'];
        $params['alertdetail']      = $alert['alerttext'];
        $params['uniteventdate']    = $alert['uniteventdate'];
        $params['alerthistorydate'] = $alert_send_datetime;
        $params['territoryname']    = $alert['territoryname'];
        $params['streetaddress']    = $alert['streetaddress'];
        $params['city']             = $alert['city'];
        $params['state']            = $alert['state'];
        $params['zipcode']          = $alert['zipcode'];
        $params['country']          = $alert['country'];
        
        if ($this->db_read->insert('crossbones.alerthistory', $params)) {
            return true;
        }

        return false;
    }
    
    /**
     * Get Alert by alert_id
     *
     * @param int alert_id
     *
     * @return array
     */
    public function getAlertById($alert_id)
    {

        $sql = "SELECT 
                    alert.*,
                    alert_territory.*,
                    territorygroup.*,
                    alert_unit.*,
                    unit.*,
                    unitgroup.*,
                    crossbones.territory.territory_id,
                    crossbones.territory.territorycategory_id,
                    crossbones.territory.account_id,
                    crossbones.territory.territorytype,
                    crossbones.territory.shape,
                    crossbones.territory.territoryname,
                    crossbones.territory.latitude,
                    crossbones.territory.longitude,
                    crossbones.territory.radius,
                    crossbones.territory.streetaddress,
                    crossbones.territory.city,
                    crossbones.territory.state,
                    crossbones.territory.zipcode,
                    crossbones.territory.country,
                    crossbones.territory.verifydate,
                    crossbones.territory.active,
                    alert.alert_id as alert_id,
                    alert_contact.contact_id as alert_contact_id,
                    alert_contact.contactgroup_id as alert_contactgroup_id,
                    alert_contact.method as alert_contact_method,
                    contact.*,
                    CONCAT(contact.firstname, ' ', contact.lastname) as contactname,
                    contactgroup.*,
                    tz.*,
                    alerttype.*
                FROM alert 
                LEFT JOIN alert_territory ON alert.alert_id = alert_territory.alert_id
                LEFT JOIN territory ON territory.territory_id = alert_territory.territory_id
                LEFT JOIN territorygroup ON territorygroup.territorygroup_id = alert_territory.territorygroup_id
                LEFT JOIN alert_unit ON alert_unit.alert_id = alert.alert_id
                LEFT JOIN unit ON unit.unit_id = alert_unit.unit_id
                LEFT JOIN unitmanagement.timezone tz ON tz.timezone_id = unit.timezone_id
                LEFT JOIN unitgroup ON unitgroup.unitgroup_id = alert_unit.unitgroup_id
                LEFT JOIN alert_contact ON alert_contact.alert_id = alert.alert_id
                LEFT JOIN contact ON contact.contact_id = alert_contact.contact_id
                LEFT JOIN contactgroup ON contactgroup.contactgroup_id = alert_contact.contactgroup_id
                LEFT JOIN alerttype ON alerttype.alerttype_id = alert.alerttype_id
                WHERE alert.alert_id = ? AND alert.active = 1
                LIMIT 1";

        $alert = $this->db_read->fetchAll($sql, array($alert_id));

        return $alert;
    }

    /**
     * Get Alert by alert name
     *
     * @param int account_id
     * @param string alertname
     *
     * @return array
     */
    public function getAlertByName($account_id, $alertname)
    {
        $sql = "SELECT 
                    alert.*,
                    alert_territory.*,
                    territorygroup.*,
                    alert_unit.*,
                    unit.*,
                    unitgroup.*,
                    crossbones.territory.territory_id,
                    crossbones.territory.territorycategory_id,
                    crossbones.territory.account_id,
                    crossbones.territory.territorytype,
                    crossbones.territory.shape,
                    crossbones.territory.territoryname,
                    crossbones.territory.latitude,
                    crossbones.territory.longitude,
                    crossbones.territory.radius,
                    crossbones.territory.streetaddress,
                    crossbones.territory.city,
                    crossbones.territory.state,
                    crossbones.territory.zipcode,
                    crossbones.territory.country,
                    crossbones.territory.verifydate,
                    crossbones.territory.active,
                    alert.alert_id as alert_id,
                    alert_contact.contact_id as alert_contact_id,
                    alert_contact.contactgroup_id as alert_contactgroup_id,
                    alert_contact.method as alert_contact_method,
                    contact.*,
                    CONCAT(contact.firstname, ' ', contact.lastname) as contactname,
                    contactgroup.*,
                    tz.*,
                    alerttype.*
                FROM alert 
                LEFT JOIN alert_territory ON alert.alert_id = alert_territory.alert_id
                LEFT JOIN territory ON territory.territory_id = alert_territory.territory_id
                LEFT JOIN territorygroup ON territorygroup.territorygroup_id = alert_territory.territorygroup_id
                LEFT JOIN alert_unit ON alert_unit.alert_id = alert.alert_id
                LEFT JOIN unit ON unit.unit_id = alert_unit.unit_id
                LEFT JOIN unitmanagement.timezone tz ON tz.timezone_id = unit.timezone_id
                LEFT JOIN unitgroup ON unitgroup.unitgroup_id = alert_unit.unitgroup_id
                LEFT JOIN alert_contact ON alert_contact.alert_id = alert.alert_id
                LEFT JOIN contact ON contact.contact_id = alert_contact.contact_id
                LEFT JOIN contactgroup ON contactgroup.contactgroup_id = alert_contact.contactgroup_id
                LEFT JOIN alerttype ON alerttype.alerttype_id = alert.alerttype_id
                WHERE alert.account_id = ? AND alert.alertname = ? AND alert.active = 1
                LIMIT 1";

        $alert = $this->db_read->fetchAll($sql, array($account_id, $alertname));

        return $alert;
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
        if ($this->db_read->delete('crossbones.alert_territory', array('alert_id' => $alert_id))) {
            return true;
        }
        return false;
    }
    
    /**
     * Get last triggered info for an alert
     *
     * @param int alert_id
     *
     * @return bool
     */
    public function getLastAlertTriggered($alert_id)
    {
        $sql = "SELECT crossbones.alerthistory.*
                FROM crossbones.alerthistory
                WHERE crossbones.alerthistory.alert_id = ? 
                ORDER BY crossbones.alerthistory.alerthistory_id DESC 
                LIMIT 1";

        $data = $this->db_read->fetchAll($sql, array($alert_id));
        if ($data !== false) {
            return $data;
        }
        return false;
    }

}
