<?php
/**
 *	Improved SQL creator functionality - this atm is just for selecting things
 *	but I'm sure it can be used on updates and such!
 */


namespace OUTRAGEweb\Database\Binding\MySQL;


class Select
{
	/**
	 *	What connection do we need to use as a reference point?
	 */
	protected $db = null;
	
	
	/**
	 *	What fields need to be selected in this query?
	 */
	protected $fields = [];
	
	
	/**
	 *	What tables need to be selected in this query?
	 */
	protected $tables = [];
	
	
	/**
	 *	What conditions need to be applied to get the right results?
	 */
	protected $where = null;
	
	
	/**
	 *	Do we have anything to group by?
	 */
	protected $group = [];
	
	
	/**
	 *	Do we have anything to sort/order?
	 */
	protected $order = [];
	
	
	/**
	 *	Do we need any 'havings' and such?
	 */
	protected $having = null;
	
	
	/**
	 *	Storing how many records we want to return.
	 */
	protected $limit = null;
	
	
	/**
	 *	Storing how many records we want to offset by.
	 */
	protected $offset = null;
	
	
	/**
	 *	Called when initiating the select builder.
	 */
	public function __construct($db)
	{
		$this->db = $db;
		
		$this->where = new Select\Scope();
		$this->having = new Select\Scope();
	}
	
	
	/**
	 *	Select what table we want to grab this from.
	 */
	public function from($table)
	{
		$this->tables[] = new Select\Table($table);
		return $this;
	}
	
	
	/**
	 *	Join from the left
	 */
	public function leftJoin($table, $where = null)
	{
		$this->tables[] = new Select\Table($table, "LEFT JOIN", $where);
		return $this;
	}
	
	
	/**
	 *	Join from the right
	 */
	public function rightJoin($table, $where = null)
	{
		$this->tables[] = new Select\Table($table, "RIGHT JOIN", $where);
		return $this;
	}
	
	
	/**
	 *	Select what fields we want to retrieve from the database.
	 */
	public function select($columns)
	{
		if(is_array($columns))
		{
			foreach($columns as $column)
				$this->fields[] = $column;
		}
		else
		{
			$this->fields[] = $columns;
		}
		
		return $this;
	}
	
	
	/**
	 *	Called when we want to add a condition to the stack.
	 */
	public function where($condition, $value = null)
	{
		if(isset($value))
			$condition = str_replace("?", $this->db->quote($value), $condition);
		
		$this->where[] = new Select\Condition($condition, count($this->where) ? "AND" : "");
		return $this;
	}
	
	
	/**
	 *	Called where we want to stick an OR into the mix.
	 */
	public function whereOr($condition, $value = null)
	{
		if(isset($value))
			$condition = str_replace("?", $this->db->quote($value), $condition);
		
		if(count($this->where))
		{
			$scope = new Select\Scope();
			$scope[] = $this->where;
			
			$this->where = $scope;
			$this->where[] = new Select\Condition($condition, "OR");
		}
		else
		{
			$this->where[] = new Select\Condition($condition);
		}
		
		return $this;
	}
	
	
	/**
	 *	Do we need to group anything?
	 */
	public function group($condition)
	{
		$this->group[] = new Select\Condition($condition);
		return $this;
	}
	
	
	/**
	 *	Do we need to order anything?
	 */
	public function order($condition)
	{
		$this->order[] = new Select\Condition($condition);
		return $this;
	}
	
	
	/**
	 *	Called when we want to add a condition to the stack.
	 */
	public function having($condition, $value = null)
	{
		if(isset($value))
			$condition = str_replace("?", $this->db->quote($value), $condition);
		
		$this->having[] = new Select\Condition($condition, count($this->having) ? "AND" : "");
		return $this;
	}
	
	
	/**
	 *	Called where we want to stick an OR into the mix.
	 */
	public function havingOr($condition, $value = null)
	{
		if(isset($value))
			$condition = str_replace("?", $this->db->quote($value), $condition);
		
		if(count($this->having))
		{
			$scope = new Select\Scope();
			$scope[] = $this->having;
			
			$this->having = $scope;
			$this->having[] = new Select\Condition($condition, "OR");
		}
		else
		{
			$this->having[] = new Select\Condition($condition);
		}
		
		return $this;
	}
	
	
	/**
	 *	Do we have to limit anything?
	 */
	public function limit($limit)
	{
		$this->limit = $limit;
		return $this;
	}
	
	
	/**
	 *	Do we have to offset anything?
	 */
	public function offset($offset)
	{
		$this->offset = $offset;
		return $this;
	}
	
	
	/**
	 *	Invoke this statement, get it to do something useful.
	 */
	public function invoke()
	{
		return $this->db->query($this->__toString());
	}
	
	
	/**
	 *	Compile this select into something useful.
	 */
	public function __toString()
	{
		$statement = "";
		
		if(count($this->tables))
		{
			$statement = "SELECT ";
			
			if(count($this->fields))
				$statement .= implode(", ", $this->fields);
			else
				$statement .= "*";
			
			$statement .= " FROM ";
			$statement .= implode(" ", $this->tables);
		}
		
		if(count($this->where))
		{
			if(count($this->tables))
				$statement .= " WHERE ";
			
			$statement .= $this->where;
		}
		
		if(count($this->having))
		{
			if(count($this->tables))
				$statement .= " HAVING ";
			
			$statement .= $this->having;
		}
		
		if(count($this->group))
			$statement .= " GROUP BY ".implode(", ", $this->group);
		
		if(count($this->order))
			$statement .= " ORDER BY ".implode(", ", $this->order);
		
		if($this->limit)
		{
			$statement .= " LIMIT ".$this->db->quote($this->limit);
			
			if($this->offset)
				$statement .= " OFFSET ".$this->db->quote($this->offset);
		}
		
		return $statement;
	}
}