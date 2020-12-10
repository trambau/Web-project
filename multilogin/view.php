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
//get the organism name from a peptide
function getName($id){
    global $myPDO;
    $query="SELECT name, genome.id FROM genome, pep WHERE pep.chromid=genome.chromid AND pepid=:id;";
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
//return the genes sequence
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
<title>View</title>
<header>
<link rel="stylesheet" href="assets/bootstrap.css"> 
<script type="text/javascript" src="assets/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="./assets/Scribl.1.1.4.min.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/dragscrollable.js"></script>
<link rel="stylesheet" id="themeCSS" href="http://chmille4.github.com/Scribl/css/iThing.css"> 
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
<script src="http://static.tumblr.com/fcdode8/Giymeu17u/jquery.mousewheel.min.js"></script>
<script src="http://static.tumblr.com/fcdode8/WVbmeu18t/jqallrangesliders-min.js"></script>

<style>
		   #scribl-zoom-slider {
		      width: 15px;
		   }
</style>
</header>
<?php
//check if genome
if($type=="genome"){
?>
<body onload="draw('canvas')">
<?php
}else{
?>
<body>
<?php } ?>
<!-------------------------------------------------------------------------TOPNAV-------------------------------------------------------------------------------------->
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
       <div style="padding-bottom:14px"> 
	   <input type="submit" class="btn btn-outline-light" value="Search">
       </div>
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
        //hide genome info/show sequence
        function showSeq(){
            var tab=document.getElementById("view");
            var seqV=document.getElementById("seqView");
            var btn=document.getElementsByName("view_btn");
            if(tab.style.display=="none"){
                tab.style.display="block";
                seqV.style.display="none";
                btn.value="tet"
            }else{
                tab.style.display="none";
                seqV.style.display="block";
                btn.value="Show Info"
            }
        }
    </script>
  


