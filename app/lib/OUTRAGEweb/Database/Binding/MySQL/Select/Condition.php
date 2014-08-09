<?php
/**
 *	Generic scope for OUTRAGEweb Select builder functionality.
 */


namespace OUTRAGEweb\Database\Binding\MySQL\Select;


class Condition
{
	/**
	 *	What is the condition?
	 */
	protected $condition = null;
	
	
	/**
	 *	What is the prefix that should precede this condition?
	 */
	protected $prefix = null;
	
	
	/**
	 *	What is the suffix that should precede this condition?
	 */
	protected $suffix = null;
	
	
	/**
	 *	Called when the condition is created.
	 */
	public function __construct($condition, $prefix = null, $suffix = null)
	{
		$this->condition = $condition;
		$this->prefix = $prefix;
		$this->suffix = $suffix;
		
		return true;
	}
	
	
	/**
	 *	Convert this condition to a string.
	 */
	public function __toString()
	{
		$condition = "";
		
		if($this->prefix)
			$condition .= $this->prefix." ";
		
		if($this->condition)
			$condition .= $this->condition;
		
		if($this->suffix)
			$condition .= " ".$this->suffix;
		
		return $condition;
	}
}