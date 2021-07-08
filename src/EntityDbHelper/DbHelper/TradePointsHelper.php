<?php
namespace Src\EntityDbHelper\DbHelper;

use Src\EntityDbHelper\DbHelper;
use Src\DatabaseManager\TradePointsConnection;


class TradePointsHelper extends DbHelper
{
    public $tradePointsConnection = null;

    /**
     * $tradePointsHelper constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->tradePointsConnection = new TradePointsConnection($this->db);
    }

    /**
     * @return mixed
     */
    public function getAllTradePoints()
    {
        return $this->tradePointsConnection->selectAll();
    }
}