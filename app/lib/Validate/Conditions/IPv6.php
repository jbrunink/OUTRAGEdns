<?php
/**
 *	IPv6 check.
 *
 *	Shamelessly procured from the Poweradmin repo:
 *	- https://github.com/poweradmin/poweradmin/blob/master/inc/dns.inc.php
 */


namespace OUTRAGEdns\Validate\Conditions;


class IPv6 extends \OUTRAGEweb\Validate\Condition
{
	/**
	 *	Called to check to see what we've passed is correct.
	 */
	public function validate($input)
	{
		if(!$input)
			return $this->error = "No valid IPv6 address supplied.";
		
		if(filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false)
			return $this->error = "The IPv6 address supplied is invalid.";
		
		return false;
	}
}