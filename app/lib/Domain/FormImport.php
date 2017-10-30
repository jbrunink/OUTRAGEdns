<?php


namespace OUTRAGEdns\Domain;

use \OUTRAGEdns\Validate\ElementList;
use \OUTRAGElib\Validate\BufferElement\FileBufferElement;


class FormImport extends ElementList
{
	/**
	 *	Define what fields we want this form to have.
	 */
	public function rules()
	{
		$upload = new FileBufferElement("upload");
		$upload->required(true);
		$upload->appendTo($this);
	}
}