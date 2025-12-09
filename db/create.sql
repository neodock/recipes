USE master;
GO

sp_configure 'contained database authentication', 1;
GO
RECONFIGURE;
GO

CREATE DATABASE NeodockRecipes CONTAINMENT = PARTIAL;
GO

USE NeodockRecipes;
GO

CREATE TABLE dbo.dbmaintenance (
    id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    version varchar(255) NOT NULL,
    inprogress bit NOT NULL,
    dateadded DATETIME2 NOT NULL DEFAULT getdate()
);

CREATE UNIQUE INDEX IX_dbmaintenance_version ON dbo.dbmaintenance (version);
INSERT INTO dbo.dbmaintenance (version, inprogress) VALUES ('1.0.0', 0);

CREATE TABLE dbo.categories (
    id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    dateadded DATETIME2 NOT NULL DEFAULT getdate(),
    datemodified DATETIME2,
    datedeleted DATETIME2
);

CREATE UNIQUE INDEX IX_categories_name ON dbo.categories (name) WHERE datedeleted IS NULL;
CREATE NONCLUSTERED INDEX IX_categories_datedeleted ON dbo.categories (datedeleted);

CREATE TABLE dbo.recipes (
    id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    filepath VARCHAR(255) NOT NULL,
    description VARCHAR(MAX),
    dateadded DATETIME2 NOT NULL DEFAULT getdate(),
    datemodified DATETIME2,
    datedeleted DATETIME2,
    category_id BIGINT NOT NULL
);

CREATE UNIQUE INDEX IX_recipes_title ON dbo.recipes (title) WHERE datedeleted IS NULL;
CREATE NONCLUSTERED INDEX IX_recipes_filepath ON dbo.recipes (filepath);
CREATE NONCLUSTERED INDEX IX_recipes_datedeleted ON dbo.recipes (datedeleted);
CREATE NONCLUSTERED INDEX IX_recipes_category_id ON dbo.recipes (category_id);
ALTER TABLE dbo.recipes ADD CONSTRAINT FK_recipes_categories FOREIGN KEY (category_id) REFERENCES dbo.categories(id);

CREATE TABLE dbo.ratings (
    id BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    rating INT NOT NULL,
    dateadded DATETIME2 NOT NULL DEFAULT getdate(),
    datemodified DATETIME2,
    datedeleted DATETIME2,
    recipe_id BIGINT NOT NULL
);

CREATE NONCLUSTERED INDEX IX_ratings_datedeleted ON dbo.ratings (datedeleted);
CREATE NONCLUSTERED INDEX IX_ratings_recipe_id ON dbo.ratings (recipe_id);
ALTER TABLE dbo.ratings ADD CONSTRAINT FK_ratings_recipes FOREIGN KEY (recipe_id) REFERENCES dbo.recipes(id);

CREATE TABLE dbo.session (
    id VARCHAR(32) NOT NULL PRIMARY KEY,
    access DATETIME2 NOT NULL,
    data VARCHAR(MAX),
	client_ip VARCHAR(255)
);
GO

CREATE USER neodockrecipes WITH PASSWORD = 'neodockchef01F&';
ALTER ROLE [db_datareader] ADD MEMBER [neodockrecipes]
GO
USE [NeodockRecipes]
GO
ALTER ROLE [db_datawriter] ADD MEMBER [neodockrecipes]
GO