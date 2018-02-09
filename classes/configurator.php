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
			
			if ($k == "raida") {
				foreach ($v as $rk => $rv) {
					if (!isset($baseConfig[$k][$rk])) {
						$error = "Invalida raida: $rk";
						return false;
					}

					$raidaInstance = $config->$k->$rk;
					foreach ($raidaInstance as $rik => $riv) {
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
		$config = [
			"timeout" => "0",
			"detectResult" => "pass",
			"raida" => []
		];


		for ($i = 0; $i < 25; $i++) {
			$config['raida']['raida' . $i] = [
				"timeout" => "inherit",
				"detectResult" => "inherit"
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
