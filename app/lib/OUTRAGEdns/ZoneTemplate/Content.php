<?php
/**
 *	ZoneTemplate model for OUTRAGEdns
 */


namespace OUTRAGEdns\ZoneTemplate;

use \OUTRAGEdns\Entity;
use \OUTRAGEdns\User;
use \OUTRAGEdns\Record;
use \OUTRAGEdns\ZoneTemplateRecord;


class Content extends Entity\Content
{
	/**
	 *	Returns the user that owns this ZoneTemplate object.
	 */
	public function getter_user()
	{
		return (new User\Content())->find()->where("id = ?", $this->owner)->invoke("first");
	}
	
	
	/**
	 *	Retrieve a list of all records owned by this zone template.
	 */
	public function getter_records()
	{
		return (new ZoneTemplateRecord\Content())->find()->where("domain_id = ?", $this->id)->sort("id ASC")->invoke("objects");
	}
	
	
	/**
	 *	Called when saving a new zone template.
	 */
	public function save($post = array())
	{
		if(!empty($post["owner"]) && is_object($post["owner"]))
			$post["owner"] = $post["owner"]->id;
		
		if(!parent::save($post))
			return false;
		
		if(array_key_exists("records", $post))
		{
			foreach($post["records"] as $item)
			{
				if(empty($item["zone_templ_id"]))
					$item["zone_templ_id"] = $this->id;
				
				$record = new ZoneTemplateRecord\Content();
				$record->save($item);
			}
		}
		
		return $this->id;
	}
	
	
	/**
	 *	Called when editing a zone template.
	 */
	public function edit($post = array())
	{
		if(!empty($post["owner"]) && is_object($post["owner"]))
			$post["owner"] = $post["owner"]->id;
		
		if(!parent::edit($post))
			return false;
		
		if(array_key_exists("records", $post))
		{
			$record = new ZoneTemplateRecord\Content();
			$record->db->delete($record->db_table, "zone_templ_id = ".$this->db->quote($this->id));
			
			foreach($post["records"] as $item)
			{
				if(empty($item["zone_templ_id"]))
					$item["zone_templ_id"] = $this->id;
				
				$record = new ZoneTemplateRecord\Content();
				$record->save($item);
			}
		}
		
		return $this->id;
	}
	
	
	/**
	 *	Export all the records, applying any substitutions to any
	 *	of the fields or values out there.
	 */
	public function export($context = [])
	{
		$exports = [];
		$fields = array_flip((new Record\Content())->db_fields);
		
		foreach($this->records as $record)
		{
			$export = $record->toArray();
			$export = array_intersect_key($export, $fields);
			
			foreach($export as $key => $value)
			{
				foreach($context as $search => $replace)
					$value = str_replace("[".$search."]", $replace, $value);
				
				$export[$key] = $value;
			}
			
			$exports[] = $export;
		}
		
		return $exports;
	}
}