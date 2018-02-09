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

class Logger {
	const MSGTYPE_ERROR = 0;
	const MSGTYPE_WARNING = 1;
	const MSGTYPE_INFO = 2;
	const MSGTYPE_DEBUG = 3;
	const DEFAULT_LOGFILE = "fakeraida.log";

	private static $fd = null;
	private static $id = null;
	private static $logLevel;
        private static $errorStrings = [
                "Error", "Warning", "Info", "Debug"
        ];

	private static $prefix = null;

	public function __construct() {
		return null;
	}

	public static function init($level = self::MSGTYPE_ERROR, $filename = self::DEFAULT_LOGFILE) {
		if (self::$fd)
			return;

		self::$logLevel = $level;
		self::$fd = @fopen($filename, "a+");
		if (!self::$fd)
			return;

		self::$id = rand(1024, 65535);
	}	

	private static function _log($level, $message) {
		if (!self::$fd)
			return;
		
		if ($level > self::$logLevel)
			return;

		$idStr = "ID:" . self::$id;
		if (self::$prefix)
			$idStr = self::$prefix . " $idStr";

		$dateStr = @date("d/m/Y H:i:s");
		$string = "$dateStr $idStr [" . self::$errorStrings[$level] . "] $message\n";

		@fwrite(self::$fd, $string);
		@fflush(self::$fd);
	}

	public static function setPrefix($prefix) {
		self::$prefix = $prefix;
	}

	public static function error($msg) {
		return self::_log(self::MSGTYPE_ERROR, $msg);
	}

	public static function warning($msg) {
		return self::_log(self::MSGTYPE_WARNING, $msg);
	}

	public static function info($msg) {
		return self::_log(self::MSGTYPE_INFO, $msg);
	}

	public static function debug($msg) {
		return self::_log(self::MSGTYPE_DEBUG, $msg);
	}

	public static function close() {
		if (self::$fd) {
			fclose(self::$fd);
			self::$fd = null;
		}
	}
}
