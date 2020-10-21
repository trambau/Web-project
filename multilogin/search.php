<?php
session_start();
// connect to database
try{
    $datab="db";
    $user="postgres";
    $dbpswd="postgres";
    $myPDO=new PDO("pgsql:host=localhost;dbname=$datab", $user, $dbpswd);
    $myPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $myPDO->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
}
catch(PDOException $e){     
    die("DB ERROR: " . $e->getMessage());
}

if (isset($_POST['search_btn'])) {
	search();
}
function search(){
    
}
?>
<!DOCTYPE html>
<html>
<header>
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style>
    .wrapper{ width: 350px; padding: 20px; }
    </style>
</header>
<title>Search</title>
<body>
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
<form action="<?php $_SERVER['PHP_SELF'];?>" method="post">
    <div class="form-group">
        <label>ID genome:</label> 
        <input type="text" value="" class="form-control" name="genomeID" placeholder="ASM...">
    </div>
    <div class="form-group">
        <label>Location:</label> 
        <input type="text" value="" name="location" placeholder="1:546..." class="form-control">
    </div>
    <div class="form-group">
        <label>Sequence</label>
        <textarea name="sequence" class="form-control" placeholder="AGCTTTT..."></textarea>
    </div>
    <label>Output type</label>
    <select class="form-control mx-sm-3" onchange="yesnoCheck(this);">
        <option selected="selected" value="genome" name="genome">Genome</option>
        <option value="other" name="other">Gene/peptide</option>
    
    </select>

    <div id="ifYes" style="display: none;">
        <div class="form-group">
            <label>Gene Id</label>
            <input type="text" name="geneID" class="form-control" placeholder="ex:c5491">
        </div>
        <div class="form-group">
            <label>ID</label> 
            <input type="text" name="id" value="" class="form-control" placeholder="ex:AAN78501">
        </div>
        <div class="form-group">
            <label>Gene biotype</label> 
            <input type="text" name="genebiotype" value="" class="form-control" placeholder="ex:protein_coding">
        </div>
        <div class="form-group">
            <label>Transcript biotype</label> 
            <input type="text" name="transBioType" value="" class="form-control" placeholder="ex:protein_coding">
        </div>
        <div class="form-group">
            <label>Description</label> 
            <input type="text" name="description" value="" class="form-control" placeholder="ex:Hypothetical protein">
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