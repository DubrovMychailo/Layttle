<?php

namespace core;

use PDO;
use PDOException;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
class DB
{
    public $pdo;
    private $db;

    public function __construct($host, $name, $login, $password)
    {
        try {
            $this->pdo = new PDO("mysql:host={$host};dbname={$name}", $login, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    protected function where($where)
    {
        if (is_array($where)) {
            $where_string = "WHERE ";
            $parts = [];
            $i = 0;
            $params = [];
            foreach ($where as $key => $value) {
                if ($key === 'OR' && is_array($value)) {
                    $orParts = [];
                    foreach ($value as $condition) {
                        foreach ($condition as $field => $val) {
                            $paramName = "param" . $i++;
                            $orParts[] = "{$field} LIKE :$paramName";
                            $params[$paramName] = $val;
                        }
                    }
                    $parts[] = '(' . implode(' OR ', $orParts) . ')';
                } else {
                    $paramName = "param" . $i++;
                    $parts[] = "{$key} = :$paramName";
                    $params[$paramName] = $value;
                }
            }
            $where_string .= implode(' AND ', $parts);
        } elseif (is_string($where)) {
            $where_string = $where;
            $params = [];
        } else {
            $where_string = '';
            $params = [];
        }
        return [$where_string, $params];
    }

    public function select($table, $fields = '*', $where = '', $params = [])
    {
        $sql = "SELECT $fields FROM $table";

        if (!empty($where)) {
            if (is_array($where)) {
                list($where_string, $query_params) = $this->where($where);
                $sql .= " $where_string";
                $params = array_merge($params, $query_params);
            } else {
                if (strpos($where, 'WHERE') === false) {
                    $sql .= " WHERE $where";
                } else {
                    $sql .= " $where";
                }
            }
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function selectOne($table, $where = [])
    {
        $sql = "SELECT * FROM {$table} ";
        list($where_string, $params) = $this->where($where);
        if ($where_string) {
            $sql .= $where_string;
        }

        error_log("SQL: $sql | Params: " . print_r($params, true), 3, 'D:\wamp64\domains\Layttle\error_cms.txt');

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert($table, $row_to_insert)
    {
        $fields_list = implode(", ", array_keys($row_to_insert));
        $params_array = [];
        foreach ($row_to_insert as $key => $value) {
            $params_array[] = ":{$key}";
        }
        $params_list = implode(",", $params_array);
        $sql = "INSERT INTO {$table} ({$fields_list}) VALUES ({$params_list})";
        $sth = $this->pdo->prepare($sql);
        foreach ($row_to_insert as $key => $value)
            $sth->bindValue(":{$key}", $value);
        $sth->execute();
        return $sth->rowCount();
    }

    public function delete($table, $where)
    {
        list($where_string, $params) = $this->where($where);
        $sql = "DELETE FROM {$table} {$where_string}";
        $sth = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $sth->bindValue(":{$key}", $value);
        }
        $sth->execute();
        return $sth->rowCount();
    }

    public function update($table, $data, $where)
    {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "$key = :$key";
        }
        $set = implode(', ', $set);

        $conditions = [];
        foreach ($where as $key => $value) {
            $conditions[] = "$key = :where_$key";
        }
        $conditions = implode(' AND ', $conditions);

        $sql = "UPDATE $table SET $set WHERE $conditions";
        $stmt = $this->pdo->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        foreach ($where as $key => $value) {
            $stmt->bindValue(":where_$key", $value);
        }

        return $stmt->execute();
    }

    public function selectRaw($query, $params = [])
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getUserDataByLogin($login)
    {
        if (empty($login) || strlen($login) < 3) {
            return null;
        }

        $result = $this->db->selectOne('users', ['login' => $login]);
        if (!$result) {
            return null;
        }
        return $result;
    }
}
