<?php
	/*
		Deletes a user account and outputs a message.
	*/
	session_start();
	require('../../php/db_connection.php');
	require('../../php/db_user.php');
	require('../../php/db_userPerms.php');
	
	$username = validate(isset($_SESSION['USERNAME']) ? $_SESSION['USERNAME'] : null);
	
	if (user_loggedIn() && user_delete($username)) {
		echo('User was successfully deleted');
	} else {
		echo('User deletion failed');
	}
?>