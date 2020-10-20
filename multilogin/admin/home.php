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

		$stmt->bindParam(":id", $id, PDO::PARAM_STR);
		
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
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
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
	<div class="header">
		<h2 style="color:#eaeaea">Admin - Home Page</h2>
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
			<img src="../images/admin_profile.png"  >

			<div>
				<?php  if (isset($_SESSION['user'])) : ?>
					<strong><?php echo $_SESSION['user']["firstname"]; ?></strong>

					<small>
						<i  style="color: #888;">(<?php echo ucfirst($_SESSION['user']['usertype']); ?>)</i> 
						<br>
						<a href="home.php?logout='1'" style="color: red;">logout</a>
                       &nbsp; <a href="createUser.php"> + add user</a>
					   &nbsp; <a href="parser.php"> add file</a>

					</small>

				<?php endif ?>
			</div>
		</div>
	</div>
	<!-- table with the users -->
	<table class="table table-striped table-advance table-hover">
	<h4><i class="fa fa-angle-right"></i> All User Details </h4>
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
				<a href="updateProfile.php?uid=<?php echo $row['id'];?>"> 
				<button class="btn btn-primary btn-xs"><i class="fa fa-pencil"></i></button></a>
				<a href="home.php?id=<?php echo $row['id'];?>"> 
				<button class="btn btn-danger btn-xs" onClick="return confirm('Do you really want to delete');"><i class="fa fa-trash-o " ></i>
				<img src="../../bin.jpg" >
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