<?php
namespace Fastdb;
use PDO;
use Exception;
use PDOException;

/**
 * Class Fastdb
 * @author mtchabok
 * @package Fastdb
 */
class Fastdb
{
	CONST DRIVER_MYSQL = 'mysql';
	CONST DRIVER_PGSQL = 'pgsql';
	CONST DRIVER_SQLSRV = 'sqlsrv';
	CONST DRIVER_SQLITE = 'sqlite';
	CONST DRIVER_SQLITE2 = 'sqlite2';

	/**
	 * status = [off, init, on, execute]
	 * @var string
	 */
	protected $_status='off';

	/**
	 * @var Config
	 */
	protected $_config;

	/**
	 * @var \PDO
	 */
	protected $_pdo;

	/**
	 * @var string|Query
	 */
	protected $_query;

	/**
	 * @var \PDOStatement
	 */
	protected $_statment;



	public function __construct($config=null)
	{
		if (!is_null($config)) $this->setConfig($config);
	}

	/**
	 * get current status: execute|on|init|off
	 * @return string
	 */
	public function getStatus()
	{
		return $this->_status;
	}






	/**
	 * get current config object or new config object
	 * @param bool $new=false
	 * @return Config
	 */
	public function getConfig($new=false)
	{
		$config = $new
			? new Config()
			: ($this->_config instanceof Config ? clone $this->_config : new Config())
		;
		return $config;
	}

	/**
	 * set config object
	 * @param Config $config
	 * @return $this
	 */
	public function setConfig(Config $config)
	{
		if($this->getStatus()=='off'){
			$this->_config = $config;
		}elseif($this->getConfig()->debug) throw new FastdbException('Database not ready for config');
		return $this;
	}






	/**
	 * connect to database
	 * @return $this|bool
	 */
	public function connect()
	{
		if($this->getStatus()=='off') {
			$this->_status = 'init';
			$config = $this->getConfig();
			$options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
			try {
				switch ($config->driver){
					case self::DRIVER_MYSQL:
						if($config->charset)
							$options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES "' . $config->charset . '"';
						break;
				}
				$this->_pdo = new PDO($config->getPdoDsn(), $config->user, $config->pass, $options);
				if($this->_pdo) {
					$this->_statment = null;
					$this->_status = 'on';
				}else throw new PDOException($this->_pdo->errorInfo(), $this->_pdo->errorCode());
			} catch (PDOException $e) {
				$this->_status = 'off';
				if ($config->debug)
					throw $e;
				return false;
			}
		}
		return $this;
	}



	/**
	 * get last query object|string or new query object
	 * @param bool $new=false
	 * @return string|Query
	 */
	public function getQuery($new=false)
	{
		$query = null;
		if($new)
			$query = new Query();
		elseif ($this->_query instanceof Query)
			$query = clone $this->_query;
		elseif ($this->_query)
			$query = $this->_query;
		return $query;
	}

	/**
	 * set query object|string
	 * @param Query|string $query
	 * @return $this
	 */
	public function setQuery($query)
	{
		$this->_query = $query;
		$this->_statment = null;
		return $this;
	}


	/**
	 * execute current query object|string
	 * @return mixed
	 */
	public function execute()
	{
		$result = null;
		$this->connect();
		if($this->getStatus()=='on'){
			$query = $this->getQuery();
			try{
				$this->_status = 'execute';
				if($query instanceof Query){
					$this->_statment = $this->_pdo->prepare( (String) $query);
					$this->_statment->execute();
				}else{
					$this->_statment = $this->_pdo->prepare($query);
					$this->_statment->execute();
				}
				$this->_status = 'on';
			}catch (PDOException $e){
				$this->_status = 'on';
				throw $e;
			}
		}else{
			throw new FastdbException('not exist database connection');
		}
		return $result;
	}


	public function fetch()
	{
		$return = null;
		$this->execute();
		if($this->_statment && !$this->_statment->errorCode()){
			$return = $this->_statment->fetchObject();
		}
		return $return;
	}

	public function fetchAll(){
		$return = null;
		$this->execute();
		if($this->_statment && !$this->_statment->errorCode()){
			$return = $this->_statment->fetchAll(PDO::FETCH_OBJ);
		}
		return $return;
	}

	public function error()
	{
		$return = null;
		if($this->_statment && $this->_statment->errorCode()){
			$return = $this->_statment->errorInfo();
		}
		return $return;
	}


	/**
	 * @param $table
	 * TODO: incomplete
	 */
	public function select($table)
	{
		$query = $this->getQuery(true);
		$query->from($table)->select('*');
		$this->setQuery($query);
		$this->execute();
	}

}