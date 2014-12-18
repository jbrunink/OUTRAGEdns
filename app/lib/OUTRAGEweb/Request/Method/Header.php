<?php
/**
 *	OUTRAGEweb framework
 *
 *	Header object - it's useful to have parsed headers!
 */


namespace OUTRAGEweb\Request\Method;


class Header extends MethodAbstract
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