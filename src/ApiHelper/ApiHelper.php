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
    public function getSurvivorDataFromPost(): array
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
    public function setInventoryDataFromPost($survivorId): array
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
     * @param $survivorId
     * @return array
     */
    public function getInventoryItemsQtyBySurvivorId($survivorId): array
    {
        $survivorInventory = $this->getInventoryBySurvivorId($survivorId);

        return $this->inventory->getInventoryItemsQty($survivorInventory);
    }

    /**
     * @param $inventory
     * @return mixed
     */
    public function addNewInventory($inventory)
    {
        return $this->inventory->addNewInventory($inventory);
    }

    public function updateInventory($inventory)
    {
        $this->inventory->updateInventory($inventory);
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
    public function getItemPoints($tablePoints): array
    {
        return $this->tradePoints->getItemPoints($tablePoints);
    }

    /**
     * @param $tradeBuyer
     * @param $tradeSeller
     * @return bool
     */
    public function tradeCanBeDone($tradeBuyer, $tradeSeller): bool
    {
        $survivorBuyerId = $this->getSurvivorIdByName($tradeBuyer['name']);
        $itemsQtyBuyer = $this->getInventoryItemsQtyBySurvivorId($survivorBuyerId);

        $survivorSellerId = $this->getSurvivorIdByName($tradeSeller['name']);
        $itemsQtySeller = $this->getInventoryItemsQtyBySurvivorId($survivorSellerId);

        $tablePoints = $this->getAllTradePoints();
        $itemPoints = $this->getItemPoints($tablePoints);

        $buyerPoints = $this->calculateTradePoints($tradeBuyer, $itemPoints, $itemsQtyBuyer);
        $sellerPoints = $this->calculateTradePoints($tradeSeller, $itemPoints, $itemsQtySeller);

        return $buyerPoints == $sellerPoints;
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
    public function checkIfTraderHasItems($traderInventory, $traderItem, $itemRequestedQty): bool
    {
        $result = true;
        foreach ($traderInventory as $item => $qty) {
            if ($item == $traderItem) {
                if ($qty < $itemRequestedQty) {
                    return false;
                }
            }
        }

        return $result;
    }

    /**
     * @param $buyerTrade
     * @param $sellerTrade
     */
    public function updateInventoryTraders($buyerTrade, $sellerTrade)
    {
        $buyerId = $this->getSurvivorIdByName($buyerTrade['name']);
        $buyerInventory = $this->getInventoryItemsQtyBySurvivorId($buyerId);
        $sellerId = $this->getSurvivorIdByName($sellerTrade['name']);
        $sellerInventory = $this->getInventoryItemsQtyBySurvivorId($sellerId);
        $toSellerItems = $this->getToTraderItems($buyerInventory, $buyerTrade);
        $toBuyerItems = $this->getToTraderItems($sellerInventory, $sellerTrade);

        $this->updateFinalTraderInventory($buyerInventory, $toBuyerItems, $buyerId);
        $this->updateFinalTraderInventory($sellerInventory, $toSellerItems, $sellerId);
    }

    /**
     * @param $traderInventory
     * @param $trade
     * @return array
     */
    public function getToTraderItems(&$traderInventory, $trade): array
    {
        $result = [];

        foreach ($traderInventory as $itemTrader => $qtyTrader) {
            foreach ($trade as $itemTrade => $qtyTrade) {
                if ($itemTrader != $itemTrade) {
                    continue;
                }
                $traderInventory[$itemTrader] = $traderInventory[$itemTrader] - $qtyTrade;
                $result[$itemTrader] = $qtyTrade;
            }
        }

        return $result;
    }

    /**
     * @param $inventory
     * @param $items
     * @param $survivorId
     */
    public function updateFinalTraderInventory($inventory, $items, $survivorId)
    {
        $temporaryInventory = ['id_survivor' => $survivorId];
        foreach ($inventory as $key => $value) {
            if ($key == 'name') {
                continue;
            }
            foreach ($items as $item => $qty) {
                if ($key == $item) {
                    $value = $inventory[$key] + $qty;
                }
            }
            $temporaryInventory['item'] = $key;
            $temporaryInventory['qty'] =  $value;
            $this->updateInventory($temporaryInventory);
        }
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

