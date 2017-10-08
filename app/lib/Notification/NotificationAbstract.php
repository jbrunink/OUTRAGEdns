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
		$this->message = $message;
		
		if(!isset($_SESSION["_notification_messages"]))
			$_SESSION["_notification_messages"] = [];
		
		$_SESSION["_notification_messages"][] = $this;
	}
}