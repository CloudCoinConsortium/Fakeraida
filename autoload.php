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

if (version_compare(PHP_VERSION, '5.4.0', '<')) {
	throw new Exception('This SDK requires PHP version 5.4 or higher.');
}

if (!extension_loaded("json")) {
	throw new Exception('This SDK requires PHP JSON module');
}

require "config.php";

date_default_timezone_set("UTC");

spl_autoload_register(function($class) {
	$prefix = "FakeRAIDA";
 
	$baseDir = __DIR__;
	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0) {
		return;
	}
	$relativeClass = substr($class, $len);
	$file = $baseDir . "/" . CLASSDIR . str_replace("\\", "/", $relativeClass) . '.php';
	$file = strtolower($file);
	if (file_exists($file)) {
		require $file;
	}
});
