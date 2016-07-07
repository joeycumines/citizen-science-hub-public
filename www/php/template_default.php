<?php
	/*
		This script exists to aid in preventing code replication, by using page templates.
		
		The approach taken here is a simple functional style, there are no classes, just methods.
		Methods should echo the result in most cases, unless it makes sense to return a string.
		
		REQUIRES:
		- No requirements for all, but there are requirements for some methods, see comment.
	*/
	
	/**
		Helper function to indent code to a certain level.
	*/
	function formatForEcho($indentLevel, $indentCharacter, $html) {
		$result = '';
		
		$separator = "\r\n";
		$line = strtok($html, $separator);
		while ($line !== false) {
			$temp = '';
			for ($x = 0; $x < $indentLevel; $x++)
				$temp .= $indentCharacter;
			
			$result .= $temp . $line . "\n";
			$line = strtok($separator);
		}
		
		return $result;
	}
	
	/**
		Echos either the account info, or the signin buttons, as a bootstrap navbar-right div.
		
		$path:
			- How to get to root from the current directory.
		
		REQUIRES:
			- php/db_connection.php
			- php/db_user.php
			- session_start();
			- php/pageRedirect.php
	*/
	function echoNavbarRightAccount($indentLevel, $indentCharacter, $path) {
		$result = '';
		if (user_loggedIn()) {
			//echo the logged in account info.
			$result = '
<ul class="nav navbar-nav navbar-right">
	<li class="dropdown">
		<a data-toggle="dropdown" href="#"> <span class="glyphicon glyphicon-user"></span> '.$_SESSION['USERNAME'].'<span class="caret"></span></a>
		
		<ul class="dropdown-menu">
			<li><a href="'.$path.'manage/user/">Account Info</a></li>
			<li><a href="'.$path.'manage/password_reset/">Reset Password</a></li>
			';
			//if we are an admin then we want to link the admin tool.
			if (canUserAdmin($_SESSION['USERNAME'])) {
				$result.= '
			<li class="divider"></li>
			<li><a href="'.$path.'admin/">Admin Tools</a></li>
				';
			}
			$result .='
			<li class="divider"></li>
			<li><a href="'.$path.'manage/signout/?redirect=/" id="signOutLink">Sign out</a></li>
		</ul>
	</li>
</ul>
<script>
	if (typeof location.origin === \'undefined\')
		location.origin = location.protocol + \'//\' + location.host;
	//log out our user on sign out click
	document.getElementById("signOutLink").onclick = function() {
		
		//Attempt to call the signout page using ajax
		//then put the result into a alert box that scrolls down from the top of the page.
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (xhttp.readyState == 4 && xhttp.status == 200) {
				//alert this
				alert(xhttp.responseText);
				//go back to origin
				window.location.href = location.origin;
			} else if (xhttp.readyState == 4) {
				document.getElementById("signOutLink").onclick = function() {return true;};
				document.getElementById("signOutLink").click();
			}
		};
		xhttp.open("GET", "'.$path.'manage/signout", true);
		xhttp.send();
		
		return false;
	}
</script>
			';
		} else {
			//echo the signin buttons
			$result = '
<div class="navbar-right">
	<a href="'.$path.'manage/signin" class="btn btn-info navbar-btn">Sign in</a>
	<a href="'.$path.'manage/signup" class="btn btn-warning navbar-btn">Sign up</a>
</div>
			';
		}
		echo(formatForEcho($indentLevel, $indentCharacter, $result));
	}
	
	/**
		Echos the navbar for manage sub directories.
		
		$path:
			- How to get to root from the current directory.
	*/
	function echoManageNavbar($indentLevel, $indentCharacter, $path) {
		echo(formatForEcho($indentLevel, $indentCharacter,'
<nav class="navbar navbar-inverse navbar-fixed-top">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#menu">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a href="'.$path.'"><img alt="Citizen Science Hub" style="max-width:40px; margin-top: 5px;" class="img-responsive2" src="'.$path.'images/logo.png"></a>
		</div>
		
		<div class="navbar-collapse collapse" id="menu">
			<ul class="nav navbar-nav">
				<li><a href="'.$path.'manage/"><span class="glyphicon glyphicon-home"></span> Home</a></li>
				<li><a target="_blank" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-picture"></span> Photos<span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="'.$path.'browse/">Your photos</a></li>
						<li class="divider"></li>
						<li><a href="'.$path.'manage/upload">Upload</a></li>   
					</ul>
				</li>
				<li><a target="_blank" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-book"></span> Tutorial<span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="'.$path.'tutorial/">All</a></li>
						<li class="divider"></li>
						<li><a href="'.$path.'tutorial/#create">Create an Account</a></li>
						<li><a href="'.$path.'tutorial/#manage">Manage your Account</a></li>
						<li class="divider"></li>
						<li><a href="'.$path.'tutorial/#android">Add to Android</a></li>
						<li><a href="'.$path.'tutorial/#iphone">Add to iPhone</a></li>
						<li class="divider"></li>
						<li><a href="'.$path.'tutorial/#upload">Uploading photos</a></li>
						<li><a href="'.$path.'tutorial/#delete">Deleting photos</a></li>
						<li><a href="'.$path.'tutorial/#share">Sharing photos</a></li>
					</ul>
				</li>
				<li><a href="'.$path.'about/"><span class="glyphicon glyphicon-info-sign"></span> About us</a></li>
				<li><a href="'.$path.'contact/"><span class="glyphicon glyphicon-envelope"></span> Contact</a></li>
			</ul>
		'));
		echoNavbarRightAccount($indentLevel+3, $indentCharacter, $path);
		echo(formatForEcho($indentLevel, $indentCharacter, '
		</div>
	</div>
</nav>
		'));
	}
	
	/**
		Echos a bootstrap alert box.
	*/
	function echoBSAlertBox($indentLevel, $indentCharacter, $type, $icon, $title, $message) {
		$result = '
<div class="alert alert-'.$type.'" role="alert">
	<span class="glyphicon '.$icon.'" aria-hidden="true"></span>
	<span class="sr-only">'.$title.':</span>
	'.$message.'
</div>
		';
		echo(formatForEcho($indentLevel, $indentCharacter, $result));
	}
	
	/**
		Echos the signin form.
		This method also handles login, so any page that calls this will be able to be logged into.
		
		$path:
			- How to get to root from the current directory.
		
		REQUIRES:
			- php/db_connection.php
			- php/db_user.php
			- php/db_userPerms.php
			- session_start();
			- php/pageRedirect.php
	*/
	function echoSigninForm($indentLevel, $indentCharacter, $path, $redirect = null) {
		//if we are not logged in, then attempt to login.
		if (!user_loggedIn()) {
			if (isset($_POST['doLogin']) && $_POST['doLogin'] == 'true') {
				//attempt to login (validate handled there)
				if (!isset($_POST['username']) || !isset($_POST['password'])) {
					//Something went very wrong.
					echoBSAlertBox($indentLevel, $indentCharacter, 'danger', 'glyphicon-exclamation-sign','Error', 'Something went wrong, please try again, or contact an admin.');
				} else if (user_login($_POST['username'], $_POST['password'])) {
					echoBSAlertBox($indentLevel, $indentCharacter, 'success', 'glyphicon-ok', 'Success', 'We logged in! <a href="'.$path.'dev/printUserDetails.php">Click here to view your details.</a>');
				} else {
					echoBSAlertBox($indentLevel, $indentCharacter, 'danger', 'glyphicon-exclamation-sign', 'Error', 'Login failed, invalid username, email, or password.');
				}
			}
		}
		
		//if we are not signed in at this point then we can echo
		if (user_loggedIn()) {
			//we redirect if we set it
			if ($redirect != null)
				echoRedirectPage($redirect);
			return;
		}
		$result = '
<link rel="stylesheet" property="stylesheet" href="'.$path.'/css/signin.css">
<form class="form-signin" method="POST">
	<input type="hidden" name="doLogin" value="true">
	<h2 class="form-signin-heading">Please sign in</h2>
	<label for="inputEmail" class="sr-only">Username OR Email address</label>
	<input type="text" id="inputEmail" name="username" class="form-control" placeholder="Username or Email" required autofocus>
	<label for="inputPassword" class="sr-only">Password</label>
	<input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
	<div class="input-group">
		<a href="'.$path.'manage/password_reset/">Forgot your password?</a>&nbsp;<a href="'.$path.'manage/signup/">Need an account?</a>
	</div>
	<div class="checkbox">
		<label>
			<input type="checkbox" value="remember-me"> Remember me
		</label>
	</div>
	<button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
</form>
		';
		echo(formatForEcho($indentLevel, $indentCharacter, $result));
	}
	
	/**
		Favicon, viewport, mobile, and other related goodies, all in one method.
	*/
	function echoIconAndMobileHeader($indentLevel, $indentCharacter, $path) {
		$result = '
<!-- START mobile + icon header -->
<link rel="icon" href="'.$path.'images/icons/favicon-128.png">

<!--Apple-->
<link rel="apple-touch-icon" href="'.$path.'images/icons/apple-touch-icon-114x114.png"/>
<link rel="apple-touch-startup-image" href="'.$path.'images/logo.png" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />

<!--Android-->
<meta name="mobile-web-app-capable" content="yes">
<link rel="icon" sizes="128x128" href="'.$path.'images/icons/favicon.ico">

<meta name="viewport" content = "width = device-width, initial-scale = 1, user-scalable = yes" />
<meta name="viewport" content = "width = device-width, initial-scale = 1, minimum-scale = 0.6, maximum-scale = 3" />

<link rel="apple-touch-icon-precomposed" sizes="57x57" href="'.$path.'images/icons/apple-touch-icon-57x57.png" />
<link rel="apple-touch-icon-precomposed" sizes="114x114" href="'.$path.'images/icons/apple-touch-icon-114x114.png" />
<link rel="apple-touch-icon-precomposed" sizes="72x72" href="'.$path.'images/icons/apple-touch-icon-72x72.png" />
<link rel="apple-touch-icon-precomposed" sizes="144x144" href="'.$path.'images/icons/apple-touch-icon-144x144.png" />
<link rel="apple-touch-icon-precomposed" sizes="60x60" href="'.$path.'images/icons/apple-touch-icon-60x60.png" />
<link rel="apple-touch-icon-precomposed" sizes="120x120" href="'.$path.'images/icons/apple-touch-icon-120x120.png" />
<link rel="apple-touch-icon-precomposed" sizes="76x76" href="'.$path.'images/icons/apple-touch-icon-76x76.png" />
<link rel="apple-touch-icon-precomposed" sizes="152x152" href="'.$path.'images/icons/apple-touch-icon-152x152.png" />
<link rel="icon" type="image/png" href="'.$path.'images/icons/favicon-196x196.png" sizes="196x196" />
<link rel="icon" type="image/png" href="'.$path.'images/icons/favicon-96x96.png" sizes="96x96" />
<link rel="icon" type="image/png" href="'.$path.'images/icons/favicon-32x32.png" sizes="32x32" />
<link rel="icon" type="image/png" href="'.$path.'images/icons/favicon-16x16.png" sizes="16x16" />
<link rel="icon" type="image/png" href="'.$path.'images/icons/favicon-128.png" sizes="128x128" />
<meta name="application-name" content="Citizen Science Hub"/>
<meta name="msapplication-TileColor" content="#FFFFFF" />
<meta name="msapplication-TileImage" content="'.$path.'images/icons/mstile-144x144.png" />
<meta name="msapplication-square70x70logo" content="'.$path.'images/icons/mstile-70x70.png" />
<meta name="msapplication-square150x150logo" content="'.$path.'images/icons/mstile-150x150.png" />
<meta name="msapplication-wide310x150logo" content="'.$path.'images/icons/mstile-310x150.png" />
<meta name="msapplication-square310x310logo" content="'.$path.'images/icons/mstile-310x310.png" />

<script>(function(a,b,c){if(c in b&&b[c]){var d,e=a.location,f=/^(a|html)$/i;a.addEventListener("click",function(a){d=a.target;while(!f.test(d.nodeName))d=d.parentNode;"href"in d&&(chref=d.href).replace(e.href,"").indexOf("#")&&(!/^[a-z\+\.\-]+:/i.test(chref)||chref.indexOf(e.protocol+"//"+e.host)===0)&&(a.preventDefault(),e.href=d.href)},!1)}})(document,window.navigator,"standalone");</script>

<!-- END mobile + icon header -->
		';
		
		echo(formatForEcho($indentLevel, $indentCharacter, $result));
	}
?>