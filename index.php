<?php
/**
 *	The beginning of the end for all OUTRAGEweb requests.
 */


if(!class_exists("\OUTRAGEweb\Construct\Autoloader"))
	require $_SERVER["DOCUMENT_ROOT"]."/app/lib/OUTRAGEweb/Construct/Autoloader.php";


# bootstrap the autoloader and load the config - crucial for pretty much
# everything in the system
session_start();

\OUTRAGEweb\Construct\Autoloader::register();

\OUTRAGEweb\Configuration\Wallet::getInstance()->load($_SERVER["DOCUMENT_ROOT"]."/app/etc/config/*.json");
\OUTRAGEweb\Configuration\Wallet::getInstance()->load($_SERVER["DOCUMENT_ROOT"]."/app/etc/config/entities/*.json");


# perhaps it's a good idea to init our request environment
$environment = new \OUTRAGEweb\Request\Environment();


$content = new \OUTRAGEdns\User\Content();
$content->load(1);

$template = new \OUTRAGEdns\Domain\Content();
$template->db->begin();

$set = array
(
	"name" => "ss.westie.sh",
	
	"records" => array
	(
		[ "name" => "ss.westie.sh", "type" => "A", "content" => "127.0.0.1" ],
	),
);

var_dump($template->save($set));

$template->db->commit();