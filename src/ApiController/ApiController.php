<?php

namespace Src\ApiController;

use Src\ApiHelper\ApiHelper;

class ApiController
{

    private $helper;
    private $requestMethod;
    private $uriPath;

    public function __construct($requestMethod, $uriPath)
    {
        $this->requestMethod = $requestMethod;
        $this->uriPath = $uriPath;
        $this->helper = new ApiHelper();
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
                $this->helper->runBadRequest();
                break;
        }

        header($response['status_code_header']);
        echo $response['body'];
    }

    /**
     * @return array
     */
    public function getActionCall(): array
    {
        if (!$this->uriPath[2]) {
            $this->helper->runBadRequest();
        }

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

    /**
     * @return array
     */
    public function getUpdateCall(): array
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

    /**
     * @return array
     */
    private function _getInfectedPercentage(): array
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

    /**
     * @return array
     */
    private function _getNonInfectedPercentage(): array
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

    /**
     * TODO
     */
    private function _getAverageAllResources()
    {
    }

    /**
     * TODO
     */
    private function _getLostPoints()
    {
    }

    /**
     * @return array
     */
    private function _addSurvivor(): array
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

    /**
     * @param $type
     * @return array
     */
    private function _updateSurvivor($type): array
    {
        $vars = json_decode(file_get_contents("php://input"), true);

        if (!$vars['name']) {
            $this->helper->runBadRequest();
        }

        $isLocationType = $type == 'location';

        if ($isLocationType && !$vars['location']) {
            $this->helper->runBadRequest();
        }

        if($isLocationType){
            $long = $vars['location']['longitude'];
            $lat = $vars['location']['latitude'];
            $location = sprintf('%s/%s', $long, $lat);
        }

        $survivorData = $this->helper->getSurvivorByName($vars['name']);
        $survivorData[$type] = $isLocationType ? $location : $survivorData[$type] + 1;

        $this->helper->updateSurvivor($survivorData);

        $message = 'Survivor: ' . $survivorData['name'] . ' Updated! ';

        if (!$isLocationType && $survivorData[$type] >= 3) {
            $survivorData['infected'] = 1;
            $this->helper->updateSurvivor($survivorData);
            $message .= $survivorData['name'] . ' is now infected!!';
        }

        $response['status_code_header'] = $this->helper::STATUS_OK;
        $response['body'] = json_encode($message);

        return $response;
    }

    /**
     * @return array
     */
    private function _tradeItems(): array
    {
        $vars = json_decode(file_get_contents("php://input"), true);

        if (!$vars['buyer'] || !$vars['seller'] || !$vars['buyer']['name'] || !$vars['seller']['name']) {
            $this->helper->runBadRequest();
        }

        $isTradeOk = $this->helper->tradeCanBeDone($vars['buyer'], $vars['seller']);

        if ($isTradeOk) {
            $this->helper->updateInventoryTraders($vars['buyer'], $vars['seller']);
        }

        $message = $isTradeOk ? 'Trade Done' : 'Trade cannot be done';

        $response['status_code_header'] = $this->helper::STATUS_OK;
        $response['body'] = json_encode($message);

        return $response;
    }

}