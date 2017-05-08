<?php
class Package {
	private $files;
	private $dir;

	public function __construct($file, $dir) {
		$this->files = array();
		$this->dir = $dir;
		if(is_file($file)) {
			$fp = fopen($file, 'r');
			while($line = fgets($fp)) {
				$line = trim($line);
				if(is_file($this->dir. $line)) {
					$this->files[] = $line;
				} else if(is_dir($this->dir. $line)) {
					$this->files = array_merge($this->files, $this->listFiles($this->dir. $line));
				}
			}
			fclose($fp);
		}

	}

	public function getFiles() {
		return $this->files;
	}

	public function listFiles($path) {
		$path = preg_replace('/(.*[^\/])$/', '$1/', $path);
		$files = glob($path .'*');
		$out = array();
		foreach($files as $file) {
			//echo $file ."\n";
			if(is_file($file)) {
				$out[] = str_replace(STORE_DIR, '', $file);
			} else {
				$out = array_merge($out, $this->listFiles($file));
			}
		}
		return $out;
	}

	public function copyFiles($dest) {
		foreach($this->getFiles() as $file) {
			$file_array = explode('/', $file);
			$path = array_slice($file_array, 0, sizeof($file_array) - 1);
			$path_string = '';
			foreach($path as $dir) {
				$path_string .= $dir .'/';
				if(!is_dir($dest . $path_string)) {
					mkdir($dest . $path_string);
				}
			}
			copy($this->dir . $file, $dest . $file);
		}
	}
}
