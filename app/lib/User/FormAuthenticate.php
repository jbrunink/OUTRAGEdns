<?php
/**
 *	Form for managing ZoneTemplates.
 */


namespace OUTRAGEdns\User;

use \OUTRAGEdns\Validate\ElementList;
use \OUTRAGElib\Validate\Constraint\Required;


class FormAuthenticate extends ElementList
{
	/**
	 *	Define what fields we want this form to have.
	 */
	public function rules()
	{
		$this->build([
			"username" => [ new Required(true) ],
			"password" => [ new Required(true) ],
		]);
	}
}