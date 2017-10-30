<?php


namespace OUTRAGEdns\User;

use \OUTRAGEdns\Entity;
use \OUTRAGEdns\Domain;
use \OUTRAGEdns\ZoneTemplate;
use \Symfony\Component\HttpFoundation\Request;


class Content extends Entity\Content
{
	/**
	 *	What zone templates does this user own?
	 */
	protected function getter_templates()
	{
		if(!$this->id)
			return null;
		
		return ZoneTemplate\Content::find()->where([ "owner" => $this->id ])->order("id ASC")->get("objects");
	}
	
	
	/**
	 *	How many zone templates does this user own?
	 */
	protected function getter_templates_no()
	{
		if(!$this->id)
			return 0;
		
		return ZoneTemplate\Content::find()->where([ "owner" => $this->id ])->get("count");
	}
	
	
	/**
	 *	What domains does this user own?
	 */
	protected function getter_domains()
	{
		if(!$this->id)
			return null;
		
		return Domain\Content::find()->join("zones", "zones.domain_id = domains.id")->where([ "zones.owner" => $this->id ])->order("id ASC")->get("objects");
	}
	
	
	/**
	 *	How many domains does this user own?
	 */
	protected function getter_domains_no()
	{
		if(!$this->id)
			return 0;
		
		return Domain\Content::find()->join("zones", "zones.domain_id = domains.id")->where([ "zones.owner" => $this->id ])->get("count");
	}
	
	
	/**
	 *	Called to save the user.
	 */
	public function save($post = array())
	{
		if(!empty($post["password"]))
			$post["password"] = sha1($post["password"]);
		
		return parent::save($post);
	}
	
	
	/**
	 *	Called to edit the user.
	 */
	public function edit($post = array())
	{
		if(empty($post["password"]))
			unset($post["password"]);
		else
			$post["password"] = sha1($post["password"]);
		
		return parent::edit($post);
	}
	
	
	/**
	 *	Shall we authenticate this user?
	 */
	public function authenticate(Request $request, $credentials)
	{
		if(!isset($credentials["username"]) || !isset($credentials["password"]))
			return false;
		
		$target = $this->find()
		               ->where([ "username" => $credentials["username"] ])
		               ->where([ "password" => sha1($credentials["password"]) ])
		               ->where([ "active" => 1 ])
		               ->get("first");
		
		if(!$target)
			return false;
		
		$session = $request->getSession();
		
		$session->invalidate();
		$session->set("authenticated_users_id", $target->id);
		
		return $this->load($target->id);
	}
	
	
	/**
	 *	Let's log this user out.
	 */
	public function logout(Request $request)
	{
		$request->getSession()->invalidate();
		return true;
	}
}