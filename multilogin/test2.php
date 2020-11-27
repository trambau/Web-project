<?php
phpinfo();

$mess="test";
$sub="TEST";
$to="ramelapierre@gmail.com";
$headers = 'From: no-reply@annotations.cf';
mail("ramelapierre@gmail.com", "Test sendmail", "Final test", $headers);

echo $flag;

?>
