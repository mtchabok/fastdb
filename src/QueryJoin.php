<?php
namespace Fastdb;

/**
 * Class QueryJoin
 * @package Fastdb
 */
class QueryJoin extends QueryObject
{
	const LEFT = 'LEFT JOIN';
	const RIGHT = 'RIGHT JOIN';

	/**
	 * @var String
	 */
	protected $_type;

	/**
	 * @var QueryTableName
	 */
	protected $_table;

	/**
	 * @var array
	 */
	protected $_on = array();


	public function __construct($type=null, $table=null, $on=null)
	{
		if(null!==$type) $this->setType($type);
		if(null!==$table) $this->setTable($table);
		if(!is_null($on)) {
			if (!is_array($on)) $on = array($on);
			$this->_on = array_merge($this->_on, $on);
		}
	}

	/**
	 * @return QueryTableName
	 */
	public function getTable()
	{
		return $this->_table;
	}

	/**
	 * @param QueryTableName $table
	 * @return $this
	 */
	public function setTable(QueryTableName $table)
	{
		$this->_table = $table;
		$this->_table->setParent($this);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->_type;
	}

	/**
	 * @param string $type
	 * @return $this
	 */
	public function setType($type)
	{
		$this->_type = (string) $type;
		return $this;
	}





	public function __toString()
	{
		$query = '';

		return $query;
	}

	public function __call($name, $arguments)
	{
		$return = null;
		if(null!==$this->_parent && $this->_parent instanceof Query){
			$return = call_user_func_array(array($this->_parent,$name), $arguments);
			if($return === $this->_parent) $return = $this;
		}
		return $return;
	}


}

class QueryLeftJoin extends QueryJoin{

	protected $_type = parent::LEFT;

	public function __construct($table=null, $on=null)
	{
		parent::__construct(self::LEFT, $table, $on);
	}

	public function setType($type)
	{
		return $this;
	}
}

class QueryRightJoin extends QueryJoin{

	protected $_type = parent::RIGHT;

	public function __construct($table=null, $on=null)
	{
		parent::__construct(self::RIGHT, $table, $on);
	}

	public function setType($type)
	{
		return $this;
	}
}
