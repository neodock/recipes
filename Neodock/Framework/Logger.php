<?php
namespace Neodock\Framework
{
	/**
	 * Logger
	 *
	 * Logging class
	 *
	 * @version 1.0
	 * @author jeffzahorchak
	 */
	class Logger
	{
        /**
         * Singleton instance of Logger
         * @var Logger
         */
        private static $instance = null;

        private function __construct()
        {
        }

        /**
         * Gets an instance of the singleton logger
         * @return Logger
         */
        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance = new Logger();
            }

            return self::$instance;
        }

        /**
         * Provides a log message
         * @param LogLevel $level
         * @param string $message
         * @param array $context
         * @return void
         */
        public function log($level, $message, array $context = array())
        {
            $config = Configuration::getInstance();

            //is the level of this message <= the logging level from Config?
            if ($level->getLogLevelNumber() <= Configuration::get('logger_minimumloglevel')->getLogLevelNumber())
            {
                //add the {currentservertime} and {loglevel} values to the $context array, overwriting them if necessary
                $context['currentservertime'] = (new \DateTime())->format(\DateTime::W3C);
                $context['loglevel'] = $level->getLogLevelName();

                //format and interpolate message with context
                $replace = array();

                foreach ($context as $key => $val) {
                    // check that the value can be casted to string
                    if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                        $replace['{' . $key . '}'] = $val;
                    }
                }

                //prepend the log leader to the $message
                $message = '{currentservertime} | {loglevel} | ' . $message;

                //interpolate replacement values into the message text
                $message = strtr($message, $replace);

                //send message to error_log()... if logger_logfile is set, route there, else to system log
                if (null === $config->get('logger_logfile')) {
                    error_log($message);
                } else {
                    error_log($message . PHP_EOL, 3, $config->get('logger_logfile'));
                }
            }
        }

        /**
         * Provides an Emergency log message
         * @param string $message
         * @param array $context
         */
        public function emergency($message, array $context = array())
        {
            $this->log(LogLevel::Emergency(), $message, $context);
        }

        /**
         * Provides an Alert log message
         * @param string $message
         * @param array $context
         */
        public function alert($message, array $context = array())
        {
            $this->log(LogLevel::Alert(), $message, $context);
        }

        /**
         * Provides a Critical log message
         * @param string $message
         * @param array $context
         */
        public function critical($message, array $context = array())
        {
            $this->log(LogLevel::Critical(), $message, $context);
        }

        /**
         * Provides an Error log message
         * @param string $message
         * @param array $context
         */
        public function error($message, array $context = array())
        {
            $this->log(LogLevel::Error(), $message, $context);
        }

        /**
         * Provides a Warning log message
         * @param string $message
         * @param array $context
         */
        public function warning($message, array $context = array())
        {
            $this->log(LogLevel::Warning(), $message, $context);
        }

        /**
         * Provides a Notice log message
         * @param string $message
         * @param array $context
         */
        public function notice($message, array $context = array())
        {
            $this->log(LogLevel::Notice(), $message, $context);
        }

        /**
         * Provides an Informational log message
         * @param string $message
         * @param array $context
         */
        public function informational($message, array $context = array())
        {
            $this->log(LogLevel::Informational(), $message, $context);
        }

        /**
         * Provides a Debug log message
         * @param string $message
         * @param array $context
         */
        public function debug($message, array $context = array())
        {
            $this->log(LogLevel::Debug(), $message, $context);
        }
	}
}