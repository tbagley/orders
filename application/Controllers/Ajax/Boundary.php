<?php

namespace Controllers\Ajax;

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
class Boundary extends BaseAjax
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->vehicle_data = new VehicleData;
        $this->vehicle_logic = new VehicleLogic;
        $this->address_logic = new AddressLogic;

        $this->territory_data = new TerritoryLogic;
        $this->territory_logic = new TerritoryLogic;
    }

    /**
     * Get landmarks for x-editable dropdown
     *
     * @return void
     *
     */    
    public function getBoundaryOptions($placeholder = null, $value = '')
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

        // set territory type to be 'boundary'
        $this->territory_logic->setTerritoryType('boundary');
        
        $territory_groups = $groups = array();
        $user_id = $this->user_session->getUserId();
        $account_id = $this->user_session->getAccountId();
        
        $territory_groups = $this->territory_logic->getTerritoryGroupsByUserId($user_id);
        
        if (! empty($territory_groups)) {
            foreach ($territory_groups as $territory_group) {
                $groups[] = $territory_group['territorygroup_id'];
            }
        }

        $boundaries = $this->territory_data->getTerritoryByGroupIds($account_id, $groups);
        if (! empty($boundaries)) {
            $last_index = count($boundaries) - 1;
            foreach ($boundaries as $index => $boundary) {
                $separator = ',';

                if ($index == $last_index) {
                    $separator = '';
                }

                $output .= '{"value": "' . $boundary['territory_id'] . '", "text": "' . $boundary['territoryname'] . '"}' . $separator;
            }
        }

        $output .= ']';
        
        // reset territory type array back to default (i.e. all territory types)
        $this->territory_logic->resetTerritoryType();
        
        die($output);
    }
    
    /**
     * Get boundary groups for x-editable dropdown
     *
     * @return void
     *
     */
    public function getBoundaryGroupOptions()
    {
        $user_id = $this->user_session->getUserId();
        $output  = '[';
        
        // set territory type to 'landmark'
        $this->territory_logic->setTerritoryType('boundary');
        
        $boundary_groups = $this->territory_logic->getTerritoryGroupsByUserId($user_id);
        if ($boundary_groups !== false) {
            $last_index = count($boundary_groups) - 1;
            foreach ($boundary_groups as $index => $group) {
                $separator = ',';

                if ($index == $last_index) {
                    $separator = '';
                }

                $output .= '{"value": "' . $group['territorygroup_id'] . '", "text": "' . $group['territorygroupname'] . '"}' . $separator;
            }
        }

        // reset territory type
        $this->territory_logic->resetTerritoryType();

        $output .= ']';
        die($output);

    }
}