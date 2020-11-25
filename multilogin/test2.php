<?php
#$cmd="curl -sL 'https://blast.ncbi.nlm.nih.gov/Blast.cgi?CMD=Put&QUERY=SSWWAHVEMGPPDPILGVTEAYKRDTNSKK&PROGRAM=blastp&FILTER=L&DATABASE=nr&FORMAT_TYPE=XML' | grep 'RID = '";
#$res=shell_exec($cmd);
#$rid=explode(' ', $res)[6];
#echo $rid;
//$cm="curl -sL 'https://blast.ncbi.nlm.nih.gov/Blast.cgi?CMD=Get&RID=VWF3JPM401N'| grep 'READY'";
//$c="curl -sL 'https://blast.ncbi.nlm.nih.gov/Blast.cgi?CMD=Get&RID=VWF3JPM401N&VIEW_RESULTS=FromRes&FORMAT_TYPE=Text&ALIGNMENT_VIEW=Tabular'"
$c="curl -sL 'https://blast.ncbi.nlm.nih.gov/Blast.cgi?CMD=Get&RID=VWF3JPM401N&VIEW_RESULTS=FromRes&FORMAT_TYPE=HTML'";
$re=shell_exec($c);
?>
<!DOCTYPE html>
<html lang="en">
  <header>
    <title>
      Blast results
    </title>
  </header>
  <body>
    <?php echo $re;?>
  </body>
</html>
