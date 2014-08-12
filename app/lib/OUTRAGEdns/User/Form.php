<?php
/**
 *	Form for managing ZoneTemplates.
 */


namespace OUTRAGEdns\User;

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
		
		# fullname
		$fullname = new FormElement\Text("fullname");
		$fullname->required(true);
		$fullname->appendTo($this);
		
		# username
		$username = new FormElement\Text("username");
		$username->required(true);
		$username->appendTo($this);
		
		# password
		$password = new FormElement\Text("password");
		$password->password(false);
		$password->required(false);
		$password->appendTo($this);
		
		# email
		$email = new FormElement\Text("email");
		$email->required(true);
		$email->appendTo($this);
	}
	
	
	/**
	 *	Mutating the rules for adding users.
	 */
	public function rulesAdd()
	{
		$this->getElement("password")->required(true)->password(true);
	}
	
	
	/**
	 *	Mutating the rules for editing users.
	 */
	public function rulesEdit()
	{
		$this->getElement("password")->required(false)->password(false);
	}
}