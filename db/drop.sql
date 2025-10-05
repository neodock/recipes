USE master;
GO

DECLARE @SQL nvarchar(max);

IF EXISTS (SELECT 1 FROM sys.databases WHERE name = 'NeodockRecipes')
    BEGIN
        SET @SQL =
                N'USE NeodockRecipes;
                  ALTER DATABASE NeodockRecipes SET SINGLE_USER WITH ROLLBACK IMMEDIATE;
                  USE master;
                  DROP DATABASE NeodockRecipes;';
        EXEC (@SQL);
        USE master;
    END;
GO