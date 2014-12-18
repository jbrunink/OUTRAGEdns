<?php
/**
 *	Message to store validation error results in.
 */


namespace OUTRAGEweb\Validate\Error;


class Message
{
	/**
	 *	What is the name of the offending item?
	 */
	public $name = null;
	
	
	/**
	 *	What context was it thrown in?
	 */
	public $context = null;
	
	
	/**
	 *	What was thrown?
	 */
	public $message = null;
}