<?php

/**
 * Created by PhpStorm.
 * User: itboy
 * Date: 10/16/2015
 * Time: 9:58 AM
 */
class TableData
{
    private $values = array();
    private $condition = null;
    private $table;

    /**
     * TableData constructor.
     * @param $table
     */
    public function __construct($table)
    {
        $this->table = $table;
    }


    /**
     * @param $column
     * @param $value
     * @return $this
     */
    public function putDirectParam($column, $value)
    {
        $this->values[$column] = $value;
        return $this;
    }

    /**
     * @param string $column
     * @param $value
     * @param int $dataType
     * @param int $length
     * @return $this
     */
    public function putBindParam($column, $value, $dataType = null, $length = null)
    {
        if ($dataType === null && $length === null)
            $this->values["?"][$column] = $value;
        elseif ($dataType !== null)
            $this->values["?"][$column] = array($value, $dataType);
        else
            $this->values["?"][$column] = array($value, $dataType, $length);

        return $this;
    }

    /**
     * $position value start from 1 not from 0
     *
     * @param int $position
     * @param $value
     * @param int $dataType
     * @param int $length
     * @return $this
     */
    public function putConditionBindValue($position, $value, $dataType = null, $length = null)
    {
        $index = $position - 1;
        if ($dataType === null && $length === null)
            $this->condition["?"][$index] = $value;
        elseif ($dataType !== null)
            $this->condition["?"][$index] = array($value, $dataType);
        else
            $this->condition["?"][$index] = array($value, $dataType, $length);

        return $this;
    }

    public function removeConditionBindValue($position)
    {
        $index = $position - 1;
        if (isset($this->condition["?"][$index])) {
            unset($this->condition["?"][$index]);
            return true;
        }

        return false;
    }

    public function removeCondition()
    {
        if (isset($this->condition['condition'])) {
            $this->condition = null;
            return true;
        }

        return false;
    }

    public function getCondition()
    {
        return $this->condition;
    }

    public function setCondition($condition)
    {
        $this->condition["condition"] = $condition;
        return $this;
    }

    public function getDirectParam($column)
    {
        return $this->values[$column];
    }

    public function getBindParam($column)
    {
        return $this->values["?"][$column];
    }

    public function containDirectParam($column)
    {
        return isset($this->values[$column]);
    }

    public function containBindParam($column)
    {
        return isset($this->values["?"][$column]);
    }

    public function removeDirectParam($column)
    {
        if (isset($this->values[$column])) {
            unset($this->values[$column]);
            return true;
        }

        return false;
    }

    public function removeBindParam($column)
    {
        if (isset($this->values["?"][$column])) {
            unset($this->values["?"][$column]);
            return true;
        }

        return false;
    }

    public function isEmpty()
    {
        return ($this->size() < 1);
    }

    public function size()
    {
        return count($this->values);
    }

    public function getArray()
    {
        return $this->values;
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param mixed $table
     * @return $this
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }
}
