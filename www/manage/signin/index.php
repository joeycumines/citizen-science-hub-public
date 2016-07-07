<?php
	/*
		This page is for user signin.
	*/
	session_start();
	require('../../php/db_connection.php');
	require('../../php/db_user.php');
	require('../../php/template_default.php');
	require('../../php/pageRedirect.php');
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<title>Sign in</title>
		
		<link rel="stylesheet" href="../../css/bootstrap.min.css">
		<link rel="stylesheet" href="../../css/bootstrap-theme.min.css">
		<?php
			echoIconAndMobileHeader(2, "\t", '../');
		?>
	</head>
	<body>
		<div class="container">
			<?php
				echoSigninForm(3, "\t", '../../', '../');
			?>
		</div> <!-- /container -->
		
		<script src="../../js/jquery-1.12.3.min.js"></script>
		<script src="../../js/bootstrap.min.js"></script>
	</body>
</html>

