<?php


namespace OUTRAGEdns\User;

use \OUTRAGEdns\Entity;
use \OUTRAGEdns\Notification;


class Controller extends Entity\Controller
{
	/**
	 *	Called when we want to add a domain.
	 */
	public function add()
	{
		$this->form->rulesAdd();
		
		if($this->response->godmode)
			$this->form->rulesAdmin();
		
		if($this->request->getMethod() == "POST" && $this->request->request->has("commit"))
		{
			if($this->form->validate($this->request->request))
			{
				$connection = $this->db->getAdapter()->getDriver()->getConnection();
				
				try
				{
					$connection->beginTransaction();
					
					$this->content->save($this->form->getValues());
					
					$connection->commit();
					
					new Notification\Success("Successfully added this user.");
					
					header("Location: ".$this->content->actions->edit);
					exit;
				}
				catch(Exception $exception)
				{
					$connection->rollback();
					
					new Notification\Error("This user wasn't added due to an internal.");
				}
			}
		}
		
		return $this->toHTML();
	}
	
	
	/**
	 *	Called when we want to edit a user.
	 */
	public function edit($id)
	{
		$this->form->rulesEdit();
		
		if($this->response->godmode)
			$this->form->rulesAdmin();
		
		if(!$this->content->id)
			$this->content->load($id);
		
		if(!$this->content->id || (!$this->response->godmode && $this->content->id !== $this->response->user->id))
		{
			new Notification\Error("You don't have access to this user.");
			
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
					
					if($this->request->session->current_users_id == $this->content->id)
						new Notification\Success("Successfully updated your profile.");
					else
						new Notification\Success("Successfully updated this user.");
				}
				catch(Exception $exception)
				{
					$connection->rollback();
					
					new Notification\Error("This user wasn't edited due to an internal.");
				}
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
		
		if(!$this->content->id || (!$this->response->godmode && $this->content->id !== $this->response->user->id))
		{
			new Notification\Error("You don't have access to this user.");
			
			header("Location: ".$this->content->actions->grid);
			exit;
		}
		
		$connection = $this->db->getAdapter()->getDriver()->getConnection();
		
		try
		{
			$connection->beginTransaction();
			
			$this->content->remove();
			
			$connection->commit();
			
			new Notification\Success("Successfully removed this user.");
		}
		catch(Exception $exception)
		{
			$connection->rollback();
					
			new Notification\Error("This user wasn't removed due to an internal.");
		}
		
		header("Location: ".$this->content->actions->grid);
		exit;
	}
	
	
	/**
	 *	Called when we want show the grid view.
	 */
	public function grid()
	{
		if(!$this->response->godmode)
		{
			header("Location: /");
			exit;
		}
		
		if(!$this->response->users)
		{
			$request = Content::find();
			$request->order("id ASC");
			
			$this->response->users = $request->get("objects");
		}
		
		return $this->toHTML();
	}
	
	
	/**
	 *	Called when we want a user to access their account.
	 */
	public function account()
	{
		return $this->edit($this->response->user->id);
	}
	
	
	/**
	 *	So, we have a dashboard now.
	 */
	public function dashboard()
	{
		return $this->toHTML();
	}
	
	
	/**
	 *	Called when we want to let someone into the panel.
	 */
	public function login()
	{
		$this->response->fullwidth = true;
		
		if($this->request->getMethod() == "POST" && $this->request->request->has("commit"))
		{
			$form = new FormAuthenticate();
			
			if($form->validate($this->request->request))
			{
				if($this->content->authenticate($this->request, $form->getValues()))
				{
					header("Location: /");
					exit;
				}
			}
		}
		
		return $this->toHTML();
	}
	
	
	/**
	 *	Called when we want to log someone out of the panel.
	 */
	public function logout()
	{
		if($this->response->user)
			$this->response->user->logout($this->request);
		
		header("Location: /");
		exit;
	}
}
