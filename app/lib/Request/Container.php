<?php


namespace OUTRAGEdns\Request;

use \OUTRAGElib\Structure\ObjectList;
use \OUTRAGElib\Structure\ObjectListDelegatorTrait;
use \OUTRAGElib\Structure\ObjectListPopulationTrait;
use \OUTRAGElib\Structure\ObjectListRetrievalTrait;


class Container extends ObjectList
{
	/**
	 *	Some traits to boost the functionality of ObjectList
	 */
	use ObjectListDelegatorTrait;
	use ObjectListPopulationTrait;
	use ObjectListRetrievalTrait;
}