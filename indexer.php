<!DOCTYPE HTML>

<html>
    <head>
        <meta charset="UTF-8">
        <title>転置インデックス作成</title>
    </head>
    <body>

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
        $textTitle = "";
        $textInput = rtrim(htmlspecialchars($_POST['doc']));

        if( !empty($textInput) ){

            // documentのDB登録
            $registDocSql = 'insert into documents (TITLE, BODY) values (?, ?)';
            $registStmt = $dbh->prepare($registDocSql);
            $registStmt->execute(array($textTitle, $textInput));
            // 登録したdocumentIDの取得
            $documentID = $dbh->lastInsertId();

            // Mecabでの形態素解析処理start

            echo "[形態素に分解した結果]<br/>";

            // 転置インデックス用の連想配列
            // array(
            //      key(インデックス) => value(ポスティングリスト(出現するドキュメントID)
            //      ....
            // )
            $newInvertedIndex = array();
            // Mecabでの解析結果を改行コードで分割
            $resultSet  = explode( "\n" , $mecab->parse( $textInput ) );

            foreach( $resultSet as $eachResult ){
                if( substr( $eachResult , 0 , 3 ) !== 'EOS' ){
                    list( $eachMorpheme , $eachInfo ) = explode( "\t" , $eachResult );                    
                    echo $eachMorpheme. "<br/>";

                    $existCheckStmt = $dbh->prepare("SELECT * FROM tokens WHERE TOKEN = '". $eachMorpheme. "'");
                    $existCheckStmt->execute();
                    $existCheckResult = $existCheckStmt->fetchAll();

                    if( empty($existCheckResult) ){
                        $newInvertedIndex[$eachMorpheme] = array($documentID);

                        // invertedIndexをDBへデータ追加
                        $sql = "insert into tokens (TOKEN, DOCS_COUNT, POSTINGS) values (?, ?, ?)";
                        $stmt = $dbh->prepare($sql);
                        $stmt->execute(array($eachMorpheme, "1", $documentID));
                    }else{
                        if(isset($newInvertedIndex[$eachMorpheme])){
                            // 同じドキュメント内ですでに登録されいるとき
                        }else{
                            $newPostings = $existCheckResult[0]["POSTINGS"]. ",". $documentID;
                            $sql = "update tokens SET DOCS_COUNT = DOCS_COUNT + 1, POSTINGS = '". $newPostings 
                                ."' WHERE ID =  ". $existCheckResult[0]["ID"];
                            $stmt = $dbh->prepare($sql);
                            $stmt->execute();

                            $newInvertedIndex[$eachMorpheme] = array($newPostings);
                        }
                    }
                }else{
                    break;
                }
            }
        }else{
            echo "処理を中断します。<br/>";
        }
    ?>

    <a href="index.html">TOP</a>

    </body>
</html>
