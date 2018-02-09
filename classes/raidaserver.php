<?php

/**
 * Copyright 2018 CloudCoin
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Facebook.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */

namespace FakeRAIDA;

class RAIDAServer {

	private $idx;

	const VERSION = "2017.09.01";
	const TEST_DATE = "2016-12-20 9:00 pm";

	private $errMaps = [
		"400" => "400: Bad Request",
		"401" => "401: Unauthorized",
		"402" => "402: Payment Required",
		"403" => "403: Forbidden",
		"404" => "404: Not Found",
		"405" => "408: Request Timeout",
		"408" => "408: Request Timeout",
		"500" => "500: Internal Server Error",
		"503" => "503: Service Unavailable"
	];


	function __construct($url) {

		$matches = [];
		if (!preg_match("/^raida(\d+)\..+$/", $url, $matches))
			throw new FakeRAIDAException("Invalid URL");

		$this->idx = $matches[1];

		Logger::setPrefix($this->getName());
		Logger::debug("Initialized");
	}

	public function getName() {
		return "RAIDA" . strval($this->idx);
	}

	public function showError($status) {
		Logger::debug("Error $status");

		if (!isset($this->errMaps[$status]))
			$status = "500";

		$obj = [
			"server" => $this->getName(),
			"status" => "error",
			"message" => $this->errMaps[$status],
			"time" => "See header"
		];

		throw new FakeRAIDAException($obj);
//		$this->output($obj);
	}

	public function output($obj) {
		$data = json_encode($obj);
		$jsonLastError = json_last_error();
		if ($jsonLastError !== JSON_ERROR_NONE) 
			throw new FakeRAIDAException("Failed to encode JSON: " . $jsonLastError);

		echo $data;
	}

	public function setService($service, $params) {

		$serivce = strtolower($service);
		$service = ucfirst($service);
		$method = "__service$service";

		if (!method_exists($this, $method)) 
			$this->showError("404");

		$this->$method($params);
	}

	private function getResponseTemplate($message, $status = "ready") {
		$now = @date("Y-m-d H:i:s");

		return [
			"server" => $this->getName(),
			"status" => $status,
			"time" => $now,
			"message" => $message
		];
	}

	private function __serviceVersion($params) {
		// brief
		if (isset($params['b'])) {
			echo self::VERSION;
			return;
		}

		$strTime = Utils::getStrTime(self::VERSION);	

		$obj = $this->getResponseTemplate("Up: This software was last updated on $strTime");
		$obj['version'] = self::VERSION;
		unset($obj['status']);

		$this->output($obj);
	}

	private function __serviceEcho($params) {
		
		$obj = $this->getResponseTemplate("Up: Detection agent ready to detect authenticity.");

		$this->output($obj);
	}

	private function __serviceTest($params) {
		$obj = $this->getResponseTemplate("There are 16777216 records in the DB");
		$obj['time'] = self::TEST_DATE;

		$this->output($obj);
	}




}
