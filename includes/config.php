<?php
/**
 * Configuration file for Neodock Recipes
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Site configuration
define('SITE_NAME', 'Neodock Recipes');
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']));

// Database configuration for SQL Server
define('DB_SERVER', 'sql.ad.neodock.net');
define('DB_USER', 'recipes');
define('DB_PASSWORD', 'recipes'); // Change this!
define('DB_NAME', 'recipes');

// Recipes configuration
define('RECIPES_DIR', 'repo');
define('RECIPE_BASE_DIR', 'E:/inetpub/recipes/');

// Rating functionality configuration
define('RATING_ENABLED', true); // Master switch to enable/disable rating functionality
define('USE_IP_ALLOWLIST', true); // Enable IP allowlist for rating functionality
$RATING_IP_ALLOWLIST = [
    '127.0.0.1',          // localhost
    '10.0.0.0/8',         // Example corporate network range
    '1.2.3.4',
    'neodock-pc.ad.neodock.net', // Allow by hostname
]; 

// Session configuration
session_start();

// Admin configuration
define('ADMIN_ENABLED', true); // Enable admin functionality
$ADMIN_IP_ALLOWLIST = [
    '127.0.0.1',          // localhost
    '10.0.0.0/8',         // Example corporate network range
    '1.2.3.4',
    'neodock-pc.ad.neodock.net', // Allow by hostname
];
