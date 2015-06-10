<?php

namespace Models\Logic;

use Models\Logic\BaseLogic;
use Models\Data\UserData;
use Models\Logic\AddressLogic;
use GTC\Component\Utils\Arrayhelper;
use Swift\Transport\Validate;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Models\Logic\VehicleLogic;
use Models\Logic\TerritoryLogic;
use Symfony\Component\HttpFoundation\Request;
use GTC\Component\Form\Validation;

class UserLogic extends BaseLogic
{
    private $permission_dependency = array(
                                        '5' => array(2,9), // 5 = vehicle group permission; 2 = edit, 9 = view
                                        '6' => array(3,10) // 6 = landmark group permission; 3 = create/edit, 10 = view
                                        );

    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();

        $this->user_data     = new UserData;
        $this->address_logic = new AddressLogic;
        $this->contact_logic = new ContactLogic;
        $this->vehicle_logic = new VehicleLogic;
        $this->territory_logic = new TerritoryLogic;
        $this->validator = new Validation;
    }

    /**
     */
    public function getLegalId($user_id)
    {
        $result = $this->user_data->getLegalId($user_id);

        return $result;
    }

    /**
     */
    public function setLegalId($user_id,$legal_id)
    {
        $result = $this->user_data->setLegalId($user_id,$legal_id);

        return $result;
    }

    /**
     */
    public function umCheckAccountNameExist($account_name)
    {
        $result = $this->user_data->umCheckAccountNameExist($account_name);
        
        return $result;
    }

    /**
     */
    public function umUserDetail($user_id)
    {
        $result = $this->user_data->umUserDetail($user_id);
        
        return $result;
    }

    /**
     */
    public function umUserNameTaken($account_username)
    {
        $result = $this->user_data->umUserNameTaken($account_username);
        
        return $result;
    }

    /**
     * Support Ajax User Account Check Requests
     */
    public function ajaxEmailCheck($email) {

        if($email){
            $result = $this->user_data->ajaxEmailCheck($email);
        } else {
            $result['error'] = 'Email Address is Missing' ;
        }
        return $result ;

    }

    /**
     * Support Ajax User Account Check Requests
     */
    public function ajaxUserCheck($username) {

        if($username){
            $result = $this->user_data->ajaxUserCheck($username);
        } else {
            $result['error'] = 'User Name is Missing' ;
        }
        return $result ;

    }

    /**
     * Support Ajax User Account Create Requests
     */
    public function ajaxUserCreate($user,$post) {

        $params = array(
            'account_id'        => $user['account_id'],
            'userstatus_id'     => 1,
            'usertype_id'       => $post['usertype_id'],
            'timezone_id'       => $user['timezone_id'],
            'roles'             => 'ROLE_ACCOUNT_USER',
            'vehiclegroups'     => $post['vehiclegroups'],
            'firstname'         => $post['firstname'],
            'lastname'          => $post['lastname'],
            'email'             => $post['email'],
            'username'          => $post['username'],
            'password'          => $post['password']
        );

        $addUser = $this->addUser($params);

        return implode(', ', $addUser);

        // $post['createdate'] = date ('Y-m-d H:i:s') ;

        // // set up contact info
        // $contact_params = array(
        //     'account_id'        => $user['account_id'],
        //     'firstname'         => $post['firstname'],
        //     'lastname'          => $post['lastname'],
        //     'email'             => $post['email'],
        //     'contactstatus'     => 'pending',
        //     'createdate'        => $post['createdate']
        // );

        // $params = $contact_params;

        // // unset cellnumber and carrier before inserting the new record into user table
        // if (isset($post['cellnumber'])) {
        //     $contact_params['cellnumber'] = $post['cellnumber'];
        //     unset($post['cellnumber']);     
        // }
        
        // if (isset($post['cellcarrier_id'])) {
        //     $contact_params['cellcarrier_id'] = $post['cellcarrier_id'];
        //     unset($post['cellcarrier_id']);
        // }
        
        // $raw_password = $post['password'];
        
        // // encrypt password before saving
        // $params['password'] = $this->encodePassword($post['password']);
        
        // $user_id = $this->user_data->addUser($params);
        // if (! empty($user_id)) {

        //     if (! empty($contact)) {    // if a contact with the same user email exist, associate it to this user
        //         $this->contact_logic->updateContactInfo($contact['contact_id'], array('user_id' => $user_id));
        //     } else {                    // else if it doesn't exist or it's already associated to another user, create another contact record with this email and associate it to this new user
        //         $contact_params['user_id'] = $user_id;
        //         $this->contact_logic->addContact($contact_params);
        //     }

        //     return $this->sendUserLoginInfo($user_id, $params['email'], $params['username'], $raw_password, "{$params['firstname']} {$params['lastname']}");
        // }


        // $post['password'] = $this->encodePassword($post['password']);
        // if($post['email']){
        //     $result = $this->user_data->ajaxUserCreate($user,$post);
        // }
        // return $result ;

    }

    /**
     * Support Ajax User Type Check Requests
     */
    public function ajaxUserTypeCheck($usertype) {

        if($usertype){
            $result = $this->user_data->ajaxUserTypeCheck($usertype);
        } else {
            $result['error'] = 'User Type Name is Missing' ;
        }
        return $result ;

    }

    /**
     * Support Ajax User Type Create Requests
     */
    public function ajaxUserTypeCreate($user,$post) {

        if($post['usertype']){
            $result = $this->user_data->ajaxUserTypeCreate($user,$post);
        }
        return $result ;

    }
                                                    
    /**
     * Support Ajax User Type Update Requests
     */
    public function ajaxUserTypeUpdate($user,$post) {

        if(($post['usertype'])&&($post['usertype_id'])){
            $result = $this->user_data->ajaxUserTypeUpdate($user,$post);
        }
        return $result ;

    }
                                                    
    /**
     * Get all user groups/types
     *
     * @param int $account_id
     *
     * @return array|bool
     */
    public function getUserTypesByAccountId($account_id)
    {
        $this->validator->validate('record_id', $account_id);
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }
        
        if (! $this->hasError()) {
            //return array();
            return $this->user_data->getUserTypesByAccountId($account_id);
        }

        return false;
    }
                                                    
    /**
     * Get all users
     *
     * @param int $account_id
     *
     * @return array|bool
     */
    public function getUsersByAccountId($account_id)
    {
        $this->validator->validate('record_id', $account_id);
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }
        
        if (! $this->hasError()) {
            //return array();
            return $this->user_data->getUsersByAccountId($account_id);
        }

        return false;
    }

    /**
     * Get all permission category
     *
     * @param bool
     *
     * @return array|bool
     */
    public function getPermissionCategory($include_permissions = false)
    {
        $catergories = $this->user_data->getPermissionCategory();
        if ($catergories !== false AND ! empty($catergories) ) {
            if ($include_permissions) {
                // get all permission per type
                foreach ( $catergories as $key => $category) {
                    $catergories[$key]['permissions'] = $this->user_data->getPermissionByCategoryId($category['permissioncategory_id']);
                }
            }
            
            return $catergories;
        }

        return array();
    }

    /**
     * Get all permissions for a user
     *
     * @return array
     */
    public function getPermissions($account_id,$user_id)
    {
        return $this->user_data->getPermissions($account_id,$user_id);
    }

    /**
     * Get the filtered contacts by provided params
     *
     * @params: int $account_id
     * @params: array $params
     *
     * @return array
     */
    public function getFilteredUsers($account_id, $params)
    {
        $total_users = array();
        $users['iTotalRecords']          = 0;
        $users['iTotalDisplayRecords']   = 0;
        $users['data']                   = array();

        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('Invalid Parameters');
        } else {
            // validate search string (need to handle displaying the error message in the UI)
            if (isset($params['search_string']) AND $params['search_string'] !== '') {
                $this->validator->validate('alpha', $params['search_string']);
            }
        }
        
        $this->validator->validate('record_id', $account_id);
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
    
            switch ($params['filter_type']) {
                
                case 'string_search':
                
                    $searchfields = array('`user`.firstname', '`user`.lastname');
                    $result = $this->user_data->getFilteredUsersStringSearch($account_id, $params, $searchfields);
                    if ($result !== false) {
                        $total_users = $result;
                    }

                break;
                
                case 'group_filter':

                    if (isset($params['usertype_id']) AND strtolower($params['usertype_id']) == 'all') {
                        $params['usertype_id'] = array();
                    } else {
                        $params['usertype_id'] = array($params['usertype_id']);
                    }

                    if (isset($params['userstatus_id']) AND strtolower($params['userstatus_id']) == 'all') {
                        $params['userstatus_id'] = array();
                    } else {
                        $params['userstatus_id'] = array($params['userstatus_id']);
                    }

                    if (isset($params['user_status']) AND strtolower($params['user_status']) == 'all') {
                        $params['user_status'] = '';
                    }

                    $result = $this->user_data->getFilteredUsers($account_id, $params);
                    if ($result !== false) {
                        $total_users = $result;
                    }
                
                break;
                
                default:

                break;
            }
   
            // for the formatted unit events, process for datatable return results
            if (! empty($total_users)) {

                // init total results
                $iTotal                             = count($total_users);
                $iFilteredTotal                     = count($total_users);
                $users['iTotalRecords']          = $iTotal;
                $users['iTotalDisplayRecords']   = $iFilteredTotal;
                $aColumns                           = array();        // datatable columns event field/key names
                
                for ( $i = 0 ; $i < intval( $params['iColumns'] ) ; $i++ ) {
                    $aColumns[] = $params['mDataProp_'.$i];
                }

                // if doing a string search in filter search box
                if ( isset($params['string_search']) AND $params['string_search'] != "" ) {
                    $total_users = $this->filterUsersStringSearch($params['string_search'], $aColumns, $total_users);
                    $iTotal         = count($total_users);
                    $iFilteredTotal = count($total_users);
                }

                $users['iTotalRecords'] = $iTotal;
                $users['iTotalDisplayRecords'] = $iFilteredTotal;
        
                $formatted_results = array();
                if (! empty($total_users)) {
                    foreach ($total_users as $user) {
                        $row = $user;
                        $row['name'] = $row['user_fullname'];
                        //test place holder
                        //$test_hour = rand(1,20);
                        //$row['lastlogin'] = date('h:i A m/d/Y', mktime(date('H') - $test_hour, date('i'), 0, date('m'), date('d') - $test_hour, date('Y')));
                        
                        $row['DT_RowId'] = 'user-tr-'.$row['user_id'];       // automatic tr id value for dataTable to set

                        if ($row['name'] == '' OR is_null($row['name'])){
                            $row['name'] = $params['default_value'];
                        }

                        if (empty($row['cellnumber'])) {
                            $row['cellnumber'] = $params['default_value'];
                        }

                        /* user type stub (-Tom) */
                        $row['type'] = 'Default';


                        $formatted_results[] = $row;
                    }

                    // if doing a column sorting
                    if ( isset( $params['iSortCol_0'] ) AND $params[ 'bSortable_'.intval($params['iSortCol_0']) ] == "true") {
                        $formatted_results = $this->filterUsersSort($aColumns[ intval($params['iSortCol_0']) ], ($params['sSortDir_0']==='asc' ? 'asc' : 'desc'), $formatted_results);
                    }

                    // if doing paging, find correct page list
                    if ( isset($params['iDisplayStart']) AND $params['iDisplayLength'] != '-1' ) {
                        $formatted_results = array_splice($formatted_results, $params['iDisplayStart'], $params['iDisplayLength']);
                    }
                }

                $users['data'] = $formatted_results;
            }
        }

        return $users;
    }

    /**
     * Return contacts having sorted by column field by sort order
     *
     * @params: string $column_name
     * @params: string $sort_order
     * @params: array contact
     *
     * @return array $results
     */
    public function filterUsersSort($column_name, $sort_order, $users)
    {
        $results = $users;
        $sorting_order = '<';       // ascending sort by default
        if ( $sort_order == 'desc') {
            $sorting_order = '>';       // descending sort
        }

        if ( isset($column_name) AND $column_name != "" ) {
            switch($sorting_order) {
                case '<':
                    usort($results, Arrayhelper::usort_compare_asc($column_name));
                break;
                case '>':
                    usort($results, Arrayhelper::usort_compare_desc($column_name));
                break;
            }
        }
        
        return $results;
    }

    /**
     * Get the filtered contacts by provided params
     *
     * @params: int $account_id
     * @params: array $params
     *
     * @return array
     */
    public function getFilteredUserTypeList($account_id, $params)
    {
        $total_types = array();
        $types['iTotalRecords']          = 0;
        $types['iTotalDisplayRecords']   = 0;
        $types['data']                   = array();


        if (! is_array($params) OR empty($params)) {
           $this->setErrorMessage('Invalid Parameters');
        } else {
            // validate search string (need to handle displaying the error message in the UI)
            if (isset($params['search_string']) AND $params['search_string'] !== '') {
                $this->validator->validate('alphanumeric', $params['search_string']);
            }
        }
        
        $this->validator->validate('record_id', $account_id);
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            $searchfields = array('usertype');
            $result = $this->user_data->getFilteredUserTypeStringSearch($account_id, $params, $searchfields);
            if ($result !== false AND ! empty($result) AND $result[0]['usertype_id'] !== NULL) {
                $total_types = $result;
            }

            // for the formatted unit events, process for datatable return results
            if (! empty($total_types)) {

                // init total results
                $iTotal                             = count($total_types);
                $iFilteredTotal                     = count($total_types);
                $types['iTotalRecords']          = $iTotal;
                $types['iTotalDisplayRecords']   = $iFilteredTotal;
                $aColumns                           = array();        // datatable columns event field/key names
                
                for ( $i = 0 ; $i < intval( $params['iColumns'] ) ; $i++ ) {
                    $aColumns[] = $params['mDataProp_'.$i];
                }

                $types['iTotalRecords'] = $iTotal;
                $types['iTotalDisplayRecords'] = $iFilteredTotal;
        
                $formatted_results = array();
                if (! empty($total_types)) {
                    foreach ($total_types as $type) {
                        $row = $type;
                        
                        $row['DT_RowId'] = 'usertype-tr-'.$row['usertype_id'];       // automatic tr id value for dataTable to set

                        $formatted_results[] = $row;
                    }

                    // if doing a column sorting
                    if ( isset( $params['iSortCol_0'] ) AND $params[ 'bSortable_'.intval($params['iSortCol_0']) ] == "true") {
                        $formatted_results = $this->filterUsersSort($aColumns[ intval($params['iSortCol_0']) ], ($params['sSortDir_0']==='asc' ? 'asc' : 'desc'), $formatted_results);
                    }

                    // if doing paging, find correct page list
                    if ( isset($params['iDisplayStart']) AND $params['iDisplayLength'] != '-1' ) {
                        $formatted_results = array_splice($formatted_results, $params['iDisplayStart'], $params['iDisplayLength']);
                    }
                }

                $types['data'] = $formatted_results;
            }
        }
        return $types;
    }
    
    /**
     * Add user to the account
     *
     * @param array params
     *
     * @return bool|int
     */
    public function addUser($params)
    {

        $this->validator->validate('record_id', $params['account_id']);

        if (! is_array($params) OR (count($params) < 1)) {
            $this->setErrorMessage('Invalid parameters');
        } else {
            $this->validator->validate('record_id', $params['account_id']);
        
            if (! empty($params['email'])) {
                // $this->validator->validate('email', $params['email']);                                                          
                if ($this->validator->hasError()) {                                                                         // 1st - check email for valid formatting
                    $this->setErrorMessage($this->validator->getErrorMessage());    
                } else {                                                                                                    // 2nd - check email for duplication
                    // $email = $this->user_data->getUserByEmail($params['email']);
                    // if (empty($email)) {
                    //     $contact = $this->contact_logic->getContactByEmail($params['email']);
                    //     if (! empty($contact) AND is_array($contact)) {                                                     // 3rd - check email to make sure that a contact can be created/updated to associate to this user
                    //         $contact = array_pop($contact);        
                    //         if (! empty($contact['user_id'])) {                                                             // if an existing contact with this email has already been assigned to another user, create a new contact record for this user
                    //             unset($contact);
                    //         }
                    //     }
                    // } else {
                    //     $this->setErrorMessage('Duplicate email');
                    // }
                }                    
            } else {
                // $this->setErrorMessage('Email cannot be empty');
            }
            
            if (!($params['firstname'])) { $params['firstname']='[First Name]'; }
            // $this->validator->validate('first_last_name', $params['firstname']);
            
            if (!($params['lastname'])) { $params['lastname']='[Last Name]'; }
            // $this->validator->validate('first_last_name', $params['lastname']);
            
            $this->validator->validate('record_id', $params['usertype_id']);                                                // 4th - validate user type id
            
            if (! empty($params['username'])) {                                                                             // 5th - validate username
                // $this->validator->validate('username', $params['username']);
                if (! $this->validator->hasError()) {
                    $duplicate = $this->user_data->getUserByUsername($params['username']);
                    if (! empty($duplicate)) {
                        $this->setErrorMessage('This username has already been taken');
                    }                    
                }        
            } else {
                $this->setErrorMessage('Username cannot be empty');
            }
            
            if (! empty($params['password'])) {                                                                             // 6th - validate password
                // $this->validator->validate('password', $params['password']);    
            } else {
                $this->setErrorMessage('Password cannot be empty');
            }
    
            if (! empty($params['cellnumber']) AND ! empty($params['cellcarrier_id'])) {                                    // 7th - validate sms number and sms carrier (if provided)
    
                $params['cellnumber'] = $this->address_logic->formatPhoneForSaving($params['cellnumber']);
                
                if (! is_numeric($params['cellnumber']) OR strlen($params['cellnumber']) !== 10) {
                    $this->setErrorMessage('This SMS Number is invalid');
                }
                
                if (! is_numeric($params['cellcarrier_id']) OR $params['cellcarrier_id'] <= 0) {
                    $this->setErrorMessage('This SMS Carrier is invalid');
                }    
            }
                        
        }
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {

            // get account info
            $account = $this->user_data->getAccountInfo($params['account_id']);
            
            if (! empty($account) AND is_array($account)) {
                $account = array_pop($account);
            }
            
            // set user role to ROLE_ACCOUNT_USER
            $params['roles'] = 'ROLE_ACCOUNT_USER';
            
            // set userstatus to PENDING
            $params['userstatus_id'] = 1;
            
            // set timezone to account time zone
            $params['timezone_id'] = (! empty($account['timezone_id'])) ? $account['timezone_id'] : 0;
            
            // set date created
            $params['createdate'] = date('Y-m-d H:i:s');

            // set up contact info
            $contact_params = array(
                'account_id'        => $params['account_id'],
                'firstname'         => $params['firstname'],
                'lastname'          => $params['lastname'],
                'email'             => $params['email'],
                'contactstatus'     => 'pending',
                'createdate'        => $params['createdate']
            );

            // unset cellnumber and carrier before inserting the new record into user table
            if (isset($params['cellnumber'])) {
                $contact_params['cellnumber'] = $params['cellnumber'];
                unset($params['cellnumber']);     
            }
            
            if (isset($params['cellcarrier_id'])) {
                $contact_params['cellcarrier_id'] = $params['cellcarrier_id'];
                unset($params['cellcarrier_id']);
            }
            
            $raw_password = $params['password'];
            
            // encrypt password before saving
            $params['password'] = $this->encodePassword($params['password']);
            
            // save vehiclegroup data for later
            $vehiclegroups = $params['vehiclegroups'];
            unset($params['vehiclegroups']);

            $user_id = $this->user_data->addUser($params);
            
            if (! empty($user_id)) {

                if (! empty($contact)) {    // if a contact with the same user email exist, associate it to this user
                    $this->contact_logic->updateContactInfo($contact['contact_id'], array('user_id' => $user_id));
                } else {                    // else if it doesn't exist or it's already associated to another user, create another contact record with this email and associate it to this new user
                    $contact_params['user_id'] = $user_id;
                    $this->contact_logic->addContact($contact_params);
                    foreach ($vehiclegroups as $k => $vg) {
                        if($vg>0){
                            $cnt++;
                            $this->user_data->addUserToVehicleGroup($user_id,$vg);
                        }
                    }
                    $vehiclegroups[] = 'new groups for ' . $user_id . ' attempted ' . $cnt ;
                    // return $vehiclegroups;
                }

                if($params['email']){
                    return $this->sendUserLoginInfo($user_id, $params['email'], $params['username'], $raw_password,$params['firstname'] . " " . $params['lastname']);                
                }
                return true;

            }
            
            //return $this->user_data->addUser($params);
        }
        // return false;    
        return 'ERROR: ' . $this->getErrorMessage();
    }

    /**
     * Update user info
     *
     * @param int   user_id
     * @param array params
     *
     * @return bool
     */
    public function updateUserInfo($user_id, $params)
    {
        $this->validator->validate('record_id', $user_id);
        
        if (! is_array($params) OR (count($params) < 1)) {
            $this->setErrorMessage('err_params');
        } else {
            if (! empty($params['email'])) {
                if (! \Swift_Validate::email($params['email'])) {   // 1st - check to see if email is valid
                    $this->setErrorMessage('Email is invalid');    
                } else {                                            // 2nd - check if email is duplicated
                    $email = $this->user_data->getUserByEmail($params['email']);
                    if (! empty($email)) {
                        $this->setErrorMessage('Email is duplicated');    
                    }        
                }                   
            }
            
            if (isset($params['firstname'])) {
                $this->validator->validate('first_last_name', $params['firstname']);
            }
            
            if (isset($params['lastname'])) {
                $this->validator->validate('first_last_name', $params['lastname']);
            }
            
            if (isset($params['username'])) {
                $this->validator->validate('username', $params['username']);
            }
            
            if (isset($params['password'])) {
                $this->validator->validate('password', $params['password']);
                if (! $this->validator->hasError()) {
                    $params['password'] = $this->encodePassword($params['password']);    
                }
            }
            
            if (isset($params['userstatus_id'])) {
                // userstatus_id is a 'natural_number' type instead of 'record_id' because it can be 0 (Deleted User Status)
                $this->validator->validate('natural_number', $params['userstatus_id']);
            }
        }
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }
        
        if (! $this->hasError()) {
            return $this->user_data->updateUserInfo($user_id, $params);
        }
        return false;    
    }

    /**
     * Delete user
     *
     * @param int   user_id
     *
     * @return bool
     */
    public function deleteUser($user_id)
    {
        $this->validator->validate('record_id', $user_id);
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }
        
        if (! $this->hasError()) {
            return $this->user_data->deleteUser($user_id);
        }
        return false;    
    }

    /**
     * Get user by user id
     *
     * @param int   user_id
     *
     * @return bool
     */
    public function getUserById($user_id, $include_vehiclegroups = false, $include_landmarkgroups = false)
    {
        $this->validator->validate('record_id', $user_id);
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }        
        
        if (! $this->hasError()) {
            //return $this->user_data->getUserById($user_id);
            $userdata = $this->user_data->getUserById($user_id);

            if (! empty($userdata)) {
                if (! empty($userdata[0]['cellnumber'])) {
                    // format cellnumber for display
                    $userdata[0]['formatted_cellnumber'] = $this->address_logic->formatPhoneDisplay($userdata[0]['cellnumber']);    
                }

                if ($include_vehiclegroups === true) {

                    // get all vehicle groups in this account
                    $available_vehiclegroups = $this->vehicle_logic->getVehicleGroupsByAccountId($userdata[0]['account_id']);
                    
                    // get all vehicle groups already assigned to this user
                    $assigned_vehiclegroups = $this->vehicle_logic->getVehicleGroupsByUserId($user_id);
                    
                    if ($available_vehiclegroups === false) {
                        $available_vehiclegroups = array();
                    }
                    
                    if ($assigned_vehiclegroups === false) {
                        $assigned_vehiclegroups = array();
                    }                    
                    
                    // loop through and remove assigned groups from available groups
                    if (! empty($available_vehiclegroups) AND ! empty($assigned_vehiclegroups)) {
                        $as_vehicle_group_ids = array();
                        
                        // iterate through the list of assigned groups and get all the group ids for checking against the available groups
                        foreach ($assigned_vehiclegroups as $as_group) {
                            if (! isset($as_vehicle_group_ids[$as_group['unitgroup_id']])) {
                                $as_vehicle_group_ids[$as_group['unitgroup_id']] = $as_group;
                            }        
                        }
                        
                        // iterate through the list of available groups and remove the group if it's already assigned to this user 
                        foreach ($available_vehiclegroups as $index => $av_group) {
                            if (isset($as_vehicle_group_ids[$av_group['unitgroup_id']])) {
                                unset($available_vehiclegroups[$index]);
                            }            
                        }    
                    }
                    
                    $userdata[0]['assigned_vehiclegroups'] = $assigned_vehiclegroups;
                    $userdata[0]['available_vehiclegroups'] = $available_vehiclegroups;  
                }
                
                if ($include_landmarkgroups === true) {
                    // set territory to `landmark` in order to get only landmark groups
                    $this->territory_logic->setTerritoryType(array('landmark'));
                                        
                    // get all territory groups in this account
                    $available_territorygroups = $this->territory_logic->getTerritoryGroupsByAccountId($userdata[0]['account_id']);                    
                    
                    // get all territory groups already assigned to this user
                    $assigned_territorygroups = $this->territory_logic->getTerritoryGroupsByUserId($user_id);
                    

                    // reset territory to all type
                    $this->territory_logic->resetTerritoryType();
                    
                    if ($available_territorygroups === false) {
                        $available_territorygroups = array();
                    }
                    
                    if ($assigned_territorygroups === false) {
                        $assigned_territorygroups = array();
                    }                    
                    
                    // loop through and remove assigned groups from available groups
                    if (! empty($available_territorygroups) AND ! empty($assigned_territorygroups)) {
                        $as_territory_group_ids = array();
                        
                        // iterate through the list of assigned groups and get all the group ids for checking against the available groups
                        foreach ($assigned_territorygroups as $as_group) {
                            if (! isset($as_territory_group_ids[$as_group['territorygroup_id']])) {
                                $as_territory_group_ids[$as_group['territorygroup_id']] = $as_group;
                            }        
                        }
                        
                        // iterate through the list of available groups and remove the group if it's already assigned to this user 
                        foreach ($available_territorygroups as $index => $av_group) {
                            if (isset($as_territory_group_ids[$av_group['territorygroup_id']])) {
                                unset($available_territorygroups[$index]);
                            }            
                        }    
                    }
                    
                    $userdata[0]['assigned_territorygroups'] = $assigned_territorygroups;
                    $userdata[0]['available_territorygroups'] = $available_territorygroups;
                }
                //print_rb($userdata);
                return $userdata;    
            } else {
                $this->setErrorMessage('Could not retrieve user data with the user id');
            }
        }
        return false;    
    }

    /**
     * Get usertype by usertype id
     *
     * @param int usertype_id
     *
     * @return bool
     */
    public function getUserTypeById($usertype_id, $include_typepermissions = false)
    {
        $this->validator->validate('record_id', $usertype_id);
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            //return $this->user_data->getUserById($user_id);
            $usertype = $this->user_data->getUserTypeById($usertype_id);
            if (! empty($usertype)) {
                if ($include_typepermissions === true) {

                    // get all associated permissions for this usertype
                    $usertype[0]['permissions'] = $this->user_data->getUserTypePermissionsById($usertype_id);
                }

                return $usertype;    
            } else {
                $this->setErrorMessage('Could not retrieve user type data');
            }
        }
        return false;    
    }

    /**
     * Get user by email
     *
     * @param string   email
     *
     * @return array
     */
    public function getUserByEmail($email)
    {
    	$user=$this->user_data->getUserByEmail($email);
    	
    	return $user;
    }

    /**
     * Get user by username
     *
     * @param string   username
     *
     * @return array
     */
    public function getUserByUsername($username)
    {
    	$user=$this->user_data->getUserByUsername($username);
    	
    	return $user;
    }

    /**
     * Add usertype to the account
     *
     * @param array params
     *
     * @return bool|int
     */
    public function addUserType($params)
    {
        if (! is_array($params) OR (count($params) < 1)) {
            $this->setErrorMessage('Invalid parameters');
        } else {
            $this->validator->validate('record_id', $params['account_id']);
            $this->validator->validate('usertype_name', $params['usertype']);
        }
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }        

        if (! $this->hasError()) {
            
            $usertype_id = $this->user_data->addUserType($params);
            if ($usertype_id !== false AND is_numeric($usertype_id) AND $usertype_id > 0) {
                // associate locked permission (default) for the newly created usertype
                // currently only permission_id = 9 (Vehicles page view access) is locked
                $locked_permissions = $this->user_data->getLockedPermissions();
                if (! empty($locked_permissions)) {
                    foreach($locked_permissions as $key => $permission) {
                        $permission_params                  = array();
                        $permission_params['permission_id'] = $permission['permission_id'];
                        $permission_params['usertype_id']   = $usertype_id;
                        $this->user_data->addUserTypePermission($permission_params);                       
                    }
                }

                return $usertype_id;
            }
        }
        return false;    
    }

    /**
     * Delete usertype
     *
     * @param int   account_id
     * @param int   usertype_id
     *
     * @return bool
     */
    public function deleteUserType($account_id, $usertype_id)
    {
        $this->validator->validate('record_id', $account_id);
        $this->validator->validate('record_id', $usertype_id);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            // check if usertype is associated to a user, delete only if no association
            if ($this->user_data->checkUserTypeUserAssociationExist($usertype_id) === false) {
                if($this->user_data->deleteUserType($account_id, $usertype_id) !== false) {
                    //delete usertype_id permission relation
                    $this->user_data->deleteUserTypePermission($usertype_id);
                }
                return true;
            } else {
                $this->setErrorMessage('There are User still assigned to this User Type');
                 return false;
            }
        }
        return false;    
    }

    /**
     * Update usertype info
     *
     * @param int   usertype_id
     * @param array params
     *
     * @return bool
     */
    public function updateUserTypeInfo($usertype_id, $params)
    {
        $this->validator->validate('record_id', $usertype_id); 
        
        if (! is_array($params) OR (count($params) < 1)) {
            $this->setErrorMessage('Invalid Parameter');
        } else {
            if (isset($params['usertype'])) {
                $this->validator->validate('usertype_name', $params['usertype']);
            }
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }
        
        if (! $this->hasError()) {
            return $this->user_data->updateUserTypeInfo($usertype_id, $params);
        }
        return false;    
    }

    /**
     * check if usertype exist
     *
     * @param string $usertype
     * @param int $account_id
     *
     * @return bool
     */    
    public function checkUserTypeExist($account_id, $usertype)
    {
        $this->validator->validate('record_id', $account_id); 
        
        $this->validator->validate('usertype_name', $usertype);

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }
        
        if (! $this->hasError()) {
            
            $type = $this->user_data->checkUserTypeExist($account_id, $usertype);
            if (is_array($type) AND ! empty($type)) {
                return true;
            }
        }
        return false;    
    }

    /*
     * Get usertype options for account
     *
     * @param int $account_id
     *
     * @return void
     */
    public function getUserTypeOptions($account_id)
    {
        $usertypes = '';

        $types = $this->user_data->getUserTypeOptions($account_id);
        if ($types !== false AND ! empty($types)) {
            $usertypes = $types;
        }
        return $usertypes;
    }

    /**
     * Add usertype permissions to the usertype
     *
     * @param array params
     *
     * @return bool
     */
    public function addUserTypePermission($params)
    {
        if (! is_array($params) OR (count($params) < 1)) {
            $this->setErrorMessage('Invalid parameters');
        } else {
            $this->validator->validate('record_id', $params['account_id']);
            $this->validator->validate('record_id', $params['usertype_id']);            
        }

        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            
            if (isset($params['permission_add']) AND is_array($params['permission_add']) AND ! empty($params['permission_add'])) {
                $added_permission = array();
                $process_dependency = array();

                // add usertype permission assoc
                foreach ($params['permission_add'] as $key => $permission_id ) {
                    $temp_permission                    = array();
                    $temp_permission['permission_id']   = $permission_id;
                    $temp_permission['usertype_id']     = $params['usertype_id'];
                    $permission_exist                   = $this->user_data->getUserTypePermission($temp_permission);
                    if ($permission_exist === false OR empty($permission_exist)) {
                        // add this permission
                        $this->user_data->addUserTypePermission($temp_permission);
                        
                        // store added permission_id to be used later
                        $added_permission[] = $permission_id;
                        if(isset($this->permission_dependency[$permission_id]) AND ! empty($this->permission_dependency[$permission_id])) {
                            // if added permission has dependency permission, store it for use later
                            $process_dependency[] = $permission_id;
                        }
                    }
                }

                if (! empty($process_dependency)) {
                    foreach ($process_dependency as $key => $permission_id) {
                        foreach($this->permission_dependency[$permission_id] as $id => $dependency_id) {
                            if (! in_array($dependency_id, $added_permission)) {
                                $temp_params                    = array();
                                $temp_params['permission_id']   = $dependency_id;
                                $temp_params['usertype_id']     = $params['usertype_id'];

                                // insert or update on duplicate
                                $this->user_data->insertUserTypePermission($temp_params);
                            }
                        }
                    }
                }
            }

            if (isset($params['permission_remove']) AND is_array($params['permission_remove']) AND ! empty($params['permission_remove'])) {
                // remove usertype permission assoc
                foreach ($params['permission_remove'] as $key => $permission_id ) {
                    $temp_permission                    = array();
                    $temp_permission['permission_id']   = $permission_id;
                    $temp_permission['usertype_id']     = $params['usertype_id'];
                    $this->user_data->removeUserTypePermission($temp_permission);
                }
            }            
           
            return true;
        }
        return false;    
    }

    /**
     * Send registration email to newly added user
     *
     * @param array userdata
     *
     * @return bool
     */
    public function sendUserRegistrationEmail($user_id, $email, $username)
    {
        $this->validator->validate('record_id', $user_id);
        
        $this->validator->validate('username', $username);
        
        if (! empty($email)) {
            if (! \Swift_Validate::email($email)) {   // 1st - check email for valid formatting
                $this->setErrorMessage('err_invalid_email');    
            }    
        } else {
            $this->setErrorMessage('err_invalid_email');
        }
        
        if (! defined('EMAIL_HOST') OR 
            ! defined('EMAIL_PORT') OR 
            ! defined('EMAIL_USERNAME') OR 
            ! defined('EMAIL_PASSWORD')) {
            $this->setErrorMessage('err_email_configuration');
        }
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }    

        if (! $this->hasError()) {
            // generate a random 32 characters token to send with registration link
            $token = $this->generateToken();
            
            // get domain from the request object
            $request = Request::createFromGlobals();
            $domain = $request->getHttpHost();
            
            $failed_recipients = array();
            
            // Create the mail transport configuration
            $transport = \Swift_SmtpTransport::newInstance(EMAIL_HOST, EMAIL_PORT, EMAIL_SECURITY);
            $transport->setUsername(EMAIL_USERNAME);
            $transport->setPassword(EMAIL_PASSWORD);
            
            // Create the message
            $message = \Swift_Message::newInstance();
            $message->setSubject('Crossbones User Registration');
            $message->setBody('<html><body>Please click on the link below to complete your new user registration for Crossbones.<br><br><a href="http://'.$domain.'/registration/'.$token.'">http://'.$domain.'/registration/'.$token.'</a><br></body></html>', 'text/html');
            // $message->setFrom(array('changepassword@'.EMAIL_FROM_DOMAIN => EMAIL_FROM_DOMAIN));
            $message->setFrom(array('changepassword@'.$_SERVER['SERVER_NAME'] => $_SERVER['SERVER_NAME']));
            $message->setTo(array($email => $fullname));
            $message->setBcc(array('monitor@positionplusgps.com' => 'UserLogic::sendTokenAccess'));

            // Send the email
            $mailer = \Swift_Mailer::newInstance($transport);
            $mailer->send($message, $failed_recipients);                                     

            if (empty($failed_recipients)) {
                $expiration_date = date('Y-m-d H:i:s', (time() + USER_REGISTRATION_EXPIRY)); // set expiration date to 48 hours
                $added = $this->user_data->addUserInvitation($user_id, $token, $expiration_date);
                if ($added !== false) {
                    return true;
                } else {
                    $this->setErrorMessage('err_save_user_invitation');
                }
            } else {
                $this->setErrorMessage('err_send_email');    
            }
        }
        return false;    
    }

    /**
     * Send Login email to newly added user
     *
     * @param array userdata
     *
     * @return bool
     */
    public function sendUserLoginInfo($user_id, $email, $username, $password, $fullname)
    {
        $this->validator->validate('record_id', $user_id);
        
        if (! empty($email)) {
            if (! \Swift_Validate::email($email)) {   // 1st - check email for valid formatting
                $this->setErrorMessage('err_invalid_email');    
            }    
        } else {
            $this->setErrorMessage('err_invalid_email');
        }
        
        $this->validator->validate('username', $username);
        
        $this->validator->validate('password', $password);
        
        $this->validator->validate('full_name', $fullname);
        
        if (! defined('EMAIL_HOST') OR 
            ! defined('EMAIL_PORT') OR 
            ! defined('EMAIL_USERNAME') OR 
            ! defined('EMAIL_PASSWORD')) {
            $this->setErrorMessage('err_email_configuration');
        }
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }    

        if (! $this->hasError()) {
            
            // get domain from the request object
            $request = Request::createFromGlobals();
            $domain = $request->getHttpHost();
            
            $failed_recipients = array();
            
            // Create the mail transport configuration
            $transport = \Swift_SmtpTransport::newInstance(EMAIL_HOST, EMAIL_PORT, EMAIL_SECURITY);
            $transport->setUsername(EMAIL_USERNAME);
            $transport->setPassword(EMAIL_PASSWORD);
            
            // Create the message
            $message = \Swift_Message::newInstance();
            $message->setSubject('User Login');
            $message->setBody('<html><body>Provided below is your username and password. Please click on the link below and log in using these credentials.<br><br>Username: '.$username.'<br>Password: '.$password.'<br><br><a href="http://'.$domain.'">http://'.$domain.'</a><br></body></html>', 'text/html');
            
            // $message->setFrom(array('registration@'.EMAIL_FROM_DOMAIN => EMAIL_FROM_DOMAIN));
            $message->setFrom(array('registration@'.$_SERVER['SERVER_NAME'] => $_SERVER['SERVER_NAME']));
            $message->setTo(array($email => $fullname));
            $message->setBcc(array('monitor@positionplusgps.com' => 'UserLogic:sendUserLoginInfo'));

            // Send the email
            $mailer = \Swift_Mailer::newInstance($transport);
            $mailer->send($message, $failed_recipients);                                     

            if (empty($failed_recipients)) {
                return true;
            } else {
                $this->setErrorMessage('err_send_email');    
            }
        }
        return false;    
    }

    /**
     * Resend username for forgot username purposes
     *
     * @param array userdata
     *
     * @return bool
     */
    public function resendUserLoginInfo($user_id, $email, $username, $fullname)
    {
        $this->validator->validate('record_id', $user_id);
        
        if (! empty($email)) {
            if (! \Swift_Validate::email($email)) {   // 1st - check email for valid formatting
                $this->setErrorMessage('err_invalid_email');    
            }    
        } else {
            $this->setErrorMessage('err_invalid_email');
        }
        
        if (! defined('EMAIL_HOST') OR 
            ! defined('EMAIL_PORT') OR 
            ! defined('EMAIL_USERNAME') OR 
            ! defined('EMAIL_PASSWORD')) {
            $this->setErrorMessage('err_email_configuration');
        }
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }    

        if (! $this->hasError()) {
            
            // get domain from the request object
            $request = Request::createFromGlobals();
            $domain = $request->getHttpHost();
            
            $failed_recipients = array();
            
            // Create the mail transport configuration
            $transport = \Swift_SmtpTransport::newInstance(EMAIL_HOST, EMAIL_PORT, EMAIL_SECURITY);
            $transport->setUsername(EMAIL_USERNAME);
            $transport->setPassword(EMAIL_PASSWORD);
            
            // Create the message
            $message = \Swift_Message::newInstance();
            $message->setSubject('PositionPlus Username Recovery');
            $message->setBody('<html><body>Provided below is your username for PositionPlus. Please click on the link below and log in using this username.<br><br>Username: '.$username.'<br><br><a href="http://'.$domain.'">http://'.$domain.'</a><br></body></html>', 'text/html');
            
            // $message->setFrom(array('forgotusername@'.EMAIL_FROM_DOMAIN => EMAIL_FROM_DOMAIN));
            $message->setFrom(array('forgotusername@'.$_SERVER['SERVER_NAME'] => $_SERVER['SERVER_NAME']));
            $message->setTo(array($email => $fullname));
            $message->setBcc(array('monitor@positionplusgps.com' => 'UserLogic::resendUserLoginInfo'));

            // Send the email
            $mailer = \Swift_Mailer::newInstance($transport);
            $mailer->send($message, $failed_recipients);                                     

            if (empty($failed_recipients)) {
                return true;
            } else {
                $this->setErrorMessage('err_send_email');    
            }
        }
        return false;    
    }

    /**
     * Send change password link with token
     *
     * @param array userdata
     *
     * @return bool
     */
    public function sendTokenAccess($user_id, $email, $fullname)
    {
        $this->validator->validate('record_id', $user_id);
        
        if (! empty($email)) {
            if (! \Swift_Validate::email($email)) {   // 1st - check email for valid formatting
                $this->setErrorMessage('err_invalid_email');    
            }    
        } else {
            $this->setErrorMessage('err_invalid_email');
        }
        
        if (! defined('EMAIL_HOST') OR 
            ! defined('EMAIL_PORT') OR 
            ! defined('EMAIL_USERNAME') OR 
            ! defined('EMAIL_PASSWORD')) {
            $this->setErrorMessage('err_email_configuration');
        }
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }    

        if (! $this->hasError()) {
        
        	$token=md5($user_id.time());
        	$expires=date('Y-m-d G:i:s',strtotime('+1 week'));
        	
        	if($this->user_data->addTempToken($user_id,$token,$expires))
        	{
	            // get domain from the request object
	            $request = Request::createFromGlobals();
	            $domain = $request->getHttpHost();
	            
	            $failed_recipients = array();
	            
	            // Create the mail transport configuration
	            $transport = \Swift_SmtpTransport::newInstance(EMAIL_HOST, EMAIL_PORT, EMAIL_SECURITY);
	            $transport->setUsername(EMAIL_USERNAME);
	            $transport->setPassword(EMAIL_PASSWORD);
	            
	            // Create the message
	            $message = \Swift_Message::newInstance();
	            $message->setSubject('PositionPlus Password Change');
	            $message->setBody('<html><body>Provided below is a link to change your password for PositionPlus. Please click on the link below and enter a new password when prompted.<br><br><a href="http://'.$domain.'/changepassword/'.$token.'">http://'.$domain.'/changepassword/'.$token.'</a><br></body></html>', 'text/html');
	            
	            // $message->setFrom(array('changepassword@'.EMAIL_FROM_DOMAIN => EMAIL_FROM_DOMAIN));
                $message->setFrom(array('changepassword@'.$_SERVER['SERVER_NAME'] => $_SERVER['SERVER_NAME']));
                $message->setTo(array($email => $fullname));
                $message->setBcc(array('monitor@positionplusgps.com' => 'UserLogic::sendTokenAccess'));
	
	            // Send the email
	            $mailer = \Swift_Mailer::newInstance($transport);
	            $mailer->send($message, $failed_recipients);                                     
	
	            if (empty($failed_recipients)) {
	                return true;
	            } else {
	                $this->setErrorMessage('err_send_email');    
	            }
	        }
        }
        return false;    
    }

    /**
     * Get token
     *
     * @param token
     *
     * @return bool
     */
    public function getToken($token)
    {	
    	$temp_token=$this->user_data->getTempToken($token);
    	
    	if($temp_token)
    	{
	    	return $temp_token[0]['user_id'];
    	}
    	else
    	{
	    	return false;
    	}
    }
    
    /**
     * Generate random token
     *
     * @return string
     */
    public function generateToken()
    {
        return md5(uniqid(mt_rand(), true));        
    }

    /**
     * Get user by invitation token
     *
     * @param string token
     *
     * @return bool|array
     */    
    public function getUserByInvitationToken($token)
    {
        if (empty($token) OR strlen($token) !== 32) {
            $this->setErrorMessage('Invalid Token');    
        }
        
        if (! $this->hasError()) {
            $userdata = $this->user_data->getUserByInvitationToken($token);
            if (! empty($userdata) AND is_array($userdata)) {
                $userdata = array_pop($userdata);
                // validate user data before completing registration
                if (! empty($userdata['expiredate'])) {                                 
                    if ((strtotime($userdata['expiredate']) - time()) > 0) {            // Step 1 - validate the expiration date (if the expire date is after than current date, proceed to the next step)
                        if ((! empty($userdata['userstatusname'])) AND 
                            (strtolower($userdata['userstatusname']) === 'pending')) {  // Step 2 - validate the current status (make sure that user status is still PENDING for activation)
                            return $userdata;    
                        } else {
                            $this->setErrorMessage('This user is no longer pending for activation');
                        }
                    } else {
                        $this->setErrorMessage('The token has expired');    
                    }  
                } else {
                    $this->setErrorMessage('Invalid expired date');    
                }
            } else {
                $this->setErrorMessage('Could not retrieve user data using this token.');
            }
        }
        return false;    
    }
    
    /**
     * @param string token
     *
     * @return bool|array
     */ 
    public function getCreateDate($user_id)
    {
        return $this->user_data->getCreateDate($user_id);
    }
    
    /**
     * Get error messages (calls the parent method)
     *
     * @param string token
     *
     * @return bool|array
     */ 
    public function getErrorMessage()
    {
        return parent::getErrorMessage();
    }
    
    /**
     * Generate random token
     *
     * @param string token
     *
     * @return bool|array
     */ 
    public function getTimezones()
    {
        return $this->user_data->getTimezones();
    }

    /**
     * Validate new user
     *
     * @param int user_id
     * @param string token
     * @param array params
     *
     * @return bool|int
     */
    public function validateNewUser($user_id, $token, $params)
    {
        $this->validator->validate('record_id', $user_id);

        if (empty($token) OR strlen($token) !== 32) {
            $this->setErrorMessage('This token is invalid');
        }

        if (! is_array($params) OR (count($params) < 1)) {
            $this->setErrorMessage('The paramters are invalid');
        } else {
            if (! empty($user_id) AND ! empty($token)) {                                                                // 1st - validate invitation token, user id, and possible contact association
                
                // get user data for validation
                $userdata = $this->user_data->getUserByInvitationToken($token);
                
                if (! empty($userdata) AND is_array($userdata)) {
                    $userdata = array_pop($userdata);
                    if (empty($userdata['user_id']) OR (! empty($userdata['user_id']) AND ($userdata['user_id'] !== $user_id))) {
                        $this->setErrorMessage('The user id does not match the one for this token');
                    }
                    
                    if ((strtotime($userdata['expiredate']) - time()) < 0) {                                            // check to make sure that token hasn't expired
                        $this->setErrorMessage('This token has expired');    
                    }
 
                    $contact = $this->contact_logic->getContactByUserId($user_id);                                      // get the contact that was associated to this user
                    if (! empty($contact) AND is_array($contact)) {
                        $contact = array_pop($contact);
                    }  
                } else {
                    $this->setErrorMessage('This token is invalid');
                }
            } else {
                $this->setErrorMessage('The user id and token are invalid');
            }
            
                            
            if (! empty($params['username'])) {                                                                         // 2nd - validate username
                $this->validator->validate('username', $params['username']);
                if (! $this->validator->hasError()) {                                                                   // if username has all valid characters, check for duplication
                    $duplicate = $this->user_data->getUserByUsername($params['username']);
                    if (! empty($duplicate)) {
                        $this->setErrorMessage('This username has already been taken');
                    }
                }        
            } else {
                $this->setErrorMessage('Username cannot be empty');
            }
            
            if (! empty($params['password'])) {                                                                         // 3rd - validate password
                $this->validator->validate('password', $params['password']);    
            } else {
                $this->setErrorMessage('Password cannot be empty');
            }
    
            if (! empty($params['cellnumber']) AND ! empty($params['cellcarrier_id'])) {                                // 4th - validate sms number and sms carrier

                $this->validator->validate('phone_number', $params['cellnumber']);
                // if valid phone number, strip out all non numeric characters 
                if (! $this->validator->hasError()) {
                    $params['cellnumber'] = $this->address_logic->formatPhoneForSaving($params['cellnumber']);    
                }

                $this->validator->validate('record_id', $params['cellcarrier_id']);    
            }
            
            $this->validator->validate('record_id', $params['timezone_id']);                                            // 5th - validate timezone            
        }
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }

        if (! $this->hasError()) {
            // set up contact info
            $contact_params = array(
                'account_id'        => $userdata['account_id'],
                'user_id'           => $user_id,
                'firstname'         => $userdata['firstname'],
                'lastname'          => $userdata['lastname'],
                'email'             => $userdata['email'],
                'contactstatus'     => 'active'
            );

            // remove SMS Number and Carrier before updating crossbones.user table (`cellphone` column will be remove from crossbones.user table)
            if (isset($params['cellnumber'])) {
                $contact_params['cellnumber'] = $params['cellnumber'];
                unset($params['cellnumber']);
            }
            
            if (isset($params['cellcarrier_id'])) {
                $contact_params['cellcarrier_id'] = $params['cellcarrier_id'];
                unset($params['cellcarrier_id']);
            }

            // set userstatus to Active
            $params['userstatus_id'] = 3;
                        
            // encode password            
            $params['password'] = $this->encodePassword($params['password']);
            
            $updated = $this->user_data->updateUserInfo($user_id, $params);
            if (! empty($updated)) {
                if (! empty($contact)) {    // if a contact exist for this user, update its status to active
                    $this->contact_logic->updateContactInfo($contact['contact_id'], array('user_id' => $user_id, 'contactstatus' => 'active'));
                } else {                    // else if it doesn't exist, create contact and then associate it to this user
                    $this->contact_logic->addContact($contact_params);
                }
                
                // if this user is an Admin type, create the default unit/landmark group associations for this user
                $this->addDefaultGroupsToUser($user_id);

                return true;
            } else {
                $this->setErrorMessage('Could not activate this user due to a database issue.');
            }
        }
        return false;    
    }
    
    /**
     * Needs to be moved into GTC\User at some point
     *
     * @param $account_password
     * @return string
     */
    private function encodePassword($account_password)
    {
        $encoder = new MessageDigestPasswordEncoder('sha512', true, 5000);
        $encodedPassword = $encoder->encodePassword($account_password, null);
        
        return $encodedPassword;
    }

    /**
     * Get users by NOT IN
     *
     * @param $account_id, $params
     * @return array|bool
     */
    public function getUserWhereNotIn($account_id, $params)
    {
        $this->validator->validate('record_id', $account_id);
        
        if (empty($params) OR ! is_array($params)) {
            $this->setErrorMessage('Invalid parameters');
        }
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }
        
        if (! $this->hasError()) {
            return $this->user_data->getUserWhereNotIn($account_id, $params);    
        }

        return false;
    }

    /**
     * Get all system accounts
     *
     * @param array account_status
     *
     * @return array|bool
     */
    public function getAccounts($account_status)
    {
        if (! is_array($account_status)) {
            $account_status = array($account_status);
        }
        
        if (! is_array($account_status) OR (count($account_status) < 1)) {
            $this->setErrorMessage('Missing Account Status');
        }
        
        if (! $this->hasError()) {
            return $this->user_data->getAccounts($account_status);
        }

        return false;
    }

    /**
     * Get users by NOT IN
     *
     * @param $account_id, $params
     * @return array|bool
     */
    public function getUserByAccountId($account_id)
    {
        $this->validator->validate('record_id', $account_id);
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }
        
        if (! $this->hasError()) {
            return $this->user_data->getUserByAccountId($account_id);    
        }

        return false;
    }

    /**
     * Get user command history
     *
     * @param $account_id, $params
     * @return array|bool
     */
    public function getUserCommandHistory($account_id, $filter_params) 
    {
        $this->validator->validate('record_id', $account_id);
        
        if (! empty($filter_params) AND is_array($filter_params)) {
            if (isset($filter_params['user_id'])) {
                $this->validator->validate('record_id', $filter_params['user_id']);
            }
            
            if (isset($filter_params['starttime']) AND empty($filter_params['starttime'])) {
                $this->setErrorMessage('Invalid Start Date');
            }
            
            if (isset($filter_params['endtime']) AND empty($filter_params['endtime'])) {
                $this->setErrorMessage('Invalid End Date');
            }
            
            if (isset($filter_params['unit_id'])) {
                $this->validator->validate('record_id', $filter_params['unit_id']);
            }
        }
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }
        
        if (! $this->hasError()) {
            return $this->user_data->getUserCommandHistory($account_id, $filter_params);   
        }        
        return false; 
    }

        /**
     * Get user command history
     *
     * @param $account_id, $params
     * @return array|bool
     */
    public function getUserStarterDisabledUnit($account_id, $filter_params) 
    {
        $this->validator->validate('record_id', $account_id);
        $this->validator->validate('record_id', $filter_params['unit_id']);
 
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }
        
        if (! $this->hasError()) {
            return $this->user_data->getUserStarterDisabledUnit($account_id, $filter_params);   
        }        
        return false; 
    }
    
    /**
     * Add default groups to Admin type users
     * 
     * @param user_id
     * @return bool
     */
    public function addDefaultGroupsToUser($user_id)
    {
        $this->validator->validate('record_id', $user_id);
         
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }         
        
        if (! $this->hasError()) {
            $userdata = $this->user_data->getUserById($user_id);
            if (! empty($userdata)) {
                $userdata = array_pop($userdata);
                if (! empty($userdata['usertype']) AND $userdata['usertype'] == 'Admin' AND ! empty($userdata['canned']) AND $userdata['canned'] == 1) {
                    $unitgroup_added = $territorygroup_added = false;
                    
                    // add default vehicle group
                    $default_unitgroup = $this->vehicle_logic->getVehicleDefaultGroup($userdata['account_id']);
                    if (! empty($default_unitgroup) AND ! empty($default_unitgroup['unitgroup_id'])) {
                        $unitgroup_exist = $this->vehicle_logic->getVehicleGroupsByUserId($user_id, array($default_unitgroup['unitgroup_id']));
                        if (empty($unitgroup_exist)) {    // if user does not already have access to the default unit group, give them access
                            $unitgroup_added = $this->vehicle_logic->addVehicleGroupToUser($default_unitgroup['unitgroup_id'], $user_id);
                            if ($unitgroup_added !== false) {
                                $unitgroup_added = true;
                            } else {
                                $this->setErrorMessage('Could not add the default vehicle group to this user');
                            }    
                        } else {                        // else user already have access, no need to give them access again
                            $unitgroup_added = true;
                        }
                    } else {
                        $this->setErrorMessage('This account does not have a default vehicle group');
                    }
                    
                    // add default territory group
                    $this->territory_logic->setTerritoryType(array('landmark'));
                    $default_territorygroup = $this->territory_logic->getTerritoryDefaultGroup($userdata['account_id']);
                    if (! empty($default_territorygroup) AND ! empty($default_territorygroup['territorygroup_id'])) {
                        $territorygroup_exist = $this->territory_logic->getTerritoryGroupsByUserId($user_id, array($default_territorygroup['territorygroup_id']));
                        if (empty($territorygroup_exist)) {    // if user does not already have access to the default territory group, give them access
                            $territorygroup_added = $this->territory_logic->addTerritoryGroupToUser($default_territorygroup['territorygroup_id'], $user_id);
                            if ($territorygroup_added !== false) {
                                $territorygroup_added = true;    
                            } else {
                                $this->setErrorMessage('Could not add the default landmark group to this user');
                            }    
                        } else {                                // else user already have access, no need to give them access again
                            $territorygroup_added = true;
                        }
                    } else {
                        $this->setErrorMessage('This account does not have a default landmark group');    
                    }
                    $this->territory_logic->resetTerritoryType();
                    
                    return ($unitgroup_added AND $territorygroup_added);
                } else {    // user is not an Admin type, do not add default groups to the user
                    return true;    
                }
            } else {
                $this->setErrorMessage('Failed to retrieve user data for this user');                
            }    
        }
        return false;         
    }

    /**
     * Validate POST parameters for updating account info
     * 
     * @param array params
     * @return array
     */
    public function validateAccountInfoPostData($params) 
    {
        $ret = array(
            'params'    => array(),
            'error'     => ''
        );
        
        if (! empty($params)) {
            if (! empty($params['id'])) {
                $temp_params = array();

                switch ($params['id']) {
                    case 'my-account-first-name':     
                        $this->validator->validate('first_last_name', $params['value']);
                        if (! $this->validator->hasError()) {
                            $temp_params['firstname'] = $params['value'];    
                        }
                        break;
                    case 'my-account-last-name':
                        $this->validator->validate('first_last_name', $params['value']);
                        if (! $this->validator->hasError()) {
                            $temp_params['lastname'] = $params['value'];    
                        }
                        break;
                    case 'my-account-email':
                        $this->validator->validate('email', $params['value']);
                        if (! $this->validator->hasError()) {
                            $temp_params['email'] = $params['value'];   
                        }
                        break;
                    case 'my-account-username':
                        $this->validator->validate('username', $params['value']);
                        if (! $this->validator->hasError()) {
                            $temp_params['username'] = $params['value'];
                        }
                        break;
                    case 'my-account-password':
                        if (! empty($params['password_confirm'])) {
                            $password = $params['value'];
                            $confirm = $params['password_confirm'];
                            $this->validator->validate('password', $password);
                            $this->validator->validate('password', $confirm);
                            if (! $this->validator->hasError()) {
                                if ($password === $confirm) {
                                    $temp_params['password'] = $password;    
                                } else {
                                    $ret['error'] = 'The confirmed password does not match the new password';
                                }
                            }    
                        } else {
                            $ret['error'] = 'Please confirm the new password';
                        }
                        break;
                }
                
                if ($this->validator->hasError()) {
                    $error = $this->validator->getErrorMessage();
                    if (! empty($error) AND is_array($error)) {
                        $error = implode(', ', $error);
                    } else {
                        $error = 'Invalid input';    
                    }
                    $ret['error'] = $error;
                }
                
                if (! empty($temp_params)) {
                    $ret['params'] = $temp_params;    
                } else if (empty($ret['error'])) {
                    $ret['error'] = 'No account info to update';
                } 
            } else {
                $ret['error'] = 'Invalid parameter';
            }                    
        } else {
            $ret['error'] = 'No parameters to be validated';
        }
        
        return $ret;     
    }
    
    /**
     * Set the user's status and contact status to Active if user is logging in for the first time 
     *
     * @param int   $user_id
     * @return bool
     */
    public function activateUser($user_id)
    {
        $this->validator->validate('record_id', $user_id);
        
        if ($this->validator->hasError()) {
            $this->setErrorMessage($this->validator->getErrorMessage());
        }
        
        if (! $this->hasError()) {
            // update user and contact status to 'active'
            $user_updated = $this->updateUserInfo($user_id, array('userstatus_id' => 3));
            if ($user_updated !== false) {
                $contact = $this->contact_logic->getContactByUserId($user_id);
    
                if (! empty($contact)) {
                    $contact = array_pop($contact);
                    $this->contact_logic->updateContactInfo($contact['contact_id'], array('contactstatus' => 'active'));
                }
    
                // add vehicle/territory default group to user if usertype is Admin
                $this->addDefaultGroupsToUser($user_id);
                
                return true;
            }
        }
        return false;   
    }

    /**
     * Get a url
     *
     * @param array url
     *
     * @return array
     */
    public function getURL($url)
    {
        return $this->user_data->getURL($url);
    }
     
}