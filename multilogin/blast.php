<?php
if(empty($_GET['RID'])&& isset($_GET['ID'])){
    $id=$_GET['ID'];
    $cmd="curl -sL 'https://blast.ncbi.nlm.nih.gov/Blast.cgi?CMD=Put&QUERY='.$id.'&PROGRAM=blastp&FILTER=L&DATABASE=nr&FORMAT_TYPE=XML' | grep 'RID = '";
    $res=shell_exec($cmd);
    $rid=explode(' ', $res);
    $rid=$rid[count($rid)-1];
    $refresh="blast.php?RID=$rid";
    header('Location: '.$refresh);
}
if(isset($_GET['RID'])){
    //wait 60 sec before checking the resulst as per the ncbi guidelines.
    sleep(60);
    $cmdres="curl -sL 'https://blast.ncbi.nlm.nih.gov/Blast.cgi?CMD=Get&RID=VWF3JPM401N'| grep 'READY'";
    $flag=shell_exec($cmdres);

    while(empty($flag)){
        sleep(30);
        $flag=shell_exec($cmdres);
    }
    $url="https://blast.ncbi.nlm.nih.gov/Blast.cgi?CMD=Get&RID=VWF3JPM401N&VIEW_RESULTS=FromRes&FORMAT_TYPE=HTML";
    header('Location: '.$url);
}
?>
<!DOCTYPE HTML>
<html>
    <header>
        <title>
            Blast
        </title>
    </header>
    <body>
        <h4>Wait for results</h4>
    </body>
</html>