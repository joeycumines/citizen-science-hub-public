<?php

	function echoRedirectPage($redirectPath) {
		echo("
<!DOCTYPE html>
<!--The redirect code-->
<html>
	<head>
		<meta http-equiv=\"refresh\" content=\"1; url=$redirectPath\">
		<script type=\"text/javascript\">
			window.location.href = \"$redirectPath\"
		</script>
		<title>Page Redirection</title>
	</head>
	<body>
		If you are not redirected automatically, follow the <a href=\"$redirectPath\">link to your page</a>
	</body>
</html>
		");
	}

?>