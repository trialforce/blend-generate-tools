<?php

use \DataHandle\Config;
use \DataHandle\Request;

require 'config.php';

header( 'Content-Type: text/html; charset=utf-8' );
header( 'X-Frame-Options: SAMEORIGIN' );
define( 'APP_PATH',  dirname( __FILE__ ));
ini_set("display_errors", "1"); //desabilita mostrar erros na tela
ini_set("log_errors", "1"); //habilita log de erros
ini_set("error_log", getcwd() . '/error.log');
ini_set('memory_limit', -1);

function filePath( $class, $extension = 'php' )
{
    $file = APP_PATH . '/' . strtolower( str_replace('\\','/',$class) ) . '.' . $extension;
    return $file;
}

function loadFile( $class )
{
    $filename = filePath( '/'. $class, 'php' );

    if ( is_file( $filename ) )
    {
        require $filename;
    }
}

spl_autoload_register( 'loadFile' );

$config[ 'defaultPage' ] = 'page\main';
$config[ 'response' ] = 'content';
$config[ 'dbConnType' ] = Request::get( 'dbConnType' );
$config[ 'dbConnHost' ] = Request::get( 'dbConnHost' );
$config[ 'dbConnName' ] = Request::get( 'dbConnName' );
$config[ 'dbConnUser' ] = Request::get( 'dbConnUser' );
$config[ 'dbConnPassword' ] = Request::get( 'dbConnPassword' );
$config[ 'dbConnDsn' ] = $config[ 'dbConnType' ] . ':host = ' . $config[ 'dbConnHost' ] . ';
dbname = ' . $config[ 'dbConnName' ];
Config::getInstance()->setData( $config );

$app = new App();
$app->handle();
