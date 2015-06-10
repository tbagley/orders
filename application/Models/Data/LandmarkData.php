<?php

namespace Models\Data;

use Models\Data\BaseData;

class LandmarkData extends BaseData
{
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
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
        $data = array();

        $sql = "SELECT * 
                FROM landmarkgroup 
                WHERE user_id = ? AND active = 1
                ORDER BY landmarkgroupname ASC";

        $data = $this->db_read->fetchAll($sql, array($user_id));
        return $data;
    }

    /**
     * Get the landmark group info by landmarkgroup_id
     *
     * @params: landmarkgroup_id
     *
     * @return array | bool
     */
    function getLandmarkGroupById($landmarkgroup_id)
    {
        $data = array();
        $sql = "SELECT * 
                FROM landmarkgroup 
                WHERE active = 1 AND landmarkgroup_id = ?";

        $data = $this->db_read->fetchAll($sql, array($landmarkgroup_id));
        return $data;
    }

    /**
     * Get the landmarks by landmark group ids
     *
     * @params: user_id, landmark_groups
     *
     * @return array | bool
     */    
    public function getLandmarksByGroupIds($user_id, $landmark_groups)
    {
        $landmarks = array();
        $where_in = "";
        if (isset($landmark_groups) AND ! empty($landmark_groups)) {
            $landmark_groups = implode(",", $landmark_groups);
            $where_in = "AND crossbones.landmarkgroup.landmarkgroup_id IN ({$landmark_groups}) ";
        }

        $sql = "SELECT 
                    crossbones.landmark.landmark_id,
                    crossbones.landmark.account_id,
                    crossbones.landmark.shape,
                    crossbones.landmark.landmarkname,
                    crossbones.landmark.latitude,
                    crossbones.landmark.longitude,
                    crossbones.landmark.radius,
                    crossbones.landmark.streetaddress,
                    crossbones.landmark.city,
                    crossbones.landmark.state,
                    crossbones.landmark.zipcode,
                    crossbones.landmark.country,
                    crossbones.landmark.reference,
                    crossbones.landmark.verifydate,
                    crossbones.landmark.active,
                    crossbones.landmarkgroup.*,
                    crossbones.landmark.landmark_id as landmark_id 
                FROM crossbones.landmark 
                LEFT JOIN crossbones.landmarkgroup_landmark ON crossbones.landmark.landmark_id = crossbones.landmarkgroup_landmark.landmark_id 
                LEFT JOIN crossbones.landmarkgroup ON crossbones.landmarkgroup_landmark.landmarkgroup_id = crossbones.landmarkgroup.landmarkgroup_id 
                WHERE crossbones.landmark.account_id = ? {$where_in} AND crossbones.landmark.active = 1
                ORDER BY crossbones.landmark.landmarkname ASC";

        $landmarks = $this->db_read->fetchAll($sql, array($user_id));

        return $landmarks;
    }    

    /**
     * Get the filtered landmarks by $params (string search)
     *
     * @params: int user_id
     * @params: array $params
     * @params: array $searchfields
     *
     * @return array | bool
     */    
    public function getFilteredLandmarksStringSearch($user_id, $params, $searchfields)
    {
        $landmarks = array();

        $where_search_string = "";
        if (isset($params['search_string']) AND $params['search_string'] != '') {

            $search_string = $params['search_string'];
            if (! empty($searchfields) AND is_array($searchfields)) {
                $where_search_string = "AND (";
                
                foreach ($searchfields as $key => $fieldname) {
                    $where_search_string .= "`".$fieldname."` LIKE '%".str_replace("_", "\_", $search_string)."%' OR ";
                }
    
        		$where_search_string = substr_replace($where_search_string, "", -4);
        		$where_search_string .= ")";
            }
        }

        $sql = "SELECT 
                    crossbones.landmark.landmark_id,
                    crossbones.landmark.account_id,
                    crossbones.landmark.shape,
                    crossbones.landmark.landmarkname,
                    crossbones.landmark.latitude,
                    crossbones.landmark.longitude,
                    crossbones.landmark.radius,
                    crossbones.landmark.streetaddress,
                    crossbones.landmark.city,
                    crossbones.landmark.state,
                    crossbones.landmark.zipcode,
                    crossbones.landmark.country,
                    crossbones.landmark.reference,
                    crossbones.landmark.verifydate,
                    crossbones.landmark.active,
                    crossbones.landmarkgroup.active AS landmarkgroup_active,
                    crossbones.landmarkgroup.landmarkgroupname as landmarkgroupname,
                    crossbones.landmarkgroup.landmarkgroup_id as landmarkgroup_id,
                    IF(crossbones.landmark.reference = '1', 'Reference', 'Landmark') as landmark_type 
                FROM crossbones.landmark 
                LEFT JOIN crossbones.landmarkgroup_landmark ON crossbones.landmark.landmark_id = crossbones.landmarkgroup_landmark.landmark_id 
                LEFT JOIN crossbones.landmarkgroup ON crossbones.landmarkgroup_landmark.landmarkgroup_id = crossbones.landmarkgroup.landmarkgroup_id 
                WHERE crossbones.landmark.account_id = ? AND crossbones.landmark.active = 1 {$where_search_string}
                ORDER BY landmark.landmarkname ASC";

        $landmarks = $this->db_read->fetchAll($sql, array($user_id));

        return $landmarks;        
    }


    /**
     * Get the filtered landmarks by $params (landmark group ids)
     *
     * @params: int user_id
     * @params: array $params
     *
     * @return array | bool
     */    
    public function getFilteredLandmarks($user_id, $params)
    {
        $landmarks = array();
        $where_in_groups = "";
        if (isset($params['landmarkgroup_id']) AND ! empty($params['landmarkgroup_id'])) {
            $landmark_groups = implode(",",$params['landmarkgroup_id']);
            $where_in_groups = " AND crossbones.landmarkgroup.landmarkgroup_id IN ({$landmark_groups}) ";
        }

        $where_reference = "";
        if (isset($params['landmark_type']) AND $params['landmark_type'] != '') {
            $landmark_type = $params['landmark_type'];
            $where_reference = " AND crossbones.landmark.reference = '{$landmark_type}' ";
        }

        $sql = "SELECT 
                    crossbones.landmark.landmark_id,
                    crossbones.landmark.account_id,
                    crossbones.landmark.shape,
                    crossbones.landmark.landmarkname,
                    crossbones.landmark.latitude,
                    crossbones.landmark.longitude,
                    crossbones.landmark.radius,
                    crossbones.landmark.streetaddress,
                    crossbones.landmark.city,
                    crossbones.landmark.state,
                    crossbones.landmark.zipcode,
                    crossbones.landmark.country,
                    crossbones.landmark.reference,
                    crossbones.landmark.verifydate,
                    crossbones.landmark.active,
                    crossbones.landmarkgroup.active AS landmarkgroup_active,
                    crossbones.landmarkgroup.landmarkgroupname as landmarkgroupname,
                    crossbones.landmarkgroup.landmarkgroup_id as landmarkgroup_id,
                    IF(crossbones.landmark.reference = '1', 'Reference', 'Landmark') as landmark_type
                FROM crossbones.landmark 
                LEFT JOIN crossbones.landmarkgroup_landmark ON crossbones.landmark.landmark_id = crossbones.landmarkgroup_landmark.landmark_id 
                LEFT JOIN crossbones.landmarkgroup ON crossbones.landmarkgroup_landmark.landmarkgroup_id = crossbones.landmarkgroup.landmarkgroup_id 
                WHERE crossbones.landmark.account_id = ? AND crossbones.landmark.active = 1{$where_in_groups} {$where_reference}
                ORDER BY landmark.landmarkname ASC";

        $landmarks = $this->db_read->fetchAll($sql, array($user_id));

        return $landmarks;       
    }


    /**
     * Get the filtered incomplete landmarks by $params (string search)
     *
     * @params: int user_id
     * @params: array $params
     * @params: array $searchfields
     *
     * @return array | bool
     */    
    public function getIncompleteLandmarksStringSearch($user_id, $params, $searchfields)
    {
        $landmarks = array();

        $where_search_string = "";
        if (isset($params['search_string']) AND $params['search_string'] != '') {

            $search_string = $params['search_string'];
            if (! empty($searchfields) AND is_array($searchfields)) {
                $where_search_string = "AND (";
                
                foreach ($searchfields as $key => $fieldname) {
                    $where_search_string .= "`".$fieldname."` LIKE '%".str_replace("_", "\_", $search_string)."%' OR ";
                }
    
        		$where_search_string = substr_replace($where_search_string, "", -4);
        		$where_search_string .= ")";
            }
        }

        $sql = "SELECT 
                    crossbones.landmarkupload.*,
                    crossbones.landmarkgroup.active AS landmarkgroup_active,
                    crossbones.landmarkupload.landmarkgroupname as landmarkgroupname,
                    crossbones.landmarkgroup.landmarkgroup_id as landmarkgroup_id,
                    IF(crossbones.landmarkupload.reference = '1', 'Reference', 'Landmark') as landmark_type 
                FROM crossbones.landmarkupload 
                LEFT JOIN crossbones.landmarkgroup ON LOWER(crossbones.landmarkupload.landmarkgroupname) = LOWER(crossbones.landmarkgroup.landmarkgroupname)
                WHERE crossbones.landmarkupload.account_id = ? {$where_search_string}
                ORDER BY landmarkupload.landmarkname ASC";

        $landmarks = $this->db_read->fetchAll($sql, array($user_id));

        return $landmarks;        
    }


    /**
     * Get the filtered incomplete landmarks by $params (landmark group ids)
     *
     * @params: int user_id
     * @params: array $params
     *
     * @return array | bool
     */    
    public function getFilteredIncompleteLandmarks($user_id, $params)
    {
        $landmarks = array();
        $where_in_groups = "";
        if (isset($params['landmarkgroup_id']) AND ! empty($params['landmarkgroup_id'])) {
            $landmark_groups = implode(",",$params['landmarkgroup_id']);
            $where_in_groups = " AND crossbones.landmarkgroup.landmarkgroup_id IN ({$landmark_groups}) ";
        }

        $where_reference = "";
        if (isset($params['landmark_type']) AND $params['landmark_type'] != '') {
            $landmark_type = $params['landmark_type'];
            $where_reference = " AND crossbones.landmarkupload.reference = '{$landmark_type}' ";
        }

        $where_reason = "";
        if (isset($params['reason']) AND $params['reason'] != '') {
            $where_reason = " AND (crossbones.landmarkupload.reason LIKE '%".$params['reason']."%')";
        }

        $sql = "SELECT 
                    crossbones.landmarkupload.*,
                    crossbones.landmarkgroup.active AS landmarkgroup_active,
                    crossbones.landmarkupload.landmarkgroupname as landmarkgroupname,
                    crossbones.landmarkgroup.landmarkgroup_id as landmarkgroup_id,
                    IF(crossbones.landmarkupload.reference = '1', 'Reference', 'Landmark') as landmark_type
                FROM crossbones.landmarkupload 
                LEFT JOIN crossbones.landmarkgroup ON LOWER(crossbones.landmarkgroup.landmarkgroupname) = LOWER(crossbones.landmarkupload.landmarkgroupname) 
                WHERE crossbones.landmarkupload.account_id = ? {$where_in_groups}{$where_reference}{$where_reason}
                ORDER BY landmarkupload.landmarkname ASC";

        $landmarks = $this->db_read->fetchAll($sql, array($user_id));

        return $landmarks;       
    }
    
    /**
     * Save landmark to database
     *
     * @param array params
     *
     * @return int|bool
     */    
    public function saveLandmark($params) 
    {
        if (! is_array($params) OR count($params) <= 0) {
            $this->setErrorMessage('err_param');
        } else {

            if (isset($params['landmarkgroupname'])) {
                unset($params['landmarkgroupname']);
            }

            if (isset($params['unit_id'])) {
                unset($params['unit_id']);
            }

            $sql = $columns = $values = $bbox = '';

            if (isset($params['boundingbox'])) {
                $bbox = $params['boundingbox'];
                unset($params['boundingbox']);
            }

            $columns = '`'. implode('`,`', array_keys($params)) . '`';  // column names
            $values = "'" . implode("','", $params) . "'";              // column values
            
            if ($bbox !== '') {
                $columns .= ',`boundingbox`';
                $values .= ',' . $bbox;
            }
            
            $sql = 'INSERT INTO crossbones.landmark (' . $columns . ') VALUES (' . $values . ')';
            
            // NOTE: doctrine throws an error when using insert() due to 'boundingbox' geometry value (see error below), so we're using executeQuery() for now
            // error: Numeric value out of range: 1416 Cannot get geometry object from data you send to the GEOMETRY field
            //if ($this->db_read->insert('crossbones.landmark', $params)) {
            if ($this->db_read->executeQuery($sql)) {
                return $this->db_read->lastInsertId();
            } else {
                $this->setErrorMessage('err_database');
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
        if ($this->db_read->insert('crossbones.landmarkgroup_landmark', array('landmarkgroup_id' => $landmarkgroup_id, 'landmark_id' => $landmark_id))) {
            return true;
        }
        return false;
    }    

    /**
     * Delete landmark (mark as inactive)
     *
     * @param int landmark_id
     * @param int account_id
     * @param bool reference
     *
     * @return bool
     */    
    public function deleteLandmark($landmark_id, $account_id, $reference) 
    {
        if ($this->db_read->update('crossbones.landmark', array('active' => 0), array('landmark_id' => $landmark_id, 'account_id' => $account_id))) {    // remove/deactivate landmark
            if ($reference === true) {  // if it's a reference landmark, remove the landmark - unit association
                $this->db_read->delete('crossbones.unit_landmark', array('landmark_id' => $landmark_id));
            } else {                    // else, remove the landmarkgroup - landmark association
                $this->db_read->delete('crossbones.landmarkgroup_landmark', array('landmark_id' => $landmark_id));
            }
            return true;    
        }        

        return false;
    }

    /**
     * Delete an unfound landmark
     *
     * @param int landmarkupload_id
     * @param int account_id
     *
     * @return bool
     */    
    public function deleteLandmarkUpload($landmarkupload_id, $account_id) 
    {
        if ($this->db_read->delete('crossbones.landmarkupload', array('landmarkupload_id' => $landmarkupload_id, 'account_id' => $account_id))) {
            return true;
        }
        return false;
    }

    /**
     * Update the unfound landmark by unfound landmark id
     *
     * @params: unit_id, params
     *
     * @return array | bool
     */
    public function updateIncompleteLandmark($landmarkupload_id, $params)
    {
        //temporary since table does not have shape
        if (isset($params['shape'])) {
            unset($params['shape']);
        }
        
        if ($this->db_read->update('crossbones.landmarkupload', $params, array('landmarkupload_id' => $landmarkupload_id))) {
            return true;
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
        if ($this->db_read->update('crossbones.landmark', $params, array('landmark_id' => $landmark_id, 'account_id' => $account_id)) !== false) {    // remove/deactivate landmark
            return true;    
        }        

        return false;
    }


    /**
     * Update the unit info by unit_id
     *
     * @params: unit_id, params
     *
     * @return array | bool
     */
    public function updateLandmarkInfo($landmark_id, $params)
    {
/*
        if ($this->db_read->update('crossbones.landmark', $params, array('landmark_id' => $landmark_id))) {
            return true;
        }
        return false;
*/

        $sql = $updates = '';
        
        foreach($params as $col => $value) {
            //$updates .= $col . ' = ' . $value . ', ';
            if ($col == 'boundingbox') {
                $updates .= "{$col} = {$value}, ";
            } else {   
                $updates .= "{$col} = '{$value}', ";
            }
        }
        
        // trim starting and ending white spaces and commas
        $updates = trim($updates);
        $updates = trim($updates, ',');
                    
        $sql = 'UPDATE crossbones.landmark SET ' . $updates . ' WHERE landmark_id = ' . $landmark_id . ' LIMIT 1';
       
        // NOTE: doctrine throws an error when using update() due to 'boundingbox' geometry value (see error below), so we're using executeQuery() for now
        // error: Numeric value out of range: 1416 Cannot get geometry object from data you send to the GEOMETRY field
        //if ($this->db_read->insert('crossbones.landmark', $params)) {
        if ($this->db_read->executeQuery($sql)) {
            return true;
        } else {
            $this->setErrorMessage('err_database');
        }
        
        return false;
    }

    /**
     * Update the unit attribute by unit_id
     *
     * @params: unit_id, params
     *
     * @return array | bool
     */
    public function updateLandmarkGroupByLandmarkId($landmark_id, $params)
    {   
        if ($this->db_read->update('crossbones.landmarkgroup_landmark', $params, array('landmark_id' => $landmark_id))) {
            return true;
        }

        return false;
    }

    /**
     * Update the landmarkgroup_landmark relation
     *
     * @params: landmark_id, params
     *
     * @return array | bool
     */
    public function updateLandmarkgroupLandmark($landmark_id, $params)
    {   
        if ($this->db_read->update('crossbones.landmarkgroup_landmark', $params, array('landmark_id' => $landmark_id))) {
            return true;
        }

        return false;
    }

    /**
     * Get the landmarkgroup_landmark relation info by landmark_id
     *
     * @params: landmark_id
     *
     * @return array | bool
     */
    function getLandmarkgroupLandmark($landmark_id)
    {
        $data = array();
        $sql = "SELECT * 
                FROM landmarkgroup_landmark 
                WHERE landmark_id = ? 
                LIMIT 1";

        $data = $this->db_read->fetchAll($sql, array($landmark_id));
        return $data;
    }

    /**
     * Add landmarkgroup to landmark relation
     *
     * @param array params
     *
     * @return int|bool
     */
    public function addLandmarkgroupLandmark($params)
    {
        if ($this->db_read->insert('crossbones.landmarkgroup_landmark', $params)) {
            return true;    
        }        
        return false;    
    }

    /**
     * Add landmark to vehicle
     *
     * @param array params
     *
     * @return int|bool
     */
    public function addLandmarkToVehicle($params) 
    {
        if ($this->db_read->insert('crossbones.unit_landmark', $params)) {
            return true;    
        }        
        return false;    
    }
    
    /**
     * Get landmarks by unit id
     *
     * @param int unit_id
     *
     * @return bool
     */     
    public function getLandmarksByUnitId($unit_id, $reference = null, $verified = '') 
    {
        $and_reference = $and_verified = "";
        $landmarks = array();
        
        if ($reference === true) {
            $and_reference = "AND lm.territorytype = 'reference' ";
            if (! empty($verified)) {
                switch ($verified) {
                    case 'verified':
                        $and_verified = "AND lm.verifydate > '0000-00-00' ";
                        break;
                    case 'no-verified':
                        $and_verified = "AND lm.verifydate = '0000-00-00' ";
                        break;
                    case 'all':
                        $and_verified = "";
                        break;
                }
            }
        } else if ($reference === false) {
            $and_reference = "AND lm.territorytype = 'landmark' ";
        }
        
        $sql = "SELECT 
                    lm.territory_id,
                    lm.account_id,
                    lm.shape,
                    lm.territoryname,
                    lm.latitude,
                    lm.longitude,
                    lm.radius,
                    lm.streetaddress,
                    lm.city,
                    lm.state,
                    lm.zipcode,
                    lm.country,
                    lm.territorytype,
                    lm.verifydate,
                    lm.active,
                    ul.unit_id as unit_id 
                FROM crossbones.unit_territory AS ul
                LEFT JOIN crossbones.territory AS lm ON lm.territory_id = ul.territory_id
                WHERE ul.unit_id = ? {$and_reference}{$and_verified}AND lm.active = 1";      

        if ($landmarks = $this->db_read->fetchAll($sql, array($unit_id))) {
            //print_rb($landmarks);
            return $landmarks;
        }
        
        return false;        
    }    

    /**
     * Get landmarks by unit id
     *
     * @param int       account_id
     * @param string    title
     *
     * @return bool
     */ 
    public function getLandmarkByTitle($account_id, $title)
    {
        $landmark = array();

        $sql = "SELECT 
                    lm.landmark_id,
                    lm.account_id,
                    lm.shape,
                    lm.landmarkname,
                    lm.latitude,
                    lm.longitude,
                    lm.radius,
                    lm.streetaddress,
                    lm.city,
                    lm.state,
                    lm.zipcode,
                    lm.country,
                    lm.reference,
                    lm.verifydate,
                    lm.active
                FROM crossbones.landmark AS lm
                WHERE lm.landmarkname = ? AND lm.account_id = ? AND lm.active = 1
                LIMIT 1";
        
        if (($landmark = $this->db_read->fetchAll($sql, array($title, $account_id))) !== false) {
            
//print_r($landmark);
//exit;
            return $landmark;
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
        if (isset($landmark_ids) AND ! empty($landmark_ids)) {
            $landmarks = implode(",", $landmark_ids);

            $sql = "SELECT 
                        lm.landmark_id,
                        lm.account_id,
                        lm.shape,
                        lm.landmarkname,
                        lm.latitude,
                        lm.longitude,
                        lm.radius,
                        lm.streetaddress,
                        lm.city,
                        lm.state,
                        lm.zipcode,
                        lm.country,
                        lm.reference,
                        lm.verifydate,
                        lm.active,
                        lg.active as landmarkgroup_active,
                        lg.landmarkgroupname as landmarkgroupname,
                        lg.landmarkgroup_id as landmarkgroup_id,
                        lm.reference as reference,
                        IF(lm.reference = '1', 'Reference', 'Landmark') as landmark_type,
                        asText(lm.boundingbox) as boundingbox
                    FROM crossbones.landmark AS lm
                    LEFT JOIN crossbones.landmarkgroup_landmark AS lgl ON lgl.landmark_id = lm.landmark_id
                    LEFT JOIN crossbones.landmarkgroup AS lg ON lg.landmarkgroup_id = lgl.landmarkgroup_id
                    WHERE lm.landmark_id IN ({$landmarks}) AND lm.active = 1 
                    ORDER BY lm.landmarkname";

            if (($landmark = $this->db_read->fetchAll($sql)) !== false) {
                //print_rb($landmark);
                return $landmark;
            }
        }
        
        return false;  
    }

    /**
     * Get incomplete landmark by provided landmarkupload_id
     *
     * @param int landmarkupload_id
     *
     * @return bool|array
     */    
    public function getLandmarkUploadByIds($landmarkupload_ids)
    {
        if (isset($landmarkupload_ids) AND ! empty($landmarkupload_ids)) {
            $landmarkuploads = implode(",", $landmarkupload_ids);

            $sql = "SELECT 
                        lmu.*,
                        lmu.landmarkupload_id as landmark_id,
                        lg.active as landmarkgroup_active,
                        lg.landmarkgroupname as landmarkgroupname,
                        lg.landmarkgroup_id as landmarkgroup_id,
                        lmu.reference as reference,
                        IF(lmu.reference = '1', 'Reference', 'Landmark') as landmark_type,
                        'circle' as shape
                    FROM crossbones.landmarkupload AS lmu
                    LEFT JOIN crossbones.landmarkgroup AS lg ON LOWER(lg.landmarkgroupname) = LOWER(lmu.landmarkgroupname)
                    WHERE lmu.landmarkupload_id IN ({$landmarkuploads}) 
                    ORDER BY lmu.landmarkname";

            if (($landmark = $this->db_read->fetchAll($sql)) !== false) {
                return $landmark;
            }
        }
        
        return false;  
    }

    
    /**
     * Get units by landmark id
     *
     * @param int       landmark_id
     *
     * @return bool
     */ 
    public function getUnitsByLandmarkId($landmark_id)
    {
        $sql = "SELECT *
                FROM crossbones.unit_landmark AS ul
                LEFT JOIN crossbones.unit AS u ON u.unit_id = ul.unit_id
                WHERE ul.landmark_id = ?";
                                
        if ($units = $this->db_read->fetchAll($sql, array($landmark_id))) {
            return $units;
        }
        
        return false;
    }
    
    /**
     * Save an incomplete landmark
     *
     * @param array params
     *
     * @return bool
     */ 
    public function saveIncompleteLandmark($params)
    {
        if ($this->db_read->insert('crossbones.landmarkupload', $params)) {
            return true;
        }
        
        return false;
    }         

    /**
     * Save landmarkgroup to database
     *
     * @param array params
     *
     * @return int|bool
     */    
    public function addLandmarkGroup($params) 
    {
        if (! is_array($params) OR count($params) <= 0) {
            $this->setErrorMessage('err_param');
        } else {
            if ($this->db_read->insert('crossbones.landmarkgroup', $params)) {
                return $this->db_read->lastInsertId();
            } else {
                $this->setErrorMessage('err_database');
            }
        }
        
        return false;
    }

    /**
     * Get landmarkgroup by title
     *
     * @param int       account_id
     * @param string    title
     *
     * @return bool
     */ 
    public function getLandmarkGroupByTitle($account_id, $title)
    {
        $sql = "SELECT *
                FROM crossbones.landmarkgroup AS lmg
                WHERE lmg.landmarkgroupname = ? AND lmg.user_id = ? AND lmg.active = 1
                LIMIT 1";
        
        if (($landmark = $this->db_read->fetchAll($sql, array($title, $account_id))) !== false) {
            return $landmark;
        }
        
        return false;
    }
}
