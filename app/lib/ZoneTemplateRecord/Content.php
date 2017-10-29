<?php


namespace OUTRAGEdns\ZoneTemplateRecord;

use \OUTRAGEdns\Entity;
use \OUTRAGEdns\ZoneTemplate;
use \OUTRAGEdns\Record\Content as RecordContent;


class Content extends RecordContent
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
}