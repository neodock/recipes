<?php
namespace Neodock
{
    $config = Framework\Configuration::getInstance();

//Debugging
    //(true|false) Enable debug mode.  To use, pass a 'debug=true' query string parameter to the site with this flag set to true.
    $config->set('debug', true);

//Logging
    //(true|false) Enable logging to file.
    $config->set('logger', false);

    //(LogLevel) Log at levels at or above this one.  For example, Framework\LogLevel::Error() would log Error, Critical, Alert and Emergency messages
    $config->set('logger_minimumloglevel', Framework\LogLevel::Error());

//Sessions
    //(true|false) Enable sessions
    $config->set('session_enable', false);

    //(true|false) Enable setting recommended session config via ini_set
    $config->set('session_config', false);

    //Session storage provider (file, database)
    $config->set('session_storage', 'database');

//Site Setup
    //fully qualified or relative base url to prepend to generated links
    $config->set('baseurl', "https://neodock-pc.ad.neodock.net/recipes");

//Database
    //database type (mysql, pgsql, sqlsrv)
    $config->set('db_type', 'pgsql');

    //db server connection string
    //Example:  mysql:host=127.0.0.1;port=3306;dbname=neodock
    //Example:  pgsql:host=127.0.0.1;port=5432;dbname=neodock
    //Example:  sqlsrv:host=127.0.0.1;port=5432;dbname=neodock
    $config->set('db_dsn', 'pgsql:host=127.0.0.1;port=5432;dbname=neodock');

    //db login/username
    $config->set('db_login', 'databaseusername');
    $config->set('db_password', 'supersecret123');


//Recipes configuration
    //Enable ratings?
    $config->set('enable_ratings', true);
    $config->set('ratings_trusted_ips', [
        '127.0.0.1',          // localhost
        '10.0.0.0/8',         // Example corporate network range
        'neodock-pc.ad.neodock.net', // Allow by hostname
    ]);

    //Enable admin functions?
    $config->set('enable_admin', true);
    $config->set('admin_trusted_ips', [
        '127.0.0.1',          // localhost
        '10.0.0.0/8',         // Example corporate network range
        'neodock-pc.ad.neodock.net', // Allow by hostname
    ]);

//Error Handling
    //error page in
    $config->set('error_controller', 'Home');
    $config->set('error_page', 'Error');


//Default Config Override
//You can override or reset any default or custom configurations in configuration_local.php.  This file is not overwritten by Git.
    if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'configuration_local.php')) {
        include(__DIR__ . DIRECTORY_SEPARATOR . 'configuration_local.php');
    }
}