<?php
/**
 * Setup script for Neodock Recipes
 * 
 * This script will create the necessary database and tables for the application.
 * Run this script once before using the application.
 */
require_once 'includes/admin.php';
require_once 'includes/config.php';

// Check if user is allowed to access admin
if (!is_admin_allowed()) {
    header('Location: ../index.php');
    exit;
}

// Connect to SQL Server (master database to create our app database)
$connectionInfo = array(
    "Database" => "master",
    "UID" => DB_USER,
    "PWD" => DB_PASSWORD
);

$conn = sqlsrv_connect(DB_SERVER, $connectionInfo);

if (!$conn) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

// Create database if it doesn't exist
$sql = "IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = N'" . DB_NAME . "')
        BEGIN
            CREATE DATABASE [" . DB_NAME . "];
        END";

$result = sqlsrv_query($conn, $sql);

if ($result === false) {
    die("Database creation failed: " . print_r(sqlsrv_errors(), true));
}

// Close the connection to master database
sqlsrv_close($conn);

// Connect to our application database
$connectionInfo = array(
    "Database" => DB_NAME,
    "UID" => DB_USER,
    "PWD" => DB_PASSWORD
);

$conn = sqlsrv_connect(DB_SERVER, $connectionInfo);

if (!$conn) {
    die("Connection to app database failed: " . print_r(sqlsrv_errors(), true));
}

// Create recipes table
$recipes_table_sql = "IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'recipes')
                      BEGIN
                          CREATE TABLE recipes (
                              id INT IDENTITY(1,1) PRIMARY KEY,
                              path NVARCHAR(255) NOT NULL UNIQUE,
                              title NVARCHAR(255) NOT NULL,
                              category NVARCHAR(100) NOT NULL,
                              created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                          );
                      END";

$result = sqlsrv_query($conn, $recipes_table_sql);

if ($result === false) {
    die("Recipes table creation failed: " . print_r(sqlsrv_errors(), true));
}

// Create ratings table
$ratings_table_sql = "IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'ratings')
                      BEGIN
                          CREATE TABLE ratings (
                              id INT IDENTITY(1,1) PRIMARY KEY,
                              recipe_id INT NOT NULL,
                              rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 10),
                              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                              FOREIGN KEY (recipe_id) REFERENCES recipes(id)
                          );
                      END";

$result = sqlsrv_query($conn, $ratings_table_sql);

if ($result === false) {
    die("Ratings table creation failed: " . print_r(sqlsrv_errors(), true));
}

// Close the connection
sqlsrv_close($conn);

// Success message
echo "<h1>Setup Completed Successfully</h1>";
echo "<p>The database and tables have been created.</p>";
echo "<p>YOU SHOULD IMMEDIATELY RESTRICT OR DELETE THE setup.php FILE IN THE ROOT OF THIS PROJECT!</p>";
echo "<p><a href='index.php'>Go to Neodock Recipes</a></p>";
