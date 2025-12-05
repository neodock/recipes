<?php
namespace Neodock\Framework
{
	/**
	 * Database wrapper
	 *
	 * Provides database services for MySQL or Postgres
	 *
	 * @version 1.0
	 * @author jeffzahorchak
	 */
	class Database
	{
        /**
         * Database username
         * @var string
         */
        private $dbuser;

        /**
         * Database password
         * @var string
         */
        private $dbpass;

        /**
         * Database type
         * @var string
         */
        private $dbtype;

        /**
         * Database connection string 
         * @var string
         */
        private $dbdsn;

        /**
         * Database connection handle
         * @var \PDO
         */
        private $dbh;

        /**
         * Last PDO error
         * @var string
         */
        private $error;

        /**
         * PDO Statement
         * @var \PDOStatement
         */
        private $stmt;

        /**
         * @throws \Exception
         */
        public function __construct(bool $for_session = false){
            //initialize values
            Debug::logMessage('Initialized Database class.');

            $this->dbtype = Configuration::get('db_type');
            $this->dbdsn = Configuration::get('db_dsn');
            $this->dbuser = Configuration::get('db_login');
            $this->dbpass = Configuration::get('db_password');

            // Set options
            $options = array(
                \PDO::ATTR_ERRMODE       => \PDO::ERRMODE_EXCEPTION
            );
            // Create a new PDO instanace
            try{
                $this->dbh = new \PDO($this->dbdsn, $this->dbuser, $this->dbpass, $options);
            }
            // Catch any errors
            catch(\PDOException $e){
                $this->error = $e->getMessage();
                Debug::logMessage('A PDO error occurred: ' . $this->error . '.');
                throw new \Exception('Unable to connect to the database.',0,$e);
            }
        }

        /**
         * Sets and prepares a database query aganst the currently connected database
         * @param string $query
         */
        public function query(string $query): void
        {
            Debug::logMessage('Executing SQL query: ' . $query . '.');

            $this->stmt = $this->dbh->prepare($query);
        }

        /**
         * Binds a parameter of the optionally specified type against the current query
         * @param string $param
         * @param mixed $value
         * @param string|null $type
         */
        public function bind(string $param, mixed $value, ?string $type = null): void
        {
            if (is_null($type)) {
                switch (true) {
                    case is_int($value):
                        $type = \PDO::PARAM_INT;
                        break;
                    case is_bool($value):
                        $type = \PDO::PARAM_BOOL;
                        break;
                    case is_null($value):
                        $type = \PDO::PARAM_NULL;
                        break;
                    default:
                        $type = \PDO::PARAM_STR;
                }
            }
            $this->stmt->bindValue($param, $value, $type);
        }

        /**
         * Executes the prepared query against the currently connected database
         * @return bool
         */
        public function execute(): bool
        {
            return $this->stmt->execute();
        }

        /**
         * Returns the associative array result set (all rows) from the executed query
         * @return array
         */
        public function resultset(): array
        {
            $this->execute();
            return $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        /**
         * Returns the associative array result set (one row) from the executed query
         * @return array
         */
        public function single(): array
        {
            $this->execute();
            return $this->stmt->fetch(\PDO::FETCH_ASSOC);
        }

        /**
         * Returns the count of rows in the result set of the last executed query
         * @return int
         */
        public function rowCount(): int
        {
            return $this->stmt->rowCount();
        }

        /**
         * Returns the last inserted id (serial/autonumber) of the last inserted row
         * @return string
         */
        public function lastInsertId(): string
        {
            return $this->dbh->lastInsertId();
        }

        /**
         * Begins a database transaction
         * @return bool
         */
        public function beginTransaction(): bool
        {
            return $this->dbh->beginTransaction();
        }

        /**
         * Commits the active database transaction
         * @return bool
         */
        public function endTransaction(): bool
        {
            return $this->dbh->commit();
        }

        /**
         * Rolls back the active database transaction
         * @return bool
         */
        public function cancelTransaction(): bool
        {
            return $this->dbh->rollBack();
        }

        /**
         * Dumps the debugging parameters from PDO to standard output
         * @return void
         */
        public function debugDumpParams(): void
        {
            $this->stmt->debugDumpParams();
        }

        /**
         * Closes the database connection
         * @return bool
         */
        public function close(): bool
        {
            $this->stmt = null;
            $this->dbh = null;
            return true;
        }
	}
}