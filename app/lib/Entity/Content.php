<?php
/**
 *	OUTRAGEdns specific stuff for content and models, etc.
 */


namespace OUTRAGEdns\Entity;

use \OUTRAGEdns\Configuration\Configuration;
use \OUTRAGEweb\Construct;
use \OUTRAGEweb\Entity;


class Content extends Entity\Content
{
	/**
	 *	Use custom delegator trait
	 */
	use DelegatorTrait;
	
	
	/**
	 *	Let's define some actions.
	 */
	public function getter_actions()
	{
		$actions = new Construct\ObjectContainer();
		$endpoint = $this->settings->route ?: $this->settings->type."s";
		
		foreach($this->settings->actions as $action => $info)
		{
			if(!empty($info->id) && empty($this->id))
				continue;
			
			$path = "/".$endpoint."/".$action."/";
			
			if(!empty($info->id))
				$path .= $this->id."/";
			
			$actions[$action] = $path;
		}
		
		return $actions;
	}
	
	
	/**
	 *	It would be good to log certain things.
	 */
	public function log($action, $state = null)
	{
		if(!$this->id)
			return false;
		
		$post = array
		(
			"content_type" => get_class($this),
			"content_id" => $this->id,
			"action" => $action,
			"state" => serialize($state),
			"the_date" => time(),
		);
		
		return $this->db->insert("logs", $post);
	}
}
