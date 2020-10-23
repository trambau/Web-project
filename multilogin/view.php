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
    <script>
        function addNewlines(str) {
            var result = '';
            while (str.length > 0) {
                result += str.substring(0, 100) + '\n';
                str = str.substring(100);
            }
            return result;
        }
    </script>
<table>
    <tbody>
        <tr>
        <span style="width:200px; word-wrap:break-word; display:inline-block; font-family:monospace"> 
   <?php echo $res['sequence'];?>
        </span>
        </tr>
    </tbody>
</table>

</body>
</html>