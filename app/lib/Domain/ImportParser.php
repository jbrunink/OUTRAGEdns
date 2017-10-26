<?php


namespace OUTRAGEdns\Domain;

use \LTDBeget\dns\configurator\Zone;
use \OUTRAGEdns\Record\Form as RecordForm;
use \OUTRAGElib\FileStream\File;
use \OUTRAGElib\FileStream\Stream;
use \SimpleXMLElement;


class ImportParser
{
	/**
	 *	What records have been generated?
	 */
	public $records = [];
	
	
	/**
	 *	Parse our input stream
	 */
	public function parse(File $file)
	{
		switch($file->getClientMediaType())
		{
			case "text/xml":
				return $this->records = $this->parseXML($file);
			break;
			
			case "application/json":
				return $this->records = $this->parseJSON($file);
			break;
			
			default:
				return $this->records = $this->parseZoneFile($file);
			break;
		}
		
		return false;
	}
	
	
	/**
	 *	Parse XML import
	 */
	protected function parseXML(File $file)
	{
		$feed = simplexml_load_string($file->getStream()->getContents());
		$valid = [];
		
		foreach($feed->record as $row)
		{
			$record = (array) $row;
			
			foreach($record as $key => $value)
			{
				if($value instanceof SimpleXMLElement)
					$record["name"] = (string) $value;
			}
			
			$form = new RecordForm();
			
			if($form->validate($record))
				$valid[] = $form->getValues();
		}
		
		return $valid;
	}
	
	
	/**
	 *	Parse JSON import
	 */
	protected function parseJSON(File $file)
	{
		$feed = json_decode($file->getStream()->getContents(), true);
		$valid = [];
		
		if(is_array($feed))
		{
			foreach($feed["records"] as $record)
			{
				$form = new RecordForm();
				
				if($form->validate($record))
					$valid[] = $form->getValues();
			}
		}
		
		return $valid;
	}
	
	
	/**
	 *	Parse Zone file
	 */
	protected function parseZoneFile(File $file)
	{
		var_dump($file);
		exit;
	}
}