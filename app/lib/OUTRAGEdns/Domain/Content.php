<?php
/**
 *	Domain model for OUTRAGEdns
 */


namespace OUTRAGEdns\Domain;

use \OUTRAGEdns\Entity;
use \OUTRAGEdns\Record;
use \OUTRAGEdns\User;
use \OUTRAGEdns\Zone;
use \OUTRAGEdns\ZoneTemplate;


class Content extends Entity\Content
{
	/**
	 *	What zone does this domain belong to?
	 */
	public function getter_zone()
	{
		return Zone\Content::find()->where("domain_id = ?", $this->id)->invoke("first");
	}
	
	
	/**
	 *	What account owns this domain?
	 */
	public function getter_user()
	{
		if(!$this->zone)
			return null;
		
		return $this->zone->user;
	}
	
	
	/**
	 *	What record template, if any, is currently in use on this domain?
	 */
	public function getter_template()
	{
		if(!$this->zone)
			return null;
		
		return $this->zone->template;
	}
	
	
	/**
	 *	Now, for the fun bit of retrieving all the records that belong to this
	 *	domain.
	 */
	public function getter_records()
	{
		return Record\Content::find()->where("domain_id = ?", $this->id)->sort("id ASC")->invoke("objects");
	}
	
	
	/**
	 *	How many records does this domain possess?
	 */
	public function getter_records_no()
	{
		return Record\Content::find()->where("domain_id = ?", $this->id)->invoke("count");
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
		
		# create a child zone
		$zone = new Zone\Content();
		
		$set = array_merge([ "domain_id" => $this->id ], array_intersect_key($post, array_flip($zone->db_fields)));
		
		if($set)
			$zone->save($set);
		
		# do stuff with records
		if(array_key_exists("records", $post))
		{
			foreach($post["records"] as $item)
			{
				if(empty($item["domain_id"]))
					$item["domain_id"] = $this->id;
				
				$record = new Record\Content();
				$record->save($item);
			}
		}
		
		if($this->template)
		{
			$exports = $this->template->export([ "ZONE" => $this->name, "SERIAL" => 0 ]);
			
			foreach($exports as $export)
			{
				$export["domain_id"] = $this->id;
				
				$record = new Record\Content();
				$record->save($export);
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
			$record = new Record\Content();
			$record->db->delete($record->db_table, "domain_id = ".$this->db->quote($this->id));
			
			foreach($post["records"] as $item)
			{
				if(empty($item["domain_id"]))
					$item["domain_id"] = $this->id;
				
				$record = new Record\Content();
				$record->save($item);
			}
		}
		
		return $this->id;
	}
}