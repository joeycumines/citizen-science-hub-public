<?php
	/*
		This is the homepage for the website. Mainly contains information about the site,
		without functional tools.
	*/
	
	session_start();
	require('php/db_connection.php');
	require('php/db_user.php');
	require('php/db_userPerms.php');
	require('php/template_default.php');
	require('php/pageRedirect.php');
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Citizen Science Hub - Home</title>
		<link rel="stylesheet" href="css/bootstrap.min.css">
		<link rel="stylesheet" href="css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="css/whhg.css">
		<link rel="stylesheet" href="css/template.css">
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
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<p>This is an IT capstone project where you can upload photos that will be run through image recognition algorithm (current algorithm is just a proof of concept).</p>
					<p>Everyone is welcome to use it</p>
					<p>Just simply create an account and upload as many photos as you want</p>
				</div>
			</div>
		</div>
		<script src="js/jquery-1.12.3.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
	</body>
</html>