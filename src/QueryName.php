<?php
/**
 * Created by PhpStorm.
 * User: mtchabok
 * Date: 9/7/2018
 * Time: 7:04 PM
 */

namespace Fastdb;


class QueryName
{
	/**
	 * @var Query
	 */
	protected $_parent;

	/**
	 * @var string|Query
	 */
	protected $_name = '';

	/**
	 * @var string
	 */
	protected $_alias = '';


	public function __construct($name = null, $alias = null)
	{
		if(null!==$name) $this->setName($name);
		if(null!==$alias) $this->setAlias($alias);
	}

	/**
	 * @return Query|null
	 */
	public function getParent()
	{
		return $this->_parent;
	}

	/**
	 * @param Query $parent
	 */
	public function setParent(Query $parent)
	{
		$this->_parent = $parent;
		if($this->_name instanceof Query)
			$this->_name->setParent($parent);
	}

	/**
	 * @return Query|string
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * @param Query|string $name
	 */
	public function setName($name)
	{
		$this->_name = $name;
	}

	/**
	 * @return string
	 */
	public function getAlias()
	{
		return $this->_alias;
	}

	/**
	 * @param string $alias
	 */
	public function setAlias($alias)
	{
		$this->_alias = $alias;
	}

	public function __toString()
	{
		if($this->_name instanceof Query){
			$return = '('.$this->_name.')';
		}else $return = (string) $this->_name;
		if($this->_alias){
			$return.= ' AS '.($this->_parent
				?$this->_parent->quoteName($this->_alias)
					:$this->_alias);
		}
		return $return;
	}
}


/**
 * Class QuerySelectName
 * @package Fastdb
 */
class QuerySelectName extends QueryName{

}


/**
 * Class QueryTableName
 * @package Fastdb
 */
class QueryTableName extends QueryName{

}