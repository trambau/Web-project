<?php
include('functions.php');

if (!isLoggedIn()) {
    $_SESSION['msg'] = "You must log in first";
    header('location: login.php');
}
/*  
$type=$_POST['type'];
function search(){
    $name=trim($_POST['name']);
    $genomeid=trim($_POST['genomeID']);
    $loc=trim($_POST['location']);
    $seq=trim($_POST['sequence']);
    $des=trim($_POST['description']);
    $geneB=trim($_POST['geneBiotype']);
    $transB=trim($_POST['transBiotype']);
    $def=trim($_POST['']);
    //check sequnece length
    if(strlen($seq) < 3  && !(empty($seq))){
        $seq_er = "The seq must have atleast 3 characters.";
    }
    //check if there is an error with the sequence
    if(empty($seq_er)){
        global $type;
        if($type=="genome"){
            $res=genomeSearch($name, $loc, $genomeid, $seq);
        }else{  
            $res=pepSearch();
        }
        return $res;
    }
    
}
function genomeSearch($name, $loc, $genomeid, $seq){
    global $myPDO;
    $array=array($name, $loc, $genomeid);
    $search='';
    foreach($array as $val){
        if(!(empty($val)) && !(empty($search))){
            $search.=' & '.$val; 
        }elseif(!(empty($val)) && empty($search)){
            $search=$val;
        }
    }
    $query="SELECT id, chromid, name from genome where to_tsvector('english', chromid ||' '|| name ||' '|| loc) @@ plainto_tsquery(:par);";
    try{
        $stmt=$myPDO->prepare($query);
        $stmt->bindParam(":par", $search);
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
    $query="SELECT id, ";
}
$res=search();
header('results.php');
*/
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
<div class="header" style="background-color:dodgerblue">
        <br>
        <a style="float:right;color:red" href="home.php?logout='1'">logout</a>
        <h2 style="color:azure">LOGO</h2>
    </div>
    <?php
    if($type=="genome"){
    ?>
<table class="table table-striped table-advance table-hover">
	<h4><i class="fa fa-angle-right"></i> Results </h4>
		<br>
		<thead>
		<tr>
            <th>ID</th>
			<th>Gene ID</th>
            <th>Name</th>
		</tr>
		</thead>
<tbody>
    <?php 
	while($row=$res->fetch(PDO::FETCH_ASSOC)){
	?>
	<tr onclick="location.href='view.php?id=<?php echo $row['id'];?>'">
            <td><?php echo $row['id'];?></td>
            <td><?php echo $row['chromid'];?></td>
            <td><?php echo $row['name'];?></td>

    </tr>
	<?php 
	} //end while
	?>
</tbody>
</table>

<?php 
} //end if type==genome
?>

</body>
</html>