<?php
/**
 *	Validation condition for OUTRAGEweb: Alias for date, however it has the
 *	added feature of being a transformer.
 */


namespace OUTRAGEweb\Validate\Conditions;

use \OUTRAGEweb\Validate;


class Time extends Date implements Validate\Transformer
{
	/**
	 *	Called to get the value of this date.
	 */
	public function transform($value)
	{
		if($this->result instanceof \DateTime)
			return $this->result->getTimestamp();
		
		return 0;
	}
}