<?php
namespace Fastdb;

/**
 * Class Config
 * @author mtchabok
 * @package Fastdb
 */
class Config
{
	/**
	 * @var bool
	 */
	public $debug = false;

	/**
	 * @var bool
	 */
	public $log = false;

	/**
	 * @var string
	 */
	public $driver;

	/**
	 * @var string
	 */
	public $server;

	/**
	 * @var string
	 */
	public $file;

	/**
	 * @var int|string
	 */
	public $port;

	/**
	 * @var string
	 */
	public $user;

	/**
	 * @var string
	 */
	public $pass;

	/**
	 * @var string
	 */
	public $database;

	/**
	 * @var string
	 */
	public $charset;

	/**
	 * @var string
	 */
	public $tablePrefix;


	/**
	 * dsn connection string
	 * @return array|string
	 */
	public function getPdoDsn()
	{
		$dsn = '';
		switch ($this->driver){
			case Fastdb::DRIVER_MYSQL:
				$dsn = array();
				$dsn[] = 'host='.$this->server;
				if($this->database) $dsn[] = 'dbname='.$this->database;
				if($this->charset) $dsn[] = 'charset='.$this->charset;
				$dsn = 'mysql:'.implode(';', $dsn);
				break;
			case Fastdb::DRIVER_PGSQL:
				$dsn = array();
				$dsn[] = 'host='.$this->server;
				if($this->database) $dsn[] = 'dbname='.$this->database;
				if($this->user) $dsn[] = 'user='.$this->user;
				if($this->pass) $dsn[] = 'password='.$this->pass;
				if($this->charset) $dsn[] = 'charset='.$this->charset;
				$dsn = 'pgsql:'.implode(';', $dsn);
				break;
			case Fastdb::DRIVER_SQLSRV:
				$dsn = array();
				$dsn[] = 'server='.$this->server.($this->port?",{$this->port}":'');
				if($this->database) $dsn[] = 'database='.$this->database;
				//if($this->charset) $dsn[] = 'charset='.$this->charset;
				$dsn = 'sqlsrv:'.implode(';', $dsn);
				break;
			case Fastdb::DRIVER_SQLITE:
				$dsn = 'sqlite:'.$this->file;
				break;
			case Fastdb::DRIVER_SQLITE2:
				$dsn = 'sqlite2:'.$this->file;
				break;
		}
		return $dsn;
	}

	/**
	 * @param $dsn
	 * @return $this
	 * TODO: incomplete
	 */
	public function setPdoDsn($dsn)
	{
		$dsn = explode(':', $dsn, 2);
		$this->driver = trim($dsn[0]);
		$dsn = explode(';', $dsn[1]);
		foreach ($dsn as $v){
			$v = explode('=', $v,2);
			switch ($v[0]){
				case 'host':
					$this->host = explode(':', $v[1]);
					if(!empty($this->server[1])) $this->port = $this->server[1];
					$this->host = $this->server[0];
					break;
				case 'port':
					$this->port = $v[1];
					break;
				case 'dbname':
					$this->database = $v[1];
					break;
				case 'charset':
					$this->charset = $v[1];
					break;
			}
		}
		return $this;
	}
}