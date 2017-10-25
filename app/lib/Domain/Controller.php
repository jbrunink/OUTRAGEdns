<?php


namespace OUTRAGEdns\Domain;

use \Net_DNS2_Exception as DnsException;
use \Net_DNS2_Packet_Response as DnsPacketResponse;
use \Net_DNS2_Resolver as DnsResolver;
use \OUTRAGEdns\Entity;
use \OUTRAGEdns\Notification;
use \OUTRAGEdns\Record;
use \OUTRAGEdns\ZoneTemplate;
use \Symfony\Component\HttpFoundation\Response;


class Controller extends Entity\Controller
{
	/**
	 *	Called when we want to add a domain.
	 */
	public function add()
	{
		if($this->request->getMethod() == "POST" && $this->request->request->has("commit"))
		{	
			if($this->form->validate($this->request->request))
			{
				$connection = $this->db->getAdapter()->getDriver()->getConnection();
				
				try
				{
					$connection->beginTransaction();
					
					$values = $this->form->getValues();
					
					if(empty($values["owner"]))
						$values["owner"] = $this->user->id;
					
					$this->content->save($values);
					
					$connection->commit();
					
					new Notification\Success("Successfully created the domain: ".$this->content->name);
					
					header("Location: ".$this->content->actions->edit);
					exit;
				}
				catch(Exception $exception)
				{
					$connection->rollback();
					
					new Notification\Error("This domain wasn't added due to an internal error.");
				}
			}
		}
		
		if(!$this->response->templates)
			$this->response->templates = ZoneTemplate\Content::find()->where([ "owner" => $this->user->id ])->order("name ASC")->get("objects");
		
		return $this->toHTML();
	}
	
	
	/**
	 *	Called when we want to edit a domain.
	 */
	public function edit($id)
	{
		if(!$this->content->id)
			$this->content->load($id);
		
		if(!$this->content->id || (!$this->response->godmode && $this->content->user->id !== $this->user->id))
		{
			new Notification\Error("You don't have access to this domain.");
			
			header("Location: ".$this->content->actions->grid);
			exit;
		}
		
		if($this->request->getMethod() == "POST" && $this->request->request->has("commit"))
		{
			if($this->form->validate($this->request->request))
			{
				$connection = $this->db->getAdapter()->getDriver()->getConnection();
				
				try
				{
					$connection->beginTransaction();
					
					$this->content->edit($this->form->getValues());
					
					$connection->commit();
					
					new Notification\Success("Successfully updated the domain: ".$this->content->name);
				}
				catch(Exception $exception)
				{
					$connection->rollback();
					
					new Notification\Error("This zone template wasn't edited due to an internal error.");
				}
			}
		}
		
		if(!$this->response->templates)
			$this->response->templates = ZoneTemplate\Content::find()->where([ "owner" => $this->user->id ])->order("name ASC")->get("objects");
		
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
		
		if($this->request->query->has("revision"))
		{
			$select = $this->db->select();
			
			$select->from("logs")
				   ->columns([ "the_date", "state" ])
				   ->where([ "id" => $this->request->query->get("revision") ])
				   ->where([ "content_type" => get_class($this->content) ])
				   ->where([ "content_id" => $this->content->id ])
				   ->where([ "action" => "records" ])
				   ->limit(1)
				   ->order("the_date DESC");
			
			$statement = $this->db->prepareStatementForSqlObject($select);
			$result = $statement->execute();
			
			$response = iterator_to_array($result);
			
			if(!count($response))
			{
				header("Location: ".$this->content->actions->edit);
				exit;
			}
			
			$state = unserialize($response[0]["state"]);
			
			if(!empty($state["records"]))
			{
				foreach($state["records"] as $record)
				{
					switch($record->type)
					{
						case "SOA":
							$this->response->records["soa"][] = $record;
						break;
						
						case "NS":
							$this->response->nameservers[] = $record->content;
							$this->response->records["list"][] = $record;
						break;
						
						default:
							$this->response->records["list"][] = $record;
						break;
					}
				}
			}
			
			new Notification\Success("You are currently editing records that were last active on ".date('jS M Y \a\t H:i', $response[0]["the_date"]).'.');
		}
		else
		{
			foreach($this->content->records as $record)
			{
				switch($record->type)
				{
					case "SOA":
						$this->response->records["soa"][] = $record;
					break;
					
					case "NS":
						$this->response->nameservers[] = $record->content;
						$this->response->records["list"][] = $record;
					break;
					
					default:
						$this->response->records["list"][] = $record;
					break;
				}
			}
		}
		
		$this->response->nameservers = array_unique($this->response->nameservers);
		
		return $this->toHTML();
	}
	
	
	/**
	 *	Called when we want to import a set of records into this
	 *	panel. Supports either XML/JSON exports from OUTRAGEdns or
	 *	DNS zone files
	 */
	public function import($id)
	{
		$form = new FormImport();
		
		if(!$this->content->id)
			$this->content->load($id);
		
		if(!$this->content->id || (!$this->response->godmode && $this->content->user->id !== $this->user->id))
		{
			new Notification\Error("You don't have access to this domain.");
			
			header("Location: ".$this->content->actions->grid);
			exit;
		}
		
		if($this->request->getMethod() == "POST" && $this->request->request->has("commit"))
		{
			if($form->validate($this->request->request))
			{
				$values = $form->getValues();
				
				$helper = new ImportParser();
				$helper->parse($values["upload"]);
				
				var_dump($helper);
				exit;
			}
			else
			{
				new Notification\Error("No correct import was provided.");
			}
		}
		
		return $this->toHTML();
	}
	
	
	/**
	 *	Called when we want to remove a domain.
	 */
	public function remove($id)
	{
		if(!$this->content->id)
			$this->content->load($id);
		
		if(!$this->content->id || (!$this->response->godmode && $this->content->user->id !== $this->user->id))
		{
			new Notification\Error("You don't have access to this domain.");
			
			header("Location: ".$this->content->actions->grid);
			exit;
		}
		
		$connection = $this->db->getAdapter()->getDriver()->getConnection();
		
		try
		{
			$connection->beginTransaction();
			
			$this->content->remove();
			
			$connection->commit();
			
			new Notification\Success("Successfully removed the domain: ".$this->content->name);
		}
		catch(Exception $exception)
		{
			$connection->rollback();
			
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
		
		if(!$this->content->id || (!$this->response->godmode && $this->content->user->id !== $this->user->id))
		{
			new Notification\Error("You don't have access to this domain.");
			
			header("Location: ".$this->content->actions->grid);
			exit;
		}
		
		$format = $this->request->query->get("format") ?: "json";
		$use_prefix = $this->request->query->get("prefix");
		
		$response = new Response();
		$response->setStatusCode(Response::HTTP_OK);
		
		switch($format)
		{
			case "json":
				$response->headers->set("Content-Type", "application/json");
				
				if($this->request->query->get("preview"))
					$response->headers->set("Content-Disposition", 'attachment; filename="'.$this->content->name.'.json"');
			break;
			
			case "xml":
				$response->headers->set("Content-Type", "application/xml");
				
				if($this->request->query->get("preview"))
					$response->headers->set("Content-Disposition", 'attachment; filename="'.$this->content->name.'.xml"');
			break;
			
			case "bind":
			default:
				$response->headers->set("Content-Type", "text/plain");
				
				if($this->request->query->get("preview"))
					$response->headers->set("Content-Disposition", 'attachment; filename="'.$this->content->name.'.txt"');
			break;
		}
		
		$revision_id = null;
		
		if($this->request->query->has("revision"))
			$revision_id = $this->request->query->get("revision");
		
		$response->setContent($this->content->export($format, $use_prefix, $revision_id));
		
		return $response;
	}
	
	
	/**
	 *	Called when we want to retrieve the history.
	 */
	public function revisions($id)
	{
		if(!$this->content->id)
			$this->content->load($id);
		
		if(!$this->content->id || (!$this->response->godmode && $this->content->user->id !== $this->user->id))
		{
			new Notification\Error("You don't have access to this domain.");
			
			header("Location: ".$this->content->actions->grid);
			exit;
		}
		
		$select = $this->db->select();
		
		$select->from("logs")
			   ->columns([ "id", "the_date" ])
			   ->where([ "content_type" => get_class($this->content) ])
			   ->where([ "content_id" => $this->content->id ])
			   ->where([ "action" => "records" ])
			   ->order("the_date DESC");
		
		$statement = $this->db->prepareStatementForSqlObject($select);
		$result = $statement->execute();
		
		$this->response->revisions = iterator_to_array($result);
		
		return $this->toHTML();
	}
	
	
	/**
	 *	Called when we want to test the records
	 */
	public function test($id)
	{
		if(!$this->content->id)
			$this->content->load($id);
		
		if(!$this->content->id || (!$this->response->godmode && $this->content->user->id !== $this->user->id))
		{
			new Notification\Error("You don't have access to this domain.");
			
			header("Location: ".$this->content->actions->grid);
			exit;
		}
		
		# might we have support for revisions? meh, probably not
		$records = $this->content->records;
		
		# it's probably a good idea to test both the server we're currently on
		# as well as some publically accessable servers
		$nameservers = [
			"Google" => [
				"8.8.8.8",
				"8.8.4.4",
			],
		];
		
		$results = [];
		
		$record_compare = function($name, Record\Content $record, DnsPacketResponse $result)
		{
			switch($record->type)
			{
				case "SOA":
					return true;
				break;
				
				default:
					$answer_compare = function($record, $answer) use ($name)
					{
						if($answer->name != $name)
							return false;
						
						if(isset($answer->address))
						{
							if($answer->address == $record->content)
								return true;
						}
						
						return false;
					};
					
					foreach($result->answer as $answer)
					{
						if($answer_compare($record, $answer))
							return true;
					}
				break;
			}
			
			return false;
		};
		
		foreach($nameservers as $key => $list)
		{
			$resolver = new DnsResolver([
				"nameservers" => $list,
				"use_tcp" => true,
			]);
			
			foreach($records as $record)
			{
				if(!isset($results[$record->id]))
					$results[$record->id] = [];
				
				try
				{
					$name = ($record->name ? $record->name."." : "").$this->content->name;
					$result = $resolver->query($name, $record->type);
					
					$results[$record->id][$key] = $record_compare($name, $record, $result);
				}
				catch(DnsException $exception)
				{
					$results[$record->id][$key] = false;
				}
			}
		}
		
		var_dump($results);
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
			$request->join("zones", "zones.domain_id = domains.id");
			$request->order("id ASC");
			
			if(!$this->response->godmode)
				$request->where([ "zones.owner" => $this->user->id ]);
			
			$this->response->domains = $request->get("objects");
		}
		
		return $this->toHTML();
	}
}