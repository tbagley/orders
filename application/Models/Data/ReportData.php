<?php

namespace Models\Data;

use Models\Data\BaseData;

class ReportData extends BaseData
{
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * Get report
     *
     * @return array
     */
    public function getReport($sql, $sqlPlaceHolder)
    {
        $report = $this->db_read->fetchAll($sql, $sqlPlaceHolder);
        return $report;
    }

    /**
     * Get all reporttypes
     *
     * @return array
     */
    public function getReportTypes()
    {
        $sql = "SELECT *
                FROM reporttype
                WHERE active = 1
                ORDER BY reporttypename ASC";

        return $this->db_read->fetchAll($sql); 
    }
    
    /**
     * Get the filtered schedule reports by $params (string search)
     *
     * @params: int account_id
     * @params: array $params
     * @params: array $searchfields
     *
     * @return array | bool
     */    
    public function getFilteredScheduleReportsStringSearch($account_id, $params, $searchfields)
    {
        $sqlPlaceHolder = array($account_id);

        $where_search_string = "";
        if (isset($params['search_string']) AND $params['search_string'] != '') {

            $search_string = $params['search_string'];
            if (! empty($searchfields) AND is_array($searchfields)) {
                $where_search_string = "AND (";

                foreach ($searchfields as $key => $fieldname) {
                    $where_search_string .= "`".$fieldname."` LIKE ? OR ";
                    $sqlPlaceHolder[] = '%'.str_replace("_", "\_", $search_string).'%';
                }

                $where_search_string = substr($where_search_string, 0, -4);
                $where_search_string .= ")";
            }
        }

        $sql = "SELECT 
					rt.reporttypename as reporttypename,
					rt.active as reporttypeactive,
					cg.*,
					c.contact_id as contact_id,
					CONCAT(c.firstname, ' ', c.lastname) as contactname,
					sr.*
                FROM schedulereport sr 
                LEFT JOIN reporttype rt ON rt.reporttype_id = sr.reporttype_id
                LEFT JOIN schedulereport_contact src ON sr.schedulereport_id = src.schedulereport_id
                LEFT JOIN contactgroup cg ON src.contactgroup_id = cg.contactgroup_id
                LEFT JOIN contact c ON c.contact_id = src.contact_id
                WHERE sr.account_id = ? AND sr.active = 1 {$where_search_string}
                ORDER BY sr.schedulereportname ASC";

        $reports = $this->db_read->fetchAll($sql, $sqlPlaceHolder);

        return $reports;
    }

    /**
     * Get the filtered schedule reports by $params
     *
     * @params: int user_id
     * @params: array $params
     *
     * @return array | bool
     */    
    public function getFilteredScheduleReports($account_id, $params)
    {
        $reports = array();
        $sqlPlaceHolder = array($account_id);

        $where_reporttype = "";
        if (isset($params['reporttype_id']) AND $params['reporttype_id'] != '') {
            $where_reporttype = " AND sr.reporttype_id = ? ";
            $sqlPlaceHolder[] = $params['reporttype_id'];
        }

        $where_schedule = "";
        if (isset($params['schedule']) AND ! empty($params['schedule'])) {
            $where_schedule = " AND sr.schedule = ? ";
            $sqlPlaceHolder[] = $params['schedule'];
        }

        $where_contact_group = "";
        if (isset($params['contactgroup_id']) AND ! empty($params['contactgroup_id'])) {
            $where_contact_group = " AND cg.contactgroup_id = ? ";
            $sqlPlaceHolder[] = $params['contactgroup_id'];
        }

        $where_contact = "";
        if (isset($params['contact_id']) AND ! empty($params['contact_id'])) {
            $where_contact_group = " AND c.contact_id = ? ";
            $sqlPlaceHolder[] = $params['contact_id'];
        }

        /*
        $where_unit_group = "";
        if (isset($params['unitgroup_id']) AND ! empty($params['unitgroup_id'])) {
            $where_unit_group = " AND ug.unitgroup_id = ? ";
            $sqlPlaceHolder[] = $params['unitgroup_id'];
        }

        $where_unit = "";
        if (isset($params['unit_id']) AND ! empty($params['unit_id'])) {
            $where_unit = " AND u.unit_id = ? ";
            $sqlPlaceHolder[] = $params['unit_id'];
        }

        $where_territory_group = "";
        if (isset($params['territorygroup_id']) AND ! empty($params['territorygroup_id'])) {
            $where_territory_group = " AND t.territorygroup_id = ? ";
            $sqlPlaceHolder[] = $params['territorygroup_id'];
        }

        $where_territory = "";
        if (isset($params['territory_id']) AND ! empty($params['territory_id'])) {
            $where_territory = " AND u.territory_id = ? ";
            $sqlPlaceHolder[] = $params['territory_id'];
        }*/

        $sql = "SELECT 
					rt.reporttypename as reporttypename,
					rt.active as reporttypeactive,
					cg.*,
					c.contact_id as contact_id,
					CONCAT(c.firstname, ' ', c.lastname) as contactname,
					sr.*
                FROM schedulereport sr 
                LEFT JOIN reporttype rt ON rt.reporttype_id = sr.reporttype_id
                LEFT JOIN schedulereport_contact src ON sr.schedulereport_id = src.schedulereport_id
                LEFT JOIN contactgroup cg ON src.contactgroup_id = cg.contactgroup_id
                LEFT JOIN contact c ON c.contact_id = src.contact_id
                WHERE sr.account_id = ? AND sr.active = 1 {$where_reporttype}{$where_schedule}{$where_contact_group}{$where_contact}
                ORDER BY sr.schedulereportname ASC";

        $reports = $this->db_read->fetchAll($sql, $sqlPlaceHolder);

        return $reports;
      
    }

