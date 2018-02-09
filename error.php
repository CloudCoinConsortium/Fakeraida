<?php

require "autoload.php";

use FakeRAIDA\RAIDAServer;
use FakeRAIDA\FakeRAIDAException;
use FakeRAIDA\Logger;

$errorStatus = $_SERVER["REDIRECT_STATUS"];

Logger::init(Logger::MSGTYPE_DEBUG);

try {
	$raidaServer = new RAIDAServer($_SERVER['SERVER_NAME']);
	$raidaServer->showError($errorStatus);
} catch (FakeRAIDAException $e) {
	echo $e->getMessage();
}
