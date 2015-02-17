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
		
		if(!empty($this->request->get->revision))
		{
			$stmt = $this->content->db->select()
			             ->from("logs")
			             ->select([ "the_date", "state" ])
			             ->where("id = ?", $this->request->get->revision)
			             ->where("content_type = ?", get_class($this->content))
			             ->where("content_id = ?", $this->content->id)
			             ->where("action = ?", "records")
			             ->order("the_date DESC");
			
			$response = $stmt->invoke();
			
			if(!count($response))
			{
				header("Location: ".$this->content->actions->edit);
				exit;
			}
			
			$state = unserialize($response[0]["state"]);
			
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
		
		echo $this->content->export($format, $use_prefix);		
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
		
		$stmt = $this->content->db->select()
		             ->from("logs")
		             ->select([ "id", "the_date" ])
		             ->where("content_type = ?", get_class($this->content))
		             ->where("content_id = ?", $this->content->id)
		             ->where("action = ?", "records")
		             ->order("the_date DESC");
		
		$this->response->revisions = $stmt->invoke()->toArray();
		
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
			$request->leftJoin("zones", "zones.domain_id = ".$this->content->db_table.".id");
			$request->sort("id ASC");
			
			if(!$this->response->godmode)
				$request->where("zones.owner = ?", $this->response->user->id);
			
			$this->response->domains = $request->invoke("objects");
		}
		
		return $this->response->display("index.twig");
	}
}
