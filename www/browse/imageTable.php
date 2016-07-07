<?php
	/*
		Simply echos the table of images for the current user.
	*/
	session_start();
	require('../php/db_connection.php');
	require('../php/db_user.php');
	require('../php/db_image.php');
	require('../php/exif.php');
	
	if (user_loggedIn())
		image_echoProcessingQueueTable();
	else
		echo('No user logged in.');
?>