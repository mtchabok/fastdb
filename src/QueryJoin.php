<?php
/**
 * Created by PhpStorm.
 * User: mtchabok
 * Date: 8/31/2018
 * Time: 1:03 PM
 */

namespace Fastdb;


class QueryJoin
{
	const LEFT = 'LEFT JOIN';
	const RIGHT = 'RIGHT JOIN';

	/**
	 * @var String
	 */
	public $type;

	/**
	 * @var QueryTable
	 */
	public $table;

	/**
	 * @var array
	 */
	public $on = array();

	public function __construct($type, $table=null, $on=null)
	{
		$this->type = $type;
		$this->table = $table;
		if(!is_null($on)) {
			if (!is_array($on)) $on = array($on);
			$this->on = array_merge($this->on, $on);
		}
	}


}