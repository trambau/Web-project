<?php 
    include('../functions.php');
//update the annotator in the annotaion table
if(isset($_GET['uid']) && isset($_GET['pepid']) && !empty($_GET['uid'])){
	global $myPDO;
	$id=$_GET['uid'];
	$pepid=$_GET['pepid'];
	$query="UPDATE annot SET annotator=(SELECT id FROM users WHERE email=:id) WHERE annotid=:pepid;";
	try{
		$stmt=$myPDO->prepare($query);

		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		$stmt->bindParam(":pepid", $pepid, PDO::PARAM_STR);
		$stmt->execute();
		
		if($stmt){
			echo "<script>alert('Sequence assigned ');</script>";
	}
	}catch(Exception $e){
		die($e->getMessage());
	}
}

//------------PAGE--------------
$nbres=8;
$totpage;
if(isset($_GET['page'])){
    $page=$_GET['page'];
}else{
    $page=1;
}
$startat=($page-1)*$nbres;
$path="assign.php";
//---------------------------------
?>

<!DOCTYPE html>
<html>
<head>
	<title>Home</title>
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
	<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/base/minified/jquery-ui.min.css" type="text/css" /> 
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
    <script type="text/javascript" src="http://code.jquery.com/ui/1.10.1/jquery-ui.min.js"></script>  
</head>
<body>
	<div class="table-responsive">
	<table class="table table-striped table-advance table-hover">
	
		<hr>
		<thead>
		<tr>
			<th>Sequence ID</th>
			<th>Strain</th>
			<th>Chromosome ID</th>
			<th>Annotator</th>
			<th>Assign</th>
		</tr>
		</thead>
	<tbody>
	<!-- Display the non assigned sequences  !!!VALIDATE=0 TO REMOVE(MAYBE)-->
	<?php 
	global $myPDO;
    $query="SELECT name, pepid, pep.chromid FROM annot, pep, genome where annotid=pepid and pep.chromid=genome.chromid and isannotated=0 and annotator IS NULL LIMIT :nbres OFFSET :startat;";
    $q2="SELECT annotid FROM annot, genome, pep where annotid=pepid and pep.chromid=genome.chromid and isannotated=0 and annotator IS NULL;";
    $stmt=$myPDO->prepare($query);
    $st2=$myPDO->prepare($q2);
    $stmt->bindParam(":nbres", $nbres, PDO::PARAM_INT);
    $stmt->bindParam(":startat", $startat, PDO::PARAM_INT);
    $stmt->execute();
    $st2->execute();

    $nbrow=$st2->rowCount();
    $totpage=ceil($nbrow/$nbres);
	while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
	?>
	<tr>
        <td><?php echo $row['pepid'];?></td> 
        <td><?php echo $row['name'];?></td>
        <td><?php echo $row['chromid'];?></td>
        <td>
        <input type="text" class="form-control" name="annot" id="annot" value="" placeholder="email@mail.com">
<!--autocomplete-->
<script type="text/javascript">
$(function() {
    //autocomplete
    $("#annot").autocomplete({
        source: "completeUser.php",
        minLength: 1
    });                
});
</script>
        </td>
        <td>
<script>
    function goTo(){
        var us=document.getElementById("annot").value;
        if(us===""){
            alert('Please select an Email first.');
        }else{
            window.location.href = "testindex.php?uid="+us+"&pepid="+"<?php echo $row['pepid'];?>";
        }
    }
</script>
			<button class="btn btn-danger btn-xs" onClick="goTo()"><i class="fa fa-trash-o "></i>Assign</button>
			</td>
	</tr>
	<?php 
	}//end while
	?>
</tbody>
</table >
</div>

</div>
</div>
<div>
    <!--------------------------PAGINATION------------------------>
<nav aria-label="Page navigation">
<ul class="pagination" max-size='10'>

<!--Get previous page-->
<li class="page-item"><a class="page-link" href="<?php echo $path."?page=";?>1">First</a></li>
<li class="page-item"><a class="page-link" href="<?php echo $path."?page=";?><?php 
if($page>1){
    echo $page-1;
}else{
    echo $page;
}?>">Previous</a></li>

<?php

$i=$page;
$pagesDisplayed=5;
//check if there are more than one page to display
if($totpage>1){
$j=$totpage-$pagesDisplayed-1;
}else{
    $j=1;
}
//Check if the current page is in the last ten pages
if($page>$j){
    //add index between the current page and 10 pages before the last page
    for($k=$j; $k<$page; $k++){
        ?>
        <li class="page-item"><a class="page-link" href="<?php echo $path."?page=".$k;?>"><?php echo $k;?></a></li>
        <?php   
    }
}
    while($i<=$totpage && $i<$page+$pagesDisplayed){
?>
    <li class="page-item"><a class="page-link" href=""<?php  echo $path."?page=".$i;?>"><?php echo $i;?></a></li>
    
<?php
    $i++;
    }//end while   
?>
<!-- Get next page-->
<li class="page-item"><a class="page-link" href="<?php echo $path."?page=";?><?php
if($page<$totpage){
    echo $page+1;
}else{
    echo $totpage;
}
?>">Next</a></li>
<li class="page-item"><a class="page-link" href="<?php echo $path."?page=".$totpage;?>">Last</a></li>

</ul>
</nav>
</body>
</html>