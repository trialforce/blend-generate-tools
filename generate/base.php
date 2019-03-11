<?php

namespace Generate;

abstract class Base
{

    protected $tableName;

    public function __construct($tableName)
    {
        $this->setTableName($tableName);
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    public abstract function generate();
}
