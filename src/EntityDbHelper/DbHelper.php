<?php

namespace Src\EntityDbHelper;

use Src\DatabaseManager\DatabaseConnector;

class DbHelper
{

    public $db = null;

    /**
     * DbHelper constructor.
     */
    public function __construct()
    {
        $dbClass = new DatabaseConnector;
        $this->db = $dbClass->getConnection();
    }
}