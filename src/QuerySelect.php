<?php
/**
 * Created by PhpStorm.
 * User: mtchabok
 * Date: 8/23/2018
 * Time: 10:01 PM
 */

namespace Fastdb;


class QuerySelect
{
	public $name = '';
	public $alias = '';

	public function __construct($select, $alias = null)
	{
		$this->name = $select;
		$this->alias = $alias;
	}

	public function __toString()
	{
		return $this->name.($this->alias?' AS '.$this->alias:'');
	}
}