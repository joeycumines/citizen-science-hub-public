<?php
	/*
		Implementation of helper methods such as server side image parsing, and image retrieval.
		
		REQUIRES:
		- session_start();
		- /www/php/db_connection.php
		
		USES PHP 5.3+ METHODS
	*/
	
	/**
		Echos the image from the folder (so use relative paths.). Don't send any html.
		Returns true if we found the image, else false.
		
		In addition to this, we handle zip files as file transfers.
	*/
	function getImage($filename) {
		global $DB_IMAGE_LOCATION;
		//we need to sanitise filename.
		
		//trim and strip
		$filename = validate($filename);
		//use basename to get the end segment
		$filename = basename($filename);
		
		//double check
		
		//explode by '/' then get last segment
		$temp = explode('/', $filename);
		$filename = $temp[count($temp)-1];
		
		//explode by '\' then get last segment
		$temp = explode('\\', $filename);
		$filename = $temp[count($temp)-1];
		
		if (empty($filename))
			return false;
		
		//set the actual path
		$filename = $DB_IMAGE_LOCATION . $filename;
		
		//check our file extension
		$temp = explode('.', $filename);
		$fT = strtolower($temp[count($temp)-1]);
		
		if (file_exists($filename)) {
			if (false){//if ($fT == 'zip') {
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename='.basename($filename));
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				ob_clean();
				flush();
				readfile($filename);
			} else {
				//this should automatically handle
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$contentType = finfo_file($finfo, $filename);
				finfo_close($finfo);
				header('Content-Type: ' . $contentType);
				readfile($filename);
			}
			return true;
		}
		//we failed
		return false;
	}
	
	/**
		Create a thumbnail from a image.
	*/
	function image_createThumbnail($filepath, $thumbpath, $thumbnail_width, $thumbnail_height, $retainAspect=false, $background=false) {
		list($original_width, $original_height, $original_type) = getimagesize($filepath);
		if ($original_width > $original_height) {
			$new_width = $thumbnail_width;
			$new_height = intval($original_height * $new_width / $original_width);
		} else {
			$new_height = $thumbnail_height;
			$new_width = intval($original_width * $new_height / $original_height);
		}
		//if we want to retain the aspect and avoid backgrounds then we set the thumbnail details
		if ($retainAspect) {
			$thumbnail_width = $new_width;
			$thumbnail_height = $new_height;
		}
		$dest_x = intval(($thumbnail_width - $new_width) / 2);
		$dest_y = intval(($thumbnail_height - $new_height) / 2);
		if ($original_type === 1) {
			$imgt = "ImageGIF";
			$imgcreatefrom = "ImageCreateFromGIF";
		} else if ($original_type === 2) {
			$imgt = "ImageJPEG";
			$imgcreatefrom = "ImageCreateFromJPEG";
		} else if ($original_type === 3) {
			$imgt = "ImagePNG";
			$imgcreatefrom = "ImageCreateFromPNG";
		} else {
			return false;
		}
		$old_image = $imgcreatefrom($filepath);
		$new_image = imagecreatetruecolor($thumbnail_width, $thumbnail_height); // creates new image, but with a black background
		// figuring out the color for the background
		if(is_array($background) && count($background) === 3) {
		  list($red, $green, $blue) = $background;
		  $color = imagecolorallocate($new_image, $red, $green, $blue);
		  imagefill($new_image, 0, 0, $color);
		// apply transparent background only if is a png image
		} else if($background === 'transparent' && $original_type === 3) {
		  imagesavealpha($new_image, TRUE);
		  $color = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
		  imagefill($new_image, 0, 0, $color);
		}
		imagecopyresampled($new_image, $old_image, $dest_x, $dest_y, 0, 0, $new_width, $new_height, $original_width, $original_height);
		$imgt($new_image, $thumbpath);
		return file_exists($thumbpath);
	}
?>