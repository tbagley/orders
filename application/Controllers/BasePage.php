<?php

namespace Controllers;

use GTC\Component\Form\Validation;
use Models\Logic\ContactLogic;
use Models\Logic\UserLogic;
use Models\Logic\VehicleLogic;

/**
 * Class BasePage
 *
 * Parent class for all web page routes
 *
 * @package Controllers
 */
class BasePage extends Base
{
    protected $view_data = array();

    // can be overwritten in any page
    protected $apple_touch_icon = "apple-touch-114-positionplus.png";

    // can be overwritten in any page
    protected $browser_title = "Position Plus";

    // can be overwitten in any page
    protected $layout = 'layout/layout.html.twig';

    // array of css files to include
    protected $css_files = array();

    // array of javascript files to include
    protected $js_files = array();

    // array of primary navigation links
    protected $primaryNavigation = array();

    // array of secondary navigation links
    protected $secondaryNavigation = array();

    /**
     *
     */
    public function __construct()
    {

        parent::__construct();

        // all page require twig
        $this->load_twig();

        /* add global css */
        array_push($this->css_files, 'core');
        array_push($this->css_files, 'fonts');

        /* add global js */
        array_push($this->js_files, 'core');
        array_push($this->js_files, '../vendor/jquery/jquery.cookie');
        array_push($this->js_files, '../vendor/bootstrap-daterangepicker/js/moment');

        // define default browser title, can be overwritten in any controller
        $this->view_data['apple_touch_icon'] = $this->apple_touch_icon;
        $this->view_data['browser_title'] = $this->browser_title;

        // define basic template stuff
        $this->view_data['layout']        = $this->layout;

        // this needs to be moved to cachebuster method
        //$this->view_data['revision']      = $this->getRevision();
        $this->view_data['CACHE_BUSTER']  = 'xxx'; // substr($this->view_data['revision'], 0, 3);

        $this->user_logic = new UserLogic;
        $this->vehicle_logic = new VehicleLogic;

    }
    
    /**
     * Overloaded method used to deny request (session timed out)
     */
    protected function denyRequest()
    {
        $this->loginAction();
    }

