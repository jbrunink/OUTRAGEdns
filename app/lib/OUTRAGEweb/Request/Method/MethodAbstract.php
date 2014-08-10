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
	 *	We can use this constant to determine whether setter calls populate the
	 *	object directly or should just be stuck in any container, if one exists.
	 */
	const DELEGATOR_SET_UNKNOWN_INTO_CONTAINER = false;
	
	
	/**
	 *	Called whenever the method is to be initialised.
	 */
	public function __construct($container = null)
	{
		if(isset($container))
			$this->populateContainerRecursively($container);
	}
}