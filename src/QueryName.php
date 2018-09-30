<?php
namespace Fastdb;

/**
 * Class QueryName
 * @package Fastdb
 */
abstract class QueryName extends QueryObject
{
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

}
