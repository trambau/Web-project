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
            
            $resL=genomeSearch($name, $loc, $genomeid, $seq);
        }else{  
           
            $resL=pepSearch($name, $loc, $seq, $geneid, $id, $trans, $transB, $des, $geneB, $symbole, $genomeid);
        }
        return $resL;
    
}
function genomeSearch($name, $loc, $genomeid, $seq){
    global $myPDO;
  
    if(isset($_GET['search'])){
        if(!empty($_GET['search'])){
            
        $search="%".$_GET['search']."%";
            /*
            $query2="SELECT id, chromid, name FROM genome WHERE name ILIKE :s
            UNION
            SELECT id, chromid, name FROM genome WHERE chromid ILIKE :s
            UNION
            SELECT id, chromid, name FROM genome WHERE loc ILIKE :s;";
            */
            $query2="SELECT id, chromid, name, loc, sequence FROM genome WHERE name ILIKE :s
            UNION
            SELECT id, chromid, name, loc, sequence FROM genome WHERE chromid ILIKE :s
            UNION
            SELECT id, chromid, name, loc, sequence FROM genome WHERE loc ILIKE :s;";
        }else{
          
            $query2="SELECT id, chromid, name, loc, sequence FROM genome;";
        }
        try{
            $stmt=$myPDO->prepare($query2);
            $stmt2=$myPDO->prepare($query2);
            if(!empty($_GET['search'])){
                $stmt->bindParam(":s", $search, PDO::PARAM_STR);
                $stmt2->bindParam(":s", $search, PDO::PARAM_STR);
            }
            $stmt->execute();
            $stmt2->execute();
            $res=$stmt;
            $resList=$stmt2;
            /*
            while($row=$resList->fetch()){
                print($row['name']);
            }*/
        }catch(PDOException $e){
            die($e->getMessage());
        }
    }elseif(empty($name) && empty($loc) && empty($seq) && empty($genomeid)){
        //$query="SELECT id, chromid, name from genome;";
        $query2="SELECT id, chromid, name, loc, sequence from genome;";
       
        try{
            $stmt=$myPDO->prepare($query2);
            $stmt->execute();

            $resList=$stmt;
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
           
            $query2="SELECT id, chromid, name, loc, sequence  FROM genome WHERE to_tsvector('english', chromid ||' '|| name ||' '|| loc) @@ plainto_tsquery(:par)
                    INTERSECT SELECT id, chromid, name, loc, sequence FROM genome WHERE sequence ILIKE :seq;";
        }else{
            
            $query2="SELECT id, chromid, name, loc, sequence FROM genome WHERE sequence ILIKE :seq;";
        }
        try{
            $stmt=$myPDO->prepare($query2);
            if(!empty($search)){
                $stmt->bindParam(":par", $search);
            }
            $stmt->bindParam(":seq", $seq, PDO::PARAM_STR);
            $stmt->execute();
            $resList=$stmt;
            $res=$stmt;
        }catch(PDOException $e){
            die($e->getMessage());
        }
    }



    return [$res, $resList];
}
// function to search inside the peptides and cds
function pepSearch($name, $loc, $seq, $geneid, $id, $trans, $transB, $des, $geneB, $symbole, $genomeid){
   
    global $myPDO, $startat, $nbres, $totpage;
    //return all the protein
    if(isset($_GET['search'])){
        if(!empty($_GET['search'])){
            
        $search="%".$_GET['search']."%";
        $query="(SELECT pep.id, name, pepid, location, pep.chromid FROM genome, pep WHERE genome.chromid=pep.chromid AND name ILIKE :s ORDER BY pep.id)
        UNION
        (SELECT pep.id, name, pepid, location, pep.chromid FROM genome,pep WHERE genome.chromid=pep.chromid AND pepid ILIKE :s ORDER BY pep.id)
        UNION
        (SELECT pep.id, name, pepid, location, pep.chromid FROM genome,pep WHERE genome.chromid=pep.chromid AND pep.chromid ILIKE :s ORDER BY pep.id)
        UNION
        (SELECT pep.id, name, pepid, location, pep.chromid FROM genome,pep WHERE genome.chromid=pep.chromid AND location ILIKE :s ORDER BY pep.id)
        UNION
        (SELECT pep.id, name, pepid, location, pep.chromid FROM genome,pep, annot WHERE genome.chromid=pep.chromid AND geneid ILIKE :s AND annotid=pepid ORDER BY pep.id)
        UNION
        (SELECT pep.id, name, pepid, location, pep.chromid FROM genome,pep, annot WHERE genome.chromid=pep.chromid AND transcript ILIKE :s AND annotid=pepid ORDER BY pep.id) 
        UNION
        (SELECT pep.id, name, pepid, location, pep.chromid FROM genome,pep, annot WHERE genome.chromid=pep.chromid AND genetype ILIKE :s AND annotid=pepid ORDER BY pep.id)
        UNION
        (SELECT pep.id, name, pepid, location, pep.chromid FROM genome,pep, annot WHERE genome.chromid=pep.chromid AND transcrypttype ILIKE :s AND annotid=pepid ORDER BY pep.id)
        UNION
        (SELECT pep.id, name, pepid, location, pep.chromid FROM genome,pep, annot WHERE genome.chromid=pep.chromid AND symbol ILIKE :s AND annotid=pepid ORDER BY pep.id)
        UNION
        (SELECT pep.id, name, pepid, location, pep.chromid FROM genome,pep, annot WHERE genome.chromid=pep.chromid AND description ILIKE :s AND annotid=pepid ORDER BY pep.id)
        LIMIT :nbres OFFSET :startat
        ;";
  
        $query2="(SELECT DISTINCT pepid, pep.location, pep.sequence as seqp, cds.sequence as seqc, name, pep.chromid, annot.geneID, annot.transcript, annot.transcryptType, annot.geneType, annot.symbol, description FROM genome, pep, annot, cds WHERE pepid=cdsid and annotid=pepid and genome.chromid=pep.chromid AND name ILIKE :s)
        UNION
        (SELECT pepid, pep.location, pep.sequence as seqp, cds.sequence as seqc, name, pep.chromid, annot.geneID, annot.transcript, annot.transcryptType, annot.geneType, annot.symbol, description FROM genome, pep, annot, cds WHERE genome.chromid=pep.chromid AND pepid=cdsid AND annotid=pepid AND pepid ILIKE :s)
        UNION
        (SELECT pepid, pep.location, pep.sequence as seqp, cds.sequence as seqc, name, pep.chromid, annot.geneID, annot.transcript, annot.transcryptType, annot.geneType, annot.symbol, description FROM genome, pep, annot, cds WHERE genome.chromid=pep.chromid AND pepid=cdsid AND annotid=pepid AND pep.chromid ILIKE :s)
        UNION
        (SELECT pepid, pep.location, pep.sequence as seqp, cds.sequence as seqc, name, pep.chromid, annot.geneID, annot.transcript, annot.transcryptType, annot.geneType, annot.symbol, description FROM genome, pep, annot, cds WHERE genome.chromid=pep.chromid AND pepid=cdsid AND annotid=pepid AND pep.location ILIKE :s)
        UNION
        (SELECT pepid, pep.location, pep.sequence as seqp, cds.sequence as seqc, name, pep.chromid, annot.geneID, annot.transcript, annot.transcryptType, annot.geneType, annot.symbol, description FROM genome, pep, annot, cds WHERE genome.chromid=pep.chromid AND pepid=cdsid AND annotid=pepid AND geneid ILIKE :s)
        UNION
        (SELECT pepid, pep.location, pep.sequence as seqp, cds.sequence as seqc, name, pep.chromid, annot.geneID, annot.transcript, annot.transcryptType, annot.geneType, annot.symbol, description FROM genome, pep, annot, cds WHERE genome.chromid=pep.chromid AND pepid=cdsid AND annotid=pepid AND transcript ILIKE :s) 
        UNION
        (SELECT pepid, pep.location, pep.sequence as seqp, cds.sequence as seqc, name, pep.chromid, annot.geneID, annot.transcript, annot.transcryptType, annot.geneType, annot.symbol, description FROM genome, pep, annot, cds WHERE genome.chromid=pep.chromid AND pepid=cdsid AND annotid=pepid AND genetype ILIKE :s)
        UNION
        (SELECT pepid, pep.location, pep.sequence as seqp, cds.sequence as seqc, name, pep.chromid, annot.geneID, annot.transcript, annot.transcryptType, annot.geneType, annot.symbol, description FROM genome, pep, annot, cds WHERE genome.chromid=pep.chromid AND pepid=cdsid AND annotid=pepid AND transcrypttype ILIKE :s)
        UNION
        (SELECT pepid, pep.location, pep.sequence as seqp, cds.sequence as seqc, name, pep.chromid, annot.geneID, annot.transcript, annot.transcryptType, annot.geneType, annot.symbol, description FROM genome, pep, annot, cds WHERE genome.chromid=pep.chromid AND pepid=cdsid AND annotid=pepid AND symbol ILIKE :s)
        UNION
        (SELECT pepid, pep.location, pep.sequence as seqp, cds.sequence as seqc, name, pep.chromid, annot.geneID, annot.transcript, annot.transcryptType, annot.geneType, annot.symbol, description FROM genome, pep, annot, cds WHERE genome.chromid=pep.chromid AND pepid=cdsid AND annotid=pepid AND description ILIKE :s)
        ;";
        }else{
           
            $query="SELECT pep.id, name, pepid, location, pep.chromid FROM pep, genome WHERE pep.chromid=genome.chromid ORDER BY pep.id LIMIT :nbres OFFSET :startat;";
            $query2="SELECT pepid, pep.location, pep.sequence as seqp, cds.sequence as seqc, name, pep.chromid, annot.geneID, annot.transcript, annot.transcryptType, annot.geneType, annot.symbol, description FROM pep, cds, annot, genome WHERE pepid=cdsid and pepid=annotid and genome.chromid=pep.chromid;";
        }
        try{
            $stmt=$myPDO->prepare($query);
            $s2=$myPDO->prepare($query2);
            if(!empty($_GET['search'])){
                $stmt->bindParam(":s", $search, PDO::PARAM_STR);
                $s2->bindParam(":s", $search, PDO::PARAM_STR);
            }
            $stmt->bindParam(":nbres", $nbres, PDO::PARAM_INT);
            $stmt->bindParam(":startat", $startat, PDO::PARAM_INT);
            $s2->execute();
            $stmt->execute();
            $res=$stmt;
            $resList=$s2;
            $nbrow=$s2->rowCount();
            $totpage=ceil($nbrow/$nbres);
        }catch(PDOException $e){
            die($e->getMessage());
        }
    }elseif(empty($name) && empty($loc) && empty($seq)&& empty($geneid)&& empty($geneB)&& empty($des)&& empty($id)&& empty($trans)&& empty($transB)&& empty($symbole) && empty($genomeid)){
        $query="SELECT pep.id, name, pepid, location, pep.chromid FROM pep, genome WHERE genome.chromid=pep.chromid ORDER BY pep.id ORDER BY pep.id LIMIT :nbres OFFSET :startat;";
       
        try{
            $stmt=$myPDO->prepare($query);
            $stmt->bindParam(":nbres", $nbres, PDO::PARAM_INT);
            $stmt->bindParam(":startat", $startat, PDO::PARAM_INT);
            $stmt->execute();
            $res=$stmt;
        }catch(PDOException $e){
            die($e->getMessage());
        }
        //$query2="select pep.id from pep;";
        $query2="SELECT pepid, pep.location, pep.sequence as seqp, cds.sequence as seqc, name, pep.chromid, annot.geneID, annot.transcript, annot.transcryptType, annot.geneType, annot.symbol, description FROM pep, cds, genome, annot WHERE cdsid=pepid and annotid=pepid and genome.chromid=pep.chromid;";
        try{
            $stmt2=$myPDO->prepare($query2);
            $stmt2->execute();
            $resList=$stmt2;
            $nbrow=$stmt2->rowCount();
            $totpage=ceil($nbrow/$nbres);
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
        if(preg_match('/[^atgc%]/i', $seq)){//peptide
            //check if there are input parameters other than sequence
            if(!empty($search)){
                
                //query with parameter and sequence pep
            $query="(SELECT pep.id, pep.chromid, name, location, pepid FROM pep, genome, annot WHERE pep.chromid=genome.chromid AND pepid=annotid AND to_tsvector('english', pep.chromid ||' '|| name ||' '|| location ||' '||pepid||' '||geneid||' '||transcript||' '||genetype||' '||transcrypttype||' '||symbol||' '||description) @@ plainto_tsquery(:par) ORDER BY pep.id)
                    INTERSECT 
                    (SELECT pep.id, pep.chromid, name, pep.location, pepid FROM pep, genome WHERE pep.chromid=genome.chromid AND pep.sequence ILIKE :seq ORDER BY pep.id)
                    LIMIT :nbres OFFSET :startat;";
                    
            $query2="SELECT pepid, pep.location, pep.sequence as seqp, cds.sequence as seqc, name, pep.chromid, annot.geneID, annot.transcript, annot.transcryptType, annot.geneType, annot.symbol, description FROM pep, genome, annot, cds WHERE pepid=cdsid and pepid=annotid AND pep.chromid=genome.chromid AND to_tsvector('english', pep.chromid ||' '|| name ||' '|| location ||' '||pepid||' '||geneid||' '||transcript||' '||genetype||' '||transcrypttype||' '||symbol||' '||description) @@ plainto_tsquery(:par)
                    INTERSECT SELECT pepid, pep.location, pep.sequence as seqp, cds.sequence as seqc, name, pep.chromid, annot.geneID, annot.transcript, annot.transcryptType, annot.geneType, annot.symbol, description FROM pep, genome, annot, cds WHERE pepid=cdsid and pepid=annotid AND pep.chromid=genome.chromid and pep.sequence ILIKE :seq;";

            }else{
               
                //query with just the sequence pep
                $query="SELECT pep.id, pep.chromid, name, pep.location, pepid FROM pep, genome WHERE pep.chromid=genome.chromid AND pep.sequence ILIKE :seq ORDER BY pep.id LIMIT :nbres OFFSET :startat;";
                //$query2="SELECT pep.id FROM pep WHERE pep.sequence ILIKE :seq;";

                $query2="SELECT pepid, pep.location, pep.sequence as seqp, cds.sequence as seqc, name, pep.chromid, annot.geneID, annot.transcript, annot.transcryptType, annot.geneType, annot.symbol, description FROM pep, cds, annot, genome WHERE pepid=cdsid and pepid=annotid and genome.chromid=pep.chromid and pep.sequence ILIKE :seq;";

            }
        }else{//cds
            //check if there are input parameters other than sequence
            if(!empty($search)){
                echo "test";
                //query with parameters and gene sequence
            $query="(SELECT pep.id, pep.chromid, name, location, pepid FROM pep, genome, annot WHERE pepid=annotid AND pep.chromid=genome.chromid AND to_tsvector('english', pep.chromid ||' '|| name ||' '|| location ||' '|| pepid ||' '|| geneid ||' '|| transcript ||' '|| genetype ||' '|| transcrypttype ||' '|| symbol ||' '|| description) @@ plainto_tsquery(:par) ORDER BY pep.id)
                    INTERSECT 
                    (SELECT pep.id, pep.chromid, name, pep.location, pepid FROM pep, cds, genome WHERE pep.chromid=genome.chromid AND cds.sequence ILIKE :seq AND cdsid=pepid ORDER BY pep.id)
                    LIMIT :nbres OFFSET :startat ;";
            
            $query2="SELECT pepid, pep.location, pep.sequence as seqp, cds.sequence as seqc, name, pep.chromid, annot.geneID, annot.transcript, annot.transcryptType, annot.geneType, annot.symbol, description FROM pep, genome, annot, cds WHERE pepid=annotid AND pep.chromid=genome.chromid and pepid=cdsid AND to_tsvector('english', pep.chromid ||' '|| name ||' '|| pep.location ||' '||pepid||' '||geneid||' '||transcript||' '||genetype||' '||transcrypttype||' '||symbol||' '||description) @@ plainto_tsquery(:par)
                     INTERSECT SELECT pepid, pep.location, pep.sequence as seqp, cds.sequence as seqc, name, pep.chromid, annot.geneID, annot.transcript, annot.transcryptType, annot.geneType, annot.symbol, description FROM pep, cds, genome, annot WHERE pep.chromid=genome.chromid AND cds.sequence ILIKE :seq AND cdsid=pepid and pepid=annotid;";  
            
            }else{
                
                //query with the gene sequence only
                $query="SELECT pep.id, pep.chromid, name, pep.location, pepid FROM pep, cds, genome WHERE cdsid=pepid AND cds.chromid=genome.chromid AND cds.sequence ILIKE :seq ORDER BY pep.id LIMIT :nbres OFFSET :startat;";
                //query to get the number of row
   
                $query2="SELECT pepid, pep.location, pep.sequence as seqp, cds.sequence as seqc, name, pep.chromid, annot.geneID, annot.transcript, annot.transcryptType, annot.geneType, annot.symbol, description FroM cds, pep, annot, genome WHERE cdsid=pepid AND annotid=pepid AND pep.chromid=genome.chromid AND cds.sequence ILIKE :seq;";


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

            $resList=$stmt2;
            $nbrow=$stmt2->rowCount();
            $totpage=ceil($nbrow/$nbres);
        }catch(PDOException $e){
            die($e->getMessage());
        }
    }
    
return [$res, $resList];
}
//Download the only the wanted parameters from the results
function Download($list){
    header('Content-Type: text/plain');
    header("Content-disposition: attachment; filename=results.txt");
    set_time_limit(0);

    if(isset($_POST['name'])){
        print('name');
    }
    if(isset($_POST['id'])){
        print(";chromid");
    }
    if(isset($_POST['pepid'])){
        print(";pepid");
    }
    if(isset($_POST['geneid'])){
        print(";geneid");
    }
    if(isset($_POST['loc']) || isset($_POST['loca'])){
        print(";location");
    }
    if(isset($_POST['geneid'])){
        print(";geneid");
    }
    if(isset($_POST['genet'])){
        print(";genetype");
    }
    if(isset($_POST['trans'])){
        print(";transcript");
    }
    if(isset($_POST['transt'])){
        print(";transcriptType");
    }
    if(isset($_POST['sym'])){
        print(";symbol");
    }
    if(isset($_POST['des'])){
        print(";description");
    }
    if(isset($_POST['seq'])){
        print(";genome.sequence");
    }
    if(isset($_POST['seqp'])){
        print(";pep.sequence");
    }
    if(isset($_POST['seqc'])){
        print(";cds.sequence");
    }
    print("\n");

    while($row=$list->fetch()){
        
        if(isset($_POST['name'])){
            print($row['name']);
        }
        if(isset($_POST['id'])){
            print(";".$row['chromid']);
        }
        if(isset($_POST['pepid'])){
            print(";".$row['pepid']);
        }
        if(isset($_POST['loc'])){
            print(";".$row['loc']);
        }
        if(isset($_POST['loca'])){
            print(";".$row['location']);
        }
        if(isset($_POST['geneid'])){
            print(";".$row['geneid']);
        }
        if(isset($_POST['genet'])){
            print(";".$row['genetype']);
        }
        if(isset($_POST['trans'])){
            print(";".$row['transcript']);
        }
        if(isset($_POST['transt'])){
            print(";".$row['transcrypttype']);
        }
        if(isset($_POST['sym'])){
            print(";".$row['symbol']);
        }
        if(isset($_POST['des'])){
            print(";".$row['description']);
        }
        if(isset($_POST['seq'])){
            print(";".$row['sequence']);
        }
        if(isset($_POST['seqp'])){
            print(";".$row['seqp']);
        }
        if(isset($_POST['seqc'])){
            print(";".$row['seqc']);
        }
        
        print("\n");
        
    }
    exit(0);
}
$resL=search();
$res=$resL[0];
if(isset($_POST['down_btn'])){ 
   Download($resL[1]);
}
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
      <script type="text/javascript" src="assets/bootstrap.min.js"></script>

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
          $name.="(".$_SESSION['user']['userrole'].")";
          
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
        <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Download
        </a>
        <div class="dropdown-menu" style="width:200px; padding:10px" aria-labelledby="navbarDropdown">
            <h6>Select the wanted fields</h6>
            <div class="dropdown-divider"></div>
            <form action="<?php 
            if(isset($_GET['type'])){
                print($_SERVER["PHP_SELF"]."?type=".$_GET['type']."&search=".$_GET['search']);
            }else{
            echo $_SERVER["PHP_SELF"];
            }?>" method="post">
            <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" value="name" id="name" name="down">
            <label class="form-check-label" for="name">Organism Name</label>
            </div>
            <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" value="id" id="id" name="down">
            <label class="form-check-label" for="id">Chromosome ID</label>
            </div>
            <?php if($type=="genome"){ ?>
            <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" value="loc" id="loc" name="down">
            <label class="form-check-label" for="loc">Location</label>
            </div>
            <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" value="seq" id="seq" name="down">
            <label class="form-check-label" for="seq">Sequence</label>
            </div>
            <?php }else{?>
            <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" value="pepid" id="pepid" name="down">
            <label class="form-check-label" for="pepid">Peptide/CDS ID</label>
            </div>
            <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" value="loca" id="loca" name="down">
            <label class="form-check-label" for="loca">Location</label>
            </div>
            <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" value="geneid" id="geneid" name="down">
            <label class="form-check-label" for="geneid">Gene ID</label>
            </div>
            <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" value="trans" id="trans" name="down">
            <label class="form-check-label" for="trans">Transcript</label>
            </div>
            <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" value="transt" id="transt" name="down">
            <label class="form-check-label" for="transt">Transcript Type</label>
            </div>
            <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" value="genet" id="genet" name="down">
            <label class="form-check-label" for="genet">Gene Type</label>
            </div>
            <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" value="sym" id="sym" name="down">
            <label class="form-check-label" for="sym">Symbol</label>
            </div>
            <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" value="des" id="des" name="down">
            <label class="form-check-label" for="des">Description</label>
            </div>
            <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" value="seqp" id="seqp" name="down">
            <label class="form-check-label" for="seqp">Peptide Sequence</label>
            </div>
            <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" value="seqc" id="seqc" name="down">
            <label class="form-check-label" for="seqc">CDS Sequence</label>
            </div>
            <?php }?>
            <div class="dropdown-divider"></div>
            <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="select_all">
            <label class="form-check-label" for="select_all">Select All</label>
            </div>
            <input type="submit" class="btn btn-primary" value="Download" name="down_btn">
<script>
    document.getElementById('select_all').onclick = function() {
        var checkboxes = document.getElementsByName('down');
        for (var checkbox of checkboxes) {
            checkbox.checked = this.checked;
        }
    }
</script>

        </form>
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
       <div style="padding-bottom:3%">
	   <input type="submit" class="btn btn-outline-light" value="Search">
       </div>
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
<ul class="pagination pg-blue" max-size='10'>

<!--Get previous page-->
<li class="page-item"><a class="page-link" href="results.php?page=1<?php
if(isset($_GET['type'])){
    echo "&type=".$type."&search=".$_GET['search'];
}
?>">First</a></li>
<li class="page-item"><a class="page-link" href="results.php?page=<?php 
if($page>1){
    echo $page-1;
}else{
    echo $page;
}
if(isset($_GET['type'])){
    echo "&type=".$type."&search=".$_GET['search'];
}
?>">Previous</a></li>

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
            <li class="page-item"><a class="page-link" href="results.php?page=<?php echo $k;
            if(isset($_GET['type'])){
                echo "&type=".$type."&search=".$_GET['search'];
            }
            ?>"><?php echo $k;?></a></li>
            <?php   
        }
    
}
    while($i<=$totpage && $i<$page+$pagesDisplayed){
?>          
        <li class="page-item"><a class="page-link" style="<?php if($i==$page){echo "background:dodgerblue; color:white"; }?>" href="results.php?page=<?php echo $i;
        if(isset($_GET['type'])){
            echo "&type=".$type."&search=".$_GET['search'];
        }
        ?>"><?php echo $i;?></a></li>
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
if(isset($_GET['type'])){
    echo "&type=".$type."&search=".$_GET['search'];
}
?>">Next</a></li>
<li class="page-item"><a class="page-link" href="results.php?page=<?php echo $totpage;
if(isset($_GET['type'])){
    echo "&type=".$type."&search=".$_GET['search'];
}
?>">Last</a></li>

</ul>
</nav>

</body>
</html>