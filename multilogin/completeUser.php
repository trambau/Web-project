<?php
include('functions.php');
//Get all the email matching with the input from the user
function getemail(){
    global$myPDO;
    try {
        //select user with at least annotator privileges
        $query="SELECT email FROM users WHERE email ILIKE :term
        INTERSECT(
        SELECT email FROM users WHERE userrole='annotator'
        UNION SELECT email FROM users WHERE userrole='validator'
        UNION SELECT email FROM users WHERE userrole='admin'
        );";
        $stmt = $myPDO->prepare($query);
        $stmt->execute(array('term' => '%'.$_GET['term'].'%'));
        
        while($row = $stmt->fetch()) {
            $return_arr[] =  $row['email'];
        }

    } catch(PDOException $e) {
        echo 'ERROR: ' . $e->getMessage();
    }
    return $return_arr;
}


if (isset($_GET['term'])){
    $return_arr = array();
    $return_arr=getemail();
    /* Toss back results as json encoded array. */
    echo json_encode($return_arr);
}

?>