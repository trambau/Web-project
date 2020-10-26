<?php

function getName(){
    try {
        $datab="db";
        $user="postgres";
        $dbpswd="postgres";

        $conn=new PDO("pgsql:host=localhost;dbname=$datab", $user, $dbpswd);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        $stmt = $conn->prepare('SELECT name FROM genome WHERE name ILIKE :term ');
        $stmt->execute(array('term' => '%'.$_GET['term'].'%'));
        
        while($row = $stmt->fetch()) {
            $return_arr[] =  $row['name'];
        }

    } catch(PDOException $e) {
        echo 'ERROR: ' . $e->getMessage();
    }
    return $return_arr;
}


if (isset($_GET['term'])){
    $return_arr = array();
    $return_arr=getName();
    /* Toss back results as json encoded array. */
    echo json_encode($return_arr);
}

?>