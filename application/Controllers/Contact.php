<?php

namespace Controllers;

use Models\Data\ContactData;
use Models\Logic\ContactLogic;

/**
 * Class Contact
 *
 * Thin Controller for Contact CRUD
 *
 */
class Contact extends BasePage
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->load_db('master');
        $this->load_db('slave');
        
        $this->contact_data = new ContactData;
        $this->contact_logic = new ContactLogic;

    }
}