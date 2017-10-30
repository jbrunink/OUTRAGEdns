<?php


namespace OUTRAGEdns\DynamicAddress;

use \OUTRAGEdns\DynamicAddressRecord;
use \OUTRAGEdns\Record;
use \OUTRAGEdns\Entity;
use \OUTRAGEdns\User;


class Content extends Entity\Content
{
	/**
	 *	Returns the user that owns this object.
	 */
	protected function getter_user()
	{
		if(!$this->owner)
			return null;
		
		return User\Content::find()->where([ "id" => $this->owner ])->get("first");
	}
	
	
	/**
	 *	Now, for the fun bit of retrieving all the records that belong to this
	 *	domain.
	 */
	protected function getter_records()
	{
		if(!$this->id)
			return null;
		
		$records = DynamicAddressRecord\Content::find()->where([ "dynamic_address_id" => $this->id ])->order("id ASC")->get("objects");
		
		foreach($records as $record)
			$record->parent = $this;
		
		return $records;
	}
	
	
	/**
	 *	How many records does this domain possess?
	 */
	protected function getter_records_no()
	{
		if(!$this->id)
			return 0;
		
		return DynamicAddressRecord\Content::find()->where([ "dynamic_address_id" => $this->id ])->get("count");
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
				# what record are we targeting? IDs of records are now
				# passed here instead
				$target = new Record\Content();
				
				if($target->load($item))
				{
					$record = new DynamicAddressRecord\Content();
					
					$data = array
					(
						"dynamic_address_id" => $this->id,
						"domain_id" => $target->domain_id,
						"name" => $target->name,
					);
					
					$record->save($data);
				}
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
			foreach($this->records as $record)
				$record->remove();
			
			foreach($post["records"] as $item)
			{
				# what record are we targeting? IDs of records are now
				# passed here instead
				$target = new Record\Content();
				
				if($target->load($item))
				{
					$record = new DynamicAddressRecord\Content();
					
					$data = array
					(
						"dynamic_address_id" => $this->id,
						"domain_id" => $target->domain_id,
						"name" => $target->name,
					);
					
					$record->save($data);
				}
			}
		}
		
		return $this->id;
	}
}