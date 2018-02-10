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

class hintsAPI extends HTTPAPI {

	function __construct($args) {
		$this->url = $args['url'];

                parent::__construct($this->url);
        }

	private function buildRequest() {
		return "";
	}

	public function doRequest($params) {

		$reqParams = [];
		foreach ($params as $k => $v) {
			$reqParams[] = "$k=$v";
		}

		$params = join("&", $reqParams);
		$url = $this->url . "?$params";

		Logger::debug("Requesting $url");

		$request = $this->buildRequest();
		$rv = $this->doRequestCommonURL($request, $url);
		if (!$rv) {
			$this->error = "Request failed. See logs for the details";
			return null;
		}

		return $rv;
        }




}
