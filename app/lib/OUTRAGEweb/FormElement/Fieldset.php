<?php
/**
 *	This is a fieldset.
 */


namespace OUTRAGEweb\FormElement;

use \OUTRAGEweb\Validate;


class Fieldset extends Validate\Template
{
	/**
	 *	Returns the element name - used in the template engine.
	 */
	public function getter_element_name()
	{
		return strtolower((new \ReflectionObject($this))->getShortName());
	}
}