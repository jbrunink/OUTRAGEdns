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
		return ZoneTemplate\Content::find()->where("id = ?", $this->zone_templ_id)->invoke("first");
	}
}