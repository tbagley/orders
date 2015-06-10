<?php

namespace Controllers;

use GTC\Component\Utils\Date;
use GTC\Component\Utils\Dropdown;
use GTC\Component\Utils\CSV\CSVBuilder;
use GTC\Component\Map\Tiger;
use GTC\Component\Utils\VinDecoder;

use Models\Logic\AddressLogic;

use Models\Data\VehicleData;
use Models\Logic\VehicleLogic;

use Models\Data\TerritoryData;
use Models\Logic\TerritoryLogic;

use Models\Logic\UserData;
use Models\Logic\UserLogic;

use Models\Logic\UnitCommandLogic;

use Symfony\Component\HttpFoundation\Request;

use GTC\Component\Utils\PDF\TCPDFBuilder;



/**
 * Class Vehicle
 *
 */
class Vehicle extends BasePage
{    
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        array_push($this->css_files, 'vehicle');
        array_push($this->css_files, 'map');
        
        if($this->route_data['method']=='demo')
        {
	        array_push($this->js_files, 'demo');
        }
        else
        {
        	array_push($this->js_files, 'vehicle');
        }
        
        switch ($this->map_api) {
            case 'google':
                // GOOGLE MAP CLASS
                array_push($this->js_files, 'gmap');
                break;
            case 'mapbox':           
                // MAPBOX CLASS
                array_push($this->js_files, 'mapbox');
                break;
        }
        
        array_push($this->js_files, 'map');

        // start database
        $this->load_db('master');
        $this->load_db('slave');

        $this->vehicle_data = new VehicleData;
        $this->vehicle_logic = new VehicleLogic;
        $this->address_logic = new AddressLogic;
        $this->territory_data = new TerritoryLogic;
        $this->territory_logic = new TerritoryLogic;
        // $this->user_data = new UserData;
        $this->user_logic = new UserLogic;
        $this->unitcommand_logic = new UnitCommandLogic;

        if ($this->access['vehicle']['read']) {

            array_push($this->secondaryNavigation, array(
                'label' => 'Map',
                'route' => 'vehicle/map'
            ));

            array_push($this->secondaryNavigation, array(
                'label' => 'List',
                'route' => 'vehicle/list'
            ));
        }

        if ($this->access['vehicle_group']['write']) {
            array_push($this->secondaryNavigation, array(
                'label' => 'Groups',
                'route' => 'vehicle/group'
            ));
        }

