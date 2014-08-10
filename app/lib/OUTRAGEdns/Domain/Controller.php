<?php
/**
 *	Domain model for OUTRAGEdns
 */


namespace OUTRAGEdns\Domain;

use \OUTRAGEdns\Entity;


class Controller extends Entity\Controller
{
	/**
	 *	Called when we want to add a domain.
	 */
	public function add()
	{
		var_dump($this->content, $this->request);
		exit;
	}
	
	
	/**
	 *	Called when we want to edit a domain.
	 */
	public function edit($id)
	{
		var_dump($this->content, $this->request, $id);
		exit;
	}
	
	
	/**
	 *	Called when we want to remove a domain.
	 */
	public function remove($id)
	{
		var_dump($this->content, $this->request, $id);
		exit;
	}
	
	
	/**
	 *	Called when we want show the grid view.
	 */
	public function grid()
	{
		var_dump($this->content, $this->request);
		exit;
	}
}