    /**
     * Get the filtered report history by $params (string search)
     *
     * @params: int account_id
     * @params: array $params
     * @params: array $searchfields
     *
     * @return array | bool
     */    
    public function getFilteredReportHistoryStringSearch($account_id, $params, $searchfields)
    {
        $reports = array();
        $sqlPlaceHolder = array($account_id);

        $where_search_string = "";
        if (isset($params['search_string']) AND $params['search_string'] != '') {

            $search_string = $params['search_string'];
            if (! empty($searchfields) AND is_array($searchfields)) {
                $where_search_string = "AND (";

                foreach ($searchfields as $key => $fieldname) {
                    $where_search_string .= "`".$fieldname."` LIKE ? OR ";
                    $sqlPlaceHolder[] = '%'.str_replace("_", "\_", $search_string).'%';
                }

                $where_search_string = substr($where_search_string, 0, -4);
                $where_search_string .= ")";
            }
        }

        $where_fromdate = "";
        if (isset($params['fromdate']) AND ! empty($params['fromdate'])) {
            $where_fromdate = " AND rh.createdate >= ?";
            $sqlPlaceHolder[] = $params['fromdate'];
        }

        $sql = "SELECT 
					rt.reporttypename as reporttypename,
					rt.active as reporttypeactive,
					CONCAT(u.firstname, ' ', u.lastname) as username,
					rh.*
                FROM reporthistory rh 
                LEFT JOIN reporttype rt ON rt.reporttype_id = rh.reporttype_id
                LEFT JOIN user u ON rh.user_id = u.user_id
                WHERE rh.account_id = ? AND rh.createdate >= ''{$where_search_string}{$where_fromdate}
                ORDER BY rh.createdate DESC";

        $reports = $this->db_read->fetchAll($sql, $sqlPlaceHolder);

        return $reports;
    }

