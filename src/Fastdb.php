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
	protected $_statement;



	public function __construct(Config $config=null)
	{
		$this->config = null!==$config? $config: new Config();
	}


	/**
	 * return quote table name
	 * @param string $name
	 * @return string
	 */
	public function quoteTable($name)
	{
		$return = '';
		switch ($this->config->driver){
			case self::DRIVER_MYSQL:
				$return = explode('.', trim($name, ' `'));
				foreach ($return as &$v) $v = '`'.trim($v, ' `').'`';
				$return = implode('.', $return);
				break;
			case self::DRIVER_SQLSRV:
				$return = explode('.', trim($name, ' []'));
				foreach ($return as &$v) $v = '['.trim($v, ' []').']';
				$return = implode('.', $return);
				break;
		}
		return $return;
	}


	/**
	 * return quote field name of table
	 * @param string $name
	 * @return string
	 */
	public function quoteName($name)
	{
		$return = '';
		switch ($this->config->driver){
			case self::DRIVER_MYSQL:
				$return = explode('.', trim($name, ' `'));
				foreach ($return as &$v) $v = '`'.trim($v, ' `').'`';
				$return = implode('.', $return);
				break;
			case self::DRIVER_SQLSRV:
				$return = explode('.', trim($name, ' []'));
				foreach ($return as &$v) $v = '['.trim($v, ' []').']';
				$return = implode('.', $return);
				break;
		}
		return $return;
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
				$this->_statement = null;
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
			$query = new Query($this);
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
		$this->prepare($query);
		return $this;
	}

	/**
	 * @param Query|string $statement
	 * @param array $driver_options=null
	 * @return \PDOStatement|false
	 */
	public function prepare($statement, $driver_options=array())
	{
		if($this->_status!='on') $this->connect();
		$this->_query = $statement;
		try {
			if($this->_query instanceof Query) $this->_query->setParent($this);
			$this->_statement = parent::prepare((string)$statement, $driver_options);
			$this->_statement->executed = false;
		}catch (PDOException $e){
			if($this->config->debug)
				throw $e;
			else return false;
		}
		return $this->_statement;
	}







	/**
	 * execute current query object|string
	 * @return mixed
	 */
	public function execute()
	{
		if($this->_status!='on') $this->connect();
		$lastStatus = $this->_status;
		try{
			$this->_status = 'execute';
			$this->_statement->executed = $this->_statement->execute();
			$this->_status = $lastStatus;
		}catch (PDOException $e){
			$this->_status = $lastStatus;
			throw $e;
		}
		return $this->_statement->executed;
	}



	/**
	 * execute any query without use params and return pdo statement
	 * @param Query|string $statement
	 * @return \PDOStatement
	 */
	public function query($statement)
	{
		if($this->_status!='on') $this->connect();
		$this->_query = $statement;
		$lastStatus = $this->_status;
		try {
			$this->_status = 'execute';
			if($this->_query instanceof Query) $this->_query->setParent($this);
			$this->_statement = parent::query((string) $statement);
			$this->_statement->executed = true;
			$this->_status = $lastStatus;
		}catch (PDOException $e){
			$this->_status = $lastStatus;
			throw $e;
		}
		return $this->_statement;
	}



	/**
	 * execute INSERT|UPDATE|DELETE Query and return number of affected records
	 * @param Query|string $statement
	 * @return int
	 */
	public function exec($statement)
	{
		if($this->_status!='on') $this->connect();
		$this->_query = $statement;
		$lastStatus = $this->_status;
		try {
			$this->_status = 'execute';
			if($this->_query instanceof Query) $this->_query->setParent($this);
			$affectedRecords = parent::exec((string) $statement);
			$this->_status = $lastStatus;
		}catch (PDOException $e){
			$this->_status = $lastStatus;
			throw $e;
		}
		return $affectedRecords;
	}


	/**
	 * return row count of execute current query
	 * @return int
	 */
	public function rowCount()
	{
		if($this->_status!='on') $this->connect();
		try{
			if(empty($this->_statement->executed))
				$this->_statement->executed = $this->_statement->execute();
			$result = $this->_statement->rowCount();
		}catch (PDOException $e){
			throw $e;
		}
		return $result;
	}



	/**
	 * @param string $class_name='stdClass'
	 * @return bool|\stdClass
	 */
	public function fetch($class_name='stdClass')
	{
		if($this->_status!='on') $this->connect();
		$lastStatus = $this->_status;
		try{
			$this->_status = 'execute';
			if(empty($this->_statement->executed))
				$this->_statement->executed = $this->_statement->execute();
			$result = $this->_statement->fetchObject($class_name);
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
		if($this->_status!='on') $this->connect();
		$lastStatus = $this->_status;
		try{
			$this->_status = 'execute';
			if(empty($this->_statement->executed))
				$this->_statement->executed = $this->_statement->execute();
			$result = $this->_statement->fetchAll(PDO::FETCH_OBJ);
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