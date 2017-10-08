<?php


namespace OUTRAGEdns\ZoneTemplateRecord;

use \OUTRAGEdns\Entity;


class Controller extends Entity\Controller
{
	/**
	 *	Return a blank copy of the grid item that sits in the
	 *	zone template record table.
	 */
	public function griditem()
	{
		return $this->response->display("objects/zonetemplaterecord/grid-item.twig");
	}
}