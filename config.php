<?php
/*
file to connect to the database
*/
    $db="db";
    $user='postgres';
    $dbpswd='postgres';
    
    try{
        $myPDO=new PDO("pgsql:host=localhost;dbname=$db", $user, $dbpswd);
        echo "connected";
        
        $myPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $myPDO->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        /* --fonctionne
        $myPDO->exec("
        INSERT INTO users
        VALUES (
            $email, $pswd, $phone);
    ");*/
    }
    catch(PDOException $e){     
        die("DB ERROR: " . $e->getMessage());
    }

    /*
    --executer depuis fichier sql
    $query_file = 'sql_query.txt';
   
    $fp = fopen($query_file, 'r');
    $sql = fread($fp, filesize($query_file));
    fclose($fp); */ 
?>
