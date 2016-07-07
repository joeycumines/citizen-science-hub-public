<?php
	/*
		Deletes a image and outputs a message.
	*/
	session_start();
	require('../php/db_connection.php');
	require('../php/db_user.php');
	require('../php/db_userPerms.php');
	require('../php/db_image.php');
	require('../php/exif.php');
	
	$sourceId = validate(isset($_GET['sourceId']) ? $_GET['sourceId'] : null);
	
	if (user_loggedIn() && canUserAdmin($_SESSION['USERNAME'])) {
		//try to delete the image
		if (runUpdatePrepared(getNewPDO(), 'DELETE FROM image_flags WHERE source_id = :source_id;', array(':source_id'=>$sourceId))) {
			echo('Image was successfully pardoned');
		} else {
			echo('Image pardon failed');
		}
	} else {
		echo('You do not have permission to do that');
	}
?>