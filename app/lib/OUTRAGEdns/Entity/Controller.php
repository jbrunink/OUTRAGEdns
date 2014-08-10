<?php
/**
 *	OUTRAGEdns specific stuff for content and models, etc.
 */


namespace OUTRAGEdns\Entity;


class Controller extends \OUTRAGEweb\Entity\Controller
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
		
		$this->response->config = \OUTRAGEweb\Configuration\Wallet::getInstance();
	}
}