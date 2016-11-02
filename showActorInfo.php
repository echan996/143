
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
	<div class='main'>
		<?php
			$db= mysqli_connect("localhost", "cs143", "");
			if(!$db)
				die("Unable to connect to database.");
			if(!mysqli_select_db($db,"CS143"))
				die("Unable to select DB.");
			$aid = $_GET["id"];
			$mysql_req="SELECT * FROM Actor WHERE id='$aid'";
			$row = mysqli_fetch_assoc(mysqli_query($db,$mysql_req));
			if($row==NULL){
				echo "<h3>Error Actor not found in database.</h3>";
				exit();
			}
		?>
		<h4>Actor Information</h4>
		<table id='t1'>
			<tr>
				<th>Name</th>
				<th>Sex</th>
				<th>Date of Birth</th>
				<th>Date of Death</th>
			</tr>
			<tr>
				<td><?php echo $row["first"]." ".$row["last"]; ?></td>
				<td><?php echo $row["sex"];?></td>
				<td><?php echo $row["dob"]; ?></td>
				<td><?php if($row["dod"]==NULL) echo "Still Living"; else echo $row["dod"]; ?></td>
			</tr>
		
		</table>
		<table id ='t1'>
			<tr>
				<th>Role</th>
				<th>Movie</th>
			</tr>
			<br><br>
			<tr>
				<?php
					$mysql_rq = "SELECT role, title FROM MovieActor MA, Movie M WHERE MA.aid='$aid' AND MA.mid=M.id";
					$query = mysqli_query($db,$mysql_rq);
					while($row = mysqli_fetch_assoc($query)){
						echo "<tr><td>{$row['role']}</td><td>{$row['title']}</td></tr>";
					}
				?>
			</tr>
		</table>
	</div>
</html>