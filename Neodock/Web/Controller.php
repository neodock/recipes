<?php
namespace Neodock\Web
{
	/**
	 * Controller
	 *
	 * Controller base class for MVC
	 *
	 * @version 1.0
	 * @author jeffzahorchak
	 */
	class Controller
	{
        /**
         * Template name
         * @var string
         */
        private $templateName;

        /**
         * Template contents
         * @var string
         */
        private $template;

        /**
         * Page name
         * @var string
         */
        private $pageName;

        /**
         * Page contents
         * @var string
         */
        private $page;

        /**
         * Page loaded indicator
         * @var bool
         */
        private $pageloaded;

        /**
         * Page title
         * @var string
         */
        private $title;

        /**
         * ViewVars collection
         * @var array
         */
        private $viewvars;

        /**
         * Constructs a new Controller class
         * @param mixed $templateName 
         * @throws \Exception 
         */
        public function __construct($templateName)
        {
            \Neodock\Framework\Debug::logMessage('Trying to load template \''.$templateName.'\'.');
            $templateName = preg_replace("/[^a-zA-Z0-9-_]/", "", $templateName);
            $this->templateName = $templateName;
            if (file_exists(\Neodock\Framework\Configuration::get('layoutdir') . DIRECTORY_SEPARATOR . $templateName . '.php')) {
                $this->template = file_get_contents(\Neodock\Framework\Configuration::get('layoutdir') . DIRECTORY_SEPARATOR . $templateName . '.php');
            } else {
                throw new \Exception('Controller was unable to load the layout at ' . \Neodock\Framework\Configuration::get('layoutdir') . DIRECTORY_SEPARATOR . $templateName . '.php');
            }
            $this->pageloaded = false;
            $this->title = "";
            $this->viewvars = array();
        }

        /**
         * Loads a page from the template
         * @param mixed $pageName Page to load
         * @throws PageNotFoundException 
         */
        public function LoadPage($pageName)
        {
            \Neodock\Framework\Debug::logMessage('Trying to load page \''.$pageName.'\'.');

            $pageName = preg_replace("/[^a-zA-Z0-9-_]/", "", $pageName);
            if (strlen($pageName) > 128 || !file_exists(\Neodock\Framework\Configuration::get('pagedir') . DIRECTORY_SEPARATOR . $this->templateName . DIRECTORY_SEPARATOR . $pageName . '.php'))
            {
                \Neodock\Framework\Debug::logMessage('Controller::Render was unable to load the page at ' . \Neodock\Framework\Configuration::get('pagedir') . DIRECTORY_SEPARATOR . $this->templateName . DIRECTORY_SEPARATOR . $pageName . '.php');
                throw new PageNotFoundException();
            }

            $this->page = file_get_contents(\Neodock\Framework\Configuration::get('pagedir') . DIRECTORY_SEPARATOR . $this->templateName . DIRECTORY_SEPARATOR . $pageName . '.php');
            $this->pageloaded = true;
        }

        /**
         * Sets the title for the currently loaded Page within the Controller
         * @param mixed $title Page title
         */
        public function setTitle($title)
        {
            \Neodock\Framework\Debug::logMessage('Setting controller page title to \'' . $title .'\'.');
            $this->title = $title;
        }

        /**
         * Sets or overwrites the value of a ViewVar within the Controller
         * @param mixed $variableName Name of the ViewVar to set
         * @param mixed $variableValue Value of the ViewVar to set
         */
        public function setViewVar($variableName, $variableValue)
        {
            $this->viewvars[$variableName] = $variableValue;
        }

        /**
         * Gets the value of a ViewVar within the Controller
         * @param mixed $variableName Name of the ViewVar to get
         * @return mixed
         */
        public function getViewVar($variableName)
        {
            if (array_key_exists($variableName, $this->viewvars))
            {
                return $this->viewvars[$variableName];
            }
            return null;
        }

        /**
         * Renders the Page against the Template set within the Controller
         * @throws \Exception 
         * @return mixed
         */
        public function Render()
        {
            if (!$this->pageloaded) {
                \Neodock\Framework\Debug::logMessage('Cannot render page, no page was loaded.');
                throw new \Exception('Cannot render page, controller has not had a LoadPage() call completed.');
            }

            \Neodock\Framework\Debug::logMessage('Start rendering template...');
            ob_start();
            eval('?>' . $this->page);
            $pagecontent = ob_get_clean();
            \Neodock\Framework\Debug::logMessage('...template rendering completed.');

            \Neodock\Framework\Debug::logMessage('Start rendering page...');
            ob_start();
            eval('?>' . $this->template);
            $templatecontent = ob_get_clean();
            \Neodock\Framework\Debug::logMessage('...page rendering completed.');

            $config = \Neodock\Framework\Configuration::getInstance();
            $baseurl = $config->get('baseurl');

            $output = str_replace('%%%PAGECONTENT%%%', $pagecontent, $templatecontent);
            $output = str_replace('%%%PAGETITLE%%%', $this->title, $output);
            $output = str_replace('%%%BASEURL%%%', $baseurl, $output);

            return $output;
        }

        /**
         * Generates an ActionLink using a Controller, Page, Link Text and Style(s)
         * @param string $controller Controller name for the link
         * @param string $page Page name for the link
         * @param string $text Link Text for the link
         * @param string $css CSS Style(s) to apply to the link
         * @return string
         */
        public static function ActionLink(string $controller, string $page, string $text, string $css = '')
        {
            $config = \Neodock\Framework\Configuration::getInstance();
            $baseurl = $config->get('baseurl');

            $link = '<a href="' . $baseurl . '/' . $controller . '/' . $page . '"';

            if (strlen($css) > 0)
            {
                $link .= ' class="' . $css . '"';
            }

            $link .= '>' . $text . '</a>';

            return $link;
        }
	}
}