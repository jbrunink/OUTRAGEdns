<?php
/**
 *	ZoneTemplateRecord model for OUTRAGEdns
 */


namespace OUTRAGEdns\ZoneTemplateRecord;

use \OUTRAGEdns\Entity;
use \OUTRAGEdns\ZoneTemplate;


class Content extends Entity\Content
{
	/**
	 *	What template does this record template belong to?
	 */
	public function getter_parent()
	{
		$request = (new ZoneTemplate\Content())->find();
		$request->where("id = ?", $this->zone_templ_id);
		
		return $request->invoke("first");
	}
}