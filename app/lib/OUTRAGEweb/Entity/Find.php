<?php
/**
 *	Find entity for OUTRAGEweb - this is what some people would call a model,
 *	or a data mapper, or whatever. You get the idea.
 */


namespace OUTRAGEweb\Entity;


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
		
		$this->select = $content->db->select();
		$this->select->from($content->db_table);
	}
	
	
	/**
	 *	We can be lazy and wrap all calls that don't exit from this to
	 *	the select engine.
	 */
	public function __call($method, $arguments)
	{
		if(method_exists($this->select, $method))
			call_user_func_array([ $this->select, $method ], $arguments);
		
		return $this;
	}
	
	
	/**
	 *	Finish the select, and give results based on what was requested.
	 */
	public function invoke($type = null)
	{
		$method = "return".ucfirst($type);
		
		if($type && method_exists($this, $method))
			return $this->$method();
		
		return $this->returnDefault();
	}
	
	
	/**
	 *	Return the count of this query.
	 */
	public function returnCount()
	{
		$this->select->select("COUNT(1) AS __item");
		$this->select->group($this->content->db_table.".id");
		
		$results = $this->select->invoke();
		
		return !count($results) ? 0 : (integer) $results[0]["__item"];
	}
	
	
	/**
	 *	Return the objects of this query.
	 */
	public function returnObjects()
	{
		$this->select->select($this->content->db_table.".id AS __item");
		$this->select->group($this->content->db_table.".id");
		
		$objects = [];	
		$results = $this->select->invoke();
		
		foreach($results as $result)
		{
			$object = new $this->content->class();
			$object->load($result["__item"]);
			
			if($object->id)
				$objects[] = $object;
		}
		
		return $objects;
	}
	
	
	/**
	 *	Return the first object people find.
	 */
	public function returnFirst()
	{
		$objects = $this->returnObjects();
		
		if(!empty($objects[0]))
			return $objects[0];
		
		return null;
	}
	
	
	/**
	 *	The default, unknown return type.
	 */
	public function returnDefault()
	{
		$return = [ "objects" => [], "count" => 0 ];
		
		$return["objects"] = $this->returnObjects();
		$return["count"] = $this->returnCount();
		
		return $return;
	}
}