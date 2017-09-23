<?php
/**
 *	Dynamic address model for OUTRAGEdns
 */


namespace OUTRAGEdns\DynamicAddress;

use \OUTRAGEdns\Validate\Element;
use \OUTRAGEdns\Validate\ElementList;


class Form extends ElementList
{
	/**
	 *	Define what fields we want this form to have.
	 */
	public function rules()
	{
		# name
		$name = new Element("name");
		$name->required(true);
		$name->appendTo($this);
		
		# token
		$token = new Element("token");
		$token->required(false);
		$token->appendTo($this);
		
		# records
		$records = new Element("records");
		$records->required(false);
		$records->setIsArray(true);
		$records->appendTo($this);
	}
}