<?php
/**
 * Created by PhpStorm.
 * User: itboy
 * Date: 10/25/2015
 * Time: 6:23 PM
 */

require_once "/database/DB.php";

/**
 * Class PDOHelper
 */
class PDOHelper
{
    private $transactionRunning = false;
    private $transactionOwner = ""; //who begin transaction

    /** @var  PDO $db */
    private $db;

    /**
     * PDOHelper constructor.
     * @param PDO $db
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * @param TableData $tableData
     * @return bool
     */
    public function insertTD(TableData $tableData)
    {
        return $this->insert($tableData->getTable(), $tableData->getArray());
    }

    /**
     * insert values in table by
     * @param $table
     * @param array $tableData
     * @return bool
     */
    public function insert($table, array $tableData)
    {
        $result = false;
        $query = $this->createPreparedInsertQuery($table, $tableData);   //create insert query
        $values = $this->getQueryParamsToBind($tableData);   //get values to bind

        $stmt = $this->db->prepare($query); // create prepared statement

        $this->bindParameters($stmt, $values);
        try {
            $result = $stmt->execute();
        } catch (\PDOException $e) {
            $this->handle_sql_error($query, $e);
        }

        return $result;
    }

    /**
     * @param $table_name
     * @param $values
     * @return string
     */
    public function createPreparedInsertQuery($table_name, $values)
    {
        $query_keys = "INSERT INTO " . $table_name . "(";
        $query_holders = " VALUES(";

        $i = 0;
        foreach ($values as $key => $value) {
            if ($i != 0) {
                $query_keys .= ", ";
                $query_holders .= ", ";
            }

            //if there are values to bind then bind them
            if ($key === "?") {
                $j = 0;
                foreach ($value as $bind_key => $bind_value) {
                    if ($j != 0) {
                        $query_keys .= ", ";
                        $query_holders .= ", ";
                    }
                    $query_keys .= $bind_key;
                    $query_holders .= "?";

                    $j++;
                }

            } else {
                $query_keys .= $key;
                $query_holders .= $value;
            }

            $i++;
        }

        $query_keys .= ")";
        $query_holders .= ")";

        return $query_keys . $query_holders;
    }

    /**
     * return array of values which will bind
     *
     * @param $tableData
     * @return array
     */
    public function getQueryParamsToBind($tableData)
    {
        if (isset($tableData['?'])) {
            return array_values($tableData['?']);
        } else {
            return array();
        }
    }

    /**
     * @param PDOStatement $stmt
     * @param array $values
     */
    private function bindParameters(PDOStatement $stmt, array $values)
    {
        foreach ($values as $index => $value) {
            if (is_array($value)) {  //if $value is array then it have properties like type and length
                if (count($values[$index]) >= 3) {
                    //$values[$index][0] => value
                    //$values[$index][1] => type
                    //$values[$index][2] => length
                    $stmt->bindParam($index + 1, $values[$index][0], $values[$index][1], $values[$index][2]);
                } else {
                    //$values[$index][0] => value
                    //$values[$index][1] => type
                    $stmt->bindParam($index + 1, $values[$index][0], $values[$index][1]);
                }
            } else {  // $value is not type of array then it simply contain value
                //$values[$index][0] => value
                $stmt->bindParam($index + 1, $values[$index]);
            }
        }
    }

    private function handle_sql_error($query, $msg, $extra = null)
    {
        $error["query"] = $query;
        $error["extra"] = $extra;
        $error["msg"] = $msg;
//        file_put_contents('error/DB_Errors.json', json_encode($error)."\r\n", FILE_APPEND);
        echo "<h4 style='color: #FF0000;'>Exception caught</h4>";
        var_dump($error);
        die;
    }

    /**
     * @param TableData $tableData
     * @return int
     */
    public function updateTD(TableData $tableData)
    {
        return $this->update($tableData->getTable(), $tableData->getArray(), $tableData->getCondition());
    }

    /**
     * @param $table
     * @param array $tableValues
     * @param  $condition
     * @return int
     */
    public function update($table, array $tableValues, $condition)
    {
        $result = 0;

        //Create prepare statement from keys of values
        $query = $this->createUpdatePreparedQuery($table, $tableValues);

        //get column values to bind
        //later these column values array will merge with "condition" clause bind values if available
        $parameters = $this->getQueryParamsToBind($tableValues);


        $query .= $this->extractConditionClause($condition);

        //both arrays contain only values which will bind
        //merge column values and condition values
        $parameters = array_merge($parameters, $this->getQueryParamsToBind($condition));

        $stmt = $this->db->prepare($query);
        $this->bindParameters($stmt, $parameters);
        try {
            /*
                        var_dump($query);
                        var_dump($parameters);
                        die();
            */

            $stmt->execute();
            $result = $stmt->rowCount();

            //if error then exception will be thrown
            //If same data updated then rowCount return 0
        } catch (\PDOException $e) {
            $this->handle_sql_error($query, $e);
        }

        return $result;
    }

