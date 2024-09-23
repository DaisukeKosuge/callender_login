<?php
include('funcs.php');
$pdo = db_conn();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // パスワードをハッシュ化

    // ユーザーをデータベースに登録
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':password', $password, PDO::PARAM_STR);
    $status = $stmt->execute();

    if ($status === false) {
        $error = $stmt->errorInfo();
        echo "登録に失敗しました: " . $error[2];
    } else {
        echo "登録が完了しました。ログインしてください。";
    }
}
?>

<form method="POST" action="register.php">
    名前: <input type="text" name="name" required><br>
    Email: <input type="email" name="email" required><br>
    パスワード: <input type="password" name="password" required><br>
    <button type="submit">登録</button>
</form>
