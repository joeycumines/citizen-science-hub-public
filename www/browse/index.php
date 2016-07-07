<?php
	/*
		This script should act as a file browser, to display and search images.
		
		FOR NOW, we simply display a big table of images for debugging purposes.
	*/
	
	session_start();
	require('../php/db_connection.php');
	require('../php/db_user.php');
	require('../php/db_userPerms.php');
	require('../php/pageRedirect.php');
	require('../php/template_default.php');
	require('../php/db_image.php');
	require('../php/exif.php');
	
	if (!user_loggedIn()) {
		//if we are not logged in, then redirect to login page.
		echoRedirectPage('../manage/signin/');
		die();
	}
	
	//get all the images for this user.
	
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Citizen Science Hub - Browse</title>
		<link rel="stylesheet" href="../css/bootstrap.min.css">
		<link rel="stylesheet" href="../css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="../css/whhg.css">
		<link rel="stylesheet" href="../css/template.css">
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
			<div class="panel panel-default">
				<div class="panel-heading"><strong>Images by <?php echo($_SESSION['USERNAME']); ?></strong></div>
				<div class="panel-body" style="overflow: scroll;">
					<div id="imageTable">
						<?php
							image_echoProcessingQueueTable();
						?>
					</div>
				</div>
			</div>
		</div>
		<script src="../js/jquery-1.12.3.min.js"></script>
		<script src="../js/bootstrap.min.js"></script>
		<script>
			var updatingTable = false;
			var updateTable = window.setInterval(function() {
				if (updatingTable == false) {
					updatingTable = true;
					var xhttp = new XMLHttpRequest();
					xhttp.onreadystatechange = function() {
						if (xhttp.readyState == 4 && xhttp.status == 200 && document.getElementById("imageTable").innerHTML != xhttp.responseText) {
							document.getElementById("imageTable").innerHTML = xhttp.responseText;
						}
						if (xhttp.readyState == 4)
							updatingTable = false;
					};
					xhttp.open("GET", "imageTable.php", true);
					xhttp.send();
				}
			}, 1000);
		</script>
	</body>
</html>