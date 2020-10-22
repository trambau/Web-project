<?php
include('functions.php');

if (!isLoggedIn()) {
    $_SESSION['msg'] = "You must log in first";
    header('location: login.php');
}
$type=$_POST['type'];
function search(){
    $genomeid=trim($_POST['genomeID']);
    $loc=trim($_POST['location']);
    $seq=trim($_POST['sequence']);
    global $type;
    if($type=="genome"){
        $res=genomeSearch();
    }else{
        $res=pepSearch();
    }
    return $res;
}
function genomeSearch(){
    global $myPDO;
    $query="SELECT id, geneid, loc, sequence from genome;";
    try{
        $stmt=$myPDO->prepare($query);
        $stmt->execute();
        $res=$stmt;
       // $res=$stmt->fetchAll();
    }catch(PDOException $e){
        die($e->getMessage());
    }
    return $res;
}
function pepSearch(){
    global $myPDO;
    $query="SELECT ";
}
$res=search();
?>

<!DOCTYPE html>
<html>
<title>
Results
</title>
<header>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
</header>
<body>

<table class="table table-striped table-advance table-hover">
	<h4><i class="fa fa-angle-right"></i> Results </h4>
		<hr>
		<thead>
		<tr>
			<th>Gene ID</th>
		</tr>
		</thead>
<tbody>
    <?php 
	while($row=$res->fetch(PDO::FETCH_ASSOC)){
	?>
	<tr>
			<td onclick="location.href='view.php?id=<?php echo $row['id'];?>?type=<?php echo $type;?>'"><?php echo $row['geneid'];?></td>
            
	</tr>
	<?php 
	}
	?>
</tbody>
</table>

</body>
</html>