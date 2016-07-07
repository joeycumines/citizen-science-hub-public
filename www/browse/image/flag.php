<?php
	/*
		Flags a image and outputs a message.
	*/
	session_start();
	require('../../php/db_connection.php');
	require('../../php/db_user.php');
	require('../../php/db_userPerms.php');
	require('../../php/db_image.php');
	require('../../php/exif.php');
	
	$username = validate(isset($_SESSION['USERNAME']) ? $_SESSION['USERNAME'] : null);
	$sourceId = validate(isset($_GET['sourceId']) ? $_GET['sourceId'] : null);
	$reason = validate(isset($_GET['reason']) ? $_GET['reason'] : null);
	
	if (user_loggedIn() && image_flag($username, $sourceId, $reason)) {
		echo('Image was successfully flagged');
	} else {
		echo('Image flagging failed');
	}
?>