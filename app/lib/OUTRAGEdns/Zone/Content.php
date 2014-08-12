<?php
/**
 *	Zone model for OUTRAGEdns
 */


namespace OUTRAGEdns\Zone;

use \OUTRAGEdns\Entity;
use \OUTRAGEdns\Record;
use \OUTRAGEdns\ZoneTemplate;


class Content extends Entity\Content
{
	/**
	 *	Returns the user that owns this object.
	 */
	public function getter_user()
	{
		if(!$this->owner)
			return null;
		
		return User\Content::find()->where("id = ?", $this->owner)->invoke("first");
	}
	
	
	/**
	 *	Chances are, there is a template associated with this zone.
	 *	We need this!
	 */
	public function getter_template()
	{
		if(!$this->zone_templ_id)
			return null;
		
		return ZoneTemplate\Content::find()->where("id = ?", $this->zone_templ_id)->invoke("first");
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