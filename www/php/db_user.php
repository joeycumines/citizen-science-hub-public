<?php
	/*
		This script encapsulates user login and related user centric tasks.
		User Permissions are handled separately.
		User names and emails are not considered case sensitive.
		
		REQUIRES INCLUDE:
		- /www/php/db_connection.php
		REQUIRES session_start()
		
		NOTES ON SESSIONS:
		- session_start(); Needs to be called to access, obviously. One per script (top level)
		- $_SESSION['USERNAME'] stores the user's name.
		- Methods that get field info for users from db can be called without a parameter if the user is logged in.
	*/
	
	/**
		This method will set the $_SESSION['USERNAME'] variable if we provided a valid
		username/email and password.
		
		Returns true if we logged in, else false.
	*/
	function user_login($userNameOrEmail, $userPass) {
		$userNameOrEmail = validate($userNameOrEmail);
		$userPass = validate($userPass);
		//Get the username we are logging in as, if $userNameOrEmail matches a username or email,
		//and the $userPass hash matches.
		$rows = runQueryPrepared(getNewPDO(), 'SELECT username
				FROM user
				WHERE (LOWER(username) = LOWER(:userNameOrEmail) OR LOWER(email) = LOWER(:userNameOrEmail)) AND
				hash = SHA2(CONCAT(:userPass, salt), 256) AND deleted = 0;', 
				array(':userNameOrEmail'=>$userNameOrEmail, ':userPass'=>$userPass));
		
		//The db design is such that we will never get more the one match, but
		foreach ($rows as $row) {
			$_SESSION['USERNAME'] = $row['username'];
			return true;
		}
		
		return false;
	}
	
	/**
		Logs the current user out. Returns a string to identify
		what actually happened. (were we logged in, etc).
	*/
	function user_logout() {
		$userNameTemp = isset($_SESSION['USERNAME']) ? $_SESSION['USERNAME'] : null;
		//destroys session no matter what
		$sessionName = session_name();
		$sessionCookie = session_get_cookie_params();
		session_destroy();
		setcookie($sessionName, false, $sessionCookie['lifetime'], $sessionCookie['path'], $sessionCookie['domain'], $sessionCookie['secure']);
		if ($userNameTemp != null) {
			return 'Logged out "'.$userNameTemp.'".';
		} else {
			return 'No user logged in, nothing to logout.';
		}
	}
	
	/**
		Attempts to create a new user with the given parameters.
		
		Returns true on success, false on failure.
		Does not log in.
		Does not check for user already exists.
		
		This method calls validate to strip html and trim all input.
	*/
	function user_create($username, $email, $password, $fname, $lname, $organisation) {
		//validate fields to strip bad characters etc.
		$username = validate($username);
		$email = validate($email);
		$password = validate($password);
		$fname = validate($fname);
		$lname = validate($lname);
		$organisation = validate($organisation);
		
		//we want to fail if password is empty
		if ($password == null || empty($password))
			return false;
		//or if the username is empty
		if ($username == null || empty($username))
			return false;
		//or if the email is empty
		if ($email == null || empty($email))
			return false;
		
		//the rest are allowed to be null.
		
		try {
			//Not crypto secure random, but good enough
			$salt = uniqid();
			
			//Today in mysql's format (ISO DATE)
			$currentDate = date('Y-m-d');
			//Try to insert row, will fail on bad parameters (db) or exiting row.
			return runUpdatePrepared(getNewPDO(), 'INSERT INTO user (username, email, hash, salt, fname, lname, organisation, created, updated)
					VALUES (:username, :email, SHA2(CONCAT(:password, :salt), 256), :salt, :fname, :lname, :organisation, :currentDate, :currentDate);',
					array(':username'=>$username, ':email'=>$email, ':password'=>$password, ':salt'=>$salt, ':fname'=>$fname, 
					'lname'=>$lname, ':organisation'=>$organisation, ':currentDate'=>$currentDate));
		} catch (Exception $e) {
			return false;
		}
	}
	
	/**
		Get the fields of a user row in the db.
		Returns the mysql row as an array if we found, else null.
		Can be used to check if user exits or not.
		Default parameter uses $_SESSION['USERNAME'].
		
		This method validates the input field.
	*/
	function user_getUser($userNameOrEmail = null, $pdo = null) {
		if ($userNameOrEmail == null) {
			if (!isset($_SESSION['USERNAME']) || empty($_SESSION['USERNAME']))
				return null;
			$userNameOrEmail = $_SESSION['USERNAME'];
		}
		$userNameOrEmail = validate($userNameOrEmail);
		
		//make/check db connection
		if ($pdo == null) {
			$pdo = getNewPDO();
		}
		
		//run query
		$rows = runQueryPrepared($pdo, 'SELECT *
				FROM user
				WHERE LOWER(username) = LOWER(:userNameOrEmail) OR LOWER(email) = LOWER(:userNameOrEmail);', 
				array(':userNameOrEmail'=>$userNameOrEmail));
		foreach ($rows as $row) {
			return $row;
		}
		return null;
	}
	
	/*
		Returns true if we are logged in with a valid username, else false.
	*/
	function user_loggedIn($pdo = null) {
		//make/check db connection
		if ($pdo == null) {
			$pdo = getNewPDO();
		}
		//checks (currently logged in user) and uses pdo
		return user_getUser(null, $pdo) != null;
	}
	
	/**
		Dummy method to convert date to a specific timezone.
	*/
	function user_desiredDateTimezone($date) {
		return $date;
	}
	
	/**
		Dummy method to convert dateTime to a specific timezone.
	*/
	function user_desiredDateTimeTimezone($dateTime) {
		return $dateTime;
	}
	
	/**
		Delete a user account (sets flag).
		
		RETURNS:
			true on success, else false.
	*/
	function user_delete($username, $pdo = null) {
		if (empty($username)) {
			return false;
		}
		
		//make db con if we need
		if ($pdo == null) {
			$pdo = getNewPDO();
		}
		
		return runUpdatePrepared($pdo, 'UPDATE user SET deleted = 1 WHERE username = :username;', array(':username'=>$username));
	}
?>