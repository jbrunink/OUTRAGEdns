<?php


namespace OUTRAGEdns\Configuration;

use \OUTRAGElib\Structure\ObjectList;
use \OUTRAGElib\Structure\ObjectListMagicMethodTrait;
use \OUTRAGElib\Structure\ObjectListPopulationTrait;
use \OUTRAGElib\Structure\ObjectListRetrievalTrait;
use \Symfony\Component\Yaml\Yaml;


class Configuration extends ObjectList
{
	/**
	 *	Some traits to boost the functionality of ObjectList
	 */
	use ObjectListMagicMethodTrait;
	use ObjectListPopulationTrait;
	use ObjectListRetrievalTrait;
	
	
	/**
	 *	Singleton
	 */
	public static function getInstance()
	{
		static $instance = null;
		
		if(is_null($instance))
		{
			# retrieve paths
			$paths = [];
			
			$paths = array_merge($paths, glob(APP_DIR."/etc/config/*.yaml"));
			$paths = array_merge($paths, glob(APP_DIR."/etc/config/entities/*.yaml"));
			
			$instance = new self();
			
			# now turn paths into data
			$array = [];
			
			foreach($paths as $path)
				$array = array_merge_recursive($array, Yaml::parse(file_get_contents($path)));
			
			$instance->populateObjectList($array);
		}
		
		return $instance;
	}
}