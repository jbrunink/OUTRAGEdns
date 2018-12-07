<?php
/**
 *	FQDN check.
 */


namespace OUTRAGEdns\Validate\Constraint;

use \Exception;
use \OUTRAGElib\Validate\ConstraintAbstract;


class FullyQualifiedDomainName extends ConstraintAbstract
{
	/**
	 *	Called to check to see what we've passed is correct.
	 */
	public function test($input)
	{
		$input = preg_replace("/\.$/", "", $input);
		
		if(strlen($input) > 253)
		{
			$this->error = "FQDN is too long.";
			return false;
		}
		
		$labels = explode(".", $input);
		
		foreach($labels as $index => $label)
		{
			if(strlen($label) < 1 || strlen($label) > 63)
			{
				$this->error = "Invalid token length.";
				return false;
			}
			
			if(substr($label, 0, 1) == "-" || substr($label, -1, 1) == "-")
			{
				$this->error = "Invalid characters in FQDN.";
				return false;
			}
			
			if(!preg_match('/^[\w-]+$/', $label))
			{
				$this->error = "Invalid characters in FQDN.";
				return false;
			}
		}
		
		return true;
	}
}
