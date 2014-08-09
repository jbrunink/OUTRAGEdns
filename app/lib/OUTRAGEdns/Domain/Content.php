<?php
/**
 *	Domain model for OUTRAGEdns
 */


namespace OUTRAGEdns\Domain;

use \OUTRAGEdns\Entity;
use \OUTRAGEdns\Record;
use \OUTRAGEdns\User;
use \OUTRAGEdns\ZoneTemplate;


class Content extends Entity\Content
{
	/**
	 *	What account owns this domain?
	 */
	public function getter_user()
	{
		$statement = $this->db->select();
		
		$statement->from("zones");
		$statement->fields([ "owner" ]);
		$statement->where("domain_id = ?", $this->id);
		
		$result = $statement->invoke();
		
		if(!$result || !$result[0]["owner"])
			return null;
		
		$content = new User\Content();
		$content->load($result[0]["owner"]);
		
		return $content->id ? $content : null;
	}
	
	
	/**
	 *	What record template, if any, is currently in use on this domain?
	 */
	public function getter_zone_template()
	{
		$statement = $this->db->select();
		
		$statement->from("zones");
		$statement->fields([ "zone_templ_id" ]);
		$statement->where("domain_id = ?", $this->id);
		
		$result = $statement->invoke();
		
		if(!$result || !$result[0]["zone_templ_id"])
			return null;
		
		$content = new ZoneTemplate\Content();
		$content->load($result[0]["zone_templ_id"]);
		
		return $content->id ? $content : null;
	}
	
	
	/**
	 *	Now, for the fun bit of retrieving all the records that belong to this
	 *	domain.
	 */
	public function getter_domains()
	{
		$request = (new Record\Content())->find();
		$request->where("domain_id = ?", $this->id);
		$request->sort("id ASC");
		
		return $request->invoke("objects");
	}
	
	
	/**
	 *	Called when this object has been changed, and we want to perform some
	 *	other operations to it or something.
	 */
	protected function onChange($post = array())
	{
		if(!empty($post["owner"]))
		{
			if(is_object($post["owner"]))
				$post["owner"] = $post["owner"]->id;
		}
		
		if(!empty($post["records"]))
		{
			foreach($post["records"] as $item)
			{
				if(empty($item["domain_id"]))
					$item["domain_id"] = $this->id;
				
				$record = new Record\Content();
				$record->save($item);
				
				var_dump($record->id);
			}
		}
		
		if(!empty($post["records_dropped"]))
		{
			$request = (new Record\Content())->find();
			$request->where("domain_id = ?", $this->id);
			$request->where("id IN (?)", $post["records_dropped"]);
			
			$objects = $request->invoke("objects");
			
			foreach($objects as $object)
				$object->remove();
		}
		
		return true;
	}
}