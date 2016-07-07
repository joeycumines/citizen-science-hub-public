<?php
	/*
		POST to this script 'source_id'=>source_id. Only works when logged in,
		and has permission to add to queue.
		
		The queue considers the priority of the user (at the time the image is added to queue).
		
		
	*/
	session_start();
	require('db_connection.php');
	require('db_user.php');
	require('db_userPerms.php');
	require('clientError.php');
	require('queue_tools.php');
	
	//first, we need to see if we are logged in and have permissions.
	$pdo = getNewPDO();
	
	if (!user_loggedIn($pdo)) {
		$mess = 'You are unauthorised to add to queue: You are not logged in.';
		httpError(401, $mess);
		echo($mess);
		die();
	}
	
	//get our queue priority (higher priority numbers are always performed first for now).
	$queuePriority = getQueuePriority($_SESSION['USERNAME'], $pdo);
	if ($queuePriority <= 0) {
		$mess = 'You are unauthorised to add to queue: You havent been given permission to process images.';
		httpError(403, $mess);
		echo($mess);
		die();
	}
	
	//$queuePriority = 1;
	
	//get our POST vars
	$sourceId = isset($_POST['source_id']) ? validate($_POST['source_id']) : null;
	//fallback GET for debugging.
	if (empty($sourceId))
		$sourceId = isset($_GET['source_id']) ? validate($_GET['source_id']) : null;
	
	if (empty($sourceId)) {
		$mess = 'You did not send us the correct POST format.';
		httpError(400, $mess);
		echo($mess);
		die();
	}
	
	//now we can add to queue, at the end of our particular priority.
	/*if (queue_insert(queue_getBackId($queuePriority, $pdo)+1, $sourceId, $queuePriority, $pdo))
		echo('added');
	else
		echo('failed');*/
	//we use the queue_add, because we want to be consistent if we change one but not the other.
	
	if (queue_add($sourceId, $pdo))
		echo('added');
	else
		echo('failed');
?>