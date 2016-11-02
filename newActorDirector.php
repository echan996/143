
<html>
		<head>
		<title>Query System</title>
			<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	  <!-- Latest compiled and minified JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	
    
    <style>
    	#title {
    		padding-top: .75%;
    		color: white;
    		font-size: 140%;
    	}
    	.navbar-fixed-top{
    		height: 3%;
    	}
    	.navbar-left {
    		padding-top: 3%;
    		height:100%; 
    		width: 15%;
    		background-color: rgb(238, 238, 238);
    		margin-right: 1%;
    	}
    	.main {
    		padding: 3%;
    	}
    	body{
    		overflow-y: hidden; 
    	}
    	table{
    		margin-left: 3%;
    	}
    	.input-group {
    		width: 50%;
    	}
    </style>
	</head>

	<body BGCOLOR="lightgrey">
		<nav class="navbar navbar-inverse navbar-fixed-top">
	  	<ul id="title">Query System</ul>
		</nav>

		<nav class="navbar navbar-left" >
			<table>
				<tr>
					<th>Add new content</th>
				</tr>
				<tr>
					<td><a href="newActorDirector.php">Add Actor/Director</a></td>
				</tr>
				<br>
				<tr>
					<td><a href="movieInfo.php">Add Movie Information</a></td>
				</tr>
				<tr>
					<td><a href="comment.php">Add Comment</a></td>
				</tr>
				<tr>
					<td><a href="ActorToMovie.php">Add Actor/Movie Association</a></td>
				</tr>
				<tr>
					<td><a href="DirectorToMovie.php">Add Director/Movie Association</a></td>
				</tr>
			</table>
			<table>
				<tr>
					<th>Browsing content</th>
				</tr>
				<tr>
					<td><a href="search.php">Search</a></td>
				</tr>
				<br>
			</table>
		</nav>
	<div class="main">
		<h3>Add Actor or Director</h3>
			<form action="./newActorDirector.php" method="GET">
				<div class="input-group">
					<label for="type">Type:</label> <br>
					  <input type="radio" name="job" value="Actor"> Actor
						<input type="radio" name="job" value="Director"> Director<br>
				</div>
				<div class="input-group">
			    <label for="first">First Name:</label>
			    <input type="text" class="form-control" name="first" maxlength="20">
			  </div>
			  <div class="input-group">
			    <label for="last">Last Name:</label>
			    <input type="text" class="form-control" name="last" maxlength="20">
			  </div>
			  <div class="input-group">
			  	<label for="gender">Gender:</label> <br>
				  <input type="radio" name="Gender" value="Male"> Male
						<input type="radio" name="Gender" value="Female"> Female<br>
				</div>
			  <div class="input-group">
			    <label for="dob">Date of Birth:</label>
			    <input type="text" class="form-control" name="dob" maxlength="10" placeholder="YYYY/MM/DD"> 
			  </div>
			  <div class="input-group">
			    <label for="dod">Date of Death:</label>
			    <input type="text" class="form-control" name="dod" maxlength="10" placeholder="YYYY/MM/DD if applicable">
			  </div>
			  <br>
				<input type="submit"  class="btn btn-default" value="Submit"/>
			</form>
		</div>
			</form>
	</body>

	<?php
		if($_GET["job"]=="")
			exit();
		
		$db= mysqli_connect("localhost", "cs143", "");
		if(!$db)
			die("Unable to connect to database.");
		if(!mysqli_select_db($db,"CS143"))
			die("Unable to select DB.");
		$job=$_GET["job"];
		$first=mysqli_real_escape_string($db, trim($_GET["first"]));
		$last=mysqli_real_escape_string($db, trim($_GET["last"]));
		$gender=$_GET["Gender"];
		$dob =mysqli_real_escape_string($db,trim($_GET["dob"]));
		$dod = mysqli_real_escape_string($db, trim($_GET["dod"]));

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
		else if(!preg_match('/^\d{4}\/\d{2}\/\d{2}$/',$dob))
			echo "Invalid date of birth.";
		else if($dod!="" && !preg_match('/^\d{4}\/\d{2}\/\d{2}$/',$dod))
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
		}
		mysql_close($db);
	?>
</html>