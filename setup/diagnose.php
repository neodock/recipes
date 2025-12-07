<?php
    function display(string $message): void {
        echo $message . PHP_EOL;
    }

    function displayline(): void {
        display('-------------------------------------------------------------------------------');
    }

    function displayblank(): void {
        display('');
    }

    display('Checking PHP environment for Neodock Recipe...');
    displayline();
    displayblank();

    display('Checking PHP version...');
    if (version_compare(PHP_VERSION, '8.3.0', '<')) {
        display('ERROR: PHP version '. PHP_VERSION .' NOT OK, must be at least 8.3.0');
        display('Please upgrade PHP to at least 8.3.0 and then rerun this script.');
        die();
    } else {
        display('PHP version ' . PHP_VERSION . ' is OK');
    }

    displayline();
    displayblank();

    display('Checking PHP extensions...');

    $neededextensions = [
        'bcmath',
        'bz2',
        'calendar',
        'ctype',
        'curl',
        'date',
        'dom',
        'fileinfo',
        'filter',
        'gd',
        'hash',
        'iconv',
        'json',
        'libxml',
        'mbstring',
        'openssl',
        'pcre',
        'PDO',
        'pdo_sqlsrv',
        'pgsql',
        'Phar',
        'random',
        'readline',
        'Reflection',
        'session',
        'SimpleXML',
        'sockets',
        'SPL',
        'sqlsrv',
        'standard',
        'tokenizer',
        'xml',
        'xmlreader',
        'xmlwriter',
        'xsl',
        'zip',
        'zlib'
    ];

    $installedextensions = get_loaded_extensions();
    $missingextensions = array_diff($neededextensions, $installedextensions);

    if (count($missingextensions) > 0) {
        display('ERROR: PHP extensions NOT OK, the following missing PHP extensions must be installed or enabled:');
        foreach ($missingextensions as $missingextension) {
            display(' - ' . $missingextension);
        }

        display('Please enable the above listed extensions in your php.ini file and then rerun this script.');
        die();
    } else {
        display('PHP extensions are OK');
    }

    displayline();
    displayblank();

    display('Checking Composer install...');
    $basedir = dirname(__FILE__, 2);
    $composerlocation = $basedir . DIRECTORY_SEPARATOR . 'composer.phar';

    if (!file_exists($composerlocation)) {
        display('ERROR: Composer is NOT OK, '. $composerlocation .' is missing, you need to install it and then run composer update.');
        display('Please download composer.phar from https://getcomposer.org/download/ and follow the commandline install instructions.');
        display('Once composer is installed, run "composer update" from the commandline in the root directory of this project, and then rerun this script.');
        die();
    } else {
        display('Composer install is OK');
    }

    displayline();
    displayblank();

    $projectroot = dirname(__FILE__, 2);
    $output = [];
    $returncode = 0;

    display('Installing/Updating Composer dependencies...');
    $composercommand = 'cd ' . $projectroot . ' && '. PHP_BINARY . ' ' . $composerlocation . ' --quiet update';
    exec($composercommand, $output, $returncode);

    if ($returncode !== 0) {
        display('ERROR: Composer dependencies could not be installed/updated, please check the output above for error details, resolve, and then rerun this script.');
        die();
    } else {
        display('Composer dependencies installed/updated OK');
    }

    displayline();
    displayblank();
    display('All checks passed, Neodock Recipes is ready to run!');