    public function createUpdatePreparedQuery($table_name, $values)
    {
        $update_query = "UPDATE " . $table_name . " SET ";

        $i = 0;
        foreach ($values as $key => $value) {
            if ($i != 0) {
                $update_query .= ", ";
            }

            //if there are values to bind then bind them
            if ($key === "?") {
                $j = 0;
                foreach ($value as $bind_key => $bind_value) {
                    if ($j != 0) {
                        $update_query .= ", ";
                    }
                    $update_query .= $bind_key . " = ?";

                    $j++;
                }

            } else {
                $update_query .= $key . " = " . $value;
            }

            $i++;
        }

        return $update_query;
    }

    /**
     * @param $condition
     * @return string
     */
    private function extractConditionClause($condition)
    {
        if (isset($condition['condition'])) {
            // concatenate condition clause
            return " " . $condition['condition'];
        }

        return "";
    }

    /**
     * @param TableData $tableData
     * @return int
     */
    public function deleteTD(TableData $tableData)
    {
        return $this->delete($tableData->getTable(), $tableData->getCondition());
    }

    /**
     * @param string $table
     * @param $condition
     * @return int
     */
    public function delete($table, $condition)
    {
        $result = 0;
        $query = "DELETE FROM " . $table;
        $query .= $this->extractConditionClause($condition);
        $values = $this->getQueryParamsToBind($condition);

        $stmt = $this->db->prepare($query);
        $this->bindParameters($stmt, $values);

        try {
            $stmt->execute();
            $result = $stmt->rowCount();
        } catch (\PDOException $e) {
            $this->handle_sql_error($query, $e);
        }

        return $result;
    }

    public function beginTransaction($owner = "")
    {
        if ($this->isTransactionRunning()) {
            $this->handle_sql_error(null, "DB Transaction Already Started By: " . $this->getTransactionOwner(), array("owner" => $owner));
        }
        $this->setTransactionRunning(true);
        $this->setTransactionOwner($owner);

        $this->db->beginTransaction();
    }

    /**
     * @return boolean
     */
    public function isTransactionRunning()
    {
        return $this->transactionRunning;
    }

    /**
     * @return string
     */
    public function getTransactionOwner()
    {
        return $this->transactionOwner;
    }

    /**
     * @param boolean $transactionRunning
     */
    private function setTransactionRunning($transactionRunning)
    {
        $this->transactionRunning = $transactionRunning;
    }

    /**
     * @param string $transactionOwner
     */
    private function setTransactionOwner($transactionOwner)
    {
        $this->transactionOwner = $transactionOwner;
    }

    /**
     * @param $success
     * @return mixed
     */
    public function endTransaction($success)
    {
        if ($this->isTransactionRunning()) {
            if ($success) {
                $this->commit();
            } else {
                $this->rollback();
            }
        }

        return $success;
    }

    public function commit()
    {
        $this->setTransactionRunning(false);
        $this->setTransactionOwner("");
        $this->db->commit();

    }

    public function rollback()
    {
        $this->setTransactionRunning(false);
        $this->setTransactionOwner("");
        $this->db->rollBack();
    }

    /*Getter and Setters*/

    public function select($query, $params = array(), $fetch_mode = PDO::FETCH_ASSOC)
    {
        $results = null;
        try {
            $stmt = $this->execute($query, $params);
            if ($stmt->rowCount()) {
                $results = $stmt->fetchAll($fetch_mode);
            }
            $stmt->closeCursor();
        } catch (\PDOException $e) {
            $this->handle_sql_error($query, $e);
        }

        return $results;
    }

    public function execute($query, $params = array())
    {
        $stmt = $this->db->prepare($query);

        if (is_array($params) && count($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }

        return $stmt;
    }

    public function lastInsertedId()
    {
        return $this->db->lastInsertId();
    }

    public function errorCode()
    {
        return $this->db->errorCode();
    }

    public function errorInfo()
    {
        return $this->db->errorInfo();
    }
}