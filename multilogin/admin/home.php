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
					</small>

				<?php endif ?>
			</div>
		</div>
	</div>
	<table>
	<tbody>
	<?php 
	global $myPDO;
	$query="SELECT * FROM users;";
	$stmt=$myPDO->prepare($query);
	$stmt->execute();
	while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
		//echo $row['id'];
	?>
	<tr>
			<td><?php echo $row['firstname'];?></td>
			<td><?php echo $row['lastname'];?></td>
			<td><?php echo $row['email'];?></td>
			<td><?php echo $row['id'];?></td> 
			<td><?php echo $row['userrole'];?></td>
			<td><?php echo $row['lastlogin'];?></td>
	</tr>
	<?php 
	}
	?>
	</tbody>
</table>
</body>
</html>