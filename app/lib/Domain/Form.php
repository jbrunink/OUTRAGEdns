<?php
/**
 *	Form for managing ZoneTemplates.
 */


namespace OUTRAGEdns\Domain;

use \OUTRAGEdns\Record\Form as RecordForm; 
use \OUTRAGEdns\Validate\Element;
use \OUTRAGEdns\Validate\ElementList;
use \OUTRAGEweb\Configuration;


class Form extends ElementList
{
	/**
	 *	Define what fields we want this form to have.
	 */
	public function rules()
	{
		$config = Configuration\Wallet::getInstance();
		
		# name
		$name = new Element("name");
		$name->required(true);
		$name->appendTo($this);
		
		# type
		$type = new Element("type");
		$type->required(true);
		$type->contains($config->records->synctypes->toArrayKeys());
		$type->appendTo($this);
		
		# zone template
		$zone_templ_id = new Element("zone_templ_id");
		$zone_templ_id->required(false);
		$zone_templ_id->appendTo($this);
		
		# comments
		$comment = new Element("comment");
		$comment->required(false);
		$comment->appendTo($this);
		
		# records
		$records = new RecordForm("records");
		$records->setIsArray(true);
		$records->appendTo($this);
	}
}