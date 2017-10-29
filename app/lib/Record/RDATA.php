<?php


namespace OUTRAGEdns\Record;

use \Exception;
use \Symfony\Component\Yaml\Yaml;


class RDATA
{
	/**
	 *	Store RDATA
	 */
	protected static $rdata = [];
	
	
	/**
	 *	Store exclusions data
	 */
	protected static $exclusions = [
		"MX" => [
			"PREFERENCE" => "prio",
		],
		
		"SRV" => [
			"PRIORITY" => "prio",
		],
	];
	
	
	/**
	 *	Does this type exist?
	 */
	public static function has($type)
	{
		if(empty(self::$rdata))
		{
			if(file_exists(APP_DIR."/etc/RDATA/RDATA.yml"))
				$data = file_get_contents(APP_DIR."/etc/RDATA/RDATA.yml");
			
			self::$rdata = Yaml::parse($data);
		}
		
		return isset(self::$rdata[$type]);
	}
	
	
	/**
	 *	Retrieve RDATA
	 */
	public static function get($type)
	{
		if(self::has($type))
			return self::$rdata[$type];
		
		return null;
	}
	
	
	/**
	 *	Retrieves the mapping of exclusions to fields which are represented within PowerDNS
	 */
	public static function getExclusions($type)
	{
		if(isset(self::$exclusions[$type]))
			return self::$exclusions;
		
		return [];
	}
}