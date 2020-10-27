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
    $query="SELECT name FROM genome, pep WHERE pep.chromid=genome.chromid AND pepid=:id;";
    try{
        $stmt=$myPDO->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_STR);
        $stmt->execute();
        $res=$stmt->fetch();
    }catch(PDOException $e){
        die($e->getMessage());
    }
    return $res['name'];
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
<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
</header>
<body>
<div class="header" style="background-color:dodgerblue">
        <br>
        <a style="float:right;color:red" href="home.php?logout='1'">logout</a>
        <h2 style="color:azure">LOGO</h2>
    </div>
    <br>
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
<div class="col-12 col-md-4 col-xl-8 py-md-5 bd-content">
<?php
//check if genome
if($type=="genome"){
?>
<table class="table table-bordered table-stripped">
    <tbody>
        <tr>
            <th>Strain</th>
            <td><?php echo $res['name']; ?></td>
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
    </tbody>

</table>
<table>
    <tbody>
        <tr>
        <span style="width:200px; word-wrap:break-word; display:inline-block; font-family:monospace"> 
   <?php// echo $res['sequence'];?>
        </span>
        </tr>
    </tbody>
</table>
<?php
//end if type genome
}else{
?>
<table class="table table-bordered table-stripped">
    <tbody>
        <tr>
            <th>Strain</th>
            <td><?php  echo(getName($res['pepid'])); ?></td>
        </tr>
        <tr>    
            <th>protein ID</th>
            <td><?php echo $res['pepid'];?></td>
        </tr>
        <tr>    
            <th>Chromosome ID</th>
            <td><?php echo $res['chromid'];?></td>
        </tr>
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
        <tr>    
        <th>Peptide sequence</th>
        <td>
            <table>
            <tr>
            <span style="width:200px; word-wrap:break-word; display:inline-block; font-family:monospace"> 
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
            <span style="width:200px; word-wrap:break-word; display:inline-block; font-family:monospace"> 
            <?php echo getCDSseq($res['pepid']);?>
            </span>
            </tr>
            </table>
        </td>
        </tr>
        
    </tbody>

</table>
<?php
}//end if peptide
?>
</div>
</body>
</html>