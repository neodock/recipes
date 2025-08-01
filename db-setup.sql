-- SQL Script for setting up Neodock Recipes database

-- Create database if it doesn't exist
IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = N'Recipes')
BEGIN
    CREATE DATABASE [Recipes];
END
GO

-- Use the database
USE [Recipes];
GO

-- Create recipes table if it doesn't exist
IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'recipes')
BEGIN
    CREATE TABLE recipes (
        id INT IDENTITY(1,1) PRIMARY KEY,
        path NVARCHAR(255) NOT NULL UNIQUE,
        title NVARCHAR(255) NOT NULL,
        category NVARCHAR(100) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
END
GO

-- Create ratings table if it doesn't exist
IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'ratings')
BEGIN
    CREATE TABLE ratings (
        id INT IDENTITY(1,1) PRIMARY KEY,
        recipe_id INT NOT NULL,
        rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 10),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (recipe_id) REFERENCES recipes(id)
    );
END
GO

-- Create index for faster lookups
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'IX_ratings_recipe_id' AND object_id = OBJECT_ID('ratings'))
BEGIN
    CREATE INDEX IX_ratings_recipe_id ON ratings(recipe_id);
END
GO

-- Create view for recipe ratings summary
IF NOT EXISTS (SELECT * FROM sys.views WHERE name = 'recipe_ratings_summary')
BEGIN
    EXEC('CREATE VIEW recipe_ratings_summary AS
    SELECT 
        r.id AS recipe_id,
        r.title,
        r.category,
        r.path,
        AVG(rt.rating) AS avg_rating,
        COUNT(rt.id) AS ratings_count
    FROM 
        recipes r
    LEFT JOIN 
        ratings rt ON r.id = rt.recipe_id
    GROUP BY 
        r.id, r.title, r.category, r.path');
END
GO
