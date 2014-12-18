<?php
/**
 *	URL class for OUTRAGEweb - analyses the submitted URL and does things
 *	with it.
 */


namespace OUTRAGEweb\Request\Method;


class URL extends MethodAbstract
{
	/**
	 *	Retrieves the protocol of this request.
	 */
	public function getter_protocol()
	{
		return parse_url($this->container[0], PHP_URL_SCHEME);
	}
	
	
	/**
	 *	Retrieves the domain this request is coming from.
	 */
	public function getter_domain()
	{
		return parse_url($this->container[0], PHP_URL_HOST);
	}
	
	
	/**
	 *	Retrieves the path this request is pointing to.
	 */
	public function getter_prefix()
	{
		return parse_url($this->container[0], PHP_URL_PATH);
	}
	
	
	/**
	 *	Gets the full path of this request.
	 */
	public function getter_path()
	{
		$url = $this->container;
		$url[0] = $this->prefix;
		
		if(empty($url[1]))
			return "/";
		
		$url = implode("/", $url);
		$url = str_replace("//", "/", $url)."/";
		
		return parse_url($url, PHP_URL_PATH);
	}
	
	
	/**
	 *	This allows us to offset the URL.
	 */
	public function setOffset($offset)
	{
		unset($this->prefix);
		
		$prefix = [];
		$offset += 1;
		
		do
		{
			$prefix[] = array_shift($this->container);
		}
		while(--$offset);
		
		array_unshift($this->container, implode("/", $prefix)."/");
		
		return $this;
	}
}