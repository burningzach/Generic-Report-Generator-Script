<?php

$host  = "hostname";
$user = "username";  
$pass = "password";
$dbase = "dbasename";


$mysqli = mysqli_connect($host,$user,$pass,$dbase) or die (mysqli_error());

if(isset($_POST['name'])) $name = $_POST['name'];
if(isset($_POST['process_uid'])) $process_uid = $_POST['process_uid'];
if(isset($_POST['process_name'])) $process_name = $_POST['process_name'];
if(isset($_POST['name']) && isset($_POST['process_uid']) && isset($_POST['process_name'])) {
	mysqli_query($mysqli, "INSERT INTO report_types(name, process_uid, processname) VALUES('$name', '$process_uid', '$process_name')")or die("Unable to save.");
}
$flag = 0;


// generate the inserted id
$insert_id = mysqli_insert_id($mysqli);


if(isset($_POST['new_field'])) {
	$new_fields = $_POST['new_field'];

	$report_fields = array();
	$i = 0;
	foreach($new_fields as $f) {
		$report_fields[$i]['name'] = $f;
		$report_fields[$i]['report_id'] = $insert_id;
		$i++;
	}

	foreach($report_fields as $r) {
		$name = $r['name'];
		$report_id = $r['report_id'];
		mysqli_query($mysqli, "INSERT INTO report_fields(report_id, name) VALUES('$report_id', '$name')")or die("Unable to save.");
	}
 
}

// Deleting report_type Starts here
if(isset($_GET['delete_id'])) $delete_id = $_GET['delete_id'];

if(isset($_GET['delete_id'])) {
	$query = "DELETE FROM report_types WHERE report_type_id = '$delete_id'";
	mysqli_query($mysqli, $query);
	$query = "DELETE FROM report_fields WHERE report_id = '$delete_id'";
	mysqli_query($mysqli, $query);	

  $flag = 1;
}
// End deleting report type


// Retrieve all process names
$query = "SELECT P.PRO_UID, CON_VALUE
			FROM PROCESS P
			INNER JOIN CONTENT C
			ON C.CON_ID = P.PRO_UID
			INNER JOIN APPLICATION A
			ON A.PRO_UID = C.CON_ID
			GROUP BY CON_VALUE
			";
$result = mysqli_query($mysqli,$query);
//var_dump($result);
$processes = array();
while($process = mysqli_fetch_assoc($result)) {
	$arr = array(
		'process_uid' => $process['PRO_UID'],
		'process_name' =>$process['CON_VALUE']
		);
	array_push($processes, $arr);
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
  <link rel="stylesheet" type="text/css" href="form.css" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	<style type="text/css">
	input {
		display: block;
		margin-bottom: 5px;
		margin-top: 5px;
	}

	div > input {
		display: inline-block;
	}

	table tr > td {
		padding: 5px;
		border-bottom: 1px solid #999;
	}

	table tr > td > a {
		margin-left: 10px;
	}

	</style>
  </head>
  <body style="background: lightblue;">
  <div class="form"><br /><br /><br />
  	<form action="index.php" id="" method="POST" enctype="multipart/form-data">
  	<p>Add Report</p>
  	<br	/>
  	<label>Report name: </label>
  	<input type="text" name="name" />
  	<input type="hidden" name="process_uid" id="process_uid" />
  	<select id="process" name="process_name">
  		<option></option>
  		<?php foreach($processes as $p) { ?>
  		
  		<option value="<?php echo $p['process_name']; ?>" data-process-uid="<?php echo $p['process_uid']; ?>"><?php echo $p['process_name']; ?></option>

  		<?php } ?>
  	</select>
  	<br />
  	<button id="add-field" class="button">Add Field</button>
  	<!-- <p>Show Delegations</p>
  	<input type="radio" name="show_delegation" value="Y" style="display: inline;"> <span>Yes</span>
  	<input type="radio" name="show_delegation" value="N" style="display: inline;"> <span>No</span> -->

  	<button style="margin-top: 20px;display: block;" class="button">Submit</button>
  	<hr />
  	<table>
  		<thead>
  			<tr>
  				<td>Report Name</td>
  				<td>Process ID</td>
  				<td>Process Name</td>
  				<td>Action</td>
  			</tr>
  		</thead>
  		<tbody>
  		<?php
  		$query = "SELECT * FROM report_types";
		$result = mysqli_query($mysqli,$query);
		$reports = array();
		while($reports = mysqli_fetch_assoc($result)) {
  		?>
  			<tr>
  				<td><?php echo $reports['name']; ?></td>
  				<td><?php echo $reports['process_uid']; ?></td>
  				<td><?php echo $reports['processname']; ?></td>
  				<td><a href="generate.php?<?php echo "report_id=".$reports['report_type_id']."&process_uid=".$reports['process_uid']; ?>" class="button">View Report</a><a href="edit.php?id=<?php echo $reports['report_type_id']; ?>" class="button">Edit</a><a href="index.php?delete_id=<?php echo $reports['report_type_id']; ?>" class="button">Delete</a></td>
  			</tr>
  		<?php } ?>
  		</tbody>
  	</table>
  </body>
</div>
  <script type="text/javascript">
  	$(document).ready(function() {

  		$(document).on('change', '#process', function(event) {
  			event.preventDefault();
  			/* Act on the event */
  			var process_uid = $(this).find("option:selected").attr("data-process-uid");
  			$("#process_uid").val(process_uid);
  		});

  		$(document).on('click', '#add-field', function(event) {
  			event.preventDefault();
  			/* Act on the event */
  			var me = $(this);
  			me.before('<div><input type="text" name="new_field[]" /><button class="remove-field">Remove Field</button></div>');
  		});

  		$(document).on('click', '.remove-field', function(event) {
  			event.preventDefault();
  			/* Act on the event */
  			var me = $(this);
  			me.parent().remove();
  		});

  	});
  </script>
   <?php
  if($flag === 1) { ?>
  <script language='javascript' type='text/javascript'>alert('Successfully Deleted!')</script>
  <script language='javascript' type='text/javascript'>window.open('index.php?sucess=1','_self')</script>
  <?php
  }
  ?>
  
</html>
