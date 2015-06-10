<?php

namespace Controllers;

use Models\Data\ContactData;
use Models\Logic\ContactLogic;
use GTC\Component\Utils\CSV\CSVBuilder;
use Models\Data\UserData;
use Models\Logic\UserLogic;
use Models\Data\VehicleData;
use Models\Logic\VehicleLogic;

/**
 * Class Users
 *
 * Controller for User Administration Actions
 *
 */
class Users extends BasePage
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        array_push($this->css_files, 'users');
        array_push($this->js_files, 'users');
        array_push($this->js_files, '../vendor/jquery/jquery-ui-1.10.3.custom');


        $this->load_db('master');
        $this->load_db('slave');

        $this->contact_logic = new ContactLogic;
        $this->user_logic = new UserLogic;
        $this->user_data = new UserData;
        $this->vehicle_data     = new VehicleData;
        $this->vehicle_logic    = new VehicleLogic;

        //
        // BELOW NAVIGATION SETTINGS NEED TO BE IDENTICAL TO THOSE IN Controllers/Device.php
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
        // ABOVE NAVIGATION SETTINGS NEED TO BE IDENTICAL TO THOSE IN Controllers/Device.php
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
     * User Management main
     *
     * @route users_list /users/list
     */
    public function listview()
    {
        $view_data  = array();

        $account_id = $this->user_session->getAccountId();
        $user_id = $this->user_session->getUserId();


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



        $view_data['vehicle_groups'] = $this->vehicle_logic->getVehicleGroupsByAccountId($account_id);
        $view_data['usertypes'] = $this->user_logic->getUserTypesByAccountId($account_id);

        $view_data['carriers'] = $this->contact_logic->getSMSCarrierOptions();

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

        $this->render("page/user/list.html.twig", $view_data);
    }

    /**
     * User Type Management
     */
    public function type()
    {
        $view_data = array();

        $user_id = $this->user_session->getUserId();
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




        // get all permission categories including permissions per category used for usertype permission association display
        $view_data['permissioncategory'] = $this->user_logic->getPermissionCategory(true);

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

        $this->render("page/user/type-list.html.twig", $view_data);
    }

}