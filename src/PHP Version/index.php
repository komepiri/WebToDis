<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
<link rel="stylesheet" href="style.css">
<style>
img.emoji {
  height: 1em;
  width: 1em;
  margin: 0 .05em 0 .1em;
  vertical-align: -0.1em;
}
</style>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js"></script>
    <title>Web To Discord</title>
</head>
<body>
<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

$channelURLMap = [
    '1221415572043075594' => 'https://discord.com/api/webhooks/1229693407039848468/sKQolpF12HCQ4tBir_RIS7dNaoy2ynr2jwwKgBmQwakIwLpgKFe9NNldGZTiRWdoupqI',
    '1221412394023125153' => 'https://discord.com/api/webhooks/1236876172298682389/znOoItYq0wYphnpKI2YyRAadmbAltO935WG1njmiZtTb_nLslqkYsPgjgjQ-aiLXw9p3',
    '1221415688841986109' => 'https://discord.com/api/webhooks/1236876215923638303/XaBXMi6gwt8nDV0HuKGF2f4UYS82kJBbgmwgcZOGHZ315SdFiZrcy1HnuX5bAbCV7dls',
    '1221415809235026031' => 'https://discord.com/api/webhooks/1236876272970502174/EvYZWsL1kUw57bozBg65LwTCu7ZXUOK--GB1sNam3gNYwJ2uB4MDjCMOb4Q3Xed1dqym',
    '1236874581076086827' => 'https://discord.com/api/webhooks/1236876340221710436/GJLrd8Q_a33cAySUeP_GGBKIDQmKDLGRhQnj3CohU0fRE03fjEN9DEOoVIWi6Xnd6fQv',
  ];

    $id = $_GET['id'];
    if ($id == null) {
        $id = '1221415572043075594';
    }
    //echo $id;
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Discord Webhook URL
    $id2 = $_POST['id'];
    $webhookUrl = $channelURLMap[$id2];    // qフォームから送信されたデータを取得
    $name = $_POST['name'];
    $content = $_POST['content'];
    $image = $_POST['image'];
    // メッセージデータ
    $messageData = array(
        'username' => "$name",
        'avatar_url' => "$image",
        'content' => "$content"
    );

    // メッセージをJSON形式にエンコード
    $jsonData = json_encode($messageData);

    // cURLを使用してDiscordにメッセージを投稿
    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    // レスポンスを表示（デバッグ用）
    header('Location: http://localhost');
}
?>
<hr><h1>Web To Discord v2.0</h1>
<hr>
[<a href="">更新</a>]<br>
<hr>
<label for="list">送信先:</label><br>
<select id="endpointSelect" name="channelid">
    <option value="YOUR_CHANNEL_ID">YOUR_CHANNEL_NAME</option>
    <option value="YOUR_CHANNEL_ID">YOUR_CHANNEL_NAME</option>
</select>
<button onclick="redirect()">別チャンネルに移動</button>

<script>
  function redirect() {
    // プルダウンメニューの要素を取得
    var selectElement = document.getElementById("endpointSelect");
    // 選択されたオプションの値を取得
    var selectedValue = selectElement.value;
    // URLに?id=のパラメータを追加
    var url = "https://localhost/?id=" + selectedValue;
    // リダイレクト
    window.location.href = url;
  }
</script>

<form id="form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<input type="hidden" name="id" value="<?php echo $id ?>">
    <label for="name">名前:</label><br>
    <input type="text" id="name" class="inText" name="name" maxlength="35" required><br>
<label for="name">アイコン画像リンク:</label><br>
    <input type="text" id="image" class="inText" name="image"><br>

    <label for="content">内容:</label><br>
    <textarea id="content" name="content" class="textArea" rows="4" onfocus="colorReset(this)"
    oninput="textAreaHeightSet(this)"
    onchange="textAreaHeightSet(this)" maxlength="600" required></textarea><br>

    <input type="submit" value="送信">
</form><br>

<script>
        window.onload = function() {
            var inputName = Cookies.get("username");
            if (typeof inputName === "undefined") {
                document.getElementById("name").value = "";
            } else {
                document.getElementById("name").value = inputName;
            }

            var inputimgUrl = Cookies.get("imageurl");
            if (typeof inputimgUrl === "undefined") {
                document.getElementById("image").value = "";
            } else {
                document.getElementById("image").value = inputimgUrl;
            }
        };

        document.getElementById("form").onsubmit = function() {
            var imgurl = document.getElementById("image").value;
            Cookies.set("imageurl", imgurl , { expires: 365 });

            var name = document.getElementById("name").value;
            Cookies.set("username", name , { expires: 365 });
        };
    </script>
<?php

$apiEndpoint = 'http://YOUR_API_ENDPOINT' . $id; // Assuming your Node.js server is running locally

$response = file_get_contents("$apiEndpoint/");
$data = json_decode($response, true);

if (!$data) {
    die('APIサーバーがダウンしているか、投稿がありません。');
}
foreach ($data as $message) {
    $content2 = htmlspecialchars($message['content'], ENT_QUOTES, "UTF-8");
    $content2 = nl2br($content2);
    echo '<div>';
    echo '  <div style="display: flex; align-items: center; margin-bottom: 8px;">';
    echo '    <img src="' . $message['author']['avatarUrl'] . '" alt="Avatar" style="width: 24px; height: 24px; border-radius: 50%; margin-right: 8px; cursor: pointer;" onclick="fillTextarea(\'<@' . $message['author']['id'] . '>\')">';
    echo $message['author']['username'];
    echo '&nbsp;&nbsp; <p class="half-size">'. date('Y/m/d H:i:s', $message['timestamp'] / 1000) . '</p>';
    echo '  </div>';
    echo '  <div style="margin-left: 32px;">'; // Adjust margin as needed
        if (isset($message['imageUrl'])) {
    echo  $content2 . '<br><img src="' . $message['imageUrl'] . '" width="300" height="auto"</img>';
} else {
    echo $content2;
}
    echo '    <br>';
    echo '  </div>';
    echo '</div>';
    echo '<br>';
}

// JavaScript to fill the textarea
echo '<script>';
echo 'function fillTextarea(text) {';
echo '  var textarea = document.getElementById("content");'; // Replace with the actual ID of your textarea
echo '  textarea.value = text;';
echo '}';
echo '</script>';

?>
</body>
</html>
