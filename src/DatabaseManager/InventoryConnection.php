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

    public function selectBySurvivorId(array $input)
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

            $stmt->execute(
                [
                    'id_survivor' => $input['id_survivor'],
                    'item'        => $input['item'],
                    'qty'         => $input['qty'],
                ]
            );

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

            var_dump($stmt);

        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function update($id_survivor, array $input)
    {
        $stmt = "
            UPDATE $this->tbName 
            SET 
                id_survivor = :id_survivor
                item = :item
                qty = :qty
            WHERE
                id_survivor = :id_survivor; 
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

            return $stmt->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

}
