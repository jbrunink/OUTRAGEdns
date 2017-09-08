<?php
/**
 *	FQDN check.
 */


namespace OUTRAGEdns\Validate\Conditions;


class FullyQualifiedDomainName extends \OUTRAGEweb\Validate\Condition
{
	/**
	 *	Called to check to see what we've passed is correct.
	 */
	public function validate($input)
	{
		$input = preg_replace("/\.$/", "", $input);
		
		if(strlen($input) > 253)
			return $this->error = "FQDN is too long.";
		
		$labels = explode(".", $input);
		
		foreach($labels as $index => $label)
		{
			if(strlen($label) < 1 || strlen($label) > 63)
				return $this->error = "Invalid token length.";
			
			if(substr($label, 0, 1) == "-" || substr($label, -1, 1) == "-")
				return $this->error = "Invalid characters in FQDN.";
			
			if(!preg_match('/^[\w-\/]+$/', $label))
				return $this->error = "Invalid characters in FQDN.";
		}
		
		return false;
	}
}