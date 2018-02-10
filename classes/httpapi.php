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

class HTTPAPI {
	var $needAuth;

	function __construct($url, $contentType = "", $timeout = SOCKET_TIMEOUT) {
		$this->url = $url;
		$this->timeout = $timeout;
		$this->contentType = $contentType;
		$this->errno = 0;
		$this->error = "";
	}

	function doRequestCommon($request) {
		return $this->doRequestCommonURL($request, $this->url);
	}

	function doRequestCommonURL($request, $url) {
		$ch = curl_init();

		if (!is_array($request)) {
			$header[] = "Content-type: " . $this->contentType;
			$header[] = "Content-length: " . strlen($request);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
	
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
	
		if ($request)
			curl_setopt($ch, CURLOPT_POSTFIELDS, $request);

		if ($this->needAuth) {
			$credentials = $this->login . ":" . $this->password;
			curl_setopt($ch, CURLOPT_USERPWD, $credentials);
		}

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);

		Logger::debug("Connecting to " . $url);
		$response = curl_exec($ch);
		if (curl_errno($ch)) {
			$this->errno = curl_errno($ch);
			Logger::error("Failed to perform request: " . curl_error($ch));
			return false;
		}

		$info = curl_getinfo($ch);
		if ($info['http_code'] != 200) {
			Logger::error("Unexpected HTTP return code: " . $info['http_code']);
			Logger::debug("Response: " . $response);
			curl_close($ch);
			return false;
		}

		curl_close($ch);

		return $response;
	}

	static function fabric($className, $args) {
		$className = "\FakeRAIDA\\$className";

		$object = new $className($args);

		return $object;
	}
}
        

