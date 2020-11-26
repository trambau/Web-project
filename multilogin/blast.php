<?php
if(isset($_GET['NB']) && $_GET['NB']>1){
    $flag2=1;
}else{
    $flag2=0;
}
if(empty($_GET['RID'])&& isset($_GET['ID'])){
    $id=$_GET['ID'];
    $cmd="curl -sL 'https://blast.ncbi.nlm.nih.gov/Blast.cgi?CMD=Put&QUERY=$id&PROGRAM=blastp&FILTER=L&DATABASE=nr&FORMAT_TYPE=XML' | grep 'RID = '";
    //curl -sL 'https://blast.ncbi.nlm.nih.gov/Blast.cgi?CMD=Put&QUERY=AAN78603&PROGRAM=blastp&FILTER=L&DATABASE=nr&FORMAT_TYPE=XML' | grep 'RID = '
    set_time_limit(0);
    $res=shell_exec($cmd);
    $rid=explode(' ', $res);
    $rid=trim($rid[count($rid)-1]);
    $refresh="blast.php?RID=$rid&NB=1";
    header('Location: '.$refresh);
}
if($flag2==1){
    //wait 60 sec before checking the resulst as per the ncbi guidelines.
    //sleep(60);
    print($_SERVER['PHP_SELF']."?RID=".$_GET['RID']);
    $rid=$_GET['RID'];
    $cmdres="curl -sL 'https://blast.ncbi.nlm.nih.gov/Blast.cgi?CMD=Get&RID=$rid' | grep 'READY'";
    $flag=shell_exec($cmdres);
    if(!empty($flag)){
        $url="https://blast.ncbi.nlm.nih.gov/Blast.cgi?CMD=Get&RID=$rid&VIEW_RESULTS=FromRes&FORMAT_TYPE=HTML";
        header('Location: '.$url);
    }
    /*
    while(empty($flag)){
        sleep(30);
        $flag=shell_exec($cmdres);
        curl -sL 'https://blast.ncbi.nlm.nih.gov/Blast.cgi?CMD=Get&RID=VZEKRAG8013' | grep 'READY'
    }*/
    //$url="https://blast.ncbi.nlm.nih.gov/Blast.cgi?CMD=Get&RID=$rid&VIEW_RESULTS=FromRes&FORMAT_TYPE=HTML";
    //header('Location: '.$url);
    
}
?>
<!DOCTYPE html>
<html>
    <header>
        <title>
            Blast
        </title>
        <script>
           
        </script>
    </header>
   <body>
       <p>Please wait the page will redirect when the results are ready.</p>
      <script>
        
            var rid=window.location.href.split("=");
            rid=rid[1].split("&")[0];
            var count=window.location.href.split("=")[2];
            if(count==1){
                count++;
                setTimeout(function(){
                    window.location.href ='blast.php?RID='+rid+'&NB='+count;
                }, 60000);
            }else{
                alert("test");
                count++;
                setTimeout(function(){
                    window.location.href ='blast.php?RID='+rid+'&NB='+count;
                }, 30000);
            }
      </script>
   </body>
</html>