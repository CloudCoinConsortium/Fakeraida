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

	const DR_PASS = "pass";
	const DR_FAIL = "fail";
	const DR_ERROR = "error";
	const DR_EMPTY = "empty";

	private $configurator;
	private $briefOutput;
	private $config;

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
		$this->briefOutput = false;

		$this->configurator = new Configurator();

		Logger::setPrefix($this->getName());
		Logger::debug("Initialized");
	}

	static function getResults() {
		return [self::DR_PASS, self::DR_FAIL, self::DR_ERROR, self::DR_EMPTY];
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

		Logger::debug("Output: $data");
		echo $data;
	}

	public function runService($service, $params) {

		$service = strtolower($service);
		$service = ucfirst($service);
		$method = "__service$service";

		if (!method_exists($this, $method)) 
			$this->showError("404");

		Logger::debug("run $service " . print_r($params, true));

		if (isset($params['b'])) {
			$this->briefOutput = true;
			unset($params['b']);
		}

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

	private function failError() {
		if ($this->briefOutput) {
			echo "fail";
			return true;
		}

		return false;
	}

	private function isValidDenomination($denomination) {
		return in_array($denomination, [1, 5, 25, 100, 250]);
	}
	
	private function isValidNn($nn) {
		return $nn == 1;
	}

	private function isValidSn($sn) {
		return ($sn > 0 && $sn < 16777216);
	}

	private function isValidGUID($guid) {
		return !empty($guid) && preg_match('/^[A-F0-9]{32}$/i', $guid);
	}

	private function getDenominationBySn($sn) {
		$denomination = 0;

		if ($sn > 0 && $sn < 2097153) {
			$denomination = 1;
		} else if ($sn < 4194305) {
		        $denomination = 5;
		} else if ($sn < 6291457) {
			$denomination = 25;
		} else if ($sn < 14680065) {
			$denomination = 100;
		} else if ($sn < 16777217) {
			$denomination = 250;
		} else {
			$denomination = 0;
		}

		return $denomination;
	}

	private function mergeConfig() {
		$config = $this->configurator->getConfig();

		$this->config = [];
		foreach ($config as $k => $v) {
			if ($k == "raida") {
				$myKey = "raida{$this->idx}";
				$myRAIDA = $config->raida->$myKey;

				foreach ($myRAIDA as $rk => $rv) {
					if ($rv == "inherit")
						continue;

					$this->config[$rk] = $rv;
				}

				continue;
			}

			$this->config[$k] = $v;
		}
	}

	private function __serviceDetect($params) {
		foreach (["nn", "sn", "an", "pan", "denomination"] as $k) {
			if (!isset($params[$k])) {
				if ($this->failError())
					return;

				$errorMsg = "GET Parameters: You must provide a nn, sn, an, pan and denomination. $k";
				$obj = $this->getResponseTemplate($errorMsg, "fail");
				$this->output($obj);

				return;
			}
		}

		$sn = intval($params['sn']);
		$nn = intval($params['nn']);
		$denomination = intval($params['denomination']);

		$an = $params['an'];
		$pan = $params['pan'];

		$errorMsg = "";
		if (!$this->isValidDenomination($denomination)) {
			$errorMsg = "Denomination: The unit's Denomination was out of range.";
		} else if (!$this->isValidNn($nn)) { 
			$errorMsg = "Network: Incorrect network server. Your pie slices are on another server. See server directory for correct server.";
		} else if (!$this->isValidSn($sn)) {
			$errorMsg = "SN: The unit's serial number was out of range.";
		} else if (!$this->isValidGUID($an)) {
			$errorMsg = "AN: The unit's Authenticity Number was out of range.";
		} else if (!$this->isValidGUID($pan)) {
			$errorMsg = "PAN: The unit\'s Proposed Authenticity Number was out of range.";
		} else {
			$correctDenomination = $this->getDenominationBySn($sn);
			if ($correctDenomination != $denomination) {
				$errorMsg = "Denomination: The item you are authenticating is a $correctDenomination unit. However, the request was for a $denomination unit. Someone may be trying to pass a money unit that is not of the true value";
			}
		}

		if ($errorMsg) {
			if ($this->failError())
				return;

			$obj = $this->getResponseTemplate($errorMsg, "fail");
			$obj['sn'] = $sn;
			$this->output($obj);
	
			return;
		}

		$this->mergeConfig();

		if ($this->config['timeout'])
			sleep($this->config['timeout']);

		$result = $this->config['detectResult'];
		if ($result == self::DR_EMPTY) {
			$this->output("");
			return;
		}

		if ($this->briefOutput) {
			echo $result;
			return;
		}

		if ($result == self::DR_PASS) {
			$message = "Authentic: The unit is an authentic $denomination-unit. Your Proposed Authenticity Number is now the new Authenticty Number. Update your file.";
		} else if ($result == self::DR_FAIL) {
			$message = "Counterfeit: The unit failed to authenticate on this server. You may need to fix it on other servers.";
		} else {
			$message = "Error";
		}

		$obj = $this->getResponseTemplate($message);
		$obj['sn'] = $sn;
		$obj['status'] = $result;
		$this->output($obj);


	}

	private function __serviceVersion($params) {
		if ($this->briefOutput) {
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
