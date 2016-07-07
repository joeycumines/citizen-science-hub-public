<?php
	/*
		This script is the general include for generating the database connection.
		
		Also contains:
		- Simple query helper methods
		- A validate method for input fields (final check).
		- Sets the default time zone, so that we can store local time in the db (AEST).
		- mysql type conversion helpers
	*/
	
	//set our timezone to AEST
	date_default_timezone_set("Australia/Brisbane");
	
	//declare db connection globals
	$DB_CONNECTION_STRING = 'mysql:host=localhost;dbname=citizen_science';
	$DB_USERNAME='';
	$DB_PASSWORD='';
	
	//Absolute path on the server where images will be stored.
	$DB_IMAGE_LOCATION = '/home/debian/citizen_science/images/';
	
	/**
		Gets a new PDO object connected to the citizen_science db.
	*/
	function getNewPDO() {
		global $DB_CONNECTION_STRING, $DB_USERNAME, $DB_PASSWORD;
		$pdo = new PDO($DB_CONNECTION_STRING, $DB_USERNAME, $DB_PASSWORD);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $pdo;
	}
	
	/**
		Runs a query on a pdo object. Text string only, don't use string concat unless
		you know what you are doing.
	*/
	function runQuery($pdo, $queryString) {
		$stmt = $pdo->query($queryString);
		return $stmt;
	}
	
	/**
		Runs a update on the db, takes a queryString and an array of parameters that
		will be properly inserted into a prepared statement to help protect against
		sql injection.
	*/
	function runUpdatePrepared($pdo, $queryString, $prepArray) {
		$sth = $pdo->prepare($queryString, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		return $sth->execute($prepArray);
	}
	
	/**
		Runs a query on the db, takes a queryString and an array of parameters that
		will be properly inserted into a prepared statement to help protect against
		sql injection.
	*/
	function runQueryPrepared($pdo, $queryString, $prepArray) {
		$sth = $pdo->prepare($queryString, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute($prepArray);
		return $sth->fetchAll();
	}
	
	/**
		A generic helper method to validate input from untrusted source.
		Strips special characters, escapes html and slashes, and trims the text.
		If there is no data it will return null for easy comparison.
	*/
	function validate($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		
		//empty string or variable results in null, for easy comparison
		if (empty($data))
			return null;
		
		return $data;
	}
	
?>
