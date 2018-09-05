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
	 * @var Fastdb
	 */
	protected $_pdo;


	/**
	 * @var Query
	 */
	protected $_parent;

	/**
	 * selection fields
	 * @var array
	 */
	protected $_select = array();

	/**
	 * table for insert records
	 * @var QueryTable
	 */
	protected $_insert;

	/**
	 * table for update records
	 * @var QueryTable
	 */
	protected $_update;

	/**
	 * table for delete records
	 * @var QueryTable
	 */
	protected $_delete;

	/**
	 * from tables
	 * @var array
	 */
	protected $_from = array();

	/**
	 * array of QueryJoin`s
	 * @var array
	 */
	protected $_join = array();

	/**
	 * array of bind value
	 * @var array
	 */
	protected $_values = array();

	/**
	 * Query constructor.
	 * @param Query|Fastdb $parent=null
	 */
	public function __construct($parent=null)
	{
		if($parent instanceof Fastdb)
			$this->_pdo = $parent;
		elseif($parent instanceof Query)
			$this->_parent = $parent;
	}



	/**
	 * @return Fastdb
	 */
	public function getPdo()
	{
		return $this->_pdo;
	}

	/**
	 * @param Fastdb $pdo
	 */
	public function setPdo(Fastdb $pdo)
	{
		$this->_pdo = $pdo;
	}




	/**
	 * parent query
	 * @return Query
	 */
	public function getParent()
	{
		return $this->_parent;
	}


	/**
	 * @return Query
	 */
	public function getQuery()
	{
		return new Query($this);
	}


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
			$this->_select[] = $name instanceof QuerySelect
				? $name : new QuerySelect($v, is_numeric($k)?$k:null);
		}
		return $this;
	}


	/**
	 * @param string|QueryTable $table
	 * @param string $alias
	 * @return $this
	 */
	public function insert($table, $alias=null)
	{
		$this->_type = self::TYPE_INSERT;
		$this->_insert = $table instanceof QueryTable ? $table : new QueryTable($table, $alias);
		return $this;
	}


	/**
	 * @param string|QueryTable $table
	 * @param string $alias
	 * @return $this
	 */
	public function update($table, $alias=null)
	{
		$this->_type = self::TYPE_UPDATE;
		$this->_insert = $table instanceof QueryTable ? $table : new QueryTable($table, $alias);
		return $this;
	}


	/**
	 * @param string|QueryTable $table
	 * @param string $alias
	 * @return $this
	 */
	public function delete($table, $alias = null)
	{
		$this->_type = self::TYPE_DELETE;
		$this->_insert = $table instanceof QueryTable ? $table : new QueryTable($table, $alias);
		return $this;
	}



	/**
	 * @param string|array|QueryTable $table
	 * @param string $alias=null
	 * @return $this
	 */
	public function from($table, $alias=null)
	{
		if(!is_array($table))
			$table = array($alias=>$table);
		foreach ($table as $k=>&$v) {
			$this->_from[] = is_numeric($k)
				? new QueryTable($v)
				: new QueryTable($v, $k)
			;
		}
		return $this;
	}

	/**
	 * @param string|QueryJoin $type
	 * @param QueryTable $table=null
	 * @param string $on=null
	 * @return $this
	 */
	public function join($type, $table=null, $on=null)
	{
		if($type instanceof QueryJoin){
			$this->_join[] = $type;
		}else{
			$this->_join[] = new QueryJoin($type, $table, $on);
		}
		return $this;
	}


	/**
	 * @param string $value
	 * @return string
	 */
	public function quote($value)
	{
		return $this->_pdo->quote($value);
	}


	/**
	 * @param string $name
	 * @return string
	 */
	public function quoteTable($name)
	{
		return $this->_pdo->quoteTable($name);
	}


	/**
	 * @param string $name
	 * @return string
	 */
	public function quoteName($name)
	{
		return $this->_pdo->quoteName($name);
	}



	/**
	 * @param string|array $key
	 * @param mixed $value=null
	 * @return $this
	 */
	public function setValue($key, $value=null)
	{
		$values = is_array($key)?$key:array($key=>$value);
		foreach ($values as $key=>&$value){
			$key = ':'.ltrim($key, ' :');
			$this->_values[$key] = $value;
		}
		return $this;
	}


	/**
	 * @param string $key
	 * @return mixed|null
	 */
	public function getValue($key)
	{
		$key = ':'.ltrim($key, ' :');
		$return = null;
		if(array_key_exists($key, $this->_values))
			$return = $this->_values[$key];
		return $return;
	}


	/**
	 * bind value and return key
	 * @param string|array|Query $value
	 * @param string $key=null
	 * @return array|string
	 */
	public function value($value = null, $key = null)
	{
		$values = is_array($value)?$value:array($key=>$value);
		$return = array();
		foreach ($values as $key=>&$value){
			if(!$key || is_numeric($key)) $key = ':VAL'.count($this->_values);
			$this->setValue($key,$value);
			$return[] = $key;
		}
		return count($return)===1?$return[0]:$return;
	}

}