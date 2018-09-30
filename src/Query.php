<?php
/**
 * Created by PhpStorm.
 * User: mtchabok
 * Date: 8/23/2018
 * Time: 9:03 PM
 */

namespace Fastdb;
require_once __DIR__.'/QueryName.php';
require_once __DIR__.'/QueryJoin.php';

class Query extends QueryObject
{
	CONST TYPE_SELECT = 'SELECT';
	CONST TYPE_INSERT = 'INSERT';
	CONST TYPE_UPDATE = 'UPDATE';
	CONST TYPE_DELETE = 'DELETE';

	CONST WHERE_OR = 'OR';
	CONST WHERE_AND = 'AND';

	CONST DATABASENAMEPATTERN = '/:D_(.*?);/';
	CONST TABLENAMEPATTERN = '/:T_(.*?);/';
	CONST FIELDNAMEPATTERN = '/:N_(.*?);/';

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
	 * table for insert records
	 * @var QueryTableName
	 */
	protected $_insert;

	/**
	 * table for update records
	 * @var QueryTableName
	 */
	protected $_update;

	/**
	 * table for delete records
	 * @var QueryTableName
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
	 * @var array
	 */
	protected $_orders = array();

	/**
	 * @var int
	 */
	protected $_offsetLimit = 0;

	/**
	 * @var int
	 */
	protected $_offsetBegin = 0;

	/**
	 * Query constructor.
	 * @param Query|Fastdb $parent=null
	 */
	public function __construct($parent=null)
	{
		if(null!==$parent) $this->setParent($parent);
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
	 * @param string|QueryTableName $table
	 * @param string $alias
	 * @return $this
	 */
	public function insert($table, $alias=null)
	{
		$this->_type = self::TYPE_INSERT;
		$this->_insert = $table instanceof QueryTableName ? $table : new QueryTableName($table, $alias);
		return $this;
	}


	/**
	 * @param string|QueryTableName $table
	 * @param string $alias
	 * @return $this
	 */
	public function update($table, $alias=null)
	{
		$this->_type = self::TYPE_UPDATE;
		$this->_insert = $table instanceof QueryTableName ? $table : new QueryTableName($table, $alias);
		return $this;
	}


	/**
	 * @param string|QueryTableName $table
	 * @param string $alias
	 * @return $this
	 */
	public function delete($table, $alias = null)
	{
		$this->_type = self::TYPE_DELETE;
		$this->_insert = $table instanceof QueryTableName ? $table : new QueryTableName($table, $alias);
		return $this;
	}



	/**
	 * @param string|array|QueryTableName $table
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
	 * @param QueryTableName $table=null
	 * @param string $on=null
	 * @return $this
	 */
	public function join($type, $table=null, $on=null)
	{
		$join = $type instanceof QueryJoin
			? $type
			: new QueryJoin($type, $table, $on);
		$this->_join[] = $join;
		$join->setParent($this);
		return $this;
	}


	public function getNewJoin()
	{
		return new QueryJoin();
	}



	/**
	 * @param string $name
	 * @param string $dir=null ASC|DESC
	 * @return $this
	 */
	public function order($name, $dir = null)
	{
		$this->_orders[] = array($name, $dir);
		return $this;
	}


	/**
	 * @param int $limit
	 * @param int $begin=0
	 * @return $this
	 */
	public function offset($limit, $begin=0)
	{
		$this->_offsetLimit = (int) $limit;
		$this->_offsetBegin = (int) $begin;
		return $this;
	}




	public function __toString()
	{
		$query = '';
		$dbLink = $this->getDbLink();
		$this->_childesValues = array();
		switch ($this->_type){
			case 'insert':

				break;
			case 'update':

				break;
			case 'delete':

				break;
			default:
				// ---------------- SELECT QUERY --------------------
				$query = 'SELECT';
				if($this->_select){
					$querySelect = $this->_select;
					foreach ($querySelect as &$qs){
						$qss = ''.$qs;
						if($qs instanceof QuerySelectName){
							$childValues = $qs->_getAllValues();
							foreach ($childValues as $k=>&$v){
								if(
									(array_key_exists($k, $this->_childesValues) && $this->_childesValues[$k]!=$v)
									|| (array_key_exists($k, $this->_values) && $this->_values[$k]!=$v)
								){
									$kNew = uniqid(':VAL');
									$qss = str_replace($k, $kNew, $qss);
									$k=$kNew;
								}
								$this->_childesValues[$k]=$v;
							}
						}
						$qs = $qss;
					}
					$query.= ' '.implode(',', $querySelect);
					$querySelect = null;
				}else $query.= ' *';

				if($this->_from){
					$query.= ' FROM';
					$queryFrom = $this->_from;
					foreach ($queryFrom as &$qf){
						$qfs = ''.$qf;
						if($qf instanceof QueryTableName){
							$childValues = $qf->_getAllValues();
							foreach ($childValues as $k=>&$v){
								if(
									(array_key_exists($k, $this->_childesValues) && $this->_childesValues[$k]!=$v)
									|| (array_key_exists($k, $this->_values) && $this->_values[$k]!=$v)
								){
									$kNew = uniqid(':VAL');
									$qfs = str_replace($k, $kNew, $qfs);
									$k=$kNew;
								}
								$this->_childesValues[$k]=$v;
							}
						}
						$qf = $qfs;
					}
					$query.= ' '.implode(',', $queryFrom);
					$queryFrom = null;
				}

				if($this->_orders){
					$query.= ' ORDER BY';
					$queryOrder = $this->_orders;
					foreach ($queryOrder as &$v){
						$v = $v[0].(empty($v[1])?'':' '.$v[1]);
					}
					$query.= ' '.implode(',', $queryOrder);
					$queryOrder=null;
				}

				if($this->_offsetLimit){
					switch ($dbLink->config->driver){
						case Fastdb::DRIVER_MYSQL:
							$query.= ' LIMIT '.$this->_offsetLimit;
							if($this->_offsetBegin) $query.= ' OFFSET '.$this->_offsetBegin;
							break;
						case Fastdb::DRIVER_SQLSRV:
							$query.= ' OFFSET '.$this->_offsetLimit.' ROWS';
							if($this->_offsetBegin) $query.= ' FETCH NEXT '.$this->_offsetBegin.' ROWS ONLY';
							break;
					}
				}

		}
		return $query;
	}


}