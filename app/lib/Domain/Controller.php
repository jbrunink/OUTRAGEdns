<?php


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
			if($this->form->validate($this->request->post))
			{
				$connection = $this->db->getAdapter()->getDriver()->getConnection();
				
				try
				{
					$connection->beginTransaction();
					
					$values = $this->form->getValues();
					
					if(empty($values["owner"]))
						$values["owner"] = $this->response->user->id;
					
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
			$this->response->templates = ZoneTemplate\Content::find()->where([ "owner" => $this->response->user->id ])->order("name ASC")->get("objects");
		
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
			if($this->form->validate($this->request->post))
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
			$this->response->templates = ZoneTemplate\Content::find()->where([ "owner" => $this->response->user->id ])->order("name ASC")->get("objects");
		
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
		
		if(!empty($this->request->get->revision))
		{
			$select = $this->db->select();
			
			$select->from("logs")
				   ->columns([ "the_date", "state" ])
				   ->where([ "id" => $this->request->get->revision ])
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
					
					default:
						$this->response->records["list"][] = $record;
					break;
				}
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
				
				if(empty($this->request->get->preview))
					header('Content-Disposition: attachment; filename="'.$this->content->name.'.json"');
			break;
			
			case "xml":
				header("Content-Type: application/xml");
				
				if(empty($this->request->get->preview))
					header('Content-Disposition: attachment; filename="'.$this->content->name.'.xml"');
			break;
			
			case "bind":
			default:
				header("Content-Type: text/plain");
				
				if(empty($this->request->get->preview))
					header('Content-Disposition: attachment; filename="'.$this->content->name.'.txt"');
			break;
		}
		
		$revision_id = null;
		
		if(!empty($this->request->get->revision))
			$revision_id = $this->request->get->revision;
		
		echo $this->content->export($format, $use_prefix, $revision_id);		
		exit;
	}
	
	
	/**
	 *	Called when we want to retrieve the history.
	 */
	public function revisions($id)
	{
		if(!$this->content->id)
			$this->content->load($id);
		
		if(!$this->content->id || (!$this->response->godmode && $this->content->user->id !== $this->response->user->id))
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
		
		return $this->response->display("index.twig");
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
				$request->where([ "zones.owner" => $this->response->user->id ]);
			
			$this->response->domains = $request->get("objects");
		}
		
		return $this->response->display("index.twig");
	}
}