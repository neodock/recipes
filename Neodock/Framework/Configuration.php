<?php
namespace Neodock\Framework
{
	/**
	 * Neodock Configuration
	 *
	 * Configuration storage and handler
	 *
	 * @version 1.0
	 * @author jeffzahorchak
	 */
	class Configuration {
        /**
         * Singleton instance
         * @var Configuration
         */
        private static $instance;

        /**
         * Configuration settings array
         * @var array
         */
        private $config;

        private function __construct()
        {
            Debug::logMessage('Initialized Configuration class.');

            $this->config = array();
        }

        /**
         * getInstance() returns the singleton Configuration object for further use
         * @return Configuration
         */
        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance = new Configuration();
            }

            return self::$instance;
        }

        /**
         * Adds a setting to the Configuration object
         *
         * @param string $key The configuration key.
         * @param mixed $value The value associated with the configuration key.
         * @return void
         */
        public static function set(string $key, $value)
        {
            if (is_string($value)) {
                Debug::logMessage('Attempting to set key \''.$key.'\' to value \''.$value.'\'');
            } else {
                Debug::logMessage('Attempting to set key \''.$key.'\' to a non-string value');
            }

            if (!array_key_exists($key, self::getInstance()->config))
            {
                Debug::logMessage('...value for '. $key .' stored successfully');
                self::getInstance()->config[$key] = $value;
            } else {
                Debug::logMessage('...value for '. $key . ' overwritten successfully');
                self::getInstance()->config[$key] = $value;
            }
        }

        /**
         * Retrieves a setting from the Configuration object
         *
         * @param string $key The configuration key.
         * @return mixed The value associated with the configuration key.
         */
        public static function get(string $key)
        {
            if (array_key_exists($key, self::getInstance()->config))
            {
                return self::getInstance()->config[$key];
            } else {
                Debug::logMessage('Key \'' . $key . '\' was not found.  Returning NULL.');
                return null;
            }
        }
    }
}