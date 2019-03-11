<?php

namespace Generate;

/**
 * Class used to generate code
 */
class Model extends Base
{

    public function generateProperties($columns, $comments, $publicProperties)
    {
        $result = '';
        //gera propriedades
        foreach ($columns as $column)
        {
            if ($comments)
            {
                $columnlabel = $column->getLabel() ? $column->getLabel() : $column->mountLabel();
                $phpType = $column->getPHPType();
                $result .= '    /**' . PHP_EOL;
                $result .= '     * ' . $columnlabel . PHP_EOL;
                $result .= '     * @var ' . $phpType . PHP_EOL;
                $result .= '     */' . PHP_EOL;
            }

            $visibility = $publicProperties ? 'public' : 'protected';

            $result .= '    ' . $visibility . ' $' . $column->getName() . ";" . PHP_EOL;
        }

        return $result;
    }

    public function generateColumns($columns)
    {
        $columnsCode = '        $columns = array();' . PHP_EOL;

        foreach ($columns as $column)
        {
            $columnlabel = $column->getLabel() ? $column->getLabel() : $column->mountLabel();
            $columnname = $column->getName();
            $type = mb_strtoupper($column->getType());
            $type = $type == 'INT' || $type == 'BIG_INT' ? 'INTEGER' : $type;
            $size = $column->getSize() > 0 ? $column->getSize() : 'NULL';
            $nullable = $column->getNullable() == 1 ? 'TRUE' : 'FALSE';
            $isPrimaryKey = $column->getIsPrimaryKey() == 1 ? 'TRUE' : 'FALSE';
            $defaultValue = $column->getDefaultValue();

            if (is_null($defaultValue))
            {
                $defaultValue = 'NULL';
            }
            else if (!is_numeric($defaultValue)) //string value
            {
                $defaultValue = '\'' . $defaultValue . '\'';
            }

            if ($defaultValue === 'CURRENT_TIMESTAMP')
            {
                $defaultValue = 'NOW';
            }

            $extra = $column->isAutoPrimaryKey() ? 'Column::EXTRA_AUTO_INCREMENT' : 'NULL';
            $columnsCode .= ' $columns[ \'' . $columnname . '\' ] = new Column( \'' . $columnlabel . '\', \'' . $columnname . '\', Column::TYPE_' . $type . ', ' . $size . ', ' . $nullable . ', ' . $isPrimaryKey . ', ' . $defaultValue . ', ' . $extra . ' );' . PHP_EOL;

            if ($column->getReferenceTable())
            {
                $referenceTable = $column->getReferenceTable();
                $referenceField = $column->getReferenceField();
                $columnsCode .= '        $columns[ \'' . $columnname . '\' ]->setReferenceTable(\'' . $referenceTable . '\',\'' . $referenceField . '\');' . PHP_EOL;
            }
        }

        return '
    /**
    * Column definition
    */
    public static function defineColumns()
    {
' . $columnsCode . '
        return $columns;
    }
}';
    }

    /**
     * Generate a model
     *
     * @param string $tableName
     * @param boolean $comment
     * @return string
     */
    function generate()
    {
        //configs
        $table = $this->tableName;
        $comments = TRUE;
        $publicProperties = FALSE;
        $columns = \Db\Catalog\Mysql::listColums($table, FALSE);
        $tableExists = \Db\Catalog\Mysql::tableExists($table, FALSE);

        $className = ucfirst($table);
        $label = $tableExists->label ? $tableExists->label : $className;

        $classCode = '<?php' . PHP_EOL . PHP_EOL . 'namespace Model;' . PHP_EOL . 'use \Db\Column as Column;' . PHP_EOL;
        $namespace = '\Model\\';
        $extends = '\Db\Model';

        if ($comments)
        {
            $classCode .= "
/**
* Model $label
*/
";
        }

        $classCode .= "class $className extends $extends\n{" . PHP_EOL;
        $classCode .= $this->generateProperties($columns, $comments, $publicProperties);

        //gera getters and setter
        if (!$publicProperties)
        {
            foreach ($columns as $column)
            {
                $column instanceof \Db\Column;
                $columnName = $column->getName();
                $functionName = ucfirst($columnName);
                $phpType = $column->getPHPType();
                $columnlabel = $column->getLabel() ? $column->getLabel() : $column->mountLabel();
                $commentLabel = lcfirst($columnlabel);

                if ($comments)
                {
                    $classCode .= '
    /**
    * Retorna o ' . $commentLabel . '
    * @return ' . $phpType . '
    */
';
                }

                if ($column->getType() == \Db\Column::TYPE_DATETIME)
                {
                    $returnCode = 'new \Type\Datetime($this->' . $columnName . ')';
                }
                else if ($column->getType() == \Db\Column::TYPE_DECIMAL)
                {
                    $returnCode = 'new \Type\Decimal($this->' . $columnName . ')';
                }
                else
                {
                    $returnCode = '$this->' . $columnName;
                }

                $classCode .= '    public function get' . $functionName . '()
    {
        return ' . $returnCode . ';
    }';

                if ($comments)
                {
                    /**
                     * Define o código
                     * @param int $id
                     * @return \Model\Noticia
                     */
                    $classCode .= '

    /**
    * Define o ' . $commentLabel . '
    * @param ' . $phpType . ' ' . $columnName . '
    * return ' . $namespace . $className . '
    */
';
                }

                $classCode .= '    public function set' . $functionName . '($' . $columnName . ')
    {
        $this->' . $columnName . ' = $' . $columnName . ';
        return $this;
    }
';
            }
        }

        $classCode .= $this->generateColumns($columns);

        return $classCode;
    }

}
