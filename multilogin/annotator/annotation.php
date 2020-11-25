<?php 
    include('../functions.php');
//update the annotator in the annotaion table
if (!isLoggedIn()) {
	$_SESSION['msg'] = "You must log in first";
	header('location: ../login.php');
}

if(!isAnnotator()){
    header('location:../index.php');
}

//SEND the annotations for review
if(!empty($_GET['rid'])){
	global $myPDO;
	$id=$_GET['rid'];
	$query="UPDATE annot SET upreview=1 WHERE annotid=:id;";
	try{
		$stmt=$myPDO->prepare($query);
		$stmt->bindParam(":id", $id, PDO::PARAM_STR);
		$stmt->execute();
	}catch(PDOException $e){
		die($e->getMessage());
	}
}
function updateAnnot($geneid, $geneT, $trans, $transT, $symbol, $des, $id){
	global $myPDO; 
	$query="UPDATE annot SET geneid=:geneid, transcript=:trans, genetype=:geneT, transcrypttype=:transT, symbol=:symbol, description=:des WHERE annotid=:upid;";
	try{
		$stmt=$myPDO->prepare($query);
		$stmt->bindParam(':geneid', $geneid, PDO::PARAM_STR);
		$stmt->bindParam(':geneT', $geneT, PDO::PARAM_STR);
		$stmt->bindParam(':trans', $trans, PDO::PARAM_STR);
		$stmt->bindParam(':transT', $transT, PDO::PARAM_STR);
		$stmt->bindParam(':symbol', $symbol, PDO::PARAM_STR);
		$stmt->bindParam(':des', $des, PDO::PARAM_STR);
		$stmt->bindParam(':upid', $id, PDO::PARAM_STR);
		$stmt->execute();
	}catch(PDOException $e){
		die($e->getMessage());
	}
}

//UPDATE annotation
if(isset($_POST['save-btn']) && !empty($_GET['upid'])){
	var_dump($_GET['upid']);
	updateAnnot( $_POST['geneid'], $_POST['geneT'],$_POST['trans'], $_POST['transT'], $_POST['symbol'], $_POST['des'], $_GET['upid']);
	/*
	global $myPDO; 
	$query="UPDATE annot SET geneid=:geneid, transcript=:trans, genetype=:geneT, transcrypttype=:transT, symbol=:symbol, description=:des WHERE annotid=:upid;";
	try{
		$stmt=$myPDO->prepare($query);
		$stmt->bindParam(':geneid', $_POST['geneid'], PDO::PARAM_STR);
		$stmt->bindParam(':geneT', $_POST['geneT'], PDO::PARAM_STR);
		$stmt->bindParam(':trans', $_POST['trans'], PDO::PARAM_STR);
		$stmt->bindParam(':transT', $_POST['transT'], PDO::PARAM_STR);
		$stmt->bindParam(':symbol', $_POST['symbol'], PDO::PARAM_STR);
		$stmt->bindParam(':des', $_POST['des'], PDO::PARAM_STR);
		$stmt->bindParam(':upid', $_GET['upid'], PDO::PARAM_STR);
		$stmt->execute();
	}catch(PDOException $e){
		die($e->getMessage());
	}*/
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
$path="annotation.php";
//---------------------------------
?>
<!DOCTYPE html>
<html>
<header>
<title>Annotations</title>
	<link rel="stylesheet" type="text/css" href="../assets/bootstrap.css">
	<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/base/minified/jquery-ui.min.css" type="text/css" /> 
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
    <script type="text/javascript" src="http://code.jquery.com/ui/1.10.1/jquery-ui.min.js"></script>  
</header>
<body>
<div class="table-responsive">
	<table class="table table-striped table-advance table-hover">
		<hr>
		<thead>
		<tr>
			<th>Sequence ID</th>
			<th>Strain</th>
			<th>geneID</th>
			<th>Gene biotype</th>
			<th>transcript</th>
			<th>transcript biotype</th>
			<th>symbole</th>
			<th>Description</th>
			<th>Save</th>
			<th>Validate</th>
		</tr>
		</thead>
		<tbody>
			<?php
			global $myPDO;
			//GET the sequences to annotate
			$query="SELECT DISTINCT annotid, name, pep.id as pid, genome.id as gid, geneid, transcript, genetype, transcrypttype, symbol, description 
			FROM annot, pep, genome, users 
            WHERE annotid=pepid AND pep.chromid=genome.chromid AND annotator=:id AND upreview=0;";
            $q2="SELECT annotid 
			FROM annot, pep
            WHERE annotid=pepid AND annotator=:id AND upreview=0;";

			try{
				$stmt=$myPDO->prepare($query);
				$stmt->bindParam(":id", $_SESSION['user']['id'], PDO::PARAM_STR);
				$stmt->execute();
                $s2=$myPDO->prepare($q2);
                $s2->bindParam(":id", $_SESSION['user']['id'], PDO::PARAM_STR);
                $s2->execute();
                $nbrow=$s2->rowCount();
                $totpage=ceil($nbrow/$nbres);
			}catch(PDOException $e){
				die($e->getMessage());
			}
			
			while($row=$stmt->fetch()){//get the annotation values not yet validated.
			?>

			<tr>
			
				<td onclick="window.parent.location.href='../view.php?id=<?php echo $row['pid'];?>&type=pep'"><u style=color:dark-blue"><?php echo $row['annotid'];?></u></td>
				<td onclick="window.parent.location.href='../view.php?id=<?php echo $row['gid'];?>&type=genome'"><u style=color:dark-blue"><?php echo $row['name'];?></u></td>
				<form action="<?php echo $_SERVER['PHP_SELF'];?>?upid=<?php echo $row['annotid'];?>" method="post">
				<td><input class="form-control" type="text" name="geneid" value="<?php echo $row['geneid'];?>"></td>
				<td><input class="form-control" type="text" name="geneT" value="<?php echo $row['genetype'];?>"></td>
				<td><input class="form-control" type="text" name="trans" value="<?php echo $row['transcript'];?>"></td>
				<td><input class="form-control" type="text" name="transT" value="<?php echo $row['transcrypttype'];?>"></td>
				<td><input class="form-control" type="text" name="symbol" value="<?php echo $row['symbol'];?>"></td>
				<td><input class="form-control" type="text" name="des" value="<?php echo $row['description'];?>"></td>
				<td><input type="submit" class="btn btn-xs btn-outline-dark" value="Save" name="save-btn"></td>
				</form>
				<!-- Button to send the annotations -->
				<td>
					<a href="annotation.php?rid=<?php echo $row['annotid'];?>">
					<button class="btn btn-info btn-xs" onClick=""><i class="fa fa-trash-o "></i>Validate</button>
					</a>
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
  if($totpage>1){//check if there is more than one page
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
} //end if
?>
</body>
</html>