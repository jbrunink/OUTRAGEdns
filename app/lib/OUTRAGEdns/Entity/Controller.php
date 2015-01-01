<?php
/**
 *	OUTRAGEdns specific stuff for content and models, etc.
 */


namespace OUTRAGEdns\Entity;

use \OUTRAGEweb\Entity;
use \OUTRAGEweb\Configuration;
use \OUTRAGEdns\User;


class Controller extends Entity\Controller
{
	/**
	 *	This method is called before the path is executed - this can be used to prepare
	 *	stuff like content before it's time for stuff to be performed on it.
	 */
	public function init()
	{
		if($this->content)
			$this->response->content = $this->content;
		
		if($this->form)
			$this->response->form = $this->form;
		
		$this->response->config = Configuration\Wallet::getInstance();
		$this->response->godmode = false;
		
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
			$this->response->users = User\Content::find()->where("active = 1")->order("id ASC")->invoke("objects");
		
		$this->request->session->_notification_messages = [];
		return true;
	}
}