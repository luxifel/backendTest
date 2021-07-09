<?php

namespace Src\DatabaseManager;

class InventoryConnection
{
    private $db = null;
    private $tbName = 'inventory';

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function selectAll()
    {
        $stmt = "
            SELECT 
                item, qty
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

    /**
     * @param $survivorId
     * @return mixed
     */
    public function selectBySurvivorId($survivorId)
    {
        $stmt = "
            SELECT 
                item, qty
            FROM
                $this->tbName
            WHERE id_survivor = :id_survivor;
        ";
        try {
            $stmt = $this->db->prepare($stmt);
            $stmt->bindParam(':id_survivor', $survivorId, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function insert(array $input)
    {
        $stmt = "
            INSERT INTO $this->tbName (id_survivor, item, qty)
            VALUES (:id_survivor, :item, :qty);
        ";
        try {
            $stmt = $this->db->prepare($stmt);
            $stmt->execute(
                [
                    'id_survivor' => $input['id_survivor'],
                    'item'        => $input['item'],
                    'qty'         => $input['qty'],
                ]
            );
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function update(array $input)
    {
        $stmt = "
            UPDATE $this->tbName 
            SET 
                id_survivor = :id_survivor,
                item = :item,
                qty = :qty
            WHERE
                id_survivor = :id_survivor 
              AND
               item = :item;   
        ";
        try {
            $stmt = $this->db->prepare($stmt);
            $stmt->bindParam(':id_survivor', $input['id_survivor'], \PDO::PARAM_INT);
            $stmt->bindParam(':item', $input['item'], \PDO::PARAM_STR);
            $stmt->bindParam(':qty', $input['qty'], \PDO::PARAM_INT);
            $stmt->execute();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

}
