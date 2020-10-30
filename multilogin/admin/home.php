<?php 
include('../functions.php');

if (!isAdmin()) {
	$_SESSION['msg'] = "You must log in first";
	header('location: ../login.php');
}

if (isset($_GET['logout'])) {
	session_destroy();
	unset($_SESSION['user']);
	header("location: ../login.php");
}
//check if id is set, if yes delete the user with that id
if(isset($_GET['id'])){
	global $myPDO;
	$id=$_GET['id'];
	$query="DELETE FROM users WHERE id=:id;";
	try{
		$stmt=$myPDO->prepare($query);
		test();

		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		
		$stmt->execute();
		
		if($stmt){
			echo "<script>alert('Data deleted');</script>";
	}
	}catch(Exception $e){
		die($e->getMessage());
	}
}
//check if uid set, if yes validate the account
if(isset($_GET['uid'])){
	global $myPDO;
	$id=$_GET['uid'];
	$que="UPDATE users SET isapproved=1 WHERE id=:id;";
	try{
		$stmt=$myPDO->prepare($que);
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		$stmt->execute();
		if($stmt){
			echo "<script>alert('Account validated');</script>";
		}
	}catch(Exception $e){
		die($e->getMessage());
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Home</title>	
	<link rel="stylesheet" href="../assets/bootstrap.css"> 
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

	<style>
	.header {
		background: #003366;
	}
	button[name=register_btn] {
		background: #003366;
	}
	</style>
</head>
<body>
<!-------TOPNAV---------------------------->
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color:black">
  <a class="navbar-brand" href="../index.php"><h4 style="margin:0px">LOGO</h4></a>
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
        <a class="nav-link" href="home.php" >Home <span class="sr-only">(current)</span></a>
        <?php
        }else{
        ?>
        <a class="nav-link" href="../index.php" >Home <span class="sr-only">(current)</span></a>
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
          <a class="dropdown-item" href="../search.php">Search</a>
          <a class="dropdown-item" href="../index.php">Home</a>
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

		  <a class="dropdown-item" style="color:red" href="?logout=1">Logout</a>
		  <div class="dropdown-divider"></div>
		  <a class="dropdown-item" style="color:darkslategrey" href="createUser.php">Create User</a>
		  <a class="dropdown-item" style="color:darkslategrey" href="parser.php">Add file</a>

        </div>
      </li>
    </ul>
    <div class="p-2">
    <form class="form-inline my-2 my-lg-1" style="line-height:75%" action="../results.php" method="get">
       <div class="p-2">
		 <div style="float:inline-start" class="input-group">
		 <select class="btn btn-outline-light btn-mini" name="type">
    <option selected="selected" value="genome">Genome</option>
    <option value="pep">Peptide</option>
  </select>
      <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search" name="search">

    
         </div>
      <br>
      <small><a href="../search.php" style="color:white">advanced search</a></small>
	   </div> 
	   <input type="submit" class="btn btn-outline-light" value="Search">
    </form>
    </div>

  </div>
</nav>
<br>

	<!-- table with the users -->
	<table class="table table-striped table-advance table-hover">
	<h4><i class="fa fa-angle-right"></i>User Details </h4>
		<hr>
		<thead>
		<tr>
			<th>ID</th>
			<th class="hidden-phone">First Name</th>
			<th> Last Name</th>
			<th> Email Id</th>
			<th>Role</th>
			<th>Last Login</th>
			<th>Valid</th>
		</tr>
		</thead>
	<tbody>
	<!-- Select the users and display the info in a table row-->
	<?php 
	global $myPDO;
	$query="SELECT * FROM users;";
	$stmt=$myPDO->prepare($query);
	$stmt->execute();
	while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
	?>
	<tr>
			<td><?php echo $row['id'];?></td> 
			<td><?php echo $row['firstname'];?></td>
			<td><?php echo $row['lastname'];?></td>
			<td><?php echo $row['email'];?></td>
			<td><?php echo $row['userrole'];?></td>
			<td><?php echo $row['lastlogin'];?></td>
			<td><?php 
			if($row['isapproved']==0){
				?>
				<a href="home.php?uid=<?php echo $row['id'];?>"> 
				<button class="btn btn-danger btn-xs" onClick="return confirm('Do you really want to validate the account?');"><i class="fa fa-trash-o "></i>Validate</button></a>
			<?php
			}else{
				echo "Valid";
			}
			?>
			</td>
			<td>    
			    <!--              
				<a href="updateProfile.php?uid=<?php echo $row['id'];?>"> 
				<button class="btn btn-primary btn-xs"><i class="fa fa-pencil"></i></button></a>
				-->
				<a href="home.php?id=<?php echo $row['id'];?>"> 
				<button class="btn btn-danger btn-xs" onClick="return confirm('Do you really want to delete');"><i class="fa fa-trash-o " ></i>
				<img src="../assets/bin.jpg" >
				</button></a>
			</td>
	</tr>
	<?php 
	}
	?>
	</tbody>
</table>
</body>
</html>