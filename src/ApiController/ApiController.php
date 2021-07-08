<?php

namespace Src\ApiController;

use Src\EntityDbHelper\DbHelper\SurvivorHelper;
use Src\EntityDbHelper\DbHelper\InventoryHelper;
use Src\EntityDbHelper\DbHelper\TradePointsHelper;


class ApiController
{
    const STATUS_OK = 'HTTP/1.1 200 OK';
    const STATUS_ERROR = 'HTTP/1.1 400 BAD REQUEST';
    private $survivor;
    private $inventory;
    private $tradePoints;
    private $requestMethod;
    private $uriPath;
    private $uriQuery;
    private $uriParams;

    public function __construct($requestMethod, $uriPath)
    {
        $this->requestMethod = $requestMethod;
        $this->uriPath = $uriPath;
        $this->processRequest();
    }

    public function processRequest()
    {
        $response = [];
        switch ($this->requestMethod) {
            case 'GET':
                $response = $this->getActionCall();
                break;
            case 'POST':
                $response = $this->_addSurvivor();
                break;
            case 'PUT':
                $response = $this->getUpdateCall();
                break;
            default:
                $this->runBadRequest();
                break;
        }

        header($response['status_code_header']);
        echo $response['body'];
    }

    public function getActionCall()
    {
        if (!$this->uriPath[2]) {
            $this->runBadRequest();
        }

        $this->_setParamsDataFromUriQuery();
        $result = [];
        switch ($this->uriPath[2]) {
            case 'getInfectedPercentage':
                $result = $this->_getInfectedPercentage();
                break;
            case 'getNonInfectedPercentage':
                $result = $this->_getNonInfectedPercentage();
                break;
            case 'getAverageAllResources':
                $this->_getAverageAllResources();
                break;
            case 'getLostPoints':
                $this->_getLostPoints();
                break;
            default:
                $this->runBadRequest();
                break;
        }

        return $result;
    }

    public function getUpdateCall()
    {
        if (!$this->uriPath[2]) {
            $this->runBadRequest();
        }
        $result = [];
        switch ($this->uriPath[2]) {
            case 'updateLocation':
                $result = $this->_updateSurvivor('location');
                breaK;
            case 'reportInfected':
                $result = $this->_updateSurvivor('reported');
                breaK;
            case 'tradeItems':
                $result = $this->_tradeItems();
                breaK;
            default:
                $this->runBadRequest();
                break;
        }

        return $result;
    }

    private function _getInfectedPercentage()
    {
        $infected = 0;
        $survivorsQty = 0;
        $this->survivor = new SurvivorHelper();
        $survivors = $this->survivor->getAllSurvivors();

        foreach ($survivors as $survivor) {
            $survivorsQty++;
            foreach ($survivor as $key => $value) {
                if ($key !== 'infected') {
                    continue;
                }

                if ($value == 0) {
                    continue;
                }

                $infected++;
            }
        }
        $percentage = intval($this->getPercentage($infected, $survivorsQty));

        $message = "Infected Percentage = " . $percentage . "%";

        $response['status_code_header'] = self::STATUS_OK;
        $response['infected'] = $infected;
        $response['survivorsQty'] = $survivorsQty;
        $response['percentage'] = $percentage;

        $response['body'] = json_encode($message);

        return $response;
    }

    private function _getNonInfectedPercentage()
    {
        $infectedResponse = $this->_getInfectedPercentage();
        $percentage = $infectedResponse['percentage'];
        $nonInfectedPercentage = intval(100 - $percentage);
        $message = "Non Infected Percentage = " . $nonInfectedPercentage . "%";
        $response['status_code_header'] = self::STATUS_OK;
        $response['nonInfectedPercentage'] = $nonInfectedPercentage;

        $response['body'] = json_encode($message);

        return $response;
    }

    private function _getAverageAllResources()
    {
    }

    private function _getLostPoints()
    {
    }

    private function _setParamsDataFromUriQuery()
    {
        $this->uriQuery = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        if (!$this->uriQuery) {
            $this->uriParams = null;

            return;
        }
        $params = [];
        parse_str($this->uriQuery, $params);

        $this->uriParams = $params;
    }

    private function _addSurvivor()
    {
        if ($this->uriPath[2] !== 'addSurvivor') {
            $this->runBadRequest();
        }

        $survivorData = $this->_getSurvivorDataFromPost();
        $this->survivor = new SurvivorHelper();
        $this->survivor->addNewSurvivor($survivorData);

        $this->inventory = new InventoryHelper();
        $survivorId = $this->survivor->getSurvivorByName($survivorData['name'])['id_survivor'];
        $inventoryData = $this->_getInventoryDataFromPost($survivorId);

        $this->inventory->addNewInventory($inventoryData);

        $message = 'Survivor Created!';
        $response['status_code_header'] = self::STATUS_OK;
        $response['body'] = json_encode($message);

        return $response;
    }

    private function _getSurvivorDataFromPost()
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

    private function _getInventoryDataFromPost($survivorId)
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

    private function _updateSurvivor($type)
    {
        $vars = json_decode(file_get_contents("php://input"), true);
        if (!$vars['name']) {
            $this->runBadRequest();
        }
        $isLocationType = $type == 'location';
        if ($isLocationType && !$vars['location']) {
            $this->runBadRequest();
        }

        $this->survivor = new SurvivorHelper();
        $survivorData = $this->survivor->getSurvivorByName($vars['name']);
        $survivorData[$type] = $isLocationType ? $vars['location'] : $survivorData[$type] + 1;

        $this->survivor->updateSurvivor($survivorData);

        $message = 'Survivor: ' . $survivorData['name'] . ' Updated! ';

        if (!$isLocationType && $survivorData[$type] >= 3) {
            $survivorData['infected'] = 1;
            $message .= $survivorData['name'] . ' is now infected!!';
        }

        $response['status_code_header'] = self::STATUS_OK;
        $response['body'] = json_encode($message);

        return $response;
    }

    private function _tradeItems()
    {
        $vars = json_decode(file_get_contents("php://input"), true);

        if(!$vars['buyer'] || !$vars['seller']){
            $this->runBadRequest();
        }

        $tradePoints = [];
        $this->tradePoints = new TradePointsHelper();
        $tradePoints = $this->tradePoints->getAllTradePoints();

        var_dump($tradePoints);

        $message = 'Trade Done';
        $response['status_code_header'] = self::STATUS_OK;
        $response['body'] = json_encode($message);

        return $response;
    }

    /**
     * @param $a
     * @param $b
     * @return float|int
     *
     * $a : $b = result : 100
     */
    public function getPercentage($a, $b)
    {
        return ($a / $b) * 100;
    }

    public function runBadRequest()
    {
        header(self::STATUS_ERROR);
        echo "Bad Request";
        exit();
    }

}