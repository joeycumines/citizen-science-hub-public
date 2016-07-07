<?php
	/*
		This page is for creating new user accounts.
		The form calls POST to itself.
		
		ADDITIONALLY:
		- if the doAjax field is set, it will echo a JSON for use with JS powered account creation.
		
		Result Object:
		- Used in php, and if is enabled, JS.
		- Structure
			- status
				- 0 or 1, 0 being ok 1 being error.
			- messages
				- messageTitle
				- messageText
				- messageIcon
				- messageType
				- messageId
					- The id of the dom element to display error above. Uses names so name and id must match.
	*/
	
	session_start();
	require('../../php/db_connection.php');
	require('../../php/db_user.php');
	require('../../php/db_userPerms.php');
	require('../../php/pageRedirect.php');
	require('../../php/template_default.php');
	
	
	
	/**
		Form error message template.
	*/
	function newFormErrorMessage($messageTitle, $messageText, $messageIcon, $messageType, $messageId) {
		$result = array();
		$result['messageTitle'] = $messageTitle;
		$result['messageText'] = $messageText;
		$result['messageIcon'] = $messageIcon;
		$result['messageType'] = $messageType;
		$result['messageId'] = $messageId;
		return $result;
	}
	
	/**
		Echos a form error in the event we don't have JS working.
	*/
	function echoFormError($errorId, $obj) {
		//if we didn't have obj exit
		if ($obj == null)
			return;
		
		//for every message we had in error
		foreach ($obj['messages'] as $message) {
			if ($message['messageId'] == $errorId) {
				echoBSAlertBox(0, "\t", $message['messageType'], $message['messageIcon'], 
					$message['messageTitle'], $message['messageText']);
			}
		}
	}
	
	/**
		The result object for our create.
	*/
	$result = null;
	
	//if we submit the form
	if (isset($_POST['doCreate']) && $_POST['doCreate'] == 'true') {
		
		//we now attempt to build a result object. 
		$result = array();
		$result['messages'] = array();
		$result['status'] = 0;
		
		if (!isset($_POST['doAjax']) || !isset($_POST['username']) || !isset($_POST['first_name']) ||
				!isset($_POST['last_name']) || !isset($_POST['organisation']) || !isset($_POST['emailaddress']) ||
				!isset($_POST['password']) || !isset($_POST['password_again']) || empty($_POST['doAjax']) || 
				empty($_POST['username']) || empty($_POST['first_name']) ||
				empty($_POST['last_name']) || empty($_POST['emailaddress']) ||
				empty($_POST['password']) || empty($_POST['password_again'])) {
			//something went wrong with the form structure
			//'Something went wrong, please try again, or contact an admin.'
			array_push($result['messages'], newFormErrorMessage('Error', 'Something went wrong, please make sure you completed all the fields and try again, or contact an admin.', 
					'glyphicon-exclamation-sign', 'danger', 'doCreate'));
			$result['status'] = 1;
		} else {
			//check to see if user exists
			$userDetails = user_getUser($_POST['username']);
			if ($userDetails == null) {
				if ($_POST['password'] != $_POST['password_again']) {
					//if we had passwords that didnt match.
					array_push($result['messages'], newFormErrorMessage('Error', 'Your passwords did not match!', 
						'glyphicon-exclamation-sign', 'danger', 'password_again'));
					$result['status'] = 1;
				} else if (!user_create($_POST['username'], $_POST['emailaddress'], $_POST['password'], $_POST['first_name'], $_POST['last_name'], $_POST['organisation'])) { //Attempt to create user
					//error
					array_push($result['messages'], newFormErrorMessage('Error', 'Something went wrong, you may have used a used email. Please try again, or contact an admin.', 
						'glyphicon-exclamation-sign', 'danger', 'doCreate'));
					$result['status'] = 1;
				} else {
					//success
					array_push($result['messages'], newFormErrorMessage('Success', 'Successfully created your account!', 
						'glyphicon-ok', 'success', 'doCreate'));
					$result['status'] = 0;
				}
			} else {
				array_push($result['messages'], newFormErrorMessage('Error', 'Username was already in use.', 
						'glyphicon-exclamation-sign', 'danger', 'username'));
				$result['status'] = 1;
			}
		}
		
		if (isset($_POST['doAjax']) && $_POST['doAjax'] == 'true') {
			//we will just echo the result object
			echo(json_encode($result));
			die();
		} else {
			//we have to manually display the boxes, this is handled bellow with inline php.
		}
	}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>New Registration</title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="author" content="">
		
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
				<h2>Registration</h2>
			</div>
			
			<form role="form" method="POST" id="createAccountForm">
				<?php echoFormError('doCreate', $result); ?>
				<input type="hidden" name="doCreate" id="doCreate" value="true">
				<input type="hidden" name="doAjax" id="doAjax" value="false">
				<div class="form-group">
					<label for="username" class="col-md-2">
						Username:
					</label>
					<div class="col-md-10">
						<?php echoFormError('username', $result); ?>
						<input type="text" class="form-control" id="username" name="username" placeholder="Enter a new username" required>
					</div>
					
				</div>
				
				<div class="form-group">
					<label for="first_name" class="col-md-2">
						First name:
					</label>
					<div class="col-md-10">
						<?php echoFormError('first_name', $result); ?>
						<input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter your first name" required>
					</div>
					
				</div>
				
				<div class="form-group">
					<label for="last_name" class="col-md-2">
						Last name:
					</label>
					<div class="col-md-10">
						<?php echoFormError('last_name', $result); ?>
						<input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter your last name" required>
					</div>
					
				</div>
				
				<div class="form-group">
					<label for="organisation" class="col-md-2">
						Organisation:
					</label>
					<div class="col-md-10">
						<?php echoFormError('organisation', $result); ?>
						<input type="text" class="form-control" id="organisation" name="organisation" placeholder="Enter your organisation (or leave blank)">
					</div>
					
				</div>
				
				<div class="form-group">
					<label for="emailaddress" class="col-md-2">
						Email address:
					</label>
					<div class="col-md-10">
						<?php echoFormError('emailaddress', $result); ?>
						<input type="email" class="form-control" id="emailaddress" name="emailaddress" placeholder="Enter email address" required>
						<p class="help-block">
							Example: yourname@domain.com
						</p>
					</div>
					
				</div>
				
				<div class="form-group">
					<label for="password" class="col-md-2">
						Password:
					</label>
					<div class="col-md-10">
						<?php echoFormError('password', $result); ?>
						<input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
						<p class="help-block">
							Min: 6 characters (Alphanumeric only)
						</p>
					</div>
				</div>
				
				<div class="form-group">
					<label for="password_again" class="col-md-2">
						Confirm:
					</label>
					<div class="col-md-10">
						<?php echoFormError('password_again', $result); ?>
						<input type="password" class="form-control" id="password_again" name="password_again" placeholder="Enter password again" required>
						<p class="help-block">
							Min: 6 characters (Alphanumeric only)
						</p>
					</div>
				</div>
				
				<div class="checkbox">
					<div class="col-md-2">
					</div>
					<div class="col-md-10">
						<?php echoFormError('tandc', $result); ?>
						<label><input type="checkbox" id="tandc" name="tandc">I have read the Terms and Conditions</label>
					</div>
					
					
				</div>
				
				<div class="row">
					<div class="col-md-2">
					</div>
					<div class="col-md-10">
						<button type="submit" class="btn btn-info">
							Register
						</button>
					</div>
				</div>
				
			</form>
		</div>
		<script src="../../js/jquery-1.12.3.min.js"></script>
		<script src="../../js/bootstrap.min.js"></script>
		<script>
		/*
			function submitdata()
			{

			var name=document.getElementById( "name_of_user" );
			var age=document.getElementById( "age_of_user" );
			var course=document.getElementById( "course_of_user" );

			$.ajax({
					type: 'post',
					url: 'insertdata.php',
					data: {
					user_name:name,
					user_age:age,
					user_course:course
					},
					success: function (response) {
					$('#success__para').html("You data will be saved");
					}
				});

			return false;

			}
			//This is inline JavaScript to validate the form in php but have it play nice with UIX design.
			var createForm = document.getElementById('createAccountForm');
			var doAjax = document.getElementById('doAjax');
			//set it to true so we can submit all the form values as is.
			doAjax.value = 'true';
			
			//handle onsubmit
			createForm.onsubmit = function() {
				//get every field using jQuery
				var $inputs = $('#createAccountForm :input');
				var values = {};
				$inputs.each(function() {
					values[this.name] = $(this).val();
				});
				//Submit a POST to this using jQuery
				
				
				//return false to prevent event bubbling.
				return false;
			}*/
		</script>
	</body>
</html>