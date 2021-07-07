<?php
namespace Src\EntityDbHelper\DbHelper;

use Src\EntityDbHelper\DbHelper;
use Src\DatabaseManager\InventoryConnection;

class InventoryHelper extends DbHelper
{

    public $inventoryConnection = null;

    /**
     * InventoryHelper constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->inventoryConnection = new InventoryConnection($this->db);
    }

    /**
     * @return mixed
     */
    public function getAllInventories()
    {
        return $this->inventoryConnection->selectAll();
    }

    /**
     * @param $survivorId
     * @return mixed
     */
    public function getInventoryBySurvivorId($inventory)
    {
        return $this->inventoryConnection->selectBySurvivorId($inventory)[0];
    }

    /**
     * @param $inventory
     * @return mixed
     */
    public function addNewInventory($inventory)
    {

        $existInventory = $this->getInventoryBySurvivorId($inventory);
        if($existInventory){
            header('HTTP/1.1 400 BAD REQUEST');
            echo "Inventory already exists, pick another survivor name";
            exit();
        }

        foreach ($inventory as $item){
            $this->inventoryConnection->insert($item);
        }
    }

}

