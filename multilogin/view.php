<?php
include('functions.php');

if(isset($_GET['id']) && isset($_GET['type'])){
    global $myPDO;
    $id=intval($_GET['id']);
    $type=trim($_GET['type']);
    
    if($type=="genome"){
        $query="SELECT * FROM genome WHERE id=:id;";
    }else{
        $query="SELECT * FROM pep, annot WHERE pep.id=:id AND pep.id=annot.id;";
    }
    try{
        $stmt=$myPDO->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $res=$stmt->fetch();
    }catch(PDOException $e){
        die($e->getMessage());
    }
}

function getName($id){
    global $myPDO;
    $query="SELECT name, genome.id, isannotated FROM genome, pep WHERE pep.chromid=genome.chromid AND pepid=:id;";
    try{
        $stmt=$myPDO->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_STR);
        $stmt->execute();
        $res=$stmt->fetch();
    }catch(PDOException $e){
        die($e->getMessage());
    }
    return $res;
}
function getCDSseq($id){
    global $myPDO;
    $query="SELECT sequence FROM cds WHERE cdsid=:id;";
    try{
        $stmt=$myPDO->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_STR);
        $stmt->execute();
        $res=$stmt->fetch();
    }catch(PDOException $e){
        die($e->getMessage());
    }
    return $res['sequence'];
}
?>
<!DOCTYPE html>
<html>
<title>
View
</title>
<header>
<!--<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">-->
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
          $nam=$_SESSION['user']['firstname'];
          $nam.="(".$_SESSION['user']['userrole'].")";
          echo $nam;?></p>

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
    <script>
        function addNewlines(str) {
            var result = '';
            while (str.length > 0) {
                result += str.substring(0, 200) + '\n';
                str = str.substring(200);
            }
            return result;
        }
    </script>
    <div class="row">


<?php
//check if genome
if($type=="genome"){
?>
<div class="col">
<div class="col-12 col-md-4 col-xl-15 py-md-15 bd-content">
<table class="table table-bordered table-stripped">
    <tbody>
        <tr>
            <th>Strain</th>
            <td><?php echo $res['name']; ?></td>
        </tr>
        <tr>
            <th>Chromosome ID</th>
            <td><?php echo $res['chromid'];?></td>
        </tr>
        <tr>    
            <th>Number of genes</th>
            <td><?php
            $query="select count(cdsid) from cds, genome where genome.chromid=cds.chromid and genome.id=:id;";
            try{
                $stmt=$myPDO->prepare($query);
                $stmt->bindParam(":id", $res['id'], PDO::PARAM_INT);
                $stmt->execute();
                echo($stmt->fetch()['count']);
            }catch(PDOException $e){
                die($e->getMessage());
            }
            ?>
            </td>
        </tr>
        <tr>
            <th>Sequence</th>
            <td>
                <table>
                <tr>
                <span style="width:400px; word-wrap:break-word; display:inline-block; font-family:monospace"> 
                <?php 
                
                echo $res['sequence'];
                
                ?>
                </span>
                </tr>
                </table>
            </td>
        </tr>
    </tbody>

</table>
</div>
</div>
<div class="col" style="float:right">
<table class="col-md-25">
<tr><th>Access external ressources</th></tr>
<tr><td><a href="https://www.ncbi.nlm.nih.gov/genome/?term=<?php echo $res['chromid'];?>">Genome DB</a></td></tr>
</table>
</div>


<?php
//end if type genome
}else{//-----------------PEPTIDE---------------
?>

<div class="col">
<div class="col-12 col-md-4 col-xl-15 py-md-15 bd-content">
<table class="table table-bordered table-hover">
    <tbody>
        <tr>
            <th>Strain</th>
            <td onclick="location.href='view.php?id=<?php echo(getName($res['pepid'])['id']);?>&type=genome'"><u style="color:darkblue"><?php  echo(getName($res['pepid'])['name']); ?></u></td>
        </tr>
        <tr>    
            <th>protein ID</th>
            <td><?php echo $res['pepid'];?></td>
        </tr>
        <tr>    
            <th>Chromosome ID</th>
            <td onclick="location.href='view.php?id=<?php echo(getName($res['pepid'])['id']);?>&type=genome'"><u style="color:darkblue"><?php echo $res['chromid'];?></u></td>
        </tr>
        
        <?php 
        //check if the annotations are valid.
        if(getName($res['pepid'])['isannotated']==1){?>
        <tr>    
            <th>Transcript</th>
            <td><?php echo $res['transcript'];?></td>
        </tr>
        <tr>    
            <th>gene</th>
            <td><?php echo $res['geneid'];?></td>
        </tr>
        <tr>    
            <th>Gene Biotype</th>
            <td><?php echo $res['genetype'];?></td>
        </tr>
        <tr>    
            <th>Transcript Biotype</th>
            <td><?php echo $res['transcrypttype'];?></td>
        </tr>
        <tr>    
            <th>Symbole</th>
            <td><?php echo $res['symbol'];?></td>
        </tr>
        <tr>    
            <th>Description</th>
            <td><?php echo $res['description'];?></td>
        </tr>
        <?php
        //end if annotaions valid.
        }
        ?>
        <tr>    
        <th>Peptide sequence</th>
        <td>
            <table>
            <tr>
            <span style="width:300px; word-wrap:break-word; display:inline-block; font-family:monospace"> 
            <?php echo $res['sequence'];?>
            </span>
            </tr>
            </table>
        </td>
        </tr>
        <tr>    
        <th>CDS sequence</th>
        <td>
            <table>
            <tr>
            <span style="width:300px; word-wrap:break-word; display:inline-block; font-family:monospace"> 
            <?php echo getCDSseq($res['pepid']);?>
            </span>
            </tr>
            </table>
        </td>
        </tr>
        
    </tbody>

</table>
</div>
</div>
<div class="col" style="float:right">
<table class="col-md-25">
<tr><th>Access external ressources</th></tr>
<tr><td><a href="https://www.ncbi.nlm.nih.gov/protein/<?php echo $res['pepid'];?>">Protein DB</a></td></tr>

<tr><td><a href="https://www.ncbi.nlm.nih.gov/genome/?term=<?php echo $res['chromid'];?>">Genome DB</a></td></tr>
</table>
</div>


<?php
}//end if peptide
?>
</div><!--div row-->
</div>
</body>
</html>