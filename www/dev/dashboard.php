<?php
	/*
		Displays a list of uploaded items, in a table view.
	*/
	session_start();
	require('../php/db_connection.php');
	require('../php/db_user.php');
	require('../php/db_image.php');
	
	$pdo = getNewPDO();
	
	if (!user_loggedIn($pdo)) {
		echo('Not logged in.');
		die();
	}
	
	//get source ids for this user, order by time desc
	$sourceRows = runQueryPrepared($pdo, 'SELECT source_id, uploaded_fn, uploaded_dt FROM image_source WHERE username = :username ORDER BY uploaded_dt desc;', array(':username'=>$_SESSION['USERNAME']));
	
	//get processed ids for this user, order by time desc.
	$processedRows = runQueryPrepared($pdo, 'SELECT processed_id, source_id FROM image_processed WHERE source_id IN (SELECT source_id FROM image_source WHERE username = :username);', array(':username'=>$_SESSION['USERNAME']));
	
	$processed = array();
	//put the processed rows into a object where we can have multiple processed ids for each source
	foreach ($processedRows as $row) {
		if (!isset($processed[$row['source_id']]))
			$processed[$row['source_id']] = array();
		array_push($processed[$row['source_id']], $row['processed_id']);
	}
	
	//build a table
	echo('<table>');
	foreach($sourceRows as $row) {
		echo('<tr><td><a href="../browse/image/source/?id='.$row['source_id'].'">'.$row['uploaded_fn'].'</a></td><td>'.$row['uploaded_dt'].'</td>');
		if (isset($processed[$row['source_id']])) {
			//we have processed, give links
			echo('<td>');
			foreach($processed[$row['source_id']] as $proc) {
				echo('<a href="../browse/image/?id='.$proc.'">processed! </a>');
			}
			echo('</td>');
		} else {
			//just say processing
			echo('<td>processing</td>');
		}
		echo('</tr>');
	}
	echo('</table>');
?>