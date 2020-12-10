<?php include('functions.php') ;
 ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="assets/bootstrap.css">  
    <style type="text/css">
        body{ font: 14px sans-serif; }
        .wrapper{ width: 350px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Sign Up</h2>
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
				<select name="userRole" id="userRole" class="btn btn-mini btn-outline-dark">
					<option value="utilisateur" <?php if($role=="utilisateur"){echo "selected";}?>>Utilisateur</option>
					<option value="annotator" <?php if($role=="annotator"){echo "selected";}?>>Annotateur</option>
					<option value="validator" <?php if($role=="validator"){echo "selected";}?>>Validateur</option>
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
                <input type="submit" class="btn btn-primary" value="Submit" name="register_btn">
                <input type="reset" class="btn btn-outline-dark" value="Reset">
            </div>
            <p>Already have an account? <a href="login.php">Login here</a>.</p>
        </form>
    </div>    
</body>
</html>