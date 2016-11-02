<html>
	<head>
		<style>
			table, th, td {
			    border: 1px solid black;
			    border-collapse: collapse;
			}
		</style>
	</head>
	<h1>Query</h1>
	<form>
		<TEXTAREA type="text" name="query" ROWS=15 COLS=70></TEXTAREA>
		<input type="submit">
		<table id="results">
		<br>
		<?php
			$query = $_GET["query"];
			$db = new mysqli('localhost', 'cs143', '', 'TEST');
			if ($db->connect_errno > 0){
			    die('Unable to connect to database [' . $db->connect_error . ']');
			}			
			$rs = $db->query($query);
			if($rs->num_rows <= 0) return;
			$result = "";
			while($row = $rs->fetch_assoc()) {
					$result .= '<tr>';
					foreach($row as $i) {
						$result .= '<td>' . $i . '</td>';
					}
			    $result .= '</tr>';
			}
			echo $result;
		?>
		</table>
	</form>
</html>