    /**
     * Get the filtered eport history by $params
     *
     * @params: int account_id
     * @params: array $params
     *
     * @return array | bool
     */    
    public function getFilteredReportHistory($account_id, $params)
    {
        $reports = array();
        $sqlPlaceHolder = array($account_id);

        $where_reporttype = "";
        if (isset($params['reporttype_id']) AND $params['reporttype_id'] != '') {
            $where_reporttype = " AND rh.reporttype_id = ? ";
            $sqlPlaceHolder[] = $params['reporttype_id'];
        }

        $where_user_id = "";
        if (isset($params['user_id']) AND ! empty($params['user_id'])) {
            $where_user_id = " AND rh.user_id = ? ";
            $sqlPlaceHolder[] = $params['user_id'];
        }

        $where_fromdate = "";
        if (isset($params['fromdate']) AND ! empty($params['fromdate'])) {
            $where_fromdate = " AND rh.createdate >= ?";
            $sqlPlaceHolder[] = $params['fromdate'];
        }

        $where_todate = "";
        if (isset($params['todate']) AND ! empty($params['todate'])) {
            $where_todate = " AND rh.createdate < ?";
            $sqlPlaceHolder[] = $params['todate'];
        }

        $sql = "SELECT 
					rt.reporttypename as reporttypename,
					rt.active as reporttypeactive,
					CONCAT(u.firstname, ' ', u.lastname) as username,
					rh.*
                FROM reporthistory rh 
                LEFT JOIN reporttype rt ON rt.reporttype_id = rh.reporttype_id
                LEFT JOIN user u ON rh.user_id = u.user_id
                WHERE rh.account_id = ? {$where_reporttype}{$where_user_id}{$where_fromdate}{$where_todate}
                ORDER BY rh.createdate DESC";

        $reports = $this->db_read->fetchAll($sql, $sqlPlaceHolder);

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
        $report = array();

        $sql = "SELECT 
					rt.reporttypename as reporttypename,
					rt.active as reporttypeactive,
					cg.*,
					c.contact_id as contact_id,
					CONCAT(c.firstname, ' ', c.lastname) as contactname,
					sru.*,
					sru.selection as unitselection,
					IF(sru.selection = 'unit', 'single', sru.selection) as vehicle_mode, 
					u.unitname as unitname,
					ug.unitgroupname as unitgroupname,
					srt.*,
					srt.selection as territoryselection,
					IF(srt.selection = 'territory', 'single', srt.selection) as landmark_mode,
					tg.territorygroupname as territorygroupname,
					t.territoryname as territoryname,
					ur.user_id as filter_user_id,
					CONCAT(ur.firstname, ' ', ur.lastname) as filter_username,
					IF(srur.selection = 'user', 'single', srur.selection) as filter_user_selection,
					sr.*
                FROM schedulereport sr 
                LEFT JOIN reporttype rt ON rt.reporttype_id = sr.reporttype_id
                LEFT JOIN schedulereport_contact src ON sr.schedulereport_id = src.schedulereport_id
                LEFT JOIN contactgroup cg ON src.contactgroup_id = cg.contactgroup_id
                LEFT JOIN contact c ON c.contact_id = src.contact_id
                LEFT JOIN schedulereport_unit sru ON sru.schedulereport_id = sr.schedulereport_id
                LEFT JOIN unitgroup ug ON ug.unitgroup_id = sru.unitgroup_id
                LEFT JOIN unit u ON u.unit_id = sru.unit_id
                LEFT JOIN schedulereport_territory as srt ON srt.schedulereport_id = sr.schedulereport_id
                LEFT JOIN territorygroup tg ON srt.territorygroup_id = tg.territorygroup_id
                LEFT JOIN territory t ON t.territory_id = srt.territory_id
                LEFT JOIN schedulereport_user as srur ON srur.schedulereport_id = sr.schedulereport_id
                LEFT JOIN user ur ON ur.user_id = srur.user_id
                WHERE sr.schedulereport_id = ? AND sr.active = 1";

        $report = $this->db_read->fetchAll($sql, array($schedulereport_id));

        return $report;
    }

    /**
     * Get Schedule report by id
     *
     * @param int schedulereport_id
     *
     * @return bool|array
     */
    public function getScheduleReportByName($account_id, $report_name)
    {
        $report = array();

        $sql = "SELECT 
					sr.*
                FROM schedulereport sr
                WHERE sr.account_id = ? AND sr.schedulereportname = ? AND sr.active = 1";

        $report = $this->db_read->fetchAll($sql, array($account_id, $report_name));

        return $report;
    }    

    /**
     * Save Schedule report
     *
     * @param array $params
     *
     * @return int|bool
     */
    public function saveScheduleReport($params)
    {
        $sql = $columns = $values = '';
        $sqlPlaceHolder = array();
        
        $columns = '`'. implode('`,`', array_keys($params)) . '`';  // column names
        $values = substr(str_repeat('?,', count($params)), 0, -1);  // placeholders
        $sqlPlaceHolder = array_values($params);                        // array of values
        
        $sql = 'INSERT INTO crossbones.schedulereport (' . $columns . ') VALUES (' . $values . ')';
        
        if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) {
            return $this->db_read->lastInsertId();
        } else {
            $this->setErrorMessage('err_database');
        }
                    
