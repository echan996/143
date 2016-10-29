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
		<h3>Add Movie Information</h3>
			<form action="./movieInfo.php" method="GET">
				Title:	<input type="text" name="title" maxlength="100"><br>
				Year:	<input type="text" name="year" maxlength="4"><br>
				Rating: <input type="text" name="year" maxlength="10"><br>
				Company: <input type="text" name="company" maxlength="50"><br>
				Genre: <input type="text" name="genre" maxlength="20"><br>
				<input type="submit" value="Submit"/>
			</form>
	</body>

	<?php
		$db= mysqli_connect("localhost", "cs143", "");
		if(!$db)
			die("Unable to connect to database.");
		if(!mysqli_select_db($db,"CS143"))
			die("Unable to select DB.");
		
		$title = mysqli_real_escape_string($db, trim($_GET["title"]));

		$year_string = mysqli_real_escape_string($db, trim($_GET["year"]));
		$rating = mysqli_real_escape_string($db, trim($_GET["rating"]));
		$company = mysqli_real_escape_string($db, trim($_GET["company"]));
		$genre = mysqli_real_escape_string($db, trim($_GET["genre"]));
		$maxid=mysqli_fetch_array(mysqli_query($db,"SELECT id FROM MaxMovieID"))[0];
		if(!$maxid)
			echo "Query failed.";
		$newid=$maxid+1;
		//validate title input
		if(!preg_match('/^[A-Za-z\.\-\']+$/',$title))
			echo 'Invalid title.';
		//validate year input
		if(!preg_match('/^[0-9]/', $year_string) || intval($year_string) > 2016)
			echo "Invalid year.";
		$year = intval($year_string);
		//validate rating
		if($rating !== 'G' && $rating !== 'PG' && $rating !=  'PG-13' && $rating !== 'R' && $rating !== 'NC-17')
			echo "Invalid MPAA rating.";
		//validate company
		if(!preg_match('/^[A-Za-z\.\-\']+$/',$company))
			echo 'Invalid company name.';
		//validate genre
		if(!preg_match('/^[A-Za-z\.\-\']+$/',$genre))
			echo 'Invalid Genre.';

		$tuple1 = "INSERT INTO Movie VALUES ('$newid', '$title', '$year', '$rating', '$company')";
		$tuple2 = "INSERT INTO MoveiGenre VALUES ('$newid', '$genre')";
		if(mysqli_query($db,$tuple)){
			while(!mysqli_query($db,"UPDATE MaxMovieID SET id='$newid' WHERE TRUE" ));
		}
		if(mysqli_query($db,$tuple2)){
			while(!mysqli_query($db,"UPDATE MaxMovieID SET id='$newid' WHERE TRUE" ));
		}
		$maxid=mysqli_fetch_array(mysqli_query($db,"SELECT id FROM MaxMovieID"))[0];
			echo "$maxid";

	?>

	</html>

