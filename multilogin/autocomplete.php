<?php
include("functions.php");
//get the name matching the user input
function getName(){
    global $myPDO;
    try {
        
        $stmt = $myPDO->prepare('SELECT name FROM genome WHERE name ILIKE :term ');
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