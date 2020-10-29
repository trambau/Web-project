
<?php include('../functions.php');
if (!isAdmin()) {
	$_SESSION['msg'] = "You must log in first";
	header('location: ../login.php');
}
if (isset($_GET['logout'])) {
	session_destroy();
	unset($_SESSION['user']);
	header("location: ../login.php");
}
include('../model.html');
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
<title>Add data</title>
</head>
<body>
    <div class="header" style="background-color:dodgerblue">
        <br>
        <a style="float:right;color:red" href="home.php?logout='1'">logout</a>
        <h2 style="color:azure">TEST</h2>
    </div>
    <h4> Enter the absolute path of the directory with the fasta files.</h4>
    <form action="<?php $_SERVER['PHP_SELF'];?>" method="post">
   
    <input type="text" value="<?php echo $fileDir;?>" placeholder="file location" value="<?php echo $loc?>" name="file">
    <br></br>
    <input type="submit" value="Submit" class="btn btn-primary" name="addFile_btn">
    <input type="reset" value="Reset" class="btn btn-default">
	<input type="button" onclick="location.href='./home.php';" value="Back" class="btn btn-default"/>
    </form>

    <?php if (isset($_SESSION['addSuccess'])) : ?>
			<div class="error success" >
				<h3>
					<?php 
						echo $_SESSION['addSuccess']; 
						unset($_SESSION['addSuccess']);
					?>
				</h3>
			</div>
		<?php endif ?>
</body>
</html>
