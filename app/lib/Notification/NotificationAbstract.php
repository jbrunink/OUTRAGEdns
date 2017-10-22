<?php


namespace OUTRAGEdns\Notification;


abstract class NotificationAbstract
{
	/**
	 *	What colour do we need this notification to be?
	 */
	public $colour = null;
	
	
	/**
	 *	What message is this notification?
	 */
	public $message = null;
	
	
	/**
	 *	Let's define a message!
	 */
	public function __construct($message = "")
	{
		global $session;
		
		$this->message = $message;
		
		$key = "_notification_messages";
		$list = $session->get($key) ?: [];
		
		$list[] = $this;
		
		$session->set($key, $list);
	}
}