<?php 
session_start();


// connect to database
try{
    /*
    $datab="db";
    $user="postgres";
    $dbpswd="postgres";
    */
    
    $datab="sample";
    $user="trambaud";
    $dbpswd="trambaud";
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

    //get the values from the form
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
        //Check if usertype is set, if yes validate the account
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
            //create an account not yet validated
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
//check if looged in
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
//check if the current user is admin
function isAdmin()
{
	if (isset($_SESSION['user']) && $_SESSION['user']['usertype'] == 'admin' ) {
		return true;
	}else{
		return false;
	}
}
//check if the current user is a validator
function isValidator(){
    if (isset($_SESSION['user']) && $_SESSION['user']['userrole'] == 'validator' ) {
		return true;
	}else{
		return false;
	}
}
//check if the current user is an annotator
function isAnnotator(){
    if (isset($_SESSION['user']) && $_SESSION['user']['userrole'] == 'annotator' ) {
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
//browse every files in a directory
function parseDir ($dir){
    $files=glob($dir."*.fa");
    foreach ($files as $file) {
        parseFile($file);
    }
}
//check if file or directory and add it to the DB
function addFiles(){
    //make sure the script is fully executed
    set_time_limit(0);
    $dir=$_POST['file'];
    if(is_dir($dir)){
        parseDir($dir);
        validGenome();
    }elseif(is_file($dir)){
        parseFile($dir);
        validGenome();
    }else{
        die("Not a file or directory.");
    }
}
//check if the button was pressed
if (isset($_POST['addFile_btn'])) {
    addFiles();
    //success message
    $_SESSION['addSuccess']="Files added.";
}
//parse the file and insert them into the db
function parseFile ($file){
    global $myPDO;
    $lines=file($file) or die("unable to open it");
    foreach ($lines as $line) {
        if(substr($line, 0,1)==">"){

            $array=preg_split('/[\s:]+/',$line);
            $array[0]=substr($array[0], 1);
            //value creation for genome table
            if ($array[1]=="dna") {
                //add the full sequence to the last row
                if(!empty($seq)){
                    $query="UPDATE genome SET sequence=:seq WHERE id=:id;";
                    try{
                        $stmt=$myPDO->prepare($query);
                        $stmt->bindParam(":seq", $seq, PDO::PARAM_STR);
                        $stmt->bindParam(":id", $dbID, PDO::PARAM_INT);
                        $stmt->execute();
                    }catch(Exception $e){
                        die($e->getMessage());
                    }
                }

                $flag=0;
                $id=$location="";
                $id=$array[4];
                $location=$array[6]. " " .$array[7]." ".$array[8];
                //get the strain name
                $tmp=explode('/',$file);
                $tmp=array_pop($tmp);
                $name=explode('.', $tmp);
                $name=$name[0];
                $name=str_replace("_", " ", $name);
                //value insertion
                $query="INSERT INTO genome VALUES (DEFAULT, :id, :loc, :seq, DEFAULT, :name);";
                try{
                    $stmt=$myPDO->prepare($query);
                    $stmt->bindParam(":id", $id, PDO::PARAM_STR);
                    $stmt->bindParam(":loc", $location, PDO::PARAM_STR);
                    $stmt->bindParam(":seq", $seq, PDO::PARAM_STR);
                    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
                    $stmt->execute();
                    $dbID=$myPDO->lastInsertId();
                }catch(Exception $e){
                    die("error insert genome".$e->getMessage());
                }



            //value for pep table
            }elseif ($array[1]=="pep") {
                //add the full sequence to the last row
                if(!empty($seq)){
                    $query="UPDATE pep SET sequence=:seq WHERE id=:id;";
                    try{
                        $stmt=$myPDO->prepare($query);
                        $stmt->bindParam(":seq", $seq, PDO::PARAM_STR);
                        $stmt->bindParam(":id", $dbID, PDO::PARAM_INT);
                        $stmt->execute();
                    }catch(Exception $e){
                        die("error update seq in pep".$e->getMessage());
                    }
                }
                $pepId=$array[0];
                $flag=1;
                $id=$location=$geneId=$transcript=$geneType=$transType=$sym=$description=$seq="";
                //$id=$array[3];

                for($i=0; $i<count($array); $i++) {
                    if($array[$i]=="chromosome"){
                        $id=$array[$i+1];
                        $i++;
                    }elseif($array[$i]=="plasmid"){
                        $id=$array[$i+1];
                        $location=$array[$i+2]." ".$array[$i+3]." ".$array[$i+4];
                        $i+=4;
                    }elseif($array[$i]=="Chromosome"){
                        $location=$array[$i+1]." ".$array[$i+2]." ".$array[$i+3];
                        $i+=3;
                    }elseif ($array[$i]=="gene") {
                        $geneId=$array[$i+1];
                        $i++;
                    }elseif($array[$i]=="transcript"){
                        $transcript=$array[$i+1];
                        $i++;
                    }elseif($array[$i]=="gene_biotype"){
                        $geneType=$array[$i+1];
                        $i++;
                    }elseif ($array[$i]=="transcript_biotype") {
                        $transType=$array[$i+1];
                        $i++;
                    }elseif($array[$i]=="gene_symbol"){
                        $sym=$array[$i+1];
                        $i++;
                    }elseif ($array[$i]=="description") {
                        for($j=$i+1; $j<count($array); $j++){
                            $description.=" ".$array[$j];
                        }
                        $i=count($array);
                    }
                }

                //value insertion
                $query="INSERT INTO pep VALUES (DEFAULT, :pepId, :chromid, :loc, :seq);";
                try{
                    $stmt=$myPDO->prepare($query);
                    $stmt->bindParam(":pepId", $pepId, PDO::PARAM_STR);
                    $stmt->bindParam(":chromid", $id, PDO::PARAM_STR);
                    $stmt->bindParam(":loc", $location, PDO::PARAM_STR);
                    $stmt->bindParam(":seq", $seq, PDO::PARAM_STR);
                    $stmt->execute();
                    $dbID=$myPDO->lastInsertId();
                }catch(Exception $e){
                    die("error insert pep".$e->getMessage());
                }
                //not annoted
                if(empty($geneId)){
                    $queryAnnot="INSERT INTO annot (id, annotid, validated)  VALUES (DEFAULT, :pepId, 0);";
                    try{
                        $stmt2=$myPDO->prepare($queryAnnot);
                        $stmt2->bindParam(":pepId", $pepId, PDO::PARAM_STR);
                        $stmt2->execute();
                    }catch(PDOException $e){
                        die("ERROR annot empty".$e->getMessage());
                    }
                }else{
                    //Already annoted
                    $queryAnnot="INSERT INTO annot VALUES (DEFAULT, :pepId, :geneId, :trans, :geneType, :transType, :symbol, :des, 1, NULL);";
                    try{
                        $stmt2=$myPDO->prepare($queryAnnot);
                        $stmt2->bindParam(":pepId", $pepId, PDO::PARAM_STR);
                        $stmt2->bindParam(":geneId", $geneId, PDO::PARAM_STR);
                        $stmt2->bindParam(":trans", $transcript, PDO::PARAM_STR);
                        $stmt2->bindParam(":geneType", $geneType, PDO::PARAM_STR);
                        $stmt2->bindParam(":transType", $transType, PDO::PARAM_STR);
                        $stmt2->bindParam(":symbol", $sym, PDO::PARAM_STR);
                        $stmt2->bindParam(":des", $description, PDO::PARAM_STR);
                        $stmt2->execute();
                    }catch(PDOException $e){
                        die("ERROR annot".$e->getMessage());
                    }
                }
            //value for cds table
            }else{

                //add the full sequence to the last row
                if(!empty($seq)){
                    $query="UPDATE cds SET sequence=:seq WHERE id=:id;";
                    try{
                        $stmt=$myPDO->prepare($query);
                        $stmt->bindParam(":seq", $seq, PDO::PARAM_STR);
                        $stmt->bindParam(":id", $dbID, PDO::PARAM_INT);
                        $stmt->execute();
                    }catch(Exception $e){
                        die("Error update cds seq".$e->getMessage());
                    }
                }
                $cdsId=$array[0];
                $flag=2;
                $seq="";
                $id=$array[3];
                $location=$array[5]." ".$array[6]." ".$array[7];

                //value insertion
                $query="INSERT INTO cds VALUES (DEFAULT, :cdsId, :chromid, :loc, :seq);";
                try{
                    $stmt=$myPDO->prepare($query);
                    $stmt->bindParam(":cdsId", $cdsId, PDO::PARAM_STR);
                    $stmt->bindParam(":chromid", $id, PDO::PARAM_STR);
                    $stmt->bindParam(":loc", $location, PDO::PARAM_STR);
                    $stmt->bindParam(":seq", $seq, PDO::PARAM_STR);
                    $stmt->execute();
                    $dbID=$myPDO->lastInsertId();
                }catch(Exception $e){
                    die("error cds insert".$id."this chrom id".$e->getMessage());
                }

            }
            $seq="";
        //if the ligne doesn't start with > the it goes into the sequence.
        }else{
            $seq=$seq.trim($line);
        }    
    }
    //update the last sequence in the last row inserted
    //check with flag if the table is genome, cds or pep
    if($flag==0){
        $query="UPDATE genome SET sequence=:seq WHERE id=:id;";
        try{
            $stmt=$myPDO->prepare($query);
            $stmt->bindParam(":seq", $seq, PDO::PARAM_STR);
            $stmt->bindParam(":id", $dbID, PDO::PARAM_INT);
            $stmt->execute();
        }catch(Exception $e){
            die("error last update seq genome".$e->getMessage());
        }
    }elseif($flag==1){
        $query="UPDATE pep SET sequence=:seq WHERE id=:id;";
        try{
            $stmt=$myPDO->prepare($query);
            $stmt->bindParam(":seq", $seq, PDO::PARAM_STR);
            $stmt->bindParam(":id", $dbID, PDO::PARAM_INT);
            $stmt->execute();
        }catch(Exception $e){
            die("error last update pep".$e->getMessage());
        }
    }else{
        $query="UPDATE cds SET sequence=:seq WHERE id=:id;";
        try{
            $stmt=$myPDO->prepare($query);
            $stmt->bindParam(":seq", $seq, PDO::PARAM_STR);
            $stmt->bindParam(":id", $dbID, PDO::PARAM_INT);
            $stmt->execute();
        }catch(Exception $e){
            die("error last update cds".$e->getMessage());
        }
    }
}
//check if genome annotated and valid it
function validGenome(){
    global $myPDO;
    $query="UPDATE genome
    SET isAnnotated=1
    FROM pep, annot
    WHERE genome.chromID=pep.chromID
    AND pep.pepid=annot.annotID
    AND annot.validated=1
    ;";
    try{
        $stmt=$myPDO->prepare($query);
        $stmt->execute();
    }catch(PDOException $e){
        die("ERROR VG".$e->getMessage());
    }
}
?>