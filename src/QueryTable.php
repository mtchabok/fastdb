<?php
namespace Fastdb;

/**
 * Class QueryTable
 * @package Fastdb
 */
class QueryTable
{
	public $name = '';
	public $alias = '';

	public function __construct($name, $alias=null)
	{
		$this->name = $name;
		$this->alias = $alias;
	}

	public function __toString()
	{
		return $this->name.($this->alias?' AS '.$this->alias:'');
	}
}