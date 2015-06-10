<?php

namespace Models\Data;

class AccountData extends BaseData
{

    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();

    }

    /**
     * @param $account_id
     * @return mixed
     */
    public function getAccountDetail($account_id)
    {
        // needs: name, timezone, username, status, type

        $sql = "
            SELECT
              account.*,
              accountstatus.accountstatusname,
              timezone.timezone,
              user.user_id, user.username, user.firstname, user.lastname, user.email,
              urls.url, urls.protocol AS url_protocol
            FROM
              crossbones.account AS account
              LEFT JOIN crossbones.accountstatus AS accountstatus ON accountstatus.accountstatus_id = account.accountstatus_id
              LEFT JOIN unitmanagement.timezone AS timezone ON timezone.timezone_id = account.timezone_id
              LEFT JOIN crossbones.user AS user ON user.account_id = account.account_id
              LEFT JOIN crossbones.urls AS urls ON urls.id = account.url_id
            WHERE
              account.account_id=?
            AND
              user.account_id=?
            AND
              user.roles='ROLE_ACCOUNT_OWNER'
            ";

        $results = $this->db_read->fetchAssoc($sql, array($account_id, $account_id));

        return $results;

    }

    /**
     * @return mixed
     */
    public function getListAccountStatus()
    {
        $results = $this->db_read->fetchAll("SELECT * FROM crossbones.accountstatus");

        return $results;
    }

    /**
     *
     */
    public function getListAccountTypes()
    {
        $results = $this->db_read->fetchAll("SELECT * FROM crossbones.accounttype");

        return $results;
    }

    /**
     * @param $search
     * @return mixed
     */
    public function search($search, $offset = 0, $limit = 20, $resellerId = 0)
    {
        $offset = filter_var($offset, FILTER_VALIDATE_INT, array('default' => 0));
        $limit  = filter_var($limit,  FILTER_VALIDATE_INT, array('default' => 20));
        
        if($resellerId > 1)
        {
	        $reseller_query=" AND account.dealer_id=$resellerId";
        }
        else
        {
	        $reseller_query="";
        }

        $sql = "
            SELECT
                account.*, accountstatus.accountstatusname
            FROM
                crossbones.account AS account, crossbones.accountstatus AS accountstatus
            WHERE
                accountstatus.accountstatus_id = account.accountstatus_id
            AND
                `accountname`  LIKE ?
            $reseller_query
            LIMIT ?, ?
        ";

        $statement = $this->db_read->executeQuery($sql, array("%$search%", $offset, $limit), array(\PDO::PARAM_STR, \PDO::PARAM_INT, \PDO::PARAM_INT));
        $results   = $statement->fetchAll();
        return $results;
    }

    /**
     * Get Number of Accounts with specified account status ID
     *
     * Account Statuses:
     *
     *      0 = All Statuses (all accounts)
     *      1 = Active
     *      2 = Canceled
     *      3 = Involuntary Suspension
     *      4 = Voluntary Suspension
     *      5 = Inactive
     *
     * @param int $status_id Default = 0 (all accounts)
     * @return mixed
     */
    public function getNumberOfAccountsByAccountStatusId($status_id = 0)
    {


        $sql_params = array();
        $result     = NULL;

        $sql = "SELECT
                    COUNT(account.account_id) as account_total
                FROM
                    crossbones.account
        ";

        if ($status_id !== 0) {

            $sql = "
                SELECT
                    COUNT(account.account_id) as account_total
                FROM
                    crossbones.account
                WHERE
                    crossbones.account.dealer_id = $status_id
            ";

            $sql_params = array($status_id);

        }

        $results = $this->db_read->fetchAll($sql, $sql_params);

        return $results;
    }

    /**
     * @return mixed
     */
    public function getAllTimezones()
    {
        $results = $this->db_read->fetchAll("SELECT * FROM unitmanagement.timezone");

        return $results;
    }

    /**
     * @return mixed
     */
    public function getTimezone($timezone_id)
    {
        $results = $this->db_read->fetchAll("SELECT * FROM unitmanagement.timezone WHERE timezone_id=$timezone_id");

        return $results;
    }

    /**
     * @param $accountname
     * @return mixed
     */
    public function searchForSingleAccountName($accountname)
    {
        $results = $this->db_read->fetchAll("SELECT `accountname` FROM crossbones.account WHERE `accountname`=? limit 1", array($accountname));

        return $results;
    }

    /**
     * @param $username
     * @return mixed
     */
    public function searchForSingleAccountUserName($username)
    {
        $results = $this->db_read->fetchAll("SELECT `username` FROM crossbones.user WHERE `username`=? limit 1", array($username));

        return $results;
    }

    /**
     * @param $account_status
     * @param $dealer_id
     * @param $account_type
     * @param $account_timezone
     * @param $customercode
     * @param $account_name
     * @param $createdate
     * @return string
     */
    public function insertNewAccount($account_status, $dealer_id, $account_type, $account_timezone, $customercode, $account_name, $account_theme, $account_address, $account_phone, $createdate, $account_locate)
    {

        $account_id = false;
        try {

            $insert = array(
                'accountstatus_id' => $account_status,
                'dealer_id' => $dealer_id,
                'accounttype_id' => $account_type,
                'timezone_id' => $account_timezone,
                'customercode' => $customercode,
                'accountname' => $account_name,
                'theme' => $account_theme,
                'address' => $account_address,
                'phonenumber' => $account_phone,
                'createdate' => $createdate,
                'locate' => $account_locate
            );

            $result = $this->db_write->insert('crossbones.account', $insert);

            if ($result) {
                $account_id = $this->db_write->lastInsertId();
            } else {
                $this->setErrorMessage("Failed to insert Account");
            }


        } catch (\Exception $e) {
            $this->setErrorMessage($e);
        }

        return $account_id;
    }

    /**
     * @param $account_id
     * @param $userstatus_id
     * @param $account_timezone
     * @param $role
     * @param $account_username
     * @param $encodedPassword
     * @param $createdate
     * @return string
     */
    public function insertNewUser($account_id, $userstatus_id, $usertype_id, $account_timezone, $role, $account_firstname, $account_lastname, $account_email, $createdate)
    {

        $user_id = false;
        try {

            $insert =  array(
                'account_id' => $account_id,
                'userstatus_id' => $userstatus_id,
                'usertype_id' => $usertype_id,
                'timezone_id' => $account_timezone,
                'roles' => $role,
                'firstname' => $account_firstname,
                'lastname' => $account_lastname,
                'email' => $account_email,
                'createdate' => $createdate
            );

            $result = $this->db_write->insert('crossbones.user', $insert);

            if ($result) {
                $user_id = $this->db_write->lastInsertId();
            } else {
                $this->setErrorMessage("Failed to insert User");
            }
        } catch (\Exception $e) {
            $this->setErrorMessage($e);
        }

        return $user_id;
    }

    /**
     * @param $account_id
     * @param $groupname
     * @param $grpdefault
     * @return string
     */
    public function insertUnitGroup($account_id, $groupname, $grpdefault)
    {
        $unitgroup_id = false;
        try {

            $insert = array(
                'account_id' => $account_id,
                'unitgroupname' => $groupname,
                '`default`' => $grpdefault,
                'active' => "1"
            );

            $result = $this->db_write->insert('crossbones.unitgroup', $insert);

            if ($result) {
                $unitgroup_id = $this->db_write->lastInsertId();
            } else {
                $this->setErrorMessage("Failed to insert: crossbones.unitgroup");
            }


        } catch (\Exception $e) {
            $this->setErrorMessage($e);
        }

        return $unitgroup_id;
    }

    public function insertTerritoryGroup($account_id, $groupname, $grpdefault,$grptype)
    {
        $territorygroup_id = false;
        try {

            $insert = array(
                'account_id' => $account_id,
                'territorygroupname' => $groupname,
                'territorytype' => $grptype,
                '`default`' => $grpdefault,
                'active' => "1"
            );

            $result = $this->db_write->insert('crossbones.territorygroup', $insert);

            if ($result) {
                $territorygroup_id = $this->db_write->lastInsertId();
            } else {
                $this->setErrorMessage("Failed to insert: crossbones.territorygroup");
            }


        } catch (\Exception $e) {
            $this->setErrorMessage($e);
        }

        return $territorygroup_id;
    }

    public function insertUserTerritoryGroup($user_id, $territorygroup_id)
    {
        $out = false;
        try {

            $insert = array(
                'territorygroup_id' => $territorygroup_id,
                'user_id' => $user_id
            );

            $result = $this->db_write->insert('crossbones.user_territorygroup', $insert);

            $out = true;
        } catch (\Exception $e) {
            $this->setErrorMessage($e);
        }

        return $out;
    }

    public function insertUserUnitgroup($unitgrp_id, $user_id)
    {
        $out = false;
        try {

            $insert = array(
                'unitgroup_id' => $unitgrp_id,
                'user_id' => $user_id
            );

            $result = $this->db_write->insert('crossbones.user_unitgroup', $insert);

            $out = true;
        } catch (\Exception $e) {
            $this->setErrorMessage($e);
        }

        return $out;
    }

    /**
     * @param $account_id
     * @param $account_name
     * @return bool|string
     */
    public function updateAccountName($account_id, $account_name)
    {
        $return = false;
        try {
            $set = array('accountname' => $account_name);
            $where = array('account_id' => $account_id);

            $result = $this->db_write->update('crossbones.account', $set, $where);

            if ($result) {
               $return = true;
            } else {
                $this->setErrorMessage("Update failed");
            }
        } catch (\Exception $e) {
            $this->setErrorMessage($e);
        }

        return $return;
    }

    /**
     * @param $user_id
     * @param $account_username
     * @return bool|string
     */
    public function updateAccountUserName($user_id, $account_username)
    {
        $return = false;
        try {
            $set = array('username' => $account_username);
            $where = array('user_id' => $user_id);

            $result = $this->db_write->update('crossbones.user', $set, $where);

            if ($result) {
                $return = true;
            } else {
                $this->setErrorMessage("Update failed");
            }
        } catch (\Exception $e) {
            $this->setErrorMessage($e);
        }

        return $return;
    }

    /**
     * @param int $account_id
     * @param array $params
     * @return bool|string
     */
    public function updateAccountInfo($account_id, $params)
    {
        $return = false;
        try {

            $result = $this->db_write->update('crossbones.account', $params, array('account_id' => $account_id));

            if ($result) {
                $return = true;
            } else {
                // Doctrin is being weird, if the data being updated is the same, it doesnt say that it updated the row.
                $return = true;
            }
        } catch (\Exception $e) {
            $this->setErrorMessage($e);
        }

        return $return;
    }

    /**
     * @param $user_id
     * @param $encodedPassword
     * @return bool|string
     */
    public function updateAccountUserPassword($user_id, $encodedPassword)
    {
        $return = false;
        try {
            $set = array('password' => $encodedPassword);
            $where = array('user_id' => $user_id);

            $result = $this->db_write->update('crossbones.user', $set, $where);

            if ($result) {
                $return = true;
            } else {
                $this->setErrorMessage("Update failed - updateAccountUserPassword");
            }
        } catch (\Exception $e) {
            $this->setErrorMessage($e);
        }

        return $return;
    }

    public function getUnitIDs($account_id)
    {
        $sql = "
            SELECT
              `unit_id`
            FROM
              crossbones.unit
            WHERE
              `account_id` = ?
        ";

        $results = $this->db_read->fetchAll($sql, array($account_id));

        return $results;
    }

    public function getDefaultGroup($account_id)
    {
        $sql = "
            SELECT
              `unitgroup_id`
            FROM
              crossbones.unitgroup
            WHERE
              `default`=1
            AND
              `account_id` = ?
        ";

        $results = $this->db_read->fetchAssoc($sql, array($account_id));

        return $results['unitgroup_id'];
    }

    public function getAccountAlerts($account_id)
    {
        $sql = "
            SELECT
              `alert_id`
            FROM
              crossbones.alert
            WHERE
              `account_id` = ?
        ";

        $results = $this->db_read->fetchAll($sql, array($account_id));

        return $results;
    }

    public function getAccountSchReports($account_id)
    {
        $sql = "
            SELECT
              `schedulereport_id`
            FROM
              crossbones.schedulereport
            WHERE
              `account_id` = ?
        ";

        $results = $this->db_read->fetchAll($sql, array($account_id));

        return $results;
    }

    public function getAccountUsers($account_id)
    {
        $sql = "
            SELECT
              `user_id`
            FROM
              crossbones.user
            WHERE
              `account_id` = ?
        ";

        $results = $this->db_read->fetchAll($sql, array($account_id));

        return $results;
    }

    public function getAccountUser($user_id)
    {
        $sql = "
            SELECT
              *
            FROM
              crossbones.user
            WHERE
              `user_id` = ?
        ";

        $results = $this->db_read->fetchAssoc($sql, array($user_id));

        return $results;
    }

    public function updateUserInfo($user_id, $params)
    {
        $result = $this->db_write->update('crossbones.user', $params, array('user_id' => $user_id));
        return true;
    }

    public function updateAccountUsersInfo($account_id, $params)
    {
        $result = $this->db_write->update('crossbones.user', $params, array('account_id' => $account_id));
        return true;
    }

    public function getAllAccounts($resellerId = 0)
    {
		if($resellerId > 1)
        {
	        $reseller_query=" WHERE account.dealer_id=$resellerId";
        }
        else
        {
	        $reseller_query="";
        }
		
        $sql = "SELECT account.account_id, account.accountname FROM account $reseller_query";

        $results = $this->db_read->fetchAll($sql);

        return $results;

    }

    public function suggestAccounts($query, $resellerId = 0)
    {
        if($resellerId > 1)
        {
	        $reseller_query=" AND account.dealer_id=$resellerId";
        }
        else
        {
	        $reseller_query="";
        }
        
        $sql = "SELECT account.account_id, account.accountname FROM account WHERE account.accountname LIKE ? $reseller_query";

        $results = $this->db_read->fetchAll($sql, array($query));

        return $results;
    }

    public function getEmailCountByAddress($email = '')
    {
        // get non deleted user email count for provided email
        $sql = "
            SELECT
              COUNT(user.email) AS count
            FROM
              crossbones.user
            WHERE
              email = ? AND userstatus_id not in (0,4,2)
        ";

        $results = $this->db_read->fetchAll($sql, array($email));

        return $results[0]['count'];
    }

    public function getUsernameCountByAddress($username = '')
    {
        // get non deleted user email count for provided email
        $sql = "
            SELECT
              COUNT(user.username) AS count
            FROM
              crossbones.user
            WHERE
              username=? AND userstatus_id not in (0,4,2)
        ";

        $results = $this->db_read->fetchAll($sql, array($username));

        return $results[0]['count'];
    }

    public function deleteAccountAlert($alert_id)
    {
        $this->db_write->delete("crossbones.alert", array('alert_id' => $alert_id));
    }

    public function deleteAlertContact($alert_id)
    {
        $this->db_write->delete("crossbones.alert_contact", array('alert_id' => $alert_id));
    }

    public function deleteAlertTerritory($alert_id)
    {
        $this->db_write->delete("crossbones.alert_territory", array('alert_id' => $alert_id));
    }

    public function deleteAlertUnit($alert_id)
    {
        $this->db_write->delete("crossbones.alert_unit", array('alert_id' => $alert_id));
    }

    public function deleteAlertHistory($alert_id)
    {
        $this->db_write->delete("crossbones.alerthistory", array('alert_id' => $alert_id));
    }

    public function deleteAlertSend($alert_id)
    {
        $this->db_write->delete("crossbones.alertsend", array('alert_id' => $alert_id));
    }

    public function deleteAccountNote($accountnote_id)
    {
        $this->db_write->delete("crossbones.accountnote", array('accountnote_id' => $accountnote_id));
    }

    public function deleteAccountContact($account_id)
    {
        $this->db_write->delete("crossbones.contact", array('account_id' => $account_id));
    }

    public function deleteAccountContactGroup($account_id)
    {
        $this->db_write->delete("crossbones.contactgroup", array('account_id' => $account_id));
    }

    public function deleteAccountReportHistory($account_id)
    {
        $this->db_write->delete("crossbones.reporthistory", array('account_id' => $account_id));
    }

    public function deleteAccountScheduleReport($account_id)
    {
        $this->db_write->delete("crossbones.schedulereport", array('account_id' => $account_id));
    }

    public function deleteSchReportContact($schedulereport_id)
    {
        $this->db_write->delete("crossbones.schedulereport_contact", array('schedulereport_id' => $schedulereport_id));
    }

    public function deleteSchReportTerritory($schedulereport_id)
    {
        $this->db_write->delete("crossbones.schedulereport_territory", array('schedulereport_id' => $schedulereport_id));
    }

    public function deleteSchReportUnit($schedulereport_id)
    {
        $this->db_write->delete("crossbones.schedulereport_unit", array('schedulereport_id' => $schedulereport_id));
    }

    public function deleteSchReportUser($schedulereport_id)
    {
        $this->db_write->delete("crossbones.schedulereport_user", array('schedulereport_id' => $schedulereport_id));
    }

    public function deleteTerritory($account_id)
    {
        $this->db_write->delete("crossbones.territory", array('account_id' => $account_id));
    }

    public function deleteTerritoryGroup($account_id)
    {
        $this->db_write->delete("crossbones.territorygroup", array('account_id' => $account_id));
    }

    public function deleteTerritoryUpload($account_id)
    {
        $this->db_write->delete("crossbones.territoryupload", array('account_id' => $account_id));
    }

    public function deleteAccountUnitGroup($account_id)
    {
        $this->db_write->delete("crossbones.unitgroup", array('account_id' => $account_id));
    }

    public function deleteUserTerritoryGroup($user_id)
    {
        $this->db_write->delete("crossbones.user_territorygroup", array('user_id' => $user_id));
    }

    public function deleteUserUnitGroup($user_id)
    {
        $this->db_write->delete("crossbones.user_unitgroup", array('user_id' => $user_id));
    }

    public function deleteuserInvitation($user_id)
    {
        $this->db_write->delete("crossbones.userinvitation", array('user_id' => $user_id));
    }

    public function deleteAccountUsers($account_id)
    {
        $this->db_write->delete("crossbones.user", array('account_id' => $account_id));
    }

    public function deleteAccount($account_id){
        $this->db_write->delete("crossbones.account", array('account_id' => $account_id));
    }
}
