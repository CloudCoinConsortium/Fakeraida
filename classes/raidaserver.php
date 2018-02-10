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

	const MAX_MDCOINS = 200;

	const FIX_SECONDS_ALLOWED = 15;

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

	private $rns = [
		"3885168a36d3f253d806eb1ba83e60feb27d528cae03", //0
		"4a04f41e2c91d6aa81f82104f140ac4c0a537d32c903",
		"d779e0f70dbcbd4cba2a1879d1831862f8128701f3b2", //2
		"8fa6cb435208ec45c13127dbd7b08268954bb0d6b038",
		"6516081b58b40ad0f4888f61894bb45718a34154d98e",
		"70e7d5c72e16b56444df7dc1240f275729cedd815c56",
		"c18b2051d76fbfe1fb465968fb9d45079b781bf1a5e0",
		"a36ca75c74f97195f527d00301f8bb00442727639e34", //7
		"ee81142bfb1097890f74662964abc9e94e65b89cc5ba",
		"3cab081b161e87d381ba151d45fd9d1f12ed2872bb06",
		"e23ae452f30e6eb4febfd97d89ae333f7394f91e4189",
		"d12a81437037c96bf0477d6afdda4e65d072dd19c159",
		"7d3e0c855ef30681902f8c6fbcfff82ba312ccad2da9",
		"21174c878e35020ad40b2743edde312de29527d9aed0",
		"c27b973d6fa7825db2959c8dbe45122a7259b99bdba1", //14
		"2e818637f130b0266bd1d9c5d3a1587a18750fe71730",
		"8c7fa26520c0c18e737a2a95039f16defde3784291a3",
		"c56eb5e9327108317f19c07bd214818652d1e09f686d",
		"95540a225c9021405c05a6a612a2b05ff5684c9a6c58", //18
		"3209e6adbaf5eb07eb170795694e6b643190f93285fb",
		"d17bcfd393b7e2c3f1ed53a4b0fcdb3c1312e1fdf79e",
		"6dfb9e7f75bea864ac19d3a8fbd5c744433ac6aeb40f",
		"2df4145e9441afaeae87536b2e38fe1d75d40a8e3d7b", //22 
		"1f04fe6bb8d76b038fda63e67e5e46b35a924a472779",
		"4fe5d3c74b237e5372540b7ce3ecd82b2828b951ddb3"  //24
	];

	private $neighbourMap = [
		0 => [19, 20, 21, 24,  1,  4,  5,  6],
		1 => [20, 21, 22,  0,  2,  5,  6,  7],
		2 => [21, 22, 23,  1,  3,  6,  7,  8],
		3 => [22, 23, 24,  2,  4,  7,  8,  9],
		4 => [23, 24,  0,  3,  5,  8,  9, 10],
		5 => [24,  0,  1,  4,  6,  9, 10, 11],
		6 => [0,  1,  2,  5,  7, 10, 11, 12],
		7 => [1,  2,  3,  6,  8, 11, 12, 13],
		8 => [2,  3,  4,  7,  9, 12, 13, 14],
		9 => [3,  4,  5,  8, 10, 13, 14, 15],
		10 => [4,  5,  6,  9, 11, 14, 15, 16],
		11 => [5,  6,  7, 10, 12, 15, 16, 17],
		12 => [6,  7,  8, 11, 13, 16, 17, 18],
		13 => [7,  8,  9, 12, 14, 17, 18, 1],
		14 => [8,  9, 10, 13, 15, 18, 19, 20],
		15 => [9, 10, 11, 14, 16, 19, 20, 21],
		16 => [10, 11, 12, 15, 17, 20, 21, 22],
		17 => [11, 12, 13, 16, 18, 21, 22, 23],
		18 => [12, 13, 14, 17, 19, 22, 23, 24],
		19 => [13, 14, 15, 18, 20, 23, 24,  0],
		20 => [14, 15, 16, 19, 21, 24,  0,  1],
		21 => [15, 16, 17, 20, 22,  0,  1,  2],
		22 => [16, 17, 18, 21, 23,  1,  2,  3],
		23 => [17, 18, 19, 22, 24,  2,  3,  4],
		24 => [18, 19, 20, 23,  0,  3,  4,  5]
	];

	function __construct($url) {

		$matches = [];
		if (!preg_match("/^raida(\d+)\.(.+)$/", $url, $matches))
			throw new FakeRAIDAException("Invalid URL");

		$this->idx = $matches[1];
		$this->domain = $matches[2];
		$this->briefOutput = false;

		$this->configurator = new Configurator();
		$this->totalRAIDAs = 25;

		Logger::setPrefix($this->getName());
		Logger::debug("Initialized");
	}

	static function getResults() {
		return [self::DR_PASS, self::DR_FAIL, self::DR_ERROR, self::DR_EMPTY];
	}

	public function getName() {
		return "RAIDA" . strval($this->idx);
	}

	private function getMyRN() {
		return $this->rns[$this->idx];
	}

	private function getMyNeighbourMap() {
		return $this->neighbourMap[$this->idx];
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
	}

	public function output($obj) {
		$data = json_encode($obj);
		$jsonLastError = json_last_error();
		if ($jsonLastError !== JSON_ERROR_NONE) 
			throw new FakeRAIDAException("Failed to encode JSON: " . $jsonLastError);

		Logger::debug("Output: $data");
		echo $data;
	}

	public function runService($service, $params, $postParams = []) {

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

		if (count($postParams) > 0) {
			Logger::debug("Post: " . print_r($postParams, true));
			$this->$method($params, $postParams);
		} else {
			$this->$method($params);
		}
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

	private function isValidRN($rn) {
		return preg_match("/^[a-f0-9]{44}$/", $rn);
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

					if ($rk == "mdcoins") {
						$rv = array_map(function($item) use ($myRAIDA, $config) {
							if ($item == "inherit") {
								$item = $myRAIDA->detectResult;
								if ($item == "inherit")
									$item = $config->detectResult;
							}		

							return $item;
						}, $rv);
					}

					$this->config[$rk] = $rv;
				}

				continue;
			}

			if ($k == "mdcoins") {
				if ($v == "inherit")
					$v = [];

				$v = array_map(function($item) use ($config) {
					if ($item == "inherit")
						$item = $config->detectResult;

					return $item;
				}, $v);
			}

			if (!isset($this->config[$k]))
				$this->config[$k] = $v;
		}

		Logger::debug("Resulting config: " . print_r($this->config, true));
	}

	private function verifyDetectParams($nn, $sn, $an, $pan, $denomination) {
		Logger::debug("Verify $nn, $sn, $an, $pan, $denomination");

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
			$errorMsg = "PAN: The unit's Proposed Authenticity Number was out of range.";
		} else {
			$correctDenomination = $this->getDenominationBySn($sn);
			if ($correctDenomination != $denomination) {
				$errorMsg = "Denomination: The item you are authenticating is a $correctDenomination unit. However, the request was for a $denomination unit. Someone may be trying to pass a money unit that is not of the true value";
			}
		}

		return $errorMsg;
	}

	private function getDetectMessage($result, $denomination) {
		Logger::debug("Detect message for result $result");

		if ($result == self::DR_PASS) {
			$message = "Authentic: The unit is an authentic $denomination-unit. Your Proposed Authenticity Number is now the new Authenticty Number. Update your file.";
		} else if ($result == self::DR_FAIL) {
			$message = "Counterfeit: The unit failed to authenticate on this server. You may need to fix it on other servers.";
		} else {
			$message = "Error";
		}

		return $message;
	}

	private function checkInputCommon($params) {
		foreach (["nn", "sn", "an", "pan", "denomination"] as $k) {
			if (!isset($params[$k])) {
				if ($this->failError())
					return false;

				$errorMsg = "GET Parameters: You must provide a nn, sn, an, pan and denomination.";
				$obj = $this->getResponseTemplate($errorMsg, "fail");
				$this->output($obj);

				return false;
			}
		}

		return true;
	}

	private function __serviceGet_ticket($params) {
		if (!$this->checkInputCommon($params))
			return;

		$sn = intval($params['sn']);
		$nn = intval($params['nn']);
		$an = $params['an'];
		$pan = $params['pan'];
		$denomination = intval($params['denomination']);

		$errorMsg = $this->verifyDetectParams($nn, $sn, $an, $pan, $denomination);
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

		if ($result == self::DR_PASS) {
			$obj = $this->getResponseTemplate($this->getMyRN());
			$obj['sn'] = $sn;
			$obj['status'] = "ticket";
		} else {
			$message = $this->getDetectMessage($result, $denomination);
			$obj = $this->getResponseTemplate($message);
			$obj['sn'] = $sn;
			$obj['status'] = $result;
		}

		$this->output($obj);
	}

	private function __serviceHints($params) {
		if (!isset($params['rn']) || !$this->isValidRN($params['rn'])) {
			print "-3:2147483640";
			return;
		}

		$rn = $params['rn'];
		$myRN = $this->getMyRN();
		
		Logger::debug("rn $rn, myRN $myRN");
		if ($myRN != $rn) {
			print "-2:1000000000";
			return;
		}

		echo "1:10";
	}

	private function __serviceFix($params) {
		foreach (["fromserver1", "fromserver2", "fromserver3", "message1", "message2", "message3", "pan"] as $k) {
			if (!isset($params[$k])) {
				if ($this->failError())
					return;

				$errorMsg = "GET Parameters: You must provide a message1, message2, message3 fromeserver1, fromserver2, fromserver2 and pan.";
				$obj = $this->getResponseTemplate($errorMsg, "error");
				$this->output($obj);
		
				return;
			}
		}

		$fromserver1 = intval($params['fromserver1']);
		$fromserver2 = intval($params['fromserver2']);
		$fromserver3 = intval($params['fromserver3']);

		$message1 = $params['message1'];
		$message2 = $params['message2'];
		$message3 = $params['message3'];

		$pan = $params['pan'];

		$fromservers = [$fromserver1, $fromserver2, $fromserver3];

		foreach ($fromservers as $fromserver) {
			if ($fromserver < 0 || $fromserver > $this->totalRAIDAs - 1) {
				if ($this->failError())
					return;

				$errorMsg = "Server: Server $fromserver out of Range.";
				$obj = $this->getResponseTemplate($errorMsg, "error");
				$this->output($obj);
			
				return;
			}
		}

		$messages = [$message1, $message2, $message3];

		$idx = 0;
		foreach ($messages as $message) {
			if (!$this->isValidRN($message)) {
				if ($this->failError())
					return;

				$errorMsg = "Ticket: Message " . ($idx + 1) . " out of Range.";
				$obj = $this->getResponseTemplate($errorMsg, "error");
				$this->output($obj);
			
				return;
			}

			$idx++;
		}

		if (!$this->isValidGUID($pan)) {
			if ($this->failError())
				return;

			$errorMsg = "PAN: Proposed Authenticity Number was not formatted like: 291BAC68CAAB77088825054BCE27855F.";
			$obj = $this->getResponseTemplate($errorMsg, "error");
			$this->output($obj);
		
			return;
		}
		
		$neighbourMap = $this->getMyNeighbourMap();

		$idx1 = array_search($fromserver1, $neighbourMap);
		$idx2 = array_search($fromserver2, $neighbourMap);
		$idx3 = array_search($fromserver3, $neighbourMap);

		$srvArray = [$idx1, $idx2, $idx3];
		foreach ($srvArray as $idx) {
			if ($idx === false) {
				if ($this->failError())
					return;

				$errorMsg = "Trust: Server $idx Not Trusted.";
				$obj = $this->getResponseTemplate($errorMsg, "error");
				$this->output($obj);
		
				return;
			}
		}

		if (array_diff($srvArray, [0, 1, 3]) && array_diff($srvArray, [1, 2, 4]) 
			&& array_diff($srvArray, [3, 5, 6]) && array_diff($srvArray, [4, 6, 7])) {
			if ($this->failError())
				return;

			$errorMsg = "Trust: Server triad not trusted together.";
			$obj = $this->getResponseTemplate($errorMsg, "error");
			$this->output($obj);
		
			return;
		}
		
		$hintsAPI1 = HTTPAPI::fabric("hintsAPI",['url' => "https://raida$fromserver1." . $this->domain . "/service/hints"]);
		$hintsAPI2 = HTTPAPI::fabric("hintsAPI",['url' => "https://raida$fromserver2." . $this->domain . "/service/hints"]);
		$hintsAPI3 = HTTPAPI::fabric("hintsAPI",['url' => "https://raida$fromserver3." . $this->domain . "/service/hints"]);

		$failedServer = false;
		$rv1 = $hintsAPI1->doRequest(["rn" => $message1]);
		if (is_null($rv1)) {
			$failedServer = $fromServer1;
		} else {
			$rv2 = $hintsAPI2->doRequest(["rn" => $message2]);	
			if (is_null($rv2)) {
				$failedServer = $fromServer2;
			} else {
				$rv3 = $hintsAPI3->doRequest(["rn" => $message3]);	
				if (is_null($rv3)) {
					$failedServer = $fromServer3;
				}
			}
		}

		if ($failedServer !== false) {
			if ($this->failError())
				return;

			$errorMsg = "Connection: Could not connect to Server $failedServer";
			$obj = $this->getResponseTemplate($errorMsg, "error");
			$this->output($obj);
		
			return;
		}


		if (strstr($rv1, ":") === false) {
			$ticket1 = intval($rv1);
			$seconds1 = 0;
		} else {
			list ($ticket1, $seconds1) = explode(":", $rv1);
			$ticket1 = intval($ticket1);
		}

		if (strstr($rv2, ":") === false) {
			$ticket2 = intval($rv2);
			$seconds2 = 0;
		} else {
			list ($ticket2, $seconds2) = explode(":", $rv2);
			$ticket2 = intval($ticket2);
		}

		if (strstr($rv3, ":") === false) {
			$ticket3 = intval($rv3);
			$seconds3 = 0;
		} else {
			list ($ticket3, $seconds3) = explode(":", $rv3);
			$ticket3 = intval($ticket3);
		}

		$tickets = [$ticket1, $ticket2, $ticket3];
		$seconds = [$seconds1, $seconds2, $seconds3];

		$errorMsg = "";
		$idx = 0;
		foreach ($tickets as $ticket) {
			switch ($ticket) {
				case -1:
					$errorMsg = "Remote Ticket: Server ". $fromservers[$idx] . " database said invalid ticket.";
					break;
				case -2:
					$errorMsg = "Remote Ticket: No Ticket (" . $messages[$idx] . ") found on Server " . $fromservers[$idx] . ".";
					break;
				case -3:
					$errorMsg = "Remote Ticket: Server " . $fromservers[$idx] . " said invalid ticket.";
					break;
			}

			if ($errorMsg)
				break;

			$numSeconds = $seconds[$idx];
			if ($numSeconds > self::FIX_SECONDS_ALLOWED) {
				$errorMsg = "Time: Only " . self::FIX_SECONDS_ALLOWED . " seconds where allowed for the fix but server " . $fromservers[$idx] . " took $numSeconds.";
				break;
			}

			$idx++;	
		}

		if (!$errorMsg) {
			if ($ticket1 !== $ticket2 || $ticket1 !== $ticket3) 
				$errorMsg = "Mismatch: The Serial Numbers specified by the trusted remote servers did not match";
		}
		
		if ($errorMsg) {
			if ($this->failError())
				return;

			$obj = $this->getResponseTemplate($errorMsg, "error");
			$this->output($obj);
		
			return;
		}


		$message = "Fixed: Unit's AN was changed to the PAN. Update your AN to the new PAN.";
		$obj = $this->getResponseTemplate($message, "success");
		$obj['sn'] = $ticket1;

		$this->output($obj);
	}

	private function __serviceMulti_detect($params, $postParams) {
		$pCount = false;
		foreach (["nns", "sns", "ans", "pans", "denomination"] as $k) {
			if (!isset($postParams[$k]) || count($postParams[$k]) == 0) {
				if ($this->failError())
					return;

				$errorMsg = "POST Parameters: You must provide a nns, sns, ans, pans and denomination.";
				$obj = $this->getResponseTemplate($errorMsg, "fail");
				$this->output($obj);
		
				return;
			}

			if ($pCount !== false && count($postParams[$k]) != $pCount) {
				if ($this->failError())
					return;

				$errorMsg = "Length: Arrays not all the same length (nn,sn,an,denominations).";
				$obj = $this->getResponseTemplate($errorMsg, "fail");
				$this->output($obj);
		
				return;
			}

			$pCount = count($postParams[$k]);


			if ($pCount > self::MAX_MDCOINS) {
				if ($this->failError())
					return;

				$errorMsg = "Length: Too many coins attached.";
				$obj = $this->getResponseTemplate($errorMsg, "fail");
				$this->output($obj);
	
				return;
			}
		}

		$this->mergeConfig();
		if ($this->config['timeout'])
			sleep($this->config['timeout']);

		$messages = [];
		for ($i = 0; $i < $pCount; $i++) {
			$sn = intval($postParams['sns'][$i]);
			$nn = intval($postParams['nns'][$i]);
			$an = $postParams['ans'][$i];
			$pan = $postParams['pans'][$i];
			$denomination = intval($postParams['denomination'][$i]);

			$errorMsg = $this->verifyDetectParams($nn, $sn, $an, $pan, $denomination);
			if ($errorMsg) {
				$obj = $this->getResponseTemplate($errorMsg, "fail");
				$obj['sn'] = $sn;
	
				$messages[] = $obj;
				continue;
			}

			if ($i < count($this->config['mdcoins']))
				$result = $this->config['mdcoins'][$i];
			else
				$result = $this->config['detectResult'];

			if ($result == self::DR_EMPTY) {
				$messages[] = [];
				continue;
			}

			$message = $this->getDetectMessage($result, $denomination);
			$obj = $this->getResponseTemplate($message);
			$obj['sn'] = $sn;
			$obj['status'] = $result;

			$messages[] = $obj;
		}

		$this->output($messages);
	}

	

	private function __serviceDetect($params) {
		if (!$this->checkInputCommon($params))
			return;

		$sn = intval($params['sn']);
		$nn = intval($params['nn']);
		$an = $params['an'];
		$pan = $params['pan'];
		$denomination = intval($params['denomination']);

		$errorMsg = $this->verifyDetectParams($nn, $sn, $an, $pan, $denomination);
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

		$message = $this->getDetectMessage($result, $denomination);

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
