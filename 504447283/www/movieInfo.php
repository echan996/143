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
		<h3>Add Movie Information</h3>
			<form action="./movieInfo.php" method="GET">
				<div class="input-group">
			    <label for="title">Title:</label>
			    <input type="text" class="form-control" name="title" maxlength="100">
			  </div>
			  <div class="input-group">
			    <label for="year">Year:</label>
			    <input type="text" class="form-control" name="year" maxlength="4">
			  </div>
			  <div class="input-group">
			    <label for="rating">Rating:</label>
			    <input type="text" class="form-control" name="rating" maxlength="10">
			  </div>
			  <div class="input-group">
			    <label for="company">Company:</label>
			    <input type="text" class="form-control" name="company" maxlength="50">
			  </div>
			  <div class="input-group">
			    <label for="genre[]">Genre:</label>
						<input type="checkbox" name="genre[]" value="Action">Action</input>
						<input type="checkbox" name="genre[]" value="Adult">Adult</input>
						<input type="checkbox" name="genre[]" value="Adventure">Adventure</input>
						<input type="checkbox" name="genre[]" value="Animation">Animation</input>
						<input type="checkbox" name="genre[]" value="Comedy">Comedy</input>
						<input type="checkbox" name="genre[]" value="Crime">Crime</input>
						<input type="checkbox" name="genre[]" value="Documentary">Documentary</input>
						<input type="checkbox" name="genre[]" value="Drama">Drama</input>
						<input type="checkbox" name="genre[]" value="Family">Family</input>
						<input type="checkbox" name="genre[]" value="Fantasy">Fantasy</input>
						<input type="checkbox" name="genre[]" value="Horror">Horror</input>
						<input type="checkbox" name="genre[]" value="Musical">Musical</input>
						<input type="checkbox" name="genre[]" value="Mystery">Mystery</input>
						<input type="checkbox" name="genre[]" value="Romance">Romance</input>
						<input type="checkbox" name="genre[]" value="Sci-Fi">Sci-Fi</input>
						<input type="checkbox" name="genre[]" value="Short">Short</input>
						<input type="checkbox" name="genre[]" value="Thriller">Thriller</input>
						<input type="checkbox" name="genre[]" value="War">War</input>
						<input type="checkbox" name="genre[]" value="Western">Western</input>
			  </div>
			  <br>
				<input type="submit"  class="btn btn-default" value="Submit"/>
			</form>
		</div>
	</body>

	<?php
		if($_GET["year"] == "" || $_GET["title"] == "" || $_GET["rating"] == "" || $_GET["company"] == "") {
			exit();
		}
		$db= mysqli_connect("localhost", "cs143", "");
		if(!$db)
			die("Unable to connect to database.");
		if(!mysqli_select_db($db,"CS143"))
			die("Unable to select DB.");
		
		$title = mysqli_real_escape_string($db, trim($_GET["title"]));

		$year_string = mysqli_real_escape_string($db, trim($_GET["year"]));
		$rating = mysqli_real_escape_string($db, trim($_GET["rating"]));
		$company = mysqli_real_escape_string($db, trim($_GET["company"]));
		$genres = $_GET["genre"];
		$maxid=mysqli_fetch_array(mysqli_query($db,"SELECT id FROM MaxMovieID"))[0];
		if(!$maxid)
			echo "Query failed.";
		$newid=$maxid+1;
		//validate title input
		if($title == "" || preg_match('/[^0-9A-Za-z\.\-\' ]/',$title))
			echo 'Invalid title.';
		//validate year input
		else if($year_string == "" || preg_match('/[^0-9]/', $year_string) || intval($year_string) > 2016)
			echo "Invalid year.";
		//validate rating
		else if($rating !== 'G' && $rating !== 'PG' && $rating !=  'PG-13' && $rating !== 'R' && $rating !== 'NC-17')
			echo "Invalid MPAA rating.";
		//validate company
		else if($company == "" || preg_match('/[^A-Za-z\.\-\' ]/',$company))
			echo 'Invalid company name.';
		else {
			$year = intval($year_string);
			//run the queries
			$tuple1 = "INSERT INTO Movie VALUES ('$newid', '$title', '$year', '$rating', '$company')";
		
			if(mysqli_query($db,$tuple1)){
				while(!mysqli_query($db,"UPDATE MaxMovieID SET id='$newid' WHERE TRUE" ));
			}

			foreach ($genres as $genre) {
				$tuple2 = "INSERT INTO MovieGenre VALUES ('$newid', '$genre')";
				if(! mysqli_query($db, $tuple2)) {
					echo mysqli_error($db);
				}
			}
		}
		mysql_close($db);

	?>

	</html>

