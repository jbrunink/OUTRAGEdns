<?php
/**
 *	Form for managing Record.
 */


namespace OUTRAGEdns\Record;

use OUTRAGEweb\Configuration;
use OUTRAGEweb\FormElement;
use OUTRAGEweb\Validate;
use OUTRAGEdns\Validate\Conditions as Constraint;


class Form extends Validate\Template
{
	/**
	 *	Define what fields we want this form to have.
	 */
	public function rules()
	{
		parent::rules();
		
		$config = Configuration\Wallet::getInstance();
		
		# name
		$name = new FormElement\Text("name");
		$name->label("Record");
		$name->required(true);
		$name->appendTo($this);
		
		# type
		$type = new FormElement\Text("type");
		$type->label("Type");
		$type->contains($config->records->types->toArray());
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
		$prio->required(false);
		$prio->appendTo($this);
	}
	
	
	public function prevalidate($input)
	{
		if(empty($input["type"]))
			return false;
		
		switch($input["type"])
		{
			case "A":
				$this->getElement("content")->addCondition(new Constraint\IPv4());
			break;
			
			case "AAAA":
				$this->getElement("content")->addCondition(new Constraint\IPv6());
			break;
		}
		
		return true;
	}
}