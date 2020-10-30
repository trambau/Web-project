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
if(isset($_GET['type'])){
    $type=$_GET['type'];
}else{
    $type=$_SESSION['type'];
}
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
  
    if(isset($_GET['search'])){
        $_GET['search']="%".$_GET['search']."%";
        $q="SELECT id, chromid, name FROM genome WHERE name ILIKE :s
        UNION
        SELECT id, chromid, name FROM genome WHERE chromid ILIKE :s
        UNION
        SELECT id, chromid, name FROM genome WHERE loc ILIKE :s;";
        try{
            $stmt=$myPDO->prepare($q);
            $stmt->bindParam(":s", $_GET['search'], PDO::PARAM_STR);
            $stmt->execute();
            $res=$stmt;
        }catch(PDOException $e){
            die($e->getMessage());
        }
    }elseif(empty($name) && empty($loc) && empty($seq) && empty($genomeid)){
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
    if(isset($_GET['search'])){
        $_GET['search']="%".$_GET['search']."%";
        $q="(SELECT pep.id, name, pepid, location, pep.chromid FROM genome, pep WHERE genome.chromid=pep.chromid AND name ILIKE :s ORDER BY pep.id LIMIT :nbres OFFSET :startat)
        UNION
        (SELECT pep.id, name, pepid, location, pep.chromid FROM genome,pep WHERE genome.chromid=pep.chromid AND pepid ILIKE :s ORDER BY pep.id LIMIT :nbres OFFSET :startat)
        UNION
        (SELECT pep.id, name, pepid, location, pep.chromid FROM genome,pep WHERE genome.chromid=pep.chromid AND pep.chromid ILIKE :s ORDER BY pep.id LIMIT :nbres OFFSET :startat)
        UNION
        (SELECT pep.id, name, pepid, location, pep.chromid FROM genome,pep WHERE genome.chromid=pep.chromid AND location ILIKE :s ORDER BY pep.id LIMIT :nbres OFFSET :startat)
        UNION
        (SELECT pep.id, name, pepid, location, pep.chromid FROM genome,pep, annot WHERE genome.chromid=pep.chromid AND geneid ILIKE :s AND annotid=pepid ORDER BY pep.id LIMIT :nbres OFFSET :startat)
        UNION
        (SELECT pep.id, name, pepid, location, pep.chromid FROM genome,pep, annot WHERE genome.chromid=pep.chromid AND transcript ILIKE :s AND annotid=pepid ORDER BY pep.id LIMIT :nbres OFFSET :startat) 
        UNION
        (SELECT pep.id, name, pepid, location, pep.chromid FROM genome,pep, annot WHERE genome.chromid=pep.chromid AND genetype ILIKE :s AND annotid=pepid ORDER BY pep.id LIMIT :nbres OFFSET :startat)
        UNION
        (SELECT pep.id, name, pepid, location, pep.chromid FROM genome,pep, annot WHERE genome.chromid=pep.chromid AND transcrypttype ILIKE :s AND annotid=pepid ORDER BY pep.id LIMIT :nbres OFFSET :startat)
        UNION
        (SELECT pep.id, name, pepid, location, pep.chromid FROM genome,pep, annot WHERE genome.chromid=pep.chromid AND symbol ILIKE :s AND annotid=pepid ORDER BY pep.id LIMIT :nbres OFFSET :startat)
        UNION
        (SELECT pep.id, name, pepid, location, pep.chromid FROM genome,pep, annot WHERE genome.chromid=pep.chromid AND description ILIKE :s AND annotid=pepid ORDER BY pep.id LIMIT :nbres OFFSET :startat)
        ;";
        $q2="SELECT pep.id FROM genome, pep WHERE genome.chromid=pep.chromid AND name ILIKE :s
        UNION
        SELECT pep.id FROM pep WHERE pepid ILIKE :s
        UNION
        SELECT pep.id FROM pep WHERE pep.chromid ILIKE :s
        UNION
        SELECT pep.id FROM pep WHERE location ILIKE :s
        UNION
        SELECT pep.id FROM pep, annot WHERE geneid ILIKE :s AND annotid=pepid
        UNION
        SELECT pep.id FROM pep, annot WHERE transcript ILIKE :s AND annotid=pepid
        UNION
        SELECT pep.id FROM pep, annot WHERE genetype ILIKE :s AND annotid=pepid
        UNION
        SELECT pep.id FROM pep, annot WHERE transcrypttype ILIKE :s AND annotid=pepid
        UNION
        SELECT pep.id FROM pep, annot WHERE symbol ILIKE :s AND annotid=pepid
        UNION
        SELECT pep.id FROM pep, annot WHERE description ILIKE :s AND annotid=pepid
        ;";
        try{
            $stmt=$myPDO->prepare($q);
            $s2=$myPDO->prepare($q2);
            $stmt->bindParam(":s", $_GET['search'], PDO::PARAM_STR);
            $stmt->bindParam(":nbres", $nbres, PDO::PARAM_INT);
            $stmt->bindParam(":startat", $startat, PDO::PARAM_INT);
            $s2->bindParam(":s", $_GET['search'], PDO::PARAM_STR);
            $s2->execute();
            $stmt->execute();
            $res=$stmt;

            $nbrow=$s2->rowCount();
            $totpage=ceil($nbrow/$nbres);
        }catch(PDOException $e){
            die($e->getMessage());
        }
    }elseif(empty($name) && empty($loc) && empty($seq)&& empty($geneid)&& empty($geneB)&& empty($des)&& empty($id)&& empty($trans)&& empty($transB)&& empty($symbole) && empty($genomeid)){
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
<link rel="stylesheet" href="assets/bootstrap.css">     
      <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

</header>
<body>
    <!-------TOPNAV---------------------------->
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color:dodgerblue">
  <a class="navbar-brand" href="index.php"><h4 style="margin:0px">LOGO</h4></a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <!----------------HOME for admin or users----------------->
        <?php
        if(isAdmin()){
        ?>
        <a class="nav-link" href="admin/home.php" >Home <span class="sr-only">(current)</span></a>
        <?php
        }else{
        ?>
        <a class="nav-link" href="index.php" >Home <span class="sr-only">(current)</span></a>
        <?php
        }
        ?>
        <!--------------------------->
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Menu
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
          <a class="dropdown-item" href="search.php">Search</a>
          <a class="dropdown-item" href="index.php">Home</a>
          <div class="dropdown-divider"></div>
          <!-----DISPLAY name of user and role------------>
          <p class="dropdown-item" style="color:darkcyan"><?php 
          $name=$_SESSION['user']['firstname'];
          if(isAdmin()){
            $name.="(".$_SESSION['user']['usertype'].")";
          }else{
            $name.="(".$_SESSION['user']['userrole'].")";
          }
          echo $name;?></p>

          <a class="dropdown-item" style="color:red" href=".?logout=1">Logout</a>
          <?php
          if(isAdmin()){
          ?>
          <div class="dropdown-divider"></div>
		  <a class="dropdown-item" style="color:darkslategrey" href="admin/createUser.php">Create User</a>
		  <a class="dropdown-item" style="color:darkslategrey" href="admin/parser.php">Add file</a>
          <?php
          }?>

        </div>
      </li>
    </ul>
    <div class="p-2">
    <form class="form-inline my-2 my-lg-1" style="line-height:75%" action="results.php" method="get">
       <div class="p-2">
		 <div style="float:inline-start" class="input-group">
		 <select class="btn btn-outline-light btn-mini" name="type">
    <option selected="selected" value="genome">Genome</option>
    <option value="pep">Peptide</option>
  </select>
      <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search" name="search">

    
         </div>
      <br>
      <small><a href="search.php" style="color:white">advanced search</a></small>
	   </div> 
	   <input type="submit" class="btn btn-outline-light" value="Search">
    </form>
    </div>

  </div>
</nav>
<br>
    
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