<?php

class PersonDao
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function insert(Person $person)
    {
        $stmt = $this->db->prepare("INSERT INTO person(name) VALUES (?)");
        $stmt->bindParam(1, $person->getName());
        $stmt->execute();

        $id = $this->db->lastInsertId();

        $person->setId($id);

        unset($stmt);

        return $person;
    }

    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT id, name FROM person WHERE id = ?");
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch();
        $person = new Person($row["id"], $row["name"]);

        unset($stmt);

        return $person;
    }

    public function listPeople()
    {
        $stmt = $this->db->prepare("SELECT id, name FROM person ORDER BY id");
        $stmt->execute();

        $people = array();
        if ($stmt->execute()) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $person = new Person($row["id"], $row["name"]);
                $people[] = $person;
            }
        }

        unset($stmt);

        return $people;
    }

    public function update(Person $person)
    {
        $stmt = $this->db->prepare("UPDATE person SET name = ?  WHERE id = ?");
        $stmt->bindParam(1, $person->getName());
        $stmt->bindParam(2, $person->getId());
        $stmt->execute();

        unset($stmt);

        return $person;
    }
}