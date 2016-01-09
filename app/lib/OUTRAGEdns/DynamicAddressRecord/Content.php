<?php
/**
 *	Record model for OUTRAGEdns
 */


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
		return DynamicAddress\Content::find()->where("id = ?", $this->dynamic_address_id)->invoke("first");
	}
	
	
	/**
	 *	What domain records does this object target?
	 */
	public function getter_targets()
	{
		$find = Record\Content::find();
		
		$find->leftJoin("domains", "domains.id = records.domain_id");
		$find->leftJoin("zones", "domains.id = zones.domain_id");
		$find->leftJoin("dynamic_addresses", "dynamic_addresses.id = ".$this->db->quote($this->dynamic_address_id));
		
		$find->where("records.name = ?", $this->name)
			 ->where("records.type IN ('A', 'AAAA')")
			 ->where("zones.owner = dynamic_addresses.owner");
		
		return $find->invoke("objects");
	}
	
	
	/**
	 *	How many domain records does this object target?
	 */
	public function getter_targets_no()
	{
		$find = Record\Content::find();
		
		$find->leftJoin("domains", "domains.id = records.domain_id");
		$find->leftJoin("zones", "domains.id = zones.domain_id");
		$find->leftJoin("dynamic_addresses", "dynamic_addresses.id = ".$this->db->quote($this->dynamic_address_id));
		
		$find->where("records.name = ?", $this->name)
			 ->where("records.type IN ('A', 'AAAA')")
			 ->where("zones.owner = dynamic_addresses.owner");
		
		return $find->invoke("count");
	}
}