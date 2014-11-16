<?php    
    /** DB接続設定(PDOを利用) */
    $dsn = 'mysql:dbname=INVERTED_INDEX;host=localhost;charset=utf8';
    $user = 'root';
    $password = '';
    $options = array(
        PDO::MYSQL_ATTR_READ_DEFAULT_FILE => '/etc/my.cnf',
    );

    $dbh = new PDO($dsn, $user, $password, $options);

    $mecab = new MeCab_Tagger();
    $searchTerm = rtrim(htmlspecialchars($_GET['term']));

    if( !empty($searchTerm) ){
        $existCheckStmt = $dbh->prepare("SELECT POSTINGS FROM tokens WHERE TOKEN = '". $searchTerm. "'");
        $existCheckStmt->execute();
        $existCheckResult = $existCheckStmt->fetchAll();

        if( empty($existCheckResult) ){
        }else{
            $getDocStmt = $dbh->prepare("SELECT BODY FROM documents WHERE ID IN (". $existCheckResult[0]["POSTINGS"]. ")");
            $getDocStmt->execute();
            $searchResult = $getDocStmt->fetchAll();

            var_dump($searchResult);
        }
    }else{
        echo "処理を中断します。<br/>";
    }
?>
