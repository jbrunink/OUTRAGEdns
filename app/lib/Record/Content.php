<?php


namespace OUTRAGEdns\Record;

use \OUTRAGEdns\Entity;
use \OUTRAGEdns\Domain;


class Content extends Entity\Content
{
	/**
	 *	What domain does this record template belong to?
	 */
	public function getter_parent()
	{
		return Domain\Content::find()->where([ "id" => $this->domain_id ])->get("first");
	}
	
	
	/**
	 *	Returns the record name without the name of the parent record.
	 */
	public function getter_prefix()
	{
		return preg_replace("/\\.?".preg_quote($this->parent->name)."$/", "", $this->name);
	}
	
	
	/**
	 *	Called when saving a new record.
	 */
	public function save($post = array())
	{
		if(!isset($post["change_date"]))
			$post["change_date"] = time();
		
		if(array_key_exists("type", $post))
		{
	    	if($post["type"] === "SOA" && !isset($post["content"]))
    		{
    			$post["content"] = sprintf("%s %s %s %d %d %d %d", $post["mname"], $post["rname"], $post["serial"], $post["refresh"], $post["retry"], $post["expire"], $post["minimum"]);
    			
    			unset($post["mname"]);
    			unset($post["rname"]);
    			unset($post["refresh"]);
    			unset($post["retry"]);
    			unset($post["expire"]);
    			unset($post["minimum"]);
    		}
		}
		
		return parent::save($post);
	}
	
	
	/**
	 *	Called when editing an existing record.
	 */
	public function edit($post = array())
	{
		if(!isset($post["change_date"]))
			$post["change_date"] = time();
		
		if(array_key_exists("type", $post))
		{
    		if($post["type"] === "SOA" && !isset($post["content"]))
    		{
    			$post["content"] = sprintf("%s %s %s %d %d %d %d", $post["mname"], $post["rname"], $post["serial"], $post["refresh"], $post["retry"], $post["expire"], $post["minimum"]);
    			
    			unset($post["mname"]);
    			unset($post["rname"]);
    			unset($post["refresh"]);
    			unset($post["retry"]);
    			unset($post["expire"]);
    			unset($post["minimum"]);
    		}
		}
		
		return parent::edit($post);
	}
}