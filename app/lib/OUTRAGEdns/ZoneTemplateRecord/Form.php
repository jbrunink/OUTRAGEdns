<?php
/**
 *	Form for managing ZoneTemplateRecords.
 */


namespace OUTRAGEdns\ZoneTemplateRecord;

use OUTRAGEweb\FormElement;
use OUTRAGEweb\Validate;


class Form extends Validate\Template
{
	/**
	 *	Define what fields we want this form to have.
	 */
	public function rules()
	{
		parent::rules();
		
		# name
		$name = new FormElement\Text("name");
		$name->label("Record");
		$name->required(true);
		$name->appendTo($this);
		
		# type
		$type = new FormElement\Text("type");
		$type->label("Type");
		$type->required(true);
		$type->appendTo($this);
		
		# content
		$content = new FormElement\Text("content");
		$content->label("Content");
		$content->required(true);
		$content->appendTo($this);
		
		# content
		$ttl = new FormElement\Text("ttl");
		$ttl->label("TTL");
		$ttl->required(true);
		$ttl->appendTo($this);
		
		# content
		$prio = new FormElement\Text("prio");
		$prio->label("Priority");
		$prio->required(true);
		$prio->appendTo($this);
	}
}