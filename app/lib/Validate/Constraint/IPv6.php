<?php
/**
 *	IPv6 check.
 *
 *	Shamelessly procured from the Poweradmin repo:
 *	- https://github.com/poweradmin/poweradmin/blob/master/inc/dns.inc.php
 */


namespace OUTRAGEdns\Validate\Constraint;

use \Exception;
use \OUTRAGElib\Validate\ConstraintAbstract;


class IPv6 extends ConstraintAbstract
{
	/**
	 *	Called to check to see what we've passed is correct.
	 */
	public function test($input)
	{
		if(!$input)
		{
			$this->error = "No valid IPv4 address supplied.";
			return false;
		}
		
		if(filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false)
		{
			$this->error = "The IPv4 address supplied is invalid.";
			return false;
		}
		
		return true;
	}
}