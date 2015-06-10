<?php

namespace Controllers;

use Models\Logic\AddressLogic;

use Models\Data\VehicleData;
use Models\Logic\VehicleLogic;

use Models\Data\TerritoryData;
use Models\Logic\TerritoryLogic;

//use Zend\Permissions\Acl\Role\RoleInterface;
use GTC\Component\Utils\CSV\CSVBuilder;

/**
 * Class Boundary
 *
 */
class Boundary extends BasePage
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        array_push($this->css_files, 'boundary');
        array_push($this->css_files, 'map');
        array_push($this->js_files, 'boundary');

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

        $this->territory_data = new TerritoryLogic;
        $this->territory_logic = new TerritoryLogic;

        /** 
         * push secondary navigation based on user permission
         */
        if (true) {
            array_push($this->secondaryNavigation, array(
                'label' => 'Map',
                'route' => 'boundary/map'
            ));
        }

        if (true) {
            array_push($this->secondaryNavigation, array(
                'label' => 'List',
                'route' => 'boundary/list'
            ));
        }

        if (true) {
            array_push($this->secondaryNavigation, array(
                'label' => 'Groups',
                'route' => 'boundary/group'
            ));
        }

        if (true) {
            array_push($this->secondaryNavigation, array(
                'label' => 'Incomplete Locations',
                'route' => 'boundary/incomplete'
            ));
        }
    }
}