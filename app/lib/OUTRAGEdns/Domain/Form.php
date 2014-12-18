<?php
/**
 *	Form for managing ZoneTemplates.
 */


namespace OUTRAGEdns\Domain;

use OUTRAGEweb\Configuration;
use OUTRAGEweb\FormElement;
use OUTRAGEweb\Validate;
use OUTRAGEdns\Record;


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
		$name->required(true);
		$name->appendTo($this);
		
		# type
		$type = new FormElement\Select("type");
		$type->required(true);
		$type->contains($config->records->synctypes->toArrayKeys());
		$type->appendTo($this);
		
		# zone template
		$zone_templ_id = new FormElement\Select("zone_templ_id");
		$zone_templ_id->required(false);
		$zone_templ_id->appendTo($this);
		
		# comments
		$comment = new FormElement\Textarea("comment");
		$comment->required(false);
		$comment->appendTo($this);
		
		# records
		$records = new Record\Form("records");
		$records->isArray(true);
		$records->appendTo($this);
	}
}