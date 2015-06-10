<?php

namespace Controllers;

use GTC\Component\Utils\Date;
use GTC\Component\Utils\Dropdown;
use GTC\Component\Utils\CSV\CSVBuilder;

use Models\Logic\AddressLogic;

use Models\Data\UserData;
use Models\Logic\UserLogic;

use Models\Data\VehicleData;
use Models\Logic\VehicleLogic;

use Symfony\Component\HttpFoundation\Request;



/**
 * Class Device
 *
 */
class Device extends BasePage
{    
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        array_push($this->css_files, 'device');
        array_push($this->js_files, 'device');
        array_push($this->js_files, '../vendor/jquery/jquery-ui-1.10.3.custom');

        // start database
        $this->load_db('master');
        $this->load_db('slave');

        $this->user_logic       = new UserLogic;
        $this->user_data        = new UserData;
        $this->vehicle_data     = new VehicleData;
        $this->vehicle_logic    = new VehicleLogic;
        $this->address_logic    = new AddressLogic;

        //
        // BELOW NAVIGATION SETTINGS NEED TO BE IDENTICAL TO THOSE IN Controllers/Users.php
        //
        if ($this->access['device']['write']) {
            array_push($this->secondaryNavigation, array(
                'label' => 'DEVICES',
                'route' => 'admin/list'
            ));
            array_push($this->secondaryNavigation, array(
                'label' => 'DEVICE IMPORT/EXPORT',
                'route' => 'admin/export'
            ));
        }
        if ($this->access['user']['write']) {
            // array_push($this->secondaryNavigation, array(
            //     'label' => 'USERS',
            //     'route' => 'users/list'
            // ));
            array_push($this->secondaryNavigation, array(
                'label' => 'USERS',
                'route' => 'admin/users'
            ));
        }
        if ($this->access['usertype']['write']) {
            // array_push($this->secondaryNavigation, array(
            //     'label' => 'USER TYPES',
            //     'route' => 'users/type'
            // ));
            array_push($this->secondaryNavigation, array(
                'label' => 'USER TYPES',
                'route' => 'admin/usertypes'
            ));
        }
        if ($this->access['repo']['write']) {
            array_push($this->secondaryNavigation, array(
                'label' => 'REPO LINKS',
                'route' => 'admin/repo'
            ));
        }
        //
        // ABOVE NAVIGATION SETTINGS NEED TO BE IDENTICAL TO THOSE IN Controllers/Users.php
        //

    }
     
    /**
     *
     */
    public function index()
    {
        $this->listview();
    }

    /**
     * @route device_admin /device/admin
     */
    public function admin()
    {
        $user_id    = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();
        
        $view_data  = array();

        $view_data['vehicle_groups'] = $this->vehicle_logic->getVehicleGroupsByAccountId($account_id);
        $view_data['vehicle_status'] = $this->vehicle_logic->getVehicleStatusOptions();
        $view_data['device_status']  = $this->vehicle_logic->getDeviceStatus();

        // MAP API
        $view_data['map_api'] = $this->map_api;
        $view_data['decarta_api_key'] = $this->decarta_api_key;
        
        array_push($this->css_files, 'map');
        array_push($this->js_files, 'map');
        
        switch ($this->map_api) {
            case 'google':
                // GOOGLE MAPS API LINK 
                $view_data['map_api_link'] = 'https://maps.googleapis.com/maps/api/js?&client=gme-globaltrackingcommunications&sensor=false';
                array_push($this->js_files, 'gmap');
                array_push($this->js_files, 'infobox_packed');
                break;
        
            case 'mapbox':
                // MAPBOX API LINK
                $view_data['map_api_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.js';
                $view_data['map_css_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.css';
                array_push($this->js_files, 'mapbox');
                break;
        }

        $this->render("page/device/admin.html.twig", $view_data);
    }

    /**
     * @route device_list /device/list
     */
    public function listview()
    {
        $user_id    = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();


        //
        // *** REFRESH $this->access with real time data
        //
        $buffer = null;
        $view_data['permissions'] = $this->user_logic->getPermissions($account_id,$user_id);
        foreach ( $view_data['permissions'] as $key => $val ) {
            $buffer[$val['object']][$val['action']] = $val['label'];
        }
        $this->access = $buffer;
        //
        // *** REFRESH $this->access with real time data
        //



        
        $view_data  = array();

        $view_data['vehicle_groups'] = $this->vehicle_logic->getVehicleGroupsByAccountId($account_id);
        $view_data['vehicle_status'] = $this->vehicle_logic->getVehicleStatusOptions();
        $view_data['device_status']  = $this->vehicle_logic->getDeviceStatus();

        // MAP API
        $view_data['map_api'] = $this->map_api;
        $view_data['decarta_api_key'] = $this->decarta_api_key;
        
        array_push($this->css_files, 'map');
        array_push($this->js_files, 'map');
        
        switch ($this->map_api) {
            case 'google':
                // GOOGLE MAPS API LINK 
                $view_data['map_api_link'] = 'https://maps.googleapis.com/maps/api/js?&client=gme-globaltrackingcommunications&sensor=false';
                array_push($this->js_files, 'gmap');
                array_push($this->js_files, 'infobox_packed');
                break;
        
            case 'mapbox':
                // MAPBOX API LINK
                $view_data['map_api_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.js';
                $view_data['map_css_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.css';
                array_push($this->js_files, 'mapbox');
                break;
        }

        $this->render("page/device/list.html.twig", $view_data);
    }

    /**
     * @route device_export /device/export
     */
    public function export()
    {
        $user_id    = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();


        //
        // *** REFRESH $this->access with real time data
        //
        $buffer = null;
        $view_data['permissions'] = $this->user_logic->getPermissions($account_id,$user_id);
        foreach ( $view_data['permissions'] as $key => $val ) {
            $buffer[$val['object']][$val['action']] = $val['label'];
        }
        $this->access = $buffer;
        //
        // *** REFRESH $this->access with real time data
        //




        $createdate = $this->user_logic->getCreateDate($user_id);
        
        $view_data  = array();

        $view_data['import_export_key'] = date("U",strtotime($createdate['createdate_account'])) . '-' . $account_id . '-' . date("U",strtotime($createdate['createdate_user'])) . '-' . $user_id;

        $view_data['vehicle_groups'] = $this->vehicle_logic->getVehicleGroupsByAccountId($account_id);
        $view_data['vehicle_status'] = $this->vehicle_logic->getVehicleStatusOptions();
        $view_data['device_status']  = $this->vehicle_logic->getDeviceStatus();

        // MAP API
        $view_data['map_api'] = $this->map_api;
        $view_data['decarta_api_key'] = $this->decarta_api_key;
        
        array_push($this->css_files, 'map');
        array_push($this->js_files, 'map');
        
        switch ($this->map_api) {
            case 'google':
                // GOOGLE MAPS API LINK 
                $view_data['map_api_link'] = 'https://maps.googleapis.com/maps/api/js?&client=gme-globaltrackingcommunications&sensor=false';
                array_push($this->js_files, 'gmap');
                array_push($this->js_files, 'infobox_packed');
                break;
        
            case 'mapbox':
                // MAPBOX API LINK
                $view_data['map_api_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.js';
                $view_data['map_css_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.css';
                array_push($this->js_files, 'mapbox');
                break;
        }

        $this->render("page/device/export.html.twig", $view_data);
    }

    /**
     * @route device_list /admin/repo
     */
    public function repo()
    {
        $user_id    = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();


        //
        // *** REFRESH $this->access with real time data
        //
        $buffer = null;
        $view_data['permissions'] = $this->user_logic->getPermissions($account_id,$user_id);
        foreach ( $view_data['permissions'] as $key => $val ) {
            $buffer[$val['object']][$val['action']] = $val['label'];
        }
        $this->access = $buffer;
        //
        // *** REFRESH $this->access with real time data
        //



        
        $view_data  = array();

        // $view_data['vehicle_groups'] = $this->vehicle_logic->getVehicleGroupsByAccountId($account_id);
        // $view_data['vehicle_status'] = $this->vehicle_logic->getVehicleStatusOptions();
        // $view_data['device_status']  = $this->vehicle_logic->getDeviceStatus();

        // MAP API
        $view_data['map_api'] = $this->map_api;
        $view_data['decarta_api_key'] = $this->decarta_api_key;
        
        array_push($this->css_files, 'map');
        array_push($this->js_files, 'map');
        
        switch ($this->map_api) {
            case 'google':
                // GOOGLE MAPS API LINK 
                $view_data['map_api_link'] = 'https://maps.googleapis.com/maps/api/js?&client=gme-globaltrackingcommunications&sensor=false';
                array_push($this->js_files, 'gmap');
                array_push($this->js_files, 'infobox_packed');
                break;
        
            case 'mapbox':
                // MAPBOX API LINK
                $view_data['map_api_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.js';
                $view_data['map_css_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.css';
                array_push($this->js_files, 'mapbox');
                break;
        }

        $this->render("page/device/repo.html.twig", $view_data);
    }

    /**
     * @route device_list /repo/{X}
     */
    public function repolink($repoKey)
    {

        /**
         * if _username is submitted, this is a login attempt
         */
        $post['_username'] = 'anonymous' ;
        $post['_password'] = 'p0s1t10nplus' ;

        $login_attempt = $this->validateLogin($post);

        if(true === $login_attempt) {
            // $this->landingAction($post);
        } else {
            // get errors
            $error = $login_attempt;
        }

        // useful if we need to disable login form
        $login_restriction = null;

        // check if user is already logged in
        $token = $this->session->get('_security_' . 'secured_area');

        if (null !== $token) {
            // show login template, but display 'already logged in' message
            $login_restriction = 'already_logged_in';
        }
       
        $view_data  = array();

        // MAP API
        $view_data['map_api'] = $this->map_api;
        $view_data['decarta_api_key'] = $this->decarta_api_key;
        
        array_push($this->css_files, 'map');
        array_push($this->js_files, 'map');
        
        switch ($this->map_api) {
            case 'google':
                // GOOGLE MAPS API LINK 
                $view_data['map_api_link'] = 'https://maps.googleapis.com/maps/api/js?&client=gme-globaltrackingcommunications&sensor=false';
                array_push($this->js_files, 'gmap');
                array_push($this->js_files, 'infobox_packed');
                break;
        
            case 'mapbox':
                // MAPBOX API LINK
                $view_data['map_api_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.js';
                $view_data['map_css_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.css';
                array_push($this->js_files, 'mapbox');
                break;
        }

        $this->render("page/device/repolink.html.twig", $view_data);

    }

    /**
     * @route device_list /system
     */
    public function systemMetrics()
    {
        $user_id    = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();


        $view_data  = array();

                                                                    $today = date('M d, Y', strtotime('-8 hours'));
        $view_data['todays_date']                                 = $today ; //date('M d, Y',strtotime('-8 hours',date('Y-m-d 00:00:00')));

        $view_data['devices']                                     = array();
        $view_data['devices']['inventory']['count']               = "0";
        $view_data['devices']['inventory']['percentage']          = "0";
        $view_data['devices']['installed']['count']               = "0";
        $view_data['devices']['installed']['percentage']          = "0";
        $view_data['devices']['no_report']['count']               = "0";
        $view_data['devices']['no_report']['percentage']          = "0";
        $view_data['devices']['reminderstatus_um']['count']       = "0";
        $view_data['devices']['reminderstatus_um']['percentage']  = "0";
        $view_data['devices']['repossession']['count']            = "0";
        $view_data['devices']['repossession']['percentage']       = "0";
        $view_data['devices']['reporting']['count']               = "0";
        $view_data['devices']['reporting']['percentage']          = "0";
        $view_data['devices']['starterstatus']['count']           = "0";
        $view_data['devices']['starterstatus']['percentage']      = "0";
        $view_data['devices']['starterstatus_um']['count']        = "0";
        $view_data['devices']['starterstatus_um']['percentage']   = "0";
        $view_data['devices_total']                               = "0";

        switch($user_id){

            case '2482' :
            case '2510' : $view_data['devices'] = $this->vehicle_logic->systemDevices();
                          $view_data['devices_total'] = number_format ( $view_data['devices']['devices_total'] , 0 , '.' , ',' ) ;
                          //
                          $view_data['nons'] = $this->vehicle_logic->systemNons();
                          $view_data['nons_total'] = $view_data['nons'][0]['total'] ;
                          $view_data['nons_total_db_records'] = $view_data['nons'][0]['total_db_records'] ;
                          //
                          break;

        }
        
        // MAP API
        $view_data['map_api'] = $this->map_api;
        $view_data['decarta_api_key'] = $this->decarta_api_key;
        
        array_push($this->css_files, 'map');
        array_push($this->js_files, 'map');
        
        switch ($this->map_api) {
            case 'google':
                // GOOGLE MAPS API LINK 
                $view_data['map_api_link'] = 'https://maps.googleapis.com/maps/api/js?&client=gme-globaltrackingcommunications&sensor=false';
                array_push($this->js_files, 'gmap');
                array_push($this->js_files, 'infobox_packed');
                break;
        
            case 'mapbox':
                // MAPBOX API LINK
                $view_data['map_api_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.js';
                $view_data['map_css_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.css';
                array_push($this->js_files, 'mapbox');
                break;
        }

        $this->render("page/system/metrics.html.twig", $view_data);
    }


    /**
     * @route device_list /system
     */
    public function systemAirtime()
    {
        $user_id    = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();


        $view_data  = array();

                                                                    $today = date('M d, Y', strtotime('-8 hours'));
        $view_data['todays_date']                                 = $today ; //date('M d, Y',strtotime('-8 hours',date('Y-m-d 00:00:00')));

        $view_data['acts']                                        = array();
        $view_data['acts_total']                                  = "0";
        $view_data['airs']                                        = array();
        $view_data['airs_total']                                  = "0";
        $view_data['devices_total']                               = "0";
                
        switch($user_id){

            case '2482' :
            case '2510' : $buffer = $this->vehicle_logic->systemActivations();
                          $view_data['acts'] = $buffer['acts'];
                          $view_data['acts_total'] = number_format ( $buffer['acts_total'] , 0 , '.' , ',' ) ;
                          $buffer = $this->vehicle_logic->systemAirs();
                          $view_data['airs'] = $buffer['airs'];
                          $view_data['airs_total'] = number_format ( $buffer['airs_total'] , 0 , '.' , ',' ) ;
                          //
                          break;

        }
        
        // MAP API
        $view_data['map_api'] = $this->map_api;
        $view_data['decarta_api_key'] = $this->decarta_api_key;
        
        array_push($this->css_files, 'map');
        array_push($this->js_files, 'map');
        
        switch ($this->map_api) {
            case 'google':
                // GOOGLE MAPS API LINK 
                $view_data['map_api_link'] = 'https://maps.googleapis.com/maps/api/js?&client=gme-globaltrackingcommunications&sensor=false';
                array_push($this->js_files, 'gmap');
                array_push($this->js_files, 'infobox_packed');
                break;
        
            case 'mapbox':
                // MAPBOX API LINK
                $view_data['map_api_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.js';
                $view_data['map_css_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.css';
                array_push($this->js_files, 'mapbox');
                break;
        }

        $this->render("page/system/metrics.airtime.html.twig", $view_data);
    }


    /**
     * @route device_list /system
     */
    public function systemLibrary()
    {
        $user_id    = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();

        $view_data  = array();

        // MAP API
        $view_data['map_api'] = $this->map_api;
        $view_data['decarta_api_key'] = $this->decarta_api_key;
        
        array_push($this->css_files, 'map');
        array_push($this->js_files, 'map');
        
        switch ($this->map_api) {
            case 'google':
                // GOOGLE MAPS API LINK 
                $view_data['map_api_link'] = 'https://maps.googleapis.com/maps/api/js?&client=gme-globaltrackingcommunications&sensor=false';
                array_push($this->js_files, 'gmap');
                array_push($this->js_files, 'infobox_packed');
                break;
        
            case 'mapbox':
                // MAPBOX API LINK
                $view_data['map_api_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.js';
                $view_data['map_css_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.css';
                array_push($this->js_files, 'mapbox');
                break;
        }

        $this->render("page/system/library.html.twig", $view_data);
    }


    /**
     * @route device_list /system
     */
    public function systemLogins()
    {
        $user_id    = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();


        $view_data  = array();

                                                                    $today = date('M d, Y', strtotime('-8 hours'));
        $view_data['todays_date']                                 = $today ; //date('M d, Y',strtotime('-8 hours',date('Y-m-d 00:00:00')));

        $view_data['devices_total']                               = "0";
        $view_data['logins']                                      = array();
        $view_data['logins_total']                                = "0";

        switch($user_id){

            case '2482' :
            case '2510' : $view_data['logins'] = $this->vehicle_logic->systemLogins();
                          //
                          foreach ($view_data['logins'] as $key => $val) {
                            $view_data['logins_total'] = $view_data['logins_total'] + $val['counter'] ;
                          }
                          //
                          foreach ($view_data['logins'] as $key => $val) {
                            $count = $val['counter'] + 0 ;
                            $view_data['logins'][$key]['percentage'] = floor ( $count / $view_data['logins_total'] * 100 ) ;
                          }
                          $view_data['logins_total'] = number_format ( $view_data['logins_total'] , 0 , '.' , ',' ) ;
                          //
                          break;

        }
        
        // MAP API
        $view_data['map_api'] = $this->map_api;
        $view_data['decarta_api_key'] = $this->decarta_api_key;
        
        array_push($this->css_files, 'map');
        array_push($this->js_files, 'map');
        
        switch ($this->map_api) {
            case 'google':
                // GOOGLE MAPS API LINK 
                $view_data['map_api_link'] = 'https://maps.googleapis.com/maps/api/js?&client=gme-globaltrackingcommunications&sensor=false';
                array_push($this->js_files, 'gmap');
                array_push($this->js_files, 'infobox_packed');
                break;
        
            case 'mapbox':
                // MAPBOX API LINK
                $view_data['map_api_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.js';
                $view_data['map_css_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.css';
                array_push($this->js_files, 'mapbox');
                break;
        }

        $this->render("page/system/metrics.logins.html.twig", $view_data);
    }


    /**
     * @route device_list /system
     */
    public function systemSales()
    {
        $user_id    = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();

        $view_data  = array();

        // MAP API
        $view_data['map_api'] = $this->map_api;
        $view_data['decarta_api_key'] = $this->decarta_api_key;
        
        array_push($this->css_files, 'map');
        array_push($this->js_files, 'map');
        
        switch ($this->map_api) {
            case 'google':
                // GOOGLE MAPS API LINK 
                $view_data['map_api_link'] = 'https://maps.googleapis.com/maps/api/js?&client=gme-globaltrackingcommunications&sensor=false';
                array_push($this->js_files, 'gmap');
                array_push($this->js_files, 'infobox_packed');
                break;
        
            case 'mapbox':
                // MAPBOX API LINK
                $view_data['map_api_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.js';
                $view_data['map_css_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.css';
                array_push($this->js_files, 'mapbox');
                break;
        }

        $this->render("page/system/sales.html.twig", $view_data);
    }


    /**
     * @route device_list /system
     */
    public function systemUx()
    {
        $user_id    = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();


        $view_data  = array();

                                                                    $today = date('M d, Y', strtotime('-8 hours'));
        $view_data['todays_date']                                 = $today ; //date('M d, Y',strtotime('-8 hours',date('Y-m-d 00:00:00')));

        $view_data['uris']                                        = array();
        $view_data['uris_total']                                  = "0";
        $view_data['uxs']                                         = array();
        $view_data['uxs_total']                                   = "0";

        switch($user_id){

            case '2482' :
            case '2510' : $view_data['uxs'] = $this->vehicle_logic->systemUxs();
                          foreach ($view_data['uxs'] as $key => $val) {
                            $view_data['uxs_total'] = $view_data['uxs_total'] + $val['counter'] ;
                          }
                          foreach ($view_data['uxs'] as $key => $val) {
                            $count = $val['counter'] + 0 ;
                            $view_data['uxs'][$key]['percentage'] = floor ( $count / $view_data['uxs_total'] * 100 ) ;
                            $buffer = str_replace ( ' ' , '_' , $val['browser'] ) ;
                            $i = array('(',')',';','_ ',' _');
                            $o = array(' ',' ',' ',' ',' ');
                            $view_data['uxs'][$key]['browser'] = str_replace ( $i , $o , $buffer ) ;
                          }
                          $view_data['uxs_total'] = number_format ( $view_data['uxs_total'] , 0 , '.' , ',' ) ;
                          //
                          $view_data['uris'] = $this->vehicle_logic->systemUris();
                          $totalSeconds = 0 ;
                          foreach ($view_data['uris'] as $key => $val) {
                            $totalSeconds = $totalSeconds + $view_data['seconds'] ;
                            $view_data['uris_total'] = $view_data['uris_total'] + $val['counter'] ;
                          }
                          foreach ($view_data['uris'] as $key => $val) {
                            $count = $val['counter'] + 0 ;
                            $view_data['uris'][$key]['percentage'] = floor ( $count / $view_data['uris_total'] * 100 ) ;
                            $buffer = str_replace ( ' ' , '_' , $val['browser'] ) ;
                            $i = array('(',')',';','_ ',' _');
                            $o = array(' ',' ',' ',' ',' ');
                            $view_data['uris'][$key]['browser'] = str_replace ( $i , $o , $buffer ) ;
                          }
                          $view_data['uris_total'] = number_format ( $view_data['uris_total'] , 0 , '.' , ',' ) ;
                          //
                          break;

        }
        
        // MAP API
        $view_data['map_api'] = $this->map_api;
        $view_data['decarta_api_key'] = $this->decarta_api_key;
        
        array_push($this->css_files, 'map');
        array_push($this->js_files, 'map');
        
        switch ($this->map_api) {
            case 'google':
                // GOOGLE MAPS API LINK 
                $view_data['map_api_link'] = 'https://maps.googleapis.com/maps/api/js?&client=gme-globaltrackingcommunications&sensor=false';
                array_push($this->js_files, 'gmap');
                array_push($this->js_files, 'infobox_packed');
                break;
        
            case 'mapbox':
                // MAPBOX API LINK
                $view_data['map_api_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.js';
                $view_data['map_css_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.css';
                array_push($this->js_files, 'mapbox');
                break;
        }

        $this->render("page/system/metrics.ux.html.twig", $view_data);
    }

}
