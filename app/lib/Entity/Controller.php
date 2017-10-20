<?php


namespace OUTRAGEdns\Entity;

use \OUTRAGEdns\Configuration\Configuration;
use \OUTRAGEdns\User;
use \Symfony\Component\HttpFoundation\Request;
use \Silex\Application;


class Controller
{
	/**
	 *	Use custom delegator trait
	 */
	use EntityDelegatorTrait;
	
	
	/**
	 *	This method is called before the path is executed - this can be used to prepare
	 *	stuff like content before it's time for stuff to be performed on it.
	 */
	public function init(Request $request, Application $app)
	{
		$this->response = $app["outragedns.context"];
		
		if(isset($this->content))
			$this->response->content = $this->content;
		
		if(isset($this->form))
			$this->response->form = $this->form;
		
		$this->response->config = Configuration::getInstance();
		$this->response->godmode = false;
		
		/*
			if($this->request->session->current_users_id)
			{
				$this->response->user = new User\Content();
				$this->response->user->load($this->request->session->current_users_id);
				
				if($this->request->session->_global_admin_mode)
				{
					if($this->response->user->admin)
						$this->response->godmode = true;
					else
						$this->request->session->_global_admin_mode = 0;
				}
			}
			
			if($this->response->godmode)
				$this->response->users = User\Content::find()->where("active = 1")->order("id ASC")->get("objects");
			
			$this->request->session->_notification_messages = [];
		*/
		
		return true;
	}
}
