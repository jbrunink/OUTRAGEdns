<?php
/**
 *	Form for managing ZoneTemplates.
 */


namespace OUTRAGEdns\ZoneTemplate;

use OUTRAGEweb\Configuration;
use OUTRAGEweb\FormElement;
use OUTRAGEweb\Validate;
use OUTRAGEdns\ZoneTemplateRecord;


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
		
		# comments
		$comment = new FormElement\Textarea("comment");
		$comment->required(false);
		$comment->appendTo($this);
		
		# records
		$records = new ZoneTemplateRecord\Form("records");
		$records->isArray(true);
		$records->appendTo($this);
	}
}