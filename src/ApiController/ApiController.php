<?php

namespace Src\ApiController;

use Src\ApiHelper\ApiHelper;

class ApiController
{

    private $helper;
    private $requestMethod;
    private $uriPath;
    private $uriQuery;
    private $uriParams;

    public function __construct($requestMethod, $uriPath)
    {
        $this->requestMethod = $requestMethod;
        $this->uriPath = $uriPath;
        $this->processRequest();
        $this->helper = new ApiHelper();
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
                $this->helper->runBadRequest();
                break;
        }

        header($response['status_code_header']);
        echo $response['body'];
    }

    public function getActionCall()
    {
        if (!$this->uriPath[2]) {
            $this->helper->runBadRequest();
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
                $this->helper->runBadRequest();
                break;
        }

        return $result;
    }

    public function getUpdateCall()
    {
        if (!$this->uriPath[2]) {
            $this->helper->runBadRequest();
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
                $this->helper->runBadRequest();
                break;
        }

        return $result;
    }

    private function _getInfectedPercentage()
    {
        $infected = 0;
        $survivorsQty = 0;
        $survivors = $this->helper->getAllSurvivors();

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
        $percentage = intval($this->helper->getPercentage($infected, $survivorsQty));

        $message = "Infected Percentage = " . $percentage . "%";

        $response['status_code_header'] = $this->helper::STATUS_OK;
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
        $response['status_code_header'] = $this->helper::STATUS_OK;
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
            $this->helper->runBadRequest();
        }

        $survivorData = $this->helper->getSurvivorDataFromPost();
        $this->helper->addNewSurvivor($survivorData);

        $survivorId = $this->helper->getSurvivorIdByName($survivorData['name']);
        $inventoryData = $this->helper->setInventoryDataFromPost($survivorId);

        $this->helper->addNewInventory($inventoryData);

        $message = 'Survivor Created!';
        $response['status_code_header'] = $this->helper::STATUS_OK;
        $response['body'] = json_encode($message);

        return $response;
    }

    private function _updateSurvivor($type)
    {
        $vars = json_decode(file_get_contents("php://input"), true);
        if (!$vars['name']) {
            $this->helper->runBadRequest();
        }
        $isLocationType = $type == 'location';
        if ($isLocationType && !$vars['location']) {
            $this->helper->runBadRequest();
        }

        $survivorData = $this->helper->getSurvivorByName($vars['name']);
        $survivorData[$type] = $isLocationType ? $vars['location'] : $survivorData[$type] + 1;

        $this->helper->updateSurvivor($survivorData);

        $message = 'Survivor: ' . $survivorData['name'] . ' Updated! ';

        if (!$isLocationType && $survivorData[$type] >= 3) {
            $survivorData['infected'] = 1;
            $message .= $survivorData['name'] . ' is now infected!!';
        }

        $response['status_code_header'] = $this->helper::STATUS_OK;
        $response['body'] = json_encode($message);

        return $response;
    }

    private function _tradeItems()
    {
        $vars = json_decode(file_get_contents("php://input"), true);

        if (!$vars['buyer'] || !$vars['seller'] || !$vars['buyer']['name'] || !$vars['seller']['name']) {
            $this->helper->runBadRequest();
        }

        $isTradeOk = $this->helper->tradeCanBeDone($vars['buyer'], $vars['seller']);

        if ($isTradeOk) {
            $buyerId = $this->helper->getSurvivorIdByName($vars['buyer']['name']);
        }

        $message = $isTradeOk ? 'Trade Done' : 'Trade cannot be done';

        $response['status_code_header'] = $this->helper::STATUS_OK;
        $response['body'] = json_encode($message);

        return $response;
    }

}