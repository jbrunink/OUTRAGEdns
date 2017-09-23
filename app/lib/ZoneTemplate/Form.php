<?php
/**
 *	Form for managing ZoneTemplates.
 */


namespace OUTRAGEdns\ZoneTemplate;

use \OUTRAGEdns\Validate\Element;
use \OUTRAGEdns\Validate\ElementList;
use \OUTRAGEdns\ZoneTemplateRecord\Form as ZoneTemplateRecordForm; 
use \OUTRAGEweb\Configuration;


class Form extends ElementList
{
	/**
	 *	Define what fields we want this form to have.
	 */
	public function rules()
	{
		# name
		$name = new Element("name");
		$name->required(true);
		$name->appendTo($this);
		
		# comments
		$comment = new Element("comment");
		$comment->required(false);
		$comment->appendTo($this);
		
		# records
		$records = new ZoneTemplateRecordForm("records");
		$records->setIsArray(true);
		$records->appendTo($this);
	}
}