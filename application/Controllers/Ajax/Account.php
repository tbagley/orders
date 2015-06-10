<?php

namespace Controllers\Ajax;

use Models\Data\ContactData;
use Models\Logic\ContactLogic;
use GTC\Component\Utils\CSV\CSVBuilder;
use Models\Data\UserData;
use Models\Logic\UserLogic;

/**
 * Class Account
 *
 * Thin Controller for My Account CRUD
 *
 */
class Account extends BaseAjax
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
     * Get account info (called via ajax)
     *
     * @return void
     */
    public function ajax()
    {
        $ajax_data  = array();
        $user_id    = $this->user_session->getUserId();
        $post       = $this->request->request->all();

        if ($user_id) {
            $user = $this->user_logic->getUserById($user_id);
            if ($user !== false) {
                if (! empty($user) AND is_array($user)) {

                    $user = array_pop($user);

                    $ajax_data['code'] = 0;
                    $ajax_data['data']['user'] = $user;
                    $ajax_data['message'] = 'User Data Found;';

                    switch($post['action']) {

                        case       'update' :   $result = $this->user_logic->ajaxUpdate($user,$post['unit_id'],$post['fields'],$post['values']);
                                                $ajax_data['message'] = $result;
                                                break;

                                    default :   $ajax_data['message'] .= ' Processing unit_id: '. $post['unit_id'] . ';';
                                                $ajax_data['message'] .= ' Action: '. $post['action'] . ';';

                    }

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
     * Get account info (called via ajax)
     *
     * @return void
     */
    public function getMyAccountInfo()
    {
        $ajax_data  = array();
        $user_id    = $this->user_session->getUserId();

        if ($user_id) {
            $user = $this->user_logic->getUserById($user_id);
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
     * Update My Account Info (ajax)
     *
     * POST params: first_name, last_name, email
     *
     * @return void
     */ 
    public function updateMyAccountInfo()
    {
        $ajax_data  = array();
        $post       = $this->request->request->all();
        
        // validate POST parameters
        $result = $this->user_logic->validateAccountInfoPostData($post);
        if (($result['error'] == '') AND ! empty($result['params'])) {
            $user_id = $this->user_session->getUserId();
            $update = $this->user_logic->updateUserInfo($user_id, $result['params']);
            if ($update !== false) {
                $ajax_data['code'] = 0;
                $ajax_data['data'] = $post;
                $ajax_data['message'] = 'Updated Account Information';    
            } else {
                $errors = $this->user_logic->getErrorMessage();
                if (! empty($errors) AND is_array($errors)) {
                    $errors = implode(',',$errors);
                } else {
                    $errors = 'Failed to update account info due to database error';
                }
                
                $ajax_data['code'] = 1;
                $ajax_data['message'] = $ajax_data['validation_error'][] = $errors;
            }
        } else {
            $ajax_data['code'] = 1;
            $ajax_data['message'] = $ajax_data['validation_error'][] = $result['error'];        
        }
        
        $this->ajax_respond($ajax_data);
    }
}