        if (($this->access['vehicle_location']['read'])||($this->access['vehicle_reminder']['write'])||($this->access['vehicle_starter']['write'])) {
            array_push($this->secondaryNavigation, array(
                'label' => 'Command History',
                'route' => 'vehicle/commandhistory'
            ));
            array_push($this->secondaryNavigation, array(
                'label' => 'Pending Commands',
                'route' => 'vehicle/commandqueue'
            ));
        }
    }

    /**
     *
     */
    public function index()
    {
        $this->map();
    }

    /**
     * @route default
     */
    public function map()
    {

        // this fix makes sure the context is set properly for the default route
        if ($this->route_data['controller'] == '') {
            $this->route_data['controller'] = 'vehicle';
        }
        if ($this->route_data['method'] == '') {
            $this->route_data['method'] = 'map';
        }

        $default_paths = array('/', '/vehicle');

        if (in_array($this->route_data['route'], $default_paths)) {
            $this->route_data['route'] = 'vehicle/map';
        }
        // end fix

        $view_data = array();
        $vehicles = array();            //    array of vehicle ids
        $vehicle_groups = array();      //    array of vehicle group ids

        $user_id = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();

        $view_data['options_radius'] = $this->vehicle_data->ajaxOptions($user,'','radius');
        $view_data['sidebartoggle']  = $this->vehicle_logic->gotIt($user_id,'sidebartoggle');

        $view_data['vehicle_status'] = $this->vehicle_logic->getVehicleStatusOptions();
        $view_data['vehicle_groups'] = $this->vehicle_logic->getVehicleGroupsByUserId($user_id, null, false, $account_id);

        // display default vehicle list amount for map page on load
        $vehicles = $this->vehicle_data->getVehiclesByGroupIds($user_id, $vehicle_groups, $account_id);
        
        $vehiclelastevents = array();
        
        $view_data['vehicle_count_start'] = 0;
        $view_data['total_vehicle_count']   = count($vehicles);
        if ($view_data['total_vehicle_count'] > 0) {
            $view_data['vehicle_count_start'] = 1;
        }
        // $view_data['vehicles'] = array_splice($vehicles, 0, 20) ;
        $view_data['vehicles'] = $vehicles ;
        $view_data['forward_hidden']      = '';
        if ($view_data['total_vehicle_count'] <= 20) {
            $view_data['forward_hidden'] = "hidden";
        }

        $params['vehicle_state_status'] = 'get_count';

        // get preliminary map quick vehicle counters
        $view_data['quick_filters'] = array();
        if (! empty($vehiclelastevents)) {
            $view_data['quick_filters'] = $this->vehicle_logic->processQuickFilterCount($vehiclelastevents);
        }

        // MAP API
        $view_data['map_api'] = $this->map_api;
        $view_data['decarta_api_key'] = $this->decarta_api_key;

        switch ($this->map_api) {
            case 'google':
                // GOOGLE MAPS API LINK 
                $view_data['map_api_link'] = 'https://maps.googleapis.com/maps/api/js?&client=gme-globaltrackingcommunications&sensor=false';
                array_push($this->js_files, 'infobox_packed');
                break;
        
            case 'mapbox':
                // MAPBOX API LINK
                $view_data['map_api_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.js';
                $view_data['map_css_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.css';
                break;
        }       

        $this->render("page/vehicle/map.html.twig", $view_data);
    }

    public function batch()
    {
        array_push($this->js_files, '../vendor/jquery/jquery-ui-1.10.3.custom.min');

        $view_data = array();

        $user_id                = $this->user_session->getUserId();

        $account_id             = $this->user_session->getAccountId();
        
        $vehicle_groups         = $this->vehicle_logic->getVehicleGroupsByUserId($user_id);

        $vehicles               = $this->vehicle_data->getVehiclesByGroupIds($user_id, $vehicle_groups, $account_id);
        $view_data['vehicles']  = $vehicles ;

        $view_data['batchcommands'] = $this->vehicle_logic->getBatchCommands();

        $this->render("page/vehicle/batch.html.twig", $view_data);
    }

    public function batchqueue()
    {
        array_push($this->js_files, '../vendor/jquery/jquery-ui-1.10.3.custom.min');

        $view_data = array();

        $user_id                = $this->user_session->getUserId();

        $account_id             = $this->user_session->getAccountId();
        
        $vehicle_groups         = $this->vehicle_logic->getVehicleGroupsByUserId($user_id);

        $vehicles               = $this->vehicle_data->getVehiclesByGroupIds($user_id, $vehicle_groups, $account_id);
        $view_data['vehicles']  = $vehicles ;

        $view_data['batchcommands'] = $this->vehicle_logic->getBatchCommands();

        $this->render("page/vehicle/batchqueue.html.twig", $view_data);
    }

    /**
     * @route vehicle_demo /vehicle/demo
     */
    public function demo()
    {

        // this fix makes sure the context is set properly for the default route
        if ($this->route_data['controller'] == '') {
            $this->route_data['controller'] = 'vehicle';
        }
        if ($this->route_data['method'] == '') {
            $this->route_data['method'] = 'map';
        }

        $default_paths = array('/', '/vehicle');

        if (in_array($this->route_data['route'], $default_paths)) {
            $this->route_data['route'] = 'vehicle/demo';
        }
        // end fix

        $view_data = array();

        $vehicle_groups = array();    //    array of vehicle group ids
        $user_id = $this->user_session->getUserId();
        $view_data['vehicle_groups'] = $this->vehicle_logic->getVehicleGroupsByUserId($user_id);

        // display default vehicle list amount for map page on load
        $vehicles = $this->vehicle_data->getVehiclesByGroupIds($user_id, $vehicle_groups);
        
        $vehiclelastevents = array();
        
        $view_data['vehicle_count_start'] = 0;
        $view_data['total_vehicle_count']   = count($vehicles);
        if ($view_data['total_vehicle_count'] > 0) {
            $view_data['vehicle_count_start'] = 1;
        }
        $view_data['vehicles'] = array_splice($vehicles, 0, 20) ;
        $view_data['forward_hidden']      = '';
        if ($view_data['total_vehicle_count'] <= 20) {
            $view_data['forward_hidden'] = "hidden";
        }

        $params['vehicle_state_status'] = 'get_count';

        // get preliminary map quick vehicle counters
        $view_data['quick_filters'] = array();
        if (! empty($vehiclelastevents)) {
            $view_data['quick_filters'] = $this->vehicle_logic->processQuickFilterCount($vehiclelastevents);
        }

        // MAP API
        $view_data['map_api'] = $this->map_api;
        $view_data['decarta_api_key'] = $this->decarta_api_key;

        switch ($this->map_api) {
            case 'google':
                // GOOGLE MAPS API LINK 
                $view_data['map_api_link'] = 'https://maps.googleapis.com/maps/api/js?&client=gme-globaltrackingcommunications&sensor=false';
                array_push($this->js_files, 'infobox_packed');
                break;
        
            case 'mapbox':
                // MAPBOX API LINK
                $view_data['map_api_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.js';
                $view_data['map_css_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.css';
                break;
        }       

        $this->render("page/vehicle/demo.html.twig", $view_data);

    }

    /**
     * @route vehicle_list /vehicle/list
     */
    public function listview()
    {

        $user_id = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();

        $view_data                      = array();
        $view_data['options_radius']    = $this->vehicle_data->ajaxOptions($user,'','radius');
        $view_data['vehicle_status']    = $this->vehicle_logic->getVehicleStatusOptions();
        // $view_data['vehicle_groups']    = $this->vehicle_logic->getVehicleGroupsByUserId($user_id);
        $view_data['vehicle_groups'] = $this->vehicle_logic->getVehicleGroupsByUserId($user_id, null, false, $account_id);

        // MAP API
        $view_data['map_api'] = $this->map_api;
        $view_data['decarta_api_key'] = $this->decarta_api_key;

        switch ($this->map_api) {
            case 'google':
                // GOOGLE MAPS API LINK 
                $view_data['map_api_link'] = 'https://maps.googleapis.com/maps/api/js?&client=gme-globaltrackingcommunications&sensor=false';
                array_push($this->js_files, 'infobox_packed');
                break;
        
            case 'mapbox':
                // MAPBOX API LINK
                $view_data['map_api_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.js';
                $view_data['map_css_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.css';
                break;
        } 

        $this->render("page/vehicle/list.html.twig", $view_data);
    }

    public function group()
    {
        array_push($this->js_files, '../vendor/jquery/jquery-ui-1.10.3.custom.min');
        $view_data = array();

        $user_id                        = $this->user_session->getUserId();
        $account_id                     = $this->user_session->getAccountId();
        // $view_data['vehicle_groups']    = $this->vehicle_logic->getVehicleGroupsByUserId($user_id);
        $view_data['vehicle_groups']    = $this->vehicle_logic->getVehicleGroupsByAccountId($account_id); // needs to be fixed... this shows all groups, not just available to user (assumes account owner)
        $view_data['usertypes']         = $this->user_logic->getUserTypesByAccountId($account_id);
        $view_data['users']             = $this->user_logic->getUsersByAccountId($account_id);
        
        $this->render("page/vehicle/groups-list.html.twig", $view_data);
    }

   /**
    * @route vehicle_print /vehicle/print
    */
   public function printview($unit_id)
   {
        $user_id                        = $this->user_session->getUserId();
        $view_data                      = array();
        $view_data['vehicle_groups']    = $this->vehicle_logic->getVehicleGroupsByUserId($user_id);

        // BROWSER
        if (!(preg_match("/Chrome/",$_SERVER['HTTP_USER_AGENT']))){
            $view_data['browser'] = $_SERVER['HTTP_USER_AGENT'];
        }

        // MAP API
        $view_data['map_api'] = $this->map_api;
        $view_data['decarta_api_key'] = $this->decarta_api_key;

        switch ($this->map_api) {
            case 'google':
                   // GOOGLE MAPS API LINK
                   $view_data['map_api_link'] = 'https://maps.googleapis.com/maps/api/js?&client=gme-globaltrackingcommunications&sensor=false';
                   array_push($this->js_files, 'infobox_packed');
                   break;

            case 'mapbox':
                   // MAPBOX API LINK
                   $view_data['map_api_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.js';
                   $view_data['map_css_link'] = 'https://api.tiles.mapbox.com/mapbox.js/v1.4.0/mapbox.css';
                   break;
        }

        $view_data['unit_id'] = $unit_id;

        $unitgroup_columns      = array('unitgroup_id', 'unitgroupname');
        $unitattribute_columns  = array('unitattribute_id', 'vin', 'make', 'model', 'year', 'color', 'licenseplatenumber', 'loannumber', 'purchasedate', 'renewaldate', 'lastrenewaldate', 'stocknumber', 'activatedate', 'deactivatedate');
        $customer_columns       = array('customer_id', 'firstname', 'lastname', 'streetaddress', 'city', 'state', 'zipcode', 'country', 'homephone', 'cellphone', 'email');
        $unitinstallation_columns   = array('unitinstallation_id', 'installer', 'installdate');
        $unitodometer_columns       = array('unitodometer_id', 'initialodometer', 'currentodometer');

        $start_date = '';
        $end_date   = '';

        $unit_location = $this->vehicle_logic->getVehicleDataByLastEvent($unit_id);
        $unit_info = $this->vehicle_logic->getVehicleInfo($unit_id, $unitgroup_columns, $unitattribute_columns, $customer_columns, $unitinstallation_columns, $unitodometer_columns);

        $view_data['data'] = $unit_info;

        $view_data['data']['unitname'] = $unit_info['unitname'] ;
        $view_data['data']['latitude'] = $unit_location['latitude'] ;
        $view_data['data']['longitude'] = $unit_location['longitude'] ;
        $view_data['data']['unit_db'] = $unit_location['DB'] ;
        $view_data['data']['unit_id'] = $unit_location['ID'] ;
        $view_data['data']['SQL'] = $unit_location['SQL'] ;

        if($unit_info['streetaddress']=='no address'){
            $unit_info['streetaddress']='';
        }

        $view_data['data']['streetaddress']             = $unit_info['streetaddress'] ;
        $view_data['data']['city']                      = $unit_info['city'] ;
        $view_data['data']['state']                     = $unit_info['state'] ;
        $view_data['data']['zipcode']                   = $unit_info['zipcode'] ;
        $view_data['data']['country']                   = $unit_info['country'] ;

        // $view_data['data']['servertime']                = $unit_info['servertime'] . ' (' . str_replace('_',' ',$unit_info['unit_timezone']) . ')' ;
        // $view_data['data']['unittime']                  = $unit_info['unittime'] . ' (' . str_replace('_',' ',$unit_info['unit_timezone']) . ')' ;
        $view_data['data']['unittime']                  = (! empty($unit_info['unittime']) AND ($unit_info['unittime'] != '0000-00-00')) ? Date::utc_to_locale($unit_info['unittime'], $unit_info['unit_timezone'], 'm/d/Y g:iA') : '';
        $view_data['data']['unittime']                  .= ' (' . str_replace('_',' ',$unit_info['unit_timezone']) . ')' ;

        if($unit_info['territoryname']){
            $view_data['data']['formatted_address']     = $unit_info['territoryname'] . ': ';
        }
        $view_data['data']['formatted_address']         .= $this->address_logic->validateAddress($unit_info['streetaddress'], $unit_info['city'], $unit_info['state'], $unit_info['zipcode'], $unit_info['country']);
        $view_data['data']['infomarker_address']        = $this->address_logic->validateAddress($unit_info['streetaddress'], '<br>'.$unit_info['city'], $unit_info['state'], $unit_info['zipcode'], $unit_info['country']);
        $view_data['data']['formatted_cell_phone']      = $this->address_logic->formatPhoneDisplay($unit_info['cellphone']);
        $view_data['data']['formatted_home_phone']      = $this->address_logic->formatPhoneDisplay($unit_info['homephone']);
        // $view_data['data']['formatted_purchasedate']    = (! empty($unit_info['purchasedate']) AND ($unit_info['purchasedate'] != '0000-00-00')) ? Date::utc_to_locale($unit_info['purchasedate'], $user_timezone, 'm/d/Y') : '';
        // $view_data['data']['formatted_expirationdate']  = (! empty($unit_info['renewaldate']) AND ($unit_info['renewaldate'] != '0000-00-00')) ? Date::utc_to_locale($unit_info['renewaldate'], $user_timezone, 'm/d/Y') : '';
        // $view_data['data']['formatted_installdate']     = (! empty($unit_info['installdate']) AND ($unit_info['installdate'] != '0000-00-00')) ? Date::utc_to_locale($unit_info['installdate'], $user_timezone, 'm/d/Y') : '';
        // $view_data['data']['formatted_lastrenewaldate'] = (! empty($unit_info['lastrenewaldate']) AND ($unit_info['lastrenewaldate'] != '0000-00-00')) ? Date::utc_to_locale($unit_info['lastrenewaldate'], $user_timezone, 'm/d/Y') : '';
        $view_data['data']['installmileage']            = ! empty($unit_info['initialodometer']) ? $unit_info['initialodometer'] : 0;
        $view_data['data']['drivenmileage']             = ! empty($unit_info['currentodometer']) ? $unit_info['currentodometer'] : 0;
        $view_data['data']['totalmileage']              = (string) ($view_data['data']['installmileage'] + $view_data['data']['drivenmileage']);
        $view_data['data']['odometer_id']               = $unit_info['unitodometer_id'];
        $view_data['data']['stock']                     = $unit_info['stocknumber'];
        // $view_data['data']['formatted_activatedate']    = (! empty($unit_info['activatedate']) AND ($unit_info['activatedate'] != '0000-00-00')) ? Date::utc_to_locale($unit_info['activatedate'], $user_timezone, 'm/d/Y') : '';
        // $view_data['data']['formatted_deactivatedate']  = (! empty($unit_info['deactivatedate']) AND ($unit_info['deactivatedate'] != '0000-00-00')) ? Date::utc_to_locale($unit_info['deactivatedate'], $user_timezone, 'm/d/Y') : '';
        // $view_data['data']['formatted_installdate']     = (! empty($unit_info['installdate']) AND ($unit_info['installdate'] != '0000-00-00')) ? Date::utc_to_locale($unit_info['installdate'], $user_timezone, 'm/d/Y') : '';
        $view_data['data']['year']                      = ($unit_info['year'] == 0) ? null : $unit_info['year'];

        $this->render("page/vehicle/print.html.twig", $view_data);
   }
}
