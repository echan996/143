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
		<h3>Add Actor/Movie Association</h3>
			<form action="./ActorToMovie.php" method="GET">
			
				Actor:	<input type="text" name="name" maxlength="40"><br>
				Role:	<input type="text" name="role" maxlength="50"><br>
				Movie: <input type="text" name="movie" maxlength="100"><br>
				<input type="submit" value="Submit"/>
				
			</form>
	</body>
	<?php

		$db= mysqli_connect("localhost", "cs143", "");
		if(!$db)
			die("Unable to connect to database.");
		if(!mysqli_select_db($db,"CS143"))
			die("Unable to select DB.");
		$actor_name=mysqli_real_escape_string($db, trim($_GET("name")));
		$role = mysqli_real_escape_string($db, trim($_GET("role")));
		$movie = mysqli_real_escape_string($db, trim($_GET("movie")));
		
		if($actor_name=="")
			echo "Invalid Actor name.";
		$words = preg_split('/\s+/',$actor_name,-1,PREG_SPLIT_NO_EMPTY);
		$first = $words[0];
		$last = $words[1];
		
		else if(!preg_match('/^[A-Za-z\.\-\']+$/',$first) || !preg_match('/^[A-Za-z\.\-\']+$/',$last))
	?>
</html>