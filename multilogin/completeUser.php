<?php

function getemail(){
    try {
        
        $datab="db";
        $user="postgres";
        $dbpswd="postgres";
/*
        $datab="sample";
        $user="trambaud";
        $dbpswd="trambaud";*/

        $conn=new PDO("pgsql:host=localhost;dbname=$datab", $user, $dbpswd);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        //select user with at least annotator privileges
        $query="SELECT email FROM users WHERE email ILIKE :term
        INTERSECT(
        SELECT email FROM users WHERE userrole='annotator'
        UNION SELECT email FROM users WHERE userrole='validator'
        UNION SELECT email FROM users WHERE userrole='admin'
        );";
        $stmt = $conn->prepare($query);
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