<?php
include('functions.php');
global $myPDO;
$query="select pepid from pep;";
$stmt=$myPDO->prepare($query);
$stmt->execute();
$size=$stmt->rowCount();
var_dump($size);

?>