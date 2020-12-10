<?php
include('functions.php');
//check connected user.
if (!isLoggedIn()) {
	$_SESSION['msg'] = "You must log in first";
	header('location: login.php');
}

if(isset($_GET['NB']) && $_GET['NB']>1){
    $flag2=1;
}else{
    $flag2=0;
}
if(empty($_GET['RID'])&& isset($_GET['ID'])){
    $id=$_GET['ID'];
    $cmd="curl -sL 'https://blast.ncbi.nlm.nih.gov/Blast.cgi?CMD=Put&QUERY=$id&PROGRAM=blastp&FILTER=L&DATABASE=nr&FORMAT_TYPE=XML' | grep 'RID = '";
    set_time_limit(0);
    $res=shell_exec($cmd);
    $rid=explode(' ', $res);
    $rid=trim($rid[count($rid)-1]);
    $refresh="blast.php?RID=$rid&NB=1";
    header('Location: '.$refresh);
}
if($flag2==1){
    $rid=$_GET['RID'];
    $cmdres="curl -sL 'https://blast.ncbi.nlm.nih.gov/Blast.cgi?CMD=Get&RID=$rid' | grep 'READY'";
    $flag=shell_exec($cmdres);
    if(!empty($flag)){
        $url="https://blast.ncbi.nlm.nih.gov/Blast.cgi?CMD=Get&RID=$rid&VIEW_RESULTS=FromRes&FORMAT_TYPE=HTML";
        header('Location: '.$url);
    }    
}
?>
<!DOCTYPE html>
<html>
    <header>
        <title>
            Blast
        </title>
    <link rel="stylesheet" href="./assets/bootstrap.css"> 
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  <script type="text/javascript" src="assets/bootstrap.min.js"></script>
    </header>
   <body>
   <!-----------------TOPNAV------------------------------>
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


   <!------------------END TOPNAV----------------------------->
       <p style="padding:50px">Please wait, you will be redirected to the results when they are ready.</p>
      <script>
        
            var rid=window.location.href.split("=");
            rid=rid[1].split("&")[0];
            var count=window.location.href.split("=")[2];
            if(count==1){
                 //wait 60 sec the first time before checking the resulst as per the ncbi guidelines.
                count++;
                setTimeout(function(){
                    window.location.href ='blast.php?RID='+rid+'&NB='+count;
                }, 60000);
            }else{
                count++;
                setTimeout(function(){
                    window.location.href ='blast.php?RID='+rid+'&NB='+count;
                }, 30000);
            }
      </script>
   </body>
</html>