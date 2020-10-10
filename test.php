<html>
<title>
</title>
<head>
</head>
<body>
<?php
?>
    <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="get">
    email:
    <input type="text" name="email">
    pswd:
    <input type="text" name="pswd">
    #phone
    <input type="text" name="phone">
    <input type="submit" value="Create">
    </form>
</body>
<?php
    class TableRows extends RecursiveIteratorIterator {
        function __construct($it) {
            parent::__construct($it, self::LEAVES_ONLY);
        }

        function current() {
            return "<td style='width: 150px; border: 1px solid black;'>" . parent::current(). "</td>";
        }

        function beginChildren() {
            echo "<tr>";
        }

        function endChildren() {
            echo "</tr>" . "\n";
        }
    } 
    $email=$_GET["email"];
    $pswd=$_GET["pswd"];
    $phone=$_GET["phone"];
    $db="sample";
    $user='postgres';
    $dbpswd='postgres';
    
    
    try{
        $myPDO=new PDO("pgsql:host=localhost;dbname=$db", $user, $dbpswd);
        echo "connected";
        
        $myPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        /* --fonctionne
        $myPDO->exec("
        INSERT INTO users
        VALUES (
            $email, $pswd, $phone);
    ");*/
        $test=$myPDO->prepare("
        SELECT email
        FROM users;
    ");
    echo "<br>";
        $test->execute();

    
     // set the resulting array to associative
     $result = $test->setFetchMode(PDO::FETCH_ASSOC);

     foreach(new TableRows(new RecursiveArrayIterator($test->fetchAll())) as $k=>$v) {
         echo $v;
     }
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
</html>