    /**
     *
     * All page views should call this method when ready to present the page
     *
     * @param $template
     * @param $view_data
     */
    protected function render($template, $view_data)
    {

        $uri = explode ( '/' , $this->route_data['route'] ) ;

        switch ($uri[0]) {

            case                      'api' :   echo "BasePage" ;
                                                $result = $this->vehicle_logic->activity('','',$uri[0],'','');
                                                exit();
                                                break;  

            case                     'ajax' :   break;  

            case           'changepassword' :   
            case           'forgotpassword' :
            case           'forgotusername' : 
            case              'fulfillment' :
            case                    'login' :
            case                   'mobile' : 
            case                   'orders' : 
            case              'orderstatus' :
            case                     'repo' :
            case                'warehouse' :   // $result = $this->vehicle_logic->activity('','',$uri[0],'','');
                                                break;

                                    default :   $user_id = $this->user_session->getUserId();
                                                $account_id = $this->user_session->getAccountId();
                                                $view_data['account_id'] = $account_id;
                                                $view_data['user_id'] = $user_id;

                                                $legal = $this->user_logic->getLegalId($user_id);
                                                if($legal['account'][0]['legal_id']!=$legal['legal'][0]['legal_id']){
                                                    $view_data['legal_id'] = $legal['legal'][0]['legal_id'];
                                                    $view_data['ts_and_cs'] = $legal['legal'][0]['legal'];
                                                    $in = array('-',':',' ');
                                                    $out = array('','','');
                                                    $view_data['legal_version'] = str_replace ( $in , $out , $legal['legal'][0]['updated'] ) . '-' . $legal['legal'][0]['legal_id'];
                                                    $view_data['legal_updated'] = $legal['legal'][0]['updated'];
                                                    $view_data['account_name'] = $legal['account'][0]['account_name'];
                                                    $view_data['user_first'] = $legal['account'][0]['user_first'];
                                                    $view_data['user_last'] = $legal['account'][0]['user_last'];
                                                    $view_data['user_name'] = $legal['account'][0]['user_name'];
                                                    $view_data['user_email'] = $legal['account'][0]['user_email'];
                                                    if($legal['account'][0]['user_roles']==''){
                                                        $view_data['user_authority'] = 'Account Owner' ;
                                                        $legal['account'][0]['usertype_id'] = 1;
                                                    } else {
                                                        $view_data['user_authority'] = $legal['account'][0]['usertype'];
                                                    }
                                                    if($legal['account'][0]['usertype_id'] == 1){
                                                        $template='page/account/legal.html.twig';
                                                    } else {
                                                        $template='page/account/legalUnauthorized.html.twig';
                                                    }
                                                }
                                                
                                                $result = $this->vehicle_logic->activity($account_id,$user_id,$uri[0],'','');
                                                
                                                /**
                                                 * Default Permissions Variables
                                                 */
                                                $buffer = null;
                                                $view_data['permissions'] = $this->user_logic->getPermissions();
                                                foreach ( $view_data['permissions'] as $key => $val ) {
                                                    if($val){
                                                        $buffer = 'permissions_' . $val['object'] . '_' . $val['action'];
                                                        if(($buffer != 'permissions__')&&(!($view_data[$buffer]))){
                                                            $view_data[$buffer] = false;
                                                        }
                                                        if($val['action']=='write'){
                                                            $val['action'] = 'read';                                                        
                                                            $buffer = 'permissions_' . $val['object'] . '_' . $val['action'];
                                                            if(($buffer != 'permissions__')&&(!($view_data[$buffer]))){
                                                                $view_data[$buffer] = false;
                                                            }
                                                        }
                                                    }
                                                }
                                                /**
                                                 * Actual Permissions Settings
                                                 */
                                                $buffer = null;
                                                $view_data['permissions'] = $this->user_logic->getPermissions($account_id,$user_id);
                                                foreach ( $view_data['permissions'] as $key => $val ) {
                                                    //
                                                    $buffer[$val['object']][$val['action']] = $val['label'];
                                                    $view_data['permissions'] = $buffer;
                                                    $buffer2 = 'permissions_' . $val['object'] . '_' . $val['action'];
                                                    if(($buffer2 != 'permissions__')&&(!($view_data[$buffer2]))){
                                                        $view_data[$buffer2] = true;
                                                    }
                                                    //
                                                    if($val['action']=='write'){
                                                        $val['action']='read';
                                                        $buffer[$val['object']][$val['action']] = $val['label'];
                                                        $view_data['permissions'] = $buffer;
                                                        $buffer2 = 'permissions_' . $val['object'] . '_' . $val['action'];
                                                        if(($buffer2 != 'permissions__')&&(!($view_data[$buffer2]))){
                                                            $view_data[$buffer2] = true;
                                                        }
                                                    }
                                                    //
                                                }
                                                $this->access = $buffer;
                                                $view_data['permissions'] = $buffer;

        }

        /**
         * Permission Control Over Primary/Secondary Navigation
         */
        $view_data['blank'] = $this->route_data['route'];
        switch ($this->route_data['route']) {

            case             'admin/export' :
            case               'admin/list' :
            case               'admin/repo' :
            case              'admin/users' :
            case          'admin/usertypes' :
            case               'users/list' :
            case               'users/type' :   $this->secondaryNavigation = array();
                                                if($view_data['permissions_device_write']){
                                                    array_push($this->secondaryNavigation, array(
                                                        'label' => 'DEVICES',
                                                        'route' => 'admin/list'
                                                    ));
                                                    array_push($this->secondaryNavigation, array(
                                                        'label' => 'DEVICE IMPORT/EXPORT',
                                                        'route' => 'admin/export'
                                                    ));
                                                } else {
                                                    switch($this->route_data['route']){
                                                        case           'admin/export' :
                                                        case             'admin/list' : $blank=1;
                                                                                        break;
                                                    }
                                                }
                                                if($view_data['permissions_user_write']){
                                                    // array_push($this->secondaryNavigation, array(
                                                    //     'label' => 'USERS',
                                                    //     'route' => 'users/list'
                                                    // ));
                                                    array_push($this->secondaryNavigation, array(
                                                        'label' => 'USERS',
                                                        'route' => 'admin/users'
                                                    ));
                                                } else {
                                                    switch($this->route_data['route']){
                                                        case            'admin/users' :
                                                        case             'users/list' : $blank=1;
                                                                                        break;
                                                    }
                                                }
                                                if($view_data['permissions_usertype_write']){
                                                    // array_push($this->secondaryNavigation, array(
                                                    //     'label' => 'USER TYPES',
                                                    //     'route' => 'users/type'
                                                    // ));
                                                    array_push($this->secondaryNavigation, array(
                                                        'label' => 'USER TYPES',
                                                        'route' => 'admin/usertypes'
                                                    ));
                                                } else {
                                                    switch($this->route_data['route']){
                                                        case        'admin/usertypes' :
                                                        case             'users/type' : $blank=1;
                                                                                        break;
                                                    }
                                                }
                                                if($view_data['permissions_repo_write']){
                                                    array_push($this->secondaryNavigation, array(
                                                        'label' => 'REPOSSESSION',
                                                        'route' => 'admin/repo'
                                                    ));
                                                }
                                                if($blank){
                                                    $this->route_data['method'] = 'blank';
                                                    $template = "page/blank.html.twig";
                                                }

                                                break;

            case            'alert/contact' :
            case            'alert/history' :
            case               'alert/list' :   $this->secondaryNavigation = array();
                                                if($view_data['permissions_alert_read']){
                                                    array_push($this->secondaryNavigation, array(
                                                        'label' => 'HISTORY',
                                                        'route' => 'alert/history'
                                                    ));
                                                    if($view_data['permissions_alert_write']){
                                                        array_push($this->secondaryNavigation, array(
                                                            'label' => 'MANAGEMENT',
                                                            'route' => 'alert/list'
                                                        ));
                                                    } else if ($this->route_data['route']=='alert/list') {
                                                        $blank = 1;
                                                    }
                                                    if($view_data['permissions_alert_write']){
                                                        array_push($this->secondaryNavigation, array(
                                                            'label' => 'CONTACTS',
                                                            'route' => 'alert/contact'
                                                        ));
                                                    } else if ($this->route_data['route']=='alert/contact') {
                                                        $blank = 1;
                                                    }
                                                } else {
                                                    $blank = 1;
                                                }
                                                if($blank){
                                                    $this->route_data['method'] = 'blank';
                                                    $template = "page/blank.html.twig";
                                                }
                                                break;

            case           'landmark/group' :
            case      'landmark/incomplete' :
            case            'landmark/list' :
            case             'landmark/map' :
            case    'landmark/verification' :   $this->secondaryNavigation = array();
                                                if($view_data['permissions_landmark_read']){
                                                    array_push($this->secondaryNavigation, array(
                                                        'label' => 'MAP',
                                                        'route' => 'landmark/map'
                                                    ));
                                                    array_push($this->secondaryNavigation, array(
                                                        'label' => 'LIST',
                                                        'route' => 'landmark/list'
                                                    ));
                                                    array_push($this->secondaryNavigation, array(
                                                        'label' => 'PENDING',
                                                        'route' => 'landmark/incomplete'
                                                    ));
                                                    if($view_data['permissions_landmark_group_write']){
                                                        array_push($this->secondaryNavigation, array(
                                                            'label' => 'GROUPS',
                                                            'route' => 'landmark/group'
                                                        ));
                                                    } else if ($this->route_data['route']=='landmark/group') {
                                                        $this->route_data['method'] = 'blank';
                                                        $template = "page/blank.html.twig";
                                                    }
                                                    if($view_data['permissions_reference_address_read']){
                                                        array_push($this->secondaryNavigation, array(
                                                            'label' => 'VERIFICATION ADDRESSES',
                                                            'route' => 'landmark/verification'
                                                        ));
                                                    } else if ($this->route_data['route']=='landmark/verification') {
                                                        $this->route_data['method'] = 'blank';
                                                        $template = "page/blank.html.twig";
                                                    }
                                                } else {
                                                    $view_data['landmarks'] = null ;
                                                    $this->route_data['method'] = 'blank';
                                                    $template = "page/blank.html.twig";
                                                }
                                                break;

            case           'report/contact' :
            case              'report/list' :
            case         'report/scheduled' :   $this->secondaryNavigation = array();
                                                if($view_data['permissions_report_read']){
                                                    array_push($this->secondaryNavigation, array(
                                                        'label' => 'GENERATE',
                                                        'route' => 'report/list'
                                                    ));
                                                    // array_push($this->secondaryNavigation, array(
                                                    //     'label' => 'Saved',
                                                    //     'route' => 'report/saved'
                                                    // ));
                                                    if($view_data['permissions_report_write']){
                                                        array_push($this->secondaryNavigation, array(
                                                            'label' => 'SCHEDULED',
                                                            'route' => 'report/scheduled'
                                                        ));
                                                    } else if ($this->route_data['route']=='report/scheduled') {
                                                        $blank = 1;
                                                    }
                                                    // array_push($this->secondaryNavigation, array(
                                                    //     'label' => 'HISTORY',
                                                    //     'route' => 'report/history'
                                                    // ));
                                                    if($view_data['permissions_report_write']){
                                                        array_push($this->secondaryNavigation, array(
                                                            'label' => 'CONTACTS',
                                                            'route' => 'report/contact'
                                                        ));
                                                    } else if ($this->route_data['route']=='report/contact') {
                                                        $blank = 1;
                                                    }
                                                } else {
                                                    $blank = 1;
                                                }
                                                if($blank){
                                                    $this->route_data['method'] = 'blank';
                                                    $template = "page/blank.html.twig";
                                                }
                                                break;

            case                  'system/' :
            case           'system/airtime' :
            case           'system/devices' :
            case           'system/library' :
            case            'system/logins' :
            case             'system/sales' :
            case                'system/ux' :   $this->secondaryNavigation = array();
                                                array_push($this->secondaryNavigation, array(
                                                    'label' => 'Activity',
                                                    'route' => 'system/logins'
                                                ));
                                                array_push($this->secondaryNavigation, array(
                                                    'label' => 'Airtime',
                                                    'route' => 'system/airtime'
                                                ));
                                                array_push($this->secondaryNavigation, array(
                                                    'label' => 'Devices',
                                                    'route' => 'system/devices'
                                                ));
                                                array_push($this->secondaryNavigation, array(
                                                    'label' => 'Library',
                                                    'route' => 'system/library'
                                                ));
                                                array_push($this->secondaryNavigation, array(
                                                    'label' => 'Sales',
                                                    'route' => 'system/sales'
                                                ));
                                                array_push($this->secondaryNavigation, array(
                                                    'label' => 'User Experience',
                                                    'route' => 'system/ux'
                                                ));
                                                break;

            case            'vehicle/batch' :
            case       'vehicle/batchqueue' :
            case   'vehicle/commandhistory' :
            case     'vehicle/commandqueue' :
            case            'vehicle/group' :
            case             'vehicle/list' :
            case              'vehicle/map' :   $this->secondaryNavigation = array();
                                                if($view_data['permissions_vehicle_read']){
                                                    array_push($this->secondaryNavigation, array(
                                                        'label' => 'Map',
                                                        'route' => 'vehicle/map'
                                                    ));
                                                    array_push($this->secondaryNavigation, array(
                                                        'label' => 'List',
                                                        'route' => 'vehicle/list'
                                                    ));
                                                    if ($view_data['permissions_vehicle_group_write']) {
                                                        array_push($this->secondaryNavigation, array(
                                                            'label' => 'Groups',
                                                            'route' => 'vehicle/group'
                                                        ));
                                                    } else if ($this->route_data['route']=='vehicle/group') {
                                                        $this->route_data['method'] = 'blank';
                                                        $template = "page/blank.html.twig";
                                                    }
                                                    if (($view_data['permissions_vehicle_location_read'])||($view_data['permissions_vehicle_reminder_write'])||($view_data['permissions_vehicle_starter_write'])) {
                                                        array_push($this->secondaryNavigation, array(
                                                            'label' => 'Command History',
                                                            'route' => 'vehicle/commandhistory'
                                                        ));
                                                        array_push($this->secondaryNavigation, array(
                                                            'label' => 'Pending Commands',
                                                            'route' => 'vehicle/commandqueue'
                                                        ));
                                                    }
                                                } else {
                                                    $view_data['vehicles'] = null ;
                                                    $this->route_data['method'] = 'blank';
                                                    $template = "page/blank.html.twig";
                                                }
                                                break;

        }

        /**
         * The 'theme' can be stored in an account record, which is added to the user session upon login
         */
        if (isset($this->user_session) && NULL !== $this->user_session->getAccountTheme()) {
            array_push($this->css_files, '../theme/'.$this->user_session->getAccountTheme().'/theme');
        } else {
            array_push($this->css_files, '../theme/'.$this->defaultTheme.'/theme');
        }

        $view_data['logopath']='/assets_responsive/img/logo.png';
        $url=$this->user_logic->getURL($_SERVER['HTTP_HOST']);
        if(!empty($url))
        {
            if($url['logo']!='logo.png'){
                $view_data['whitelabel']=$url['logo'];
            }
            $view_data['logo']=$url['logo'];
            $view_data['logopath']='/assets/media/logos/'.$url['logo'];
            $view_data['apple_touch_icon'] = $url['apple_touch_icon'];
            if(!($view_data['apple_touch_icon'])){
                $view_data['apple_touch_icon'] = 'apple-touch-114-whitelabel.png';
            }
            $view_data['browser_title']=$url['title'];
        }
		
        // pass all designated css/js files to the view
        $view_data['css_files'] = $this->css_files;
        $view_data['js_files']  = $this->js_files;

        // pass the logged in user to the view
        $view_data['user'] = $this->user_session;

        // pass the controller & route vars to the view
        // this could be cleaned up as route and context are redundant
        $view_data['controller'] = $this->route_data['controller'];
        $view_data['method'] = $this->route_data['method'];
        $this->route_data['route'] = $view_data['controller'] . '/' . $view_data['method'] ;
        $view_data['route'] = $this->route_data['route'];
        $view_data['context'] = $this->route_data['route'];

        // pass the access data to the view
        $view_data['environment'] = md5(ENVIRONMENT);

        // pass this to javascript
        $view_data['session_timeout'] = $this->session_timeout;

        // pass access data to view
        $view_data['access'] = (NULL !== $this->user_session) ? $this->user_session->getAccess() : array();

        $validation = new Validation;
        // add field types array to view for additional client-side validation
        $view_data['validation'] = $validation->getFieldTypes();

        // set global navigation links all viewable controller display
        if (isset($view_data['permissions'])) {

            if ($this->access['vehicle']['read']) {
                array_push($this->primaryNavigation, array(
                    'label' => 'Vehicles',
                    'route' => 'vehicle/map'
                ));
            }

            if ($this->access['landmark']['read']) {
                array_push($this->primaryNavigation, array(
                    'label' => 'Landmarks',
                    'route' => 'landmark/map'
                ));
            }
            /*
            if ($this->access['boundary_read']) {
                array_push($this->primaryNavigation, array(
                    'label' => 'Boundaries',
                    'route' => 'boundary/map'
                ));
            }
            */
            if ($this->access['alert']['read']) {
                array_push($this->primaryNavigation, array(
                    'label' => 'Alerts',
                    'route' => 'alert/history'
                ));
            }

            if ($this->access['report']['read']) {
                array_push($this->primaryNavigation, array(
                    'label' => 'Reports',
                    'route' => 'report/list'
                ));
            }

            if ( ($this->access['device']['write']) || ($this->access['user']['write']) || ($this->access['usertype']['write']) ) {
                array_push($this->primaryNavigation, array(
                    'label' => 'Admin',
                    'route' => 'admin/list'
                ));
            }

            switch($user_id){

                case '2482' :
                case '2510' : array_push($this->primaryNavigation, array(
                                'label' => 'System',
                                'route' => 'system'
                              ));
                              break;

            }

        } else {

                array_push($this->primaryNavigation, array(
                    'label' => 'account_id:' . $view_data['account_id'] . ':user_id:' . $view_data['user_id'],
                    'route' => 'vehicle/map'
                ));

        }

        $this->view_data['primary_navigation']   = $this->primaryNavigation;

        // set per controller
        $this->view_data['secondary_navigation'] = $this->secondaryNavigation;

        // merge any view_data set in a controller, over-writting may occur in the controller
        $view_data = array_merge($this->view_data, $view_data);

        // echo out the response
        echo $this->twig->render($template, $view_data);

        if(!($user_id)){
            $user_id = 999999999 ;
        }
        define ( 'CYCLE_END', microtime(true) );
        $miliseconds = ( CYCLE_END - CYCLE_START ) ;
        // echo '<hr>' . $miliseconds . '=' . CYCLE_END . '-' . CYCLE_START ;
        $metrics['uri'] = $this->route_data['route'] ;
        $metrics['miliseconds'] = floor ( $miliseconds * 1000 ) ;
        // $metrics['miliseconds'] = $miliseconds ;
        $result = $this->vehicle_logic->codebaseMetrics($_SERVER['HTTP_HOST'],$user_id,'uri',$metrics);

        exit();
    }
}
