<?php
/**
 *	Form for managing ZoneTemplates.
 */


namespace OUTRAGEdns\ZoneTemplate;

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
		
		# fieldset for info
		$info = new FormElement\Fieldset();
		$info->label("Template information");
		$info->appendTo($this);
		
		# name
		$name = new FormElement\Text("name");
		$name->label("Name");
		$name->required(true);
		$name->appendTo($info);
		
		# description
		$descr = new FormElement\Text("descr");
		$descr->label("Name");
		$descr->required(true);
		$descr->appendTo($info);
		
		$records = new FormElement\Fieldset();
		$records->name("records");
		$records->label("Manage records");
		$records->isArray(true);
		$records->template(new ZoneTemplateRecord\Form());
		$records->appendTo($this);
	}
}