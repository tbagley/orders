<?php

namespace Models\Data;

use Models\Logic\AddressLogic;
use Models\Logic\BaseLogic;
use Models\Data\BaseData;
use GTC\Component\Utils\CSV\CSVReader;
use GTC\Component\Utils\Date;
use Models\Data\ContactData;

class VehicleData extends BaseData
{
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();

        $this->address_logic    = new AddressLogic;
        $this->base_logic       = new BaseLogic;
        $this->contact_data     = new ContactData;

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
        return 'cronDeviceSweep';
    }

    /**
     * Log Metrics
     */
    public function codebaseMetrics($codebase,$user_id,$mode,$value)
    {

        $out = '#' . $codebase . '#' . $value['HTTP_HOST'];

        if(($user_id)&&($codebase)) {

            $yearMonthDay = date ( 'Y-m-d' ) ; 

            switch($mode){

                case             'action' : $out .= ':' . $mode . ':' . $user_id;
                                            // if($user_id){
                                            //     $out .= ':' . $user_id;
                                            // }
                                            break;

                case               'ajax' : $out .= ':' . $mode;
                                            $sql = "SELECT * FROM metrics.ajax WHERE codebase = ? AND ajax = ? AND created LIKE '" . $yearMonthDay . "%'";
                                            $result = $this->db_read->fetchAll($sql, array($codebase,$value));
                                            if($result){
                                                $out .= ':UPDATE';
                                                $sql = "UPDATE metrics.ajax SET counter = counter + 1 WHERE codebase = ? AND ajax = ? AND created LIKE '" . $yearMonthDay . "%'";
                                                $result = $this->db_read->fetchAll($sql, array($codebase,$value));
                                            } else {
                                                $out .= ':INSERT';
                                                $sql = "INSERT INTO metrics.ajax ( codebase , ajax , counter ) VALUES ( ? , ? , ? )";
                                                if ($this->db_write->executeQuery($sql, array($codebase,$value,1))) {
                                                }
                                            }
                                            break;

                case              'login' : $out .= ':' . $mode . ':' . $user_id;
                                            $today = date('Y-m-d 08:00:00', strtotime('-8 hours'));
                                            $sql = "SELECT * FROM metrics.login WHERE url = ? AND createdate >= '" . $today . "' ORDER BY login_id LIMIT 1";
                                            $result = $this->db_read->fetchAll($sql, array($value['HTTP_HOST']));
                                            if($result[0]['login_id']){
                                                $out .= ':UPDATE';
                                                $result[0]['counter']++;
                                                $sql = "UPDATE metrics.login SET codebase = ? , counter = ? WHERE login_id = ?";
                                                $result = $this->db_write->executeQuery($sql, array($codebase,$result[0]['counter'],$result[0]['login_id']));
                                            } else {
                                                $out .= ':INSERT';
                                                $sql = "INSERT INTO metrics.login ( codebase , url , counter , createdate , updated ) VALUES ( ? , ? , ? , now() , now() )";
                                                $result = $this->db_write->executeQuery($sql, array($codebase,$value['HTTP_HOST'],1));
                                            }
                                            $sql = "SELECT user_id FROM crossbones.user WHERE username = ? AND userstatus_id > ? AND userstatus_id < ? ORDER BY createdate ASC LIMIT 1";
                                            $result = $this->db_read->fetchAll($sql, array($user_id,0,4));
                                            $user_id = $result[0]['user_id'];
                                            if($user_id){
                                                $out .= ':' . $user_id;
                                                $sql = "SELECT * FROM metrics.action WHERE user_id = ? ORDER BY action_id LIMIT 1";
                                                $result = $this->db_read->fetchAll($sql, array($user_id));
                                                if($result[0]['action_id']){
                                                    $out .= ':UPDATE';
                                                    $result[0]['counter']++;
                                                    $sql = "UPDATE metrics.action SET codebase = ? , browser = ? , ipaddr = ? , login = now() , url = ? , counter = ? WHERE action_id = ?";
                                                    $result = $this->db_write->executeQuery($sql, array($codebase,$value['HTTP_USER_AGENT'],$value['REMOTE_ADDR'],$value['HTTP_HOST'],$result[0]['counter'],$result[0]['action_id']));
                                                } else {
                                                    $out .= ':INSERT';
                                                    $sql = "INSERT INTO metrics.action ( codebase , user_id , browser , ipaddr , counter , login , url , updated ) VALUES ( ? , ? , ? , ? , ? , now() , ? , now() )";
                                                    $result = $this->db_write->executeQuery($sql, array($codebase,$user_id,$value['HTTP_USER_AGENT'],$value['REMOTE_ADDR'],1,$value['HTTP_HOST']));
                                                }
                                            }
                                            break;

                case                'uri' : $out .= ':' . $mode;
                                            $sql = "SELECT * FROM metrics.uri WHERE url = ? AND uri = ? AND created LIKE '" . $yearMonthDay . "%'";
                                            $result = $this->db_read->fetchAll($sql, array($codebase,$value['uri']));
                                            if($result[0]['uri_id']){
                                                $out .= ':UPDATE';
                                                $result[0]['counter']++;
                                                if($result[0]['min'] > $value['miliseconds']){
                                                    $result[0]['min'] = $value['miliseconds'];
                                                }
                                                if($result[0]['min']==0){
                                                    $result[0]['min'] = $value['miliseconds'];
                                                }
                                                if($result[0]['max'] < $value['miliseconds']){
                                                    $result[0]['max'] = $value['miliseconds'];
                                                }
                                                $result[0]['seconds'] = $result[0]['seconds'] + $value['miliseconds'] ;
                                                $sql = "UPDATE metrics.uri SET counter = ? , min = ? , max = ? , seconds = ? WHERE uri_id = ?";
                                                $result = $this->db_write->executeQuery($sql, array($result[0]['counter'],$result[0]['min'],$result[0]['max'],$result[0]['seconds'],$result[0]['uri_id']));
                                            } else {
                                                $out .= ':INSERT';
                                                $sql = "INSERT INTO metrics.uri ( url , uri , counter , min , max , seconds , updated ) VALUES ( ? , ? , ? , ? , ? , ? , now() )";
                                                if ($this->db_write->executeQuery($sql, array($codebase,$value['uri'],1,$value['miliseconds'],$value['miliseconds'],$value['miliseconds']))) {
                                                }
                                            }
                                            break;

            }

        }

        return $out ;


    }



    public function fixLandmark($aid,$uid,$post)
    {
        if(!($post['territorytype'])){
            $post['territorytype'] = 'landmark';
        }
        if(!($post['boundingbox'])){
            $post['boundingbox'] = "GEOMFROMTEXT('POLYGON((29.9 -95.5))')";
        }
        $post['boundingbox'] = str_replace(array("GEOMFROMTEXT('","')"), array('',''), $post['boundingbox']);

        $sql = "INSERT INTO crossbones.territory ( account_id , territorygroup_id , territorycategory_id , territorytype , shape , territoryname , latitude , longitude , radius , boundingbox , streetaddress , city , state , zipcode , country , verifydate ) VALUES ( ? , ? , ? , ? , ? , ? , ? , ? , ? , PolyFromText(?) , ? , ? , ? , ? , ? , ? )";
        $result = $this->db_write->executeQuery($sql, array($aid,$post['group'],$post['category'],$post['territorytype'],$post['shape'],$post['name'],$post['latitude'],$post['longitude'],$post['radius'],$post['boundingbox'],$post['streetaddress'],$post['city'],$post['state'],$post['zipcode'],$post['country'],'0000-00-00'));

        if($result){
            $sql = "UPDATE crossbones.territoryupload SET active = ? WHERE territoryupload_id = ?";
            if($this->db_read->executeQuery($sql, array(1,$post['territoryupload_id']))){
                // do nothing
            }                                                                        
        }
        
        $out = $post;
        $out['account_id'] = $aid;
        $out['user_id'] = $uid;
        $out['sql'] = $sql;
        $out['result'] = $result;
        
        return $out;
    }

    public function get()
    {
        $results = $this->db_read->fetchAll("SELECT * FROM unit LIMIT 0,5", array());

        return $results;
    }

    public function getBattery($uid)
    {
        if($uid>0){
            $udb = $this->getUnitDb($uid);
            // $sql = "SELECT * FROM " . $udb . ".unit" . $uid . " WHERE id IS NOT NULL ORDER BY id DESC LIMIT 1";
            $sql = "SELECT * 
                    FROM " . $udb . ".unit" . $uid . " 
                    WHERE id IS NOT NULL 
                    ORDER BY unittime DESC 
                    LIMIT 1";
            $result = $this->db_read->fetchAll($sql, array());
        }

        $out['level'] = $result[0]['battery'];
        $out['tip'] = 'This is the current level ' . $result[0]['battery'] . ', ok?';

        return $out ;
    }

    public function getDuration($uid,$moving)
    {

        if($uid>0){
            $udb = $this->getUnitDb($uid);
            // $sql = "SELECT * FROM " . $udb . ".unit" . $uid . "
            //         WHERE id IS NOT NULL
            //         ORDER BY id DESC LIMIT 1";
            $sql = "SELECT * FROM " . $udb . ".unit" . $uid . "
                    WHERE id IS NOT NULL
                    ORDER BY unittime DESC
                    LIMIT 1";
            $result = $this->db_read->fetchAll($sql, array($res[0]['id']));
            $lastServerTime = $result[0]['servertime'];

            $diff = strtotime(date('Y-m-d H:i:s')) - strtotime($result[0]['unittime']);

            while ($diff>86400){
                $days++;
                $diff = $diff - 86400;
            }
    
            if($days>2){
                $buffer['stale'] = $days . ' days since device last reported';
            }

            $days=0;
    
            date_default_timezone_set ( 'UTC' );
            $t1 = date('Y-m-d H:i:s') ;
            switch ($moving){

                case    '1' :
                case    '3' :
                case    '4' :   $sql = "SELECT * 
                                        FROM " . $udb . ".unit" . $uid . "
                                        WHERE event_id = '2' 
                                        OR event_id = '5'
                                        ORDER BY unittime DESC 
                                        LIMIT 1";
                                $buf = $this->db_read->fetchAll($sql, array());  
                                $lastStop = $buf[0]['unittime'] ;
                                if ( $lastStop > 0 ) {
                                    $sql = "SELECT * 
                                            FROM " . $udb . ".unit" . $uid . "
                                            WHERE unittime > ?
                                            ORDER BY unittime DESC";
                                    $res = $this->db_read->fetchAll($sql, array($lastStop));
                                    $travelStart=0;
                                    foreach ( $res as $key => $val ) {
                                        if((!($t2))||($travelStart<1)){
                                            $t2 = $val['unittime'] ;
                                        }
                                        if($val['event_id']==4){
                                            $travelStart++ ;
                                        }
                                        if(($latitude != $val['latitude'])||($longitude != $val['longitude'])){
                                            $latitude = $val['latitude'] ;
                                            $longitude = $val['longitude'] ;
                                        } else {
                                            // $nonMovement++;
                                        }
                                    }
                                }
                                break;

                case    '2' :
                case    '5' :   $sql = "SELECT * 
                                        FROM " . $udb . ".unit" . $uid . "
                                        WHERE event_id > '0' 
                                        AND event_id < '6'
                                        ORDER BY servertime DESC 
                                        LIMIT 1";
                                $res = $this->db_read->fetchAll($sql, array());  
                                $t2 = $res[0]['servertime'] ;
                                break;

            }

            if ( $nonMovement ) {

                $out = 'Errors: ' . $nonMovement;

            } else if($res[0]['servertime']){

                $diff = strtotime($t1) - strtotime($t2);

                while ($diff>86400){
                    $days++;
                    $diff = $diff - 86400;
                }
                while ($diff>3600){
                    $hours++;
                    $diff = $diff - 3600;
                }
                while ($diff>60){
                    $minutes++;
                    $diff = $diff - 60;
                }

                if ($days) {
                    $out .= $days . 'd ' ;
                }
                if ($hours) {
                    $out .= $hours . 'h '  ;
                }
                if ($minutes) {
                    $out .= $minutes . 'm' ;
                }
                if(!($out)){
                    $out = 'now';
                }

            } else {

                $sql = "SELECT unitstatus_id
                        FROM crossbones.unit 
                        WHERE unit_id =?";
                $result = $this->db_read->fetchAll($sql, array($uid));
                
                if($result[0]['unitstatus_id']==2){
                    $out = 'n/a';                    
                } else {
                    $out = 'N/A';                    
                }

            }

            // $out = date('Y-m-d H:i:s') . ' - ' . $res[0]['servertime'] ;
            // $out = strtotime(date('Y-m-d H:i:s')) . ' - ' . strtotime($res[0]['servertime']);
            // $out = strtotime(date('Y-m-d H:i:s')) - strtotime($res[0]['servertime']);

        }

        $buffer['state'] = $moving;
        $buffer['duration'] = trim($out) ; // . ' ' . $t1 . '-' . $t2 ; // . ' :#' . $uid . '#: ' . $res[0]['id'] . ' : ' . $result[0]['id'] ;

        return $buffer ;

    }

    public function getMoving($uid)
    {
        if($uid>0){
            $udb = $this->getUnitDb($uid);
            $sql = "SELECT * 
                    FROM " . $udb . ".unit" . $uid . " 
                    WHERE id IS NOT NULL 
                    AND event_id < 6 
                    ORDER BY unittime DESC 
                    LIMIT 1";
            $result = $this->db_read->fetchAll($sql, array());
        }

        return $result[0]['event_id'];
    }

    public function getSatellites($uid)
    {
        if($uid>0){
            $udb = $this->getUnitDb($uid);
            $sql = "SELECT * 
                    FROM " . $udb . ".unit" . $uid . " 
                    WHERE id IS NOT NULL 
                    AND event_id < 6 
                    ORDER BY unittime DESC 
                    LIMIT 1";
            $result = $this->db_read->fetchAll($sql, array());
        }

        $out['level'] = $result[0]['satellitefix'];
        $out['tip'] = 'This is the current level ' . $result[0]['satellitefix'] . ', ok?';

        return $out ;
    }

    public function getTerritory($aid,$uid)
    {
        if(($aid>0)&&($uid>0)){
            $udb = $this->getUnitDb($uid);
            $sql = "SELECT ue.*,
                    t.account_id as account_id,
                    t.territoryname as territoryname, 
                    t.active as active 
                    FROM " . $udb . ".unit" . $uid . " ue
                    LEFT JOIN crossbones.territory t ON t.territory_id = ue.landmark_id
                    WHERE id IS NOT NULL 
                    AND ue.event_id < 8 
                    ORDER BY ue.id DESC 
                    LIMIT 1";
            $result = $this->db_read->fetchAll($sql, array());
        }

        if(($result[0]['account_id']!=$aid)||($result[0]['active']!=1)){
            $result[0]['territoryname']=null;
        }

        return $result[0]['territoryname'] ;
    }

    public function getSignal($uid)
    {
        if($uid>0){
            $udb = $this->getUnitDb($uid);
            $sql = "SELECT * 
                    FROM " . $udb . ".unit" . $uid . " 
                    WHERE id IS NOT NULL
                    AND event_id < 6 
                    ORDER BY unittime DESC
                    LIMIT 1";
            $result = $this->db_read->fetchAll($sql, array());
        }

        $out['level'] = $result[0]['cellsignal'];
        $out['tip'] = 'This is the current level ' . $result[0]['cellsignal'] . ', ok?';

        return $out ;
    }

    public function activity($account_id,$user_id,$description,$v,$u)
    {
        $params = array('account_id' => $account_id,'user_id' => $user_id,'description' => $description,'uid' => $u,'val' => $v,'server' => $_SERVER['SERVER_NAME'],'uri' => $_SERVER['REQUEST_URI'],'ip' => $_SERVER['REMOTE_ADDR'],'client' => $_SERVER['HTTP_USER_AGENT']);
        if ($this->db_write->insert('crossbones.activity', $params)) {
            return true;
        }
        return false;
    }

    /**
     * Support Ajax Delete Requests
     */
    public function ajaxAllDevices($user,$names,$ids) {

        $results = array();

        foreach ($ids as $key => $uid) {
            if($uid>0){
                $udb = $this->getUnitDb($uid);
                // $sql = "SELECT ue.*,
                //         t.territoryname as territoryname,
                //         ume.eventname as eventname
                //         FROM " . $udb . ".unit" . $uid . " ue
                //         LEFT JOIN crossbones.territory t ON t.territory_id = ue.landmark_id
                //         LEFT JOIN unitmanagement.event ume ON ume.event_id = ue.event_id
                //         WHERE ( ( t.account_id = ? AND t.active = 1 ) OR ue.landmark_id = 0 )
                //         ORDER BY id DESC LIMIT 1";
                $sql = "SELECT ue.*,
                        t.territoryname as territoryname,
                        ume.eventname as eventname
                        FROM " . $udb . ".unit" . $uid . " ue
                        LEFT JOIN crossbones.territory t ON t.territory_id = ue.landmark_id
                        LEFT JOIN unitmanagement.event ume ON ume.event_id = ue.event_id
                        WHERE ( ( t.account_id = ? AND t.active = 1 ) OR ue.landmark_id = 0 )
                        ORDER BY unittime DESC
                        LIMIT 1";
                $res = $this->db_read->fetchAll($sql, array($user['account_id']));
                $res[0]['unitname'] = $names[$key] ;
                $res[0]['unit_id'] = $uid ;
                $results[] = $res[0];
            }
        }

        return $results ;
    }

    /**
     * Support Ajax Batch Command Requests
     */
    public function ajaxBatch($account_id,$user_id,$command,$units) {

        $buffer['attempts'] = "0" ;
        $buffer['inserts'] = "0" ;

        if($command){

            foreach( $units as $key => $val ) {

                $val = trim($val);

                if($val){

                    $buffer['attempts']++;

                    $sql = "SELECT unit_id
                            FROM crossbones.unit
                            WHERE serialnumber = ?
                            OR unitname = ?";
                    $res = $this->db_read->fetchAll($sql, array($val,$val));

                    if($res[0]['unit_id']>0){

                        $sql = "INSERT INTO crossbones.commandqueue ( account_id, user_id , batchcommand_id , processedon , unit_id ) VALUES ( ? , ? , ? , ? , ? )";

                        $result = $this->db_write->executeQuery($sql, array($account_id,$user_id,$command,'0000-00-00 00:00:00',$res[0]['unit_id']));

                        $buffer['inserts']++;

                    }

                }

            }
 
        }

        return $buffer;
 
    }

    /**
     * Support Ajax Delete Requests
     */
    public function ajaxDbDelete($user,$db_t,$rid,$value,$post) {

        $sql = 'ajaxDbDelete:' . $db_t . ', ' . $rid . ', ' . $value . ' *** CASE MISSING ***' ;

        switch ($db_t) {

            case                'crossbones-contact-contactgroup' : $sql = "DELETE FROM crossbones.contactgroup_contact WHERE contact_id = '{$value}' AND contactgroup_id = '{$rid}'";
                                                                    // if ($this->db_read->executeQuery($sql, $sql_params)) {
                                                                    //     // return true;
                                                                    // }
                                                                    $this->db_write->executeQuery("DELETE FROM crossbones.contactgroup_contact WHERE contact_id = '{$value}' AND contactgroup_id = '{$rid}'");
                                                                    // $result = $this->db_write->executeQuery($sql, array($rid,$value));
                                                                    break;

        }

        $result['message'] = $sql ;

        return $result;

    }                                     

    /**
     * Support Ajax Update Requests
     */
    public function ajaxDbUpdate($user,$field,$rid,$value,$post) {

        switch ($field) {

            case            'alertcontactcontact' : $db = 'crossbones';
                                                    $table = 'alert_contact';
                                                    $field = 'contact_id' ;
                                                    $mode = 2 ;
                                                    break;

            case              'alertcontactgroup' : $db = 'crossbones';
                                                    $table = 'alert_contact';
                                                    $field = 'contactgroup_id';
                                                    $mode = 1 ;
                                                    break;

            case               'alertcontactmode' : $db = 'crossbones';
                                                    $table = 'alert_contact';
                                                    $field = 'mode';
                                                    $mode = $value ;
                                                    break;

            case                      'alerttype' : $db = 'crossbones';
                                                    $table = 'alert';
                                                    $field = 'alerttype_id';
                                                    break;

            case                  'alertunitunit' : $db = 'crossbones';
                                                    $table = 'alert_unit';
                                                    $field = 'unit_id' ;
                                                    $mode = 2 ;
                                                    break;

            case                 'alertunitgroup' : $db = 'crossbones';
                                                    $table = 'alert_unit';
                                                    $field = 'unitgroup_id';
                                                    $mode = 1 ;
                                                    break;

            case                  'alertunitmode' : $db = 'crossbones';
                                                    $table = 'alert_unit';
                                                    $field = 'mode';
                                                    $mode = $value ;
                                                    break;

            case                    'cellcarrier' :  $db = 'crossbones';
                                                    $table = 'contact';
                                                    $field = 'cellcarrier_id';
                                                    break;

            case                  'contactgroup' :  $db = 'crossbones';
                                                    $table = 'contactgroup';
                                                    $field = 'HARDCODED FIELDS';
                                                    break;

            case                  'contactstatus' : $db = 'crossbones';
                                                    $table = 'contact';
                                                    $field = 'contactstatus';
                                                    break;

    case              'crossbones-territory-city' :
    case     'crossbones-territory-streetaddress' :
    case           'crossbones-territory-zipcode' : $db = 'crossbones';
                                                    $table = 'territory';
                                                    $field = array_pop(explode('-',$field));
                                                    $rid = $post['unit_id'];
                                                    break;

    case           'crossbones-territory-latlong' : $db = 'crossbones';
                                                    $table = array_pop(explode('-',$field));
                                                    $rid = $post['unit_id'];
                                                    break;

    case            'crossbones-territory-radius' :
    case             'crossbones-territory-shape' : $db = 'crossbones';
                                                    $table = 'territory';
                                                    $field = array_pop(explode('-',$field));
                                                    break;

    case           'crossbones-territory-country' : $db = 'crossbones';
                                                    $table = 'territory';
                                                    $field = array_pop(explode('-',$field));
                                                    $sql = "SELECT country FROM crossbones.countries WHERE country_id = ?";
                                                    $res = $this->db_read->fetchAll($sql, array($value));
                                                    $value = $res[0]['country'];                    
                                                    break;

    case             'crossbones-territory-state' : $db = 'crossbones';
                                                    $table = 'territory';
                                                    $field = array_pop(explode('-',$field));
                                                    $sql = "SELECT state FROM crossbones.states WHERE state_id = ?";
                                                    $res = $this->db_read->fetchAll($sql, array($value));
                                                    $value = $res[0]['state'];                    
                                                    break;

    case 'crossbones-territory-territorycategory' : $db = 'crossbones';
                                                    $table = 'territory';
                                                    $field = 'territorycategory_id';
                                                    break;

            case                   'delete-alert' : $db = 'crossbones';
                                                    $table = 'alert';
                                                    $field = 'active';
                                                    $value = '0';
                                                    break;

            case          'delete-commandpending' : $db = 'crossbones';
                                                    $table = 'deletecommandpending';
                                                    $field = 'active';
                                                    $value = '1';
                                                    break;

            case                 'delete-contact' : $db = 'crossbones';
                                                    $table = 'deletecontact';
                                                    $field = 'active';
                                                    $value = '1';
                                                    break;

            case            'delete-contactgroup' : $db = 'crossbones';
                                                    $table = 'deletecontactgroup';
                                                    $field = 'active';
                                                    $value = '1';
                                                    break;

            case                    'delete-repo' : $db = 'crossbones';
                                                    $table = 'repo';
                                                    $field = 'active';
                                                    $value = '1';
                                                    break;

            case                    'delete-user' : $db = 'crossbones';
                                                    $table = 'user';
                                                    $field = 'userstatus_id';
                                                    $value = '0';
                                                    break;

            case                'delete-usertype' : $db = 'crossbones';
                                                    $table = 'usertype';
                                                    $field = 'active';
                                                    $value = '0';
                                                    break;

            case              'delete-incomplete' : $db = 'crossbones';
                                                    $table = 'deleteincomplete';
                                                    $field = 'active';
                                                    $value = '1';
                                                    break;

            case                'delete-landmark' : $db = 'crossbones';
                                                    $table = 'territory-delete';
                                                    $field = 'active';
                                                    $value = '0';
                                                    break;

            case        'delete-scheduled-report' : $db = 'crossbones';
                                                    $table = 'deleteschedulereport';
                                                    $field = 'active';
                                                    $value = '0';
                                                    break;

            case          'delete-territorygroup' : $db = 'crossbones';
                                                    $table = 'territorygroup';
                                                    $field = 'active';
                                                    $value = '0';
                                                    break;

            case               'delete-unitgroup' : $db = 'crossbones';
                                                    $table = 'unitgroup';
                                                    $field = 'active';
                                                    $value = '0';
                                                    break;

            case                     'deverified' :
            case                       'verified' : $db = 'crossbones';
                                                    $table = $field;
                                                    break;

            case                        'gateway' : $db = 'crossbones';
                                                    $table = 'contact';
                                                    $field = 'cellcarrier_id';
                                                    break;

            case             'permissioncategory' : $db = 'crossbones';
                                                    $table = 'permission';
                                                    $field = 'permissioncategory_id';
                                                    break;

            case                 'territorygroup' : $db = 'crossbones';
                                                    $table = 'territory';
                                                    $field = 'territorygroup_id';
                                                    break;

            case                      'unitgroup' : $db = 'crossbones';
                                                    $table = 'unit';
                                                    $field = 'unitgroup_id';
                                                    break;

            case                        'unit_id' : $db = 'crossbones';
                                                    $table = 'unit_territory';
                                                    $field = 'unit_id';
                                                    break;

            case                     'unitstatus' : $db = 'crossbones';
                                                    $table = 'unit';
                                                    $field = 'unitstatus_id';
                                                    break;

            case                      'firstname' : 
            case                       'lastname' : $db = 'crossbones';
                                                    $table = 'user';
                                                    break;

            case                       'usertype' : $db = 'crossbones';
                                                    $table = 'user';
                                                    $field = 'usertype_id';
                                                    break;

        }

        $result['message'] = 'ajaxDbUpdate *** CASE MISSING ***:' . $user['account_id'] .': db="'. $db .'", tbl="'. $table .'", field="'. $field .'", rid="'. $rid .'", value="'. $value . '"' ;

        switch ($db) {

            case         'crossbones' :
            case                 'xb' : switch($table) {

                                            case              'alert' : $sql = "UPDATE crossbones." . $table . " SET " . $field . " = ? WHERE alert_id = ?";
                                                                        if($this->db_read->executeQuery($sql, array($value,$rid))){
                                                                            // do nothing
                                                                        }
                                                                        $sql = "SELECT " . $field . " FROM crossbones." . $table . " WHERE alert_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid));
                                                                        $result['value'] = $res[0][$field] ;
                                                                        $result['message'] = 'crossbones.' . $table . ' alert_id="' . $rid . '", ' . $field . '="' . $res[0][$field] .'"' ;
                                                                        break;

                                            case      'alert_contact' : $sql = "SELECT alert_id FROM crossbones." . $table . " WHERE alert_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid));
                                                                        if(!($res)){
                                                                            $params = array(
                                                                                'alert_id' => $rid,
                                                                                'method' => 'all',
                                                                                'mode' => $mode
                                                                            );
                                                                            if ($this->db_write->insert('crossbones.'.$table, $params)) {
                                                                               $result = $this->db_write->lastInsertId();
                                                                            }

                                                                        }
                                                                        $sql = "UPDATE crossbones." . $table . " SET " . $field . " = ? , mode = ? WHERE alert_id = ?";
                                                                        if($this->db_read->executeQuery($sql, array($value,$mode,$rid))){
                                                                            // do nothing
                                                                        }
                                                                        $sql = "SELECT " . $field . " FROM crossbones." . $table . " WHERE alert_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid));
                                                                        $result['value'] = $res[0][$field] ;
                                                                        $result['message'] = 'crossbones.' . $table . ' alert_id="' . $rid . '", ' . $field . '="' . $res[0][$field] .'", mode="' . $res[0]['mode'] .'"' ;
                                                                        break;

                                            case         'alert_unit' : $sql = "SELECT alert_id FROM crossbones." . $table . " WHERE alert_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid));
                                                                        if(!($res)){
                                                                            $params = array(
                                                                                'alert_id' => $rid,
                                                                                'mode' => $mode
                                                                            );
                                                                           if ($this->db_write->insert('crossbones.'.$table, $params)) {
                                                                               $result = $this->db_write->lastInsertId();
                                                                           }

                                                                        }
                                                                        $sql = "UPDATE crossbones." . $table . " SET " . $field . " = ? , mode = ? WHERE alert_id = ?";
                                                                        if($this->db_read->executeQuery($sql, array($value,$mode,$rid))){
                                                                            // do nothing
                                                                        }
                                                                        $sql = "SELECT " . $field . " FROM crossbones." . $table . " WHERE alert_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid));
                                                                        $result['value'] = $res[0][$field] ;
                                                                        $result['message'] = 'crossbones.' . $table . ' alert_id="' . $rid . '", ' . $field . '="' . $res[0][$field] .'", mode="' . $res[0]['mode'] .'"' ;
                                                                        break;
                                            
                                     case                   'contact' : $sql = "UPDATE crossbones." . $table . " SET " . $field . " = ? WHERE contact_id = ?";
                                                                        if($this->db_read->executeQuery($sql, array($value,$rid))){
                                                                            // do nothing
                                                                        }
                                                                        $sql = "SELECT " . $field . " FROM crossbones." . $table . " WHERE contact_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid));
                                                                        $result['value'] = $res[0][$field] ;
                                                                        $result['message'] = '##### crossbones.' . $table . ' rid="' . $rid . '", field="' . $field . '", check="' . $res[0][$field] .'", value="' . $value . '"'  ;
                                                                        break;
                                     
                                            case       'contactgroup' : $sql = "SELECT contactgroup_id FROM crossbones.contactgroup_contact WHERE contact_id = ? AND contactgroup_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid,$value));
                                                                        if((!($res))&&($rid)&&($value)){
                                                                            $result['message'] = 'crossbones.contactgroup_contact contact_id="' . $rid . '", contactgroup_id="' . $value .'"' ;
                                                                            $params = array(
                                                                                'contact_id' => $rid,
                                                                                'contactgroup_id' => $value,
                                                                                'method' => 'all'
                                                                            );
                                                                           if ($this->db_write->insert('crossbones.contactgroup_contact', $params)) {
                                                                               // $res = $this->db_write->lastInsertId();
                                                                           }

                                                                        }
                                                                        $sql = "SELECT contactgroup_id FROM crossbones.contactgroup_contact WHERE contact_id = ? AND contactgroup_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid,$value));
                                                                        $result['value'] = $res[0]['contactgroup_id'] ;
                                                                        $result['message'] = 'crossbones.contactgroup_contact contact_id="' . $rid . '", contactgroup_id="' . $res[0]['contactgroup_id'] .'", method="' . $res[0]['method'] .'"' ;
                                                                        break;
                                            
                                     case      'deletecommandpending' : $table = 'commandpending' ;
                                                                        $sql = "UPDATE crossbones." . $table . " SET " . $field . " = ? WHERE commandpending_id = ?";
                                                                        if($this->db_read->executeQuery($sql, array($value,$rid))){
                                                                            // do nothing
                                                                        }
                                                                        $sql = "SELECT " . $field . " FROM crossbones." . $table . " WHERE commandpending_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid));
                                                                        $result['value'] = $res[0][$field] ;
                                                                        $result['message'] = '##### crossbones.' . $table . ' rid="' . $rid . '", field="' . $field . '", check="' . $res[0][$field] .'", value="' . $value . '"'  ;
                                                                        break;

                                     case             'deletecontact' : $value = 900000000 + $user['account_id'] ;
                                                                        $sql = "SELECT contact_id FROM crossbones.contact WHERE contact_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid));
                                                                        $email = str_replace('@','#',$res[0]['email']) ;
                                                                        $sql = "UPDATE crossbones.contact SET account_id = ? , email = ? , active = ? WHERE contact_id = ?";
                                                                        if($this->db_read->executeQuery($sql, array($value,$email,1,$rid))){
                                                                            // do nothing
                                                                        }
                                                                        $sql = "DELETE FROM crossbones.alert_contact WHERE contact_id = ?";
                                                                        if($this->db_read->executeQuery($sql, array($rid))){
                                                                            // do nothing
                                                                        }
                                                                        $sql = "DELETE FROM crossbones.contactgroup_contact WHERE contact_id = ?";
                                                                        if($this->db_read->executeQuery($sql, array($rid))){
                                                                            // do nothing
                                                                        }
                                                                        $sql = "DELETE FROM crossbones.schedulereport_contact WHERE contact_id = ?";
                                                                        if($this->db_read->executeQuery($sql, array($rid))){
                                                                            // do nothing
                                                                        }
                                                                        $sql = "SELECT contact_id FROM crossbones.contact WHERE contact_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid));
                                                                        $result['value'] = $res[0][$field] ;
                                                                        $result['message'] = '##### crossbones.' . $table . ' rid="' . $rid . '", field="' . $field . '", check="' . $res[0][$field] .'", value="' . $value . '"'  ;
                                                                        break;

                                     case        'deletecontactgroup' : $table = 'contactgroup' ;
                                                                        $sql = "UPDATE crossbones." . $table . " SET " . $field . " = ? WHERE contactgroup_id = ?";
                                                                        if($this->db_read->executeQuery($sql, array($value,$rid))){
                                                                            // do nothing
                                                                        }
                                                                        $sql = "SELECT " . $field . " FROM crossbones." . $table . " WHERE contactgroup_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid));
                                                                        $result['value'] = $res[0][$field] ;
                                                                        $result['message'] = '##### crossbones.' . $table . ' rid="' . $rid . '", field="' . $field . '", check="' . $res[0][$field] .'", value="' . $value . '"'  ;
                                                                        break;

                                     case          'deleteincomplete' : $table = 'territoryupload' ;
                                                                        $sql = "UPDATE crossbones." . $table . " SET " . $field . " = ? WHERE territoryupload_id = ?";
                                                                        if($this->db_read->executeQuery($sql, array($value,$rid))){
                                                                            // do nothing
                                                                        }
                                                                        $sql = "SELECT territoryupload_id FROM crossbones.territoryupload WHERE territoryupload_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid));
                                                                        $result['value'] = $res[0][$field] ;
                                                                        $result['message'] = '##### crossbones.territoryupload rid="' . $rid . '", check="' . $res[0][$field] .'", value="' . $value . '"'  ;
                                                                        break;

                                     case      'deleteschedulereport' : $table = 'schedulereport' ;
                                                                        $sql = "UPDATE crossbones." . $table . " SET " . $field . " = ? WHERE schedulereport_id = ?";
                                                                        if($this->db_read->executeQuery($sql, array($value,$rid))){
                                                                            // do nothing
                                                                        }
                                                                        $sql = "SELECT " . $field . " FROM crossbones." . $table . " WHERE schedulereport_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid));
                                                                        $result['value'] = $res[0][$field] ;
                                                                        $result['message'] = '##### crossbones.' . $table . ' rid="' . $rid . '", field="' . $field . '", check="' . $res[0][$field] .'", value="' . $value . '"'  ;
                                                                        break;

                                            case         'deverified' : $sql = "UPDATE crossbones.territory SET verifydate = ? WHERE territory_id = ?";
                                                                        if($this->db_read->executeQuery($sql, array('0000-00-00',$rid))){
                                                                            // do nothing
                                                                        }
                                                                        $sql = "SELECT verifydate FROM crossbones.territory WHERE territory_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid));
                                                                        $result['value'] = $res[0]['verifydate'] ;
                                                                        $result['message'] = 'crossbones.territory territory_id="' . $rid . '", verifydate="' . $res[0]['verifydate'] .'"' ;
                                                                        break;
                                            
                                            case            'latlong' : $sql = "UPDATE crossbones.territory SET latitude = ?, longitude = ? WHERE territory_id = ?";
                                                                        $latlong = explode(':',$value);
                                                                        if($this->db_read->executeQuery($sql, array($latlong[0],$latlong[1],$rid))){
                                                                            // do nothing
                                                                        }
                                                                        $sql = "SELECT latitude, longitude FROM crossbones.territory WHERE territory_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid));
                                                                        $result['value'] = $res[0]['latitude'] . ':' . $res[0]['longitude'] ;
                                                                        $result['message'] = 'crossbones.territory territory_id="' . $rid . '", latlong="' . $result['value'] .'"' ;
                                                                        break;

                                            case         'permission' : $sql = "UPDATE crossbones." . $table . " SET " . $field . " = ? WHERE permission_id = ?";
                                                                        if($this->db_read->executeQuery($sql, array($value,$rid))){
                                                                            // do nothing
                                                                        }
                                                                        $sql = "SELECT " . $field . " FROM crossbones." . $table . " WHERE permission_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid));
                                                                        $result['value'] = $res[0][$field] ;
                                                                        $result['message'] = 'crossbones.' . $table . ' permission_id="' . $rid . '", ' . $field . '="' . $res[0][$field] .'"' ;
                                                                        break;

                                            case               'repo' : $sql = "UPDATE crossbones." . $table . " SET " . $field . " = ? WHERE repo_id = ?";
                                                                        if($this->db_read->executeQuery($sql, array($value,$rid))){
                                                                            // do nothing
                                                                        }
                                                                        $sql = "SELECT " . $field . " FROM crossbones." . $table . " WHERE repo_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid));
                                                                        $result['value'] = $res[0][$field] ;
                                                                        $result['message'] = 'crossbones.' . $table . ' repo_id="' . $rid . '", ' . $field . '="' . $res[0][$field] .'"' ;
                                                                        break;

                                            case          'territory' :
                                            case     'unit_territory' : $sql = "UPDATE crossbones." . $table . " SET " . $field . " = ? WHERE territory_id = ?";
                                                                        if($this->db_read->executeQuery($sql, array($value,$rid))){
                                                                            // do nothing
                                                                        }
                                                                        $sql = "SELECT " . $field . " FROM crossbones." . $table . " WHERE territory_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid));
                                                                        $result['value'] = $res[0][$field] ;
                                                                        $result['message'] = 'crossbones.' . $table . ' territory_id="' . $rid . '", ' . $field . '="' . $res[0][$field] .'"' ;
                                                                        break;

                                            case   'territory-delete' : $sql = "UPDATE crossbones.territory SET " . $field . " = ? , territorygroup_id = ? WHERE territory_id = ?";
                                                                        if($this->db_read->executeQuery($sql, array($value,0,$rid))){
                                                                            // do nothing
                                                                            $sql = "DELETE FROM crossbones.unit_territory WHERE territory_id = ?" ;
                                                                            if($this->db_read->executeQuery($sql, array($rid))){
                                                                            }
                                                                        }
                                                                        $sql = "SELECT " . $field . " FROM crossbones.territory WHERE territory_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid));
                                                                        $result['value'] = $res[0][$field] ;
                                                                        $result['message'] = 'crossbones.' . $table . ' territory_id="' . $rid . '", ' . $field . '="' . $res[0][$field] .'"' ;
                                                                        break;

                                            case     'territorygroup' : $sql = "UPDATE crossbones." . $table . " SET " . $field . " = ? WHERE territorygroup_id = ?";
                                                                        if($this->db_read->executeQuery($sql, array($value,$rid))){
                                                                            // do nothing
                                                                        }
                                                                        $sql = "SELECT " . $field . " FROM crossbones." . $table . " WHERE territorygroup_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid));
                                                                        $result['value'] = $res[0][$field] ;
                                                                        $result['message'] = 'crossbones.' . $table . ' territorygroup_id="' . $rid . '", ' . $field . '="' . $res[0][$field] .'"' ;
                                                                        break;

                                            case               'unit' :
                                            case      'unitattribute' : $sql = "UPDATE crossbones." . $table . " SET " . $field . " = ? WHERE unit_id = ?";
                                                                        if($this->db_read->executeQuery($sql, array($value,$rid))){
                                                                            // do nothing
                                                                        }
                                                                        $sql = "SELECT " . $field . " FROM crossbones." . $table . " WHERE unit_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid));
                                                                        $result['value'] = $res[0][$field] ;
                                                                        $result['message'] = 'crossbones.' . $table . ' >>>>>>>>>>>>>>>>> unit_id="' . $rid . '", ' . $field . '="' . $res[0][$field] .'"' ;
                                                                        break;

                                            case          'unitgroup' : $sql = "UPDATE crossbones." . $table . " SET " . $field . " = ? WHERE unitgroup_id = ?";
                                                                        if($this->db_read->executeQuery($sql, array($value,$rid))){
                                                                            // do nothing
                                                                        }
                                                                        $sql = "SELECT " . $field . " FROM crossbones." . $table . " WHERE unitgroup_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid));
                                                                        $result['value'] = $res[0][$field] ;
                                                                        $result['message'] = 'crossbones.' . $table . ' unitgroup_id="' . $rid . '", ' . $field . '="' . $res[0][$field] .'"' ;
                                                                        break;

                                            case               'user' : if($field=='username'){
                                                                            $value = strtolower(preg_replace("/[^a-zA-Z0-9-]/","", $value));
                                                                            $sql = "SELECT " . $field . " FROM crossbones." . $table . " WHERE " . $field . " = ?";
                                                                            $res = $this->db_read->fetchAll($sql, array($value));
                                                                        }
                                                                        if(($field)&&(!($res))){
                                                                            $sql = "UPDATE crossbones." . $table . " SET " . $field . " = ? WHERE user_id = ?";
                                                                            if($this->db_read->executeQuery($sql, array($value,$rid))){
                                                                                // do nothing
                                                                            }
                                                                            $sql = "SELECT " . $field . " FROM crossbones." . $table . " WHERE user_id = ?";
                                                                            $result = $this->db_read->fetchAll($sql, array($rid));
                                                                        } else {
                                                                            $sql = "SELECT " . $field . " FROM crossbones." . $table . " WHERE user_id = ?";
                                                                            $result = $this->db_read->fetchAll($sql, array($rid));
                                                                            if(strtolower(preg_replace("/[^a-zA-Z0-9-]/","", $res[0][$field])) != strtolower(preg_replace("/[^a-zA-Z0-9-]/","", $result[0][$field]))){
                                                                                $result['alert'] = 'Sorry, "' . $res[0][$field] . '" is already in use' ;
                                                                            }
                                                                        }
                                                                        $result['value'] = $res[0][$field] ;
                                                                        $result['message'] = 'crossbones.' . $table . ' user_id="' . $rid . '", ' . $field . '="' . $res[0][$field] .'"' ;
                                                                        break;

                                            case           'usertype' : $sql = "UPDATE crossbones." . $table . " SET " . $field . " = ? WHERE usertype_id = ?";
                                                                        if($this->db_read->executeQuery($sql, array($value,$rid))){
                                                                            // do nothing
                                                                        }
                                                                        $sql = "SELECT " . $field . " FROM crossbones." . $table . " WHERE usertype_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid));
                                                                        $result['value'] = $res[0][$field] ;
                                                                        $result['message'] = 'crossbones.' . $table . ' usertype_id="' . $rid . '", ' . $field . '="' . $res[0][$field] .'"' ;
                                                                        break;

                                            case           'verified' : $sql = "UPDATE crossbones.territory SET verifydate = NOW() WHERE territory_id = ?";
                                                                        if($this->db_read->executeQuery($sql, array($rid))){
                                                                            // do nothing
                                                                        }
                                                                        $sql = "SELECT verifydate FROM crossbones.territory WHERE territory_id = ?";
                                                                        $res = $this->db_read->fetchAll($sql, array($rid));
                                                                        $result['value'] = $res[0]['verifydate'] ;
                                                                        $result['message'] = 'crossbones.territory territory_id="' . $rid . '", verifydate="' . $res[0]['verifydate'] .'"' ;
                                                                        break;

                                        }

        }

        return $result;

    }

    /**
     * Support Ajax Got-It Requests
     */
    public function gotIt($user_id,$gotit) {

        switch($gotit){

            case            'sidebartoggle' :   $sql = "SELECT gotit_" . $gotit . " FROM crossbones.user WHERE user_id = ?";
                                                $res = $this->db_read->fetchAll($sql, array($user_id));
                                                break;

        }

        if($res[0]['gotit_'.$gotit] == '0000-00-00 00:00:00'){
            $res = null;
        }
                                                                        
        return $res ;

    }

    /**
     * Support Ajax Got-It Requests
     */
    public function ajaxGotIt($user_id,$gotit) {

        switch($gotit){

            case            'sidebartoggle' :   $sql = "UPDATE crossbones.user SET gotit_" . $gotit . " = NOW() WHERE user_id = ?";
            
                                                if($this->db_read->executeQuery($sql, array($user_id))){
                                                    // do nothing
                                                }

                                                $buffer[0] = $user_id . ':' . $sql;

                                                break;

        }
                                                                        
        return $buffer ;

    }

    /**
     * Support Ajax Group Requests
     */
    public function ajaxGroups($group,$aid,$uid) {

        switch($group){

            case               'contacts' : $sql = "SELECT
                                                CONCAT ( c.firstname , ' ' , c.lastname  ) as contactname,
                                                c.contact_id as contact_id
                                                FROM crossbones.contact c
                                                WHERE c.account_id = ?
                                                AND c.active = ?";
                                            $result = $this->db_read->fetchAll($sql, array($aid,0));
                                            $results['available'] = $result;
                                            $sql = "SELECT
                                                    CONCAT ( c.firstname , ' ' , c.lastname  ) as contactname,
                                                    c.contact_id as contact_id
                                                    FROM crossbones.contactgroup_contact cgc
                                                    LEFT JOIN crossbones.contact c ON c.contact_id = cgc.contact_id
                                                    LEFT JOIN crossbones.contactgroup cg ON cg.contactgroup_id = cgc.contactgroup_id
                                                    WHERE cgc.contactgroup_id = ?
                                                    AND cg.active = ?";
                                            $result = $this->db_read->fetchAll($sql, array($uid,0));
                                            $results['assigned'] = $result;
                                            break;

            case          'contactgroups' : $sql = "SELECT
                                                cg.contactgroupname as contactgroupname,
                                                cg.contactgroup_id as contactgroup_id
                                                FROM crossbones.contactgroup cg
                                                WHERE cg.account_id = ?
                                                AND cg.active = ?";
                                            $result = $this->db_read->fetchAll($sql, array($aid,0));
                                            $results['available'] = $result;
                                            $sql = "SELECT
                                                    cg.contactgroupname as contactgroupname,
                                                    cgc.contactgroup_id as contactgroup_id
                                                    FROM crossbones.contactgroup_contact cgc
                                                    LEFT JOIN crossbones.contactgroup cg ON cg.contactgroup_id = cgc.contactgroup_id
                                                    WHERE cgc.contactgroup_id = ?
                                                    AND cg.active = ?";
                                            $result = $this->db_read->fetchAll($sql, array($uid,0));
                                            $results['assigned'] = $result;
                                            break;

            case              'territory' : $sql = "SELECT
                                                tg.territorygroupname as territorygroupname,
                                                t.territoryname as territoryname,
                                                t.territory_id as territory_id
                                                FROM crossbones.territory t
                                                LEFT JOIN crossbones.territorygroup tg ON tg.territorygroup_id = t.territorygroup_id
                                                WHERE t.account_id = ?
                                                AND t.active = ?";
                                            $result = $this->db_read->fetchAll($sql, array($aid,1));
                                            $results['available'] = $result;
                                            $sql = "SELECT
                                                tg.territorygroupname as territorygroupname,
                                                t.territoryname as territoryname,
                                                t.territory_id as territory_id
                                                FROM crossbones.territory t
                                                LEFT JOIN crossbones.territorygroup tg ON tg.territorygroup_id = t.territorygroup_id
                                                WHERE t.account_id = ?
                                                AND t.territorygroup_id = ?
                                                AND t.active = ?";
                                            $result = $this->db_read->fetchAll($sql, array($aid,$uid,1));
                                            $results['assigned'] = $result;
                                            break;

            case        'territorygroups' : $sql = "SELECT
                                                tg.territorygroupname as territorygroupname,
                                                tg.territorygroup_id as territorygroup_id
                                                FROM crossbones.territorygroup tg
                                                WHERE tg.account_id = ?
                                                AND tg.active = ?";
                                            $result = $this->db_read->fetchAll($sql, array($aid,1));
                                            $results['available'] = $result;
                                            $sql = "SELECT
                                                    tg.territorygroupname as territorygroupname,
                                                    utg.territorygroup_id as territorygroup_id
                                                    FROM crossbones.user_territorygroup utg
                                                    LEFT JOIN crossbones.territorygroup tg ON tg.territorygroup_id = utg.territorygroup_id
                                                    WHERE utg.user_id = ?
                                                    AND tg.active = ?";
                                            $result = $this->db_read->fetchAll($sql, array($uid,1));
                                            $results['assigned'] = $result;
                                            break;

            case                   'unit' : $sql = "SELECT
                                                ug.unitgroupname as unitgroupname,
                                                ug.unitgroup_id as unitgroup_id
                                                FROM crossbones.unitgroup ug
                                                WHERE ug.account_id = ?
                                                AND ug.active = ?";
                                            $result = $this->db_read->fetchAll($sql, array($aid,1));
                                            $results['available'] = $result;
                                            $sql = "SELECT
                                                    ug.unitgroupname as unitgroupname,
                                                    uug.unitgroup_id as unitgroup_id
                                                    FROM crossbones.user_unitgroup uug
                                                    LEFT JOIN crossbones.unitgroup ug ON ug.unitgroup_id = uug.unitgroup_id
                                                    WHERE uug.user_id = ?
                                                    AND ug.active = ?";
                                            $result = $this->db_read->fetchAll($sql, array($uid,1));
                                            $results['assigned'] = $result;
                                            break;

            case    'vehiclegroupdevices' : $sql = "SELECT
                                                u.unitname as unitname,
                                                u.unit_id as unit_id
                                                FROM crossbones.unit u
                                                WHERE u.account_id = ?";
                                            $result = $this->db_read->fetchAll($sql, array($aid));
                                            $results['available'] = $result;
                                            $sql = "SELECT
                                                u.unitname as unitname,
                                                u.unit_id as unit_id
                                                FROM crossbones.unit u
                                                WHERE u.account_id = ?
                                                AND u.unitgroup_id = ?";
                                            $result = $this->db_read->fetchAll($sql, array($aid,$uid));
                                            $results['assigned'] = $result;
                                            break;

            case     'vehiclegroupusers' :  $sql = "SELECT
                                                uug.user_id as user_id
                                                FROM crossbones.user u
                                                LEFT JOIN crossbones.user_unitgroup uug on uug.user_id = u.user_id
                                                WHERE u.account_id = ?
                                                AND u.userstatus_id < 4 
                                                AND u.userstatus_id > 0 
                                                AND u.user_id IS NOT NULL 
                                                AND uug.unitgroup_id = ?";
                                            $results = $this->db_read->fetchAll($sql, array($aid,$uid));
                                            break;

            case 'vehiclegroupusertypes' :  $sql = "SELECT
                                                utug.usertype_id as usertype_id
                                                FROM crossbones.usertype ut
                                                LEFT JOIN crossbones.usertype_unitgroup utug on utug.usertype_id = ut.usertype_id
                                                WHERE utug.unitgroup_id = ?";
                                            $results = $this->db_read->fetchAll($sql, array($uid));
                                            // $sql = "SELECT
                                            //     utug.usertype_id as usertype_id
                                            //     FROM crossbones.usertype ut
                                            //     LEFT JOIN crossbones.usertype_unitgroup utug on utug.usertype_id = ut.usertype_id
                                            //     WHERE ut.account_id = ?
                                            //     AND utug.unitgroup_id = ?";
                                            // $results = $this->db_read->fetchAll($sql, array($aid,$uid));
                                            // $results = '#' . $aid . '#' . $uid . '#' ;
                                            break;

        }

        return $results;

    }

    /**
     * Support Ajax Init Requests
     */
    public function ajaxFormFill($user_id,$user,$form,$uid) {

        switch ($form) {

            case               'alert-edit' :   $sql = "SELECT
                                                        a.*,
                                                        ac.contact_id as contact_id,
                                                        ac.contactgroup_id as contactgroup_id,
                                                        ac.method as method,
                                                        at.territory_id as territory_id,
                                                        at.territorygroup_id as territorygroup_id,
                                                        au.mode as unitgroup_mode,
                                                        au.unit_id as unit_id,
                                                        au.unitgroup_id as unitgroup_id
                                                        FROM crossbones.alert a
                                                        LEFT JOIN crossbones.alert_contact ac ON ac.alert_id = a.alert_id
                                                        LEFT JOIN crossbones.alert_territory at ON at.alert_id = a.alert_id
                                                        LEFT JOIN crossbones.alert_unit au ON au.alert_id = a.alert_id
                                                        WHERE a.alert_id = ?";
                                                $result = $this->db_read->fetchAll($sql, array($uid));
                                                if ($result[0]['unit_id']) {
                                                    $result[0]['unitgroup_mode'] = 1;
                                                } else if ($result[0]['unitgroup_id']){
                                                    $result[0]['unitgroup_mode'] = 2;
                                                } else {
                                                    $result[0]['unitgroup_mode'] = 3;
                                                }
                                                break;

            case              'device-edit' :   $sql = "SELECT
                                                        c.firstname as firstname,
                                                        c.lastname as lastname,  
                                                        c.email as email,
                                                        c.cellphone as cellphone,  
                                                        c.homephone as homephone,  
                                                        c.streetaddress as streetaddress,  
                                                        c.city as city,  
                                                        c.state as state,  
                                                        c.zipcode as zipcode,  
                                                        u.unitgroup_id as unitgroup_id,  
                                                        u.unitname as unitname,  
                                                        u.unitstatus_id as unitstatus_id,  
                                                        u.serialnumber as serialnumber,  
                                                        ua.vin as vin,  
                                                        ua.make as make,  
                                                        ua.model as model,  
                                                        ua.year as year,  
                                                        ua.color as color,  
                                                        ua.stocknumber as stocknumber,  
                                                        ua.licenseplatenumber as licenseplatenumber,  
                                                        ua.loannumber as loannumber,  
                                                        ua.plan as plan,  
                                                        ua.purchasedate as purchasedate,  
                                                        ua.renewaldate as renewaldate,  
                                                        ua.lastrenewaldate as lastrenewaldate,  
                                                        ua.activatedate as activatedate,  
                                                        ua.deactivatedate as deactivatedate,  
                                                        ui.installer as installer,  
                                                        ui.installdate as installdate,  
                                                        uo.initialodometer as initialodometer,  
                                                        uo.currentodometer as currentodometer,  
                                                        uo.initialodometer + uo.currentodometer as totalodometer,  
                                                        us.unitstatusname as unitstatusname  
                                                        FROM crossbones.unit u
                                                        LEFT JOIN crossbones.customer c ON c.unit_id = u.unit_id
                                                        LEFT JOIN crossbones.unitattribute ua ON ua.unit_id = u.unit_id
                                                        LEFT JOIN crossbones.unitinstallation ui ON ui.unit_id = u.unit_id
                                                        LEFT JOIN crossbones.unitodometer uo ON uo.unitodometer_id = u.unitodometer_id
                                                        LEFT JOIN crossbones.unitstatus us ON us.unitstatus_id = u.unitstatus_id
                                                        WHERE u.account_id = ?
                                                        AND u.unit_id = ?
                                                        LIMIT 1";

                                                $results = $this->db_read->fetchAll($sql, array($user['account_id'],$uid));
                                                $result = $results[0];
                                                break;

            case             'edit-contact' :   $sql = "SELECT * FROM crossbones.contact c WHERE c.contact_id = ?";
                                                $result = $this->db_read->fetchAll($sql, array($uid));
                                                break;

            case         'scheduled-report' :   $sql = "SELECT sr.*,
                                                        src.contact_id as contact_id,
                                                        src.contactgroup_id as contactgroup_id,
                                                        srt.territory_id as territory_id,
                                                        srt.territorygroup_id as territorygroup_id,
                                                        sru.unit_id as unit_id,
                                                        sru.unitgroup_id as unitgroup_id 
                                                        FROM crossbones.schedulereport sr 
                                                        LEFT JOIN crossbones.schedulereport_contact src ON src.schedulereport_id = sr.schedulereport_id
                                                        LEFT JOIN crossbones.schedulereport_territory srt ON srt.schedulereport_id = sr.schedulereport_id
                                                        LEFT JOIN crossbones.schedulereport_unit sru ON sru.schedulereport_id = sr.schedulereport_id
                                                        WHERE sr.account_id = ?
                                                        AND sr.schedulereport_id = ?";
                                                $result = $this->db_read->fetchAll($sql, array($user['account_id'],$uid));
                                                break;

            case                'user-edit' :   $sql = "SELECT
                                                        c.firstname as firstname,
                                                        c.lastname as lastname,  
                                                        c.email as cemail,
                                                        c.cellnumber as cellnumber,  
                                                        cc.cellcarrier_id as cellcarrier_id,  
                                                        u.firstname as ufirstname,
                                                        u.lastname as ulastname,  
                                                        u.email as email,
                                                        u.usertype_id as usertype_id  
                                                        FROM crossbones.user u
                                                        LEFT JOIN crossbones.contact c ON c.user_id = u.user_id
                                                        LEFT JOIN crossbones.cellcarrier cc ON cc.cellcarrier_id = c.cellcarrier_id
                                                        WHERE u.account_id = ?
                                                        AND u.user_id = ?
                                                        LIMIT 1";
                                                $results = $this->db_read->fetchAll($sql, array($user['account_id'],$uid));
                                                $result = $results[0];
                                                if(!($result['firstname'])){
                                                    $result['firstname'] = $results[0]['ufirstname'] ;
                                                }
                                                if(!($result['lasstname'])){
                                                    $result['lastname'] = $results[0]['ulastname'] ;
                                                }
                                                if(!($result['email'])){
                                                    $result['email'] = $results[0]['cemail'] ;
                                                }
                                                break;

            case           'user-type-edit' :   $sql = "SELECT
                                                        utp.permission_id as permission_id,
                                                        p.permissioncategory_id as permissioncategory_id
                                                        FROM crossbones.usertype_permission utp
                                                        LEFT JOIN crossbones.permission p ON p.permission_id = utp.permission_id
                                                        WHERE utp.usertype_id = ?";
                                                $result = $this->db_read->fetchAll($sql, array($uid));
                                                break;

        }

        return $result;

    }

    /**
     * Support Ajax Init Requests
     */
    public function ajaxInit($user,$init) {

        switch ($init) {
            
            case          'alert-add-contact' : $sql = "SELECT c.contact_id AS v, CONCAT ( c.firstname , ' ' , c.lastname  ) AS k FROM crossbones.contact c
                                                    WHERE c.account_id = ?
                                                    ORDER BY c.firstname ASC , c.lastname ASC LIMIT 1";
                                                $result = $this->db_read->fetchAll($sql, array($user['account_id']));
                                                break;

            case     'alert-add-contactgroup' : $sql = "SELECT cg.contactgroup_id AS v, cg.contactgroupname AS k FROM crossbones.contactgroup cg
                                                    WHERE cg.account_id = ?
                                                    ORDER BY cg.contactgroupname ASC LIMIT 1";
                                                $result = $this->db_read->fetchAll($sql, array($user['account_id']));
                                                break;

            case          'alert-add-landmark' : $sql = "SELECT t.territory_id AS v, t.territoryname AS k FROM crossbones.territory t
                                                    WHERE t.account_id = ?
                                                    ORDER BY t.territoryname ASC LIMIT 1";
                                                $result = $this->db_read->fetchAll($sql, array($user['account_id']));
                                                break;

            case     'alert-add-landmarkgroup' : $sql = "SELECT tg.territorygroup_id AS v, tg.territorygroupname AS k FROM crossbones.territorygroup tg
                                                    WHERE tg.account_id = ?
                                                    ORDER BY tg.territorygroupname ASC LIMIT 1";
                                                $result = $this->db_read->fetchAll($sql, array($user['account_id']));
                                                break;

            case          'alert-add-vehicle' : $sql = "SELECT u.unit_id AS v, u.unitname AS k FROM crossbones.unit u
                                                    WHERE u.account_id = ?
                                                    ORDER BY u.unitname ASC LIMIT 1";
                                                $result = $this->db_read->fetchAll($sql, array($user['account_id']));
                                                break;

            case     'alert-add-vehiclegroup' : $sql = "SELECT ug.unitgroup_id AS v, ug.unitgroupname AS k FROM crossbones.unitgroup ug
                                                    WHERE ug.account_id = ?
                                                    ORDER BY ug.unitgroupname ASC LIMIT 1";
                                                $result = $this->db_read->fetchAll($sql, array($user['account_id']));
                                                break;

            case        'contact-add-carrier' : $sql = "SELECT cc.cellcarrier_id AS v, cc.cellcarrier AS k FROM crossbones.cellcarrier cc
                                                    WHERE cc.cellcarrier_id != ''
                                                    ORDER BY cc.cellcarrier ASC LIMIT 1";
                                                $result = $this->db_read->fetchAll($sql, array());
                                                break;

            case          'contact-add-group' : $sql = "SELECT cg.contactgroup_id AS v, cg.contactgroupname AS k FROM crossbones.contactgroup cg
                                                    WHERE cg.account_id = ?
                                                    ORDER BY cg.contactgroupname ASC LIMIT 1";
                                                $result = $this->db_read->fetchAll($sql, array($user['account_id']));
                                                break;

        }

        return $result;

    }

    /**
     * Support Ajax Load List Requests
     */
    public function ajaxLoadList($user_id,$account_id,$ele,$uid,$search)
    {

        switch($ele){

                                case   'search-batch-devices' : if($search){
                                                                    $search = ' AND ( unitname LIKE \'' . $search . '%\' OR unitname LIKE \'%' . $search . '%\' OR serialnumber LIKE \'' . $search . '%\' OR serialnumber LIKE \'%' . $search . '%\' )' ;
                                                                }
                                                                if($uid>0){
                                                                    $sql = "SELECT serialnumber AS k, unitname AS v 
                                                                            FROM crossbones.unit 
                                                                            WHERE account_id = ? 
                                                                            AND unitgroup_id = ?"
                                                                         . $search
                                                                         . "ORDER BY unitname ASC";
                                                                    $result = $this->db_read->fetchAll($sql, array($account_id,$uid));
                                                                } else {
                                                                    $sql = "SELECT serialnumber AS k, unitname AS v 
                                                                            FROM crossbones.unit 
                                                                            WHERE account_id = ?" 
                                                                         . $search
                                                                         . "ORDER BY unitname ASC";
                                                                    $result = $this->db_read->fetchAll($sql, array($account_id));
                                                                }
                                                                break;

                                case   'search-transfer-from' :
                                case     'search-transfer-to' : if($search){
                                                                    $search = ' AND ( unitname LIKE \'' . $search . '%\' OR unitname LIKE \'%' . $search . '%\' OR serialnumber LIKE \'' . $search . '%\' OR serialnumber LIKE \'%' . $search . '%\' )' ;
                                                                }
                                                                if($uid>0){
                                                                    $sql = "SELECT unit_id AS k, CONCAT ( unitname, ' <div class=\'column-serialnumber pull-right\'><span class=\'text-grey pull-left\'>', serialnumber, '</span></div>' ) AS v 
                                                                            FROM crossbones.unit 
                                                                            WHERE account_id = ? 
                                                                            AND unitgroup_id = ?"
                                                                         . $search
                                                                         . "ORDER BY unitname ASC";
                                                                    $result = $this->db_read->fetchAll($sql, array($account_id,$uid));
                                                                } else {
                                                                    $sql = "SELECT unit_id AS k, CONCAT ( unitname, ' <div class=\'column-serialnumber pull-right\'><span class=\'text-grey pull-left\'>', serialnumber, '</span></div>' ) AS v 
                                                                            FROM crossbones.unit 
                                                                            WHERE account_id = ?" 
                                                                         . $search
                                                                         . "ORDER BY unitname ASC";
                                                                    $result = $this->db_read->fetchAll($sql, array($account_id));
                                                                }
                                                                break;

            case 'search-vehicle-group-devices-transfer-from' :
            case   'search-vehicle-group-devices-transfer-to' : if($search){
                                                                    $search = ' AND ( u.unitname LIKE \'' . $search . '%\' OR u.unitname LIKE \'%' . $search . '%\' )' ;
                                                                }
                                                                $sql = "SELECT
                                                                        u.unitname as v,
                                                                        u.unit_id as k
                                                                        FROM crossbones.unit u
                                                                        WHERE u.account_id = ?"
                                                                     . $search
                                                                     . "AND u.unitgroup_id = ?";
                                                                $result = $this->db_read->fetchAll($sql, array($account_id,$uid));
                                                                break;

            case   'search-vehicle-group-users-transfer-from' :
            case     'search-vehicle-group-users-transfer-to' : if($search){
                                                                    $search = ' AND ( u.firstname LIKE \'' . $search . '%\' OR u.firstname LIKE \'%' . $search . '%\' OR u.lastname LIKE \'' . $search . '%\' OR u.lastname LIKE \'%' . $search . '%\' OR u.username LIKE \'' . $search . '%\' OR u.username LIKE \'%' . $search . '%\' )' ;
                                                                }
                                                                $sql = "SELECT
                                                                        CONCAT ( u.firstname , ' ' , u.lastname , ' <span class=\"text-grey\">- ' , u.username , '</span>'  ) as v,
                                                                        u.user_id as k
                                                                        FROM crossbones.user u
                                                                        LEFT JOIN crossbones.user_unitgroup uug on uug.user_id = u.user_id
                                                                        WHERE u.account_id = ?"
                                                                     . $search
                                                                     . "AND uug.unitgroup_id = ?";
                                                                $result = $this->db_read->fetchAll($sql, array($account_id,$uid));
                                                                break;

        }

        return $result;

    }

    /**
     * Support Ajax Load Transerfee Requests
     */
    public function ajaxLoadTransferee($account_id,$createdate)
    {
        $sql = "SELECT * 
                FROM crossbones.account 
                WHERE account_id = ? ";
        $result = $this->db_read->fetchAll($sql, array($account_id));
        return $result[0];
    }

    /**
     * Support Ajax Locate Requests
     */
    public function ajaxLocate($user,$unit_id) {

        $udb = $this->getUnitDb($unit_id);
        // $sql = "SELECT * FROM " . $udb . ".unit" . $unit_id . " WHERE event_id = '7' AND servertime > DATE_SUB(NOW(), INTERVAL 5 MINUTE) ORDER BY id DESC LIMIT 1";
        $sql = "SELECT * FROM " . $udb . ".unit" . $unit_id . " WHERE event_id = '7' AND servertime > DATE_SUB(NOW(), INTERVAL 5 MINUTE) ORDER BY unittime DESC LIMIT 1";

        $result = $this->db_read->fetchAll($sql, array());

        return $result;

    }

    /**
     * Support Ajax Update Options Requests
     */
    public function ajaxOptions($user,$unit_id,$element,$post) {

        $res = array();

        switch ($element) {

            //
            // DROPDOWN+SELECT EDITABLE DATA
            //

            case            'alertcontactcontact' :
            case                        'contact' : $sql = "SELECT c.contact_id AS v, CONCAT ( c.firstname , ' ' , c.lastname  ) AS k FROM crossbones.contact c
                                                            WHERE c.account_id = ?
                                                            AND c.active = ?
                                                            ORDER BY firstname ASC , lastname ASC";
                                                    $res = $this->db_read->fetchAll($sql, array($user['account_id'],'0'));
                                                    break;

            case              'alertcontactgroup' :
            case                   'contactgroup' : $sql = "SELECT cg.contactgroup_id AS v, cg.contactgroupname AS k FROM crossbones.contactgroup cg
                                                            WHERE cg.account_id = ?
                                                            AND cg.active = ?
                                                            ORDER BY contactgroupname ASC";
                                                    $res = $this->db_read->fetchAll($sql, array($user['account_id'],0));
                                                    break;

            case               'alertcontactmode' : $res[0]['v'] = '0' ;        // Value
                                                    $res[0]['k'] = 'Report Only' ;  // Label
                                                    $res[1]['v'] = '2' ;        // Value
                                                    $res[1]['k'] = 'Contact' ;  // Label
                                                    $res[2]['v'] = '1' ;        // Value
                                                    $res[2]['k'] = 'Group' ;    // Label
                                                    break;

            case                      'alerttype' : $sql = "SELECT at.alerttype_id AS v, at.alerttypename AS k FROM crossbones.alerttype at
                                                            WHERE at.active = '1'
                                                            ORDER BY alerttypename ASC";
                                                    $res = $this->db_read->fetchAll($sql, array());
                                                    break;

            case                 'alertunitunit' : $sql = "SELECT u.unit_id AS v, u.unitname AS k FROM crossbones.unit u
                                                            WHERE u.account_id = ?
                                                            ORDER BY unitname ASC";
                                                    $res = $this->db_read->fetchAll($sql, array($user['account_id']));
                                                    break;

            case                 'alertunitgroup' : $sql = "SELECT ug.unitgroup_id AS v, ug.unitgroupname AS k FROM crossbones.unitgroup ug
                                                            WHERE ug.account_id = ?
                                                            ORDER BY unitgroupname ASC";
                                                    $res = $this->db_read->fetchAll($sql, array($user['account_id']));
                                                    break;

            case                  'alertunitmode' : $res[0]['v'] = '1' ;            $res[0]['k'] = 'Group' ;    // Value, Label
                                                    $res[1]['v'] = '2' ;            $res[1]['k'] = 'Vehicle' ;
                                                    break;

            case                           'days' : $res[0]['v'] = '1';                             $res[0]['k'] = 'Weekdays';    // Value, Label
                                                    $res[1]['v'] = '2';                             $res[1]['k'] = 'Weekends';
                                                    $res[2]['v'] = '3';                             $res[2]['k'] = 'All';
                                                    break;

            case                    'contactmode' : $res[0]['v'] = '1';                             $res[0]['k'] = 'Single Contact';    // Value, Label
                                                    $res[1]['v'] = '2';                             $res[1]['k'] = 'Contact Group';
                                                    $res[2]['v'] = '3';                             $res[2]['k'] = 'Report Only';
                                                    break;

            case                  'contactmethod' : $res[0]['v'] = 'all';                           $res[0]['k'] = 'All';    // Value, Label
                                                    $res[1]['v'] = 'email';                         $res[1]['k'] = 'E-mail';
                                                    $res[2]['v'] = 'sms';                           $res[2]['k'] = 'SMS';
                                                    break;

            case                  'contactstatus' : $res[0]['v'] = 'active';                        $res[0]['k'] = 'Active';    // Value, Label
                                                    $res[1]['v'] = 'pending';                       $res[1]['k'] = 'Pending';
                                                    break;

            case                        'country' : $sql = "SELECT country_id as v, country as k
                                                            FROM crossbones.countries
                                                            ORDER BY country ASC";
                                                    $res = $this->db_read->fetchAll($sql);
                                                    break;

            case                       'duration' : $res[0]['v'] = '1';                             $res[0]['k'] = '1 Day';    // Value, Label
                                                    $res[1]['v'] = '7';                             $res[1]['k'] = '7 Days';
                                                    $res[2]['v'] = '14';                            $res[2]['k'] = '14 Days';
                                                    $res[3]['v'] = '30';                            $res[3]['k'] = '30 Days';
                                                    break;

            case                        'gateway' : $sql = "SELECT cellcarrier_id as v, CONCAT ( gateway,' (',cellcarrier,')'  ) as k
                                                            FROM crossbones.cellcarrier
                                                            ORDER BY cellcarrier ASC";
                                                    $res = $this->db_read->fetchAll($sql);
                                                    break;

            case                          'hours' : $res[0]['v'] = '0';                             $res[0]['k'] = 'All';    // Value, Label
                                                    $res[1]['v'] = '1';                             $res[1]['k'] = 'In Range';
                                                    break;

            case                       'landmark' : $sql = "SELECT territory_id as v, territoryname as k
                                                            FROM crossbones.territory
                                                            WHERE account_id = ?
                                                            AND active = 1
                                                            ORDER BY territoryname ASC";
                                                    $res = $this->db_read->fetchAll($sql, array($user['account_id']));
                                                    break;

            case               'landmarkcategory' :
            case              'territorycategory' : $a='0';     $res[$a]['v'] = '0';                $res[$a]['k'] = 'None';
                                                    $a++;       $res[$a]['v'] = '1';                $res[$a]['k'] = 'Impound';
                                                    break;

            case                  'landmarkgroup' :
            case                 'territorygroup' : $sql = "SELECT territorygroup_id as v, territorygroupname as k
                                                            FROM crossbones.territorygroup
                                                            WHERE account_id = ?
                                                            AND active = 1
                                                            ORDER BY territorygroupname ASC";
                                                    $res = $this->db_read->fetchAll($sql, array($user['account_id']));
                                                    break;

            case                 'landmarkmethod' : $a='0';     $res[$a]['v'] = 'address';          $res[$a]['k'] = 'Street Address';
                                                    $a++;       $res[$a]['v'] = 'map';              $res[$a]['k'] = 'Map Click';
                                                    break;

            case                   'landmarkmode' : $res[0]['v'] = '1';                             $res[0]['k'] = 'Single Landmark';    // Value, Label
                                                    $res[1]['v'] = '2';                             $res[1]['k'] = 'Landmark Group';
                                                    $res[2]['v'] = '3';                             $res[2]['k'] = 'All Landmarks';
                                                    break;

            case                 'landmarkradius' :
            case                         'radius' : $a='0';     $res[$a]['v'] = '330';              $res[$a]['k'] = '1/16 Mile';
                                                    $a++;       $res[$a]['v'] = '660';              $res[$a]['k'] = '1/8 Mile';
                                                    $a++;       $res[$a]['v'] = '1320';             $res[$a]['k'] = '1/4 Mile';
                                                    $a++;       $res[$a]['v'] = '2640';             $res[$a]['k'] = '1/2 Mile';
                                                    $a++;       $res[$a]['v'] = '5280';             $res[$a]['k'] = '1 Mile';
                                                    $a++;       $res[$a]['v'] = '15840';            $res[$a]['k'] = '3 Miles';
                                                    $a++;       $res[$a]['v'] = '26400';            $res[$a]['k'] = '5 Miles';
                                                    $a++;       $res[$a]['v'] = '52800';            $res[$a]['k'] = '10 Miles';
                                                    $a++;       $res[$a]['v'] = '79200';            $res[$a]['k'] = '15 Miles';
                                                    $a++;       $res[$a]['v'] = '105600';           $res[$a]['k'] = '20 Miles';
                                                    $a++;       $res[$a]['v'] = '132000';           $res[$a]['k'] = '25 Miles';
                                                    $a++;       $res[$a]['v'] = '264000';           $res[$a]['k'] = '50 Miles';
                                                    $a++;       $res[$a]['v'] = '290400';           $res[$a]['k'] = '55 Miles';
                                                    $a++;       $res[$a]['v'] = '396000';           $res[$a]['k'] = '75 Miles';
                                                    $a++;       $res[$a]['v'] = '528000';           $res[$a]['k'] = '100 Miles';
                                                    $a++;       $res[$a]['v'] = '660000';           $res[$a]['k'] = '125 Miles';
                                                    $a++;       $res[$a]['v'] = '792000';           $res[$a]['k'] = '150 Miles';
                                                    $a++;       $res[$a]['v'] = '924000';           $res[$a]['k'] = '175 Miles';
                                                    $a++;       $res[$a]['v'] = '1056000';          $res[$a]['k'] = '200 Miles';
                                                    $a++;       $res[$a]['v'] = '1188000';          $res[$a]['k'] = '225 Miles';
                                                    $a++;       $res[$a]['v'] = '1320000';          $res[$a]['k'] = '250 Miles';
                                                    $a++;       $res[$a]['v'] = '2640000';          $res[$a]['k'] = '500 Miles';
                                                    break;

            case                  'landmarkshape' :
            case                          'shape' : $a='0';     $res[$a]['v'] = 'circle';           $res[$a]['k'] = 'Circle';
                                                    $a++;       $res[$a]['v'] = 'square';           $res[$a]['k'] = 'Square';
                                                    $a++;       $res[$a]['v'] = 'polygon';          $res[$a]['k'] = 'Polygon';
                                                    break;

            case                'landmarktrigger' : $res[0]['v'] = 'Entering';                      $res[0]['k'] = 'On Enter';    // Value, Label
                                                    $res[1]['v'] = 'Exiting';                       $res[1]['k'] = 'On Exit';
                                                    $res[2]['v'] = 'Both';                          $res[2]['k'] = 'On Enter and Exit';
                                                    break;

            case                        'metrics' : 
                                                    // $sql = "SELECT COUNT(DISTINCT uas.landmark_id) as landmark,
                                                    //         COUNT(DISTINCT uas.movingevent_id) as moving,
                                                    //         COUNT(DISTINCT uas.nonreportingstatus) as nonreporting,
                                                    //         COUNT(u.unitstatus_id) as installed
                                                    //         FROM crossbones.unit u
                                                    //         LEFT JOIN crossbones.unitalertstatus uas ON uas.unit_id = u.unit_id
                                                    //         WHERE u.account_id = ?
                                                    //         AND ( u.unitstatus_id = 1 OR u.unitstatus_id = 3 )";
                                                    // $res = $this->db_read->fetchAll($sql, array($user['account_id']));
                                                    
                                                    $buf = array($user['account_id'], $user['user_id']);
                                                    $sql = "SELECT roles FROM crossbones.user WHERE account_id = ? AND user_id = ? LIMIT 1";
                                                    $bux = $this->db_read->fetchAll($sql, $buf);
                                                    switch($bux[0]['roles']){
                                                        case 'ROLE_ACCOUNT_OWNER' : $role_account_owner = 1;
                                                                                    break;
                                                    }

                                                    // $permission = $this->ajaxPermissionCheck($user,'vehicles');
                                                    if(($permission)||($role_account_owner)){

                                                        $sql = "SELECT COUNT(unitstatus_id) as installed
                                                                FROM crossbones.unit
                                                                WHERE account_id = ?
                                                                AND unitstatus_id = ?";
                                                        $buf = $this->db_read->fetchAll($sql, array($user['account_id'],'1'));
                                                        $res[0]['installed'] = $buf[0]['installed'] ; 
                                                        // $res[0]['installed'] = 'x' ; 

                                                        $sql = "SELECT COUNT(unitstatus_id) as inventory
                                                                FROM crossbones.unit
                                                                WHERE account_id = ?
                                                                AND unitstatus_id = ?";
                                                        $buf = $this->db_read->fetchAll($sql, array($user['account_id'],'2'));
                                                        $res[0]['inventory'] = $buf[0]['inventory'] ; 
                                                        // $res[0]['inventory'] = 'x' ; 

                                                        $sql = "SELECT COUNT(u.unit_id) as landmark
                                                                FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitalertstatus uas ON uas.unit_id = u.unit_id
                                                                WHERE u.account_id = ?
                                                                AND u.unitstatus_id = ?
                                                                AND uas.landmark_id != ?";
                                                        $buf = $this->db_read->fetchAll($sql, array($user['account_id'],'1','0'));
                                                        $res[0]['landmark'] = $buf[0]['landmark'] ; 
                                                        // $res[0]['landmark'] = 'x' ; 
                                                        
                                                        $sql = "SELECT COUNT(u.unit_id) as starterstatus
                                                                FROM crossbones.unit u
                                                                WHERE u.account_id = ?
                                                                AND u.starterstatus = ?";
                                                        $buf = $this->db_read->fetchAll($sql, array($user['account_id'],'Disabled'));
                                                        $res[0]['starterstatus'] = $buf[0]['starterstatus'] ; 
                                                        // $res[0]['starterstatus'] = '99' ; 
                                                    
                                                        $sql = "SELECT COUNT(unit_id) as reminderstatus
                                                                FROM crossbones.unit
                                                                WHERE account_id = ?
                                                                AND reminderstatus = ?";
                                                        $buf = $this->db_read->fetchAll($sql, array($user['account_id'],'On'));
                                                        $res[0]['reminderstatus'] = $buf[0]['reminderstatus'] ; 
                                                        // $res[0]['reminderstatus'] = 'x' ; 

                                                        $sql = "SELECT COUNT(unitstatus_id) as repossession
                                                                FROM crossbones.unit
                                                                WHERE account_id = ?
                                                                AND unitstatus_id = ?";
                                                        $buf = $this->db_read->fetchAll($sql, array($user['account_id'],'3'));
                                                        $res[0]['repossession'] = $buf[0]['repossession'] ; 
                                                        // $res[0]['repossession'] = 'x' ; 
                                                        
                                                        $sql = "SELECT *
                                                                FROM crossbones.unit
                                                                WHERE account_id = ?
                                                                ORDER BY unit_id DESC";
                                                        // $realTimeData = $this->db_read->fetchAll($sql, array($user['account_id']));

                                                    } else {

                                                        $sql = "SELECT COUNT(u.unitstatus_id) as installed
                                                                FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitgroup ug ON ug.unitgroup_id = u.unitgroup_id
                                                                LEFT JOIN crossbones.user_unitgroup utg ON utg.unitgroup_id = ug.unitgroup_id
                                                                WHERE u.account_id = ?
                                                                AND utg.user_id = ?
                                                                AND u.unitstatus_id = ?";
                                                        $buf = $this->db_read->fetchAll($sql, array($user['account_id'],$user['user_id'],'1'));
                                                        $res[0]['installed'] = $buf[0]['installed'] ; 
                                                        // $res[0]['installed'] = 'x' ; 

                                                        $sql = "SELECT COUNT(u.unitstatus_id) as inventory
                                                                FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitgroup ug ON ug.unitgroup_id = u.unitgroup_id
                                                                LEFT JOIN crossbones.user_unitgroup utg ON utg.unitgroup_id = ug.unitgroup_id
                                                                WHERE u.account_id = ?
                                                                AND utg.user_id = ?
                                                                AND u.unitstatus_id = ?";
                                                        $buf = $this->db_read->fetchAll($sql, array($user['account_id'],$user['user_id'],'2'));
                                                        $res[0]['inventory'] = $buf[0]['inventory'] ; 
                                                        // $res[0]['inventory'] = 'x' ; 

                                                        $sql = "SELECT COUNT(u.unit_id) as landmark
                                                                FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitgroup ug ON ug.unitgroup_id = u.unitgroup_id
                                                                LEFT JOIN crossbones.user_unitgroup utg ON utg.unitgroup_id = ug.unitgroup_id
                                                                LEFT JOIN crossbones.unitalertstatus uas ON uas.unit_id = u.unit_id
                                                                WHERE u.account_id = ?
                                                                AND utg.user_id = ?
                                                                AND u.unitstatus_id = ?
                                                                AND uas.landmark_id != ?";
                                                        $buf = $this->db_read->fetchAll($sql, array($user['account_id'],$user['user_id'],'1','0'));
                                                        $res[0]['landmark'] = $buf[0]['landmark'] ; 
                                                        // $res[0]['landmark'] = 'x' ; 
                                                    
                                                        $sql = "SELECT COUNT(u.unit_id) as starterstatus
                                                                FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitgroup ug ON ug.unitgroup_id = u.unitgroup_id
                                                                LEFT JOIN crossbones.user_unitgroup utg ON utg.unitgroup_id = ug.unitgroup_id
                                                                WHERE u.account_id = ?
                                                                AND utg.user_id = ?
                                                                AND u.starterstatus = ?";
                                                        $buf = $this->db_read->fetchAll($sql, array($user['account_id'],$user['user_id'],'Disabled'));
                                                        $res[0]['starterstatus'] = $buf[0]['starterstatus'] ; 
                                                        // $res[0]['starterstatus'] = '99' ; 
                                                                                                                                                            
                                                        $sql = "SELECT COUNT(u.unit_id) as reminderstatus
                                                                FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitgroup ug ON ug.unitgroup_id = u.unitgroup_id
                                                                LEFT JOIN crossbones.user_unitgroup utg ON utg.unitgroup_id = ug.unitgroup_id
                                                                WHERE u.account_id = ?
                                                                AND utg.user_id = ?
                                                                AND u.reminderstatus = ?";
                                                        $buf = $this->db_read->fetchAll($sql, array($user['account_id'],$user['user_id'],'On'));
                                                        $res[0]['reminderstatus'] = $buf[0]['reminderstatus'] ; 
                                                        // $res[0]['reminderstatus'] = 'x' ; 
                                                        
                                                        $sql = "SELECT COUNT(u.unitstatus_id) as repossession
                                                                FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitgroup ug ON ug.unitgroup_id = u.unitgroup_id
                                                                LEFT JOIN crossbones.user_unitgroup utg ON utg.unitgroup_id = ug.unitgroup_id
                                                                WHERE u.account_id = ?
                                                                AND utg.user_id = ?
                                                                AND u.unitstatus_id = ?";
                                                        $buf = $this->db_read->fetchAll($sql, array($user['account_id'],$user['user_id'],'3'));
                                                        $res[0]['repossession'] = $buf[0]['repossession'] ; 
                                                        // $res[0]['repossession'] = 'x' ; 

                                                        $sql = "SELECT u.*
                                                                FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitgroup ug ON ug.unitgroup_id = u.unitgroup_id
                                                                LEFT JOIN crossbones.user_unitgroup utg ON utg.unitgroup_id = ug.unitgroup_id
                                                                WHERE u.account_id = ?
                                                                AND utg.user_id = ?
                                                                ORDER BY u.unit_id DESC";
                                                        // $realTimeData = $this->db_read->fetchAll($sql, array($user['account_id'],$user['user_id']));

                                                    }

                                                    $sevenDaysAgo = date('d-m-Y', strtotime("-7 days")) ;

                                                    foreach ($realTimeData as $key => $row) {
                                                        if(($row['db'])&&($row['unit_id'])&&($row['unitstatus_id']!=2)&&( ($row['lastmove']==null) || (strtotime($row['lastmove'])<=strtotime($sevenDaysAgo)) || (strtotime($row['lastmovecheck'])<=strtotime($sevenDaysAgo)) ) ){
                                                            $sql = "SELECT ueu.servertime as servertime
                                                                    FROM " . $row['db'] . ".unit" . $row['unit_id'] . " ueu 
                                                                    WHERE (
                                                                            ueu.event_id = 1
                                                                         OR ueu.event_id = 2
                                                                         OR ueu.event_id = 3
                                                                         OR ueu.event_id = 4
                                                                         OR ueu.event_id = 5
                                                                         OR ueu.event_id = 11
                                                                         OR ueu.event_id = 12
                                                                         OR ueu.event_id = 13
                                                                         OR ueu.event_id = 40
                                                                         OR ueu.event_id = 41
                                                                         OR ueu.event_id = 42
                                                                         OR ueu.event_id = 43
                                                                         OR ueu.event_id = 44
                                                                         OR ueu.event_id = 47
                                                                         OR ueu.event_id = 48
                                                                         OR ueu.event_id = 49
                                                                         OR ueu.event_id = 50
                                                                         OR ueu.event_id = 111
                                                                         OR ueu.event_id = 112
                                                                        ) 
                                                                    AND ueu.servertime > DATE_SUB(NOW(), INTERVAL 7 DAY) 
                                                                    ORDER BY ueu.id DESC LIMIT 1";
                                                            $test = $this->db_read->fetchAll($sql, array('1'));
                                                            if(!($test)){
                                                                $test[0]['servertime'] = date('d-m-Y', strtotime("-8 days")) ;
                                                                $res[0]['movement']++ ; // = $row['unit_id'] . '#' . $result[0]['servertime'] . '#' . $date . '#';
                                                            }
                                                            $sql = "UPDATE crossbones.unit
                                                                    SET lastmove = ? ,
                                                                    lastmovecheck = now()
                                                                    WHERE account_id = ?
                                                                    AND  unit_id = ?";
                                                            $result = $this->db_write->executeQuery($sql, array($test[0]['servertime'],$user['account_id'],$row['unit_id']));
                                                        }
                                                    }
                                                    
                                                    foreach ($realTimeData as $key => $row) {
                                                        if(($row['db'])&&($row['unit_id'])&&($row['unitstatus_id']!=2)&&( ($row['lastreport']==null) || (strtotime($row['lastreport'])<=strtotime($sevenDaysAgo)) || (strtotime($row['lastreportcheck'])<=strtotime($sevenDaysAgo)) ) ){
                                                            $sql = "SELECT ueu.servertime as servertime
                                                                    FROM " . $row['db'] . ".unit" . $row['unit_id'] . " ueu 
                                                                    WHERE ueu.servertime > DATE_SUB(NOW(), INTERVAL 7 DAY) 
                                                                    ORDER BY ueu.id DESC LIMIT 1";
                                                            $test = $this->db_read->fetchAll($sql, array('1'));
                                                            if(!($test)){
                                                                $res[0]['nonreporting']++ ;
                                                                $test[0]['servertime'] = $sevenDaysAgo; 
                                                            }
                                                            $sql = "UPDATE crossbones.unit
                                                                    SET lastreport = ? ,
                                                                    lastreportcheck = now()
                                                                    WHERE account_id = ?
                                                                    AND  unit_id = ?";
                                                            $result = $this->db_write->executeQuery($sql, array($test[0]['servertime'],$user['account_id'],$row['unit_id']));                                                            
                                                        }
                                                    }
                                                    
                                                    break;

            case                      'overspeed' : $a='0';     $res[$a]['v'] = '25';               $res[$a]['k'] = '25 MPH';
                                                    $a++;       $res[$a]['v'] = '26';               $res[$a]['k'] = '26 MPH';
                                                    $a++;       $res[$a]['v'] = '27';               $res[$a]['k'] = '27 MPH';
                                                    $a++;       $res[$a]['v'] = '28';               $res[$a]['k'] = '28 MPH';
                                                    $a++;       $res[$a]['v'] = '29';               $res[$a]['k'] = '29 MPH';
                                                    $a++;       $res[$a]['v'] = '30';               $res[$a]['k'] = '30 MPH';
                                                    $a++;       $res[$a]['v'] = '31';               $res[$a]['k'] = '31 MPH';
                                                    $a++;       $res[$a]['v'] = '32';               $res[$a]['k'] = '32 MPH';
                                                    $a++;       $res[$a]['v'] = '33';               $res[$a]['k'] = '33 MPH';
                                                    $a++;       $res[$a]['v'] = '34';               $res[$a]['k'] = '34 MPH';
                                                    $a++;       $res[$a]['v'] = '35';               $res[$a]['k'] = '35 MPH';
                                                    $a++;       $res[$a]['v'] = '36';               $res[$a]['k'] = '36 MPH';
                                                    $a++;       $res[$a]['v'] = '37';               $res[$a]['k'] = '37 MPH';
                                                    $a++;       $res[$a]['v'] = '38';               $res[$a]['k'] = '38 MPH';
                                                    $a++;       $res[$a]['v'] = '39';               $res[$a]['k'] = '39 MPH';
                                                    $a++;       $res[$a]['v'] = '40';               $res[$a]['k'] = '40 MPH';
                                                    $a++;       $res[$a]['v'] = '41';               $res[$a]['k'] = '41 MPH';
                                                    $a++;       $res[$a]['v'] = '42';               $res[$a]['k'] = '42 MPH';
                                                    $a++;       $res[$a]['v'] = '43';               $res[$a]['k'] = '43 MPH';
                                                    $a++;       $res[$a]['v'] = '44';               $res[$a]['k'] = '44 MPH';
                                                    $a++;       $res[$a]['v'] = '45';               $res[$a]['k'] = '45 MPH';
                                                    $a++;       $res[$a]['v'] = '46';               $res[$a]['k'] = '46 MPH';
                                                    $a++;       $res[$a]['v'] = '47';               $res[$a]['k'] = '47 MPH';
                                                    $a++;       $res[$a]['v'] = '48';               $res[$a]['k'] = '48 MPH';
                                                    $a++;       $res[$a]['v'] = '49';               $res[$a]['k'] = '49 MPH';
                                                    $a++;       $res[$a]['v'] = '50';               $res[$a]['k'] = '50 MPH';
                                                    $a++;       $res[$a]['v'] = '51';               $res[$a]['k'] = '51 MPH';
                                                    $a++;       $res[$a]['v'] = '52';               $res[$a]['k'] = '52 MPH';
                                                    $a++;       $res[$a]['v'] = '53';               $res[$a]['k'] = '53 MPH';
                                                    $a++;       $res[$a]['v'] = '54';               $res[$a]['k'] = '54 MPH';
                                                    $a++;       $res[$a]['v'] = '55';               $res[$a]['k'] = '55 MPH';
                                                    $a++;       $res[$a]['v'] = '56';               $res[$a]['k'] = '56 MPH';
                                                    $a++;       $res[$a]['v'] = '57';               $res[$a]['k'] = '57 MPH';
                                                    $a++;       $res[$a]['v'] = '58';               $res[$a]['k'] = '58 MPH';
                                                    $a++;       $res[$a]['v'] = '59';               $res[$a]['k'] = '59 MPH';
                                                    $a++;       $res[$a]['v'] = '60';               $res[$a]['k'] = '60 MPH';
                                                    $a++;       $res[$a]['v'] = '61';               $res[$a]['k'] = '61 MPH';
                                                    $a++;       $res[$a]['v'] = '62';               $res[$a]['k'] = '62 MPH';
                                                    $a++;       $res[$a]['v'] = '63';               $res[$a]['k'] = '63 MPH';
                                                    $a++;       $res[$a]['v'] = '64';               $res[$a]['k'] = '64 MPH';
                                                    $a++;       $res[$a]['v'] = '65';               $res[$a]['k'] = '65 MPH';
                                                    $a++;       $res[$a]['v'] = '66';               $res[$a]['k'] = '66 MPH';
                                                    $a++;       $res[$a]['v'] = '67';               $res[$a]['k'] = '67 MPH';
                                                    $a++;       $res[$a]['v'] = '68';               $res[$a]['k'] = '68 MPH';
                                                    $a++;       $res[$a]['v'] = '69';               $res[$a]['k'] = '69 MPH';
                                                    $a++;       $res[$a]['v'] = '70';               $res[$a]['k'] = '70 MPH';
                                                    $a++;       $res[$a]['v'] = '71';               $res[$a]['k'] = '71 MPH';
                                                    $a++;       $res[$a]['v'] = '72';               $res[$a]['k'] = '72 MPH';
                                                    $a++;       $res[$a]['v'] = '73';               $res[$a]['k'] = '73 MPH';
                                                    $a++;       $res[$a]['v'] = '74';               $res[$a]['k'] = '74 MPH';
                                                    $a++;       $res[$a]['v'] = '75';               $res[$a]['k'] = '75 MPH';
                                                    $a++;       $res[$a]['v'] = '76';               $res[$a]['k'] = '76 MPH';
                                                    $a++;       $res[$a]['v'] = '77';               $res[$a]['k'] = '77 MPH';
                                                    $a++;       $res[$a]['v'] = '78';               $res[$a]['k'] = '78 MPH';
                                                    $a++;       $res[$a]['v'] = '79';               $res[$a]['k'] = '79 MPH';
                                                    $a++;       $res[$a]['v'] = '80';               $res[$a]['k'] = '80 MPH';
                                                    $a++;       $res[$a]['v'] = '81';               $res[$a]['k'] = '81 MPH';
                                                    $a++;       $res[$a]['v'] = '82';               $res[$a]['k'] = '82 MPH';
                                                    $a++;       $res[$a]['v'] = '83';               $res[$a]['k'] = '83 MPH';
                                                    $a++;       $res[$a]['v'] = '84';               $res[$a]['k'] = '84 MPH';
                                                    $a++;       $res[$a]['v'] = '85';               $res[$a]['k'] = '85 MPH';
                                                    $a++;       $res[$a]['v'] = '86';               $res[$a]['k'] = '86 MPH';
                                                    $a++;       $res[$a]['v'] = '87';               $res[$a]['k'] = '87 MPH';
                                                    $a++;       $res[$a]['v'] = '88';               $res[$a]['k'] = '88 MPH';
                                                    $a++;       $res[$a]['v'] = '89';               $res[$a]['k'] = '89 MPH';
                                                    $a++;       $res[$a]['v'] = '90';               $res[$a]['k'] = '90 MPH';
                                                    $a++;       $res[$a]['v'] = '91';               $res[$a]['k'] = '91 MPH';
                                                    $a++;       $res[$a]['v'] = '92';               $res[$a]['k'] = '92 MPH';
                                                    $a++;       $res[$a]['v'] = '93';               $res[$a]['k'] = '93 MPH';
                                                    $a++;       $res[$a]['v'] = '94';               $res[$a]['k'] = '94 MPH';
                                                    $a++;       $res[$a]['v'] = '95';               $res[$a]['k'] = '95 MPH';
                                                    $a++;       $res[$a]['v'] = '96';               $res[$a]['k'] = '96 MPH';
                                                    $a++;       $res[$a]['v'] = '97';               $res[$a]['k'] = '97 MPH';
                                                    $a++;       $res[$a]['v'] = '98';               $res[$a]['k'] = '98 MPH';
                                                    $a++;       $res[$a]['v'] = '99';               $res[$a]['k'] = '99 MPH';
                                                    $a++;       $res[$a]['v'] = '100';              $res[$a]['k'] = '100 MPH';
                                                    break;

            case             'permissioncategory' : $sql = "SELECT permissioncategory_id as v, permissioncategoryname as k
                                                            FROM crossbones.permissioncategory
                                                            WHERE sortorder > 0
                                                            ORDER BY sortorder ASC";
                                                    $res = $this->db_read->fetchAll($sql, array($user['account_id']));
                                                    break;

            case                          'range' : $a='0';     $res[$a]['v'] = '6';                $res[$a]['k'] = '6a';
                                                    $a++;       $res[$a]['v'] = '7';                $res[$a]['k'] = '7a';
                                                    $a++;       $res[$a]['v'] = '8';                $res[$a]['k'] = '8a';
                                                    $a++;       $res[$a]['v'] = '9';                $res[$a]['k'] = '9a';
                                                    $a++;       $res[$a]['v'] = '10';               $res[$a]['k'] = '10a';
                                                    $a++;       $res[$a]['v'] = '11';               $res[$a]['k'] = '11a';
                                                    $a++;       $res[$a]['v'] = '12';               $res[$a]['k'] = '12p';
                                                    $a++;       $res[$a]['v'] = '13';               $res[$a]['k'] = '1p';
                                                    $a++;       $res[$a]['v'] = '14';               $res[$a]['k'] = '2p';
                                                    $a++;       $res[$a]['v'] = '15';               $res[$a]['k'] = '3p';
                                                    $a++;       $res[$a]['v'] = '16';               $res[$a]['k'] = '4p';
                                                    $a++;       $res[$a]['v'] = '17';               $res[$a]['k'] = '5p';
                                                    $a++;       $res[$a]['v'] = '18';               $res[$a]['k'] = '6p';
                                                    $a++;       $res[$a]['v'] = '19';               $res[$a]['k'] = '7p';
                                                    $a++;       $res[$a]['v'] = '20';               $res[$a]['k'] = '8p';
                                                    $a++;       $res[$a]['v'] = '21';               $res[$a]['k'] = '9p';
                                                    $a++;       $res[$a]['v'] = '22';               $res[$a]['k'] = '10p';
                                                    $a++;       $res[$a]['v'] = '23';               $res[$a]['k'] = '11p';
                                                    $a++;       $res[$a]['v'] = '0';                $res[$a]['k'] = '12a';
                                                    $a++;       $res[$a]['v'] = '1';                $res[$a]['k'] = '1a';
                                                    $a++;       $res[$a]['v'] = '2';                $res[$a]['k'] = '2a';
                                                    $a++;       $res[$a]['v'] = '3';                $res[$a]['k'] = '3a';
                                                    $a++;       $res[$a]['v'] = '4';                $res[$a]['k'] = '4a';
                                                    $a++;       $res[$a]['v'] = '5';                $res[$a]['k'] = '5a';
                                                    break;

            case                          'state' : $sql = "SELECT state_id as v, state as k
                                                            FROM crossbones.states
                                                            ORDER BY state ASC";
                                                    $res = $this->db_read->fetchAll($sql);
                                                    break;

            case                       'usertype' : 
            case              'user-type-options' : 
            case             'user-add-user-type' : 
            case          'user-update-user-type' : $sql = "SELECT canned, usertype_id as v, usertype as k
                                                            FROM crossbones.usertype
                                                            WHERE account_id = ?
                                                            AND active = '1'
                                                            ORDER BY canned DESC, usertype ASC";
                                                    $res = $this->db_read->fetchAll($sql, array($user['account_id']));
                                                    break;

            case                        'carrier' :
            case                    'cellcarrier' :
            case               'user-add-carrier' :
            case            'user-update-carrier' : $sql = "SELECT cellcarrier_id as v, cellcarrier as k
                                                            FROM crossbones.cellcarrier
                                                            WHERE cellcarrier_id IS NOT NULL AND cellcarrier_id != '' AND cellcarrier IS NOT NULL AND cellcarrier != ''
                                                            ORDER BY cellcarrier ASC";
                                                    $res = $this->db_read->fetchAll($sql, array($user['account_id']));
                                                    break;

            case                      'unitgroup' : $res[0] = array(
                                                        'k' => $unit_id,
                                                        'v' => $unit_id
                                                    );
                                                    if($unit_id!='group'){
                                                        $sql = "SELECT ug.unitgroup_id AS v, 
                                                                ug.unitgroupname AS k
                                                                FROM crossbones.unitgroup ug
                                                                LEFT JOIN crossbones.unit u ON u.account_id = ug.account_id
                                                                WHERE u.account_id = ? and ug.active = '1'
                                                                ORDER BY k ASC";
                                                        $res = $this->db_read->fetchAll($sql, array($user['account_id']));
                                                    } else {
                                                        $sql = "SELECT ug.unitgroup_id AS v, ug.unitgroupname AS k FROM crossbones.unitgroup ug
                                                                LEFT JOIN crossbones.unit u ON u.account_id = ug.account_id
                                                                WHERE u.account_id = ?
                                                                ORDER BY k ASC";
                                                        $res = $this->db_read->fetchAll($sql, array($user['account_id']));
                                                    }
                                                    break;

            case                     'unitstatus' : $sql = "SELECT us.unitstatus_id AS v, us.unitstatusname AS k FROM crossbones.unitstatus us
                                                            WHERE active > 0
                                                            ORDER BY unitstatusname ASC";
                                                    $res = $this->db_read->fetchAll($sql, array());
                                                    break;

            case       'secondary-sidebar-scroll' : if($unit_id){
                                                        $sql = "SELECT u.unit_id AS v, u.unitname AS k FROM crossbones.unit u
                                                                WHERE u.account_id = ?
                                                                AND u.unitname LIKE ?
                                                                ORDER BY unitname ASC";
                                                        $res = $this->db_read->fetchAll($sql, array($user['account_id'],$unit_id.'%'));
                                                    } else {
                                                        $sql = "SELECT u.unit_id AS v, u.unitname AS k FROM crossbones.unit u
                                                                WHERE u.account_id = ?
                                                                ORDER BY unitname ASC";
                                                        $res = $this->db_read->fetchAll($sql, array($user['account_id']));
                                                    }
                                                    break;

            case                        'unit_id' :
            case                        'vehicle' : $sql = "SELECT unit_id as v, unitname as k
                                                            FROM crossbones.unit
                                                            WHERE account_id = ?
                                                            ORDER BY unitname ASC";
                                                    $res = $this->db_read->fetchAll($sql, array($user['account_id']));
                                                    break;

            case                   'vehiclegroup' : $sql = "SELECT unitgroup_id as v, unitgroupname as k
                                                            FROM crossbones.unitgroup
                                                            WHERE account_id = ?
                                                            ORDER BY unitgroupname ASC";
                                                    $res = $this->db_read->fetchAll($sql, array($user['account_id']));
                                                    break;

            case                    'vehiclemode' : $res[0]['v'] = '1';                         $res[0]['k'] = 'Single Vehicle';    // Value, Label
                                                    $res[1]['v'] = '2';                         $res[1]['k'] = 'Vehicle Group';
                                                    $res[2]['v'] = '3';                         $res[2]['k'] = 'All Vehicles';
                                                    break;

                                          default : $res[0]['v'] = 'ajaxOptions' ; // Value
                                                    $res[0]['k'] = 'ajaxOptions' ; // Label

        }

        if(!($res)){
            $res[0]['v'] = '';
            $res[0]['k'] = '' ;
            // $res[0]['v'] = $sql;
            // $res[0]['k'] = $sql . ':' . $user['account_id'] . ':' . $unit_id ;
        } else {
            foreach ( $res as $k => $v ) {
                if(!($v['k'])){
                    $res[$k]['k'] = ucwords($element) . ' ' . $v['v'];
                }
            }
        }
        return $res ;
    }

    /**
     * Support Ajax Requests for Permission Updates
     */
    public function ajaxPermissionCheck($user,$element,$context) {

        $sql = "SELECT p.label,
                p.object,
                p.action,
                u.roles
                FROM crossbones.user u 
                LEFT JOIN crossbones.usertype ut ON ut.usertype_id = u.usertype_id
                LEFT JOIN crossbones.usertype_permission utp ON utp.usertype_id = u.usertype_id
                LEFT JOIN crossbones.permission p ON p.permission_id = utp.permission_id
                WHERE ( ut.canned = ? OR ut.account_id = ? OR ( u.account_id = ? AND u.roles = ? ) )
                AND u.user_id = ?
                AND ut.active = ? 
                ORDER BY p.sortorder, p.object, p.label";
        $res = $this->db_read->fetchAll($sql, array(1,$user['account_id'],$user['account_id'],'ROLE_ACCOUNT_OWNER',$user['user_id'],1));

        foreach ( $res as $key => $val ) {
            $access[$val['object']][$val['action']] = 1;
            if($val['action']=='write'){
                $access[$val['object']]['read'] = 1;                
            }
        }

        switch($element){

            case                     'alert-edit-days' :
            case                  'alert-edit-contact' :
            case             'alert-edit-contactgroup' :
            case            'alert-edit-contactmethod' :
            case              'alert-edit-contactmode' :
            case                 'alert-edit-duration' :
            case                  'alert-edit-endhour' :
            case                    'alert-edit-hours' :
            case                 'alert-edit-landmark' :
            case            'alert-edit-landmarkgroup' :
            case             'alert-edit-landmarkmode' :
            case          'alert-edit-landmarktrigger' :
            case                     'alert-edit-name' :
            case                'alert-edit-overspeed' :
            case                'alert-edit-starthour' :
            case                     'alert-edit-type' :
            case                  'alert-edit-vehicle' :
            case             'alert-edit-vehiclegroup' :
            case              'alert-edit-vehiclemode' :    $permission = $access['alert']['write'] ;
                                                            break;

            case                    'customer-address' :
            case                       'customer-city' :
            case                      'customer-email' :
            case                 'customer-first-name' :
            case                 'customer-home-phone' :
            case                  'customer-last-name' :
            case               'customer-mobile-phone' :
            case                      'customer-state' :
            case                    'customer-zipcode' :    $permission = $access['vehicle_customer']['write'] ;
                                                            break;
           case 'edit-contact-group-contacts-assigned' :
            case       'edit-landmark-groups-assigned' :
            case      'edit-landmark-group-title-edit' :    $permission = $access['landmark_group']['write'] ;
                                                            break;

            case          'user-edit-devices-assigned' :
            case         'user-edit-devices-available' :
            case 'edit-vehicle-group-devices-assigned' :
           case 'edit-vehicle-group-devices-available' :
            case       'edit-vehicle-group-title-edit' :
           case        'edit-vehicle-group-user-types' :    $permission = $access['vehicle_group']['write'] ;
                                                            break;

            case                 'user-type-edit-name' :    $permission = $access['usertype']['write'] ;
                                                            break;

            case                           'landmarks' :    $permission = $access['landmark']['write'] ;
                                                            if(!($permission)){
                                                                $permission = $access['landmark']['access'] ;
                                                            }
                                                            break;

            case                      'landmarkgroups' :    $permission = $access['landmark_group']['write'] ;
                                                            if(!($permission)){
                                                                $permission = $access['landmark_group']['access'] ;
                                                            }
                                                            break;

            case                   'landmark-category' :
            case                       'landmark-city' :
            case                      'landmark-click' :
            case                      'landmark-group' :
            case                     'landmark-radius' :
            case                    'landmark-polygon' :
            case                      'landmark-shape' :
            case                      'landmark-state' :
            case             'landmark-street-address' :
            case                   'landmark-latitude' :
            case                  'landmark-longitude' :
            case                       'landmark-name' :
            case                    'landmark-zipcode' :    $permission = $access['landmark']['write'] ;
                                                            break;
    
            case                    'my-account-email' :
            case               'my-account-first-name' :    
            case                'my-account-last-name' :    $permission = $access['landmark']['write'] ;
                                                            break;


            case               'user-edit-cellcarrier' :
            case                'user-edit-cellnumber' :
            case                     'user-edit-email' :
            case                 'user-edit-firstname' :
            case   'user-edit-landmarkgroups-assigned' :
            case                  'user-edit-lastname' :
            case                  'user-edit-usertype' :
            case    'user-edit-vehiclegroups-assigned' :    $permission = $access['user']['write'] ;
                                                            break;

            case                'edit-contact-carrier' :
            case             'edit-contact-cellnumber' :
            case                  'edit-contact-email' :
            case             'edit-contact-first-name' :
            case              'edit-contact-last-name' :    switch($context){
                                                            
                                                                case      'alert/contact' : 
                                                                case     'report/contact' : 
                                                                                  default : $permission = $access['user']['write'] ;
                                                                                            break;
                                                            }
                                                            break;

            case            'verification-address-add' :    $permission = $access['vehicle_reference_address']['write'] ;
                                                            break;

            case                            'vehicles' :    $permission = $access['vehicle']['write'] ;
                                                            if(!($permission)){
                                                                $permission = $access['vehicle']['access'] ;
                                                            }
                                                            break;

            case                       'vehiclegroups' :    $permission = $access['vehicle_group']['write'] ;
                                                            if(!($permission)){
                                                                $permission = $access['vehicle_group']['access'] ;
                                                            }
                                                            break;

            case                       'vehicle-color' :
            case                       'vehicle-group' :
            case                'vehicle-install-date' :
            case             'vehicle-install-mileage' :
            case                   'vehicle-installer' :
            case               'vehicle-license-plate' :
            case                     'vehicle-loan-id' :
            case                        'vehicle-make' :
            case                       'vehicle-model' :
            case                        'vehicle-name' :
            case                      'vehicle-status' :
            case                       'vehicle-stock' :
            case                         'vehicle-vin' :
            case                        'vehicle-year' :    $permission = $access['vehicle']['write'] ;
                                                            break;

        }

        return $permission;

    }

    /**
     * Support Ajax Requests for Permission Updates
     */
    public function ajaxPermissions($user,$usertype_id,$permission_id,$checked,$post) {

        $sql = "SELECT * FROM crossbones.usertype_permission
                WHERE usertype_id = ?
                AND permission_id = ?";
        $result = $this->db_read->fetchAll($sql, array($usertype_id,$permission_id));

        $sql = NULL ;

        if(($checked==='true')&&(!($result))){
            $attempt = 'insert';
            $params = array(
                'usertype_id' => $usertype_id,
                'permission_id' => $permission_id
            );
           if ($this->db_write->insert('crossbones.usertype_permission', $params)) {
               $result = $this->db_write->lastInsertId();
           }
        } else if(($checked==='false')&&($result)){
            $attempt = 'delete';
            $sql = "DELETE FROM crossbones.usertype_permission WHERE usertype_id = ? AND permission_id = ?";
            $result = $this->db_write->executeQuery($sql, array($usertype_id,$permission_id));            
        }

        $sql = "SELECT * FROM crossbones.usertype_permission
                WHERE usertype_id = ?
                AND permission_id = ?";
        $result = $this->db_read->fetchAll($sql, array($usertype_id,$permission_id));

        //$result['alert'] = 'attempt="' . $attempt . '", checked="' . $checked . '", usertype="' . $usertype_id . '", permission="' . $permission_id . '", result="' . $result[0]['permission_id'] . '"';

        return $result;
                                            
    }

    /**
     * Support Ajax Schedule Report Requests
     */
    public function ajaxRepo($user,$unit_id,$repo,$post)
    {

        $url = $unit_id . 'X' . date('U') ;

        $sql = "INSERT INTO crossbones.repo ( account_id , unit_id , email , phone , name , url , active ) VALUES ( ? , ? , ? , ? , ? , ? , ? )";
        if ($this->db_write->executeQuery($sql, array($user['account_id'],$unit_id,$repo['email'],$repo['phone'],$repo['name'],$url,0))) {

            $transport = \Swift_SmtpTransport::newInstance(EMAIL_HOST, EMAIL_PORT, EMAIL_SECURITY);
            $transport->setUsername(EMAIL_USERNAME);
            $transport->setPassword(EMAIL_PASSWORD);

            $body = '
                <html>
                    <body>http://' . $_SERVER['SERVER_NAME'] . '/repo/'.$url.'</body>
                </html>';
            $body_text = 'http://' . $_SERVER['SERVER_NAME'] . '/repo/'.$url;

            // Create the message
            $message = \Swift_Message::newInstance();
            $message->setSubject('Reposession Link:'.date('Y-m-d H:i:s'));
            $message->setBody($body, 'text/html');
            $message->addPart($body_text, 'text/plain');    // Add alternative plain text body
            // $message->setFrom(array('alerts@'.EMAIL_FROM_DOMAIN => EMAIL_FROM_DOMAIN)); // NTD: determine if dealers will have email domains or not
            $message->setFrom(array('alerts@'.$_SERVER['SERVER_NAME'] => $_SERVER['SERVER_NAME'])); // NTD: determine if dealers will have email domains or not

            if(!($repo['name'])){
                $repo['name'] = $repo['email'] ;
            }
            $message->setTo($repo['email'],$repo['name']);
            $message->setBcc('monitor@positionplusgps.com','VehicleData:ajaxRepo'); // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< Developer Email BCC
            // $message->setBcc('tbagley@positionplusgps.com','VehicleData:ajaxRepo'); // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< Developer Email BCC

            // Send the email
            $mailer = \Swift_Mailer::newInstance($transport);
            $mailer->send($message);

            return true;
        }

        return false;

    }

    /**
     * Support Ajax Schedule Report Requests
     */
    public function ajaxScheduleReport($account_id,$user_id,$post)
    {
        $sql = "SELECT * FROM crossbones.schedulereport
                WHERE schedulereport_id = ?
                AND account_id = ?
                AND user_id = ?";
        $result = $this->db_read->fetchAll($sql, array($post['uid'],$account_id,$user_id));
        if($result){
            if(!($post['verification'])){
                $post['verification'] = 'None';
            }
            if(!($post['schedule'])){
                $post['schedule'] = 'Daily';
            }
            if(!($post['scheduleday'])){
                $post['scheduleday'] = 'Monday';
            }
            date_default_timezone_set($timezone);
            $nextruntime = date("Y-m-d {$post['sendhour']}:00:00",strtotime("+1 day"));
            $nextruntime = $this->base_logic->wizardTzAdj('Y-m-d H:i:s',$nextruntime,$timezone);
            $sql = "UPDATE crossbones.schedulereport
                    SET reporttype_id = ?,
                    alerttype_id = ?,
                    schedulereportname = ?,
                    minute = ?,
                    day = ?,
                    mile = ?,
                    mph = ?,
                    verification = ?,
                    schedule = ?,
                    scheduleday = ?,
                    monthday = ?,
                    range = ?,
                    sendhour = ?,
                    format = ?
                    WHERE schedulereport_id = ?
                    AND account_id = ?
                    AND user_id = ?";
            $sql = "UPDATE crossbones.schedulereport
                    SET reporttype_id = ?,
                    alerttype_id = ?,
                    schedulereportname = ?,
                    minute = ?,
                    day = ?,
                    mile = ?,
                    mph = ?,
                    verification = ?,
                    schedule = ?,
                    scheduleday = ?,
                    monthday = ?,
                    sendhour = ?,
                    nextruntime = ?,
                    format = ?
                    WHERE schedulereport_id = ?
                    AND account_id = ?
                    AND user_id = ?";
            $result = $this->db_write->executeQuery($sql, array($post['reporttype'],$post['alerttype'],$post['name'],$post['minute'],$post['day'],$post['mile'],$post['mph'],$post['verification'],$post['schedule'],$post['scheduleday'],$post['monthday'],$post['sendhour'],$nextruntime,$post['format'],$post['uid'],$account_id,$user_id));
// ************************************
// BEGIN - Schedule Reports Range ENUM
// ************************************
            switch($post['range']){
                case  'Yesterday' : $params['`range`'] = '1 day';
                                    break;
              case  'Last 7 Days' : $params['`range`'] = '7 days';
                                    break;
              case 'Last 30 Days' : $params['`range`'] = '30 days';
                                    break;
              case 'Last 60 Days' : $params['`range`'] = '60 days';
                                    break;
              case 'Last 90 Days' : $params['`range`'] = '90 days';
                                    break;
                case 'This Month' : $params['`range`'] = '0 month';
                                    break;
                case 'Last Month' : $params['`range`'] = '1 month';
                                    break;
                          default : $params['`range`'] = '0 day';
            }
            // if ($this->db_write->update('crossbones.schedulereport', $params, array('schedulereport_id' => $post['uid'])) !== false) {
            //     return true;
            // }
// ************************************
// END - Schedule Reports Range ENUM
// ************************************
        }

        $sql = "SELECT src.* FROM crossbones.schedulereport_contact src
                LEFT JOIN crossbones.schedulereport sr ON sr.schedulereport_id = src.schedulereport_id
                    WHERE src.schedulereport_id = ?
                    AND sr.account_id = ?
                    AND sr.user_id = ?";
        $result = $this->db_read->fetchAll($sql, array($post['uid'],$account_id,$user_id));
        switch($post['contactmode']){
            case        'single' :  $post['contactgroup'] = 0 ;
                                    break;            
            case         'group' :  $post['contact'] = 0 ;
                                    break;            
                         default :  $post['contact'] = 0 ;
                                    $post['contactgroup'] = 0 ;
        }
        if($result){
            $sql = "UPDATE crossbones.schedulereport_contact
                    SET contact_id = ?,
                    contactgroup_id = ?
                    WHERE schedulereport_id = ?";
            $result = $this->db_write->executeQuery($sql, array($post['contact'],$post['contactgroup'],$post['uid']));
            // $chk = 'updating ' . $post['uid'] ;
        } else {
            // $chk = 'inserting ' . $post['uid'] ;
            $sql = "INSERT INTO crossbones.schedulereport_contact ( contact_id , contactgroup_id , schedulereport_id ) VALUES ( ? , ? , ? )";
            $result = $this->db_write->executeQuery($sql, array($post['contact'],$post['contactgroup'],$post['uid']));
        }

        $sql = "SELECT srt.* FROM crossbones.schedulereport_territory srt
                LEFT JOIN crossbones.schedulereport sr ON sr.schedulereport_id = srt.schedulereport_id
                    WHERE srt.schedulereport_id = ?
                    AND sr.account_id = ?
                    AND sr.user_id = ?";
        $result = $this->db_read->fetchAll($sql, array($post['uid'],$account_id,$user_id));
        if($result){
            switch($post['territorymode']){
                case        'single' :  $post['territorygroup'] = 0 ;
                                        $post['territorymode'] = 'territory' ;
                                        break;            
                case         'group' :  $post['territory'] = 0 ;
                                        $post['territorymode'] = 'group' ;
                                        break;            
                             default :  $post['territory'] = 0 ;
                                        $post['territorygroup'] = 0 ;
                                        $post['territorymode'] = 'all' ;
            }
            $sql = "UPDATE crossbones.schedulereport_territory
                    SET territory_id = ?,
                    territorygroup_id = ?,
                    selection = ?
                    WHERE schedulereport_id = ?";
            $result = $this->db_write->executeQuery($sql, array($post['territory'],$post['territorygroup'],$post['territorymode'],$post['uid']));
        }

        $sql = "SELECT sru.* FROM crossbones.schedulereport_unit sru
                LEFT JOIN crossbones.schedulereport sr ON sr.schedulereport_id = sru.schedulereport_id
                    WHERE sru.schedulereport_id = ?
                    AND sr.account_id = ?
                    AND sr.user_id = ?";
        $result = $this->db_read->fetchAll($sql, array($post['uid'],$account_id,$user_id));
        if($result){
            switch($post['unitmode']){
                case        'single' :  $post['unitgroup'] = 0 ;
                                        $post['unitmode'] = 'unit' ;
                                        break;            
                case         'group' :  $post['unit'] = 0 ;
                                        $post['unitmode'] = 'group' ;
                                        break;            
                             default :  $post['unit'] = 0 ;
                                        $post['unitgroup'] = 0 ;
                                        $post['unitmode'] = 'all' ;
            }
            $sql = "UPDATE crossbones.schedulereport_unit
                    SET unit_id = ?,
                    unitgroup_id = ?,
                    selection = ?
                    WHERE schedulereport_id = ?";
            $result = $this->db_write->executeQuery($sql, array($post['unit'],$post['unitgroup'],$post['unitmode'],$post['uid']));
        }

        return true ;
    }

    /**
     * Support Ajax Selections Requests
     */
    public function ajaxSelections($account_id,$user_id,$element,$uids,$value)
    {

        $buffer = explode ( '-' , $uids ) ;
        $ugid = $buffer[0];
        $uid = $buffer[1];

        switch ($element) {

            case                       'edit-vehicle-group-users' : $sql = "SELECT * FROM crossbones.user_unitgroup
                                                                            WHERE user_id = ?
                                                                            AND unitgroup_id = ?";
                                                                    $result = $this->db_read->fetchAll($sql, array($uid,$ugid));
                                                                    if((!($result))&&($value=='true')){
                                                                        $results = 'insert';
                                                                        $sql = "INSERT INTO crossbones.user_unitgroup ( user_id , unitgroup_id ) VALUES ( ? , ? )";
                                                                        $result = $this->db_write->executeQuery($sql, array($uid,$ugid));            
                                                                    } else if(($result)&&($value=='false')){
                                                                        $results = 'delete';
                                                                        $sql = "DELETE FROM crossbones.user_unitgroup WHERE user_id = ? AND unitgroup_id = ?";
                                                                        $result = $this->db_write->executeQuery($sql, array($uid,$ugid));            
                                                                    }
                                                                    $sql = "SELECT * FROM crossbones.user_unitgroup
                                                                            WHERE user_id = ?
                                                                            AND unitgroup_id = ?";
                                                                    $result = $this->db_read->fetchAll($sql, array($uid,$ugid));
                                                                    // $results = $result[0];
                                                                    break;
            case                  'edit-vehicle-group-user-types' : $sql = "SELECT * FROM crossbones.usertype_unitgroup
                                                                            WHERE usertype_id = ?
                                                                            AND unitgroup_id = ?";
                                                                    $result = $this->db_read->fetchAll($sql, array($uid,$ugid));
                                                                    if((!($result))&&($value=='true')){
                                                                        $results = 'insert';
                                                                        $sql = "INSERT INTO crossbones.usertype_unitgroup ( usertype_id , unitgroup_id ) VALUES ( ? , ? )";
                                                                        $result = $this->db_write->executeQuery($sql, array($uid,$ugid));            
                                                                    } else if($value=='false'){
                                                                        $results = 'delete';
                                                                        $sql = "DELETE FROM crossbones.usertype_unitgroup WHERE usertype_id = ? AND unitgroup_id = ?";
                                                                        $result = $this->db_write->executeQuery($sql, array($uid,$ugid));            
                                                                    }
                                                                    $sql = "SELECT * FROM crossbones.usertype_unitgroup
                                                                            WHERE usertype_id = ?
                                                                            AND unitgroup_id = ?";
                                                                    $result = $this->db_read->fetchAll($sql, array($uid,$ugid));
                                                                    $results = implode ( ',' , $result[0] );
                                                                    break;

        }

        return $results;

    }

    /**
     * Support Ajax TranserAccept Requests
     */
    public function ajaxTransferAccept($account_id,$units)
    {

        if ( ($account_id) && ($units) ) {

            $sql = "SELECT ug.unitgroup_id
                    FROM crossbones.unitgroup ug 
                    WHERE ug.account_id = ?
                    AND ug.default = ? 
                    AND ug.active = ? 
                    ORDER BY ug.unitgroup_id DESC LIMIT 1" ;
        
            $result = $this->db_read->fetchAll($sql, array($account_id,1,1));

            $unitgroup_id = $result[0]['unitgroup_id'] ;

            if ($unitgroup_id) {

                foreach($units as $key => $export_id){
                    $processCount=1;
                    $sql = "UPDATE crossbones.export SET server = ? WHERE transferee_account_id = ? AND export_id = ?" ;
                    if ($this->db_write->executeQuery($sql, array($processCount,$account_id,$export_id))) {
                    }

                    $attempts++ ;
                
                    $sql = "SELECT * FROM crossbones.export WHERE transferee_account_id = ? AND export_id = ?" ;
                
                    $result = $this->db_read->fetchAll($sql, array($account_id,$export_id));

                    $transferor_account_id = $result[0]['transferor_account_id'] ;
                    $unit_id = $result[0]['unit_id'] ;

                    if ($transferor_account_id) {

                        $processCount++;
                        $sql = "UPDATE crossbones.export SET server = ? WHERE transferee_account_id = ? AND export_id = ?" ;
                        if ($this->db_write->executeQuery($sql, array($processCount,$account_id,$export_id))) {
                        }

                        $sql = "UPDATE crossbones.unit SET account_id = ? , unitgroup_id = ? WHERE account_id = ? AND unit_id = ?" ;

                        if ($this->db_read->executeQuery($sql, array($account_id,$unitgroup_id,$transferor_account_id,$unit_id))) {

                            $processCount++;
                            $sql = "UPDATE crossbones.export SET server = ? WHERE transferee_account_id = ? AND export_id = ?" ;
                            if ($this->db_write->executeQuery($sql, array($processCount,$account_id,$export_id))) {
                            }

                            $transfers++;

                            $sql = "UPDATE unitmanagement.unitlist SET account_id = ? WHERE account_id = ? AND unit_id = ?" ;
                            if ($this->db_read->executeQuery($sql, array($account_id,$transferor_account_id,$unit_id))) {
                            }

                            $processCount++;
                            $sql = "UPDATE crossbones.export SET server = ? WHERE transferee_account_id = ? AND export_id = ?" ;
                            if ($this->db_write->executeQuery($sql, array($processCount,$account_id,$export_id))) {
                            }

                            $sql = "UPDATE crossbones.export SET transfered = now() WHERE transferee_account_id = ? AND export_id = ?" ;
                            if ($this->db_write->executeQuery($sql, array($account_id,$export_id))) {
                                $deletes++;
                            }

                            $processCount++;
                            $sql = "UPDATE crossbones.export SET server = ? WHERE transferee_account_id = ? AND export_id = ?" ;
                            if ($this->db_write->executeQuery($sql, array($processCount,$account_id,$export_id))) {
                            }

                            $sql = "DELETE FROM crossbones.alert_unit WHERE unit_id = ?" ;
                            if ($this->db_read->executeQuery($sql, array($unit_id))) {
                            }

                            $processCount++;
                            $sql = "UPDATE crossbones.export SET server = ? WHERE transferee_account_id = ? AND export_id = ?" ;
                            if ($this->db_write->executeQuery($sql, array($processCount,$account_id,$export_id))) {
                            }

                            $sql = "DELETE FROM crossbones.alerthistory WHERE unit_id = ?" ;
                            if ($this->db_read->executeQuery($sql, array($unit_id))) {
                            }

                            $sql = "DELETE FROM crossbones.reporthistory_unit WHERE unit_id = ?" ;
                            if ($this->db_read->executeQuery($sql, array($unit_id))) {
                            }

                            $processCount++;
                            $sql = "UPDATE crossbones.export SET server = ? WHERE transferee_account_id = ? AND export_id = ?" ;
                            if ($this->db_write->executeQuery($sql, array($processCount,$account_id,$export_id))) {
                            }

                            $sql = "DELETE FROM crossbones.schedulereport_unit WHERE unit_id = ?" ;
                            if ($this->db_read->executeQuery($sql, array($unit_id))) {
                            }

                            $processCount++;
                            $sql = "UPDATE crossbones.export SET server = ? WHERE transferee_account_id = ? AND export_id = ?" ;
                            if ($this->db_write->executeQuery($sql, array($processCount,$account_id,$export_id))) {
                            }

                            $sql = "DELETE FROM crossbones.unit_territory WHERE unit_id = ?" ;
                            if ($this->db_read->executeQuery($sql, array($unit_id))) {
                            }

                            $processCount++;
                            $sql = "UPDATE crossbones.export SET server = ? WHERE transferee_account_id = ? AND export_id = ?" ;
                            if ($this->db_write->executeQuery($sql, array($processCount,$account_id,$export_id))) {
                            }

                            $sql = "DELETE FROM crossbones.unitalertstatus WHERE unit_id = ?" ;
                            if ($this->db_read->executeQuery($sql, array($unit_id))) {
                            }

                        }

                    }

                    $processCount++;
                    $sql = "UPDATE crossbones.export SET server = ? WHERE transferee_account_id = ? AND export_id = ?" ;
                    if ($this->db_write->executeQuery($sql, array($processCount,$account_id,$export_id))) {
                    }

                }

            }

        }

        return 'ajaxTransferAccept: attempts=' . $attempts . ', transfers=' . $transfers . ', deletes=' . $deletes ;       

    }

    /**
     * Support Ajax TranserReject Requests
     */
    public function ajaxTransferReject($account_id,$units)
    {
        
        foreach($units as $key => $unit){
        
            $attempts++ ;
        
            $sql = "UPDATE crossbones.export SET rejected = now() WHERE transferee_account_id = ? AND export_id = ?" ;
        
            if ($this->db_write->executeQuery($sql, array($account_id,$unit))) {
                $deleted++;
            }

        }

        return 'ajaxTransferReject: attempts=' . $attempts . ', deleted=' . $deleted ;       
        
    }

    /**
     * Support Ajax TranserCancel Requests
     */
    public function ajaxTransferCancel($account_id,$unit,$export)
    {

        // if ($this->db_write->delete('crossbones.export', array('account_id' => $account_id, 'unit_id' => $unit, 'export_id' => $export))) {
        //     return true;
        // }
        // return false;

        $sql = "UPDATE crossbones.export SET canceled = now() WHERE transferor_account_id = ? AND unit_id = ? AND export_id = ?" ;
        if ($this->db_write->executeQuery($sql, array($account_id,$unit,$export))) {
            return true;
        }            
        return false;

    }

    /**
     * Support Ajax TranserOffer Requests
     */
    public function ajaxTransferOffer($account_id,$user_id,$routing_number,$units)
    {

        $keys = explode('-',$routing_number);

        foreach($units as $key => $unit){

            $attempts++ ;

            if( ($account_id) && ($user_id) && ($unit) && ($keys[0]) && ($keys[1]) && ($keys[2]) && ($keys[3]) ) {

                $sql = "SELECT a.account_id as transferee_account_id,
                        u.user_id as transferee_user_id
                        FROM crossbones.account a
                        LEFT JOIN crossbones.user u ON u.account_id = a.account_id
                        WHERE a.account_id = ?
                        AND a.createdate = ?
                        AND u.user_id = ?
                        AND u.createdate = ?
                        ORDER BY a.createdate ASC, u.createdate ASC
                        LIMIT 1";

                $params = array($keys[1],date('Y-m-d H:i:s',$keys[0]),$keys[3],date('Y-m-d H:i:s',$keys[2]));

                $result = $this->db_read->fetchAll($sql, $params);

                if ($result) {

                    $validations++ ;

                    $sql = "INSERT INTO crossbones.export (transferee_account_id,transferee_user_id,transferor_account_id,transferor_user_id,unit_id)
                            VALUES ('{$result[0]['transferee_account_id']}','{$result[0]['transferee_user_id']}','{$account_id}','{$user_id}','{$unit}')";
                    if ($this->db_read->executeQuery($sql, array())) {
                        $releases++ ;
                    }            

                }

            }

        }

        return 'Devices Authorized: ' . $attempts . ' : ' . $validations . ' : ' . $releases . ' : ' . $sql . ' : ' . $params[0] . ', ' . $params[1] . ', ' . $params[2] . ', ' . $params[3] . ', ' . $params[4] ;

    }

    /**
     * Support Ajax GetLandmarkData Requests
     */
    public function ajaxGetLandmarkData($user,$uid) {

        $sql = "SELECT latitude, longitude, radius, shape, boundingbox FROM crossbones.territory
                WHERE territory_id = ? AND account_id = ?";
        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
        return $res[0] ;

    }

    /**
     * Support Ajax Update Requests
     */
    public function ajaxUpdate($user,$uid,$element,$value,$post,$permission) {

        switch ($element) {

                case             'alert-edit-contact' : $sql = "SELECT alert_id FROM crossbones.alert_contact WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        if($permission){
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.alert_contact ( contact_id , contactgroup_id , alert_id ) VALUES ( ? , ? , ? )";
                                                                $res = $this->db_read->executeQuery($sql, array(0,0,$uid));
                                                            }
                                                            $sql = "UPDATE crossbones.alert_contact SET contact_id = ?, contactgroup_id = ? WHERE alert_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,0,$uid));
                                                        }
                                                        $sql = "SELECT contact_id FROM crossbones.alert_contact
                                                                WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['contact_id'] ;
                                                        break;

                case        'alert-edit-contactgroup' : $sql = "SELECT alert_id FROM crossbones.alert_contact WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        if($permission){
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.alert_contact ( contact_id , contactgroup_id , alert_id ) VALUES ( ? , ? , ? )";
                                                                $res = $this->db_read->executeQuery($sql, array(0,0,$uid));
                                                            }
                                                            $sql = "UPDATE crossbones.alert_contact SET contact_id = ?, contactgroup_id = ? WHERE alert_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array(0,$value,$uid));
                                                        }
                                                        $sql = "SELECT contactgroup_id FROM crossbones.alert_contact
                                                                WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['contactgroup_id'] ;
                                                        break;

                case       'alert-edit-contactmethod' : $sql = "SELECT alert_id FROM crossbones.alert_contact WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        if($permission){
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.alert_contact ( contact_id , contactgroup_id , alert_id ) VALUES ( ? , ? , ? )";
                                                                $res = $this->db_read->executeQuery($sql, array(0,0,$uid));
                                                            }
                                                            $sql = "UPDATE crossbones.alert_contact SET method = ? WHERE alert_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT method FROM crossbones.alert_contact
                                                                WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['method'] ;
                                                        break;

                case         'alert-edit-contactmode' : // $sql = "SELECT alert_id FROM crossbones.alert_contact WHERE alert_id = ?";
                                                        // $res = $this->db_read->fetchAll($sql, array($uid));
                                                        // if($permission){
                                                        //     if(!($res)){
                                                        //         $sql = "INSERT INTO crossbones.alert_contact ( contact_id , contactgroup_id , alert_id ) VALUES ( ? , ? , ? )";
                                                        //     } else {
                                                        //         $sql = "UPDATE crossbones.alert_contact SET contact_id = ?, contactgroup_id = ? WHERE alert_id = ?";
                                                        //     }
                                                        //     $res = $this->db_read->executeQuery($sql, array(0,0,$uid));
                                                        //     switch($value){
                                                        //         case  '1' : $sql = "UPDATE crossbones.alert_contact SET mode = ?, contactgroup_id = '0' WHERE alert_id = ?";
                                                        //                     break;
                                                        //         case  '2' : $sql = "UPDATE crossbones.alert_contact SET mode = ?, contact_id = '0' WHERE alert_id = ?";
                                                        //                     break;
                                                        //         case  '3' : $sql = "UPDATE crossbones.alert_contact SET mode = ?, contactgroup_id = '0', contact_id = '0' WHERE alert_id = ?";
                                                        //                     break;
                                                        //     }
                                                        //     $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        // }
                                                        // $sql = "SELECT contactgroup_id, contact_id FROM crossbones.alert_contact
                                                        //         WHERE alert_id = ?";
                                                        // $res = $this->db_read->fetchAll($sql, array($uid));
                                                        // if(($res[0]['contact_id']==0)&&($res[0]['contactgroup_id']==0)){
                                                        //     $result['value'] = 3 ;
                                                        // } else if($res[0]['contactgroup_id']>0){
                                                        //     $result['value'] = 2 ;
                                                        // } else if($res[0]['contact_id']>0){
                                                        //     $result['value'] = 1 ;
                                                        // }
                                                        break;

                case                'alert-edit-days' : if($permission){
                                                            $sql = "UPDATE crossbones.alert SET day = ? WHERE alert_id = ?";
                                                            switch($value){
                                                                case  '1' : $value='weekday';
                                                                            break;
                                                                case  '2' : $value='weekend';
                                                                            break;
                                                                  default : $value='all';
                                                            }
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT day FROM crossbones.alert
                                                                WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['day'] ;
                                                        break;

                case            'alert-edit-duration' : if($permission){
                                                            $sql = "UPDATE crossbones.alert SET alerttrigger = ? WHERE alert_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT alerttrigger FROM crossbones.alert
                                                                WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['alerttrigger'] ;
                                                        break;

                case             'alert-edit-endhour' : if($permission){
                                                            $sql = "UPDATE crossbones.alert SET endhour = ? WHERE alert_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT endhour FROM crossbones.alert
                                                                WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['endhour'] ;
                                                        break;

                case               'alert-edit-hours' : if($permission){
                                                            $sql = "UPDATE crossbones.alert SET time = ?, starthour = ?, endhour = ? WHERE alert_id = ?";
                                                            switch($value){
                                                                case  '1' : $value='range';
                                                                            $start = 6;
                                                                            $end = 18;
                                                                            break;
                                                                  default : $value='all';
                                                                            $start = 0;
                                                                            $end = 0;
                                                            }
                                                            $res = $this->db_read->executeQuery($sql, array($value,$start,$end,$uid));
                                                        }
                                                        $sql = "SELECT day FROM crossbones.alert
                                                                WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['day'] ;
                                                        break;

                case                'alert-edit-name' : if($permission){
                                                            $sql = "UPDATE crossbones.alert SET alertname = ? WHERE alert_id = ? AND account_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid,$user['account_id']));
                                                        }
                                                        $sql = "SELECT alertname FROM crossbones.alert
                                                                WHERE alert_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        $result['value'] = $res[0]['alertname'] ;
                                                        break;

                case           'alert-edit-overspeed' : if($permission){
                                                            $sql = "UPDATE crossbones.alert SET alerttrigger = ? WHERE alert_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT alerttrigger FROM crossbones.alert
                                                                WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['alerttrigger'] ;
                                                        break;

                case            'alert-edit-landmark' : $sql = "SELECT alert_id FROM crossbones.alert_territory WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        if($permission){
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.alert_territory ( territory_id , territorygroup_id , alert_id ) VALUES ( ? , ? , ? )";
                                                                $res = $this->db_read->executeQuery($sql, array(0,0,$uid));
                                                            }
                                                            $sql = "UPDATE crossbones.alert_territory SET territory_id = ? , territorygroup_id = ? WHERE alert_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,0,$uid));
                                                        }
                                                        $sql = "SELECT territory_id FROM crossbones.alert_territory
                                                                WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['territory_id'] ;
                                                        break;

                case       'alert-edit-landmarkgroup' : $sql = "SELECT alert_id FROM crossbones.alert_territory WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        if($permission){
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.alert_territory ( territory_id , territorygroup_id , alert_id ) VALUES ( ? , ? , ? )";
                                                                $res = $this->db_read->executeQuery($sql, array(0,0,$uid));
                                                            }
                                                            $sql = "UPDATE crossbones.alert_territory SET territory_id = ? , territorygroup_id = ? WHERE alert_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array(0,$value,$uid));
                                                        }
                                                        $sql = "SELECT territorygroup_id FROM crossbones.alert_territory
                                                                WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['territorygroup_id'] ;
                                                        break;

                case        'alert-edit-landmarkmode' : $sql = "SELECT alert_id FROM crossbones.alert_territory WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        if($permission){
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.alert_territory ( territory_id , territorygroup_id , alert_id ) VALUES ( ? , ? , ? )";
                                                            } else {
                                                                $sql = "UPDATE crossbones.alert_territory SET territory_id = ?, territorygroup_id = ? WHERE alert_id = ?";
                                                            }
                                                            $res = $this->db_read->executeQuery($sql, array(0,0,$uid));
                                                        }
                                                        $sql = "SELECT * FROM crossbones.alert_territory
                                                                WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['alert_id'] . '-' . $res[0]['territory_id'] . '-' . $res[0]['territorygroup_id'] ;
                                                        break;

                case     'alert-edit-landmarktrigger' : if($permission){
                                                            $sql = "UPDATE crossbones.alert SET alerttrigger = ? WHERE alert_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT * FROM crossbones.alert_territory
                                                                WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['alerttrigger'] ;
                                                        break;

                case           'alert-edit-starthour' : if($permission){
                                                            $sql = "UPDATE crossbones.alert SET starthour = ? WHERE alert_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT starthour FROM crossbones.alert
                                                                WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['starthour'] ;
                                                        break;

                case                'alert-edit-type' : if($permission){
                                                            $sql = "UPDATE crossbones.alert SET alerttype_id = ?, alerttrigger = ? WHERE alert_id = ? AND account_id = ?";
                                                            switch($value){
                                                                case  '2' : 
                                                                case  '6' : $type = 1;
                                                                            break;
                                                                case  '3' : $type = 'Both';
                                                                            break;
                                                                case  '7' : $type = 25;
                                                                            break;
                                                                  default : $type = '';
                                                            }
                                                            $res = $this->db_read->executeQuery($sql, array($value,$type,$uid,$user['account_id']));
                                                        }
                                                        $sql = "SELECT alerttype_id FROM crossbones.alert
                                                                WHERE alert_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        $result['value'] = $res[0]['alerttype_id'] ;
                                                        break;

                case             'alert-edit-vehicle' : $sql = "SELECT alert_id FROM crossbones.alert_unit WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        if($permission){
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.alert_unit ( unit_id , unitgroup_id , alert_id ) VALUES ( ? , ? , ? )";
                                                                $res = $this->db_read->executeQuery($sql, array(0,0,$uid));
                                                            }
                                                            $sql = "UPDATE crossbones.alert_unit SET unit_id = ? , unitgroup_id = ? WHERE alert_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,0,$uid));
                                                            $sql = "UPDATE crossbones.alert SET unit = ? WHERE alert_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array('Single',$uid));
                                                        }
                                                        $sql = "SELECT unit_id FROM crossbones.alert_unit
                                                                WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,));
                                                        $result['value'] = $res[0]['unit_id'] ;
                                                        break;

                case        'alert-edit-vehiclegroup' : $sql = "SELECT alert_id FROM crossbones.alert_unit WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        if($permission){
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.alert_unit ( unit_id , unitgroup_id , alert_id ) VALUES ( ? , ? , ? )";
                                                                $res = $this->db_read->executeQuery($sql, array(0,0,$uid));
                                                            }
                                                            $sql = "UPDATE crossbones.alert_unit SET unit_id = ? , unitgroup_id = ? WHERE alert_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array(0,$value,$uid));
                                                            $sql = "UPDATE crossbones.alert SET unit = ? WHERE alert_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array('Group',$uid));
                                                        }
                                                        $sql = "SELECT unitgroup_id FROM crossbones.alert_unit
                                                                WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,));
                                                        $result['value'] = $res[0]['unitgroup_id'] ;
                                                        break;

                case         'alert-edit-vehiclemode' : $sql = "SELECT alert_id FROM crossbones.alert_unit WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        if($permission){
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.alert_unit ( mode , alert_id ) VALUES ( ? , ? )";
                                                                // $sql = "INSERT INTO crossbones.alert_unit ( unit_id , unitgroup_id , mode , alert_id ) VALUES ( ? , ? , ? , ? )";
                                                            } else {
                                                                $sql = "UPDATE crossbones.alert_unit SET mode = ? WHERE alert_id = ?";
                                                                // $sql = "UPDATE crossbones.alert_unit SET unit_id = ?, unitgroup_id = ?, mode = ? WHERE alert_id = ?";
                                                            }
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                            switch($value){
                                                                case  '1' :
                                                                case   1  : $value = 'Single' ;
                                                                            $sql = "UPDATE crossbones.alert_unit SET unitgroup_id = ? WHERE alert_id = ?";
                                                                            $res = $this->db_read->executeQuery($sql, array(0,$uid));
                                                                            break;
                                                                case  '2' :
                                                                case   2  : $value = 'Group' ;
                                                                            $sql = "UPDATE crossbones.alert_unit SET unit_id = ? WHERE alert_id = ?";
                                                                            $res = $this->db_read->executeQuery($sql, array(0,$uid));
                                                                            break;
                                                                case  '3' :
                                                                case   3  : $value = 'All' ;
                                                                            $sql = "UPDATE crossbones.alert_unit SET unit_id = ?, unitgroup_id = ? WHERE alert_id = ?";
                                                                            $res = $this->db_read->executeQuery($sql, array(0,0,$uid));
                                                                            break;
                                                                  default : $value = 'N/A' ;
                                                                            break;
                                                            }
                                                            $sql = "UPDATE crossbones.alert SET unit = ? WHERE alert_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT unit_id FROM crossbones.alert_unit
                                                                WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $uid . '=' . $value . '#' . $res[0]['alert_id'] . '#' . $res[0]['unit_id'] . '#' . $res[0]['mode'] . '#' ;
                                                        break;

            case         'crossbones-alert-alertname' : if($permission){
                                                            $sql = "UPDATE crossbones.alert SET alertname = ? WHERE alert_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT alertname FROM crossbones.alert
                                                                WHERE alert_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['alertname'] ;
                                                        break;

              case   'crossbones-contact-cellcarrier' : if($permission){
                                                            $sql = "UPDATE crossbones.contact SET cellcarrier_id = ? WHERE user_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT cellcarrier_id FROM crossbones.contact
                                                                WHERE user_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['cellcarrier_id'] ;
                                                        break;

              case    'crossbones-contact-cellnumber' : if($permission){
                                                            $sql = "UPDATE crossbones.contact SET cellnumber = ? WHERE contact_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT cellnumber FROM crossbones.contact
                                                                WHERE user_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['cellnumber'] ;
                                                        break;

              case         'crossbones-contact-email' : if($permission){
                                                            $sql = "UPDATE crossbones.contact SET email = ? WHERE contact_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT email FROM crossbones.contact
                                                                WHERE user_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['email'] ;
                                                        break;

              case     'crossbones-contact-firstname' :
              case        'crossbones-user-firstname' : if($permission){
                                                            $sql = "UPDATE crossbones.contact SET firstname = ? WHERE user_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT firstname FROM crossbones.contact
                                                                WHERE user_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['firstname'] ;
                                                        break;

              case      'crossbones-contact-lastname' :
              case         'crossbones-user-lastname' : if($permission){
                                                            $sql = "UPDATE crossbones.contact SET lastname = ? WHERE user_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT lastname FROM crossbones.contact
                                                                WHERE user_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['lastname'] ;
                                                        break;

      case 'crossbones-contactgroup-contactgroupname' : if($permission){
                                                            $sql = "UPDATE crossbones.contactgroup SET contactgroupname = ? WHERE contactgroup_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT contactgroupname FROM crossbones.contactgroup
                                                                WHERE contactgroup_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['contactgroupname'] ;
                                                        break;

            case          'crossbones-territory-city' : //if($permission){
                                                            $sql = "UPDATE crossbones.territory SET city = ? WHERE territory_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        // }
                                                        $sql = "SELECT city FROM crossbones.territory
                                                                WHERE territory_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['city'] ;
                                                        break;

            case       'crossbones-territory-country' : //if($permission){
                                                            $sql = "UPDATE crossbones.territory SET country = ? WHERE territory_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        // }
                                                        $sql = "SELECT country FROM crossbones.territory
                                                                WHERE territory_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['country'] ;
                                                        break;

            case         'crossbones-territory-state' : //if($permission){
                                                            $sql = "UPDATE crossbones.territory SET state = ? WHERE territory_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        // }
                                                        $sql = "SELECT state FROM crossbones.territory
                                                                WHERE territory_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['state'] ;
                                                        break;

            case 'crossbones-territory-streetaddress' : //if($permission){
                                                            $sql = "UPDATE crossbones.territory SET streetaddress = ? WHERE territory_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        // }
                                                        $sql = "SELECT streetaddress FROM crossbones.territory
                                                                WHERE territory_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['streetaddress'] ;
                                                        break;

            case 'crossbones-territory-territoryname' : //if($permission){
                                                            $sql = "UPDATE crossbones.territory SET territoryname = ? WHERE territory_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        // }
                                                        $sql = "SELECT territoryname FROM crossbones.territory
                                                                WHERE territory_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['territoryname'] ;
                                                        break;

            case       'crossbones-territory-zipcode' : //if($permission){
                                                            $sql = "UPDATE crossbones.territory SET zipcode = ? WHERE territory_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        // }
                                                        $sql = "SELECT zipcode FROM crossbones.territory
                                                                WHERE territory_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['zipcode'] ;
                                                        break;

              case            'crossbones-user-email' : if($permission){
                                                            $sql = "UPDATE crossbones.contact SET email = ? WHERE user_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT email FROM crossbones.contact
                                                                WHERE user_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['email'] ;
                                                        break;

              case         'crossbones-user-username' : $sql = "SELECT user_id FROM crossbones.user
                                                                WHERE username = ? AND user_id != ?";
                                                        $res = $this->db_read->fetchAll($sql, array($value,$uid));
                                                        if($permission){
                                                            if((!($res))&&($value)){
                                                                $sql = "UPDATE crossbones.user SET username = ? WHERE user_id = ?";
                                                                $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                            } else if (!($value)) {
                                                                $result['alert'] = 'Sorry, username may not be empty' ;
                                                            } else {
                                                                $result['alert'] = 'Sorry, "' . $value . '" is already in use by another user' ;
                                                            }
                                                        }
                                                        $sql = "SELECT username FROM crossbones.user
                                                                WHERE user_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['username'] ;
                                                        break;

              case 'crossbones-usertype-usertypename' : if($permission){
                                                            $sql = "UPDATE crossbones.usertype SET usertype = ? WHERE usertype_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT usertype FROM crossbones.usertype
                                                                WHERE usertype_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['usertype'] ;
                                                        break;

              case   'crossbones-unitattribute-color' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.unitattribute WHERE unit_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.unitattribute ( unit_id , createdate ) VALUES ( ? , now() )";
                                                                $res = $this->db_write->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.unitattribute SET color = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT color FROM crossbones.unitattribute
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['color'] ;
                                                        break;

   case 'crossbones-unitattribute-licenseplatenumber' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.unitattribute WHERE unit_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.unitattribute ( unit_id , createdate ) VALUES ( ? , now() )";
                                                                $res = $this->db_write->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.unitattribute SET licenseplatenumber = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT licenseplatenumber FROM crossbones.unitattribute
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['licenseplatenumber'] ;
                                                        break;

           case 'crossbones-unitattribute-loannumber' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.unitattribute WHERE unit_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.unitattribute ( unit_id , createdate ) VALUES ( ? , now() )";
                                                                $res = $this->db_write->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.unitattribute SET loannumber = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT loannumber FROM crossbones.unitattribute
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['loannumber'] ;
                                                        break;

              case    'crossbones-unitattribute-make' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.unitattribute WHERE unit_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.unitattribute ( unit_id , createdate ) VALUES ( ? , now() )";
                                                                $res = $this->db_write->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.unitattribute SET make = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT make FROM crossbones.unitattribute
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['make'] ;
                                                        break;

              case   'crossbones-unitattribute-model' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.unitattribute WHERE unit_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.unitattribute ( unit_id , createdate ) VALUES ( ? , now() )";
                                                                $res = $this->db_write->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.unitattribute SET model = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT model FROM crossbones.unitattribute
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['model'] ;
                                                        break;

              case     'crossbones-unitattribute-vin' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.unitattribute WHERE unit_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.unitattribute ( unit_id , createdate ) VALUES ( ? , now() )";
                                                                $res = $this->db_write->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.unitattribute SET vin = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT vin FROM crossbones.unitattribute
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['vin'] ;
                                                        break;

              case    'crossbones-unitattribute-year' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.unitattribute WHERE unit_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.unitattribute ( unit_id , createdate ) VALUES ( ? , now() )";
                                                                $res = $this->db_write->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.unitattribute SET year = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT year FROM crossbones.unitattribute
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['year'] ;
                                                        break;

                      case 'crossbones-unit-unitname' : if($permission){
                                                            $sql = "UPDATE crossbones.unit SET unitname = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT unitname FROM crossbones.unit
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['unitname'] ;
                                                        break;

            case 'crossbones-unitgroup-unitgroupname' : if($permission){
                                                            $sql = "UPDATE crossbones.unitgroup SET unitgroupname = ? WHERE unitgroup_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT unitgroupname FROM crossbones.unitgroup
                                                                WHERE unitgroup_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['unitgroupname'] ;
                                                        break;

                        case    'customer-first-name' : $sql = "SELECT unit_id FROM crossbones.customer
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        if($permission){
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.customer ( unit_id ) VALUES ( ? )";
                                                                $res = $this->db_read->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.customer SET firstname = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT firstname FROM crossbones.customer
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['firstname'] ;
                                                        break;

                        case     'customer-last-name' : $sql = "SELECT unit_id FROM crossbones.customer
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        if($permission){
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.customer ( unit_id ) VALUES ( ? )";
                                                                $res = $this->db_read->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.customer SET lastname = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT lastname FROM crossbones.customer
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['lastname'] ;
                                                        break;

                        case       'customer-address' : $sql = "SELECT unit_id FROM crossbones.customer
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        if($permission){
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.customer ( unit_id ) VALUES ( ? )";
                                                                $res = $this->db_read->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.customer SET streetaddress = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT streetaddress FROM crossbones.customer
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['streetaddress'] ;
                                                        break;

                        case          'customer-city' : $sql = "SELECT unit_id FROM crossbones.customer
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        if($permission){
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.customer ( unit_id ) VALUES ( ? )";
                                                                $res = $this->db_read->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.customer SET city = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT city FROM crossbones.customer
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['city'] ;
                                                        break;

                        case         'customer-state' : $sql = "SELECT unit_id FROM crossbones.customer
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        if($permission){
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.customer ( unit_id ) VALUES ( ? )";
                                                                $res = $this->db_read->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.customer SET state = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT state FROM crossbones.customer
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['state'] ;
                                                        break;

                        case       'customer-zipcode' : $sql = "SELECT unit_id FROM crossbones.customer
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        if($permission){
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.customer ( unit_id ) VALUES ( ? )";
                                                                $res = $this->db_read->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.customer SET zipcode = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT zipcode FROM crossbones.customer
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['zipcode'] ;
                                                        break;

                        case         'customer-email' : $sql = "SELECT unit_id FROM crossbones.customer
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        if($permission){
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.customer ( unit_id ) VALUES ( ? )";
                                                                $res = $this->db_read->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.customer SET email = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT email FROM crossbones.customer
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['email'] ;
                                                        break;

                        case  'customer-mobile-phone' : $sql = "SELECT unit_id FROM crossbones.customer
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        if($permission){
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.customer ( unit_id ) VALUES ( ? )";
                                                                $res = $this->db_read->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.customer SET cellphone = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT cellphone FROM crossbones.customer
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['cellphone'] ;
                                                        break;

                        case    'customer-home-phone' : $sql = "SELECT unit_id FROM crossbones.customer
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        if($permission){
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.customer ( unit_id ) VALUES ( ? )";
                                                                $res = $this->db_read->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.customer SET homephone = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT homephone FROM crossbones.customer
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['homephone'] ;
                                                        break;

                        case    'device-group-update' : if($permission){
                                                            $sql = "UPDATE crossbones.unit SET unitgroup_id = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT unitgroup_id FROM crossbones.unit
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['unitgroup_id'] ;
                                                        break;

                case           'edit-contact-carrier' : if($permission){
                                                            $sql = "UPDATE crossbones.contact SET cellcarrier_id = ? WHERE contact_id = ? AND account_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid,$user['account_id']));
                                                        }
                                                        $sql = "SELECT cellcarrier_id FROM crossbones.contact
                                                                WHERE contact_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        $result['value'] = $res[0]['cellcarrier_id'] ;
                                                        break;

                case        'edit-contact-cellnumber' : $value = preg_replace("/[^0-9]/","",$value);
                                                        if($permission){
                                                            $sql = "UPDATE crossbones.contact SET cellnumber = ? WHERE contact_id = ? AND account_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid,$user['account_id']));
                                                        }
                                                        $sql = "SELECT cellnumber FROM crossbones.contact
                                                                WHERE contact_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        $result['value'] = $res[0]['cellnumber'] ;
                                                        break;

                case             'edit-contact-email' : if($permission){
                                                            $sql = "UPDATE crossbones.contact SET email = ? WHERE contact_id = ? AND account_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid,$user['account_id']));
                                                        }
                                                        $sql = "SELECT email FROM crossbones.contact
                                                                WHERE contact_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        $result['value'] = $res[0]['email'] ;
                                                        break;

                case        'edit-contact-first-name' : if($permission){
                                                            $sql = "UPDATE crossbones.contact SET firstname = ? WHERE contact_id = ? AND account_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid,$user['account_id']));
                                                        }
                                                        $sql = "SELECT firstname FROM crossbones.contact
                                                                WHERE contact_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        $result['value'] = $res[0]['firstname'] ;
                                                        break;

                case         'edit-contact-last-name' : if($permission){
                                                            $sql = "UPDATE crossbones.contact SET lastname = ? WHERE contact_id = ? AND account_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid,$user['account_id']));
                                                        }
                                                        $sql = "SELECT lastname FROM crossbones.contact
                                                                WHERE contact_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        $result['value'] = $res[0]['lastname'] ;
                                                        break;

                case 'edit-landmark-group-title-edit' : if($permission){
                                                            $sql = "UPDATE crossbones.territorygroup SET territorygroupname = ? WHERE territorygroup_id = ? AND account_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid,$user['account_id']));
                                                        }
                                                        $sql = "SELECT territorygroupname FROM crossbones.territorygroup
                                                                WHERE territorygroup_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        $result['value'] = $res[0]['territorygroupname'] ;
                                                        break;

                case  'edit-vehicle-group-title-edit' : if($permission){
                                                            $sql = "UPDATE crossbones.unitgroup SET unitgroupname = ? WHERE unitgroup_id = ? AND account_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid,$user['account_id']));
                                                        }
                                                        $sql = "SELECT unitgroupname FROM crossbones.unitgroup
                                                                WHERE unitgroup_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        $result['value'] = $res[0]['unitgroupname'] ;
                                                        break;

                        case      'landmark-category' : if($permission){
                                                            $sql = "UPDATE crossbones.territory SET territorycategory_id = ? WHERE territory_id = ? AND account_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid,$user['account_id']));
                                                        }
                                                        $sql = "SELECT territorycategory_id FROM crossbones.territory
                                                                WHERE territory_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        $result['value'] = $res[0]['territorycategory_id'] ;
                                                        break;

                        case          'landmark-city' : if($permission){
                                                            $sql = "UPDATE crossbones.territory SET city = ? WHERE territory_id = ? AND account_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid,$user['account_id']));
                                                        }
                                                        $sql = "SELECT city FROM crossbones.territory
                                                                WHERE territory_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        $result['value'] = $res[0]['city'] ;
                                                        break;

                        case         'landmark-click' : // $v = explode(':',$value);
                                                        $v = $value ;
                                                        if($v[0]=='undefined'){ $v[0]=''; }
                                                        if($v[1]=='undefined'){ $v[1]=''; }
                                                        if($v[2]=='undefined'){ $v[2]=''; }
                                                        if($v[3]=='undefined'){ $v[3]=''; }
                                                        if($v[4]=='undefined'){ $v[4]=''; }
                                                        if($v[5]=='undefined'){ $v[5]=''; }
                                                        if($v[6]=='undefined'){ $v[6]=''; }
                                                        // if($permission){
                                                            $sql = "UPDATE crossbones.territory SET latitude = ?, longitude = ?, streetaddress = ?, city = ?, state = ?, zipcode = ?, country = ? WHERE territory_id = ? AND account_id = ?";
                                                            $result['value']['sql']       = $sql . ', ' . $v[0] . ', ' . $v[1] . ', ' . $v[2] . ', ' . $v[3] . ', ' . $v[4] . ', ' . $v[5] . ', ' . $v[6] . ', ' . $uid . ', ' . $user['account_id'];
                                                            $res = $this->db_read->executeQuery($sql, array($v[0],$v[1],$v[2],$v[3],$v[4],$v[5],$v[6],$uid,$user['account_id']));
                                                        // }
                                                        $sql = "SELECT latitude, longitude, streetaddress, city, state, zipcode, country FROM crossbones.territory
                                                                WHERE territory_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        $result['value'] = $res[0]['latitude'] . ':' . $res[0]['longitude'] . ':' . $res[0]['city'] . ':' . $res[0]['state'] . ':' . $res[0]['zipcode'] . ':' . $res[0]['country'];
                                                        break;

                        case       'landmark-country' : if($permission){
                                                            $sql = "UPDATE crossbones.territory SET country = ? WHERE territory_id = ? AND account_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid,$user['account_id']));
                                                        }
                                                        $sql = "SELECT country FROM crossbones.territory
                                                                WHERE territory_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        $result['value'] = $res[0]['country'] ;
                                                        break;

                        case         'landmark-group' : if($permission){
                                                            $sql = "UPDATE crossbones.territory SET territorygroup_id = ? WHERE territory_id = ? AND account_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid,$user['account_id']));
                                                        }
                                                        $sql = "SELECT territorygroup_id FROM crossbones.territory
                                                                WHERE territory_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        $result['value'] = $res[0]['territorygroup_id'] ;
                                                        break;

                        case    'landmark-latlngedit' : if($permission){
                                                            $sql = "UPDATE crossbones.territory SET streetaddress = ?, city = ?, state = ?, zipcode = ?, country = ? WHERE territory_id = ? AND account_id = ?";
                                                            $result['value']['sql']       = $sql . ', ' . $value['address'] . ', ' . $value['city'] . ', ' . $value['state'] . ', ' . $value['zip'] . ', ' . $value['country'] . ', ' . $uid . ', ' . $user['account_id'];
                                                            $res = $this->db_read->executeQuery($sql, array($value['address'],$value['city'],$value['state'],$value['zip'],$value['country'],$uid,$user['account_id']));
                                                        }
                                                        $sql = "SELECT latitude, longitude, streetaddress, city, state, zipcode, country FROM crossbones.territory
                                                                WHERE territory_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        $result['value']['latitude']  = $res[0]['latitude'];
                                                        $result['value']['longitude'] = $res[0]['longitude'];
                                                        $result['value']['city']      = $res[0]['city'];
                                                        $result['value']['state']     = $res[0]['state'];
                                                        $result['value']['zipcode']   = $res[0]['zipcode'];
                                                        $result['value']['country']   = $res[0]['country'];
                                                        break;

                        case       'landmark-latlong' : $v = explode(':',$value); 
                                                        // if($permission){
                                                            $sql = "UPDATE crossbones.territory SET latitude = ?, longitude = ? WHERE territory_id = ? AND account_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($v[0],$v[1],$uid,$user['account_id']));
                                                        // }
                                                        $sql = "SELECT latitude, longitude FROM crossbones.territory
                                                                WHERE territory_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        $result['value'] = $res[0]['latitude'] . ':' . $res[0]['longitude'] ;
                                                        break;

                        case      'landmark-latitude' : if($permission){
                                                            $sql = "UPDATE crossbones.territory SET latitude = ? WHERE territory_id = ? AND account_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid,$user['account_id']));
                                                        }
                                                        $sql = "SELECT latitude FROM crossbones.territory
                                                                WHERE territory_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        $result['value'] = $res[0]['latitude'] ;
                                                        break;

                        case     'landmark-longitude' : if($permission){
                                                            $sql = "UPDATE crossbones.territory SET longitude = ? WHERE territory_id = ? AND account_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid,$user['account_id']));
                                                        }
                                                        $sql = "SELECT longitude FROM crossbones.territory
                                                                WHERE territory_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        $result['value'] = $res[0]['longitude'] ;
                                                        break;

                        case          'landmark-name' : if($permission){
                                                            $sql = "UPDATE crossbones.territory SET territoryname = ? WHERE territory_id = ? AND account_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid,$user['account_id']));
                                                        }
                                                        $sql = "SELECT territoryname FROM crossbones.territory
                                                                WHERE territory_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        $result['value'] = $res[0]['territoryname'] ;
                                                        break;

                        case       'landmark-polygon' : // $value = null;
                                                        if(!($value)){
                                                            $value[] = '29.9 -95.5';
                                                            $value[] = '28.8 -94.7';
                                                        }
                                                        foreach ($value as $k => $v){
                                                            if($buffer){
                                                                $buffer .= ', ';
                                                            }
                                                            $a = explode(' ',$v);
                                                            $b[] = round($a[0], 7) . " ". round($a[1], 7);
                                                            $buffer .= round($a[0], 7) . " ". round($a[1], 7);
                                                        }
                                                        if($buffer){
                                                            $buffer .= ', ';
                                                        }
                                                        $buffer .= $b[0];
                                                        $a = explode(' ',$b[0]);
                                                        $value = 'polygon';
                                                        if($permission){
                                                            $sql = "UPDATE crossbones.territory SET latitude = ?, longitude = ?, boundingbox = PolygonFromText('POLYGON((" . $buffer . "))') WHERE territory_id = ? AND account_id = ?";
                                                            $result['value'] = $sql . ',' . $uid . ',' . $user['account_id'] ;
                                                            $res = $this->db_read->executeQuery($sql, array($a[0],$a[1],$uid,$user['account_id']));
                                                        }
                                                        $sql = "SELECT shape , boundingbox FROM crossbones.territory 
                                                                WHERE territory_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        // $result['value'] = $res[0]['shape'] ;
                                                        break;

                        case        'landmark-radius' : if(!($post['boundingbox'])){
                                                            $post['boundingbox'] = "GEOMFROMTEXT('POLYGON((29.9 -95.5))')";
                                                        }
                                                        // $post['boundingbox'] = "" . $post['boundingbox'] . "";
                                                        if($permission){
                                                            $sql = "UPDATE crossbones.territory SET radius = ?, boundingbox = " . $post['boundingbox'] . " WHERE territory_id = ? AND account_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid,$user['account_id']));
                                                        }
                                                        $sql = "SELECT radius FROM crossbones.territory
                                                                WHERE territory_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        $result['value'] = $res[0]['radius'] ;
                                                        break;

                        case         'landmark-shape' : if(!($post['boundingbox'])){
                                                            $post['boundingbox'] = "GEOMFROMTEXT('POLYGON((29.9 -95.5))')";
                                                        }
                                                        // $post['boundingbox'] = "" . $post['boundingbox'] . "";
                                                        if($permission){
                                                            $sql = "UPDATE crossbones.territory SET shape = ?, boundingbox = " . $post['boundingbox'] . " WHERE territory_id = ? AND account_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid,$user['account_id']));
                                                        }
                                                        $result['value'] = $sql . ':' . $value . ',' . $uid . ',' . $user['account_id'] ;
                                                        $sql = "SELECT shape FROM crossbones.territory
                                                                WHERE territory_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        // $result['value'] = $res[0]['shape'] ;
                                                        break;

                        case         'landmark-state' : if($permission){
                                                            $sql = "UPDATE crossbones.territory SET state = ? WHERE territory_id = ? AND account_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid,$user['account_id']));
                                                        }
                                                        $sql = "SELECT state FROM crossbones.territory
                                                                WHERE territory_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        $result['value'] = $res[0]['state'] ;
                                                        break;

                case        'landmark-street-address' : if($permission){
                                                            $sql = "UPDATE crossbones.territory SET streetaddress = ? WHERE territory_id = ? AND account_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid,$user['account_id']));
                                                        }
                                                        $sql = "SELECT streetaddress FROM crossbones.territory
                                                                WHERE territory_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        $result['value'] = $res[0]['streetaddress'] ;
                                                        break;

                        case       'landmark-zipcode' : if($permission){
                                                            $sql = "UPDATE crossbones.territory SET zipcode = ? WHERE territory_id = ? AND account_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid,$user['account_id']));
                                                        }
                                                        $sql = "SELECT zipcode FROM crossbones.territory
                                                                WHERE territory_id = ? AND account_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid,$user['account_id']));
                                                        $result['value'] = $res[0]['zipcode'] ;
                                                        break;

                        case  'my-account-first-name' : if($permission){
                                                            $sql = "UPDATE crossbones.user SET firstname = ? WHERE account_id = ? AND user_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$user['account_id'],$user['user_id']));
                                                        }
                                                        $sql = "SELECT firstname FROM crossbones.user
                                                                WHERE account_id = ? AND user_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($user['account_id'],$user['user_id']));
                                                        $result['value'] = $res[0]['firstname'] ;
                                                        break;

                        case   'my-account-last-name' : if($permission){
                                                            $sql = "UPDATE crossbones.user SET lastname = ? WHERE account_id = ? AND user_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$user['account_id'],$user['user_id']));
                                                        }
                                                        $sql = "SELECT lastname FROM crossbones.user
                                                                WHERE account_id = ? AND user_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($user['account_id'],$user['user_id']));
                                                        $result['value'] = $res[0]['lastname'] ;
                                                        break;

                        case       'my-account-email' : if($permission){
                                                            $sql = "UPDATE crossbones.user SET email = ? WHERE account_id = ? AND user_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$user['account_id'],$user['user_id']));
                                                        }
                                                        $sql = "SELECT email FROM crossbones.user
                                                                WHERE account_id = ? AND user_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($user['account_id'],$user['user_id']));
                                                        $result['value'] = $res[0]['email'] ;
                                                        break;

                        case        'repo-edit-phone' : $sql = "UPDATE crossbones.repo SET phone = ? WHERE url = ?";
                                                        $res = $this->db_read->executeQuery($sql, array($value,str_replace('/repo/','',$uid)));
                                                        $sql = "SELECT * FROM crossbones.repo
                                                                WHERE url = ?";
                                                        $res = $this->db_read->fetchAll($sql, array(str_replace('/repo/','',$uid)));
                                                        $result['value'] = $res[0]['phone'] ;
                                                        $result['permission'] = $res[0]['repo_id'] ;
                                                        break;

                        case         'repo-edit-name' : $sql = "UPDATE crossbones.repo SET name = ? WHERE url = ?";
                                                        $res = $this->db_read->executeQuery($sql, array($value,str_replace('/repo/','',$uid)));
                                                        $sql = "SELECT * FROM crossbones.repo
                                                                WHERE url = ?";
                                                        $res = $this->db_read->fetchAll($sql, array(str_replace('/repo/','',$uid)));
                                                        $result['value'] = $res[0]['name'] ;
                                                        $result['permission'] = $res[0]['repo_id'] ;
                                                        break;

                        case         'reminderstatus' : 
                        case          'starterstatus' : $sql = "UPDATE crossbones.unit SET " . $element . " = ? WHERE unit_id = ?";
                                                        $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        $sql = "SELECT * FROM crossbones.unit
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0][$element] ;
                                                        break;

                        case  'user-edit-cellcarrier' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.contact WHERE user_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.contact ( user_id ) VALUES ( ? )";
                                                                $res = $this->db_read->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.contact SET cellcarrier_id = ? WHERE user_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT cellcarrier_id as value FROM crossbones.contact
                                                                WHERE user_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['value'] ;
                                                        break;

                        case   'user-edit-cellnumber' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.contact WHERE user_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.contact ( user_id ) VALUES ( ? )";
                                                                $res = $this->db_read->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.contact SET cellnumber = ? WHERE user_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array(preg_replace('/\D/', '', $value),$uid));
                                                        }
                                                        $sql = "SELECT cellnumber as value FROM crossbones.contact
                                                                WHERE user_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['value'] ;
                                                        break;

                        case        'user-edit-email' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.user WHERE email = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($value));
                                                            if(!($res)){
                                                                $sql = "UPDATE crossbones.user SET email = ? WHERE user_id = ?";
                                                                $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                            }
                                                        }
                                                        $sql = "SELECT email as value FROM crossbones.user
                                                                WHERE user_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['value'] ;
                                                        break;

                        case    'user-edit-firstname' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.contact WHERE user_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.contact ( user_id ) VALUES ( ? )";
                                                                $res = $this->db_read->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.contact SET firstname = ? WHERE user_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT firstname as value FROM crossbones.contact
                                                                WHERE user_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['value'] ;
                                                        break;

                        case     'user-edit-lastname' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.contact WHERE user_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.contact ( user_id ) VALUES ( ? )";
                                                                $res = $this->db_read->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.contact SET lastname = ? WHERE user_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT lastname as value FROM crossbones.contact
                                                                WHERE user_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['value'] ;
                                                        break;

                        case     'user-edit-usertype' : if($permission){
                                                            $sql = "UPDATE crossbones.user SET usertype_id = ? WHERE user_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT usertype_id as value FROM crossbones.user
                                                                WHERE user_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['value'] ;
                                                        break;

                        case    'user-type-edit-name' : if($permission){
                                                            $sql = "UPDATE crossbones.usertype SET usertype = ? WHERE usertype_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT usertype as value FROM crossbones.usertype
                                                                WHERE usertype_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['value'] ;
                                                        break;

                        case           'vehicle-name' : if($permission){
                                                            if(!($value)){
                                                                $sql = "SELECT serialnumber FROM crossbones.unit WHERE unit_id = ?";
                                                                $res = $this->db_read->fetchAll($sql, array($uid));
                                                                $value = $res[0]['serialnumber'];                                                                
                                                            }
                                                            $sql = "UPDATE crossbones.unit SET unitname = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT u.unitname AS unitname FROM crossbones.unit u
                                                                WHERE u.unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['unitname'] ;
                                                        break;

                        case         'vehicle-status' : if($permission){
                                                            $sql = "UPDATE crossbones.unit SET unitstatus_id = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT u.* FROM crossbones.unit u
                                                                WHERE u.unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['unitstatus_id'] ;
                                                        break;

                        case          'vehicle-group' : if($permission){
                                                            $sql = "UPDATE crossbones.unit SET unitgroup_id = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT u.* FROM crossbones.unit u
                                                                WHERE u.unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['unitgroup_id'] ;
                                                        break;

                        case          'vehicle-model' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.unitattribute WHERE unit_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.unitattribute ( unit_id , createdate ) VALUES ( ? , now() )";
                                                                $res = $this->db_write->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.unitattribute SET model = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT u.*, ua.model AS model FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitattribute ua ON u.unit_id = ua.unit_id
                                                                WHERE u.unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['model'] ;
                                                        break;

                        case           'vehicle-year' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.unitattribute WHERE unit_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.unitattribute ( unit_id , createdate ) VALUES ( ? , now() )";
                                                                $res = $this->db_write->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.unitattribute SET year = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT u.*, ua.year AS year FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitattribute ua ON u.unit_id = ua.unit_id
                                                                WHERE u.unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['year'] ;
                                                        break;

                        case          'vehicle-color' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.unitattribute WHERE unit_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.unitattribute ( unit_id , createdate ) VALUES ( ? , now() )";
                                                                $res = $this->db_write->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.unitattribute SET color = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT ua.unitattribute_id, ua.color AS color FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitattribute ua ON u.unit_id = ua.unit_id
                                                                WHERE u.unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['color'] ;
                                                        break;

                        case      'vehicle-installer' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.unitinstallation WHERE unit_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.unitinstallation ( unit_id , createdate ) VALUES ( ? , now() )";
                                                                $res = $this->db_write->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.unitinstallation SET installer = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT * FROM crossbones.unitinstallation WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['installer'] ;
                                                        break;

                       case 'vehicle-install-mileage' : $sql = "SELECT unitodometer_id FROM crossbones.unit WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $uid = $res[0]['unitodometer_id'];
                                                        if($permission){
                                                            $sql = "UPDATE crossbones.unitodometer SET initialodometer = ? WHERE unitodometer_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT * FROM crossbones.unitodometer WHERE unitodometer_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['initialodometer'] ;
                                                        break;

                        case   'vehicle-install-date' : $val = date('Y-m-d',strtotime($value));
                                                        $sql = "SELECT * FROM crossbones.unitinstallation WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        if($permission){
                                                            if($res[0]['unit_id']){
                                                                $sql = "UPDATE crossbones.unitinstallation SET installdate = ? WHERE unit_id = ?";
                                                                $res = $this->db_read->executeQuery($sql, array($val,$uid));
                                                            } else {
                                                                $sql = "INSERT INTO crossbones.unitinstallation ( installdate , unit_id ) VALUES ( ? , ? )";
                                                                $res = $this->db_read->executeQuery($sql, array($val,$uid));
                                                            }
                                                        }
                                                        $sql = "SELECT * FROM crossbones.unitinstallation WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['installdate'] ;
                                                        break;

                        case  'vehicle-license-plate' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.unitattribute WHERE unit_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.unitattribute ( unit_id , createdate ) VALUES ( ? , now() )";
                                                                $res = $this->db_write->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.unitattribute SET licenseplatenumber = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT u.*, ua.licenseplatenumber AS licenseplatenumber FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitattribute ua ON u.unit_id = ua.unit_id
                                                                WHERE u.unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['licenseplatenumber'] ;
                                                        break;

                        case        'vehicle-loan-id' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.unitattribute WHERE unit_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.unitattribute ( unit_id , createdate ) VALUES ( ? , now() )";
                                                                $res = $this->db_write->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.unitattribute SET loannumber = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT u.*, ua.loannumber AS loannumber FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitattribute ua ON u.unit_id = ua.unit_id
                                                                WHERE u.unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['loannumber'] ;
                                                        break;

                        case          'vehicle-stock' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.unitattribute WHERE unit_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.unitattribute ( unit_id , createdate ) VALUES ( ? , now() )";
                                                                $res = $this->db_write->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.unitattribute SET stocknumber = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT u.*, ua.stocknumber AS stocknumber FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitattribute ua ON u.unit_id = ua.unit_id
                                                                WHERE u.unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['stocknumber'] ;
                                                        break;

                        case           'vehicle-plan' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.unitattribute WHERE unit_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.unitattribute ( unit_id , createdate ) VALUES ( ? , now() )";
                                                                $res = $this->db_write->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.unitattribute SET plan = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT u.*, ua.plan AS plan FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitattribute ua ON u.unit_id = ua.unit_id
                                                                WHERE u.unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['plan'] ;
                                                        break;

                        case   'vehicle-purchasedate' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.unitattribute WHERE unit_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.unitattribute ( unit_id , createdate ) VALUES ( ? , now() )";
                                                                $res = $this->db_write->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.unitattribute SET purchasedate = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT u.*, ua.purchasedate AS purchasedate FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitattribute ua ON u.unit_id = ua.unit_id
                                                                WHERE u.unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['purchasedate'] ;
                                                        break;

                        case    'vehicle-renewaldate' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.unitattribute WHERE unit_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.unitattribute ( unit_id , createdate ) VALUES ( ? , now() )";
                                                                $res = $this->db_write->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.unitattribute SET renewaldate = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT u.*, ua.renewaldate AS renewaldate FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitattribute ua ON u.unit_id = ua.unit_id
                                                                WHERE u.unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['renewaldate'] ;
                                                        break;

                       case 'vehicle-lastrenewaldate' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.unitattribute WHERE unit_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.unitattribute ( unit_id , createdate ) VALUES ( ? , now() )";
                                                                $res = $this->db_write->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.unitattribute SET lastrenewaldate = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT u.*, ua.lastrenewaldate AS lastrenewaldate FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitattribute ua ON u.unit_id = ua.unit_id
                                                                WHERE u.unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['lastrenewaldate'] ;
                                                        break;

                        case   'vehicle-activatedate' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.unitattribute WHERE unit_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.unitattribute ( unit_id , createdate ) VALUES ( ? , now() )";
                                                                $res = $this->db_write->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.unitattribute SET activatedate = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT u.*, ua.activatedate AS activatedate FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitattribute ua ON u.unit_id = ua.unit_id
                                                                WHERE u.unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['activatedate'] ;
                                                        break;

                        case 'vehicle-deactivatedate' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.unitattribute WHERE unit_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.unitattribute ( unit_id , createdate ) VALUES ( ? , now() )";
                                                                $res = $this->db_write->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.unitattribute SET deactivatedate = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT u.*, ua.deactivatedate AS deactivatedate FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitattribute ua ON u.unit_id = ua.unit_id
                                                                WHERE u.unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['deactivatedate'] ;
                                                        break;

                        case           'vehicle-make' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.unitattribute WHERE unit_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.unitattribute ( unit_id , createdate ) VALUES ( ? , now() )";
                                                                $res = $this->db_write->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.unitattribute SET make = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT u.*, ua.make AS make FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitattribute ua ON u.unit_id = ua.unit_id
                                                                WHERE u.unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['make'] ;
                                                        break;

                        case            'vehicle-vin' : if($permission){
                                                            $sql = "SELECT * FROM crossbones.unitattribute WHERE unit_id = ?";
                                                            $res = $this->db_read->fetchAll($sql, array($uid));
                                                            if(!($res)){
                                                                $sql = "INSERT INTO crossbones.unitattribute ( unit_id , createdate ) VALUES ( ? , now() )";
                                                                $res = $this->db_write->executeQuery($sql, array($uid));
                                                            }
                                                            $sql = "UPDATE crossbones.unitattribute SET vin = ? WHERE unit_id = ?";
                                                            $res = $this->db_read->executeQuery($sql, array($value,$uid));
                                                        }
                                                        $sql = "SELECT u.*, ua.vin AS vin FROM crossbones.unit u
                                                                LEFT JOIN crossbones.unitattribute ua ON u.unit_id = ua.unit_id
                                                                WHERE u.unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        $result['value'] = $res[0]['vin'] ;
                                                        break;

                                              default : $result['value'] = 'ajaxUpdate:*** CASE MISSING ***:uid="' . $uid . '", element="' . $element . '", value="' . $value . '"' ;
        }


        switch($element) {

            case                      'vehicle-color' :
            case              'vehicle-license-plate' :
            case                    'vehicle-loan-id' :
            case                       'vehicle-make' :
            case                      'vehicle-model' :
            case                      'vehicle-stock' :
            case                        'vehicle-vin' :
            case                       'vehicle-year' : $sql = "SELECT unitattribute_id
                                                                FROM crossbones.unitattribute
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        if($res[0]['unitattribute_id']>0){
                                                            $sql = "SELECT unitattribute_id
                                                                    FROM crossbones.unit 
                                                                    WHERE unit_id = ?";
                                                            $chk = $this->db_read->fetchAll($sql, array($uid));
                                                            if($chk[0]['unitattribute_id']<1){
                                                                $sql = "UPDATE crossbones.unit 
                                                                        SET unitattribute_id = ? 
                                                                        WHERE unit_id = ?";
                                                                $chk = $this->db_read->executeQuery($sql, array($res[0]['unitattribute_id'],$uid));
                                                            }
                                                        }
                                                        break;

            case               'vehicle-install-date' : 
            case            'vehicle-install-mileage' : 
            case                  'vehicle-installer' : $sql = "SELECT unitinstallation_id
                                                                FROM crossbones.unitinstallation
                                                                WHERE unit_id = ?";
                                                        $res = $this->db_read->fetchAll($sql, array($uid));
                                                        if($res[0]['unitinstallation_id']>0){
                                                            $sql = "SELECT unitinstallation_id
                                                                    FROM crossbones.unit 
                                                                    WHERE unit_id = ?";
                                                            $chk = $this->db_read->fetchAll($sql, array($uid));
                                                            if($chk[0]['unitinstallation_id']<1){
                                                                $sql = "UPDATE crossbones.unit 
                                                                        SET unitinstallation_id = ? 
                                                                        WHERE unit_id = ?";
                                                                $chk = $this->db_read->executeQuery($sql, array($res[0]['unitinstallation_id'],$uid));
                                                            }
                                                        }
                                                        break;

        }

                                            
        $result['sql'] = $sql ;
        $result['confirm'] = $result['value'] ;

        return $result;
                                            
    }

    /**
     * Support Ajax Update List Requests
     */
    public function ajaxUpdateList($user,$uid,$element,$value) {

        switch ($element) {

            case       'edit-contact-group-contacts-assigned' : 
            case      'edit-contact-group-contacts-available' : if($uid>0){
                                                                    $sql = "DELETE FROM crossbones.contactgroup_contact WHERE contactgroup_id = ? ";
                                                                    if ($this->db_read->executeQuery($sql, array($uid))) {
                                                                    }
                                                                    foreach ( $value as $k => $v ) {
                                                                        $val = $uid . ':' . $v;
                                                                        $sql = "INSERT INTO crossbones.contactgroup_contact ( contactgroup_id , contact_id ) VALUES ( ? , ? )";
                                                                        if ($this->db_read->executeQuery($sql, array($uid,$v))) {
                                                                        }
                                                                    }
                                                                }
                                                                $result['value'] = $sql . ' | ' . $val ;
                                                                break;

            case              'edit-landmark-groups-assigned' : 
            case             'edit-landmark-groups-available' : $sql = "UPDATE crossbones.territory SET territorygroup_id = ? WHERE territorygroup_id = ?";
                                                                if ($this->db_read->executeQuery($sql, array(0,$uid))) {
                                                                }
                                                                $val = array($uid);
                                                                $sql = "UPDATE crossbones.territory SET territorygroup_id = ? WHERE";
                                                                        foreach ( $value as $k => $v ) {
                                                                            if($bool){
                                                                                $sql .= " OR territory_id = ?" ; 
                                                                            } else {
                                                                                $bool=1;
                                                                                $sql .= " territory_id = ?" ; 
                                                                            }
                                                                            array_push($val,$v);
                                                                        }
                                                                $result['value'] = $sql . ' | ' . implode(',',$val) ;
                                                                if($bool){
                                                                    if ($this->db_read->executeQuery($sql, $val)) {
                                                                    }
                                                                }
                                                                break;

            case        'edit-vehicle-group-devices-assigned' : 
            case       'edit-vehicle-group-devices-available' : $val = array($uid);
                                                                $sql = "UPDATE crossbones.unit SET unitgroup_id = ? WHERE";
                                                                        foreach ( $value as $k => $v ) {
                                                                            if($bool){
                                                                                $sql .= " OR unit_id = ?" ; 
                                                                            } else {
                                                                                $bool=1;
                                                                                $sql .= " unit_id = ?" ; 
                                                                            }
                                                                            array_push($val,$v);
                                                                        }
                                                                $result['value'] = $sql . ' | ' . implode(',',$val) ;
                                                                if($bool){
                                                                    if ($this->db_read->executeQuery($sql, $val)) {
                                                                    }
                                                                }
                                                                break;

            case                 'user-edit-devices-assigned' :
            case                'user-edit-devices-available' : $val = array('',$uid);
                                                                $sql = "UPDATE crossbones.unit SET unitgroup_id = ? WHERE unitgroup_id = ?";
                                                                if ($this->db_read->executeQuery($sql, $val)) {
                                                                }
                                                                $val = array($uid);
                                                                $sql = "UPDATE crossbones.unit SET unitgroup_id = ? WHERE";
                                                                        foreach ( $value as $k => $v ) {
                                                                            if($bool){
                                                                                $sql .= " OR unit_id = ?" ; 
                                                                            } else {
                                                                                $bool=1;
                                                                                $sql .= " unit_id = ?" ; 
                                                                            }
                                                                            array_push($val,$v);
                                                                        }
                                                                $result['value'] = $sql . ' | ' . implode(',',$val) ;
                                                                if($bool){
                                                                    if ($this->db_read->executeQuery($sql, $val)) {
                                                                    }
                                                                }
                                                                break;

            case          'user-edit-landmarkgroups-assigned' : $sql = "SELECT utg.territorygroup_id as territorygroup_id
                                                                        FROM crossbones.user_territorygroup utg
                                                                        WHERE utg.user_id = ?";
                                                                $res = $this->db_read->fetchAll($sql, array($uid));
                                                                foreach ( $res as $row ) {
                                                                    $b1[$row['territorygroup_id']] = 1 ;
                                                                }
                                                                $result['value'] .= 'Add: ' ;
                                                                foreach ( $value as $k => $v ) {
                                                                    if(!($b1[$v])){
                                                                        $sql = "INSERT INTO crossbones.user_territorygroup (territorygroup_id,user_id)
                                                                                VALUES ('{$v}','{$uid}')";
                                                                        if ($this->db_read->executeQuery($sql, array())) {
                                                                            $b2[$v] = 1 ;
                                                                            $result['value'] .= $v . ', ' ;
                                                                        }
                                                                    } else {
                                                                        $b2[$v] = 1 ;
                                                                    }
                                                                }
                                                                $result['value'] .= 'Out: ' ;
                                                                foreach ( $res as $row ) {
                                                                    if(!($b2[$row['territorygroup_id']])){
                                                                        $sql = "DELETE FROM crossbones.user_territorygroup 
                                                                                WHERE territorygroup_id = ?
                                                                                AND user_id = ?";
                                                                        if ($this->db_read->executeQuery($sql, array($row['territorygroup_id'],$uid))) {
                                                                            $result['value'] .= $row['territorygroup_id'] . ', ' ;
                                                                        }
                                                                    }
                                                                }
                                                                $result['value'] .= 'for ' . $uid ;
                                                                break;

            case           'user-edit-vehiclegroups-assigned' : $sql = "SELECT uug.unitgroup_id as unitgroup_id
                                                                        FROM crossbones.user_unitgroup uug
                                                                        WHERE uug.user_id = ?";
                                                                $res = $this->db_read->fetchAll($sql, array($uid));
                                                                foreach ( $res as $row ) {
                                                                    $b1[$row['unitgroup_id']] = 1 ;
                                                                }
                                                                $result['value'] .= 'Add: ' ;
                                                                foreach ( $value as $k => $v ) {
                                                                    if(!($b1[$v])){
                                                                        $sql = "INSERT INTO crossbones.user_unitgroup (unitgroup_id,user_id)
                                                                                VALUES ('{$v}','{$uid}')";
                                                                        if ($this->db_read->executeQuery($sql, array())) {
                                                                            $b2[$v] = 1 ;
                                                                            $result['value'] .= $v . ', ' ;
                                                                        }
                                                                    } else {
                                                                        $b2[$v] = 1 ;
                                                                    }
                                                                }
                                                                $result['value'] .= 'Out: ' ;
                                                                foreach ( $res as $row ) {
                                                                    if(!($b2[$row['unitgroup_id']])){
                                                                        $sql = "DELETE FROM crossbones.user_unitgroup 
                                                                                WHERE unitgroup_id = ?
                                                                                AND user_id = ?";
                                                                        if ($this->db_read->executeQuery($sql, array($row['unitgroup_id'],$uid))) {
                                                                            $result['value'] .= $row['unitgroup_id'] . ', ' ;
                                                                        }
                                                                    }
                                                                }
                                                                $result['value'] .= 'for ' . $uid ;
                                                                break;

        }

        return $result;

    }

    /**
     * Support Repo Requests
     */
    public function getRepo($url)
    {
        $expirePolicy=15;

        $sql = "SELECT
                r.createdate as expiration,
                r.repo_id as repoKey,
                ua.*,
                u.db as db,
                u.unitname
                FROM crossbones.repo r
                LEFT JOIN crossbones.unit u ON u.unit_id = r.unit_id
                LEFT JOIN crossbones.unitattribute ua ON ua.unit_id = r.unit_id
                WHERE r.url = ?
                AND r.createdate > DATE_SUB(now(), INTERVAL " . $expirePolicy . " DAY)
                AND active = 0
                ORDER BY r.repo_id DESC
                LIMIT 1";

        $unit = $this->db_read->fetchAssoc($sql, array($url));

        $udb = $unit['db'] ;
        $uid = $unit['unit_id'] ;

        $result = $unit;

        $days=0;
        $out=null;
        $diff = strtotime(date('Y-m-d H:i:s')) - strtotime($unit['expiration']);
        while ($diff>86400){
            $days++;
            $diff = $diff - 86400;
        }
        $expiration = $expirePolicy - $days;
        $result['expiration'] = $expiration ;

        if ( ($udb) && ($uid)) {

            // $sql = "SELECT * 
            //         FROM " . $udb . ".unit" . $uid . "
            //         WHERE event_id > ? 
            //         AND event_id < ? 
            //         ORDER BY id DESC LIMIT 1";
            $sql = "SELECT * 
                    FROM " . $udb . ".unit" . $uid . "
                    WHERE event_id > ? 
                    AND event_id < ? 
                    ORDER BY unittime DESC LIMIT 1";
            $status = $this->db_read->fetchAssoc($sql, array(0,6));

            switch($status['event_id']){
                case              1  :
                case             '1' :
                case              3  :
                case             '3' :
                case              4  :
                case             '4' : $status = '<span class="text-bold text-green">Moving</span>' ;
                                       break;
                             default : $status = '<span class="text-bold text-red">Stopped</span>' ;
            }

            $sql = "SELECT ue.*, 
                    e.eventname as eventname,
                    t.territoryname as territoryname 
                    FROM " . $udb . ".unit" . $uid . " ue 
                    LEFT JOIN crossbones.territory t ON t.territory_id = ue.landmark_id
                    LEFT JOIN unitmanagement.event e ON e.event_id = ue.event_id
                    WHERE ue.id IS NOT NULL 
                    ORDER BY ue.unittime DESC LIMIT 1";
            $unit = $this->db_read->fetchAssoc($sql, array());

            $days=0;
            $hours=0;
            $minutes=0;
            $out=null;
            $diff = strtotime(date('Y-m-d H:i:s')) - strtotime($unit['servertime']);
            while ($diff>86400){
                $days++;
                $diff = $diff - 86400;
            }
            while ($diff>3600){
                $hours++;
                $diff = $diff - 3600;
            }
            while ($diff>60){
                $minutes++;
                $diff = $diff - 60;
            }
            if ($days) {
                $out .= $days . '&nbsp;days&nbsp;' ;
            }
            if ($hours) {
                $out .= $hours . '&nbsp;hours&nbsp;'  ;
            }
            if ($minutes) {
                $out .= $minutes . '&nbsp;minutes&nbsp;' ;
            }
            if(!($out)){
                $out = 'now';
            } else {
                $out .= 'ago';
            }
            $servertimediff = $out;

            $days=0;
            $hours=0;
            $minutes=0;
            $out=null;
            $diff = strtotime(date('Y-m-d H:i:s')) - strtotime($unit['unittime']);
            while ($diff>86400){
                $days++;
                $diff = $diff - 86400;
            }
            while ($diff>3600){
                $hours++;
                $diff = $diff - 3600;
            }
            while ($diff>60){
                $minutes++;
                $diff = $diff - 60;
            }
            if ($days) {
                $out .= $days . '&nbsp;days&nbsp;' ;
            }
            if ($hours) {
                $out .= $hours . '&nbsp;hours&nbsp;'  ;
            }
            if ($minutes) {
                $out .= $minutes . '&nbsp;minutes&nbsp;' ;
            }
            if(!($out)){
                $out = 'now';
            } else {
                $out .= 'ago';
            }
            $unittimediff = $out;

            $result['eventname'] = $unit['eventname'] ;
            $result['formatted_address'] = $this->address_logic->validateAddress($unit['streetaddress'], $unit['city'], $unit['state'], $unit['zipcode'], $unit['country']);
            $result['latitude'] = $unit['latitude'] ;
            $result['longitude'] = $unit['longitude'] ;
            $result['servertime'] = $unit['servertime'] ;
            $result['servertimediff'] = $servertimediff ;
            $result['status'] = $status ;
            $result['territoryname'] = $unit['territoryname'] ;
            $result['unittime'] = $unit['unittime'] ;
            $result['unittimediff'] = $unittimediff ;

        }

        return $result;

    }

    /**
     * @return array
     */
    public function getBatchCommands()
    {
        $sql = "SELECT *
                FROM crossbones.batchcommand
                WHERE batchcommand_id > 0";
        // $res = $this->db_read->fetchAssoc($sql, array());
        $res = array();
        // $b['batchcommand_id'] = 1 ;
        // $b['batchcommand'] = 'Locate' ;
        // $res[] = $b;
        $b['batchcommand_id'] = 2 ;
        $b['batchcommand'] = 'Payment Reminder Off' ;
        $res[] = $b;
        $b['batchcommand_id'] = 3 ;
        $b['batchcommand'] = 'Payment Reminder On' ;
        $res[] = $b;
        $b['batchcommand_id'] = 4 ;
        $b['batchcommand'] = 'Starter Disable' ;
        $res[] = $b;
        $b['batchcommand_id'] = 5 ;
        $b['batchcommand'] = 'Starter Enable' ;
        $res[] = $b;
        return $res;
    }

    /**
     * Get the unit data and it's event info for the unit
     *
     * @param array $unit
     * @param int $unitevent_id
     *
     * @return bool
     */
     public function getUnitDb($unit_id)
     {
        $sql = "SELECT db as db
                FROM crossbones.unit
                WHERE unit_id = ?";
        $data = $this->db_read->fetchAssoc($sql, array($unit_id));
        return $data['db'];
     }

    /**
     * Get the unit data and it's event info for the unit
     *
     * @param array $unit
     * @param int $unitevent_id
     *
     * @return bool
     */
     public function getVehicleDataByLastEvent($database,$unit_id)
     {
        $sql = "SELECT *
                FROM {$database}.unit{$unit_id}
                WHERE id IS NOT NULL
                ORDER BY id DESC
                LIMIT 1";

        $data = $this->db_read->fetchAssoc($sql, array());
        $data['SQL'] = $sql;
        return $data;
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
        $unit_id = $unit['unit_id'];
        $database = $unit['db'];

        $sql = "SELECT *
                FROM {$database}.unit{$unit_id} AS ue
                LEFT JOIN unitmanagement.event AS e ON e.event_id = ue.event_id
                WHERE `id` = ?";

        $data = $this->db_read->fetchAssoc($sql, array($unitevent_id));
        return $data;
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
        $data = array();
        $where_device_status = '';
        $where_unit_status = '';
        $sqlPlaceHolder = array($unitgroup_id);

        /*
        if ($device_status == 1 AND $usertimezone != '') {
            // active device (device expiration date has not passed)
            $currentdatetime = Date::locale_to_utc(date('Y-m-d H:i:s'), $usertimezone);
            $where_device_status = ' AND ua.renewaldate > ?';
            $sqlPlaceHolder[] = $currentdatetime;
        } else if ($device_status == 2) {
            // not active device
            $currentdate = Date::locale_to_utc(date('Y-m-d H:i:s'), $usertimezone);
            $where_device_status = ' AND ua.renewaldate < ?';
            $sqlPlaceHolder[] = $currentdatetime;
        } else {
            // get every unit (0 = every unit)
        }
        */

        switch ($unit_status)
        {
            case 1: // "Installed"
                // fallthorugh
            case 2: // "Inventory"
                // fallthorugh
            case 3: // "Repossession"
                $where_unit_status = ' AND u.unitstatus_id = ?';
                $sqlPlaceHolder[] = $unit_status;
                break;
        }

        $sql = "SELECT
                    u.*,
                    ug.*,
                    ua.unitattribute_id AS unitattribute_id,
                    ua.vin AS vin,
                    ua.make AS make,
                    ua.model AS model,
                    ua.year AS year,
                    ua.color AS color,
                    ua.licenseplatenumber AS licenseplatenumber,
                    ua.loannumber AS loannumber,
                    ua.plan AS plan,
                    ua.purchasedate AS purchasedate,
                    ua.renewaldate AS renewaldate,
                    ua.lastrenewaldate AS lastrenewaldate
                FROM unit u
                LEFT JOIN unitgroup ug ON u.unitgroup_id = ug.unitgroup_id
                LEFT JOIN unitattribute ua ON u.unitattribute_id = ua.unitattribute_id
                WHERE ug.unitgroup_id = ? AND ug.active = 1{$where_device_status}{$where_unit_status}
                ORDER BY u.unitname ASC";

        $data = $this->db_read->fetchAll($sql, $sqlPlaceHolder);

        return $data;
    }

    /**
     * Get all account vehicle info by account id
     *
     * @params: account_id
     *
     * @return array
     */
    public function getVehicleDataInfoByAccountId($account_id)
    {
        $data = array();

        $sql = "SELECT
                    u.*,
                    uas.*,
                    ug.*,
                    u.unit_id AS unit_id,
                    ug.unitgroup_id AS unitgroup_id,
                    uas.alertevent_id AS alertevent_id,
                    uas.landmark_id AS previous_landmark_id,
                    uas.boundary_id AS previous_boundary_id,
                    utz.timezone AS unit_timezone
                FROM crossbones.unit u
                LEFT JOIN crossbones.unitgroup ug ON ug.unitgroup_id = u.unitgroup_id
                LEFT JOIN crossbones.unitalertstatus uas ON u.unit_id = uas.unit_id
                LEFT JOIN unitmanagement.timezone utz ON utz.timezone_id = u.timezone_id
                WHERE u.account_id = ?
                ORDER BY u.unitname ASC";

        $data = $this->db_read->fetchAll($sql, array($account_id));

        return $data;
    }

    /**
     * Get all account vehicle info by account id
     * NOTE: 'unit_id' is a unique key in this table
     *
     * @params: account_id
     *
     * @return array
     */
    public function updateUnitAlertStatus($unit_id, $params)
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

        $update = rtrim($update, ',');

        $sql_params = array_merge(array($unit_id), $values_arr, $values_arr);

        $sql = "INSERT INTO crossbones.unitalertstatus (unit_id{$columns})
                VALUES ({$values})
                ON DUPLICATE KEY UPDATE {$update}";

        if ($this->db_read->executeQuery($sql, $sql_params)) {
            return true;
        }

        return false;
    }

    public function getVehiclesByGroupIds($user_id, $vehicle_groups, $account_id)
    {

        $user['account_id'] = $account_id;
        $user['user_id'] = $user_id;
        // $permission = $this->ajaxPermissionCheck($user,'vehicles');

        if($account_id>0){

            $sql = "UPDATE crossbones.unit
                    SET lastactivationcheck = ? ,
                    lastmovecheck = ? ,
                    lastreportcheck = ?
                    WHERE account_id = ?";
            $result = $this->db_write->executeQuery($sql, array(null,null,null,$account_id));

            $sql_params = array($account_id,$user_id);
            $sql = "SELECT roles
                    FROM crossbones.user
                    WHERE account_id = ?
                    AND user_id = ?";
            $roles = $this->db_read->fetchAll($sql, $sql_params);

            if(($roles[0]['roles']=='ROLE_ACCOUNT_OWNER')||($permission)){
                $sql_params = array($account_id);
                $sql = "SELECT u.*,
                            u.unit_id as unit_id,
                            u.unitname as name,
                            u.db_records as db_records,
                            ug.unitgroupname as unitgroupname,
                            uo.currentodometer as currentodometer,
                            uo.initialodometer as initialodometer
                        FROM crossbones.unit u
                        LEFT JOIN crossbones.unitgroup ug ON ug.unitgroup_id = u.unitgroup_id
                        LEFT JOIN crossbones.unitodometer uo ON uo.unitodometer_id = u.unitodometer_id
                        LEFT JOIN crossbones.user_unitgroup uug ON uug.unitgroup_id = ug.unitgroup_id
                        LEFT JOIN crossbones.user user ON user.user_id = uug.user_id
                        WHERE u.account_id = ?
                        ORDER BY u.unitstatus_id ASC, u.unitname ASC";
            } else {
                $sql_params = array($account_id,$user_id);
                $sql = "SELECT u.*,
                            u.unit_id as unit_id,
                            u.unitname as name,
                            u.db_records as db_records,
                            ug.unitgroupname as unitgroupname,
                            uo.currentodometer as currentodometer,
                            uo.initialodometer as initialodometer
                        FROM crossbones.unit u
                        LEFT JOIN crossbones.unitgroup ug ON ug.unitgroup_id = u.unitgroup_id
                        LEFT JOIN crossbones.unitodometer uo ON uo.unitodometer_id = u.unitodometer_id
                        LEFT JOIN crossbones.user_unitgroup uug ON uug.unitgroup_id = ug.unitgroup_id
                        LEFT JOIN crossbones.user user ON user.user_id = uug.user_id
                        WHERE u.account_id = ?
                        AND uug.user_id = ?
                        ORDER BY u.unitstatus_id ASC, u.unitname ASC";
            }

        } else if ($user_id) {
            $sql_params = array($user_id);
            $sql = "SELECT u.*,
                        u.unit_id as unit_id,
                        u.db_records as db_records,
                        u.unitname as name
                    FROM crossbones.unit u
                    WHERE u.user_id = ?
                    ORDER BY u.unitstatus_id ASC, u.unitname ASC";
        }

        $results = $this->db_read->fetchAll($sql, $sql_params);

        foreach ( $results as $key => $record ) {
            if(($record['unit_id']>0)&&($record['unit_id']!=$last)){
                $last = $record['unit_id'] ;
                if(!($record['name'])){
                    $record['name'] = $record['serialnumber'] ;
                }
                $vehicles[] = $record ;
            }
        }

        return $vehicles;

    }

    public function getVehiclesByGroupId($user_id, $group_id)
    {
        $sql = "SELECT ug.*,
                    uod.*,
                    u.*,
                    u.unit_id as unit_id,
                    u.unitname as name
                FROM crossbones.unit u
                LEFT JOIN crossbones.unitgroup ug ON u.unitgroup_id = ug.unitgroup_id
                LEFT JOIN crossbones.user_unitgroup uug ON uug.unitgroup_id = ug.unitgroup_id
                LEFT JOIN crossbones.unitodometer uod ON uod.unitodometer_id = u.unitodometer_id
                WHERE uug.user_id = ? AND ug.unitgroup_id IN (?)
                ORDER BY u.unitname ASC";

        $vehicles = $this->db_read->fetchAll($sql, array($user_id, $group_id));

        return $vehicles;
    }

    /**
     * Get the vehicle groups info by user id
     *
     * @params: user_id, unitgroup_ids (optional array of unitgroup ids)
     *
     * @return array | bool
     */
    function getVehicleGroupsByUserId($user_id, $unitgroup_ids, $account_id)
    {

        $user['account_id'] = $account_id;
        $user['user_id'] = $user_id;
        $permission = $this->ajaxPermissionCheck($user,'vehiclegroups');

        $sql_params = array($account_id,$user_id);
        $sql = "SELECT roles
                FROM crossbones.user
                WHERE account_id = ?
                AND user_id = ?";
        $roles = $this->db_read->fetchAll($sql, $sql_params);

        if(($roles[0]['roles']=='ROLE_ACCOUNT_OWNER')||($permission)){

            $sql_params = array($account_id);
            $where_in = "";
            if (! empty($unitgroup_ids) AND is_array($unitgroup_ids)) {
                $where_in = "AND unitgroup_id IN (" . trim(str_repeat('?,', count($unitgroup_ids)), ',') . ") ";
                $sql_params = array_merge($sql_params, array_values($unitgroup_ids));
            }

            // $sql = "SELECT uug.*,
            //                 ug.*
            //         FROM user_unitgroup uug
            //         LEFT JOIN unitgroup ug ON uug.unitgroup_id = ug.unitgroup_id
            //         WHERE ug.account_id = ?
            //         AND ug.active = 1 {$where_in}
            //         ORDER BY ug.unitgroupname ASC";
            $sql = "SELECT *
                    FROM unitgroup
                    WHERE account_id = ?
                    AND active = 1 {$where_in}
                    ORDER BY unitgroupname ASC";

        } else {

            $sql_params = array($user_id);
            $where_in = "";
            if (! empty($unitgroup_ids) AND is_array($unitgroup_ids)) {
                $where_in = "AND ug.unitgroup_id IN (" . trim(str_repeat('?,', count($unitgroup_ids)), ',') . ") ";
                $sql_params = array_merge($sql_params, array_values($unitgroup_ids));
            }

            $sql = "SELECT uug.*,
                            ug.*
                    FROM user_unitgroup uug
                    LEFT JOIN unitgroup ug ON uug.unitgroup_id = ug.unitgroup_id
                    WHERE uug.user_id = ? AND ug.active = 1 {$where_in}
                    ORDER BY ug.unitgroupname ASC";

        }

        $data = $this->db_read->fetchAll($sql, $sql_params);
        return $data;
    }

    /**
     * Get the vehicle groups info by user id
     *
     * @params: user_id
     *
     * @return array | bool
     */
    function getVehicleGroupByName($unitgroupname, $account_id, $group_status = '')
    {
        $sqlPlaceHolder = array($account_id, $unitgroupname);

        $where_active = '';
        if (isset($group_status) AND $group_status != '') {
            $where_active = ' AND active = ?';
            $sqlPlaceHolder[] = $group_status;
        }

        $sql = "SELECT *
                FROM unitgroup
                WHERE account_id = ? AND unitgroupname = ?{$where_active}
                LIMIT 1";

        return $this->db_read->fetchAll($sql, $sqlPlaceHolder);
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
        $sql = "SELECT *
                FROM unitgroup
                WHERE account_id = ? AND active = 1
                ORDER BY unitgroupname ASC";

        $data = $this->db_read->fetchAll($sql, array($account_id));
        return $data;
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

        if (isset($account_id)){

            $where_device_status = '';
            $where_unit_status = '';
            $sqlPlaceHolder = array($account_id);

            if (isset($usertimezone) AND $usertimezone != '') {
                $timezone = $usertimezone;
            } else {
                $timezone = SERVER_TIMEZONE;
            }

            /*
            if ($device_status == 1) {
                // active device (device expiration date has not passed)
                $currentdatetime = Date::locale_to_utc(date('Y-m-d H:i:s'), $timezone);
                $where_device_status = ' AND ua.renewaldate > ?';
                $sqlPlaceHolder[] = $currentdatetime;
            } else if ($device_status == 2) {
                // not active device
                $currentdate = Date::locale_to_utc(date('Y-m-d H:i:s'), $timezone);
                $where_device_status = ' AND ua.renewaldate < ?';
                $sqlPlaceHolder[] = $currentdatetime;
            } else {
                // get every unit (0 = every unit)
            }
            */

            switch ($unit_status)
            {
                case 1: // "Installed"
                    // fallthorugh
                case 2: // "Inventory"
                    // fallthorugh
                case 3: // "Repossession"
                    $where_unit_status = ' AND u.unitstatus_id = ?';
                    $sqlPlaceHolder[] = $unit_status;
                    break;
            }

            if (isset($vehicle_group_id) AND strtolower($vehicle_group_id) !== 'all'){
                $where_vehicle_group_id_status = ' AND u.unitgroup_id = ?';
                $sqlPlaceHolder[] = $vehicle_group_id;
            }

            $sql = "SELECT ug.*,
                            u.*
                    FROM crossbones.unit u
                    LEFT JOIN crossbones.unitgroup ug ON u.unitgroup_id = ug.unitgroup_id
                    LEFT JOIN crossbones.unitattribute ua ON ua.unitattribute_id = u.unitattribute_id
                    WHERE u.account_id = ?{$where_device_status}{$where_unit_status}{$where_vehicle_group_id_status}
                    ORDER BY u.unitname ASC";

            $vehicles = $this->db_read->fetchAll($sql, $sqlPlaceHolder);

            return $vehicles;

        }

    }

    /**
     * Get the vehicle groups info by ids
     *
     * @params: user_id, group_id
     *
     * @return array | bool
     */
    function getVehicleGroupsById($account_id, $group_id)
    {
        $sql_params = array($account_id);
        $place_holders = trim(str_repeat('?,', count($group_id)), ',');
        $sql_params = array_merge($sql_params, array_values($group_id));

        $sql = "SELECT *
                FROM unitgroup
                WHERE account_id = ? AND unitgroup_id IN (".$place_holders.")
                ORDER BY unitgroupname ASC";

        $data = $this->db_read->fetchAll($sql, $sql_params);
        return $data;
    }

    /**
     * Get the vehicle groups info by ids
     *
     * @params: user_id, group_id
     *
     * @return array | bool
     */
    public function searchVehicleByName($user_id, $search_string, $searchfields)
    {
        $sql_params = array($user_id);

        $where_search_string = "";
        if (! empty($searchfields) AND is_array($searchfields)) {
            $where_search_string = "AND (";

            foreach ($searchfields as $key => $fieldname) {
                $where_search_string .= "`".$fieldname."` LIKE ? OR ";
                $sql_params[] = '%'.str_replace("_", "\_", $search_string).'%';
            }

    		$where_search_string = substr_replace($where_search_string, "", -4);
    		$where_search_string .= ")";
        }

        $sql = "SELECT u.*, u.unitname as name
                FROM unit u
                LEFT JOIN user_unitgroup uug ON uug.unitgroup_id = u.unitgroup_id
                WHERE uug.user_id = ? {$where_search_string}
                ORDER BY u.unitname ASC";

        $data = $this->db_read->fetchAll($sql, $sql_params);
        return $data;
    }

    /**
     * Get the vehicle groups info by name
     *
     * @params: account_id, search_string, searchfields
     *
     * @return array | bool
     */
    public function getFilteredVehicleGroupStringSearch($user_id, $search_string, $searchfields)
    {
        $sql_params = array($user_id);
        $where_search_string = "";
        if (! empty($searchfields) AND is_array($searchfields)) {
            $where_search_string = "AND (";

            foreach ($searchfields as $key => $fieldname) {
                $where_search_string .= "`".$fieldname."` LIKE ? OR ";
                $sql_params[] = '%'.str_replace("_", "\_", $search_string).'%';
            }

    		$where_search_string = substr_replace($where_search_string, "", -4);
    		$where_search_string .= ")";
        }

        $sql = "SELECT ug.*, COUNT(unit.unit_id) AS unitcount
                FROM unitgroup ug
                LEFT JOIN unit ON unit.unitgroup_id = ug.unitgroup_id
                LEFT JOIN user_unitgroup ugu ON ugu.unitgroup_id = ug.unitgroup_id
                WHERE ugu.user_id = ? AND ug.active = 1 {$where_search_string}
                GROUP BY ug.unitgroup_id
                ORDER BY ug.unitgroupname ASC";

        $data = $this->db_read->fetchAll($sql, $sql_params);

        return $data;
    }

    /**
     * Get the vehicle info by filtered paramaters
     *
     * @params: group_id, event_id, sort_by
     *
     * @return array
     */
    public function getFilteredVehicles($user_id, $vehicle_groups = array())
    {
        $sql_params = array($user_id);
        $where_group_in = "";

        if (isset($vehicle_groups) AND ! empty($vehicle_groups)) {
            $where_group_in = "AND u.unitgroup_id IN (" . trim(str_repeat('?,', count($vehicle_groups)), ',') . ") ";
            $sql_params = array_merge($sql_params, array_values($vehicle_groups));
        }

        $sql = "SELECT *, u.unitname as name
                FROM crossbones.unit u
                LEFT JOIN crossbones.unitgroup ug ON u.unitgroup_id = ug.unitgroup_id
                LEFT JOIN crossbones.user_unitgroup uug ON ug.unitgroup_id = uug.unitgroup_id
                WHERE uug.user_id = ? {$where_group_in}
                ORDER BY u.unitname ASC";

        $vehicles = $this->db_read->fetchAll($sql, $sql_params);

        return $vehicles;
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
     *	   The unitattribute_columns array should contain column names from the 'unitattribute' table in the
     *	   crossbones database. This example will return you the values from the unitattribute_id, unit_id,
     *	   vin, and make columns for the unit based on the unit_id.
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
    	$join_unitinstallation = $join_customer = $join_unitattribute = $join_unitgroup = $join_unitodometer = $columns = "";

    	if (is_array($unitgroup_columns) AND ! empty($unitgroup_columns)) {
	    	$join_unitgroup .= " LEFT JOIN crossbones.unitgroup ug ON ug.unitgroup_id = u.unitgroup_id";
	    	$unitgroup_columns = implode(", ug.", $unitgroup_columns);
	    	$columns .= ", ug.{$unitgroup_columns}";
    	}

    	if (is_array($unitattribute_columns) AND ! empty($unitattribute_columns)) {
	    	$join_unitattribute = "LEFT JOIN crossbones.unitattribute ua ON ua.unit_id = u.unit_id";
	    	$unitattribute_columns = implode(", ua.", $unitattribute_columns);
	    	$columns .= ", ua.{$unitattribute_columns}";
    	}

    	if (is_array($customer_columns) AND ! empty($customer_columns)) {
	    	$join_customer = "LEFT JOIN crossbones.customer c ON c.unit_id = u.unit_id";
	    	$customer_columns = implode(", c.", $customer_columns);
	    	$columns .= ", c.{$customer_columns}";
    	}

    	if (is_array($unitinstallation_columns) AND ! empty($unitinstallation_columns)) {
	    	$join_unitinstallation = "LEFT JOIN crossbones.unitinstallation ui ON ui.unit_id = u.unit_id";
	    	$unitinstallation_columns = implode(", ui.", $unitinstallation_columns);
	    	$columns .= ", ui.{$unitinstallation_columns}";
    	}

    	if (is_array($unitodometer_columns) AND ! empty($unitodometer_columns)) {
	    	$join_unitodometer = "LEFT JOIN crossbones.unitodometer uo ON uo.unitodometer_id = u.unitodometer_id";
	    	$unitodometer_columns = implode(", uo.", $unitodometer_columns);
	    	$columns .= ", uo.{$unitodometer_columns}";
    	}

        $sql = "SELECT u.*, us.unitstatusname{$columns}, utz.timezone AS unit_timezone
                FROM crossbones.unit u
                LEFT JOIN crossbones.unitstatus us ON us.unitstatus_id = u.unitstatus_id
                LEFT JOIN unitmanagement.timezone utz ON u.timezone_id = utz.timezone_id
                {$join_unitgroup}
                {$join_unitattribute}
                {$join_customer}
                {$join_unitinstallation}
                {$join_unitodometer}
                WHERE u.unit_id = ?";

        $data = $this->db_read->fetchAssoc($sql, array($unit_id));

        if(!($data['unitname'])) {
            $data['unitname'] = $data['serialnumber'];
        }

        $udb = $data['db'] ;

        $sql = "SELECT ue1.*, 
                t.territoryname as territoryname 
                FROM " . $udb . ".unit" . $unit_id . " ue1 
                LEFT JOIN crossbones.territory t ON t.territory_id = ue1.landmark_id
                WHERE ue1.id IS NOT NULL 
                ORDER BY ue1.unittime DESC LIMIT 1";
        $unit = $this->db_read->fetchAssoc($sql, array());

        $data['unit_streetaddress'] = $unit['streetaddress'];
        $data['unit_city'] = $unit['city'];
        $data['unit_state'] = $unit['state'];
        $data['unit_zipcode'] = $unit['zipcode'];
        $data['unit_country'] = $unit['country'];
        $data['latitude'] = $unit['latitude'];
        $data['longitude'] = $unit['longitude'];
        $data['territoryname'] = $unit['territoryname'];
        $data['unittime'] = $unit['unittime'];
        $data['servertime'] = $unit['servertime'];

        $sql = "SELECT ue1.*
                FROM " . $udb . ".unit" . $unit_id . " ue1 
                WHERE ue1.id IS NOT NULL 
                ORDER BY ue1.id ASC LIMIT 1";
        $u2 = $this->db_read->fetchAssoc($sql, array());

        $data['firstevent'] = $u2['servertime'];
        
        return $data;
    }

    /**
     * Support System Reports
     */
    public function systemActivations()
    {

        $year1 = date('Y-m-d 08:00:00', strtotime('-12 months'));
        $sql = "SELECT COUNT(ma.unit_id) as units,
                usp.simprovider as carrier,
                usp.simprovider_id as carrier_id
                FROM metrics.activation ma
                LEFT JOIN crossbones.unit cu ON cu.unit_id = ma.unit_id
                LEFT JOIN crossbones.simcard cs ON cs.simcard_id = cu.simcard_id
                LEFT JOIN unitmanagement.simcardinventory usi ON usi.simcardinventory_id = cs.simcardinventory_id
                LEFT JOIN unitmanagement.simprovider usp ON usp.simprovider_id = usi.provider_id
                WHERE cu.unitstatus_id > 0
                AND cu.unitstatus_id < 4
                AND ma.activation > '" . $year1 . "'
                GROUP BY usp.simprovider_id
                ORDER BY usp.simprovider ASC";
        $buf = $this->db_read->fetchAll($sql, array());
        $subtotal = 0 ;
        foreach ( $buf as $key => $carrier ) {
            $units = $carrier['units'] + 0 ;
            $subtotal = $subtotal + $units ;
            $buf[$key]['units'] = number_format ( $units , 0 , '.' , ',' ) ;
        }
        $total = $subtotal + $total ;
        $acts['1'] = $buf;

        $year2 = date('Y-m-d 08:00:00', strtotime('-24 months'));
        $sql = "SELECT COUNT(ma.unit_id) as units,
                usp.simprovider as carrier,
                usp.simprovider_id as carrier_id
                FROM metrics.activation ma
                LEFT JOIN crossbones.unit cu ON cu.unit_id = ma.unit_id
                LEFT JOIN crossbones.simcard cs ON cs.simcard_id = cu.simcard_id
                LEFT JOIN unitmanagement.simcardinventory usi ON usi.simcardinventory_id = cs.simcardinventory_id
                LEFT JOIN unitmanagement.simprovider usp ON usp.simprovider_id = usi.provider_id
                WHERE cu.unitstatus_id > 0
                AND cu.unitstatus_id < 4
                AND ma.activation < '" . $year1 . "'
                AND ma.activation > '" . $year2 . "'
                GROUP BY usp.simprovider_id
                ORDER BY usp.simprovider ASC";
        $buf = $this->db_read->fetchAll($sql, array());
        $subtotal = 0 ;
        foreach ( $buf as $key => $carrier ) {
            $units = $carrier['units'] + 0 ;
            $subtotal = $subtotal + $units ;
            $buf[$key]['units'] = number_format ( $units , 0 , '.' , ',' ) ;
        }
        $total = $subtotal + $total ;
        $acts['2'] = $buf;

        $out['acts'] = $acts;
        $out['acts_total'] = $total ;

        return $out;

    }

    /**
     * Support System Reports
     */
    public function systemAirs()
    {
        $sql = "SELECT cu.*,
                usp.simprovider as carrier,
                usp.simprovider_id as simprovider_id,
                COUNT(cu.unit_id) as device_count
                FROM crossbones.unit cu
                LEFT JOIN crossbones.simcard cs ON cs.simcard_id = cu.simcard_id
                LEFT JOIN unitmanagement.simcardinventory usi ON usi.simcardinventory_id = cs.simcardinventory_id
                LEFT JOIN unitmanagement.simprovider usp ON usp.simprovider_id = usi.provider_id
                WHERE cu.activation > 0
                GROUP BY usp.simprovider_id
                ORDER BY usp.simprovider ASC";
        $carriers = $this->db_read->fetchAll($sql, array());
        foreach ( $carriers as $key => $carrier ) {
            $device_count = $carrier['device_count'] + 0 ;
            $carriers[$key]['device_count'] = number_format ( $device_count , 0 , '.' , ',' ) ;
            $total = $total + $device_count ;
            $sql = "SELECT  COUNT(cu.unit_id) as count,
                    cu.subscription as plan,
                    cu.subscription as plan
                    FROM crossbones.unit cu
                    LEFT JOIN crossbones.simcard cs ON cs.simcard_id = cu.simcard_id
                    LEFT JOIN unitmanagement.simcardinventory usi ON usi.simcardinventory_id = cs.simcardinventory_id
                    LEFT JOIN unitmanagement.simprovider usp ON usp.simprovider_id = usi.provider_id
                    WHERE usp.simprovider_id = ?
                    GROUP BY cu.subscription
                    ORDER BY cu.subscription ASC";
            if(!($carrier['carrier'])){
                $carriers[$key]['carrier'] = 'Unknown' ;                
            }
            $plans = $this->db_read->fetchAll($sql, array($carrier['simprovider_id']));
            foreach ( $plans as $k => $plan ) {
                $plan_count = $plan['count'] + 0 ;
                $plans[$k]['count'] = number_format ( $plan_count , 0 , '.' , ',' ) ;
                $plans[$k]['percentage'] = floor ( ( $plan_count / $device_count ) * 10000 ) / 100 ;
                $plans[$k]['plan'] = str_replace(' Year', 'yr', $plan['plan']) ;
            }
            $carriers[$key]['plans'] = $plans ;
        }
        $out['airs'] = $carriers;
        $out['airs_total'] = $total;
        return $out;
    }

    /**
     * Support System Reports
     */
    public function systemDevices()
    {
        $out  = array();

        $today = date('Y-m-d 00:00:00', strtotime('-8 hours'));
        
        $threeDaysAgo = date('Y-m-d 00:00:00', strtotime('-80 hours'));

        $fourDaysAgo = date('Y-m-d 00:00:00', strtotime('-104 hours'));

        $sevenDaysAgo = date('Y-m-d 00:00:00', strtotime('-176 hours'));

        $fifteenDaysAgo = date('Y-m-d 00:00:00', strtotime('-368 hours'));

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //
        // $sql = "SELECT unit_id,
        //         db,
        //         db_records,
        //         lastreport,
        //         lastreportcheck
        //         FROM crossbones.unit
        //         WHERE unitstatus_id != 2
        //         AND account_id != 74
        //         AND (
        //                 ( lastreportcheck < '" . $today . "' OR lastreportcheck IS NULL )
        //                     OR 
        //                 ( lastreport < '" . $threeDaysAgo . "' OR lastreport = '0000-00-00 00:00:00' OR lastreport IS NULL )
        //             )
        //         LIMIT 5000";
        // $rows = $this->db_read->fetchAll($sql, array());
        // $out['fix'] = '0' ;
        // $out['lastfix'] = '0' ;
        // $out['sweep'] = '0' ;
        // foreach ( $rows as $key => $row ) {
        //     $out['sweep']++ ;
        //     if ( 
        //         ( $row['db_records'] < 2 ) 
        //         ||
        //         ( $row['lastreport'] == '0000-00-00 00:00:00' )
        //         ||
        //         ( ! ( $row['lastreport'] ) )
        //         ||
        //         (
        //             (strtotime($row['lastreport'])<strtotime($threeDaysAgo))
        //             &&
        //             (
        //                 ($row['lastreportcheck']=='0000-00-00 00:00:00')
        //                 ||
        //                 (!($row['lastreportcheck']))
        //             ) 
        //         )
        //     ){
        //         $sql = "SELECT servertime,
        //                 COUNT(id) as db_records
        //                 FROM " . $row['db'] . ".unit" . $row['unit_id'] . " 
        //                 ORDER BY servertime DESC
        //                 LIMIT 1";
        //         $buf = $this->db_read->fetchAssoc($sql, array());
        //         $sql = "UPDATE crossbones.unit
        //                 SET db_records = ? ,
        //                 lastreport = ? ,
        //                 lastreportcheck = now()
        //                 WHERE unit_id = ?";
        //         if ($this->db_read->executeQuery($sql, array($buf['db_records'],$buf['servertime'],$row['unit_id']))) {
        //             $out['lastfix'] = $row['unit_id'] . ': ' . $buf['servertime'] ;
        //             $out['fix']++ ;
        //         }
        //     } else if(strtotime($row['lastreportcheck'])<strtotime($threeDaysAgo)){
        //         $sql = "UPDATE crossbones.unit
        //                 SET lastreportcheck = now()
        //                 WHERE unit_id = ?";
        //         if ($this->db_read->executeQuery($sql, array($row['unit_id']))) {
        //             $out['lastfix'] = $row['unit_id'] . ': ' . $buf['servertime'] ;
        //             $out['fix']++ ;
        //         }
        //     }
        // }
        //
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $sql = "SELECT unitstatus_id,
                COUNT(unit_id) as counter
                FROM crossbones.unit
                WHERE unitstatus_id != 2
                AND account_id != 74 
                AND activation IS NOT NULL
                AND activation != ?";
        $buf = $this->db_read->fetchAll($sql, array('0000-00-00 00:00:00'));
        $active = $buf[0]['counter'] + 0 ;
        //
        $sql = "SELECT unitstatus_id,
                COUNT(unit_id) as counter
                FROM crossbones.unit
                WHERE unitstatus_id != 2
                AND account_id != 74 
                AND db_records > 1
                AND ( lastreport =  '0000-00-00 00:00:00' OR lastreport IS NULL )";
        $buf = $this->db_read->fetchAll($sql, array());
        $reporting0 = $buf[0]['counter'] + 0 ;
        //
        $sql = "SELECT unitstatus_id,
                COUNT(unit_id) as counter
                FROM crossbones.unit
                WHERE unitstatus_id != 2
                AND account_id != 74 
                AND db_records > 1
                AND lastreport !=  '0000-00-00 00:00:00'
                AND lastreport IS NOT NULL
                AND lastreport >= '" . $threeDaysAgo . "'";
        $buf = $this->db_read->fetchAll($sql, array());
        $reporting3 = $buf[0]['counter'] + 0 ;
        //
        $sql = "SELECT unitstatus_id,
                COUNT(unit_id) as counter
                FROM crossbones.unit
                WHERE unitstatus_id != 2
                AND account_id != 74 
                AND db_records > 1
                AND lastreport !=  '0000-00-00 00:00:00'
                AND lastreport IS NOT NULL
                AND lastreport >= '" . $sevenDaysAgo . "'";
        $buf = $this->db_read->fetchAll($sql, array());
        $reporting7 = $buf[0]['counter'] + 0 ;
        //
        $sql = "SELECT unitstatus_id,
                COUNT(unit_id) as counter
                FROM crossbones.unit
                WHERE unitstatus_id != 2
                AND account_id != 74 
                AND db_records > 1
                AND lastreport !=  '0000-00-00 00:00:00'
                AND lastreport IS NOT NULL
                AND lastreport >= '" . $fifteenDaysAgo . "'";
        $buf = $this->db_read->fetchAll($sql, array());
        $reporting15 = $buf[0]['counter'] + 0 ;
        //
        $sql = "SELECT unitstatus_id,
                COUNT(unit_id) as counter
                FROM crossbones.unit
                WHERE unitstatus_id != 2
                AND account_id != 74 
                AND db_records > 1
                AND lastreport !=  '0000-00-00 00:00:00'
                AND lastreport IS NOT NULL
                AND lastreport < '" . $threeDaysAgo . "'";
        $buf = $this->db_read->fetchAll($sql, array());
        $nonReporting3 = $buf[0]['counter'] + 0 ;
        //
        $sql = "SELECT unitstatus_id,
                COUNT(unit_id) as counter
                FROM crossbones.unit
                WHERE unitstatus_id != 2
                AND account_id != 74 
                AND db_records > 1
                AND lastreport !=  '0000-00-00 00:00:00'
                AND lastreport IS NOT NULL
                AND lastreport < '" . $sevenDaysAgo . "'";
        $buf = $this->db_read->fetchAll($sql, array());
        $nonReporting7 = $buf[0]['counter'] + 0 ;
        //
        $sql = "SELECT unitstatus_id,
                COUNT(unit_id) as counter
                FROM crossbones.unit
                WHERE unitstatus_id != 2
                AND account_id != 74 
                AND db_records > 1
                AND lastreport !=  '0000-00-00 00:00:00'
                AND lastreport IS NOT NULL
                AND lastreport < '" . $fifteenDaysAgo . "'";
        $buf = $this->db_read->fetchAll($sql, array());
        $nonReporting15 = $buf[0]['counter'] + 0 ;
        //
        $sql = "SELECT unitstatus_id,
                COUNT(unit_id) as counter
                FROM crossbones.unit
                WHERE reminderstatus = ?
                AND account_id = 74";
        $buf = $this->db_read->fetchAll($sql, array('On'));
        $reminderstatusUm = number_format ( $buf[0]['counter'] , 0 , '.' , ',' ) ;
        //
        $sql = "SELECT unitstatus_id,
                COUNT(unit_id) as counter
                FROM crossbones.unit
                WHERE starterstatus = ?
                AND account_id = 74";
        $buf = $this->db_read->fetchAll($sql, array('Disabled'));
        $starterstatusUm = number_format ( $buf[0]['counter'] , 0 , '.' , ',' ) ;
        //
        $sql = "SELECT unitstatus_id,
                COUNT(unit_id) as counter
                FROM crossbones.unit
                WHERE reminderstatus = ?
                AND account_id != 74";
        $buf = $this->db_read->fetchAll($sql, array('On'));
        $reminderstatus = number_format ( $buf[0]['counter'] , 0 , '.' , ',' ) ;
        //
        $sql = "SELECT unitstatus_id,
                COUNT(unit_id) as counter
                FROM crossbones.unit
                WHERE starterstatus = ?
                AND account_id != 74";
        $buf = $this->db_read->fetchAll($sql, array('Disabled'));
        $starterstatus = number_format ( $buf[0]['counter'] , 0 , '.' , ',' ) ;
        //
        $sql = "SELECT unitstatus_id,
                COUNT(unit_id) as counter
                FROM crossbones.unit
                WHERE account_id = 74
                GROUP BY unitstatus_id";
        $buf = $this->db_read->fetchAll($sql, array());
        $total = 0 ;
        foreach ( $buf as $key => $val ) {
            $total = $total + $val['counter'];
            switch($val['unitstatus_id']){
                case                 '1' :
                case                  1  :  $installedUm = $val['counter'] ;
                                            break; 
                case                 '2' :
                case                  2  :  $inventoryUm = $val['counter'] ;
                                            break; 
                case                 '3' :
                case                  3  :  $repossessionUm = $val['counter'] ;
                                            break; 
            }
        }
        $out['unitmanagement']['count'] = $total ;
        //
        $sql = "SELECT unitstatus_id,
                COUNT(unit_id) as counter
                FROM crossbones.unit
                WHERE account_id != 74
                GROUP BY unitstatus_id";
        $buf = $this->db_read->fetchAll($sql, array());
        $total = 0 ;
        foreach ( $buf as $key => $val ) {
            $total = $total + $val['counter'];
            switch($val['unitstatus_id']){
                case                 '1' :
                case                  1  :  $installed = $val['counter'] ;
                                            break; 
                case                 '2' :
                case                  2  :  $inventory = $val['counter'] ;
                                            break; 
                case                 '3' :
                case                  3  :  $repossession = $val['counter'] ;
                                            break; 
            }
        }
        $out['subscriber']['count'] = $total ;
        //
        $total = $out['subscriber']['count'] + $out['unitmanagement']['count'] ;
        //
        $out['devices_total'] = $total ;
        //
        $out['installed_um']['count']           = number_format ( $installedUm , 0 , '.' , ',' ) ;
        $out['installed_um']['percentage']      = floor ( ( $installedUm / $total ) * 10000 ) / 100 ;
        $out['inventory_um']['count']           = number_format ( $inventoryUm , 0 , '.' , ',' ) ;
        $out['inventory_um']['percentage']      = floor ( ( $inventoryUm / $total ) * 10000 ) / 100 ;
        $out['repossession_um']['count']        = number_format ( $repossessionUm , 0 , '.' , ',' ) ;
        $out['repossession_um']['percentage']   = floor ( ( $repossessionUm / $total ) * 10000 ) / 100 ;
        //
        $out['installed']['count']              = number_format ( $installed , 0 , '.' , ',' ) ;
        $out['installed']['percentage']         = floor ( ( $installed / $total ) * 10000 ) / 100 ;
        $out['inventory']['count']              = number_format ( $inventory , 0 , '.' , ',' ) ;
        $out['inventory']['percentage']         = floor ( ( $inventory / $total ) * 10000 ) / 100 ;
        $out['repossession']['count']           = number_format ( $repossession , 0 , '.' , ',' ) ;
        $out['repossession']['percentage']      = floor ( ( $repossession / $total ) * 10000 ) / 100 ;
        //
        //$total = $installed + $repossession ;
        $out['active']['count']                 = number_format ( $active , 0 , '.' , ',' ) ;
        $out['active']['percentage']            = floor ( ( $active / $total ) * 10000 ) / 100 ;
        //
        $out['reporting0']['count']              = number_format ( $reporting0 , 0 , '.' , ',' ) ;
        $out['reporting0']['percentage']         = floor ( ( $reporting0 / $active ) * 10000 ) / 100 ;
        //
        $out['reporting3']['count']              = number_format ( $reporting3 , 0 , '.' , ',' ) ;
        $out['reporting3']['percentage']         = floor ( ( $reporting3 / $active ) * 10000 ) / 100 ;
        $out['nonreport3']['count']              = number_format ( $nonReport3 , 0 , '.' , ',' ) ;
        $out['nonreport3']['percentage']         = floor ( ( $nonReport3 / $active ) * 10000 ) / 100 ;
        $no_report3 = $active - $reporting3 ;
        $out['no_report3']['count']              = number_format ( $no_report3 , 0 , '.' , ',' ) ;
        $out['no_report3']['percentage']         = floor ( ( $no_report3 / $active ) * 10000 ) / 100 ;
        //
        $out['reporting7']['count']              = number_format ( $reporting7 , 0 , '.' , ',' ) ;
        $out['reporting7']['percentage']         = floor ( ( $reporting7 / $active ) * 10000 ) / 100 ;
        $out['nonreport7']['count']              = number_format ( $nonReport7 , 0 , '.' , ',' ) ;
        $out['nonreport7']['percentage']         = floor ( ( $nonReport7 / $active ) * 10000 ) / 100 ;
        $no_report7 = $active - $reporting7 ;
        $out['no_report7']['count']              = number_format ( $no_report7 , 0 , '.' , ',' ) ;
        $out['no_report7']['percentage']         = floor ( ( $no_report7 / $active ) * 10000 ) / 100 ;
        //
        $out['reporting15']['count']              = number_format ( $reporting15 , 0 , '.' , ',' ) ;
        $out['reporting15']['percentage']         = floor ( ( $reporting15 / $active ) * 10000 ) / 100 ;
        $out['nonreport15']['count']              = number_format ( $nonReport15 , 0 , '.' , ',' ) ;
        $out['nonreport15']['percentage']         = floor ( ( $nonReport15 / $active ) * 10000 ) / 100 ;
        $no_report15 = $active - $reporting15 ;
        $out['no_report15']['count']              = number_format ( $no_report15 , 0 , '.' , ',' ) ;
        $out['no_report15']['percentage']         = floor ( ( $no_report15 / $active ) * 10000 ) / 100 ;
        //
        $out['reminderstatus_um']['count']      = number_format ( $reminderstatusUm , 0 , '.' , ',' ) ;
        $out['reminderstatus_um']['percentage'] = floor ( ( $reminderstatusUm / $total ) * 10000 ) / 100 ;
        $out['starterstatus_um']['count']       = number_format ( $starterstatusUm , 0 , '.' , ',' ) ;
        $out['starterstatus_um']['percentage']  = floor ( ( $starterstatusUm / $total ) * 10000 ) / 100 ;
        $out['reminderstatus']['count']         = number_format ( $reminderstatus , 0 , '.' , ',' ) ;
        $out['reminderstatus']['percentage']    = floor ( ( $reminderstatus / $total ) * 10000 ) / 100 ;
        $out['starterstatus']['count']          = number_format ( $starterstatus , 0 , '.' , ',' ) ;
        $out['starterstatus']['percentage']     = floor ( ( $starterstatus / $total ) * 10000 ) / 100 ;

        return $out;
    }

    /**
     * Support System Reports
     */
    public function systemLogins()
    {
        $today = date('Y-m-d 08:00:00', strtotime('-8 hours'));
        $sql = "SELECT *
                FROM metrics.login
                WHERE createdate >= '" . $today . "'
                GROUP BY url
                ORDER BY counter DESC";
        $buf = $this->db_read->fetchAll($sql, array());
        foreach ( $buf as $key => $val ) {
            $buf[$key]['date'] = $today;
            $counter = $buf[$key]['counter'] + 0;
            $buf[$key]['count'] = number_format ( $counter , 0 , '.' , ',' ) ;
        }
        return $buf;
    }

    /**
     * Support System Reports
     */
    public function systemNons()
    {
        $threeDaysAgo = date('Y-m-d 00:00:00', strtotime('-80 hours'));
        $fifteenDaysAgo = date('Y-m-d 00:00:00', strtotime('-368 hours'));
        $sql = "SELECT COUNT(u.unit_id) as devicecount,
                u.*,
                a.accountname as accountname,
                um.manufacturer as manufacturer
                FROM crossbones.unit u
                LEFT JOIN crossbones.account a ON a.account_id = u.account_id
                LEFT JOIN unitmanagement.unitmanufacturer um ON um.unitmanufacturer_id = u.unitmanufacturer_id
                WHERE u.unitstatus_id != 2
                AND u.account_id != 74
                AND u.activation IS NOT NULL
                AND u.activation != '0000-00-00 00:00:00'
                AND u.lastreport != '0000-00-00 00:00:00'
                AND ( u.lastreport < '" . $fifteenDaysAgo . "' OR u.lastreport IS NULL )
                GROUP BY a.account_id
                ORDER BY devicecount DESC, u.lastreport DESC";
        $accounts = $this->db_read->fetchAll($sql, array());
        $total = 0 ;
        $total_db_records = 0 ;
        foreach ( $accounts as $key => $val ) {
            $total = $total + $val['devicecount'] ;
            $total_db_records = $total_db_records + $val['db_records'] ;
        }
        foreach ( $accounts as $key => $val ) {
            $rows++;
            $sql = "SELECT COUNT(u.unit_id) as devicecount
                    FROM crossbones.unit u
                    WHERE u.unitstatus_id != 2
                    AND u.account_id = ?";
            $deployed = $this->db_read->fetchAll($sql, array($val['account_id']));
            // $sql = "SELECT u.*,
            //         a.accountname as accountname,
            //         um.manufacturer as manufacturer,
            //         us.unitstatusname as unitstatusname,
            //         uv.version as version
            //         FROM crossbones.unit u
            //         LEFT JOIN crossbones.account a ON a.account_id = u.account_id
            //         LEFT JOIN crossbones.unitstatus us ON us.unitstatus_id = u.unitstatus_id
            //         LEFT JOIN unitmanagement.unitmanufacturer um ON um.unitmanufacturer_id = u.unitmanufacturer_id
            //         LEFT JOIN unitmanagement.unitversion uv ON uv.unitversion_id = u.unitversion_id
            //         WHERE u.unitstatus_id != 2
            //         AND u.account_id = ?
            //         AND u.db_records > 1
            //         AND ( u.lastreport < '" . $threeDaysAgo . "' OR u.lastreport IS NULL )
            //         ORDER BY a.accountname ASC, u.lastreport DESC";
            // $units = $this->db_read->fetchAll($sql, array($val['account_id']));
            // $r = 0 ;
            // $account_db_records = 0 ;
            // foreach ( $units as $k => $v ) {
            //     $r++;
            //     $units[$k]['row'] = $r ;
            //     $db_records = $v['db_records'] + 0 ;
            //     $account_db_records = $account_db_records + $db_records ;
            //     $units[$k]['db_records'] = number_format ( $db_records , 0 , '.' , ',' ) ;
            // }
            $accounts[$key]['units'] = $units;
            $accounts[$key]['row'] = $rows;
            $accounts[$key]['percentage'] = floor ( ( $val['devicecount'] / $total ) * 10000 ) / 100 ;
            $accounts[$key]['db_records'] = number_format ( $account_db_records , 0 , '.' , ',' ) ;
            $accounts[$key]['deployed'] = $deployed[0]['devicecount'];
            $accounts[$key]['deployed_percentage'] = floor ( ( $val['devicecount'] / $deployed[0]['devicecount'] ) * 10000 ) / 100 ;
        }
        $accounts[0]['total'] = number_format ( $total , 0 , '.' , ',' ) ;
        $accounts[0]['total_db_records'] = number_format ( $total_db_records , 0 , '.' , ',' ) ;
        return $accounts;
    }

    /**
     * Support System Reports
     */
    public function systemUris()
    {
        $today = date('Y-m-d 00:00:00', strtotime('-8 hours'));
        $sql = "SELECT *
                FROM metrics.uri
                WHERE updated >= '" . $today . "'
                GROUP BY uri
                ORDER BY max DESC , counter DESC , uri ASC";
        $buf = $this->db_read->fetchAll($sql, array());
        foreach ( $buf as $key => $val ) {
            $counter = $val['counter'] + 0 ;
            $seconds = $val['seconds'] + 0 ;
            $buf[$key]['date'] = $today;
            $buf[$key]['counter'] = $counter;
            $buf[$key]['seconds'] = $seconds;
            $buf[$key]['ave'] = floor ( ( $seconds / $counter ) ) ;
            $buf[$key]['max'] = $val['max'] + 0;
            $buf[$key]['min'] = $val['min'] + 0;
        }
        return $buf;
    }

    /**
     * Support System Reports
     */
    public function systemUxs()
    {
        $today = date('Y-m-d 00:00:00', strtotime('-8 hours'));
        $sql = "SELECT *,
                COUNT(action_id) as counter,
                MIN(login) as firstlogin,
                MAX(login) as lastlogin
                FROM metrics.action
                WHERE updated >= '" . $today . "'
                GROUP BY browser
                ORDER BY counter DESC";
        $buf = $this->db_read->fetchAll($sql, array());
        foreach ( $buf as $key => $val ) {
            $buf[$key]['date'] = $today;
            $buf[$key]['count'] = $buf[$key]['counter'] + 0;
        }
        return $buf;
    }

    /**
     * Update the unit info by unit_id
     *
     * @params: unit_id, params
     *
     * @return array | bool
     */
    public function updateUnitInfo($unit_id, $params)
    {
        if ($this->db_read->update('crossbones.unit', $params, array('unit_id' => $unit_id))) {
            return true;
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
        $sql = "SELECT *
                FROM unitgroup
                WHERE account_id = ? AND active = 1 AND `default` = 1
                LIMIT 1";

        return $this->db_read->fetchAssoc($sql, array($account_id));
    }

    /**
     * Update the unitgroup info by unitgroup_id
     *
     * @params: unitgroup_id, params
     *
     * @return array | bool
     */
    public function updateVehicleGroupIds($user_id, $group_id, $devices)
    {
        $sql_params = array();
        $sql = "UPDATE crossbones.unit
                SET unitgroup_id = '{$group_id}'
                WHERE {$devices} ";

        if($this->db_read->executeQuery($sql, $sql_params)){
            return $sql;
            //return true;
        }
        return false;
    }

    /**
     * Update the unitgroup info by unitgroup_id
     *
     * @params: unitgroup_id, params
     *
     * @return array | bool
     */
    public function updateVehicleGroupInfo($unitgroup_id, $params)
    {
        if ($this->db_read->update('crossbones.unitgroup', $params, array('unitgroup_id' => $unitgroup_id))) {
            return true;
        }
        return false;
    }

    /**
     * Update the unit attribute by unit_id
     * NOTE: 'unit_id' is a unique key in this table
     *
     * @params: unit_id, params
     *
     * @return array | bool
     */
    public function updateUnitAttributeByUnitId($unit_id, $params)
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

        $update = rtrim($update, ',');

        $sql_params = array_merge(array($unit_id), $values_arr, $values_arr);

        $sql = "INSERT INTO crossbones.unitattribute (unit_id{$columns})
                VALUES ({$values})
                ON DUPLICATE KEY UPDATE {$update}";

        if ($this->db_read->executeQuery($sql, $sql_params)) {
            return true;
        }

        return false;
    }

    /**
     * Update the unit installation by unit_id
     * NOTE: 'unit_id' is a unique key in this table
     *
     * @params: unit_id, params
     *
     * @return array | bool
     */
    public function updateUnitInstallationByUnitId($unit_id, $params)
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

        $update = rtrim($update, ',');

        $sql_params = array_merge(array($unit_id), $values_arr, $values_arr);

        $sql = "INSERT INTO crossbones.unitinstallation (unit_id{$columns})
                VALUES ({$values})
                ON DUPLICATE KEY UPDATE {$update}";

        if ($this->db_read->executeQuery($sql, $sql_params)) {
            return true;
        }

        return false;
    }

    /**
     * Get all vehicle last event by unit info
     *
     * @param vehicle
     *
     * @return array|bool
     */
    public function getLastReportedEvent($vehicle)
    {
        $unit_id = $vehicle['unit_id'];
        $database = $vehicle['db'];

        $sql = "SELECT ue.*, e.eventname,
                       IF(t.active = 0, NULL, t.territoryname) AS territoryname
                FROM {$database}.unit{$unit_id} AS ue
                LEFT JOIN unitmanagement.event AS e ON e.event_id = ue.event_id
                LEFT JOIN crossbones.territory AS t ON t.territory_id = ue.landmark_id
                ORDER BY unittime desc
                LIMIT 1";

        $data = $this->db_read->fetchAssoc($sql);
        return $data;
    }

    /**
     * Get all vehicle last event by unit info
     *
     * @param vehicle
     *
     * @return array|bool
     */
    public function getLastReportedStopMoveEvent($vehicle)
    {
        $unit_id = $vehicle['unit_id'];
        $database = $vehicle['db'];

        $sql = "SELECT ue.*, e.eventname,
                       IF(t.active = 0, NULL, t.territoryname) AS territoryname
                FROM {$database}.unit{$unit_id} AS ue
                LEFT JOIN unitmanagement.event AS e ON e.event_id = ue.event_id
                LEFT JOIN crossbones.territory AS t ON t.territory_id = ue.landmark_id
                WHERE ue.event_id = '3'
                OR ue.event_id = '4'
                OR ue.event_id = '5'
                OR ue.event_id = '5'
                OR ue.event_id = '10'
                OR ue.event_id = '11'
                OR ue.event_id = '12'
                OR ue.event_id = '13'
                ORDER BY id desc
                LIMIT 1";

        $data = $this->db_read->fetchAssoc($sql);
        return $data;
    }

    /**
     * Get device capabilities
     *
     * @param vehicle
     *
     * @return array|bool
     */
    public function getUnitVersionFeatures($unit_id)
    {

        $sql = "SELECT uv.*, a.locate as locate, u.unit_id as unit_id
                FROM crossbones.unit as u
                LEFT JOIN crossbones.account AS a ON a.account_id = u.account_id
                LEFT JOIN unitmanagement.unitversion AS uv ON uv.unitmanufacturer_id = u.unitmanufacturer_id AND uv.unitversion_id = u.unitversion_id
                WHERE unit_id = '{$unit_id}'
                ORDER BY unit_id desc
                LIMIT 1";

        $data = $this->db_read->fetchAssoc($sql);

        return $data;

    }

    /**
     * Get a vehicle's event data by unit_id, database, event id
     *
     * @params: unit_id, database, event_id
     *
     * @return array | bool
     */
    public function getEventById($unit_id, $database, $event_id)
    {
        $sql = "SELECT ue.*,
                        e.eventname AS eventname,
                       IF(t.active = 0, NULL, t.territoryname) AS territoryname
                FROM {$database}.unit{$unit_id} AS ue
                LEFT JOIN unitmanagement.event AS e ON e.event_id = ue.event_id
                LEFT JOIN crossbones.territory AS t ON t.territory_id = ue.landmark_id
                WHERE `id` = ?
                LIMIT 1";

        $data = $this->db_read->fetchAssoc($sql, array($event_id));
        return $data;
    }

    /**
     * Get a unit's event info for the event_id
     *
     *	@params: event_id
     *
     * @return void
     */
    public function getEventInfoById($id)
    {
        // connect to unitmanagment db and pull event info from event table with provided id
        $sql = "SELECT *
                FROM unitmanagement.event
                WHERE event_id = ?
                LIMIT 1";

        $data = $this->db_read->fetchAssoc($sql, array($id));
        return $data;
    }

    /**
     * Get set of unit events that are categorized under a subset id
     *
     *	@params int $eventsubset_id
     *
     * @return array|bool
     */
    public function getEventsBySubsetId($eventsubset_id)
    {
        // connect to unitmanagment db and pull event for event subset id
       $sql = "SELECT
                    event.event_id as event_id,
                    event.eventname as eventname,
                    eventsubset_event.eventsubset_id
                FROM
                    unitmanagement.event
                INNER JOIN unitmanagement.eventsubset_event ON unitmanagement.event.event_id = unitmanagement.eventsubset_event.event_id
                WHERE
                    unitmanagement.eventsubset_event.eventsubset_id = ?";

        $data = $this->db_read->fetchAll($sql, array($eventsubset_id));
        return $data;
    }

    /**
     * Update a unit's group
     *
     *	@params: unit_id, group_id
     *
     * @return bool
     */
    public function updateAssignedVehicleGroup($unit_id, $group_id)
    {
        if ($this->db_read->update('crossbones.unit', array('unitgroup_id' => $group_id), array('unit_id' => $unit_id))) {
            return true;
        }
        return false;
    }

    /**
     * Get the vehicle history by unit_id
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
        $sql_params = array();
        $where_date = '';

        if (! empty($start_date) AND ! empty($end_date)) {
            $where_date = " AND ue.unittime >= ? AND ue.unittime < ?";
            $sql_params = array($start_date, $end_date);
        }

        $sql = "SELECT ue.*, e.eventname as eventname
                FROM {$event_db}.unit{$unit_id} AS ue
                LEFT JOIN unitmanagement.event AS e ON e.event_id = ue.event_id
                WHERE 1 {$where_date}
                ORDER BY unittime ASC";

        $data = $this->db_read->fetchAll($sql, $sql_params);

        return $data;
    }

    /**
     * Get all vehicle events for unit according to params
     *
     * @param $unit_id
     * @param $params event_db, start_date, end_date
     *
     * @return array|bool
     */
    public function getVehicleQuickHistory($unit_id, $params)
    {
        $sql_params     = array();
        $event_db       = $params['event_db'];
        $start_date     = $params['start_date'];
        $end_date       = $params['end_date'];
        $where_date     = '';

        if (! empty($start_date) AND ! empty($end_date)) {
            $where_date = " AND unittime >= ? AND unittime < ?";
            $sql_params = array($start_date, $end_date);
        }

        $sql = "SELECT ue.*,
                       IF(t.active = 0, NULL, t.territoryname) AS territoryname
                FROM {$event_db}.unit{$unit_id} AS ue
                LEFT JOIN crossbones.territory AS t ON t.territory_id = ue.landmark_id
                WHERE 1 {$where_date}
                ORDER BY unittime ASC";

        $data = $this->db_read->fetchAll($sql, $sql_params);

        return $data;
    }


    /**
     * Update Customer Info
     * NOTE: 'unit_id' is a unique key in this table
     *
     * @params int unit_id
     * @params array params
     *
     * @return bool
     */
    function updateCustomerInfo($unit_id, $params)
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

        $update = rtrim($update, ',');

        $sql_params = array_merge(array($unit_id), $values_arr, $values_arr);

        $sql = "INSERT INTO crossbones.customer (unit_id{$columns})
                VALUES ({$values})
                ON DUPLICATE KEY UPDATE {$update}";

        if ($this->db_read->executeQuery($sql, $sql_params)) {
            return true;
        }

        return false;
    }

    /**
     * Get all unit status
     *
     * @return array|bool
     */
    function getAllUnitStatus()
    {
        $sql = "SELECT *
                FROM crossbones.unitstatus";

	    $data = $this->db_read->fetchAll($sql);
	    return $data;
    }

    /**
     * Pull all events for a unit form the specified date range
     *
     * @return array|bool
     */
    public function getVehicleUnitEvents($unit, $from_date, $to_date)
	{
        $events = array();

		$database = $unit['db'];
		$unit_id = $unit['unit_id'];

		$sql_params = array($from_date, $to_date);

        if ( isset($unit['db']) AND ! empty($unit['db'])) {
            $sql = "SELECT *
                    FROM {$database}.unit{$unit_id} AS ue
                    WHERE ue.`unittime` >= ? AND ue.`unittime` < ?
                    ORDER BY ue.unittime ASC";

            $events = $this->db_read->fetchAll($sql, $sql_params);
        }

        return $events;
	}

    /**
     * Pull all events for a unit form the specified date range
     *
     * @return array|bool
     */
    public function getVehicleUnitEventsAfterId($unit, $event_rid, $limit = null)
	{
        $events = array();

		$database = $unit['db'];
		$unit_id = $unit['unit_id'];

        $event_limit = 500; //default limit

        if (isset($limit) AND is_numeric($limit) AND $limit > 0) {
            $event_limit = $limit;
        }

        if ( isset($unit['db']) AND ! empty($unit['db'])) {
            $sql = "SELECT *
                    FROM {$database}.unit{$unit_id} AS ue
                    WHERE ue.id > ?
                    ORDER BY ue.unittime ASC
                    LIMIT {$event_limit}";

            $events = $this->db_read->fetchAll($sql, array($event_rid));
        }

        return $events;
	}

    /**
     * Pull all events for a unit form the specified date range
     *
     * @return array|bool
     */
    public function getVehicleUnitEventsFromId($unit, $event_rid, $limit = null)
	{
        $events = array();

		$database = $unit['db'];
		$unit_id = $unit['unit_id'];

        $event_limit = 500; //default limit

        if (isset($limit) AND is_numeric($limit) AND $limit > 0) {
            $event_limit = $limit;
        }

        if ( isset($unit['db']) AND ! empty($unit['db'])) {
            $sql = "SELECT *
                    FROM {$database}.unit{$unit_id} AS ue
                    WHERE ue.id >= ?
                    ORDER BY ue.unittime ASC
                    LIMIT {$event_limit}";

            $events = $this->db_read->fetchAll($sql, array($event_rid));
        }

        return $events;
	}

    /**
     * Get all manufacturer events
     *
     * @return array|bool
     */
    public function getManufacturerEvents()
    {
        // connect to unitmanagment db and pull event info from event table with provided id
        $sql = "SELECT *
                FROM unitmanagement.event
                ORDER BY eventname";

        $data = $this->db_read->fetchAll($sql);
        return $data;
    }

    /**
     * Add vehicle group to user
     *
     * @param vehiclegroup_id
     * @param user_id
     *
     * @return bool|int
     */
    public function addVehicleGroupToUser($vehiclegroup_id, $user_id)
    {
        if ($this->db_write->insert('crossbones.user_unitgroup', array('unitgroup_id' => $vehiclegroup_id, 'user_id' => $user_id))) {
            return $this->db_write->lastInsertId();
        }
        return false;
    }

    /**
     * Add vehicle group to account
     *
     * @param array $params
     *
     * @return bool|int
     */
    public function addVehicleGroup($params)
    {
        if ($this->db_write->insert('crossbones.unitgroup', $params)) {
            return $this->db_write->lastInsertId();
        }
        return false;
    }

    /**
     * Remove vehicle group from user
     *
     * @param vehiclegroup_id
     * @param user_id
     *
     * @return bool|int
     */
    public function removeVehicleGroupFromUser($vehiclegroup_id, $user_id)
    {
        if ($this->db_write->delete('crossbones.user_unitgroup', array('unitgroup_id' => $vehiclegroup_id, 'user_id' => $user_id))) {
            return true;
        }
        return false;
    }

    /**
     * Get all account vehicles that are not assigned to any groups
     *
     * @param account_id
     *
     * @return bool|int
     */
    public function getVehiclesWithNoGroup($account_id)
    {
        $sql = "SELECT *
                FROM unit
                WHERE account_id = ? AND unitgroup_id = 0
                ORDER BY unitname ASC";

        return $this->db_read->fetchAll($sql, array($account_id));
    }

    /**
     * Remove all vehicles from unit group by unit group id
     *
     * @param unitgroup_id
     *
     * @return bool
     */
    public function removeAllVehiclesFromGroup($unitgroup_id, $default_group = 0)
    {
        if ($this->db_write->update('crossbones.unit', array('unitgroup_id' => $default_group), array('unitgroup_id' => $unitgroup_id)) !== false) {
            return true;
        }
        return false;
    }

    /**
     * Get the vehicle info by filtered paramaters
     *
     * @params: account_id, unitgroup_id search_string
     *
     * @return array
     */
    public function getFilteredAvailableVehicles($account_id, $unitgroup_id, $search_string, $search_fields)
    {
        $sqlPlaceHolder = array($account_id);

        $where_search_string = "";
        if (! empty($search_fields) AND is_array($search_fields)) {
            $where_search_string = " AND (";

            foreach ($search_fields as $key => $fieldname) {
                $where_search_string .= "`".$fieldname."` LIKE ? OR ";
                $sqlPlaceHolder[] = '%'.str_replace("_", "\_", $search_string).'%';
            }

    		$where_search_string = substr_replace($where_search_string, "", -4);
    		$where_search_string .= ")";
        }

        $where_group_in = "";
        if (isset($unitgroup_id) AND $unitgroup_id != '') {
            $where_group_in = " AND unit.unitgroup_id = ? ";
            $sqlPlaceHolder[] = $unitgroup_id;
        }

        $sql = "SELECT *
                FROM unit
                WHERE account_id = ?{$where_search_string}{$where_group_in}
                ORDER BY unitname ASC";

        $data = $this->db_read->fetchAll($sql, $sqlPlaceHolder);

        return $data;
    }

    /**
     * Get the users by vehicle group id
     *
     * @params: group_id
     *
     * @return array
     */
    public function getUsersByVehicleGroupId($account_id, $group_id)
    {
        $sql = "SELECT u.*, CONCAT(u.firstname, ' ', u.lastname) AS fullname, ug.*
                FROM user AS u
                LEFT JOIN user_unitgroup AS uug ON uug.user_id = u.user_id
                LEFT JOIN unitgroup as ug ON ug.unitgroup_id = uug.unitgroup_id
                WHERE u.account_id = ? 
                AND u.userstatus_id > 0
                AND uug.unitgroup_id = ?
                ORDER BY fullname ASC";

        $data = $this->db_read->fetchAll($sql, array($account_id, $group_id));
        return $data;
    }

    /**
     * Get the filtered contacts by params (string search)
     *
     * @params int account_id
     * @params array $params
     * @params array $searchfields
     *
     * @return array | bool
     */
    public function getFilteredDeviceStringSearch($account_id, $params, $searchfields)
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

        		$where_search_string = substr_replace($where_search_string, "", -4);
        		$where_search_string .= ")";
            }
        }

        $sql = "SELECT *, u.unit_id as unit_id, u.unitname as name
                FROM unit u
                LEFT JOIN unitgroup ug ON ug.unitgroup_id = u.unitgroup_id
                LEFT JOIN unitattribute ua ON u.unit_id = ua.unit_id
                LEFT JOIN unitstatus us ON u.unitstatus_id = us.unitstatus_id
                LEFT JOIN unitinstallation ui ON u.unitinstallation_id = ui.unitinstallation_id
                WHERE u.account_id = ? AND us.active = 1 {$where_search_string}
                GROUP BY u.unit_id
                ORDER BY u.unitname ASC";

        $units = $this->db_read->fetchAll($sql, $sql_params);
        return $units;
    }

    /**
     * Get the filtered contacts by $params
     *
     * @params: int account_id
     * @params: array $params
     *
     * @return array | bool
     */
    public function getFilteredDeviceList($account_id, $params)
    {
        $sql_params = array($account_id);

        $where_group_in = "";
        if (isset($params['unitgroup_id']) AND ! empty($params['unitgroup_id'])) {
            $where_group_in = "AND u.unitgroup_id IN (" . trim(str_repeat('?,', count($params['unitgroup_id'])), ',') . ") ";
            $sql_params = array_merge($sql_params, array_values($params['unitgroup_id']));
        }

        $where_status_in = "";
        if (isset($params['unitstatus_id']) AND ! empty($params['unitstatus_id'])) {
            $where_status_in = "AND u.unitstatus_id IN (" . trim(str_repeat('?,', count($params['unitstatus_id'])), ',') . ") ";
            $sql_params = array_merge($sql_params, array_values($params['unitstatus_id']));
        }

        $sql = "SELECT *, u.unit_id as unit_id, u.unitname as name
                FROM unit u
                LEFT JOIN unitgroup ug ON ug.unitgroup_id = u.unitgroup_id
                LEFT JOIN unitattribute ua ON u.unit_id = ua.unit_id
                LEFT JOIN unitstatus us ON u.unitstatus_id = us.unitstatus_id
                LEFT JOIN unitinstallation ui ON u.unitinstallation_id = ui.unitinstallation_id
                WHERE u.account_id = ? AND us.active = 1 {$where_group_in} {$where_status_in}
                GROUP BY u.unit_id
                ORDER BY u.unitname ASC";

        $units = $this->db_read->fetchAll($sql, $sql_params);
        return $units;
    }

    /**
     * Get all unitstatus
     *
     * @return bool
     */
    public function getDeviceStatus()
    {
        $sql = "SELECT *
                FROM unitstatus
                ORDER BY unitstatusname ASC";

        $data = $this->db_read->fetchAll($sql);

        return $data;
    }

    /**
     * Get the vehicle's last 10 reported ignition off events
     *
     * @params: array unit, ignition_off
     *
     * @return void
     */
    public function getLastTenVehicleOffEvents($unit, $ignition_off)
    {
        $events     = array();
		$database   = $unit['db'];
		$unit_id    = $unit['unit_id'];
		$place_holders = '?';

		$sql_params = array(2);

        if (isset($ignition_off) AND ! empty($ignition_off)) {
            $place_holders = trim(str_repeat('?,', count($ignition_off)), ',');
            $sql_params = array_values($ignition_off);
        }

        if ( isset($unit['db']) AND ! empty($unit['db'])) {
            $sql = "SELECT *
                    FROM {$database}.unit{$unit_id} AS ue
                    WHERE ue.event_id IN (".$place_holders.")
                    ORDER BY ue.unittime DESC
                    LIMIT 11";

            $events = $this->db_read->fetchAll($sql, $sql_params);
        }

        return $events;
    }

    /**
     * Get the vehicle's last drive event
     *
     * @params: array unit, drive_events
     *
     * @return void
     */
    public function getVehicleLastDriveEvent($unit, $drive_events)
    {
        $events     = array();
		$database   = $unit['db'];
		$unit_id    = $unit['unit_id'];
		$place_holders = '?';
		$sql_params = array(3);

        $from_date = date('Y-m-d H:i:s', mktime(0, 0, 0, (date("m") - 6), date("d"), date("Y")));

        if (isset($drive_events) AND ! empty($drive_events)) {
            $place_holders = trim(str_repeat('?,', count($drive_events)), ',');
            $sql_params = array_values($drive_events);
        }

        $sql_params = array_merge($sql_params, array($from_date));

        if ( isset($unit['db']) AND ! empty($unit['db'])) {
            $sql = "SELECT *
                    FROM {$database}.unit{$unit_id} AS ue
                    WHERE ue.event_id IN (".$place_holders.") AND ue.unittime > ?
                    ORDER BY ue.unittime DESC
                    LIMIT 1";

            $events = $this->db_read->fetchAssoc($sql, $sql_params);
            if (is_array($events) AND !empty($events)) {
                return $events;
            } else {
                return array('id' => '0');
            }

            return $events;
        }

        return false;
    }

    /**
     * Get vehicle info report
     *
     * @return bool|array
     */
    public function getVehicleInfoReport($account_id)
    {
        $sql = "SELECT ug.*,
                       us.*,
                       ua.*,
                       ui.*,
                       c.*,
                       s.*,
                       si.*,
                       sp.*,
                       u.*
                FROM crossbones.unit AS u
                LEFT JOIN crossbones.unitgroup AS ug ON ug.unitgroup_id = u.unitgroup_id
                LEFT JOIN crossbones.unitstatus AS us ON us.unitstatus_id = u.unitstatus_id
                LEFT JOIN crossbones.unitattribute AS ua ON ua.unit_id = u.unit_id
                LEFT JOIN crossbones.unitinstallation AS ui ON ui.unit_id = u.unit_id
                LEFT JOIN crossbones.customer AS c ON c.unit_id = u.unit_id
                LEFT JOIN crossbones.simcard AS s ON s.simcard_id = u.simcard_id
                LEFT JOIN unitmanagement.simcardinventory AS si ON si.simcardinventory_id = s.simcardinventory_id
                LEFT JOIN unitmanagement.simprovider AS sp ON sp.simprovider_id = si.provider_id
                WHERE u.account_id = ?
                ORDER BY u.unitname ASC";

        return $this->db_read->fetchAll($sql, array($account_id));
    }

    public function getVehicleMileageByAccountId($account_id, $filter_params)
    {
        $params = array($account_id);
        $where_filter = "";
        if (! empty($filter_params) AND is_array($filter_params)) {
            if (isset($filter_params['total_miles'])) {
                $where_filter .= " AND (uo.initialodometer + uo.currentodometer) > ?";
                $params[] = $filter_params['total_miles'];
            }

            if (isset($filter_params['unit_id'])) {
                $where_filter .= " AND u.unit_id = ?";
                $params[] = $filter_params['unit_id'];
            }

            if (isset($filter_params['unitgroup_id'])) {
                $where_filter .= " AND u.unitgroup_id = ?";
                $params[] = $filter_params['unitgroup_id'];
            }
            /*
            if (isset($filter_params['active']) AND ! empty($filter_params['user_timezone'])) {
                if ($filter_params['active'] == 1) {
                    // active device (device expiration date has not passed)
                    $currentdatetime = Date::locale_to_utc(date('Y-m-d H:i:s'), $filter_params['user_timezone']);
                    $where_filter .= ' AND ua.renewaldate > ?';
                    $params[] = $currentdatetime;
                } else if ($filter_params['active'] == 2) {
                    // not active device
                    $currentdate = Date::locale_to_utc(date('Y-m-d H:i:s'), $filter_params['user_timezone']);
                    $where_filter .= ' AND ua.renewaldate < ?';
                    $params[] = $currentdatetime;
                }
            }*/
        }

        $sql = "SELECT u.unit_id AS unit_id,
                       u.unitname AS unitname,
                       u.account_id AS account_id,
                       uo.*,
                       (uo.initialodometer + uo.currentodometer) AS total_mileage
                FROM crossbones.unit AS u
                LEFT JOIN crossbones.unitodometer AS uo ON uo.unitodometer_id = u.unitodometer_id
                LEFT JOIN crossbones.unitattribute AS ua ON ua.unitattribute_id = u.unitattribute_id
                WHERE u.account_id = ? {$where_filter}
                ORDER BY total_mileage DESC";

        return $this->db_read->fetchAll($sql, $params);
    }

    public function getVehicleInfoWhere($account_id, $filter_params)
    {
        $params = array($account_id);
        $where_filter = "";
        if (! empty($filter_params) AND is_array($filter_params)) {
            //if (isset($filter_params['starterstatus'])) {
            //    $where_filter .= " AND starterstatus = ?";
            //    $params[] = $filter_params['starterstatus'];
            //}

            if (isset($filter_params['unit_id'])) {
                $where_filter .= " AND u.unit_id = ?";
                $params[] = $filter_params['unit_id'];
            }

            if (isset($filter_params['unitgroup_id'])) {
                $where_filter .= " AND u.unitgroup_id = ?";
                $params[] = $filter_params['unitgroup_id'];
            }

            if (isset($filter_params['active']) AND ! empty($filter_params['user_timezone'])) {
                if ($filter_params['active'] == 1) {
                    // active device (device expiration date has not passed)
                    $currentdatetime = Date::locale_to_utc(date('Y-m-d H:i:s'), $filter_params['user_timezone']);
                //    $where_filter .= ' AND ua.renewaldate > ?';
                //    $params[] = $currentdatetime;
                } else if ($filter_params['active'] == 2) {
                    // not active device
                    $currentdate = Date::locale_to_utc(date('Y-m-d H:i:s'), $filter_params['user_timezone']);
                //    $where_filter .= ' AND ua.renewaldate < ?';
                //    $params[] = $currentdatetime;
                }
            }
        }

        $sql = "SELECT ua.*, u.*
                FROM crossbones.unit AS u
                LEFT JOIN crossbones.unitattribute AS ua ON ua.unitattribute_id = u.unitattribute_id
                WHERE account_id = ? {$where_filter}
                ORDER BY unitname ASC";

        return $this->db_read->fetchAll($sql, $params);
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
        // active device (device expiration date has not passed)
        //$currentdatetime = Date::locale_to_utc(date('Y-m-d H:i:s'), SERVER_TIMEZONE);

        $sql = "SELECT ua.*,
                        u.*
                FROM crossbones.unit u
                LEFT JOIN unitattribute ua ON ua.unitattribute_id = u.unitattribute_id
                WHERE u.account_id = ?
                ORDER BY u.unit_id";// AND ua.renewaldate > ?

        return $this->db_read->fetchAll($sql, array($account_id));//, $currentdatetime));
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
        // active device (device expiration date has not passed)
        //$currentdatetime = Date::locale_to_utc(date('Y-m-d H:i:s'), SERVER_TIMEZONE);

        $sql = "SELECT ua.*,
                        uo.*,
                        u.*
                FROM crossbones.unit u
                LEFT JOIN unitattribute ua ON ua.unitattribute_id = u.unitattribute_id
                LEFT JOIN unitodometer uo ON uo.unitodometer_id = u.unitodometer_id
                WHERE u.account_id = ? AND u.unitodometer_id > 0
                ORDER BY u.unit_id";// AND ua.renewaldate > ?

        return $this->db_read->fetchAll($sql, array($account_id));//, $currentdatetime));
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
        if ($this->db_write->insert('crossbones.unitodometer', $params)) {
            return $this->db_write->lastInsertId();;
        }
        return false;
    }

    /**
     * Update unit odometer
     *
     * @param int unitodometer_id
     * @param array params
     *
     * @return bool
     */
    public function updateUnitOdometer($unitodometer_id, $params)
    {
        if ($this->db_write->update('crossbones.unitodometer', $params, array('unitodometer_id' => $unitodometer_id)) !== false) {
            return true;
        }
        return false;
    }

    /**
     * Delete unit odometer
     *
     * @param int unitodometer_id
     * @param array params
     *
     * @return bool
     */
    public function deleteUnitOdometer($unitodometer_id)
    {
        if ($this->db_write->delete('crossbones.unitodometer', array('unitodometer_id' => $unitodometer_id)) !== false) {
            return true;
        }
        return false;
    }

    /**
     * Get unit odometer
     *
     * @param int unitodometer_id
     * @param array params
     *
     * @return bool
     */
    public function getUnitOdometer($unitodometer_id)
    {
    	$sql = "SELECT *
                FROM crossbones.unitodometer
                WHERE unitodometer_id = ?";

        return $this->db_read->fetchAssoc($sql, array($unitodometer_id));
    }

    public function getEventByIdForUnitSince( $unit_id, $unit_db, $since_datetime, $event_id , $unit_msisdn)
    {

        switch ($event_id) {

            // 
            // LOCATE ON DEMAND + TWILIO Workaround - Todd Bagley
            // 
            case         7 :
            case       999 :    $sql = "SELECT * FROM {$unit_db}.unit{$unit_id} AS ue 
                                        WHERE ( ue.event_id = '7'
                                        OR ue.event_id = '999' )
                                        AND ue.servertime > DATE_SUB(now(), INTERVAL 21 SECOND)
                                        ORDER BY ue.servertime DESC
                                        LIMIT 1";
                                return $this->db_read->fetchAssoc($sql, array());
                                break;

            // 
            // Starter Enable/Disable + TWILIO Workaround - Todd Bagley
            // 
            case        28 :
            case        29 :    $sql = "SELECT * FROM {$unit_db}.unit{$unit_id} AS ue 
                                        WHERE ( ue.event_id = '28'
                                        OR ue.event_id = '29' )
                                        AND ue.servertime > DATE_SUB(now(), INTERVAL 21 SECOND)
                                        ORDER BY ue.servertime DESC
                                        LIMIT 1";
                                return $this->db_read->fetchAssoc($sql, array());
                                break;

            // 
            // Payment Reminder ON/OFF + TWILIO Workaround - Todd Bagley
            // 
            case       109 :
            case       110 :    if (isset($unit_msisdn)) {
                                    $sql = "SELECT * FROM sms.in AS sms 
                                            WHERE sms.msisdn = '{$unit_msisdn}'
                                            AND ( sms.message LIKE '%,3011,0%' OR sms.message LIKE '%,3011,1%' OR sms.message LIKE '%,3011,2%' )
                                            AND sms.createdate > DATE_SUB(now(), INTERVAL 21 SECOND)
                                            ORDER BY sms.in_id DESC
                                            LIMIT 1";
                                    return $this->db_read->fetchAssoc($sql, array());
                                } else {
                                    return false;
                                }
                                break;

                    default:    $sql = "SELECT * FROM {$unit_db}.unit{$unit_id} AS ue 
                                        WHERE ue.event_id = ? 
                                        AND ue.servertime > DATE_SUB(now(), INTERVAL 21 SECOND)
                                        LIMIT 1";
                                return $this->db_read->fetchAssoc($sql, array($event_id));
                                // return $this->db_read->fetchAssoc($sql, array($event_id,$since_datetime));

        }

    }

    /**
     * Process Command Batch Import
     *
     * @return array
     */
    public function importCommandBatch($account_id, $user_id, $file_path)
    {
        $expected_columns = 3 ;
        $separator = ',' ;
        $enclosure = '"' ;

        $buffer = array();
        $buffer['account_id'] = $account_id;
        $buffer['user_id'] = $user_id;
        $buffer['file_path'] = $file_path;

        if (strlen($file_path) > 3) {

            $csv_reader= new CSVReader();
            $csv_reader->setSeparator($separator);
            $csv_reader->setEnclosure($enclosure);
            $csv_reader->setMaxRowSize(0);
            $csv_reader->setFile($file_path, $expected_columns, true);

            for ($i = 0; $row = $csv_reader->parseFileByLine(); $i++) {

                if($i>0){

                    $buffer['attempt_debug_loop']++;
                    $search = $row['device(gpsserialnumber/unitname/vin)'] ;
                    $reminder_on = $row['reminderon(datetoturnreminderon)'] ;
                    $starter_disable = $row['starterdisable(datetodisablestarter)'] ;

                    $sql = "SELECT unit.* 
                            FROM crossbones.unit unit
                            LEFT JOIN crossbones.account account ON account.account_id = unit.account_id
                            LEFT JOIN crossbones.user user ON user.account_id = account.account_id
                            LEFT JOIN crossbones.unitattribute ua ON ua.unit_id = unit.unit_id
                            WHERE unit.account_id = ?
                            AND user.user_id = ? 
                            AND ( 
                                unit.serialnumber LIKE '%" . $search . "' 
                                OR unit.serialnumber LIKE '%" . $search . "%' 
                                OR unit.unitname LIKE '%" . $search . "' 
                                OR unit.unitname LIKE '%" . $search . "%' 
                                OR ua.vin LIKE '%" . $search . "' 
                                OR ua.vin LIKE '%" . $search . "%' 
                            )
                            ORDER BY unit.serialnumber DESC , unit.unitname DESC , ua.vin DESC
                            LIMIT 1";

                    $res = $this->db_read->fetchAll($sql, array($account_id,$user_id));
                                            
                    $unit_id = $res[0]['unit_id'] ;

                    if($unit_id>0){
                        $buffer['process_debug_loop']++;

                        $ts = explode ('/',$reminder_on) ;
                        if(!($ts[0])){
                            $ts[0] = date('m');
                        }
                        if(!($ts[1])){
                            $ts[1] = date('d');
                        }
                        if(!($ts[2])){
                            $ts[2] = date('Y');
                        }
                        $reminder_on = $ts[2] . '-' . $ts[0] . '-' . $ts[1] . ' 00:00:00' ;

                        $ts = explode ('/',$starter_disable) ;
                        if(!($ts[0])){
                            $ts[0] = date('m');
                        }
                        if(!($ts[1])){
                            $ts[1] = date('d');
                        }
                        if(!($ts[2])){
                            $ts[2] = date('Y');
                        }
                        $starter_disable = $ts[2] . '-' . $ts[0] . '-' . $ts[1] . ' 00:00:00' ;

                        $sql = "SELECT *
                                FROM crossbones.commandpending
                                WHERE unit_id = ?";

                        $res = $this->db_read->fetchAll($sql, array($unit_id));

                        if($res){

                            $sql = "UPDATE crossbones.commandpending
                                    SET active = ?,
                                    reminder_off = now(),
                                    reminder_off_processed = ?,
                                    reminder_on = ?,
                                    reminder_on_processed = ?,
                                    starter_disable = ?,
                                    starter_disable_processed = ?,
                                    starter_enable = now(),
                                    starter_enable_processed = ?,
                                    user_id = ?
                                    WHERE unit_id = ?";

                            $sql = "UPDATE crossbones.commandpending
                                    SET active = ''
                                    WHERE unit_id = ?";

                            if ($this->db_write->executeQuery($sql, array($unit_id))) {
                            }

                        } else {

                            $sql = "INSERT INTO crossbones.commandpending ( reminder_off , reminder_on , starter_disable , starter_enable , user_id , unit_id ) VALUES ( now() , ? , ? , now() , ? , ? )";

                            if ($this->db_write->executeQuery($sql, array($reminder_on,$starter_disable,$user_id,$unit_id))) {
                            }

                        }

                        $buf = 'row_' . $i ;
                        $buffer[$buf] = $res[0]['unit_id'] ;
                    }

                }

            }

        }

        return $buffer ;
    }

}
