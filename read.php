<?php
session_start();
include('funcs.php');
$pdo = db_conn();

// ログアウト処理
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: read.php");
    exit;
}

// ログイン処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        header("Location: read.php");
        exit;
    } else {
        $login_error = "ログインに失敗しました。";
    }
}

// 登録処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':password', $password, PDO::PARAM_STR);
    $status = $stmt->execute();

    if ($status) {
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['name'] = $name;
        header("Location: read.php");
        exit;
    } else {
        $register_error = "登録に失敗しました。";
    }
}

// ログイン中のユーザー
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スケジュールカレンダー</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php if (isset($user_id)) : ?>
    <!-- ログインしている場合、カレンダーとスケジュール操作を表示 -->
    <h1>スケジュールカレンダー</h1>
    <p>ようこそ、<?= htmlspecialchars($_SESSION['name']); ?>さん！</p>
    <button id="logoutButton">ログアウト</button>
    <button id="createScheduleButton">新規スケジュール作成</button>
    <div id="calendar"></div>
<?php else : ?>
    <button id="loginButton">ログイン / 登録</button>
<?php endif; ?>

<!-- ログイン・登録モーダル -->
<div id="authModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h2>ログイン</h2>
        <?php if (isset($login_error)) : ?>
            <p style="color: red;"><?= $login_error; ?></p>
        <?php endif; ?>
        <form id="loginForm" method="POST">
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="パスワード" required><br>
            <button type="submit" name="login">ログイン</button>
        </form>

        <h2>新規登録</h2>
        <?php if (isset($register_error)) : ?>
            <p style="color: red;"><?= $register_error; ?></p>
        <?php endif; ?>
        <form id="registerForm" method="POST">
            <input type="text" name="name" placeholder="名前" required><br>
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="パスワード" required><br>
            <button type="submit" name="register">登録</button>
        </form>

        <button id="closeAuthModal">閉じる</button>
    </div>
</div>

<!-- 新規スケジュール作成・編集モーダル -->
<div id="scheduleModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h2 id="modalTitle">新規スケジュール作成</h2>
        <form id="scheduleForm">
            <input type="hidden" id="event_id" name="event_id">
            <input type="text" name="name" placeholder="名前" value="<?= htmlspecialchars($_SESSION['name']) ?>" readonly><br>
            <input type="text" id="event_name" name="event_name" placeholder="予定名" required><br>
            <input type="datetime-local" id="start_time" name="start_time" required><br>
            <input type="datetime-local" id="end_time" name="end_time" required><br>
            <button type="submit" id="saveButton">保存</button>
            <button type="button" id="deleteButton" style="display: none;">削除</button>
            <button type="button" id="closeScheduleModal">閉じる</button>
        </form>
    </div>
</div>

<!-- jQueryスクリプト -->
<script>
$(document).ready(function() {
    // ログイン・登録モーダルの表示
    $('#loginButton').click(function() {
        $('#authModal').fadeIn();
    });

    // モーダルを閉じる
    $('#closeAuthModal, #closeScheduleModal').click(function() {
        $('#authModal').fadeOut();
        $('#scheduleModal').fadeOut();
    });

    // ログアウト処理
    $('#logoutButton').click(function() {
        $.post('read.php', { logout: true }, function() {
            location.reload();
        });
    });

    // カレンダーをログインユーザーにのみ表示
    <?php if (isset($user_id)) : ?>
        var calendarEl = $('#calendar')[0];
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'ja',
            events: <?php
                // スケジュールデータを取得してカレンダーに渡す
                $stmt = $pdo->prepare("SELECT id, event_name AS title, start_time AS start, end_time AS end FROM schedule WHERE user_id = :user_id");
                $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($result);
            ?>,
            eventClick: function(info) {
                // イベントクリック時に編集モーダルを表示
                $('#event_id').val(info.event.id);
                $('#event_name').val(info.event.title);
                $('#start_time').val(new Date(info.event.start).toISOString().slice(0, 16));
                $('#end_time').val(new Date(info.event.end).toISOString().slice(0, 16));
                $('#deleteButton').show();
                $('#scheduleModal').fadeIn();
            },
            eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false }
        });
        calendar.render();

        // デバッグ用ログ出力
        console.log(<?php echo json_encode($result); ?>);
    <?php endif; ?>

    // 新規スケジュール作成モーダルの表示
    $('#createScheduleButton').click(function() {
        $('#event_id').val('');
        $('#event_name').val('');
        $('#start_time').val('');
        $('#end_time').val('');
        $('#deleteButton').hide();
        $('#scheduleModal').fadeIn();
    });

    // スケジュール保存（新規登録および編集）
    $('#scheduleForm').submit(function(event) {
        event.preventDefault();
        var formData = $(this).serialize();
        
        $.post('write.php', formData, function(response) {
            alert(response);
            location.reload(); // ページをリロードしてカレンダーを更新
        });
    });

    // スケジュール削除
    $('#deleteButton').click(function() {
        var eventId = $('#event_id').val();
        if (confirm("このスケジュールを削除しますか？")) {
            $.post('delete.php', { event_id: eventId }, function(response) {
                alert(response);
                location.reload();
            });
        }
    });
});
</script>

</body>
</html>
