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

class Configurator {

	const CONFIG_FILENAME = "raida.json";

	private $configPath;

	function __construct($path = self::CONFIG_FILENAME) {
		$this->configPath = $path;
	}

	public function doPage() {
		$config = null;
		if (isset($_POST['config']))
			$config = $_POST['config'];

		$suffix = $config ? "UPDATE" : "VIEW" ;
		Logger::debug("Config page. $suffix");
		
		$this->doAuth();

		$msg = "";
		if ($config) {
			$config = @json_decode($config);
			$jsonLastError = json_last_error();
			if ($jsonLastError !== JSON_ERROR_NONE) {
				$msg = "ERROR: Invalid JSON";
			} else {
				$error = "";
				if ($this->saveConfig($config, $error)) {
					$msg = "Config file has been updated successfully";
				} else
					$msg = "ERROR: $error";
			}
		} else {
			if (!file_exists($this->configPath))
				$this->setDefaultConfig();
		}

		$config = file_get_contents($this->configPath);
		if ($config === false)
			throw new FakeRAIDAException("Failed to open configfile " . $this->configPath);

		$data = [
			"msg" => "$msg",
			"json" => "$config"
		];


		$this->render($data);
	}

	public function getConfig() {
		$config = file_get_contents($this->configPath);
		if ($config === false)
			throw new FakeRAIDAException("Failed to open configfile " . $this->configPath);

		$config = @json_decode($config);
		$jsonLastError = json_last_error();
		if ($jsonLastError !== JSON_ERROR_NONE) 
			throw new FakeRAIDAException("Failed to parse JSON " . $jsonLastError);

		return $config;
	}

	private function saveConfig($config, &$error) {
		Logger::debug("New config: " . print_r($config, true));

		$baseConfig = $this->formConfig();

		foreach ($config as $k => $v) {
			if (!isset($baseConfig[$k])) {
				$error = "Unknown key: $k";
				return false;
			}
			
			if ($k == "timeout")
				$v = "" . intval($v);

			if ($k == "detectResult") {
				if (!in_array($v, RAIDAServer::getResults())) {
					$error = "Invalid detectResult: $v";
					return false;
				}
			}

			if ($k == "mdcoins") {
				if ($v != "inherit" && !is_array($v) || count($v) > RAIDAServer::MAX_MDCOINS) {
					$error = "Invalid mdcoins length";
					return false;
				} else {
					foreach ($v as $coinResult) {
						if (!in_array($coinResult, RAIDAServer::getResults())) {
							if ($coinResult != "inherit") {
								$error = "Invalid mdCoin detectResult: $coinResult";
								return false;
							}
						}
					}
				}
			}
			
			if ($k == "raida") {
				foreach ($v as $rk => $rv) {
					if (!isset($baseConfig[$k][$rk])) {
						$error = "Invalida raida: $rk";
						return false;
					}

					$raidaInstance = $config->$k->$rk;
					foreach ($raidaInstance as $rik => $riv) {
						if (!isset($baseConfig[$k][$rk][$rik])) {
							$error = "Unknown key: $rik";
							return false;
						}

						if ($rik == "detectResult") {
							if (!in_array($riv, RAIDAServer::getResults())) {
								if ($riv !== "inherit") {
									$error = "Invalid $rk detectResult: $riv";
									return false;
								}
							}
						}

						if ($rik == "timeout" && $riv !== "inherit") {
							$riv = "" . intval($riv);
						}
					
						if ($rik == "mdcoins") {
							if ($riv != "inherit" && !is_array($riv) || count($riv) > RAIDAServer::MAX_MDCOINS) {
								$error = "Invalid mdcoins length";
								return false;
							} else {
								foreach ($riv as $rcr) {
									if (!in_array($rcr, RAIDAServer::getResults())) {
										if ($rcr != "inherit") {
											$error = "Invalid mdCoin result: $rcr";
											return false;
										}
									}
								}
							}
						}

						$baseConfig[$k][$rk][$rik] = $riv;
					}
				}

				continue;
			}
	
			$baseConfig[$k] = $v;
		}
		

		$config = @json_encode($baseConfig, JSON_PRETTY_PRINT);
		if (!file_put_contents($this->configPath, $config))
			throw new FakeRAIDAException("Failed to save config");

		return true;
	}

	private function formConfig() {
		$rns = [
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

		$config = [
			"timeout" => "0",
			"detectResult" => "pass",
			"mdcoins" => [],
			"hint" => "1:10",
			"raida" => [],
		];


		for ($i = 0; $i < 25; $i++) {
			$config['raida']['raida' . $i] = [
				"timeout" => "inherit",
				"detectResult" => "inherit",
				"mdcoins" => "inherit",
				"rn" => $rns[$i],
				"hint" => "1:10"
			];
		}

		return $config;
	}

	private function setDefaultConfig() {
		$config = $this->formConfig();

		$json = @json_encode($config, JSON_PRETTY_PRINT);

		$rv = file_put_contents($this->configPath, $json);
		if ($rv === false) {
			Logger::error("Failed to save file");
			return;
		}
	}

	private function render($data) {
		$templateData = file_get_contents(TEMPLATEDIR . "/config.html");

		foreach ($data as $k => $v) {
			$templateData = preg_replace("/{\{$k\}}/", $v, $templateData);
		}

		echo $templateData;
	}

	private function doAuth() {

		if (!isset($_SERVER['PHP_AUTH_USER'])) {
			header('WWW-Authenticate: Basic realm="My Realm"');
			header('HTTP/1.0 401 Unauthorized');

			die('Access denied');
		}

		$user = $_SERVER['PHP_AUTH_USER'];
		$password = $_SERVER['PHP_AUTH_PW'];

		if ($user !== CONFIG_USER || $password !== CONFIG_PASSWORD)
			die('Access denied');
			
		return false;
	}


}
