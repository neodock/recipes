<?php
    require_once(__DIR__ . '/../vendor/autoload.php');
    require_once(__DIR__ . '/../Neodock/Autoloader.php');
    $autoloader = new \Neodock\Autoloader();
    $autoloader->addNamespace('Neodock\Framework', '/../Neodock' . DIRECTORY_SEPARATOR . 'Framework', false);
    $autoloader->addNamespace('Neodock\Web', '/../Neodock' . DIRECTORY_SEPARATOR . 'Neodock' . DIRECTORY_SEPARATOR . 'Web', false);
    $autoloader->addNamespace('Neodock\Recipes', '/../Neodock' . DIRECTORY_SEPARATOR . 'Neodock' . DIRECTORY_SEPARATOR . 'Recipes', false);
    $autoloader->register();
    require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'configuration.php');