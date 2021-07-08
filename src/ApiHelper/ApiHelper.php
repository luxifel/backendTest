<?php

namespace Src\ApiHelper;

use Src\EntityDbHelper\DbHelper\SurvivorHelper;
use Src\EntityDbHelper\DbHelper\InventoryHelper;
use Src\EntityDbHelper\DbHelper\TradePointsHelper;

class ApiHelper
{

    const STATUS_OK = 'HTTP/1.1 200 OK';
    const STATUS_ERROR = 'HTTP/1.1 400 BAD REQUEST';

    public $survivor;
    public $inventory;
    public $tradePoints;

    public function __construct()
    {
        $this->survivor = new SurvivorHelper();
        $this->inventory = new InventoryHelper();
        $this->tradePoints = new TradePointsHelper();
    }

    /**
     * @return array
     */
    public function getSurvivorDataFromPost()
    {
        $survivorData = [];
        if (!$_POST['name']) {
            $this->runBadRequest();
        }
        $survivorData['name'] = $_POST['name'];
        $survivorData['age'] = $_POST['age'];
        $survivorData['gender'] = $_POST['gender'];
        $survivorData['location'] = $_POST['location'];
        $survivorData['infected'] = 0;
        $survivorData['reported'] = 0;

        return $survivorData;
    }

    /**
     * @param $survivorId
     * @return array
     */
    public function setInventoryDataFromPost($survivorId)
    {
        $inventoryData = [];
        array_push($inventoryData,
            ['id_survivor' => $survivorId, 'item' => 'water', 'qty' => $_POST['water']],
            ['id_survivor' => $survivorId, 'item' => 'food', 'qty' => $_POST['food']],
            ['id_survivor' => $survivorId, 'item' => 'medication', 'qty' => $_POST['medication']],
            ['id_survivor' => $survivorId, 'item' => 'ammunition', 'qty' => $_POST['ammunition']]
        );

        return $inventoryData;
    }

    /**
     * @param $tradeBuyer
     * @param $tradeSeller
     * @return bool
     */
    public function tradeCanBeDone($tradeBuyer, $tradeSeller)
    {
        $survivorBuyerId = $this->getSurvivorIdByName($tradeBuyer['name']);
        $inventoryBuyer = $this->getInventoryBySurvivorId($survivorBuyerId);
        $itemsQtyBuyer = $this->getInventoryItemsQty($inventoryBuyer);

        $survivorSellerId = $this->getSurvivorIdByName($tradeSeller['name']);
        $inventorySeller = $this->getInventoryBySurvivorId($survivorSellerId);
        $itemsQtySeller = $this->getInventoryItemsQty($inventorySeller);

        $tablePoints = $this->getAllTradePoints();
        $itemPoints = $this->getItemPoints($tablePoints);

        $buyerPoints = $this->calculateTradePoints($tradeBuyer, $itemPoints, $itemsQtyBuyer);
        $sellerPoints = $this->calculateTradePoints($tradeSeller, $itemPoints, $itemsQtySeller);

        return $buyerPoints == $sellerPoints;
    }

    /**
     * @return mixed
     */
    public function getAllSurvivors()
    {
        return $this->survivor->getAllSurvivors();
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getSurvivorByName($name)
    {
        return $this->survivor->getSurvivorByName($name);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getSurvivorIdByName($name)
    {
        return $this->getSurvivorByName($name)['id_survivor'];
    }

    /**
     * @param $survivor
     */
    public function addNewSurvivor($survivor)
    {
        $this->survivor->addNewSurvivor($survivor);
    }

    /**
     * @param $survivorData
     */
    public function updateSurvivor($survivorData)
    {
        $this->survivor->updateSurvivor($survivorData);
    }

    /**
     * @param $survivorId
     * @return mixed
     */
    public function getInventoryBySurvivorId($survivorId)
    {
        return $this->inventory->getInventoryBySurvivorId($survivorId);
    }

    /**
     * @param $inventory
     * @return array
     */
    public function getInventoryItemsQty($inventory)
    {
        return $this->inventory->getInventoryItemsQty($inventory);
    }

    /**
     * @param $inventory
     * @return mixed
     */
    public function addNewInventory($inventory)
    {
        return $this->inventory->addNewInventory($inventory);
    }

    /**
     * @return mixed
     */
    public function getAllTradePoints()
    {
        return $this->tradePoints->getAllTradePoints();
    }

    /**
     * @param $tablePoints
     * @return array
     */
    public function getItemPoints($tablePoints)
    {
        return $this->tradePoints->getItemPoints($tablePoints);
    }

    /**
     * @param $trader
     * @param $itemPoints
     * @param $traderInventory
     * @return bool|float|int
     */
    public function calculateTradePoints($trader, $itemPoints, $traderInventory)
    {
        $result = 0;

        foreach ($itemPoints as $item => $point) {
            foreach ($trader as $key => $value) {
                if ($item !== $key) {
                    continue;
                }
                if (!$this->checkIfTraderHasItems($traderInventory, $key, $value)) {
                    return false;
                }
                $result = ($value * $point) + $result;
            }
        }

        return $result;
    }

    /**
     * @param $traderInventory
     * @param $traderItem
     * @param $itemQty
     * @return bool
     */
    public function checkIfTraderHasItems($traderInventory, $traderItem, $itemQty)
    {
        $result = true;
        foreach ($traderInventory as $item => $qty) {
            if ($item == $traderItem) {
                if ($itemQty < $qty) {
                    return false;
                }
            }
        }

        return $result;
    }

    /**
     * @param $a
     * @param $b
     * @return float|int
     *
     * $a : $b = result : 100
     */
    public function getPercentage(
        $a,
        $b
    ) {
        return ($a / $b) * 100;
    }

    /**
     * Exception Trigger
     */
    public function runBadRequest()
    {
        header(self::STATUS_ERROR);
        echo "Bad Request";
        exit();
    }
}