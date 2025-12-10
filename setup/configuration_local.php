<?php
//Set root installation directory of the application
$config->set('basedir', 'E:/recipes');

//Debugging - enable or disable with true|false
$config->set('debug', false);

//Logging
$config->set('logger', false);
$config->set('logger_minimumloglevel', \Neodock\Framework\LogLevel::Informational());
$config->set('logger_logfile', $config->get('basedir') . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'neodock_recipes.log');

$config->set('baseurl', "https://recipes.neodock.net/recipes");

//Database
$config->set('db_type', 'sqlsrv');
$config->set('db_dsn', 'sqlsrv:Server=sqlservername,1433;Database=NeodockRecipes');
$config->set('db_login', 'neodockrecipes');
$config->set('db_password', 'supersecretpassword');

//Session management
$config->set('session_enable', true);
$config->set('session_config', true);
$config->set('session_storage', 'database');


//Recipes configuration
$config->set('enable_ratings', false);
$config->set('ratings_trusted_ips',
    [
        '127.0.0.1',          // localhost
        '10.0.0.0/8',         // local network range
        'hostname.ad.neodock.net' //hostname
    ]
);

//Enable admin functions?
$config->set('enable_admin', false);
$config->set('admin_trusted_ips',
    [
        '127.0.0.1',          // localhost
        '10.0.0.0/8',         // local network range
        'hostname.ad.neodock.net' //hostname
    ]
);