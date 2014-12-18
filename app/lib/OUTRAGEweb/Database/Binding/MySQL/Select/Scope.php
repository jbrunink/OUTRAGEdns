<?php
/**
 *	Generic scope for OUTRAGEweb Select builder functionality.
 */


namespace OUTRAGEweb\Database\Binding\MySQL\Select;

use \OUTRAGEweb\Construct;


class Scope extends Construct\ObjectContainer
{
	/**
	 *	Turn this scope container into a string.
	 */
	public function __toString()
	{
		if(!count($this))
			return "";
		
		$statement = "(";
		
		foreach($this as $index => $condition)
		{
			if($index > 0)
				$statement .= " ";
			
			$statement .= $condition;
		}
		
		$statement .= ")";
		
		return $statement;
	}
}