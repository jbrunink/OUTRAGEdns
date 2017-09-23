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
			$result = array
			(
				"errors" => $this->getErrors() ?: false,
			);
			
			echo json_encode($result, JSON_PRETTY_PRINT);
			exit;
		}
		
		return $result;
	}
}