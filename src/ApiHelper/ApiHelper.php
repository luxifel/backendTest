<?php

namespace Src\ApiHelper;

use Src\EntityDbHelper\DbHelper\SurvivorHelper;
use Src\EntityDbHelper\DbHelper\InventoryHelper;
use Src\EntityDbHelper\DbHelper\TradePointsHelper;

class ApiHelper
{

    const STATUS_OK = 'HTTP/1.1 200 OK';
    const STATUS_ERROR = 'HTTP/1.1 400 BAD REQUEST';

    private $survivor;
    private $inventory;
    private $tradePoints;

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
     * @param $name
     * @return mixed
     */
    public function getSurvivorIdByName($name)
    {
        if (!$this->survivor) {
            $this->survivor = new SurvivorHelper();
        }

        return $this->survivor->getSurvivorByName($name)['id_survivor'];
    }

    /**
     * @param $survivorId
     * @return mixed
     */
    public function getInventoryBySurvivorId($survivorId)
    {
        if (!$this->inventory) {
            $this->inventory = new InventoryHelper();
        }

        return $this->inventory->getInventoryBySurvivorId($survivorId);
    }

    /**
     * @param $inventory
     * @return array
     */
    public function getInventoryItemsQty($inventory)
    {
        if (!$this->inventory) {
            $this->inventory = new InventoryHelper();
        }

        return $this->inventory->getInventoryItemsQty($inventory);
    }

    /**
     * @return mixed
     */
    public function getAllTradePoints()
    {
        if (!$this->inventory) {
            $this->tradePoints = new TradePointsHelper();
        }

        return $this->tradePoints->getAllTradePoints();
    }

    /**
     * @param $tablePoints
     * @return array
     */
    public function getItemPoints($tablePoints)
    {
        if (!$this->inventory) {
            $this->tradePoints = new TradePointsHelper();
        }

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