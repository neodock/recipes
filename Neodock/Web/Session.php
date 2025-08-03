<?php
namespace Neodock\Web
{
	/**
	 * Session
	 *
	 * Web-based session wrapper using database storage
	 *
	 * @version 1.0
	 * @author jeffzahorchak
	 */
	class Session implements \SessionHandlerInterface
	{
        /**
         * Stores a session-specific database connection (persistent)
         * @var \Neodock\Framework\Database
         */
        private $db;

        /**
         * Sets recommended session configuration for a reasonably hardened HTTP(s) session
         * @return void
         */
        public static function configureSessionINI()
        {
            $config = \Neodock\Framework\Configuration::getInstance();
            $debug = \Neodock\Framework\Debug::getInstance();

            if ($config->get('session_config'))
            {
                $debug->logMessage('Configuring Session INI settings...');

                ini_set('session.cookie_lifetime', 0);
                ini_set('session.use_cookies', 'On');
                ini_set('session.use_only_cookies', 'On');
                ini_set('session.cookie_httponly', 'On');
                if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
                    ini_set('session.cookie_secure', 'On');
                }
                ini_set('session.use_strict_mode', 'On');
                ini_set('session.sid_length', 128);
                ini_set('session.hash_function', 'sha256');
                ini_set('session.cache_limiter', 'nocache');
                ini_set('session.gc_maxlifetime', 3600);
                ini_set('session.gc_probability', 100);
                ini_set('session.gc_divisor', 100);
            } else {
                $debug->logMessage('Not configuring Session INI settings...');
            }
        }

        /**
         * Constructs a Session object
         */
        public function __construct()
        {
            $debug = \Neodock\Framework\Debug::getInstance();
            $debug->logMessage('In Session constructor.');

            $this->db = new \Neodock\Framework\Database(true);
        }

        /**
         * Opens a session
         * @param mixed $save_path 
         * @param mixed $session_name 
         * @return bool
         */
        public function open($save_path, $session_name)
        {
            global $debug;
            $debug->logMessage("In Session::open.");

            if ($this->db)
            {
                $debug->logMessage('...returning true');
                return true;
            } else {
                $debug->logMessage('...returning false');
                return false;
            }
        }

        /**
         * Closes a session
         * @return bool
         */
        public function close()
        {
            global $debug;
            $debug->logMessage("In Session::close.");

            if ($this->db->close())
            {
                $debug->logMessage('...returning true');
                return true;
            } else {
                $debug->logMessage('...returning false');
                return false;
            }
        }

        /**
         * Reads the value of a $session_id
         * @param mixed $session_id 
         * @return mixed
         */
        public function read($session_id)
        {
            global $debug;
            $debug->logMessage("In Session:read(".$session_id.").");

            $this->db->query('SELECT data FROM session WHERE id = :id');
            $this->db->bind(':id', $session_id);

            if ($this->db->execute())
            {
                $row = $this->db->single();
                if(is_null($row['data']))
                {
                    $debug->logMessage('...returning \'\' (empty string).');
                    return '';
                } else {
                    $debug->logMessage('...returning \''.$row['data'].'\'.');
                    return $row['data'];
                }
            } else {
                $debug->logMessage('...returning \'\' (empty string).');
                return '';
            }
        }

        /**
         * Persists a session to storage
         * @param mixed $session_id 
         * @param mixed $session_data 
         * @return bool
         */
        public function write($session_id, $session_data)
        {
            global $debug;
            $debug->logMessage("In Session:write(".$session_id.").");

            $access = time();

            $this->db->query('INSERT INTO session (id, access, data, client_ip) VALUES (:id, :access, :data, :clientip) ON CONFLICT (id) DO UPDATE SET access = :access2, data = :data2, client_ip = :clientip2;');
            $this->db->bind(':id', $session_id);
            $this->db->bind(':access', $access);
            $this->db->bind(':clientip', $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADR'] ?? '127.0.0.1');
            $this->db->bind(':data', $session_data);
            $this->db->bind(':access2', $access);
            $this->db->bind(':data2', $session_data);
            $this->db->bind(':clientip2', $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADR'] ?? '127.0.0.1');

            if ($this->db->execute())
            {
                $debug->logMessage('...returning true');
                return true;
            } else {
                $debug->logMessage('...returning false');
                return false;
            }
        }

        /**
         * Deactivates a session by session_id
         * @param mixed $session_id 
         * @return bool
         */
        public function destroy($session_id)
        {
            global $debug;
            $debug->logMessage("In Session:destroy(".$session_id.").");

            $this->db->query('DELETE FROM session WHERE id = :id');
            $this->db->bind(':id', $session_id);

            if ($this->db->execute())
            {
                $debug->logMessage('...returning true');
                return true;
            } else {
                $debug->logMessage('...returning false');
                return false;
            }
        }

        /**
         * Performs session garbage collection
         * @param mixed $maxlifetime 
         * @return bool
         */
        public function gc($maxlifetime)
        {
            global $debug;
            $debug->logMessage("In Session:gc(".$maxlifetime.").");

            $access = time() - $maxlifetime;

            $this->db->query('DELETE FROM session WHERE access < :access');
            $this->db->bind(':access', $access);

            if ($this->db->execute())
            {
                $debug->logMessage('...returning true');
                return true;
            } else {
                $debug->logMessage('...returning false');
                return false;
            }
        }
	}
}