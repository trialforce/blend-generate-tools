<?php

namespace Generate;

class Combo extends Base
{

    public function generate()
    {
        $columns = \Db\Catalog\Mysql::listColums($this->tableName, FALSE);
        $class = ucfirst($this->tableName);

        $result = '<?php' . "\n\n";
        $result .= 'namespace Combo;' . "\n\n";
        $result .= 'class ' . $class . ' extends \View\Ext\Combo ' . "\n" . '{' . "\n\n";
        $result .= '    public static function getDataSource()' . "\n" . '    {' . "\n\n";
        $result .= '        $datasource = new \DataSource\Model( new \Model\\' . $class . '() );' . "\n\n";


        if (is_array($columns))
        {
            foreach ($columns as $column)
            {
                //$columns[] = new \View\Grid\Column( 'nome', 'Nome/Razão', \View\Grid\Column::ALIGN_LEFT, \Db\Column::TYPE_VARCHAR );
                $result .= '        $columns[] = new \View\Grid\Column( \'' . $column->getLabel() . '\', \'' . $column->getName() . '\', \View\Grid\Column::ALIGN_LEFT, \Db\Column::TYPE_VARCHAR );' . "\n";
            }
        }

        $result .= "\n" . '        $datasource->setColumns( $columns );' . "\n";
        $result .= "\n" . '        return $datasource;' . "\n    }\n}";

        return $result;
    }

}
