<?php
namespace Fastdb;

/**
 * Class QueryObject
 * @package Fastdb
 */
abstract class QueryObject
{
	/**
	 * @var QueryJoin|Query|Fastdb
	 */
	protected $_parent;


	/**
	 * array of bind value
	 * @var array
	 */
	protected $_values = array();

	/**
	 * array of childes values
	 * @var array
	 */
	protected $_childesValues = array();


	/**
	 * @return QueryJoin|Query|Fastdb
	 */
	public function getParent()
	{
		return $this->_parent;
	}

	/**
	 * @return Fastdb|null
	 */
	public function getDbLink()
	{
		return $this->_parent instanceof Fastdb
			?$this->_parent
			:($this->_parent instanceof Query?$this->_parent->getDbLink():null);
	}

	/**
	 * @param QueryJoin|Query|Fastdb $parent
	 * @return $this
	 */
	public function setParent($parent)
	{
		$this->_parent = $parent;
		if($this->_values){
			$this->_parent->setValue($this->_values);
			$this->_values = array();
		}
		return $this;
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
	 * return all values
	 * @return array
	 */
	public function getValues()
	{
		return $this->_values;
	}

	/**
	 * @return array
	 */
	public function _getAllValues()
	{
		$return = $this->getValues();
		foreach ($this->_childesValues as $k=>&$v)
			if(!array_key_exists($k, $return))
				$return[$k] = $v;
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
		$returnIsArray = false;
		if(!is_array($value)){
			$values = $value;
			$returnIsArray = true;
		}else $values = array($key=>$value);
		$keys = array_keys($values);
		foreach ($keys as &$key){
			if(!$key || is_numeric($key)) $key = uniqid(':VAL');
			else $key = ':'.ltrim($key, ' :');
		}
		$values = array_values($values);
		for($i=0;$i<count($keys);$i++){
			$this->_values[$keys[$i]] = $values[$i];
		}
		return $returnIsArray?$keys:$keys[0];
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function name($name)
	{
		if(false===preg_match(Query::FIELDNAMEPATTERN, $name)){
			$name = ':N_'.$name.';';
		}
		return $name;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function table($name)
	{
		if(false===preg_match(Query::TABLENAMEPATTERN, $name)){
			$name = ':T_'.$name.';';
		}
		return $name;
	}


	public function database($name)
	{
		if(false===preg_match(Query::DATABASENAMEPATTERN, $name)){
			$name = ':D_'.$name.';';
		}
		return $name;
	}






	/**
	 * @param string $value
	 * @return string
	 */
	public function quote($value)
	{
		$db = $this->getDbLink();
		$return = $db instanceof Fastdb?$db->quote($value):$this->value($value);
		return $return;
	}


	public function quoteDatabase($name){
		$db = $this->getDbLink();
		$return = $db instanceof Fastdb?$db->quoteDatabase($name):$this->database($name);
		return $return;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function quoteTable($name)
	{
		$db = $this->getDbLink();
		$return = $db instanceof Fastdb?$db->quoteTable($name):$this->table($name);
		return $return;
	}


	/**
	 * @param string $name
	 * @return string
	 */
	public function quoteName($name)
	{
		$db = $this->getDbLink();
		$return = $db instanceof Fastdb?$db->quoteName($name):$this->name($name);
		return $return;
	}


	/**
	 * @return string
	 */
	abstract public function __toString();

}