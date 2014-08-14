<?php
/**
 *	ZoneTemplate model for OUTRAGEdns
 */


namespace OUTRAGEdns\ZoneTemplate;

use \OUTRAGEdns\Entity;
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
					
					if($this->response->user)
						$values["owner"] = $this->response->user;
					
					$this->content->save($values);
					$this->content->db->commit();
					
					new Notification\Success("Successfully created the zone template: ".$this->content->name);
					
					header("Location: ".$this->content->actions->edit);
					exit;
				}
				catch(Exception $exception)
				{
					$this->content->db->rollback();
				}
			}
		}
		
		return $this->response->display("index.twig");
	}
	
	
	/**
	 *	Called when we want to edit a domain.
	 */
	public function edit($id)
	{
		if(!$this->content->id)
			$this->content->load($id);
		
		if(!empty($this->request->post->commit))
		{
			if($this->form->validate($this->request->post->toArray()))
			{
				try
				{
					$this->content->db->begin();
					$this->content->edit($this->form->values());
					$this->content->db->commit();
					
					new Notification\Success("Successfully updated the zone template: ".$this->content->name);
				}
				catch(Exception $exception)
				{
					$this->content->db->rollback();
				}
			}
		}
		
		return $this->response->display("index.twig");
	}
	
	
	/**
	 *	Called when we want to remove a domain.
	 */
	public function remove($id)
	{
		if(!$this->content->id)
			$this->content->load($id);
		
		try
		{
			$this->content->db->begin();
			$this->content->remove();
			$this->content->db->commit();
			
			new Notification\Success("Successfully removed the zone template: ".$this->content->name);
		}
		catch(Exception $exception)
		{
			$this->content->db->rollback();
		}
		
		header("Location: ".$this->content->actions->grid);
		exit;
	}
	
	
	/**
	 *	Called when we want show the grid view.
	 */
	public function grid()
	{
		if(!$this->response->templates)
		{
			$request = Content::find();
			$request->where("owner = ?", $this->response->user->id);
			$request->sort("id ASC");
			
			$this->response->templates = $request->invoke("objects");
		}
		
		return $this->response->display("index.twig");
	}
}