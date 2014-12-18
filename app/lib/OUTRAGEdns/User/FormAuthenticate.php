<?php
/**
 *	Form for managing ZoneTemplates.
 */


namespace OUTRAGEdns\User;

use OUTRAGEweb\FormElement;
use OUTRAGEweb\Validate;


class FormAuthenticate extends Validate\Template
{
	/**
	 *	Define what fields we want this form to have.
	 */
	public function rules()
	{
		parent::rules();
		
		# username
		$username = new FormElement\Text("username");
		$username->required(true);
		$username->appendTo($this);
		
		# password
		$password = new FormElement\Text("password");
		$password->password(true);
		$password->required(true);
		$password->appendTo($this);
	}
}