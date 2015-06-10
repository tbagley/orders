<?php

namespace Controllers\Ajax;

use GTC\Component\Utils\Date;
use GTC\Component\Utils\Dropdown;
use GTC\Component\Utils\CSV\CSVBuilder;
use GTC\Component\Map\Tiger;
use GTC\Component\Utils\VinDecoder;

use Models\Logic\AddressLogic;

use Models\Data\AlertData;
use Models\Logic\AlertLogic;

use Models\Data\ContactData;
use Models\Logic\ContactLogic;

use Models\Data\TerritoryData;
use Models\Logic\TerritoryLogic;

use Models\Logic\UnitCommandLogic;

use Models\Data\UserData;
// use Models\Logic\UserData;
use Models\Logic\UserLogic;

use Models\Data\UnitData;
use Models\Logic\UnitLogic;

use Models\Data\VehicleData;
use Models\Logic\VehicleLogic;

use Symfony\Component\HttpFoundation\Request;

use GTC\Component\Utils\PDF\TCPDFBuilder;


/**
 * Class Vehicle
 *
 */
class Vehicle extends BaseAjax
{    
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->address_logic = new AddressLogic;
        $this->alert_data = new AlertData;
        $this->alert_logic = new AlertLogic;
        $this->contact_data = new ContactData;
        $this->contact_logic = new ContactLogic;
        $this->territory_data = new TerritoryLogic;
        $this->territory_logic = new TerritoryLogic;
        $this->unitcommand_logic = new UnitCommandLogic;
        $this->user_logic = new UserLogic;
        $this->unit_data = new UnitData;
        $this->unit_logic = new UnitLogic;
        $this->vehicle_data = new VehicleData;
        $this->vehicle_logic = new VehicleLogic;

    }

    /**
     * Upload landmarks
     *
     * @return void
     */    
    public function commandbatch()
    {
        // simulate AJAX params
        $view_data = $this->ajax_data;
        $response['code'] = 1; // set response code to 'fail' by default
        $message = 'ERROR'; // set response message as a fail message by default
        $files = $this->request->files;
        
        if (! empty($files)) {
            $file_path = '';
            foreach ($files as $f) {
                $file_path = $f->getPathname();
                break;  // only need to get one file path per upload
            }  
            
            $user_id    = $this->user_session->getUserId();
            $account_id = $this->user_session->getAccountId();

            if (! empty($account_id)) {
                if (file_exists($file_path)) {
                    if (strlen($file_path) > 3) {
                        $result = $this->vehicle_logic->importCommandBatch($account_id, $user_id, $file_path);
                        $response['result'] = $result;
                        $upload_message .= '<span class="text-green">SUCCESS: File Imported</span>';   
                    } else {
                        $upload_message .= '<span class="text-warning">ERROR: file name is too short</span>';   
                    }
                    unlink($file_path);                                 
                } else {
                    $upload_message .= '<span class="text-warning">ERROR: Cannot find uploaded file or file is empty</span>';   
                }
            } else {
                $upload_message .= '<span class="text-warning">ERROR: This account id is not valid</span>';
            }
        } else {
            $upload_message .= '<span class="text-warning">ERROR: Failed to upload the csv file</span>';
        }
        $upload_message .= '<p><span class="text-grey text-10">To start over, please select a new file...</span></p>';

        $response['message'] = $message;
        $response['upload_message'] = $upload_message;
        $view_data['data']['response'] = json_encode($response);

        $this->ajax_render('partial/iframe-upload-response.html.twig', $view_data);
    }

    /**
     * checkEmailFormat
     *
     * @return void
     */
    public function emailFormatCheck($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $email = NULL ; 
        }
        return $email;
    }

    /**
     * Fix Landmark (called via ajax)
     *
     * @return void
     */
    public function fixLandmark()
    {
        $ajax_data  = array();
        $user_id    = $this->user_session->getUserId();
        $post       = $this->request->request->all();

        if ($user_id) {
            $user = $this->user_logic->getUserById($user_id);
            if ($user !== false) {
                if (! empty($user) AND is_array($user)) {
                    $user_array = $user;
                    $user = array_pop($user);
                    $ajax_data['code'] = 0;        
                    $ajax_data['message'] = $this->vehicle_logic->fixLandmark($user['account_id'],$user_id,$post);
                }
            }
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Get account info (called via ajax)
     *
     * @return void
     */
    public function ajax()
    {
        $ajax_data  = array();
        $account_id = $this->user_session->getAccountId();
        $user_id    = $this->user_session->getUserId();
        $post       = $this->request->request->all();

        if ($user_id) {
            $user = $this->user_logic->getUserById($user_id);
            if ($user !== false) {
                if (! empty($user) AND is_array($user)) {

                    $user_array = $user;

                    $user = array_pop($user);

                    $ajax_data['code'] = 0;
                    $ajax_data['data']['user'] = $user;
                    $ajax_data['message'] = 'User Data Found for: ' . $post['action'];

                    if(is_array($post['unit_id'])){
                        $u = implode(',',$post['unit_id']);
                    } else {
                        $u = $post['unit_id'];
                    }
                    if(is_array($post['value'])){
                        $v = implode(',',$post['value']);
                    } else {
                        $v = $post['value'];
                    }

                    $user['account_id'] = $account_id ;

                    $result = $this->vehicle_logic->activity($user['account_id'],$user_id,$post['action'],$v,$u);

                    switch($post['action']) {

                        case      'alert-add' : $params['account_id'] = $user['account_id'];
                                                $params['alerttype_id'] = $post['type'];
                                                $params['alertname'] = $post['title'];
                                                //
                                                switch ($post['type']) {
                                                    case               2  :
                                                    case              '2' :
                                                    case               6  :
                                                    case              '6' : $params['alerttrigger'] = $post['duration'];
                                                                            break;
                                                    case               3  : 
                                                    case              '3' : $params['alerttrigger'] = $post['landmarktrigger'];
                                                                            break;
                                                    case               7  : 
                                                    case              '7' : $params['alerttrigger'] = $post['overspeed'];
                                                                            break;
                                                                  default : $params['alerttrigger'] = '0';
                                                }
                                                //
                                                switch($post['contactmode']){
                                                    case                '1' :
                                                    case                 1  :   $post['contactgroup'] = 0 ;
                                                                                break;
                                                    case                '2' :
                                                    case                 2  :   $post['contact'] = 0 ;
                                                                                break;
                                                                    default :   $post['contact'] = 0 ;
                                                                                $post['contactgroup'] = 0 ;
                                                                                break;
                                                }
                                                //
                                                switch($post['landmarkmode']){
                                                    case                '1' :
                                                    case                 1  :   //$params['territory'] = 'Single';
                                                                                $post['landmarkgroup'] = 0 ;
                                                                                break;
                                                    case                '2' :
                                                    case                 2  :   //$params['territory'] = 'Group';
                                                                                $post['landmark'] = 0 ;
                                                                                break;
                                                                    default :   //$params['territory'] = 'All';
                                                                                $post['landmark'] = 0 ;
                                                                                $post['landmarkgroup'] = 0 ;
                                                                                break;
                                                }
                                                //
                                                switch($post['vehiclemode']){
                                                    case                '1' :
                                                    case                 1  :   $params['unit'] = 'Single';
                                                                                $post['vehiclegroup'] = 0 ;
                                                                                break;
                                                    case                '2' :
                                                    case                 2  :   $params['unit'] = 'Group';
                                                                                $post['vehicle'] = 0 ;
                                                                                break;
                                                                    default :   $params['unit'] = 'All';
                                                                                $post['vehicle'] = 0 ;
                                                                                $post['vehiclegroup'] = 0 ;
                                                                                break;
                                                }
                                                //
                                                switch($post['days']){
                                                    case          1  :
                                                    case         '1' : $params['day'] = 'weekday';
                                                                       break;
                                                    case          2  :
                                                    case         '2' : $params['day'] = 'weekend';
                                                                       break;
                                                             default : $params['day'] = 'all';
                                                                       break;
                                                }
                                                //
                                                switch($post['hours']){
                                                    case           1  :
                                                    case          '1' : $params['time'] = 'range';
                                                                        break;
                                                              default : $params['time'] = 'all';
                                                                        break;
                                                }
                                                //
                                                $params['starthour'] = $post['starthour'];
                                                $params['endhour'] = $post['endhour'];
                                                $params['active'] = 1;
                                                $params['createdate'] = date('Y-m-d H:i:s');
                                                $alert_id = $this->alert_logic->addAlert($params);
                                                $ajax_data['alert_id'] = '#' . $alert_id . '#' ;
                                                if($alert_id){
                                                    $params = null;
                                                    $params['alert_id'] = $alert_id;
                                                    $params['mode'] = $post['vehiclemode'];
                                                    $params['unit_id'] = $post['vehicle'];
                                                    $params['unitgroup_id'] = $post['vehiclegroup'];
                                                    $result = $this->alert_logic->addAlertUnit($params);
                                                    //
                                                    $params = null;
                                                    $params['alert_id'] = $alert_id;
                                                    $params['territory_id'] = $post['landmark'];    
                                                    $params['territorygroup_id'] = $post['landmarkgroup'];
                                                    $result = $this->alert_logic->addAlertTerritory($params);
                                                    //
                                                    $params = null;
                                                    $params['alert_id'] = $alert_id;
                                                    $params['method'] = $post['contactmode'];
                                                    $params['mode'] = 0;
                                                    $params['contact_id'] = $post['contact'];
                                                    $params['contactgroup_id'] = $post['contactgroup'];
                                                    $result = $this->alert_logic->addAlertContact($params);
                                                    //
                                                    $ajax_data['message'] = 'New Alert Created: ' . $alert_id;
                                                    $ajax_data['value'] = $result['value'] ;
                                                } else {
                                                    $errors = $this->alert_logic->getErrorMessage();
                                                    $ajax_data['alert'] = 'Error' ;
                                                    $ajax_data['message'] = $errors;
                                                    $result['error'] = 'database error' ;
                                                }
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['action'] ;
                                                $ajax_data['mode'] = $post['action'] ;
                                                break;

                        case            'all' : $result = $this->vehicle_logic->ajaxAll($user,$post['unit_id'],$post['element'],$post['value']);
                                                $ajax_data['message'] = 'Result... ' . $result['value'];
                                                $ajax_data['action'] = $post['unit_id'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['mode'] = 'clicklandmark' ;
                                                $ajax_data['value'] = $result['value'] ; //'wizard-pending' ;
                                                break;

                        case     'allDevices' : $result = $this->vehicle_logic->ajaxAllDevices($user,$post['value'],$post['unit_id']);
                                                $zoomLevel = $post['element'] ;
                                                while(($uniqueLatLngPair<2)&&($attempts<20)){
                                                    $cluster=null;
                                                    $cluster=array();
                                                    $uniqueLatLngPair=0;
                                                    $attempts++;
                                                    foreach ( $result as $k1 => $v1 ) {
                                                        $ajax_data['zoom'] = $post['element'] ;
                                                        switch ($post['element']) {

                                                            case             '1' :
                                                            case              1  :
                                                            case             '2' :
                                                            case              2  :
                                                            case             '3' :
                                                            case              3  :
                                                            case             '4' :
                                                            case              4  :
                                                            case             '5' :
                                                            case              5  :
                                                            case             '6' :
                                                            case              6  :
                                                            case             '7' :
                                                            case              7  :
                                                            case             '8' :
                                                            case              8  : $lat = round($v1['latitude']*1);
                                                                                   $lng = round($v1['longitude']*1);
                                                                                   break;

                                                            case             '9' :
                                                            case              9  :
                                                            case            '10' :
                                                            case             10  : $lat = round($v1['latitude']*10);
                                                                                   $lng = round($v1['longitude']*10);
                                                                                   break;

                                                            case            '11' :
                                                            case             11  :
                                                            case            '12' :
                                                            case             12  : 
                                                            case            '13' :
                                                            case             13  : $lat = round($v1['latitude']*100);
                                                                                   $lng = round($v1['longitude']*100);
                                                                                   break;

                                                            case            '14' :
                                                            case             14  :
                                                            case            '15' :
                                                            case             15  :
                                                            case            '16' :
                                                            case             16  :
                                                            case            '17' :
                                                            case             17  : $lat = round($v1['latitude']*1000);
                                                                                   $lng = round($v1['longitude']*1000);
                                                                                   break;

                                                            case            '18' :
                                                            case             18  :
                                                            case            '19' :
                                                            case             19  :
                                                            case            '20' :
                                                            case             20  :
                                                            case            '21' :
                                                            case             21  :
                                                            case            '22' :
                                                            case             22  :
                                                            case            '23' :
                                                            case             23  :
                                                            case            '24' :
                                                            case             24  :
                                                            case            '25' :
                                                            case             25  :
                                                            case            '26' :
                                                            case             26  :
                                                            case            '27' :
                                                            case             27  :
                                                            case            '28' :
                                                            case             28  :
                                                            case            '29' :
                                                            case             29  : $lat = $v1['latitude'];
                                                                                   $lng = $v1['longitude'];
                                                                                   break;

                                                                         default : $lat = round($v1['latitude']/10);
                                                                                   $lng = round($v1['longitude']/10);

                                                        }
                                                        $buf = $lat . '_' . $lng;
                                                        if(!($cluster[$buf])){
                                                            $uniqueLatLngPair++;
                                                        }
                                                        $cluster[$buf]['count']++ ;
                                                        $cluster[$buf]['unit_id'] .= $v1['unit_id'] ;
                                                        $cluster[$buf]['justTheseDevices'][] = $v1['unit_id'] ;
                                                        if($cluster[$buf]['unitname']){
                                                            $cluster[$buf]['unitname'] .= "<br>\n" ;
                                                        }
                                                        $cluster[$buf]['unitname'] .= $v1['unitname'] ; // . ' (' . $zoomLevel . ':' . $post['element'] . ':' . $buf . ')' ;
                                                        $cluster[$buf]['latitude'] = $v1['latitude'] ;
                                                        $cluster[$buf]['longitude'] = $v1['longitude'] ;
                                                    }
                                                    $post['element']++;
                                                }
                                                $ajax_data['action'] = $post['dbid'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['mode'] = $post['action'] ;
                                                $ajax_data['value'] = $cluster ; //'wizard-pending' ;
                                                break;

                        case 'batch-commands' : $result = $this->vehicle_logic->ajaxBatch($user['account_id'],$user_id,$post['unit_id'],explode ( "\n" , $post['value'] ));
                                                $ajax_data['message'] = 'Result... #' . $post['unit_id'] . '# : ' . $result['a'] . ' : ' . $result['attempts'] . ' / ' . $result['inserts'];
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['mode'] = $post['action'] ;
                                                $ajax_data['value'] = $result['attempts'] ;
                                                break;

                        case  'clicklandmark' : $result = $this->vehicle_logic->ajaxUpdate($user,$post['unit_id'],$post['element'],$post['value']);
                                                $ajax_data['message'] = 'Result... ' . $result['value'];
                                                $ajax_data['action'] = $post['unit_id'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['mode'] = 'clicklandmark' ;
                                                $ajax_data['value'] = $result['value'] ; //'wizard-pending' ;
                                                break;

                        case    'contact-add' : $params['account_id'] = $user['account_id'];
                                                $params['user_id'] = '0';
                                                $params['cellcarrier_id'] = $post['cellcarrier_id'];
                                                $params['firstname'] = $post['firstname'];
                                                $params['lastname'] = $post['lastname'];
                                                $params['email'] = $post['email'];
                                                $params['cellnumber'] = $post['cellnumber'];
                                                $params['contactstatus'] = 'active';
                                                $params['createdate'] = date('Y-m-d');
                                                $email = $this->emailFormatCheck($post['email']);
                                                if($email){
                                                    $result = $this->contact_logic->addContact($params);
                                                    if($result){
                                                        $ajax_data['message'] = 'New Contact Created' ;
                                                        $ajax_data['action'] = $post['action'] ;
                                                        $ajax_data['value'] = $result['value'] ;
                                                        if(($result>0)&&($post['contactgroup'])){
                                                            $params = array();
                                                            $params['contact_id'] = $result;
                                                            $params['contactgroup_id'] = $post['contactgroup'];
                                                            $params['method'] = '0';
                                                            $params['createdate'] = date('Y-m-d');
                                                            $result = $this->contact_logic->addContactToContactGroup($params);
                                                            if($result){
                                                                $ajax_data['message'] = 'New Contact Created and Added to Group';
                                                            }
                                                        }
                                                    } else {
                                                        $errors = $this->contact_logic->getErrorMessage();
                                                        $error = 'ERROR: ' . str_replace('err_', '', implode(', ',$errors));
                                                        $ajax_data['action'] = 'alert' ;
                                                        $ajax_data['message'] = $error;
                                                        $result['error'] = 'database error' ;
                                                    }
                                                } else {
                                                    $ajax_data['action'] = 'alert' ;
                                                    $ajax_data['message'] = 'email address format is incorrect' ;
                                                }
                                                $ajax_data['element'] = $post['action'] ;
                                                $ajax_data['mode'] = $post['action'] ;
                                                break;

                    case   'contactgroup-add' : $result = $this->contact_logic->addContactGroup($user['account_id'],$post['name']);
                                                if($result){
                                                    $ajax_data['message'] = 'New Contact Group Created';
                                                    $ajax_data['action'] = $post['action'] ;
                                                } else {
                                                    $result['error'] = 'database error' ;
                                                }
                                                $ajax_data['element'] = $post['action'] ;
                                                $ajax_data['mode'] = $post['action'] ;
                                                break;

                        case       'dbupdate' : $uid = array_pop(explode('-',$post['element']));
                                                $ajax_data['message'] = 'dbupdate inbounds... ' . $post['dbid'] . ' / ' . $uid . ' / ' . $post['value'] . ' / ' . $post['unit_id'];
                                                $result = $this->vehicle_logic->ajaxDbUpdate($user,$post['dbid'],$uid,$post['value'],$post);
                                                $ajax_data['message'] = $result['message'];
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['mode'] = 'updated' ;
                                                $ajax_data['value'] = $result['value'] ;
                                                switch($post['dbid']){
                                                    case         'crossbones-territory-streetaddress' :
                                                    case                  'crossbones-territory-city' :
                                                    case               'crossbones-territory-zipcode' : $ajax_data['LandmarkGet'] = $uid;
                                                                                                        $ajax_data['LandmarkRid'] = $post['unit_id'];
                                                                                                        $eida = explode('-',$post['element']);
                                                                                                        $ajax_data['LandmarkTbl'] = $eida[1]; 
                                                                                                        break;
                                                    case               'crossbones-territory-latlong' : $tbla = explode(':',$post['value']);
                                                                                                        $ajax_data['LandmarkTbl'] = $tbla[2]; 
                                                                                                        $ajax_data['mode'] = 'latlong' ;
                                                }
                                                break;

                        case     'deverified' :
                        case       'verified' : $uid = $post['unit_id'];
                                                $result = $this->vehicle_logic->ajaxDbUpdate($user,$post['action'],$uid,$post['value'],$post);
                                                $ajax_data['message'] = $result['message'];
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['dbid'] ;
                                                $ajax_data['mode'] = 'verification' ;
                                                $ajax_data['value'] = $result['value'] ;
                                                break;

                        case       'dbdelete' : $uid = $post['unit_id'];
                                                $ajax_data['message'] = 'dbdelete inbounds... ' . $post['dbid'] . ' / ' . $uid . ' / ' . $post['value'] . ' / ' . $post['unit_id'];
                                                $result = $this->vehicle_logic->ajaxDbDelete($user,$post['dbid'],$uid,$post['value'],$post);
                                                $ajax_data['message'] = $result['message'];
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['mode'] = 'deleted' ;
                                                $ajax_data['value'] = $result['value'] ;
                                                break;

                case           'delete-alert' :
                case  'delete-commandpending' :
                case         'delete-contact' :
                case    'delete-contactgroup' :
                case      'delete-incomplete' :
                case        'delete-landmark' :
                case            'delete-repo' :
            case    'delete-scheduled-report' :
                case  'delete-territorygroup' :
                case       'delete-unitgroup' :
                case            'delete-user' :
                case        'delete-usertype' : $uid = $post['unit_id'];
                                                $ajax_data['message'] = 'delete inbounds... ' . $post['dbid'] . ' / ' . $uid . ' / ' . $post['value'] . ' / ' . $post['unit_id'];
                                                $result = $this->vehicle_logic->ajaxDbUpdate($user,$post['action'],$uid,0,$post);
                                                $ajax_data['message'] = $result['message'];
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['mode'] = 'deleted' ;
                                                $ajax_data['value'] = $result['value'] ;
                                                break;

                        case    'device-edit' : if($post['element']=='load'){
                                                    $result = $this->vehicle_logic->ajaxFormFill($user_id,$user,$post['action'],$post['unit_id']);
                                                    $ajax_data['value'] = $result ;
                                                    $ajax_data['message'] = $post['action'] . ', target user_id:' . $post['unit_id'] . ', account_id: ' . $user['account_id'] . ', user_id: ' . $user_id;
                                                    $ajax_data['action'] = $post['action'] ;
                                                    $ajax_data['element'] = $post['action'] ;
                                                    $ajax_data['uid'] = $post['unit_id'];
                                                    $ajax_data['mode'] = 'device-edit' ;
                                                }
                                                break;

                case             'alert-edit' : if($post['element']=='load'){
                                                    $result = $this->vehicle_logic->ajaxFormFill($user_id,$user,$post['action'],$post['unit_id']);
                                                    $ajax_data['value'] = $result ;
                                                    $ajax_data['message'] = $post['action'] . ', target landmarkgroup_id:' . $post['unit_id'] . ', account_id: ' . $user['account_id'] . ', user_id: ' . $user_id;
                                                    $ajax_data['action'] = $post['action'] ;
                                                    $ajax_data['element'] = $post['action'] ;
                                                    $ajax_data['uid'] = $post['unit_id'];
                                                    $ajax_data['mode'] = 'formfill-alert' ;
                                                }
                                                break;

                case           'edit-contact' : if($post['element']=='load'){
                                                    $result = $this->vehicle_logic->ajaxFormFill($user_id,$user,$post['action'],$post['unit_id']);
                                                    $ajax_data['value'] = $result ;
                                                    $ajax_data['message'] = $post['action'] . ', target landmarkgroup_id:' . $post['unit_id'] . ', account_id: ' . $user['account_id'] . ', user_id: ' . $user_id;
                                                    $ajax_data['action'] = $post['action'] ;
                                                    $ajax_data['element'] = $post['action'] ;
                                                    $ajax_data['uid'] = $post['unit_id'];
                                                    $ajax_data['mode'] = 'formfill-contact' ;
                                                }
                                                break;

                case     'edit-contact-group' : if($post['element']=='load'){
                                                    $result = $this->vehicle_logic->ajaxFormFill($user_id,$user,$post['action'],$post['unit_id']);
                                                    $ajax_data['value'] = $result ;
                                                    $result = $this->vehicle_logic->ajaxGroups('contacts',$user['account_id'],$post['unit_id']);
                                                    $ajax_data['contacts'] = $result ;
                                                    $ajax_data['message'] = $post['action'] . ', target landmarkgroup_id:' . $post['unit_id'] . ', account_id: ' . $user['account_id'] . ', user_id: ' . $user_id;
                                                    $ajax_data['action'] = $post['action'] ;
                                                    $ajax_data['element'] = $post['action'] ;
                                                    $ajax_data['uid'] = $post['unit_id'];
                                                    $ajax_data['mode'] = 'formfill-contact-group' ;
                                                }
                                                break;

                case    'edit-landmark-group' : if($post['element']=='load'){
                                                    $result = $this->vehicle_logic->ajaxFormFill($user_id,$user,$post['action'],$post['unit_id']);
                                                    $ajax_data['value'] = $result ;
                                                    $result = $this->vehicle_logic->ajaxGroups('territory',$user['account_id'],$post['unit_id']);
                                                    $ajax_data['territories'] = $result ;
                                                    $ajax_data['message'] = $post['action'] . ', target landmarkgroup_id:' . $post['unit_id'] . ', account_id: ' . $user['account_id'] . ', user_id: ' . $user_id;
                                                    $ajax_data['action'] = $post['action'] ;
                                                    $ajax_data['element'] = $post['action'] ;
                                                    $ajax_data['uid'] = $post['unit_id'];
                                                    $ajax_data['mode'] = 'formfill' ;
                                                }
                                                break;

                    case 'edit-vehicle-group' : if($post['element']=='load'){
                                                    $result = $this->vehicle_logic->ajaxGroups('vehiclegroupdevices',$user['account_id'],$post['value']);
                                                    $ajax_data['vehiclegroupdevices1'] = $result ;
                                                    $result = $this->vehicle_logic->ajaxGroups('vehiclegroupdevices',$user['account_id'],$post['unit_id']);
                                                    $ajax_data['vehiclegroupdevices2'] = $result ;
                                                    $result = $this->vehicle_logic->ajaxGroups('vehiclegroupusers',$user['account_id'],$post['value']);
                                                    $ajax_data['vehiclegroupusers'] = $result ;
                                                    $result = $this->vehicle_logic->ajaxGroups('vehiclegroupusertypes',$user['account_id'],$post['value']);
                                                    $ajax_data['vehiclegroupusertypes'] = $result ;
                                                    $ajax_data['message'] = $post['action'] . ', target group 1:' . $post['unit_id'] . ', target group 2:' . $post['value'] . ', account_id: ' . $user['account_id'] . ', user_id: ' . $user_id;
                                                    $ajax_data['action'] = $post['action'] ;
                                                    $ajax_data['element'] = $post['action'] ;
                                                    $ajax_data['uid'] = $post['unit_id'];
                                                    $ajax_data['value'] = $post['action'] ;
                                                    $ajax_data['mode'] = 'formfill' ;
                                                }
                                                break;

                    case             'got-it' : $result = $this->vehicle_logic->ajaxGotIt($user_id,$post['value']);
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['action'] ;
                                                $ajax_data['uid'] = $post['unit_id'];
                                                $ajax_data['value'] = $post['action'] ;
                                                $ajax_data['mode'] = 'got-it' ;
                                                break;

                    case               'init' : switch($post['element']){

                                                    case          'alert-add-contact' :
                                                    case     'alert-add-contactgroup' :
                                                    case         'alert-add-landmark' :
                                                    case    'alert-add-landmarkgroup' :
                                                    case          'alert-add-vehicle' :
                                                    case     'alert-add-vehiclegroup' : 
                                                    case        'contact-add-carrier' : 
                                                    case          'contact-add-group' : $result = $this->vehicle_logic->ajaxInit($user,$post['element']);
                                                                                        $ajax_data['action'] = $post['action'] ;
                                                                                        $ajax_data['element'] = $post['element'] ;
                                                                                        $ajax_data['mode'] = 'init' ;
                                                                                        $ajax_data['value'] = $result ;
                                                                                        break;
                        
                                                }
                                                break;

                    case  'landmarkgroup-add' : $title = $post['title'];
                                                $result = $this->territory_logic->saveTerritory($user['account_id'], $latitude, $longitude, $title, $radius, $street_address, $city, $state, $zip, $country, $shape, $type, $group, $coordinates, $category_id = 0, $reference = false);
                                                if($result){
                                                    $ajax_data['message'] = 'New Landmark Created';
                                                    $ajax_data['action'] = $post['action'] ;
                                                } else {
                                                    $result['error'] = 'database error' ;   
                                                }
                                                $ajax_data['element'] = $post['action'] ;
                                                $ajax_data['mode'] = $post['action'] ;
                                                break;

                 case       'device-transfer' : $result = $this->vehicle_logic->ajaxLoadList($user_id,$user['account_id'],'search-transfer-from',$post['value'],'');
                                                $ajax_data['from'] = $result;
                                                $result = $this->vehicle_logic->ajaxLoadList($user_id,$user['account_id'],'search-transfer-to',$post['unit_id'],'');
                                                $ajax_data['to'] = $result;
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['action'] ;
                                                $ajax_data['mode'] = 'load-transfer-devices' ;
                                                $ajax_data['value'] = $buffer ;
                                                $ajax_data['message'] = 'user_id=' . $user_id . ':user=' . $user . ':element=' . $post['element'] . ':value=' . $ajax_data['value'] . ':action=' . $post['action'] . ':uid=' . $post['unit_id'];
                                                break;

                 case   'landmark-latlngedit' : $result = $this->vehicle_logic->ajaxUpdate($user,$post['unit_id'],$post['element'],$post['value']);
                                                $ajax_data['message'] = 'Result... ' . $result['value'];
                                                $ajax_data['action'] = $post['unit_id'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['mode'] = 'landmark-latlngedit' ;
                                                $ajax_data['value'] = $result['value'] ; //'wizard-pending' ;
                                                break;

                 case      'landmark-polygon' : $result = $this->vehicle_logic->ajaxUpdate($user,$post['unit_id'],$post['element'],$post['value']);
                                                $ajax_data['message'] = 'Result... ' . $result['value'];
                                                $ajax_data['action'] = $post['unit_id'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['mode'] = 'landmark-polygon' ;
                                                $ajax_data['value'] = $result['value'] ; //'wizard-pending' ;
                                                $ajax_data['alert'] = 'Updated' ;
                                                break;

                 case 'load-transfer-devices' : $buffer = array_pop(explode('-',$post['element']));
                                                $result = $this->vehicle_logic->ajaxLoadList($user_id,$user['account_id'],$post['element'],$post['unit_id'],$post['value']);
                                                $ajax_data[$buffer] = $result;
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['mode'] = $post['action'] ;
                                                $ajax_data['value'] = $buffer ;
                                                $ajax_data['message'] = 'user_id=' . $user_id . ':user=' . $user . ':element=' . $post['element'] . ':value=' . $ajax_data['value'] . ':action=' . $post['action'] . ':uid=' . $post['unit_id'];
                                                break;

                 case     'mark-for-transfer' : $transferee_account_id = explode('-',$post['value']);
                                                $result = $this->vehicle_logic->ajaxLoadTransferee($transferee_account_id[1],$transferee_account_id[0]);
                                                $ajax_data['transferee'] = $result;
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['action'] ;
                                                $ajax_data['mode'] = $post['action'] ;
                                                $ajax_data['value'] = $post['value'] ;
                                                $ajax_data['message'] = 'hello world ' . $result['accountname'] . ', ' . $result['address'] . ', ' . $result['phonenumber'] . ', ' . $transferee_account_id[1];
                                                break;

                case        'schedule-report' : $ajax_data['message'] = 'schedule-report - ' . $post['action'];
                                                $result = $this->vehicle_logic->ajaxScheduleReport($user['account_id'],$user_id,$post);
                                                if($result){
                                                    $ajax_data['alert'] = 'Updated' ;
                                                    $ajax_data['code'] = 0;
                                                } else {
                                                    $ajax_data['alert'] = 'ERROR';
                                                    $ajax_data['code'] = 1;
                                                }
                                                $ajax_data['value'] = $result;
                                                break;

                case       'scheduled-report' : if($post['element']=='load'){
                                                    $result = $this->vehicle_logic->ajaxFormFill($user_id,$user,$post['action'],$post['unit_id']);
                                                    $ajax_data['value'] = $result ;
                                                    $ajax_data['message'] = $post['action'] . ', target schedulereport_id:' . $post['unit_id'] . ', account_id: ' . $user['account_id'] . ', user_id: ' . $user_id;
                                                    $ajax_data['action'] = $post['action'] ;
                                                    $ajax_data['element'] = $post['action'] ;
                                                    $ajax_data['uid'] = $post['unit_id'];
                                                    $ajax_data['mode'] = 'formfill-schedulereport' ;
                                                }
                                                break;

            // case 'scheduled-report-edit-save' : // if($post['element']=='load'){
            //                                         // $result = $this->vehicle_logic->ajaxFormFill($user_id,$user,$post['action'],$post['unit_id']);
            //                                         // $ajax_data['value'] = $result ;
            //                                         // $ajax_data['message'] = $post['action'] . ', target scheduled-report-edit-save:' . $post['unit_id'] . ', account_id: ' . $user['account_id'] . ', user_id: ' . $user_id;
            //                                         // $ajax_data['action'] = $post['action'] ;
            //                                         // $ajax_data['element'] = $post['action'] ;
            //                                         // $ajax_data['uid'] = $post['unit_id'];
            //                                         // $ajax_data['mode'] = 'formfill-schedulereport' ;
            //                                     // }
            //                                     break;

                 case        'selectAllClear' : $ajax_data['message'] = 'v=' . $post['value'] . ': e=' . $post['element'] . ': u=' . $post['unit_id'];
                                                foreach($post['dbid'] as $k => $v){
                                                    $count++ ;
                                                    $result = $this->vehicle_logic->ajaxSelections($user['account_id'],$user_id,$post['element'],$post['unit_id'].'-'.$v,$post['value']);
                                                }
                                                if($result['permission']){
                                                    $ajax_data['alert'] = 'Updated' ;
                                                } else {
                                                    $ajax_data['alert'] = 'Change Denied' ;
                                                    // $ajax_data['alert'] = $post['element'] ;
                                                }
                                                $ajax_data['code'] = 0 ;
                                                $ajax_data['message'] = 'selections';
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['action'] ;
                                                $ajax_data['mode'] = $post['action'] ;
                                                $ajax_data['value'] = $post['value'] ;
                                                $result='';
                                                break;

                 case            'selections' : $ajax_data['message'] = 'v=' . $post['value'] . ': e=' . $post['element'] . ': u=' . $post['unit_id'];
                                                $result = $this->vehicle_logic->ajaxSelections($user['account_id'],$user_id,$post['element'],$post['unit_id'],$post['value']);
                                                if($result['permission']){
                                                    $ajax_data['alert'] = 'Updated' ;
                                                } else {
                                                    $ajax_data['alert'] = 'Change Denied' ;
                                                    // $ajax_data['alert'] = $post['element'] ;
                                                }
                                                $ajax_data['code'] = 0 ;
                                                $ajax_data['message'] = 'selections';
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['action'] ;
                                                $ajax_data['mode'] = $post['action'] ;
                                                $ajax_data['value'] = $post['value'] ;
                                                $result='';
                                                break;

                 case   'transfer-authorized' : $ajax_data['message'] = 'v=' . $post['value'] . ': e=' . $post['element'] . ': u=' . $post['unit_id'];
                                                $result = $this->vehicle_logic->ajaxTransferOffer($user['account_id'],$user_id,$post['value'],$post['unit_id']);
                                                $ajax_data['message'] = $result;
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['action'] ;
                                                $ajax_data['mode'] = $post['action'] ;
                                                $ajax_data['value'] = $post['value'] ;
                                                $result='';
                                                break;

                 case     'transfer-canceled' : $ajax_data['message'] = 'v=' . $post['value'] . ': e=' . $post['element'] . ': u=' . $post['unit_id'];
                                                $result = $this->vehicle_logic->ajaxTransferCancel($user['account_id'],$post['value'],$post['unit_id']);
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['action'] ;
                                                $ajax_data['mode'] = $post['action'] ;
                                                $ajax_data['value'] = $post['value'] ;
                                                $result='';
                                                break;

                 case       'transfer-accept' : $ajax_data['message'] = 'u=' . implode(', ',$post['unit_id']);
                                                $result = $this->vehicle_logic->ajaxTransferAccept($user['account_id'],$post['unit_id']);
                                                $ajax_data['message'] = $result;
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['action'] ;
                                                $ajax_data['mode'] = $post['action'] ;
                                                $ajax_data['value'] = $post['value'] ;
                                                $result='';
                                                break;

                 case       'transfer-reject' : $ajax_data['message'] = 'u=' . implode(', ',$post['unit_id']);
                                                $result = $this->vehicle_logic->ajaxTransferReject($user['account_id'],$post['unit_id']);
                                                $ajax_data['message'] = $result;
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['action'] ;
                                                $ajax_data['mode'] = $post['action'] ;
                                                $ajax_data['value'] = $post['value'] ;
                                                $result='';
                                                break;

                    case   'verification-add' : $title = '';
                                                $latitude = '33.66007';
                                                $longitude = '-117.69663';
                                                $radius = 660;
                                                $street_address = $post['street_address'];
                                                $city = $post['city'];
                                                $state = $post['state'];
                                                $zip = $post['zip'];
                                                $country = $post['country'];
                                                $shape = 'circle';
                                                $result = $this->territory_logic->newVerification($user, $post['unit_id'], $latitude, $longitude, $title, $radius, $street_address, $city, $state, $zip, $country, $shape);

                                                if($result['permission']){
                                                    if($result['error']){
                                                        $ajax_data['alert'] = $result['error'] ;
                                                        $result['error'] = $result['error'] ;
                                                    } else {
                                                        $ajax_data['alert'] = 'Record Added' ;
                                                        $ajax_data['message'] = 'New Verification Address Created';
                                                        $ajax_data['action'] = $post['action'] ;
                                                    }
                                                } else {
                                                    // $ajax_data['alert'] = 'Change Denied' ;
                                                    $ajax_data['code'] = 0 ;
                                                }
                                                $ajax_data['element'] = $post['action'] ;
                                                $ajax_data['mode'] = 'verification' ;
                                                $ajax_data['action'] = 'verification' ;
                                                break;

            case   'verification-address-new' : $title = $post['value'][0];
                                                $latitude = $post['value'][1];
                                                $longitude = $post['value'][2];
                                                $radius = $post['value'][8];
                                                $street_address = $post['value'][3];
                                                $city = $post['value'][4];
                                                $state = $post['value'][5];
                                                $zip = $post['value'][6];
                                                $country = $post['value'][7];
                                                $shape = 'circle';
                                                $result = $this->territory_logic->newVerification($user, $post['unit_id'], $latitude, $longitude, $title, $radius, $street_address, $city, $state, $zip, $country, $shape);

                                                if($result['permission']){
                                                    if($result['error']){
                                                        $ajax_data['alert'] = $result['error'] ;
                                                        $result['error'] = $result['error'] ;
                                                    } else {
                                                        $ajax_data['alert'] = 'Record Added' ;
                                                        $ajax_data['message'] = 'New Verification Address Created';
                                                        $ajax_data['action'] = $post['action'] ;
                                                    }
                                                } else {
                                                    // $ajax_data['alert'] = 'Change Denied' ;
                                                    $ajax_data['code'] = 0 ;
                                                }
                                                $ajax_data['element'] = $post['action'] ;
                                                $ajax_data['mode'] = 'verification' ;
                                                $ajax_data['action'] = 'verification' ;
                                                $ajax_data['value'] = '#'.$post['unit_id'].'#'.$latitude.'#'.$longitude.'#'.$title.'#'.$radius.'#'.$street_address.'#'.$city.'#'.$state.'#'.$zip.'#'.$country.'#'.$shape;
                                                break;

                        case        'metrics' : $result = $this->vehicle_logic->ajaxOptions($user,$uid,$post['action'],$post);
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['action'] ;
                                                $ajax_data['mode'] = $post['action'] ;
                                                $ajax_data['value'] = $result ;
                                                break;

                        case     'permission' : $ids = explode('-',$post['element']);
                                                $pid = array_pop($ids);
                                                $utid = $post['unit_id'];
                                                $ajax_data['message'] = 'permission inbounds... ' . $utid . ' / ' . $pid . ' / ' . $post['element'] . ' / ' . $post['value'];
                                                $result = $this->vehicle_logic->ajaxPermissions($user,$utid,$pid,$post['value'],$post);
                                                // $ajax_data['message'] = 'permission request: ' . $result . '';
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['mode'] = 'checkbox' ;
                                                $ajax_data['value'] = $result[0]['permission_id'] ;
                                                $ajax_data['alert'] = $result['alert'] ;
                                                $result = NULL ;
                                                break;

                        case 'permissionsall' : foreach ($post['element'] as $k => $v){
                                                    $ids = explode('-',$v);
                                                    $pid = array_pop($ids);
                                                    $utid = $post['unit_id'];
                                                    $ajax_data['message'] .= '...pai: ' . $utid . ' / ' . $pid . ' / ' . $post['value'];
                                                    $result = $this->vehicle_logic->ajaxPermissions($user,$utid,$pid,$post['value'],$post);
                                                    $ajax_data['message'] = 'permission request: ' . $result . '';
                                                }
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['mode'] = 'checkbox' ;
                                                $ajax_data['value'] = $post['value'] ;
                                                $ajax_data['alert'] = $result['alert'] ;
                                                $result = NULL ;
                                                break;

                        case      'recupdate' : $result = $this->vehicle_logic->ajaxUpdate($user,$post['unit_id'],$post['dbid'],$post['value'],$post);
                                                $ajax_data['action'] = $post['unit_id'] ;
                                                $ajax_data['alert'] = $result['alert'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['message'] = 'Result... ' . $post['unit_id'] . ' > ' .  $post['dbid'] . ' > ' .  $post['value'] . ' = ' .  $result['value'];
                                                $ajax_data['mode'] = 'updated' ;
                                                $ajax_data['confirm'] = $result['confirm'] ;
                                                $ajax_data['value'] = $result['value'] ;
                                                break;

                        case           'repo' : $result = $this->vehicle_logic->ajaxRepo($user,$post['unit_id'],$post['value'],$post);
                                                $ajax_data['action'] = $post['unit_id'] ;
                                                $ajax_data['alert'] = $result['alert'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['message'] = 'Result... ' . $post['unit_id'] . ' > ' .  $post['dbid'] . ' > ' .  $post['value'] . ' = ' .  $result['value'];
                                                $ajax_data['mode'] = 'repo' ;
                                                $ajax_data['value'] = $result['value'] ;
                                                break;

                        case        'repoKey' : $result = $this->vehicle_logic->getRepo($post['unit_id']);
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['message'] = 'Result... ' . $post['unit_id'] . ' > ' .  $post['dbid'] . ' > ' .  $post['value'] . ' = ' .  $result['value'];
                                                $ajax_data['mode'] = 'repoKey' ;
                                                $ajax_data['value'] = $result ;
                                                break;

                        case     'savechange' : $ajax_data['message'] = 'savechange inbounds... ' . $post['element'] . ' / ' . $post['unit_id'] . ' / ' . $post['value'];
                                                $result = $this->vehicle_logic->ajaxUpdate($user,$post['unit_id'],$post['element'],$post['value'],$post);
                                                $ajax_data['message'] .= ' = ' . $result['value'] ;
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['mode'] = $post['action'] ;
                                                if($result['permission']){
                                                    $ajax_data['alert'] = 'Updated' ;
                                                } else {
                                                    $ajax_data['alert'] = 'Change Denied' ;
                                                    $ajax_data['alert'] = $post['element'] ;
                                                    $ajax_data['code'] = 0 ;
                                                }
                                                $ajax_data['value'] = $result['value'] ;
                                                break;

                        case       'savelist' : $ajax_data['message'] = 'savelist inbounds... ' . $post['element'] . ' / ' . $post['unit_id'] . ' / ' . $post['value'];
                                                $result = $this->vehicle_logic->ajaxUpdateList($user,$post['unit_id'],$post['element'],$post['value'],$post);
                                                $ajax_data['message'] .= ' = ' . $result['value'] ;
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['mode'] = $post['action'] ;
                                                if($result['permission']){
                                                    $ajax_data['alert'] = 'Updated' ;
                                                } else {
                                                    $ajax_data['alert'] = 'Change Denied' ;
                                                    // $ajax_data['alert'] = $post['element'] ;
                                                    $ajax_data['code'] = 0 ;
                                                }
                                                $ajax_data['value'] = $result['value'] ;
                                                break;

                        case         'update' : $user['user_id'] = $user_id;
                                                $result = $this->vehicle_logic->ajaxUpdate($user,$post['unit_id'],$post['element'],$post['value']);
                                                $ajax_data['message'] = 'Result... ' . $post['unit_id'] . '=' . $result['value'];
                                                $ajax_data['action'] = $post['unit_id'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['mode'] = 'updated' ;
                                                if(($result['permission'])||($result['value'])){
                                                    $ajax_data['alert'] = 'Updated' ;
                                                } else {
                                                    $ajax_data['alert'] = 'Change Denied ' ;
                                                    // $ajax_data['alert'] .= $post['element'] ;
                                                    $ajax_data['code'] = 0 ;
                                                }
                                                $ajax_data['value'] = $result['value'] ; //'wizard-pending' ;
                                                break;

                        case 'updatelandmark' : $user['user_id'] = $user_id;
                                                $result = $this->vehicle_logic->ajaxUpdate($user,$post['unit_id'],'landmark-latitude',$post['value'][0]);
                                                $ajax_data['value']['lat'] = $result['value'] ;
                                                $result = $this->vehicle_logic->ajaxUpdate($user,$post['unit_id'],'landmark-longitude',$post['value'][1]);
                                                $ajax_data['value']['lng'] = $result['value'] ;
                                                $result = $this->vehicle_logic->ajaxUpdate($user,$post['unit_id'],'landmark-street-address',$post['value'][2]);
                                                $ajax_data['value']['street'] = $result['value'] ;
                                                $result = $this->vehicle_logic->ajaxUpdate($user,$post['unit_id'],'landmark-city',$post['value'][3]);
                                                $ajax_data['value']['city'] = $result['value'] ;
                                                $result = $this->vehicle_logic->ajaxUpdate($user,$post['unit_id'],'landmark-state',$post['value'][4]);
                                                $ajax_data['value']['state'] = $result['value'] ;
                                                $result = $this->vehicle_logic->ajaxUpdate($user,$post['unit_id'],'landmark-zipcode',$post['value'][5]);
                                                $ajax_data['value']['zip'] = $result['value'] ;
                                                $ajax_data['message'] = 'Result... ' . $ajax_data['value']['street'] . ', ' . $ajax_data['value']['city'] . ', ' . $ajax_data['value']['state'] . ', ' . $ajax_data['value']['zip'] ;
                                                $ajax_data['action'] = $post['unit_id'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['mode'] = 'updatedlandmark' ;
                                                if($result['permission']){
                                                    $ajax_data['alert'] = 'Updated' ;
                                                } else {
                                                    $ajax_data['alert'] = 'Change Denied' ;
                                                    $ajax_data['alert'] = $post['element'] ;
                                                    $ajax_data['code'] = 0 ;
                                                }
                                                $ajax_data['value'] = $result['value'] ; //'wizard-pending' ;
                                                break;

                        case  'updaterefresh' : $result = $this->vehicle_logic->ajaxUpdate($user,$post['unit_id'],$post['element'],$post['value'],$post['dbid']);
                                                $ajax_data['message'] = 'Result... ' . $result['value'] . ' - ' . $post['dbid'];
                                                $ajax_data['action'] = $post['unit_id'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['mode'] = $post['action'] ;
                                                if($result['permission']){
                                                    $ajax_data['alert'] = 'Updated' ;
                                                } else {
                                                    $ajax_data['alert'] = 'Change Denied' ;
                                                    $ajax_data['alert'] = $post['element'] ;
                                                    $ajax_data['code'] = 0 ;
                                                }
                                                $ajax_data['value'] = 'wizard-pending' ;
                                                break;

                        case   'updateSelect' : $result = $this->vehicle_logic->ajaxUpdate($user,$post['unit_id'],$post['element'],$post['value']);
                                                $ajax_data['message'] = 'uid:' . $post['unit_id'] . ', ele:' . $post['element'] . ', val:' . $post['value'];
                                                $ajax_data['action'] = $post['unit_id'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['mode'] = 'updateSelect' ;
                                                if($result['permission']){
                                                    $ajax_data['alert'] = 'Updated' ;
                                                } else {
                                                    $ajax_data['alert'] = 'Change Denied' ;
                                                    // $ajax_data['alert'] = $post['element'] ;
                                                    $ajax_data['code'] = 0 ;
                                                }
                                                $ajax_data['value'] = $result['value'] ; //'wizard-pending' ;
                                                break;

                        case       'user-add' : $post['username'] = strtolower($post['username']);
                                                $email = $this->emailFormatCheck($post['email']);
                                                if((!($post['email']))||($email)){
                                                    $result = $this->user_logic->ajaxEmailCheck($email);
                                                    if((!($post['email']))||(!($result))){
                                                        $result = $this->user_logic->ajaxUserCheck($post['username']);
                                                        if(!($result)){
                                                            $result = $this->user_logic->ajaxUserCreate($user,$post);
                                                            if(!($result)){
                                                                $ajax_data['message'] = 'New Account Created';
                                                                $ajax_data['action'] = $post['action'] ;
                                                            } else {
                                                                $ajax_data['action'] = 'alert' ;
                                                                $ajax_data['message'] = $result;
                                                                $result['error'] = 'database error' ;
                                                            }
                                                        } else {
                                                            $ajax_data['action'] = 'alert' ;
                                                            if ( $result['error'] ) {
                                                                $ajax_data['message'] = 'ERROR: ' . $result['error'] ;
                                                            } else {
                                                                $ajax_data['message'] = 'Sorry, username "' . $result['username'] . '" is already in use (' . $result['user_id'] . ')' ;
                                                            }
                                                        }
                                                    } else {
                                                        $ajax_data['action'] = 'alert' ;
                                                        if ( $result['error'] ) {
                                                            $ajax_data['message'] = 'ERROR: ' . $result['error'] ;
                                                        } else {
                                                            $ajax_data['message'] = 'Sorry, email "' . $result[0]['email'] . '" is already in use' ;
                                                        }
                                                    }
                                                } else {
                                                    $ajax_data['action'] = 'alert' ;
                                                    $result['error'] = 'email address format is incorrect' ;
                                                    $ajax_data['message'] = 'ERROR: ' . $result['error'] ;
                                                }
                                                $ajax_data['element'] = $post['action'] ;
                                                $ajax_data['mode'] = $post['action'] ;
                                                foreach ( $result as $row ) {
                                                    $ajax_data['value'][$post['element']][$row['k']] = $row['v'];
                                                }
                                                break;

                        case  'user-type-add' : $result = $this->user_logic->ajaxUserTypeCheck($post['usertype']);
                                                if(!($result)){
                                                    $result = $this->user_logic->ajaxUserTypeCreate($user,$post);
                                                    if($result){
                                                        $ajax_data['message'] = $result ; // 'New User Type Created';
                                                        $ajax_data['action'] = $post['action'] ;
                                                    } else {
                                                        $result['error'] = 'database error' ;
                                                    }
                                                } else {
                                                    $ajax_data['action'] = 'alert' ;
                                                    if ( $result['error'] ) {
                                                        $ajax_data['message'] = 'ERROR: ' . $result['error'] ;
                                                    } else {
                                                        $ajax_data['message'] = 'Sorry, user type name "' . $result[0]['usertype'] . '" is already in use' ;
                                                    }
                                                }
                                                $ajax_data['element'] = $post['action'] ;
                                                $ajax_data['mode'] = $post['action'] ;
                                                // foreach ( $result as $row ) {
                                                //     $ajax_data['value'][$post['element']][$row['k']] = $row['v'];
                                                // }
                                                break;

                        case      'user-edit' : if($post['element']=='load'){
                                                    $result = $this->vehicle_logic->ajaxFormFill($user_id,$user,$post['action'],$post['unit_id']);
                                                    $ajax_data['value'] = $result ;
                                                    $result = $this->vehicle_logic->ajaxGroups('territorygroups',$user['account_id'],$post['unit_id']);
                                                    $ajax_data['landmarkgroups'] = $result ;
                                                    $result = $this->vehicle_logic->ajaxGroups('unit',$user['account_id'],$post['unit_id']);
                                                    $ajax_data['vehiclegroups'] = $result ;
                                                    $ajax_data['message'] = $post['action'] . ', target user_id:' . $post['unit_id'] . ', account_id: ' . $user['account_id'] . ', user_id: ' . $user_id;
                                                    $ajax_data['action'] = $post['action'] ;
                                                    $ajax_data['element'] = $post['action'] ;
                                                    $ajax_data['uid'] = $post['unit_id'];
                                                    $ajax_data['mode'] = 'formfill' ;
                                                }
                                                break;

                        case 'user-type-edit' : if($post['element']=='load'){
                                                    $result = $this->vehicle_logic->ajaxFormFill($user_id,$user,$post['action'],$post['unit_id']);
                                                    $ajax_data['permissions'] = $result ;
                                                    $ajax_data['message'] = $post['action'] . ', target user_id:' . $post['unit_id'] . ', account_id: ' . $user['account_id'] . ', user_id: ' . $user_id;
                                                    $ajax_data['action'] = $post['action'] ;
                                                    $ajax_data['element'] = $post['action'] ;
                                                    $ajax_data['uid'] = $post['unit_id'];
                                                    $ajax_data['mode'] = 'formfill' ;
                                                }
                                                break;

                // case  'options-contactstatus' : $post['action'] = array_pop(explode('-',$post['action']));
                //                                 $uid = array_pop(explode('-',$post['element']));
                //                                 $result = $this->vehicle_logic->ajaxOptions($user,$uid,$post['action']);
                //                                 foreach ( $result as $row ) {
                //                                     $ajax_data['value'][$post['element']][$row['k']] = $row['v'];
                //                                 }
                //                                 $ajax_data['message'] = 'Options for Contact Status: Pending / Active ; Element: ' . $post['element'];
                //                                 $ajax_data['action'] = $post['action'] ;
                //                                 $ajax_data['element'] = $post['element'] ;
                //                                 $ajax_data['mode'] = $post['action'] ;
                //                                 break;

                //
                // DROPDOWN+SELECT EDITABLE DATA
                //
           case 'options-alertcontactcontact' :
           case   'options-alertcontactgroup' :
           case    'options-alertcontactmode' :
           case           'options-alerttype' :
           case       'options-alertunitunit' :
           case      'options-alertunitgroup' :
           case       'options-alertunitmode' :
           case             'options-country' :
           case             'options-carrier' :
           case         'options-cellcarrier' :
           case             'options-contact' :
           case        'options-contactgroup' :
           case       'options-contactmethod' :
           case         'options-contactmode' :
           case       'options-contactstatus' :
           case                'options-days' :
           case            'options-duration' :
           case             'options-gateway' :
           case               'options-hours' : 
           case            'options-landmark' : 
           case    'options-landmarkcategory' :
           case       'options-landmarkgroup' :
           case      'options-landmarkmethod' :
           case        'options-landmarkmode' :
           case      'options-landmarkradius' :
           case       'options-landmarkshape' :
           case     'options-landmarktrigger' :
           case           'options-overspeed' :
           case  'options-permissioncategory' :
           case              'options-radius' :
           case               'options-shape' :
           case               'options-state' :
           case   'options-territorycategory' :
           case      'options-territorygroup' :
           case           'options-unitgroup' :
           case             'options-unit_id' :
           case          'options-unitstatus' :
           case            'options-usertype' :
           case             'options-vehicle' :
           case         'options-vehiclemode' : $post['action'] = array_pop(explode('-',$post['action']));
                                                $uid = array_pop(explode('-',$post['element']));
                                                $result = $this->vehicle_logic->ajaxOptions($user,$uid,$post['action'],$post);
                                                foreach ( $result as $row ) {
                                                    $ajax_data['value'][$post['element']][$row['k']] = $row['v'];
                                                }
                                                $ajax_data['message'] = $post['action'] . ', unit_id:' . $post['unit_id'] . ', Element: ' . $post['element'];
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['mode'] = 'options' ;
                                                break;

                //
                // DROPDOWN+SELECT EDITABLE DATA
                //
            case 'update-alertcontactcontact' :
              case 'update-alertcontactgroup' :
               case 'update-alertcontactmode' :
                case       'update-alerttype' :
                case   'update-alertunitunit' :
                case  'update-alertunitgroup' :
                case   'update-alertunitmode' :
                case     'update-cellcarrier' :
                case    'update-contactgroup' :
                case   'update-contactstatus' :
                case         'update-country' :
                case         'update-gateway' :
             case 'update-permissioncategory' :
                case          'update-radius' :
                case           'update-shape' :
                case           'update-state' :
              case 'update-territorycategory' :
                case  'update-territorygroup' :
                case       'update-unitgroup' :
                case      'update-unitstatus' :
                case        'update-usertype' : $post['action'] = array_pop(explode('-',$post['action']));
                                                $uid = array_pop(explode('-',$post['element']));
                                                $result = $this->vehicle_logic->ajaxDbUpdate($user,$post['action'],$uid,$post['value']);
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['message'] = 'Result... ' . $result['message'];
                                                $ajax_data['mode'] = 'updated' ;
                                                $ajax_data['text'] = NULL ;
                                                $ajax_data['value'] = $result['value'] ;
                                                break;

case    'update-crossbones-territory-country' :
case      'update-crossbones-territory-state' :
                                                $dbid = explode('-',$post['action']);
                                                $post['action'] = $dbid[1] . '-' . $dbid[2] . '-' . $dbid[3] ; 
                                                $uid = array_pop(explode('-',$post['element']));
                                                $result = $this->vehicle_logic->ajaxDbUpdate($user,$post['action'],$uid,$post['value']);
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['message'] = 'Result... ' . $result['message'];
                                                $ajax_data['mode'] = 'updated' ;
                                                $ajax_data['text'] = NULL ;
                                                $ajax_data['value'] = $result['value'] ;
                                                $ajax_data['LandmarkGet'] = $post['element'] ;
                                                $ajax_data['LandmarkRid'] = $uid;
                                                $ajax_data['LandmarkTbl'] = 'list' ;
                                                break;

case      'update-crossbones-territory-shape' :
case 'update-crossbones-territory-territorycategory' :
                                                $dbid = explode('-',$post['action']);
                                                $post['action'] = $dbid[1] . '-' . $dbid[2] . '-' . $dbid[3] ; 
                                                $uid = array_pop(explode('-',$post['element']));
                                                $result = $this->vehicle_logic->ajaxDbUpdate($user,$post['action'],$uid,$post['value']);
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['message'] = 'Result... ' . $result['message'];
                                                $ajax_data['mode'] = 'updated' ;
                                                $ajax_data['text'] = NULL ;
                                                $ajax_data['value'] = $result['value'] ;
                                                break;

                case         'update-unit_id' : $dbid = explode('-',$post['action']);
                                                $post['action'] = $dbid[1] . '-' . $dbid[2] . '-' . $dbid[3] ; 
                                                $uid = array_pop(explode('-',$post['element']));
                                                $result = $this->vehicle_logic->ajaxDbUpdate($user,$post['action'],$uid,$post['value']);
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['message'] = 'Result... ' . $result['message'];
                                                $ajax_data['mode'] = 'updated' ;
                                                $ajax_data['text'] = NULL ;
                                                $ajax_data['value'] = $result['value'] ;
                                                $ajax_data['LandmarkGet'] = $post['element'] ;
                                                $ajax_data['LandmarkRid'] = $uid;
                                                $ajax_data['LandmarkTbl'] = 'verification' ;
                                                break;

case     'update-crossbones-territory-radius' : $dbid = explode('-',$post['action']);
                                                $post['action'] = $dbid[1] . '-' . $dbid[2] . '-' . $dbid[3] ; 
                                                $uid = array_pop(explode('-',$post['element']));
                                                $result = $this->vehicle_logic->ajaxDbUpdate($user,$post['action'],$uid,$post['value']);
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['message'] = 'Result... ' . $result['message'];
                                                $ajax_data['mode'] = 'updated' ;
                                                $ajax_data['text'] = NULL ;
                                                $ajax_data['value'] = $result['value'] ;
                                                break;

                case   'update-vehiclestatus' : $uid = $post['unit_id'];
                                                $result = $this->vehicle_logic->ajaxDbUpdate($user,'unitstatus',$uid,$post['value']);
                                                $ajax_data['action'] = $post['action'] ;
                                                $ajax_data['element'] = $post['element'] ;
                                                $ajax_data['message'] = 'Result... ' . $result['message'];
                                                $ajax_data['mode'] = 'updated' ;
                                                $ajax_data['text'] = NULL ;
                                                $ajax_data['value'] = $result['value'] ;
                                                break;

                                      default : $ajax_data['message'] = '*** AJAX ***';
                                                if($post['unit_id']){
                                                    $ajax_data['message'] .= ' Processing unit_id: '. $post['unit_id'] . ';';
                                                }
                                                $ajax_data['message'] .= ' Action: '. $post['action'] . ' *** CASE MISSING *** ;';

                    }

                    if($result['alert']){
                        $ajax_data['alert'] = $result['alert'];
                    }

                } else {
                    $ajax_data['alert'] = '1' ;
                    $ajax_data['action'] = 'logout' ;
                    $ajax_data['code'] = 1;
                    $ajax_data['message'] = 'User does not exist';                        
                }       
            } else {
                    $ajax_data['alert'] = '2' ;
                $ajax_data['action'] = 'logout' ;
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Failed to get user info';                    
            }    
        } else {
                    $ajax_data['alert'] = '3' ;
            $ajax_data['action'] = 'logout' ;
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid user id';
        }
        
        $this->ajax_respond($ajax_data);
    }
    
    /**
     * Get all vehicles (called via ajax)
     *
     * @return void
     */
    public function getAllVehicles()
    {
        $ajax_data  = array();
        $user_id    = $this->user_session->getUserId();

        $vehicle_groups = array();
        $ajax_data['code'] = 0;
        $ajax_data['data']['vehicles'] = $this->vehicle_logic->getVehiclesByGroupIds($user_id, $vehicle_groups);
        $ajax_data['message'] = 'Success';

        $this->ajax_respond($ajax_data);
    }

    /**
     * Get the vehicle info by group_id (called via ajax)
     *
     * POST params: group_id
     *
     * @return array
     */
    public function getVehiclesByGroupIds()
    {
        $ajax_data      = array();
        $user_id        = $this->user_session->getUserId();
        $vehicle_groups = array();

        if (($unit_info = $this->vehicle_data->getVehiclesByGroupIds($user_id, $vehicle_groups)) !== false) {
            $ajax_data['code'] = 0;
            $ajax_data['data']['vehicles'] = $unit_info;
            $ajax_data['message'] = 'Success';
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Error';
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Get the vehicle info by filtered paramaters (called via ajax)
     *
     * POST params: group_id, event_id, sort_by
     *
     * @return array
     */
    public function getFilteredVehicles()
    {
        $ajax_data  = array();
        $user_id    = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();
        $post       = $this->request->request->all();
        
        $vehicle_groups = (isset($post['vehicle_group_id']) AND !empty($post['vehicle_group_id'])) ? $post['vehicle_group_id'] : '';
        $vehicle_state_status = (isset($post['vehicle_state_status']) AND !empty($post['vehicle_state_status'])) ? $post['vehicle_state_status'] : '';
        $params = $post;
        
        if ($vehicle_groups != '') {
            if (($unit_info = $this->vehicle_logic->getFilteredVehicles($user_id, $vehicle_groups, $post)) !== false) {
                $ajax_data['code'] = 0;
                $ajax_data['data'] = $unit_info;
                $ajax_data['message'] = 'Success';
            } else {
                $ajax_data['code'] = 0;
                $ajax_data['data']['vehicles'] = array();
                $ajax_data['message'] = 'Error';
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Error';
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Get the vehicle info by filtered paramaters (called via ajax)
     *
     * POST params: group_id, event_id, sort_by
     *
     * @return array
     */
    public function getFilteredVehicleList()
    {
        $ajax_data      = array();
        $vehicle_groups = array();
        $post           = $this->request->request->all();
        $user_id        = $this->user_session->getUserId();
        $account_id     = $this->user_session->getAccountId();
        $user_timezone  = $this->user_session->getUserTimeZone();

        $sEcho = 0;
        if (isset($post['sEcho']) AND $post['sEcho'] != '') {
            $sEcho = $post['sEcho'];
        }        

        $output = array(
            "sEcho" => intval($sEcho),
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "data" => array()
        );

        // if doing a string search, no group filter
        if (isset($post['string_search']) AND $post['string_search'] != '') {
            $post['vehicle_group_id'] = 'ALL';
        }

		$params = $post;
		$params['user_timezone'] = $user_timezone;

        $vehicle_group_id = (isset($post['vehicle_group_id']) AND !empty($post['vehicle_group_id'])) ? $post['vehicle_group_id'] : '';

        if ($vehicle_group_id != '' AND $vehicle_group_id != 'ALL' AND $vehicle_group_id != 'All') {
            $vehicle_groups = $vehicle_group_id;
        }

        $vehicles = $this->vehicle_logic->getVehiclesByGroupIds($user_id, $vehicle_groups);
        if (! empty($vehicles) AND $vehicles !== false) {
                $output = $this->vehicle_logic->getVehicleListDataInfo($vehicles, $params);
        }

        echo json_encode( $output );
        exit;
    }

    /**
     * Search and return any vehicle units having provided name (called via ajax)
     *
     * POST params: vehicle_name
     *
     * @return array | bool
     */
     public function searchVehicleByName()
     {
        $ajax_data  = array();
        $user_id    = $this->user_session->getUserId();
        $post       = $this->request->request->all();

        $search_string = (isset($post['search_string']) AND !empty($post['search_string'])) ? $post['search_string'] : '';

        $params = $post;
        $searchfields = array(); // leaving blank for the moment

        $unit_info = $this->vehicle_logic->searchVehicleByName($user_id, $search_string, $params, $searchfields);
        if ($unit_info !== false) {
            if(isset($unit_info['vehicles']) AND ! empty($unit_info['vehicles'])) {
                $ajax_data['code'] = 0;
                $ajax_data['data'] = $unit_info;
                $ajax_data['message'] = 'Success';
            } else {
                $ajax_data['code'] = 0;
                $ajax_data['data'] = $unit_info;
                $ajax_data['message'] = 'No Match Found';
            }
        } else {
            $ajax_data['code'] = 0;
            $ajax_data['data']['vehicles'] = array();
            $errors = $this->vehicle_logic->getErrorMessage();
            if (! empty($errors) AND is_array($errors)) {
                $errors = implode(', ',$errors);
            } else {
                $errors = 'Action failed due to database issue';
            }
            
            $ajax_data['validation_error'][] = $ajax_data['message'] = $errors;
        }

        $this->ajax_respond($ajax_data);
     }

    /**
     * Get the vehicle info by unit_id (called via ajax)
     *
     * unitgroup_columns, unitattribute_columns, customer_columns are all arrays containg string values.
     * The string values represent the column names from their respected table for which you want to retreive the
     * desired data from.
     *
     * ex: $unitattribute_columns = array('unitattribute_id', 'unit_id', 'vin', 'make');
     *
     *	   The unitattribute_columns array should contain column names from the 'unitattribute' table in the
     *	   crossbones database. This example will return you the values from the unitattribute_id, unit_id,
     *	   vin, and make columns for the unit based on the unit_id.
     *
     * POST params: unit_id
     *
     * @return array
     */
    public function getVehicleInfo()
    {
        $account_id     = $this->user_session->getAccountId();
        $user_id        = $this->user_session->getUserId();
        $user_timezone  = $this->user_session->getUserTimeZone();
        $post           = $this->request->request->all();

        $ajax_data      = array();
        $unitgroup_columns      = array('unitgroup_id', 'unitgroupname');
        $unitattribute_columns  = array('unitattribute_id', 'vin', 'make', 'model', 'year', 'color', 'licenseplatenumber', 'loannumber', 'purchasedate', 'renewaldate', 'lastrenewaldate', 'stocknumber', 'activatedate', 'deactivatedate');
        $customer_columns       = array('customer_id', 'firstname', 'lastname', 'streetaddress', 'city', 'state', 'zipcode', 'country', 'homephone', 'cellphone', 'email');
        $unitinstallation_columns   = array('unitinstallation_id', 'installer', 'installdate');
        $unitodometer_columns       = array('unitodometer_id', 'initialodometer', 'currentodometer');
        $event_id   = '';
        $eventdata  = array();

    	$start_date = '';
    	$end_date   = '';

    	$unit_id    = $post['unit_id'];

        if (($unit_info = $this->vehicle_logic->getVehicleInfo($unit_id, $unitgroup_columns, $unitattribute_columns, $customer_columns, $unitinstallation_columns, $unitodometer_columns)) !== false) {

            $ajax_data['code'] = 0;
            $ajax_data['data'] = $unit_info;
            $ajax_data['message'] = 'Successfully retrieve vehicle info';

            $ajax_data['data']['streetaddress']             = $unit_info['streetaddress'] ;
            $ajax_data['data']['city']                      = $unit_info['city'] ;
            $ajax_data['data']['state']                     = $unit_info['state'] ;
            $ajax_data['data']['zipcode']                   = $unit_info['zipcode'] ;
            $ajax_data['data']['country']                   = $unit_info['country'] ;

            $ajax_data['data']['subscription']              = $unit_info['subscription'] ;

            $ajax_data['data']['formatted_address']         = $this->address_logic->validateAddress($unit_info['streetaddress'], $unit_info['city'], $unit_info['state'], $unit_info['zipcode'], $unit_info['country']);
            $ajax_data['data']['infomarker_address']        = $this->address_logic->validateAddress($unit_info['streetaddress'], '<br>'.$unit_info['city'], $unit_info['state'], $unit_info['zipcode'], $unit_info['country']);
            $ajax_data['data']['formatted_cell_phone']      = $this->address_logic->formatPhoneDisplay($unit_info['cellphone']);
            $ajax_data['data']['formatted_home_phone']      = $this->address_logic->formatPhoneDisplay($unit_info['homephone']);
            $ajax_data['data']['formatted_activatedate']    = (! empty($unit_info['firstevent']) AND ($unit_info['firstevent'] != '0000-00-00')) ? Date::utc_to_locale($unit_info['firstevent'], $user_timezone, 'm/d/Y') : '';
            $ajax_data['data']['formatted_purchasedate']    = (! empty($unit_info['purchasedate']) AND ($unit_info['purchasedate'] != '0000-00-00')) ? Date::utc_to_locale($unit_info['purchasedate'], $user_timezone, 'm/d/Y') : '';
            $ajax_data['data']['formatted_expirationdate']  = (! empty($unit_info['renewaldate']) AND ($unit_info['renewaldate'] != '0000-00-00')) ? Date::utc_to_locale($unit_info['renewaldate'], $user_timezone, 'm/d/Y') : '';
            $ajax_data['data']['formatted_lastrenewaldate'] = (! empty($unit_info['lastrenewaldate']) AND ($unit_info['lastrenewaldate'] != '0000-00-00')) ? Date::utc_to_locale($unit_info['lastrenewaldate'], $user_timezone, 'm/d/Y') : '';
            $ajax_data['data']['formatted_deactivatedate']  = (! empty($unit_info['deactivatedate']) AND ($unit_info['deactivatedate'] != '0000-00-00')) ? Date::utc_to_locale($unit_info['deactivatedate'], $user_timezone, 'm/d/Y') : '';
            $ajax_data['data']['formatted_installdate']     = (! empty($unit_info['installdate']) AND ($unit_info['installdate'] != '0000-00-00')) ? date('m/d/Y',strtotime($unit_info['installdate'])) : '';
            $ajax_data['data']['installmileage']            = ! empty($unit_info['initialodometer']) ? $unit_info['initialodometer'] : 0;
            $ajax_data['data']['drivenmileage']             = ! empty($unit_info['currentodometer']) ? $unit_info['currentodometer'] : 0;
            $ajax_data['data']['totalmileage']              = (string) ($ajax_data['data']['installmileage'] + $ajax_data['data']['drivenmileage']);
            $ajax_data['data']['odometer_id']               = $unit_info['unitodometer_id'];
            $ajax_data['data']['stock']                     = $unit_info['stocknumber'];
            $ajax_data['data']['year']                      = ($unit_info['year'] == 0) ? null : $unit_info['year'];

            $ajax_data['data']['moving']['state']           = $this->vehicle_data->getMoving($unit_id);
            $ajax_data['data']['moving_status']             = $ajax_data['data']['moving']['state'];
            $ajax_data['data']['moving']                    = $this->vehicle_data->getDuration($unit_id,$ajax_data['data']['moving']['state']);
            $ajax_data['data']['battery']                   = $this->vehicle_data->getBattery($unit_id);
            $ajax_data['data']['signal']                    = $this->vehicle_data->getSignal($unit_id);
            $ajax_data['data']['satellites']                = $this->vehicle_data->getSatellites($unit_id);
            $ajax_data['data']['territoryname']             = $this->vehicle_data->getTerritory($account_id,$unit_id);

            if (! empty($post['event_id'])) {
            	$event_id = $post['event_id'];
            }               

            if ($event_id == '') {
	            $row = $this->vehicle_logic->getLastReportedEvent($unit_id);
                $features = $this->vehicle_data->getUnitVersionFeatures($unit_id);
            } else {
	            $row = $this->vehicle_logic->getEventById($unit_id, $unit_info['db'], $event_id);
                $features = $this->vehicle_data->getUnitVersionFeatures($event_id);
            }

            foreach ($features as $k => $v) {
                $ajax_data['permission'][$k] = $features[$k];
            }

        	if ($row !== false) {
	        	if (! empty($row)) {
    	        	$row['unit_id'] = $unit_id;
                    $row['unitname'] = $unit_info['unitname'];
                    if(!($row['unitname'])){
                        $row['unitname'] = $unit_info['serialnumber'];
                    }
                    $row['formatted_address']   = $this->address_logic->validateAddress($row['streetaddress'], $row['city'], $row['state'], $row['zipcode'], $row['country']);
                    $row['infomarker_address']   = $this->address_logic->validateAddress($row['streetaddress'], '<br>'.$row['city'], $row['state'], $row['zipcode'], $row['country']);
                    $row['display_servertime']  = Date::locale_to_locale(date('Y-m-d H:i:s'), SERVER_TIMEZONE, $user_timezone, 'h:i A m/d/Y');
			    	$row['display_unittime']    = Date::utc_to_locale($row['unittime'], $user_timezone, 'h:i A m/d/Y');
                    $eventdata = $row;

                    // calculate time since last moving event
                    $row = $this->vehicle_logic->getLastReportedStopMoveEvent($unit_id);
                    $utctime               = Date::locale_to_utc(date('Y-m-d H:i:s'), SERVER_TIMEZONE, 'Y-m-d H:i:s');
                    $since_event_duration   = Date::time_difference_seconds($utctime, $row['unittime']);
                    $in = array('s','d','h','m');
                    $out = array( ' seconds',' days',' hours',' minutes');
                    $buffer = Date::seconds_to_timespan($since_event_duration,true,true,true,false,true);
                    $eventdata['since_eventtime'] = str_replace($in,$out,$buffer);
                    $eventdata['event_id'] = trim($row['event_id']);
                    if(!($eventdata['since_eventtime'])) {
                        $eventdata['since_eventtime'] = 'less than 1 minute' ;
                    } else if($eventdata['since_eventtime']=='1 minutes') {
                        $eventdata['since_eventtime'] = '1 minute' ;
                    }
                    $eventdata['since_eventtime'] = $eventdata['since_eventtime'] ;
 		    	} else {
    	        	$ajax_data['code']      = 0;
    	        	$ajax_data['message']   = 'Vehicle event not found';
		    	}
        	} else {
	        	$ajax_data['code']      = 0;
	        	$ajax_data['message']   = 'Vehicle has no event table';
        	}

        	$ajax_data['data']['eventdata'] = $eventdata;
            switch($ajax_data['data']['moving_status']){
                case                   1  :
                case                  '1' :
                case                   3  :
                case                  '3' :
                case                   4  :
                case                  '4' : $ajax_data['data']['eventdata']['stoppedormoving'] = 'Moving';
                                            break;
                                  default : $ajax_data['data']['eventdata']['stoppedormoving'] = 'Stopped';
            }

            $ajax_data['data']['eventdata']['moving'] = $ajax_data['data']['moving']['duration'];

            if($ajax_data['data']['moving']['duration']=='n/a'){
                $ajax_data['data']['moving']['state'] = 'I';
                $ajax_data['data']['moving']['stale'] = null ;
            } else if($ajax_data['data']['moving']['duration']=='N/A'){
                $ajax_data['data']['moving']['state'] = 'N';
                $ajax_data['data']['moving']['stale'] = null ;
            }

            if (! empty($post['start_date'])) {
            	$start_date = $post['start_date'];
            }

            if (! empty($post['end_date'])) {
            	$end_date = $post['end_date'];
            }
        } else {
            $ajax_data['code']      = 1;
            $ajax_data['message']   = 'Error';
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Update the vehicle info by unit_id (called via ajax)
     *
     * POST params: groupid , devices (comma delimited)
     *
     * @return array
     */
    public function updateVehicleGroupIds()
    {
        $user_id        = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();
        $post           = $this->request->request->all();

        $update = $this->vehicle_logic->updateVehicleGroupIds($account_id,$post['groupid'], $post['devices']);
        if ($update !== false) {
            $ajax_data['code']      = 0;
            $ajax_data['message']   = 'Group ids updated successfully';
            $ajax_data['message']   = $update;
        } else {
            $ajax_data['code']      = 1;
            $ajax_data['message']   = 'Group id updates unsuccessful';
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Update the vehicle info by unit_id (called via ajax)
     *
     * POST params: unit_id, $status, $vehicle_name, $serial
     *
     * @return array
     */
    public function updateVehicleInfo()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();
    	$unit_id    = $post['primary_keys']['vehiclePk'];
        $params     = array();
        $table      = '';

        switch ($post['id']) {
            case 'vehicle-status':
                $params['unitstatus_id'] = $post['value'];
                $table = 'unit';
                break;
            case 'vehicle-name':
                $params['unitname'] = $post['value'];
                $table = 'unit';
                break;
            case 'vehicle-vin':
                $params['vin'] = $post['value'];
                $table = 'unitattribute';
                break;
            case 'vehicle-make':
                $params['make'] = $post['value'];
                $table = 'unitattribute';
                break;
            case 'vehicle-model':
                $params['model'] = $post['value'];
                $table = 'unitattribute';
                break;
            case 'vehicle-year':
                $params['year'] = $post['value'];
                $table = 'unitattribute';
                break;
            case 'vehicle-color':
                $params['color'] = $post['value'];
                $table = 'unitattribute';
                break;
            case 'vehicle-stock':
                $params['stocknumber'] = $post['value'];
                $table = 'unitattribute';
                break;
            case 'vehicle-license-plate':
                $params['licenseplatenumber'] = $post['value'];
                $table = 'unitattribute';
                break;
            case 'vehicle-loan-id':
                $params['loannumber'] = $post['value'];
                $table = 'unitattribute';
                break;
            case 'vehicle-install-date':
                $params['user_timezone'] = $this->user_session->getUserTimeZone();
                $params['installdate'] = $post['value'];
                $table = 'unitinstallation';
                break;
            case 'vehicle-installer':
                $params['installer'] = $post['value'];
                $table = 'unitinstallation';
                break;
            case 'vehicle-install-mileage':
                $params['unitodometer_id'] = ! empty($post['primary_keys']['vehicleOdometerId']) ? $post['primary_keys']['vehicleOdometerId'] : 0;
                $params['initialodometer'] = $post['value'];
                $table = 'unitodometer';
                break;
            default:
                break;
        }

        if (! empty($params) AND ! empty($unit_id)) {
            $update = $this->vehicle_logic->updateVehicleInfo($unit_id, $params, $table);
            if ($update !== false) {
	            $ajax_data['data'] = $post;
	            // if a unit didn't have a unitodometer record before and one was created for it, 
	            // pass back the unitodometer id to be updated in the vehicle info (needed for inline-editing)
	            if (isset($params['unitodometer_id'])) {
	               if ($params['unitodometer_id'] == 0) {
    	               $ajax_data['data']['unitodometer_id'] = $update;    
    	           }
    	           // strip out any leading zeroes
    	           $ajax_data['data']['value'] = ltrim($post['value'], '0');
	            }
                $ajax_data['code'] = 0;
                $ajax_data['message'] = 'Updated Vehicle Information';
            } else {
                $ajax_data['code'] = 1;
                $errors = $this->vehicle_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(', ',$errors);
                } else {
                    $errors = 'Action failed due to database issue';
                }
                
                $ajax_data['validation_error'][] = $ajax_data['message'] = $errors;
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Error';
        }
        
        if($ajax_data['data']['value']==0)
        {
	        $ajax_data['data']['value']='0';
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Update the group a vehicle is assigned to
     *
     * POST params: unit_id, group_id
     *
     * @return void
     */
    public function updateAssignedVehicleGroup()
    {
		$ajax_data  = array();
		$post       = $this->request->request->all();
		$unit_id    = $post['primary_keys']['vehiclePk'];
		$group_id   = $post['value'];

		if ($this->vehicle_logic->updateAssignedVehicleGroup($unit_id, $group_id) !== false) {
			$ajax_data['data'] = $post;
			$ajax_data['code'] = 0;
			$ajax_data['message'] = 'Updated Vehicle Group';
		} else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Error';
		}

        $this->ajax_respond($ajax_data);
    }

    /**
     * Get the vehicle's last reported event (called via ajax)
     *
     * POST params: unit_id
     *
     * @return void
     */
    public function getLastReportedEvent()
    {
        $ajax_data      = array();
        $post           = $this->request->request->all();
        $unit_id        = $post['unit_id'];
        $user_timezone  = $this->user_session->getUserTimeZone();

        if (! is_array($unit_id)) {
            $unit_id = array($unit_id);
        }

        foreach ($unit_id as $index => $id) {
    		if (($unit_info = $this->vehicle_logic->getVehicleInfo($id)) !== false) {
		        if (($row = $this->vehicle_logic->getLastReportedEvent($id)) !== false) {
		        	if (! empty($row)) {
		        	    $row['unit_id']             = $unit_id;
        	        	$row['unitname']            = $unit_info['unitname'];
        		    	$row['formatted_address']   = $this->address_logic->validateAddress($row['streetaddress'], $row['city'], $row['state'], '', $row['country']);
                        $row['infomarker_address']  = $row['formatted_address'] ;
                        $row['display_servertime']  = Date::locale_to_locale(date('Y-m-d H:i:s'), SERVER_TIMEZONE, $user_timezone, 'h:i A m/d/Y');
        		    	$row['display_unittime']    = Date::utc_to_locale($row['unittime'], $user_timezone, 'h:i A m/d/Y');
        		    	$unit_info['eventdata']     = $row;
        		    	$ajax_data['message']       = 'Current Location';
			    	} else {
    			        $ajax_data['message'] = 'No events';
			    	}

			    	//  success - if at least one vehicle has an event
    		    	if (! isset($ajax_data['code']) OR $ajax_data['code'] === 1) {
        		    	$ajax_data['code'] = 0;
    		    	}
		        } else {
    		        if (! isset($ajax_data['code'])) {
        		        $ajax_data['code'] = 1;
		        	}
			        $ajax_data['message'] = 'No event table';
		        }
		        $ajax_data['data'][] = $unit_info;
                $ajax_data['data']['moving']['state']           = $this->vehicle_data->getMoving($id);
                $ajax_data['data']['moving']                    = $this->vehicle_data->getDuration($id,$ajax_data['data']['moving']['state']);
                $ajax_data['data']['battery']                   = $this->vehicle_data->getBattery($id);
                $ajax_data['data']['signal']                    = $this->vehicle_data->getSignal($id);
                $ajax_data['data']['satellites']                = $this->vehicle_data->getSatellites($id);

                if($ajax_data['data']['moving']['duration']=='n/a'){
                    $ajax_data['data']['moving']['state'] = 'Inventory';
                } else if($ajax_data['data']['moving']['duration']=='N/A'){
                    $ajax_data['data']['moving']['state'] = 'Installed';
                }

	        } else {
		        $ajax_data['code']      = 1;
		        $ajax_data['message']   = 'No unit info';
	        }
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Get a vehicle's event data by unit_id, event id
     *
     * POST params: unit_id, event_id
     *
     * @return void
     */
    public function getEventById()
    {
    	$ajax_data      = array();
    	$post           = $this->request->request->all();
    	$event_id       = $post['event_id'];
    	$unit_id        = $post['unit_id'];
        $user_timezone  = $this->user_session->getUserTimeZone();

		if (($unit_info = $this->vehicle_logic->getVehicleInfo($unit_id)) !== false) {
			if (! empty($unit_info['db'])) {
		        if (($row = $this->vehicle_logic->getEventById($unit_id, $unit_info['db'], $event_id)) !== false) {
		            $row['unit_id']             = $unit_id;
		        	$row['unitname']            = $unit_info['unitname'];
			    	$row['formatted_address']   = $this->address_logic->validateAddress($row['streetaddress'], $row['city'], $row['state'], '', $row['country']);
       		    	$row['display_servertime']  = Date::locale_to_locale(date('Y-m-d H:i:s'), SERVER_TIMEZONE, $user_timezone, 'h:i A m/d/Y');
       		    	$row['display_unittime']    = Date::utc_to_locale($row['unittime'], $user_timezone, 'h:i A m/d/Y');

			    	$unit_info['eventdata']     = $row;
			    	$ajax_data['code']          = 0;
			    	$ajax_data['message']       = 'Success';
			    	$ajax_data['data']          = $unit_info;
		        } else {
		        	$ajax_data['code']      = 1;
			        $ajax_data['message']   = 'Error';
		        }
	        } else {
	            $ajax_data['code']      = 1;
	            $ajax_data['message']   = 'Error';
	        }
        } else {
	        $ajax_data['code']      = 1;
	        $ajax_data['message']   = 'Error';
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Send commands to vehicle (called via ajax)
     *
     * POST params: unit_id, command_type
     *
     * @return void
     */
    public function sendCommand()
    {
    	$ajax_data      = array();
	    $post           = $this->request->request->all();
	    $unit_id        = $post['unit_id'];	    
	    $command_type   = $post['command_type'];

	    if ($command_type === 'starter_enable' OR $command_type === 'starter_disable') {	                                                  // toggle starter
		    if ($this->unitcommand_logic->toggleStarter($unit_id, ($command_type === 'starter_enable') ? true : false) !== false) {
			    $ajax_data['code'] = 0;
                $ajax_data['message'] = 'Command Sent';
		    } else {
                $ajax_data['code'] = 1;
                $errors = $this->unitcommand_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(', ',$errors);
                } else {
                    $errors = 'Action failed due to database issue';
                }
                
                $ajax_data['validation_error'][] = $ajax_data['message'] = $errors;
		    }
        } else if ($command_type === 'reminder_on' OR $command_type === 'reminder_off') {                                                   // toggle reminder
            if ($this->unitcommand_logic->toggleReminder($unit_id, ($command_type === 'reminder_on') ? true : false) !== false) {
                $ajax_data['code'] = 0;
                $ajax_data['message'] = 'Command Sent';
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Error';
            }
        } else if ($command_type === 'locate_on_demand') {                                                                                  // locate on demand
            if ($this->unitcommand_logic->locateOnDemand($unit_id, true)) {
                $ajax_data['code'] = 0;
                $ajax_data['message'] = 'Command Sent';
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Error';
                $ajax_data['message'] = $this->unitcommand_logic->getErrorMessage();
            }
	    } else {
		    $ajax_data['code'] = 1;
		    $ajax_data['message'] = 'Invalid command type: "' . $command_type . '"' ;
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
            $twilio = $this->unitcommand_logic->smsTwilio($sms_params);
        }

	    $this->ajax_respond($ajax_data);
    }

    public function getVehicleGroupOptions()
    {
        // this response need to be formatted exactly as below (whitespace is arbitrary). DO NOT JSON encode!
        $user_id = $this->user_session->getUserId();

        $output = '[';

        if (($unit_groups = $this->vehicle_logic->getVehicleGroupsByUserId($user_id)) !== false) {
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

        $vehicle_groups = $groups = array();
        $user_id = $this->user_session->getUserId();
        $vehicle_groups = $this->vehicle_logic->getVehicleGroupsByUserId($user_id);
        
        if (! empty($vehicle_groups)) {
            foreach ($vehicle_groups as $vehicle_group) {
                $groups[] = $vehicle_group['unitgroup_id'];
            }
        }
        
        // display default vehicle list amount for map page on load (default = 20)
        $vehicles = $this->vehicle_data->getVehiclesByGroupIds($user_id, $groups);
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

    public function updateCustomerInfo()
    {
    	$ajax_data  = array();
        $post       = $this->request->request->all();
        $unit_id    = $post['primary_keys']['vehiclePk'];
        $params     = array();

        switch($post['id']) {
            case 'customer-first-name':
                $params['firstname'] = $post['value'];
                break;
            case 'customer-last-name':
                $params['lastname'] = $post['value'];
                break;
            case 'customer-address':
                $params['streetaddress'] = $post['value'];
                break;
            case 'customer-city':
                $params['city'] = $post['value'];
                break;
            case 'customer-state':
                $params['state'] = $post['value'];
                break;
            case 'customer-zipcode':
                $params['zipcode'] = $post['value'];
                break;
            case 'customer-mobile-phone':
                $params['cellphone'] = $post['value'];
                break;
            case 'customer-home-phone':
                $params['homephone'] = $post['value'];
                break;
            case 'customer-email':
                $params['email'] = $post['value'];
                break;
            default:
                break;
        }

        if ($this->vehicle_logic->updateCustomerInfo($unit_id, $params) !== false) {
            $ajax_data['code'] = 0;
            $ajax_data['data'] = $post;
            $ajax_data['message'] = 'Updated Customer Information';
        } else {
            $ajax_data['code'] = 1;
            $errors = $this->vehicle_logic->getErrorMessage();
            if (! empty($errors) AND is_array($errors)) {
                $errors = implode(', ',$errors);
            } else {
                $errors = 'Action failed due to database issue';
            }
            
            $ajax_data['validation_error'][] = $ajax_data['message'] = $errors;                
        }

        $this->ajax_respond($ajax_data);
    }

    public function getStatesOptions()
    {
        $output = '[';

        $states = Dropdown::get_list_of_states();

        $last_index = count($states) - 1;
        $index = 0;

        foreach ($states as $key => $state) {
            $separator = ',';

            if ($index == $last_index) {
                $separator = '';
            }

            $output .= '{"value": "' . $key . '", "text": "' . $key . '"}' . $separator;
            $index++;
        }

        $output .= ']';
        die($output);
    }

    public function getVehicleStatusOptions()
    {
        $output = '[';

        $statuses = $this->vehicle_logic->getAllUnitStatus();

        $last_index = count($statuses) - 1;
        $index = 0;

        foreach ($statuses as $key => $status) {
            $separator = ',';

            if ($index == $last_index) {
                $separator = '';
            }

            $output .= '{"value": "' . $status['unitstatus_id'] . '", "text": "' . $status['unitstatusname'] . '"}' . $separator;
            $index++;
        }

        $output .= ']';
        die($output);
    }

    public function getAllVehicleMake()
    {
        echo
        '[
            {"value": "Honda", "text": "Honda"},
            {"value": "Toyota", "text": "Toyota"},
            {"value": "Acura", "text": "Acura"},
            {"value": "Kia", "text": "Kia"},
            {"value": "Ford", "text": "Ford"}
        ]';
        exit();
    }

    public function getAllVehicleYear()
    {
        echo
        '[
            {"value": "2013", "text": "2013"},
            {"value": "2012", "text": "2012"},
            {"value": "2011", "text": "2011"},
            {"value": "2010", "text": "2010"},
            {"value": "2009", "text": "2009"}
        ]';
        exit();
    }


    public function fetchAddressCSVImportTemplate()
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=import_address_template.csv');
        exit('"Name","Street","City","State","Zipcode","Country","Latitude","Longitude","Radius (miles)"');
    }

    public function getVehicleQuickHistory()
    {
        // this response need to be formatted exactly as below (whitespace is arbitrary). DO NOT JSON encode!
        $post   = $this->request->request->all();
    	$output = array(
    		"sEcho" => intval($post['sEcho']),
    		"iTotalRecords" => 0,
    		"iTotalDisplayRecords" =>0,
    		"data" => array()
    	);

        $user_id        = $this->user_session->getUserId();
        $account_id     = $this->user_session->getAccountId();
        $user_timezone  = $this->user_session->getUserTimeZone();

		$unit_id                    = $post['unit_id'];
		$params                     = $post;
		$params['duration']         = (isset($post['duration'])) ? $post['duration'] : '';
		$params['user_timezone']    = $user_timezone;

        // get event db for this unit
        $unit_info = $this->vehicle_logic->getVehicleInfo($unit_id);
		if ($unit_info !== false) {
			if (! empty($unit_info['db'])) {
			    $params['event_db']         = $unit_info['db'];
			    $params['unit_timezone']    = $unit_info['unit_timezone'];

			    // with event db, pull events for unit with provided params
			    $output = $this->vehicle_logic->getVehicleQuickHistory($user_id, $unit_id, $params);
            }
        }

        echo json_encode( $output );
        exit();
    }

    /**
     * Add a reference landmark to a unit (called via ajax)
     *
     * POST params: unit_id, longitude, longitude, street_address, state, country, title, radius
     *
     * @return array
     */
    public function addReferenceLandmarkToVehicle() 
    {
        $ajax_data  = array();
        $error      = $latitude = $longitude = $street_address = $city = $state = $zip = $country = '';
        $post       = $this->request->request->all();
        $unit_id    = $post['unit_id'];
        $account_id = $this->user_session->getAccountId();

        if (! empty($post['latitude'])) {
            $latitude = $post['latitude'];
        }
        
        if (! empty($post['longitude'])) {
            $longitude = $post['longitude'];
        }
        
        if (! empty($post['street_address'])) {
            $street_address = $post['street_address'];    
        }            
        
        if (! empty($post['city'])) {
            $city = $post['city'];
        }
        
        if (! empty($post['state'])) {
            $state = $post['state'];
        }

        if (! empty($post['zip'])) {
            $zip = $post['zip'];
        }
        
        if (! empty($post['country'])) {
            $country = $post['country'];
        }
        
        if (! empty($post['radius'])) {
            $radius = $post['radius'];
        }
        
        if (! empty($post['title'])) {
            $title = $post['title'];
        }
        
        if (! empty($unit_id)) {
            if ($latitude !== '' AND $longitude !== '') {
                if ($title !== '') {
                    if ($radius !== '') {
                        $latlong_coordinates = array(array('latitude' => $latitude, 'longitude' => $longitude));

                        $landmark_id = $this->territory_logic->saveTerritory($account_id, $latitude, $longitude, $title, $radius, $street_address, $city, $state, $zip, $country, 'circle', 'reference', '', $latlong_coordinates, 0, true);

                        if ($landmark_id !== false) {
                            if ($this->territory_logic->addTerritoryToVehicle($unit_id, $landmark_id) !== false) {
                                $ajax_data['code'] = 0;
                                $ajax_data['message'] = 'Added reference landmark to unit';   
                            } else {
                                if ($this->territory_logic->deleteTerritory($landmark_id, $account_id, true) !== false) {
                                    $error = 'Failed to add landmark to unit';
                                } else {
                                    $error = 'Failed to remove landmark';
                                }
                            }    
                        } else {
                           $error = 'Failed to save landmark to database';                                
                        }    
                    } else {
                        $error = 'Invalid radius';                            
                    }
                } else {
                    $error = 'Address Name cannot be blank';                        
                }
            } else {
                $error = 'Invalid latitude and/or longitude';                        
            }
        } else {
            $error = 'Invalid unit id';
        }
        
        if ($error !== '') {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = $error;
        }
        
        $this->ajax_respond($ajax_data); 
    }

    /**
     * Send email of Vehicle Quick History to a list of emails
     *
     * POST params: unit_id, event_type, email_list, start_date, end_date, time
     * 
     * @return void
     */
     public function sendEmailVehicleQuickHistory()
     {
        $ajax_data      = array();
        $error          = '';
        $user_id        = $this->user_session->getUserId();
        $user_timezone  = $this->user_session->getUserTimeZone();
        $post           = $this->request->request->all();
        $unit_id        = $post['unit_id'];

        $params['event_type']       = $post['event_type'];
        $params['start_date']       = Date::locale_to_utc($post['start_date'], $user_timezone);
        $params['end_date']         = Date::locale_to_utc($post['end_date'], $user_timezone);
        $params['duration']         = (isset($post['duration']))? $post['duration'] :'';
        $params['user_timezone']    = $user_timezone;
        $emails                     = $post['emails'];
        
        // temp validation for now
        if (empty($unit_id)) {
            $error = 'Invalid unit id';
        }
        
        if (empty($user_id)) {
            $error = 'Invalid user id';    
        }
        
        if (empty($params['event_type'])) {
            $error = 'Invalid quick history type';
        }
        
        if (empty($params['start_date'])) {
            $error = 'Invalid start date';
        }
        
        if (empty($params['end_date'])) {
            $error = 'Invalid end date';
        }

        if (! empty($emails)) {
            $emails = array_map('trim', explode(',', $emails));
        }
        
        if (empty($emails)) {
            $error = 'No email was provided';
        } else {
            $total_email_count = count($emails);  // the total number of emails that are going to be submitted
        }
        
        if ($error == '') {
            // get event db for this unit
    		if (($unit_info = $this->vehicle_logic->getVehicleInfo($unit_id)) !== false) {
    			if (! empty($unit_info['db'])) {
    			    $params['event_db']     = $unit_info['db'];
    			    $params['account_name'] = $this->user_session->getAccountName();
    			    $params['unit_name']    = $unit_info['unitname'];
                    $email_message          = $status_message = '';
                    
                    if (($result = $this->vehicle_logic->sendEmailVehicleQuickHistory($user_id, $unit_id, $emails, $params)) !== false) {
                        $failed_recipients  = $result['failed_recipients'];
                        $invalid_emails     = $result['invalid_emails'];
                        $total_email_fail   = count($failed_recipients) + count($invalid_emails);
                        
                        if ($total_email_fail == $total_email_count) {      // failed, no email was sent out to any of the email addresses (total emails failed == total emails submitted)
                            $ajax_data['code'] = 1;        
                            $status_message = 'Error (1)';
                        } else {                                            // success, the email was successfully sent to at least one of the email addresses
                            $ajax_data['code'] = 0;
                            $status_message = 'Email Sent';
                            if ($total_email_fail == 0) {
                                $email_message .= '<p>The email has been sent. It may take a while for the recipients to receive the email.</p>';
                            }
                        }
                        
                        if (! empty($failed_recipients)) {  
                            // $email_message .= 'The email was not able to be sent to the following recipient(s): <br><ul><li>' . implode('<li>', $failed_recipients) . '</ul>'; 
                            $email_message .= 'Partial'; 
                        }
                        
                        if (! empty($invalid_emails)) {
                            // $email_message .= 'The following email address(es) are invalid: <br><ul><li>' . implode('<li>', $invalid_emails) . '</ul>';
                            $email_message .= 'Invalid Emails'; 
                        }
                    } else {
                        $ajax_data['code'] = 1;
                        $status_message = 'Error (2)';
                        if ($errors = $this->vehicle_logic->getErrorMessage()) {
                            // $email_message = 'Failed to send email due to the following reason(s): <br><ul>';
                            $email_message .= '<ul>';
                            foreach($errors as $err) {
                                $email_message .= '<li>' . $err . '</li>';
                            }
                            $email_message .= '</ul>';
                        }
                    }
                    
                    $ajax_data['message'] = $status_message;
                    $ajax_data['email_message'] = $email_message;

                }
            }            
        } else {
             $ajax_data['code'] = 0;
             $ajax_data['message'] = $error;                
        }

         $this->ajax_respond($ajax_data);
     }

    /**
     * Get reference landmark data for Verification table (called via ajax)
     *
     * POST params: unit_id
     *
     * @return array
     */    
    public function getVehicleVerificationData() 
    {
        $user_timezone  = $this->user_session->getUserTimeZone();
        $post           = $this->request->request->all();
    	$unit_id        = $post['unit_id'];
    	$filter_type    = $post['filter_type'];
		$params                     = $post;
		$params['user_timezone']    = $user_timezone;

        // this response need to be formatted exactly as below (whitespace is arbitrary). DO NOT JSON encode!
    	$output = array(
    		"sEcho"                 => intval($post['sEcho']),
    		"iTotalRecords"         => 0,
    		"iTotalDisplayRecords"  => 0,
    		"data"                  => array()
    	);

        // get the reference landmarks for this unit
        if (($landmarks = $this->territory_logic->getVehicleVerificationData($unit_id, $params)) !== false) {
            $output = $landmarks;
        }
    	
        echo json_encode( $output );
        exit();            
    }

    /**
     * Export reference landmarks for a specific unit
     *
     * GET params: format, unit_id
     *
     * @return array
     */    
    public function exportReferenceLandmarks($format, $unit_id)
    {
        $user_timezone  = $this->user_session->getUserTimeZone();
        
        if (! empty($unit_id)) {
            $account_name = $this->user_session->getAccountName();
            $unit = $this->vehicle_logic->getVehicleInfo($unit_id);
            if (($unit !== false) AND ! empty($unit)) {
                $results = array();
                
                $landmarks = $this->territory_logic->getTerritoryByUnitId($unit_id, $user_timezone, true);
                if ($landmarks !== false) {
                    $results = $landmarks;    
                }
                
                $account_name = preg_replace('/[^A-Za-z0-9]/','_',trim($account_name));
                $unit_name = preg_replace('/[^A-Za-z0-9]/','_',trim($unit['unitname'])); 

                $filename = $account_name . '_' . $unit_name . '_reference_addresses';

                $fields = array('territoryname' => 'Name','formatted_address' => 'Address','latitude' => 'Latitude', 'longitude' => 'Longitude', 'radius_in_miles' => 'Radius (miles)', 'verified' => 'Verified', 'formatted_verified_date' => 'Verified Date');
                
                if($format == 'pdf') {
                    $pdf_builder = new TCPDFBuilder('L');
                    $pdf_builder->createTitle('Vehicle Verification');
                    $pdf_builder->createTable($results, $fields, $unit['unitname']);
                    $pdf_builder->Output($filename, 'D');
                } else {
                    $csv_builder = new CSVBuilder();
                    $csv_builder->setSeparator(',');
                    $csv_builder->setClosure('"');
                    $csv_builder->setFields($fields);
                    $csv_builder->format($results)->export($filename);                
                }
            }
        }
        
        exit();
    }

    /**
     * Export quick history for a specific unit
     *
     * GET params: unit_id, event_type, start_time, end_time, time
     *
     * @return void
     */    
    public function exportVehicleQuickHistory($format, $unit_id, $event_type, $start_date, $end_date, $time)
    {
        if (! empty($unit_id) AND ! empty($event_type) AND ! empty($start_date) AND ! empty($end_date)) {
            
            $params = array();            
            $params['event_type']   = $event_type;
            $params['start_date']   = str_replace('_', ' ', $start_date);
            $params['end_date']     = str_replace('_', ' ', $end_date);
            $params['duration']     = $time;
            $params['export']       = true;
            $user_id        = $this->user_session->getUserId();
            $account_name   = $this->user_session->getAccountName();
            $user_timezone  = $this->user_session->getUserTimeZone();

            $params['start_date']       = Date::locale_to_utc($params['start_date'], $user_timezone);
            $params['end_date']         = Date::locale_to_utc($params['end_date'], $user_timezone);
            $params['user_timezone']    = $user_timezone;
            
            // get event db for this unit
    		if (($unit_info = $this->vehicle_logic->getVehicleInfo($unit_id)) !== false) {
    			if (! empty($unit_info['db'])) {
    			    $params['event_db'] = $unit_info['db'];
    			    
    			    // with event db, pull events for unit with provided params
                    if (($output = $this->vehicle_logic->getVehicleQuickHistory($user_id, $unit_id, $params)) !== false) {
                        $account_name = preg_replace('/[^A-Za-z0-9]/','_',trim($account_name));
                        $unit_name = preg_replace('/[^A-Za-z0-9]/','_',trim($unit_info['unitname'])); 
                        $fields = array();
                        
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

                        $filename = $account_name . '_' . $unit_name . '_' . 'quick_history_' . $event_type;
                     
                        if($format == 'pdf') {
                            $pdf_builder = new TCPDFBuilder('L');
                            $pdf_builder->createTitle('Vehicle History');
                            $pdf_builder->createTable($output, $fields, $unit_info['unitname']);
                            $pdf_builder->Output($filename, 'D');
                        } else {
                            $csv_builder = new CSVBuilder();
                            $csv_builder->setSeparator(',');
                            $csv_builder->setClosure('"');
                            $csv_builder->setFields($fields);
                            $csv_builder->format($output)->export($filename);
                        }
                    }
                }
            }                       
        }
        
        exit();
    }

    /**
     * Upload reference landmarks for a specific unit
     *
     * @return void
     */    
    public function uploadReferenceLandmarks()
    {
        // simulate AJAX params
        $view_data = $this->ajax_data;
        $response['code'] = 1; // set response code to 'fail' by default
        $message = 'Failed to upload landmarks'; // set response message as a fail message by default
        $upload_message = '';
        $post = $this->request->request->all();
        $files = $this->request->files;

		if (! empty($files)) {
    		$file_path = '';
    		foreach ($files as $f) {
        		$file_path = $f->getPathname();
        		break;    // there should only be one file uploaded at a time
    		}
    		if (file_exists($file_path)) {
    		    if (! empty($post['reference-landmark-upload-unit-id'])) {
        		    $unit_id = $post['reference-landmark-upload-unit-id'];
        		    $account_id = $this->user_session->getAccountId();
        		    $user_id = $this->user_session->getUserId();
    
            		if (! empty($account_id)) {
            		    $incomplete_landmark_counter = $this->territory_logic->uploadTerritory($account_id, $user_id, $unit_id, $file_path, 'reference');
                		if ($incomplete_landmark_counter !== false) {
                    		$response['code'] = 0; // upload success
                    		$message = 'Success ';
                    		if ($incomplete_landmark_counter === 0) {
                        	    $upload_message = '<p>Your Addresses have been uploaded and processing will begin shortly. Once processed, any incomplete entries may be updated <a href="/landmark/incomplete"><small>here</small></a>.</p>';	
                    		} else {
                        	   	$upload_message = '<p><b>' . $incomplete_landmark_counter . '</b> address(es) were not able to be process due to missing, incomplete, and/or invalid components.';
                        	   	$upload_message .= ' You can make the correction to these addresses <a href="/landmark/incomplete"><small>here</small></a>. </p>';
                    		}
                		} else {
                    		$upload_message = 'An invalid template was used';
                		}
            		} else {
                		$upload_message = 'This account id is not valid';
            		}
        		} else {
            		$upload_message = 'This unit id is not valid';    		  
        		}
        		unlink($file_path);
            } else {
                $upload_message = 'Cannot find uploaded file or file is empty';    
            }
		} else {
			$upload_message = 'Failed to upload the csv file';
		}

		$response['message'] = $message;
        $response['message'] .= ', unit_id:' ;
        $response['message'] .= $unit_id ;
        $response['message'] .= ', account_id:' ;
        $response['message'] .= $account_id ;
        $response['message'] .= ', user_id:' ;
        $response['message'] .= $user_id ;
		$response['upload_message'] = $upload_message;
        $view_data['data']['response'] = json_encode($response);

        $this->ajax_render('partial/iframe-upload-response.html.twig', $view_data);
    }

    /**
     * Export Filter Vehicle List by search_string/group for an account
     *
     * GET params: string $format Specify type of export: pdf or csv
     * GET params: string $filterType (string_search or group_filter)
     * GET params: $filterValue1 (search_string or a group_id depending on type)
     * GET params: $filterValue2 (all or state status filter)
     *
     * @return array
     */    
    public function exportFilteredVehicleList($format, $filterType, $filterValue1, $filterValue2)
    {

        $vehicle_groups             = array();
        $account_id                 = $this->user_session->getAccountId();
        $user_id                    = $this->user_session->getUserId();
        $user_timezone              = $this->user_session->getUserTimeZone();
        $results                    = array();

        $params['sEcho']            = 0;
        $params['bSearchable_0']    = true;
        $params['bSearchable_1']    = true;
        $params['bSearchable_2']    = true;
        $params['bSearchable_3']    = true;
        $params['bSearchable_4']    = true;
        $params['bSearchable_5']    = true;
        $params['bSearchable_6']    = true;
        $params['bSearchable_7']    = true;
        $params['bSortable_0']      = true;
        $params['bSortable_1']      = true;
        $params['bSortable_2']      = true;
        $params['bSortable_3']      = true;
        $params['bSortable_4']      = true;
        $params['bSortable_5']      = true;
        $params['bSortable_6']      = true;
        $params['bSortable_7']      = true;
        $params['iSortCol_0']       = 0;
        $params['iSortingCols']     = 1;
        $params['iColumns']         = 8;
        $params['mDataProp_0']      = 'unitname';
        $params['mDataProp_1']      = 'unitgroupname';
        $params['mDataProp_2']      = 'eventstatus';
        $params['mDataProp_3']      = 'duration';
        $params['mDataProp_4']      = 'formatted_address';
        $params['mDataProp_5']      = 'lastevent';
        $params['mDataProp_6']      = 'display_unittime';
        $params['mDataProp_7']      = 'mileage';
        $params['sSortDir_0']       = 'asc';

        // if($filterType=='_SEARCH_'){
        //     $filterType='';
        // }
        // $params['string_search'] = $filterType;
        // if ($filterValue1 != '' AND $filterValue1 != 'ALL' AND $filterValue1 != 'All') {
        //     // $vehicle_groups = $filterValue1;
        // }
        // $params['vehicle_group_id'] = $filterValue1;
        // $params['vehicle_state_status'] = $filterValue2;
        // // if filterType is a string search, set params according
        // if ($filterType == 'string_search') {
        //     $params['string_search'] = $filterValue1;
        //     $params['vehicle_group_id'] = 'ALL';
        //     $params['vehicle_state_status'] = 'ALL';
        // } else {
        //     // if filterType is group_filter, set params according for the group
        //     if ($filterValue1 != '' AND $filterValue1 != 'ALL' AND $filterValue1 != 'All') {
        //         $vehicle_groups = $filterValue1;
        //     }
        //     $params['vehicle_group_id'] = $filterValue1;
        //     $params['vehicle_state_status'] = $filterValue2;
        // }
        
        $params['user_timezone'] = $user_timezone;

        if (($vehicles = $this->vehicle_logic->getVehiclesByGroupIds($user_id, $vehicle_groups, $account_id)) !== false) {

            foreach ( $vehicles as $k1 => $v1 ) {
                if($v1['unitstatus_id']!=2){
                    $noInventory[]=$v1;
                }
            }
            $vehicles=$noInventory;

            $output = $this->vehicle_logic->getVehicleListDataInfo($vehicles, $params, '');
            $results = (isset($output['data']) AND ! empty($output['data'])) ? $output['data'] : array();
        }
        // $results[] = array('unitname' => 'VARS','unitgroupname' => 'string_search:' . $params['string_search'], 'eventstatus' => 'vehicle_group_id:' . $params['vehicle_group_id'], 'duration' => 'vehicle_state_status:' . $params['vehicle_state_status']) ;
        
        // $filename = str_replace(' ', '_', $this->user_session->getAccountName().'_FilterVehicleListExport_'.$filterType.'-'.$filterValue1.'-'.$filterValue2);
        $fields = array('unitname' => 'Vehicle','unitgroupname' => 'Group', 'eventstatus' => 'Status', 'duration' => 'Duration', 'formatted_address' => 'Address','lastevent' => 'Last Event', 'display_unittime' => 'Date & Time', 'mileage' => 'Mileage');

        switch($format) {

            case 'csv' :    $csv_builder = new CSVBuilder();
                            $csv_builder->setSeparator(',');
                            $csv_builder->setClosure('"');
                            $csv_builder->setFields($fields);
                            $csv_builder->format($results)->export($filename);
                            break;

                default :   $pdf_builder = new TCPDFBuilder('L');
                            $pdf_builder->createTitle('Vehicle List');
                            $pdf_builder->createTable($results, $fields);
                            $pdf_builder->Output($filename, 'D');

        }

        exit();
    }

    /**
     * Delete reference address/landmark (called via AJAX)
     *
     * POST params: landmark_id
     *
     * @return void
     */     
    public function deleteReferenceLandmark()
    {
        $ajax_data      = array();
        $post           = $this->request->request->all();
        $account_id     = $this->user_session->getAccountId();
        $landmark_id    = 0;
        
        if (! empty($post['landmark_id'])) {
            $landmark_id = $post['landmark_id'];
        }
        
        if (! empty($landmark_id)) {
            if (! empty($account_id)) {
                if ($this->territory_logic->deleteTerritory($landmark_id, $account_id, true) !== false) {
                    $ajax_data['code'] = 0;
                    $ajax_data['message'] = 'Deleted reference address';    
                } else {
                    $ajax_data['code'] = 1;
                    $ajax_data['message'] = 'Failed to delete the reference address';                    
                }
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Invalid Account ID';   
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid Landmark ID'; 
        }    
        
        $this->ajax_respond($ajax_data);
    }

    /**
     * Update a reference landmark (called via AJAX)
     *
     * POST params: landmark_id, longitude, longitude, street_address, state, country, title, radius, zipcode, country
     *
     * @return void
     */
    public function updateReferenceLandmark() 
    {
        $ajax_data      = $params = array();
        $landmark_id    = 0;
        $post           = $this->request->request->all();
        $account_id     = $this->user_session->getAccountId();

        if (! empty($post['latitude'])) {
            $params['latitude'] = $post['latitude'];
        }
        
        if (! empty($post['longitude'])) {
            $params['longitude'] = $post['longitude'];
        }
        
        if (! empty($post['street_address'])) {
            $params['streetaddress'] = $post['street_address'];    
        }
        
        if (! empty($post['city'])) {
            $params['city'] = $post['city'];
        }
        
        if (! empty($post['state'])) {
            $params['state'] = $post['state'];
        }

        if (! empty($post['zip'])) {
            $params['zipcode'] = $post['zip'];
        }
        
        if (! empty($post['country'])) {
            $params['country'] = $post['country'];
        }
        
        if (! empty($post['radius'])) {
            $params['radius'] = $post['radius'];
        }
        
        if (! empty($post['title'])) {
            $params['territoryname'] = $post['title'];
        }
        
        if (! empty($post['landmark_id'])) {
            $landmark_id = $post['landmark_id'];
        }

        if (! empty($landmark_id)) {
            if (! empty($account_id)) {
                if ($this->territory_logic->updateTerritoryInfo($landmark_id, $params, 'territory') !== false) {
                    $ajax_data['code'] = 0;
                    $ajax_data['message'] = 'Verification address updated';
                } else {
                    $ajax_data['code'] = 1;
                    $ajax_data['message'] = 'Failed to update verification address';                                
                }
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Invalid Account ID';                        
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid Territory ID'; 
        }
        
        $this->ajax_respond($ajax_data);        
    }
    
    /**
     * Reverse Geocode using Tiger
     *
     * POST params: latitude, longitude
     *
     * @return void
     */
    public function reverseGeocode() 
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();
        $latitude   = '';
        $longitude  = '';
        
        if (! empty($post['latitude'])) {
            $latitude = $post['latitude'];
        }
        
        if (! empty($post['longitude'])) {
            $longitude = $post['longitude'];
        }
        
        if ($latitude != '' AND is_numeric($latitude) AND $longitude != '' AND is_numeric($longitude)) {
            $geocoder = new Tiger();
            $result = $geocoder->reverseGeocode($latitude, $longitude);
            $ajax_data['code'] = 0;
            $ajax_data['data'] = $result;
            $ajax_data['message'] = 'Successfully retrieved data';
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid coordinates';
        }        
        
        $this->ajax_respond($ajax_data);        
    }
    
    /**
     * Get quick history for a specific unit to plot on map
     *
     * GET params: unit_id, event_type, start_time, end_time, time
     *
     * @return void
     */    
    public function getVehicleQuickHistoryForMap()
    {
        $ajax_data      = array();
        $post           = $this->request->request->all();
        $error          = '';
        $user_id        = $this->user_session->getUserId();
        $user_timezone  = $this->user_session->getUserTimeZone();
        $unit_id        = $post['unit_id'];

        $params['event_type']   = $post['event_type'];
        $params['start_date']   = Date::locale_to_utc($post['start_date'], $user_timezone);
        $params['end_date']     = Date::locale_to_utc($post['end_date'], $user_timezone);
        $params['duration']     = (isset($post['duration']))? $post['duration'] :'';
        $params['sEcho']        = 0;
        $params['iColumns']     = 4;
        
        // temp validation for now
        if (empty($unit_id)) {
            $error = 'Invalid unit id';
        }
        
        if (empty($user_id)) {
            $error = 'Invalid user id';    
        }
        
        if (empty($params['event_type'])) {
            $error = 'Invalid quick history type';
        }
        
        if (empty($params['start_date'])) {
            $error = 'Invalid start date';
        }
        
        if (empty($params['end_date'])) {
            $error = 'Invalid end date';
        }
        
        if ($error == '') {
            // get event db for this unit
    		if (($unit_info = $this->vehicle_logic->getVehicleInfo($unit_id)) !== false) {
    			if (! empty($unit_info['db'])) {
    			    $params['event_db'] = $unit_info['db'];
                    //$params['export'] = true;
                    $params['user_timezone'] = $user_timezone;
                    if (($result = $this->vehicle_logic->getVehicleQuickHistory($user_id, $unit_id, $params)) !== false) {                            
                        $ajax_data['code'] = 0;
                        $ajax_data['data'] = $result['data'];
                        $ajax_data['message'] = empty($result) ? 'No data available for this time duration' : 'Successfully retrieved data';
                    } else {
                        $ajax_data['code'] = 1;
                        $ajax_data['message'] = 'Failed to retrieve data';
                    }
                } else {
                    $ajax_data['code'] = 1;
                    $ajax_data['message'] = 'Unit has no event database';
                }
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Failed to retrieved unit info';
            }            
        } else {
             $ajax_data['code'] = 0;
             $ajax_data['message'] = $error;                
        }
         
         $this->ajax_respond($ajax_data);
    }

    /**
     * Add vehicles to a vehicle group (called via ajax)
     *
     * POST params: vehicles, unitgroup_id
     *
     * @return void
     */
    public function addVehicleToGroup()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();

        if (! empty($post['unitgroup_id'])) {
            if (! empty($post['vehicles']) AND is_array($post['vehicles'])) {
                $vehicles = $post['vehicles'];
                $unitgroup_id = $post['unitgroup_id'];
                $ajax_data['message'] = '';
                foreach ($vehicles as $unit) {
                    if ($this->vehicle_logic->updateAssignedVehicleGroup($unit['id'], $unitgroup_id) !== false) {
                        if (! isset($ajax_data['code'])) {
                            $ajax_data['code'] = 0;
                            $ajax_data['message'] = 'Assigned vehicles to vehicle group';
                        }
                        $ajax_data['data']['added_groups'][] = $unit;      
                    } else {
                        $ajax_data['code'] = 1;
                        $errors = $this->vehicle_logic->getErrorMessage();
                        if (! empty($errors) AND is_array($errors)) {
                            $errors = implode(',', $errors);
                        } else {
                            $errors = 'Failed due to database error';
                        }
                        $ajax_data['message'] .= $unit['name'] . ' - ' . $errors . ' | ';
                        $ajax_data['data']['failed_groups'][] = $unit;
                    }
                }
                
                // if one or more vehicle groups can not be assign to this user, build a list of these vehicle groups and their associating error message
                if (($ajax_data['code'] === 1) AND ! empty($ajax_data['message']) AND ! empty($ajax_data['data']['failed_groups'])) {
                    $ajax_data['message'] = 'The following vehicle(s) were not able to be assign to this Group: ' . $ajax_data['message'];
                } 
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Invalid vehicle ids';                    
            }    
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid Group id';
        }
       
        $this->ajax_respond($ajax_data);
    }

    /**
     * Add vehicle group to user (called via ajax)
     *
     * POST params: vehiclegroups, user_id
     *
     * @return void
     */
    public function addVehicleGroupToUser()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();

        if (! empty($post['user_id'])) {
            if (! empty($post['vehiclegroups']) AND is_array($post['vehiclegroups'])) {
                $vehiclegroups = $post['vehiclegroups'];
                $user_id = $post['user_id'];
                $ajax_data['message'] = '';
                foreach ($vehiclegroups as $vehiclegroup) {
                    if ($this->vehicle_logic->addVehicleGroupToUser($vehiclegroup['id'], $user_id) !== false) {
                        if (! isset($ajax_data['code'])) {
                            $ajax_data['code'] = 0;
                            $ajax_data['message'] = 'Assigned vehicle groups to user';
                        }
                        $ajax_data['data']['added_groups'][] = $vehiclegroup;      
                    } else {
                        $ajax_data['code'] = 1;
                        $errors = $this->vehicle_logic->getErrorMessage();
                        if (! empty($errors) AND is_array($errors)) {
                            $errors = implode(',', $errors);
                        } else {
                            $errors = 'Failed due to database error';
                        }
                        $ajax_data['message'] .= $vehiclegroup['name'] . ' - ' . $errors . ' | ';
                        $ajax_data['data']['failed_groups'][] = $vehiclegroup;
                    }
                }
                
                // if one or more vehicle groups can not be assign to this user, build a list of these vehicle groups and their associating error message
                if (($ajax_data['code'] === 1) AND ! empty($ajax_data['message']) AND ! empty($ajax_data['data']['failed_groups'])) {
                    $ajax_data['message'] = 'The following vehicle group(s) were not able to be assign to this user: ' . $ajax_data['message'];
                } 
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Invalid vehicle group ids';                    
            }    
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid user id';
        }
       
        $this->ajax_respond($ajax_data);
    }

    /**
     * Remove vehicle group from user (called via ajax)
     *
     * POST params: vehiclegroup_id, user_id
     *
     * @return void
     */
    public function removeVehicleGroupFromUser()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();

        if (! empty($post['user_id'])) {
            if (! empty($post['vehiclegroups']) AND is_array($post['vehiclegroups'])) {
                $user_id = $post['user_id'];
                $vehiclegroups = $post['vehiclegroups'];
                $ajax_data['message'] = '';
                foreach ($vehiclegroups as $vehiclegroup) {
                    if ($this->vehicle_logic->removeVehicleGroupFromUser($vehiclegroup['id'], $user_id) !== false) {
                        if (! isset($ajax_data['code'])) {
                            $ajax_data['code'] = 0;
                            $ajax_data['message'] = 'Removed vehicle group from user';      
                        }
                    } else {
                        $ajax_data['code'] = 1;
                        $errors = $this->vehicle_logic->getErrorMessage();
                        if (! empty($errors) AND is_array($errors)) {
                            $errors = implode(',', $errors);
                        } else {
                            $errors = 'Failed due to database error';
                        }
                        $ajax_data['message'] .= $vehiclegroup['name'] . ' - ' . $errors . ' | ';
                        $ajax_data['data']['failed_groups'][] = $vehiclegroup;
                    }
                }
                
                // if one or more vehicle groups can not be remove from this user, build a list of these vehicle groups and their associating error message
                if (($ajax_data['code'] === 1) AND ! empty($ajax_data['message']) AND ! empty($ajax_data['data']['failed_groups'])) {
                    $ajax_data['message'] = 'The following vehicle group(s) were not able to be remove from this user: ' . $ajax_data['message'];
                }
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Invalid vehicle group ids';                    
            }    
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid user id';
        }
               
        $this->ajax_respond($ajax_data);
    }

    /**
     * Get the vehicle group info by filtered paramaters (called via ajax)
     *
     * @return array
     */
    public function getFilteredVehicleGroupList()
    {
        $ajax_data      = array();
        $vehicle_groups = array();
        $post           = $this->request->request->all();
        $user_id        = $this->user_session->getUserId();

        $sEcho = 0;
        if (isset($post['sEcho']) AND $post['sEcho'] != '') {
            $sEcho = $post['sEcho'];
        }

        $output = array(
            "sEcho" => intval($sEcho),
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "data" => array()
        );

    	$output = $this->vehicle_logic->getFilteredVehicleGroupList($user_id, $post);

        echo json_encode( $output );
        exit;
    }

    /**
     * Get the vehicle group info by group id (called via ajax)
     *
     * @return void
     */
    public function getVehicleGroupInfo()
    {
        $ajax_data  = array();
        $account_id = $this->user_session->getAccountId();
        $user_id    = $this->user_session->getUserId();
        $post       = $this->request->request->all();
        
        if (! empty($post['group_id'])) {
            $vehiclegroup = $this->vehicle_logic->getVehicleGroupsByUserId($user_id, array($post['group_id']), true); // the third paramter is a bool indicating if we should include the units
            if ($vehiclegroup !== false) {
                $vehicle_groups = array();
                // get the current vehicle groups that user have access to for building dropdowns
                $temp_groups = $this->vehicle_logic->getVehicleGroupsByUserId($user_id);
                if (! empty($temp_groups)) {
                    // get the array values (in order to get numeric index array)
                    $vehicle_groups = array_values($temp_groups);    
                }        
                $ajax_data['code'] = 0;
                $ajax_data['data']['vehicle_groups'] = $vehicle_groups;
                $ajax_data['data']['vehiclegroup_data'] = array_pop($vehiclegroup['groups']);
                $ajax_data['data']['defaultgroup_id'] = $vehiclegroup['defaultgroup_id'];
                $ajax_data['message'] = 'Successfully retrieved vehicle group info';    
            } else {
                $errors = $this->vehicle_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(',', $errors);    
                }
                $ajax_data['code'] = 1;
                $ajax_data['message'] = $errors;                    
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid vehicle group id';                
        }
        
        $this->ajax_respond($ajax_data);
    }

    /**
     * Update the vehicle group info by vehicle group id (called via ajax)
     *
     * POST params: unitgroup_id
     *
     * @return array
     */
    public function updateVehicleGroupInfo()
    {
        $ajax_data      = array();
        $post           = $this->request->request->all();
        $account_id     = $this->user_session->getAccountId();
    	$unitgroup_id   = $post['primary_keys']['vehicleGroupPk'];
        $params         = array();
        
        if (! empty($post['id'])) {
            if ($post['id'] == 'vehicle-group-name') {
                $params['unitgroupname'] = $post['value'];
            }
        }

        if (! empty($params) AND ! empty($unitgroup_id)) {
            if ($this->vehicle_logic->updateVehicleGroupInfo($unitgroup_id, $account_id, $params) !== false) {
                //$ajax_data['data']      = $post;
                $ajax_data['code']      = 0;
                $ajax_data['message']   = 'Updated Vehicle Group Information';
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Failed to Update Vehicle Group Information';
                $errors = $this->vehicle_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(',', $errors);
                    //$ajax_data['message'] = $errors;
                    $ajax_data['validation_error'] = array($errors);
                }
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Error';
        }

        $ajax_data['data'] = $post;

        $this->ajax_respond($ajax_data);
    }
    
    /**
     * Delete vehicle group (called via ajax)
     *
     * POST params: unitgroup_id
     *
     * @return array
     */
    public function deleteVehicleGroup()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();
        $account_id = $this->user_session->getAccountId();

        if (! empty($post['vehiclegroup_id'])) {
            $params             = array();
            $params['active']   = 0;
            if ($this->vehicle_logic->updateVehicleGroupInfo($post['vehiclegroup_id'], $account_id, $params) !== false) {
	            $ajax_data['code'] = 0;
                $ajax_data['message'] = 'Deleted vehicle group';
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Failed to delete vehicle group';
                $errors = $this->vehicle_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(',', $errors);
                    $ajax_data['validation_error']  = $errors;
                    $ajax_data['message']           = $errors;
                }
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid vehicle group id';
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Add vehicle group to user (called via ajax)
     *
     * POST params: vehicles, vehiclegroup_id
     *
     * @return void
     */
    public function addVehiclesToGroup()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();

        if (! empty($post['vehiclegroup_id'])) {
            if (! empty($post['vehicles']) AND is_array($post['vehicles'])) {
                $vehicles = $post['vehicles'];
                $vehiclegroup_id = $post['vehiclegroup_id'];
                $ajax_data['message'] = '';
                foreach ($vehicles as $vehicle) {
                    if ($this->vehicle_logic->updateUnitInfo($vehicle['id'], array('unitgroup_id' => $vehiclegroup_id)) !== false) {
                        if (! isset($ajax_data['code'])) {
                            $ajax_data['code'] = 0;
                            $ajax_data['message'] = 'Assigned vehicles to vehicle group';
                        }
                        $ajax_data['data']['added_groups'][] = $vehicle;      
                    } else {
                        $ajax_data['code'] = 1;
                        $errors = $this->vehicle_logic->getErrorMessage();
                        if (! empty($errors) AND is_array($errors)) {
                            $errors = implode(',', $errors);
                        } else {
                            $errors = 'Failed due to database error';
                        }
                        $ajax_data['message'] .= $vehicle['name'] . ' - ' . $errors . ' | ';
                        $ajax_data['data']['failed_groups'][] = $vehicle;
                    }
                }
                
                // if one or more vehicles can not be assign to this user, build a list of these vehicles and their associating error message
                if (($ajax_data['code'] === 1) AND ! empty($ajax_data['message']) AND ! empty($ajax_data['data']['failed_groups'])) {
                    $ajax_data['message'] = 'The following vehicle(s) were not able to be assign to this group: ' . $ajax_data['message'];
                } 
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Invalid vehicles';                    
            }    
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid vehicle group id';
        }
       
        $this->ajax_respond($ajax_data);
    }

    /**
     * Remove vehicle group from user (called via ajax)
     *
     * POST params: vehicles, vehiclegroup_id
     *
     * @return void
     */
    public function removeVehiclesFromGroup()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();

        if (! empty($post['vehiclegroup_id'])) {
            if (! empty($post['vehicles']) AND is_array($post['vehicles'])) {
                $vehicles = $post['vehicles'];
                $ajax_data['message'] = '';
                foreach ($vehicles as $vehicle) {
                    if ($this->vehicle_logic->updateUnitInfo($vehicle['id'], array('unitgroup_id' => $post['vehiclegroup_id'])) !== false) {
                        if (! isset($ajax_data['code'])) {
                            $ajax_data['code'] = 0;
                            $ajax_data['message'] = 'Removed vehicles from vehicle group';      
                        }
                    } else {
                        $ajax_data['code'] = 1;
                        $errors = $this->vehicle_logic->getErrorMessage();
                        if (! empty($errors) AND is_array($errors)) {
                            $errors = implode(',', $errors);
                        } else {
                            $errors = 'Failed due to database error';
                        }
                        $ajax_data['message'] .= $vehicle['name'] . ' - ' . $errors . ' | ';
                        $ajax_data['data']['failed_groups'][] = $vehicle;
                    }
                }
                
                // if one or more vehicle groups can not be remove from this user, build a list of these vehicle groups and their associating error message
                if (($ajax_data['code'] === 1) AND ! empty($ajax_data['message']) AND ! empty($ajax_data['data']['failed_groups'])) {
                    $ajax_data['message'] = 'The following vehicle(s) were not able to be remove from this group: ' . $ajax_data['message'];
                }
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Invalid vehicles';                    
            }    
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid vehicle group id';
        }
               
        $this->ajax_respond($ajax_data);
    }

    /**
     * Save vehicle group (called via ajax)
     *
     * POST params: vehiclegroupname
     *
     * @return void
     */
    public function addVehicleGroup()
    {
        $ajax_data  = array();
        $user_id    = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();
        $post       = $this->request->request->all();
        
        if (! empty($post['vehiclegroupname'])) {
            $vehiclegroup_id = $this->vehicle_logic->addVehicleGroup($account_id, $post['vehiclegroupname']);
            if ($vehiclegroup_id !== false) {
                $ajax_data['code'] = 0;
                $ajax_data['message'] = 'Added Vehicle Group';
                // $add_to_user = $this->vehicle_logic->addVehicleGroupToUser($vehiclegroup_id, $user_id);
                if ($add_to_user !== false) {
                    $ajax_data['data']['groupid'] = $vehiclegroup_id;
                    $ajax_data['action'] = 'vehiclegroup-add';
                } else {
                    $errors = $this->vehicle_logic->getErrorMessage();
                    if (! empty($errors) AND is_array($errors)) {
                        $errors = implode(',', $errors);
                    } else {
                        $errors = 'Failed due to database error';
                    }
                    
                    $ajax_data['code'] = 1;
                    $ajax_data['message'] = $errors;                        
                }    
            } else {
                $errors = $this->vehicle_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(',', $errors);
                } else {
                    $errors = 'Failed due to database error';
                }
                
                $ajax_data['code'] = 1;
                $ajax_data['message'] = $errors;                   
            }    
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Vehicle Group Name cannot be blank';                
        }
               
        $this->ajax_respond($ajax_data);            
    }
    
    /**
     * Get the vehicle info by filtered paramaters (called via ajax)
     *
     * POST params: search_string, vehiclegroup_id
     *
     * @return array
     */
    public function getFilteredAvailableVehicles()
    {
        $ajax_data  = array();
        $account_id = $this->user_session->getAccountId();
        $post       = $this->request->request->all();
        
        $params['search_string'] =  isset($post['search_string']) ? $post['search_string'] : '';
        $params['unitgroup_id'] =  (isset($post['vehiclegroup_id']) AND $post['vehiclegroup_id']!= '') ? $post['vehiclegroup_id'] : '';

        $vehicles = $this->vehicle_logic->getFilteredAvailableVehicles($account_id, $params);
        if ($vehicles !== false) {
            $ajax_data['code'] = 0;
            $ajax_data['data']['units'] = $vehicles;
            $ajax_data['message'] = 'Successfully retrieved available vehicles';
        } else {
            $errors = $this->vehicle_logic->getErrorMessage();
            if (! empty($errors) AND is_array($errors)) {
                $errors = implode(',', $errors);
            } else {
                $errors = 'Failed due to database error';
            }
            
            $ajax_data['code'] = 1;
            $ajax_data['message'] = $errors; 
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Get users by vehicle group id (called via ajax)
     *
     * @return void
     */
    public function getUsersByVehicleGroupId()
    {
        $ajax_data  = array();
        $account_id = $this->user_session->getAccountId();
        $post       = $this->request->request->all();
        
        if (! empty($post['group_id'])) {
            $users = $this->vehicle_logic->getUsersByVehicleGroupId($account_id, $post['group_id'], true); // the third parameter indicates if we want to include users not assigned to this group too
            if ($users !== false) {
                $ajax_data['code'] = 0;
                $ajax_data['data']['users'] = $users;
                $ajax_data['data']['usertypes'] = $this->user_logic->getUserTypesByAccountId($account_id);
                $ajax_data['message'] = 'Successfully retrieved users';    
            } else {
                $errors = $this->vehicle_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(',', $errors);    
                }
                $ajax_data['code'] = 1;
                $ajax_data['message'] = $errors;                    
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid vehicle group id';                
        }
        
        $this->ajax_respond($ajax_data);
    }

    /**
     * Assign/Remove users to/from a vehicle group (called via ajax)
     *
     * POST params: vehiclegroup_id, add_users, remove_users
     *
     * @return void
     */
    public function updateVehicleGroupUsers()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();
        
        if (! empty($post['vehiclegroup_id'])) {
            $vehiclegroup_id = $post['vehiclegroup_id'];                
            if (! empty($post['add_users']) OR ! empty($post['remove_users'])) {
                $add_message = $remove_message = '';
                
                // remove users from vehicle group
                if (! empty($post['add_users'])) {
                    $add_users = $post['add_users'];
                    foreach ($add_users as $user) {
                        $added = $this->vehicle_logic->addVehicleGroupToUser($vehiclegroup_id, $user['id']);
                        if ($added !== false) {
                            if (! isset($ajax_data['code'])) {
                                $ajax_data['code'] = 0;
                            }
                            $ajax_data['data']['updated_users'][] = $user;    
                        } else {
                            $errors = $this->vehicle_logic->getErrorMessage();
                            if (! empty($errors) AND is_array($errors)) {
                                $errors = implode(',', $errors);    
                            }
                            $ajax_data['code'] = 1;
                            $add_message .= $user['name'] . ' - ' . $errors . ' | ';
                            $ajax_data['data']['failed_add_users'][] = $user;                    
                        }
                    }
                    
                    // if one or more users can not be added to this vehicle group, build a list of these users and their associating error message
                    if (($ajax_data['code'] === 1) AND ! empty($add_message) AND ! empty($ajax_data['data']['failed_add_users'])) {
                        $add_message = 'The following user(s) were not able to be added to this group: ' . $add_message;
                    }
                }
                
                // remove users from vehicle group
                if (! empty($post['remove_users'])) {
                    $remove_users = $post['remove_users'];
                    foreach ($remove_users as $user) {
                        $removed = $this->vehicle_logic->removeVehicleGroupFromUser($vehiclegroup_id, $user['id']);
                        if ($removed !== false) {
                            if (! isset($ajax_data['code'])) {
                                $ajax_data['code'] = 0;
                            }
                            $ajax_data['data']['updated_users'][] = $user;     
                        } else {
                            $errors = $this->vehicle_logic->getErrorMessage();
                            if (! empty($errors) AND is_array($errors)) {
                                $errors = implode(',', $errors);    
                            }
                            $ajax_data['code'] = 1;
                            $remove_message .= $user['name'] . ' - ' . $errors . ' | ';
                            $ajax_data['data']['failed_remove_users'][] = $user;                    
                        }
                    }
                    
                    // if one or more users can not be removed from this vehicle group, build a list of these users and their associating error message
                    if (($ajax_data['code'] === 1) AND ! empty($remove_message) AND ! empty($ajax_data['data']['failed_remove_users'])) {
                        $remove_message = 'The following user(s) were not able to be removed from this group: ' . $remove_message;
                    }
                }
                
                if ($ajax_data['code'] === 1) {
                    $ajax_data['message'] = $add_message . $remove_message;
                } else {
                    $ajax_data['message'] = 'Successfully assigned/removed users from vehicle group';
                }
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'No users to add to vehicle group or remove from vehicle group';                    
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid vehicle group id';                
        }
        
        $this->ajax_respond($ajax_data);
    }

    /**
     * Decode a vin using VINquery's VIN Decoding Web Services (called via ajax)
     *
     * POST params: unit_id, vin
     *
     * @return array
     */
    public function decodeVin()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();
        
        if (! empty($post['unit_id'])) {
            if (! empty($post['vin'])) {
                // initializes the vin decoder class and passes along an access key to use the web service
                $vin_decoder = new VinDecoder('1cd8bfd1-4e97-4b7e-9651-8eb8a6d4f32f');
                
                // set the report type (2 - Extended Report)
                $vin_decoder->setReportType(2);
                
                // decode the vin and return the result as an associative array
                $result = $vin_decoder->decodeVin($post['vin']);
                
                if (! empty($result)) {
                    $update = array();
                    if (! empty($result['make'])) {
                        $update['make'] = $result['make'];
                    }
                    
                    if (! empty($result['model'])) {
                        $update['model'] = $result['model'];
                    }
                    
                    if (! empty($result['year'])) {
                        $update['year'] = $result['year'];
                    }
                    
                    $update_vehicle = $this->vehicle_logic->updateVehicleInfo($post['unit_id'], $update, 'unitattribute');
                    if ($update_vehicle !== false) {
                        $ajax_data['code'] = 0;
                        $ajax_data['data'] = $result;
                        $ajax_data['message'] = 'Vin was successfully decoded and vehicle info has been updated';
                    } else {
                        $error = $this->vehicle_logic->getErrorMessage();
                        if (! empty($error) AND is_array($error)) {
                            $error = implode(', ', $error);
                        } else {
                            $error = 'Failed to update vehicle info due to an unknown error';
                        }
                        
                        $ajax_data['code'] = 1;
                        $ajax_data['message'] = $error;
                    }    
                } else {
                    $error = $vin_decoder->getErrorMessage();
                    if (! empty($error) AND is_array($error)) {
                        $error = implode(', ', $error);
                    } else {
                        $error = 'Failed to decode vin due to an unknown error';
                    }
                    
                    $ajax_data['code'] = 1;
                    $ajax_data['message'] = $error;
                }
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Please enter a Vin to be decoded';                
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Record Id (' . $post['unit_id'] . ') is invalid';                
        }
        
        $this->ajax_respond($ajax_data);
    }

    public function getCommandStatus()
    {

        $ajax_data      = array();
        $post           = $this->request->request->all();
        $unit_id        = $post['unit_id'];
        $command_type   = $post['command_type'];

        $ajax_data['code'] = 1;
        $ajax_data['in_id'] = $unit_id;
        $ajax_data['console'] = $command_type;
        $ajax_data['message'] = 'Waiting for response... (' . $unit_id . ':' .$command_type .')';

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

        $ajax_data['code'] = 2;
        $ajax_data['message'] = 'Processing... (' . $unit_id . ':' .$command_type . ':'  . $event_id .')';

        $response = $this->unitcommand_logic->getCommandResponse( $unit_id, $event_id );
        if ($response !== false && !empty($response)) {

            $ajax_data['code'] = 0;
            $ajax_data['console'] = 'unitcommand_logic->getCommandResponse:success:' . $response['id'] . ':' . $response['event_id'] . ':' . $response['servertime'];
            $ajax_data['message'] = 'Successfully Responded';

            $user_id = $this->user_session->getUserId();
            if ($user_id) {
                $user = $this->user_logic->getUserById($user_id);
                if ($user !== false) {
                    switch ($command_type) {
                        case   'locate_on_demand' : $result = $this->vehicle_logic->ajaxLocate($user,$unit_id);
                                                    $lastunitevent['latitude'] = $result[0]['latitude'];
                                                    $lastunitevent['longitude'] = $result[0]['longitude'];
                                                    $lastunitevent['formatted_address'] = $this->address_logic->validateAddress($result[0]['streetaddress'], $result[0]['city'], $result[0]['state'], $result[0]['zipcode'], $result[0]['country']);
                                                    $lastunitevent['infomarker_address'] = $this->address_logic->validateAddress($result[0]['streetaddress'], '<br>'.$result[0]['city'], $result[0]['state'], $result[0]['zipcode'], $result[0]['country']);
                                                    $ajax_data['event'] = $lastunitevent;
                                                    $ajax_data['message'] = 'Locate Successful';
                                                    break;
                        case     'starter_enable' : $element='starterstatus';
                                                    $value='Enabled';
                                                    $ajax_data['message'] = 'Response: Starter Enabled';
                                                    break;
                        case    'starter_disable' : $element='starterstatus';
                                                    $value='Disabled';
                                                    $ajax_data['message'] = 'Response: Starter Disabled';
                                                    break;
                        case        'reminder_on' : $element='reminderstatus';
                                                    $value='On';
                                                    $ajax_data['message'] = 'Response: Reminder On';
                                                    break;
                        case       'reminder_off' : $element='reminderstatus';
                                                    $value='Off';
                                                    $ajax_data['message'] = 'Response: Reminder Off';
                                                    break;
                    }
                    if(($element)&&($value)){
                        $result = $this->vehicle_logic->ajaxUpdate($user,$unit_id,$element,$value);
                        if($result['value']==$value){
                            // $ajax_data['message'] .= 'ful';
                            $ajax_data['metrics'] = 1;
                        }
                    }
                }
            }

        } else {
            $ajax_data['code'] = 1;
            // $ajax_data['console'] = 'unitcommand_logic->getCommandResponse:record not found';
            // $ajax_data['message'] = 'Record not found';
            $error = $this->unitcommand_logic->getErrorMessage();
            if (!empty($error)){
                if(is_array($error)) {
                    $ajax_data['message'] = 'ERROR3:' . $unit_id . ':' . $event_id . ':' . implode(', ', $error);
                } else {
                    $ajax_data['message'] = 'ERROR2:' . $unit_id . ':' . $event_id . ':' . $error;
                }
            } else {
                $ajax_data['message'] = 'ERROR1:' . $unit_id . ':' . $event_id . ':' . $error;
            }                
        }    

        $this->ajax_respond($ajax_data);
       
    }
}
