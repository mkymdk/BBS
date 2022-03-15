<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>BBS</title>
</head>
<body>
    <?php
    //  データベースへの接続　開始
    $dsn = 'データベース名';
    $user = 'ユーザー名';
    $password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    
    // データを登録するための「テーブル」を作成 開始
    $sql = "CREATE TABLE IF NOT EXISTS tbBBS"
    ." ("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "called char(32),"
    . "comment TEXT,"
    . "date TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,"
    . "pass char(32)"
    .");";
    $stmt = $pdo->query($sql);
    
    // 入力フォーム
    if (!empty($_POST["called"]) && !empty($_POST["comment"]) && !empty($_POST["called_password"])) { 
    $called = $_POST["called"];
    $comment = $_POST["comment"]; 
    $pass = $_POST["called_password"];
    
        if (empty($_POST["number"])) {    // 新規登録モード
            // データ（レコード）を登録 　開始
            $sql = $pdo -> prepare("INSERT INTO tbBBS (called, comment, pass) VALUES (:called, :comment, :pass)");
            //登録するデータをセット
            $sql -> bindParam(':called', $called, PDO::PARAM_STR);
            $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
            $sql -> bindParam(':pass', $pass, PDO::PARAM_STR);
            //SQL実行
            $sql -> execute();
            //bindParamの引数名（:comment など）はテーブルのカラム名に併せるとミスが少なくなる
            // データ（レコード）を登録 終了
        } else { //編集モード
            $number = $_POST["number"];
            $sql = 'SELECT * FROM tbBBS';
            $stmt = $pdo->query($sql);
            $results = $stmt->fetchAll();
                foreach ($results as $row){
                // 投稿番号と編集対象番号を比較
                    if ($row['id'] == $number && $row['pass'] == $pass) {
                        $id = $number; //変更する投稿番号
                        $date = date("Y/m/d H:i:s");
                        $sql = 'UPDATE tbBBS SET called=:called,comment=:comment,date=:date WHERE id=:id AND pass=:pass';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':called', $called, PDO::PARAM_STR);
                        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
                        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                        $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
                        $stmt->execute();
                    } else {
                        if ($row['id'] == $number && $row['pass'] != $pass) {
                            echo "パスワードが違います。"; 
                        }
                    }
                }
        }
    
    // 削除フォーム
    } elseif (!empty($_POST["deletion"]) && !empty($_POST["deletion_password"])) { 
        $deletion = $_POST["deletion"];
        $pass = $_POST["deletion_password"];
        $sql = 'SELECT * FROM tbBBS';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
            foreach ($results as $row){
            // 投稿番号と削除対象番号を比較
                if ($row['id'] == $deletion && $row['pass'] == $pass) {
                    $id = $deletion;
                    $sql = 'delete from tbBBS where id=:id AND pass=:pass';
                    //SQL文に対して、一部分的を変更の可能な場所として定義し、さらに定義したものを自由に変更できるようにする
                    $stmt = $pdo->prepare($sql); 
                    // bind 変数を「：」で始まるものに結びつけ(関連付け)
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
                    $stmt->execute();
                } elseif ($row['id'] == $deletion && $row['pass'] != $pass) {
                    echo "パスワードが違います。"; 
                }
            }
            
    // 編集フォーム        
    } elseif (!empty($_POST["edit"]) && !empty($_POST["edit_password"])) { 
        $edit = $_POST["edit"];
        $edit_password = $_POST["edit_password"];
        $sql = 'SELECT * FROM tbBBS';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
            foreach ($results as $row){
                // 投稿番号と編集対象番号を比較
                if ($row['id'] == $edit) {
                    $edit_number = $row['id'];
                    $edit_called = $row['called'];
                    $edit_comment = $row['comment'];
                    $new_edit_password = $row['pass'];
                        if ($new_edit_password != $edit_password) {
                            echo "パスワードが違います。";
                        }
                }
            }
    }
    ?>

     <!--フォーム：「名前」「コメント」の入力と「送信」ボタンが1つあるフォームを作成-->
    <form action="" method="post">
        <input type="text" name="called" placeholder="名前" 
        value= <?php if (isset($edit_called) && $new_edit_password == $edit_password) {echo $edit_called;}?>>
        <br> 
        <input type="text" name="comment" placeholder="コメント" 
        value=<?php if (isset($edit_comment) && $new_edit_password == $edit_password) {echo $edit_comment;}?>>
        <br>
        <input type="text" name="called_password" placeholder="パスワード">
        <input type="hidden" name="number" 
        value=<?php if (isset($edit_number) && $new_edit_password == $edit_password) {echo $edit_number;}?>>
        <input type="submit" name="submit">
    </form>
    
    <br>
    
    <form action="" method="post">
        <input type="text" name="deletion" placeholder="削除対象番号">
        <br>
        <input type="text" name="deletion_password" placeholder="パスワード">
        <button type="submit" name="deletion_submit" value="exec">削除</button>
    </form>
    
    <br>
    
    <form action="" method="post">
        <input type="text" name="edit" placeholder="編集対象番号">
        <br>
        <input type="text" name="edit_password" placeholder="パスワード">
        <button type="submit" name="edit_submit" value="exec">編集</button>
    </form>
    
    <?php 
    // 入力したデータレコードを抽出し、表示する　開始
    $sql = 'SELECT * FROM tbBBS';
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
        foreach ($results as $row){
            //$rowの中にはテーブルのカラム名が入る
            echo $row['id'].',';
            echo $row['called'].',';
            echo $row['comment'].',';
            echo $row['date'].'<br>';
        echo "<hr>";
        }
    // 入力したデータレコードを抽出し、表示する　終了

    ?>

</body>
</html>