
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
		<tr>
			<a href="search.php">Search</a>
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

		$db= mysqli_connect("localhost", "cs143", "");
		if(!$db)
			die("Unable to connect to database.");
		if(!mysqli_select_db($db,"CS143"))
			die("Unable to select DB.");
		$job=$_GET["job"];
		$first=mysqli_real_escape_string($db, trim($_GET["first"]));
		$last=mysqli_real_escape_string($db, trim($_GET["last"]));
		$gender=$_GET["Gender"];
		$dob =$_GET["dob"];
		$checkdob = date_parse($dob);
		$dod = $_GET["dod"];
		$checkdod= date_parse($dod);
		$maxid=mysqli_fetch_array(mysqli_query($db,"SELECT id FROM MaxPersonID"))[0];
		if(!$maxid)
			echo "Query failed.";
		$newid=$maxid+1;
		if($job=="")
			echo "You must select Actor or Director.";
		else if(!preg_match('/^[A-Za-z\.\-\']+$/',$first) || !preg_match('/^[A-Za-z\.\-\']+$/',$last)) //name must be composed of alphabetical char and -,.,' 
			echo "Invalid first/last name.";
		else if($gender=="" && $job=="Actor")
			echo "You must select a gender for Actor.";
		else if($dob=="" || !checkdate($checkdob["month"], $checkdob["day"], $checkdob["year"]))
			echo "Invalid date of birth.";
		else if($dod!="" && !checkdate($checkdod["month"], $checkdod["day"], $checkdod["year"]))
			echo "Invalid date of death.";
		else{ // tentative valid input
			if($job=="Director"){
				if($dod=="")
					$tuple="INSERT INTO Director VALUES('$newid','$last','$first','$dob',NULL)";
				else
					$tuple="INSERT INTO Director VALUES('$newid','$last','$first','$dob','$dod')";
			}
			else{
				if($dod=="")
					$tuple="INSERT INTO Actor VALUES('$newid','$last','$first','$gender','$dob',NULL)";
				else{
					$tuple="INSERT INTO Actor VALUES('$newid','$last','$first','$gender','$dob','$dod')";
				}
			}
			if(mysqli_query($db,$tuple)){
				while(!mysqli_query($db,"UPDATE MaxPersonID SET id='$newid' WHERE TRUE" ));
			}
			$maxid=mysqli_fetch_array(mysqli_query($db,"SELECT id FROM MaxPersonID"))[0];
			//echo "$maxid";
		}
		
	?>
</html>