<?php
namespace Neodock
{
    try
    {
        //initialize Composer autoloader
        require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

        //initialize Neodock Autoloader
        require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Neodock' . DIRECTORY_SEPARATOR . 'Autoloader.php');
        $autoloader = new Autoloader();
        $autoloader->addNamespace('Neodock\Framework', __DIR__ . DIRECTORY_SEPARATOR. '..' . DIRECTORY_SEPARATOR . 'Neodock' . DIRECTORY_SEPARATOR . 'Framework', false);
        $autoloader->addNamespace('Neodock\Web', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Neodock' . DIRECTORY_SEPARATOR . 'Web', false);
        $autoloader->addNamespace('Neodock\Recipes', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Neodock' . DIRECTORY_SEPARATOR . 'Recipes', false);
        $autoloader->register();

        //start debugging
        Framework\Debug::logMessage('Began processing within index.php');

        //setup and load configuration
        require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'configuration.php');

        //detect paths and add to config
        Framework\Configuration::set('rootdir', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
        Framework\Configuration::set('layoutdir', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'layouts');
        Framework\Configuration::set('modeldir', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'models');
        Framework\Configuration::set('staticdir', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'static');
        Framework\Configuration::set('pagedir', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'pages');
        Framework\Configuration::set('recipe_repo', __DIR__ . DIRECTORY_SEPARATOR . 'repo');

        //default page -- /Home/Index if not already in a proper rewritten URL
        if (!array_key_exists('controller', $_GET) || !array_key_exists('page', $_GET))
        {
            $_GET['controller'] = 'Home';
            $_GET['page'] = 'Index';
        }

        //initialize session handler
        if (Framework\Configuration::get('session_enable') && Framework\Configuration::get('session_storage') == 'database')
        {
            //Neodock\Web\Session() will configure and override session with the Neodock Session DB handler
            $session = new Web\Session();
        }

        //handle theme, search, parameters, etc.
        // Get current theme preference (default to dark)
        $theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'dark';

        // Get search query if exists
        $search_query = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';

        // Get category filter if exists
        $category_filter = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : '';

        // Get top rated recipes for homepage (up to 20)
        $top_rated_recipes = Recipes\RecipeUtilities::GetTopRatedRecipes(); // Using default limit of 20

        // Get recipe directories and recipes based on search/filter
        $recipes = Recipes\RecipeUtilities::GetRecipes($search_query, $category_filter);

        //initialize a controller with the name of the controller passed in
        $controller = new Web\Controller($_GET['controller']);
        $controller->LoadPage($_GET['page']);

        echo $controller->Render();

        //end execution
        Framework\Debug::logMessage('End of index.php');
    }
    catch (Web\PageNotFoundException $ex)
    {
        Framework\Debug::logMessage("In PageNotFound Handler...");
        if (!is_null(Framework\Configuration::get('error_controller')))
        {
            Framework\Debug::logMessage('Overriding controller to ' . Framework\Configuration::get('error_controller'));
            $controller = new Web\Controller(Framework\Configuration::get('error_controller'));
        }

        if (!is_null(Framework\Configuration::get('errorpage_404')))
        {
            Framework\Debug::logMessage('Overriding page to ' . Framework\Configuration::get('errorpage'));
            $controller->LoadPage(Framework\Configuration::get('errorpage'));
        } else {
            $controller->LoadPage('404');
        }
        header('HTTP/1.0 404 Not Found');
        echo $controller->Render();
    }
    catch (\Exception $ex) {
        Framework\Debug::logMessage('An unhandled exception occurred: ' . $ex->getMessage());
        Framework\Logger::getInstance()->alert('An unhandled exception occurred: ' . $ex->getMessage(), ['exception' => $ex]);
        echo 'An unhandled exception occurred: ' . $ex->getMessage() . '.';
    }
    finally
    {
        if (Framework\Debug::isDebug())
        {
            Framework\Debug::printAll();
        }

        Framework\Debug::logAll();
    }
}