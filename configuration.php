<?php
namespace Neodock
{
    $config = Framework\Configuration::getInstance();

//Paths and Directories
    $config->set('rootdir', __DIR__);
    $config->set('layoutdir', __DIR__ . DIRECTORY_SEPARATOR . 'layouts');
    $config->set('modeldir', __DIR__  . DIRECTORY_SEPARATOR . 'models');
    $config->set('staticdir', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'static');
    $config->set('pagedir', __DIR__ . DIRECTORY_SEPARATOR . 'pages');
    $config->set('recipedir', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'repo');

//Debugging
    //(true|false) Enable debug mode.  To use, pass a 'debug=true' query string parameter to the site with this flag set to true.
    $config->set('debug', false);

//Logging
    //(true|false) Enable logging to file.
    $config->set('logger', false);

    //(LogLevel) Log at levels at or above this one.  For example, Framework\LogLevel::Error() would log Error, Critical, Alert and Emergency messages
    $config->set('logger_minimumloglevel', Framework\LogLevel::Error());

    //(string) full path to log file to write to
    //example: to log to a file:    $config->set('logger_logfile', __DIR__ . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'neodock_recipes.log');
    $config->set('logger_logfile', null);

//Sessions
    //(true|false) Enable sessions
    $config->set('session_enable', false);

    //(true|false) Enable setting recommended session config via ini_set
    $config->set('session_config', false);

    //Session storage provider (file, database)
    $config->set('session_storage', 'file');

//Site Setup
    //fully qualified or relative base url to prepend to generated links
    $config->set('baseurl', "https://recipes.neodock.net/recipes");

//Database
    //database type (mysql, pgsql, sqlsrv)
    $config->set('db_type', 'sqlsrv');

    //db server connection string
    //Example:  mysql:host=127.0.0.1;port=3306;dbname=neodock
    //Example:  pgsql:host=127.0.0.1;port=5432;dbname=neodock
    //Example:  sqlsrv:Server=127.0.0.1,1433;Database=neodock
    $config->set('db_dsn', 'sqlsrv:Server=sql2022.ad.neodock.net,1433;Database=NeodockRecipes');

    //db login/username
    $config->set('db_login', 'neodockrecipes');
    $config->set('db_password', 'neodockchef01F&');


//Recipes configuration
    //Enable ratings?
    $config->set('enable_ratings', true);
    $config->set('ratings_trusted_ips',
        [
        '127.0.0.1',          // localhost
        '10.0.0.0/8',         // Example corporate network range
        '71.251.155.66',      // Allow an IP address
        'neodock-pc.ad.neodock.net', // Allow by hostname
        ]
    );

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