<?php
/**
 *	Form for managing ZoneTemplateRecords.
 */


namespace OUTRAGEdns\ZoneTemplateRecord;

use OUTRAGEweb\Validate;
use OUTRAGEdns\Record;

class Form extends Record\Form
{
	/**
	 *	What's the domain suffix?
	 */
	public function getSuffix($input)
	{
		return (strlen($input["name"]) > 0 ? "." : "")."[".Content::MARKER_ZONE."]";
	}
}