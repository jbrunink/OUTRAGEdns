<?php


namespace OUTRAGEdns\Entity;

use \Exception;
use \Zend\Db\Sql\Expression;


class Find
{
	/**
	 *	Let's store a select query here!
	 */
	protected $select = null;
	
	
	/**
	 *	What is the source object that we're seeking?
	 */
	protected $content = null;
	
	
	/**
	 *	Called whenever we're booting up the query engine.
	 */
	public function __construct(Content $content)
	{
		$this->content = $content;
		
		$this->select = $this->content->db->select();
		$this->select->from($this->content->db_table);
	}
	
	
	/**
	 *	We can be lazy and wrap all calls that don't exit from this to
	 *	the select engine.
	 */
	public function __call($method, $arguments)
	{
		if(method_exists($this->select, $method))
			call_user_func_array([ $this->select, $method ], $arguments);
		else
			throw new Exception("Method '".$method."' not found");
		
		return $this;
	}
	
	
	/**
	 *	Return the current state of execution as a string.
	 */
	public function __toString()
	{
		return (string) $this->select;
	}
	
	
	/**
	 *	Finish the select, and give results based on what was requested.
	 */
	public function get($type = null)
	{
		$method = "get".ucfirst($type);
		
		if($type && method_exists($this, $method))
			return $this->$method();
		
		return $this->getDefault();
	}
	
	
	/**
	 *	Return the count of this query.
	 */
	protected function getCount()
	{
		$count = 0;
		
		$this->select->reset($this->select::COLUMNS);
		$this->select->columns([ "_count" => new Expression("COUNT(".$this->content->db_table.".id)") ], false);
		
		$this->select->reset($this->select::GROUP);
		
		$statement = $this->content->db->prepareStatementForSqlObject($this->select);
		
		foreach($statement->execute() as $result)
			$count += $result["_count"];
		
		return $count;
	}
	
	
	/**
	 *	Return the objects of this query.
	 */
	protected function getObjects()
	{
		$objects = [];
		
		$this->select->reset($this->select::COLUMNS);
		$this->select->columns([ "_result" => "id" ]);
		
		$this->select->reset($this->select::GROUP);
		$this->select->group($this->content->db_table.".id");
		
		$statement = $this->content->db->prepareStatementForSqlObject($this->select);
		
		foreach($statement->execute() as $result)
		{
			$object = new $this->content->class();
			$object->load($result["_result"]);
			
			if($object->id)
				$objects[] = $object;
		}
		
		return $objects;
	}
	
	
	/**
	 *	Return the subquery of this query.
	 */
	protected function getStatement()
	{
		$objects = [];
		
		$this->select->reset($this->select::COLUMNS);
		$this->select->reset($this->select::GROUP);
		
		$this->select->group($this->content->db_table.".id");
		
		return $this->content->db->prepareStatementForSqlObject($this->select);
	}
	
	
	/**
	 *	Return the first object people find.
	 */
	protected function getFirst()
	{
		$objects = $this->getObjects();
		
		if(!empty($objects[0]))
			return $objects[0];
		
		return null;
	}
	
	
	/**
	 *	The default, unknown return type.
	 */
	protected function getDefault()
	{
		$return = [
			"objects" => [],
			"count" => 0
		];
		
		$return["objects"] = $this->returnObjects();
		$return["count"] = $this->returnCount();
		
		return $return;
	}
}