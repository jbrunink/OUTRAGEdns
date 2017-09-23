<?php
/**
 *	Form for managing ZoneTemplateRecords.
 */


namespace OUTRAGEdns\ZoneTemplateRecord;

use \OUTRAGEdns\Record\Form as RecordForm;


class Form extends RecordForm
{
	/**
	 *	What's the domain suffix?
	 */
	public function getSuffix($input)
	{
		return (strlen($input["name"]) > 0 ? "." : "")."[".Content::MARKER_ZONE."]";
	}
}