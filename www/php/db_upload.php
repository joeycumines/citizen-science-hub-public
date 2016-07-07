<?php
	/*
		This script contains methods to facilitate uploading of images.
		Incorporate these methods to enable uploading.
		
		Things to note:
		- db_connection.php contains $DB_IMAGE_LOCATION global
			- All images in the server will be stored in this one folder.
			- Php should have read/write access to this folder.
			- If this folder does not exits, php tries to create it.
			
		REQUIRES INCLUDE:
		- /www/php/db_connection.php
		- /www/php/db_user.php
		- /www/php/db_userPerms.php
		
		REQUIRES SESSION (logged in user)
	*/
	
	/**
		Can we access the image storage folder?
		Returns true/false, tries to create it 
		if it does not exist.
		
		Currently not implemented, just creates folder if it needs to.
	*/
	function canAccessImageFolder() {
		global $DB_IMAGE_LOCATION;
		$result = true;
		
		if ( !file_exists($DB_IMAGE_LOCATION) ) {
			$oldmask = umask(0);  // helpful when used in linux server, apparently
			mkdir ($DB_IMAGE_LOCATION, 0744);
		}
		
		return $result;
	}
	
	/**
		Uploads a file to the image_source folder. Uses $_POST and $_FILES.
		Returns an array, with fields success(bool) and result(mixed).
		
		Result will either be the source_id(int) or a error(string).
		
		***NOTE***
		The file is already uploaded by this point, using default php POST handling.
		
		Supports multiple file types, namely .zip and images.
		
		Form structure:
		myfile = file we are uploading
		uploadLat = the lat double from js getCurrentPosition()
		uploadLng = the lng double from js getCurrentPosition()
		uploadAcc = the accuracy double from js getCurrentPosition()
		uploadAlt = the altitude double from js getCurrentPosition()
		uploadAac = the altitude acc double from js getCurrentPosition()
		uploadH = the heading double from js getCurrentPosition()
		uploadS = the speed double from js getCurrentPosition()
		tags = the tags we want to add, as a comma delineated string
	*/
	function handleUpload($fileIndex, $pdo = null) {
		global $DB_IMAGE_LOCATION;
		$result = array();
		$result['success'] = false;
		$result['result'] = 'Error occurred with upload.';
		
		if ($pdo == null) {
			//make a new pdo connection
			$pdo = getNewPDO();
		}
		
		//check if we are logged in
		if (!user_loggedIn($pdo)) {
			$result['result'] = 'Error occurred with upload: Not logged in.';
			return $result;
		}
		//check we can upload
		if (!canUserUpload($_SESSION['USERNAME'], $pdo)) {
			$result['result'] = 'Error occurred with upload: No permission to upload.';
			return $result;
		}
		
		//check we can access the image folder
		if (!canAccessImageFolder()) {
			$result['result'] = 'Error occurred with upload: Unable to access image folder.';
			return $result;
		}
		
		$uf = 'myfile'; //just a shortcut
		
		//have we uploaded a file?
		if (!isset($_FILES[$uf])) {
			$result['result'] = 'Error occurred with upload: No file was uploaded.';
			return $result;
		}
		
		$useDeviceLocation = isset($_POST['useDeviceLocation']) && $_POST['useDeviceLocation'] == 'true' ? true : false;
		
		//try to add the image we uploaded
		//first we get our posted details
		$uploadLat = validate(isset($_POST['uploadLat']) && $useDeviceLocation ? $_POST['uploadLat'] : null);
		if ($uploadLat != null)
			$uploadLat = floatval($uploadLat);
			
		$uploadLng = validate(isset($_POST['uploadLng']) && $useDeviceLocation ? $_POST['uploadLng'] : null);
		if ($uploadLng != null)
			$uploadLng = floatval($uploadLng);
		
		$uploadAcc = validate(isset($_POST['uploadAcc']) && $useDeviceLocation ? $_POST['uploadAcc'] : null);
		if ($uploadAcc != null)
			$uploadAcc = floatval($uploadAcc);
		
		$uploadAlt = validate(isset($_POST['uploadAlt']) && $useDeviceLocation ? $_POST['uploadAlt'] : null);
		if ($uploadAlt != null)
			$uploadAlt = floatval($uploadAlt);
		
		$uploadAac = validate(isset($_POST['uploadAac']) && $useDeviceLocation ? $_POST['uploadAac'] : null);
		if ($uploadAac != null)
			$uploadAac = floatval($uploadAac);
		
		$uploadH = validate(isset($_POST['uploadH']) && $useDeviceLocation ? $_POST['uploadH'] : null);
		if ($uploadH != null)
			$uploadH = floatval($uploadH);
		
		$uploadS = validate(isset($_POST['uploadS']) && $useDeviceLocation ? $_POST['uploadS'] : null);
		if ($uploadS != null)
			$uploadS = floatval($uploadS);
		
		$tags = validate(isset($_POST['tags']) ? $_POST['tags'] : null);
		if ($tags == null)
			$tags = '';
		
		//Get the current datetime in mysql string format
		//YYYY-MM-DD HH:MM:SS
		$uploadDt = date('Y-m-d H:i:s');
		
		//get the original file name
		$uploadFn = basename($_FILES[$uf]["name"][$fileIndex]);
		
		//we have all the fields to upload now, but we need to:
			//check the file is an image OR has the .zip extension
			//determine a new (random) file name.
			//Move the file to the image folder
			//upload details to the database
		
		//This is changed later, target path
		$target_file = $DB_IMAGE_LOCATION . $uploadFn;
		$uploadOk = 1;
		$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
		//allow only .zip OR an image file.
		if(strtolower($imageFileType) != "zip") {
			//we are not zip, try to check the image size.
			$check = getimagesize($_FILES[$uf]["tmp_name"][$fileIndex]);
			if($check !== false) {
				//file is an image
			} else {
				$result['result'] = 'Error occurred with upload: File was not a .zip or valid image.';
				return $result;
			}
		}
		
		//set the target to our new path
		$tempP = explode('.', $target_file);
		$fT = $tempP[count($tempP)-1];
		$newFName = date("Ymd") . '-' . uniqid() . '.' . $fT;
		$target_file = $DB_IMAGE_LOCATION . $newFName;
		
		//check if file already exists
		if (file_exists($target_file)) {
			$result['result'] = 'Error occurred with upload: A conflict occurred, please try again.';
			return $result;
		}
		
		//Check file size (60 Megabyte, also the current limit on dev server php.ini)
		if ($_FILES[$uf]["size"][$fileIndex] > 60000000) {
			$result['result'] = 'Error occurred with upload: The file upload limit was exceeded. Try a smaller file, or less files in the zip.';
			return $result;
		}
		
		//if we got to here, then the upload is ok so far.
		if (move_uploaded_file($_FILES[$uf]["tmp_name"][$fileIndex], $target_file)) {
			//we now need to send the details to the database.
			$success = runUpdatePrepared($pdo, 'INSERT INTO image_source (username, save_location, uploaded_lat, 
					uploaded_lng, uploaded_acc, uploaded_alt, uploaded_aac, uploaded_h, uploaded_s, uploaded_dt, uploaded_fn, tags) 
					VALUES (:username, :targetFile, :uploadLat, :uploadLng, :uploadAcc, :uploadAlt, :uploadAac, 
					:uploadH, :uploadS, :uploadDt, :uploadFn, :tags);', 
					array(':username'=>$_SESSION['USERNAME'], ':targetFile'=>$newFName, ':uploadLat'=>$uploadLat,
					':uploadLng'=>$uploadLng, ':uploadAcc'=>$uploadAcc, ':uploadAlt'=>$uploadAlt, 
					':uploadAac'=>$uploadAac, ':uploadH'=>$uploadH, ':uploadS'=>$uploadS, ':uploadDt'=>$uploadDt, 
					':uploadFn'=>$uploadFn, ':tags'=>$tags));
			if (!$success) {
				//we failed to insert into the db.
				$result['result'] = 'Error occurred with upload: Failed to add to the database. Contact support.';
				return $result;
			} else {
				//we succeeded!
				$lastId = $pdo->lastInsertId();
				$result['result'] = intval($lastId);
				$result['success'] = true;
				return $result;
			}
		} else {
			//there was an error moving the file between directories.
			$result['result'] = 'Error occurred with upload: Unable to create file on server.';
			return $result;
		}
		
		//we should never get here
		$result['result'] = 'Error occurred with upload: Script failure.';
		$result['success'] = false;
		return $result;
	}
?>