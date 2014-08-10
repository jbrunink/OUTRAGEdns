<?php
/**
 *	Template for validation components in OUTRAGEweb.
 */


namespace OUTRAGEweb\Validate;


abstract class Condition
{
	/**
	 *	Store the error message in this variable, the validator
	 *	will pick this up.
	 */
	protected $error = null;
	
	
	/**
	 *	Use this method to deal with validating the input value.
	 *
	 *	Any return value is treated as boolean, however there is a slight cinch.
	 *	A false value denotes success, a true value denotes failure. Compare this
	 *	to the return values of a process in your operating system. $? anyone?
	 */
	abstract public function validate($input);
	
	
	/**
	 *	Method to allow the validator access to the error message.
	 */
	public function error()
	{
		return $this->error;
	}
	
	
	/**
	 *	Cleans the error log, ready for a fresh validation.
	 */
	public function clean()
	{
		$this->error = null;
		return $this;
	}
}