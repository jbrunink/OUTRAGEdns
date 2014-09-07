<?php
/**
 *	ErrorMessage interface for accepting error messages in OUTRAGEweb.
 */


namespace OUTRAGEweb\Validate\Error;


interface MessageInterface
{
	/**
	 *	Used to log an error with a particular item.
	 */
	public function error(\OUTRAGEweb\Validate\Component $context, $message = "");
}