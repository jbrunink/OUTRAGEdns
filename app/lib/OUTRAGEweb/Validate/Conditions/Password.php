<?php
/**
 *	Validation condition for OUTRAGEweb: Password checking
 */


namespace OUTRAGEweb\Validate\Conditions;

use \OUTRAGEweb\Validate;


class Password extends Validate\Condition implements Validate\Transformer
{
	/**
	 *	We'll want to save some key data here...
	 */
	protected $required = null;
	
	
	/**
	 *	Called to set the arguments.
	 */
	public function arguments($required = true)
	{
		$this->required = (boolean) $required;
	}
	
	
	/**
	 *	We need to check that this is a valid password.
	 *
	 *	@todo: implement dictionary checking!
	 */
	public function validate($input)
	{
		if($this->required)
		{
			$input = trim($input);
			
			if(!$input)
				return $this->error = "You need to provide a password.";
			
			if(strlen($input) < 4)
				return $this->error = "Password is too short.";
		}
		
		return false;
	}
	
	
	/**
	 *	Transform the password into a nice little hash.
	 */
	public function transform($value)
	{
		if($this->required || !empty($value))
			return hash("sha384", trim($value));
		
		return null;
	}
}