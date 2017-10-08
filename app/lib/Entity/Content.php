<?php


namespace OUTRAGEdns\Entity;

use \Exception;
use \OUTRAGElib\Delegator\DelegatorTrait;
use \OUTRAGElib\Structure\ObjectList;
use \OUTRAGElib\Structure\ObjectListPopulationTrait;
use \OUTRAGElib\Structure\ObjectListRetrievalTrait;


class Content extends ObjectList
{
	/**
	 *	We need to be able to use our special delegators somehow
	 */
	use DelegatorTrait;
	
	
	/**
	 *	Allow the ability to populate this object with a method
	 */
	use ObjectListPopulationTrait;
	
	
	/**
	 *	Allow the ability retrieve stuff from this object
	 */
	use ObjectListRetrievalTrait;
	
	
	/**
	 *	Define relationships between namespaces and classes
	 */
	use EntityDelegatorTrait;
	
	
	/**
	 *	Store our delegators for this class in here
	 */
	use ContentDelegatorTrait;
	
	
	/**
	 *	Called to load an object into memory.
	 */
	public function load($identifier = null)
	{
		if(is_null($identifier))
			return false;
		
		$select = $this->db->select();
		
		$select->from($this->db_table)
			   ->where([ "id" => $identifier ])
			   ->limit(1);
		
		$statement = $this->db->prepareStatementForSqlObject($select);
		
		foreach($statement->execute() as $result)
			$this->populateObjectList($result);
		
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
		
		$db_fields = array_flip($this->db_fields);
		
		$insert = $this->db->insert();
		$insert->into($this->db_table);
		$insert->values(array_intersect_key($post, $db_fields));
		
		$statement = $this->db->prepareStatementForSqlObject($insert);
		
		if($result = $statement->execute())
		{
			if($id = $result->getGeneratedValue())
				return $this->load($id);
		}
		
		return false;
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
		
		$db_fields = array_flip($this->db_fields);
		
		$update = $this->db->update();
		$update->table($this->db_table);
		$update->set(array_intersect_key($post, $db_fields));
		$update->where([ "id" => $this->id ]);
		
		$statement = $this->db->prepareStatementForSqlObject($update);
		$statement->execute();
		
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
		
		$delete = $this->db->delete();
		$delete->from($this->db_table);
		$delete->where([ "id" => $this->id ]);
		
		$statement = $this->db->prepareStatementForSqlObject($delete);
		$statement->execute();
		
		return true;
	}
	
	
	/**
	 *	We can use this to find objects that match what we want to find.
	 *	Now can be called statically!
	 */
	public static function find()
	{
		$class = "\\".get_called_class();
		$content = new $class();
		
		if($content->namespace)
		{
			$target = "\\".$content->namespace."\\Find";
			
			if(class_exists($target))
				return new $target($content);
		}
		
		return new Find($content);
	}
	
	
	/**
	 *	It would be good to log certain things (perhaps)
	 */
	public function log($action, $state = null)
	{
		if(!$this->id)
			return false;
		
		$post = array
		(
			"content_type" => get_class($this),
			"content_id" => $this->id,
			"action" => $action,
			"state" => serialize($state),
			"the_date" => time(),
		);
		
		$insert = $this->db->insert();
		$insert->into("logs");
		$insert->values($post);
		
		$statement = $this->db->prepareStatementForSqlObject($insert);
		$statement->execute();
		
		return true;
	}
}