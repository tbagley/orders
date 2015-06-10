<?php

namespace Controllers;

use Models\Logic\AddressLogic;

use Models\Data\VehicleData;
use Models\Logic\VehicleLogic;

use Models\Data\TerritoryData;
use Models\Logic\TerritoryLogic;

use Models\Logic\UserLogic;

//use Zend\Permissions\Acl\Role\RoleInterface;
use GTC\Component\Utils\Measurement;
use GTC\Component\Utils\CSV\CSVBuilder;
use GTC\Component\Map\Tiger;

use GTC\Component\Utils\PDF\TCPDFBuilder;

/**
 * Class Landmark
 *
 */
class Landmark extends BasePage
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        array_push($this->css_files, 'landmark');
        array_push($this->css_files, 'map');
        array_push($this->js_files, 'landmark');

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
        
        $this->load_db('master');
        $this->load_db('slave');


        $this->vehicle_data = new VehicleData;
        $this->vehicle_logic = new VehicleLogic;
        $this->address_logic = new AddressLogic;

        $this->territory_data = new TerritoryData;
        $this->territory_logic = new TerritoryLogic;
        
        $this->user_logic = new UserLogic;

        if ($this->access['landmark']['read']) {
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
        }

        if ($this->access['landmark_group']['write']) {
            array_push($this->secondaryNavigation, array(
                'label' => 'GROUPS',
                'route' => 'landmark/group'
            ));
        }

        if ($this->access['vehicle_reference_address']['read']) {
            array_push($this->secondaryNavigation, array(
                'label' => 'VERIFICATION ADDRESSES',
                'route' => 'landmark/verification'
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
     *
     */
    public function map() 
    {
        $view_data  = array();
  
        $landmarks = $landmark_groups = $groups = array();

        $user_id    = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();

        $user['account_id'] = $account_id ;

        $this->territory_logic->setTerritoryType('landmark');

        $landmark_groups = $view_data['landmark_groups'] = $this->territory_logic->getTerritoryGroupsByUserId($user_id);

        $view_data['options_shapes']      = $this->vehicle_data->ajaxOptions($user,'','shape');
        $view_data['options_radius']      = $this->vehicle_data->ajaxOptions($user,'','radius');
        $view_data['options_groups']      = $this->vehicle_data->ajaxOptions($user,'','landmarkgroup');
        $view_data['options_categories']  = $this->vehicle_data->ajaxOptions($user,'','landmarkcategory');
        $view_data['options_states']      = $this->vehicle_data->ajaxOptions($user,'','state');
        $view_data['options_countries']   = $this->vehicle_data->ajaxOptions($user,'','country');

        if (! empty($landmark_groups)) {
            foreach ($landmark_groups as $group) {
                $groups[] = $group['territorygroup_id'];
            }
        }

        // display default landmark list amount for map page on load (default = 20)
        $landmarks = $this->territory_logic->getTerritoryByGroupIds($user_id, array(), $account_id);  //reset to pull all landmarks for user_id

        $view_data['total_landmark_count']  = count($landmarks);        
        // $view_data['landmarks']             = array_splice($landmarks, 0, 20);
        $view_data['landmarks']             = $landmarks;
        $view_data['forward_hidden']      = '';
        if ($view_data['total_landmark_count'] <= 20) {
            $view_data['forward_hidden'] = "hidden";
            $view_data['view_count'] = $view_data['total_landmark_count']; 
        } else {
            $view_data['view_count'] = 20;
        }

        $view_data['landmark_count_start'] = 0;
        if ($view_data['total_landmark_count'] > 0) {
            $view_data['landmark_count_start'] = 1;
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

        $view_data['territory_categories'] = $this->territory_logic->getAllTerritoryCategories();

        $this->render("page/landmark/map.html.twig", $view_data);
    }

    /**
     * @route landmark_list /landmark/list
     */
    public function listview() 
    {
        $view_data  = array();
        $groups     = array();

        $user_id    = $this->user_session->getUserId();
        $user['user_id'] = $user_id;
        $account_id = $this->user_session->getAccountId();
        $user['account_id'] = $account_id;
        
        $this->territory_logic->setTerritoryType('landmark');
        
        $landmark_groups = $view_data['landmark_groups'] = $this->territory_logic->getTerritoryGroupsByUserId($user_id);
        $view_data['options_shapes']      = $this->vehicle_data->ajaxOptions($user,'','shape');
        $view_data['options_radius']      = $this->vehicle_data->ajaxOptions($user,'','radius');
        $view_data['options_groups']      = $this->vehicle_data->ajaxOptions($user,'','landmarkgroup');
        $view_data['options_categories']  = $this->vehicle_data->ajaxOptions($user,'','landmarkcategory');
        $view_data['options_states']      = $this->vehicle_data->ajaxOptions($user,'','state');
        $view_data['options_countries']   = $this->vehicle_data->ajaxOptions($user,'','country');

        foreach ($landmark_groups as $group) {
            $groups[] = $group['territorygroup_id'];
        }

        // display default vehicle list amount for map page on load (default = 20)
        $view_data['landmarks'] = array_splice($this->territory_logic->getTerritoryByGroupIds($account_id, array()), 0, 20);

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
        
        $view_data['territory_categories'] = $this->territory_logic->getAllTerritoryCategories(); 

        $this->render("page/landmark/list.html.twig", $view_data);
    }

    public function group()
    {
        array_push($this->js_files, '../vendor/jquery/jquery-ui-1.10.3.custom.min');
        $view_data = array();
        
        $user_id = $this->user_session->getUserId();
        
        $view_data['landmarks']       = $this->territory_logic->getFilteredTerritoryList($user_id);
        $view_data['landmark_groups'] = $this->territory_logic->getTerritoryGroupsByUserId($user_id);

        $this->render("page/landmark/groups-list.html.twig", $view_data);
    }

    /**
     *
     */
    public function incomplete()
    {
        $view_data = array();

        $user_id = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();
        
        $landmark_groups = $view_data['landmark_groups'] = $this->territory_logic->getTerritoryGroupsByUserId($user_id);

        $groups = array();

        foreach ($landmark_groups as $group) {
            $groups[] = $group['territorygroup_id'];
        }

        // display default vehicle list amount for map page on load (default = 20)
        $view_data['landmarks'] = array_splice($this->territory_logic->getTerritoryByGroupIds($account_id, $groups), 0, 20);

        $view_data['vehicles']  = $this->vehicle_data->getVehiclesByGroupIds($user_id, array(),$account_id);
        // $view_data['landmarks']       = $this->territory_logic->getFilteredTerritoryList($user_id);
        // $landmark_groups = $view_data['landmark_groups'] = $this->territory_logic->getTerritoryGroupsByUserId($user_id);
        $user['user_id'] = $user_id;
        $user['account_id'] = $account_id;
        $view_data['options_shapes']      = $this->vehicle_data->ajaxOptions($user,'','shape');
        $view_data['options_radius']      = $this->vehicle_data->ajaxOptions($user,'','radius');
        $view_data['options_groups']      = $this->vehicle_data->ajaxOptions($user,'','landmarkgroup');
        $view_data['options_categories']  = $this->vehicle_data->ajaxOptions($user,'','landmarkcategory');
        $view_data['options_states']      = $this->vehicle_data->ajaxOptions($user,'','state');
        $view_data['options_countries']   = $this->vehicle_data->ajaxOptions($user,'','country');

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

        $this->render("page/landmark/incomplete.html.twig", $view_data);
    }

    public function verification() 
    {
        $view_data  = array();
        $groups     = array();

        $user_id    = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();

        $this->territory_logic->setTerritoryType('reference');

        $view_data['vehicles']  = $this->vehicle_data->getVehiclesByGroupIds($user_id, array(),$account_id);
        $view_data['landmarks']       = $this->territory_logic->getFilteredTerritoryList($user_id);
        $landmark_groups = $view_data['landmark_groups'] = $this->territory_logic->getTerritoryGroupsByUserId($user_id);
        $view_data['options_shapes']      = $this->vehicle_data->ajaxOptions($user,'','shape');
        $view_data['options_radius']      = $this->vehicle_data->ajaxOptions($user,'','radius');
        $view_data['options_groups']      = $this->vehicle_data->ajaxOptions($user,'','landmarkgroup');
        $view_data['options_categories']  = $this->vehicle_data->ajaxOptions($user,'','landmarkcategory');
        $view_data['options_states']      = $this->vehicle_data->ajaxOptions($user,'','state');
        $view_data['options_countries']   = $this->vehicle_data->ajaxOptions($user,'','country');

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

        //$this->render("page/landmark/list.html.twig", $view_data);
        $this->render("page/landmark/verification.html.twig", $view_data);
    }
}