
<html>
	<head>
		<title>Query System</title>
		<br>
		<center><h1>Query System</h1></center>
	</head>
	<table>
		<tr>
			<a href="newActorDirector.php">Add Actor/Director</a>
		</tr>
		<br>
		<tr>
			<a href="movieInfo.php">Add Movie Information</a>
		</tr>
		<br>
		<tr>
			<a href="comment.php">Add Comment</a>
		</tr>
		<br>
		<tr>
			<a href="ActorToMovie.php">Add Actor/Movie Association</a>
		</tr>
		<br>
		<tr>
			<a href="DirectorToMovie.php">Add Director/Movie Association</a>
		</tr>
		<br>
	</table>
	
	<body BGCOLOR="lightgrey">
		<h3>Add actor or director</h3>
			<form action="./newActorDirector.php" method="GET">
			
				Type:	<input type="radio" name="job" value="Actor">Actor
					<input type="radio" name="job" value="Director"> Director<br>
				
				First Name:	<input type="text" name="first" maxlength="20"><br>
				Last Name:	<input type="text" name="last" maxlength="20"><br>
				Gender:	<input type="radio" name="Gender" value="Male">Male
					<input type="radio" name="Gender" value="Female"> Female<br>
				Date of Birth: <input type="text" name="dob" maxlength="10"> <font size="1">(YYYY/MM/DD)</font><br>
				Date of Death: <input type="text" name="dod" maxlength="10"> <font size="1">(YYYY/MM/DD) if applicable</font><br>
				<input type="submit" value="Submit"/>
				
			</form>
	</body>
	
	<?php
		//establish connection with the MySQL database
		$db_connection = mysql_connect("localhost", "cs143", "");
		
		//choose database to use
		mysql_select_db("CS143", $db_connection);

		//get the user's inputs
		$job=$_GET["job"];
		$first=trim($_GET["first"]);
		$last=trim($_GET["last"]);
		$gender=$_GET["gender"];
		$dob = date_parse(trim($_GET["dob"]));
		$dod= date_parse(trim($_GET["dod"]));
		if($job=="")
			echo "You must select Actor or Director.";
		else if(!preg_match('/^[A-Za-z\.\-\']+$/',$first) || !preg_match('/^[A-Za-z\.\-\']+$/',$last)) //name must be composed of alphabetical char and -,.,' 
			echo "Invalid first/last name.";
		else if($gender=="" && $job=="Actor")
			echo "You must select a gender for Actor.";
		else if($dob=="" || !checkdate($dob["month"], $dob["day"], $dob["year"]))
			echo "Invalid date of birth.";
		else if($dod!="" && !checkdate($dod["month"], $dod["day"], $dod["year"]))
			echo "Invalid date of death.";
		else{ // tentative valid input
		}
	?>
</html>