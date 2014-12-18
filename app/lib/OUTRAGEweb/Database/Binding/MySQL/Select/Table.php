<?php
/**
 *	Generic scope for OUTRAGEweb Select builder functionality.
 */


namespace OUTRAGEweb\Database\Binding\MySQL\Select;


class Table
{
	/**
	 *	What is the table name?
	 */
	protected $table = null;
	
	
	/**
	 *	Are we joining anything?
	 */
	protected $join_dir = null;
	
	
	/**
	 *	What arguments are we passing to the join?
	 */
	protected $where = null;
	
	
	/**
	 *	Called when the condition is created.
	 */
	public function __construct($table, $join_dir = null, $where = null)
	{
		$this->table = $table;
		
		if(!empty($join_dir))
		{
			$this->join_dir = $join_dir;
			$this->where = $where;
		}
		
		return true;
	}
	
	
	/**
	 *	Convert this condition to a string.
	 */
	public function __toString()
	{
		if(!$this->join_dir)
			return $this->table;
		
		$statement = $this->join_dir." ".$this->table;
		
		if($this->where)
			$statement .= " ON ".$this->where;
		
		return $statement;
	}
}