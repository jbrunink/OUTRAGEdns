<?php


namespace OUTRAGEdns\Entity;

use \OUTRAGEdns\Configuration\Configuration;
use \OUTRAGEdns\User;
use \OUTRAGElib\Delegator\DelegatorTrait;
use \Silex\Application;
use \Symfony\Component\HttpFoundation\Request;


class Controller
{
	/**
	 *	What is the application?
	 */
	protected $application = null;
	
	
	/**
	 *	What is the request?
	 */
	protected $request = null;
	
	
	/**
	 *	What is the response?
	 */
	protected $response = null;
	
	
	/**
	 *	Use custom delegator trait
	 */
	use DelegatorTrait;
	
	
	/**
	 *	Use custom delegator trait
	 */
	use EntityDelegatorTrait;
	
	
	/**
	 *	Use custom delegator trait
	 */
	use ControllerDelegatorTrait;
	
	
	/**
	 *	This method is called before the path is executed - this can be used to prepare
	 *	stuff like content before it's time for stuff to be performed on it.
	 */
	public function init(Request $request, Application $app)
	{
		$this->application = $app;
		$this->request = $request;
		
		# at the moment there's no way within symfony to do this sort of thing
		# sadly - hurrah for custom functionality
		$this->request->url = $this->getRequestURL($this->request);
		
		# response is our umbrella variable
		$this->response = $app["outragedns.context"];
		
		$this->response->fullwidth = false;
		$this->response->request = $this->request;
		
		if($this->content)
			$this->response->content = $this->content;
		
		if($this->form)
			$this->response->form = $this->form;
		
		$this->response->config = Configuration::getInstance();
		$this->response->godmode = false;
		
		# is our user logged in?
		$session = $this->request->getSession();
		
		if($session->has("authenticated_users_id"))
		{
			$this->response->user = new User\Content();
			$this->response->user->load($session->get("authenticated_users_id"));
			
			if($session->has("_global_admin_mode"))
			{
				if($this->response->user->admin)
					$this->response->godmode = $session->get("_global_admin_mode") && true;
				else
					$session->remove("_global_admin_mode");
			}
		}
		
		if($this->response->godmode)
			$this->response->users = User\Content::find()->where("active = 1")->order("id ASC")->get("objects");
		
		$session->set("_notification_messages", []);
		
		return null;
	}
	
	
	/**
	 *	Tells Silex to output this request as a HTML request, using the standard umbrella object
	 *	as a source of data
	 */
	protected function toHTML($template = "index.twig")
	{
		$context = $this->application["outragedns.context"];
		$output = $this->application["twig"]->render($template, $context->toArray());
		
		return $output;
	}
	
	
	/**
	 *	Creates an array of paths that we can use in our templates and controllers
	 *	to figure out what route we're wanting to take
	 */
	protected function getRequestURL(Request $request)
	{
		$list = explode("/", parse_url($request->server->get("REQUEST_URI"), PHP_URL_PATH));
		$list[0] = $request->getScheme()."://".$request->getHttpHost();
		
		if(strlen(end($list)) == 0)
			array_pop($list);
		
		return $list;
	}
}
