<?php
/**
 * Created by PhpStorm.
 * User: mtchabok
 * Date: 8/23/2018
 * Time: 10:06 PM
 */

namespace Fastdb;


class QueryFrom
{
	public $table = '';
	public $alias = '';

	public function __construct($table, $alias=null)
	{
		$this->table = $table;
		$this->alias = $alias;
	}

	public function __toString()
	{
		return $this->table.($this->alias?' AS '.$this->alias:'');
	}
}