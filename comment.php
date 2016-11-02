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
				<h3> Add Movie Comment </h3>
				<form action="./comment.php" method="GET">
					<div class="input-group">
					  <label for="sel1">Movie:</label>
					  <select class="form-control" name="sel1">
					  	<option></option>
					  	<?php
					  		$db= mysqli_connect("localhost", "cs143", "");
								if(!$db)
									die("Unable to connect to database.");
								if(!mysqli_select_db($db,"CS143"))
									die("Unable to select DB.");

								$query = "SELECT id, title FROM Movie ORDER BY title ASC";
								$res = mysqli_query($db, $query);
								while (($row = mysqli_fetch_assoc($res)) != null)
								{
										if($row['title']!= "")
								    	echo "<option value = '{$row['id']}'> {$row['title']} </option>";
								}
					  	?>
					  </select>
					</div>
					<div class="input-group">
			    	<label for="reviewer">Reviewer Name:</label>
			    	<input type="text" class="form-control" name="reviewer" maxlength="20">
			  	</div>
			  	<div class="input-group">
			  		<label for="sel2">Rating: </label>
			  		<select class="form-control" name="sel2">
			  			<option>1</option>
			  			<option>2</option>
			  			<option>3</option>
			  			<option>4</option>
			  			<option>5</option>
			  		</select>
			  	</div>
			  	<div class="input-group">
			  			<label for="comment">Comment: </label>
			  		<textarea class = "form-control" name = "comment" rows = "5"></textarea>
			  	</div>
			  <input type="submit"  class="btn btn-default" value="Submit"/>
			 </form>
		</div>
	</body>
	<?php
		if($_GET["sel1"] == "" || $_GET["sel2"] == "" 
			|| $_GET["reviewer"] == "" || $_GET["comment"] == "") 
			exit();

		$db= mysqli_connect("localhost", "cs143", "");
		if(!$db)
			die("Unable to connect to database.");
		if(!mysqli_select_db($db,"CS143"))
			die("Unable to select DB.");
		
		$name = mysqli_real_escape_string($db, trim($_GET["reviewer"]));
		$mid = $_GET["sel1"];
		$rating = $_GET["sel2"];
		$comment = mysqli_real_escape_string($db, trim($_GET["comment"]));

		$query = "INSERT INTO Review VALUES ('$name', now() , '$mid', '$rating', '$comment')";
		if(mysqli_query($db, $query))
				echo "Failed to update table";
	?>
</html>