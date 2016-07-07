<?php
	/*
		Outputs a json array, of the last 15 images that were processed by our system.
		
		Only included images that are not deleted, and not by deleted users.
		
		Data includes:
		- uploaded_dt
		- save_location (original image)
		- processed_id
		- uploaded_fn
		- source_id
	*/
	require('../php/db_connection.php');
	require('../php/db_user.php');
	require('../php/db_userPerms.php');
	require('../php/pageRedirect.php');
	
	$rows = runQueryPrepared(getNewPDO(), '
SELECT (SELECT p.processed_id FROM image_processed p WHERE p.source_id = s.source_id ORDER BY p.processed_id desc LIMIT 1) AS processed_id,
s.uploaded_dt AS uploaded_dt, s.save_location AS save_location, s.source_id AS source_id, s.uploaded_fn AS uploaded_fn
FROM image_source s
WHERE s.source_id IN (SELECT p.source_id FROM image_processed p WHERE p.source_id = s.source_id) AND s.deleted = 0 
AND s.username IN (SELECT u.username FROM user u WHERE u.username = s.username AND u.deleted = 0)
ORDER BY s.uploaded_dt desc, s.uploaded_fn
LIMIT 15;
		', array());
	echo(json_encode($rows));
?>