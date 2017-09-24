<?php
/**
 *	Database connection class for OUTRAGEweb
 */


namespace OUTRAGEdns\Database\Binding\MySQL;

use \OUTRAGEdns\Entity\DelegatorTrait;
use \OUTRAGEweb\Database\Binding\MySQL\Connection as ConnectionParent;


class Connection extends ConnectionParent
{
	use DelegatorTrait;
}