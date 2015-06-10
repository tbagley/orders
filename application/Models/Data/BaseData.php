<?php

namespace Models\Data;

use Models\Base;

class BaseData extends Base
{
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();

        $core =& get_instance();

        $this->db_write = $core->db_write;
        $this->db_read = $core->db_read;
    }
}