<?php


namespace OUTRAGEdns\Entity;

use \OUTRAGEdns\Configuration\Action;
use \ReflectionObject;
use \Zend\Db\Metadata\Metadata as DbMetadata;


trait ContentDelegatorTrait
{
	/**
	 *	Retrieve the config for this element.
	 */
	protected function getter_settings()
	{
		$reflection = new ReflectionObject($this);
		
		$namespace = $reflection->getNamespaceName();
		$namespace = str_replace("\\", ".", $namespace);
		
		foreach($this->config->entities as $entity)
		{
			if($entity->namespace == $namespace)
				return $this->settings = $entity;
		}
		
		return null;
	}
	
	
	/**
	 *	Return the database name associated with this object.
	 */
	protected function getter_db_table()
	{
		if(!$this->settings)
			return false;
		
		return $this->settings->table;
	}
	
	
	/**
	 *	Return the fields that this object has available in its table.
	 */
	protected function getter_db_fields()
	{
		if(!$this->db_table)
			return array();
		
		$metadata = new DbMetadata($this->db->getAdapter());
		
		$list = [];
		
		foreach($metadata->getTable($this->db_table)->getColumns() as $column)
			$list[] = $column->getName();
		
		return $this->db_fields = $list;
	}
	
	
	/**
	 *	Let's define some actions.
	 */
	protected function getter_actions()
	{
		$actions = new Action();
		$endpoint = $this->settings->route ?: $this->settings->type."s";
		
		foreach($this->settings->actions as $action => $info)
		{
			if(!empty($info->id) && empty($this->id))
				continue;
			
			$path = "/".$endpoint."/".$action."/";
			
			if(!empty($info->id))
				$path .= $this->id."/";
			
			$actions[$action] = $path;
		}
		
		return $actions;
	}
}