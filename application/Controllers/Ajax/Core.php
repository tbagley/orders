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
class Core extends BaseAjax
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
        $this->ajax_respond('Hello World');

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

}