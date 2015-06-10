<?php

namespace Models\Data;

use Models\Logic\AddressLogic;
use Models\Logic\BaseLogic;
use Models\Data\BaseData;
use GTC\Component\Utils\Date;

class TerritoryData extends BaseData
{
    private $territoryType = array('landmark','reference','boundary');
    
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();

        $this->address_logic    = new AddressLogic;
        $this->base_logic       = new BaseLogic;
    }

    /**
     *  API Models Gateway
     */
    public function apiPartnerKey($apiKey)
    {

        $sql = "SELECT api_id,
                partner 
                FROM crossbones.api
                WHERE partner_key = ?
                AND active != 1
                LIMIT 1";

        return $this->db_read->fetchAll($sql, array($apiKey));

    }

    /**
     *  API Models Gateway
     */
    public function apiSubscriberKey($apiKey)
    {

        $sql = "SELECT api.account_id as api_account_id,
                api.user_id as api_user_id,
                a.account_id,
                u.user_id 
                FROM crossbones.api api
                LEFT JOIN crossbones.user u ON u.user_id = api.user_id
                LEFT JOIN crossbones.account a ON a.account_id = u.account_id
                WHERE api.subscriber_key = ?
                AND api.active != 1
                LIMIT 1";

        return $this->db_read->fetchAll($sql, array($apiKey));

    }

    /**
     *  API Models Gateway
     */
    public function apiSerialNumber($serialnumber)
    {

        $sql = "SELECT
                    unit_id
                FROM crossbones.unit
                WHERE serialnumber = ?";

        $db = $this->db_read->fetchAll($sql, array($serialnumber));

        return $db[0]['unit_id'] ;

    }

    /**
     *  API Models Gateway
     */
    public function api($apiKey,$script,$params,$boundingbox,$metric)
    {

        $sql = "SELECT api.account_id as api_account_id,
                api.user_id as api_user_id,
                a.account_id,
                u.user_id 
                FROM crossbones.api api
                LEFT JOIN crossbones.user u ON u.user_id = api.user_id
                LEFT JOIN crossbones.account a ON a.account_id = u.account_id
                WHERE api.subscriber_key = ?
                LIMIT 1";

        $subscriber = $this->db_read->fetchAll($sql, array($apiKey));

        $subscriber[0]['params'] = $params ;

        $subscriber[0]['script'] = $script ;

        $account_id = $subscriber[0]['account_id'] ;

        if ( $account_id ) {

            foreach($params as $key => $parameter){
                if(($parameter[0])&&($parameter[1])){
                    switch($parameter[0]){
                        case               'days' : $days = $parameter[1] ;
                                                    break;
                        case             'metric' : $metric = $parameter[1] ;
                                                    break;
                        case       'serialnumber' : $serialnumber = $parameter[1] ;
                                                    break;                                                                      
                        case               'unit' : $unit = $parameter[1] ;
                                                    $serialnumber = $parameter[1] ;
                                                    break;                                                                      
                        case            'unit_id' : $unit_id = $parameter[1] ;
                                                    break;                                                                      
                    }
                }
            }

            if((!(is_array($params)))&&(!($unit_id))){
                $unit_id = $params;
            }
        
        } else {
            $script = null ;
        }

        if ( ( $serialnumber ) || ( $unit ) || ( $unit_id ) ) {

            if ( $unit ) {

                $sql = "SELECT
                            db,
                            unit_id
                        FROM crossbones.unit
                        WHERE ( serialnumber = ? OR unit_id = ? )
                        AND account_id = ?";

                $db = $this->db_read->fetchAll($sql, array($serialnumber,$unit,$account_id));

                $sql = "SELECT
                            db,
                            unit_id
                        FROM crossbones.unit
                        WHERE unit_id = ?
                        AND account_id = ?";

                $db = $this->db_read->fetchAll($sql, array($unit,$account_id));

            } else if ( $serialnumber ) {

                $sql = "SELECT
                            db,
                            unit_id
                        FROM crossbones.unit
                        WHERE serialnumber = ?
                        AND account_id = ?";

                $db = $this->db_read->fetchAll($sql, array($serialnumber,$account_id));

            } else if ( $unit_id ) {

                $sql = "SELECT
                            db,
                            unit_id
                        FROM crossbones.unit
                        WHERE unit_id = ?
                        AND account_id = ?";

                $db = $this->db_read->fetchAll($sql, array($unit_id,$account_id));

            }

            $unit_id = $db[0]['unit_id'];
                                        
        }

        switch ($script) {

            case 'frequent_stops' : $frequent_stops = array() ;

                                    if( ($account_id) && ($unit_id) ){

                                        $sql = "SELECT
                                                    u.db as db,
                                                    t.timezone
                                                FROM crossbones.unit u 
                                                LEFT JOIN unitmanagement.timezone t ON t.timezone_id = u.timezone_id
                                                WHERE unit_id = ?
                                                AND account_id = ?";

                                        $db = $this->db_read->fetchAll($sql, array($unit_id,$account_id));

                                        if($db[0]['db']){

                                            $sql = "SELECT
                                                        t.account_id as account_id,
                                                        t.active as tact,
                                                        t.territoryname as territoryname,
                                                        t.latitude as tlat,
                                                        t.longitude as tlong,
                                                        t.radius as tradius,
                                                        ue.*,
                                                        ume.eventname as eventname
                                                    FROM " . $db[0]['db'] . ".unit" . $unit_id . " ue
                                                    LEFT JOIN unitmanagement.event ume ON ume.event_id = ue.event_id
                                                    LEFT JOIN crossbones.territory t ON t.territory_id = ue.landmark_id
                                                    WHERE ue.id IS NOT NULL
                                                    AND ue.event_id IS NOT NULL
                                                    ORDER BY ue.unittime DESC";

                                            $rows = $this->db_read->fetchAll($sql, array());
                                            
                                            $lastunittime=date('Y-m-d H:i:s');
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
                                                    $tzTime = $this->base_logic->timezoneDelta($timezone,$row['unittime'],1);
                                                    //
                                                    //
                                                    $address = $this->address_logic->validateAddress(str_replace('no address','',$row['streetaddress']), $row['city'], $row['state'], $row['zipcode'], $row['country']);
                                                    if(($row['active'])&&($row['territoryname'])&&($row['account_id']==$account_id)){
                                                        $address = '(' . $row['territoryname'] . ') ' . $address;
                                                    }
                                                    $label = str_replace('"','\"',$address);
                                                    $unittime = date('m/d/Y h:ia' , strtotime($tzTime)) ;
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
                                                    $frequent[$buffer]['details'][date('N' , strtotime($tzTime))][date('U' , strtotime($tzTime))][] = $duration ; 
                                                    $frequent[$buffer]['aveDurationTime'] = $frequent[$buffer]['aveDurationTime'] + $aveDuration;
                                                    $frequent[$buffer]['aveDurationCount']++;
                                                    //
                                                    $sort[$buffer] = $frequent[$buffer]['stops'] ;
                                                    //
                                                }
                                            }

                                            arsort($sort);

                                            foreach ($sort as $key => $stops) {

                                                $report['records']++;

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
                                                $dow .= $buffer . '%';

                                                $i = array('am_','pm_');
                                                $o = array('','');
                                                $tod = null;
                                                $todCnt = 0 ;
                                                ksort($frequent[$key]['tod']);
                                                foreach ($frequent[$key]['tod'] as $kk => $vv ) {
                                                    $kk = str_replace ( $i, $o, $kk ) ;
                                                    if($vv > $todCnt){
                                                        $todCnt = $vv ;                                                                    
                                                        $tod = $kk;
                                                    } else if($vv == $todCnt){                                                                    
                                                        $tod .= ', ' . $kk;
                                                    }
                                                }
                                                $buffer =  floor ( $todCnt / $frequent[$key]['stops'] * 100 ) ;
                                                $todLink = $tod . ' ' . $buffer . '%';
                                                $tod .= $buffer . '%';

                                                ksort($frequent[$key]['details']);
                                                $details = array();
                                                foreach ($frequent[$key]['details'] as $ak => $av ) {
                                                    $cnt=0;
                                                    $events = array();
                                                    $detail = array();
                                                    $detail['dow'] = $this->base_logic->wizardDow($ak);
                                                    krsort($av);
                                                    foreach ($av as $bk => $bv ) {
                                                        foreach ($bv as $kk => $vv ) {
                                                            $event = array();
                                                            $event['timestamp'] =  date('Y-m-d H:i:s', $bk) ;
                                                            $event['duration'] =  $vv;
                                                            $events[] = $event;
                                                        }
                                                    }
                                                    $detail['events'] = $events; 
                                                    $details[] = $detail;
                                                }
                                                
                                                $row = array() ;

                                                $row['stops'] = $stops ;
                                                $row['address'] = $frequent[$key]['address'] ;
                                                $row['latitude'] = $frequent[$key]['latitude'] ;
                                                $row['longitude'] = $frequent[$key]['longitude'] ;
                                                $row['day_of_week'] = $dowLink ;
                                                $row['time_of_day'] = $todLink ;
                                                $row['duration'] = $aveDuration ;
                                                $row['details'] = $details ;
                                                $row['timezone'] = $db[0]['timezone'] ;

                                                $frequent_stops[] = $row ;

                                            }

                                        }

                                    }

                                    $out[$script] = $frequent_stops ;

                                    break;

            case     'last_event' : if( ($db[0]['db']) && ($unit_id) ){

                                        $sql = "SELECT
                                                    ue.city,
                                                    ue.latitude,
                                                    ue.longitude,
                                                    ue.unittime,
                                                    ue.servertime,
                                                    ue.state,
                                                    ue.streetaddress,
                                                    ue.zipcode,
                                                    t.territoryname as territoryname,
                                                    ume.eventname as eventname
                                                FROM " . $db[0]['db'] . ".unit" . $unit_id . " ue
                                                LEFT JOIN unitmanagement.event ume ON ume.event_id = ue.event_id
                                                LEFT JOIN crossbones.territory t ON t.territory_id = ue.landmark_id
                                                WHERE ue.id IS NOT NULL
                                                AND ue.event_id IS NOT NULL 
                                                ORDER BY ue.unittime DESC
                                                LIMIT 1";

                                        $rows = $this->db_read->fetchAll($sql, array());

                                        $out[$script] = $rows ;

                                    }
                                    break ;

            case        'metrics' : $metrics = array() ;

                                    $sql = "SELECT COUNT(unitstatus_id) as installed
                                            FROM crossbones.unit
                                            WHERE account_id = ?
                                            AND unitstatus_id = ?";
                                    $buf = $this->db_read->fetchAll($sql, array($account_id,'1'));
                                    $metrics[0]['installed'] = $buf[0]['installed'] ; 

                                    $sql = "SELECT COUNT(unitstatus_id) as inventory
                                            FROM crossbones.unit
                                            WHERE account_id = ?
                                            AND unitstatus_id = ?";
                                    $buf = $this->db_read->fetchAll($sql, array($account_id,'2'));
                                    $metrics[0]['inventory'] = $buf[0]['inventory'] ; 

                                    $sql = "SELECT COUNT(u.unit_id) as landmark
                                            FROM crossbones.unit u
                                            LEFT JOIN crossbones.unitalertstatus uas ON uas.unit_id = u.unit_id
                                            WHERE u.account_id = ?
                                            AND u.unitstatus_id = ?
                                            AND uas.landmark_id != ?";
                                    $buf = $this->db_read->fetchAll($sql, array($account_id,'1','0'));
                                    $metrics[0]['landmark'] = $buf[0]['landmark'] ; 
                                    
                                    $sql = "SELECT COUNT(u.unit_id) as starterstatus
                                            FROM crossbones.unit u
                                            WHERE u.account_id = ?
                                            AND u.starterstatus = ?";
                                    $buf = $this->db_read->fetchAll($sql, array($account_id,'Disabled'));
                                    $metrics[0]['starterstatus'] = $buf[0]['starterstatus'] ; 
                                
                                    $sql = "SELECT COUNT(unit_id) as reminderstatus
                                            FROM crossbones.unit
                                            WHERE account_id = ?
                                            AND reminderstatus = ?";
                                    $buf = $this->db_read->fetchAll($sql, array($account_id,'On'));
                                    $metrics[0]['reminderstatus'] = $buf[0]['reminderstatus'] ; 

                                    $sql = "SELECT COUNT(unitstatus_id) as repossession
                                            FROM crossbones.unit
                                            WHERE account_id = ?
                                            AND unitstatus_id = ?";
                                    $buf = $this->db_read->fetchAll($sql, array($account_id,'3'));
                                    $metrics[0]['repossession'] = $buf[0]['repossession'] ; 
                                    
                                    // $sql = "SELECT *
                                    //         FROM crossbones.unit
                                    //         WHERE account_id = ?
                                    //         ORDER BY unit_id DESC";
                                    // $realTimeData = $this->db_read->fetchAll($sql, array($account_id));

                                    // $metrics[0]['movement'] = 0 ;
                                    // $metrics[0]['nonreporting'] = 0 ;

                                    // $sevenDaysAgo = date('d-m-Y', strtotime("-7 days")) ;

                                    // foreach ($realTimeData as $key => $row) {
                                    //     if(($row['db'])&&($row['unit_id'])&&($row['unitstatus_id']!=2)&&( ($row['lastmove']==null) || (strtotime($row['lastmove'])<=strtotime($sevenDaysAgo)) || (strtotime($row['lastmovecheck'])<=strtotime($sevenDaysAgo)) ) ){
                                    //         $sql = "SELECT ueu.servertime as servertime
                                    //                 FROM " . $row['db'] . ".unit" . $row['unit_id'] . " ueu 
                                    //                 WHERE (
                                    //                         ueu.event_id = 1
                                    //                      OR ueu.event_id = 2
                                    //                      OR ueu.event_id = 3
                                    //                      OR ueu.event_id = 4
                                    //                      OR ueu.event_id = 5
                                    //                      OR ueu.event_id = 11
                                    //                      OR ueu.event_id = 12
                                    //                      OR ueu.event_id = 13
                                    //                      OR ueu.event_id = 40
                                    //                      OR ueu.event_id = 41
                                    //                      OR ueu.event_id = 42
                                    //                      OR ueu.event_id = 43
                                    //                      OR ueu.event_id = 44
                                    //                      OR ueu.event_id = 47
                                    //                      OR ueu.event_id = 48
                                    //                      OR ueu.event_id = 49
                                    //                      OR ueu.event_id = 50
                                    //                      OR ueu.event_id = 111
                                    //                      OR ueu.event_id = 112
                                    //                     ) 
                                    //                 AND ueu.servertime > DATE_SUB(NOW(), INTERVAL 7 DAY) 
                                    //                 ORDER BY ueu.id DESC LIMIT 1";
                                    //         $test = $this->db_read->fetchAll($sql, array('1'));
                                    //         if(!($test)){
                                    //             $metrics[0]['movement']++ ; // = $row['unit_id'] . '#' . $result[0]['servertime'] . '#' . $date . '#';
                                    //             $test[0]['servertime'] = date('d-m-Y', strtotime("-8 days")) ;
                                    //         }
                                    //         $sql = "UPDATE crossbones.unit
                                    //                 SET lastmove = ? ,
                                    //                 lastmovecheck = now()
                                    //                 WHERE account_id = ?
                                    //                 AND  unit_id = ?";
                                    //         $result = $this->db_write->executeQuery($sql, array($test[0]['servertime'],$user['account_id'],$row['unit_id']));
                                    //     }
                                    // }
                                    
                                    // foreach ($realTimeData as $key => $row) {
                                    //     if(($row['db'])&&($row['unit_id'])&&($row['unitstatus_id']!=2)&&( ($row['lastreport']==null) || (strtotime($row['lastreport'])<=strtotime($sevenDaysAgo)) || (strtotime($row['lastreportcheck'])<=strtotime($sevenDaysAgo)) ) ){
                                    //         $sql = "SELECT ueu.servertime as servertime
                                    //                 FROM " . $row['db'] . ".unit" . $row['unit_id'] . " ueu 
                                    //                 WHERE ueu.servertime > DATE_SUB(NOW(), INTERVAL 7 DAY) 
                                    //                 ORDER BY ueu.id DESC LIMIT 1";
                                    //         $test = $this->db_read->fetchAll($sql, array('1'));
                                    //         if(!($test)){
                                    //             $metrics[0]['nonreporting']++ ;
                                    //             $test[0]['servertime'] = $sevenDaysAgo; 
                                    //         }
                                    //         $sql = "UPDATE crossbones.unit
                                    //                 SET lastreport = ? ,
                                    //                 lastreportcheck = now()
                                    //                 WHERE account_id = ?
                                    //                 AND  unit_id = ?";
                                    //         $result = $this->db_write->executeQuery($sql, array($test[0]['servertime'],$user['account_id'],$row['unit_id']));                                                            
                                    //     }
                                    // }

                                    $out[$script] = $metrics ;

                                    break ;

            case          'stops' : $stops = array() ;
                        
                                    if($unit_id){

                                        $sql = "SELECT
                                                    db
                                                FROM crossbones.unit
                                                WHERE unit_id = ?";

                                        $db = $this->db_read->fetchAll($sql, array($unit_id));
                                        
                                        if($db[0]['db']){

                                            if($days>0){
                                                // days has been set by user
                                            } else {
                                                $days = 90 ;
                                            }

                                            $sql = "SELECT
                                                        t.account_id as account_id,
                                                        t.territoryname as territoryname,
                                                        ue.*,
                                                        ume.eventname as eventname
                                                    FROM " . $db[0]['db'] . ".unit" . $unit_id . " ue
                                                    LEFT JOIN unitmanagement.event ume ON ume.event_id = ue.event_id
                                                    LEFT JOIN crossbones.territory t ON t.territory_id = ue.landmark_id
                                                    WHERE ue.id IS NOT NULL
                                                    AND ue.event_id IS NOT NULL
                                                    AND ue.unittime > (NOW() - INTERVAL " . $days . " DAY)
                                                    ORDER BY ue.unittime DESC";

                                            $rows = $this->db_read->fetchAll($sql, array());

                                            $lastunittime=date('Y-m-d H:i:s');
                                            $page=1;
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
                                                $unittime = date('m/d/Y h:ia' , strtotime($this->base_logic->timezoneDelta($timezone,$row['unittime'],1))) ;
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
                                                //
                                                $row = array() ;
                                                //
                                                // $row['record'] = $records ;
                                                $row['unittime'] = $final['unittime'] ;
                                                $row['eventid'] = $final['event_id'] ;
                                                $row['eventname'] = $final['eventname'] ;
                                                $row['latitude'] = $final['latitude'] ;
                                                $row['longitude'] = $final['longitude'] ;
                                                $row['label'] = $final['label'] ;
                                                $row['title'] = $final['title'] ;
                                                $row['address'] = $final['address'] ;
                                                $row['duration'] = $final['duration'] ;
                                                //
                                                $stops[] = $row ;
                                                //
                                                $records--;
                                                //
                                            }

                                        }

                                    }

                                    $out[$script] = $stops ;

                                    break;

            case        'update' :  foreach($params as $key => $parameter){
                                        $db = null;
                                        $tbl = null;
                                        $field = null;
                                        $value = null;
                                        if(($parameter[0])&&($parameter[1])){
                                            switch($parameter[0]){
                                                case              'color' :
                                                case 'licenseplatenumber' :
                                                case         'loannumber' :
                                                case               'make' :
                                                case              'model' :
                                                case        'stocknumber' :
                                                case                'vin' :
                                                case               'year' : $db = 'crossbones' ;
                                                                            $tbl = 'unitattribute' ;
                                                                            $field = $parameter[0] ;
                                                                            $value = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            break; 
                                                case        'installdate' :
                                                case          'installer' : $db = 'crossbones' ;
                                                                            $tbl = 'unitinstallation' ;
                                                                            $field = $parameter[0] ;
                                                                            $value = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            break; 
                                                case           'unitname' : $db = 'crossbones' ;
                                                                            $tbl = 'unit' ;
                                                                            $field = $parameter[0] ;
                                                                            $value = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            break; 
                                            }
                                            if( ($db) && ($tbl) && ($unit_id) && ($field) && ($value) ){
                                                // $out['update'][0]['database'] = $db ;
                                                // $out['update'][0]['table'] = $tbl ;
                                                // $out['update'][0]['unit'] = $unit_id ;
                                                // $out['update'][0]['field'] = $field ;
                                                // $out['update'][0]['value'] = $value ;
                                                if ($this->db_read->update($db.'.'.$tbl, array($field => $value), array('unit_id' => $unit_id))) {
                                                    $out['update'][0][$field] =  $value ;
                                                }
                                            }
                                        }
                                    }
                                    if(!($out[$script])){
                                        $out[$script][0][] = 'No Changes Made' ;
                                    } else {
                                        $out[$script][0]['unit_id'] = $unit_id ;
                                    }
                                    break;


            case         'users' :  $sql = "SELECT u.firstname,
                                            u.lastname,
                                            u.email,
                                            u.username,
                                            u.roles,
                                            us.userstatusname as status
                                            FROM crossbones.account a
                                            LEFT JOIN crossbones.user u ON a.account_id = u.account_id
                                            LEFT JOIN crossbones.userstatus us ON us.userstatus_id = u.userstatus_id
                                            WHERE a.account_id = ?
                                            AND u.userstatus_id > 0";

                                    $users = $this->db_read->fetchAll($sql, array($account_id));

                                    $out[$script] = $users ;

                                    break ;

            case      'vehicles' :  $sqlPlaceHolder[] = $account_id ;
                                    switch($metric){

                                        case            'installed' : $search = ' AND u.unitstatus_id = ?';
                                                                      $sqlPlaceHolder[] = 1 ;
                                                                      break;

                                        case            'inventory' : $search = ' AND u.unitstatus_id = ?';
                                                                      $sqlPlaceHolder[] = 2 ;
                                                                      break;

                                        case             'landmark' : $search = ' AND u.unitstatus_id = ? AND uas.landmark_id != ?';
                                                                      $sqlPlaceHolder[] = '1' ;
                                                                      $sqlPlaceHolder[] = '0' ;
                                                                      break;

                                        case           'reminderon' : $search = " AND u.reminderstatus = ?";
                                                                      $sqlPlaceHolder[] = 'On' ;
                                                                      break;

                                        case         'repossession' : $search = ' AND u.unitstatus_id = ?';
                                                                      $sqlPlaceHolder[] = 3 ;
                                                                      break;

                                        case      'starterdisabled' : $search = ' AND u.starterstatus = ?';
                                                                      $sqlPlaceHolder[] = 'Disabled' ;
                                                                      break;

                                    }

                                    $sql = "SELECT u.unit_id,
                                            u.unitname,
                                            u.serialnumber,
                                            u.imei,
                                            u.uid,
                                            u.simcard_id,
                                            u.unitmanufacturer_id,
                                            u.unitversion_id,
                                            u.timezone_id,
                                            u.rgeo_id,
                                            u.subscription,
                                            uas.landmark_id as landmark_id,
                                            t.territoryname as landmark_name,
                                            us.unitstatusname as status_unit,
                                            u.starterstatus as status_starter,
                                            u.reminderstatus as status_reminder,
                                            u.lastmove as last_move,
                                            u.lastreport as last_event,
                                            u.db_records as event_count,
                                            u.activation,
                                            ua.activatedate as activatedate,
                                            ua.color as color,
                                            ua.deactivatedate as deactivatedate,
                                            ua.licenseplatenumber as licenseplatenumber,
                                            ua.loannumber as loannumber,
                                            ua.make as make,
                                            ua.model as model,
                                            ua.plan as plan,
                                            ua.purchasedate as purchasedate,
                                            ua.renewaldate as renewaldate,
                                            ua.stocknumber as stocknumber,
                                            ua.vin as vin,
                                            ua.year as year
                                            FROM crossbones.account a
                                            LEFT JOIN crossbones.unit u ON a.account_id = u.account_id
                                            LEFT JOIN crossbones.unitattribute ua ON ua.unit_id = u.unit_id
                                            LEFT JOIN crossbones.unitalertstatus uas ON uas.unit_id = u.unit_id
                                            LEFT JOIN crossbones.unitstatus us ON us.unitstatus_id = u.unitstatus_id
                                            LEFT JOIN crossbones.territory t ON t.territory_id = uas.landmark_id
                                            WHERE a.account_id = ?"
                                            . $search;

                                    $users = $this->db_read->fetchAll($sql, $sqlPlaceHolder);

                                    $out['metric'][] = array($metric) ;
                                    if($users){
                                        $out[$script] = $users ;
                                    }

                                    break ;

            case  'verification' :
            case 'verifications' :  if ( $unit_id ) {

                                        $sql = "SELECT 
                                            t.city,
                                            t.latitude,
                                            t.longitude,
                                            t.radius,
                                            t.state,
                                            t.streetaddress,
                                            t.territoryname,
                                            t.zipcode
                                            FROM crossbones.unit u
                                            LEFT JOIN crossbones.unit_territory ut ON ut.unit_id = u.unit_id
                                            LEFT JOIN crossbones.territory t ON t.territory_id = ut.territory_id
                                            WHERE u.unit_id = ?
                                            AND t.territorytype = ?
                                            AND u.unitstatus_id > 0";

                                        $verifications = $this->db_read->fetchAll($sql, array($unit_id,'reference'));

                                    }

                                    $out[$script] = $verifications ;

                                    break ;

            case 'verification_add' :

                                    $city = null;
                                    $country = null;
                                    $latitude = null;
                                    $longitude = null;
                                    $radius = null;
                                    $streetaddress = null;
                                    $state = null;
                                    $territoryname = null;
                                    $zipcode = null;
                                    foreach($params as $key => $parameter){
                                        if(($parameter[0])&&($parameter[1])){
                                            switch($parameter[0]){
                                                case               'city' : $city = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            break; 
                                                case            'country' : $country = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            break; 
                                                case           'latitude' : $latitude = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            break; 
                                                case          'longitude' : $longitude = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            break; 
                                                case             'radius' : $radius = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            break; 
                                                case      'streetaddress' : $streetaddress = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            break; 
                                                case              'state' : $state = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            break; 
                                                case      'territoryname' : $territoryname = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            break; 
                                                case            'zipcode' : $zipcode = str_replace ( '+' , ' ' , $parameter[1] ) ;
                                                                            break; 
                                            }
                                        }
                                    }
                            
                                    if( ($account_id) && ($city) && ($country) && ($latitude) && ($longitude) && ($radius) && ($streetaddress) && ($state) && ($territoryname) && ($unit_id) && ($zipcode) ){
                            
                                        $out[$script][0]['account_id'] = 'attempt:' . $account_id ;

                                        if(!($boundingbox)){
                                            $boundingbox = "GEOMFROMTEXT('POLYGON((29.9 -95.5))')";
                                        }
                                        $boundingbox = str_replace(array("GEOMFROMTEXT('","')"), array('',''), $boundingbox);

                                        $sql = "INSERT INTO crossbones.territory ( account_id , territorygroup_id , territorycategory_id , territorytype , shape , territoryname , latitude , longitude , radius , boundingbox , streetaddress , city , state , zipcode , country , verifydate ) VALUES ( ? , ? , ? , ? , ? , ? , ? , ? , ? , PolyFromText(?) , ? , ? , ? , ? , ? , ? )";

                                        $result = $this->db_write->executeQuery($sql, array($account_id,0,0,'reference','circle',$territoryname,$latitude,$longitude,$radius,$boundingbox,$streetaddress,$city,$state,$zipcode,$country,'0000-00-00'));

                                        if ($result) {
                                            
                                            $out[$script][0]['insert'] = 'territory' ;

                                            $sql = "SELECT * 
                                                    FROM crossbones.territory
                                                    WHERE account_id = ?
                                                    ORDER BY territory_id DESC
                                                    LIMIT 1";

                                            $territory_id = $this->db_read->fetchAll($sql, array($account_id));

                                            if ( $territory_id[0]['territory_id'] ) {

                                                $out[$script][0]['insert'] = 'territory_unit' ;

                                                $sql = "INSERT INTO crossbones.unit_territory ( unit_id , territory_id ) VALUES ( ? , ? )";

                                                $result = $this->db_write->executeQuery($sql, array($unit_id,$territory_id[0]['territory_id']));

                                                if ($result) {
                                                    
                                                    $out[$script][0]['insert'] = 'success' ;

                                                }

                                            }

                                        } else {
                                            
                                            $out[$script][0]['insert'] = 'fail' ;
                                        
                                        }


                                    } else {
                            
                                        $out[$script][0]['_ERROR'] = 'One or more of the following variables is missing data...' ;
                                        
                                        $out[$script][0]['city'] = $city ;
                                        $out[$script][0]['country'] = $country ;
                                        $out[$script][0]['latitude'] = $latitude ;
                                        $out[$script][0]['longitude'] = $longitude ;
                                        $out[$script][0]['radius'] = $radius ;
                                        $out[$script][0]['streetaddress'] = $streetaddress ;
                                        $out[$script][0]['state'] = $state ;
                                        $out[$script][0]['territoryname'] = $territoryname ;
                                        $out[$script][0]['zipcode'] = $zipcode ;
                            
                                    }
                                    
                                    break ;

        }

        $out['subscriber'] = $subscriber ;

        return $out ;

    }

    /**
     * Get the territory groups info by user id and territory type
     *
     * @params: user_id, territorygroup_ids (optional array of territory group ids)
     * @params: type
     *
     * @return array | bool
     */
    public function getTerritoryGroupsByUserId($user_id, $territorygroup_ids)
    {
        $sqlPlaceHolder = array($user_id);
        $where_type = $where_in = "";
        if (isset($this->territoryType) AND ! empty($this->territoryType)) {
            $where_type = "AND tg.territorytype IN (" . substr(str_repeat('?,', count($this->territoryType)), 0, -1) . ") ";
            $sqlPlaceHolder = array_merge($sqlPlaceHolder, $this->territoryType);
        }
        
        if (! empty($territorygroup_ids) AND is_array($territorygroup_ids)) {           
            $where_in = "AND tg.territorygroup_id IN (" . substr(str_repeat('?,', count($territorygroup_ids)), 0, -1) . ") ";
            $sqlPlaceHolder = array_merge($sqlPlaceHolder, $territorygroup_ids);
        }

        $sql = "SELECT * 
                FROM user_territorygroup utg
                INNER JOIN territorygroup tg ON utg.territorygroup_id = tg.territorygroup_id
                WHERE utg.user_id = ? AND tg.active = 1 {$where_type} {$where_in}
                ORDER BY tg.territorygroupname ASC";

        $data = $this->db_read->fetchAll($sql, $sqlPlaceHolder);
        return $data;
    }

    /**
     * Get the territory groups by account id and territory type
     *
     * @params: account_id
     * @params: type
     *
     * @return array | bool
     */
    public function getTerritoryGroupsByAccountId($account_id)
    {
        $sqlPlaceHolder = array($account_id);
        $where_type = "";
        if (isset($this->territoryType) AND ! empty($this->territoryType)) {
            $where_type = "AND crossbones.territorygroup.territorytype IN (" . substr(str_repeat('?,', count($this->territoryType)), 0, -1) . ") ";
            $sqlPlaceHolder = array_merge($sqlPlaceHolder, $this->territoryType);
        }

        $sql = "SELECT *
                FROM territorygroup
                WHERE territorygroup.account_id = ? AND territorygroup.active = 1 {$where_type}
                ORDER BY territorygroupname ASC";

        return $this->db_read->fetchAll($sql, $sqlPlaceHolder);
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
        $data = array();

        $sql = "SELECT 
                    *
                FROM territory t
                WHERE t.territorygroup_id = ? AND t.active = 1";

        $data = $this->db_read->fetchAll($sql, array($territorygroup_id));

        return $data;
    }

    /**
     * Get the territory group info by territorygroup_id
     *
     * @params: $territorygroup_id
     *
     * @return array | bool
     */
    public function getTerritoryGroupById($territorygroup_id)
    {
        $data = array();
        $sql = "SELECT * 
                FROM territorygroup 
                WHERE active = 1 AND territorygroup_id = ?";

        $data = $this->db_read->fetchAll($sql, array($territorygroup_id));
        return $data;
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

        $sqlPlaceHolder = array($account_id);
        $where_type     = '';
        $where_default  = '';
        
        if (isset($territorytype) AND $territorytype != '') {
                $where_type = " AND tg.territorytype = ?";
                $sqlPlaceHolder[] = $territorytype;            
        } else {
            if (isset($this->territoryType) AND ! empty($this->territoryType)) {
                $type = $this->territoryType[0];
                $where_type = " AND tg.territorytype = ?";
                $sqlPlaceHolder[] = $type;
            }
        }

        //TEMPORARY FIX TO GET DEFAULT GROUP
        // REMOVE ONCE DATABASE HAS default column
        $by_groupname = true;
        $where_groupname = '';
        if ($by_groupname) {
            $where_groupname = " AND tg.territorygroupname = ?";
            $groupname = 'Default';
            $sqlPlaceHolder[] = $groupname;
        } else {
            if (true) {
                $where_default = " AND tg.default = ?";
                $isdefault = '1';
                $sqlPlaceHolder[] = $isdefault;
            }             
        }

        $sql = "SELECT * 
                FROM territorygroup tg
                WHERE tg.account_id = ? AND active = 1{$where_type}{$where_default}{$where_groupname}
                LIMIT 1";

        return $this->db_read->fetchAssoc($sql, $sqlPlaceHolder);
    }

    /**
     * Get all permissions for a user
     *
     * @return array
     */
    public function getPermissions($account_id,$user_id)
    {
        $permissions = array();

        if (($account_id)&&($user_id)){
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
            $permissions = $this->db_read->fetchAll($sql, array($account_id,$user_id,1));
        } else {
            $sql = "SELECT * FROM crossbones.permission WHERE permission_id IS NOT NULL";
            $permissions = $this->db_read->fetchAll($sql, array());
        }

        return $permissions ;

    }
    
    /**
     * Get the territories by territory group ids
     *
     * @params: user_id, territorygroups
     *
     * @return array | bool
     */    
    public function getTerritoryByGroupIds($user_id, $territorygroups, $account_id, $permission)
    {

        if($account_id>0){

            $sql_params = array($account_id,$user_id);
            $sql = "SELECT roles
                    FROM crossbones.user
                    WHERE account_id = ?
                    AND user_id = ?";
            $roles = $this->db_read->fetchAll($sql, $sql_params);

            if(($roles[0]['roles']=='ROLE_ACCOUNT_OWNER')||($permission)){
    
                $sql_params = array($account_id);
                $sql = "SELECT t.*,
                            t.territory_id as territory_id,
                            t.territoryname as territoryname
                        FROM crossbones.territory t
                        LEFT JOIN crossbones.territorygroup tg ON tg.territorygroup_id = t.territorygroup_id
                        LEFT JOIN crossbones.user_territorygroup utg ON utg.territorygroup_id = tg.territorygroup_id
                        LEFT JOIN crossbones.user user ON user.user_id = utg.user_id
                        WHERE t.account_id = ?
                        AND t.active = 1 
                        AND t.territorytype = 'landmark'
                        ORDER BY t.territoryname ASC";

            } else {

                $sql_params = array($account_id,$user_id);
                $sql = "SELECT t.*,
                            t.territory_id as territory_id,
                            t.territoryname as territoryname
                        FROM crossbones.territory t
                        LEFT JOIN crossbones.territorygroup tg ON tg.territorygroup_id = t.territorygroup_id
                        LEFT JOIN crossbones.user_territorygroup utg ON utg.territorygroup_id = tg.territorygroup_id
                        LEFT JOIN crossbones.user user ON user.user_id = utg.user_id
                        WHERE t.account_id = ?
                        AND t.active = 1 
                        AND utg.user_id = ?
                        AND t.territorytype = 'landmark'
                        ORDER BY t.territoryname ASC";
            }

        } else {

            $sql_params = array($user_id);
            $sql = "SELECT t.*,
                        t.territory_id as territory_id,
                        t.territoryname as name
                    FROM crossbones.territory t
                    WHERE t.territory_id = ?
                    AND t.active = 1 
                    AND t.territorytype = 'landmark'
                    ORDER BY t.territoryname ASC";

        }

        $results = $this->db_read->fetchAll($sql, $sql_params);

        foreach ( $results as $key => $record ) {
            if(($record['territory_id']>0)&&($record['territory_id']!=$last)){
                $last = $record['territory_id'] ;
                $territories[] = $record ;
            }
        }

        return $territories;

        // $sqlPlaceHolder = array($user_id);
        // $where_in = "";
        // if (isset($territorygroups) AND ! empty($territorygroups)) {
        //     $where_in = "AND tg.territorygroup_id IN (" . substr(str_repeat('?,', count($territorygroups)), 0, -1) . ") ";
        //     $sqlPlaceHolder = array_merge($sqlPlaceHolder, $territorygroups);
        // }

        // $where_type = "";
        // if (isset($this->territoryType) AND ! empty($this->territoryType)) {
        //     $where_type = "AND t.territorytype IN (" . substr(str_repeat('?,', count($this->territoryType)), 0, -1) . ") ";
        //     $sqlPlaceHolder = array_merge($sqlPlaceHolder, $this->territoryType);
        // } 

        // $sql = "SELECT 
        //             t.territory_id,
        //             t.account_id,
        //             t.territorycategory_id,
        //             t.shape,
        //             t.territoryname,
        //             t.latitude,
        //             t.longitude,
        //             t.radius,
        //             t.streetaddress,
        //             t.city,
        //             t.state,
        //             t.zipcode,
        //             t.country,
        //             t.territorytype,
        //             t.verifydate,
        //             t.active,
        //             tg.*,
        //             t.territorytype as territorytype,
        //             t.territory_id as territory_id 
        //         FROM crossbones.territory t
        //         LEFT JOIN crossbones.territorygroup tg ON t.territorygroup_id = tg.territorygroup_id
        //         LEFT JOIN crossbones.user_territorygroup utg ON tg.territorygroup_id = utg.territorygroup_id
        //         WHERE utg.user_id = ? AND t.active = 1 {$where_in} {$where_type} 
        //         ORDER BY t.territoryname ASC";

        // $territory = $this->db_read->fetchAll($sql, $sqlPlaceHolder);

        // return $territory;
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
    public function getFilteredTerritoryStringSearch($user_id, $params, $searchfields)
    {
        $sqlPlaceHolder = array($user_id);
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

        $where_type = "";
        if (isset($this->territoryType) AND ! empty($this->territoryType)) {
            $where_type = "AND t.territorytype IN (" . substr(str_repeat('?,', count($this->territoryType)), 0, -1) . ") ";
            $sqlPlaceHolder = array_merge($sqlPlaceHolder, $this->territoryType);
        } 

        $sql = "SELECT 
                    t.territory_id,
                    t.account_id,
                    t.territorycategory_id,
                    t.shape,
                    t.territoryname,
                    t.latitude,
                    t.longitude,
                    t.radius,
                    t.streetaddress,
                    t.city,
                    t.state,
                    t.zipcode,
                    t.country,
                    t.territorytype,
                    t.verifydate,
                    t.active,
                    tg.active AS territorygroup_active,
                    tg.territorygroupname as territorygroupname,
                    tg.territorygroup_id as territorygroup_id,
                    u.* 
                FROM crossbones.territory t
                LEFT JOIN crossbones.territorygroup tg ON t.territorygroup_id = tg.territorygroup_id
                LEFT JOIN crossbones.user_territorygroup utg ON tg.territorygroup_id = utg.territorygroup_id
                LEFT JOIN crossbones.unit_territory ut ON ut.territory_id = t.territory_id
                LEFT JOIN crossbones.unit u ON u.unit_id = ut.unit_id 
                WHERE utg.user_id = ? AND t.active = 1 {$where_search_string} {$where_type}
                ORDER BY t.territoryname ASC";

        $territory = $this->db_read->fetchAll($sql, $sqlPlaceHolder);

        return $territory;
    }

    /**
     * Get the filtered landmarks by $params (landmark group ids)
     *
     * @params: int user_id
     * @params: array $params
     *
     * @return array | bool
     */    
    public function getFilteredTerritory($user_id, $params)
    {
        $sqlPlaceHolder = array($user_id);
        $where_in_groups = "";
        if (isset($params['territorygroup_id']) AND ! empty($params['territorygroup_id'])) {
            $where_in_groups = " AND tg.territorygroup_id IN (" . substr(str_repeat('?,', count($params['territorygroup_id'])), 0, -1) . ") ";
            $sqlPlaceHolder = array_merge($sqlPlaceHolder, $params['territorygroup_id']);            
        }

        $where_type = "";
        if (isset($params['territorytype']) AND $params['territorytype'] != '') {
            $where_type = " AND t.territorytype = ? ";
            $sqlPlaceHolder[] = $params['territorytype'];
        } else {
            if (isset($this->territoryType) AND ! empty($this->territoryType)) {
                $where_type = " AND t.territorytype IN (" . substr(str_repeat('?,', count($this->territoryType)), 0, -1) . ") ";
                $sqlPlaceHolder = array_merge($sqlPlaceHolder, $this->territoryType);                
            }
        }

        $where_in_categories = "";
        if (isset($params['territorycategory_id']) AND ! empty($params['territorycategory_id'])) {
            $where_in_categories = " AND t.territorycategory_id IN (" . substr(str_repeat('?,', count($params['territorycategory_id'])), 0, -1) . ") ";
            $sqlPlaceHolder = array_merge($sqlPlaceHolder, $params['territorycategory_id']);
        }

        $sql = "SELECT 
                    t.territory_id,
                    t.account_id,
                    t.territorycategory_id,
                    t.shape,
                    t.territoryname,
                    t.latitude,
                    t.longitude,
                    t.radius,
                    t.streetaddress,
                    t.city,
                    t.state,
                    t.zipcode,
                    t.country,
                    t.territorytype,
                    t.verifydate,
                    t.active,
                    tg.active AS territorygroup_active,
                    tg.territorygroupname as territorygroupname,
                    tg.territorygroup_id as territorygroup_id,
                    u.*
                FROM crossbones.territory t
                LEFT JOIN crossbones.territorygroup tg ON t.territorygroup_id = tg.territorygroup_id 
                LEFT JOIN crossbones.user_territorygroup utg ON tg.territorygroup_id = utg.territorygroup_id
                LEFT JOIN crossbones.unit_territory ut ON ut.territory_id = t.territory_id
                LEFT JOIN crossbones.unit u ON u.unit_id = ut.unit_id 
                WHERE utg.user_id = ? AND t.active = 1{$where_in_groups} {$where_type} {$where_in_categories}
                ORDER BY t.territoryname ASC";

        $territory = $this->db_read->fetchAll($sql, $sqlPlaceHolder);

        return $territory;
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
    public function getIncompleteTerritoryStringSearch($account_id, $params, $searchfields)
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
        
        // since the result set from this query is only used to be display in the UI datatables and no where else,
        // it makes since to convert the territorytype directly from 'reference' to 'verification' in order
        // to save having to do the conversion else where
        $sql = "SELECT 
                    tu.*,
                    tg.active AS territorygroup_active,
                    tu.territorygroupname as territorygroupname,
                    tg.territorygroup_id as territorygroup_id,
                    IF (tu.territorytype = 'reference', 'verification', tu.territorytype) AS territorytype
                FROM crossbones.territoryupload tu
                LEFT JOIN crossbones.territorygroup tg ON LOWER(tu.territorygroupname) = LOWER(tg.territorygroupname)
                WHERE tu.account_id = ? {$where_search_string}
                ORDER BY tu.territoryname ASC";

        $landmarks = $this->db_read->fetchAll($sql, $sqlPlaceHolder);

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
    public function getFilteredIncompleteTerritory($account_id, $params)
    {
        $sqlPlaceHolder = array($account_id);
        $where_in_groups = "";
        if (isset($params['territorygroup_id']) AND ! empty($params['territorygroup_id'])) {
            $where_in_groups = " AND tg.territorygroup_id IN (" . substr(str_repeat('?,', count($params['territorygroup_id'])), 0, -1) . ") ";
            $sqlPlaceHolder = array_merge($sqlPlaceHolder, $params['territorygroup_id']); 
        }

        $where_reference = "";
        if (isset($params['territorytype']) AND $params['territorytype'] != '') {
            $where_reference = " AND tu.territorytype = ? ";
            $sqlPlaceHolder[] = $params['territorytype'];
        }

        $where_reason = "";
        if (isset($params['reason']) AND $params['reason'] != '') {
            $where_reason = " AND (tu.reason LIKE ?)";
            $sqlPlaceHolder[] = '%'.$params['reason'].'%';
        }

        // since the result set from this query is only used to be display in the UI datatables and no where else,
        // it makes since to convert the territorytype directly from 'reference' to 'verification' in order
        // to save having to do the conversion else where
        $sql = "SELECT 
                    tu.*,
                    tg.active AS territorygroup_active,
                    tu.territorygroupname as territorygroupname,
                    tg.territorygroup_id as territorygroup_id,
                    IF (tu.territorytype = 'reference', 'verification', tu.territorytype) AS territorytype
                FROM crossbones.territoryupload tu
                LEFT JOIN crossbones.territorygroup tg ON LOWER(tg.territorygroupname) = LOWER(tu.territorygroupname) 
                WHERE tu.account_id = ? {$where_in_groups}{$where_reference}{$where_reason}
                ORDER BY tu.territoryname ASC";

        $landmarks = $this->db_read->fetchAll($sql, $sqlPlaceHolder);

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
    public function getFilteredVerificationTerritoryStringSearch($user_id, $params, $searchfields)
    {
        $sqlPlaceHolder = array($user_id);

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

        $where_type = "";
        if (isset($this->territoryType) AND ! empty($this->territoryType)) {
            $where_type = " AND t.territorytype IN (" . substr(str_repeat('?,', count($this->territoryType)), 0, -1) . ") ";
            $sqlPlaceHolder = array_merge($sqlPlaceHolder, $this->territoryType);
        } 

        $sql = "SELECT 
                    t.territory_id,
                    t.account_id,
                    t.territorycategory_id,
                    t.shape,
                    t.territoryname,
                    t.latitude,
                    t.longitude,
                    t.radius,
                    t.streetaddress,
                    t.city,
                    t.state,
                    t.zipcode,
                    t.country,
                    t.territorytype,
                    t.verifydate,
                    t.active,
                    u.* 
                FROM crossbones.territory t
                LEFT JOIN crossbones.unit_territory ut ON ut.territory_id = t.territory_id
                LEFT JOIN crossbones.unit u ON u.unit_id = ut.unit_id 
                LEFT JOIN crossbones.user_unitgroup uug ON uug.unitgroup_id = u.unitgroup_id
                LEFT JOIN crossbones.user ON user.user_id = uug.user_id
                WHERE user.user_id = ? AND t.active = 1 {$where_search_string} {$where_type}
                ORDER BY t.territoryname ASC";

        $territory = $this->db_read->fetchAll($sql, $sqlPlaceHolder);

        return $territory;
    }

    /**
     * Get the filtered landmarks by $params (landmark group ids)
     *
     * @params: int user_id
     * @params: array $params
     *
     * @return array | bool
     */    
    public function getFilteredVerificationTerritory($user_id, $params)
    {
        $sqlPlaceHolder = array($user_id);

        $where_type = "";
        if (isset($params['territorytype']) AND $params['territorytype'] != '') {
            $where_type = " AND t.territorytype = ? ";
            $sqlPlaceHolder[] = $params['territorytype'];
        } else {
            if (isset($this->territoryType) AND ! empty($this->territoryType)) {
                $where_type = "AND t.territorytype IN (" . substr(str_repeat('?,', count($this->territoryType)), 0, -1) . ") ";
                $sqlPlaceHolder = array_merge($sqlPlaceHolder, $this->territoryType);
            }
        }

        $where_verified = "";
        if (isset($params['verified']) AND $params['verified'] != '') {
            if ($params['verified'] == 'verified') {
                $where_verified = "AND t.verifydate > ? ";
            } else {
                $where_verified = "AND t.verifydate = ? ";
            }
            $sqlPlaceHolder[] = '0000-00-00';
        }

        $where_unit_id = "";
        if (isset($params['vehicle_id']) AND $params['vehicle_id'] != '') {
            $where_unit_id = "AND u.unit_id = ? ";
            $sqlPlaceHolder[] = $params['vehicle_id'];
        }

        $sql = "SELECT 
                    t.territory_id,
                    t.account_id,
                    t.territorycategory_id,
                    t.shape,
                    t.territoryname,
                    t.latitude,
                    t.longitude,
                    t.radius,
                    t.streetaddress,
                    t.city,
                    t.state,
                    t.zipcode,
                    t.country,
                    t.territorytype,
                    t.verifydate,
                    t.active,
                    u.*
                FROM crossbones.territory t
                LEFT JOIN crossbones.unit_territory ut ON ut.territory_id = t.territory_id
                LEFT JOIN crossbones.unit u ON u.unit_id = ut.unit_id 
                LEFT JOIN crossbones.user_unitgroup uug ON uug.unitgroup_id = u.unitgroup_id
                LEFT JOIN crossbones.user ON user.user_id = uug.user_id
                WHERE user.user_id = ? AND t.active = 1 {$where_type} {$where_verified} {$where_unit_id}
                ORDER BY t.territoryname ASC";

        $territory = $this->db_read->fetchAll($sql, $sqlPlaceHolder);

        return $territory;
    }

    /**
     * Get the territory group by $params (string search)
     *
     * @params: int user_id
     * @params: string search_string
     * @params: array $searchfields
     *
     * @return array | bool
     */
    public function getTerritoryGroupListStringSearch($user_id, $search_string, $searchfields)
    {
        $sqlPlaceHolder = array($user_id);

        $where_search_string = "";
        if (isset($search_string) AND $search_string != '') {
            if (! empty($searchfields) AND is_array($searchfields)) {
                $where_search_string = "AND (";

                foreach ($searchfields as $key => $fieldname) {
                    $where_search_string .= "tg.`".$fieldname."` LIKE ? OR ";
                    $sqlPlaceHolder[] = '%'.str_replace("_", "\_", $search_string).'%';
                }

                $where_search_string = substr($where_search_string, 0, -4);
                $where_search_string .= ")";
            }
        }

        $where_type = "";
        if (isset($this->territoryType) AND ! empty($this->territoryType)) {
            $where_type = "AND tg.territorytype IN (" . substr(str_repeat('?,', count($this->territoryType)), 0, -1) . ") ";
            $sqlPlaceHolder = array_merge($sqlPlaceHolder, $this->territoryType);
        } 

        $sql = "SELECT 
                    tg.*, count(t.territory_id) AS territory_count
                FROM user_territorygroup utg
                INNER JOIN territorygroup tg ON tg.territorygroup_id = utg.territorygroup_id
                LEFT JOIN territory t ON t.territorygroup_id = utg.territorygroup_id
                WHERE utg.user_id = ? AND tg.active = 1 {$where_search_string} {$where_type}
                GROUP BY tg.territorygroup_id
                ORDER BY tg.territorygroupname ASC";

        $territory = $this->db_read->fetchAll($sql, $sqlPlaceHolder);

        return $territory;
    }


    /**
     * Save landmark to database
     *
     * @param array params
     *
     * @return int|bool
     */    
    public function saveTerritory($params) 
    {
        if (! is_array($params) OR count($params) <= 0) {
            $this->setErrorMessage('err_param');
        } else {

            if (isset($params['territorygroupname'])) {
                unset($params['territorygroupname']);
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
            $values = substr(str_repeat('?,', count($params)), 0, -1);
            $sqlPlaceHolder = array_values($params);
            
            if ($bbox !== '') {
                $columns .= ',`boundingbox`';
                $values .= ',' . $bbox;
            }

            $sql = 'INSERT INTO crossbones.territory (' . $columns . ') VALUES (' . $values . ')';
            
            // NOTE: doctrine throws an error when using insert() due to 'boundingbox' geometry value (see error below), so we're using executeQuery() for now
            // error: Numeric value out of range: 1416 Cannot get geometry object from data you send to the GEOMETRY field
            //if ($this->db_read->insert('crossbones.landmark', $params)) {
            if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) {
                $sql = 'INSERT INTO crossbones.library (' . $columns . ') VALUES (' . $values . ')';
                if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) {
                }
                $sql = "SELECT territory_id FROM crossbones.territory WHERE account_id = ? AND territoryname = ? ORDER BY createdate DESC LIMIT 1";
                $sqlPlaceHolder = array($params['account_id'],$params['territoryname']);
                $result = $this->db_read->fetchAssoc($sql, $sqlPlaceHolder);
                return implode('',$result);
                // return $this->db_read->lastInsertId();
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
    public function saveTerritoryToTerritoryGroup($territory_id, $territorygroup_id)
    {
        if ($this->db_read->update('crossbones.territory', array('territorygroup_id' => $territorygroup_id), array('territory_id' => $territory_id))) {
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
    public function deleteTerritory($territory_id, $account_id, $reference) 
    {
        if ($this->db_read->update('crossbones.territory', array('active' => 0, 'territorygroup_id' => 0), array('territory_id' => $territory_id, 'account_id' => $account_id))) {    // remove/deactivate landmark
            if ($reference === true) {  // if it's a reference landmark, remove the landmark - unit association
                $this->db_read->delete('crossbones.unit_territory', array('territory_id' => $territory_id));
            } else {                    // else, remove the landmarkgroup - landmark association
                //$this->db_read->update('crossbones.territory', array('territorygroup_id' => 0), array('territory_id' => $territory_id));
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
    public function deleteTerritoryUpload($territoryupload_id, $account_id) 
    {
        if ($this->db_read->delete('crossbones.territoryupload', array('territoryupload_id' => $territoryupload_id, 'account_id' => $account_id))) {
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
    public function updateIncompleteTerritory($territoryupload_id, $params)
    {
        $sql = $updates = $bbox = '';
        $sqlPlaceHolder = array();
        
        $updates = "`" . implode("` = ?, `", array_keys($params)) . "` = ?";
        $sqlPlaceHolder = array_values($params);
        
        $sqlPlaceHolder[] = $territoryupload_id;

        $sql = 'UPDATE crossbones.territoryupload SET ' . $updates . ' WHERE territoryupload_id = ? LIMIT 1';

        // NOTE: doctrine throws an error when using update() due to 'boundingbox' geometry value (see error below), so we're using executeQuery() for now
        //if ($this->db_read->insert('crossbones.territory', $params)) {
        if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) {
            return true;
        } else {
            $this->setErrorMessage('err_database');
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
        if ($this->db_read->update('crossbones.territory', $params, array('territory_id' => $territory_id, 'account_id' => $account_id)) !== false) {    // remove/deactivate landmark
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
    public function updateTerritoryInfo($territory_id, $params)
    {
        $sql = $updates = $bbox = '';
        $sqlPlaceHolder = array();
        
        if (isset($params['boundingbox'])) {
            $bbox = $params['boundingbox'];
            unset($params['boundingbox']);
        }
        
        $updates = "`" . implode("` = ?, `", array_keys($params)) . "` = ?";
        $sqlPlaceHolder = array_values($params);
        
        if ($bbox !== '') {
            $updates .= ', `boundingbox` = ' . $bbox;
        }
        
        $sqlPlaceHolder[] = $territory_id;

        $sql = 'UPDATE crossbones.territory SET ' . $updates . ' WHERE territory_id = ? LIMIT 1';
       
        // NOTE: doctrine throws an error when using update() due to 'boundingbox' geometry value (see error below), so we're using executeQuery() for now
        // error: Numeric value out of range: 1416 Cannot get geometry object from data you send to the GEOMETRY field
        //if ($this->db_read->insert('crossbones.territory', $params)) {
        if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) {
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
    public function updateTerritoryGroupByTerritoryId($territory_id, $params)
    {   
        //if ($this->db_read->update('crossbones.territorygroup_territory', $params, array('territory_id' => $territory_id))) {
        if ($this->db_read->update('crossbones.territory', array('territorygroup_id' => $params['territorygroup_id']), array('territory_id' => $territory_id))) {
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
    public function updateTerritorygroupTerritory($territory_id, $params)
    {   
        //if ($this->db_read->update('crossbones.territorygroup_territory', $params, array('territory_id' => $territory_id))) {
        if ($this->db_read->update('crossbones.territory', array('territorygroup_id' => $params['territorygroup_id']), array('territory_id' => $territory_id))) {
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
    function getTerritorygroupTerritory($territory_id)
    {
        $data = array();
        $sql = "SELECT * 
                FROM territory t
                INNER JOIN territorygroup tg ON t.territorygroup_id = tg.territorygroup_id 
                WHERE t.territory_id = ? 
                LIMIT 1";

        $data = $this->db_read->fetchAll($sql, array($territory_id));
        return $data;
    }

    /**
     * Add landmarkgroup to landmark relation
     *
     * @param array params
     *
     * @return int|bool
     */
    public function addTerritorygroupTerritory($params)
    {
        
        //if ($this->db_read->insert('crossbones.territorygroup_territory', $params)) {
        if ($this->db_read->update('crossbones.territory', array('territorygroup_id' => $params['territorygroup_id']), array('territory_id' => $params['territory_id']))) {
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
    public function addTerritoryToVehicle($params) 
    {
        if ($this->db_read->insert('crossbones.unit_territory', $params)) {
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
    public function getTerritoryByUnitId($unit_id, $reference = null, $verified = '') 
    {
        $and_reference = $and_verified = "";
        $sqlPlaceHolder = array($unit_id);

        if ($reference === true) {
            $and_reference = "AND lm.territorytype = ? ";
            $sqlPlaceHolder[] = 'reference';
            if (! empty($verified)) {
                switch ($verified) {
                    case 'verified':
                        $and_verified = "AND lm.verifydate > ? ";
                        $sqlPlaceHolder[] = '0000-00-00';
                        break;
                    case 'no-verified':
                        $and_verified = "AND lm.verifydate = ? ";
                        $sqlPlaceHolder[] = '0000-00-00';
                        break;
                    case 'all':
                        $and_verified = "";
                        break;
                }
            }
        } else if ($reference === false) {
            $and_reference = "AND lm.territorytype = ? ";
            $sqlPlaceHolder[] = 'landmark';
        }

        $sql = "SELECT 
                    lm.territory_id,
                    lm.account_id,
                    lm.territorycategory_id,
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

        if ($landmarks = $this->db_read->fetchAll($sql, $sqlPlaceHolder)) {
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
    public function getTerritoryByTitle($account_id, $title)
    {
        $landmark = array();

        $sql = "SELECT 
                    lm.territory_id,
                    lm.account_id,
                    lm.territorycategory_id,
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
                    lm.active
                FROM crossbones.territory AS lm
                WHERE lm.territoryname = ? 
                AND lm.account_id = ? 
                AND lm.active = 1
                LIMIT 1";

        if (($landmark = $this->db_read->fetchAll($sql, array($title, $account_id))) !== false) {
            return $landmark;
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
    public function getUnitTerritoryByTitle($account_id, $unit_id, $title)
    {
        $landmark = array();

        $sql = "SELECT 
                    lm.territory_id,
                    lm.account_id,
                    lm.territorycategory_id,
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
                    lm.active
                FROM crossbones.territory AS lm
                LEFT JOIN crossbones.unit_territory ut ON ut.territory_id = lm.territory_id
                WHERE lm.territoryname = ? 
                AND lm.account_id = ? 
                AND ut.unit_id = ? 
                AND lm.active = 1
                LIMIT 1";

        if (($landmark = $this->db_read->fetchAll($sql, array($title, $account_id, $unit_id))) !== false) {
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
    public function getTerritoryByIds($territory_ids, $defaulttype = true)
    {
        $sqlPlaceHolder = array();
        $where_type = "";
        if ($defaulttype AND isset($this->territoryType) AND ! empty($this->territoryType)) {
            $where_type = "t.territorytype IN (" . substr(str_repeat('?,', count($this->territoryType)), 0, -1) . ") AND";
            $sqlPlaceHolder = array_values($this->territoryType);
        }

        if (isset($territory_ids) AND ! empty($territory_ids)) {
            $territories = substr(str_repeat('?,', count($territory_ids)), 0, -1);
            $sqlPlaceHolder = array_merge($sqlPlaceHolder, $territory_ids);
            
            $sql = "SELECT 
                        t.territory_id,
                        t.account_id,
                        t.territorycategory_id,
                        t.shape,
                        t.territorytype,
                        t.territoryname,
                        t.latitude,
                        t.longitude,
                        t.radius,
                        t.streetaddress,
                        t.city,
                        t.state,
                        t.zipcode,
                        t.country,
                        t.verifydate,
                        t.active,
                        tg.active as territorygroup_active,
                        tg.territorygroupname as territorygroupname,
                        tg.territorygroup_id as territorygroup_id,
                        asText(t.boundingbox) as boundingbox,
                        tc.territorycategoryname as territorycategoryname,
                        tc.active as territorycategory_active
                    FROM crossbones.territory t
                    LEFT JOIN crossbones.territorygroup tg ON tg.territorygroup_id = t.territorygroup_id
                    LEFT JOIN crossbones.territorycategory tc ON t.territorycategory_id = tc.territorycategory_id
                    WHERE {$where_type} t.territory_id IN ({$territories}) AND t.active = 1
                    ORDER BY t.territoryname";

            if (($territory = $this->db_read->fetchAll($sql, $sqlPlaceHolder)) !== false) {
                return $territory;
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
    public function getTerritoryUploadByIds($territoryupload_ids)
    {
        if (isset($territoryupload_ids) AND ! empty($territoryupload_ids)) {
            $territoryuploads = substr(str_repeat('?,', count($territoryupload_ids)), 0, -1);
            $sqlPlaceHolder = array_values($territoryupload_ids);
            
            $sql = "SELECT 
                        lmu.*,
                        lmu.territoryupload_id as territory_id,
                        lg.active as territorygroup_active,
                        lg.territorygroupname as territorygroupname,
                        lg.territorygroup_id as territorygroup_id,
                        lmu.territorytype as territorytype,
                        'circle' as shape
                    FROM crossbones.territoryupload AS lmu
                    LEFT JOIN crossbones.territorygroup AS lg ON LOWER(lg.territorygroupname) = LOWER(lmu.territorygroupname)
                    WHERE lmu.territoryupload_id IN ({$territoryuploads}) 
                    ORDER BY lmu.territoryname";

            if (($landmark = $this->db_read->fetchAll($sql, $sqlPlaceHolder)) !== false) {
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
    public function getUnitsByTerritoryId($territory_id)
    {
        $sql = "SELECT *
                FROM crossbones.unit_territory AS ul
                LEFT JOIN crossbones.unit AS u ON u.unit_id = ul.unit_id
                WHERE ul.territory_id = ?";
                                
        if ($units = $this->db_read->fetchAll($sql, array($territory_id))) {
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
    public function saveIncompleteTerritory($params)
    {
        $save_params = array(
            'account_id'            => $params['account_id'],
            'unit_id'               => $params['unit_id'],
            'territorycategory_id'  => $params['territorycategory_id'], 
            'territoryname'         => $params['territoryname'],
            'territorygroupname'    => $params['territorygroupname'], 
            'territorytype'         => $params['territorytype'], 
            'streetaddress'         => $params['streetaddress'], 
            'city'                  => $params['city'], 
            'state'                 => $params['state'], 
            'zipcode'               => $params['zipcode'], 
            'reference'             => 0, 
            'latitude'              => $params['latitude'], 
            'longitude'             => $params['longitude'], 
            'reason'                => $params['reason'], 
            'country'               => $params['country'], 
            'radius'                => $params['radius'], 
            'shape'                 => $params['shape'],
            'process'               => 0,
            'processdate'           => 0,
            'user_id'               => $params['user_id']
        );
        
        $columns = '`' . implode('`,`', array_keys($save_params)) . '`';
        $values = substr(str_repeat('?,', count($save_params)), 0, -1);
        
        $sqlPlaceHolder = array_values($save_params);
        
        $sql = "INSERT INTO `crossbones`.`territoryupload` ({$columns}) VALUES ({$values})";

        if ($this->db_read->executeQuery($sql, $sqlPlaceHolder)) {
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
    public function addTerritoryGroup($params) 
    {
        if (! is_array($params) OR count($params) <= 0) {
            $this->setErrorMessage('err_param');
        } else {
            if ($this->db_read->insert('crossbones.territorygroup', $params)) {
                return $this->db_read->lastInsertId();
            } else {
                $this->setErrorMessage('err_database');
            }
        }
        
        return false;
    }

    /**
     * Save landmarkgroup user association
     *
     * @param array params
     *
     * @return int|bool
     */    
    public function addUserTerritoryGroup($params) 
    {
        if (! is_array($params) OR count($params) <= 0) {
            $this->setErrorMessage('Invalid Information');
        } else {
            if ($this->db_read->insert('crossbones.user_territorygroup', $params)) {
                return $this->db_read->lastInsertId();
            } else {
                $this->setErrorMessage('Database Error');
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
    public function getTerritoryGroupByTitle($account_id, $title)
    {
        $sql = "SELECT *
                FROM territorygroup
                WHERE territorygroupname = ? AND account_id = ? AND active = 1
                LIMIT 1";
        
        $landmark = $this->db_read->fetchAll($sql, array($title, $account_id));
        if ($landmark !== false) {
            return $landmark;
        }

        return false;
    }
    
    /**
     * Get all territory categories
     *
     * @return array
     */
    public function getAllTerritoryCategories()
    {
        $sql = "SELECT * 
                FROM crossbones.territorycategory AS tc
                WHERE tc.active = 1";
        return $this->db_read->fetchAll($sql);    
    }

    /**
     * Get reference unit by territory id
     *
     * @return array
     */
    public function getUnitTerritoryByTerritoryId($territory_id)
    {
        $sql = "SELECT u.* 
                FROM crossbones.unit_territory as ut
                INNER JOIN crossbones.unit AS u ON u.unit_id = ut.unit_id
                WHERE ut.territory_id = ? 
                ORDER BY u.unitname 
                LIMIT 1";

        $unit = $this->db_read->fetchAll($sql, array($territory_id));

        if ($unit !== false AND ! empty($unit[0])) {
            return $unit[0];
        }

        return false;
    }  

    /**
     * Get all unit territory
     *
     * @return array
     */
    public function getUnitTerritoryByUserId($user_id)
    {
        $sql = "SELECT DISTINCT u.unit_id, u.* 
                FROM crossbones.unit as u
                LEFT JOIN crossbones.unit_territory AS ut ON u.unit_id = ut.unit_id
                WHERE u.account_id = ? 
                ORDER BY u.unitname";

        $unit = $this->db_read->fetchAll($sql, array($user_id));

        if ($unit !== false) {
            return $unit;
        }
        
        return false;
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
         $this->territoryType = $territory_type;
         if ($this->territoryType) {
             return true;
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
         return $this->territoryType;
     }

    /**
     * Get the current territory type(s)
     *
     * @return void
     */
     public function resetTerritoryType()
     {
         $this->territoryType = array('landmark','boundary','reference');
     }

    /**
     * Add territory group to user
     *
     * @param territorygroup_id
     * @param user_id
     *
     * @return bool|int
     */
    public function addTerritoryGroupToUser($territorygroup_id, $user_id)
    {
        return $this->db_write->insert('crossbones.user_territorygroup', array('territorygroup_id' => $territorygroup_id, 'user_id' => $user_id));
    }

    /**
     * Remove territory group from user
     *
     * @param territorygroup_id
     * @param user_id
     *
     * @return bool|int
     */
    public function removeTerritoryGroupFromUser($territorygroup_id, $user_id)
    {
        return $this->db_write->delete('crossbones.user_territorygroup', array('territorygroup_id' => $territorygroup_id, 'user_id' => $user_id));
    }

    /**
     * Get territory groups by group ids
     *
     * @params array group_id
     *
     * @return array | bool
     */
    public function getTerritoryGroupsByIds($user_id, $group_id)
    {
        $sqlPlaceHolder = array($user_id);
        $where_type = "";
        if (isset($this->territoryType) AND ! empty($this->territoryType)) {
            $where_type = "AND tg.territorytype IN (" . substr(str_repeat('?,', count($this->territoryType)), 0, -1) . ") ";
            $sqlPlaceHolder = array_merge($sqlPlaceHolder, array_values($this->territoryType));
        }

        $group_placeholders = substr(str_repeat('?,', count($group_id)), 0, -1);
        $sqlPlaceHolder = array_merge($sqlPlaceHolder, array_values($group_id));
        
        $sql = "SELECT * 
                FROM user_territorygroup utg
                LEFT JOIN territorygroup tg ON utg.territorygroup_id = tg.territorygroup_id
                WHERE utg.user_id = ? {$where_type} AND utg.territorygroup_id IN ({$group_placeholders})
                ORDER BY tg.territorygroupname ASC";

        $data = $this->db_read->fetchAll($sql, $sqlPlaceHolder);
        return $data;
    }

    /**
     * Update territory group
     *
     * @param int   territorygroup_id
     * @param array params
     *
     * @return bool
     */
    public function updateTerritoryGroupInfo($territorygroup_id, $params) 
    {
        if ($this->db_read->update('crossbones.territorygroup', $params, array('territorygroup_id' => $territorygroup_id)) !== false) {    // remove/deactivate landmark
            return true;
        }
        return false;
    }

    /**
     * Remove all territories from territory group by territory group id
     *
     * @param unitgroup_id
     *
     * @return bool
     */
    public function removeAllTerritoriesFromGroup($territorygroup_id)
    {
        if ($this->db_write->update('crossbones.territory', array('territorygroup_id' => 0), array('territorygroup_id' => $territorygroup_id)) !== false) {
            return true;
        }
        return false;
    }

    /**
     * Get the territory info by filtered paramaters
     *
     * @params: account_id, territorygroup_id, search_string
     *
     * @return array
     */
    public function getFilteredAvailableTerritories($account_id, $territorygroup_id, $search_string, $search_fields)
    {
        $sqlPlaceHolder = array($account_id, $territorygroup_id);
        $where_search_string = "";
        if (! empty($search_fields) AND is_array($search_fields)) {
            $where_search_string = "AND (";

            foreach ($search_fields as $key => $fieldname) {
                $where_search_string .= "`".$fieldname."` LIKE ? OR ";
                $sqlPlaceHolder[] = '%'.str_replace("_", "\_", $search_string).'%';
            }

            $where_search_string = substr($where_search_string, 0, -4);
    		$where_search_string .= ")";
        }

        $where_type = "";
        if (isset($this->territoryType) AND ! empty($this->territoryType)) {
            $where_type = "AND territorytype IN (" . substr(str_repeat('?,', count($this->territoryType)), 0, -1) . ") ";
            $sqlPlaceHolder = array_merge($sqlPlaceHolder, array_values($this->territoryType));            
        }

        $sql = "SELECT 
                    territory_id,
                    account_id,
                    territorycategory_id,
                    shape,
                    territoryname,
                    latitude,
                    longitude,
                    radius,
                    streetaddress,
                    city,
                    state,
                    zipcode,
                    country,
                    territorytype,
                    verifydate,
                    active 
                FROM territory
                WHERE account_id = ? AND territorygroup_id = ? AND active = 1 {$where_search_string} {$where_type}
                ORDER BY territoryname ASC";

        $data = $this->db_read->fetchAll($sql, $sqlPlaceHolder);

        return $data;
    }
    
    /**
     * Get territories by account id
     *
     * @params: account_id
     *
     * @return array
     */
    public function getTerritoriesByAccountId($account_id)
    {
        $sqlPlaceHolder = array($account_id);
        $where_type = "";
        if (isset($this->territoryType) AND ! empty($this->territoryType)) {
            $where_type = "AND territorytype IN (" . substr(str_repeat('?,', count($this->territoryType)), 0, -1) . ") ";
            $sqlPlaceHolder = array_merge($sqlPlaceHolder, array_values($this->territoryType)); 
        }
        
        // territories must have a group (only reference dont use grouping)
        $valid_group = "";
        if (count($this->territoryType) == 1 AND $this->territoryType[0] == 'landmark') {
            $valid_group = "AND territorygroup_id > 0";
        }
        
        $sql = "SELECT * 
                FROM territory
                WHERE account_id = ? AND active = 1 {$where_type} {$valid_group}
                ORDER BY territoryname ASC";

        $data = $this->db_read->fetchAll($sql, $sqlPlaceHolder);

        return $data;
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
        $sqlPlaceHolder = array($account_id, 'reference');
        $where = "";
        
        if (isset($params['vehicle_id'])) {
            $where = "AND u.unit_id = ?";
            $sqlPlaceHolder[] = $params['vehicle_id'];            
        } else if (isset($params['vehiclegroup_id'])) {
            $where = "AND u.unitgroup_id = ?";
            $sqlPlaceHolder[] = $params['vehiclegroup_id'];
        }
		/*
        if (isset($params['active']) AND ! empty($params['user_timezone'])) {
            if ($params['active'] == 1) {
                // active device (device expiration date has not passed)
                $currentdatetime = Date::locale_to_utc(date('Y-m-d H:i:s'), $params['user_timezone']);
                $where .= ' AND ua.renewaldate > ?';
                $sqlPlaceHolder[] = $currentdatetime;
            } else if ($params['active'] == 2) {
                // not active device
                $currentdate = Date::locale_to_utc(date('Y-m-d H:i:s'), $params['user_timezone']);
                $where .= ' AND ua.renewaldate < ?';
                $sqlPlaceHolder[] = $currentdatetime;
            }
        }
        */
        $sql = "SELECT t.*,
                       IF(t.verifydate = '0000-00-00', '0', '1') AS verified,
                       u.*,
                       ug.*
                FROM territory AS t
                INNER JOIN unit_territory AS ut ON ut.territory_id = t.territory_id
                INNER JOIN unit AS u ON u.unit_id = ut.unit_id
                LEFT JOIN unitgroup AS ug ON ug.unitgroup_id = u.unitgroup_id
                LEFT JOIN unitattribute AS ua ON ua.unitattribute_id = u.unitattribute_id
                WHERE t.account_id = ? AND t.territorytype = ? AND t.active = 1 {$where}
                ORDER BY u.unitname ASC";

        return $this->db_read->fetchAll($sql, $sqlPlaceHolder);
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
        $sqlPlaceHolder = array($account_id);
        $where_type = "";
        if (isset($this->territoryType) AND ! empty($this->territoryType)) {
            $where_type = "AND territorytype IN (" . substr(str_repeat('?,', count($this->territoryType)), 0, -1) . ") ";
            $sqlPlaceHolder = array_merge($sqlPlaceHolder, array_values($this->territoryType));            
        }
        
        $sql = "SELECT * 
                FROM territorygroup 
                WHERE account_id = ? AND active = 1 AND `default` = 1 {$where_type}
                LIMIT 1";

        return $this->db_read->fetchAssoc($sql, $sqlPlaceHolder);
    }

    /**
     * Get unprocess incomplete territories to be geo/rgeo by cron
     *
     * @return array
     */
    public function getIncompleteTerritoriesForProcess()
    {
        $sqlPlaceHolder = array(0, 'requires geo', 'requires rgeo');
        
        $sql = "SELECT * 
                FROM territoryupload as tu
                WHERE tu.process = ? AND tu.reason IN (?,?)";
        
        return $this->db_read->fetchAll($sql, $sqlPlaceHolder);
    }    
}
