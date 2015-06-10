<?php

namespace Models\Logic;

use Models\Base;

use Models\Data\AccountData;
use Models\Logic\UserLogic;
use Models\Logic\UnitLogic;

use GTC\Component\Utils\Date;
use GTC\Component\Utils\DataTableHelper;
use GTC\Component\Utils\PasswordHelper;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class AccountLogic extends Base
{
    /**
     * Container for error messages
     *
     * @var array
     */
    private $errors = array();

    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();

        $this->account_data = new AccountData;
        $this->user_logic = new UserLogic;
    }

    /**
     * @return array
     */
    public function createAccount($params)
    {
    	if($params['dealer_id'] > 0)
    	{
    		$umuserdetail=$this->user_logic->umUserDetail($params['dealer_id']);
    		$umuser_role=$umuserdetail['roles'];
    	}
    	else
    	{
	    	$umuser_role="ROLE_ADMIN";
    	}
    	
        $ajax_data = array();

        $message = "";

        $acode = 0;

        $user_id = null;

        $role = "ROLE_ACCOUNT_OWNER";

        //$datetime = new \DateTime(NULL, new \DateTimeZone('America/Los_Angeles'));
        //$createdate = $datetime->format("Y-m-d H:i:s");
        $createdate = Date::locale_to_utc(date('Y-m-d H:i:s'), SERVER_TIMEZONE);

        $account_id         = $params['account_id'];
        $account_name       = trim($params['account_name']);
        $account_email      = trim($params['account_email']);

        $account_firstname  = trim($params['account_firstname']);
        $account_lastname   = trim($params['account_lastname']);

        $account_status     = $params['account_status'];
        $account_timezone   = $params['account_timezone'];
        $account_type       = $params['account_type'];
        $account_theme      = $params['account_theme'];
        $account_address    = $params['account_address'];
        $account_phone      = $params['account_phone'];
        $account_username   = $params['account_username'];
        $account_password   = $params['account_password'];
        $account_locate     = '';
        
        if ($account_name !== null && $account_email !== null && $account_firstname !== null && $account_lastname !== null && $account_username !== null && $account_password !== null) {

            $checkAccountNameExist = $this->user_logic->umCheckAccountNameExist($account_name);

            if (!($checkAccountNameExist)) {

                $username_taken = $this->user_logic->umUsernameTaken($account_username);

                if ($username_taken !== true) {

                    $customercode = 1;
                    
                    if($umuser_role=="ROLE_RESELLER")
                    {
                    	$reseller_id = $params['dealer_id'];
                    }
                    else
                    {
                    	$reseller_id = 1;
    				}
    				
                    $account_id = $this->account_data->insertNewAccount($account_status, $reseller_id, $account_type, $account_timezone, $customercode, $account_name, $account_theme, $account_address, $account_phone, $createdate, $account_locate);

                    if (is_numeric($account_id)) {

                        $userstatus_id = 3;
                        $usertype_id = 1;

                        $user_id = $this->account_data->insertNewUser($account_id, $userstatus_id, $usertype_id, $account_timezone, $role, $account_firstname, $account_lastname, $account_email, $createdate);

                        if ( true === is_numeric($user_id) ) {

                            $groupname = "Default";
                            $grpdefault = 1;
                            $unitgrp_id = $this->account_data->insertUnitGroup($account_id, $groupname, $grpdefault);
                            if (true === is_numeric($unitgrp_id)) {

                                $insertUserUnitgroup = $this->account_data->insertUserUnitgroup($unitgrp_id, $user_id);
                                if ($insertUserUnitgroup === false) {
                                    $message = implode(',', $this->account_data->getErrorMessage()).' (error 1)';
                                    $acode = 1;
                                }

                            } else {
                                $message = implode(',', $this->account_data->getErrorMessage()).' (error 2)';
                                $acode = 1;
                            }


                            $territorygrptype = "landmark";
                            $territorygroup_id = $this->account_data->insertTerritoryGroup($account_id, $groupname, $grpdefault, $territorygrptype);
                            if (true === is_numeric($territorygroup_id)) {

                                $insertUserTerritoryGroup = $this->account_data->insertUserTerritoryGroup($user_id, $territorygroup_id);
                                if ($insertUserTerritoryGroup === false) {
                                    $message = implode(',', $this->account_data->getErrorMessage()).' (error 3)';
                                    $acode = 1;
                                }

                            } else {
                                $message = implode(',', $this->account_data->getErrorMessage()).' (error 4)';
                                $acode = 1;
                            }

                            $account_password_encoded = $this->encodePassword($account_password);
                            $this->account_data->updateUserInfo($user_id, array(
                                 'username' => $account_username
                                ,'password' => $account_password_encoded
                            ));

                            $ajax_data['user_id'] = $user_id;

                        } else {
                            $message = implode(',', $this->account_data->getErrorMessage()).' (error 5)';
                            $acode = 1;
                        }

                    } else {
                        $message = implode(',', $this->account_data->getErrorMessage()).' (error 6)';
                        $acode = 1;
                    }

                    // $message = "Account Created";

                } else {
                    $message = "Username Taken";
                    $acode = 1;
                }

            } else {
                $message = 'Duplicate Account Name';
                $acode = 1;
            }

        } else {
            $message = "Blank Fields:" ;
            $message .= " name: " . $account_name ;
            $message .= ", email: " . $account_email ;
            $message .= ", firstname: " . $account_firstname ;
            $message .= ", lastname: " . $account_lastname ;
            $message .= ", username: " . $account_username ;
            $message .= ", pswd: " . $account_password ;
            $acode = 1;
        }

        $ajax_data['code'] = $acode;
        $ajax_data['message'] = $message;
        $ajax_data['account_id'] = $account_id;

        return $ajax_data;
    }//createAccount

    /**
     * @return mixed
     */
    public function updateAccount()
    {

        $message = "";
        $acode = 0;

        $account_id         = $_POST['account_id'];
        $account_name       = trim($_POST['account_name']);
        $user_id            = $_POST['user_id'];

        if (! empty($_POST)) {

            $account_params = array();
            $accountdetail = $this->getAccountDetail($account_id);

            // Process for Account Info
            if (! empty($account_name) AND $accountdetail['accountname'] != $account_name) {
                $checkAccountNameExist = $this->checkAccountNameExist($account_name);
                if ($checkAccountNameExist === true) {
                    $account_params['accountname'] = $account_name;
                }
            }
            if (isset($_POST['account_status']) AND ! empty($_POST['account_status'])) {
                $account_params['accountstatus_id'] = $_POST['account_status'];
            }
            if (isset($_POST['account_timezone']) AND ! empty($_POST['account_timezone'])) {
                $account_params['timezone_id'] = $_POST['account_timezone'];
                $all_users_params['timezone_id'] = $_POST['account_timezone'];
            }
            if (isset($_POST['account_type']) AND ! empty($_POST['account_type'])) {
                $account_params['accounttype_id'] = $_POST['account_type'];
            }
            if (isset($_POST['account_theme']) AND ! empty($_POST['account_theme'])) {
                $account_params['theme'] = $_POST['account_theme'];
            }
            if (isset($_POST['account_address']) AND ! empty($_POST['account_address'])) {
                $account_params['address'] = $_POST['account_address'];
            }
            if (isset($_POST['account_phone']) AND ! empty($_POST['account_phone'])) {
                $account_params['phonenumber'] = $_POST['account_phone'];
            }
            if (isset($_POST['account_locate']) AND ! empty($_POST['account_locate'])) {
                $account_params['locate'] = 'Y';
            } else {
                $account_params['locate'] = '';
            }
            if (isset($_POST['account_id']) AND ! empty($_POST['account_id']) AND ! empty($account_params)) {
                // update account info
                $update = $this->account_data->updateAccountInfo($account_id, $account_params);
            }

            // Process for User update info for this account owner
            $user_params = array();
            if (isset($_POST['account_firstname']) AND ! empty($_POST['account_firstname'])) {
                $user_params['firstname'] = trim($_POST['account_firstname']);
            }
            if (isset($_POST['account_lastname']) AND ! empty($_POST['account_lastname'])) {
                $user_params['lastname'] = trim($_POST['account_lastname']);
            }
            if (isset($_POST['account_email']) AND ! empty($_POST['account_email'])) {
                $user_params['email'] = trim($_POST['account_email']);
            }
            $username_taken = $this->user_logic->usernameTaken($_POST['account_username']);
            if ($username_taken !== true) {
                if (isset($_POST['account_username']) AND ! empty($_POST['account_username'])) {
                    $user_params['username'] = $_POST['account_username'];
                }
            }
            if (isset($_POST['account_password']) AND ! empty($_POST['account_password'])) {
                $account_password_encoded = $this->user_logic->encodePassword($_POST['account_password']);
                $user_params['password'] = $account_password_encoded;
            }
            if (isset($_POST['user_id']) AND ! empty($_POST['user_id']) AND ! empty($user_params)) {
                // update user info
               $result = $this->account_data->updateUserInfo($_POST['user_id'], $user_params);
                $ajax_data['user_id'] = $_POST['user_id'];
            }
            
            if (isset($_POST['account_id']) AND ! empty($all_users_params)) {
                // update all account users info
               $result = $this->account_data->updateAccountUsersInfo($_POST['account_id'], $all_users_params);
            }

            $acode = 0;
            $message = "Saved";
        } else {
            $message = "Blank Feilds";
            $acode = 1;
        }

        $ajax_data['code'] = $acode;
        $ajax_data['message'] = $message;
        $ajax_data['account_id'] = $account_id;
        $ajax_data['account_name'] = $account_name;

        return $ajax_data;
    }//updateAccount

    /**
     * Needs to be moved into GTC\User at some point
     *
     * @param $account_password
     * @return string
     */
    private function encodePassword($account_password)
    {
        //$salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
        //$encodedPassword =  crypt($account_password, '$6$rounds=5000$'. $salt .'$');

        $encoder = new MessageDigestPasswordEncoder('sha512', true, 5000);
        $encodedPassword = $encoder->encodePassword($account_password, null);

        return $encodedPassword;
    }

    /**
     * Checks to see if there are any user accounts with the same user name
     *
     * @param $account_username
     * @return null|string
     */
    private function checkAccountUsernameExist($account_username)
    {
        $error = null;

        $searchAccountUserName = $this->account_data->searchForSingleAccountUserName($account_username);

        if ($searchAccountUserName !== null) {
            if (@$searchAccountUserName[0]['username'] == $account_username) {
                $error = "Duplicate User Name";
            }
        }

        return $error;
    }

    /**
     * Checks to see if there are any other Account names that are the same
     *
     * @param $account_name
     * @return null|string
     */
    private function checkAccountNameExist($account_name)
    {
        $return = true;

        $searchAccountName = $this->account_data->searchForSingleAccountName($account_name);

        if ($searchAccountName !== null) {
            if (@$searchAccountName[0]['accountname'] == $account_name) {
                $this->setErrorMessage("Duplicate Account Name");
                $return = false;
            }
        }

        return $return;
    }

    /**
     * Main account name search
     *
     * @param $params
     * @return mixed
     */
    public function search($params)
    {

        $total_accounts = array();
        $accounts['iTotalRecords']          = 0;
        $accounts['iTotalDisplayRecords']   = 1;
        $accounts['data']                   = array();

        $search_string = "crossbones";
        if (isset($params['sSearch']) AND $params['sSearch'] != '') {
            //TODO needed to be changed to Forms/Request
            $search_string = $params['sSearch'];
        } else {
            $search_string = '%'; // % == all
        }

        $result = $this->account_data->search($search_string, $params['iDisplayStart'], $params['iDisplayLength'], (isset($params['resellerId']) ? $params['resellerId'] : 0));
        if(isset($params['resellerId'])){
            $total  = $this->account_data->getNumberOfAccountsByAccountStatusId($params['resellerId']);    
        } else {
            $total  = $this->account_data->getNumberOfAccountsByAccountStatusId(0);

        }
        
        //print_rb($total[0]['account_total']);


        //$accounts['iTotalRecords']          = count($result);
        $accounts['iTotalRecords']          = $total[0]['account_total'];
        //$accounts['iTotalDisplayRecords']   = count($result);
        $accounts['iTotalDisplayRecords']   = $total[0]['account_total'];

        $accounttypes = $this->getTypeList();
        
        foreach($result as $k => $v){
            $accounttypeid = $result[$k]['accounttype_id'];

            $result[$k]['accounttype_id'] = isset($accounttypes[$accounttypeid]) ? $accounttypes[$accounttypeid] : '';
        }

        $result = DataTableHelper::sortDataTables($params,$result);



        $accounts['data'] = $result;

        return $accounts;
    }

    public function searchSuggest($query = '', $resellerId = 0)
    {
        if ( $query == '') {

            $result = $this->account_data->getAllAccounts($resellerId);

        } else {

            $result = $this->account_data->suggestAccounts($query, $resellerId);

        }

        return $result;
    }

    public function emailIsValid($email = '')
    {
        $output = FALSE;

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $output = TRUE;
        }

        return $output;

    }

    public function emailAddressIsInUse($email = '')
    {

        $output = FALSE;
        $count  = $this->account_data->getEmailCountByAddress($email);

        if ($count > 0) {
            $output = TRUE;
        }

        return $output;
    }

    public function usernameAddressIsInUse($username = '')
    {

        $output = FALSE;
        $count  = $this->account_data->getUsernameCountByAddress($username);

        if ($count > 0) {
            $output = TRUE;
        }

        return $output;
    }

    /**
     * Return a limited array of timezones for a dropdown
     *
     * @return array
     */
    public function getTimezones()
    {
        //This is the list of ID's where going to show
        $list = array(470,471,472,473,474,475,476,477,478,479,480,481,469);

        $out = array();

        $result = $this->account_data->getAllTimezones();

        foreach ($result as $k => $v) {
            //echo "$k = $v[timezone_id]<br>";

            if (in_array($v['timezone_id'], $list)) {
                $out[$v['timezone_id']] = $v['timezone'];
            }
        }

        return $out;
    }

    /**
     * Return a limited array of timezones for a dropdown
     *
     * @return array
     */
    public function getThemes()
    {
        $themes  = array(
            0 => 'crossbones',
            1 => 'fleet',
            2 => 'positionplus'
        );

        return $themes;
    }

    /**
     * Returns a list of possible account status
     *
     * @return array
     */
    public function getListAccountStatus()
    {
        $out = array();

        $result = $this->account_data->getListAccountStatus();

        foreach ($result as $k => $v) {
                $out[$v['accountstatus_id']] = $v['accountstatusname'];
        }

        return $out;

    }

    public function getUnitCount($account_id)
    {
        $result = $this->account_data->getUnitIDs($account_id);

        return count($result);
    }

    /**
     * Main account array, will return all uppper part of the account page.
     *
     * @param $account_id
     * @return mixed
     */
    public function getAccountDetail($account_id)
    {

        $result = $this->account_data->getAccountDetail($account_id);

        //print_r($result);
        //exit;

        //TODO hardcoded accounttype

        //This is just in case something is really messed up, and for twig not to freak out.
        if(isset($result['accounttype']) === false)       { $result['accounttype'] = "Customer"; }
        if(isset($result['accountstatus_id']) === false)  { $result['accountstatus_id'] = "1"; }
        if(isset($result['accountstatusname']) === false) { $result['accountstatusname'] = "Active"; }
        if(isset($result['user_id']) === false)           { $result['user_id'] = "0"; }
        if(isset($result['timezone_id']) === false)       { $result['timezone_id'] = "146"; }
        if(isset($result['timezone']) === false)          { $result['timezone'] = "US/Pacific"; }
        if(isset($result['accounttype_id']) === false)    { $result['accounttype_id'] = "1"; }


        return $result;
    }

    /**
     * This is used to stup a brand new account
     *
     * @return array
     */
    public function defaultAccountDetail()
    {
        $core =& get_instance();

        $detail = array();

        //$detail['accountname'] = "Create a new Account.";
        $detail['accountstatus_id'] = "1";
        $detail['accountstatusname'] = "Active";
        $detail['timezone_id'] = "146";
        $detail['timezone'] = "US/Pacific";
        $detail['accounttype_id'] = "1";
        $detail['accounttype'] = "Customer";
        $detail['firstname'] = "";
        $detail['lastname'] = "";
        $detail['user_id'] = "";
        $detail['theme'] = $core->config['parameters']['default_theme'];

        return $detail;

    }

    /**
     * Returns a list of all the account types
     *
     * @return array
     */
    public function getTypeList()
    {
        $return = array();
        
        $result = $this->account_data->getListAccountTypes();
        
        if (! empty($result)) {
            foreach ($result as $k => $v) {
                $return[$v['accounttype_id']] = $v['accounttype'];
            }
        }

        return $return;
    }

    public function getDefaultGroup($account_id)
    {
        return $this->account_data->getDefaultGroup($account_id);
    }

    public function getAccountAlerts($account_id)
    {
        $out = array();

        $result = $this->account_data->getAccountAlerts($account_id);

        foreach ($result as $k => $v) {
            $out[] = $v['alert_id'];
        }

        return $out;
    }

    public function getAccountSchReports($account_id)
    {
        $out = array();

        $result = $this->account_data->getAccountSchReports($account_id);

        foreach ($result as $k => $v) {
            $out[] = $v['schedulereport_id'];
        }

        return $out;
    }

    public function getAccountUsers($account_id)
    {
        $out = array();

        $result = $this->account_data->getAccountUsers($account_id);

        foreach ($result as $k => $v) {
            $out[] = $v['user_id'];
        }

        return $out;
    }

    public function getAccountUser($user_id)
    {
        return $this->account_data->getAccountUser($user_id);
    }

    public function deleteAccount($params)
    {
        $this->unit_logic = new UnitLogic;
        
        $out = array();

        $account_id = $params['accountId'];

        $baseaccount = $this->account_data->getAccountDetail($account_id);
        $accountnote_id = $baseaccount['accountnote_id'];
        $this->account_data->deleteAccountNote($accountnote_id);


        // delete alert related info for this account
        $arrayOfAlertIDs = $this->getAccountAlerts($account_id);
        if (count($arrayOfAlertIDs) >= 1) {
            foreach($arrayOfAlertIDs as $k => $alert_id){
                $this->account_data->deleteAccountAlert($alert_id);
                $this->account_data->deleteAlertContact($alert_id);
                $this->account_data->deleteAlertTerritory($alert_id);
                $this->account_data->deleteAlertUnit($alert_id);
                $this->account_data->deleteAlertHistory($alert_id);
                $this->account_data->deleteAlertSend($alert_id);
            }
        }
        $this->account_data->deleteAccountContact($account_id);
        $this->account_data->deleteAccountContactGroup($account_id);        


        // delete schedule report related info for this account
        $arrayOfSchReportIDs = $this->getAccountSchReports($account_id);
        if (count($arrayOfSchReportIDs) >= 1) {
            foreach($arrayOfSchReportIDs as $k => $schedulereport_id){
                $this->account_data->deleteSchReportContact($schedulereport_id);
                $this->account_data->deleteSchReportTerritory($schedulereport_id);
                $this->account_data->deleteSchReportUnit($schedulereport_id);
                $this->account_data->deleteSchReportUser($schedulereport_id);
            }
        }
        $this->account_data->deleteAccountScheduleReport($account_id);
        $this->account_data->deleteAccountReportHistory($account_id);


        // delete territories and unit related info for this account and the account users
        $arrayOfUserIDs = $this->getAccountUsers($account_id);
        if (count($arrayOfUserIDs) >= 1) {
            foreach($arrayOfUserIDs as $k => $user_id){
                $this->account_data->deleteUserTerritoryGroup($user_id);
                $this->account_data->deleteUserUnitGroup($user_id);
                $this->account_data->deleteuserInvitation($user_id);
            }
        }
        $this->account_data->deleteTerritory($account_id);
        $this->account_data->deleteTerritoryGroup($account_id);
        $this->account_data->deleteTerritoryUpload($account_id);


        // delete all unit related info for this account
        $arrayofAccountUnitIDs = $this->unit_logic->getAllAccountUnitIds($account_id);
        if (count($arrayofAccountUnitIDs) >= 1) {
            foreach($arrayofAccountUnitIDs as $k => $unit){
                $this->unit_logic->deleteUnit($unit);
            }
        }
        $this->account_data->deleteAccountUnitGroup($account_id);
        
        // delete all users for this account
        $this->account_data->deleteAccountUsers($account_id);
        
        // delete this Account
        $this->account_data->deleteAccount($account_id);

        return $out;
    }

    public function getSudoUrl($account_id)
    {
        $account_detail = $this->account_data->getAccountDetail($account_id);

        if (empty($account_detail)) {
            return false;
        }

        $token = $this->user_logic->resetSudo($account_detail['user_id']);

        $url = $account_detail['url_protocol'].'://'.$account_detail['url'].'/sudo/'.$token;

        return $url;
    }

    public function getDemoUrl()
    {
        $account_detail = $this->account_data->getAccountDetail(1);

        if (empty($account_detail)) {
            return false;
        }

        $token = $this->user_logic->resetSudo($account_detail['user_id']);

        $url = $account_detail['url_protocol'].'://'.$account_detail['url'].'/demo/'.$token;
		
        return $url;
    }
}
