<?php
	/*
		Deletes a image and outputs a message.
	*/
	session_start();
	require('../../php/db_connection.php');
	require('../../php/db_user.php');
	require('../../php/db_userPerms.php');
	require('../../php/db_image.php');
	require('../../php/exif.php');
	
	$username = validate(isset($_SESSION['USERNAME']) ? $_SESSION['USERNAME'] : null);
	$sourceId = validate(isset($_GET['sourceId']) ? $_GET['sourceId'] : null);
	
	//if we are an admin, then we set username to the correct username
	if ($username != null && $sourceId != null && canUserAdmin($username)) {
		//find the actual username of the image
		$rows = runQueryPrepared(getNewPDO(), 'SELECT username FROM image_source WHERE source_id = :source_id;'
				, array(':source_id'=>$sourceId));
		foreach ($rows as $row) {
			$username = $row['username'];
			break;
		}
	}
	
	if (user_loggedIn() && image_delete($username, $sourceId)) {
		echo('Image was successfully deleted');
	} else {
		echo('Image deletion failed');
	}
?>