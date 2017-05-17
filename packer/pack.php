<?php
ini_set('display_errors', '1');
require('lib/package.php');
require('config.php');

session_start();
session_unset();
$error = '';
if(!is_file(EXTENSION_FILES)) {
	$error = 'No Extension file defined!';
}

if(!$_POST['dest']) {
	$error = 'No destination set';
} else {
	$dest = preg_replace('/(.*[^\/])$/', '$1/', $_POST['dest']);
}

if(!empty($error)) {
	$_SESSION['oep_result'] = array(
		'class' => 'error',
		'message' => $error
	);
} else {
	if(!is_dir($dest)) {
		mkdir($dest);
	}
	
	$pack = new Package(EXTENSION_FILES, STORE_DIR);
	
	$pack->copyFiles($dest);
	
	$pack->zip(substr($dest, 0, strlen($dest) - 1));

	$message = 'Files copied to '. $dest;

	$_SESSION['oep_result'] = array(
		'class' => 'success',
		'message' => $message
	);
}

header('location:index.php');
