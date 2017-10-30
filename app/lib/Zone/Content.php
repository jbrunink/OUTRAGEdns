<?php


namespace OUTRAGEdns\Zone;

use \OUTRAGEdns\Entity;
use \OUTRAGEdns\Record;
use \OUTRAGEdns\User;
use \OUTRAGEdns\ZoneTemplate;


class Content extends Entity\Content
{
	/**
	 *	Returns the user that owns this object.
	 */
	protected function getter_user()
	{
		if(!$this->owner)
			return null;
		
		return User\Content::find()->where([ "id" => $this->owner ])->get("first");
	}
	
	
	/**
	 *	Chances are, there is a template associated with this zone.
	 *	We need this!
	 */
	protected function getter_template()
	{
		if(!$this->zone_templ_id)
			return null;
		
		return ZoneTemplate\Content::find()->where([ "id" => $this->zone_templ_id ])->get("first");
	}
	
	
	/**
	 *	Called when saving a new zone template.
	 */
	public function save($post = array())
	{
		if(!empty($post["owner"]) && is_object($post["owner"]))
			$post["owner"] = $post["owner"]->id;
		
		return parent::save($post);
	}
	
	
	/**
	 *	Called when editing a zone template.
	 */
	public function edit($post = array())
	{
		if(!empty($post["owner"]) && is_object($post["owner"]))
			$post["owner"] = $post["owner"]->id;
		
		return parent::edit($post);
	}
}