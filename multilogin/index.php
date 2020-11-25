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


//------------PAGE--------------
$nbres=8;
$totpage;
if(isset($_GET['page'])){
    $page=$_GET['page'];
}else{
    $page=1;
}
$startat=($page-1)*$nbres;
$path="index.php";
//---------------------------------
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
          $name.="(".$_SESSION['user']['userrole'].")";
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
     <div style="padding-bottom:14px">
	   <input type="submit" class="btn btn-outline-light" value="Search">
     </div>
    </form>
    </div>

  </div>
<!--  </div>-->
</nav>


<br>
<!--------------------- Display for Validator------------------->

						<?php if(isValidator()){?>
							
	
<!---------------------IFRAME PART---------------->
<div class="row">
<div class="col">
<h4><i class="fa fa-angle-right"></i> Sequences in wait</h4>
<iframe src="./validator/assign.php" height="700" width="900" frameborder="0" marginwidth="10" marginheight="0"></iframe>
</div>
<div class="">
<h4><i class="fa fa-angle-right"></i>Annotations to check</h4>
<iframe src="./validator/review.php" frameborder="0" height="700" width="1000"></iframe>
</div>
</div>
<!---------------------------------REVIEW------------------->

<?php
//------------------------end validator-----------------------------
}
if(isAnnotator()){//---------Annotator display
?>
<div class="row" style="padding:15px; overflow:hidden" >
<h4><i class="fa fa-angle-right"></i> Sequences To Annotate</h4>
<iframe src="./annotator/annotation.php" height="700" width="1800" frameborder="0" marginwidth="10" marginheight="0"></iframe>
</div>
<?php //end if annotator
}
//else{//-------------------------User role user-------------------
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
			$query="SELECT annotid, name, geneid, transcript, genetype, transcrypttype, symbol, description, email, genome.id as gid, pep.id as pid 
			FROM annot, pep, genome, users 
			WHERE annotid=pepid AND pep.chromid=genome.chromid AND validated=0 AND upreview=0 AND users.id=annotator;";
			$q2="SELECT annotid
			FROM annot, users 
			WHERE validated=0 AND upreview=0 AND users.id=annotator;";
			try{
				$stmt=$myPDO->prepare($query);
				$stmt->execute();
				$s2=$myPDO->prepare($q2);
				$s2->execute();
				$nbrow=$s2->rowCount();
                $totpage=ceil($nbrow/$nbres);
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
 <!--------------------------PAGINATION------------------------>
 <?php
  if($totpage>1){//check if there are more than one page
  ?>
  <nav aria-label="Page navigation">
<ul class="pagination" max-size='10'>

<!--Get previous page-->
<li class="page-item"><a class="page-link" href="<?php echo $path."?page=";?>1">First</a></li>
<li class="page-item"><a class="page-link" href="<?php echo $path."?page=";?><?php 
if($page>1){
    echo $page-1;
}else{
    echo $page;
}?>">Previous</a></li>

<?php

$i=$page;
$pagesDisplayed=5;
//check if there are more than one page to display
if($totpage>$pagesDisplayed){
$j=$totpage-$pagesDisplayed-1;
}else{
    $j=1;
}
//Check if the current page is in the last ten pages
if($page>$j){
    //add index between the current page and 10 pages before the last page
    for($k=$j; $k<$page; $k++){
        ?>
        <li class="page-item"><a class="page-link" href="<?php echo $path."?page=".$k;?>"><?php echo $k;?></a></li>
        <?php   
    }
}
    while($i<=$totpage && $i<$page+$pagesDisplayed){
?>
    <li class="page-item"><a class="page-link" href="<?php  echo $path."?page=".$i;?>"><?php echo $i;?></a></li>
    
<?php
    $i++;
    }//end while   
?>
<!-- Get next page-->
<li class="page-item"><a class="page-link" href="<?php echo $path."?page=";?><?php
if($page<$totpage){
    echo $page+1;
}else{
    echo $totpage;
}
?>">Next</a></li>
<li class="page-item"><a class="page-link" href="<?php echo $path."?page=".$totpage;?>">Last</a></li>

</ul>
</nav>
<?php
} //end if
?>
<?php
//}//end if user
?>

			
</div>
</body>
</html>