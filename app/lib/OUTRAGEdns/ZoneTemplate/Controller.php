<?php
/**
 *	ZoneTemplate model for OUTRAGEdns
 */


namespace OUTRAGEdns\ZoneTemplate;

use \OUTRAGEdns\Entity;


class Controller extends Entity\Controller
{
	/**
	 *	Called when we want to add a domain.
	 */
	public function add()
	{
		$form = new Form();
		
		$post = array
		(
			"name" => "test.westie.sh",
			"descr" => "Sample westie.sh zone template record",
		);
		
		var_dump($form->validate($post), $form->values());
		exit;
		
		return $this->response->display("index.twig");
	}
	
	
	/**
	 *	Called when we want to edit a domain.
	 */
	public function edit($id)
	{
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