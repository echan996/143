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
		<h3>Add Movie/Director Association</h3>
			<form action="./DirectorToMovie.php" method="GET">
			
				Director:	<input type="text" name="name" maxlength="40"><br>
				Movie: <input type="text" name="movie" maxlength="100"><br>
				<input type="submit" value="Submit"/>
				
			</form>
	</body>
	<?php
		
		
		if($_GET["name"]=="")
			exit();
		$db= mysqli_connect("localhost", "cs143", "");
		if(!$db)
			die("Unable to connect to database.");
		if(!mysqli_select_db($db,"CS143"))
			die("Unable to select DB.");
		
		$did=mysqli_real_escape_string($db, trim($_GET["name"]));
		$mid = mysqli_real_escape_string($db, trim($_GET["movie"]));
		
		$tuple = "INSERT INTO MovieDirector Values('$mid','$did')";

		if(!mysqli_query($db,$tuple))
			echo "Failed to update table";
		
		
	?>
</html>