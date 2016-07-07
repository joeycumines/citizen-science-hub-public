<?php
	/*
		Password reset page. Only works if the user's password and salt have both been set to empty.
	*/
	
	require('../../../php/db_connection.php');
	require('../../../php/db_user.php');
	require('../../../php/db_userPerms.php');
	require('../../../php/template_default.php');
	
	$error = null;
	
	$congrat = null;
	
	//get the username from POST or GET
	$username = isset($_GET['username']) ? validate($_GET['username']) : (isset($_POST['username']) ? validate($_POST['username']) : null);
	
	//perform our password reset
	$userPass = validate(isset($_POST["userPass"]) ? $_POST["userPass"] : null);
	$userPassConfirm = validate(isset($_POST["userPassConfirm"]) ? $_POST["userPassConfirm"] : null);
	
	//if we want to reset
	if (!empty($userPass) && !empty($username) && !empty($userPassConfirm)) {
		//if the passwords are not the same, we just give an error
		if ($userPass != $userPassConfirm) {
			$error = 'Your new password must match.';
		} else {
			$pdo = getNewPDO();
			//check to see if we have empty hash and salt
			$rows = runQueryPrepared($pdo, 'SELECT username FROM user WHERE username = :username AND hash = \'\' AND salt = \'\';', array(':username'=>$username));
			$check = null;
			foreach ($rows as $row) {
				$check = $row['username'];
				break;
			}
			
			if ($check == $username) {
				//if we have matching new passwords, and we have set the flag to reset.
				//reset the password
				$userSalt = uniqid();
				$updated = false;
				try {
					$updated = runUpdatePrepared(getNewPDO(), 'UPDATE user SET hash = SHA2(CONCAT(:userPass, :userSalt), 256), salt = :userSalt WHERE username = :userName;', 
						array(':userSalt' => $userSalt, ':userName' => $username, ':userPass' => $userPass));
				} catch (PDOException $e) {
					$updated = false;
				}
				if ($updated) {
					$congrat = 'Successfully changed your password!';
				} else {
					$error = 'There was a database error, while updating the passwords.';
				}
			} else {
				$error = 'You are not able to reset your password at this time.';
			}
		}
	}
	
	$errorBox = '';
	if (!empty($error)) {
		$errorBox = '
			<div class="alert alert-danger" role="alert">
				<strong>Oops! </strong>'.$error.'
			</div>
';
	}
	if (!empty($congrat)) {
		$errorBox = '
			<div class="alert alert-success" role="alert">
				<strong>Congratulations! </strong>'.$congrat.'
			</div>
';
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
		<meta name="description" content="Password reset page based on the login portal">
		<meta name="author" content="Joey">
		
		<title>Password Reset</title>

		<link rel="stylesheet" href="../../../css/bootstrap.min.css">
		<link rel="stylesheet" href="../../../css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="../../../css/whhg.css">
		<link rel="stylesheet" href="../../../css/template.css">
		
	</head>

	<body>
		<div id="header">
			<nav class="navbar navbar-inverse navbar-static-top navbar-fixed-top">
				<div class="container"> 
					<div class="navbar-header">
						<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#menu">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
					</div>
					<div class="navbar-collapse collapse" id="menu">
						<ul class="nav navbar-nav">
							<li><a href="../../">Home</a></li>
						</ul>
						
						<?php
							//echos user account info, or login boxes.
							echoNavbarRightAccount(7, "\t", '../../../');
						?>
					</div>
				</div>
			</nav>
		</div>
		<div class="container">
			<?php echo($errorBox); ?>
			<form class="form-signin" method="POST" action=".">
				<input type="hidden" name="username" value="<?php if (!empty($username)) echo($username); ?>">
				<h2 class="form-signin-heading">Password Reset is Required</h2>
				<label for="inputEmail" class="sr-only">Enter Password</label>
				<input name="userPass" type="password" id="inputEmail" class="form-control" placeholder="new password" required autofocus>
				<label for="inputPassword" class="sr-only">Confirm</label>
				<input name="userPassConfirm" type="password" id="inputPassword" class="form-control" placeholder="confirm password" required>
				<button class="btn btn-lg btn-primary btn-block" type="submit">Continue</button>
			</form>

		</div> <!-- /container -->
		
		<script src="../../../js/jquery-1.12.3.min.js"></script>
		<script src="../../../js/bootstrap.min.js"></script>

	</body>
</html>