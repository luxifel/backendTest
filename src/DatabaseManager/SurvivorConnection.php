<?php

namespace Src\DatabaseManager;

class SurvivorConnection
{
    private $db = null;
    private $tbName = 'survivors';

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function selectAll()
    {
        $stmt = "
            SELECT 
                name, age, gender, location, infected, reported
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

    public function selectByName($name)
    {
        $stmt = "
            SELECT
                id_survivor, name, age, gender, location, infected, reported
            FROM
                $this->tbName
            WHERE
                name = :name;
        ";

        try {
            $stmt = $this->db->prepare($stmt);

            $stmt->execute(
                [
                    'name'     => $name
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
            INSERT INTO $this->tbName (name, age, gender, location, infected, reported) 
            VALUES (:name, :age, :gender, :location, :infected, :reported);
        ";
        try {
            $stmt = $this->db->prepare($stmt);
            $stmt->execute(
                [
                    'name'     => $input['name'],
                    'age'      => $input['age'],
                    'gender'   => $input['gender'],
                    'location' => $input['location'],
                    'infected' => $input['infected'],
                    'reported' => $input['reported'],
                ]
            );

            return $stmt->rowCount();

        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function update(array $input)
    {
        $stmt = "
            UPDATE $this->tbName 
            SET 
                name = :name,
                age = :age,
                gender = :gender,
                location = :location,
                infected = :infected,
                reported = :reported
            WHERE
                name = :name; 
        ";
        try {
            $stmt = $this->db->prepare($stmt);
            $stmt->execute(
                [
                    'name'     => $input['name'],
                    'age'      => $input['age'],
                    'gender'   => $input['gender'],
                    'location' => $input['location'],
                    'infected' => $input['infected'],
                    'reported' => $input['reported'],
                ]
            );

        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

}
