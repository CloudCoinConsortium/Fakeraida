<?php

require "autoload.php";

use FakeRAIDA\RAIDAServer;
use FakeRAIDA\FakeRAIDAException;
use FakeRAIDA\Logger;
use FakeRAIDA\Configurator;

Logger::init(Logger::MSGTYPE_DEBUG);

if (!isset($_GET['service']))
	die('{"server":"RAIDA","status":"error","message":"Internal Server Error","time":"See header"}');

try {
	if ($_GET['service'] == "config") {
		$configurator = new Configurator();
		$configurator->doPage();
		exit;
	}

	$raidaServer = new RAIDAServer($_SERVER['SERVER_NAME']);
	$raidaServer->runService($_GET['service'], $_GET, $_POST);
} catch (FakeRAIDAException $e) {
	echo $e->getMessage();
}
