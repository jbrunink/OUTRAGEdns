<?php
/**
 *	User model for OUTRAGEdns
 */


namespace OUTRAGEdns\User;

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
		return ZoneTemplate\Content::find()->where("owner = ?", $this->id)->sort("id ASC")->invoke("objects");
	}
	
	
	/**
	 *	How many zone templates does this user own?
	 */
	public function getter_templates_no()
	{
		return ZoneTemplate\Content::find()->where("owner = ?", $this->id)->invoke("count");
	}
	
	
	/**
	 *	What domains does this user own?
	 */
	public function getter_domains()
	{
		return Domain\Content::find()->where("owner = ?", $this->id)->sort("id ASC")->invoke("objects");
	}
	
	
	/**
	 *	How many domains does this user own?
	 */
	public function getter_domains_no()
	{
		return Domain\Content::find()->where("owner = ?", $this->id)->invoke("count");
	}
	
	
	/**
	 *	Called to save the user.
	 */
	public function save($post)
	{
		return parent::save($post);
	}
	
	
	/**
	 *	Called to edit the user.
	 */
	public function edit($post)
	{
		if(empty($post["password"]))
			unset($post["password"]);
		
		return parent::edit($post);
	}
}