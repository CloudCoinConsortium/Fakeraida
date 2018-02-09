<?php

require "autoload.php";

use FakeRAIDA\RAIDAServer;
use FakeRAIDA\FakeRAIDAException;
use FakeRAIDA\Logger;

Logger::init(Logger::MSGTYPE_DEBUG);

if (!isset($_GET['service']))
	die('{"server":"RAIDA","status":"error","message":"Internal Server Error","time":"See header"}');

try {
	$raidaServer = new RAIDAServer($_SERVER['SERVER_NAME']);
	$raidaServer->setService($_GET['service'], $_GET);
} catch (FakeRAIDAException $e) {
	echo $e->getMessage();
}
