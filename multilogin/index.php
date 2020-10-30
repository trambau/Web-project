<?php 
    include('functions.php');
    //Check if user is logged inS
if (!isLoggedIn()) {
	$_SESSION['msg'] = "You must log in first";
	header('location: login.php');
}
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
if(!empty($_GET['rid'])){
	global $myPDO;
	$id=$_GET['rid'];
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
//UPDATE annotation
if(isset($_POST['save-btn']) && !empty($_GET['upid'])){
	global $myPDO; 
	$query="UPDATE annot SET geneid=:geneid, transcript=:trans, genetype=:geneT, transcrypttype=:transT, symbol=:symbol, description=:des WHERE annotid=:upid;";
	try{
		$stmt=$myPDO->prepare($query);
		$stmt->bindParam(':geneid', $_POST['geneid'], PDO::PARAM_STR);
		$stmt->bindParam(':geneT', $_POST['geneT'], PDO::PARAM_STR);
		$stmt->bindParam(':trans', $_POST['trans'], PDO::PARAM_STR);
		$stmt->bindParam(':transT', $_POST['transT'], PDO::PARAM_STR);
		$stmt->bindParam(':symbol', $_POST['symbol'], PDO::PARAM_STR);
		$stmt->bindParam(':des', $_POST['des'], PDO::PARAM_STR);
		$stmt->bindParam(':upid', $_GET['upid'], PDO::PARAM_STR);
		$stmt->execute();
	}catch(PDOException $e){
		die($e->getMessage());
	}
}
//-------------------------HTML----------------------------------
?>

<!DOCTYPE html>
<html>
<head>
	<title>Home</title>
	
	<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/base/minified/jquery-ui.min.css" type="text/css" /> 
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="http://code.jquery.com/ui/1.10.1/jquery-ui.min.js"></script> 
	
    <link rel="stylesheet" href="assets/bootstrap.css">
      
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>


</head>
<body>
<!-------TOPNAV---------------------------->
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color:dodgerblue">
  <a class="navbar-brand" href="index.php"><h4 style="margin:0px">LOGO</h4></a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <!----------------HOME for admin or users----------------->
        <?php
        if(isAdmin()){
        ?>
        <a class="nav-link" href="admin/home.php" >Home <span class="sr-only">(current)</span></a>
        <?php
        }else{
        ?>
        <a class="nav-link" href="index.php" >Home <span class="sr-only">(current)</span></a>
        <?php
        }
        ?>
        <!--------------------------->
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Menu
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
          <a class="dropdown-item" href="search.php">Search</a>
          <a class="dropdown-item" href="index.php">Home</a>
          <div class="dropdown-divider"></div>
          <!-----DISPLAY name of user and role------------>
          <p class="dropdown-item" style="color:darkcyan"><?php 
          $name=$_SESSION['user']['firstname'];
          if(isAdmin()){
            $name.="(".$_SESSION['user']['usertype'].")";
          }else{
            $name.="(".$_SESSION['user']['userrole'].")";
          }
          echo $name;?></p>

		  <a class="dropdown-item" style="color:red" href=".?logout=1">Logout</a>
		  <?php
          if(isAdmin()){
          ?>
          <div class="dropdown-divider"></div>
		  <a class="dropdown-item" style="color:darkslategrey" href="admin/createUser.php">Create User</a>
		  <a class="dropdown-item" style="color:darkslategrey" href="admin/parser.php">Add file</a>
          <?php
          }?>

        </div>
      </li>
    </ul>
    <div class="p-2">
    <form class="form-inline my-2 my-lg-1" style="line-height:75%" action="results.php" method="get">
       <div class="p-2">
		 <div style="float:inline-start" class="input-group">
		 <select class="btn btn-outline-light btn-mini" name="type">
    <option selected="selected" value="genome">Genome</option>
    <option value="pep">Peptide</option>
  </select>
      <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search" name="search">

    
         </div>
      <br>
      <small><a href="search.php" style="color:white">advanced search</a></small>
	   </div> 
	   <input type="submit" class="btn btn-outline-light" value="Search">
    </form>
    </div>

  </div>
<!--  </div>-->
</nav>


<br>
<!--------------------- Display for Validator------------------->

						<?php if(isValidator()){?>
							
	<div class="row">
		<!----------------------ASSIGING SEQUENCES---------------------------
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
	<!-- Display the non assigned sequences 
	<?php 
	global $myPDO;
	$query="SELECT name, pepid, pep.chromid FROM annot, pep, genome where annotid=pepid and pep.chromid=genome.chromid and isannotated=0 and annotator IS NULL;";
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
-->
<!---------------------IFRAME PART---------------->
<div class="col">
<h4><i class="fa fa-angle-right"></i> Sequences in wait</h4>
<iframe src="./validator/assign.php" height="700" width="900" frameborder="0" marginwidth="10" marginheight="0"></iframe>
</div>
<div class="">
<h4><i class="fa fa-angle-right"></i>Annotations to check</h4>
<iframe src="./validator/review.php" frameborder="0" height="700" width="1000"></iframe>
</div>
<!---------------------------------REVIEW-------------------
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
</div>----------------------------END REVIEW------------------------------------->
</div><!--end div row-->

<?php
//------------------------end validator-----------------------------
}
if(isAnnotator()){//---------Annotator display
?>
<div class="table-responsive col-md-8">
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
			<th>Save</th>
			<th>Validate</th>
		</tr>
		</thead>
		<tbody>
			<?php
			global $myPDO;
			//GET the sequences to annotate
			$query="SELECT DISTINCT annotid, name, pep.id as pid, genome.id as gid, geneid, transcript, genetype, transcrypttype, symbol, description 
			FROM annot, pep, genome, users 
			WHERE annotid=pepid AND pep.chromid=genome.chromid AND annotator=:id AND validated=0;";
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

			<tr>
			
				<td onclick="location.href='view.php?id=<?php echo $row['pid'];?>&type=pep'"><u style=color:dark-blue"><?php echo $row['annotid'];?></u></td>
				<td onclick="location.href='view.php?id=<?php echo $row['gid'];?>&type=genome'"><u style=color:dark-blue"><?php echo $row['name'];?></u></td>
				<form action="<?php echo $_SERVER['PHP_SELF'];?>?upid=<?php echo $row['annotid'];?>" method="post">
				<td><input class="form-control" type="text" name="geneid" value="<?php echo $row['geneid'];?>"></td>
				<td><input class="form-control" type="text" name="geneT" value="<?php echo $row['genetype'];?>"></td>
				<td><input class="form-control" type="text" name="trans" value="<?php echo $row['transcript'];?>"></td>
				<td><input class="form-control" type="text" name="transT" value="<?php echo $row['transcrypttype'];?>"></td>
				<td><input class="form-control" type="text" name="symbol" value="<?php echo $row['symbol'];?>"></td>
				<td><input class="form-control" type="text" name="des" value="<?php echo $row['description'];?>"></td>
				<td><input type="submit" class="btn btn-xs" value="Save" name="save-btn"></td>
				</form>
				<!-- Button to send the annotations -->
				<td>
					<a href="index.php?rid=<?php echo $row['annotid'];?>">
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
}
//else{//-------------------------User type user-------------------
?>
<!--------------------TABLE with the annotation in work -------------------->
<div class="table-responsive col-md-8">
	<table class="table table-striped table-advance table-hover">
	<h4><i class="fa fa-angle-right"></i>Current Annotations</h4>
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
			<th>Annotator</th>
		</tr>
		</thead>
		<tbody>
			<?php
			global $myPDO;
			//GET the sequences to annotate
			$query="SELECT annotid, name, geneid, transcript, genetype, transcrypttype, symbol, description, email 
			FROM annot, pep, genome, users 
			WHERE annotid=pepid AND pep.chromid=genome.chromid AND validated=0 AND isAnnotated=0 AND users.id=annotator;";
			try{
				$stmt=$myPDO->prepare($query);
				$stmt->execute();
				$stmt;
			}catch(PDOException $e){
				die($e->getMessage());
			}
			
			while($row=$stmt->fetch()){//get the annotation values not yet validated.
			?>

			<tr>
			
				<td onclick="location.href='view.php?id=<?php echo $row['pid'];?>&type=pep'"><u style=color:dark-blue"><?php echo $row['annotid'];?></u></td>
				<td onclick="location.href='view.php?id=<?php echo $row['gid'];?>&type=genome'"><u style=color:dark-blue"><?php echo $row['name'];?></u></td>
				<form action="<?php echo $_SERVER['PHP_SELF'];?>?upid=<?php echo $row['annotid'];?>" method="post">
				<td><?php echo $row['geneid'];?></td>
				<td><?php echo $row['genetype'];?></td>
				<td><?php echo $row['transcript'];?></td>
				<td><?php echo $row['transcrypttype'];?></td>
				<td><?php echo $row['symbol'];?></td>
				<td><?php echo $row['description'];?></td>
				<td><?php echo $row['email'];?></td>

			</tr>
			<?php	
			}//end while
			?>
		</tbody>
	</table>
</div>			

<?php
//}//end if user
?>

			
</div>
</body>
</html>