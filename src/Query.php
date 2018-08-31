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
	 * @var Query
	 */
	protected $_parent;

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
	 * array of QueryJoin`s
	 * @var array
	 */
	protected $_join = array();

	/**
	 * Query constructor.
	 * @param Query $parent=null
	 */
	public function __construct(Query $parent=null)
	{
		$this->_parent = $parent;
	}

	/**
	 * @return Query
	 */
	public function getQuery()
	{
		return new Query($this);
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


	public function join($type, $table=null, $on=null)
	{
		if($type instanceof QueryJoin){
			$this->_join[] = $type;
		}else{
			$this->_join[] = new QueryJoin($type, $table, $on);
		}
		return $this;
	}

}