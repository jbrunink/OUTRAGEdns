<?php


namespace OUTRAGEdns\Domain;

use \OUTRAGEdns\Entity;
use \OUTRAGEdns\Record;
use \OUTRAGEdns\User;
use \OUTRAGEdns\Zone;
use \OUTRAGEdns\ZoneTemplate;
use \OUTRAGEdns\ZoneTemplateRecord;


class Content extends Entity\Content
{
	/**
	 *	What zone does this domain belong to?
	 */
	public function getter_zone()
	{
		if(!$this->id)
			return null;
		
		return Zone\Content::find()->where([ "domain_id" => $this->id ])->get("first");
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
		if(!$this->id)
			return null;
		
		$records = Record\Content::find()->where([ "domain_id" => $this->id ])->order("id ASC")->get("objects");
		
		foreach($records as $record)
			$record->parent = $this;
		
		return $records;
	}
	
	
	/**
	 *	How many records does this domain possess?
	 */
	public function getter_records_no()
	{
		if(!$this->id)
			return 0;
		
		return Record\Content::find()->where([ "domain_id" => $this->id ])->get("count");
	}
	
	
	/**
	 *	Get the latest serial of this domain.
	 */
	public function getter_serial()
	{
		$invalid = "0";
		
		foreach($this->records as $record)
		{
			if($record->type != "SOA")
				continue;
			
			$parts = explode(" ", $record->content);
			
			return isset($parts[2]) ? $parts[2] : $invalid;
		}
		
		return $invalid;
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
		$changed = false;
		
		if(array_key_exists("records", $post))
		{
			$changed = true;
			
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
			$changed = true;
			$exports = $this->template->export([ "@", $this->name, ZoneTemplateRecord\Content::MARKER_ZONE => $this->name, ZoneTemplateRecord\Content::MARKER_SERIAL => $this->generateFreshSerial() ]);
			
			foreach($exports as $export)
			{
				$export["domain_id"] = $this->id;
				
				$record = new Record\Content();
				$record->save($export);
			}
		}
		
		unset($this->records);
		unset($this->records_no);
		
		if($changed)
			$this->log("records", [ "records" => $this->records ]);
		
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
		
		if($this->zone)
		{
			$set = array_intersect_key($post, array_flip($this->zone->db_fields));
			
			if($set)
				$this->zone->edit($set);
		}
		
		$changed = false;
		
		if(array_key_exists("records", $post))
		{
			$changed = true;
			
			foreach($this->records as $record)
				$record->remove();
			
			foreach($post["records"] as $item)
			{
				if(empty($item["domain_id"]))
					$item["domain_id"] = $this->id;
				
				$record = new Record\Content();
				$record->save($item);
			}
		}
		
		$this->updateSerial();
		
		unset($this->records);
		unset($this->records_no);
		
		if($changed)
			$this->log("records", [ "records" => $this->records ]);
		
		return $this->id;
	}
	
	
	/**
	 *	Updates the serial record, based on whatever is passed to it.
	 */
	public function updateSerial($serial = null)
	{
		if($serial === null)
			$serial = $this->generateFreshSerial();
		
		foreach($this->records as $record)
		{
			if($record->type != "SOA")
				continue;
			
			$parts = explode(" ", $record->content);
			$parts[2] = $serial;
			
			return $record->edit([ "content" => implode(" ", $parts) ]);
		}
		
		return false;
	}
	
	
	/**
	 *	Use this to generate a new fresh, clean serial based on uh, previous serials
	 *	or something or other.
	 *
	 *	Relies on the same poor method that everyone else is used to doing, you know,
	 *	date and an index and the such.
	 */
	public function generateFreshSerial()
	{
		if($this->serial === "0")
			return sprintf("%s%02d", date("Ymd"), 0);
		
		$result = null;
		
		if(preg_match("/^".date("Ymd")."(\d{2})$/", $this->serial, $result))
			return sprintf("%s%02d", date("Ymd"), (int) $result[1] + 1);
		
		return sprintf("%s%02d", date("Ymd"), 0);
	}
	
	
	/**
	 *	Export the records to a string.
	 */
	public function export($format = "json", $use_prefix = true, $revision_id = null)
	{
		$time = time();
		$records = array();
		
		if(isset($revision_id))
		{
			$select = $this->db->select();
			
			$select->from("logs")
				   ->columns([ "the_date", "state" ])
				   ->where([ "id" => $revision_id ])
				   ->where([ "content_type" => get_class($this) ])
				   ->where([ "content_id" => $this->id ])
				   ->where([ "action" => "records" ])
				   ->limit(1)
				   ->order("the_date DESC");
			
			$statement = $this->db->prepareStatementForSqlObject($select);
			$result = $statement->execute();
			
			$response = iterator_to_array($result);
			
			if(count($response))
			{
				$time = 0;
				$state = unserialize($response[0]["state"]);
				
				if(!empty($state["records"]))
				{
					$time = $response[0]["the_date"];
					$records = $state["records"];
				}
			}
		}
		else
		{
			$records = $this->records;
		}
		
		switch($format)
		{
			case "json":
				$response = [ "domain" => $this->name, "records" => [], "from" => date("r", $time) ];
				
				if($use_prefix)
					$response["prefix"] = true;
				
				foreach($records as $record)
				{
					$store = $record->toArray();
					
					unset($store["id"]);
					unset($store["domain_id"]);
					
					if($use_prefix)
						$store["name"] = $record->prefix;
					
					$response["records"][] = $store;
				}
				
				return json_encode($response);
			break;
			
			case "xml":
				$response = new \SimpleXmlElement("<records></records>");
				$response->addAttribute("domain", $this->name);
				$response->addAttribute("from", date("r", $time));
				
				if($use_prefix)
					$response->addAttribute("prefix", 1);
				
				foreach($records as $record)
				{
					$store = $record->toArray();
					
					unset($store["id"]);
					unset($store["domain_id"]);
					
					if($use_prefix)
						$store["name"] = $record->prefix;
					
					$child = $response->addChild("record");
					
					foreach($store as $key => $value)
						$child->addChild($key, $value);
				}
				
				return $response->asXML();
			break;
			
			case "bind":
				$response = [];
				
				$response[] = ';';
				$response[] = ';    Created by OUTRAGEdns';
				$response[] = ';    Exported from records that were active on '.date("r", $time);
				$response[] = ';';
				$response[] = '';
				
				$response[] = sprintf('$ORIGIN %s', $this->name.".");
				$response[] = sprintf('$TTL %s', "3600");
				$response[] = '';
				
				$max_name_len = 0;
				$max_ttl_len = 0;
				$max_type_len = 0;
				
				foreach($records as $record)
				{
					if($use_prefix)
						$name = $record->prefix;
					else
						$name = ($record->name ? $record->name."." : "").$this->name.".";
					
					$max_name_len = max($max_name_len, strlen($name ?: "@"));
					$max_ttl_len = max($max_ttl_len, strlen((string) $record->ttl));
					$max_type_len = max($max_type_len, strlen($record->type));
				}
				
				if(!$use_prefix)
					$max_name_len += 1;
				
				foreach($records as $record)
				{
					if($use_prefix)
						$name = $record->prefix;
					else
						$name = ($record->name ? $record->name."." : "").$this->name.".";
					
					switch($record->type)
					{
						case "SOA":
							$parts = explode(" ", $record->content);
							
							$response[] = sprintf("%s %s IN %s %s %s (", $use_prefix ? "@" : $this->name.".", $record->ttl, $record->type, $parts[0].".", $parts[1].".");
							$response[] = str_pad("", 4).sprintf("%s ; %s", $parts[2], "serial");
							$response[] = str_pad("", 4).sprintf("%s ; %s", $parts[3], "refresh");
							$response[] = str_pad("", 4).sprintf("%s ; %s", $parts[4], "retry");
							$response[] = str_pad("", 4).sprintf("%s ; %s", $parts[5], "expire");
							$response[] = str_pad("", 4).sprintf("%s ; %s", $parts[6], "minimum");
							$response[] = ")";
							$response[] = '';
						break;
						
						case "MX":
						case "SRV":
							$response[] = sprintf("%s %s IN %s %s %s", str_pad($name ?: "@", $max_name_len), str_pad($record->ttl, $max_ttl_len), str_pad($record->type, $max_type_len), $record->prio, $record->content.".");
						break;
						
						case "NS":
						case "CNAME":
							$response[] = sprintf("%s %s IN %s %s", str_pad($name ?: "@", $max_name_len), str_pad($record->ttl, $max_ttl_len), str_pad($record->type, $max_type_len), $record->content.".");
						break;
						
						default:
							$response[] = sprintf("%s %s IN %s %s", str_pad($name ?: "@", $max_name_len), str_pad($record->ttl, $max_ttl_len), str_pad($record->type, $max_type_len), $record->content);
						break;
					}
				}
				
				$response[] = '';
				
				return implode("\n", $response);
			break;
		}
		
		return null;
	}
}
