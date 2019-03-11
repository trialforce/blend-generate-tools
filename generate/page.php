<?php

namespace Generate;

class Page extends Base
{

    public function generate()
    {
        $className = ucfirst($this->tableName);

        $code = "<?php

namespace Page;

class {$className} extends \Page\Crud
{

}
";

        return $code;
    }

}
