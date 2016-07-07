<?php
	/*
		This script will be the individual image view. Images are displayed via get request to their processed id,
		a bad request will result in a 404 error.
	*/
	
	session_start();
	require('../../php/db_connection.php');
	require('../../php/db_user.php');
	require('../../php/db_userPerms.php');
	require('../../php/pageRedirect.php');
	require('../../php/template_default.php');
	require('../../php/db_image.php');
	require('../../php/exif.php');
	
	//if we cannot view the image then we display a 404 page
	$content = '<h1>404 - Page Not Found</h1>';
	
	$processedId = isset($_GET['id']) ? $_GET['id'] : null;
	
	$pdo = getNewPDO();
	
	if (canUserViewImage(null, $processedId, $pdo)) {
		//get the image details
		$details = image_getDetails(null, $processedId, $pdo);
		if ($details != null) {
			//we got the image details!.
			$content = '
				<div class="row">
			';
			//if we are logged in and the image owner or an admin
			if (user_loggedIn() && isset($_SESSION['USERNAME']) && ($_SESSION['USERNAME'] == $details['username'] || canUserAdmin($_SESSION['USERNAME'])))
				$content .= '
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<div class="btn-group btn-group-justified">
							<a href="#" onclick="deleteImageClick();" class="btn btn-large btn-block btn-danger">Delete Image</a>
							<script>
if (typeof location.origin === \'undefined\')
	location.origin = location.protocol + \'//\' + location.host;
function deleteImageClick() {
	if (confirm("Are you sure you want to delete this image?")) {
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (xhttp.readyState == 4 && xhttp.status == 200) {
				alert(xhttp.responseText);
				if (xhttp.responseText == "Image was successfully deleted") {
					var previousPage = \''.(isset($_SERVER['HTTP_REFERER']) && empty($_SERVER['HTTP_REFERER']) == false ? $_SERVER['HTTP_REFERER'] : '').'\';
					previousPage = previousPage.trim();
					if (previousPage != null && previousPage != \'\')
						window.location.href = previousPage;
					else
						window.location.href = location.origin + "/browse/";
				}
			}
		};
		xhttp.open("GET", "delete.php?sourceId='.$details['source_id'].'", true);
		xhttp.send();
	}
};
							</script>
						</div>
						<br>
					</div>
					';
					//end if statement
			//we add the flag image button next, if we are logged in.
			if (user_loggedIn())
				$content .= '
					<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
						<div class="btn-group btn-group-justified">
							<a href="#" onclick="flagImageClick();" class="btn btn-large btn-block btn-warning">Flag Image</a>
							<script>
if (typeof location.origin === \'undefined\')
	location.origin = location.protocol + \'//\' + location.host;
function flagImageClick() {
	var reason = prompt("Please enter a reason why this image is inappropriate or incorrect.", "");
	if (reason != null)
		reason = reason.trim();
	if (reason != null && reason != \'\') {
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (xhttp.readyState == 4 && xhttp.status == 200) {
				alert(xhttp.responseText);
			}
		};
		xhttp.open("GET", "flag.php?sourceId='.$details['source_id'].'&reason="+reason, true);
		xhttp.send();
	}
};
							</script>
						</div>
						<br>
					</div>
					';
					//end if statement
			$content.= '
				</div>
				<div class="row" style="max-height: 200px;">
					<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
						<a href="../../browse/image/source/?id='.$details['source_id'].'" target="_blank" >
							<img src="../../browse/image/source/?thumbnail=true&retainAspect=true&width=200&height=200&id='.$details['source_id'].'" class="previewImg img-responsive" alt="source" style="max-height:200px;">
						</a>
					</div>
					<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
						<a href="../../browse/image/processed/?id='.$details['processed_id'].'" target="_blank" >
							<img src="../../browse/image/processed/?thumbnail=true&retainAspect=true&width=200&height=200&id='.$details['processed_id'].'" class="previewImg img-responsive" alt="processed" style="max-height:200px;">
						</a>
					</div>
				</div>
				<h2>Tags</h2>
				<p>'.$details['tags'].'</p>
				';
			//use bootstrap grid view to display our details in a (hopefully) sane manner.
			//now, we can parse the exif data into a more readable format
			$human = json_decode($details['metadata'], true);
			
			//grap the lat lng from either the db or $human
			$lat = isset($details['uploaded_lat']) ? $details['uploaded_lat'] : null;
			$lng = isset($details['uploaded_lng']) ? $details['uploaded_lng'] : null;
			if ($human != null && $human != 'null' && !empty($human) && is_array($human)) {
				$human = exif_reformat($human);
				
				if ($lat == null)
					$lat = validate(isset($human['gpslatitude']) ? $human['gpslatitude'] : null);
				if ($lng == null)
					$lng = validate(isset($human['gpslongitude']) ? $human['gpslongitude'] : null);
			}
			$leftClass = '';
			$rightClass = '';
			
			if ($lat != null && $lng != null) {
				$leftClass = 'col-md-8 col-lg-7';
				$rightClass = 'col-md-4 col-lg-5';
				$lat = floatval($lat);
				$lng = floatval($lng);
			}
			//$content.='<div id="latlng">'.$lat.','.$lng.'</div>';
			
			$content .= '<div class="row"><div class="'.$leftClass.'">';
			
			if ($human != null && $human != 'null' && !empty($human) && is_array($human) ) {
				$content.= '<h2>Metadata</h2>';
				//we use the tags from the database, so ignore the tags field for now.
				unset($human['tags']);
				//put them into bootstrap rows and columns.
				$rowCount = 0;
				$maxRows = 3;
				
				$backColor = '#FFFFFF';
				
				//ksort($human);
				foreach ($human as $key=>$value) {
					//open the row
					if ($rowCount == 0) {
						if ($backColor == '#FFFFFF') {
							$backColor = '#F2F2F2';
						} else {
							$backColor = '#FFFFFF';
						}
						$content.=('<div class="row" style="background-color: '.$backColor.';">');
					}
					
					//echo a div with our data in it.
					$content.=('<div class="col-sm-4 col-md-4 col-lg-4"><div class="row"><div class="col-xs-6 col-sm-6 col-md-5 col-lg-4"><p style="font-size: 80%;"><strong>'.preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\x9F]/u', '',$key).
							':&nbsp;</strong></p></div><div class="col-xs-6 col-sm-6 col-md-7 col-lg-8"><p style="font-size: 75%;">'.preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\x9F]/u', '',$value).'</p></div></div></div>');
					$rowCount++;
					if ($rowCount >= $maxRows) {
						$content.=('</div>');
						$rowCount = 0;
					}
				}
				//close the row
				if ($rowCount != 0) {
					$content.=('</div>');
				}
			}
			
			$content .= '
			</div><div class="'.$rightClass.'">
				'.($lat != null && $lng != null ? ('
				<div id="map" style="height:300px;"></div>
				<script>

				  function initMap() {
					var myLatLng = {lat: '.$lat.', lng: '.$lng.'};

					var map = new google.maps.Map(document.getElementById(\'map\'), {
					  zoom: 4,
					  center: myLatLng
					});

					var marker = new google.maps.Marker({
					  position: myLatLng,
					  map: map,
					  title: \''.$details['uploaded_fn'].'\'
					});
				  }
				</script>
				<script async defer
					src="https://maps.googleapis.com/maps/api/js?callback=initMap">
				</script>
				') : '').'
			</div></div>';
			
			//share on social media
			$content.='
<!-- Buttons start here. -->
<h3>Share this on social media!</h3>
<div class="row"><div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
	<ul class="rrssb-buttons">
		<li class="rrssb-facebook">
			<!--  Replace with your URL. For best results, make sure you page has the proper FB Open Graph tags in header:
			https://developers.facebook.com/docs/opengraph/howtos/maximizing-distribution-media-content/ -->
			<a href="https://www.facebook.com/sharer/sharer.php?u='.urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']).'" class="popup">
				<span class="rrssb-icon">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 29 29"><path d="M26.4 0H2.6C1.714 0 0 1.715 0 2.6v23.8c0 .884 1.715 2.6 2.6 2.6h12.393V17.988h-3.996v-3.98h3.997v-3.062c0-3.746 2.835-5.97 6.177-5.97 1.6 0 2.444.173 2.845.226v3.792H21.18c-1.817 0-2.156.9-2.156 2.168v2.847h5.045l-.66 3.978h-4.386V29H26.4c.884 0 2.6-1.716 2.6-2.6V2.6c0-.885-1.716-2.6-2.6-2.6z"/></svg>
				</span>
				<span class="rrssb-text">facebook</span>
			</a>
		</li>
		<li class="rrssb-twitter">
			<!-- Replace href with your Meta and URL information  -->
			<a href="https://twitter.com/intent/tweet?text='.urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']).'"
			class="popup">
				<span class="rrssb-icon">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 28 28"><path d="M24.253 8.756C24.69 17.08 18.297 24.182 9.97 24.62a15.093 15.093 0 0 1-8.86-2.32c2.702.18 5.375-.648 7.507-2.32a5.417 5.417 0 0 1-4.49-3.64c.802.13 1.62.077 2.4-.154a5.416 5.416 0 0 1-4.412-5.11 5.43 5.43 0 0 0 2.168.387A5.416 5.416 0 0 1 2.89 4.498a15.09 15.09 0 0 0 10.913 5.573 5.185 5.185 0 0 1 3.434-6.48 5.18 5.18 0 0 1 5.546 1.682 9.076 9.076 0 0 0 3.33-1.317 5.038 5.038 0 0 1-2.4 2.942 9.068 9.068 0 0 0 3.02-.85 5.05 5.05 0 0 1-2.48 2.71z"/></svg>
				</span>
				<span class="rrssb-text">twitter</span>
			</a>
		</li>
		<li class="rrssb-reddit">
			<a href="http://www.reddit.com/submit?url='.urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']).'">
				<span class="rrssb-icon">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 28 28"><path d="M11.794 15.316c0-1.03-.835-1.895-1.866-1.895-1.03 0-1.893.866-1.893 1.896s.863 1.9 1.9 1.9c1.023-.016 1.865-.916 1.865-1.9zM18.1 13.422c-1.03 0-1.895.864-1.895 1.895 0 1 .9 1.9 1.9 1.865 1.03 0 1.87-.836 1.87-1.865-.006-1.017-.875-1.917-1.875-1.895zM17.527 19.79c-.678.68-1.826 1.007-3.514 1.007h-.03c-1.686 0-2.834-.328-3.51-1.005a.677.677 0 0 0-.958 0c-.264.265-.264.7 0 1 .943.9 2.4 1.4 4.5 1.402.005 0 0 0 0 0 .005 0 0 0 0 0 2.066 0 3.527-.46 4.47-1.402a.678.678 0 0 0 .002-.958c-.267-.334-.688-.334-.988-.043z"/><path d="M27.707 13.267a3.24 3.24 0 0 0-3.236-3.237c-.792 0-1.517.287-2.08.76-2.04-1.294-4.647-2.068-7.44-2.218l1.484-4.69 4.062.955c.07 1.4 1.3 2.6 2.7 2.555a2.696 2.696 0 0 0 2.695-2.695C25.88 3.2 24.7 2 23.2 2c-1.06 0-1.98.616-2.42 1.508l-4.633-1.09a.683.683 0 0 0-.803.454l-1.793 5.7C10.55 8.6 7.7 9.4 5.6 10.75c-.594-.45-1.3-.75-2.1-.72-1.785 0-3.237 1.45-3.237 3.2 0 1.1.6 2.1 1.4 2.69-.04.27-.06.55-.06.83 0 2.3 1.3 4.4 3.7 5.9 2.298 1.5 5.3 2.3 8.6 2.325 3.227 0 6.27-.825 8.57-2.325 2.387-1.56 3.7-3.66 3.7-5.917 0-.26-.016-.514-.05-.768.965-.465 1.577-1.565 1.577-2.698zm-4.52-9.912c.74 0 1.3.6 1.3 1.3a1.34 1.34 0 0 1-2.683 0c.04-.655.596-1.255 1.396-1.3zM1.646 13.3c0-1.038.845-1.882 1.883-1.882.31 0 .6.1.9.21-1.05.867-1.813 1.86-2.26 2.9-.338-.328-.57-.728-.57-1.26zm20.126 8.27c-2.082 1.357-4.863 2.105-7.83 2.105-2.968 0-5.748-.748-7.83-2.105-1.99-1.3-3.087-3-3.087-4.782 0-1.784 1.097-3.484 3.088-4.784 2.08-1.358 4.86-2.106 7.828-2.106 2.967 0 5.7.7 7.8 2.106 1.99 1.3 3.1 3 3.1 4.784C24.86 18.6 23.8 20.3 21.8 21.57zm4.014-6.97c-.432-1.084-1.19-2.095-2.244-2.977.273-.156.59-.245.928-.245 1.036 0 1.9.8 1.9 1.9a2.073 2.073 0 0 1-.57 1.327z"/></svg>
				</span>
				<span class="rrssb-text">reddit</span>
			</a>
		</li>
		<li class="rrssb-googleplus">
			<!-- Replace href with your meta and URL information.  -->
			<a href="https://plus.google.com/share?url='.urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']).'" class="popup">
				<span class="rrssb-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M21 8.29h-1.95v2.6h-2.6v1.82h2.6v2.6H21v-2.6h2.6v-1.885H21V8.29zM7.614 10.306v2.925h3.9c-.26 1.69-1.755 2.925-3.9 2.925-2.34 0-4.29-2.016-4.29-4.354s1.885-4.353 4.29-4.353c1.104 0 2.014.326 2.794 1.105l2.08-2.08c-1.3-1.17-2.924-1.883-4.874-1.883C3.65 4.586.4 7.835.4 11.8s3.25 7.212 7.214 7.212c4.224 0 6.953-2.988 6.953-7.082 0-.52-.065-1.104-.13-1.624H7.614z"/></svg>            </span>
				<span class="rrssb-text">google+</span>
			</a>
		</li>
	</ul>
	<!-- Buttons end here -->
	
</div></div>
			';
		}
	}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Citizen Science Hub - Image View</title>
		<link rel="stylesheet" href="../../css/bootstrap.min.css">
		<link rel="stylesheet" href="../../css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="../../css/whhg.css">
		<link rel="stylesheet" href="../../css/template.css">
		<link rel="stylesheet" href="../../node_modules/rrssb/css/rrssb.css" />
		<?php
			echoIconAndMobileHeader(2, "\t", '../../');
		?>
		
	</head>
	<body>
		<div id="header">
			<?php
				//Echos the navbar
				//parameters: indent level, indent character(s), how to get to the root www directory.
				echoManageNavbar(3, "\t", '../../');
			?>
		</div>
		<div class="jumbotron">
			<div class="panel panel-default">
				<div class="panel-heading"><strong><?php echo(isset($details) && isset($details['uploaded_fn']) ? $details['uploaded_fn'] : '404'); ?></strong></div>
				<div class="panel-body">
					<?php echo($content); ?>
				</div>
			</div>
		</div> 
		<script src="../../js/jquery-1.12.3.min.js"></script>
		<script src="../../js/bootstrap.min.js"></script>
	</body>
</html>