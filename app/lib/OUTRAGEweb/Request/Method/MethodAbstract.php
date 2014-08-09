<?php
/**
 *	OUTRAGEweb framework
 *
 *	MethodAbstract - the abstraction that provides functionality
 *	for most of the HTTP method/verbs out there.
 */


namespace OUTRAGEweb\Request\Method;

use OUTRAGEweb\Construct;


abstract class MethodAbstract extends Construct\ObjectContainer
{
	/**
	 *	Called whenever the method is to be initialised.
	 */
	public function __construct($container = null)
	{
		if(isset($container))
			$this->populateContainerRecursively($container);
	}
}