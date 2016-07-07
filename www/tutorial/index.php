<?php
	/*
		This is the tutorial page.
	*/
	
	session_start();
	require('../php/db_connection.php');
	require('../php/db_user.php');
	require('../php/db_userPerms.php');
	require('../php/template_default.php');
	require('../php/pageRedirect.php');
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Citizen Science Hub - Contact Us</title>
		<link rel="stylesheet" href="../css/bootstrap.min.css">
		<link rel="stylesheet" href="../css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="../css/whhg.css">
		<link rel="stylesheet" href="../css/template.css">
		<!---    <style type="text/css">
			body{
			min-height: 2000px;
			padding-top: 70px;
			}
			.container{
			margin: 5px;
			}
			
		</style> -->
		<?php
			echoIconAndMobileHeader(2, "\t", '../');
		?>
	</head>
	
	<body>
		<div id="header">
			<?php
				//Echos the navbar
				//parameters: indent level, indent character(s), how to get to the root www directory.
				echoManageNavbar(3, "\t", '../');
			?>
		</div>
		
		<div class="jumbotron">
			<div class="container">
				<h2>Tutorials</h2>
				<p>
					Creating an account with us allows you to upload photos, to aid in research.<br>
					
					You can add our web app to your mobile homescreen, and perform various operations, as
					demonstrated in our handy video tutorials.
				</p>
				<h3 id="android">Android Tutorial</h3>
				<video width="320" height="240" controls>
					<source src="../videos/android_tutorial.mp4" type="video/mp4">
					Your browser does not support video playback.
				</video>
				<h3 id="iphone">iPhone Tutorial</h3>
				<video width="320" height="240" controls>
					<source src="../videos/iphone_tutorial.mp4" type="video/mp4">
					Your browser does not support video playback.
				</video>
				<h3 id="create">Create An Account</h3>
				<video width="320" height="240" controls>
					<source src="../videos/create_account.mp4" type="video/mp4">
					Your browser does not support video playback.
				</video>
				<h3 id="manage">Update Your Info</h3>
				<video width="320" height="240" controls>
					<source src="../videos/updating_account_info.mp4" type="video/mp4">
					Your browser does not support video playback.
				</video>
				<h3 id="upload">Upload Photos</h3>
				<video width="320" height="240" controls>
					<source src="../videos/uploading_photos.mp4" type="video/mp4">
					Your browser does not support video playback.
				</video>
				<h3 id="delete">Delete Photos</h3>
				<video width="320" height="240" controls>
					<source src="../videos/deleting_photos.mp4" type="video/mp4">
					Your browser does not support video playback.
				</video>
				<h3 id="share">Share Photos</h3>
				<video width="320" height="240" controls>
					<source src="../videos/sharing_photos.mp4" type="video/mp4">
					Your browser does not support video playback.
				</video>
			</div>
		</div>
		<script src="../js/jquery-1.12.3.min.js"></script>
		<script src="../js/bootstrap.min.js"></script>
	</body>
</html>