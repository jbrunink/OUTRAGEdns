<?php


namespace OUTRAGEdns\Domain;

use \LTDBeget\dns\Tokenizer as ZoneTokeniser;
use \OUTRAGEdns\Record\Form as RecordForm;
use \OUTRAGEdns\Domain\Content as DomainContent;
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
	 *	What domain are we referencing?
	 */
	public $domain = null;
	
	
	/**
	 *	Constructor
	 */
	public function __construct(DomainContent $domain = null)
	{
		$this->domain = $domain;
	}
	
	
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
			
			if($this->domain)
				$form->content = $this->domain;
			
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
				
				if($this->domain)
					$form->content = $this->domain;
				
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
		$feed = $file->getStream()->getContents();
		$valid = [];
		
		# something to bear in mind folks, this library does not really like having
		# Windows line endings, so, this will need to be replaced in order to use it!
		# @todo: contemplate writing own DNS parser via https://doc.nette.org/en/2.4/tokenizer
		$feed = str_replace("\r\n", "\n", $feed);
		
		$records = ZoneTokeniser::tokenize($feed, [ "relativeToOrigin" => true ]);
		
		if(count($records) > 0)
		{
			foreach($records as $record)
			{
				$row = [];
				
				if(!empty($record["NAME"]))
				{
					if($record["NAME"] == "@")
						$row["name"] = "";
					else
						$row["name"] = $record["NAME"];
				}
				
				if(isset($record["TYPE"]))
					$row["type"] = $record["TYPE"];
				
				if(isset($record["TTL"]))
					$row["ttl"] = $record["TTL"];
				
				if(isset($record["RDATA"]) && isset($record["RDATA"]["PREFERENCE"]))
				{
					$row["prio"] = $record["RDATA"]["PREFERENCE"];
					
					unset($record["RDATA"]["PREFERENCE"]);
				}
				
				if(isset($record["RDATA"]))
				{
					if($row["type"] == "TXT")
						$row["content"] = "\"".implode(" ", $record["RDATA"])."\"";
					else
						$row["content"] = implode(" ", $record["RDATA"]);
				}
				
				# fixing stuff for PowerDNS imports
				switch($record["TYPE"])
				{
					case "MX":
					case "SRV":
					case "NS":
					case "CNAME":
						$row["content"] = rtrim($row["content"], ".");
					break;
					
					case "SOA":
						$chunks = explode(" ", $row["content"]);
						
						$chunks[0] = rtrim($chunks[0], ".");
						$chunks[1] = rtrim($chunks[1], ".");
						
						$row["content"] = implode(" ", $chunks);
					break;
				}
				
				$form = new RecordForm();
				
				if($this->domain)
					$form->content = $this->domain;
				
				if($form->validate($row))
					$valid[] = $form->getValues();
			}
		}
		
		return $valid;
	}
}