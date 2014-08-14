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
		
		if($this->request->session->current_users_id)
		{
			$this->response->user = new User\Content();
			$this->response->user->load($this->request->session->current_users_id);
		}
		
		$this->request->session->_notification_messages = [];
		return true;
	}
}