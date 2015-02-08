<?php
/**
 *	Domain model for OUTRAGEdns
 */


namespace OUTRAGEdns\Domain;

use \OUTRAGEdns\Entity;
use \OUTRAGEdns\ZoneTemplate;
use \OUTRAGEdns\Notification;


class Controller extends Entity\Controller
{
	/**
	 *	Called when we want to add a domain.
	 */
	public function add()
	{
		if(!empty($this->request->post->commit))
		{
			if($this->form->validate($this->request->post->toArray()))
			{
				try
				{
					$this->content->db->begin();
					
					$values = $this->form->values();
					
					if(empty($values["owner"]))
						$values["owner"] = $this->response->user;
					
					$this->content->save($values);
					$this->content->db->commit();
					
					new Notification\Success("Successfully created the domain: ".$this->content->name);
					
					header("Location: ".$this->content->actions->edit);
					exit;
				}
				catch(Exception $exception)
				{
					$this->content->db->rollback();
					
					new Notification\Error("This domain wasn't added due to an internal error.");
				}
			}
		}
		
		if(!$this->response->templates)
			$this->response->templates = ZoneTemplate\Content::find()->where("owner = ?", $this->response->user->id)->order("name ASC")->invoke("objects");
		
		return $this->response->display("index.twig");
	}
	
	
	/**
	 *	Called when we want to edit a domain.
	 */
	public function edit($id)
	{
		if(!$this->content->id)
			$this->content->load($id);
		
		if(!$this->content->id || (!$this->response->godmode && $this->content->user->id !== $this->response->user->id))
		{
			new Notification\Error("You don't have access to this domain.");
			
			header("Location: ".$this->content->actions->grid);
			exit;
		}
		
		if(!empty($this->request->post->commit))
		{
			if($this->form->validate($this->request->post->toArray()))
			{
				try
				{
					$this->content->db->begin();
					$this->content->edit($this->form->values());
					$this->content->db->commit();
					
					new Notification\Success("Successfully updated the domain: ".$this->content->name);
				}
				catch(Exception $exception)
				{
					$this->content->db->rollback();
					
					new Notification\Error("This zone template wasn't edited due to an internal error.");
				}
			}
		}
		
		if(!$this->response->templates)
			$this->response->templates = ZoneTemplate\Content::find()->where("owner = ?", $this->response->user->id)->order("name ASC")->invoke("objects");
		
		# list all the nameservers that are currently defined
		$this->response->nameservers = [];
		
		if(!empty($this->config->records->soa->nameservers))
			$this->response->nameservers = array_merge($this->response->nameservers, $this->config->records->soa->nameservers->toArray());
		
		# oh, and it's a good idea to separate out the SOA record(s) from the other
		# records, that way we can make the SOA independently editable
		$this->response->records = array
		(
			"soa" => [],
			"list" => [],
		);
		
		foreach($this->content->records as $record)
		{
			switch($record->type)
			{
				case "SOA":
					$this->response->records["soa"][] = $record;
				break;
				
				case "NS":
					$this->response->nameservers[] = $record->content;
				
				default:
					$this->response->records["list"][] = $record;
				break;
			}
		}
		
		$this->response->nameservers = array_unique($this->response->nameservers);
		
		return $this->response->display("index.twig");
	}
	
	
	/**
	 *	Called when we want to remove a domain.
	 */
	public function remove($id)
	{
		if(!$this->content->id)
			$this->content->load($id);
		
		if(!$this->content->id || (!$this->response->godmode && $this->content->user->id !== $this->response->user->id))
		{
			new Notification\Error("You don't have access to this domain.");
			
			header("Location: ".$this->content->actions->grid);
			exit;
		}
		
		try
		{
			$this->content->db->begin();
			$this->content->remove();
			$this->content->db->commit();
			
			new Notification\Success("Successfully removed the domain: ".$this->content->name);
		}
		catch(Exception $exception)
		{
			$this->content->db->rollback();
			
			new Notification\Error("This zone template wasn't removed due to an internal error.");
		}
		
		header("Location: ".$this->content->actions->grid);
		exit;
	}
	
	
	/**
	 *	Called when we want to export this record.
	 */
	public function export($id)
	{
		if(!$this->content->id)
			$this->content->load($id);
		
		if(!$this->content->id || (!$this->response->godmode && $this->content->user->id !== $this->response->user->id))
		{
			new Notification\Error("You don't have access to this domain.");
			
			header("Location: ".$this->content->actions->grid);
			exit;
		}
		
		$format = !empty($this->request->get->format) ? strtolower($this->request->get->format) : "json";
		$use_prefix = !empty($this->request->get->prefix);
		
		switch($format)
		{
			case "json":
				header("Content-Type: application/json");
				
				$response = [ "domain" => $this->content->name, "records" => [] ];
				
				foreach($this->content->records as $record)
				{
					$store = $record->toArray();
					
					unset($store["id"]);
					unset($store["domain_id"]);
					
					if($use_prefix)
						$store["name"] = $record->prefix;
					
					$response["records"][] = $store;
				}
				
				echo json_encode($response);
			break;
			
			case "xml":
				header("Content-Type: application/xml");
				
				$response = new \SimpleXmlElement("<records></records>");
				$response->addAttribute("domain", $this->content->name);
				
				foreach($this->content->records as $record)
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
				
				echo $response->asXML();
			break;
			
			case "bind":
				header("Content-Type: text/plain");
				
				$response = [];
				
				$response[] = ';';
				$response[] = ';    Created by OUTRAGEdns';
				$response[] = ';';
				$response[] = '';
				
				$response[] = sprintf('$ORIGIN %s', $this->content->name);
				$response[] = sprintf('$TTL %s', "1h");
				$response[] = '';
				
				$max_name_len = 0;
				$max_ttl_len = 0;
				$max_type_len = 0;
				
				foreach($this->content->records as $record)
				{
					$name = $use_prefix ? $record->prefix : $record->name;
					
					$max_name_len = max($max_name_len, strlen($name ?: "@"));
					$max_ttl_len = max($max_ttl_len, strlen((string) $record->ttl));
					$max_type_len = max($max_type_len, strlen($record->type));
				}
				
				foreach($this->content->records as $record)
				{
					$name = $use_prefix ? $record->prefix : $record->name;
					
					switch($record->type)
					{
						case "SOA":
							$parts = explode(" ", $record->content);
							
							$response[] = sprintf("%s %s IN %s %s %s (", $use_prefix ? "@" : $this->content->name, $record->ttl, $record->type, $parts[0], $parts[1]);
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
							$response[] = sprintf("%s %s IN %s %s %s", str_pad($name ?: "@", $max_name_len), str_pad($record->ttl, $max_ttl_len), str_pad($record->type, $max_type_len), $record->prio, $record->content);
						break;
						
						default:
							$response[] = sprintf("%s %s IN %s %s", str_pad($name ?: "@", $max_name_len), str_pad($record->ttl, $max_ttl_len), str_pad($record->type, $max_type_len), $record->content);
						break;
					}
				}
				
				echo implode("\r\n", $response);
			break;
		}
		
		exit;
	}
	
	
	/**
	 *	Called when we want show the grid view.
	 */
	public function grid()
	{
		if(!$this->response->domains)
		{
			$request = Content::find();
			$request->leftJoin("zones", "zones.domain_id = ".$this->content->db_table.".id");
			$request->sort("id ASC");
			
			if(!$this->response->godmode)
				$request->where("zones.owner = ?", $this->response->user->id);
			
			$this->response->domains = $request->invoke("objects");
		}
		
		return $this->response->display("index.twig");
	}
}
