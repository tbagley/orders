<?php

namespace Controllers\Ajax;

use Models\Data\ContactData;
use Models\Logic\ContactLogic;
use GTC\Component\Utils\CSV\CSVBuilder;
use Models\Data\UserData;
use Models\Logic\UserLogic;

/**
 * Class Users
 *
 * Thin Controller for Contact CRUD
 *
 */
class Users extends BaseAjax
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->user_logic = new UserLogic;
        $this->user_data = new UserData;
        
        $this->contact_logic = new ContactLogic;

    }
     
    /**
     * Get the users by filtered paramaters (called via ajax)
     *
     * POST params: filter_type, user_role, search_string
     *
     * @return array
     */
    public function getFilteredUsers()
    {
        $ajax_data          = array();
        $post               = $this->request->request->all();
        $account_id         = $this->user_session->getAccountId();

        $sEcho = 0;
        if (isset($post['sEcho']) AND $post['sEcho'] != '') {
            $sEcho = $post['sEcho'];
        }

        $output = array(
            "sEcho" => intval($sEcho),
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "data" => array()
        );

        $search_type                = (isset($post['filter_type']) AND !empty($post['filter_type'])) ? $post['filter_type'] : 'group_filter';
        $params                     = $post;
        $params['usertype_id']      = $post['usertype_id'];
        $params['userstatus_id']    = $post['userstatus_id'];
        $params['default_value']    = '-';
        
        if ($search_type != '') {
            $users = $this->user_logic->getFilteredUsers($account_id, $params);
            if ($users !== false) {
                
                $output['iTotalRecords']        = (isset($users['iTotalRecords']) AND ! empty($users['iTotalRecords'])) ? $users['iTotalRecords'] : 0;
                $output['iTotalDisplayRecords'] = (isset($users['iTotalDisplayRecords']) AND ! empty($users['iTotalDisplayRecords'])) ? $users['iTotalDisplayRecords'] : 0;
                $output['data']                 = (isset($users['data']) AND ! empty($users['data'])) ? $users['data'] : array();
            }
        }

        echo json_encode( $output );
        exit;
    }


    /**
     * Get the users by filtered paramaters (called via ajax)
     *
     * POST params: string search_string
     *
     * @return array
     */
    public function getFilteredUserTypeList()
    {
        $ajax_data          = array();
        $post               = $this->request->request->all();
        $account_id         = $this->user_session->getAccountId();

        $sEcho = 0;
        if (isset($post['sEcho']) AND $post['sEcho'] != '') {
            $sEcho = $post['sEcho'];
        }

        $output = array(
            "sEcho" => intval($sEcho),
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "data" => array()
        );

        $params                     = $post;
        $params['default_value']    = '-';
        
        $users = $this->user_logic->getFilteredUserTypeList($account_id, $params);
        if ($users !== false) {
            
            $output['iTotalRecords']        = (isset($users['iTotalRecords']) AND ! empty($users['iTotalRecords'])) ? $users['iTotalRecords'] : 0;
            $output['iTotalDisplayRecords'] = (isset($users['iTotalDisplayRecords']) AND ! empty($users['iTotalDisplayRecords'])) ? $users['iTotalDisplayRecords'] : 0;
            $output['data']                 = (isset($users['data']) AND ! empty($users['data'])) ? $users['data'] : array();
        }

        echo json_encode( $output );
        exit;
    }


    
    /**
     * Export Filter User List by search_string/group for an account
     *
     * GET params: string $filterType (string_search or group_filter)
     * GET params: $filterValue1 (search_string or a user_role)
     *
     * @return array
     */    
    public function exportFilteredUsersList($filterType, $filterValue1)
    {
        $account_id                 = $this->user_session->getAccountId();
        $results                    = array();

        $params['sEcho']            = 0;
        $params['bSearchable_0']    = true;
        $params['bSearchable_1']    = true;
        $params['bSearchable_2']    = true;
        $params['bSearchable_3']    = true;
        $params['bSearchable_4']    = true;
        $params['bSortable_0']      = true;
        $params['bSortable_1']      = true;
        $params['bSortable_2']      = true;
        $params['bSortable_3']      = true;
        $params['bSortable_4']      = true;
        $params['iSortCol_0']       = 0;
        $params['iSortingCols']     = 1;
        $params['iColumns']         = 5;
        $params['mDataProp_0']      = 'name';
        $params['mDataProp_1']      = 'username';
        $params['mDataProp_2']      = 'userstatusname';
        $params['mDataProp_3']      = 'email';
        //$params['mDataProp_4']      = 'lastlogin';
        $params['sSortDir_0']       = 'asc';

        $params['filter_type']      = $filterType;
        $params['default_value']    = '';

        // if filterType is a string search, set params according
        if ($filterType == 'string_search') {
            $params['search_string']    = $filterValue1;
            $params['user_roles']       = 'ALL';
        } else {

            $params['user_role'] = $filterValue1;
        }
        
        $params['user_role'] = 'all';
        
        $users = $this->user_logic->getFilteredUsers($account_id, $params);
        if ($users !== false) {
            $results = (isset($users['data']) AND ! empty($users['data'])) ? $users['data'] : array();
        }

        $filename   = str_replace(' ', '_', $this->user_session->getAccountName().'_FilterUsersExport_'.(($filterType == 'string_search') ? $filterType : 'Role Filter').'-'.$filterValue1);
        $fields     = array('name' => 'Name','username' => 'Username', 'userstatusname' => 'User Status','email' => 'Email'); //, 'lastlogin' => 'Last Login');

        $csv_builder = new CSVBuilder();
        $csv_builder->setSeparator(',');
        $csv_builder->setClosure('"');
        $csv_builder->setFields($fields);
        $csv_builder->format($results)->export($filename);
        
        exit();
    }

    /*
     * Add user to the account (ajax)
     *
     * POST params: first_name, last_name, email
     *
     * @return void
     */
    public function addUser()
    {
        $view_data  = array();
        $account_id = $this->user_session->getAccountId();
        $post       = $this->request->request->all();

        $error = '';
        if (! empty($post['first_name'])) {
            $params['firstname'] = $post['first_name'];    
        } else {
            $error = 'First Name cannot be blank';    
        }
        
        if (! empty($post['last_name'])) {
            $params['lastname'] = $post['last_name'];    
        } else {
            $error = 'Last Name cannot be blank';    
        }
        
        if (! empty($post['email'])) {
            $params['email'] = $post['email'];    
        } else {
            $error = 'Email cannot be blank';    
        }
        
        if (! empty($post['usertype_id'])) {
            $params['usertype_id'] = $post['usertype_id'];
        } else {
            $error = 'User Type is invalid';
        }
        
        if (! empty($post['username'])) {
            $params['username'] = $post['username'];    
        } else {
            $error = 'Username cannot be blank';
        }
        
        if (! empty($post['password'])) {
            $params['password'] = $post['password'];    
        } else {
            $error = 'Password cannot be blank';
        }
        
        if (! empty($post['cellnumber'])) {
            $params['cellnumber'] = $post['cellnumber'];
            if (! empty($post['cellcarrier_id'])) {
                $params['cellcarrier_id'] = $post['cellcarrier_id'];
            } else {
                $error = 'Invalid cell carrier';
            }
        }
        
        if ($error === '') {

            $params['account_id'] = $account_id;
            $user_id = $this->user_logic->addUser($params);
            if ($user_id !== false) {
                $ajax_data['code'] = 0;
                $ajax_data['message'] = 'Saved user';    
            } else {
                $errors = $this->user_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(',', $errors);
                } else {
                    $errors = 'Failed to save user due to a database error';
                }
                
                $ajax_data['code'] = 1;
                $ajax_data['message'] = $errors;                    
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = $error;
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Update user info (ajax)
     *
     * POST params: first_name, last_name, email, user/contact cellnumber, contact cellcarrier
     *
     * @return void
     */ 
    public function updateUserInfo()
    {
        $ajax_data = array();
        $post = $this->request->request->all();
        
        if (! empty($post['id'])) {
            $params         = array();
            $user_id        = $post['primary_keys']['userPk'];
            $update_table   = 'user';
            
            $contact_id     = (isset($post['primary_keys']['contactPk']) AND $post['primary_keys']['contactPk'] != '') ? $post['primary_keys']['contactPk'] : '';
            $user_cell      = (isset($post['primary_keys']['userCell']) AND $post['primary_keys']['userCell'] != '') ? $post['primary_keys']['userCell'] : '';
            $contact_carrier = (isset($post['primary_keys']['contactCarrier']) AND $post['primary_keys']['contactCarrier'] != '') ? $post['primary_keys']['contactCarrier'] : '';

            switch ($post['id']) {
                case 'user-firstname':
                    $params['firstname'] = $post['value'];       
                    break;
                case 'user-lastname':
                    $params['lastname'] = $post['value'];
                    break;
                case 'user-email':
                    $params['email'] = $post['value'];
                    break;
                case 'user-usertype':
                    $params['usertype_id'] = $post['value'];
                    break;
                case 'user-cell':
                    $params['cellnumber'] = $post['value'];

                    if ($post['value'] == '') {
                        $params['cellcarrier_id'] = 0;
                    } else {
                        if (! empty($contact_carrier)) {
                            $params['cellcarrier_id'] = $contact_carrier;
                        }
                    }

                    $update_table = 'contact';
                    break;
                case 'contact-carrier':
                    $params['cellcarrier_id'] = $post['value'];

                    if (! empty($user_cell)) {
                        $params['cellnumber'] = $user_cell;
                    }

                    $update_table = 'contact';
                    break;
            }
            
            if (! empty($params)) {
                if ($update_table == 'user') {
                    $update = $this->user_logic->updateUserInfo($user_id, $params);
                } else {
                    $update = $this->contact_logic->updateContactInfo($contact_id, $params);
                }
                if ($update !== false) {
                    $ajax_data['code'] = 0;
                    $ajax_data['data'] = $post;
                    $ajax_data['message'] = 'Updated User Information';    
                } else {
                    if ($update_table == 'user') {
                        $errors = $this->user_logic->getErrorMessage();
                    } else {
                        $errors = $this->contact_logic->getErrorMessage();
                    }
                    
                    if (! empty($errors) AND is_array($errors)) {
                        $errors = implode(',', $errors);
                    } else {
                        $errors = 'Failed to update user due to a database error';
                    }
                    
                    $ajax_data['code']      = (in_array($post['id'], array('user-cell','contact-carrier'))) ? 0 : 1;
                    $ajax_data['data']      = $post;
                    $ajax_data['message']   = $errors;
                    $ajax_data['validation_error'][] = $errors; // needed to trigger field highligting
                }    
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'No user info to update';                    
            } 
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid parameter';
        }
        
        $this->ajax_respond($ajax_data);
    }

    /**
     * Delete user (ajax)
     *
     * POST params: user_id
     *
     * @return void
     */ 
    public function deleteUser()
    {
        $ajax_data = array();
        $post = $this->request->request->all();

        if (! empty($post['user_id'])) {
            $deleted = $this->user_logic->deleteUser($post['user_id']);
            if ($deleted !== false) {
                $ajax_data['code'] = 0;
                $ajax_data['message'] = 'Deleted User';    
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Failed to delete user';                        
            }    
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'No user info to update';                    
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Get the user by user id (called via ajax)
     *
     * POST params: user_id
     *
     * @return array
     */
    public function getUserById()
    {
        $ajax_data          = array();
        $post               = $this->request->request->all();

        if (! empty($post['user_id'])) {
            $user_id = $post['user_id'];

            $user = $this->user_logic->getUserById($user_id, true, true);

            if ($user !== false) {
                if (! empty($user) AND is_array($user)) {
                    $user = array_pop($user);
                    $ajax_data['code'] = 0;
                    $ajax_data['data']['user'] = $user;
                    $ajax_data['message'] = 'Successfully retrieved user info';
                } else {
                    $ajax_data['code'] = 1;
                    $ajax_data['message'] = 'User does not exist';                        
                }       
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Failed to get user info';                    
            }    
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid user id';
        }
        
        $this->ajax_respond($ajax_data);
    }

    /**
     * Get the usertype by usertype id (called via ajax)
     *
     * POST params: usertype_id
     *
     * @return array
     */
    public function getUserTypeById()
    {
        $ajax_data          = array();
        $post               = $this->request->request->all();

        if (! empty($post['usertype_id'])) {
            $usertype_id = $post['usertype_id'];
            
            $usertype = $this->user_logic->getUserTypeById($usertype_id, true);

            if ($usertype !== false) {
                if (! empty($usertype) AND is_array($usertype)) {
                    $usertype = array_pop($usertype);
                    $ajax_data['code'] = 0;
                    $ajax_data['data'] = $usertype;
                    $ajax_data['message'] = 'Successfully retrieved user info';
                } else {
                    $ajax_data['code'] = 1;
                    $ajax_data['message'] = 'User does not exist';                        
                }       
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'Failed to get user info';                    
            }    
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid user id';
        }
        
        $this->ajax_respond($ajax_data);
    }

    /*
     * Add usertype to the account (ajax)
     *
     * POST params: first_name, last_name, email
     *
     * @return void
     */
    public function addUserType()
    {
        $view_data  = array();
        $account_id = $this->user_session->getAccountId();
        $post       = $this->request->request->all();
        
        $error = '';
        if (! empty($post['usertype'])) {
            $params['usertype'] = $post['usertype'];    
        } else {
            $error = 'User Type cannot be blank';    
        }
        
        if ($error === '') {

            $params['account_id'] = $account_id;
            $params['canned'] = 0;
            $type_exist = $this->user_logic->checkUserTypeExist($params['account_id'], $params['usertype']);
            if ($type_exist === false) {
                $usertype_id = $this->user_logic->addUserType($params);
                if ($usertype_id !== false) {
                    $ajax_data['code'] = 0;
                    $ajax_data['message'] = 'Saved User Type';    
                } else {
                    $errors = $this->user_logic->getErrorMessage();
                    if (! empty($errors) AND is_array($errors)) {
                        $errors = implode(',', $errors);
                    } else {
                        $errors = 'Failed to save user type due to a database error';
                    }
                    
                    $ajax_data['code'] = 1;
                    $ajax_data['message'] = $errors;                   
                }
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'User Type Aleady Exist'; 
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = $error;
        }   

        $this->ajax_respond($ajax_data);
    }

    /**
     * Delete user type (ajax)
     *
     * POST params: usertype_id
     *
     * @return void
     */ 
    public function deleteUserType()
    {
        $ajax_data  = array();
        $account_id = $this->user_session->getAccountId();
        $post       = $this->request->request->all();

        if (! empty($post['usertype_id'])) {
            $deleted = $this->user_logic->deleteUserType($account_id, $post['usertype_id']);
            if ($deleted !== false) {
                $ajax_data['code'] = 0;
                $ajax_data['message'] = 'Deleted User Type';    
            } else {
                $errors = $this->user_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(',', $errors);
                } else {
                    $errors = 'Failed to delete User Type';
                }
                
                $ajax_data['code'] = 1;
                $ajax_data['validation_error'][] = $ajax_data['message'] = $errors;  
            }    
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'No User Type info to update';                    
        }

        $this->ajax_respond($ajax_data);
    }

    /**
     * Update usertype info (ajax)
     *
     * POST params: user-type-name
     *
     * @return void
     */ 
    public function updateUserTypeInfo()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();
        
        if (! empty($post['id'])) {
            $params = array();
            $usertype_id = $post['primary_keys']['userTypePk'];

            if ($post['id'] == 'user-type-name') {
                $params['usertype'] = $post['value'];       
            }

            if (! empty($params)) {
                $update = $this->user_logic->updateUserTypeInfo($usertype_id, $params);
                if ($update !== false) {
                    $ajax_data['code'] = 0;
                    $ajax_data['data'] = $post;
                    $ajax_data['message'] = 'Updated UserType Information';    
                } else {
                    $errors = $this->user_logic->getErrorMessage();
                    if (! empty($errors) AND is_array($errors)) {
                        $errors = implode(',', $errors);
                    } else {
                        $errors = 'Failed to update user type due to a database error';
                    }
                    
                    $ajax_data['code'] = 1;
                    $ajax_data['validation_error'][] = $ajax_data['message'] = $errors;                        
                }    
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = 'No usertype info to update';                    
            } 
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'Invalid parameter';
        }
        
        $this->ajax_respond($ajax_data);
    }

    /*
     * Get usertype options for account
     *
     * @return void
     */
    public function getUserTypeOptions()
    {
        $account_id = $this->user_session->getAccountId();
        
        $output = '[';

        $usertypes = $this->user_logic->getUserTypeOptions($account_id);

        $last_index = count($usertypes) - 1;
        $index = 0;

        foreach ($usertypes as $key => $type) {
            $separator = ',';

            if ($index == $last_index) {
                $separator = '';
            }

            $output .= '{"value": "' . $type['usertype_id'] . '", "text": "' . $type['usertype'] . '"}' . $separator;
            $index++;
        }

        $output .= ']';
        die($output);
    }

    /*
     * Get user dropdown options for report
     *
     * @return void
     */
    public function getUserInlineDropdownOptions($placeholder = null, $value = '')
    {
        $output = '[';

        if ($placeholder !== null) {
            $output .= '
                {
                    "value": "'.$value.'",
                    "text":  "'.$placeholder.'"
                },
            ';
        }

        $account_id = $this->user_session->getAccountId();
        $users = $this->user_logic->getUserByAccountId($account_id);
        
        if ($users !== false) {
            $last_index = count($users) - 1;
            foreach ($users as $index => $user) {
                $separator = ',';

                if ($index == $last_index) {
                    $separator = '';
                }

                $output .= '{"value": "' . $user['user_id'] . '", "text": "' . $user['fullname'] . '"}' . $separator;
            }
        }

        $output .= ']';

        die($output);
    }


    /*
     * Add usertype permissions to the usertype (ajax)
     *
     * POST params: usertype_id, array permissions
     *
     * @return void
     */
    public function addUserTypePermission()
    {
        $view_data  = array();
        $account_id = $this->user_session->getAccountId();
        $post       = $this->request->request->all();

        $error = '';
        if (empty($post['usertype_id']) OR $post['usertype_id'] == '') {
            $error = 'Invalid User Type';    
        }

        if (! isset($post['permission_add']) AND ! isset($post['permission_remove'])) {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = 'No Change Provided';
        } else {
            if ($error === '') {
                $params = $post;
                $params['account_id'] = $account_id;
                $typepermissions = $this->user_logic->addUserTypePermission($params);
                if ($typepermissions) {
                    $ajax_data['code'] = 0;
                    $ajax_data['message'] = 'UserType Permission Update Successful';
                } else {
                    $ajax_data['code'] = 1;
                    $ajax_data['message'] = 'UserType Permission Error'; 
                }
            } else {
                $ajax_data['code'] = 1;
                $ajax_data['message'] = $error;
            }
        }

        $this->ajax_respond($ajax_data);
    }

}