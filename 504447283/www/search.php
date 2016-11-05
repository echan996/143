
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
			position: fixed;
    	}
    	.main {
    		padding-left: 17%;
			padding-top: 4%;
    	}
    	body{
    		
    	}
    	table{
    		margin-left: 3%;

    	}
		table#t1{
			font-family: arial, sans-serif;
			border-collapse: collapse;
			width: 85%;
			
		}
		
		table#t1 td{
			border: 1px solid #dddddd;
			text-align: left;
			padding: 8px;
		} 
		table#t1 th {
			border: 1px solid #dddddd;
			text-align: left;
			padding: 8px;
		}
		table#t1 tr:nth-child(even) {
			background-color: #dddddd;
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
	<form action="search.php" method="GET">
		<input type="text" name="query">
		<input type="submit" value="Submit">
	</form>
	<?php if($_GET["query"]=="") exit();?>
	<table id="t1" align="left">
		
		<tr>
			<th>Movie Name</th>
			<th>Year</th>
		</tr>
		<?php
			
			$db= mysqli_connect("localhost", "cs143", "");
			if(!$db)
				die("Unable to connect to database.");
			if(!mysqli_select_db($db,"CS143"))
				die("Unable to select DB.");
			$query = mysqli_real_escape_string($db, trim($_GET["query"]));
			
			$query_terms=explode(' ',$query);
			if(count($query_terms)>0){
				//get movie matches
				$mysql_req = "SELECT id, year, title FROM Movie WHERE title LIKE '%$query_terms[0]%'";
				for($i=1;$i<count($query_terms);$i++){
					$mysql_req.=" AND title LIKE '%$query_terms[$i]%'";
				}
				$query_result = mysqli_query($db,$mysql_req);
				while($row=mysqli_fetch_assoc($query_result))
					echo "<tr><td> <a href=\"showMovieInfo.php?id=".$row['id']."\"".">".$row['title']."</a>	</td><td>".$row['year']."</td></tr>";
			}
		?>
	</table>
	<br><br>
	<table id ="t1" align="left">
		<tr>
			<th>Actor Name</th>
			<th>Date of Birth</th>
		</tr>
		<?php
		if(count($query_terms)>0){
				//get actor matches
				$mysql_req = "SELECT id, dob, last, first FROM Actor WHERE (first LIKE '%$query_terms[0]%' OR last LIKE '%$query_terms[0]%')";
				for($i=1;$i<count($query_terms);$i++){
					$mysql_req.=" AND (first LIKE '%$query_terms[$i]%' OR last LIKE '%$query_terms[$i]%')";
				}
				$query_result = mysqli_query($db,$mysql_req);
				while($row=mysqli_fetch_assoc($query_result))
					echo "<tr><td> <a href=\"showActorInfo.php?id=".$row['id']."\"".">".$row['first']." ".$row['last']."</a></td><td>".$row['dob']."</td></tr>";
			}
		?>
	</table>
	</div>
</html>