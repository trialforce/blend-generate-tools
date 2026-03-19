<?php

namespace Generate;

/**
 * Class used to generate code to intranet project
 */
class ModelCms extends \Generate\Model
{

    function generate($comments = TRUE, $publicProperties = FALSE)
    {
        $table = $this->tableName;

        $columns = \Db\Catalog\Mysql::listColums($table, FALSE);

        unset($columns['situacao']);
        unset($columns['idCriador']);
        unset($columns['idAlterador']);
        unset($columns['criacao']);
        unset($columns['alteracao']);
        unset($columns['idEmpresa']);
        unset($columns['situacao    ']);

        $tableExists = \Db\Catalog\Mysql::tableExists($table, FALSE);

        $classCode .= $this->generateHeader($tableExists, '\Model\Cms');
        $classCode .= $this->generateProperties($columns, $comments, $publicProperties);
        $classCode .= $this->generateGetSetter($columns, true);
        $columnsCode = $this->generateColumns($columns);
        $classCode .= str_replace('return $columns;', 'return array_merge( $columns, self::obterColunasPadrao());', $columnsCode);

        return $classCode;
    }

}
