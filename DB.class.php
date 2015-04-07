<?php

class DB {

    private static $_instance = null;
    private $_pdo,
            $_query,
            $_error = false,
            $_results,
            $_count = 0;
    private $HOST = 'localhost';
    private $DATABASE = 'pdo_test';
    private $USERNAME = 'root';
    private $PASSWORD = 'root';

    private function __construct() {
        try {
            $this->_pdo = new PDO('mysql:host=' . $this->HOST . '; dbname=' . $this->DATABASE, $this->USERNAME, $this->PASSWORD);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public static function getInstance() {
        if (!isset(self::$_instance)) {
            self::$_instance = new DB();
        }
        return self::$_instance;
    }

    private function initiate($query) {
        $this->_count = $query->_count;
        $this->_results = $query->_result;
        $this->_error = $query->_error;
        $this->_pdo = $query->_pdo;
        $this->_query = $query->_query;
    }

    public function query($sql, $params = array()) {
        $this->_error = false;
        if ($this->_query = $this->_pdo->prepare($sql)) {
            $x = 1;
            if (count($params)) {
                foreach ($params as $param) {
                    $this->_query->bindValue($x, $param);
                    $x++;
                }
            }
            if ($this->_query->execute()) {
                $this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
                $this->_count = $this->_query->rowCount();
            } else {
                $this->_error = true;
            }
            return $this;
        }
    }

    private function action($action, $table, $where = array(), $limit = NULL, $orderBy = array(), $groupBy = NULL) {
        if (count($where) === 3) {
            $operators = array('=', '>', '<', '>=', '<=', 'LIKE');

            $where_field = $where[0];
            $where_operator = $where[1];
            $where_value = $where[2];

            $orderBy_field = NULL;
            $orderBy_direction = 'ASC';

            if (count($orderBy) === 2) {
                $orderBy_field = $orderBy[0];
                if (in_array($orderBy[1], array('ASC', 'DESC'))) {
                    $orderBy_direction = $orderBy[1];
                } else {
                    $orderBy_field = NULL;
                }
            } else {
                $orderBy_field = NULL;
            }

            if (in_array($where_operator, $operators)) {
                $sql = "{$action} FROM {$table} WHERE {$where_field} {$where_operator} ?";

                if ($groupBy != NULL) {
                    $sql.= " GROUP BY $groupBy";
                }

                if ($orderBy_field != NULL) {
                    $sql.= " ORDER BY $orderBy_field $orderBy_direction";
                }

                if ($limit != NULL) {
                    $sql .= " LIMIT $limit";
                }

                if (!$this->query($sql, array($where_value))->_error) {
                    return $this;
                }
            }
        }
        return false;
    }

    /**
     * 
     * @param String $table
     * @param array $sql_data
     * $sql_data is an associative array which has elements 
     * {'where','group_by','order_by','limit'}
     * Syntax where: array({field},{operator},{value}). Eg: array('id','=',2)
     * Syntax order_by: array({field},{ASC/DESC}). Eg: array('name','DESC')
     * @return array
     */
    public function query_read($table, $sql_data) {
        $where = $sql_data['where'];
        $groupBy = $sql_data['group_by'];
        $orderBy = $sql_data['order_by'];
        $limit = $sql_data['limit'];

        if ($where == NULL) {
            $where = array('0', '=', '0');
        }

        return $this->action('SELECT *', $table, $where, $limit, $orderBy, $groupBy);
    }

    /**
     * 
     * @param String $table
     * @param String $field_to_return
     * @param array $sql_data $sql_data is an associative array which has elements {'where','group_by','order_by','limit'} 
     * Syntax where: array({field},{operator},{value}). Eg: array('id','=',2) 
     * Syntax order_by: array({field},{ASC/DESC}). Eg: array('name','DESC')
     * @return String 
     */
    public function query_value($table, $field_to_return, $sql_data) {
        $result = $this->query_read($table, $sql_data);
        if ($result->num_rows() > 0) {
            return $result->query_first()->$field_to_return;
        } else {
            return NULL;
        }
    }

    /**
     * 
     * @param String $table
     * @param array $where Syntax: array({field},{operator},{value}). Eg: array('id','=',2)
     * @return DBObject
     */
    public function query_delete($table, $where = array()) {
        return $this->action('DELETE', $table, $where);
    }

    /**
     * 
     * @param String $table
     * @param array $array_data Syntax: array({field}=>{new_value})
     * @param array $where Syntax: array({field},{operator},{value})
     * @return boolean TRUE for success, FALSE otherwise.
     */
    public function query_update($table, $array_data, $where = array()) {
        $set = '';
        $sql = '';
        $x = 1;

        $operators = array('=', '>', '<', '>=', '<=', 'LIKE');

        $where_field = $where[0];
        $where_operator = $where[1];
        $where_value = $where[2];

        foreach ($array_data as $name => $value) {
            $set .="{$name} = '{$value}'";
            if ($x < count($array_data)) {
                $set .= ', ';
            }
            $x++;
        }

        if ((count($where) === 3) && (in_array($where_operator, $operators))) {
            $sql = "UPDATE {$table} SET {$set} WHERE {$where_field} {$where_operator} ?";
        } else {
            $sql = "UPDATE {$table} SET {$set}";
        }

        if (!$this->query($sql, array($where_value))->error()) {
            return true;
        }
        return false;
    }

    /**
     * 
     * @param String $table
     * @param array $array_data
     * Syntax array_data: array({field}=>{value})
     * @return boolean
     */
    public function query_insert($table, $array_data = array()) {
        if (count($array_data)) {
            $keys = array_keys($array_data);
            $values = null;
            $x = 1;

            foreach ($array_data as $field) {
                $values .='?';
                if ($x < count($array_data)) {
                    $values .= ', ';
                }
                $x++;
            }

            $sql = "INSERT INTO {$table} (`" . implode('`, `', $keys) . "`) VALUES ({$values})";
            if (!$this->query($sql, $array_data)->error()) {
                return true;
            }
        }
        return false;
    }

    /**
     * 
     * @param type $query
     * @return int Number of rows in the resulting query.
     */
    public function num_rows($query = NULL) {
        if ($query != NULL) {
            $this->initiate($query);
        }
        return $this->_count;
    }

    /**
     * 
     * @param type $query
     * @return PDO PDO Object with 1st row data.
     */
    public function query_first($query = NULL) {
        if ($query != NULL) {
            $this->initiate($query);
        }
        return $this->results()[0];
    }

    /**
     * 
     * @param type $query
     * @return PDO PDO Objects with resulting data.
     */
    public function results($query = NULL) {
        if ($query != NULL) {
            $this->initiate($query);
        }
        return $this->_results;
    }

    /**
     * 
     * @param type $query
     * @return array Array containing reulting data.
     */
    public function fetch_array($query = NULL) {
        if ($query != NULL) {
            $this->initiate($query);
        }

        $columns = array();
        for ($i = 0; $i < $this->_query->columnCount(); $i++) {
            $columns[$i] = $this->_query->getColumnMeta($i)['name'];
        }
        $data = array();
        $x = 0;
        foreach ($this->_results as $row) {
            foreach ($columns as $column) {
                $data[$x][$column] = $row->$column;
            }
            $x++;
        }
        return $data;
    }

    /**
     * 
     * @return int Id of the last insert.
     */
    public function insert_id() {
        return $this->_pdo->lastInsertId();
    }

    /**
     * 
     * @param type $query
     * @return boolean TRUE if the operation is unsuccessful, FALSE otherwise. 
     */
    public function error($query = NULL) {
        if ($query != NULL) {
            $this->initiate($query);
        }
        return $this->_error;
    }

}
