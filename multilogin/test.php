<?php
$test="aatgtG%";
$pep="mtadttgc";
$p="MMMmmmmM";
$a="AAA";
var_dump(preg_match('/[^atgc%]/i', $test));
var_dump(preg_match('/[^atgc%]/i', $pep));
?>