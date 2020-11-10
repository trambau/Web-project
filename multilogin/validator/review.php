<?php 
	include('../functions.php');
	if (!isLoggedIn()) {
		$_SESSION['msg'] = "You must log in first";
		header('location: ../login.php');
	}
	if(!isValidator()){
		header('location:../index.php');
	}
//REJECT the annotation and put a comment 
if(!empty($_GET['annotationid']) && isset($_GET['comment'])){
	global $myPDO;
	$id=$_GET['annotationid'];
	$com=$_GET['comment'];
	$query="UPDATE annot SET geneid='', transcript='', genetype='', transcrypttype='', symbol='', description=:com, upreview=0 WHERE annotid=:id;";
	try{
		$stmt=$myPDO->prepare($query);
		$stmt->bindParam(":com", $com, PDO::PARAM_STR);
		$stmt->bindParam(":id", $id, PDO::PARAM_STR);
		$stmt->execute();
	}catch(PDOException $e){
		die($e->getMessage());
	}
}

//VALIDATE the annotation and put them in the DATABASE
if(!empty($_GET['aid'])){
	global $myPDO;
	$id=$_GET['aid'];
	$query="UPDATE annot SET validated=1 WHERE id=:id;";
	try{
		$stmt=$myPDO->prepare($query);
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		$stmt->execute();
	}catch(PDOException $e){
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
$path="review.php";
//---------------------------------

//-------------------------HTML----------------------------------
?>

<!DOCTYPE html>
<html>
<head>
	<title>Home</title>
	<!--<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">-->
	<link rel="stylesheet" href="../assets/bootstrap.css"> 

	<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/base/minified/jquery-ui.min.css" type="text/css" /> 
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
    <script type="text/javascript" src="http://code.jquery.com/ui/1.10.1/jquery-ui.min.js"></script>  
</head>
<body>
<!-- Display for Validator-->

<div class="table-responsive">
	<table class="table table-striped table-advance table-hover">
	
		<hr>
		<thead>
		<tr>
			<th style="font-size:12px">Sequence ID</th>
			<th style="font-size:12px">Strain</th>
			<th style="font-size:12px">Annotator</th>
			<th style="font-size:12px">geneID</th>
			<th style="font-size:12px">Gene biotype</th>
			<th style="font-size:12px">transcript</th>
			<th style="font-size:12px">transcript biotype</th>
			<th style="font-size:12px">symbole</th>
			<th style="font-size:12px">Description</th>
			<th style="font-size:12px">Validate</th>
			<th style="font-size:12px">Reject</th>
		</tr>
		</thead>
		<tbody>
			<?php
            global $myPDO; 			//Get the annotation needing review.
			$query="SELECT annot.id as aid, annotid, name, email, geneid, transcript, genetype, transcrypttype, symbol, description 
			FROM annot, pep, genome, users 
            WHERE annotid=pepid AND pep.chromid=genome.chromid AND users.id=annotator AND upreview=1 AND validated=0 AND annotator IS NOT NULL LIMIT :nbres OFFSET :startat;";
            $q2="SELECT annotid FROM annot WHERE upreview=1 AND validated=0 AND annotator IS NOT NULL;";
			try{
                $stmt=$myPDO->prepare($query);
                $stmt->bindParam(":nbres", $nbres, PDO::PARAM_INT);
                $stmt->bindParam(":startat", $startat, PDO::PARAM_INT);
				$stmt->execute();
                $st2=$myPDO->prepare($q2);
                $st2->execute();
                $nbrow=$st2->rowCount();
                $totpage=ceil($nbrow/$nbres);
			}catch(PDOException $e){
				die($e->getMessage());
			}
			while($row=$stmt->fetch()){
            ?>
            
			<tr>
				<td  style="font-size:12px"><?php echo $row['annotid'];?></td>
				<td  style="font-size:12px"><?php echo $row['name'];?></td>
				<td  style="font-size:12px"><?php echo $row['email'];?></td>
				<td  style="font-size:12px"><?php echo $row['geneid'];?></td>
				<td  style="font-size:12px"><?php echo $row['genetype'];?></td>
				<td  style="font-size:12px"><?php echo $row['transcript'];?></td>
				<td  style="font-size:12px"><?php echo $row['transcrypttype'];?></td>
				<td  style="font-size:12px"><?php echo $row['symbol'];?></td>
				<td  style="font-size:12px; word-wrap:break-word"><?php echo $row['description'];?></td>
				<td >
					<a href="review.php?aid=<?php echo $row['aid'];?>">
					<button class="btn btn-xs btn-info" onClick="" style="font-size:x-small"><i class="fa fa-trash-o "></i>Validate</button>
					</a>
				</td>
				<td class="col-1">
	<script>
	//take input from user
	function getComment(){
	var message=window.prompt("Write a comment for the annotator.");
	window.location.href = "review.php?comment="+message+"&annotationid="+"<?php echo $row['annotid'];?>";
	}
	</script>
				<button class="btn btn-danger btn-xs" onClick="getComment()" style="font-size:x-small"><i class="fa fa-trash-o "></i>Reject</button>
				</td>
			</tr>
			<?php	
			}//end while
			?>
		</tbody>
	</table>
</div>
  <!--------------------------PAGINATION------------------------>
  <?php
 if($totpage>1){ 
  ?>
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
if($totpage>$pagesDisplayed){
$j=$totpage-$pagesDisplayed-1;
}else{
    $j=1;
}
//Check if the current page is in the last pages
if($page>$j){
    //add index between the current page and j pages before the last page
    for($k=$j; $k<$page; $k++){
		?>
        <li class="page-item"><a class="page-link" href="<?php echo $path."?page=".$k;?>"><?php echo $k;?></a></li>
        <?php   
    }
}
    while($i<=$totpage && $i<$page+$pagesDisplayed){
?>
    <li class="page-item"><a class="page-link" href="<?php  echo $path."?page=".$i;?>"><?php echo $i;?></a></li>
    
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
<?php
}
?>
</body>
</html>