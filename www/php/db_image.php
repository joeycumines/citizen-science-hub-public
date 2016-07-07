<?php
	/*
		Tools for image management in the database.
		
		For tools to manipulate the actual files, see php/db_imageTools.php.
		
		NOTES:
			- In the way we have implemented this, we intend to support multiple processing of images. 
			- If any of the processed records are deleted then we will be unable to access any.
			- These methods are more a stop gap then anything else; in the event that this tool was put
					into production, then we would be developing advanced search algorithms and the like.
	*/
	
	/**
		Delete a image record from the database. Doesn't actually delete anything, just sets the flag to deleted.
		
		Flag is set for all images with the same source id, because we now store the flag in the source table.
		
		Only works if the username is the same as the image uploader.
		
		$username:
			- The user that is performing the action.
		$imageId:
			- The processed_id of the image, in the table 'image_processed'.
	*/
	function image_delete($username, $sourceId, $pdo = null) {
		if (empty($username) || empty($sourceId)) {
			return false;
		}
		
		//make db con if we need
		if ($pdo == null) {
			$pdo = getNewPDO();
		}
		
		return runUpdatePrepared($pdo, 'UPDATE image_source SET deleted = 1 WHERE username = :username AND source_id = :sourceId;', array(':username'=>$username, ':sourceId'=>$sourceId));
	}
	
	/**
		Flag a given image.
	*/
	function image_flag($username, $sourceId, $reason, $pdo = null) {
		if (empty($username) || empty($sourceId) || empty($reason)) {
			return false;
		}
		
		//make db con if we need
		if ($pdo == null) {
			$pdo = getNewPDO();
		}
		
		//delete any existing flag
		runUpdatePrepared($pdo, 'DELETE FROM image_flags WHERE username = :username AND source_id = :sourceId;', array(':username'=>$username, ':sourceId'=>$sourceId));
		
		return runUpdatePrepared($pdo, 'INSERT INTO image_flags (source_id, username, reason) VALUES (:sourceId, :username, :reason);', 
				array(':username'=>$username, ':sourceId'=>$sourceId, ':reason'=>$reason));
	}
	
	/**
		Given the username of a user, we get all the images for them, ordered by date in descending chrono order.
		
		DOES NOT TAKE INTO ACCOUNT
		- Images from deleted user accounts
		- Images which have been deleted
		
		$username:
			- The username of the user we wish to get.
			
		RETURNS:
			An array of rows, representing the images in the database.
			Row fields:
				- source_id, uploaded_fn, uploaded_dt (direct from database image_source)
				- processed_ids : [<processed ids we have for this source file>]
					- The 
	*/
	function image_getForUser($username, $pdo = null) {
		//make db con if we need
		if ($pdo == null) {
			$pdo = getNewPDO();
		}
		
		$result = array();
		
		//check if user exists and is not deleted
		//Get all source images that are not deleted, and the processed id for them.
		$sql = 'SELECT source_id, uploaded_dt, uploaded_fn FROM image_source WHERE username IN (SELECT username FROM user WHERE username = :username AND deleted = 0) AND deleted = 0 ORDER BY uploaded_dt desc;';
		$rows = runQueryPrepared($pdo, $sql, array(':username'=>$username));
		
		foreach ($rows as $row) {
			$sourceId = $row['source_id'];
			$proc = runQueryPrepared($pdo, 'SELECT processed_id FROM image_processed WHERE source_id = :sourceId;', array(':sourceId'=>$sourceId));
			$temp = array();
			$temp['source_id'] = $row['source_id'];
			$temp['uploaded_dt'] = $row['uploaded_dt'];
			$temp['uploaded_fn'] = $row['uploaded_fn'];
			$temp['processed_ids'] = array();
			foreach ($proc as $procRow) {
				array_push($temp['processed_ids'], $procRow['processed_id']);
			}
			
			array_push($result, $temp);
		}
		
		return $result;
	}
	
	/**
		Gets the details for a image, requires a processed id.
		We will return all of the data for that image.
		
		RETURNS:
		- on failure
			- null
		- on success
			- an array 
				- where ['source'] = row from the source table
				- ['processed'] = row from the processed table
	*/
	function image_getDetails($username, $processedId, $pdo = null) {
		if ($pdo == null) {
			$pdo = getNewPDO();
		}
		$sql = '
SELECT 
p.processed_id AS processed_id,
p.source_id AS source_id,
p.save_location AS processed_save_location,
p.metadata AS metadata,
s.username AS username,
s.save_location AS source_save_location,
s.uploaded_lat AS uploaded_lat,
s.uploaded_lng AS uploaded_lng,
s.uploaded_acc AS uploaded_acc,
s.uploaded_alt AS uploaded_alt,
s.uploaded_aac AS uploaded_aac,
s.uploaded_h AS uploaded_h,
s.uploaded_s AS uploaded_s,
s.uploaded_dt AS uploaded_dt,
s.uploaded_fn AS uploaded_fn,
s.tags AS tags,
s.temperature AS temperature,
s.salinity AS salinity,
s.depth AS depth,
s.altitude AS altitude,
s.light AS light

FROM image_processed p 
LEFT JOIN image_source s ON p.source_id=s.source_id WHERE p.processed_id = :processedId AND s.deleted = 0 LIMIT 1;
		';
		
		$rows = runQueryPrepared($pdo, $sql, array(':processedId'=>$processedId));
		foreach($rows as $row) {
			return $row;
		}
		
		return null;
	}
	
	/**
		Returns an array of all flagged image rows (processed).
		
		The result is an array ordered by number of flags desc.
	*/
	function image_flagged() {
		
		return null;
	}
	
	/**
		Echos a table representing the processing queue for the current user. Does not checked to see if logged in.
	*/
	function image_echoProcessingQueueTable() {
		if (!isset($_SESSION['USERNAME']))
			return;
		$images = image_getForUser($_SESSION['USERNAME']);
		//echo a table.
		echo('<table class="table table-striped table-condensed"><thead><tr><th>Image</th><th>Timestamp</th><th>Status</th></tr></thead><tbody>');
		foreach($images as $row) {
			echo('<tr><td><a href="image/source/?id='.$row['source_id'].'">'.$row['uploaded_fn'].'</a></td><td>'.$row['uploaded_dt'].'</td>');
			if (!empty($row['processed_ids'])) {
				//we have processed, give links
				echo('<td>');
				foreach($row['processed_ids'] as $proc) {
					echo('<a href="image/?id='.$proc.'">processed! </a>');
				}
				echo('</td>');
			} else {
				//just say processing
				echo('<td>processing</td>');
			}
			echo('</tr>');
		}
		echo('</tbody></table>');
	}
?>