<?php
include('functions.php');

$type=$_GET['type'];
if(isset($_GET['id'])){
    global $myPDO;
    $id=intval($_GET['id']);
    $query="SELECT * FROM genome WHERE id=:id;";
    try{
        $stmt=$myPDO->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $res=$stmt->fetch();
    }catch(PDOException $e){
        die($e->getMessage());
    }
}

?>
<!DOCTYPE html>
<html>
<title>
View
</title>
<header>
</header>
<body>
<table>
    <tbody>
        <tr>
            
        </tr>
    </tbody>
</table>

</body>
</html>