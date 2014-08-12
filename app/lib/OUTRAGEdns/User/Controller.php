<?php
/**
 *	User model for OUTRAGEdns
 */


namespace OUTRAGEdns\User;

use \OUTRAGEdns\Entity;


class Controller extends Entity\Controller
{
	/**
	 *	Called when we want to add a domain.
	 */
	public function add()
	{
		$this->form->rulesAdd();
		
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
		
		return $this->response->display("index.twig");
	}
	
	
	/**
	 *	Called when we want to edit a domain.
	 */
	public function edit($id)
	{
		$this->form->rulesEdit();
		
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
		if(!$this->response->users)
		{
			$request = Content::find();
			$request->sort("id ASC");
			
			$this->response->users = $request->invoke("objects");
		}
		
		return $this->response->display("index.twig");
	}
	
	
	/**
	 *	Called when we want a user to access their account.
	 */
	public function account()
	{
		return $this->edit($this->response->user->id);
	}
}