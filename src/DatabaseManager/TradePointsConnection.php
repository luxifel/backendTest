<?php

namespace Src\DatabaseManager;

class TradePointsConnection
{
    private $db = null;
    private $tbName = 'trade_points';

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function selectAll()
    {
        $stmt = "
            SELECT 
                item, points
            FROM
                $this->tbName
        ";

        try {
            $stmt = $this->db->prepare($stmt);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function selectByItem($item)
    {
        $stmt = "
            SELECT 
                item, points
            FROM
                $this->tbName
            WHERE 
                item = :item;
        ";

        try {
            $stmt = $this->db->prepare($stmt);
            $stmt->execute(
                [
                    'item' => $item
                ]
            );

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
}
