<?php


namespace OUTRAGEdns\DynamicAddressRecord;

use \OUTRAGEdns\Record;
use \OUTRAGEdns\DynamicAddress;
use \OUTRAGEdns\Entity;


class Content extends Entity\Content
{
	/**
	 *	Retrieves the parent record.
	 */
	public function getter_parent()
	{
		return DynamicAddress\Content::find()->where([ "id" => $this->dynamic_address_id ])->get("first");
	}
	
	
	/**
	 *	What domain records does this object target?
	 */
	public function getter_targets()
	{
		$find = Record\Content::find();
		
		$find->join("domains", "domains.id = records.domain_id");
		$find->join("zones", "domains.id = zones.domain_id");
		$find->join("dynamic_addresses", "dynamic_addresses.id = dynamic_addresses_records.id");
		
		$find->where([ "records.name" => $this->name ])
			 ->where("records.type IN ('A', 'AAAA')")
			 ->where("zones.owner = dynamic_addresses.owner");
		
		return $find->get("objects");
	}
	
	
	/**
	 *	How many domain records does this object target?
	 */
	public function getter_targets_no()
	{
		$find = Record\Content::find();
		
		$find->join("domains", "domains.id = records.domain_id");
		$find->join("zones", "domains.id = zones.domain_id");
		$find->join("dynamic_addresses", "dynamic_addresses.id = ".intval($this->dynamic_address_id));
		
		$find->where([ "records.name" => $this->name ])
			 ->where("records.type IN ('A', 'AAAA')")
			 ->where("zones.owner = dynamic_addresses.owner");
		
		return $find->get("count");
	}
}