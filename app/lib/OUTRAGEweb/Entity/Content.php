<?php
/**
 *	Content entity for OUTRAGEweb - this is what some people would call a model,
 *	or a data mapper, or whatever. You get the idea.
 */


namespace OUTRAGEweb\Entity;

use \OUTRAGEweb\Construct;


abstract class Content extends Construct\ObjectContainer
{
	/**
	 *	Retrieve the config for this element.
	 */
	public function getter_settings()
	{
		$reflection = new \ReflectionObject($this);
		
		$namespace = $reflection->getNamespaceName();
		$namespace = str_replace("\\", ".", $namespace);
		
		foreach($this->config->entities as $entity)
		{
			if($entity->namespace == $namespace)
				return $entity;
		}
		
		return null;
	}
	
	
	/**
	 *	Return the database name associated with this object.
	 */
	public function getter_db_table()
	{
		if(!$this->settings)
			return false;
		
		return $this->settings->table;
	}
	
	
	/**
	 *	Return the fields that this object has available in its table.
	 */
	public function getter_db_fields()
	{
		if(!$this->db_table)
			return array();
		
		return $this->db->describe($this->db_table);
	}
	
	
	/**
	 *	Called to load an object into memory.
	 */
	public function load($identifier = null)
	{
		$statement = $this->db->select();
		
		$statement->from($this->db_table);
		$statement->select($this->db_fields);
		$statement->where("id = ?", $identifier);
		$statement->limit(1);
		
		$result = $statement->invoke();
		
		if(!$result)
			return false;
		
		$this->resetContainer();
		$this->populateContainer($result[0]);
		
		return $this->id;
	}
	
	
	/**
	 *	Called to save an object to the database, based on an object/array
	 *	passed to this method.
	 */
	public function save($post = array())
	{
		if($this->id)
			return false;
		
		if(method_exists($this, "validate"))
		{
			if(!$this->validate($this, __FUNCTION__, [ $post ]))
				throw new \Exception("Unable to perform action - ".__FUNCTION__);
		}
		
		$result = $this->db->insert($this->db_table, $post, $this->db_fields);
		
		if(!$result)
			return $result;
		
		if(method_exists($this, "onChange"))
			$this->onChange($post);
		
		return $this->load($result);
	}
	
	
	/**
	 *	Called to update an object to the database, based on an object/array
	 *	passed to this method.
	 */
	public function edit($post = array())
	{
		if(!$this->id)
			return false;
		
		if(method_exists($this, "validate"))
		{
			if(!$this->validate($this, __FUNCTION__, [ $post ]))
				throw new \Exception("Unable to perform action - ".__FUNCTION__);
		}
		
		$result = $this->db->update($this->db_table, $post, "id = ".$this->db->quote($this->id), 1, $this->db_fields);
		
		if(!$result)
			return $result;
		
		if(method_exists($this, "onChange"))
			$this->onChange($post);
		
		return $this->load($this->id);
	}
	
	
	/**
	 *	Called to remove an object.
	 */
	public function remove()
	{
		if(method_exists($this, "validate"))
		{
			if(!$this->validate($this, __FUNCTION__, []))
				throw new \Exception("Unable to perform action - ".__FUNCTION__);
		}
		
		return $this->db->delete($this->db_table, "id = ".$this->db->quote($this->id), 1);
	}
	
	
	/**
	 *	We can use this to find objects that match what we want to find.
	 */
	public function find()
	{
		$target = "\\".$this->namespace."\\Find";
		
		if(class_exists($target))
			return new $target($this);
		
		return new Find($this);
	}
}