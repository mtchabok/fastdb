<?php
/**
 * Created by PhpStorm.
 * User: mtchabok
 * Date: 8/23/2018
 * Time: 8:28 PM
 */

namespace Fastdb;


/**
 * Class Fastdb
 * @package Fastdb
 */
class Fastdb
{
	const DRIVER_MYSQL = 'MYSQL';
	const DRIVER_MSSQL = 'MSSQL';
	const DRIVER_SQLITE = 'SQLITE';

	/**
	 * @var string|Query
	 */
	protected $_query='';

	/**
	 * @param bool $new=false
	 * @return string|Query
	 */
	public function getQuery($new=false)
	{
		$query = $new
			?new Query()
			:$this->_query
		;
		return $query;
	}

	/**
	 * @param Query|string $query
	 * @return $this
	 */
	public function setQuery($query)
	{
		$this->_query = $query;
		return $this;
	}

	public function select($table)
	{
		$query = $this->getQuery(true);
		$query->from($table)->select('*');
		$this->setQuery($query);
	}

}