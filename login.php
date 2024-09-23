<?php
session_start();
include('funcs.php');
$pdo = db_conn();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // ユーザーをデータベースから検索
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // パスワードの確認
    if ($user && password_verify($password, $user['password'])) {
        // セッションにユーザー情報を保存
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        header("Location: dashboard.php"); // ログイン後のページにリダイレクト
    } else {
        echo "ログインに失敗しました。";
    }
}
?>

<form method="POST" action="login.php">
    Email: <input type="email" name="email" required><br>
    パスワード: <input type="password" name="password" required><br>
    <button type="submit">ログイン</button>
</form>
