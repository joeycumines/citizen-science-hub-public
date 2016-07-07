<?php
	/*
		Include this if you need to display an error to the user / script.
		
		- 404 etc
	*/
	
	/**
		Gives us a http error. Doesn't kill the script though, so you must do that yourself (perhaps after 
		displaying a better error page).
		
		http://stackoverflow.com/a/23190950
	*/
	function httpError($httpStatusCode, $httpStatusMsg) {
		$phpSapiName = substr(php_sapi_name(), 0, 3);
		if ($phpSapiName == 'cgi' || $phpSapiName == 'fpm') {
			header('Status: '.$httpStatusCode.' '.$httpStatusMsg);
		} else {
			$protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
			header($protocol.' '.$httpStatusCode.' '.$httpStatusMsg);
		}
	}
?>
