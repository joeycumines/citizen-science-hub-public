<?php
	/*
		This is the contact us page.
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
				<h2>Contact Us</h2>
				<h3>Dr Matthew Dunbabin - Sponsor</h3>
				<p><a href="mailto:m.dunbabin@qut.edu.au">m.dunbabin@qut.edu.au</a></p>
				<h3>Joseph Cumines - Developer</h3>
				<p><a href="mailto:joeycumines@gmail.com">joeycumines@gmail.com</a></p>
				<h3>Levi Davison - Project Manager</h3>
				<p><a href="mailto:Levi.davison@hotmail.com">Levi.davison@hotmail.com</a></p>
				<h3>Duy ”Jerry” Dang - Graphics and Design</h3>
				<p><a href="mailto:Duy.dangngoc@gmail.com">Duy.dangngoc@gmail.com</a></p>
				<h3>Alex Cartwright - Database Developer</h3>
				<p><a href="mailto:Alexcartwright@live.com">Alexcartwright@live.com</a></p>
			</div>
		</div>
		<script src="../js/jquery-1.12.3.min.js"></script>
		<script src="../js/bootstrap.min.js"></script>
	</body>
</html>