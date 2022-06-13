<?php

namespace Page;

use \DataHandle\Session;
use \DataHandle\Request;

class Main extends \Page\Page
{

    public function __construct()
    {
        parent::__construct();

        $this->setBaseUrl();
        $this->addScript(BLEND_PATH . '/js/jquery.min.js');
        $this->addScript(BLEND_PATH . '/js/blend.js');
        $this->setTitle('Blend - Tools');
    }

    public function onCreate()
    {
        $this->setContentView('Page\Main');
        //restor fields value from session prefs.
        $this->byId('dbConnType')->setAttribute('value', Session::get('dbConnType'));
        $this->byId('dbConnHost')->setAttribute('value', Session::get('dbConnHost'));
        $this->byId('dbConnType')->setAttribute('value', Session::get('dbConnType'));
        $this->byId('dbConnName')->setAttribute('value', Session::get('dbConnName'));
        $this->byId('dbConnUser')->setAttribute('value', Session::get('dbConnUser'));
        $this->byId('dbConnPassword')->setAttribute('value', Session::get('dbConnPassword'));
    }

    public function saveConf()
    {
        \App::dontChangeUrl();
		
        if (strlen(Request::get('dbConnHost')) > 0)
        {
            Session::set('dbConnType', Request::get('dbConnType'));
            Session::set('dbConnHost', Request::get('dbConnHost'));
            Session::set('dbConnName', Request::get('dbConnName'));
            Session::set('dbConnUser', Request::get('dbConnUser'));
            Session::set('dbConnPassword', Request::get('dbConnPassword'));

            self::createConfigFromSession();
        }
        else
        {
            throw new \UserException('Not host defined');
        }

        $tables = \Db\Catalog\Mysql::listTables();

        if (is_array($tables))
        {
            foreach ($tables as $table)
            {
                if (!$table->label)
                {
                    $table->label = $table->name;
                }

                $options[$table->name] = $table->name . ' - ' . $table->label;
            }
        }

        $fileList = glob('generate/*.php');

        foreach ($fileList as $file)
        {
            $file = str_replace(array('generate/', '.php'), '', $file);

            if ($file == 'base')
            {
                continue;
            }

            $generate[$file] = $file;
        }

        $result[] = new \View\Label(null, null, 'Table name');
        $result[] = new \View\Br();
        $result[] = new \View\Select('tableName', $options);
        $result[] = new \View\Br();
        $result[] = new \View\Label(null, null, 'Generator');
        $result[] = new \View\Br();
        $result[] = new \View\Select('generate', $generate);
        $result[] = new \View\Br();
        $result[] = new \View\Br();
        $result[] = new \View\Button('btnGenerate', 'Generate', 'generate');
        $result[] = new \View\Br();
        $result[] = new \View\Br();
        $result[] = new \View\Div('result');

        return $result;
    }

    public function generate()
    {
        \App::dontChangeUrl();
        \App::setResponse('limbo');
        //var_dump($_REQUEST);
        $tableName = Request::get('tableName');
        $generate = Request::get('generate');

        $className = '\Generate\\' . $generate;

        if (!class_exists($className))
        {
            throw new \UserException('Class ' . $className . ' doesn\'t exist.');
        }

        $generate = new $className($tableName);
        $result = $generate->generate();

        $pre = new \View\Pre('', $result);

        $this->byId('result')->html($pre);

        return '';
    }

    public static function createConfigFromSession()
    {
        new \Db\ConnInfo('default', \DataHandle\Session::get('dbConnType'), \DataHandle\Session::get('dbConnHost'), \DataHandle\Session::get('dbConnName'), \DataHandle\Session::get('dbConnUser'), \DataHandle\Session::get('dbConnPassword'));
    }

}

\Page\Main::createConfigFromSession();
