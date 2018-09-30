<?php
namespace Fastdb;

/**
 * Class QueryWhere
 * @package Fastdb
 */
class QueryWhere extends QueryObject
{



	protected $_left;
	protected $_operator; // = <> > < >= <= BETWEEN LIKE IN
	protected $_right;

	protected $_before = Query::WHERE_AND;



	public function __toString()
	{
		$return = '';
		return $return;
	}


	public function getBefore()
	{
		return $this->_before;
	}

}