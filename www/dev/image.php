<?php
	/*
		Individual image view.
		
		POST:
		id: processed_id
		
		- very basic display for now.
	*/
	
	require('../php/db_connection.php');
	require('../php/exif.php');
	
	$id = isset($_GET['id']) ? validate($_GET['id']) : null;
	
	if ($id == null) {
		echo('no id');
		die();
	}
	
	$pdo = getNewPDO();
	
	$rows = runQueryPrepared($pdo, 'SELECT * FROM image_processed WHERE processed_id = :id', array(':id'=>$id));
	$proc = null;
	foreach($rows as $row) {
		$proc = $row;
		break;
	}
	if ($proc == null) {
		echo('bad id');
		die();
	}
	
	$rows = runQueryPrepared($pdo, 'SELECT * FROM image_source WHERE source_id = :id', array(':id'=>$proc['source_id']));
	$src = null;
	foreach($rows as $row) {
		$src = $row;
	}
	
	if ($src == null) {
		echo('bad id 2');
		die();
	}
	
	$metadata = json_decode($proc['metadata'], true);
	
?>
<h1><?php echo($src['uploaded_fn']); ?></h1>

<div>
	<textarea style="width: 700px; height: 600px;">
		<?php
			print_r(exif_reformat(exif_read_data($DB_IMAGE_LOCATION.$src['save_location'])));
		?>
	</textarea>
</div>

<div>
	<textarea style="width: 700px; height: 600px;"><?php echo(json_encode($metadata, JSON_PRETTY_PRINT)); ?></textarea>
</div>
<div>
	<h2>source image</h2>
	<img style="width: 700px;" src="../browse/image/source/?id=<?php echo($proc['source_id']); ?>"></img>
</div>
<div>
	<h2>processed image</h2>
	<img style="width: 700px;" src="../browse/image/processed/?id=<?php echo($proc['processed_id']); ?>"></img>
</div>