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
	}
}