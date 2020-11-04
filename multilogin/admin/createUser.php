<?php include('../functions.php');
if (!isAdmin()) {
	$_SESSION['msg'] = "You must log in first";
	header('location: ../login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="../assets/bootstrap.css"> 
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <style type="text/css">
		.wrapper{ width: 350px; padding: 20px;}
		
    </style>
</head>
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
          $name.="(".$_SESSION['user']['userrole'].")";
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
<body style="background-color:#f2f2f2">
    <div class="wrapper">
        <h2>Create new user</h2>
        <p>Please fill this form to create an account.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
			<div class="form-group <?php echo (!empty($fName_er)) ? 'has-error' : ''; ?>">
                <label>First Name</label>
                <input type="text" name="firstName" class="form-control" value="<?php echo $fName; ?>" placeholder="First Name">
                <span class="help-block"><?php echo $fName_er; ?></span>
            </div> 
			<div class="form-group <?php echo (!empty($lName_er)) ? 'has-error' : ''; ?>">
                <label>Last Name</label>
                <input type="text" name="lastName" class="form-control" value="<?php echo $lName; ?>" placeholder="Last Name">
                <span class="help-block"><?php echo $lName_er; ?></span>
            </div> 
			<div class="form-group <?php echo (!empty($phone_er)) ? 'has-error' : ''; ?>">
                <label>Phone number</label>
                <input type="text" name="phone" class="form-control" value="<?php echo $phone; ?>" placeholder="0123...">
                <span class="help-block"><?php echo $phone_er; ?></span>
            </div> 
			<div class="form-group <?php echo (!empty($role_er)) ? 'has-error' : ''; ?>">
				<label>User role</label>
				<select name="userRole" id="userRole" class="btn btn-min btn-outline-dark">
					<option selected="selected" value="utilisateur" <?php if($role=="utilisateur"){echo "selected";}?>>Utilisateur</option>
					<option value="annotator" <?php if($role=="annotator"){echo "selected";}?>>Annotateur</option>
					<option value="validator" <?php if($role=="validator"){echo "selected";}?>>Validateur</option>
          <option value="admin" <?php if($role=="validator"){echo "selected";}?>>Admin</option>
				</select>
				<span class="help-block"><?php echo $role_er; ?></span>
			</div>
            <div class="form-group <?php echo (!empty($email_er)) ? 'has-error' : ''; ?>">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo $email; ?>" placeholder="example@email.com">
                <span class="help-block"><?php echo $email_er; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_er)) ? 'has-error' : ''; ?>">
                <label>Password</label>
                <input type="password" name="password_1" class="form-control" value="<?php echo $password_1; ?>">
                <span class="help-block"><?php echo $password_er; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($password2_er)) ? 'has-error' : ''; ?>">
                <label>Confirm Password</label>
                <input type="password" name="password_2" class="form-control" value="<?php echo $password_2; ?>">
                <span class="help-block"><?php echo $password2_er; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Create User" name="createUser_btn">
                <input type="reset" class="btn btn-outline-dark" value="Reset">
            </div>
        </form>
    </div>    
</body>
</html>