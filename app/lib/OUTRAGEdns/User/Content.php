<?php
/**
 *	User model for OUTRAGEdns
 */


namespace OUTRAGEdns\User;

use OUTRAGEweb\Request;
use OUTRAGEdns\Entity;
use OUTRAGEdns\Domain;
use OUTRAGEdns\ZoneTemplate;


class Content extends Entity\Content
{
	/**
	 *	What zone templates does this user own?
	 */
	public function getter_templates()
	{
		if(!$this->id)
			return null;
		
		return ZoneTemplate\Content::find()->where("owner = ?", $this->id)->sort("id ASC")->invoke("objects");
	}
	
	
	/**
	 *	How many zone templates does this user own?
	 */
	public function getter_templates_no()
	{
		if(!$this->id)
			return 0;
		
		return ZoneTemplate\Content::find()->where("owner = ?", $this->id)->invoke("count");
	}
	
	
	/**
	 *	What domains does this user own?
	 */
	public function getter_domains()
	{
		if(!$this->id)
			return null;
		
		return Domain\Content::find()->where("owner = ?", $this->id)->sort("id ASC")->invoke("objects");
	}
	
	
	/**
	 *	How many domains does this user own?
	 */
	public function getter_domains_no()
	{
		if(!$this->id)
			return 0;
		
		return Domain\Content::find()->where("owner = ?", $this->id)->invoke("count");
	}
	
	
	/**
	 *	Called to save the user.
	 */
	public function save($post = array())
	{
		return parent::save($post);
	}
	
	
	/**
	 *	Called to edit the user.
	 */
	public function edit($post = array())
	{
		if(empty($post["password"]))
			unset($post["password"]);
		
		return parent::edit($post);
	}
	
	
	/**
	 *	Shall we authenticate this user?
	 */
	public function authenticate(Request\Environment $environment, $credentials)
	{
		if(!isset($credentials["username"]) || !isset($credentials["password"]))
			return false;
		
		$target = $this->find()
		               ->where("username LIKE ?", $credentials["username"])
		               ->where("password LIKE ?", $credentials["password"])
		               ->where("active = 1")
		               ->invoke("first");
		
		if(!$target)
			return false;
		
		$environment->session->reset();
		$environment->session->current_users_id = $target->id;
		
		return $this->load($target->id);
	}
	
	
	/**
	 *	Let's log this user out.
	 */
	public function logout(Request\Environment $environment = null)
	{
		if($environment)
			$environment->session->reset();
	}
}