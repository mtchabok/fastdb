<?php
namespace Fastdb;

/**
 * Class QueryTableName
 * @package Fastdb
 */
class QueryTableName extends QueryName{
	public function __toString()
	{
		$this->_childesValues = array();
		if($this->_name instanceof QueryObject){
			$return = '('.$this->_name.')';
			$childValues = $this->_name->_getAllValues();
			foreach ($childValues as $k=>&$v){
				if(
					(array_key_exists($k, $this->_childesValues) && $this->_childesValues[$k]!=$v)
					|| (array_key_exists($k, $this->_values) && $this->_values[$k]!=$v)
				){
					$kNew = uniqid(':VAL');
					$return = str_replace($k, $kNew, $return);
					$k=$kNew;
				}
				$this->_childesValues[$k]=$v;
			}
		}else $return = $this->quoteTable($this->_name);
		if($this->_alias){
			$return.= ' AS '.$this->quoteName($this->_alias);
		}
		return $return;
	}
}