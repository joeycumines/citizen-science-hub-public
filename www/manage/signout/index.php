<?php
	/*
		Just echos the output of user_logout()
	*/
	
	session_start();
	require('../../php/db_connection.php');
	require('../../php/db_user.php');
	require('../../php/pageRedirect.php');
	
	$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : null;
	
	$message = user_logout();
	
	if ($redirect != null)
		echoRedirectPage('../..'.$redirect);
	else
		echo($message);
?>
