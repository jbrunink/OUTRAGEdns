<?php
/**
 *	ZoneTemplate model for OUTRAGEdns
 */


namespace OUTRAGEdns\ZoneTemplate;

use \OUTRAGEdns\Entity;
use \OUTRAGEdns\Entity\User;
use \OUTRAGEdns\ZoneTemplateRecord;


class Content extends Entity\Content
{
	/**
	 *	Returns the user that owns this ZoneTemplate object.
	 */
	public function getter_user()
	{
		$request = (new User\Content())->find();
		$request->where("id = ?", $this->owner);
		
		return $request->invoke("first");
	}
	
	
	/**
	 *	Retrieve a list of all records owned by this zone template.
	 */
	public function getter_records()
	{
		$request = (new ZoneTemplateRecord\Content())->find();
		$request->where("zone_templ_id = ?", $this->id);
		$request->sort("id ASC");
		
		return $request->invoke("objects");
	}
	
	
	/**
	 *	Called when saving a new zone template.
	 */
	public function save($post = array())
	{
		if(!empty($post["owner"]) && is_object($post["owner"]))
			$post["owner"] = $post["owner"]->id;
		
		return parent::save($post);
	}
	
	
	/**
	 *	Called when editing a zone template.
	 */
	public function edit($post = array())
	{
		if(!empty($post["owner"]) && is_object($post["owner"]))
			$post["owner"] = $post["owner"]->id;
		
		return parent::edit($post);
	}
	
	
	/**
	 *	Called when things are being modified in general.
	 */
	protected function onChange($post = array())
	{
		if(!empty($post["records"]))
		{
			foreach($post["records"] as $item)
			{
				if(empty($item["zone_templ_id"]))
					$item["zone_templ_id"] = $this->id;
				
				$record = new ZoneTemplateRecord\Content();
				$record->save($item);
			}
		}
		
		if(!empty($post["records_dropped"]))
		{
			$request = (new ZoneTemplateRecord\Content())->find();
			$request->where("zone_templ_id = ?", $this->id);
			$request->where("id IN (?)", $post["records_dropped"]);
			
			$objects = $request->invoke("objects");
			
			foreach($objects as $object)
				$object->remove();
		}
		
		return true;
	}
}