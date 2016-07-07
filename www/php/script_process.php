<?php
	/*
		This is a simple script to allow communication between the web server and local scripts.
		Adds processes EXIF extraction and adds a processed image with link to source.
		
		GET this.
		
		sourceId=&processedPath=&authKey=
	*/
	
	require('db_connection.php');
	require('clientError.php');
	
	//disable errors so we don't break the json parser
	//ini_set('display_errors', 'Off');
	//error_reporting(0);
	
	$script_process_authCode = '';
	$result = array();
	$result['status'] = 0;
	$result['message'] = 'Success.';
	
	if (!isset($_GET['authKey']) || $script_process_authCode != $_GET['authKey']) {
		//bad auth key
		$mess = 'Your auth code was incorrect, access denied.';
		httpError(403, $mess);
		$result['status'] = 1;
		$result['message'] = $mess;
		echo(json_encode($result));
		die();
	}
	
	$sourceId = isset($_GET['sourceId']) ? validate($_GET['sourceId']) : null;
	$processedPath = isset($_GET['processedPath']) ? validate($_GET['processedPath']) : null;
	
	if ($sourceId == null || $processedPath == null) {
		//missing params
		$mess = 'You were missing parameters.';
		httpError(401, $mess);
		$result['status'] = 1;
		$result['message'] = $mess;
		echo(json_encode($result));
		die();
	}
	
	//load the source row from the database.
	try {
		$pdo = getNewPDO();
		$rows = runQueryPrepared($pdo, 'SELECT * FROM image_source WHERE source_id = :sourceId;', array(':sourceId'=>$sourceId));
		$sourceRow = null;
		foreach ($rows as $row) {
			$sourceRow = $row;
			break;
		}
		
		if ($sourceRow == null) {
			$mess = 'Your parameters were malformed.';
			httpError(401, $mess);
			$result['status'] = 1;
			$result['message'] = $mess;
			echo(json_encode($result));
			die();
		}
		
		//use this to store key-pairs
		$metadata = array();
		
		//now we can attempt to parse the EXIF data into the metadata, and extract data from the source.
		$exif = exif_read_data($DB_IMAGE_LOCATION.$sourceRow['save_location']);
		if ($exif !== false) {
			//we have data
			foreach($exif as $key=>$value) {
				$temp = json_encode($value);
				if (!empty($temp))
					$metadata[$key] = $value;
			}
		} else {
			$metadata['tags'] = null;
		}
		
		//tags field is simply extracted from the image source.
		$metadata['tags'] = array();
		$tags = explode(',', $sourceRow['tags']);
		foreach($tags as $tag) {
			$t = validate($tag);
			if (!empty($t))
				array_push($metadata['tags'], $t);
		}
		
		//add to the database.
		if (!runUpdatePrepared($pdo, 'INSERT INTO image_processed (source_id, save_location, metadata) VALUES (:sourceId, :saveLocation, :metadata);',
					array(':sourceId'=>$sourceRow['source_id'], ':saveLocation'=>$processedPath, ':metadata'=>json_encode($metadata)))) {
			$mess = 'We failed to update the database.';
			httpError(500, $mess);
			$result['status'] = 1;
			$result['message'] = $mess;
			echo(json_encode($result));
			die();
		}
		
	} catch (Exception $e) {
		$mess = 'Your parameters were malformed. '.$e->getMessage();
		httpError(401, $mess);
		$result['status'] = 1;
		$result['message'] = $mess;
		echo(json_encode($result));
		die();
	}
	
	echo(json_encode($result));
?>