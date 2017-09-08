<?php
/**
 *	Dynamic address model for OUTRAGEdns
 */


namespace OUTRAGEdns\DynamicAddress;

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
		
		# name
		$name = new FormElement\Text("name");
		$name->required(true);
		$name->appendTo($this);
		
		# token
		$token = new FormElement\Text("token");
		$token->required(false);
		$token->appendTo($this);
		
		# records
		$records = new FormElement\Text("records");
		$records->required(false);
		$records->isArray(true);
		$records->appendTo($this);
	}
}