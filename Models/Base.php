<?php

namespace Models;

abstract class Base
{
    const MAX_TEXT_LENGTH = 1000;
    const MAX_VARCHAR_LENGTH = 255;

    protected $dbConnection;

    protected $id;

    public function __construct(int $id = null)
    {
        if (!is_null($id)) {
            $this->id = $id;
        }
    }

    abstract public function table() : string;
    abstract public function fields() : array;
    abstract public function primaryKeys() : array;
    abstract public function requiredFields() : array;

    protected function getDbConnection() : \PDO
    {
        if (is_null($this->dbConnection)) {
            $this->dbConnection = $this->openDbConnection();
        }

        return $this->dbConnection;
    }

    protected function openDbConnection() : \PDO
    {
        try {
            $pdo = new \PDO('mysql:host=' . \Config::server() . ';dbname=' . \Config::db(), \Config::user(), \Config::pass());
            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            return $pdo;
        } catch(\PDOException $e) {
            throw new \Exception('Database is down.');
        }
    }

    public function all() : array
    {
        $db = $this->getDbConnection();

        $stmt = $db->prepare('SELECT * FROM `' . $this->table() . '`');
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function toArray() : array
    {
        if (is_null($this->id)) {
            return [];
        }

        $db = $this->getDbConnection();

        $stmt = $db->prepare('SELECT * FROM `' . $this->table() . '` WHERE id = ?');
        $stmt->execute([$this->id]);
        $result = $stmt->fetchAll();

        if (count($result) == 0) {
            return [];
        }

        if (count($result) > 1) {
            throw new \Exception('Database data is broken.');
        }

        return current($result);
    }

    public function insert(array $params) : bool
    {
        if (!is_null($this->id)) {
            return false;
        }

        $this->validate($params, $this->requiredFields());

        $sql = 'INSERT INTO `' . $this->table() . '` (`' . implode('`, `', array_keys($params)) . '`)
                VALUES (' . implode(', ', array_fill(0, count($params), '?')) . ')';

        $stmt = $this->getDbConnection()->prepare($sql);

        $result = $stmt->execute(array_values($params));

        return $result === true ? true : false;
    }

    public function update(array $params, array $requiredParams) : bool
    {
        if (is_null($this->id)) {
            throw new \Exception('Wrong method called.');
        }

        if ($this->toArray() == []) {
            throw new \NotFoundException;
        }

        $this->validate($params, $requiredParams);

        $sql = 'UPDATE `' . $this->table() . '`
                SET `' . implode('` = ?,`', array_keys($params)) . '` = ?
                WHERE `id` = ?';

        $stmt = $this->getDbConnection()->prepare($sql);

        $result = $stmt->execute(array_merge(array_values($params), [$this->id]));

        return $result === true ? true : false;
    }

    public function delete() : bool
    {
        if (is_null($this->id)) {
            throw new \Exception('Wrong method called.');
        }

        if ($this->toArray() == []) {
            throw new \NotFoundException;
        }

        $stmt = $this->getDbConnection()->prepare('DELETE FROM `' . $this->table() . '` WHERE `id` = ?');
        $result = $stmt->execute([$this->id]);

        return $result === true ? true : false;
    }

    protected function validate(array $params, array $requiredParams = [])
    {
        $allowedFields = array_diff($this->fields(), $this->primaryKeys());

        foreach ($params as $field => $value) {
            if (!in_array($field, $allowedFields, true)) {
                throw new \ValidationException('Invalid field ' . $field . ' passed.');
            }
        }

        foreach ($requiredParams as $field) {
            if (!array_key_exists($field, $params)) {
                throw new \ValidationException('Missing required field ' . $field);
            }
        }

        $this->_validate($params);
    }

    protected function _validate(array $params)
    {
        // optionally implemented by children
    }

    public function fillableFields()
    {
        return array_diff($this->fields(), $this->primaryKeys());
    }
}