<?php
namespace Fastdb;

/**
 * Class QueryWhereGroup
 * @package Fastdb
 */
class QueryWhereGroup extends QueryObject
{
	protected $_wheres = array();

	protected $_before = Query::WHERE_AND;

	public function __toString()
	{
		$return = '';
		foreach ($this->_wheres as &$where){
			if($where instanceof QueryWhereGroup){
				$return.= ($return?' '.$where->getBefore().' ':'').$where;
			}elseif ($where instanceof QueryWhere){
				$return.= ($return?' '.$where->getBefore().' ':'').$where;
			}else{
				$return.= ($return?' '.$this->_before.' ':'').$where;
			}
		}
		if(count($this->_wheres)>1){
			$return = '('.$return.')';
		}
		return $return;
	}

	public function getBefore()
	{
		return $this->_before;
	}
}