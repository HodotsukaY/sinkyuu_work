<?php
// ----------------------------------------------------
// 1. データベース接続設定
// ----------------------------------------------------
$pdo_dsn = 'mysql:host=localhost;dbname=sample_db;charset=utf8mb4;';
$pdo_user = 'root'; //本来であればユーザを作成します
$pdo_pass = ''; //本来であればパスワードを設定します
$pdo_option = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_STRINGIFY_FETCHES => false
);

$message = "ユーザー名とパスワードを入力してください。";
$pdo = null;

// ----------------------------------------------------
// 2. フォームデータ受信と処理
// ----------------------------------------------------

// フォームが POST メソッドで送信されたかチェック
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // データが存在するかチェック
    if (isset($_POST['username'], $_POST['password'])) {

        $username = $_POST['username'];
        $plain_password = $_POST['password'];

        // パスワードのハッシュ化（セキュリティ上必須）
        $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

        try {
            // データベースに接続
            $pdo = new PDO($pdo_dsn, $pdo_user, $pdo_pass, $pdo_option);

            // SQLインサート文の準備（プリペアドステートメント）
            $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
            $stmt = $pdo->prepare($sql);

            // 値のバインド
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);

            // 実行
            $stmt->execute();

            $message = "✅ ユーザー **" . htmlspecialchars($username) . "** の登録が完了しました！";

        } catch (\PDOException $e) {
            // エラー処理（開発中は詳細を表示しても良いが、本番環境では一般的なエラーメッセージに）
            $message = "❌ データベースエラーが発生しました: " . $e->getMessage();
        }
    } else {
        $message = "❌ ユーザー名またはパスワードが入力されていません。";
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ユーザー登録フォーム</title>
</head>
<body>
    <h1>新規ユーザー登録</h1>

    <p style="font-weight: bold;"><?= $message ?></p>
    <hr>

    <form action="" method="post">

        <label for="username">ユーザー名:</label>
        <input type="text" id="username" name="username" required>
        <br>

        <label for="password">パスワード:</label>
        <input type="password" id="password" name="password" required>
        <br>

        <input type="submit" value="登録実行">
    </form>
</body>
</html>