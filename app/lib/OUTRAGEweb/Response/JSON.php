<?php
/**
 *	Handler for dispatching JSON objects...
 */


namespace OUTRAGEweb\Response;


class JSON extends ResponseAbstract
{
	/**
	 *	Display the template that we're needing.
	 */
	public function display($template = null, array $arguments = [])
	{
		echo $this->render($template, $arguments);
		return true;
	}
	
	
	/**
	 *	Renders the template that we're needing.
	 */
	public function render($template = null, array $arguments = [])
	{
		$callback = $this->request->get->callback;
		
		return ($callback ? $callback."(" : "").json_encode(array_merge($this->toArray(), $arguments)).($callback ? ");" : "");
	}
}