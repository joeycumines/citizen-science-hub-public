<?php
	/*
		Deletes a image and outputs a message.
		
		Only works if we are an admin.
	*/
	session_start();
	require('../php/db_connection.php');
	require('../php/db_user.php');
	require('../php/db_userPerms.php');
	require('../php/db_image.php');
	require('../php/exif.php');
	
	$username = validate(isset($_GET['username']) ? $_GET['username'] : null);
	$sourceId = validate(isset($_GET['sourceId']) ? $_GET['sourceId'] : null);
	
	if (user_loggedIn() && canUserAdmin($_SESSION['USERNAME'])) {
		//try to delete the image
		if (image_delete($username, $sourceId)) {
			echo('Image was successfully deleted');
		} else {
			echo('Image deletion failed');
		}
	} else {
		echo('You do not have permission to do that');
	}
?>