<?php
/**
 *	DatabaseQuery class for Phoenix
 */


namespace OUTRAGEweb\Database\Binding\MySQL;

use \OUTRAGEweb\Construct;


class Result extends Construct\ObjectContainer
{
	/**
	 *	Database result resource.
	 */
	protected $expression = null;
	protected $result = null;
	
	public $num_rows = null;
	
	
	/**
	 *	Called when the result has been initiated.
	 */
	public function __construct($expression, \mysqli_result $result)
	{
		$this->result = $result;
		$this->expression = $expression;
		
		while(($item = $result->fetch_assoc()))
			$this->push(new \ArrayObject($item));
		
		$this->num_rows = $this->result->num_rows;
		
		return true;
	}
	
	
	/**
	 *	Called when this result has been removed.
	 */
	public function __destruct()
	{
		$this->result->free();
		$this->result = null;
	}
	
	
	/**
	 *	Retrieve to the actual result.
	 */
	public function result()
	{
		return $this->result;
	}
	
	
	/**
	 *	Retrieve the expression used to get these results.
	 */
	public function getExpression()
	{
		return $this->expression;
	}
}