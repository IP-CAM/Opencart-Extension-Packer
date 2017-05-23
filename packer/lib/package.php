<?php
require('log.php');
class Package {
	private $files;
	private $zip_files;
	private $ocmod;
	private $db_file;
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
				if(!$this->ocmod && preg_match('/.*\.ocmod.xml$/', $line)) {
					$this->ocmod = $line;
				} else if(!$this->db_file && preg_match('/.*\.sql$/', $line)) {
					$this->db_file = $line;
				} else if(is_file($this->dir. $line)) {
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

	public function getZipFiles() {
		return $this->zip_files;
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

		if($this->ocmod) {
			if(!copy($this->dir . $this->ocmod, $dest . 'install.xml')) {
				$this->log->write('The OC Mod file i('. $this->ocmod .')could not be copied');
			} else {
				$this->zip_files[] = $dest . 'install.xml';
			}
		}
		
		if($this->db_file) {
			if(!copy($this->dir . $this->db_file, $dest . 'install.sql')) {
				$this->log->write('The DB file i('. $this->db_file .')could not be copied');
			} else {
				$this->zip_files[] = $dest . 'install.sql';
			}
		}

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
			if(!copy($this->dir . $file, $dest . $file)) {
				$this->log->write('The file '. $this->dir . $file .' could not be copied to '. $dest . $file);
			} else {
				$this->zip_files[] = $dest . $file;
			}
		}
	}

	public function zip($name) {
		$zip = new ZipArchive();
		$zip_file = $name . '.ocmod.zip';
		$base = $this->dir . $name;
		if(is_file($zip_file)) {
			unlink($zip_file);
		}
		if ($zip->open($zip_file, ZipArchive::CREATE)!==TRUE) {
			$this->log->write('Zip file could not be created');;
		}
		foreach($this->getZipFiles() as $file) {
			if(!$zip->addFile($file, str_replace($name, '', $file))) {
				$this->log->write('Could not add file '. $file .' to zip');
			}
		}
		$zip->close();
	}
}
