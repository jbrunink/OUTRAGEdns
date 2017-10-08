<?php


namespace OUTRAGEdns\Record;

use \OUTRAGEdns\Configuration\Configuration;
use \OUTRAGEdns\Validate\Constraint;
use \OUTRAGEdns\Validate\Constraint\FullyQualifiedDomainName;
use \OUTRAGEdns\Validate\Constraint\IPv4;
use \OUTRAGEdns\Validate\Constraint\IPv6;
use \OUTRAGEdns\Validate\Element;
use \OUTRAGEdns\Validate\ElementList;
use \OUTRAGEdns\ZoneTemplate\Form as ZoneTemplateForm;
use \OUTRAGElib\Validate\Transformer\StringModifier;


class Form extends ElementList
{
	/**
	 *	Define what fields we want this form to have.
	 */
	public function rules()
	{
		$configuration = Configuration::getInstance();
		
		# name
		$name = new Element("name");
		$name->setLabel("Record");
		$name->required(false);
		$name->appendTo($this);
		
		# type
		$type = new Element("type");
		$type->setLabel("Type");
		$type->contains($configuration->records->types->toArray());
		$type->required(true);
		$type->appendTo($this);
		
		# content
		$content = new Element("content");
		$content->setLabel("Content");
		$content->required(true);
		$content->appendTo($this);
		
		# content
		$ttl = new Element("ttl");
		$ttl->setLabel("TTL");
		$ttl->required(true);
		$ttl->appendTo($this);
		
		# content
		$prio = new Element("prio");
		$prio->setLabel("Priority");
		$prio->setDefault("0");
		$prio->required(false);
		$prio->appendTo($this);
	}
	
	
	/**
	 *	Called whenever pre-processing is to be done in the data to determine
	 *	how we need to validate things.
	 */
	public function prevalidate($input)
	{
		if(empty($input["type"]))
			return false;
		
		# since woot suggested that people should be lazy and not have
		# to type in their full domain name to make an entry, let's add a
		# suffix transformer, to make PowerDNS and woot happy.
		if($suffix = $this->getSuffix($input))
			$this->getElement("name")->addConstraint(new StringModifier($suffix, StringModifier::SUFFIX));
		
		# now choose what things we need to validate against
		$input["type"] = strtoupper($input["type"]);
		
		switch($input["type"])
		{
			case "SOA":
				$mname = new Element("mname");
				$mname->setLabel("Primary NS");
				$mname->required(true);
				$mname->appendTo($this);
				
				$rname = new Element("rname");
				$rname->setLabel("Contact");
				$rname->required(true);
				$rname->appendTo($this);
				
				$refresh = new Element("refresh");
				$refresh->setLabel("Refresh");
				$refresh->required(true);
				$refresh->appendTo($this);
				
				$refresh = new Element("serial");
				$refresh->setLabel("Serial");
				$refresh->required(true);
				$refresh->appendTo($this);
				
				$retry = new Element("retry");
				$retry->setLabel("Retry");
				$retry->required(true);
				$retry->appendTo($this);
				
				$expire = new Element("expire");
				$expire->setLabel("Expire");
				$expire->required(true);
				$expire->appendTo($this);
				
				$minimum = new Element("minimum");
				$minimum->setLabel("Minimum");
				$minimum->required(true);
				$minimum->appendTo($this);
				
				$this->getElement("content")->required(false);
			break;
			
			case "A":
				$this->getElement("content")->addConstraint(new IPv4());
			break;
			
			case "AAAA":
				$this->getElement("content")->addConstraint(new IPv6());
			break;
			
			case "CNAME":
				if($this instanceof ZoneTemplateForm == false)
					$this->getElement("content")->addConstraint(new FullyQualifiedDomainName());
			break;
		}
		
		return true;
	}
	
	
	/**
	 *	Returns the suffix to be used in form validation.
	 */
	public function getSuffix($input)
	{
		$suffix = "";
		
		if($this->root->passed && !empty($this->root->passed["name"]))
			$suffix = $this->root->passed["name"];
		elseif($this->root->content)
			$suffix = $this->root->content->name;
		
		if(strlen($input["name"]) > 0)
			$suffix = ".".$suffix;
		
		return $suffix;
	}
}