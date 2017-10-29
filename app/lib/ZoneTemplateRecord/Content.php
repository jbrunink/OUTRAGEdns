<?php


namespace OUTRAGEdns\ZoneTemplateRecord;

use \OUTRAGEdns\Entity;
use \OUTRAGEdns\ZoneTemplate;


class Content extends Entity\Content
{
	/**
	 *	Marker used to denote pseudo domains.
	 */
	const MARKER_ZONE = "ZONE";
	
	
	/**
	 *	Marker used to denote pseudo serial numbers.
	 */
	const MARKER_SERIAL = "SERIAL";
	
	
	/**
	 *	What template does this record template belong to?
	 */
	protected function getter_parent()
	{
		if(!$this->zone_templ_id)
			return null;
		
		return ZoneTemplate\Content::find()->where([ "id" => $this->zone_templ_id ])->get("first");
	}
	
	
	/**
	 *	Returns the record name without the name of the parent record - in this
	 *	case, the [ZONE] marker.
	 */
	protected function getter_prefix()
	{
		return preg_replace("/\\.?".preg_quote("[".self::MARKER_ZONE."]")."$/", "", $this->name);
	}
	
	
	/**
	 *	Called when saving a new record.
	 */
	public function save($post = array())
	{
		if($post["type"] === "SOA" && !isset($post["content"]))
		{
			$post["content"] = sprintf("%s %s %s %d %d %d %d", $post["mname"], $post["rname"], $post["serial"], $post["refresh"], $post["retry"], $post["expire"], $post["minimum"]);
			
			unset($post["mname"]);
			unset($post["rname"]);
			unset($post["refresh"]);
			unset($post["retry"]);
			unset($post["expire"]);
			unset($post["minimum"]);
		}
		
		return parent::save($post);
	}
	
	
	/**
	 *	Called when editing an existing record.
	 */
	public function edit($post = array())
	{
		if($post["type"] === "SOA" && !isset($post["content"]))
		{
			$post["content"] = sprintf("%s %s %s %d %d %d %d", $post["mname"], $post["rname"], $post["serial"], $post["refresh"], $post["retry"], $post["expire"], $post["minimum"]);
			
			unset($post["mname"]);
			unset($post["rname"]);
			unset($post["refresh"]);
			unset($post["retry"]);
			unset($post["expire"]);
			unset($post["minimum"]);
		}
		
		return parent::edit($post);
	}
}