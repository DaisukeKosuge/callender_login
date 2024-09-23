<?php
include('funcs.php');
$pdo = db_conn();
session_start();

$event_id = $_POST['event_id'];
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("DELETE FROM schedule WHERE id = :id AND user_id = :user_id");
$stmt->bindValue(':id', $event_id, PDO::PARAM_INT);
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$status = $stmt->execute();

if ($status) {
    echo "スケジュールが削除されました。";
} else {
    echo "スケジュールの削除に失敗しました。";
}
?>
