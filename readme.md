# Neodock Recipes
A PHP repository for cooking recipes

### Overview
This project was developed using Neodock's opensource PHP framework.  It supports SQL Server as a backend database.  It requires at least PHP 8.4 or newer.

### Installation Instructions
1. Create an empty folder (assuming C:\recipes)
2. Use git to clone this repository into the folder:
```git
git clone https://github.com/neodock/recipes.git C:\recipes
```     
3. Create a configuration_local.php file in the root of the project.   Copy any settings you would like to change from configuration.php.example and set them in this new file.
4. Configure IIS to point a virtual directory root to C:\recipes\public
5. Use the db folder to create your database.  Sample database schema is included for SQL Server.
6. Place any recipe files in the repo folder underneath of the C:\recipes\public folder.
7. Create a logs folder under C:\recipes
8. Browse to the site and enjoy!  Bon appetit!