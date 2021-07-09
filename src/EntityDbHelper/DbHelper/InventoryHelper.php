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
    public function getInventoryBySurvivorId($survivorId)
    {
        return $this->inventoryConnection->selectBySurvivorId($survivorId);
    }

    /**
     * @param $inventory
     * @return mixed
     */
    public function addNewInventory($inventory)
    {
        $existInventory = $this->getInventoryBySurvivorId($inventory['id_survivor']);
        if ($existInventory) {
            header('HTTP/1.1 400 BAD REQUEST');
            echo "Inventory already exists, pick another survivor name";
            exit();
        }

        foreach ($inventory as $item) {
            $this->inventoryConnection->insert($item);
        }
    }

    /**
     * @param $inventory
     * @return array
     */
    public function getInventoryItemsQty($inventory): array
    {
        $itemQty = [];
        foreach ($inventory as $value) {
            $item = '';
            foreach ($value as $key => $val) {
                if ($key == 'item') {
                    $item = $val;
                }
                if ($key == 'qty') {
                    $itemQty[$item] = $val;
                }
            }
        }

        return $itemQty;
    }

    public function updateInventory($inventory)
    {
        $this->inventoryConnection->update($inventory);
    }

}

