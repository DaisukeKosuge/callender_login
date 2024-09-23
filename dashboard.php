<?php
include('funcs.php');
check_session(); // ログイン確認

echo "ようこそ、" . htmlspecialchars($_SESSION['name']) . "さん！";
?>

<a href="logout.php">ログアウト</a>
<!-- スケジュール管理の画面（read.phpの内容をここに統合） -->
<?php include('read.php'); ?>
