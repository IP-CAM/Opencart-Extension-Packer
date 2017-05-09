<?php
class Log {
	private $fp;

	public function __construct($file) {
		$this->fp = fopen($file, 'w');
	}

	public function write($message) {
		fwrite($this->fp, $message . "\n");
	}

	public function __destruct() {
		fclose($this->fp);
	}
}
