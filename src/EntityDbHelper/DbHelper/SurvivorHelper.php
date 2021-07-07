<?php
namespace Src\EntityDbHelper\DbHelper;

use Src\EntityDbHelper\DbHelper;
use Src\DatabaseManager\SurvivorConnection;

class SurvivorHelper extends DbHelper
{

    public $survivorConnection = null;

    /**
     * SurvivorHelper constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->survivorConnection = new SurvivorConnection($this->db);
    }

    /**
     * @return mixed
     */
    public function getAllSurvivors()
    {
        return $this->survivorConnection->selectAll();
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getSurvivorByName($name)
    {
        return $this->survivorConnection->selectByName($name)[0];
    }

    /**
     * @param $survivor
     * @return mixed
     */
    public function addNewSurvivor($survivor)
    {

        $existSurvivor = $this->getSurvivorByName($survivor['name']);
        if($existSurvivor){
            header('HTTP/1.1 400 BAD REQUEST');
            echo "Survivor already exists, pick another name";
            exit();
        }
        return $this->survivorConnection->insert($survivor);
    }

}

