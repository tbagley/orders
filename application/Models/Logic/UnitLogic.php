<?php

namespace Models\Logic;

use Models\Logic\BaseLogic;
use Models\Data\UnitData;

class UnitLogic extends BaseLogic
{   
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();

        $this->unit_data = new UnitData;
    }

    /**
     * Get error messages (calls the parent method)
     *
     * @return bool|array
     */ 
    public function getErrorMessage()
    {
        return parent::getErrorMessage();
    }

    /**
     * Get unit info
     */ 
    public function getUnitInfo($unit_id)
    {
        $result = $this->unit_data->getUnitInfo($unit_id);

        $sim = $this->unit_data->getSimInfo($result['simcard_id']);

        if ($sim === null || $sim === false) {
            $out = $result;
        } else {
            $out = array_merge($result, $sim);
        }

        return $out;
    }

}