<?php
/**
 *	Transformer interface for transforming element values
 *	on validators in OUTRAGEweb.
 */


namespace OUTRAGEweb\Validate;


interface Transformer
{
	/**
	 *	Called to transform a value into something that we're wanting.
	 *
	 *	There are two things to note about this - one is that this performs a destructive
	 *	change that affects the conditions and transformers further down the chain, and
	 *	that even if there have been errors flagged up, a call to transform will always
	 *	be called.
	 *
	 *	An idea is to check if $this->error has been populated - that's how I'm doing
	 *	all of the validators anyway.
	 *
	 *	$value will always be passed - but you don't always need to use it. ;)
	 */
	public function transform($value);
}