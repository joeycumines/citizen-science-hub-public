<?php
	/*
		A page accessible to all users, logged in or not, to reset their passwords. The user is prompted to enter their 
		email address, which, if it is found, will be sent a limited-life reset link to this page.
	*/
	
	/**
		The time you can reset your password by following the link.
	*/
	$RESET_TOKEN_EXPIRATION_MINUTES = 10;
	
	session_start();
	require('../../php/db_connection.php');
	require('../../php/pageRedirect.php');
	require('../../php/db_user.php');
	require('../../php/db_userPerms.php');
	require('../../php/template_default.php');
	
	//4 modes, ask for email, confirmation of email sent, password reset from email link, and complete password reset.
	$code = isset($_GET['code']) ? validate($_GET['code']) : null;
	$email = isset($_GET['email']) ? validate($_GET['email']) : null;
	$password = isset($_GET['password']) ? validate($_GET['password']) : null;
	$passwordAgain  = isset($_GET['passwordAgain']) ? validate($_GET['passwordAgain']) : null;
	
	$pageContent = '';
	$pdo = getNewPDO();
	
	if (!empty($code) && !empty($email)) {
		//we are performing reset
		//look for a matching email, reset, and check time against the db.
		$username = null;
		$dateTimeNow = date('Y-m-d H:i:s');
		$rows = runQueryPrepared($pdo, 'SELECT username FROM user WHERE email = :email AND password_reset_code = :code AND password_reset_expiration > :now
				AND password_reset_code IS NOT NULL AND password_reset_code <> \'\';',
				array(':email'=>$email, ':code'=>$code, ':now'=>$dateTimeNow));
		foreach ($rows as $row) {
			$username = $row['username'];
			break;
		}
		
		if ($username == null) {
			//we fail
			$pageContent = '<p>We were unable to reset your password, check the link you followed was correct <a href=".">click here to retry</a>.</p>';
		} else {
			//we clear the password and salt for $username, and redirect to the reset page.
			if (runUpdatePrepared($pdo, 'UPDATE user SET hash = \'\', salt = \'\' WHERE username = :username;', array(':username'=>$username))
					&& runUpdatePrepared($pdo, 'UPDATE user SET password_reset_code = NULL WHERE username = :username;', array(':username'=>$username))) {
				echoRedirectPage('new/?username='.$username);
				die();
			} else {
				$pageContent = '<p>We were unable to reset your password, check the link you followed was correct <a href=".">click here to retry</a>.</p>';
			}
		}
	} else if (!empty($email)) {
		//we are sending email then confirming
		//First we need to check if the email exists.
		$rows = runQueryPrepared($pdo, 'SELECT username FROM user WHERE email = :email;', array(':email'=>$email));
		$username = null;
		foreach($rows as $row) {
			$username = $row['username'];
			break;
		}
		if ($username == null) {
			//email did not exist
			$pageContent = '
				<p>Email address "'.$email.'" does not exist! Was that the email linked to your account with us?</p>
			';
		} else {
			//send email to $email with link to reset password
			
			//generate a new password_reset_code and set the password_reset_expiration
			$expiration = date('Y-m-d H:i:s', strtotime('+'.$RESET_TOKEN_EXPIRATION_MINUTES.' minutes'));
			$randomFactor = uniqid();
			//generates token by hashing a (non crypto safe) random uuid and the hash.
			//the reason we do this is to help prevent attacks where a man in the middle can figure out the reset code based on the time it was calculated.
			if (runUpdatePrepared($pdo, 'UPDATE user SET password_reset_code = SHA2(CONCAT(hash, :randomFactor), 256), password_reset_expiration = :expiration WHERE username = :username;'
						, array(':username'=>$username, ':expiration'=>$expiration, ':randomFactor'=>$randomFactor))) {
				//get the generated reset code
				$rows = runQueryPrepared($pdo, 'SELECT password_reset_code FROM user WHERE username = :username;', array(':username'=>$username));
				$resetCode = null;
				foreach($rows as $row) {
					$resetCode = $row['password_reset_code'];
					break;
				}
				if ($resetCode != null) {
					//try to send the email.
					$emailSubject = 'Citizen Science Hub - PASSWORD RESET: '.$username;
					$resetLink = 'http://citizen-science.ni.gy/manage/password_reset/?code='.urlencode($resetCode).'&email='.urlencode($email);
					$emailBody = 'You requested a password reset. Follow the link bellow, or copy and paste the url into your browser.

'.$resetLink;
					require('../../vendor/autoload.php');
					$transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, "ssl")
					->setUsername('citizen.science.hub.noreply@gmail.com')
					->setPassword('OUnsbnygwu28iSHkjhhd08wskjijjjjsS');
					
					$mailer = Swift_Mailer::newInstance($transport);
					
					$message = Swift_Message::newInstance($emailSubject)
					->setFrom(array('citizen.science.hub.noreply@gmail.com' => 'Citizen Science Hub NoReply'))
					->setTo(array($email))
					->setBody($emailBody);
					
					//try to send the email and handle result.
					if ($mailer->send($message)) {
						$pageContent = '
							<p>A link to reset your password has been sent to '.$email.'</p>
						';
					} else {
						$pageContent = '
							<p>We found your email, but were unable to send you mail! Please contact support if this persists.</p>
						';
					}
				} else {
					$pageContent = '
						<p>We found your email, but we were unable to generate you a reset token! Please contact support.</p>
					';
				}
			} else {
				$pageContent = '
					<p>We found your email, but we were unable to generate you a reset token! Please contact support.</p>
				';
			}
		}
	} else {
		// we are asking for email address
		$pageContent = '
					<form>
						<div class="form-group">
							<label for="email">Enter the linked email address, you will be sent a link to reset your password.</label>
							<input type="email" class="form-control" name="email" id="email" required>
						</div>
						<input type="submit" class="btn btn-default" value="Send Reset Email" />
					</form>
		';
	}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Reset Your Password</title>
		<link rel="stylesheet" href="../../css/bootstrap.min.css">
		<link rel="stylesheet" href="../../css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="../../css/whhg.css">
		<link rel="stylesheet" href="../../css/template.css">
		<?php
			echoIconAndMobileHeader(2, "\t", '../');
		?>
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
							echoNavbarRightAccount(7, "\t", '../../');
						?>
					</div>
				</div>
			</nav>
		</div>
		<div class="container">
			<div class="row">
				<div class="">
					<div class="panel panel-default">
						<div class="panel-heading"><strong>Password Reset</strong> <small></small></div>
						<div class="panel-body">
							<?php echo($pageContent); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<script src="../../js/jquery-1.12.3.min.js"></script>
		<script src="../../js/bootstrap.min.js"></script>
	</body>
</html>