<?php
	/*
		User account page.
		
		We have a very simple form to update your details, with a link to change your password.
		We also have a button to delete your account, that relies on JavaScript.
	*/
	
	session_start();
	require('../../php/db_connection.php');
	require('../../php/db_user.php');
	require('../../php/db_userPerms.php');
	require('../../php/pageRedirect.php');
	require('../../php/template_default.php');
	
	//redirect to signin if we are not.
	if (!user_loggedIn()) {
		echoRedirectPage('../signin/');
		die();
	}
	
	$user = user_getUser();
	
	//update if we need
	$doUpdate = isset($_POST['doUpdate']) && $_POST['doUpdate'] == 'true' ? true : false;
	$firstName = validate(isset($_POST['first_name']) ? $_POST['first_name'] : null);
	$lastName = validate(isset($_POST['last_name']) ? $_POST['last_name'] : null);
	$organisation = validate(isset($_POST['organisation']) ? $_POST['organisation'] : null);
	$email = validate(isset($_POST['emailaddress']) ? $_POST['emailaddress'] : null);
	
	$messageHtml = '';
	
	//we don't require org but do require the rest
	if ($doUpdate && $firstName != null && $lastName != null && $email != null) {
		$pdo = getNewPDO();
		if (runUpdatePrepared($pdo, 'UPDATE user SET fname = :fname, lname = :lname, organisation = :organisation, email = :email WHERE username = :username;', 
					array(':username'=>$user['username'], ':fname'=>$firstName, ':lname'=>$lastName, ':organisation'=>$organisation, ':email'=>$email))) {
			$messageHtml = '
			<div class="alert alert-success">
				<strong>Success</strong> Successfully updated your details.
			</div>
			';
			$user = user_getUser();
		} else {
			$messageHtml = '
			<div class="alert alert-danger">
				<strong>Error</strong> We failed to update your details, the selected email may be in use.
			</div>
			';
		}
	}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Citizen Science Hub - <?php echo($user['username']); ?></title>
		<link rel="stylesheet" href="../../css/bootstrap.min.css">
		<link rel="stylesheet" href="../../css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="../../css/whhg.css">
		<link rel="stylesheet" href="../../css/template.css">
		<?php
			echoIconAndMobileHeader(2, "\t", '../../');
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
		<div class="container">
			<?php echo($messageHtml); ?>
			<div class="panel panel-default">
				<div class="panel-heading"><strong>Your Account: '<?php echo($user['username']); ?>'</strong></div>
				<div class="panel-body">
					<form role="form" method="POST" id="editAccountForm">
						<input type="hidden" name="doUpdate" value="true">
						<div class="form-group">
							<label for="first_name" class="col-md-2">
								First name:
							</label>
							<div class="col-md-10">
								<input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter your first name" value="<?php echo($user['fname']); ?>" required>
							</div>
						</div>
						<div class="form-group">
							<label for="last_name" class="col-md-2">
								Last name:
							</label>
							<div class="col-md-10">
								<input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter your last name" value="<?php echo($user['lname']); ?>" required>
							</div>
							
						</div>
						<div class="form-group">
							<label for="organisation" class="col-md-2">
								Organisation:
							</label>
							<div class="col-md-10">
								<input type="text" class="form-control" id="organisation" name="organisation" placeholder="Enter your organisation (or leave blank)" value="<?php echo($user['organisation']); ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="emailaddress" class="col-md-2">
								Email address:
							</label>
							<div class="col-md-10">
								<input type="email" class="form-control" id="emailaddress" name="emailaddress" placeholder="Enter email address" value="<?php echo($user['email']); ?>" required>
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-2">
							</div>
							<div class="col-md-10">
								<button type="submit" class="btn btn-info">
									Update Info
								</button>
							</div>
						</div>
						
					</form>
					<br>
					<div class="row">
						<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
							<div class="btn-group btn-group-justified">
								<a href="../password_reset" class="btn btn-large btn-block btn-warning">Reset Password</a>
							</div>
							<br>
						</div>
						<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
							<div class="btn-group btn-group-justified">
								<a href="#" onclick="deleteAccountClick();" class="btn btn-large btn-block btn-danger">Delete Account</a>
								<script>
if (typeof location.origin === 'undefined')
	location.origin = location.protocol + '//' + location.host;
function deleteAccountClick() {
	if (confirm('Are you sure you want to delete you account and all associated data?')) {
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (xhttp.readyState == 4 && xhttp.status == 200) {
				alert(xhttp.responseText);
				if (xhttp.responseText == "User was successfully deleted")
					window.location.href = location.origin + "/manage/signout/?redirect=/";
			}
		};
		xhttp.open("GET", "delete.php?sourceId='.$details['source_id'].'", true);
		xhttp.send();
	}
};
								</script>
							</div>
							<br>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script src="../../js/jquery-1.12.3.min.js"></script>
		<script src="../../js/bootstrap.min.js"></script>
	</body>
</html>