<?php


namespace OUTRAGEdns\Record;

use \OUTRAGEdns\Domain;
use \OUTRAGEdns\Entity;


class Content extends Entity\Content
{
	/**
	 *	What domain does this record template belong to?
	 */
	protected function getter_parent()
	{
		return Domain\Content::find()->where([ "id" => $this->domain_id ])->get("first");
	}
	
	
	/**
	 *	Returns the record name without the name of the parent record.
	 */
	protected function getter_prefix()
	{
		return preg_replace("/\\.?".preg_quote($this->parent->name)."$/", "", $this->name);
	}
	
	
	/**
	 *	Load RDATA into this object
	 */
	protected function getter_rdata()
	{
		$this->rdata = [];
		
		if($keys = RDATA::get($this->type))
		{
			# if the last item of the keys list is (bool) true, then we need to
			# translate that into '@RDATA' - this isn't something official but
			# just a way to ensure that things specifically are not named in the
			# spec, such as NULL records, are properly catered for
			if(($index = array_search(true, $keys, true)) !== false)
			{
				if($index == count($keys) - 1)
					$keys[$index] = "@RDATA";
			}
			
			# explode our tokens
			$tokens = preg_split("/\s+/", $this->content, count($keys));
			
			# put some tokens in the right place, thanks PowerDNS
			$exclusions = RDATA::getExclusions($this->type);
			
			if(count($exclusions) > 0)
			{
				foreach($exclusions as $rkey => $field)
				{
					$index = array_search($rkey, $keys);
					
					if($index !== -1)
						array_splice($tokens, $index, 0, [ $this->{$field} ]);
				}
			}
			
			if(count($keys) == count($tokens))
			{
				$this->rdata = array_combine($keys, $tokens);
				
				if($this->type == "TXT")
				{
					if(substr($this->rdata["TXT-DATA"], 0, 1) == '"' && substr($this->rdata["TXT-DATA"], -1, 1) == '"')
						$this->rdata["TXT-DATA"] = substr($this->rdata["TXT-DATA"], 1, -1);
				}
			}
		}
		
		return $this->rdata;
	}
	
	
	/**
	 *	Called when saving a new record.
	 */
	public function save($post = array())
	{
		if(in_array("change_date", $this->db_fields))
		{
			if(!isset($post["change_date"]))
				$post["change_date"] = time();
		}
		
		# if content has not been set, rely on RDATA to populate the content field!
		# be aware of the MX/SRV record gotchas!
		if(!isset($post["content"]))
			$this->prepareContentField($post);
		
		return parent::save($post);
	}
	
	
	/**
	 *	Called when editing an existing record.
	 */
	public function edit($post = array())
	{	
		if(in_array("change_date", $this->db_fields))
		{
			if(!isset($post["change_date"]))
				$post["change_date"] = time();
		}
		
		# if content has not been set, rely on RDATA to populate the content field!
		# be aware of the MX/SRV record gotchas!
		if(!isset($post["content"]))
			$this->prepareContentField($post);
		
		return parent::edit($post);
	}
	
	
	/**
	 *	Modify the data to be inserted/modified by playing about with content
	 */
	protected function prepareContentField(&$post)
	{
		$rdata = RDATA::get($post["type"]);
		$exclusions = RDATA::getExclusions($post["type"]);
		
		$list = [];
		
		foreach($rdata as $rkey)
		{
			if(isset($exclusions[$rkey]))
				continue;
			
			$key = strtolower($rkey);
			
			if(isset($post[$key]))
				$list[] = $post[$key];
			
			unset($post[$key]);
		}
		
		if(count($list) > 0)
			$post["content"] = implode(" ", $list);
		
		return true;
	}
}