        return false;
    }

    /**
     * Save Schedule report contact
     *
     * @param array $params
     *
     * @return int|bool
     */
    public function saveScheduleReportContact($params)
    {
        if ($this->db_read->insert('crossbones.schedulereport_contact', $params)) {
            
            return $this->db_read->lastInsertId();
        }
        
        return false;
    } 

    /**
     * Save Schedule report unit
     *
     * @param array $params
     *
     * @return int|bool
     */
    public function saveScheduleReportUnit($params)
    {
        if ($this->db_read->insert('crossbones.schedulereport_unit', $params)) {
            
            return $this->db_read->lastInsertId();
        }
        
        return false;
    } 

    /**
     * Save Schedule report territory
     *
     * @param array $params
     *
     * @return int|bool
     */
    public function saveScheduleReportTerritory($params)
    {
        if ($this->db_read->insert('crossbones.schedulereport_territory', $params)) {
            
            return $this->db_read->lastInsertId();
        }
        
        return false;
    } 

    /**
     * Save Schedule report user
     *
     * @param array $params
     *
     * @return int|bool
     */
    public function saveScheduleReportUser($params)
    {
        if ($this->db_read->insert('crossbones.schedulereport_user', $params)) {
            
            return $this->db_read->lastInsertId();
        }
        
        return false;
    } 

    /**
     * Save report history
     *
     * @param array params
     * @return bool|int
     */
    public function saveReportHistory($params)
    {
        if ($this->db_write->insert('crossbones.reporthistory', $params)) {
            return $this->db_write->lastInsertId();    
        }
        return false; 
    }

    /**
     * Get report history by reporthistory id
     *
     * @param int reporthistory_id
     * @return array
     */
    public function getReportHistoryById($reporthistory_id)
    {
        $sql = "SELECT rh.*, 
                rhu.unit_id AS unit_id,
                rhu.unitgroup_id AS unitgroup_id,
                rhu.selection AS unit_mode,
                rht.territory_id AS territory_id,
                rht.territorygroup_id AS territorygroup_id,
                rht.selection AS territory_mode,
                rhus.user_id AS filter_user_id,
                rhus.selection AS user_mode,
                rt.reporttypename AS reporttypename
                FROM reporthistory AS rh
                LEFT JOIN reporthistory_unit AS rhu ON rhu.reporthistory_id = rh.reporthistory_id
                LEFT JOIN reporthistory_territory AS rht ON rht.reporthistory_id = rh.reporthistory_id
                LEFT JOIN reporthistory_user AS rhus ON rhus.reporthistory_id = rh.reporthistory_id
                LEFT JOIN reporttype AS rt ON rt.reporttype_id = rh.reporttype_id
                WHERE rh.reporthistory_id = ?
                LIMIT 1";
        return $this->db_read->fetchAll($sql, array($reporthistory_id)); 
    }

    /**
     * Save units that are associated to a previously ran report
     *
     * @param array params
     * @return bool|int
     */    
    public function saveReportHistoryUnit($params)
    {
        return $this->db_write->insert('crossbones.reporthistory_unit', $params);
    }

    /**
     * Save territories that are associated to a previously ran report
     *
     * @param array params
     * @return bool|int
     */
    public function saveReportHistoryTerritory($params) 
    {
        return $this->db_write->insert('crossbones.reporthistory_territory', $params);
    }

    /**
     * Save users that are associated to a previously ran report
     *
     * @param array params
     * @return bool|int
     */
    public function saveReportHistoryUser($params) 
    {
        return $this->db_write->insert('crossbones.reporthistory_user', $params);
    }
    
    /**
     * Get scheduled reports to run based on the provided search time
     *
     * @param datetime search_time
     * @return bool|array
     */    
    public function getScheduledReportsToRun($search_time) 
    {
        $sql = "SELECT sr.*,
                       sc.contact_id,
                       sc.contactgroup_id,
                       su.unit_id,
                       su.unitgroup_id,
                       su.selection AS unit_mode,
                       st.territory_id,
                       st.territorygroup_id,
                       st.selection AS territory_mode,
                       susr.user_id AS filter_user_id,
                       susr.selection AS selection,
                       tz.timezone AS user_timezone,
                       rt.reporttypename AS reporttypename,
                       IF(sr.minute = 0, NULL, sr.minute) AS minute,
                       IF(sr.day = 0, NULL, sr.day) AS day,
                       IF(sr.mile = 0, NULL, sr.mile) AS mile,
                       IF(sr.mph = 0, NULL, sr.mph) AS mph
                FROM crossbones.schedulereport AS sr
                LEFT JOIN crossbones.schedulereport_contact AS sc ON sc.schedulereport_id = sr.schedulereport_id
                LEFT JOIN crossbones.schedulereport_unit AS su ON su.schedulereport_id = sr.schedulereport_id
                LEFT JOIN crossbones.schedulereport_territory AS st ON st.schedulereport_id = sr.schedulereport_id
                LEFT JOIN crossbones.schedulereport_user AS susr ON susr.schedulereport_id = sr.schedulereport_id
                LEFT JOIN crossbones.user AS us ON us.user_id = sr.user_id
                LEFT JOIN unitmanagement.timezone AS tz ON tz.timezone_id = us.timezone_id
                LEFT JOIN crossbones.reporttype AS rt ON rt.reporttype_id = sr.reporttype_id 
                WHERE sr.nextruntime <= ? AND sr.nextruntime != '0000-00-00 00:00:00' AND sr.active = 1
                ORDER BY account_id ASC";

        return $this->db_read->fetchAll($sql, array($search_time));
    }

    /**
     * Delete Schedule Report (mark as inactive)
     *
     * @param int $report_id
     * @param int account_id
     *
     * @return bool
     */    
    public function deleteScheduledReport($report_id, $account_id) 
    {
        if ($this->db_read->update('crossbones.schedulereport', array('active' => 0), array('schedulereport_id' => $report_id, 'account_id' => $account_id))) {
            // else, remove the associations
            $this->db_read->delete('crossbones.schedulereport_territory', array('schedulereport_id' => $report_id));
            $this->db_read->delete('crossbones.schedulereport_unit', array('schedulereport_id' => $report_id));
            $this->db_read->delete('crossbones.schedulereport_contact', array('schedulereport_id' => $report_id));
            $this->db_read->delete('crossbones.schedulereport_user', array('schedulereport_id' => $report_id));
            
            return true;    
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
        if ($this->db_write->update('crossbones.schedulereport', $params, array('schedulereport_id' => $report_id)) !== false) {
            return true;
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
        $sql = $columns = $update = '';
        $values = '?';
        $values_arr = array();
        
        foreach ($params as $col => $value) {
            $columns .= ',' . $col;
            $values .= ',?';
            $update .= $col . ' = ?,';
            $values_arr[] = $value;
        }

        $update = substr($update, 0, -1);        
        
        $sqlPlaceHolder = array_merge(array($report_id), $values_arr, $values_arr);
        
        $sql = "INSERT INTO crossbones.schedulereport_territory (schedulereport_id{$columns}) 
                VALUES ({$values}) 
                ON DUPLICATE KEY UPDATE {$update}";

        if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) {
            return true;
        }
        return false;
    }

    /**
     * Delete Schedule Report territory association
     *
     * @param int $report_id
     *
     * @return bool
     */    
    public function deleteScheduledReportTerritory($report_id) 
    {
        // else, remove the associations
        $this->db_read->delete('crossbones.schedulereport_territory', array('schedulereport_id' => $report_id));
        
        return true;    
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
        $sql = $columns = $update = '';
        $values = '?';
        $values_arr = array();
        
        foreach ($params as $col => $value) {
            $columns .= ',' . $col;
            $values .= ',?';
            $update .= $col . ' = ?,';
            $values_arr[] = $value;
        }
        
        $update = substr($update, 0, -1);
        
        $sqlPlaceHolder = array_merge(array($report_id), $values_arr, $values_arr);
        
        $sql = "INSERT INTO crossbones.schedulereport_unit (schedulereport_id{$columns}) 
                VALUES ({$values}) 
                ON DUPLICATE KEY UPDATE {$update}";

        if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) {
            return true;
        }
        return false;
    }

    /**
     * Delete Schedule Report unit association
     *
     * @param int $report_id
     *
     * @return bool
     */    
    public function deleteScheduledReportUnit($report_id) 
    {
        // else, remove the associations
        $this->db_read->delete('crossbones.schedulereport_unit', array('schedulereport_id' => $report_id));
        
        return true;    
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
        $sql = $columns = $update = '';
        $values = '?';
        $values_arr = array();
        
        foreach ($params as $col => $value) {
            $columns .= ',' . $col;
            $values .= ',?';
            $update .= $col . ' = ?,';
            $values_arr[] = $value;
        }

        $update = substr($update, 0, -1);
        
        $sqlPlaceHolder = array_merge(array($report_id), $values_arr, $values_arr);
        
        $sql = "INSERT INTO crossbones.schedulereport_user (schedulereport_id{$columns}) 
                VALUES ({$values}) 
                ON DUPLICATE KEY UPDATE {$update}";

        if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) {
            return true;
        }
        return false;
    }

    /**
     * Delete Schedule Report user association
     *
     * @param int $report_id
     *
     * @return bool
     */    
    public function deleteScheduledReportUser($report_id) 
    {
        // else, remove the associations
        $this->db_read->delete('crossbones.schedulereport_user', array('schedulereport_id' => $report_id));
        
        return true;    
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
        $sql = $columns = $update = '';
        $values = '?';
        $values_arr = array();
        
        foreach ($params as $col => $value) {
            $columns .= ',' . $col;
            $values .= ',?';
            $update .= $col . ' = ?,';
            $values_arr[] = $value;    
        }

        $update = substr($update, 0, -1);
        
        $sqlPlaceHolder = array_merge(array($report_id), $values_arr, $values_arr);
        
        $sql = "INSERT INTO crossbones.schedulereport_contact (schedulereport_id{$columns}) 
                VALUES ({$values}) 
                ON DUPLICATE KEY UPDATE {$update}";

        if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) {
            return true;
        }
        return false;
    }


}
