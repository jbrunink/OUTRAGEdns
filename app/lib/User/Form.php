<?php
/**
 *	Form for managing ZoneTemplates.
 */


namespace OUTRAGEdns\User;

use \OUTRAGEdns\Validate\Element;
use \OUTRAGEdns\Validate\ElementList;


class Form extends ElementList
{
	/**
	 *	Define what fields we want this form to have.
	 */
	public function rules()
	{
		# fullname
		$fullname = new Element("fullname");
		$fullname->required(true);
		$fullname->appendTo($this);
		
		# username
		$username = new Element("username");
		$username->required(true);
		$username->appendTo($this);
		
		# password
		$password = new Element("password");
		$password->required(false);
		$password->appendTo($this);
		
		# description
		$description = new Element("description");
		$description->required(false);
		$description->appendTo($this);
		
		# email
		$email = new Element("email");
		$email->required(true);
		$email->appendTo($this);
	}
	
	
	/**
	 *	Mutating the rules for adding users.
	 */
	public function rulesAdd()
	{
		$this->getElement("password")->required(true);
	}
	
	
	/**
	 *	Mutating the rules for editing users.
	 */
	public function rulesEdit()
	{
		$this->getElement("password")->required(false);
	}
	
	
	/**
	 *	Mutating the rules for the folks in godmode.
	 */
	public function rulesAdmin()
	{
		$admin = new Element("admin");
		$admin->required(false);
		$admin->appendTo($this);
		
		$active = new Element("active");
		$active->required(false);
		$active->appendTo($this);
	}
}