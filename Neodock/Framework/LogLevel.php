<?php
namespace Neodock\Framework
{
	/**
	 * LogLevel
	 *
	 * LogLevel provides logging and debugging levels to the Neodock\Framework\Debug and Neodock\Framework\Log classes
	 *
	 * @version 1.0
	 * @author jeffzahorchak
	 */
	class LogLevel
	{
        /**
         * Emergency: system is unusable
         * @var integer
         */
        public const LOG_EMERGENCY = 0;

        /**
         * Alert: action must be taken immediately
         * @var integer
         */
        public const LOG_ALERT = 1;

        /**
         * Critical: critical conditions
         * @var integer
         */
        public const LOG_CRITICAL = 2;

        /**
         * Error: error conditions
         * @var integer
         */
        public const LOG_ERROR = 3;

        /**
         * Warning: warning conditions
         * @var integer
         */
        public const LOG_WARNING = 4;

        /**
         * Notice: normal but signficant condition
         * @var integer
         */
        public const LOG_NOTICE = 5;

        /**
         * Informational: informational messages
         * @var integer
         */
        public const LOG_INFORMATIONAL = 6;

        /**
         * Debug: debug-level messages
         * @var integer
         */
        public const LOG_DEBUG = 7;

        /**
         * LogLevel value
         * @var integer
         */
        private $level;

        /**
         * Initialize a LogLevel class
         * @param integer $level
         */
        private function __construct($level)
        {
            $this->level = $level;
        }

        /**
         * Debug
         * @return LogLevel
         */
        public static function Debug()
        {
            return new LogLevel(self::LOG_DEBUG);
        }

        /**
         * Informational
         * @return LogLevel
         */
        public static function Informational()
        {
            return new LogLevel(self::LOG_INFORMATIONAL);
        }

        /**
         * Notice
         * @return LogLevel
         */
        public static function Notice()
        {
            return new LogLevel(self::LOG_NOTICE);
        }

        /**
         * Warning
         * @return LogLevel
         */
        public static function Warning()
        {
            return new LogLevel(self::LOG_WARNING);
        }

        /**
         * Error
         * @return LogLevel
         */
        public static function Error()
        {
            return new LogLevel(self::LOG_ERROR);
        }

        /**
         * Critical
         * @return LogLevel
         */
        public static function Critical()
        {
            return new LogLevel(self::LOG_CRITICAL);
        }

        /**
         * Alert
         * @return LogLevel
         */
        public static function Alert()
        {
            return new LogLevel(self::LOG_ALERT);
        }

        /**
         * Emergency
         * @return LogLevel
         */
        public static function Emergency()
        {
            return new LogLevel(self::LOG_EMERGENCY);
        }

        /**
         * Returns the LogLevel Number (integer representation)
         * @return int
         */
        public function getLogLevelNumber()
        {
            return $this->level;
        }

        /**
         * Returns the LogLevel Name (string representation)
         * @return string
         */
        public function getLogLevelName()
        {
            return $this->__toString();
        }

        /**
         * @return string
         */
        public function __toString()
        {
            $output = '';

            switch($this->level) 
            {
                case self::LOG_ALERT:
                    $output = 'Alert';
                    break;
                case self::LOG_CRITICAL:
                    $output = 'Critical';
                    break;
                case self::LOG_DEBUG:
                    $output = 'Debug';
                    break;
                case self::LOG_EMERGENCY:
                    $output = 'Emergency';
                    break;
                case self::LOG_ERROR:
                    $output = 'Error';
                    break;
                case self::LOG_INFORMATIONAL:
                    $output = 'Informational';
                    break;
                case self::LOG_NOTICE:
                    $output = 'Notice';
                    break;
                case self::LOG_WARNING:
                    $output = 'Warning';
                    break;
            }
            return $output;
        }
	}
}