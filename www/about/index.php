<?php
	/*
		This is the about us page.
	*/
	
	session_start();
	require('../php/db_connection.php');
	require('../php/db_user.php');
	require('../php/db_userPerms.php');
	require('../php/template_default.php');
	require('../php/pageRedirect.php');
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Citizen Science Hub - About Us</title>
		<link rel="stylesheet" href="../css/bootstrap.min.css">
		<link rel="stylesheet" href="../css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="../css/whhg.css">
		<link rel="stylesheet" href="../css/template.css">
		<!---    <style type="text/css">
			body{
			min-height: 2000px;
			padding-top: 70px;
			}
			.container{
			margin: 5px;
			}
			
		</style> -->
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
			<div class="container">
				<h3>About This Project</h3>
				<p><font size="3">This web portal is an IT capstone project commissioned by Dr Matthew Dunbabin to support his image recognition machine learning algorithm. The current “processed image” algorithm is just a placeholder, as Dr Matthew cannot provide us with his IP at this time.</font></p>
				
				
				<h3>Dr Matthew Dunbabin</h3>
				
				<TABLE>
					<TR>
						<TD style="vertical-align:top;"><img style="max-width:100px; margin: 10px" class="img-responsive2" src="../images/drdun.png"></TD>
						<TD><h5>Positions within Queensland University of Technology</h5>
							<ul>
								<li>Principal Research Fellow (Autonomous Systems)</li>
								<li>Science and Engineering Faculty</li>
								<li>Electrical Engineering, Computer Science</li>
								<li>Robotics  and Autonomous Systems</li>
							</ul>
						</TD>
					</TR>
				</TABLE>
				<text>Dr Matthew works tirelessly in his efforts to help conserve the Great Barrier Reef. His latest project is a machine learning algorithm that processes and analyses images from the reef. From pictures of sand, coral, and seaweed to the detrimental Crown of Thorns Starfish, Dr Matthew hopes his algorithm can be used to track reef degradation (from marine pests) in geo-specific areas through mass analysis of photographs.
					<br>
					To learn more about Dr Matthews research projects click here
					<br>
					<a href="https://wiki.qut.edu.au/display/cyphy/Matthew+Dunbabin">https://wiki.qut.edu.au/display/cyphy/Matthew+Dunbabin</a> 
				</text>
				
			</div>
			
			
			<div class="container">
				<div class="row">
					
					<div class="col-md-3">
						<h3>Joseph Cumines</h3>
						
						<TABLE>
							<TR>
								<TD style="vertical-align:top;"><img style="max-width:100px; margin: 5px" class="img-responsive2" src="../images/joey.png"></TD>
								<TD>Lead developer and coding wiz, Joey developed the framework for this project from the ground up. Joey is fluent in 
									
									<ul>
										<li>C++</li>
										<li>Java</li>
										<li>PHP</li>
										<li>Python</li>
										<li>and more</li>
									</ul>
									
								</TD>
							</TR>
						</TABLE>
						
					</div>
					
					<div class="col-md-3">
						<h3>Alex Cartwright</h3>
						
						<TABLE>
							<TR>
								<TD style="vertical-align:top;"><img style="max-width:100px; margin: 5px" class="img-responsive2" src="../images/alex.png"></TD>
								<TD>Database design.
									<br>
									Alex developed an efficient and scalable database for storing pre/post processed images and metadata.
									
								</TD>
							</TR>
						</TABLE>
						
					</div>
					
					<div class="col-md-3">
						<h3>Jerry Dang</h3>
						
						<TABLE>
							<TR>
								<TD style="vertical-align:top;"><img style="max-width:100px; margin: 5px" class="img-responsive2" src="../images/jerry.png"></TD>
								<TD>
									Interaction and GUI designer.
									<br>
									Jerry has a natural intuition for colour schemes and interface layouts, making beautiful cross platform designs with ease.
								</TD>
							</TR>
						</TABLE>
						
					</div>
					
					<div class="col-md-3">
						<h3>Levi Davison</h3>
						
						<TABLE>
							<TR>
								<TD style="vertical-align:top;"><img style="max-width:100px; margin: 5px" class="img-responsive2" src="../images/levi.png"></TD>
								<TD>
									Project manager
									<br>
									Levi coordinated group members to complete tasks on time and develop functionality according to stakeholder needs. 
								</TD>
							</TR>
						</TABLE>
						
					</div>
				</div>
			</div>
		</div>
			
		
		<script src="../js/jquery-1.12.3.min.js"></script>
		<script src="../js/bootstrap.min.js"></script>
	</body>
</html>