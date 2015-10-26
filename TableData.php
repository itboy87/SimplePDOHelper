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

    public function putDirectParam($key, $value)
    {
        $this->values[$key] = $value;
    }

    public function putBindParam($key, $value)
    {
        $this->values["?"][$key] = $value;
    }

    public function getDirectParam($key)
    {
        return $this->values[$key];
    }

    public function getBindParam($key)
    {
        return $this->values["?"][$key];
    }

    public function containDirectParam($key)
    {
        return isset($this->values[$key]);
    }

    public function containBindParam($key)
    {
        return isset($this->values["?"][$key]);
    }

    public function removeDirectParam($key)
    {
        if (isset($this->values[$key])) {
            unset($this->values[$key]);
            return true;
        }

        return false;
    }

    public function removeBindParam($key)
    {
        if (isset($this->values["?"][$key])) {
            unset($this->values["?"][$key]);
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

    public function getAsDBInsertQuery($table_name)
    {
//        return DB::createPrepareInsertQuery($table_name,$this->getArray());
    }

    public function getArray()
    {
        return $this->values;
    }

    public function getQueryParamsToBind()
    {
//        return DB::getQueryParamsToBind($this->getArray());
    }
}
