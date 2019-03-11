<?php

namespace Generate;

class FieldLayout extends Base
{

    public function generate()
    {
        $columns = \Db\Catalog\Mysql::listColums($this->tableName, FALSE);

        $result = '$array = array();' . "\n";

        if (is_array($columns))
        {
            foreach ($columns as $column)
            {
                $result .= '$array[] = array(\'' . $column->getName() . '\'=> \'span6\' );' . "\n";
            }
        }

        $result .= "\n" . 'return new \FieldLayout\Vector( $array, $this->model);' . "\n";

        return $result;
    }

}
