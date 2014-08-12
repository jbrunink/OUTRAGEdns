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
		if(!$this->zone_templ_id)
			return null;
		
		return ZoneTemplate\Content::find()->where("id = ?", $this->zone_templ_id)->invoke("first");
	}
}