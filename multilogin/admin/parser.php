
<?php include('../functions.php') ?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
<tile style="">Add data</tile>
</head>
<body>
    <form action="<?php $_SERVER['PHP_SELF'];?>" method="post">
    Enter the absolute path of the directory with the fasta files.<br>
    <input type="text" value="<?php echo $fileDir;?>" placeholder="file location" value="<?php echo $loc?>" name="file">
    <input type="submit" value="Submit" class="btn btn-primary" name="addFile_btn">
    <input type="reset" value="Reset" class="btn btn-default">
	<input type="button" onclick="location.href='./home.php';" value="Back" class="btn btn-default"/>
    </form>

    <?php if (isset($_SESSION['addSuccess'])) : ?>
			<div class="error success" >
				<h3>
					<?php 
						echo $_SESSION['addSuccess']; 
						unset($_SESSION['addSuccess']);
					?>
				</h3>
			</div>
		<?php endif ?>
</body>
</html>
<?php
/*
//so the scrip is fully executed.
set_time_limit(0);
$fileDir=$_POST['file'];
clearstatcache();
//browse all the files .fa
$files=glob($fileDir."*.fa");
//$fasta_file=$_POST['file'];

$seq="";
$flag;//indicate if the file is for a peptide, cds or genome
//connect to the db
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
//loop on every .fa file in the directory

foreach ($files as $file) {
    $lines=file($file) or die("unable to open it");
//loop on every line
    
    foreach ($lines as $line) {
        if(substr($line, 0,1)==">"){

            $array=preg_split('/[\s:]+/',$line);
            $array[0]=substr($array[0], 1);
            //value creation for gene table
            if ($array[1]=="dna") {
                //add the full sequence to the last row
                if(!empty($seq)){
                    $query="UPDATE genome SET sequence=:seq WHERE id=:id;";
                    try{
                        $stmt=$myPDO->prepare($query);
                        $stmt->bindParam(":seq", $seq, PDO::PARAM_STR);
                        $stmt->bindParam(":id", $dbID, PDO::PARAM_INT);
                        $stmt->execute();
                    }catch(Exception $e){
                        die($e->getMessage());
                    }
                }

                $flag=0;
                $id=$location="";
                $id=$array[4];
                $location=$array[6]. ":" .$array[7].":".$array[8];

                //value insertion
                $query="INSERT INTO genome VALUES (DEFAULT, :id, :loc, :seq);";
                try{
                    $stmt=$myPDO->prepare($query);
                    $stmt->bindParam(":id", $id, PDO::PARAM_STR);
                    $stmt->bindParam(":loc", $location, PDO::PARAM_STR);
                    $stmt->bindParam(":seq", $seq, PDO::PARAM_STR);
                    $stmt->execute();
                    $dbID=$myPDO->lastInsertId();
                }catch(Exception $e){
                    die($e->getMessage());
                }



            //value for pep table
            }elseif ($array[1]=="pep") {
                //add the full sequence to the last row
                if(!empty($seq)){
                    $query="UPDATE pep SET sequence=:seq WHERE id=:id;";
                    try{
                        $stmt=$myPDO->prepare($query);
                        $stmt->bindParam(":seq", $seq, PDO::PARAM_STR);
                        $stmt->bindParam(":id", $dbID, PDO::PARAM_INT);
                        $stmt->execute();
                    }catch(Exception $e){
                        die($e->getMessage());
                    }
                }
                $pepId=$array[0];
                $flag=1;
                $id=$location=$geneId=$transcript=$geneType=$transType=$sym=$description=$seq="";
                for($i=0; $i<count($array); $i++) {
                    if($array[$i]=="chromosome"){
                        $id=$array[$i+1];
                        $i++;
                    }elseif($array[$i]=="Chromosome"){
                        $location=$array[$i+1].":".$array[$i+2].":".$array[$i+3];
                        $i+=3;
                    }elseif ($array[$i]=="gene") {
                        $geneId=$array[$i+1];
                        $i++;
                    }elseif($array[$i]=="transcript"){
                        $transcript=$array[$i+1];
                        $i++;
                    }elseif($array[$i]=="gene_biotype"){
                        $geneType=$array[$i+1];
                        $i++;
                    }elseif ($array[$i]=="transcript_biotype") {
                        $transType=$array[$i+1];
                        $i++;
                    }elseif($array[$i]=="gene_symbol"){
                        $sym=$array[$i+1];
                        $i++;
                    }elseif ($array[$i]=="description") {
                        for($j=$i+1; $j<count($array); $j++){
                            $description.=" ".$array[$j];
                        }
                        $i=count($array);
                    }
                }

                //value insertion
                $query="INSERT INTO pep VALUES (DEFAULT, :pepId, :sid, :loc, :geneId, :transcript, :geneType, :transType, :sym, :des, :seq);";
                try{
                    $stmt=$myPDO->prepare($query);
                    $stmt->bindParam(":pepId", $pepId, PDO::PARAM_STR);
                    $stmt->bindParam(":sid", $id, PDO::PARAM_STR);
                    $stmt->bindParam(":loc", $location, PDO::PARAM_STR);
                    $stmt->bindParam(":geneId", $geneId, PDO::PARAM_STR);
                    $stmt->bindParam(":transcript", $transcript, PDO::PARAM_STR);
                    $stmt->bindParam(":geneType", $geneType, PDO::PARAM_STR);
                    $stmt->bindParam(":transType", $transType, PDO::PARAM_STR);
                    $stmt->bindParam(":sym", $sym, PDO::PARAM_STR);
                    $stmt->bindParam(":des", $description, PDO::PARAM_STR);
                    $stmt->bindParam(":seq", $seq, PDO::PARAM_STR);
                    $stmt->execute();
                    $dbID=$myPDO->lastInsertId();
                }catch(Exception $e){
                    die($e->getMessage());
                }
            //value for cds table
            }else{

                //add the full sequence to the last row
                if(!empty($seq)){
                    $query="UPDATE cds SET sequence=:seq WHERE id=:id;";
                    try{
                        $stmt=$myPDO->prepare($query);
                        $stmt->bindParam(":seq", $seq, PDO::PARAM_STR);
                        $stmt->bindParam(":id", $dbID, PDO::PARAM_INT);
                        $stmt->execute();
                    }catch(Exception $e){
                        die($e->getMessage());
                    }
                }
                $cdsId=$array[0];
                $flag=2;
                $id=$location=$geneId=$geneType=$transType=$sym=$description=$seq="";
                for($i=0; $i<count($array); $i++) {
                    if($array[$i]=="chromosome"){
                        $id=$array[$i+1];
                        $i++;
                    }elseif($array[$i]=="Chromosome"){
                        $location=$array[$i+1].":".$array[$i+2].":".$array[$i+3];
                        $i+=3;
                    }elseif ($array[$i]=="gene") {
                        $geneId=$array[$i+1];
                        $i++;
                    }elseif($array[$i]=="gene_biotype"){
                        $geneType=$array[$i+1];
                        $i++;
                    }elseif ($array[$i]=="transcript_biotype") {
                        $transType=$array[$i+1];
                        $i++;
                    }elseif($array[$i]=="gene_symbol"){
                        $sym=$array[$i+1];
                        $i++;
                    }elseif ($array[$i]=="description") {
                        for($j=$i+1; $j<count($array); $j++){
                            $description.=" ".$array[$j];
                        }
                        $i=count($array);
                    }
                }

                //value insertion
                $query="INSERT INTO cds VALUES (DEFAULT, :cdsId, :sid, :loc, :geneId, :geneType, :transType, :sym, :des, :seq);";
                try{
                    $stmt=$myPDO->prepare($query);
                    $stmt->bindParam(":cdsId", $cdsId, PDO::PARAM_STR);
                    $stmt->bindParam(":sid", $id, PDO::PARAM_STR);
                    $stmt->bindParam(":loc", $location, PDO::PARAM_STR);
                    $stmt->bindParam(":geneId", $geneId, PDO::PARAM_STR);
                    $stmt->bindParam(":geneType", $geneType, PDO::PARAM_STR);
                    $stmt->bindParam(":transType", $transType, PDO::PARAM_STR);
                    $stmt->bindParam(":sym", $sym, PDO::PARAM_STR);
                    $stmt->bindParam(":des", $description, PDO::PARAM_STR);
                    $stmt->bindParam(":seq", $seq, PDO::PARAM_STR);
                    $stmt->execute();
                    $dbID=$myPDO->lastInsertId();
                }catch(Exception $e){
                    die($e->getMessage());
                }

            }
            $seq="";
        //if the ligne doesn't start with > the it goes into the sequence.
        }else{
            $seq=$seq.trim($line);
        }    
    }
    //update the last sequence in the last row inserted
    //check with flag if the table is genome, cds or pep
    if($flag==0){
        $query="UPDATE genome SET sequence=:seq WHERE id=:id;";
        try{
            $stmt=$myPDO->prepare($query);
            $stmt->bindParam(":seq", $seq, PDO::PARAM_STR);
            $stmt->bindParam(":id", $dbID, PDO::PARAM_INT);
            $stmt->execute();
        }catch(Exception $e){
            die($e->getMessage());
        }
    }elseif($flag==1){
        $query="UPDATE pep SET sequence=:seq WHERE id=:id;";
        try{
            $stmt=$myPDO->prepare($query);
            $stmt->bindParam(":seq", $seq, PDO::PARAM_STR);
            $stmt->bindParam(":id", $dbID, PDO::PARAM_INT);
            $stmt->execute();
        }catch(Exception $e){
            die($e->getMessage());
        }
    }else{
        $query="UPDATE cds SET sequence=:seq WHERE id=:id;";
        try{
            $stmt=$myPDO->prepare($query);
            $stmt->bindParam(":seq", $seq, PDO::PARAM_STR);
            $stmt->bindParam(":id", $dbID, PDO::PARAM_INT);
            $stmt->execute();
        }catch(Exception $e){
            die($e->getMessage());
        }
    }
    //apache_reset_timeout();
}*/
?>
