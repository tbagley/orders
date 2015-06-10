<?php

namespace Models\Data;

use Models\Data\BaseData;

class UnitData extends BaseData
{
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * Get Unit SIM info
     *
     * @param int unit_id
     * @return array
     */
    public function getUnitSimInfo($unit_id)
    {
        $sql = "SELECT si.msisdn AS msisdn,
                       si.provider_id AS provider_id,
                       sp.twilio AS twilio,
                       sp.simprovider AS simprovider,
                       u.db AS unit_db
                FROM crossbones.unit AS u
                INNER JOIN crossbones.simcard AS sc ON sc.simcard_id = u.simcard_id
                INNER JOIN unitmanagement.simcardinventory AS si ON si.simcardinventory_id = sc.simcardinventory_id
                INNER JOIN unitmanagement.simprovider AS sp ON sp.simprovider_id = si.provider_id
                WHERE u.unit_id = ?
                LIMIT 1";
        
        return $this->db_read->fetchAll($sql, array($unit_id));

        // **********
        // PRE-TWILIO 
        //
        // $sql = "SELECT si.msisdn AS msisdn,
        //                si.msisdn AS msisdn,
        //                si.provider_id AS provider_id,
        //                u.db AS unit_db
        //         FROM crossbones.unit AS u
        //         INNER JOIN crossbones.simcard AS sc ON sc.simcard_id = u.simcard_id
        //         INNER JOIN unitmanagement.simcardinventory AS si ON si.simcardinventory_id = sc.simcardinventory_id
        //         WHERE u.unit_id = ?
        //         LIMIT 1";
        //        
        // return $this->db_read->fetchAll($sql, array($unit_id));
    }

    /**
     * Get Unit info
     *
     * @return array
     */
    public function getUnitInfo($unit_id)
    {

        $sql = "
            SELECT
                unit.*,
                timezone.timezone,
                unitversion.version,
                unitstatus.unitstatusname,
                rgeo.rgeo,
                simcard.simcard_id,
                simcardinventory.simcardinventory_id,
                simprovider.*
            FROM
                crossbones.unit AS unit
                INNER JOIN unitmanagement.unitversion AS unitversion ON unitversion.unitversion_id = unit.unitversion_id
                INNER JOIN crossbones.unitstatus AS unitstatus ON unitstatus.unitstatus_id = unit.unitstatus_id
                INNER JOIN unitmanagement.timezone AS timezone ON unit.timezone_id = timezone.timezone_id
                INNER JOIN unitmanagement.rgeo AS rgeo ON rgeo.rgeo_id = unit.rgeo_id
                INNER JOIN crossbones.simcard AS simcard ON simcard.simcard_id = unit.simcard_id
                INNER JOIN unitmanagement.simcardinventory AS simcardinventory ON simcardinventory.simcardinventory_id = simcard.simcardinventory_id
                INNER JOIN unitmanagement.simprovider AS simprovider ON simprovider.simprovider_id = simcardinventory.provider_id
            WHERE
                `unit_id`= ?
                limit 1
        ";

        $results = $this->db_read->fetchAssoc($sql, array($unit_id));

        return $results;
    }

    public function getSimInfo($simcard_id)
    {

        $sql = "
            SELECT
              *
            FROM
                crossbones.simcard as simcard
                INNER JOIN unitmanagement.simcardinventory AS inv ON inv.simcardinventory_id = simcard.simcardinventory_id
                INNER JOIN unitmanagement.simprovider AS provider ON provider.simprovider_id = inv.provider_id
            WHERE
              `simcard_id`= ?
              limit 1
        ";

        $results = $this->db_read->fetchAssoc($sql, array($simcard_id));

        return $results;

    }

}