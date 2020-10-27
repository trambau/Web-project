<?php
include('functions.php');
if (!isLoggedIn()) {
    $_SESSION['msg'] = "You must log in first";
    header('location: login.php');
}

$seq_er='';
$type=$_POST['type'];
function search(){
    global $seq_er, $name, $genomeid, $loc, $seq, $geneid, $id, $trans, $des, $geneB, $transB, $def, $symbole;
    $name=trim($_POST['name']);
    $genomeid=trim($_POST['genomeID']);
    $loc=trim($_POST['location']);
    $seq=trim($_POST['sequence']);
    $geneid=trim($_POST['geneID']);
    $id=trim($_POST['id']);
    $trans=trim($_POST['trans']);
    $des=trim($_POST['description']);
    $geneB=trim($_POST['geneBiotype']);
    $transB=trim($_POST['transBiotype']);
    $def=trim($_POST['description']);
    $symbole=trim($_POST['symbole']);
    
    //check sequnece length
    if(strlen($seq) < 3  && !(empty($seq))){
        $seq_er = "The seq must have at least 3 characters.";
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
if(!empty($res)){
    header('location: results.php');
}
?>
<!DOCTYPE html>
<html>
<header>
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style>
    .wrapper{ width: 350px; padding: 20px; }
    </style>
    <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/base/minified/jquery-ui.min.css" type="text/css" /> 
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
    <script type="text/javascript" src="http://code.jquery.com/ui/1.10.1/jquery-ui.min.js"></script>  
</header>
<title>Search</title>
<body>
<div class="header" style="background-color:dodgerblue">
        <br>
        <a style="float:right;color:red" href="home.php?logout='1'">logout</a>
        <h2 style="color:azure">LOGO</h2>
    </div>
<script>
function yesnoCheck(that) {
    if (that.value == "other") {
  //alert("check");
        document.getElementById("ifYes").style.display = "block";
    } else {
        document.getElementById("ifYes").style.display = "none";
    }
}
</script>
<div class="wrapper">
<h2>Search complete  genome or peptides/genes</h2>
<form action="<?php $_SERVER['PHP_SELF'];?>" method="post">
    <div class="form-group">
        <label>Name:</label> 
        <input type="text" value="<?php echo $name;?>" class="form-control" name="name" id="name" placeholder="Esche...">
    </div>
    <script type="text/javascript">
$(function() {
    //autocomplete
    $("#name").autocomplete({
        source: "autocomplete.php",
        minLength: 1
    });                

});
</script>

    <div class="form-group">
        <label>ID genome:</label> 
        <input type="text" value="<?php echo $genomeid;?>" class="form-control" name="genomeID" placeholder="ASM...">
    </div>
    <div class="form-group">
        <label>Location:</label> 
        <input type="text" value="<?php echo $loc;?>" name="location" placeholder="1:546..." class="form-control">
    </div>
    <div class="form-group <?php echo (!empty($seq_er)) ? 'has-error' : ''; ?>">
        <label>Sequence</label>
        <textarea name="sequence" class="form-control" placeholder="AGCTTTT..."><?php echo $seq;?></textarea>
        <span class="help-block"><?php echo $seq_er; ?></span>
    </div>
    <label>Output type</label>
    <select name="type" class="form-control mx-sm-3" onchange="yesnoCheck(this);">
        <option selected="selected" value="genome" name="genome">Genome</option>
        <option value="other" name="other">Gene/peptide</option>
    
    </select>

    <div id="ifYes" style="display: none;">
        <div class="form-group">
            <label>Gene Id</label>
            <input type="text" name="geneID" value="<?php echo $gene;?>" class="form-control" placeholder="ex:c5491">
        </div>
        <div class="form-group">
            <label>ID</label> 
            <input type="text" name="id" value="<?php echo $id;?>" class="form-control" placeholder="ex:AAN78501">
        </div>
        <div class="form-group">
            <label>Gene biotype</label> 
            <input type="text" name="geneBiotype" value="<?php echo $geneB;?>" class="form-control" placeholder="ex:protein_coding">
        </div>
        <div class="form-group">
            <label>Transcript</label> 
            <input type="text" name="trans" value="<?php echo $trans;?>" class="form-control" placeholder="ex:AAN78501">
        </div>
        <div class="form-group">
            <label>Transcript biotype</label> 
            <input type="text" name="transBioType" value="<?php echo $transB;?>" class="form-control" placeholder="ex:protein_coding">
        </div>
        <div class="form-group">
            <label>Symbole</label> 
            <input type="text" name="symbole" value="<?php echo $symbole;?>" class="form-control" placeholder="ex:THR...">
        </div>
        <div class="form-group">
            <label>Description</label> 
            <input type="text" name="description" value="<?php echo $des;?>" class="form-control" placeholder="ex:Hypothetical protein">
        </div>
    </div>
    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Search" name="search_btn">
        <input type="reset" class="btn btn-default" value="Reset">
    </div>

</form>
</div>

</body>
</html>