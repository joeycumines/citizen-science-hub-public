<?php
	/*
		This is a simple interface to work with the queue table.
		
		REQUIRES:
		/www/php/db_connection.php
		
		queue_add requires additional.
	*/
	
	/**
		Inserts at a position. All records of id >= $id are incremented by 1.
		
		Returns true if we added, else false.
	*/
	function queue_insert($id, $source_id, $priority, $pdo = null) {
		if ($pdo == null) {
			//make a pdo connection
			$pdo = getNewPDO();
		}
		
		//if not valid we make it the start.
		if ($id < 1)
			$id = 1;
		
		//make room; we increment all >= $id by 1
		$sql = 'UPDATE image_queue SET id = id + 1 WHERE id >= :id ORDER BY id desc;';
		if (!runUpdatePrepared($pdo, $sql, array(':id'=>$id)))
			return false;
		
		//add to the queue, at $id, which should now be free and $id > 0.
		$sql = 'INSERT INTO image_queue (id, source_id, priority) VALUES (:id, :source_id, :priority);';
		try {
			if (!runUpdatePrepared($pdo, $sql, array(':id'=>$id, ':source_id'=>$source_id, ':priority'=>$priority)))
				return false;
		} catch(Exception $e) {
			return false;
		}
		
		return true;
	}
	
	/**
		Returns the next item, for a given priority (if default then we consider all).
		This result is the database row for the field.
		
		If we don't have any we return null.
	*/
	function queue_next($priority = 0, $pdo = null) {
		if ($pdo == null) {
			//make a pdo connection
			$pdo = getNewPDO();
		}
		$sql = 'SELECT * FROM image_queue WHERE priority = :priority OR 1 = :noprio ORDER BY id asc LIMIT 1;';
		$rows = runQueryPrepared($pdo, $sql, array(':priority'=>$priority, ':noprio'=>($priority == 0 ? 1 : 0)));
		foreach ($rows as $row) {
			return $row;
		}
		return null;
	}
	
	/**
		Removes the given id from the queue.
	*/
	function queue_remove($id, $pdo = null) {
		if ($pdo == null) {
			//make a pdo connection
			$pdo = getNewPDO();
		}
		$sql = 'DELETE FROM image_queue WHERE id = :id;';
		return runUpdatePrepared($pdo, $sql, array(':id'=>$id));
	}
	
	/**
		Gets the id where we would insert in a priority ordered queue.
		Finds the last id of >= $priority. If there is none, then we simply
		return 0.
	*/
	function queue_getBackId($priority, $pdo = null) {
		if ($pdo == null) {
			//make a pdo connection
			$pdo = getNewPDO();
		}
		//we need to find the last id for our given priority.
		$sql = 'SELECT id FROM image_queue WHERE priority >= :priority ORDER BY id desc LIMIT 1;';
		$rows = runQueryPrepared($pdo, $sql, array(':priority'=>$priority));
		foreach ($rows as $row) {
			return $row['id'];
		}
		return 0;
	}
	
	/**
		Attempts to add a sourceId to queue, and returns true on success.
		
		Requires:
		session_start();
		require('db_connection.php');
		require('db_user.php');
		require('db_userPerms.php');
	*/
	function queue_add($sourceId, $pdo = null) {
		if (empty($sourceId))
			return false;
		
		if ($pdo == null) {
			//make a pdo connection
			$pdo = getNewPDO();
		}
		
		if (!user_loggedIn($pdo))
			return false;
		
		$queuePriority = getQueuePriority($_SESSION['USERNAME'], $pdo);
		if ($queuePriority <= 0)
			return false;
			
		//now we can add to queue, at the end of our particular priority.
		if (queue_insert(queue_getBackId($queuePriority, $pdo)+1, $sourceId, $queuePriority, $pdo))
			return true;
		
		return false;
	}
?>