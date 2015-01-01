<?php
/**
 *	DatabaseQuery class for Phoenix
 */


namespace OUTRAGEweb\Database\Binding\MySQL;

use \OUTRAGEweb\Construct;


class Result extends Construct\ObjectContainer
{
	/**
	 *	What was the original expression?
	 */
	protected $expression = null;
	
	
	/**
	 *	Return the number of rows
	 */
	public function getter_num_rows()
	{
		return $this->count();
	}
	
	
	/**
	 *	Called when the result has been initiated.
	 */
	public function __construct($expression)
	{
		$this->expression = $expression;
		
		return true;
	}
	
	
	/**
	 *	Retrieve the expression used to get these results.
	 */
	public function getExpression()
	{
		return $this->expression;
	}
}
