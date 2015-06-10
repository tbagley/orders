<?php

namespace Models\Data;

use Models\Data\UnitData;

class UnitCommandData extends UnitData
{
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    public function smsTwilio($params)
    {
        $twilio = $this->db_write->insert('sms.twilio', $params);
        return $twilio;
    }

    /**
     * Get the raw unit command
     *
     * @param int unit_id
     * @param string command_name
     * @return string | array
     */
    public function getUnitCommand($unit_id, $command_name)
    {
        // $sql = "SELECT uc.command as command
        //         FROM unitmanagement.unitcommand AS uc
        //         INNER JOIN crossbones.unit AS u ON u.unitmanufacturer_id = uc.unitmanufacturer_id
        //         WHERE u.unit_id = ? AND uc.commandname = ?
        //         LIMIT 1";

        $sql = "SELECT uc.command as command
                FROM unitmanagement.unitcommand AS uc
                LEFT JOIN unitmanagement.unitversion AS uv ON uv.unitmanufacturer_id = uc.unitmanufacturer_id
                LEFT JOIN crossbones.unit AS u ON u.unitversion_id = uv.unitversion_id
                WHERE u.unit_id = ? AND uc.commandname = ?
                LIMIT 1";
        
        return $this->db_read->fetchAll($sql, array($unit_id, $command_name));
    }

    /**
     * Log SMS message to be process
     *
     * @param array params
     * @return bool
     */
    public function logSmsMessage($params)
    {
        if ($this->db_write->insert('sms.out', $params) !== false) {
            return true;    
        }
        return false;
    }

    /**
     * get last sms message OUT by msisdn
     *
     * @param int msisdn
     * @return array sms messages
     */
    public function getLastSmsMessage($msisdn)
    {
        $sql = "SELECT *
                FROM sms.out AS so
                WHERE so.msisdn = ?
                ORDER BY so.messagedate DESC
                LIMIT 1";
        return $this->db_read->fetchAll($sql, array($msisdn));
    }

    /**
     * get last sms message IN since prevMessageDate by msisdn
     *
     * @param string datetime format string of previuos message
     * @param int msisdn
     * @return array sms messages
     */
    public function getLastSmsResponseSince($prevMessageDate, $msisdn)
    {
        $sql = "SELECT *
                FROM sms.in AS so
                WHERE so.msisdn = ? AND so.messagedate > ?
                ORDER BY so.messagedate ASC
                LIMIT 1";
        return $this->db_read->fetchAll($sql, array($msisdn, $prevMessageDate));
    }
}