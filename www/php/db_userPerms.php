<?php
	/*
		Provides a simple way to get permissions for a user.
		See method declarations.
		
		Supports default permissions that are hard coded and stored in the db.
		
		Methods on this page should have support to use an existing db con,
		as we may want to perform many queries at once.
		
		REQUIRES INCLUDE:
		- /www/php/db_connection.php
	*/
	
	/**
		This stores our default values that we build from.
		Could be done in an ini file or table, but it is not necessary at this point.
	*/
	$DEFAULT_USER_PERMS = array();
	$DEFAULT_USER_PERMS['UPLOAD_ACCESS'] = 1;
	$DEFAULT_USER_PERMS['QUEUE_PRIORITY'] = 1;
	
	/**
		Returns the (int) permission value, given a username and perm key.
		If there is no perm for that user, then 0 is returned.
		
		This is the main db interface point for all of the methods on this page.
	*/
	function getPermission($username, $permission, $pdo = null) {
		global $DEFAULT_USER_PERMS;
		//if we have a default value we load it or start at 0.
		$result = isset($DEFAULT_USER_PERMS[$permission]) ? $DEFAULT_USER_PERMS[$permission] : 0;

		//we create a pdo if we didn't already have one.
		if ($pdo == null) {
			$pdo = getNewPDO();
		}
		
		//now we have pdo, we can query for the username and permission
		$rows = runQueryPrepared($pdo, 'SELECT * FROM user_perms WHERE perm = :permission AND username = :username', 
				array(':permission'=>$permission, ':username'=>$username));
		
		foreach ($rows as $row) {
			return $row['value'];
		}
		
		return $result;
	}
	
	/*
		Macros for task - based permissions.
		
		General naming guide:
		canUser<task>($username, $pdo = null)
	*/
	
	/**
		Returns true if the user can upload photos.
		If we wish to implement limits we could do it here.
	*/
	function canUserUpload($username, $pdo = null) {
		if ($pdo == null) {
			$pdo = getNewPDO();
		}
		if (getPermission($username, 'UPLOAD_ACCESS', $pdo) > 0) {
			return true;
		}
		return false;
	}
	
	/**
		Gets the priority value that our user has.
		
		If we can't find the user then we return 0
		(0 or under cannot add to queue).
	*/
	function getQueuePriority($username, $pdo = null) {
		if ($pdo == null) {
			$pdo = getNewPDO();
		}
		return getPermission($username, 'QUEUE_PRIORITY', $pdo);
	}
	
	/**
		Can a user view a specific image?
		
		This is only used for the individual image view.
		As of right now we can view all non deleted images posted by non deleted user accounts.
	*/
	function canUserViewImage($username, $processedId, $pdo = null) {
		if (empty($processedId))
			return false;
		
		if ($pdo == null) {
			$pdo = getNewPDO();
		}
		//we can say yes if the image exists, username is not delete and neither is the image source.
		$sql = '
SELECT p.processed_id AS processed_id FROM image_processed p
WHERE p.processed_id = :processedId AND p.source_id IN (
	SELECT s.source_id FROM image_source s
	WHERE
	s.deleted = 0 AND
	s.username IN (SELECT u.username FROM user u WHERE deleted = 0)
);
		';
		
		$rows = runQueryPrepared($pdo, $sql, array(':processedId'=>$processedId));
		
		foreach($rows as $row) {
			return true;
		}
		
		return false;
	}
	
	/**
		Is the user an administrator?
	*/
	function canUserAdmin($username, $pdo = null) {
		if ($pdo == null) {
			$pdo = getNewPDO();
		}
		return (getPermission($_SESSION['USERNAME'], 'ADMIN', $pdo) >= 1);
	}
?>