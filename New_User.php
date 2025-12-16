<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>Card Form</title>

<?php
// ----------------------------------------------------
// 1. データベース接続設定
// ----------------------------------------------------
$pdo_dsn = 'mysql:host=localhost;dbname=login_db;charset=utf8mb4;';
$pdo_user = 'root'; //本来であればユーザを作成します
$pdo_pass = ''; //本来であればパスワードを設定します
$pdo_option = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_STRINGIFY_FETCHES => false
);

$message = "ユーザー名とパスワードを入力してください。";
$j = 0;
$pdo = null;

// ----------------------------------------------------
// 2. フォームデータ受信と処理
// ----------------------------------------------------

// フォームが POST メソッドで送信されたかチェック
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // データが存在するかチェック
    if (isset( $_POST['userid'], $_POST['username'], $_POST['password'])) {

        $userid = $_POST['userid'];
        $username = $_POST['username'];
        $password = $_POST['password'];

        // パスワードのハッシュ化（セキュリティ上必須）
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // データベースに接続
            $pdo = new PDO($pdo_dsn, $pdo_user, $pdo_pass, $pdo_option);

            // SQLインサート文の準備（プリペアドステートメント）
            $sql = "INSERT INTO users (user_id, user_name, password) VALUES (:user_id, :user_name, :password)";
            $stmt = $pdo->prepare($sql);

            // 値のバインド
            $stmt->bindParam(':user_name', $username, PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $userid, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);

            // 実行
            $stmt->execute();

            $message = "✅ ユーザー **" . htmlspecialchars($username) . "** の登録が完了しました！";
            $j = 1;

        } catch (\PDOException $e) {
            // エラー処理（開発中は詳細を表示しても良いが、本番環境では一般的なエラーメッセージに）
            $message = "❌ データベースエラーが発生しました: " . $e->getMessage();
        }
    } else {
        $message = "❌ ユーザー名またはパスワードが入力されていません。";
    }
}
?>

  <style>
    body {
      margin: 0;
      padding: 0;
      background-image: url("sibahu.png");
      background-size: cover;
      height: 100vh;
      font-family: sans-serif;

      /* 画面中央配置 */
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .back-btn {
      position: fixed;
      top: 20px;
      left: 20px;
      padding: 20px 70px;
      font-size: 40px;
      background: black;
      color: white;
      border: 2px solid black;
      border-radius: 100px;
      cursor: pointer;
    }

    /* ▼▼ 裏→表フリップ全体 ▼▼ */

    .flip-container {
      width: 500px;
      height: 694px;
      /* カード本来の比率に合わせる */
      perspective: 1200px;
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 0 auto;
    }

    .flip-card>div {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      backface-visibility: hidden;
    }

    /* カード画像（切れない設定） */
    .card-back img,
    .card-box img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      /* ←画像切れ防止 */
      display: block;
    }


    .flip-card {
      width: 100%;
      height: 100%;
      position: relative;
      transform-style: preserve-3d;
      animation: flip 1s forwards;
    }

    .flip-card>div {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      backface-visibility: hidden;
    }

    /* 裏面 */
    .card-back img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    /* 表面（入力画面） */
    .card-front {
      transform: rotateY(180deg);
    }

    /* フォーム位置調整 */
    .form-area {
      position: absolute;
      top: 22%;
      left: 15%;
      width: 70%;
      font-size: 20px;
      text-align: center;
    }

    .form-area input {
      width: 100%;
      margin-bottom: 50px;
      padding: 15px;
      font-size: 18px;
      background-color: rgb(227, 223, 223);
      box-sizing: border-box;
    }

    .form-area button {
      padding: 6px 16px;
      font-size: 18px;
    }

          /* 裏面 */
    .card-back img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    /* 表面（入力画面） */
    .card-front {
      transform: rotateY(180deg);
    }

    /* カード画像 */
    .card-box img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }


    <?php
    if ($j == 0) {
      ?>

   /* フリップアニメーション */
    @keyframes flip {
      0% {
        transform: rotateY(0);
      }

      100% {
        transform: rotateY(180deg);
      }
    }

    <?php
    } else {
    ?>
    @keyframes flip {
      0% {
        transform: rotateY(180deg);
      }

      100% {
        transform: rotateY(360deg);
      }
    }

    <?php
    }
    ?>

    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background:
        radial-gradient(circle at 20% 20%, #277b3c 0, #16602c 55%, #0e3f1d 100%);
    }
  </style>
</head>

<body>

  <button class="back-btn">◀ 戻る</button>
  <p style="font-weight: bold;"><?= $message ?></p>
  <!-- ▼ 裏→表の flip コンテナ ▼ -->
  <div class="flip-container">
    <div class="flip-card">

      <!-- 裏面 -->
      <div class="card-back">
        <img src="https://deckofcardsapi.com/static/img/back.png" alt="back">
      </div>

      <!-- 表面（フォーム付カード） -->
      <div class="card-front">
        <div class="card-box">
          <img src="img\3S.png" alt="3S">

          <form action="" method="post">

            <div class="form-area">
              <label for="userid">ユーザーID</label>
              <input type="text" id="userid" name="userid" required>

              <label for="username">ユーザー名</label>
              <input type="text" id="username" name="username" required>

              <label for="password">パスワード</label>
              <input type="password" id="password" name="password" required>

              <button type="submit">OK</button>

            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
  <script>

    const back = document.querySelector('.back-btn');
    back.addEventListener('click', () => {
      window.location.href = 'login.html';
    });
  </script>
</body>

</html>
