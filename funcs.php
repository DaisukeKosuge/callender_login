<?php
// DB接続
function db_conn() {
    $db_name = '*******';        
    $db_user = '*******';           
    $db_password = '*******';   
    $db_host = '*******';           

    try {
        $pdo = new PDO('mysql:dbname=' . $db_name . ';host=' . $db_host . ';charset=utf8', $db_user, $db_password);
        return $pdo;
    } catch (PDOException $e) {
        exit('DB Connection Error: ' . $e->getMessage());
    }
}

// セッションの開始と未ログイン時のリダイレクト
function check_session() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}
?>
