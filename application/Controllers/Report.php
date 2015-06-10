<?php

namespace Controllers;

//use Models\Data\VehicleData;
use Models\Logic\VehicleLogic;
//use Models\Data\TerritoryData;
use Models\Logic\TerritoryLogic;
use Models\Logic\ReportLogic;
use Models\Logic\UserLogic;
use Models\Logic\ContactLogic;

use GTC\Component\Utils\CSV\CSVBuilder;
use GTC\Component\Utils\PDF\PDFDataAdapter;
use GTC\Component\Utils\PDF\TCPDFBuilder;

/**
 * Class Report
 *
 */
class Report extends BasePage
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        array_push($this->css_files, 'report');
        array_push($this->js_files, 'report');

        array_push($this->css_files, '../vendor/bootstrap-daterangepicker/css/daterangepicker-bs3');
        /*array_push($this->js_files, '../vendor/bootstrap-daterangepicker/js/moment');*/
        array_push($this->js_files, '../vendor/bootstrap-daterangepicker/js/daterangepicker');

        $this->load_db('master');
        $this->load_db('slave');

        //$this->vehicle_data = new VehicleData;
        $this->vehicle_logic = new VehicleLogic;
        //$this->territory_data = new TerritoryData;
        $this->territory_logic = new TerritoryLogic;
        $this->report_logic = new ReportLogic;
        $this->user_logic = new UserLogic;
        $this->contact_logic = new ContactLogic;

        /** 
         * push secondary navigation based on user permission
         */
        if (true) {
            array_push($this->secondaryNavigation, array(
                'label' => 'GENERATE',
                'route' => 'report/list'
            ));
        }

        /*if (true) {
            array_push($this->secondaryNavigation, array(
                'label' => 'Saved',
                'route' => 'report/saved'
            ));
        }*/

        if (true) {
            array_push($this->secondaryNavigation, array(
                'label' => 'SCHEDULED',
                'route' => 'report/scheduled'
            ));
        }

        if (true==false) {
            array_push($this->secondaryNavigation, array(
                'label' => 'HISTORY',
                'route' => 'report/history'
            ));
        }

        if (true) {
            array_push($this->secondaryNavigation, array(
                'label' => 'CONTACTS',
                'route' => 'report/contact'
            ));
        }
    }

    public function index()
    {
        $this->listview();
    }

    /**
     * @route report_list /report/list
     */
    public function listview()
    {
        $view_data = array();

        $account_id = $this->user_session->getAccountId();
        $user_id = $this->user_session->getUserId();

        $view_data['vehicle_groups']  = $this->vehicle_logic->getVehicleGroupsByAccountId($account_id);
        $view_data['vehicles']        = $this->vehicle_logic->getVehiclesByAccountId($account_id);
        
        $this->territory_logic->setTerritoryType('landmark');
        $view_data['landmark_groups'] = $this->territory_logic->getTerritoryGroupsByAccountId($account_id);
        $view_data['landmarks']       = $this->territory_logic->getTerritoriesByAccountId($account_id);
        $this->territory_logic->resetTerritoryType();

        $this->territory_logic->setTerritoryType('boundary');
        $view_data['boundary_groups'] = $this->territory_logic->getTerritoryGroupsByAccountId($account_id);
        $view_data['boundaries']      = $this->territory_logic->getTerritoriesByAccountId($account_id);
        $this->territory_logic->resetTerritoryType();
        
        $view_data['reporttypes']     = $this->report_logic->getReportTypes();
        $view_data['contact_groups']  = $this->contact_logic->getContactGroupsByAccountId($account_id);
        $view_data['contacts']        = $this->contact_logic->getContactsByAccountId($account_id);
        $view_data['users']           = $this->user_logic->getUserByAccountId($account_id);
        $view_data['alerttypes']      = $this->report_logic->getReportAlertTypes();

        $view_data['mapalladdresses'] = $this->report_logic->mapAllAddresses();

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
        
        $this->render('page/report/list.html.twig', $view_data);
    }

    public function saved()
    {
        $view_data = array();
        $account_id = $this->user_session->getAccountId();
        
        $view_data['account_id'] = $account_id;
        $view_data['user_id'] = $user_id;
        $view_data['permissions'] = $this->access ;

        $this->render('page/report/saved.html.twig', $view_data);
    }

    public function history()
    {
        $view_data = array();
        $account_id = $this->user_session->getAccountId();
        $user_id = $this->user_session->getUserId();

        $view_data['users'] = $this->user_logic->getUserByAccountId($account_id);
        $view_data['reporttypes'] = $this->report_logic->getReportTypes();

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

        $this->render('page/report/history.html.twig', $view_data);
    }



    public function scheduled()
    {
        $view_data = array();
        
        $account_id = $this->user_session->getAccountId();
        $user_id = $this->user_session->getUserId();

        $view_data['users'] = $this->user_logic->getUserByAccountId($account_id);

        $view_data['reporttypes']       = $this->report_logic->getReportTypes();
        
        $view_data['contact_groups']    = $this->contact_logic->getContactGroupsByAccountId($account_id);
        $view_data['contacts']          = $this->contact_logic->getContactsByAccountId($account_id);

        $view_data['vehicle_groups'] = $this->vehicle_logic->getVehicleGroupsByAccountId($account_id);
        $view_data['vehicles'] = $this->vehicle_logic->getVehiclesByAccountId($account_id);
        
        $this->territory_logic->setTerritoryType('landmark');
        $view_data['landmark_groups'] = $this->territory_logic->getTerritoryGroupsByAccountId($account_id);
        $view_data['landmarks'] = $this->territory_logic->getTerritoriesByAccountId($account_id);
        $this->territory_logic->resetTerritoryType();

        $this->territory_logic->setTerritoryType('boundary');
        $view_data['boundary_groups'] = $this->territory_logic->getTerritoryGroupsByAccountId($account_id);
        $view_data['boundaries'] = $this->territory_logic->getTerritoriesByAccountId($account_id);
        $this->territory_logic->resetTerritoryType();

        $view_data['alerttypes'] = $this->report_logic->getReportAlertTypes();

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

        $this->render('page/report/scheduled.html.twig', $view_data);
    }

    public function contact()
    {
        $account_id = $this->user_session->getAccountId();
        $user_id = $this->user_session->getUserId();
        array_push($this->js_files, '../vendor/jquery/jquery-ui-1.10.3.custom.min');
        //array_push($this->js_files, 'contact');
        $view_data = $sms_carriers = array();
        
        $temp_sms_carriers = $this->contact_logic->getSMSCarrierOptions();
        if (! empty($temp_sms_carriers)) {
            foreach ($temp_sms_carriers as $sc) {
                if (! isset($sms_carriers[$sc['cellcarrier_id']])) {
                    $sms_carriers[$sc['cellcarrier_id']] = $sc['cellcarrier'];    
                }
            }
        }
        $view_data['sms_carriers']      = $temp_sms_carriers;

        $view_data['contact_groups']    = $this->contact_logic->getContactGroupsByAccountId($account_id);

        $view_data['users']             = $this->user_logic->getUserByAccountId($account_id);

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

        $this->render('page/contact/contact.html.twig', $view_data);
    }
}