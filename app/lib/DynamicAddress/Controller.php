<?php


namespace OUTRAGEdns\DynamicAddress;

use \Exception;
use \OUTRAGEdns\Entity;
use \OUTRAGEdns\Notification;


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
						$values["owner"] = $this->response->user->id;
					
					if(empty($values["token"]))
						$values["token"] = sha1(json_encode($values).uniqid().rand(1, 5000));
					
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
		
		# we will need to get the list of domains that this user owns
		# and use this as the basis for our list
		$list = [];
		
		foreach($this->response->user->domains as $domain)
		{
			foreach($domain->records as $record)
			{
				switch($record->type)
				{
					case "A":
					case "AAAA":
						if(!isset($list[$domain->id]))
						{
							$list[$domain->id] = [
								"domain" => $domain->name,
								"records" => [],
							];
						}
						
						$list[$domain->id]["records"][$record->id] = $record->name;
					break;
				}
			}
			
			if(isset($list[$domain->id]))
				$list[$domain->id]["records"] = array_unique($list[$domain->id]["records"]);
		}
		
		$this->response->available_records = $list;
		
		return $this->toHTML();
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
		
		if($this->request->getMethod() == "POST" && $this->request->request->has("commit"))
		{
			if($this->form->validate($this->request->request))
			{
				$connection = $this->db->getAdapter()->getDriver()->getConnection();
				
				try
				{
					$connection->beginTransaction();
					
					$values = $this->form->getValues();
					
					if(empty($values["token"]))
						$values["token"] = sha1(json_encode($values).uniqid().rand(1, 5000));
					
					$this->content->edit($values);
					
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
		
		# grab a list of currently selected domains
		$list = [];
		
		foreach($this->content->records as $record)
		{
			if($record->targets)
				$list[] = $record->targets[0]->id;
		}
		
		$this->response->selected_records = $list;
		
		# we will need to get the list of domains that this user owns
		# and use this as the basis for our list
		$list = [];
		
		foreach($this->response->user->domains as $domain)
		{
			foreach($domain->records as $record)
			{
				switch($record->type)
				{
					case "A":
					case "AAAA":
						if(!isset($list[$domain->id]))
						{
							$list[$domain->id] = [
								"domain" => $domain->name,
								"records" => [],
							];
						}
						
						$list[$domain->id]["records"][$record->id] = $record->name;
					break;
				}
			}
			
			if(isset($list[$domain->id]))
				$list[$domain->id]["records"] = array_unique($list[$domain->id]["records"]);
		}
		
		$this->response->available_records = $list;
		
		return $this->toHTML();
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
	 *	Called when we want show the grid view.
	 */
	public function grid()
	{
		if(!$this->response->domains)
		{
			$request = Content::find();
			$request->order("id ASC");
			
			if(!$this->response->godmode)
				$request->where([ "owner" => $this->response->user->id ]);
			
			$this->response->domains = $request->get("objects");
		}
		
		return $this->toHTML();
	}
	
	
	/**
	 *	Called when we want to update records with a new IP address.
	 */
	public function updateDynamicAddresses($token)
	{
		$this->content = Content::find()->where([ "token" => $token ])->get("first");
		
		if(!$this->content)
		{
			header("HTTP/1.1 404 Not Found");
			exit;
		}
		
		$connection = $this->db->getAdapter()->getDriver()->getConnection();
		
		try
		{
			$connection->beginTransaction();
			
			# and then we need to go through all the records we have, change
			# the value to what is required...
			$ip_addr = $_SERVER["REMOTE_ADDR"];
			$ip_type = null;
			
			if(filter_var($ip_addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
				$ip_type = "A";
			if(filter_var($ip_addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
				$ip_type = "AAAA";
			
			# and now hunt through all the records, being ruthless
			# in their replacement
			$domains = [];
			
			foreach($this->content->records as $record)
			{
				if(!$record->targets)
					continue;
				
				foreach($record->targets as $target)
				{
					if($target->type == $ip_type && $target->content != $ip_addr)
					{
						if(!isset($domains[$target->parent->id]))
							$domains[$target->parent->id] = $target->parent;
						
						$target->edit([ "content" => $ip_addr ]);
					}
				}
			}
			
			# now we dive back to the domains - we need to update the serial and
			# log the changes to version management.
			foreach($domains as $domain)
			{
				unset($domain->records);
				
				$domain->updateSerial();
				$domain->log("records", [ "records" => $domain->records ]);
			}
			
			$connection->commit();
		}
		catch(Exception $exception)
		{
			$connection->rollback();
		}
		
		exit;
	}
}