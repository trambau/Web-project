<?php 
    include('functions.php');
    //Check if user is logged inS
if (!isLoggedIn()) {
	$_SESSION['msg'] = "You must log in first";
	header('location: login.php');
}
echo $_SESSION['user']['id'];
//update the annotator in the annotaion table
if(isset($_GET['uid']) && isset($_GET['pepid']) && !empty($_GET['uid'])){
	global $myPDO;
	$id=$_GET['uid'];
	$pepid=$_GET['pepid'];
	$query="UPDATE annot SET annotator=(SELECT id FROM users WHERE email=:id) WHERE annotid=:pepid;";
	try{
		$stmt=$myPDO->prepare($query);

		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		$stmt->bindParam(":pepid", $pepid, PDO::PARAM_STR);
		$stmt->execute();
		
		if($stmt){
			echo "<script>alert('Sequence assigned ');</script>";
	}
	}catch(Exception $e){
		die($e->getMessage());
	}
}
//REJECT the annotation and put a comment 
if(!empty($_GET['annotationid']) && isset($_GET['comment'])){
	global $myPDO;
	$id=$_GET['annotationid'];
	$com=$_GET['comment'];
	$query="UPDATE annot SET geneid='', transcript='', genetype='', transcrypttype='', symbol='', description=:com, validated=0 WHERE annotid=:id;";
	try{
		$stmt=$myPDO->prepare($query);
		$stmt->bindParam(":com", $com, PDO::PARAM_STR);
		$stmt->bindParam(":id", $id, PDO::PARAM_STR);
		$stmt->execute();
	}catch(PDOException $e){
		die($e->getMessage());
	}
}
//SEND the annotations for review
if(!empty($_GET['annotid'])){
	global $myPDO;
	$id=$_GET['annotid'];
	$query="UPDATE annot SET validated=1 WHERE annotid=:id;";
	try{
		$stmt=$myPDO->prepare($query);
		$stmt->bindParam(":id", $id, PDO::PARAM_STR);
		$stmt->execute();
	}catch(PDOException $e){
		die($e->getMessage());
	}
}
//VALIDATE the annotation and put them in the DATABASE
if(!empty($_GET['genomeid'])){
	global $myPDO;
	$id=$_GET['genomeid'];
	$query="UPDATE genome SET isannotated=1 WHERE id=:id;";
	try{
		$stmt=$myPDO->prepare($query);
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		$stmt->execute();
	}catch(PDOException $e){
		die($e->getMessage());
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Home</title>
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
	<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/base/minified/jquery-ui.min.css" type="text/css" /> 
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
    <script type="text/javascript" src="http://code.jquery.com/ui/1.10.1/jquery-ui.min.js"></script>  
</head>
<body>
<div class="header" style="background-color:dodgerblue">
        <br>
        <a style="float:right;color:red" href=".?logout='1'">logout</a>
		<h2 style="color:azure">LOGO</h2>
		
			<form style="float:right;padding:7px 6px" action="">
				<input type="text">
				<a style="color:crimson" href="search.php">Advanced Search</a>
				<input type="submit">
			</form>
		
</div>
	<div class="header">
		<h2>Home Page</h2>
	</div>
	<div class="content">
		<!-- notification message -->
		<?php if (isset($_SESSION['success'])) : ?>
			<div class="error success" >
				<h3>
					<?php 
						echo $_SESSION['success']; 
						unset($_SESSION['success']);
					?>
				</h3>
			</div>
		<?php endif ?>
		<!-- logged in user information -->
		<div class="profile_info">
			<img src="images/user_profile.png"  >

			<div>
				<?php  if (isset($_SESSION['user'])) : ?>
					<strong><?php echo $_SESSION['user']["firstname"]; ?></strong>

					<small>
						<i  style="color: #888;">(<?php echo ucfirst($_SESSION['user']["userrole"]); ?>)</i> 
						<br>
						<a href="index.php?logout='1'" style="color: red;">logout</a>
					</small>

<!-- Display for Validator-->

						<?php if(isValidator()){?>
	<div class="row">
	<div class="table-responsive col-md-4">
	<table class="table table-striped table-advance table-hover">
	<h4><i class="fa fa-angle-right"></i> Sequences in wait</h4>
		<hr>
		<thead>
		<tr>
			<th>Sequence ID</th>
			<th>Strain</th>
			<th>Chromosome ID</th>
			<th>Annotator</th>
			<th>Assign</th>
		</tr>
		</thead>
	<tbody>
	<!-- Display the non assigned sequences  !!!VALIDATE=0 TO REMOVE(MAYBE)-->
	<?php 
	global $myPDO;
	$query="SELECT name, pepid, pep.chromid FROM annot, pep, genome where annotid=pepid and pep.chromid=genome.chromid and validated=0 and annotator IS NULL;";
	$stmt=$myPDO->prepare($query);
	$stmt->execute();
	while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
	?>
	<tr>
			<td><?php echo $row['pepid'];?></td> 
			<td><?php echo $row['name'];?></td>
			<td><?php echo $row['chromid'];?></td>
			<td>
			<input type="text" class="form-control" name="annot" id="annot" value="" placeholder="email@mail.com">
<script type="text/javascript">
$(function() {
    //autocomplete
    $("#annot").autocomplete({
        source: "completeUser.php",
        minLength: 1
    });                
});
</script>
			</td>
			<td>
			<script>
				function goTo(){
					var us=document.getElementById("annot").value;
					if(us===""){
						alert('Please select an Email first.');
					}else{
						window.location.href = "index.php?uid="+us+"&pepid="+"<?php echo $row['pepid'];?>";
					}
				}
				
			</script>
			<button class="btn btn-danger btn-xs" onClick="goTo()"><i class="fa fa-trash-o "></i>Assign</button>
			</td>
	</tr>
	<?php 
	}//end while
	?>
</tbody>
</table >
</div>
<div class="table-responsive col-md-6">
	<table class="table table-striped table-advance table-hover">
	<h4><i class="fa fa-angle-right"></i>Annotations to check</h4>
		<hr>
		<thead>
		<tr>
			<th>Sequence ID</th>
			<th>Strain</th>
			<th>Annotator</th>
			<th>geneID</th>
			<th>Gene biotype</th>
			<th>transcript</th>
			<th>transcript biotype</th>
			<th>symbole</th>
			<th>Description</th>
			<th>Validate</th>
			<th>Reject</th>
		</tr>
		</thead>
		<tbody>
			<?php
			global $myPDO; 			//Get the annotation needing review.
			$query="SELECT annotid, name, genome.id as gid, email, geneid, transcript, genetype, transcrypttype, symbol, description 
			FROM annot, pep, genome, users 
			WHERE annotid=pepid AND pep.chromid=genome.chromid AND users.id=annotator AND validated=1 AND isannotated=0 AND annotator IS NOT NULL;";
			try{
				$stmt=$myPDO->prepare($query);
				$stmt->execute();
				$stmt;
			}catch(PDOException $e){
				die($e->getMessage());
			}
			while($row=$stmt->fetch()){
			?>
			<tr>
				<td><?php echo $row['annotid'];?></td>
				<td><?php echo $row['name'];?></td>
				<td><?php echo $row['email'];?></td>
				<td><?php echo $row['geneid'];?></td>
				<td><?php echo $row['genetype'];?></td>
				<td><?php echo $row['transcript'];?></td>
				<td><?php echo $row['transcrypttype'];?></td>
				<td><?php echo $row['symbol'];?></td>
				<td><?php echo $row['description'];?></td>
				<td>
					<a href="index.php?genomeid=<?php echo $row['gid'];?>">
					<button class="btn btn-info btn-xs" onClick=""><i class="fa fa-trash-o "></i>Validate</button>
					</a>
				</td>
				<td>
	<script>
	//take input from user
	function getComment(){
	var message=window.prompt("Write a comment for the annotator.");
	window.location.href = "index.php?comment="+message+"&annotationid="+"<?php echo $row['annotid'];?>";
	}
	</script>
				<button class="btn btn-danger btn-xs" onClick="getComment()"><i class="fa fa-trash-o "></i>Reject</button>
				</td>
			</tr>
			<?php	
			}//end while
			?>
		</tbody>
	</table>
</div>
</div><!--end div row-->


						<?php
						//end validator
						}elseif(isAnnotator()){//Annotator display
							?>
<div class="table-responsive col-md-6">
	<table class="table table-striped table-advance table-hover">
	<h4><i class="fa fa-angle-right"></i>Sequences to Annotate</h4>
		<hr>
		<thead>
		<tr>
			<th>Sequence ID</th>
			<th>Strain</th>
			<th>geneID</th>
			<th>Gene biotype</th>
			<th>transcript</th>
			<th>transcript biotype</th>
			<th>symbole</th>
			<th>Description</th>
			<th>Validate</th>
		</tr>
		</thead>
		<tbody>
			<?php
			global $myPDO;
			$query="SELECT DISTINCT annotid, name, pep.id as pid, genome.id as gid, geneid, transcript, genetype, transcrypttype, symbol, description 
			FROM annot, pep, genome, users 
			WHERE annotid=pepid AND pep.chromid=genome.chromid AND :id=annotator AND validated=0;";
			try{
				$stmt=$myPDO->prepare($query);
				$stmt->bindParam(":id", $_SESSION['user']['id'], PDO::PARAM_STR);
				$stmt->execute();
				$stmt;
			}catch(PDOException $e){
				die($e->getMessage());
			}
			while($row=$stmt->fetch()){//get the annotation values not yet validated.
			?>
			<?php echo $row['pid'];

			echo $row['gid'];
			echo "test";?>

			<tr>
			
				<td onclick="location.href='view.php?id=<?php echo $row['pid'];?>&type=pep'"><u style=color:dark-blue"><?php echo $row['annotid'];?></u></td>
				<td onclick="location.href='view.php?id=<?php echo $row['gid'];?>&type=genome'"><u style=color:dark-blue"><?php echo $row['name'];?></u></td>
				<td ><?php echo $row['geneid'];?></td>
				<td><?php echo $row['genetype'];?></td>
				<td><?php echo $row['transcript'];?></td>
				<td><?php echo $row['transcrypttype'];?></td>
				<td><?php echo $row['symbol'];?></td>
				<td><?php echo $row['description'];?></td>
				<td>
					<a href="index.php?rid=<?php echo $$row['annotid'];?>">
					<button class="btn btn-info btn-xs" onClick=""><i class="fa fa-trash-o "></i>Validate</button>
					</a>
				</td>
			</tr>
			<?php	
			}//end while
			?>
		</tbody>
	</table>
</div>							


							<?php //end if annotator
						}else{//User type user
						?>

						<?php
						}//end if user
						?>

				<?php endif ?>
			</div>
		</div>
	</div>
</body>
</html>