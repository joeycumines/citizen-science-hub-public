<?php
	/*
		This page is the index for the manage directory, and is the landing page
		for the logged in user.
		
		If we have just logged in we display a prompt to add to your homescreen.
	*/
	
	//redirect if we are not logged in.
	session_start();
	require('../php/db_connection.php');
	require('../php/db_user.php');
	require('../php/db_userPerms.php');
	require('../php/pageRedirect.php');
	
	if (!user_loggedIn()) {
		//if we are not logged in, then redirect to login page.
		echoRedirectPage('signin/');
		die();
	}
	
	//require the default template for the navbar
	require('../php/template_default.php');
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Citizen Science Hub</title>
		<link rel="stylesheet" href="../css/bootstrap.min.css">
		<link rel="stylesheet" href="../css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="../css/whhg.css">
		<link rel="stylesheet" href="../css/template.css">
		<!--        <style type="text/css">
			body{
			min-height: 2000px;
			padding-top: 70px;
			}
			.container{
			margin: 5px;
			}
			
		</style>-->
		
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
			<!-- Main jumbotron for a primary marketing message or call to action -->
		</div>
		<div class="jumbotron">
			<div class="container">
				<?php
					//prompt to add to home screen on mobile devices
					$useragent=$_SERVER['HTTP_USER_AGENT'];
					if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
						if (!isset($_SESSION['WE_PROMPTED_MOBILE'])) {
							$_SESSION['WE_PROMPTED_MOBILE'] = true;
							echoBSAlertBox(2, "\t", 'info', 'glyphicon-info-sign', 'Mobile App', 'Did you know you can add us to your mobile desktop? Open your browser settings and choose "add to homescreen".');
						}
					}
				?>
				<h2>Your Dashboard</h2>
				<p>Where would you like to go? You can upload and manage images, manage your account, or view a live feed of recent processed uploads.</p>
				<p><a class="btn btn-primary btn-lg" href="upload" role="button">Upload photos &raquo;</a></p>
				<h3>Recently Processed Images</h3>
				<div id="liveFeed">
				</div>
			</div>
		</div>
		<script src="../js/jquery-1.12.3.min.js"></script>
		<script src="../js/bootstrap.min.js"></script>
		<script>

var feed = [];

function updateFeed(next) {
	//very simple logic here, if we have different results then we rewrite
	if (JSON.stringify(feed) != JSON.stringify(next)) {
		feed = next;
		var theDiv = document.getElementById('liveFeed');
		theDiv.innerHTML = '';
		var colCount = 0;
		for (var x = 0; x < feed.length; x++) {
			//if we need to open a new row
			if (colCount == 0) {
				theDiv.innerHTML += '<div class="row">';
			}
			theDiv.innerHTML += '<div class="col-sm-4 col-md-4"><div class="panel panel-default"><div class="panel-heading"><strong>'+feed[x].uploaded_fn+'</strong> '+
			'<small>Uploaded at '+feed[x].uploaded_dt+'</small></div><div class="panel-body">'+
			'<a href="../browse/image/?id='+feed[x].processed_id+'"><img src="../../browse/image/source/?thumbnail=true&retainAspect=true&width=200&height=200&id='+
			feed[x].source_id+'" class="img-responsive img-round" alt="processed image" style="max-height:200px;"></a>'+
			'</div></div></div>';
			colCount++;
			
			//if we need to close the row
			if (colCount >= 3) {
				colCount = 0;
				theDiv.innerHTML += '</div>';
			}
		}
		//if we need to close
		if (colCount != 0) {
			theDiv.innerHTML += '</div>';
		}
	}
}
function getFeed() {
	if (updatingTable == false) {
		updatingTable = true;
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (xhttp.readyState == 4 && xhttp.status == 200) {
				//update the table.
				updateFeed(JSON.parse(xhttp.responseText));
			}
			if (xhttp.readyState == 4)
				updatingTable = false;
		};
		xhttp.open("GET", "feed.php", true);
		xhttp.send();
	}
}
var updatingTable = false;
getFeed();
var updateTable = window.setInterval(getFeed, 1000);
		</script>
	</body>
</html>