<?php
/**
 *	IPv4 check.
 *
 *	Shamelessly procured from the Poweradmin repo:
 *	- https://github.com/poweradmin/poweradmin/blob/master/inc/dns.inc.php
 */


namespace OUTRAGEdns\Validate\Conditions;


class IPv4 extends \OUTRAGEweb\Validate\Condition
{
	/**
	 *	Called to check to see what we've passed is correct.
	 */
	public function validate($input)
	{
		if(!$input)
			return $this->error = "No valid IPv4 address supplied.";
		
		if(filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false)
			return $this->error = "The IPv4 address supplied is invalid.";
		
		return false;
	}
}