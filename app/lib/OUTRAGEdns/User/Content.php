<?php
/**
 *	User model for OUTRAGEdns
 */


namespace OUTRAGEdns\User;

use OUTRAGEdns\Entity;


class Content extends Entity\Content
{
	/**
	 *	Called to save the user.
	 */
	public function save($post)
	{
		$password_hash = sha1($post["password"]);
		
		if(!empty($post["password"]) && strcmp($this["password"], $password_hash) != 0)
			$post["password"] = $password_hash;
		
		return parent::save($post);
	}
	
	
	/**
	 *	Called to edit the user.
	 */
	public function edit($post)
	{
		$password_hash = sha1($post["password"]);
		
		if(!empty($post["password"]) && strcmp($this["password"], $password_hash) != 0)
			$post["password"] = $password_hash;
		
		return parent::edit($post);
	}
}