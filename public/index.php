<?php
namespace Neodock
{
    try
    {
        $basedir = dirname(__DIR__);

        //initialize Composer autoloader
        require_once($basedir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

        //initialize Neodock Autoloader
        require_once($basedir . DIRECTORY_SEPARATOR . 'Neodock' . DIRECTORY_SEPARATOR . 'Autoloader.php');
        $autoloader = new Autoloader();
        $autoloader->addNamespace('Neodock\Framework', $basedir . DIRECTORY_SEPARATOR . 'Neodock' . DIRECTORY_SEPARATOR . 'Framework', false);
        $autoloader->addNamespace('Neodock\Web', $basedir . DIRECTORY_SEPARATOR . 'Neodock' . DIRECTORY_SEPARATOR . 'Web', false);
        $autoloader->addNamespace('Neodock\Recipes', $basedir . DIRECTORY_SEPARATOR . 'Neodock' . DIRECTORY_SEPARATOR . 'Recipes', false);
        $autoloader->register();

        //start debugging
        Framework\Debug::logMessage('Began processing within index.php');

        //setup and load configuration
        require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'configuration.php');

        //log page view
        Framework\Logger::getInstance()->informational('Request to ' . $_SERVER['REQUEST_URI'] . ' received.');

        //default page -- /Home/Index if not already in a proper rewritten URL
        if (!array_key_exists('controller', $_GET) || !array_key_exists('page', $_GET)) {
            $_GET['controller'] = 'Home';
            $_GET['page'] = 'Index';
        }

        //initialize session handler
        if (Framework\Configuration::get('session_config'))
        {
            Web\Session::configureSessionINI();
        }

        if (Framework\Configuration::get('session_enable') && Framework\Configuration::get('session_storage') == 'database')
        {
            $session = new Web\Session();
            session_set_save_handler($session, true);
            session_start();
        } else if (Framework\Configuration::get('session_enable')) {
            session_start();
        }

        $_SESSION['last_request_time'] = time();

        //handle global sort preference save to session
        if (!isset($_SESSION['sort'])) {
            $_SESSION['sort'] = Framework\Configuration::getInstance()->get('defaultsort');
        }

        if (isset($_GET['sort'])) {
            $_SESSION['sort'] = $_GET['sort'];
        }

        //handle theme, search, parameters, etc.
        // Get current theme preference (default to dark)
        $theme = $_COOKIE['theme'] ?? 'dark';

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

        header('HTTP/1.0 404 Not Found');
        echo "<h3>404 Not Found</h3>";
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