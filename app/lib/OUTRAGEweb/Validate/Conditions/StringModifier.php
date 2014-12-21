<?php
/**
 *	Validation condition for OUTRAGEweb: Password checking
 */


namespace OUTRAGEweb\Validate\Conditions;

use \OUTRAGEweb\Validate;


class StringModifier extends Validate\Condition implements Validate\Transformer
{
	/**
	 *	List of modes that this transformer supports.
	 */
	const REPLACE = "replace";
	const PREFIX = "prefix";
	const SUFFIX = "suffix";
	
	
	/**
	 *	What string will be helping us along here?
	 */
	protected $string = null;
	
	
	/**
	 *	What mode are we modifying in?
	 */
	protected $mode = null;
	
	
	/**
	 *	Called to set the arguments.
	 */
	public function arguments($string, $mode = self::REPLACE)
	{
		$this->string = $string;
		$this->mode = $mode;
	}
	
	
	/**
	 *	We're not really validating anything here, we're just transforming.
	 */
	public function validate($input)
	{
		return false;
	}
	
	
	/**
	 *	Transform the password into a nice little hash.
	 */
	public function transform($value)
	{
		switch($this->mode)
		{
			case self::REPLACE:
				return $this->string;
			
			case self::PREFIX:
				return $this->string.$value;
			
			case self::SUFFIX:
				return $value.$this->string;
		}
		
		return $value;
	}
}