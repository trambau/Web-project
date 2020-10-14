<?php 
session_start();


// connect to database
try{
    $datab="db";
    $user="postgres";
    $dbpswd="postgres";
    $myPDO=new PDO("pgsql:host=localhost;dbname=$datab", $user, $dbpswd);
    $myPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $myPDO->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
}
catch(PDOException $e){     
    die("DB ERROR: " . $e->getMessage());
}
// variable declaration
$fName = "";
$lName = "";
$phone = "";
$email    = "";
$errors   = array(); 

// call the register() function if register_btn is clicked
if (isset($_POST['register_btn'])) {
	register();
}

// REGISTER USER
function register(){
	// call these variables with the global keyword to make them available in function
	global $myPDO, $errors, $fName, $email, $lName, $phone, $fName_er, $lName_er, $phone_er, $email_er, $password2_er, $password_er, $role, $role_er;

    // defined below to escape form values
    $fName       =  trim($_POST['firstName']);
    $fName_er="";
    $lName       =  trim($_POST['lastName']);
    $lName_er="";
    $phone       =  trim($_POST['phone']);
    $phone_er="";
    $email       =  trim($_POST['email']);
    $email_er="";
    $password_1  =  trim($_POST['password_1']);
    $password_er=$password2_er=$role_er="";
	$password_2  =  trim($_POST['password_2']);
    $role        =  trim($_POST['userRole']);

    //validate password
    if(empty($password_1)){
        $password_er = "Please enter a password.";     
    } elseif(strlen($password_1) < 6){
        $password_er = "Password must have atleast 6 characters.";
    }
    
      // Validate confirm password
      if(empty($password_2)){
        $password2_er = "Please confirm password.";     
    } else{
        if(empty($password_er) && ($password_1 != $password_2)){
            $password2_er = "Password did not match.";
        }
    }
    //Validate email
    if(empty($email)){
        $email_er = "Please enter an email.";
    } else{
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE email = :email";
        try{
            if($stmt = $myPDO->prepare($sql)){
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                // Attempt to execute the prepared statement
                if($stmt->execute()){
                    if($stmt->rowCount() == 1){
                        $email_er = "This email is already taken.";
                    } 
                } else{
                    echo "Oops! Something went wrong. Please try again later.";
                }
                // Close statement
                unset($stmt);
            }
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

	// form validation: ensure that the form is correctly filled
	if (empty($fName)) { 
        $fName_er="Please enter a first name.";
    }
    if (empty($lName)) { 
        $lName_er="Please enter a last name.";
    }
    if (empty($phone)) { 
        $phone_er="Please enter a phone number.";
    }
    if (empty($role)){
        $role_er="Please choose a role.";
    }

	// register user if there are no errors in the form       
    if(empty($fName_er) && empty($lName_er) && empty($phone_er) && empty($email_er) && empty($password_er) && empty($password2_er) && empty($role_er)){
		$password = password_hash($password_1, PASSWORD_DEFAULT);//encrypt the password before saving in the database

		if (isset($_POST['usertype'])) {
			$user_type = trim($_POST['usertype']);
			$query = "INSERT INTO users (firstname, lastname,phone,  email, usertype, pswd, isapproved, userrole) 
                      VALUES( :fName, :lName, :phone, :email, :user_type, :password, 1, :role)";
            try{
                $stmt = $myPDO->prepare($query);
                $stmt->bindParam(":fName", $fName, PDO::PARAM_STR);
                $stmt->bindParam(":lName", $lName, PDO::PARAM_STR);
                $stmt->bindParam(":phone", $phone, PDO::PARAM_STR);
                $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                $stmt->bindParam(":user_type", $user_type, PDO::PARAM_STR);
                $stmt->bindParam(":password", $password, PDO::PARAM_STR);
                $stmt->bindParam(":role", $role, PDO::PARAM_STR);
                $stmt->execute();
            }catch(Exception $e){
                die($e->getMessage());
            }
			$_SESSION['success']  = "New user successfully created!!";
			header('location: home.php');
		}else{
            
			$query = "INSERT INTO users (firstname, lastname, phone, email, usertype, pswd, userrole)
                      VALUES(:fName, :lName, :phone, :email, 'user', :password, :role)";
            try{
                $stmt = $myPDO->prepare($query);
                
                $stmt->bindParam(":fName", $fName, PDO::PARAM_STR);
                $stmt->bindParam(":lName", $lName, PDO::PARAM_STR);
                $stmt->bindParam(":phone", $phone, PDO::PARAM_STR);
                $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                $stmt->bindParam(":password", $password, PDO::PARAM_STR);
                $stmt->bindParam(":role", $role, PDO::PARAM_STR);
                $stmt->execute();
                $logged_in_user_id = $myPDO->lastInsertId();
                
            }catch(Exception $e){
                die($e->getMessage());
            }
            $_SESSION['user'] = getUserById($logged_in_user_id); // put logged in user in session
            $_SESSION['success']  = "You are now logged in";
            if($_SESSION['user']["isapproved"]==1){
                header('location: index.php');
            }else{
                header('location: login.php');
            }		
		}
    }
    unset($stmt);
    
}

// return user array from their id
function getUserById($id){
   
	global $myPDO;
    $query = "SELECT * FROM users WHERE id= :id";
    try{
        
        $stmt=$myPDO->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $user=$stmt->fetch(); 
       
    }catch(Exception $e){
        die($e->getMessage);
    }  
	return $user;
}

function display_error() {
	global $errors;

	if (count($errors) > 0){
		echo '<div class="error">';
			foreach ($errors as $error){
				echo $error .'<br>';
			}
		echo '</div>';
	}
}	
function isLoggedIn()
{
	if (isset($_SESSION['user'])) {
		return true;
	}else{
		return false;
	}
}
// log user out if logout button clicked
if (isset($_GET['logout'])) {
	session_destroy();
	unset($_SESSION['user']);
	header("location: login.php");
}
// call the login() function if register_btn is clicked
if (isset($_POST['login_btn'])) {
	login();
}

// LOGIN USER
function login(){
	global $myPDO, $email, $errors, $email_er, $password_er, $account_er;

	// grap form values
	$email = trim($_POST['email']);
	$password = trim($_POST['password']);
    $email_er=$password_er=$account_er="";
    
	// make sure form is filled properly
	if (empty($email)) {
        $email_er="Please enter an email address.";
	}
	if (empty($password)) {
		$password_er="Please enter a password.";
	}
    
	// attempt login if no errors on form
    if(empty($password_er) && empty($email_er)){
        $query = "SELECT * FROM users WHERE email=:email";
        try{
            $stmt=$myPDO->prepare($query);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->execute();
        }catch(Exception $e){
            die($e->getMessage());
        }
        //check if email exist
        if($stmt->rowCount()==1){
            $logged_in_user =$stmt->fetch();
            $hash_pass=$logged_in_user["pswd"];
            //check password
            if(password_verify($password, $hash_pass)){
                $valid=$logged_in_user["isapproved"];
                //check is the account is valid
                if($valid==1){
                    $currentdate=date('Y-m-d h:i:s');

                    $query="UPDATE users SET lastlogin=:logintime WHERE email=:email;";
                    try{
                        $stt=$myPDO->prepare($query);
                        $stt->bindParam(":logintime", $currentdate, PDO::PARAM_STR);
                        $stt->bindParam(":email", $email, PDO::PARAM_STR);
                        $stt->execute();
                    }catch(Exception $e){
                        die($e->getMessage());
                    }
                    // check if user is admin or user
                    if ($logged_in_user['usertype'] == 'admin') {

                        $_SESSION['user'] = $logged_in_user;
                        $_SESSION['success']  = "You are now logged in";
                        header('location: admin/home.php');		  
                    }else{
                        $_SESSION['user'] = $logged_in_user;
                        $_SESSION['success']  = "You are now logged in";

                        header('location: index.php');
                    }
                }else{
                    $account_er="Non validated account.";
                }
            }else{
                $password_er="Wrong password.";
            }
           
		}else {
            $email_er="No account with this email.";
		}
	}
}
function isAdmin()
{
	if (isset($_SESSION['user']) && $_SESSION['user']['usertype'] == 'admin' ) {
		return true;
	}else{
		return false;
	}
}

function totalUsers () {
    global $myPDO;
    try{
    $sql = "SELECT * FROM users;";
    $stmt=$myPDO->prepare($sql);
    $stmt->execute();
    $numRows=$stmt->rowCount();
    }catch(Exception $e){
        die($e->getMessage());
    }
    return $numRows;
}
function test (){
    echo "test function";
}

?>