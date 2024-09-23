<?php
include('funcs.php');
$pdo = db_conn();
session_start();

$name = $_SESSION['name'];
$event_name = $_POST['event_name'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];
$user_id = $_SESSION['user_id'];
$event_id = $_POST['event_id'];

if ($event_id) {
    // 既存スケジュールの編集
    $stmt = $pdo->prepare("UPDATE schedule SET event_name = :event_name, start_time = :start_time, end_time = :end_time WHERE id = :id AND user_id = :user_id");
    $stmt->bindValue(':event_name', $event_name, PDO::PARAM_STR);
    $stmt->bindValue(':start_time', $start_time, PDO::PARAM_STR);
    $stmt->bindValue(':end_time', $end_time, PDO::PARAM_STR);
    $stmt->bindValue(':id', $event_id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
} else {
    // 新規スケジュールの登録
    $stmt = $pdo->prepare("INSERT INTO schedule (name, event_name, start_time, end_time, user_id) VALUES (:name, :event_name, :start_time, :end_time, :user_id)");
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':event_name', $event_name, PDO::PARAM_STR);
    $stmt->bindValue(':start_time', $start_time, PDO::PARAM_STR);
    $stmt->bindValue(':end_time', $end_time, PDO::PARAM_STR);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
}

$status = $stmt->execute();

if ($status) {
    echo "スケジュールが保存されました。";
} else {
    echo "スケジュールの保存に失敗しました。";
}
?>
