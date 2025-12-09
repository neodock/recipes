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
         */
        private \Neodock\Framework\Database $db;

        /**
         * Sets recommended session configuration for a reasonably hardened HTTP(s) session
         */
        public static function configureSessionINI(): void
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
         * @param mixed $path
         * @param mixed $name
         */
        public function open($path, $name): bool
        {
            $debug = \Neodock\Framework\Debug::getInstance();
            $debug->logMessage("In Session::open.");

            $debug->logMessage('...returning true');
            return true;
        }

        /**
         * Closes a session
         */
        public function close(): bool
        {
            $debug = \Neodock\Framework\Debug::getInstance();
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
         * @param mixed $id
         */
        public function read($id): string|false
        {
            $debug = \Neodock\Framework\Debug::getInstance();
            $debug->logMessage("In Session:read(".$id.").");

            $this->db->query('SELECT data FROM dbo.session WHERE id = :id');
            $this->db->bind(':id', $id);

            if ($this->db->execute()) {
                $result = $this->db->resultset();

                if (count($result) > 0) {
                    if (is_null($result[0]['data'])) {
                        $debug->logMessage('...returning \'\' (empty string).');
                        return '';
                    }
                    $debug->logMessage('...returning \'' . $result[0]['data'] . '\'.');
                    return $result[0]['data'];
                }
                return '';
            } else {
                $debug->logMessage('...returning \'\' (empty string).');
                return '';
            }

        }

        /**
         * Persists a session to storage
         * @param mixed $id
         * @param mixed $data
         */
        public function write($id, $data): bool
        {
            $debug = \Neodock\Framework\Debug::getInstance();
            $debug->logMessage("In Session:write(".$id.").");

            $this->db->query('
                        MERGE INTO dbo.session AS target 
                        USING (VALUES(:id, CURRENT_TIMESTAMP, :data, :clientip)) AS source (id, access, data, client_ip) 
                        ON target.id = source.id
                        WHEN MATCHED THEN 
                            UPDATE SET 
                                access = source.access,
                                data = source.data,
                                client_ip = source.client_ip
                        WHEN NOT MATCHED THEN
                            INSERT (id, access, data, client_ip)
                            VALUES (source.id, source.access, source.data, source.client_ip);
                        ');
            $this->db->bind(':id', $id);
            $this->db->bind(':data', $data);
            $this->db->bind(':clientip', \Neodock\Recipes\AdminUtilities::GetClientIP());

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
         * @param mixed $id
         */
        public function destroy($id): bool
        {
            $debug = \Neodock\Framework\Debug::getInstance();
            $debug->logMessage("In Session:destroy(".$id.").");

            $this->db->query('DELETE FROM dbo.session WHERE id = :id');
            $this->db->bind(':id', $id);

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
         * @param mixed $max_lifetime
         */
        public function gc($max_lifetime): int|false
        {
            $debug = \Neodock\Framework\Debug::getInstance();
            $debug->logMessage("In Session:gc(".$max_lifetime.").");

            $this->db->query('DELETE FROM dbo.session WHERE access < DATEADD(SECOND, :maxlifetime, CURRENT_TIMESTAMP)');
            $this->db->bind(':maxlifetime', -$max_lifetime);

            if ($this->db->execute())
            {
                $debug->logMessage('...returning '. $this->db->rowCount() );
                return $this->db->rowCount();
            } else {
                $debug->logMessage('...returning false');
                return false;
            }
        }
	}
}