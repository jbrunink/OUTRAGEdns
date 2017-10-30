<?php


namespace OUTRAGEdns\Validate;

use \Exception;
use \OUTRAGElib\Validate\ElementList as ElementListParent;


class ElementList extends ElementListParent
{
	public function validate($input)
	{
		$result = parent::validate($input);
		
		# this fun thing is for ajax validation, weirdly enough
		if(!empty($_POST["::validate"]))
		{
			$output = array
			(
				"errors" => $this->getErrors() ?: false,
			);
			
			echo json_encode($output, JSON_PRETTY_PRINT);
			exit;
		}
		
		return $result;
	}
}