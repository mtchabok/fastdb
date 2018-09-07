<?php
/**
 * Created by PhpStorm.
 * User: mtchabok
 * Date: 8/23/2018
 * Time: 9:03 PM
 */

namespace Fastdb;
require_once __DIR__.'/QueryName.php';

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
	 * @var Query|Fastdb
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
		if(null!==$parent) $this->setParent($parent);
	}



	/**
	 * parent query
	 * @return Query|Fastdb
	 */
	public function getParent()
	{
		return $this->_parent;
	}

	/**
	 * @param Fastdb|Query $parent
	 * @return $this
	 */
	public function setParent($parent)
	{
		if($parent instanceof Fastdb || $parent instanceof Query)
			$this->_parent = $parent;
		else throw new FastdbException('only Fastdb | Query object');
		return $this;
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
	final public function getType()
	{
		return $this->_type;
	}

	/**
	 * @param $name string|array|Query
	 * @param string $alias=null
	 * @return $this
	 */
	public function select($name, $alias=null)
	{
		if(!is_array($name))
			$name = array($alias=>$name);
		foreach ($name as $k=>&$v) {
			if(!$v instanceof QuerySelectName)
				$v = new QuerySelectName($v, (!$k || is_numeric($k))?null:$k);
			$v->setParent($this);
			$this->_select[] = $v;
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
			if(!$v instanceof QueryTableName)
				$v = new QueryTableName($v, (!$k || is_numeric($k))?null:$k);
			$v->setParent($this);
			$this->_from[] = $v;
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
		$return = null;
		if($this->_parent) $return = $this->_parent->quote($value);
		return $return;
	}


	/**
	 * @param string $name
	 * @return string
	 */
	public function quoteTable($name)
	{
		$return = null;
		if($this->_parent) $return = $this->_parent->quoteTable($name);
		return $return;
	}


	/**
	 * @param string $name
	 * @return string
	 */
	public function quoteName($name)
	{
		$return = null;
		if($this->_parent) $return = $this->_parent->quoteName($name);
		return $return;
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
		if($this->_parent instanceof Query)
			$this->_parent->setValue($key, $value);
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
	 * return all values
	 * @return array
	 */
	public function getValues()
	{
		return $this->_values;
	}


	/**
	 * bind value and return key
	 * @param string|array|Query $value
	 * @param string $key=null
	 * @return array|string
	 */
	public function value($value = null, $key = null)
	{
		$returnIsArray = false;
		if(!is_array($value)){
			$values = $value;
			$returnIsArray = true;
		}else $values = array($key=>$value);

		if($this->_parent instanceof Query){
			$keys = $this->_parent->value($values);
		}else{
			$keys = array_keys($values);
			foreach ($keys as &$key){
				if(!$key || is_numeric($key)) $key = uniqid(':VAL');
				else $key = ':'.ltrim($key, ' :');
			}
		}

		$values = array_values($values);
		for($i=0;$i<count($keys);$i++){
			$this->_values[$keys[$i]] = $values[$i];
		}

		return $returnIsArray?$keys:$keys[0];
	}


	public function __toString()
	{
		$query = '';
		switch ($this->_type){
			case 'insert':
				$query.= 'INSERT'.' INTO '.$this->_insert;
				$queryNames = array();
				foreach ($this->_columns as $c) $queryNames[] = '#N_'.$c;
				$query.= ' ('.implode(',', $queryNames).')';

				$queryValues = array();
				foreach ($this->_values as &$row){
					$r = array();
					foreach ($this->_columns as $k=>$c){
						$r[] = isset($row[$k])?$row[$k]:'null';
					}
					$queryValues[] = '('.implode(',', $r).')';
				}
				$query.= ' VALUES '.implode(',', $queryValues);
				break;
			case 'update':
				$query.= 'UPDATE '.$this->_update;
				$queryValues = array();
				foreach ($this->_values as &$row){
					foreach ($this->_columns as $k=>$c){
						$queryValues[] = '#N_'.$c.'='.(isset($row[$k])?$row[$k]:'null');
					}
					break;
				}
				$query.= ' SET '.implode(',', $queryValues);
				if($this->_where)
					$query.= ' WHERE '.implode(' AND ', $this->_where);
				break;
			case 'delete':
				$query.= 'DELETE'.' FROM '.$this->_delete;
				if($this->_where)
					$query.= ' WHERE '.implode(' AND ', $this->_where);
				break;
			default:
				$query = 'SELECT';
				if($this->_select){
					$querySelect = $this->_select;
					foreach ($querySelect as &$qs){
						$qs = ''.$qs;
					}
					$query.= ' '.implode(',', $querySelect);
					$querySelect = null;
				}else $query.= ' *';

				if($this->_from){
					$query.= ' FROM';
					$queryFrom = $this->_from;
					foreach ($queryFrom as &$qf){
						$qf = ''.$qf;
					}
					$query.= ' '.implode(',', $queryFrom);
					$queryFrom = null;
				}
			echo $query;

		}
		return $query;
	}


}