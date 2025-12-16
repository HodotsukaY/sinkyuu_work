<?php
// セッションの開始
session_start();

// ----------------------------------------------------
// 0. データベース接続設定 (★★★★ 接続テスト用の一般的な設定に修正済み ★★★★)
// ----------------------------------------------------
// サーバー情報: XAMPP, MAMP, ローカルDockerなどで最も一般的
define('DB_HOST', 'localhost');

// データベース名: 実際のデータベース名に修正してください
define('DB_NAME', 'login_db'); 

// データベースユーザー名: XAMPPのデフォルトは'root'
define('DB_USER', 'root');   

// データベースパスワード: XAMPPのデフォルトはパスワードなし（空文字）
// MAMPの場合は'root'であることが多いです。環境に合わせて修正してください。
define('DB_PASS', '');   

// 認証失敗時にエラーメッセージを表示するページ
$login_page = "Login.php"; // このファイル名（login.php）を想定

$error_message = ""; 

// ----------------------------------------------------
// 1. POSTリクエストのチェックとフォームデータの受け取り
// ----------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ユーザーIDとパスワードのデータを取得
    $input_user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';
    $input_password = isset($_POST['password']) ? $_POST['password'] : '';

    // 入力値の簡易チェック
    if (empty($input_user_id) || empty($input_password)) {
        $error_message = "ユーザーIDとパスワードを入力してください。";
    } else {

        try {
            // ----------------------------------------------------
            // 2. データベース接続 (修正なし)
            // ----------------------------------------------------
            // PDO (PHP Data Objects) を使用してデータベースに接続
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // ----------------------------------------------------
            // 3. ユーザーデータの取得（プリペアドステートメントを使用）
            // ----------------------------------------------------
            // テーブル構造に合わせてカラム名を使用: user_id, user_name, password (ハッシュ値)
            $stmt = $pdo->prepare("SELECT user_id, user_name, password AS password_hash FROM users WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $input_user_id);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // ----------------------------------------------------
            // 4. 認証ロジックの実行
            // ----------------------------------------------------

            // ユーザーが存在し、password_verify() でハッシュ化パスワードが一致するか検証
            if ($user && password_verify($input_password, $user['password_hash'])) {
                
                // ★ ログイン成功 ★
                session_regenerate_id(true); 
                
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['password'] = $user['password_hash']; 
                
                // ログイン後のゲーム画面にリダイレクト
                header("Location: GameChange.php"); 
                exit();
                
            } else {
                
                // ★ ログイン失敗 ★
                $error_message = "ユーザーIDまたはパスワードが間違っています。";
            }

        } catch (PDOException $e) {
            // データベース接続またはSQL実行エラー
            $error_message = "データベース接続エラー: 設定を確認してください。";
            // ログ記録の推奨
            // error_log("Database Error: " . $e->getMessage()); 
        }
    }
}
// この下には、エラーメッセージを表示するHTMLフォームのコードが続きます
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>カードログイン画面</title>
    <style>
        /* 既存のCSSコード (変更なし) */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", "Noto Sans JP", sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column; /* エラーメッセージのために flex-direction: column を追加 */
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at 20% 20%, #277b3c 0, #16602c 55%, #0e3f1d 100%);
        }

        .table-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 35px;
        }

        .cards-row {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 40px;
            white-space: nowrap;
        }

        .card-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .card {
            position: relative;
            width: 180px;
            aspect-ratio: 63 / 88;
            cursor: pointer;
        }

        .card img {
            width: 100%;
            height: 100%;
            border-radius: 14px;
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.4);
        }

        .card-inner {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 18% 12% 14%;
            pointer-events: none;
        }

        .field-label {
            color: #444;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 14px;
            text-shadow: 0 0 3px rgba(255, 255, 255, 0.7);
        }

        .field-input {
            width: 100%;
            pointer-events: auto;
            padding: 6px 10px;
            border-radius: 8px;
            border: 3px solid #555;
            background: #dcdcdc;
            font-size: 16px;
        }

        .btn {
            min-width: 170px;
            padding: 12px 20px;
            border-radius: 12px;
            border: 3px solid #c77707;
            background: #f4b034;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 6px 0 #b06304;
        }

        .btn-secondary {
            background: #ffe082;
        }
        
        .error-message {
            color: #ffdddd; /* 背景色に合わせて明るい赤 */
            background-color: #a00000;
            padding: 10px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
            border: 2px solid #ff4444;
        }
    </style>
</head>

<body>
    
    <?php 
    // PHPで認証に失敗した場合のエラーメッセージを表示
    if ($error_message !== ""): 
    ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <div class="table-area">

        <form method="POST" action=""> 

            <div class="cards-row">

                <div class="card-wrapper">
                    <div class="card" onclick="location.href='New_User.php'">
                        <img src="https://deckofcardsapi.com/static/img/back.png" alt="back card">
                    </div>
                    <button class="btn btn-secondary" type="button" onclick="location.href='New_User.php'">新規ユーザー</button>
                </div>

                <div class="card-wrapper">
                    <div class="card" onclick="location.href='GameChange.php'">
                        <img src="https://deckofcardsapi.com/static/img/X1.png" alt="joker card">
                    </div>
                    <button class="btn btn-secondary" type="button" onclick="location.href='GameChange.php'">ゲストユーザー</button>
                </div>

                <div class="card-wrapper">
                    <div class="card">
                        <img src="img/AD.png" alt="AD">
                        <div class="card-inner">
                            <div class="field-label">ユーザー</div>
                            <input class="field-input" type="text" id="userid" name="user_id"> 
                        </div>
                    </div>
                </div>

                <div class="card-wrapper">
                    <div class="card">
                        <img src="img/2C.png" alt="2C">
                        <div class="card-inner">
                            <div class="field-label">パスワード</div>
                            <input class="field-input" type="password" id="password" name="password">
                        </div>
                    </div>
                </div>

            </div>

            <button class="btn" type="submit">ログイン</button>

        </form>

    </div>

</body>

</html>