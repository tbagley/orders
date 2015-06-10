<?php

namespace Controllers;

use Models\Data\AlertData;
use Models\Logic\AlertLogic;
use Models\Data\VehicleData;
use Models\Logic\VehicleLogic;
use Models\Data\TerritoryData;
use Models\Logic\TerritoryLogic;
use Models\Data\ContactData;
use Models\Logic\ContactLogic;
use Models\Logic\CronLogic;
use Models\Logic\UserLogic;
use GTC\Component\Utils\Date;
use GTC\Component\Utils\CSV\CSVBuilder;
use GTC\Component\Utils\PDF\TCPDFBuilder;

/**
 * Class Alert
 *
 */
class Alert extends BasePage
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        array_push($this->css_files, 'alert');
        array_push($this->js_files, 'alert');

        $this->load_db('master');
        $this->load_db('slave');

        $this->alert_data = new AlertData;
        $this->alert_logic = new AlertLogic;
        $this->vehicle_data = new VehicleData;
        $this->vehicle_logic = new VehicleLogic;
        $this->territory_data = new TerritoryData;
        $this->territory_logic = new TerritoryLogic;
        $this->contact_data = new ContactData;
        $this->contact_logic = new ContactLogic;
        $this->cron_logic = new CronLogic;
        $this->user_logic = new UserLogic;

        /** 
         * push secondary navigation based on user permission
         */
        if ($this->access['alert']['read']) {

            array_push($this->secondaryNavigation, array(
                'label' => 'HISTORY',
                'route' => 'alert/history'
            ));

            array_push($this->secondaryNavigation, array(
                'label' => 'MANAGEMENT',
                'route' => 'alert/list'
            ));
        }
        
        if ($this->access['alert']['write']) {
            array_push($this->secondaryNavigation, array(
                'label' => 'CONTACTS',
                'route' => 'alert/contact'
            ));
        }
    }
     
    public function index()
    {
        $this->history();
    }

    /**
     * @route alert_list /alert/list
     */
    public function listview()
    {
        $view_data                      = array();
        $account_id                     = $this->user_session->getAccountId();
        $user_id                        = $this->user_session->getUserId();
        $view_data['landmark_groups']   = array();
        $view_data['landmarks']         = array();
        $view_data['boundary_groups']   = array();
        $view_data['boundaries']        = array();
        $view_data['vehicle_groups']    = $this->vehicle_logic->getVehicleGroupsByUserId($user_id);
        $view_data['vehicles']          = $this->vehicle_data->getVehiclesByGroupIds($user_id, array(),$account_id);

        // get all territory groups assigned to this user
        $territory_groups = $this->territory_logic->getTerritoryGroupsByUserId($user_id);
        $view_data['landmark_groups'] = $view_data['boundary_groups'] = $view['landmarks'] = $view['boundary'] = $groups = array();

        $user['account_id'] = $account_id;
        $user['user_id'] = $user_id;
        $view_data['options_alerttypes']       = $this->vehicle_data->ajaxOptions($user,'','alerttype');
        $view_data['options_contactmethods']   = $this->vehicle_data->ajaxOptions($user,'','contactmethod');
        $view_data['options_contactmodes']     = $this->vehicle_data->ajaxOptions($user,'','contactmode');
        $view_data['options_contacts']         = $this->vehicle_data->ajaxOptions($user,'','contact');
        $view_data['options_contactgroups']    = $this->vehicle_data->ajaxOptions($user,'','contactgroup');
        $view_data['options_days']             = $this->vehicle_data->ajaxOptions($user,'','days');
        $view_data['options_duration']         = $this->vehicle_data->ajaxOptions($user,'','duration');
        $view_data['options_hours']            = $this->vehicle_data->ajaxOptions($user,'','hours');
        $view_data['options_landmarktriggers'] = $this->vehicle_data->ajaxOptions($user,'','landmarktrigger');
        $view_data['options_landmarkmodes']    = $this->vehicle_data->ajaxOptions($user,'','landmarkmode');
        $view_data['options_landmarks']        = $this->vehicle_data->ajaxOptions($user,'','landmark');
        $view_data['options_landmarkgroups']   = $this->vehicle_data->ajaxOptions($user,'','landmarkgroup');
        $view_data['options_overspeed']        = $this->vehicle_data->ajaxOptions($user,'','overspeed');
        $view_data['options_range']            = $this->vehicle_data->ajaxOptions($user,'','range');
        $view_data['options_vehiclemodes']     = $this->vehicle_data->ajaxOptions($user,'','vehiclemode');
        $view_data['options_vehicles']         = $this->vehicle_data->ajaxOptions($user,'','vehicle');
        $view_data['options_vehiclegroups']    = $this->vehicle_data->ajaxOptions($user,'','vehiclegroup');

        if (! empty($territory_groups)) {
            $new_territories = array();
            foreach ($territory_groups as $index => $group) {
                $groups[] = $group['territorygroup_id'];
                if (! isset($new_territories[$group['territorygroup_id']])) {
                    $new_territories[$group['territorygroup_id']] = $group;
                }
            }
            
            // get territories by group ids
            $territories = $this->territory_logic->getTerritoryByGroupIds($user_id, $groups);
            
            // separate boundaries and landmarks
            if (! empty($territories)) {
                $landmarks = $boundaries = $landmark_groups = $boundary_groups = array();
                foreach ($territories as $territory) {
                    if (isset($territory['territorytype'])) {
                        switch ($territory['territorytype']) {
                            case 'landmark':
                                if (! isset($landmarks[$territory['territory_id']])) {
                                    $landmarks[$territory['territory_id']] = $territory;
                                }
                                
                                if (! isset($landmark_groups[$territory['territorygroup_id']])) {
                                    $landmark_groups[$territory['territorygroup_id']] = $new_territories[$territory['territorygroup_id']];
                                }
                                break;
                            case 'boundary':
                                if (! isset($landmarks[$territory['territory_id']])) {
                                    $boundaries[$territory['territory_id']] = $territory;
                                }
                                
                                if (! isset($landmark_groups[$territory['territorygroup_id']])) {
                                    $boundary_groups[$territory['territorygroup_id']] = $new_territories[$territory['territorygroup_id']];
                                }
                                break;
                        }
                    }    
                }
                
                $view_data['landmark_groups']   = $landmark_groups;
                $view_data['landmarks']         = $landmarks;
                $view_data['boundary_groups']   = $boundary_groups;
                $view_data['boundaries']        = $boundaries;
            } 
        }

        // get contact groups
        $view_data['alerts']            = $this->alert_logic->getAlertsByUserId($user_id);
        $view_data['contacts']          = $this->contact_logic->getContactsByAccountId($account_id);
        $view_data['contact_groups']    = $this->contact_logic->getContactGroupsByAccountId($account_id);
        $view_data['alerttypes']        = $this->alert_logic->getAlertTypes();
        
        $this->render('page/alert/list.html.twig', $view_data);
    }

    public function history()
    {
        $view_data = array();
        $user_id                        = $this->user_session->getUserId();
        $account_id                     = $this->user_session->getAccountId();
        $view_data['landmark_groups']   = array();
        $view_data['landmarks']         = array();
        $view_data['boundary_groups']   = array();
        $view_data['boundaries']        = array();
        $view_data['vehicle_groups']    = $this->vehicle_logic->getVehicleGroupsByUserId($user_id);
        $view_data['vehicles']          = $this->vehicle_data->getVehiclesByGroupIds($user_id, array(),$account_id);
        // get all territory groups assigned to this user
        $territory_groups = $this->territory_logic->getTerritoryGroupsByUserId($user_id);
        $view_data['landmark_groups'] = $view_data['boundary_groups'] = $view['landmarks'] = $view['boundary'] = $groups = array();

        $user['account_id'] = $account_id;
        $user['user_id'] = $user_id;
        $view_data['options_alerttypes']       = $this->vehicle_data->ajaxOptions($user,'','alerttype');
        $view_data['options_contactmethods']   = $this->vehicle_data->ajaxOptions($user,'','contactmethod');
        $view_data['options_contactmodes']     = $this->vehicle_data->ajaxOptions($user,'','contactmode');
        $view_data['options_contacts']         = $this->vehicle_data->ajaxOptions($user,'','contact');
        $view_data['options_contactgroups']    = $this->vehicle_data->ajaxOptions($user,'','contactgroup');
        $view_data['options_days']             = $this->vehicle_data->ajaxOptions($user,'','days');
        $view_data['options_duration']         = $this->vehicle_data->ajaxOptions($user,'','duration');
        $view_data['options_hours']            = $this->vehicle_data->ajaxOptions($user,'','hours');
        $view_data['options_landmarktriggers'] = $this->vehicle_data->ajaxOptions($user,'','landmarktrigger');
        $view_data['options_landmarkmodes']    = $this->vehicle_data->ajaxOptions($user,'','landmarkmode');
        $view_data['options_landmarks']        = $this->vehicle_data->ajaxOptions($user,'','landmark');
        $view_data['options_landmarkgroups']   = $this->vehicle_data->ajaxOptions($user,'','landmarkgroup');
        $view_data['options_overspeed']        = $this->vehicle_data->ajaxOptions($user,'','overspeed');
        $view_data['options_range']            = $this->vehicle_data->ajaxOptions($user,'','range');
        $view_data['options_vehiclemodes']     = $this->vehicle_data->ajaxOptions($user,'','vehiclemode');
        $view_data['options_vehicles']         = $this->vehicle_data->ajaxOptions($user,'','vehicle');
        $view_data['options_vehiclegroups']    = $this->vehicle_data->ajaxOptions($user,'','vehiclegroup');

        $temp_sms_carriers = $this->contact_logic->getSMSCarrierOptions();
        if (! empty($temp_sms_carriers)) {
            foreach ($temp_sms_carriers as $sc) {
                if (! isset($sms_carriers[$sc['cellcarrier_id']])) {
                    $sms_carriers[$sc['cellcarrier_id']] = $sc['cellcarrier'];    
                }
            }
        }        
        $view_data['sms_carriers'] = $temp_sms_carriers;

        if (! empty($territory_groups)) {
            $new_territories = array();
            foreach ($territory_groups as $index => $group) {
                $groups[] = $group['territorygroup_id'];
                if (! isset($new_territories[$group['territorygroup_id']])) {
                    $new_territories[$group['territorygroup_id']] = $group;
                }
            }
            
            // get territories by group ids
            $territories = $this->territory_logic->getTerritoryByGroupIds($user_id, $groups);
            
            // separate boundaries and landmarks
            if (! empty($territories)) {
                $landmarks = $boundaries = $landmark_groups = $boundary_groups = array();
                foreach ($territories as $territory) {
                    if (isset($territory['territorytype'])) {
                        switch ($territory['territorytype']) {
                            case 'landmark':
                                if (! isset($landmarks[$territory['territory_id']])) {
                                    $landmarks[$territory['territory_id']] = $territory;
                                }
                                
                                if (! isset($landmark_groups[$territory['territorygroup_id']])) {
                                    $landmark_groups[$territory['territorygroup_id']] = $new_territories[$territory['territorygroup_id']];
                                }
                                break;
                            case 'boundary':
                                if (! isset($landmarks[$territory['territory_id']])) {
                                    $boundaries[$territory['territory_id']] = $territory;
                                }
                                
                                if (! isset($landmark_groups[$territory['territorygroup_id']])) {
                                    $boundary_groups[$territory['territorygroup_id']] = $new_territories[$territory['territorygroup_id']];
                                }
                                break;
                        }
                    }    
                }
                
                $view_data['landmark_groups']   = $landmark_groups;
                $view_data['landmarks']         = $landmarks;
                $view_data['boundary_groups']   = $boundary_groups;
                $view_data['boundaries']        = $boundaries;
            } 
        }
        
        $view_data['alerts']            = $this->alert_logic->getAlertsByUserId($user_id);
        $view_data['vehicle_groups']    = $this->vehicle_logic->getVehicleGroupsByUserId($user_id);
        $view_data['contacts']          = $this->contact_logic->getContactsByAccountId($account_id);
        $view_data['contact_groups']    = $this->contact_logic->getContactGroupsByAccountId($account_id);
        $view_data['alerttypes']        = $this->alert_logic->getAlertTypes();

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
        
        $this->render('page/alert/history.html.twig', $view_data);
    }

    public function contact()
    {
        $account_id = $this->user_session->getAccountId();
        $user_id    = $this->user_session->getUserId();
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
        $view_data['sms_carriers'] = $temp_sms_carriers;

        // get contact groups
        $view_data['contact_groups'] = $this->contact_logic->getContactGroupsByAccountId($account_id);        
        $view_data['users']          = $this->user_logic->getUserByAccountId($account_id);

        $this->render('page/contact/contact.html.twig', $view_data);
    }
}
