<?php
/**
 *	Abstract for all form elements
 */


namespace OUTRAGEweb\FormElement;

use \OUTRAGEweb\Validate;


abstract class FormElementAbstract extends Validate\Element
{
	/**
	 *	Returns the element name - used in the template engine.
	 */
	public function getter_element_name()
	{
		return strtolower((new \ReflectionObject($this))->getShortName());
	}
}