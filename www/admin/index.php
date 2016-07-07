<?php
	/*
		This page is only accessible to logged in users who have the ADMIN perm >= 1.
		
		Currently just displays images in order count(username) desc uploaded_dt asc.
	*/
	
	session_start();
	require('../php/db_connection.php');
	require('../php/db_user.php');
	require('../php/db_userPerms.php');
	require('../php/pageRedirect.php');
	require('../php/template_default.php');
	require('../php/db_image.php');
	require('../php/exif.php');
	
	//authorisation
	if (!user_loggedIn() || !canUserAdmin($_SESSION['USERNAME'])) {
		//if we are not logged in, then redirect to login page.
		echo('You are unauthorised to view this page. (how did you get here?)');
		die();
	}
	
	$pdo = getNewPDO();
	
	//we build a ordered list of the top reported images. This list has option to either pardon or delete.
	//drop downs enable us to view the reported reasons by whom.
	
	//first we need to get the top reported images (limit 50) order count(username) desc uploaded_dt asc.
	$sql = '
SELECT (SELECT COUNT(f.username) FROM image_flags f WHERE f.source_id = s.source_id) AS num_flags,
	s.username AS username, s.save_location AS save_location, s.source_id AS source_id,
	s.uploaded_dt as uploaded_dt, s.uploaded_fn as uploaded_fn
FROM image_source s
WHERE (SELECT COUNT(f.username) FROM image_flags f WHERE f.source_id = s.source_id) > 0 AND deleted = 0
ORDER BY num_flags desc, uploaded_dt asc
LIMIT 50;
	';
	$imageRows = runQueryPrepared($pdo, $sql, array());
	//now, for every image flagged, we make a dropdown panel using bootstrap styles.
	$content = '';
	foreach ($imageRows as $row) {
		//get all the flags for this id
		$flags = runQueryPrepared($pdo, 'SELECT * FROM image_flags WHERE source_id = :source_id;', array(':source_id'=>$row['source_id']));
		
		$content.= '
<div class="panel-group" id="imagerow'.$row['source_id'].'">
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="row">
				<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 text-center">
					<a href="#" class="btn btn-primary" style="height:50px;" onclick="onClickPardon('.$row['source_id'].');">Pardon</a>
				</div>
				<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
					<div class="row">
						<div class="col-sm-6 col-md-4 col-lg-3">
							<a data-toggle="collapse" href="#collapse'.$row['source_id'].'">
								<h3>'.$row['uploaded_fn'].'</h3>
								<h4>Times Flagged: '.$row['num_flags'].'</h4>
								<h4>Uploaded: '.$row['uploaded_dt'].'</h4>
							</a>
						</div>
						<div class="col-sm-6 col-md-8 col-lg-9">
							<a href="../browse/image/source/?id='.$row['source_id'].'">
								<img src="../browse/image/source/?thumbnail=true&retainAspect=true&width=200&height=200&id='.$row['source_id'].'" class="img-responsive img-rounded" alt="'.$row['uploaded_fn'].'">
							</a>
						</div>
					</div>
				</div>
				<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 text-center">
					<a href="#" class="btn btn-danger" style="height:50px;" onclick="onClickDelete('.$row['source_id'].', \''.$row['username'].'\');">Delete</a>
				</div>
			</div>
		</div>
		<div id="collapse'.$row['source_id'].'" class="panel-collapse collapse">
			<ul class="list-group">
		';
		foreach($flags as $flag) {
			$content.='
				<li class="list-group-item"><strong>'.$flag['username'].':</strong> '.$flag['reason'].'</li>
			';
		}
		
		$content.='
			</ul>
			<div class="panel-footer"></div>
		</div>
	</div>
</div>
		';
	}
	
	if (empty($content))
		$content = 'There are no flagged images to display.';
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Citizen Science Hub - Admin</title>
		<link rel="stylesheet" href="../css/bootstrap.min.css">
		<link rel="stylesheet" href="../css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="../css/whhg.css">
		<link rel="stylesheet" href="../css/template.css">
		<?php
			echoIconAndMobileHeader(2, "\t", '../');
		?>
		
	</head>
	<body>
		<div id="header">
			<?php
				//Echos the navbar
				//parameters: indent level, indent character(s), how to get to the root www directory.
				echoManageNavbar(3, "\t", '../');
			?>
		</div>
		<div class="jumbotron">
			<div class="panel panel-default">
				<div class="panel-heading"><strong>Manage Flagged Images</strong></div>
				<div class="panel-body" style="overflow: scroll;">
					<?php echo($content); ?>
				</div>
			</div>
		</div>
		<script src="../js/jquery-1.12.3.min.js"></script>
		<script src="../js/bootstrap.min.js"></script>
		<script>
if (typeof location.origin === 'undefined')
	location.origin = location.protocol + '//' + location.host;
function removeRow(sourceId) {
	return (elem=document.getElementById('imagerow'+sourceId)).parentNode.removeChild(elem);
}
function onClickDelete(sourceId, username) {
	if (confirm('Delete image?')) {
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (xhttp.readyState == 4 && xhttp.status == 200) {
				alert(xhttp.responseText);
				if (xhttp.responseText == 'Image was successfully deleted')
					removeRow(sourceId);
			}
		};
		xhttp.open("GET", 'delete.php?sourceId='+sourceId+'&username='+username, true);
		xhttp.send();
	}
}
function onClickPardon(sourceId) {
	if (confirm('Pardon image?')) {
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (xhttp.readyState == 4 && xhttp.status == 200) {
				alert(xhttp.responseText);
				if (xhttp.responseText == 'Image was successfully pardoned')
					removeRow(sourceId);
			}
		};
		xhttp.open("GET", 'pardon.php?sourceId='+sourceId, true);
		xhttp.send();
	}
}
		</script>
	</body>
</html>