<?php
//check if genome
if($type=="genome"){
    //select all the  genes from the genome
    $sql="SELECT DISTINCT location, cdsid, cds.id as cid FROM cds, genome WHERE cds.chromid=:id;";
    try{
        $stmt=$myPDO->prepare($sql);
        $stmt->bindParam(":id", $res['chromid'], PDO::PARAM_INT);
        $stmt->execute();
        $genes=$stmt;
        $nbgenes=$stmt->rowCount();
    }catch(PDOException $e){
        die($e->getMessage());
    }
    $data=$genes->fetchAll();
    //put the results in json for javascript use
    $json=json_encode($data, JSON_PRETTY_PRINT);
?>
<div style="margin-left:auto; padding-left:20px; padding-bottom:10px">
<input type="button" class="btn btn-outline-dark btn-xs" value="Show seq/info" id="btn_view" name="view_btn" onclick="showSeq()">
</div>
<div id="seqView" style="display:none">

            <h4>Sequence</h4>
                <table>
                <tr>
                <span style="width:400px; word-wrap:break-word; display:inline-block; font-family:monospace"> 
                <?php 
                
                echo $res['sequence'];
                
                ?>
                </span>
                </tr>
                </table>
            

</div>
<div id="view">
<div class="row">
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
            <td><?php echo $nbgenes;?>
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
</div><!--div row-->
<!----------------------------------------------------------------------GENOME VIEW ---------------------------------------------------------------------------->
<script>
    var res=<?php echo $json;?>;
    //create the chart containing the genes
    function draw(canvasName) {  
				// Get Canvas and Create Chart
			  	var canvas = document.getElementById(canvasName);  	
				// Create Chart
                chart = new Scribl(canvas, 10000);
                //adjust the sizes
                chart.laneSizes=70;
                chart.laneBuffer=10;
                chart.trackBuffer=1;
                chart.trackSizes=5;
                chart.scale.auto=false;

                track=chart.addTrack().addLane();
                track2=chart.addTrack().addLane();
                var flag=0
                //add each genes from the genome on a track
                res.forEach(row => {
                   
                    //get the location, size and orientation of the genes
                    var loc=row['location'].split(" ");
                    var size=parseInt(loc[1])-parseInt(loc[0])+1;
                    if(loc[2]=="1"){
                        var or="+";
                    }else{
                        var or="-";
                    }
                    if(flag!=0 && parseInt(loc[0])<start){
                        gene=track2.addFeature( new BlockArrow("track", parseInt(loc[0]), size, or));
                    }else{
                        gene=track.addFeature( new BlockArrow("track", parseInt(loc[0]), size, or));
                    }
                    start=parseInt(loc[0]);
                    gene.name=row['cdsid'];
                    gene.onMouseover = row['cdsid'];
                    gene.onClick="./view.php?id="+row['cid']+"&type=pep";
                    flag=1;
                });
                
               //call the redraw
                chart.redraw(0, 200000);
        }
        //redraw the chart with the new value from the slider
        function redraw(min, max){
            //redraw the chart on call
            chart.scale.min=min;
            chart.scale.max=max;
            chart.redraw();
        }
        
</script>
<div class="row"><h4 style="margin-left:auto; margin-right:auto; padding-bottom:20px">Genomes Visualization</h4></div>
<div class="row">
    <!--Canvas with the genes-->
    <canvas id="canvas" style=" margin-left:auto; margin-right:auto;" width="1000px" height="250"></canvas>
    <!--Slider to browse the genome-->
    <div id="slider" style="width:1230px;margin-left:auto;margin-right:auto"></div>
</div>

</div><!-- div all ---->
<script>
      // initialize slider
      $("#slider").editRangeSlider({ bounds:{min: 1, max: 6000000}, defaultValues:{min: 100000, max: 200000}, wheelMode: "zoom" });

      // make slider redraw Scribl chart with every change
      $("#slider").on("valuesChanging", function(e, data){
        redraw(data.values.min, data.values.max);
      });

      // handle the changing of the edit boxes as well
      $("#slider").on("valuesChanged", function(e, data){
        redraw(data.values.min, data.values.max);
      });
    </script>
<?php
//end if type genome
}else{//-----------------------------------------------------------------------PEPTIDE----------------------------------------------------------------------------------------
?>
<div class="row">
<div class="col">
<div class="col-12 col-md-4 col-xl-15 py-md-15 bd-content">
<table class="table table-bordered table-hover">
    <tbody>
        <tr>
            <th>Strain</th>
            <td style="cursor:pointer" onclick="location.href='view.php?id=<?php echo(getName($res['pepid'])['id']);?>&type=genome'"><u style="color:darkblue"><?php  echo(getName($res['pepid'])['name']); ?></u></td>
        </tr>
        <tr>    
            <th>protein ID</th>
            <td><?php echo $res['pepid'];?></td>
        </tr>
        <tr>    
            <th>Chromosome ID</th>
            <td style="cursor:pointer" onclick="location.href='view.php?id=<?php echo(getName($res['pepid'])['id']);?>&type=genome'"><u style="color:darkblue"><?php echo $res['chromid'];?></u></td>
        </tr>
        
        <?php 
        //check if the annotations are valid.
        if($res['validated']==1){?>
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
<!-- Access the protein database with the current protein-->
<tr><td><a href="https://www.ncbi.nlm.nih.gov/protein/<?php echo $res['pepid'];?>">Protein DB</a></td></tr>
<!-- Access genome database with the current genome -->
<tr><td><a href="https://www.ncbi.nlm.nih.gov/genome/?term=<?php echo $res['chromid'];?>">Genome DB</a></td></tr>
</table>
<table class="col-md-25">
    <tr><th>Protein Alignment</th></tr>
    <tr><td><a href="./blast.php?ID=<?php echo $res['pepid'];?>">Blast</a></td></tr>
</table>
</div>

</div><!--div row-->
<?php
}//end if peptide
?>

</div>
</body>
</html>
