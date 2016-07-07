<?php
	/*
		This is the upload page for the user to upload a photo or photos.
		
		There are multiple functions to this page, and I have only (so far) implemented
		the simplest PHP-only features (upload files of .zip or image formats).
		
		Other plans:
		- JavaScript queue with ajax spray and wipe uploads
			- This includes progress bars
			- This hides the file inputs, instead using labels as buttons.
		- Integrate location data into the JavaScript queue
			- This will POST location data as well
			- Has a checkbox to include to indicate that you took the photo at the current location
				- Automatically checked if you hit "take photo"
	*/
	session_start();
	require('../../php/db_connection.php');
	require('../../php/db_user.php');
	require('../../php/pageRedirect.php');
	require('../../php/db_userPerms.php');
	require('../../php/db_upload.php');
	require('../../php/template_default.php');
	require('../../php/queue_tools.php');
	
	//if we were doing a ajax query to this page, then we would have to perform work then die.
	
	/**
		Uploads files from POST, echoing bootstrap alert boxes as we go.
	*/
	function attemptUpload() {
		if (isset($_POST['upload']) && $_POST['upload'] = 'true') {
			echoBSAlertBox(2, "\t", 'success', 'glyphicon-upload', 'Upload Started', 'We attempted an upload, details displayed bellow.');
			//for every file we attempted to upload, we build a alert box
			foreach($_FILES['myfile']['name'] as $key=>$name) {
				if (empty($name))
					continue;
				$result = handleUpload($key);
				if ($result['success'] == true) {
					//add to queue
					$addedToQueue = queue_add($result['result']);
					echoBSAlertBox(2, "\t", 'success', 'glyphicon-ok', 'Success', 'We successfully uploaded <a href="../../browse/image/source/?id='.$result['result'].'">'.$name.
							'</a>, '.($addedToQueue ? 'added to processing queue' : 'not added to processing queue').'! <a href="../../browse/">Click here to view the processing queue.</a>');
				} else {
					echoBSAlertBox(2, "\t", 'danger', 'glyphicon-exclamation-sign', 'Error', 'Unable to upload '.$name.', we had error: '.$result['result']);
				}
			}
		}
	}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Upload Photos</title>
		<link rel="stylesheet" href="../../css/bootstrap.min.css">
		<link rel="stylesheet" href="../../css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="../../css/whhg.css">
		<link rel="stylesheet" href="../../css/template.css">
		<!--        <style type="text/css">
			body{
			min-height: 2000px;
			padding-top: 70px;
			}
			.container{
			margin: 5px;
			}
			
		</style>-->
		
		<?php
			echoIconAndMobileHeader(2, "\t", '../');
		?>
	</head>
	<body>
		<div id="header">
			<?php
				//Echos the navbar
				//parameters: indent level, indent character(s), how to get to the root www directory.
				echoManageNavbar(3, "\t", '../../');
			?>
		</div>
		<img src="../../images/progressbar.gif" alt="progressgif" style="display:none" />
		<!-- Notifications from upload. -->
		<?php
			//for every file we attempt to create a notification box. This is hacky.
			attemptUpload();
		?>
		<div class="container">
			<div class="row">
				<div class="">
					<div class="panel panel-default">
						<div class="panel-heading"><strong>Upload Files</strong> <small>You can take a photo, upload multiple photos, or upload a .zip file with multiple photos.</small></div>
						<div class="panel-body">
							
							<!-- Standard Form -->
							<h4>Select files to upload</h4>
							<form method="POST" id="uploadForm" onsubmit="return validate();" enctype="multipart/form-data">
								<input type="hidden" name="upload" value="true">
								<div class="form-group">
									<label for="cameraFile">
										<span class="glyphicon glyphicon-camera" aria-hidden="true"></span>
										Take a photo
										<!-- When I write the JS for this page, this will be hidden, and the total queue for upload will appear down the bottom. -->
										<input type="file" class="form-control" name="myfile[]" id="cameraFile" accept="image/*">
									</label>
								</div>
								<div class="form-group" id="multiFileUploadDiv">
									<label for="myfile">File Upload</label>
									<input type="file" class="form-control" name="myfile[]" id="myfile" multiple>
								</div>
								<div class="form-group">
									<label for="tags">Enter image tags separated by commas E.g: crown of thorns, underwater, seaweed, etc.</label>
									<input type="text" class="form-control" name="tags" id="tags">
								</div>
								<div class="form-group" id="useGPSCoords"></div>
								<input type="submit" class="btn btn-default" value="Upload" />
								<input type="button" class="btn btn-default" value="Cancel" onclick="" />
							</form>
							<hr>
							<!--
							
							I will have to implement this as JavaScript. By writing it first in php only I can have noJS support as well.
							
							<div id="progress-group">
								<div class="progress">
									<div class="progress-bar" style="width: 60%;">
										File name
									</div>
									<div class="progress-text">
										Process
									</div>
								</div>
								<div class="progress">
									<div class="progress-bar" style="width: 40%;">
										File name
									</div>
									<div class="progress-text">
										Process
									</div>
								</div>
							</div>
							-->
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<script src="../../js/jquery-1.12.3.min.js"></script>
		<script src="../../js/bootstrap.min.js"></script>
		<!-- <script type="text/javascript" src="../../js/function.js"></script> I will address JS upload later. -->
		<script>
function validate() {
	if (document.getElementById('cameraFile').value == '' && document.getElementById('myfile').value == '') {
		alert('You must upload a file.');
		return false;
	}
	return true;
}

/**
	Saves the current gps position that was fetched using javascript, into the form's hidden fields.
*/
function savePosition(position) {
	document.getElementById("useDeviceLocation").checked = true;
	document.getElementById('uploadLat').value = position.coords.latitude;
	document.getElementById('uploadLng').value = position.coords.longitude;
	document.getElementById('uploadAcc').value = position.coords.accuracy;
	document.getElementById('uploadAlt').value = position.coords.altitude;
	document.getElementById('uploadAac').value = position.coords.altitudeAccuracy;
	document.getElementById('uploadH').value = position.coords.heading;
	document.getElementById('uploadS').value = position.coords.speed;
}

//On change of the cameraFile input field change, display our checkbox to enable gps coord fetching
$("document").ready(function(){
	$("#cameraFile").change(function() {
		//enable the checkbox for our image upload & hide the other file input if we select take image
		var checkboxDiv = document.getElementById('useGPSCoords');
		if (checkboxDiv.innerHTML == '') {
			checkboxDiv.innerHTML = '<label for="useDeviceLocation">Use current location?</label>'
					+'<input type="checkbox" class="form-control" name="useDeviceLocation" id="useDeviceLocation" value="true">'
					+'<input type="hidden" value="" name="uploadLat" id="uploadLat">'
					+'<input type="hidden" value="" name="uploadLng" id="uploadLng">'
					+'<input type="hidden" value="" name="uploadAcc" id="uploadAcc">'
					+'<input type="hidden" value="" name="uploadAlt" id="uploadAlt">'
					+'<input type="hidden" value="" name="uploadAac" id="uploadAac">'
					+'<input type="hidden" value="" name="uploadH" id="uploadH">'
					+'<input type="hidden" value="" name="uploadS" id="uploadS">';
			document.getElementById('multiFileUploadDiv').style.display = 'none';
		}
		
		//We load the gps if we can.
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(savePosition,
				function(error){
					 alert(error.message);
				}, {
					 enableHighAccuracy: true
						  ,timeout : 5000
				}
			);
		}
	});
});
		</script>
	</body>
</html>