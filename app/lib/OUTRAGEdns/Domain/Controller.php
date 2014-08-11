<?php
/**
 *	Domain model for OUTRAGEdns
 */


namespace OUTRAGEdns\Domain;

use \OUTRAGEdns\Entity;
use \OUTRAGEdns\ZoneTemplate;


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
					$this->content->save($this->form->values());
					$this->content->db->commit();
					
					header("Location: ".$this->content->actions->edit);
				}
				catch(Exception $exception)
				{
					$this->content->db->rollback();
				}
			}
		}
		
		if(!$this->response->templates)
		{
			$request = ZoneTemplate\Content::find();
			$request->where("1");
			$request->order("name ASC");
			
			$this->response->templates = $request->invoke("objects");
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
				}
				catch(Exception $exception)
				{
					$this->content->db->rollback();
				}
			}
		}
		
		if(!$this->response->templates)
		{
			$request = ZoneTemplate\Content::find();
			$request->where("1");
			$request->order("name ASC");
			
			$this->response->templates = $request->invoke("objects");
		}
		
		return $this->response->display("index.twig");
	}
	
	
	/**
	 *	Called when we want to remove a domain.
	 */
	public function remove($id)
	{
	}
	
	
	/**
	 *	Called when we want show the grid view.
	 */
	public function grid()
	{
	}
}