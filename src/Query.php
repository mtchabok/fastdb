<?php
/**
 * Created by PhpStorm.
 * User: mtchabok
 * Date: 8/23/2018
 * Time: 9:03 PM
 */

namespace Fastdb;


class Query
{
	const TYPE_SELECT = 'SELECT';
	const TYPE_INSERT = 'INSERT';
	const TYPE_UPDATE = 'UPDATE';
	const TYPE_DELETE = 'DELETE';

	/**
	 * query type: Query:TYPE_SELECT, ...
	 * @var string
	 */
	protected $_type = self::TYPE_SELECT;

	/**
	 * selection fields
	 * @var array
	 */
	protected $_select = array();

	/**
	 * from tables
	 * @var array
	 */
	protected $_from = array();


	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->_type;
	}

	/**
	 * @param $name string|array|Query
	 * @return $this
	 */
	public function select($name, $alias=null)
	{
		if(!is_array($name))
			$name = array($alias=>$name);
		foreach ($name as $k=>&$v) {
			$this->_select[] = is_numeric($k)
				? new QuerySelect($v)
				: new QuerySelect($v, $k)
			;
		}
		return $this;
	}

	/**
	 * @param string|array|QueryFrom $table
	 * @param string $alias=null
	 * @return $this
	 */
	public function from($table, $alias=null)
	{
		if(!is_array($table))
			$table = array($alias=>$table);
		foreach ($table as $k=>&$v) {
			$this->_from[] = is_numeric($k)
				? new QueryFrom($v)
				: new QueryFrom($v, $k)
			;
		}
		return $this;
	}



}