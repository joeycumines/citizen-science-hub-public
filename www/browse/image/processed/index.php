<?php
	/*
		Gets the raw processed file for a given processed_id.
		
		If we have thumbnail set to true and provide width and height then we will return a thumbnail instead.
	*/
	
	session_start();
	require('../../../php/db_connection.php');
	require('../../../php/db_user.php');
	require('../../../php/db_userPerms.php');
	require('../../../php/db_imageTools.php');
	
	$id = validate(isset($_GET['id']) ? $_GET['id'] : null);
	
	$thumbnail = isset($_GET['thumbnail']) && $_GET['thumbnail'] == 'true' ? true : false;
	$width = isset($_GET['width']) ? intval($_GET['width']) : 200;
	$height = isset($_GET['height']) ? intval($_GET['height']) : 200;
	$retainAspect = isset($_GET['retainAspect']) && $_GET['retainAspect'] == 'true' ? true : false;
	
	if ($id == null) {
		echo('We dont have an id to work with.');
	} else {
		$id = intval($id);
		$pdo = getNewPDO();
		//attempt to find that image in the db
		$rows = runQueryPrepared($pdo, 'SELECT save_location FROM image_processed WHERE processed_id = :id;', array(':id'=>$id));
		foreach($rows as $row) {
			$imagePath = $row['save_location'];
			//if we wanted a thumbnail we create that first.
			if ($thumbnail) {
				$thumbPath = 'thumb.'.$width.'.'.$height.'.'.$imagePath;
				if (!file_exists($DB_IMAGE_LOCATION.$thumbPath))
					image_createThumbnail($DB_IMAGE_LOCATION.$imagePath, $DB_IMAGE_LOCATION.$thumbPath, $width, $height, $retainAspect);
				$imagePath = $thumbPath;
			}
			if (!getImage($imagePath)) {
				echo('Image not found.');
			}
			die();
		}
		//if we got to here then we didnt find any
		echo('No matching id found');
	}
?>