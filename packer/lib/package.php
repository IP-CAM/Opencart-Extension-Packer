<?php
require('log.php');
class Package {
	private $files;
	private $dir;
	private $log;

	public function __construct($file, $dir) {
		$this->files = array();
		$this->dir = $dir;
		$this->log = new Log('log.txt');
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
			if(is_file($file)) {
				$out[] = str_replace(STORE_DIR, '', $file);
			} else {
				$out = array_merge($out, $this->listFiles($file));
			}
		}
		return $out;
	}

	public function copyFiles($dest) {
		$dest .= 'upload/';
		if(!is_dir($dest)) {
			mkdir($dest);
		}
		$this->log->write('Copying '. sizeof($this->getFiles()) .' files to '. $dest);
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
			$this->log->write('Copied '. $this->dir . $file .' to '. $dest . $file);
		}
	}

	public function zip($dest) {
		$zip = new ZipArchive();
		$zip_file = preg_replace('/(.*)\/$/', '$1.ocmod.zip', $dest);
		if(is_file($zip_file)) {
			unlink($zip_file);
		}
		if ($zip->open($zip_file, ZipArchive::CREATE)!==TRUE) {
			    exit("cannot open <$zip_file>\n");
		}
		foreach($this->getFiles() as $file) {
			$zip->addFile($dest .'upload/'.  $file);
		}
		$zip->close();
	}
}
