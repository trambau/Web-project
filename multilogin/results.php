<?php
session_start();
include('functions.php');
//check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['msg'] = "You must log in first";
    header('location: login.php');
}


//--------------PAGES SPLIT
$nbres=20;
$totpage;
if(isset($_GET['page'])){
    $page=$_GET['page'];
}else{
    $page=1;
}
$startat=($page-1)*$nbres;
//----------------------------

$type=$_SESSION['type'];
function search(){
    $name=$_SESSION['name'];
    $genomeid=$_SESSION['genomeid'];
    $loc=$_SESSION['loc'];
    $seq=$_SESSION['seq'];
    $geneid=$_SESSION['geneid'];
    $id=$_SESSION['id'];
    $trans=$_SESSION['trans'];
    $des=$_SESSION['des'];
    $geneB=$_SESSION['geneB'];
    $transB=$_SESSION['transB'];
    $symbole=$_SESSION['symbole'];
    //check if there is an error with the sequence
        global $type;
        if($type=="genome"){
            $res=genomeSearch($name, $loc, $genomeid, $seq);
        }else{  
            $res=pepSearch($name, $loc, $seq, $geneid, $id, $trans, $transB, $des, $geneB, $symbole, $genomeid);
        }
        return $res;
    
}
function genomeSearch($name, $loc, $genomeid, $seq){
    global $myPDO;
    if(empty($name) && empty($loc) && empty($seq) && empty($genomeid)){
        $query="SELECT id, chromid, name from genome;";
        try{
            $stmt=$myPDO->prepare($query);
            $stmt->execute();
            $res=$stmt;
        }catch(PDOException $e){
            die($e->getMessage());
        }
        
    }else{
        $array=array($name, $loc, $genomeid);
        $search='';
        $seq='%'.$seq.'%';
        foreach($array as $val){
            if(!(empty($val)) && !(empty($search))){
                $search.=' & '.$val; 
            }elseif(!(empty($val)) && empty($search)){
                $search=$val;
            }
        }
        //check if search not empty 
        if(!empty($search)){
            $query="SELECT id, chromid, name FROM genome WHERE to_tsvector('english', chromid ||' '|| name ||' '|| loc) @@ plainto_tsquery(:par)
                    INTERSECT SELECT id, chromid, name FROM genome WHERE sequence ILIKE :seq;";
        }else{
            $query="SELECT id, chromid, name FROM genome WHERE sequence ILIKE :seq;";
        }
        try{
            $stmt=$myPDO->prepare($query);
            if(!empty($search)){
                $stmt->bindParam(":par", $search);
            }
            $stmt->bindParam(":seq", $seq, PDO::PARAM_STR);
            $stmt->execute();
            $res=$stmt;
        }catch(PDOException $e){
            die($e->getMessage());
        }
    }



    return $res;
}
function pepSearch($name, $loc, $seq, $geneid, $id, $trans, $transB, $des, $geneB, $symbole, $genomeid){
    global $myPDO, $startat, $nbres, $totpage;
    //return all the protein
    if(empty($name) && empty($loc) && empty($seq)&& empty($geneid)&& empty($geneB)&& empty($des)&& empty($id)&& empty($trans)&& empty($transB)&& empty($symbole) && empty($genomeid)){
        $query="SELECT pep.id, name, pepid, location, pep.chromid FROM pep, genome WHERE genome.chromid=pep.chromid LIMIT :nbres OFFSET :startat;";
        try{
            $stmt=$myPDO->prepare($query);
            $stmt->bindParam(":nbres", $nbres, PDO::PARAM_INT);
            $stmt->bindParam(":startat", $startat, PDO::PARAM_INT);
            $stmt->execute();
            $res=$stmt;
        }catch(PDOException $e){
            die($e->getMessage());
        }
        $q2="SELECT count(pepid) FROM pep;";
        try{
            $stmt2=$myPDO->prepare($q2);
            $stmt2->execute();
            $res2=$stmt2->fetch();
            $totpage=ceil($res2['count']/$nbres);
        }catch(PDOException $e){
            die($e->getMessage());
        }
    }else{
        $array=array($name, $loc, $geneid, $id, $trans, $transB, $des, $geneB, $symbole, $genomeid);
        $search='';
        //get the sequence ready for the query
        $seq='%'.$seq.'%';
        foreach($array as $val){
            if(!(empty($val)) && !(empty($search))){
                $search.=' & '.$val; 
            }elseif(!(empty($val)) && empty($search)){
                $search=$val;
            }
        }
        //check if sequence is nucleotids or peptides
        if(preg_match('/[^atgc%]/i', $seq)){
            //check if there are input parameters other than sequence
            if(!empty($search)){
                //query with parameter and sequence pep
            $query="SELECT pep.id, pep.chromid, name, location, pepid FROM pep, genome, annot WHERE pep.chromid=genome.chromid AND pepid=annotid AND to_tsvector('english', pep.chromid ||' '|| name ||' '|| location ||' '||pepid||' '||geneid||' '||transcript||' '||genetype||' '||transcrypttype||' '||symbol||' '||description) @@ plainto_tsquery(:par)
                    INTERSECT SELECT pep.id, pep.chromid, name, pep.location, pepid FROM pep, genome WHERE pep.chromid=genome.chromid AND pep.sequence ILIKE :seq LIMIT :nbres OFFSET :startat;";
            $query2="SELECT pep.id FROM pep, genome, annot WHERE pepid=annotid AND pep.chromid=genome.chromid AND to_tsvector('english', pep.chromid ||' '|| name ||' '|| location ||' '||pepid||' '||geneid||' '||transcript||' '||genetype||' '||transcrypttype||' '||symbol||' '||description) @@ plainto_tsquery(:par)
                    INTERSECT SELECT pep.id FROM pep WHERE pep.sequence ILIKE :seq;";
            }else{
                //query with just the sequence pep
                $query="SELECT pep.id, pep.chromid, name, pep.location, pepid FROM pep, genome WHERE pep.chromid=genome.chromid AND pep.sequence ILIKE :seq LIMIT :nbres OFFSET :startat;";
                $query2="SELECT pep.id FROM pep WHERE pep.sequence ILIKE :seq;";

            }
        }else{
            //check if there are input parameters other than sequence
            if(!empty($search)){
                //query with parameters and gene sequence
            $query="SELECT pep.id, pep.chromid, name, location, pepid FROM pep, genome, annot WHERE pepid=annotid AND pep.chromid=genome.chromid AND to_tsvector('english', pep.chromid ||' '|| name ||' '|| location ||' '||pepid||' '||geneid||' '||transcript||' '||genetype||' '||transcrypttype||' '||symbol||' '||description) @@ plainto_tsquery(:par)
                    INTERSECT SELECT pep.id, pep.chromid, name, pep.location, pepid FROM pep, cds, genome WHERE pep.chromid=genome.chromid AND cds.sequence ILIKE :seq AND cdsid=pepid LIMIT :nbres OFFSET :startat;";
            $query2="SELECT pep.id FROM pep, genome, annot WHERE pepid=annotid AND pep.chromid=genome.chromid AND to_tsvector('english', pep.chromid ||' '|| name ||' '|| location ||' '||pepid||' '||geneid||' '||transcript||' '||genetype||' '||transcrypttype||' '||symbol||' '||description) @@ plainto_tsquery(:par)
                    INTERSECT SELECT pep.id FROM pep, cds, genome WHERE pep.chromid=genome.chromid AND cds.sequence ILIKE :seq AND cdsid=pepid;";    
            }else{
                //query with the gene sequence only
                $query="SELECT pep.id, pep.chromid, name, pep.location, pepid FROM pep, cds, genome WHERE pep.chromid=genome.chromid AND cds.sequence ILIKE :seq AND cdsid=pepid LIMIT :nbres OFFSET :startat;";
                //query to get the number of row
                $query2="SELECT pep.id FROM pep, cds WHERE cds.sequence ILIKE :seq AND cdsid=pepid;";

            }
        }
        try{
            $stmt=$myPDO->prepare($query);
            $stmt2=$myPDO->prepare($query2);
            //check if parameter needed
            if(!empty($search)){
            $stmt->bindParam(":par", $search);
            $stmt2->bindParam(":par", $search);
            }
            $stmt->bindParam(":seq", $seq, PDO::PARAM_STR);
            $stmt2->bindParam(":seq", $seq, PDO::PARAM_STR);
            $stmt->bindParam(":nbres", $nbres, PDO::PARAM_INT);
            $stmt->bindParam(":startat", $startat, PDO::PARAM_INT);
            $stmt->execute();
            $stmt2->execute();
            $res=$stmt;
            
            $nbrow=$stmt2->rowCount();
            $totpage=ceil($nbrow/$nbres);
        }catch(PDOException $e){
            die($e->getMessage());
        }
    }
    
return $res;
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
<div class="header" style="background-color:dodgerblue">
        <br>
        <a style="float:right;color:red" href=".?logout='1'">logout</a>
        <br>
        <a style="color:brown" href="search.php">Search</a>
        <h2 style="color:azure">LOGO</h2>
    </div>
    
    <?php
    //Genome Type
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
	<tr onclick="location.href='view.php?id=<?php echo $row['id'];?>&type=genome'">
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

//--------------------Display peptides-----------------
else{
?>
<table class="table table-striped table-advance table-hover">
	<h4><i class="fa fa-angle-right"></i> Results </h4>
		<br>
		<thead>
		<tr>
            <th>Name</th>
			<th>Peptide ID</th>
            <th>Chromosome ID</th>
            <th>Location</th>
		</tr>
		</thead>
<tbody>
    <?php 
	while($row=$res->fetch(PDO::FETCH_ASSOC)){
	?>
	<tr onclick="location.href='view.php?id=<?php echo $row['id'];?>&type=pep'">
            <td><?php echo $row['name'];?></td>
            <td><?php echo $row['pepid'];?></td>
            <td><?php echo $row['chromid'];?></td>
            <td><?php echo $row['location'];?></td>
    </tr>
    <?php
    //end while
    }
    ?>
</tbody>
</table>

<?php
//end if type=pep/cds
}
?>
<!--------------------------PAGINATION------------------------>
<nav aria-label="Page navigation">
<ul class="pagination" max-size='10'>

<!--Get previous page-->
<li class="page-item"><a class="page-link" href="results.php?page=1">First</a></li>
<li class="page-item"><a class="page-link" href="results.php?page=<?php 
if($page>1){
    echo $page-1;
}else{
    echo $page;
}?>">Previous</a></li>

<?php
$i=$page;
$pagesDisplayed=10;
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
        <li class="page-item"><a class="page-link" href="results.php?page=<?php echo $k;?>"><?php echo $k;?></a></li>
        <?php   
    }
}
    while($i<=$totpage && $i<$page+$pagesDisplayed){
?>
    <li class="page-item"><a class="page-link" href="results.php?page=<?php echo $i;?>"><?php echo $i;?></a></li>
    
<?php
    $i++;
    }//end while   
?>
<!-- Get next page-->
<li class="page-item"><a class="page-link" href="results.php?page=<?php
if($page<$totpage){
    echo $page+1;
}else{
    echo $totpage;
}
?>">Next</a></li>
<li class="page-item"><a class="page-link" href="results.php?page=<?php echo $totpage;?>">Last</a></li>

</ul>
</nav>
</body>
</html>