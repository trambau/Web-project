<?php

//template
session_start();
include('functions.php');
if (!isLoggedIn()) {
    $_SESSION['msg'] = "You must log in first";
    header('location: login.php');
}

$seq_er='';

$type=$_POST['type'];
$_SESSION['type']=$type;
global $seq_er, $name, $genomeid, $loc, $seq, $geneid, $id, $trans, $des, $geneB, $transB, $def, $symbole;
$name=trim($_POST['name']);
$_SESSION['name']=$name;
$genomeid=trim($_POST['genomeID']);
$_SESSION['genomeid']=$genomeid;
$loc=trim($_POST['location']);
$_SESSION['loc']=$loc;
$seq=trim($_POST['sequence']);
$_SESSION['seq']=$seq;
$geneid=trim($_POST['geneID']);
$_SESSION['geneid']=$geneid;
$id=trim($_POST['id']);
$_SESSION['id']=$id;
$trans=trim($_POST['trans']);
$_SESSION['trans']=$trans;
$des=trim($_POST['description']);
$_SESSION['des']=$des;
$geneB=trim($_POST['geneBiotype']);
$_SESSION['geneB']=$geneB;
$transB=trim($_POST['transBiotype']);
$_SESSION['transB']=$transB;
$symbole=trim($_POST['symbole']);
$_SESSION['symbole']=$symbole;
if(strlen($seq) < 3  && !(empty($seq))){
    $seq_er = "The seq must have at least 3 characters.";
}
if(empty($seq_er)){
    if(isset($_POST['search_btn'])){
        header('location: results.php');
    }
}
//template end
?>



<!DOCTYPE html>
<html>
	
//page style
<header>
    <style>
    .wrapper{ width: 350px; padding: 20px; }
    </style>
    <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/base/minified/jquery-ui.min.css" type="text/css" /> 
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
    <script type="text/javascript" src="http://code.jquery.com/ui/1.10.1/jquery-ui.min.js"></script>  
   
    <link rel="stylesheet" href="assets/bootstrap.css"> 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

</header>
//page style end	
	

	
//Body of Search.php	
<title>Search</title>
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
          $nam=$_SESSION['user']['firstname'];
          $nam.="(".$_SESSION['user']['userrole'].")";
          echo $nam;?></p>

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
</nav>
	
	
<script>
function yesnoCheck(that) {
    if (that.value == "other") {
  //alert("check");
        document.getElementById("ifYes").style.display = "block";
    } else {
        document.getElementById("ifYes").style.display = "none";
    }
}
</script>
	
	
<div class="wrapper">
<h2>Search complete  genome or peptides/genes</h2>
<form action="<?php $_SERVER['PHP_SELF'];?>" method="post">
    <div class="form-group">
        <label>Name:</label> 
        <input type="text" value="<?php echo $name;?>" class="form-control" name="name" id="name" placeholder="Esche...">
    </div>
    <script type="text/javascript">
$(function() {
    //autocomplete
    $("#name").autocomplete({
        source: "autocomplete.php",
        minLength: 1
    });                

});
</script>

    <div class="form-group">
        <label>ID genome:</label> 
        <input type="text" value="<?php echo $genomeid;?>" class="form-control" name="genomeID" placeholder="ASM...">
    </div>
    <div class="form-group">
        <label>Location:</label> 
        <input type="text" value="<?php echo $loc;?>" name="location" placeholder="1:546..." class="form-control">
    </div>
    <div class="form-group <?php echo (!empty($seq_er)) ? 'has-error' : ''; ?>">
        <label>Sequence:</label>
        <textarea name="sequence" class="form-control" placeholder="AGCTTTT..."><?php echo $seq;?></textarea>
        <span class="help-block"><?php echo $seq_er; ?></span>
    </div>
    <label>Output type:</label>
    <select name="type" class="form-control " onchange="yesnoCheck(this);">
        <option selected="selected" value="genome" name="genome">Genome</option>
        <option value="other" name="other">Gene/peptide</option>
    
    </select>
    <br>

    <div id="ifYes" style="display: none;">
        <div class="form-group">
            <label>Gene Id</label>
            <input type="text" name="geneID" value="<?php echo $gene;?>" class="form-control" placeholder="ex:c5491">
        </div>
        <div class="form-group">
            <label>ID</label> 
            <input type="text" name="id" value="<?php echo $id;?>" class="form-control" placeholder="ex:AAN78501">
        </div>
        <div class="form-group">
            <label>Gene biotype</label> 
            <input type="text" name="geneBiotype" value="<?php echo $geneB;?>" class="form-control" placeholder="ex:protein_coding">
        </div>
        <div class="form-group">
            <label>Transcript</label> 
            <input type="text" name="trans" value="<?php echo $trans;?>" class="form-control" placeholder="ex:AAN78501">
        </div>
        <div class="form-group">
            <label>Transcript biotype</label> 
            <input type="text" name="transBioType" value="<?php echo $transB;?>" class="form-control" placeholder="ex:protein_coding">
        </div>
        <div class="form-group">
            <label>Symbole</label> 
            <input type="text" name="symbole" value="<?php echo $symbole;?>" class="form-control" placeholder="ex:THR...">
        </div>
        <div class="form-group">
            <label>Description</label> 
            <input type="text" name="description" value="<?php echo $des;?>" class="form-control" placeholder="ex:Hypothetical protein">
        </div>
    </div>
    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Search" name="search_btn">
        <input type="reset" class="btn btn-secondary" value="Reset">
    </div>

</form>
</div>

</body>
</html>
