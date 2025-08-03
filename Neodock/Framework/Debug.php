<?php
namespace Neodock\Framework
{
    /**
     * Debug
     *
     * Debug provides facilities for logging, storing and retrieving Debugging information
     *
     * @version 1.0
     * @author jeffzahorchak
     */
    class Debug
    {
        /**
         * Singleton instance of Debug
         * @var Debug
         */
        private static $instance = null;

        /**
         * Debug messages
         * @var array
         */
        private $messages;

        /**
         * Initialization timestamp
         * @var \DateTime
         */
        private $initialized;

        private function __construct()
        {
            $this->initialized = new \DateTime();
            $this->messages = array();
            $this->messages[] = new DebugLog();
            $this->messages[0]->timestamp = $this->initialized;
            $this->messages[0]->text = "Debug initialized.";
        }

        /**
         * Gets an instance of the singleton debugger
         * @return Debug
         */
        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance = new Debug();
            }

            return self::$instance;
        }

        /**
         * Checks to see if debugging is active.  Returns true if both the configuration directive 'debug' is true and the 'debug' query string parameter is present.
         * @return bool
         */
        public static function isDebug()
        {
            $config = Configuration::getInstance();
            return ($config->get('debug') == true && isset($_GET['debug']));
        }

        /**
         * Logs a message into the debugger
         * @param string $message Message to log
         */
        public static function logMessage(string $message)
        {
            $msg = new DebugLog();
            $msg->timestamp = new \DateTime();
            $msg->text = $message;
            self::getInstance()->messages[] = $msg;
        }

        /**
         * Dumps out all of the debug entries and their timing information to stdout
         * @return void
         */
        public static function printAll()
        {
            echo '<hr/><pre>';
            for($i = 0; $i < count(self::getInstance()->messages); $i++)
            {
                if ($i == 0)
                {
                    echo '[' . self::getInstance()->messages[$i]->timestamp->format('Y-m-d H:i:s.u') . ' | elapsed 00:00:00.000000 | duration 00:00:00.000000 ] ' . self::getInstance()->messages[$i]->text . "\n";
                } else {
                    echo '[' . self::getInstance()->messages[$i]->timestamp->format('Y-m-d H:i:s.u') . ' | elapsed ' . self::getInstance()->messages[$i-1]->timestamp->diff(self::getInstance()->messages[$i]->timestamp)->format('%H:%I:%S.%F') . ' | duration ' . self::getInstance()->messages[0]->timestamp->diff(self::getInstance()->messages[$i]->timestamp)->format('%H:%I:%S.%F') . ' ] ' . self::getInstance()->messages[$i]->text . "\n";
                }
            }
            echo '</pre>';
        }

        /**
         * Writes out all of the debug entries and their timing information to Logger (at the Debug level)
         */
        public static function logAll()
        {
            for($i = 0; $i < count(self::getInstance()->messages); $i++)
            {
                if ($i == 0)
                {
                    Logger::getInstance()->debug('[' . self::getInstance()->messages[$i]->timestamp->format('Y-m-d H:i:s.u') . ' | elapsed 00:00:00.000000 | duration 00:00:00.000000 ] ' . self::getInstance()->messages[$i]->text);
                } else {
                    Logger::getInstance()->debug('[' . self::getInstance()->messages[$i]->timestamp->format('Y-m-d H:i:s.u') . ' | elapsed ' . self::getInstance()->messages[$i-1]->timestamp->diff(self::getInstance()->messages[$i]->timestamp)->format('%H:%I:%S.%F') . ' | duration ' . self::getInstance()->messages[0]->timestamp->diff(self::getInstance()->messages[$i]->timestamp)->format('%H:%I:%S.%F') . ' ] ' . self::getInstance()->messages[$i]->text);
                }
            }
        }
    }
}