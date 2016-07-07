<?php
	/*
		GET this script, and it will return the id of the lowest queue item,
		then remove that item from the queue.
		
		If we have $_GET['priority'] then we consider for only that priority.
			- This could be used later to allow for a "high priority" queue.
		
		RESULT:
		{
			status: <0 or 1, 0 being success>,
			result: <A error message or our next source_id>
		}
		
		This should not be accessed externally to the local server, as it requires a auth code.
	*/
	
	require('db_connection.php');
	require('clientError.php');
	require('queue_tools.php');
	
	$queue_next_authCode = '';
	
	$authCode = isset($_GET['authCode']) ? validate($_GET['authCode']) : null;
	
	if (empty($authCode)) {
		$mess = 'You did not provide an authentication code.';
		httpError(401, $mess);
		echo($mess);
		die();
	}
	
	if ($authCode != $queue_next_authCode) {
		$mess = 'Your auth code was incorrect, access denied.';
		httpError(403, $mess);
		echo($mess);
		die();
	}
	
	$priority = isset($_GET['priority']) ? validate($_GET['priority']) : 0;
	$result = array();
	$result['status'] = 1;
	$result['result'] = 'No result set.';
	
	//get the next item
	$nextItem = queue_next($priority);
	if ($nextItem == null) {
		//we didn't have a next item
		$result['result'] = 'Nothing in the queue.';
		echo(json_encode($result));
		die();
	}
	
	//remove the item from queue
	if (queue_remove($nextItem['id'])) {
		//return result
		$result['status'] = 0;
		$result['result'] = $nextItem['source_id'];
	} else {
		$result['result'] = 'Unable to remove item from queue.';
	}
	
	echo(json_encode($result));
	
?>