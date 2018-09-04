<?php
namespace Fastdb;
use PDO;
use Exception;
use PDOException;

/**
 * Class Fastdb
 * @author mtchabok
 * @package Fastdb
 * @property string status
 */
class Fastdb extends \PDO
{
	CONST DRIVER_MYSQL = 'mysql';
	CONST DRIVER_PGSQL = 'pgsql';
	CONST DRIVER_SQLSRV = 'sqlsrv';
	CONST DRIVER_SQLITE = 'sqlite';
	CONST DRIVER_SQLITE2 = 'sqlite2';

	/**
	 * @var Config
	 */
	public $config;


	/**
	 * status = [off, init, on, execute]
	 * @var string
	 */
	protected $_status='off';


	/**
	 * @var string|Query
	 */
	protected $_query;


	/**
	 * @var \PDOStatement
	 */
	protected $_statment;



	public function __construct(Config $config=null)
	{
		$this->config = null!==$config? $config: new Config();
	}




	/**
	 * connect to database
	 * @return $this|bool
	 */
	public function connect()
	{
		if($this->_status=='off') {
			$this->_status = 'init';
			$options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
			try {
				switch ($this->config->driver){
					case self::DRIVER_MYSQL:
						if($this->config->charset)
							$options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES "' . $this->config->charset . '"';
						break;
				}
				parent::__construct($this->config->getPdoDsn(), $this->config->user, $this->config->pass, $options);
				$this->_status = 'on';
				$this->_query = null;
				$this->_statment = null;
			} catch (PDOException $e) {
				$this->_status = 'off';
				if ($this->config->debug)
					throw $e;
				return $this;
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
		if($this->_status!='on') $this->connect();
		$this->_query = $query;
		$this->_statment = $this->prepare((string) $query);
		return $this;
	}



	/**
	 * execute current query object|string
	 * @return mixed
	 */
	public function execute()
	{
		$result = false;
		if($this->_status!='on') $this->connect();
		$lastStatus = $this->_status;
		try{
			$this->_status = 'execute';
			$result = $this->_statment->execute();
			$this->_status = $lastStatus;
		}catch (PDOException $e){
			$this->_status = $lastStatus;
			throw $e;
		}
		return $result;
	}


	/**
	 * @return bool|\stdClass
	 */
	public function fetch()
	{
		$result = false;
		if($this->_status!='on') $this->connect();
		$lastStatus = $this->_status;
		try{
			$this->_status = 'execute';
			$result = $this->_statment->fetchObject();
			$this->_status = $lastStatus;
		}catch (PDOException $e){
			$this->_status = $lastStatus;
			throw $e;
		}
		return $result;
	}


	/**
	 * @return array|bool
	 */
	public function fetchAll(){
		$result = false;
		if($this->_status!='on') $this->connect();
		$lastStatus = $this->_status;
		try{
			$this->_status = 'execute';
			$result = $this->_statment->fetchAll(PDO::FETCH_OBJ);
			$this->_status = $lastStatus;
		}catch (PDOException $e){
			$this->_status = $lastStatus;
			throw $e;
		}
		return $result;
	}






	public function __get($name)
	{
		$return = null;
		switch ($name){
			case 'status':
				$return = $this->_status;
				break;
		}
		return $return;
	}

	public function __set($name, $value)
	{
		switch ($name){
			case 'status': break;
			default: $this->{$name} = $value;
		}
	}



}