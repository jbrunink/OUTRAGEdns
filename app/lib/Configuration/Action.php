<?php


namespace OUTRAGEdns\Configuration;

use \OUTRAGElib\Delegator\DelegatorTrait;
use \OUTRAGElib\Structure\ObjectList;
use \OUTRAGElib\Structure\ObjectListPopulationTrait;
use \OUTRAGElib\Structure\ObjectListRetrievalTrait;


class Action extends ObjectList
{
	/**
	 *	Some traits to boost the functionality of ObjectList
	 */
	use DelegatorTrait;
	use ObjectListPopulationTrait;
	use ObjectListRetrievalTrait;
}